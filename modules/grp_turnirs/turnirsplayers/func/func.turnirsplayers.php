<?php

function setParaBD($Player_1,$Player_2,$updId=0)
{
     
   $sql = 'select id_reiting, reiting_ukraine,  reiting , start_reiting , name from '.T_PLAYERS.' where id='.$Player_1;
   $aPlayer_1 = db_row($sql); 
      $sql = 'select id_reiting, reiting_ukraine,  reiting , start_reiting ,   name from '.T_PLAYERS.' where id='.$Player_2;
   $aPlayer_2 = db_row($sql);
   if ($updId>0) 
   {
     // поменяем рейтинг для 1 чувака
  if (!empty($aPlayer_1['id_reiting'])) 
    {
     $aPlayerLigas =  get_ligs_player($aPlayer_1['id_reiting']);
     if (!empty($aPlayerLigas['ranking']))
     {
     $aPlayer_1['reiting_ukraine']= $aPlayerLigas['ranking'];  
     $wherePlayer ='reiting_ukraine="'. $aPlayerLigas['ranking'] .'"';
     db_query('Update '.T_PLAYERS.' SET '.$wherePlayer .' where id='.$Player_1);  
     }
    } 
      // поменяем рейтинг для 2 чувака
  if (!empty($aPlayer_2['id_reiting'])) 
    {
     $aPlayerLigas =  get_ligs_player($aPlayer_2['id_reiting']);
     if (!empty($aPlayerLigas['ranking']))
     {
        $aPlayer_2['reiting_ukraine']= $aPlayerLigas['ranking'];
        $wherePlayer ='reiting_ukraine="'. $aPlayerLigas['ranking'] .'"';
     db_query('Update '.T_PLAYERS.' SET '.$wherePlayer .' where id='.$Player_2);  
     }
    } 
   }
   
   $name = $aPlayer_1['name'].'-'.$aPlayer_2['name'];
   $where = 'ispara=1, player_id_1='.$Player_1.', player_id_2='.$Player_2.', name="'.$name.'",
   reiting_ukraine='.($aPlayer_1['reiting_ukraine']+$aPlayer_2['reiting_ukraine']).',
   reiting='.($aPlayer_1['reiting']+$aPlayer_2['reiting']).',
   start_reiting='.($aPlayer_1['start_reiting']+$aPlayer_2['start_reiting']).'
   ' ;
   
    if ($updId>0)
    {
       $sql = 'update '.T_PLAYERS.' set '.$where.' where id='.$updId;
   db_query($sql); 
  // s($sql);
       $player_id = $updId; 
    }
    else
    {
         // добавляем новую пару
   $sql = 'insert into '.T_PLAYERS.' set '.$where.', dat=now() ';
   db_query($sql);
  // s($sql);
   $player_id=db_insert_id();       
    }
  
   return   $player_id; 
}

function setPlayerInsorUpd($turnir_id,$form_new,$syf='')
{
    
    $form['id_reiting']=$form_new['id_reiting'.$syf];
    $form['player_id']=$form_new['player_id'.$syf];
    $form['god_rogd']=$form_new['god_rogd'.$syf];
    $form['reiting_ukraine']=$form_new['reiting_ukraine'.$syf];
    $form['start_reiting']=$form_new['start_reiting'.$syf];
    $form['phone']=$form_new['phone'.$syf];
    $form['city']=$form_new['city'.$syf];
    $form['sex']=$form_new['sex'.$syf];
  //  $form['sex']=$form_new['sex'.$syf];
    $form['name_ligas']=$form_new['name_ligas'.$syf];
    $form['prim']=$form_new['prim'.$syf];
    $form['new_name']=$form_new['new_name'.$syf];
  //  $form['grp']=$form_new['grp'.$syf];

    $id_reiting='';
$aPlayer= array();
if (!empty($form['id_reiting'])) 
   $id_reiting= $form['id_reiting'];
   else if (!empty($form['player_id'])) {
   // проверем если id украины узнать текущий рейтинг Украины
    $sql = 'select id_reiting from '.T_PLAYERS.' where id='.$form['player_id'];
    $id_reiting = db_field($sql,'id_reiting');
}
// если есть ID LIGAS то считаем инфу по чуваку и его текущий рейтинг SELECT * FROM bs_players WHERE COALESCE(id_reiting,'')<>''
  if (!empty($id_reiting)) 
    {
     $aPlayer =  get_ligs_player($id_reiting);
    }
 // s($form);
 $wherePlayer = 'id_reiting="'.$id_reiting.'",god_rogd="'.
 (!empty($aPlayer) ? $aPlayer['birthyear'] : $form['god_rogd']).'",
reiting_ukraine="'.(!empty($aPlayer) ? $aPlayer['ranking'] : $form['reiting_ukraine']  ).
'",phone="'.$form['phone'].'",
is_opl_reiting="'.(!empty($aPlayer['expire']) ? 1 : 0  )   .'",
city="'.(!empty($aPlayer) ? $aPlayer['city'] : $form['city']).'",sex="'.(!empty($aPlayer) ? $aPlayer['sex'] : $form['sex']).
'",name_ligas="'. (!empty($aPlayer) ? $aPlayer['fio'] : $form['name_ligas']  ).'",prim="'.$form['prim'].'",
ligas_photo="'.(!empty($aPlayer['image']) ? $aPlayer['image'] : ''  ).'"
';
    
    if (!empty($form['new_name'])) $wherePlayer.=',name="'.$form['new_name'].'"';
    
    if (!empty($form['player_id']) ) 
  {
      db_query('Update '.T_PLAYERS.' SET '.$wherePlayer .' where id='.$form['player_id']);
  //    s($wherePlayer);
  } else 
    // если игрок не выбран и есть фио в поле значит это новый игрок добавляем его
  if (empty($form['player_id']) && (!empty($form['new_name']))) 
  {
    //  $wherePlayer =
      $club = !empty($_SESSION['gt']['club']) ? $_SESSION['gt']['club'] : 0;
      $city = !empty($_SESSION['gt']['city']) ? $_SESSION['gt']['city'] : 0;
      $start1 = empty($form['start_reiting']) ? 0 :$form['start_reiting'];
      $reiting_ukraine1 =  !empty($aPlayer['ranking']) && $aPlayer['ranking']>0  ? $aPlayer['ranking']*10 : 0;
      $start1 = $reiting_ukraine1>0 ? $reiting_ukraine1 : $start1;
    //  $wherePlayer .= ',start_reiting="'.$start1.'",reiting="'.$start1.'",club='.$club.',city_def='.$city;
      $wherePlayer .= ',start_reiting="'.$start1.'",club='.$club.',city_def='.$city;
      //  s('INSERT INTO '.T_PLAYERS.' SET '.$wherePlayer.', dat=now() ');
    db_query('INSERT INTO '.T_PLAYERS.' SET '.$wherePlayer.', dat=now() ');
    $form['player_id']=db_insert_id();
  }
return $form['player_id'];
}

 function get_ligs_player($PlayId)
    {
      //  s($PlayId);
      $url="https://ligas.io/api/organizations/uttf/users/".$PlayId;
$json = file_get_contents($url);
$data = json_decode($json, TRUE);
s('$data');
s($data);
$aPlayer=array();
foreach($data['fields'] as $val)
{
  //  print_r($val['key']);
  if (isset($val['value'])){
  if ($val['key']=='expire')  $val['value'] =substr($val['value'],0,10);
   $aPlayer[$val['key']]= $val['value'];
   }
}
//s('$aPlayer');
//s($aPlayer);
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
  /*    function updateStatisticPlayer($PlayId,$aPlayer)
    {
  //  $name_ligas = empty($Player['name_ligas']) ? $aPlayer['fio'] 
//s($aPlayer);


    db_query('UPDATE '.T_PLAYERS.' SET 
    name_ligas="'.$aPlayer['fio'].'",
    god_rogd="'.$aPlayer['birthyear'].'",
    city="'.$aPlayer['city'].'",
    sex="'.$aPlayer['sex'].'",
    reiting_ukraine="'.$aPlayer['ranking'].'",
    is_opl_reiting='.(!empty($aPlayer['expire']) ? 1 : 0  )   .'
     where id='.$PlayId);   
    }*/

?>