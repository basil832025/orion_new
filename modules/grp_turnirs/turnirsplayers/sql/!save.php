<?php
 $turnir_id = poste('turnir_id');
  $id = poste('id');
  $form = poste('form');
  //s($_POST);
$is_opl_this= !empty($form['is_opl_this']) ? 1 : 0;
$is_opl_reiting= !empty($form['is_opl_reiting']) ? 1 : 0;
$break= !empty($form['break']) ? 1 : 0;
$new_player= !empty($form['new_player']) ? 1 : 0;

// если игрок снялся то нужно пройтись по всем играм отсавшимся и сделать победы
if ($break>0 && !empty($form['player_id']) && $form['player_id']>0 ) 
{
    $player = $form['player_id'];
  $sql = 'SELECT * FROM `'.T_REITING.'` where (pl_id_1='.$player.' or pl_id_2='.$player.' )
  and turnir_id='.$turnir_id.' and (set_1=0 and set_2=0);';
  $aGames = db_list($sql);
  foreach ($aGames as $k => $game)
  {
    if ($player==$game['pl_id_1']) 
    {
        db_query('update `'.T_REITING.'` SET set_1="W", set_2=3, win_player='.$game['pl_id_2'].', lose_player='.$player.' where id='.$game['id']);
    
    }
    if ($player==$game['pl_id_2']) 
    {
        db_query('update `'.T_REITING.'` SET set_1="3", set_2="W", win_player='.$game['pl_id_1'].', lose_player='.$player.' where id='.$game['id']);
    }
  }  
}
//'player_id='.$form['player_id'].
$where = 'turnir_id='.$turnir_id.', grn="'.$form['grn'].'", 
is_opl_this="'.$is_opl_this.'",
break='.$break.',new_player='.$new_player;


$id_reiting='';
$aPlayer= array();
if (!empty($form['id_reiting'])) 
   $id_reiting= $form['id_reiting'];
   else if (!empty($form['player_id'])) {
   // проверем если id украины узнать текущий рейтинг Украины
    $sql = 'select id_reiting from '.T_PLAYERS.' where id='.$form['player_id'];
    $id_reiting = db_field($sql,'id_reiting');
}
  if (!empty($id_reiting)) 
    {
     $aPlayer =  get_ligs_player($id_reiting);
    }   
$wherePlayer = 'id_reiting="'.$form['id_reiting'].'",god_rogd="'.(!empty($aPlayer) ? $aPlayer['birthyear'] : $form['god_rogd']).'",
reiting_ukraine="'.(!empty($aPlayer) ? $aPlayer['ranking'] : $form['reiting_ukraine']  ).'",start_reiting="'.$form['start_reiting'].'",
phone="'.$form['phone'].'",
is_opl_reiting="'.(!empty($aPlayer['expire']) ? 1 : 0  )   .'",
city="'.(!empty($aPlayer) ? $aPlayer['city'] : $form['city']).'",sex="'.(!empty($aPlayer) ? $aPlayer['sex'] : $form['sex']).'",name_ligas="'. (!empty($aPlayer) ? $aPlayer['fio'] : $form['name_ligas']  ).'",prim="'.$form['prim'].'"';

if (!empty($aPlayer)) {
  updateStatisticPlayer($form['player_id'],$aPlayer);  
  }
//если это изминения
if (!empty($id) && $id>0) 
{
    if (!empty($form['player_id']) ) 
  { 
    
    $id_pl=0;
    $sql ='select id from '.T_TURNIR_PLAYERS.' where player_id='.$form['player_id'].' and turnir_id='.$turnir_id;
    $id_pl = db_field($sql,'id');
    if (!empty($form['new_name'])) $wherePlayer.=',name="'.$form['new_name'].'"';
    if (!empty($id_pl))  db_query('Update '.T_PLAYERS.' SET '.$wherePlayer .' where id='.$form['player_id']);  
  } else if (!empty($form['new_name']))
  {
      $wherePlayer.=',name="'.$form['new_name'].'"';
  
    db_query('INSERT INTO '.T_PLAYERS.' SET '.$wherePlayer.', dat=now() ');
    $form['player_id']=db_insert_id();
    
   // s($form);
  }
  $where .= ' , player_id='.$form['player_id'].'';
    db_query('Update '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id);
  

}
else
{
 db_query('INSERT INTO '.T_TURNIR_PLAYERS.' SET '.$where);
}

 function get_ligs_player($PlayId)
    {
      //  s($PlayId);
      $url="https://ligas.io/api/organizations/uttf/users/".$PlayId;
$json = file_get_contents($url);
$data = json_decode($json, TRUE); 
$aPlayer=array();
foreach($data['fields'] as $val)
{
  //  print_r($val['key']);
  if (isset($val['value'])){
  if ($val['key']=='expire')  $val['value'] =substr($val['value'],0,10);
   $aPlayer[$val['key']]= $val['value'];
   }
}

$this_year =date('Y');
     if (!empty($aPlayer['expire']))
   {
     if (substr($aPlayer['expire'],0,4)==$this_year )  $aPlayer['expire'] = $this_year ; else $aPlayer['expire'] = '' ;
   }
   else
    $aPlayer['expire'] = '';

$aPlayer['fio'] = $aPlayer['surname'].' ' .$aPlayer['name'];  
$aPlayer['ranking'] = isset($aPlayer['ranking'])? $aPlayer['ranking'] : 0;
$aPlayer['sex'] = isset($aPlayer['sex'])? $aPlayer['sex'] : 'm';
    return $aPlayer;
    }
      function updateStatisticPlayer($PlayId,$aPlayer)
    {
  //  $name_ligas = empty($Player['name_ligas']) ? $aPlayer['fio'] 



    db_query('UPDATE '.T_PLAYERS.' SET 
    name_ligas="'.$aPlayer['fio'].'",
    god_rogd="'.$aPlayer['birthyear'].'",
    city="'.$aPlayer['city'].'",
    sex="'.$aPlayer['sex'].'",
    reiting_ukraine="'.$aPlayer['ranking'].'",
    is_opl_reiting='.(!empty($aPlayer['expire']) ? 1 : 0  )   .'
     where id='.$PlayId);   
    }
?>