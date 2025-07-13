<?php
if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
{

    s('HAKKER_HAKKER');
    s($_POST);
    s($_SERVER['REMOTE_ADDR']);
    s($_SERVER['HTTP_USER_AGENT']);
    exit;
    return;
}
     $turnir_id=poste('turnir_id');
      $newgame=poste('newgame');
      $table_id=poste('table_id');
      $start_game = date('H:i:s');
  $sql = 'update '.T_REITING.'  set table_game='.$table_id.', start_game="'.$start_game.'" where id='.$newgame; 
//   s($sql);
   db_query($sql);
$where_log = 'table_game='.$table_id.', start_game="'.$start_game.'"';
write_log_reiting('tables_settablegame',$where_log,'update',$newgame);
   // узнаем товарищей
         $sql='select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  r.turnir_id='.$turnir_id.' and pl_id_1>0 and pl_id_2>0 and set_1=0 and 
  set_2=0 and r.table_game >0 and id='.$newgame;
 //s($sql);
   $aResults = db_row($sql);  
 
   $dat  = date('Y-m-d');
   $time1 = new DateTime('NOW'); // это время "сейчас" (как целое число)
   $time2 =  new DateTime($dat.' '.$start_game); // а это время в недавнем прошлом
   $diff= DateIntervalToSec($time1,$time2);
  $aJson['name1'] = $aResults['name1']; 
  //$aJson['name1'] = 'Брусенко Анатолий'; 
   $aJson['name2'] = $aResults['name2']; 
  // $aJson['name2'] = 'Smyk Vasyl'; 
   $aJson['start_game'] = $start_game; 
   $aJson['name_etap'] = $aResults['name_etap']; 
   $aJson['etap_prim'] = $aResults['etap_prim'];      
   $aJson['diff'] = $diff;    
   $aJson['newgame'] = $newgame;    
   
   $aJson = json_encode($aJson,JSON_UNESCAPED_UNICODE );  
  if (!empty($aJson)) {
  $_SESSION['CONTENT_AJAX'] = $aJson;
  $_SESSION['MESSAGE_AJAX']='';
  }  else
  {
    $_SESSION['MESSAGE_AJAX']='';
    $_SESSION['CONTENT_AJAX']='777';
  } 
  
  function utf8ize($data) {
    if (is_array($data))
        foreach ($data as $key => $value)
            $data[$key] = $this->utf8ize($value);

    else if(is_object($data))
        foreach ($data as $key => $value)
            $data->$key = $this->utf8ize($value);

    else
        return utf8_encode($data);

    return $data;
}
