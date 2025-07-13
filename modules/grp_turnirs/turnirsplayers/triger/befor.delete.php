<?php
//s($_POST);
$turnir_id=poste('turnir_id');
$id=poste('id');

$sql ='select count(*) as cn from '.T_REITING.' r,`'.T_TURNIR_PLAYERS.'` tp  where tp.id='.$id.' and  (pl_id_1=tp.player_id or pl_id_2=tp.player_id) and r.turnir_id='.$turnir_id;
$cn=db_field($sql,'cn');
if ($cn>0) 
//s($sql);
 window_mess('В игрока есть назначеные или сыгранные игры. Удалять нельзя! Игр=' . $cn);
?>