# Selbsttest

## Geprüft

- PHP-Syntax aller PHP-Dateien per `php -l`.
- Datenbankinitialisierung über `php -r 'require "app/bootstrap.php"; ...'`.
- Datenbankinitialisierung mit leerem Ort-/Behälterbestand geprüft.
- InvPatch-Beispiel als Dry-Run und echter Import geprüft.
- Suche/Tags indirekt über importierte Testartikel geprüft.
- Exportfunktionen für Vollbackup und InvPatch geprüft.
- Löschbarkeit eines unbenutzten Ortes per Datenbankoperation geprüft.
- Dark-Theme/CSS-Änderung statisch geprüft; Browser-Screenshot wurde nicht erneut erzeugt.
- HTML-Escaping über Helper `h('<script>alert(1)</script>')` geprüft.
- Transaktionales Importverhalten mit absichtlich ungültiger Operation geprüft.

## Ergebnis

Die lokalen CLI-Prüfungen waren erfolgreich. Die App erzeugt die SQLite-Datenbank automatisch ohne vorgefüllte Orte und kann über XAMPP als `http://localhost/inventar/` geöffnet werden.

## Bekannte Einschränkungen

- Restore ist defensiv umgesetzt: kein stilles Überschreiben; JSON wird über InvPatch importiert, SQLite-Restore erfolgt manuell nach Backup.
- Delete ist hartes Löschen mit Sicherheitsdialog, kein Softdelete.
- Orte sind über die Orte-Seite löschbar, sofern keine Unterorte, Behälter oder Artikel daran hängen. Behälter nutzen weiterhin einfache Formulare.

## Nächste sinnvolle Verbesserungen

- Komfortable Edit-Links für Orte und Behälter.
- Abhängiger Behälter-Dropdown pro Ort per JavaScript.
- Optionaler Softdelete-Status für Artikel.
