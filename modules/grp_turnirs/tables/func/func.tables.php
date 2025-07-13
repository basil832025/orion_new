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
      $sql='select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  r.turnir_id='.$turnir_id.' and pl_id_1>0 and pl_id_2>0 and set_1=0 and 
  set_2=0 and r.table_game >0';
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

    <div class="tableEtapPrim">' . $aNoFreeTables[$i]['name_etap'] . ': ' . $aNoFreeTables[$i]['etap_prim'] . '</div>
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
      <div class="tableEtapPrim">' . $aNoFreeTables[$i]['name_etap'] . ': ' . $aNoFreeTables[$i]['etap_prim'] . '</div>
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
           $JSON_ARR[$i]['diff']=  $aNoFreeTables[$i]['diff'];
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