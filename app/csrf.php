<?php
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.h(csrf_token()).'">'; }
function require_csrf(): void { if(($_POST['csrf']??'')!==($_SESSION['csrf']??'')) throw new RuntimeException('CSRF-Token ungültig.'); }
