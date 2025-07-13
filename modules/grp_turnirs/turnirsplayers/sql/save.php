<?php
 $turnir_id = poste('turnir_id');
 $league_id = poste('league_id');
  $id = poste('id');
  $form = SystemClass::getAFormPost();
  // проверим парній ли это турнир
    $sql = 'select ispara from '.T_TURNIRS.' t where t.id='.$turnir_id;
    $ispara = db_field($sql,'ispara');
  //s($_POST);
$is_opl_this= !empty($form['is_opl_this']) ? 1 : 0;
$is_command_num= !empty($form['is_command_num']) ? $form['is_command_num'] : 0;
$is_opl_reiting= !empty($form['is_opl_reiting']) ? 1 : 0;
$break= !empty($form['break']) ? 1 : 0;
$new_player= !empty($form['new_player']) ? 1 : 0;
//s($form);

// замена игрока если не правильно вставили на правильного
// узнаем id старого игрока
$sql = 'select player_id from '.T_TURNIR_PLAYERS.' where id='.$id;
$old_player = db_field($sql,'player_id');
//s('$old_player='.$old_player);
if ($old_player!=$form['player_id']){
    //Поменяем по всем играм
    $sql = 'update '.T_REITING .' set pl_id_1='.$form['player_id']. ' where pl_id_1='.$old_player.' and turnir_id='.$turnir_id;
    db_query($sql);
    $sql = 'update '.T_REITING .' set pl_id_2='.$form['player_id']. ' where pl_id_2='.$old_player.' and turnir_id='.$turnir_id;
    db_query($sql);
    // поменяем по всем игрокам этапа
    $sql = 'update '.T_ETAPS_PLAYER_MESTA .' set player_id='.$form['player_id']. ' where player_id='.$old_player.' and turnir_id='.$turnir_id;
    db_query($sql);
}



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
        db_query('update `'.T_REITING.'` SET set_1="L", set_2=W, win_player='.$game['pl_id_2'].', lose_player='.$player.' where id='.$game['id']);
    
    }
    if ($player==$game['pl_id_2']) 
    {
        db_query('update `'.T_REITING.'` SET set_1="W", set_2="L", win_player='.$game['pl_id_1'].', lose_player='.$player.' where id='.$game['id']);
    }
  }  
}
//'player_id='.$form['player_id'].
$where = 'turnir_id='.$turnir_id.', grn="'.$form['grn'].'", mesto="'.$form['mesto'].'", 
is_opl_this="'.$is_opl_this.'",is_command_num="'.$is_command_num.'",
break='.$break.',new_player='.$new_player.', league_id="'.$league_id.'"';


if ($ispara)
{
    // если не выбрана пара
    if (empty($form['player_id']))
    {
   //доавбили 1 игрока  
   $player_id1  =  setPlayerInsorUpd($turnir_id,$form,'_1'); 
  // s('$player_id1='.$player_id1);
   // добавили 2 игрока
   $player_id2  =  setPlayerInsorUpd($turnir_id,$form,'_2'); 
   $sql = 'select id,player_id_1,player_id_2 from '.T_PLAYERS.'  where ispara=1 
   and  ((player_id_1='.$player_id1.' and player_id_2='.$player_id2.') or 
   (player_id_1='.$player_id2.' and player_id_2='.$player_id1.'))
    limit 1';
     $infoPara = db_row($sql);
   if (empty($infoPara['id']))  $player_id_para =  setParaBD($player_id1,$player_id2); 
   else 
   {
     $player_id_para = $infoPara['id'];
    // обновить рейтинг для пары и игроков
    setParaBD($player_id1,$player_id2,$player_id_para); 
   
   // $player_id_para_old = $player_id_para;
   }
  $where .= ' , ispara=1, player_id='.$player_id_para.', player_id_1='.$player_id1.', player_id_2='.$player_id2;
   }else
   {
     $sql = 'select player_id_1,player_id_2 from '.T_PLAYERS.'  where id='.$form['player_id'];
     $infoPara = db_row($sql);
     
     // обновить рейтинг для пары и игроков
    setParaBD($infoPara['player_id_1'],$infoPara['player_id_2'],$form['player_id']); 
   
      $where .= ' , ispara=1, player_id='.$form['player_id'].', player_id_1='.$infoPara['player_id_1'].', player_id_2='.$infoPara['player_id_2'];
 
   }
}
else
{


    $player_id  =  setPlayerInsorUpd($turnir_id,$form);

 $where .= ' , player_id='.$player_id;
 }
//если это изминения
if (!empty($id) && $id>0) 
{
 db_query('Update '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id);
 }
else
{
 db_query('INSERT INTO '.T_TURNIR_PLAYERS.' SET '.$where);
}

?>