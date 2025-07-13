<?php
//s($_POST);
$turnir_id=poste('turnir_id');
$form=poste('form');
$id=poste('id');
$id = !empty($id) ? $id : 0;
if (empty($form['player_id']) && empty($form['new_name']) && !isset($form['player_id_1']))
    window_mess('Виберіть існуючого гравця або введіть ПІБ нового гравця, щоб його створити!');

   if (!empty($form['player_id_1']) && !empty($form['player_id_2'])) {
    if ($form['player_id_2']==$form['player_id_1'])
     window_mess('Двох одинакових гравців неможна для пари добавляти');
   //  если эта пара уже добавлялалсь и есть уже на этом турнире то предупредим
     $sql = 'select id,player_id_1,player_id_2 from '.T_PLAYERS.'  where ispara=1 
   and  ((player_id_1='.$form['player_id_1'].' and player_id_2='.$form['player_id_2'].' ) 
   or (player_id_1='.$form['player_id_2'].' and player_id_2='.$form['player_id_1'].'))
   
    limit 1';
     $infoPara = db_row($sql);
   if (!empty($infoPara['id'])) 
   {
    // если такая пара есть проверим ниже по логике с одним игрокм присутсвует ли пара
    $form['player_id'] = $infoPara['id'];
   }   
     
}
  $cn =0; 
  if (!empty($form['player_id'])) {
 $sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where id<>'.$id.' and player_id='.$form['player_id'].' and turnir_id='.$turnir_id;
 $cn = db_field($sql,'cnt');
 if ($cn>0 ) 
 if (!empty($infoPara['id']))
  window_mess('Дана пара вже зареєстрована на даном турнірі');
else
  window_mess('Даний гравець вже є в списку гравців в даному турнірі');
  $no_rachet = !(empty($form['new_player'])) ? 1 : 0;
$where = '(pl_id_1='.$form['player_id'].' or  pl_id_2='.$form['player_id'].') and turnir_id='.$turnir_id;
  db_query('Update '.T_REITING.' SET no_send='.$no_rachet .' where '.$where); 
//} 
 }

 if ( (empty($form['player_id']) && isset($form['player_id_1'])) && 
 (empty($form['player_id_1']) || empty($form['player_id_2']))
 && (empty($form['new_name_1']) || empty($form['new_name_2']))
 && (empty($form['player_id_1']) || empty($form['new_name_2']))
 && (empty($form['new_name_1']) || empty($form['player_id_2']))
 
 ) {
        window_mess('Або виберіть пару зі списку аба виберіть двух гравців');
}

$sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
$aTurnir= db_row($sql);
$is_command = $aTurnir['is_command'];
if (!empty($is_command) && empty($form['is_command_num']))
    window_mess('В командному турнірі, треба вибрати до якої команди належить гравець!');
 //if (!empty($form['new_player'])){



?>