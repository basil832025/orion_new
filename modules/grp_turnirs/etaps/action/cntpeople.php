<?php


$cnt= poste ('cnt');
$turnir_id= poste ('turnir_id');
//s($_POST);
$_SESSION['etaps']['cnt_people'] = $cnt;

 $sql_ = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.$turnir_id;
      $cnt_players = db_field($sql_,'cnt');
      
  if ($cnt_players<$cnt) {
    $_SESSION['MESSAGE_AJAX'] = 'Кількість гравців для цього етапу не може перевищувати загальну кількість гравців!';
$_SESSION['CONTENT_AJAX'] = $cnt_players;
  }  else
  {
    $_SESSION['MESSAGE_AJAX']='';
    $_SESSION['CONTENT_AJAX']='777';
  } 


?>