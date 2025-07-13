<?php
$turnir_id=poste('turnir_id');
// тригер что то делает но не чего не возвращаетs
$sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.$turnir_id;
$cnt_players = db_field($sql,'cnt');
$cnt_players = (!empty($cnt_players) && $cnt_players>0) ? $cnt_players : 0 ;
$sql = 'update `'.T_TURNIRS.'` set cnt_players='.($cnt_players).' where id='.$turnir_id;
//s($sql);
db_query($sql);
