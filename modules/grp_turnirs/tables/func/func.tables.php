<?php
function write_log_reiting($script_tochka,$where,$oper,$id_game=0)
{
    $sql = 'insert into bs_log_reitings set 
login_name="'.$_SESSION['gt']['user_login'].'",
dat_oper=now(),
script_tochka="'.$script_tochka.'",
oper="'.$oper.'",
id_game="'.$id_game.'",
'.$where;
    db_query($sql);
}
function getTablesAll($tables_cnt,$turnir_id,$dat,$jsonTrue=false,$jsonGame= [],$selected_tables_str = '')
{
      $is_team_league = 0;
      $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'league_id');
      if (!empty($league_id)) {
          $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.(int)$league_id, 'is_team_league');
      }
      $team_name_cache = array();
      $lineup_team_cache = array();
      $lineup_player_team_cache = array();
      $get_team_name = function($team_id) use (&$team_name_cache) {
          $team_id = (int)$team_id;
          if ($team_id <= 0) {
              return '';
          }
          if (array_key_exists($team_id, $team_name_cache)) {
              return $team_name_cache[$team_id];
          }
          $name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.(int)$team_id, 'name');
          $team_name_cache[$team_id] = !empty($name) ? $name : '';
          return $team_name_cache[$team_id];
      };
      $get_lineup_team_names = function($match_id, $etap_id) use (&$lineup_team_cache, $get_team_name) {
          $match_id = trim((string)$match_id);
          $etap_id = (int)$etap_id;
          if (empty($match_id) || $etap_id <= 0) {
              return array();
          }
          $cache_key = $match_id.'|'.$etap_id;
          if (array_key_exists($cache_key, $lineup_team_cache)) {
              return $lineup_team_cache[$cache_key];
          }
          $team_ids = db_list('SELECT DISTINCT team_id FROM `bs_team_lineups` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.$etap_id.' ORDER BY team_id');
          $names = array();
          if (!empty($team_ids)) {
              foreach ($team_ids as $row) {
                  if (!empty($row['team_id'])) {
                      $team_name = $get_team_name($row['team_id']);
                      if (!empty($team_name)) {
                          $names[] = $team_name;
                      }
                  }
              }
          }
          $lineup_team_cache[$cache_key] = $names;
          return $names;
      };
      $get_player_team_name = function($match_id, $etap_id, $player_id) use (&$lineup_player_team_cache, $get_team_name) {
          $match_id = trim((string)$match_id);
          $etap_id = (int)$etap_id;
          $player_id = (int)$player_id;
          if (empty($match_id) || $etap_id <= 0 || $player_id <= 0) {
              return '';
          }
          $cache_key = $match_id.'|'.$etap_id.'|'.$player_id;
          if (array_key_exists($cache_key, $lineup_player_team_cache)) {
              return $lineup_player_team_cache[$cache_key];
          }
          $team_id = db_field('SELECT team_id FROM `bs_team_lineups` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.$etap_id.' AND player_id='.$player_id.' LIMIT 1', 'team_id');
          if (empty($team_id)) {
              $team_id = db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$player_id.' LIMIT 1', 'team_id');
          }
          $team_name = !empty($team_id) ? $get_team_name($team_id) : '';
          $lineup_player_team_cache[$cache_key] = $team_name;
          return $team_name;
      };
      // Исключаем командные игры (где оба игрока имеют is_team = 1)
      // Для столов должны показываться только игры между отдельными игроками
      $sql='select id, r.pl_id_1, r.pl_id_2,
        (select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game, r.match_id, r.team_a_id, r.team_b_id, r.etap_id,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  
  WHERE r.turnir_id='.$turnir_id.' 
  AND r.pl_id_1>0 
  AND r.pl_id_2>0 
  AND r.set_1=0 
  AND r.set_2=0 
  AND r.table_game >0
  AND NOT EXISTS (
    -- Исключаем командные игры: где оба игрока имеют is_team = 1
    SELECT 1 FROM bs_players p1, bs_players p2
    WHERE p1.id = r.pl_id_1 AND p2.id = r.pl_id_2
    AND p1.is_team = 1 AND p2.is_team = 1
  )';
 //s($sql);
   $aResults = db_list($sql);   
   $time1 = new DateTime('NOW'); // это время "сейчас" (как целое число)
    if (!empty($selected_tables_str)) {
        $selected_tables = array_map('intval', explode(',', $selected_tables_str));
    } else {
        // если selected_tables не задан — показываем всё от 1 до $tables_cnt
        $selected_tables = range(1, $tables_cnt);
    }
//добавим массив где ключи будут занятыми столами
$aNoFreeTables = array();
if (!empty($aResults))
 foreach($aResults as $aTable)
 {  
    $time2 =  new DateTime($dat.' '.$aTable['start_game']); // а это время в недавнем прошлом

$diff= DateIntervalToSec($time1,$time2);


   $aTable['diff'] = $diff; 
    if (!empty($is_team_league)) {
        $team_a_id = !empty($aTable['team_a_id']) ? (int)$aTable['team_a_id'] : 0;
        $team_b_id = !empty($aTable['team_b_id']) ? (int)$aTable['team_b_id'] : 0;
        if (($team_a_id <= 0 || $team_b_id <= 0) && !empty($aTable['match_id'])) {
            $parts = explode('_', $aTable['match_id']);
            if (count($parts) >= 4) {
                $team_a_id = (int)$parts[2];
                $team_b_id = (int)$parts[3];
            }
        }
        $team_a_name = $get_team_name($team_a_id);
        $team_b_name = $get_team_name($team_b_id);
        $stage_name = !empty($aTable['name_etap']) ? $aTable['name_etap'] : '';
        if (empty($team_a_name) || empty($team_b_name)) {
            $lineup_names = $get_lineup_team_names($aTable['match_id'], $aTable['etap_id']);
            if (count($lineup_names) >= 2) {
                $team_a_name = $lineup_names[0];
                $team_b_name = $lineup_names[1];
            }
        }
        if (!empty($team_a_name) && !empty($team_b_name)) {
            $aTable['stage_vs'] = !empty($stage_name) ? $stage_name.' :: '.$team_a_name.' vs '.$team_b_name : $team_a_name.' vs '.$team_b_name;
        }
    }
    $player_team_1 = $get_player_team_name($aTable['match_id'], $aTable['etap_id'], $aTable['pl_id_1']);
    if (!empty($player_team_1)) {
        $aTable['name1'] = $aTable['name1'].' (<span style="color:#1e6bd6;">'.$player_team_1.'</span>)';
    }
    $player_team_2 = $get_player_team_name($aTable['match_id'], $aTable['etap_id'], $aTable['pl_id_2']);
    if (!empty($player_team_2)) {
        $aTable['name2'] = $aTable['name2'].' (<span style="color:#1e6bd6;">'.$player_team_2.'</span>)';
    }
    $aNoFreeTables[$aTable['table_game']] = $aTable;   
 } 
//s($aNoFreeTables);
/*
  <style>
    @import url("css/tables.css?ver=1.6");
    </style>
*/
   $content = '<div class="container-fluid ">
    <div class="mar-center_main">
    <div class="mar-center">
    ';
   $JSON_ARR = [];
if ($tables_cnt>0) 
{
 // for ($i = 1; $i <= $tables_cnt; $i++)
    foreach ($selected_tables as $i)
    {   $post_string='';$post_string_val='';
        $class= 'class="tableBig_mini"';
        if ($_SESSION['gt']['user_rule']<10)
        {
            $class=   'class="tableBig_mini tableBig"' ;
         //   $JSON_ARR[$i]['classTableBig']= 'tableBig';

        }else
            $JSON_ARR[$i]['classTableBig']= '';
        if ($_SESSION['is_mobile']) {
            if (!empty($aNoFreeTables[$i])) {

                if ($_SESSION['gt']['user_rule'] < 10) {
                    $post_string_val = '&turnir_id=' . $turnir_id . '&table_id=' . $i;
                    $post_string = 'post_string="' . $post_string_val . '"';

                }
                //    $JSON_ARR[$i]['post_string']= $post_string_val;

                $content_txt = '
             
                

                <div ' . $class . ' id="tableBig_' . $i . '" ' . $post_string . ' newgame="' . $aNoFreeTables[$i]['id'] . '"  >
<div class="numTable">' . $i . '</div>
  <div class="table_table_mini bor_red " ><img src="../img/table_mini.png" width="94px" height="124px">  </div>
  <div class="tableMob_info_mini">
      <div class="player1">' . $aNoFreeTables[$i]['name1'] . '</div>
     <div class="player2">' . $aNoFreeTables[$i]['name2'] . '</div>

    <div class="tableEtapPrim">' . (!empty($aNoFreeTables[$i]['stage_vs']) ? $aNoFreeTables[$i]['stage_vs'] : $aNoFreeTables[$i]['name_etap'] . ': ' . $aNoFreeTables[$i]['etap_prim']) . '</div>
     <div class="startTime">Старт: <span class="b600">' . $aNoFreeTables[$i]['start_game'] . '</span></div>
       <div class="workTimeName"> Йде матч: </div> 
         <div class="workTime"> <span  class="watchTable"  id="Table_' . $i . '" start_timer="' . $aNoFreeTables[$i]['diff'] . '"></span></div>
 </div>
 

                </div>';
            } else {
                $post_string = ($_SESSION['gt']['user_rule'] < 10) ? 'post_string="&turnir_id=' . $turnir_id . '&table_id=' . $i . '" ' : '';
                $content_txt = '   <div ' . $class . ' id="tableBig_' . $i . '" ' . $post_string . ' newgame="0">

<div class="numTable">' . $i . '</div>

  <div class="table_table_mini bor_blue" >
    <img src="../img/table_mini.png" width="94px" height="124px">
  </div>
  <div class="tableMob_info_mini">
   <div class="player1">&nbsp;</div>
     <div class="player2">&nbsp;</div>
        <div class="tableEtapPrim">&nbsp;</div>
         <div class="startTime">&nbsp;</div>
 <div class="workTimeName">&nbsp;</div>
   <div class="workTime"> <span  class="watchTable"  id="Table_' . $i . '" start_timer="-1">&nbsp;</span></div> 
     </div>

   </div>'; /*<div class="player1"></div>
     <div class="player2"></div>
     <div class="tableEtapPrim"></div>
         <div class="startTime"></div>
 <div class="workTimeName"></div>
   <div class="workTime"> <span  class="watchTable"  id="Table_' . $i . '" start_timer="-1"></span></div>
 */
            }
        }else {
            if (!empty($aNoFreeTables[$i])) {

                if ($_SESSION['gt']['user_rule'] < 10) {
                    $post_string_val = '&turnir_id=' . $turnir_id . '&table_id=' . $i;
                    $post_string = 'post_string="' . $post_string_val . '"';

                }
                //    $JSON_ARR[$i]['post_string']= $post_string_val;
                $cnLetter1 = mb_strlen ($aNoFreeTables[$i]['name1']);
                if ($cnLetter1>18) {
                    $aNoFreeTables[$i]['name1'] = str_replace(' ','<br>',$aNoFreeTables[$i]['name1']);
                }
                $cnLetter2 = mb_strlen ($aNoFreeTables[$i]['name2']);
                if ($cnLetter2>18) {
                    $aNoFreeTables[$i]['name2'] = str_replace(' ','<br>',$aNoFreeTables[$i]['name2']);
                }
                $content_txt = '<div ' . $class . ' id="tableBig_' . $i . '" ' . $post_string . ' newgame="' . $aNoFreeTables[$i]['id'] . '"  >
<div class="tableBig2">
<div class="numTable">Стіл ' . $i . '</div>
  <div class="table_table_ bor_red " >
  <div class="table_table  " >
  <div class="playerMainBlock">
    <div class="player1">' . $aNoFreeTables[$i]['name1'] . '</div><div class="player2">' . $aNoFreeTables[$i]['name2'] . '</div>
    </div>
    <div class="tableMob_info">
      <div class="tableEtapPrim">' . (!empty($aNoFreeTables[$i]['stage_vs']) ? $aNoFreeTables[$i]['stage_vs'] : $aNoFreeTables[$i]['name_etap'] . ': ' . $aNoFreeTables[$i]['etap_prim']) . '</div>
     <div class="startTime">Старт: <span class="b600">' . $aNoFreeTables[$i]['start_game'] . '</span></div>
       <div class="workTimeName"> Йде матч: </div> 
         <div class="workTime"> <span  class="watchTable"  id="Table_' . $i . '" start_timer="' . $aNoFreeTables[$i]['diff'] . '"></span></div>
 
  </div>
  </div>
  </div>
  
   </div>
   </div>';
            } else {
                $post_string = ($_SESSION['gt']['user_rule'] < 10) ? 'post_string="&turnir_id=' . $turnir_id . '&table_id=' . $i . '" ' : '';
                $content_txt = '  <div ' . $class . ' id="tableBig_' . $i . '" ' . $post_string . ' newgame="0">
<div class="tableBig2">
<div class="numTable">Стіл ' . $i . '</div>
  <div class="table_table_ bor_blue" >
  <div class="table_table " >
  <div class="playerMainBlock">
     <div class="player1"></div>
     <div class="player2"></div>
     </div>
     <div class="tableMob_info">
         <div class="tableEtapPrim"></div>
         <div class="startTime"></div>
 <div class="workTimeName"></div>
   <div class="workTime"> <span  class="watchTable"  id="Table_' . $i . '" start_timer="-1"></span></div>
 
</div>
  </div>
  </div>
 </div>
  </div>';
            }
        }
        $JSON_ARR[$i]['edit']=0;
        $JSON_ARR [$i]['content']='';
        $id_game = !empty($aNoFreeTables[$i]['id']) ? $aNoFreeTables[$i]['id'] : 0;
        if (!empty($jsonGame) && !empty($jsonGame[$i-1]) && $jsonGame[$i-1]!=$id_game)
       {
            $JSON_ARR[$i]['edit']=1;
           $JSON_ARR[$i]['content']=$content_txt;
           $JSON_ARR[$i]['newgame']=$id_game;
           $JSON_ARR[$i]['diff']=  isset($aNoFreeTables[$i]['diff']) ? $aNoFreeTables[$i]['diff'] : 0;
           ;

       }
        $content .= '<div class="mainTable" id="mainTable_'.$i.'" tableBig="'.$i.'">'. $content_txt.'</div>';
    }
    $content .='</div></div></div>';
}    
if ($jsonTrue) return $JSON_ARR;   else   return $content;
}
function DateIntervalToSec($start,$end){ // as datetime object returns difference in seconds
    $diff = $end->diff($start);
   $daysInSecs = $diff->format('%r%a') * 24 * 60 * 60;
$hoursInSecs = $diff->h * 60 * 60;
$minsInSecs = $diff->i * 60;
$seconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;
    return $seconds;
}
?>
