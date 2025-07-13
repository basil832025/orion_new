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
   // s($grp);
   // s($etap_id);
   $sql = 'update '.T_REITING.'  set  start_game="0", table_game=0 where id='.$newgame; 
  // s($sql);
   db_query($sql);
$where_log = 'start_game="0", table_game=0';
write_log_reiting('tables_cancelgame',$where_log,'update',$newgame);
     $_SESSION['CONTENT_AJAX'] = 'OK';
  $_SESSION['MESSAGE_AJAX']='';
?>