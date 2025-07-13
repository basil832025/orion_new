<?php
$id = poste('id');
$sql = 'select count(*) as cn from ' . T_TURNIR_PLAYERS . '  where  player_id=' . $id;
$cn_results = db_field($sql, 'cn');
if ($cn_results > 0)
    window_mess('Цей гравець вже грав на '.$cn_results.' турнірах. Видаляти неможна!' );




