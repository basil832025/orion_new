<?php

$form = poste('form');
$turnir_id=poste('turnir_id');
$etap_id=poste('etap_id');
//if ($form['num_posev_olimp']>16)  window_mess('Місце посіву не повинно бути більше 16!');
$sql = 'SELECT mesto_all FROM `'.T_ETAPS_PLAYER_MESTA.'` where   etap_id='.$etap_id. ' order by mesto_all desc limit 1';
$mesto_all= db_field($sql,'mesto_all');
$mesto_all++;
$sql = 'update  `'.T_ETAPS_PLAYER_MESTA.'` set mesto_all='.$mesto_all.'  where  mesto_all is null and etap_id='.$etap_id;
//s($sql);
db_query($sql);
?>