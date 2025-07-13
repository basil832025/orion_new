<?php
$type_id = poste('type_id');
    $type_id = !empty($type_id) ? $type_id : 1;
    
  $id = poste('id');
  $form = poste('form');
  //s($_POST);

 /*добавляем или изминяем игрока*/
 

//'player_id='.$form['player_id'].
$where_player = 'phone="'.$form['phone'].'", prim="'.$form['prim'].'"';
if (!empty($form['new_name'])) $where_player.=',name="'.$form['new_name'].'"';
    if (!empty($form['acc']) ) 
  { 
   db_query('Update '.T_PLAYERS.' SET '.$where_player .' where id='.$form['acc']);  
  } else 
    // если игрок не выбран и есть фио в поле значит это новый игрок добавляем его
  if (empty($form['acc']) && (!empty($form['new_name']))) 
  {
 //  s('INSERT INTO '.T_PLAYERS.' SET '.$wherePlayer.', dat=now() ');
    db_query('INSERT INTO '.T_PLAYERS.' SET '.$where_player.', dat=now() ');
    $form['acc']=db_insert_id();
  }
 $ost_tov = ($type_id==1 && $form['cnt_tov']>0) ? ',ost_tov='.$form['cnt_tov'] : '' ;
 $where .= 'type_tov='.$type_id.',tov="'.$form['tov'].'", date_shop="'.date_for_sql_format($form['date_shop']).'", 
 date_start="'.date_for_sql_format($form['date_start']).'",
 date_stop=DATE_ADD("'.date_for_sql_format($form['date_start']).'",INTERVAL 1 MONTH)
 
 ,summa="'.$form['summa'].'",cnt_tov="'.$form['cnt_tov'].'" '.$ost_tov.', 
 acc='.$form['acc'];
 
//если это изминения
if (!empty($id) && $id>0) 
{
 db_query('Update '.T_SHOPS.' SET '.$where .' where id='.$id);
 }
else
{
 db_query('INSERT INTO '.T_SHOPS.' SET '.$where);
}

?>