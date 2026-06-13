<?php
function audit_log(string $type, ?int $id, string $action, $old=null, $new=null): void { db()->prepare('INSERT INTO audit_log(entity_type,entity_id,action,old_json,new_json,created_at) VALUES(?,?,?,?,?,?)')->execute([$type,$id,$action,json_encode($old,JSON_UNESCAPED_UNICODE),json_encode($new,JSON_UNESCAPED_UNICODE),now()]); }
