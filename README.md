# Minimal PhoniBox (Raspberry Pi 5, Python)

Minimalistische RFID-Audiobox ohne Webinterface.

## Features
- RFID einlesen (MFRC522)
- Lauter/Leiser per 2 Buttons
- Jedes gelesene RFID Token wird in `token_reads.log` geschrieben
- Token→Ordner Mapping in `token_map.csv` (manuell editierbar)
- Bei unbekanntem Token wird automatisch eine neue Zeile in `token_map.csv` angelegt

## Hardware
- Raspberry Pi 5
- RFID Reader MFRC522 (SPI)
- 2 Taster:
  - Lauter: GPIO17
  - Leiser: GPIO27

Pins kannst du direkt im `Config`-Dataclass in `phonibox.py` ändern.

## Installation
```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Start
```bash
python3 phonibox.py
```

## Mapping pflegen
Datei `token_map.csv`:
```csv
token_id,folder
1234567890,/home/pi/audio/kinderlieder
9876543210,/home/pi/audio/hoerspiele
```

Wenn ein Token gescannt wird:
1. Eintrag in `token_reads.log`
2. Falls Token im Mapping vorhanden: Inhalt des Ordners wird einmal abgespielt (keine Dauerschleife)
3. Entfernst du den RFID-Token während der Wiedergabe, spielt der aktuelle Inhalt trotzdem sauber zu Ende
4. Falls unbekannt: Token wird mit leerem Ordner automatisch in `token_map.csv` ergänzt

## Systemd (optional)
Beispiel-Service `/etc/systemd/system/phonibox.service`:
```ini
[Unit]
Description=Minimal PhoniBox
After=network.target

[Service]
Type=simple
User=pi
WorkingDirectory=/home/pi/phonibox
ExecStart=/home/pi/phonibox/.venv/bin/python /home/pi/phonibox/phonibox.py
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Dann:
```bash
sudo systemctl daemon-reload
sudo systemctl enable phonibox
sudo systemctl start phonibox
```
