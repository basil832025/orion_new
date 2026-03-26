<?php


$turnir_id=poste('turnir_id');

// тригер что то делает но не чего не возвращаетs
 $sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.(int)$turnir_id;
      $cnt_players = (int)db_field($sql,'cnt');
  $sql = 'SELECT count(*) as cnt FROM `'.T_ETAPS.'` where turnir_id='.(int)$turnir_id;
      $etaps_cnt = (int)db_field($sql,'cnt');
     $etaps_cnt = $etaps_cnt + 1;
      
    $aDataADD = array('mesto_to'=>100,'mesto_from'=>1,'istochnik_posev'=>'0','cnt_people'=>$cnt_players
    ,'name_etap'=>'Этап '.$etaps_cnt);

$_SESSION['BEFOR_ADD']=$aDataADD;
 ?>   
    
