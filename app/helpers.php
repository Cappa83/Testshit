<?php
function h($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function now(): string { return date('c'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function flash(?string $m=null, string $type='ok'): ?array { if($m!==null){$_SESSION['flash']=['msg'=>$m,'type'=>$type]; return null;} $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function post($k,$d=''){ return $_POST[$k] ?? $d; }
function json_response($data): never { header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE); exit; }
function download(string $name, string $mime, string $body): never { header('Content-Type: '.$mime); header('Content-Disposition: attachment; filename="'.$name.'"'); echo $body; exit; }
function render(string $view, array $vars=[]): void { extract($vars); ob_start(); require APP_ROOT.'/views/'.$view.'.php'; $content=ob_get_clean(); require APP_ROOT.'/views/layout.php'; }
function tags_to_string(array $tags): string { return implode(', ', array_column($tags,'name')); }
function csv_tags(string $s): array { return array_values(array_filter(array_unique(array_map('trim', explode(',', $s))), fn($x)=>$x!=='')); }
