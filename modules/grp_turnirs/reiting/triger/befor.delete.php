<?php
$id=poste('id');
$turnir_id=poste('turnir_id');
$sql ='select auto from '.T_REITING.'  where id='.$id.' and turnir_id='.$turnir_id;
$auto=db_field($sql,'auto');
if ($auto>0) 
 window_mess('Данная игра создана автоматически. Удалять нельзя!');

?>
