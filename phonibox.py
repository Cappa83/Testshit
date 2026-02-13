#!/usr/bin/env python3
"""Minimalistische, performante RFID-Audiobox für Raspberry Pi 5.

Features:
- RFID lesen (MFRC522 über SimpleMFRC522)
- Lauter/Leiser Buttons (GPIO)
- Kein Webinterface
- Tokens werden in CSV-Datei protokolliert
- Token -> Ordner Mapping in CSV-Datei (manuell pflegbar)
"""

from __future__ import annotations

import csv
import logging
import signal
import threading
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Dict, List

import pygame
from gpiozero import Button
from mfrc522 import SimpleMFRC522

AUDIO_EXTENSIONS = {".mp3", ".wav", ".ogg", ".flac", ".m4a"}


@dataclass(frozen=True)
class Config:
    token_map_file: Path = Path("token_map.csv")
    token_log_file: Path = Path("token_reads.log")
    volume_up_pin: int = 17
    volume_down_pin: int = 27
    volume_step: float = 0.05
    default_volume: float = 0.60
    debounce_seconds: float = 0.12
    token_absence_seconds: float = 0.4


class TokenStore:
    """CSV-basiertes Token-Mapping und Logging."""

    def __init__(self, map_file: Path, log_file: Path) -> None:
        self.map_file = map_file
        self.log_file = log_file
        self._lock = threading.RLock()
        self._ensure_files()

    def _ensure_files(self) -> None:
        if not self.map_file.exists():
            self.map_file.write_text("token_id,folder\n", encoding="utf-8")
        if not self.log_file.exists():
            self.log_file.write_text("timestamp,token_id\n", encoding="utf-8")

    def read_map(self) -> Dict[str, str]:
        mapping: Dict[str, str] = {}
        with self._lock:
            with self.map_file.open("r", encoding="utf-8", newline="") as f:
                reader = csv.DictReader(f)
                for row in reader:
                    token = (row.get("token_id") or "").strip()
                    folder = (row.get("folder") or "").strip()
                    if token:
                        mapping[token] = folder
        return mapping

    def log_token(self, token_id: str) -> None:
        timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
        with self._lock:
            with self.log_file.open("a", encoding="utf-8", newline="") as f:
                f.write(f"{timestamp},{token_id}\n")

    def append_unknown_token(self, token_id: str) -> None:
        mapping = self.read_map()
        if token_id in mapping:
            return

        with self._lock:
            # Race-safe second check
            mapping = self.read_map()
            if token_id in mapping:
                return
            with self.map_file.open("a", encoding="utf-8", newline="") as f:
                f.write(f"{token_id},\n")


class FolderPlayer:
    def __init__(self, default_volume: float) -> None:
        pygame.mixer.init()
        self._volume = max(0.0, min(1.0, default_volume))
        pygame.mixer.music.set_volume(self._volume)
        self._playlist: List[Path] = []
        self._index = 0
        self._lock = threading.RLock()

    @property
    def volume(self) -> float:
        return self._volume

    def set_volume(self, value: float) -> None:
        with self._lock:
            self._volume = max(0.0, min(1.0, value))
            pygame.mixer.music.set_volume(self._volume)
            logging.info("Lautstärke: %d%%", int(self._volume * 100))

    def stop(self) -> None:
        with self._lock:
            pygame.mixer.music.stop()
            self._playlist = []
            self._index = 0

    def play_folder(self, folder: Path) -> bool:
        if not folder.exists() or not folder.is_dir():
            logging.warning("Ordner nicht gefunden: %s", folder)
            return False

        tracks = sorted(
            [
                p
                for p in folder.rglob("*")
                if p.is_file() and p.suffix.lower() in AUDIO_EXTENSIONS
            ]
        )
        if not tracks:
            logging.warning("Keine Audiodateien in: %s", folder)
            return False

        with self._lock:
            self._playlist = tracks
            self._index = 0
            self._play_current_locked()

        logging.info("Spiele %d Tracks aus %s", len(tracks), folder)
        return True

    def tick(self) -> None:
        with self._lock:
            if not self._playlist:
                return
            if pygame.mixer.music.get_busy():
                return
            self._index += 1
            if self._index >= len(self._playlist):
                logging.info("Playlist beendet.")
                self._playlist = []
                self._index = 0
                return
            self._play_current_locked()

    def _play_current_locked(self) -> None:
        track = self._playlist[self._index]
        pygame.mixer.music.load(str(track))
        pygame.mixer.music.play()
        logging.info("Track: %s", track)


class PhoniBox:
    def __init__(self, config: Config) -> None:
        self.config = config
        self.store = TokenStore(config.token_map_file, config.token_log_file)
        self.player = FolderPlayer(config.default_volume)
        self.reader = SimpleMFRC522()
        self._running = True
        self._active_token_id: str | None = None
        self._active_token_last_seen = 0.0

        self.btn_up = Button(config.volume_up_pin, bounce_time=config.debounce_seconds)
        self.btn_down = Button(config.volume_down_pin, bounce_time=config.debounce_seconds)
        self.btn_up.when_pressed = self.on_volume_up
        self.btn_down.when_pressed = self.on_volume_down

    def on_volume_up(self) -> None:
        self.player.set_volume(self.player.volume + self.config.volume_step)

    def on_volume_down(self) -> None:
        self.player.set_volume(self.player.volume - self.config.volume_step)

    def stop(self) -> None:
        self._running = False
        self.player.stop()
        pygame.mixer.quit()

    def run(self) -> None:
        logging.info("PhoniBox gestartet. Warte auf RFID...")
        while self._running:
            self.player.tick()
            self._read_token_nonblocking()
            time.sleep(0.05)

    def _read_token_nonblocking(self) -> None:
        # Bibliothek ist blockierend; wir nutzen einen kurzen Poll-Thread mit Timeout.
        token_holder: Dict[str, str] = {}

        def _reader() -> None:
            token_id, _ = self.reader.read_no_block()
            if token_id is not None:
                token_holder["id"] = str(token_id)

        t = threading.Thread(target=_reader, daemon=True)
        t.start()
        t.join(timeout=0.02)

        token_id = token_holder.get("id")
        if not token_id:
            if self._active_token_id and (
                time.monotonic() - self._active_token_last_seen
            ) > self.config.token_absence_seconds:
                self._active_token_id = None
            return

        self._active_token_last_seen = time.monotonic()
        if token_id == self._active_token_id:
            return
        self._active_token_id = token_id

        logging.info("RFID gelesen: %s", token_id)
        self.store.log_token(token_id)

        mapping = self.store.read_map()
        folder = mapping.get(token_id, "").strip()
        if not folder:
            logging.info("Unbekanntes Token. In token_map.csv eingetragen: %s", token_id)
            self.store.append_unknown_token(token_id)
            return

        self.player.play_folder(Path(folder))


def main() -> None:
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(message)s",
    )
    app = PhoniBox(Config())

    def _sig_handler(_signum: int, _frame: object) -> None:
        logging.info("Stop-Signal erhalten.")
        app.stop()

    signal.signal(signal.SIGINT, _sig_handler)
    signal.signal(signal.SIGTERM, _sig_handler)

    try:
        app.run()
    finally:
        app.stop()


if __name__ == "__main__":
    main()
