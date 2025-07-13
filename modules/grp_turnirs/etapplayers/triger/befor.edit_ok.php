<?php
$turnir_id=poste('turnir_id');
$etap_id=poste('etap_id');
$id=poste('id');
$form=poste('form');
  if (!empty($form['player_id']) && !empty($id)) {
 $sql = 'SELECT count(*) as cnt FROM `'.T_ETAPS_PLAYER_MESTA.'` where id<>'.$id.' and player_id='.$form['player_id'].'  and etap_id='.$etap_id;
 $cn = db_field($sql,'cnt');
 if ($cn>0)  window_mess('Такий гравець вже є в списку гравців на даному етапі');
 
 }
?>