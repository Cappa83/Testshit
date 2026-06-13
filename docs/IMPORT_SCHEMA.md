# InvPatch Schema

Aktuelles Schema: `kappa-invpatch/v1`.

Ein Dokument enthält `schema`, optional `source`, optional `comment` und `operations` als Array.

Unterstützte Operationen:

- `upsert_location`: `code`, `name`, optional `area`, `parent_code`, `notes`.
- `upsert_container`: `code`, `name`, `location_code`, optional `type`, `notes`.
- `add_item`: legt immer einen neuen Artikel an, ohne Artikelcode.
- `upsert_item`: matcht exakt über `name`, `location_code`, optional `container_code`.
- `update_item`: aktualisiert über interne `id`.
- `move_item`: verschiebt über interne `id`.
- `delete_item`: löscht nur mit expliziter interner `id`.

Alle Imports laufen in einer Transaktion: Fehler bewirken Rollback.
