<?php
declare(strict_types=1);
error_reporting(E_ALL); ini_set('display_errors','0');
session_start();
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT.'/data');
define('DB_PATH', DATA_DIR.'/inventar.sqlite');
foreach ([DATA_DIR, DATA_DIR.'/backups', DATA_DIR.'/logs'] as $d) if(!is_dir($d)) mkdir($d,0775,true);
set_exception_handler(function(Throwable $e){ error_log('['.date('c').'] '.$e."\n",3,DATA_DIR.'/logs/app.log'); http_response_code(500); echo '<h1>Fehler</h1><p>Ein interner Fehler wurde protokolliert.</p>'; });
require_once __DIR__.'/helpers.php'; require_once __DIR__.'/csrf.php'; require_once __DIR__.'/db.php'; require_once __DIR__.'/audit_service.php'; require_once __DIR__.'/inventory_service.php'; require_once __DIR__.'/import_service.php'; require_once __DIR__.'/export_service.php'; require_once __DIR__.'/router.php';
init_db();
