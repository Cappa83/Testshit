<?php
function db(): PDO { static $pdo; if(!$pdo){$pdo=new PDO('sqlite:'.DB_PATH); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC); $pdo->exec('PRAGMA foreign_keys=ON');} return $pdo; }
function init_db(): void { $p=db();
$p->exec("CREATE TABLE IF NOT EXISTS locations(id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE NOT NULL, name TEXT NOT NULL, area TEXT, parent_id INTEGER NULL REFERENCES locations(id), notes TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE IF NOT EXISTS containers(id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE NOT NULL, name TEXT NOT NULL, type TEXT, location_id INTEGER NOT NULL REFERENCES locations(id), notes TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE IF NOT EXISTS items(id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, quantity REAL NOT NULL DEFAULT 1, unit TEXT NOT NULL DEFAULT 'Stk', category TEXT, location_id INTEGER NOT NULL REFERENCES locations(id), container_id INTEGER NULL REFERENCES containers(id), location_detail TEXT, notes TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE IF NOT EXISTS tags(id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT UNIQUE NOT NULL);
CREATE TABLE IF NOT EXISTS item_tags(item_id INTEGER NOT NULL REFERENCES items(id) ON DELETE CASCADE, tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE, PRIMARY KEY(item_id,tag_id));
CREATE TABLE IF NOT EXISTS audit_log(id INTEGER PRIMARY KEY AUTOINCREMENT, entity_type TEXT NOT NULL, entity_id INTEGER, action TEXT NOT NULL, old_json TEXT, new_json TEXT, created_at TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS app_meta(key TEXT PRIMARY KEY, value TEXT);");
} 
