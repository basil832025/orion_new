<?php

   // $this->module= SystemClass::getModule();
  $turnir_id = poste('turnir_id');
  $id = poste('id');
  $form = poste('form');
 // s($form);
$form = is_array($form) ? $form : [];
  $no_send= !empty($form['no_send']) ? 1 : 0;
  $break_1= !empty($form['break_1']) ? 1 : 0;
  $break_2= !empty($form['break_2']) ? 1 : 0;
$set1 = isset($form['set_1']) ? $form['set_1'] : '';
$set2 = isset($form['set_2']) ? $form['set_2'] : '';
$set1_norm = ($set1 === 'W') ? 3 : (($set1 === 'L') ? 0 : ((trim((string)$set1) === '') ? 0 : (int)$set1));
$set2_norm = ($set2 === 'W') ? 3 : (($set2 === 'L') ? 0 : ((trim((string)$set2) === '') ? 0 : (int)$set2));
$diff1 =0 ;
$diff2 = 0;
 // s($_POST);
$sql = 'select id,date_create,dat from ' . T_TURNIRS . ' r WHERE  id=' . $turnir_id;
// s($sql);
$aTurnirs = db_row($sql);
   // Надем рейтинг начальый или текущий по игрокам
   $sql1 = 'select * from '.T_PLAYERS .' where id='.$form['pl_id_1'];
   $sql2 = 'select * from '.T_PLAYERS .' where id='.$form['pl_id_2'];
    $aPlayer1 =  db_row($sql1);
    $aPlayer2 =  db_row($sql2);
     
     // ищем рейтинг конечный последнего турнира для 1 игрока
      $sql_reit = 'SELECT  id,end_reiting FROM `'.T_TURNIR_PLAYERS.'` where turnir_id<'.$turnir_id.' and player_id='.$form['pl_id_1'].'  order by turnir_id desc limit 1;';
      $aReit = db_row($sql_reit);
      $reiting1 = !empty($aReit['end_reiting']) ? $aReit['end_reiting'] : '';
          
//$reiting1 =$aPlayer1['reiting'];
$start1 =$aPlayer1['start_reiting'];

$reiting1 = (!empty($reiting1) && $reiting1>0) ? $reiting1 : $start1;

//$reiting2 =$aPlayer2['reiting'];
// ищем рейтинг конечный последнего турнира для 1 игрока
      $sql_reit = 'SELECT  id,end_reiting FROM `'.T_TURNIR_PLAYERS.'` where turnir_id<'.$turnir_id.' and player_id='.$form['pl_id_2'].'  order by turnir_id desc limit 1;';
  //    s($sql_reit);       
      $aReit = db_row($sql_reit);
      $reiting2  = !empty($aReit['end_reiting']) ?$aReit['end_reiting'] : '';

$start2 =$aPlayer2['start_reiting'];
$reiting2 = (!empty($reiting2)  && $reiting2>0) ? $reiting2 : $start2;

// begin если не было отмен  то считаем рейтинг
  if ($break_1==0 && $break_2==0) { 

// если победил 1 игрок
if ($set1_norm > $set2_norm) {
    // проверяем не больше ли ретинг 100
    if ($reiting1-$reiting2<100) {
        $diff1 = (100-($reiting1-$reiting2))/WIN_KOEF;
        if (strtotime($aTurnirs['dat'])>=strtotime('01.06.2024')){
            $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF_NEW;
          } else{
            $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF;

        }


      //  $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF;
    }
}

// если победил 2 игрок
if ($set2_norm > $set1_norm) {
    // проверяем не больше ли ретинг 100
    if ($reiting2-$reiting1<100) {
        $diff2 = (100-($reiting2-$reiting1))/WIN_KOEF;

        if (strtotime($aTurnirs['dat'])>=strtotime('01.06.2024')){
            $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF_NEW;
          } else{
            $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF;

        }

      //  $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF;
    }
}
} // end если не было отмен

$set1 = ($set1 === '') ? 0 : $set1;
$set2 = ($set2 === '') ? 0 : $set2;
$end_game = ''; $table='';

// Проверяем, является ли это командной игрой
$is_team_game = false;
if (!empty($id) && $id > 0) {
    $game_info = db_row('SELECT match_id, pair_number FROM '.T_REITING.' WHERE id='.$id);
    // Командная игра: есть match_id и pair_number = 0 или NULL
    if (!empty($game_info['match_id']) && (empty($game_info['pair_number']) || $game_info['pair_number'] == 0)) {
        $is_team_game = true;
    }
}

if ($is_team_game) {
    // Для командных игр игра завершается только когда одна из команд имеет 3 победы
    $set1_int = $set1_norm;
    $set2_int = $set2_norm;
    if (($set1_int == 3 || $set2_int == 3) || ($break_1>0 or $break_2>0)) {
        $end_game = date('H:i:s'); 
        $table= ', table_game=0';
    }
} else {
    // Для обычных игр - старая логика (любой счет > 0 означает завершение)
    if (($set1_norm > 0 or $set2_norm > 0) || ($break_1>0 or $break_2>0)) {
        $end_game = date('H:i:s'); 
        $table= ', table_game=0';
    }
}

$where = 'pl_id_1='.$form['pl_id_1'].',  pl_id_2='.$form['pl_id_2'].',turnir_id='.$turnir_id.', rt_id_1_beg='.$reiting1.', 
rt_id_2_beg='.$reiting2.',diff_1='.$diff1.', diff_2='.$diff2.',set_1="'.$set1.'",set_2="'.$set2.'",no_send='.$no_send.',
break_1='.$break_1.', break_2='.$break_2.', end_game= "'.$end_game.'"'.$table;
//s('Update '.T_REITING.' SET '.$where .' where id='.$id);
$where_log = 'pl_id_1='.$form['pl_id_1'].',  pl_id_2='.$form['pl_id_2'].',turnir_id='.$turnir_id.',set_1="'.$set1_norm.'",set_2="'.$set2_norm.'",
break_1='.$break_1.', break_2='.$break_2.', end_game= "'.$end_game.'"'.$table;
if (!empty($id) && $id>0)
{

    db_query('Update '.T_REITING.' SET '.$where .' where id='.$id);
    // пропишем рейтинг онлайн



    $sql = 'select start_game from '.T_REITING.' where id='.$id;
    $start_game = db_field($sql,'start_game');
    write_log_reiting('reiting_save_upd',$where_log,'update',$id);
} 
    
else
{
    write_log_reiting('reiting_save_ins',$where_log,'insert',0);
    db_query('INSERT INTO '.T_REITING.' SET '.$where);

}

// Запускаем триггер пересчета после сохранения (на случай, если edit_ok не был вызван)
if (file_exists('modules/grp_turnirs/reiting/triger/after.edit_ok.php')) {
    include_once 'modules/grp_turnirs/reiting/triger/after.edit_ok.php';
}


if (SystemClass::getIsAjax()==2) 
{
    $start_game = !empty($start_game) ? $start_game : '';
   $_SESSION['MESSAGE_AJAX']='';
   $_SESSION['JAVA_SCRIPT']='$javaFinishTime="'.$end_game.'"; '.'$javaStartTime="'.$start_game.'";';
    $_SESSION['CONTENT_AJAX']='OK';
 //   s('popali_ajax2'); 
}


 /*
   [pl_id_1] => 1
            [set_1] => 3
            [set_2] => 1
            [pl_id_2] => 3
 */

?>    
