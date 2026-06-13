# KappaInventar

Lokales Inventarsystem für XAMPP/PHP 8.x mit SQLite, ohne Composer, CDN oder Build-Schritt.

## Start unter XAMPP

1. Projektordner nach `C:\xampp\htdocs\inventar` kopieren.
2. Apache starten.
3. `http://localhost/inventar/` öffnen.
4. Beim ersten Aufruf wird `data/inventar.sqlite` mit leerem Schema erstellt; Orte legst du selbst an oder importierst sie per InvPatch.

## Funktionen

- Dark-UI für Dashboard, Artikel, Orte, Behälter, Import, Export/Backup, Audit Log, Wartung.
- Artikel haben keine sichtbaren Artikelcodes; verwendet wird die interne ID.
- Orte und Behälter behalten sinnvolle Codes wie `A01`, `S03-L`, `B01`.
- InvPatch JSON Import ist transaktional.
- Exporte: Vollbackup JSON, InvPatch JSON, SQLite-Download mit lokaler Backup-Kopie.
