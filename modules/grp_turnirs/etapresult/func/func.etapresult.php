<?php
include_once 'func.etapresult_2x_minus.php';
include_once 'func.etapresult_2x_minuska8.php';
include_once 'func.etapresult_olimp.php';



 function all_results_2xminuska ($etap_id,$turnir_id)
 {
      $aResultsNEW=array();
      //получаем результаты 
    $sql='SELECT (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_1) as name1,
                 (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_2) as name2,
                 (SELECT   p.ispara from bs_turnirs p where p.id=r.turnir_id) as ispara,
                   case when r.pl_id_1>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_1 and etap_id='.$etap_id.') else 0 end as mesto_all_1,
                 case when r.pl_id_2>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_2 and etap_id='.$etap_id.') else 0 end as mesto_all_2,

     r.* FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=2 
    order by olimp16_num;';
    $aResults = db_list($sql);
 //   s($aResults);
    // пройдемся по всем резульаьам обработаем 
    foreach ($aResults as $aRec)
    {
        if (empty($aRec['name1']))
            $aRec['name1'] = !empty($aRec['groups_pred1']) ? 'Група '.$aRec['groups_pred1']. ' місце '.$aRec['grp_num_pred1'] : '';
        if (empty($aRec['name2']))
            $aRec['name2'] = !empty($aRec['groups_pred2']) ? 'Група '.$aRec['groups_pred2']. ' місце '.$aRec['grp_num_pred2'] : '';

        $aResultsNEW[$aRec['olimp16_num']] = $aRec;
    }
    return $aResultsNEW;
 }
 function Mesta_2xminuska ($etap_id,$turnir_id)
 {
     $sql='SELECT (select  p.name from  '.T_PLAYERS.' p where p.id=r.player_id) as name,mesto_all
          FROM '.T_ETAPS_PLAYER_MESTA.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.'  and COALESCE(mesto_all,0)>0
    order by mesto_all;';
    $aResults = db_list($sql);
  
    return $aResults;
 }
 function all_results_table_comm ($etap_id,$turnir_id)
   {
     $aResultsNEW=[];
     $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where etap_id='.$etap_id;
     $playersMesta=db_list($sql);
     // в ключи масива загонем номер групи и начальний порядок
       $playersMestaNEW=[];
     foreach ($playersMesta as $elem)
     {
         $playersMestaNEW[$elem['groups']][$elem['grp_num']] = $elem;
     }
   //  s($playersMestaNEW);
      //получаем результаты 
    $sql='SELECT * FROM '.T_REITING.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $sql='SELECT  (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE  r.pl_id_1=m.player_id AND m.etap_id=r.etap_id) AS mesto1, 
 (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE r.pl_id_2=m.player_id AND m.etap_id=r.etap_id) AS mesto2,r.* 
FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $aResults = db_list($sql);
  //  s($sql);
       //s($aResults);
 // поменяем проходом местами 2 игры чтобы были первыми все
       //
       $aTemp=[];
    foreach ($aResults as $aRec){
        if ($aRec['pl_num_grp1']<$aRec['pl_num_grp2']){
            $aTemp[]=$aRec;
        }else{
            $aTempRec=$aRec;
            $aRec['pl_num_grp2']=$aTempRec['pl_num_grp1'];
            $aRec['pl_num_grp1']=$aTempRec['pl_num_grp2'];
            $aRec['pl_id_1']=$aTempRec['pl_id_2'];
            $aRec['pl_id_2']=$aTempRec['pl_id_1'];
            $aRec['set_1']=$aTempRec['set_2'];
            $aRec['set_2']=$aTempRec['set_1'];
            $aRec['win_player']=$aTempRec['lose_player'];
            $aRec['lose_player']=$aTempRec['win_player'];
            $aRec['break_1']=$aTempRec['break_2'];
            $aRec['break_2']=$aTempRec['break_1'];
            $aTemp[]=$aRec;
        }
    }
    $aResults=$aTemp;
       // пройдемся по всем резульаьам обработаем
    foreach ($aResults as $aRec)
    {
      /*  if (!empty($aRec['mesto1']))
        {
            $pl_num_grp1 = $aRec['mesto1'];
            $pl_num_grp2 = $aRec['mesto2'];
        //    s($aRec);
        //    s('$pl_num_grp1='.$pl_num_grp1. ' $pl_num_grp2='.$pl_num_grp2);
        }else*/
        {
            $pl_num_grp1 = $aRec['pl_num_grp1'];
            $pl_num_grp2 = $aRec['pl_num_grp2'];
        }

        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['first_res'] = $aRec['set_1'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['second_res'] = $aRec['set_2'];
       $set_1 =  $aRec['set_1']=='W' ? 3 : $aRec['set_1'];
       $set_2 =  $aRec['set_2']=='W' ? 3 : $aRec['set_2'];
       $table_active = $aRec['table_game']>0 ? '<div class="t-grid-team_table1">T'.$aRec['table_game'].'</div>' : '';
        if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){
           
            if ($set_1>0 && $set_1>$set_2 ){
                $ochko = 1 ;
                $ochko2 = 0 ;
                $colorClass='green_color';
            }else
            {
              $ochko = 0 ;
              $ochko2 = 1 ;
                $colorClass='coral_color';
            }
            if ($aRec['set_1']=='W' ){
                $ochko = 1 ;
                $ochko2 = 0 ;
                $colorClass='green_color';
            }else if ($aRec['set_1']=='L' )
            {
              $ochko = 0;
              $ochko2 = 1;
                $colorClass='coral_color';
            }
            
        //
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['ochko'] = $ochko;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['ochko'] = $ochko2;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] =
                '<div><span class="'.$colorClass.'">'.$aRec['set_1'] .':'. $aRec['set_2'].'</span></div>
            ';//<div class="left_ochko">'.$ochko.'</div><div class="right_ochko">'.$ochko2.'</div>
        } else
        {
                if ($aRec['table_game']>0 )
                  $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = 't-grid-team_table';
                else
                    $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
                $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = $table_active;

        }


    }
     //  s($aResultsNEW);
    return $aResultsNEW;

   } 
 function all_results_table ($etap_id,$turnir_id)
   {
     $aResultsNEW=[];
     $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where etap_id='.$etap_id;
     $playersMesta=db_list($sql);
     // в ключи масива загонем номер групи и начальний порядок
       $playersMestaNEW=[];
     foreach ($playersMesta as $elem)
     {
         $playersMestaNEW[$elem['groups']][$elem['grp_num']] = $elem;
     }
   //  s($playersMestaNEW);
      //получаем результаты
    $sql='SELECT * FROM '.T_REITING.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $sql='SELECT  (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE  r.pl_id_1=m.player_id AND m.etap_id=r.etap_id) AS mesto1, 
 (SELECT m.grp_mesto FROM bs_etaps_players_mesta m WHERE r.pl_id_2=m.player_id AND m.etap_id=r.etap_id) AS mesto2,r.* 
FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $aResults = db_list($sql);
 //   s($sql);
       //s($aResults);
    // пройдемся по всем резульаьам обработаем
    foreach ($aResults as $aRec)
    {
        if (!empty($aRec['mesto1']))
        {
            $pl_num_grp1 = $aRec['mesto1'];
            $pl_num_grp2 = $aRec['mesto2'];
        //    s($aRec);
        //    s('$pl_num_grp1='.$pl_num_grp1. ' $pl_num_grp2='.$pl_num_grp2);
        }else
        {
            $pl_num_grp1 = $aRec['pl_num_grp1'];
            $pl_num_grp2 = $aRec['pl_num_grp2'];
        }

        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['first_res'] = $aRec['set_1'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['second_res'] = $aRec['set_2'];
       $set_1 =  $aRec['set_1']=='W' ? 3 : $aRec['set_1'];
       $set_2 =  $aRec['set_2']=='W' ? 3 : $aRec['set_2'];
       $table_active = $aRec['table_game']>0 ? '<div class="t-grid-team_table1">T'.$aRec['table_game'].'</div>' : '';
        if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){

            if ($set_1>0 && $set_1>$set_2 ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else
            {
              $ochko = 1 ;
                $colorClass='coral_color';
            }
            if ($aRec['set_1']=='W' ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else if ($aRec['set_1']=='L' )
            {
              $ochko = 0;
                $colorClass='coral_color';
            }

        //
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = '<span class="'.$colorClass.'">'.$aRec['set_1'] .':'. $aRec['set_2'].'</span><br />'.$ochko;
        } else
        {
                if ($aRec['table_game']>0 )
                  $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = 't-grid-team_table';
                else
                    $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
                $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = $table_active;

        }

    // теперь перевоачиваем для нижней части таблицы
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['first_res'] = $aRec['set_2'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['second_res'] = $aRec['set_1'];
         $set_1 =  $aRec['set_1']=='W' ? 3 : $aRec['set_1'];
           $set_2 =  $aRec['set_2']=='W' ? 3 : $aRec['set_2'];
    if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){


         if ($set_1>0 && $set_1>$set_2 ){
                $ochko = 1 ;
                $colorClass='coral_color';
            }else
            {
              $ochko = 2 ;
                $colorClass='green_color';
            }
            if ($aRec['set_2']=='W' ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else if ($aRec['set_2']=='L' )
            {
              $ochko = 0;
                $colorClass='coral_color';
            }
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] = '<span class="'.$colorClass.'">'.$aRec['set_2'] .':'. $aRec['set_1'].'</span><br />'.$ochko;
        }else {


        if ($aRec['table_game']>0 )
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = 't-grid-team_table';
        else
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';


        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] = $table_active;

    }

    }
     //  s($aResultsNEW);
    return $aResultsNEW;

   }
   // пройтись по таблицам всем
  function all_tables_comm($etap_id,$turnir_id,$aResults)
  {
   
  
  /// old старый код  
 /*   $sql = 'SELECT case when reiting>0 then reiting else start_reiting end as beg_reit,reiting_ukraine,tp.id as turn_id, 
p.name,tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp,'.T_PLAYERS.' p where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and p.id=tp.player_id 
ORDER BY tp.groups,tp.grp_num';
*/
$sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
tp.id as turn_id,
(SELECT COUNT(*) AS cnt_win FROM bs_reiting r where turnir_id=tp.turnir_id and etap_id=tp.etap_id and win_player=tp.player_id) as cnt_win,
tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id,is_command_num   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.'
ORDER BY tp.is_command_num,tp.grp_num';
//ORDER BY tp.groups,tp.grp_num';

$aPlayers = db_list($sql);
//s($sql);
if (!empty($aPlayers)){
$aGroups = array();
$a=0;
$aTemp=array();
$aComm1=[];
$aComm2=[];
$whoCommand=0;
foreach ($aPlayers  as $k=> $player) 
{
   $player['name'] = $player['grp_mesto']==0 && $player['player_id']==0 ? 'Група '.$player['groups_pred']. ' місце '.$player['grp_num_pred'] : $player['name'];
   $player['beg_reit'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' :round($player['beg_reit'],0);
   $player['reiting_ukraine'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['reiting_ukraine'];
   $player['grp_mesto'] = $player['grp_mesto']==0 ? '' : $player['grp_mesto'];
   $player['grp_ochki'] = $player['grp_ochki']==0 ? '' : $player['grp_ochki'];
   $player['grp_win_set'] = $player['grp_win_set']==0 && $player['grp_lose_set']==0 ? '' : $player['grp_win_set'];
   $player['grp_lose_set'] =$player['grp_win_set']==0 &&  $player['grp_lose_set']==0 ? '' : $player['grp_lose_set'];
 //  s($player['groups']);
  if ($player['is_command_num']>0 && $player['is_command_num']<>$a ) {
    $aGroups[$player['is_command_num']-1] =$aTemp;
 //   s('a='.$a);
    if ($a==1) $aComm1=$aTemp;
    $aTemp=array();
  }
  if ($player['is_command_num']>0)
  {

      $aTemp[$player['grp_num']]=$player;
      $a=$player['is_command_num'];
  }  
} 

    $aGroups[$player['is_command_num']] =$aTemp;
    if ($a==2) $aComm2=$aTemp;
    $aTemp=array();
  //  s($aComm1);
  //  s($aComm2);
 //s($aGroups);
    /*<style>
         @import url("css/print.css?ver=1");
         </style> */
    $Tables_content='  <div class="print_group">
         <div class="Section1"> 
           
 
  <div class="container-fluid">

  
  ';
    $str_add_player='';
  $sql ='SELECT 
count(*) as cnt  
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  groups=0 and etap_id='.$etap_id.' and turnir_id='.$turnir_id;

$Cnt_new = db_field($sql,'cnt');
$minGrp=0;
 // здесь определимся какие группы меньше всего
  foreach ($aGroups as $grp => $aPlay)
 {
    $cnGrp =count($aPlay);
    if ($minGrp==0 || $cnGrp<$minGrp) $minGrp=$cnGrp;
 }  
   
 //foreach ($aGroups as $grp => $aPlay)

   // $cnGrp =count($aPlay);
   // s('$grp='.$grp);
   // s($aResults);
  //  if ($cnGrp==3) $ConTable =  table3($aPlay,$aResults[$grp]);
 //   if ($cnGrp==4) $ConTable =  table4($aPlay,$aResults[$grp]);
 ////   if ($cnGrp==5) $ConTable =  table5($aPlay,$aResults[$grp]);
//    if ($cnGrp==6) $ConTable =  table6($aPlay,$aResults[$grp]);
    //if ($cnGrp>6)
    //    $ConTable =  ;
    
    $str_add_player='';
   /* if (!empty($Cnt_new) && $minGrp==$cnGrp)
    {
        
        $str_add_player = '<span style="width:20px;cursor: pointer;" id="per_v_rozdel" module="etaps" action="add_playergrp" post_string="&etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp.'"  return_content_bool="" blok="" class="ajax_send" field_result="player_id" field_result_name="name" wintype="1" width_="500"><img width="35px" alt="Додати" title="Додати" src="img/slug_small/add.png" border="0"></span>';
    }*/
   $Tables_content .=' <div class="row">
  <div class="col">
 '.table_comm($aComm1,$aComm2 ,$aResults[1],'  ',$turnir_id,$etap_id ) .'

  </div>
  </div>
  ';
 //}
 $Tables_content .='
  </div>
  </div>
  
</div>

  
  ';
  return $Tables_content;
  }
  }
  function all_tables($etap_id,$turnir_id,$aResults)
  {


  /// old старый код
 /*   $sql = 'SELECT case when reiting>0 then reiting else start_reiting end as beg_reit,reiting_ukraine,tp.id as turn_id,
p.name,tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp,'.T_PLAYERS.' p where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and p.id=tp.player_id
ORDER BY tp.groups,tp.grp_num';
*/
$sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
tp.id as turn_id,
tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id,is_command_num   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.'
ORDER BY tp.groups,tp.grp_mesto,tp.grp_num';
//ORDER BY tp.groups,tp.grp_num';

$aPlayers = db_list($sql);
//s($sql);
if (!empty($aPlayers)){
$aGroups = array();
$a=1;
$aTemp=array();
foreach ($aPlayers  as $k=> $player)
{
   $player['name'] = $player['grp_mesto']==0 && $player['player_id']==0 ? 'Група '.$player['groups_pred']. ' місце '.$player['grp_num_pred'] : $player['name'];
   $player['beg_reit'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' :round($player['beg_reit'],0);
   $player['reiting_ukraine'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['reiting_ukraine'];
   $player['grp_mesto'] = $player['grp_mesto']==0 ? '' : $player['grp_mesto'];
   $player['grp_ochki'] = $player['grp_ochki']==0 ? '' : $player['grp_ochki'];
   $player['grp_win_set'] = $player['grp_win_set']==0 && $player['grp_lose_set']==0 ? '' : $player['grp_win_set'];
   $player['grp_lose_set'] =$player['grp_win_set']==0 &&  $player['grp_lose_set']==0 ? '' : $player['grp_lose_set'];
 //  s($player['groups']);
  if ($player['groups']>0 && $player['groups']<>$a ) {
    $aGroups[$player['groups']-1] =$aTemp;
    $aTemp=array();
  }
  if ($player['groups']>0)
  {
      if (!empty($player['grp_mesto']))
      $aTemp[$player['grp_mesto']]=$player;
      else
      $aTemp[$player['grp_num']]=$player;
      $a=$player['groups'];
  }
}

    $aGroups[$player['groups']] =$aTemp;
    $aTemp=array();
 //s($aGroups);
    /*<style>
         @import url("css/print.css?ver=1");
         </style> */
    $Tables_content='  <div class="print_group">
         <div class="Section1"> 
           
 
  <div class="container-fluid">

  
  ';
    $str_add_player='';
  $sql ='SELECT 
count(*) as cnt  
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  groups=0 and etap_id='.$etap_id.' and turnir_id='.$turnir_id;

$Cnt_new = db_field($sql,'cnt');
$minGrp=0;
 // здесь определимся какие группы меньше всего
  foreach ($aGroups as $grp => $aPlay)
 {
    $cnGrp =count($aPlay);
    if ($minGrp==0 || $cnGrp<$minGrp) $minGrp=$cnGrp;
 }

 foreach ($aGroups as $grp => $aPlay)
 {
    $cnGrp =count($aPlay);
   // s('$grp='.$grp);
   // s($aResults);
  //  if ($cnGrp==3) $ConTable =  table3($aPlay,$aResults[$grp]);
 //   if ($cnGrp==4) $ConTable =  table4($aPlay,$aResults[$grp]);
 ////   if ($cnGrp==5) $ConTable =  table5($aPlay,$aResults[$grp]);
//    if ($cnGrp==6) $ConTable =  table6($aPlay,$aResults[$grp]);
    //if ($cnGrp>6)
    //    $ConTable =  ;

    $str_add_player='';
    if (!empty($Cnt_new) && $minGrp==$cnGrp)
    {

        $str_add_player = '<span style="width:20px;cursor: pointer;" id="per_v_rozdel" module="etaps" action="add_playergrp" post_string="&etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp. '"  return_content_bool="" blok="" class="ajax_send" field_result="player_id" field_result_name="name" wintype="1" width_="500"><img width="35px" alt="Додати" title="Додати" src="../../../../img/slug_small/add.png" border="0"></span>';
    }
   $Tables_content .=' <div class="row">
  <div class="col">
 '.table($aPlay,$aResults[$grp],'  <div class="zagolovokGrp"> Група '.$grp.'</div>'.$str_add_player ) .'

  </div>
  </div>
  ';
 }
 $Tables_content .='
  </div>
  </div>
  
</div>

  
  ';
  return $Tables_content;
  }
  }
  function mesta_com($aComm1,$aComm2,$command_name1,$command_name2,$etap_id){
      //проверяем все ли введены результаты, если все то рассчет мест делаем и потом заполняем слеующий этап
      $sql = 'SELECT * FROM `bs_reiting` where etap_id='.$etap_id.'  and ((set_1="0" and set_2="0")  )';
      $aResAll = db_list($sql);
      // s($sql);
      $html='';
      // если нет нулевых результатов значит все заполнено
      if (empty($aResAll)) {


          $all_ochki_comm1 = 0;
          $all_ochki_comm2 = 0;
          $all_set1=0;
          $all_set2=0;
          foreach ($aComm1 as $n => $aPl) {
              //   s($aPl);
              $all_ochki_comm1 += $aPl['cnt_win'];
              $all_set1+=$aPl['grp_win_set'];

          }
          foreach ($aComm2 as $n => $aPl) {
              $all_ochki_comm2 += $aPl['cnt_win'];
              $all_set2+=$aPl['grp_win_set'];

          }
          if ($all_ochki_comm1 > $all_ochki_comm2) {
              $txt1 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів';
              $txt2 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів';
          } elseif ($all_ochki_comm1 < $all_ochki_comm2) {

              $txt1 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів';
              $txt2 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів';
          }else{// если равное количесвто очей то определяем по сетам
                if ($all_set1>$all_set2){
                    $txt1 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set1.')';
                    $txt2 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set2.')';
                }else{
                    $txt1 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set2.')';
                    $txt2 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set1.')';

                }
          }
          $html = '
 <style>
    @import url("../../../../css/2xminuska.css?ver=112154");
    </style>
<div class="column_mesta column_mesta_comm">
        <div class="misca"> Місця:</div>
        <ul><li><img height="32px" src="../../../../img/1mesto6.png"> 
        <div class="mesto1 mesto1comm">
        <span class="ml10"></span>-<span class="ml10">' . $txt1 . '</span></div></li>
        <li><img height="32px&quot;" src="../../../../img/2mesto6.png"> 
        <div class="mesto2 mesto2comm"><span class="ml10"></span>-<span class="ml10">' . $txt2 . '</span></div></li>
   </ul>
        </div>';
      }
     return $html;
  }
function table_comm($aComm1,$aComm2, $aResults,$zagl,$turnir_id,$etap_id)
{
    // s($aComm1);
     //  s($aResults);
    $aPlay=$aComm1;
    $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
    $aTurnir= db_row($sql);
    $is_command = $aTurnir['is_command'];
    $command_name1= $_SESSION['command_name1'] = $aTurnir['command_name1'];
    $command_name2= $_SESSION['command_name2'] = $aTurnir['command_name2'];
    $all_reit2 = 0;
    $all_reit_ligas2 = 0;
    $zagl ='<div class="zagolovokGrp"> Команда "'.$command_name1.'" проти команди "'.$command_name2.'" </div>';
   // s($aComm2);
    $cnt_players =count($aComm1);
    $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
        if ($av!=$aVar['krug'])
        {
            $porKrug=1; $av=$aVar['krug'];
        }
        $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
        $porKrug++;
    }
    $porKrug--;
   // s('$porKrug='.$porKrug);
  //  s($aPorGameTable);
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.mesta_com($aComm1,$aComm2,$command_name1,$command_name2,$etap_id).'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
     <tr>
  <th class="num1 ft14 fw700"></th>
  <th class="text-center ft14 fw700 num2"></th>
  <th class="fio ft14 fw700 text-end">
 №
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {

        $content .= '<th class="num fw700 ft14 text-center"><span >'.$n.'</span></th>';
    }

    $content .= '<th class="num ft14 fw700 text-center"></th>
  <th class="num2 ft14 fw700 text-center"></th>
  
  </tr></thead>
    <tr>
  <th class="num1 ft14 fw700"></th>
  <th class="text-center ft14 fw700 num2 align-content-center">Команди</th>
  <th class="fio ft14 fw700 text-end command2 align-content-center">
        Рейтинг "'.$command_name2. '"
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {
        $all_reit2+=$aPl['beg_reit'];
        $all_reit_ligas2+=$aPl['reiting_ukraine'];
        $content .= '<th class="num_comm fw700 ft14 text-center"><span >'.$aPl['beg_reit'].'<br>'.$aPl['reiting_ukraine'].'</span></th>';
    }

    $content .= '<th class="num ft14 fw700 text-center command2">'.$all_reit2.'<br>'.$all_reit_ligas2.'</th>
  <th class="num2 ft14 fw700 text-center"></th>
  
  </tr>
  <tr>
  <th class="num1 ft14 fw700 "></th>
  <th class="text-center align-bottom ft14 fw700 num2 command1 th_color_rose align-content-center">Рейтинг <br>"'.$command_name1.'"</th>
  <th class="fio ft14 fw700">
  <div class="max_heig">

<div class="up_comman command2">
Команда: "'.$command_name2.'"
</div>
<div class="down_comman command1">
Команда: "'.$command_name1.'"
</div>


</div>
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {
        $aPl['name'] = str_replace(' ','<br>',$aPl['name']);
        $content .= '<th class="num_comm fw700 ft14 text-center align-bottom"><span class="rotate-sm-90-main command2">'.$aPl['name'].'</span></th>';
    }

    $content .= '<th class="num_comm ft14 fw700 text-center command1 ft14 align-content-center ">Очки <br>"'.$command_name1.'"</th>
  <th class="num2 ft14 fw700 text-center command1 ft14 align-content-center">Віднош.<br> "'.$command_name1.'"</th>
  
  </tr>
  ';
//s('$aPlay');

//s($aPlay);
   // $cnt_peop=count($aComm2);
   // $max_ochki=$cnt_peop*2;
  //  s('$cnt_peop='.$cnt_peop);
    $all_ochki_comm1= 0;
    $all_set1 = 0;
    $all_set_win1 = 0;
    $all_set_win2 = 0;
    $all_set_lose1 = 0;
    $all_set_lose2 = 0;
    $all_set2 = 0;
    $all_ochki_comm2= 0;
    $all_reit1 = 0;
    $all_reit2 = 0;
    $all_reit_ligas1 = 0;
    $all_reit_ligas2 = 0;
    foreach ($aPlay as $n => $aPl)
    {
        $aPlay[$n]['grp_ochki']=$aPlay[$n]['cnt_win'];
        $all_ochki_comm1+=$aPlay[$n]['grp_ochki'];
        $all_set1+=$aPlay[$n]['grp_win_set']-$aPlay[$n]['grp_lose_set'];
        $all_set_win1+=$aPlay[$n]['grp_win_set'];
        $all_set_lose1+=$aPlay[$n]['grp_lose_set'];
        $all_reit1+=$aPlay[$n]['beg_reit'];
        $all_reit_ligas1+=$aPlay[$n]['reiting_ukraine'];
        $content .= '<tr>
      <td class="text-center ft14 align-middle">'.$aPlay[$n]['grp_num'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['beg_reit'].'<br />'.$aPlay[$n]['reiting_ukraine'].'</td>
      <td class="align-middle ft14 fio command1">'.$aPlay[$n]['name'].'</td>';
        $pl_this=1;
        while($pl_this<=$cnt_players)
            /// foreach ($aResults[$n] as $game => $aGame )
        {
            $pl_this_com=$pl_this+$cnt_players;
                $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$n][$pl_this_com]['active'].'">
                <div>'.$aResults[$n][$pl_this_com]['itog'].'</div>
                </td>';
            $pl_this++;
        }
        //if ($cnt_players==$n) $content .='<td class="zach"></td>';
        $content .='
      <td class="text-center ft14 align-middle min_width_td command1 fw700">'.$aPlay[$n]['grp_ochki'].'</td> 
      <td class="text-center ft14 align-middle min_width_td_vid command1">'.$aPlay[$n]['grp_win_set'].'-'.$aPlay[$n]['grp_lose_set'].'</td>
      
      </tr> ';
    }
// итого по второй команде очки
    $content .= '<tr>
        <td  > </td>
        <td  class="text-center command1 ft14"> '.$all_reit1.'<br>'.$all_reit_ligas1.'</td>
            <td   class="text-end command2 ft14 align-middle fw700">Очки "'.$command_name2.'"</td>';
    foreach ($aComm2 as $n => $aPl)
    {
        $aPl['grp_ochki']=$aPl['cnt_win'];
        $all_ochki_comm2+=$aPl['grp_ochki'];

//$aPl['beg_reit'].'<br>'.$aPl['reiting_ukraine']
        $content .= '<td class="text-center ft14 align-middle min_width_td command2 fw700"><span >'.$aPl['grp_ochki'].'</span></td>';
    }
    $content .='
            <td class="text-center ft14 align-middle min_width_td_vid t-grid-team_table_res"><div></div><div class="left_ochko">'.$all_ochki_comm1.'</div><div class="right_ochko">'.$all_ochki_comm2.'</div></td>
            <td class="text-center ft14 align-middle min_width_td_vid command1">'.$all_set_win1.'-'.$all_set_lose1.'</td>
            
    </tr>';
    // итого по второй команде соотно
    $content .= '<tr>
            
            <td colspan="3" class="text-end command2 ft14">Відношення сетів "'.$command_name2.'"</td>';
    foreach ($aComm2 as $n => $aPl)
    {
        $all_set2+=$aPl['grp_win_set']-$aPl['grp_lose_set'];
        $all_set_win2+=$aPl['grp_win_set'];
        $all_set_lose2+=$aPl['grp_lose_set'];
        $content .= '<td class="text-center ft14 align-middle min_width_td command2"><span >'.$aPl['grp_win_set'].'-'.$aPl['grp_lose_set'].'</span></td>';
    }
    $allSet = $all_set1>$all_set2 ? $all_set1 : $all_set2;
    $clas = $all_set1>$all_set2 ? 'command1' : 'command2';
    $content .='
            <td class="text-center ft14 align-middle min_width_td_vid command2">'.$all_set_win2.'-'.$all_set_lose2.'</td>
            <td class="text-center ft14 align-middle min_width_td_vid"><div class="'.$clas.'">'.$allSet.'</div></td>
            
    </tr>';
    $content.='</table></div>
  </div></div>
  ';

    return $content;
}
function table($aPlay,$aResults,$zagl)
 {
  //  s($aPlay);
 //   s($aResults);
   $cnt_players =count($aPlay);
     $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
     if ($av!=$aVar['krug'])
     {
       $porKrug=1; $av=$aVar['krug'];
     }
     $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
     $porKrug++;
    }
    $porKrug--;
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
  <tr>
  <th class="num1 ft14 fw700">№</th>
  <th class="text-center ft14 fw700 num2">Рейтинг</th>
  <th class="fio ft14 fw700">ПІБ</th>';
  foreach ($aPlay as $n => $aPl)    $content .= '<th class="num fw700 ft14 text-center">'.$n.'</th>';
  $content .= '<th class="num ft14 fw700 text-center">Очки</th>
  <th class="num2 ft14 fw700 text-center">Віднош.</th>
  <th class="num2 ft14 fw700 text-center">Місце</th>
  </tr></thead>';

  foreach ($aPlay as $n => $aPl)
  {
      $content .= '<tr>
      <td class="text-center ft14 align-middle">'.$aPlay[$n]['grp_num'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['beg_reit'].'<br />'.$aPlay[$n]['reiting_ukraine'].'</td>
      <td class="align-middle ft14 fio">'.$aPlay[$n]['name'].'</td>';
      $pl_this=1;
      while($pl_this<=$cnt_players)
     /// foreach ($aResults[$n] as $game => $aGame )
      {
         if ($pl_this == $n)
            $content .='<td class="zach"></td>';
         else
            $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$n][$pl_this]['active'].'">'.$aResults[$n][$pl_this]['itog'].'</td>';
         $pl_this++;
      }
      //if ($cnt_players==$n) $content .='<td class="zach"></td>';
       $content .='
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_ochki'].'</td> 
      <td class="text-center ft14 align-middle min_width_td_vid">'.$aPlay[$n]['grp_win_set'].'-'.$aPlay[$n]['grp_lose_set'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_mesto'].'</td>
      </tr> ';
  }
  $content.='</table></div>
  </div></div>
  ';
     if (!$_SESSION['is_mobile']  && $_SESSION['gt']['user_rule']==100) {
         $content .= '
   <div class="col">
  ' . $cnt_players . ' учасники
  <table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <tr>  
<thead class="th_color_rose">
  <th>Коло</th>';
         if ($cnt_players % 2 == 0) $cnt_players--;
         for ($i = 1; $i <= $cnt_players; $i++)
             $content .= '<th>' . $i . '</th>';
         $content .= '</thead></tr>';
         $fir = 1;
         foreach ($aPorGameTable as $row => $aGames) {
             $content .= '  <tr>';
             if ($fir == 1) {
                 $fir = 0;
                 $content .= '<td rowspan="' . $porKrug . '">Участ</td>';
             }
             foreach ($aGames as $aGame) {
                 $content .= '<td>' . $aGame['play1'] . '-' . $aGame['play2'] . '</td> ';
             }
             $content .= '</tr>';
         }
         $content .= '
  </table>
  </div>
  </div>
 ';
     }
 return $content;
 }   
  function table3($aPlay,$aResults)
 {
   $content = '  <table class="group_table">
  <tr>
  <th>№</th><th>Рей-<br />тинг</th><th class="fio">ПІБ</th>  <th class="num">1</th>  <th class="num">2</th>  <th class="num">3</th> <th class="num">Очки</th>  <th class="num">Співвід.</th>  <th class="num">Місце</th>
  </tr>        
  <tr><td>1</td><td >'.$aPlay[1]['beg_reit'].'<br />'.$aPlay[1]['reiting_ukraine'].'</td><td >'.$aPlay[1]['name'].'</td><td class="zach"></td><td >'.$aResults[1][2]['itog'].'</td><td >'.$aResults[1][3]['itog'].'</td> <td >'.$aPlay[1]['grp_ochki'].'</td> <td >'.$aPlay[1]['grp_win_set'].'-'.$aPlay[1]['grp_lose_set'].'</td><td >'.$aPlay[1]['grp_mesto'].'</td></tr> 
  <tr><td>2</td><td >'.$aPlay[2]['beg_reit'].'<br />'.$aPlay[2]['reiting_ukraine'].'</td><td >'.$aPlay[2]['name'].'</td><td >'.$aResults[2][1]['itog'].'</td><td class="zach"></td><td >'.$aResults[2][3]['itog'].'</td><td >'.$aPlay[2]['grp_ochki'].'</td> <td >'.$aPlay[2]['grp_win_set'].'-'.$aPlay[2]['grp_lose_set'].'</td><td >'.$aPlay[2]['grp_mesto'].'</td></tr> 
  <tr><td>3</td><td >'.$aPlay[3]['beg_reit'].'<br />'.$aPlay[3]['reiting_ukraine'].'</td><td >'.$aPlay[3]['name'].'</td><td >'.$aResults[3][1]['itog'].'</td><td >'.$aResults[3][2]['itog'].'</td><td class="zach"></td><td >'.$aPlay[3]['grp_ochki'].'</td> <td >'.$aPlay[3]['grp_win_set'].'-'.$aPlay[3]['grp_lose_set'].'</td><td >'.$aPlay[3]['grp_mesto'].'</td></tr> 
  </table>
  </td>';
     if ($_SESSION['gt']['user_rule']<10)
         $content .='<td>
  3 учасника
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  </tr>
  <tr>  <td rowspan="2">Учасн.</td>  <td>2-3</td>  <td>1-3</td>  <td>1-2</td>    </tr>
  </table>
 ';  
 return $content;
 } 
  
 function table4($aPlay,$aResults)
 {
   $content = '  <table class="group_table">
  <tr>
  <th>№</th>
  <th>Рей-<br />тинг</th>
  <th class="fio">ПІБ</th>  
  <th class="num">1</th>  
  <th class="num">2</th>  
  <th class="num">3</th>  
  <th class="num">4</th>
  <th class="num">Очки</th>
  <th class="num">Співвід.</th>
  <th class="num">Місце</th>
  </tr>        
  <tr>
  <td>1</td>
  <td >'.$aPlay[1]['beg_reit'].'<br />'.$aPlay[1]['reiting_ukraine'].'</td>
  <td>'.$aPlay[1]['name'].'</td>
  
  <td class="zach"></td>
  <td >'.$aResults[1][2]['itog'].'</td>
  <td >'.$aResults[1][3]['itog'].'</td>
  <td >'.$aResults[1][4]['itog'].'</td>
  <td >'.$aPlay[1]['grp_ochki'].'</td> 
  <td >'.$aPlay[1]['grp_win_set'].'-'.$aPlay[1]['grp_lose_set'].'</td>
  <td >'.$aPlay[1]['grp_mesto'].'</td>
  </tr> 
  <tr>
  <td>2</td>
  <td >'.$aPlay[2]['beg_reit'].'<br />'.$aPlay[2]['reiting_ukraine'].'</td>
  <td>'.$aPlay[2]['name'].'</td>
  <td >'.$aResults[2][1]['itog'].'</td>
  <td class="zach"></td>
  <td >'.$aResults[2][3]['itog'].'</td>
  <td >'.$aResults[2][4]['itog'].'</td>
  <td >'.$aPlay[2]['grp_ochki'].'</td>
  <td >'.$aPlay[2]['grp_win_set'].'-'.$aPlay[2]['grp_lose_set'].'</td>
  <td >'.$aPlay[2]['grp_mesto'].'</td>
  </tr> 
  <tr><td>3</td><td >'.$aPlay[3]['beg_reit'].'<br />'.$aPlay[3]['reiting_ukraine'].'</td><td>'.$aPlay[3]['name'].'</td><td >'.$aResults[3][1]['itog'].'</td><td >'.$aResults[3][2]['itog'].'</td><td class="zach"></td><td >'.$aResults[3][4]['itog'].'</td><td >'.$aPlay[3]['grp_ochki'].'</td><td >'.$aPlay[3]['grp_win_set'].'-'.$aPlay[3]['grp_lose_set'].'</td><td >'.$aPlay[3]['grp_mesto'].'</td></tr> 
  <tr><td>4</td><td >'.$aPlay[4]['beg_reit'].'<br />'.$aPlay[4]['reiting_ukraine'].'</td><td>'.$aPlay[4]['name'].'</td><td >'.$aResults[4][1]['itog'].'</td><td >'.$aResults[4][2]['itog'].'</td><td >'.$aResults[4][3]['itog'].'</td><td class="zach"></td><td >'.$aPlay[4]['grp_ochki'].'</td><td >'.$aPlay[4]['grp_win_set'].'-'.$aPlay[4]['grp_lose_set'].'</td><td >'.$aPlay[4]['grp_mesto'].'</td></tr> 
  </table>
  </td>';
     if ($_SESSION['gt']['user_rule']<10)
         $content .='<td>
  4 учасника
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  </tr>
  <tr>  <td rowspan="2">Учасн.</td>  <td>1-3</td>  <td>1-4</td>  <td>1-2</td>    </tr>
   <tr>    <td>2-4</td>  <td>2-3</td>  <td>3-4</td>   </tr>
  </table>
 ';  
 return $content;
 } 
 function table5($aPlay,$aResults)
 { 
    $content = '  <table class="group_table">
  <tr>
  <th>№</th><th>Рей-<br />тинг</th><th class="fio">ПІБ</th>  <th class="num">1</th>  <th class="num">2</th>  <th class="num">3</th>  <th class="num"> 4</th>
  <th class="num">5</th>  <th class="num">Очки</th>  <th class="num">Спів.</th>  <th class="num">Місце</th>
  </tr>        
  <tr>
  <td>1</td>
  <td >'.$aPlay[1]['beg_reit'].'<br />'.$aPlay[1]['reiting_ukraine'].'</td>
  <td >'.$aPlay[1]['name'].'</td>
  <td class="zach"></td>
  <td >'.$aResults[1][2]['itog'].'</td>
  <td >'.$aResults[1][3]['itog'].'</td>
  <td >'.$aResults[1][4]['itog'].'</td>
  <td >'.$aResults[1][5]['itog'].'</td>
  <td >'.$aPlay[1]['grp_ochki'].'</td>
   <td >'.$aPlay[1]['grp_win_set'].'-'.$aPlay[1]['grp_lose_set'].'</td>
   <td >'.$aPlay[1]['grp_mesto'].'</td>
   </tr> 
  <tr>
  <td>2</td>
  <td >'.$aPlay[2]['beg_reit'].'<br />'.$aPlay[2]['reiting_ukraine'].'</td>
  <td >'.$aPlay[2]['name'].'</td>
  <td >'.$aResults[2][1]['itog'].'</td>
  <td class="zach"></td>
  <td >'.$aResults[2][3]['itog'].'</td>
  <td >'.$aResults[2][4]['itog'].'</td>
  <td >'.$aResults[2][5]['itog'].'</td>
  <td >'.$aPlay[2]['grp_ochki'].'</td> 
  <td >'.$aPlay[2]['grp_win_set'].'-'.$aPlay[2]['grp_lose_set'].'</td>
  <td >'.$aPlay[2]['grp_mesto'].'</td>
  </tr> 
  <tr>
  <td>3</td>
  <td >'.$aPlay[3]['beg_reit'].'<br />'.$aPlay[3]['reiting_ukraine'].'</td>
  <td >'.$aPlay[3]['name'].'</td>
  <td >'.$aResults[3][1]['itog'].'</td>
  <td >'.$aResults[3][2]['itog'].'</td>
  <td class="zach"></td>
  <td >'.$aResults[3][4]['itog'].'</td>
  <td >'.$aResults[3][5]['itog'].'</td>
  <td >'.$aPlay[3]['grp_ochki'].'</td> 
  <td >'.$aPlay[3]['grp_win_set'].'-'.$aPlay[3]['grp_lose_set'].'</td>
  <td >'.$aPlay[3]['grp_mesto'].'</td>
  </tr> 
  <tr>
  <td>4</td>
  <td >'.$aPlay[4]['beg_reit'].'<br />'.$aPlay[4]['reiting_ukraine'].'</td>
  <td >'.$aPlay[4]['name'].'</td>
  <td >'.$aResults[4][1]['itog'].'</td>
  <td >'.$aResults[4][2]['itog'].'</td>
  <td >'.$aResults[4][3]['itog'].'</td>
  <td class="zach"></td>
  <td >'.$aResults[4][5]['itog'].'</td>
  <td >'.$aPlay[4]['grp_ochki'].'</td> 
  <td >'.$aPlay[4]['grp_win_set'].'-'.$aPlay[4]['grp_lose_set'].'</td>
  <td >'.$aPlay[4]['grp_mesto'].'</td>
  </tr> 
  <tr>
  <td>5</td>
  <td >'.$aPlay[5]['beg_reit'].'<br />'.$aPlay[5]['reiting_ukraine'].'</td>
  <td >'.$aPlay[5]['name'].'</td><td >'.$aResults[5][1]['itog'].'</td>
  <td >'.$aResults[5][2]['itog'].'</td><td >'.$aResults[5][3]['itog'].'</td>
  <td >'.$aResults[5][4]['itog'].'</td>
  <td class="zach"></td>
  <td >'.$aPlay[5]['grp_ochki'].'</td> 
  <td >'.$aPlay[5]['grp_win_set'].'-'.$aPlay[5]['grp_lose_set'].'</td>
  <td >'.$aPlay[5]['grp_mesto'].'</td></tr> 
 </table>
  </td>';
     if ($_SESSION['gt']['user_rule']<10)
         $content .='<td>
       4 учасника
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  </tr>
  <tr>  <td rowspan="2">Учасн</td>  <td>1-3</td>  <td>1-4</td>  <td>1-2</td>    </tr>
   <tr>    <td>2-4</td>  <td>2-3</td>  <td>3-4</td>   </tr>
  </table>
  5 учасників
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  <th>IV</th><th>V</th>  </tr>
  <tr>  <td rowspan="2">Учасн</td>  <td>2-4</td>  <td>1-4</td>  <td>1-3</td>  <td>2-3</td> <td>1-2</td>  </tr>
   <tr>    <td>1-5</td>  <td>3-5</td>  <td>2-5</td>  <td>4-5</td>  <td>3-4</td> </tr>
  </table>
';

return $content;
 }
  function table6($aPlay,$aResults)
 { 
     $content = '  <table class="group_table">
  <tr>
  <th>№</th><th>Рей-<br />тинг</th><th class="fio">ПІБ</th>  <th class="num">1</th>  <th class="num">2</th>  <th class="num">3</th>  <th class="num"> 4</th>
  <th class="num">5</th>  <th class="num">6</th>  <th class="num">Очки</th>  <th class="num">Спів.</th>  <th class="num">Місце</th>
  </tr>        
  <tr><td>1</td><td >'.$aPlay[1]['beg_reit'].'<br />'.$aPlay[1]['reiting_ukraine'].'</td><td >'.$aPlay[1]['name'].'</td><td class="zach"></td><td >'.$aResults[1][2]['itog'].'</td><td >'.$aResults[1][3]['itog'].'</td><td >'.$aResults[1][4]['itog'].'</td><td >'.$aResults[1][5]['itog'].'</td> <td >'.$aResults[1][6]['itog'].'</td><td >'.$aPlay[1]['grp_ochki'].'</td> <td >'.$aPlay[1]['grp_win_set'].'-'.$aPlay[1]['grp_lose_set'].'</td><td >'.$aPlay[1]['grp_mesto'].'</td></tr> 
  <tr><td>2</td><td >'.$aPlay[2]['beg_reit'].'<br />'.$aPlay[2]['reiting_ukraine'].'</td><td >'.$aPlay[2]['name'].'</td><td >'.$aResults[2][1]['itog'].'</td><td class="zach"></td><td >'.$aResults[2][3]['itog'].'</td><td >'.$aResults[2][4]['itog'].'</td><td >'.$aResults[2][5]['itog'].'</td><td >'.$aResults[2][6]['itog'].'</td><td >'.$aPlay[2]['grp_ochki'].'</td> <td >'.$aPlay[2]['grp_win_set'].'-'.$aPlay[2]['grp_lose_set'].'</td><td >'.$aPlay[2]['grp_mesto'].'</td></tr> 
  <tr><td>3</td><td >'.$aPlay[3]['beg_reit'].'<br />'.$aPlay[3]['reiting_ukraine'].'</td><td >'.$aPlay[3]['name'].'</td><td >'.$aResults[3][1]['itog'].'</td><td >'.$aResults[3][2]['itog'].'</td><td class="zach"></td><td >'.$aResults[3][4]['itog'].'</td><td >'.$aResults[3][5]['itog'].'</td><td >'.$aResults[3][6]['itog'].'</td><td >'.$aPlay[3]['grp_ochki'].'</td> <td >'.$aPlay[3]['grp_win_set'].'-'.$aPlay[3]['grp_lose_set'].'</td><td >'.$aPlay[3]['grp_mesto'].'</td></tr> 
  <tr><td>4</td><td >'.$aPlay[4]['beg_reit'].'<br />'.$aPlay[4]['reiting_ukraine'].'</td><td >'.$aPlay[4]['name'].'</td><td >'.$aResults[4][1]['itog'].'</td><td >'.$aResults[4][2]['itog'].'</td><td >'.$aResults[4][3]['itog'].'</td><td class="zach"></td><td >'.$aResults[4][5]['itog'].'</td><td >'.$aResults[4][6]['itog'].'</td><td >'.$aPlay[4]['grp_ochki'].'</td> <td >'.$aPlay[4]['grp_win_set'].'-'.$aPlay[4]['grp_lose_set'].'</td><td >'.$aPlay[4]['grp_mesto'].'</td></tr> 
  <tr><td>5</td><td >'.$aPlay[5]['beg_reit'].'<br />'.$aPlay[5]['reiting_ukraine'].'</td><td >'.$aPlay[5]['name'].'</td><td >'.$aResults[5][1]['itog'].'</td><td >'.$aResults[5][2]['itog'].'</td><td >'.$aResults[5][3]['itog'].'</td><td >'.$aResults[5][4]['itog'].'</td><td class="zach"></td><td >'.$aResults[5][6]['itog'].'</td><td >'.$aPlay[5]['grp_ochki'].'</td> <td >'.$aPlay[5]['grp_win_set'].'-'.$aPlay[5]['grp_lose_set'].'</td><td >'.$aPlay[5]['grp_mesto'].'</td></tr> 
  <tr><td>6</td><td >'.$aPlay[6]['beg_reit'].'<br />'.$aPlay[6]['reiting_ukraine'].'</td><td >'.$aPlay[6]['name'].'</td><td >'.$aResults[6][1]['itog'].'</td><td >'.$aResults[6][2]['itog'].'</td><td >'.$aResults[6][3]['itog'].'</td><td >'.$aResults[6][4]['itog'].'</td><td >'.$aResults[6][5]['itog'].'</td><td class="zach"></td><td >'.$aPlay[6]['grp_ochki'].'</td> <td >'.$aPlay[6]['grp_win_set'].'-'.$aPlay[6]['grp_lose_set'].'</td><td >'.$aPlay[6]['grp_mesto'].'</td></tr> 
 </table>
  </td>';
     if ($_SESSION['gt']['user_rule']<10)
         $content .='<td>
  5 учасників
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  <th>IV</th><th>V</th>  </tr>
  <tr>  <td rowspan="2">Учасн</td>  <td>2-4</td>  <td>1-4</td>  <td>1-3</td>  <td>2-3</td> <td>1-2</td>  </tr>
   <tr>    <td>1-5</td>  <td>3-5</td>  <td>2-5</td>  <td>4-5</td>  <td>3-4</td> </tr>
  </table>
  6 участників
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  <th>IV</th>  <th>V</th>  </tr>
  <tr>  <td rowspan="3">Учасн</td>  <td>2-4</td>  <td>1-4</td>  <td>1-3</td>  <td>2-3</td>  <td>1-2</td>  </tr>
   <tr>    <td>1-5</td>  <td>2-6</td>  <td>2-5</td>  <td>1-6</td>  <td>3-4</td>  </tr> 
   <tr>  <td>3-6</td>  <td>3-5</td>  <td>4-6</td>  <td>4-5</td>  <td>5-6</td>  </tr> 
  </table>
';
return $content;
}  

?>