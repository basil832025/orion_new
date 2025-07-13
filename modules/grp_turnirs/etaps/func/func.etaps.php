<?php
include_once 'func.etapresultedit.php';
include_once 'func.etapresult_2x_minus_edit.php';

function get_games($field,$id)
{
     // s('get_play');
    $num_player =  substr($field, -1);
    $sql='select count(*) as cn     
  from '.T_REITING.' r  where  r.etap_id='.$id;
   // s($sql);
    $cnt = db_field($sql,'cn');
   // s($cnt);
   /* if ($cnt==0) {
        $sql='select cnt_games    
  from '.T_ETAPS.' r  where  r.id='.$id;
          s($sql);
        $cnt = db_field($sql,'cnt_games');
    }*/

    return $cnt;
}

function set2minuska($form,$turnir_id,$etap_id,$type_etap=0)
{
///$etap_id=poste('id');
//s($form);
// если $form['istochnik_posev'] =0 это берем из всех спорсменов тунира по рейтингу
$istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0; 
//s($istochnik_posev);
// сколько игроков будет участовать в данном этапе, если 0 то все игроки турнира
$cnt_people=!empty($form['cnt_people']) ? $form['cnt_people'] : 0;
//s('$cnt_people='.$cnt_people);
$cnt_people = $cnt_people<=16 ? $cnt_people : 16; // не больше 16 человек на турнире

//заполняем игры
getAndSetGames16($cnt_people,$etap_id,$turnir_id,$type_etap);
setGamesPlayers($etap_id,$type_etap);
setGamesPlayersNoPlayer($etap_id,$type_etap);
//}
}

function get_num_game_pars($num_posev_olimp,$aVariants2minuska_16)
{
   // global $aVariants2minuska_16;
    $playNum=0;
    foreach ($aVariants2minuska_16 as $num => $aPars)
    {
        if ($aPars['player1']==$num_posev_olimp) { $playNum=1;$Player2=$aPars['player2'];  break;}
        if ($aPars['player2']==$num_posev_olimp) { $playNum=2; $Player2=$aPars['player1'];  break;}
    }
    return array($num,$playNum,$Player2);
}
function get_num_game_pars_one($num_posev_olimp,$aVariants2minuska_16)
{
    // global $aVariants2minuska_16;
    $playNum=0;
    foreach ($aVariants2minuska_16 as $num => $aPars)
    {
        if ($aPars['player1']==$num_posev_olimp) { $playNum=1;$Player1=$aPars['player1'];  break;}
        if ($aPars['player2']==$num_posev_olimp) { $playNum=2; $Player1=$aPars['player2'];  break;}
    }
    return array($num,$playNum,$Player1);
}
function setGamesPlayers($etap_id,$type_etap=0)
{ 
global $aVariants2minuska_16,$aVariants2minuska_8,$aVariantsOlimp_8,$aVariantsOlimp_16;
// получаем отсортированных людей по местам в группе
$sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where num_posev_olimp>0 and player_id>0 and  etap_id='.$etap_id.
'  order by num_posev_olimp';
$aPlayersGrp = db_list($sql);
$cnt_people = count ($aPlayersGrp);
    if ($type_etap==5) $aVariants= ($cnt_people>8) ?  $aVariantsOlimp_16  : $aVariantsOlimp_8;
    else
 $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
if (!empty($aPlayersGrp))
{
    foreach ($aPlayersGrp as $aPlayer)
    {
        // определяем номер игры данной пары 
        list($num,$playNum,$Player2) = get_num_game_pars($aPlayer['num_posev_olimp'],$aVariants);
        $sql = 'select * from '.T_REITING.' where etap_id='.$etap_id.' and olimp16_num='.$num;
        $Game = db_row($sql);
        if (empty($Game))// если данной игры  не существует то значит игрок в след этапе 
        {
           // s('$num1='.$num);
            $winMin =  $aVariants[$num]['win'] ;
            $aWin = explode(".", $winMin);
            $playNum= $aWin[1];
            $num= $aWin[0];
        //    s('$num2='.$num);



            if (($cnt_people==3 || $cnt_people==2) && $num==5) { $num=7;$playNum=1;}
            if ( $cnt_people==2 && $num==6) { $num=7;$playNum=2;}
        }
        $sqlPlayer = $playNum==1 ? 'pl_id_1='.$aPlayer['player_id'] : 'pl_id_2='.$aPlayer['player_id'];
        $sql = 'update '.T_REITING.' set '.$sqlPlayer.'  
        where etap_id='.$etap_id.' and olimp16_num='.$num; 
        db_query($sql);
    }
}
}
function setGamesPlayersNoPlayer($etap_id,$type_etap=0)
{
    global $aVariants2minuska_16,$aVariants2minuska_8,$aVariantsOlimp_8,$aVariantsOlimp_16;
// получаем отсортированных людей по местам в группе
    $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where num_posev_olimp>0 and player_id=0 and  etap_id='.$etap_id.
        '  order by num_posev_olimp';
    $aPlayersGrp = db_list($sql);
    $cnt_people = count ($aPlayersGrp);
    if ($type_etap==5) $aVariants= ($cnt_people>8) ?  $aVariantsOlimp_16  : $aVariantsOlimp_8;
    else
        $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
    if (!empty($aPlayersGrp))
    {
        foreach ($aPlayersGrp as $aPlayer)
        {
            // определяем номер игры данной пары
            list($num,$playNum,$Player2) = get_num_game_pars($aPlayer['num_posev_olimp'],$aVariants);
            $sql = 'select * from '.T_REITING.' where etap_id='.$etap_id.' and olimp16_num='.$num;
            $Game = db_row($sql);
            if (empty($Game))// если данной игры  не существует то значит игрок в след этапе
            {
                $winMin =  $aVariants[$num]['win'] ;
                $aWin = explode(".", $winMin);
                $playNum= $aWin[1];
                $num= $aWin[0];
                if (($cnt_people==3 || $cnt_people==2) && $num==5) { $num=7;$playNum=1;}
                if ( $cnt_people==2 && $num==6) { $num=7;$playNum=2;}
            }
            $sqlPlayer = $playNum==1 ? 'groups_pred1='.$aPlayer['groups_pred'].',grp_num_pred1='.$aPlayer['grp_num_pred'].',mesto_all_pred1='.$aPlayer['mesto_all_pred']
                : 'groups_pred2='.$aPlayer['groups_pred'].',grp_num_pred2='.$aPlayer['grp_num_pred'].',mesto_all_pred2='.$aPlayer['mesto_all_pred'];
            $sql = 'update '.T_REITING.' set '.$sqlPlayer.'  
        where etap_id='.$etap_id.' and olimp16_num='.$num;
            db_query($sql);
        }
    }
}
function isGetPars($aPlayersGrp,$Player2)
{
  foreach ($aPlayersGrp as $aPlayer)
  {
     if ($aPlayer['num_posev_olimp']==$Player2) return 1;
  }
  return 0;
}
function getAndSetGames16($cnt_people,$etap_id,$turnir_id,$type_etap=0)
{
global $aVariants2minuska_16,$aVariants2minuska_8,$aVariantsOlimp_8,$aVariantsOlimp_16;

if ($type_etap==5 || $type_etap==4) $aVariants= ($cnt_people>8) ?  $aVariantsOlimp_16  : $aVariantsOlimp_8;
else
    $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
  $maxPl= ($cnt_people>8) ?  8  : 4;
//  s($aVariants);
  $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where num_posev_olimp>0  and  etap_id='.$etap_id.
'  order by num_posev_olimp';
//s('ttt1='.$sql);
$aPlayersGrp = db_list($sql);  
if (!empty($aPlayersGrp))
{
    $aIskl=array();
    $aIsGames=array();
   // s($aPlayersGrp);
    foreach ($aPlayersGrp as $aPlayer)
    {
       // s($aPlayer);
        // определяем номер игры данной пары 
        list($num,$playNum,$Player2) = get_num_game_pars($aPlayer['num_posev_olimp'],$aVariants);
       $keyTmp = array_search($num,$aIskl);
       $aIskl[]=$num;
     //  s('$keyTmp='.$keyTmp);
       if ($keyTmp=== false) 
       {
        
       
    //    s('$num='.$num);
     //   s($Player2);
        
        $isPar2 = isGetPars($aPlayersGrp,$Player2);
        if ($isPar2>0) 
        {
            $aIsGames[]=$num;
            $prim = $aVariants[$num]['etap'];
          $where = 'turnir_id='.$turnir_id.',pl_id_1=0,pl_id_2=0,
            rt_id_1_beg=0,rt_id_2_beg=0,olimp16_num='.$num.',etap_prim="'.$prim.'",type_game=2, etap_id='.$etap_id.', auto=1
            ';
            
            $sql ='insert into '.T_REITING.'  SET '.$where  ;
           //  s('t2='.$sql);
            db_query($sql);  
        }
        }
    } //s($aIskl);
}    
 // s($aIsGames);
    foreach ($aVariants as $numGame => $aGame)
    {
        if ($numGame>$maxPl)
        { 
            $is_game=1;
            if ($type_etap==3 && $numGame>28 && $cnt_people>8) $is_game=0;
            if ($type_etap==4 && $numGame>15 && $numGame!=26 && $cnt_people>8) $is_game=0;
             if ($type_etap==3 &&   $numGame>12 &&   $cnt_people<=8) $is_game=0;
            if ($type_etap==4   && $numGame!=11 && $numGame>7 &&   $cnt_people<=8) $is_game=0;
             
            if ($cnt_people>8) 
                $cn_p = (!empty($aGame['cnt'])) ? $aGame['cnt'] : 9;
            else
                $cn_p = (!empty($aGame['cnt'])) ? $aGame['cnt'] : 2;
            //  s($cn_p);
            //  s($cnt_people);
     //   s($aGame);
     //   s($aIsGames);
        //    s('$numGame='.$numGame);
          //  s('$type_etap='.$type_etap);
           // s('$is_game='.$is_game);
            if ($is_game && !empty($aGame['isGame1']) )
            {
              $keyTmp1 = array_search($aGame['isGame1'],$aIsGames);
          //    s('$keyTmp1='.$keyTmp1);
              if (($keyTmp1=== false) && (!empty($aGame['isGame11'])) ) $keyTmp1 = array_search($aGame['isGame11'],$aIsGames);
              $keyTmp2=1;
             if (!empty($aGame['isGame2']))
              $keyTmp2 = array_search($aGame['isGame2'],$aIsGames);
              //  s('$keyTmp2='.$keyTmp2);
              if (($keyTmp1=== false) || ($keyTmp2=== false)) $is_game = 0;
              // дополнительные условия
              if (!$is_game && (!empty($aGame['isArray'])) && (!empty($aGame['isCnt'])))
              {
                    $arrGame = explode(',',$aGame['isArray']);
                //    s($arrGame);
                    $cntTrue=0;
                    foreach ($arrGame as $elem)
                    {

                        $keyTmp1 = array_search($elem,$aIsGames);
                       // s('$elem='.$elem.' $keyTmp1='.$keyTmp1);
                        if ($keyTmp1!==false) $cntTrue++;
                     //   s('$cntTrue='.$cntTrue);
                    }
                    if ($cntTrue>=$aGame['isCnt']) $is_game=true;
              }
            }
        //    s('$is_game='.$is_game);
           if ($cn_p<=$cnt_people && $is_game)
            {
                $where = 'turnir_id='.$turnir_id.',pl_id_1=0,pl_id_2=0,
                rt_id_1_beg=0,rt_id_2_beg=0,olimp16_num='.$numGame.',etap_prim="'.$aGame['etap'].'",type_game=2, etap_id='.$etap_id.', auto=1'; 
                
                $sql ='insert into '.T_REITING.'  SET '.$where  ;
               //  s('t3='.$sql);
                db_query($sql);
                $aIsGames[]=$numGame;
             //   s($aIsGames);
            }
       
        }
    }  


}
function getGroupEtapIstochnik($aIstochnikEtap,$etap_id,$cnt_people,$form)
{
global    $indZone1, $indZone2,$indPodZone1,$indPodZone2, $indPodZone3, $indPodZone4,
$zona1, $zona2, $Podzona1, $Podzona2, $Podzona3, $Podzona4,
$iskl4grp16, $iskl4grp15 , $iskl4grp14 ;  
//s($aIstochnikEtap);
//s($etap_id);
//s($cnt_people);
//s($form);


// сначала удаяляем по этапу все взаимосвязи если нет еще игор сыгранных
//$sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where  etap_id='.$etap_id;
//db_query($sql);
$sql = 'select  t.* from '.T_ETAPS.' t  where  t.id='.$etap_id;  
// s($sql);
$aEtap =db_row($sql); 
$cntGroups=!empty($aIstochnikEtap['cnt_grp']) ? $aIstochnikEtap['cnt_grp'] : $aIstochnikEtap['cntGroups'];
$isParnNumGrp = $cntGroups % 2 == 0 ? 1 :0;
$istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0; 
//  КОД до 21,01,2022
/*
$is_reiting_zmeyka= $aEtap['is_reiting_zmeyka'];

$diff_players =  $cnt_people % $cntGroups; // узнаем сколько дополнительный мест нужно посеять не кратное поличесву групп и участников

$cnt_mest_group_bez_ost = ($cnt_people - $diff_players) / $cntGroups; // сколько мест в группах сееем подряд

$cnt_people_bez_ostachi= $is_reiting_zmeyka==1 ? $cnt_people : $cnt_mest_group_bez_ost *$cntGroups;
*/
$cnt_people_bez_ostachi=$cnt_people;

$mesto_from = $form['mesto_from']-1;
$sql = 'select t.is_reiting,t.is_reiting_w,t.group_id_old from '.T_TURNIRS.' t where t.id='.$aIstochnikEtap['turnir_id'];
$aTurnirSetting = db_row($sql);
$is_reiting = $aTurnirSetting['is_reiting'] ? $aTurnirSetting['is_reiting'] : $aTurnirSetting['is_reiting_w'];

//  $etap_id=$istochnik_posev;
////----------здесь нужно добавить боллее сложную логику выбрать места согласна занятых мест в группах 
// с возможным определением дополнительных мест по рейтингу если установлена такаая опция по турниру или по этапу
//,(select '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end from `'.T_PLAYERS.'` p  where p.id=e.player_id) as beg_reit
//  КОД до 21,01,2022
/*$sql='SELECT e.*  FROM `'.T_ETAPS_PLAYER_MESTA.'` e 
where  etap_id='.$istochnik_posev.' order by groups,grp_mesto,grp_num ';*/
$sql='SELECT e.*  FROM `'.T_ETAPS_PLAYER_MESTA.'` e 
where  etap_id='.$istochnik_posev.' and mesto_all is not null order by mesto_all limit '.$mesto_from.','.$cnt_people;

//s('tytyt1111');
//s($sql);
$aPlayers=db_list($sql);
//s($aPlayers);
$aGroupMesto=array();
// пройдемся по массиву и заполним двумерный массив групп и мест 
if (!empty($aPlayers))
{
    // с какой групп начинаем, может это 2й финал и уже не с первой группы нужно сеять 
    $grp_mesto=1; $fisPris=true; $diff =0; $startGrp =1; $startgrp_mesto=1; $startPoryadok = 1;
    foreach ($aPlayers as $aMesto)
    { 
        $tmpMesto = !empty($aMesto['grp_mesto']) ? $aMesto['grp_mesto'] : $aMesto['grp_num'];
        if ($fisPris) 
        {
            //if ($cntGroups % 2 == 0) 
            $diff = $tmpMesto - $grp_mesto;
            $fisPris=false;
            $startGrp = $aMesto['groups'];
         //   if ($startGrp==$cntGroups) $startPoryadok = 2;
            $startgrp_mesto = $tmpMesto - $diff;
        }
        $tmpMesto = $tmpMesto - $diff;
        $aGroupMesto[$aMesto['groups']][$tmpMesto] = $aMesto;
    }  
    // если это 2 финал то нужно поменять группы для массива как буд-то это с 1 группы
    if ($diff > 0) 
    {
        $aGroupMestoTemp=[];

        $startGrp =1;
      //  s('$diff='.$diff);
        $n=1;
        // пройдемся по группам и если нет в группе 1 мест( индекса ) то нужно уменьшить индекс на 1
        // а также поменяем номера групп местами

        $aGroupMestoTemp =array();
       foreach ($aGroupMesto as $aMesto)
     { //s($aMesto);
         // пройдемся по подмасиву мест и поменяем нумерацию с 1 места или индекса
         $mest = 1;
         $aMestoNew = [];
         foreach ($aMesto as $elem)
         {
             $aMestoNew[$mest]=$elem;
             $mest++;
         }

        $aGroupMestoTemp[$n] = $aMestoNew ;
       // s($aGroupMesto[$n]);
        $n++;   
    }
    $aGroupMesto=$aGroupMestoTemp;
    $cntGroups = $n-1;
    }
 //   s('$cntGroups='.$cntGroups);
   // s('$startGrp='.$startGrp);
//s($aGroupMesto);
}   
$indZone1=0;
$indZone2=0;
$indPodZone1=0;
$indPodZone2=0;
$indPodZone3=0;
$indPodZone4=0;
$zona1=array(1,4,5,8,9,12,13,16);
$zona2=array(2,3,6,7,10,11,14,15);
$Podzona1=array(1,8,9,16);
$Podzona2=array(4,5,12,13);
$Podzona3=array(3,6,11,14);
$Podzona4=array(2,7,10,15);
$iskl4grp16 = array(13=>16,14=>15,15=>14,16=>13);
$iskl4grp15 = array(13=>14,14=>15,15=>13);
$iskl4grp14 = array(13=>14,14=>13);
//  $iskl4grp11 = array(13=>14);

// s($sql);
$n =1 ; // порядковый номер 
//$por_zmeyki = $startPoryadok; // 1 вниз 2 вверх 
$por_zmeyki = 1; // 1 вниз 2 вверх 
$numGrpZmeyki = $startGrp; // порядковый номер группы
//s('$numGrpZmeyki='.$numGrpZmeyki);
$mestowork=$startgrp_mesto; // начинаем с 1 мест
//s('$startgrp_mesto='.$startgrp_mesto);
while ($n<=$cnt_people_bez_ostachi) 
{
  //  s('$n='.$n);
    $sug=1;
    // запускаем змейку по группам в прямом порядке
    if ( $sug && $por_zmeyki == 1)
    { 
        if (!empty($aGroupMesto[$numGrpZmeyki][$mestowork])) 
        {
        if (($cntGroups % 2 == 0) && ($cntGroups>2)  ) // парное количество
    //    if (($cntGroups % 2 == 0) && ($cntGroups>2) && ($diff == 0) ) // парное количество
        {
          
            // применям правило для 1 мест
           if ($mestowork==1) $aGroupMesto= getMestoWork1mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people,$n);
            // применям правило для 3 мест
           if ($mestowork==3) $aGroupMesto =  getMestoWork3mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people);
          
            
        
        } 
        else
        {
            $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$n;
          //  s('n='.$n);
          //  s('$numGrpZmeyki='.$numGrpZmeyki);
          //  s('$mestowork='.$mestowork);
          //  s($aGroupMesto);
            // записуем в бд место
            setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
            
        }   
        if ($n==$cnt_people_bez_ostachi) $n++;
        }  
        $numGrpZmeyki++; 
        
        //}
        if ($numGrpZmeyki>$cntGroups)
        {
            if ($cntGroups==2)
            {
               $numGrpZmeyki = 1; // порядковый номер группы
                $por_zmeyki=1;  
            }
            else
            {
               $numGrpZmeyki = $cntGroups; // порядковый номер группы
                $por_zmeyki=2;  
            }
            
            $sug=0;
            $mestowork++;  
        }
    } //---- конец змейки в прямом порядке
    if ( $sug && $por_zmeyki == 2)
    { 
        if (!empty($aGroupMesto[$numGrpZmeyki][$mestowork])) 
        {
        if ( ($cntGroups % 2 == 0) && ($cntGroups>2) && !empty($aGroupMesto[$numGrpZmeyki][1]['zona'] )) // парное количество
  //      if (($diff == 0) && ($cntGroups % 2 == 0) && ($cntGroups>2) && !empty($aGroupMesto[$numGrpZmeyki][1]['zona'] )) // парное количество
        {
           // s('$numGrpZmeyki='.$numGrpZmeyki);
          //  s($aGroupMesto);
                    // применям правило для 2 мест
                if ($mestowork==2) $aGroupMesto = getMestoWork2mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people);
                // применям правило для 4 мест
                if ($mestowork==4) $aGroupMesto= getMestoWork4mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people,$n); 
            
       } 
        else
        {
            $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$n;
         //   s('n='.$n);
         ////   s('$numGrpZmeyki='.$numGrpZmeyki);
         //   s('$mestowork='.$mestowork);
         //   s($aGroupMesto);
            // записуем в бд место
            setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
        } 
        if ($n==$cnt_people_bez_ostachi) $n++;
        }
       $numGrpZmeyki--; 
       if ($numGrpZmeyki<1) 
       {
            $por_zmeyki=1; 
            $sug=0;  
            $numGrpZmeyki = 1; // порядковый номер группы
            $mestowork++;  
       }
    
    }    //---- конец змейки в обратном порядке   
  if (!empty($aGroupMesto[$numGrpZmeyki][$mestowork])) 
    $n++;

}// end while  
}
function getMestoWork4mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people,$n)
{ 
    global    $indZone1, $indZone2,$indPodZone1,$indPodZone2, $indPodZone3, $indPodZone4,
    $zona1, $zona2, $Podzona1, $Podzona2, $Podzona3, $Podzona4,
    $iskl4grp16, $iskl4grp15 , $iskl4grp14 ; 
    $mestowork =4;
    if ($cnt_people==16) $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']= $iskl4grp16[$n];
    if ($cnt_people==15) $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']= $iskl4grp15[$n];
    if ($cnt_people==14) $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']= $iskl4grp14[$n];
    if ($cnt_people==13) $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']= $iskl4grp14[$n];
    // записуем в бд место
    setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
    return $aGroupMesto;
}

function getMestoWork1mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people,$n)
{ 
global    $indZone1, $indZone2,$indPodZone1,$indPodZone2, $indPodZone3, $indPodZone4,
$zona1, $zona2, $Podzona1, $Podzona2, $Podzona3, $Podzona4,
$iskl4grp16, $iskl4grp15 , $iskl4grp14 ; 
$mestowork =1;
$aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$n;
// записуем в бд место
setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
if (in_array($n, $zona1)) 
{ // нашли данный номер в 1 зоне то сдвинем индекс зоны
    $indZone1= array_search($n,$zona1);
    $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=1;
    // теперь ищем в под зонах 1 и 2
    if (in_array($n, $Podzona1)) 
    {
        $indPodZone1= array_search($n,$Podzona1);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=1;
    }
    if (in_array($n, $Podzona2)) 
    {
        $indPodZone2= array_search($n,$Podzona2);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=2;
    }
}

// ищем в подзоне 2
if (in_array($n, $zona2)) 
{ // нашли данный номер в 1 зоне то сдвинем индекс зоны
    $indZone2= array_search($n,$zona2);
    $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=2;
    // теперь ищем в под зонах 1 и 2
    if (in_array($n, $Podzona3)) 
    {
        $indPodZone3= array_search($n,$Podzona3);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=3;
    }
    if (in_array($n, $Podzona4)) 
    {
        $indPodZone4= array_search($n,$Podzona4);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=4;
    }
}
return $aGroupMesto;
}     
function getMestoWork2mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people)
{ 
    global    $indZone1, $indZone2,$indPodZone1,$indPodZone2, $indPodZone3, $indPodZone4,
    $zona1, $zona2, $Podzona1, $Podzona2, $Podzona3, $Podzona4,
    $iskl4grp16, $iskl4grp15 , $iskl4grp14 ; 
    $mestowork =2;
    $numZona = $aGroupMesto[$numGrpZmeyki][1]['zona']; 
    if ($numZona==1)
    {
        // делаем реверс зоны и находим минимальное место
        $indZone2++;
        $mesto2 = $zona2[$indZone2];
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=2;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto2;
        if (in_array($mesto2, $Podzona3)) 
        {
            $indPodZone3= array_search($mesto2,$Podzona3);
            $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=3;
        }
        if (in_array($mesto2, $Podzona4)) 
        {
            $indPodZone4= array_search($mesto2,$Podzona4);
            $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=4;
        }
    }
    if ($numZona==2)
    {
        // делаем реверс зоны и находим минимальное место
        $indZone1++;
        $mesto2 = $zona1[$indZone1];
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=1;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto2;
        if (in_array($mesto2, $Podzona1)) 
        {
            $indPodZone1= array_search($mesto2,$Podzona1);
            $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=1;
        }
        if (in_array($mesto2, $Podzona2)) 
        {
            $indPodZone2= array_search($mesto2,$Podzona2);
            $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=2;
        }
    }
    // записуем в бд место
    setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
    return $aGroupMesto;

}    

function getMestoWork3mesto($aGroupMesto,$numGrpZmeyki,$etap_id,$cnt_people)
{ 
    global    $indZone1, $indZone2,$indPodZone1,$indPodZone2, $indPodZone3, $indPodZone4,
    $zona1, $zona2, $Podzona1, $Podzona2, $Podzona3, $Podzona4,
    $iskl4grp16, $iskl4grp15 , $iskl4grp14 ; 
    $mestowork =3;
    $numpodzona = $aGroupMesto[$numGrpZmeyki][2]['podzona'];   
  //  s('$numpodzona='.$numpodzona);
    if ($numpodzona==1)
    {
        // делаем реверс подзоны и находим минимальное место
        $indPodZone2++;
        $mesto3 = $Podzona2[$indPodZone2];
        $indZone1= array_search($mesto3,$zona1);
        
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=1;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=2;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto3;
    }
    if ($numpodzona==2)
    {
        // делаем реверс подзоны и находим минимальное место
        $indPodZone1++;
    //    s('$indPodZone1='.$indPodZone1);
        $mesto3 = $Podzona1[$indPodZone1];
    //    s('$mesto3='.$mesto3);
        $indZone1= array_search($mesto3,$zona1);
    //    s('$indZone1='.$indZone1);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=1;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=1;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto3;
     }
    if ($numpodzona==3)
    {
        // делаем реверс подзоны и находим минимальное место
        $indPodZone4++;
        $mesto3 = $Podzona4[$indPodZone4];
        $indZone2= array_search($mesto3,$zona2);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=2;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=4;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto3;
        }
    if ($numpodzona==4)
    {
        // делаем реверс подзоны и находим минимальное место
        $indPodZone3++;
        $mesto3 = $Podzona3[$indPodZone3];
        $indZone2= array_search($mesto3,$zona2);
        $aGroupMesto[$numGrpZmeyki][$mestowork]['zona']=2;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['podzona']=3;
        $aGroupMesto[$numGrpZmeyki][$mestowork]['num_posev_olimp']=$mesto3;
    }
    if ($aGroupMesto[$numGrpZmeyki][$mestowork]>$cnt_people)
    {
    
    }
    // записуем в бд место
    setBDMestoOlimp($aGroupMesto[$numGrpZmeyki][$mestowork],$etap_id,$cnt_people,$mestowork);
    return $aGroupMesto;   
}
function setBDMestoOlimp($aGroupMesto,$etap_id,$cnt_people,$mestowork)
{
if (!empty($aGroupMesto) && !empty($aGroupMesto['num_posev_olimp']))
{
 //   s('$aGroupMesto');
//    s($aGroupMesto);
    $player_id = !empty($aGroupMesto['grp_mesto']) ? $aGroupMesto['player_id'] : 0 ;
  /*  if ($aGroupMesto['num_posev_olimp']>$cnt_people) 
    {
        if ($cnt_people==15) $aGroupMesto['num_posev_olimp']=14;
        if ($cnt_people==14 && $aGroupMesto['num_posev_olimp']==15) $aGroupMesto['num_posev_olimp']=13;
        if ($cnt_people==14 && $aGroupMesto['num_posev_olimp']==16) $aGroupMesto['num_posev_olimp']=14;
        if ($cnt_people==13 ) $aGroupMesto['num_posev_olimp']=14;
        if ($cnt_people==9) $aGroupMesto['num_posev_olimp']=9;
        if ($cnt_people==11) $aGroupMesto['num_posev_olimp']=11;
        if ($cnt_people==11 && $mestowork==3) $aGroupMesto['num_posev_olimp']=10;
        if ($cnt_people==10 && $mestowork==3 && $aGroupMesto['num_posev_olimp']==11) $aGroupMesto['num_posev_olimp']=9;
        if ($cnt_people==10 && $mestowork==3 && $aGroupMesto['num_posev_olimp']==12) $aGroupMesto['num_posev_olimp']=10;
        // if ($cnt_people==10 && $mestowork==2 ) $aGroupMesto['num_posev_olimp']=9;
        if ($aGroupMesto['num_posev_olimp']>$cnt_people)
        {
            $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where etap_id='.$etap_id.' order by num_posev_olimp';
            $NoMests = db_list($sql);
            $mestoPrav=1;
            foreach ($NoMests as $mesto)
            {
                if ($mesto['num_posev_olimp']!=$mestoPrav){$aGroupMesto['num_posev_olimp']=$mestoPrav; s($mestoPrav); break; } 
                $mestoPrav++;
            }
        }
    }*/
    $num_posev_olimp = $aGroupMesto['num_posev_olimp'];
    $groups_pred = ',groups_pred='.$aGroupMesto['groups'].', 
    grp_num_pred='.(!empty($aGroupMesto['grp_mesto']) ? $aGroupMesto['grp_mesto'] : $aGroupMesto['grp_num']).',mesto_all_pred= '.$aGroupMesto['mesto_all'].',etap_id_pred='.$aGroupMesto['etap_id'];
    
    $where = 'turnir_id='.$aGroupMesto['turnir_id'].',player_id='.$player_id.',etap_id='.$etap_id.',
    num_posev_olimp='.$num_posev_olimp. $groups_pred; 
      //   s('$where'.$where) ;
    $sql ='insert into '.T_ETAPS_PLAYER_MESTA.'  SET '.$where  ;
    db_query($sql);
}

}
// заполняем спорстменов или по рейтингу или по зянятых мест этапа группы
function setGroupsEtapPlayers($form,$turnir_id,$etap_id,$updateGrp=0)
{
     $istochnik_posev=!empty($form['istochnik_posev']) ? $form['istochnik_posev'] : 0; 
    //s($istochnik_posev);
    // сколько игроков будет участовать в данном этапе, если 0 то все игроки турнира
    $cnt_people=!empty($form['cnt_people']) ? $form['cnt_people'] : 0; 
    $cnGrp=!empty($form['cnt_grp']) ? $form['cnt_grp'] : 1;
   // s('$cnGrp='.$cnGrp);
    //if (!empty($turnir_id))
    //{
    //проверим если есть хоть 1 результат то удалять ничего нельзя по этому этапу
    // $sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0';
    //$cn_results=db_field($sql,'cn');
    //if ($cn_results==0) 
    //{
    $aVariants = array();    
    if ($cnGrp==0 && !empty($form['group_id']))
    {
       // $sql = 'select  v.* from '.T_ETAPS.' t, '.T_TURNIR_VARIANTS.' v where  t.id='.$etap_id.' and v.id=group_id ';
        $sql = 'select  v.* from '.T_TURNIR_VARIANTS.' v where  v.id= '.$form['group_id'];
     //  s($sql);
        $aVariants = db_row($sql);
        $cnGrp = $aVariants['cntGroups'];
    }
  //  s('$cnGrp1='.$cnGrp);
    $sql = 'select t.is_reiting,t.is_reiting_w,t.group_id_old from '.T_TURNIRS.' t where t.id='.$turnir_id;
    $aTurnirSetting = db_row($sql);

    //s($sql);
    //s($aVariants);
   
    //************ єто старая логика добавления нового игрока, доработаем по другому позже
    ///$group_id_old = $aVariants['group_id_old'];
    /// нужно добавить обарботку для таблиц вид посевапо рейтнгу Лигас или клуба 
    $is_reiting = $aTurnirSetting['is_reiting'] ? $aTurnirSetting['is_reiting'] : $aTurnirSetting['is_reiting_w'];
    
    //************ єто старая логика добавления нового игрока, доработаем по другому позже
    ///if ($group_id_old==0) {
    if ($updateGrp==0)
    {    
    if ($istochnik_posev==0) // если это источник игроков все игроки (для первого этапа в основоом)) 
    {  
        $sql = 'SELECT  '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit,
        tp.is_command_num, tp.id as turn_id, p.*  
        FROM `'.T_TURNIR_PLAYERS.'` tp,'.T_PLAYERS.' p where turnir_id='.$turnir_id.' and p.id=tp.player_id 
        ORDER BY is_command_num, 1 desc,2 desc '. ($cnt_people>0 ? ' limit '.$cnt_people : '');
        //$aPlayers = db_list($sql);
    }else
    {
        $cnt_players_etaps = $form['cnt_people'];
        $mesto_from = $form['mesto_from']-1;
        //  $etap_id=$istochnik_posev;
        ////----------здесь нужно добавить боллее сложную логику выбрать места согласна занятых мест в группах 
        // с возможным определением дополнительных мест по рейтингу если установлена такаая опция по турниру или по этапу
        $sql='SELECT e.*,p.name,p.id FROM `'.T_ETAPS_PLAYER_MESTA.'` e, `'.T_PLAYERS.'` p 
        where e.player_id=p.id and etap_id='.$istochnik_posev.' order by mesto_all limit '.$mesto_from.','.$cnt_players_etaps;
     }
     }
     else
     {
        $sql='SELECT '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit, p.*  
         FROM `'.T_ETAPS_PLAYER_MESTA.'` e, `'.T_PLAYERS.'` p 
        where e.player_id=p.id and etap_id='.$etap_id.' ORDER BY 1 desc, 2 desc  ';
   
     }
  //   s($sql);
    $aPlayers = db_list($sql);
    //s($aPlayers);
    
    $zmeyka = 1; $n=1; $numPl=1;
    $plAll=0;
     ///if ($group_id_old==0) {
    // пройдемся по всем игрокам и заполним группы по рейтингу змейкой
 //  s($aPlayers);
    foreach ($aPlayers as $por => $player) 
    {//s('$cnGr$np='.$n);
        $groups_pred = ($istochnik_posev==0) ? '' :',groups_pred='.$player['groups'].', grp_num_pred='.$player['grp_num'].',mesto_all_pred= '.$player['mesto_all'].',etap_id_pred='.$player['etap_id'];
        $sug = true;
        $player['id']= (($istochnik_posev==0) or (!empty($player['grp_mesto']))) ? $player['id'] : 0;
        $plAll++;
        if ($n<1) $n=1; 
        if ($n>$cnGrp) $n=$cnGrp; 
        //  s('$cnGr$np='.$n);
        // змейка в прямом порядке
        if ($sug && $zmeyka==1 && $n<=$cnGrp) 
        {
            $where = 'turnir_id='.$turnir_id.',player_id='.$player['id'].',etap_id='.$etap_id.',is_command_num="'.$player['is_command_num'].'",
            groups='.$n.',grp_num='.$numPl.',mesto_all='.$plAll. $groups_pred; 
              //   s($where) ;
            if ($updateGrp==0)  
            $sql ='insert into '.T_ETAPS_PLAYER_MESTA.'  SET '.$where  ;
            else
            $sql ='update '.T_ETAPS_PLAYER_MESTA.'  SET '.$where .' where player_id='.$player['id'].' and etap_id='.$etap_id ;
            
            db_query($sql);
           //   s('DOooooo');
          //    s($sql);
            if ($n==$cnGrp) 
            {
            $zmeyka=2; $numPl++; $sug=0;
            }
            $n++;
        }
        // змейка в обратном  порядке
        if ($sug && $zmeyka==2 && $n>=1) 
        {
               // новая логика 03,12,20  
            $where = 'turnir_id='.$turnir_id.',player_id='.$player['id'].',etap_id='.$etap_id.',is_command_num="'.$player['is_command_num'].'",
            groups='.$n.',grp_num='.$numPl.',mesto_all='.$plAll.$groups_pred ; 
            //  s($where) ;  
            if ($updateGrp==0)  
            $sql ='insert into '.T_ETAPS_PLAYER_MESTA.'  SET '.$where  ;
            else
            $sql ='update '.T_ETAPS_PLAYER_MESTA.'  SET '.$where .' where player_id='.$player['id'].' and etap_id='.$etap_id ;
        
          db_query($sql);
           // s('POSLE');
           //    s($sql);
            if ($n==1) 
            {
                 $zmeyka=1; $numPl++; $sug=0;
            }
            $n--;
        }
    } 
}
function getCntGameInGroup($cntPlayers)
{
    $n=0;
     for ($i = 1; $i < $cntPlayers; $i++) 
     {
        $n = $n + $i;
     }  
     return $n;
}
//  добавить порядок игр в таблицу для не существующегго варианта таблицы
function setPoryadokGame($aVariants_people)
{
    $virtual_people = $aVariants_people;
    $isPar=1;
    // проверяем парное или не парное количество участников
   if ($aVariants_people % 2 != 0) 
   {
     $virtual_people++; 
     $isPar=0;  
   } 
   $etaps  =$virtual_people-1; //  сколько кругов для данного вида групп
   $game_etap = $virtual_people / 2; // сколько игр в круге 
   $first_player = $virtual_people; //с кем играет первый сеяный в данном круге
   $this_etap=1;
   $aGamePoryadok=array();
   while ($this_etap<=$etaps)
   {    $game_etap_this=1;
        $this_first_player=$first_player;
        // идем вниз по вторым номерам
        while($game_etap_this<=$game_etap)
        {
            if ($game_etap_this==1) 
            {
                
                    $aGamePoryadok[$this_etap][$game_etap_this]['second_player']=$first_player; 
                    $aGamePoryadok[$this_etap][$game_etap_this]['fisrt_player']=1;  
                
            }else
            {
                $this_first_player--;
                // если в последних этапах уже меньше 2 игрока то присвавем макимального игрока
                if ($this_first_player<2) $this_first_player=$virtual_people;
               $aGamePoryadok[$this_etap][$game_etap_this]['second_player']=$this_first_player; 
           
            }
            $game_etap_this++;
        }
        $game_etap_this=$game_etap;
        // идем вверх по первым номерам
        while($game_etap_this>=2)
        {
               $this_first_player--;
                // если в последних этапах уже меньше 2 игрока то присвавем макимального игрока
                if ($this_first_player<2) $this_first_player=$virtual_people;
               $aGamePoryadok[$this_etap][$game_etap_this]['fisrt_player']=$this_first_player; 
           
            $game_etap_this--;
        }
        $first_player--;
        $this_etap++;
   }
   $num=1; // порядок игр
   // записуем в бд данный порядок
   foreach($aGamePoryadok as $etap => $aGames)
   {
      foreach ($aGames as $aGame)
      {
        if ($isPar==1 || ($aGame['fisrt_player']!=$virtual_people && $aGame['second_player']!=$virtual_people))
        {
         $where = 'num='.$num.',players='.$aVariants_people.',
         krug='.$etap.',
            play1='.$aGame['fisrt_player'].',
            play2='.$aGame['second_player']; 
            
            $sql ='insert into '.T_GROUP_PORYADOK.'  SET '.$where  ;
            db_query($sql);
            $num++;
         }
        
      }
   }
   $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$aVariants_people; 
    $aVarGrp_= db_list($sql);
    return $aVarGrp_;
   //s($aGamePoryadok);
  //s('$aVariants_people='.$aVariants_people); 
}
// заполняем порядок игр для этапа группы
function setGroupsEtap($form,$turnir_id,$etap_id)
{
    s($form);
    // сколько игроков будет участовать в данном этапе, если 0 то все игроки турнира
    $cnt_people=!empty($form['cnt_people']) ? $form['cnt_people'] : 0; 
    $cnGrp=!empty($form['cnt_grp']) ? $form['cnt_grp'] : 1;
    $aVariants = array();    
  /*  if ($cnGrp==0)
    {
        $sql = 'select  v.* from '.T_ETAPS.' t, '.T_TURNIR_VARIANTS.' v where  t.id='.$etap_id.' and v.id=group_id ';
        $aVariants = db_row($sql);
        $cnGrp = $aVariants['cntGroups'];
    }*/
  
    if ($cnt_people % $cnGrp ==0 ) // деление без остатка
    {
      $aVariants_people1 =  $cnt_people / $cnGrp;
      $aVariants_people2 =  0;
    }else
    {
        $aVariants_people1 =  ceil($cnt_people / $cnGrp);
        $aVariants_people2 =  floor($cnt_people / $cnGrp);
      
    }
    //  s($aPlayers);
  
    //заполним порядок игр согласно таблиц поряядка в группах
    
    //*************** какой то дубляж закоментил повторный запрос выше уже есть данны в масиве
    //$sql = 'SELECT  v.* from bs_turnirs t,bs_turnirs_variants v where v.id=t.group_id and  t.id='.$turnir_id;
    //$aVariants = db_row ($sql);
    //$aVarGrp2=array();
    $sql = 'SELECT * FROM `bs_group_poryadok` p where p.players='.$aVariants_people1; 
    $aVarGrp_= db_list($sql);
    if (empty($aVarGrp_)) $aVarGrp_ = setPoryadokGame($aVariants_people1);
    
    $aVarGrp[$aVariants_people1]= $aVarGrp_;
    if ($aVariants_people2>0) 
    {
        $sql = 'SELECT * FROM `bs_group_poryadok` p where p.players='.$aVariants_people2; 
        $aVarGrp_ = db_list($sql);  
        if (empty($aVarGrp_)) $aVarGrp_ = setPoryadokGame($aVariants_people2);
    
        $aVarGrp[$aVariants_people2] = $aVarGrp_;  
    }
    ///******стараЯ логика 
    //$sql = 'SELECT groups,count(grp_num) as cnPlayer FROM `bs_turnirplayers` tp where turnir_id='.$turnir_id.' GROUP BY groups ORDER BY 2 asc,groups';
    $sql = 'SELECT groups,count(grp_num) as cnPlayer FROM `'.T_ETAPS_PLAYER_MESTA.'` tp where etap_id='.$etap_id.' 
    and turnir_id='.$turnir_id.' GROUP BY groups ORDER BY 2 asc,groups';
    $aPoryadPlayer= db_list($sql);
    //s($sql);
    $cn = count($aPoryadPlayer);
    $aVarianPoryadPlayers=array();
    $aKoff = array();
    $koff_=0;
    $prior=0;
    //s($aPoryadPlayer);
    //s($aVarGrp);
    $cntGames = 0;
    // определим количесвто игр
    foreach ($aPoryadPlayer  as $n => $group )
    {
      $cntGames = $cntGames + getCntGameInGroup($group['cnPlayer']);  
    }
  //  s('$cntGames='.$cntGames);
    foreach ($aPoryadPlayer  as $n => $group )
    { //s(getCntGameInGroup($group['cnPlayer']));
        $koff=round($cntGames/getCntGameInGroup($group['cnPlayer']),2);
        if ($koff_<>$koff) {$koff_=$koff; $prior++;}
        // s($group['groups']);
        $aVarianPoryadPlayers[$group['groups']] = $aVarGrp[$group['cnPlayer']];  
        $aKoff[$group['groups']]['koff'] = $koff;
        $aKoff[$group['groups']]['koff_okr'] = round($koff,0);
        $aKoff[$group['groups']]['koff_sum'] = $koff;
        $aKoff[$group['groups']]['now'] = 0;
        $aKoff[$group['groups']]['elem'] = 0;
        $aKoff[$group['groups']]['prior'] = $prior;
        $aKoff[$group['groups']]['max_game'] = getCntGameInGroup($group['cnPlayer']);
        if ($cn-1==$n && $prior>1) $aKoff[$group['groups']]['prior'] = $prior+1;
    }
    $aPravPoryadok=array();
    $this_elem=0;
    if ($prior==1) $kf=0; else $kf=1;
    // пройдемся по всем играм
    for ($i = 1; $i <= $cntGames; $i++) 
    {
        //    s('game='.$i);
        // маленький проход по массиву где есть кофф и текущие маркеры
        $sug=1;
        foreach ($aKoff as $grpN => $elem)
        {
            if ($elem['prior']==1) 
            { // приоритет наибольшие группы
                // s($elem['elem'].' por1 '.$grpN.' '.$elem['now'].' $elem[koff_okr]='.$elem['koff_okr']);
                if ($elem['now']==0 || ($elem['now']-$kf)==$elem['koff_okr'])  
                { // если это 1 элемент или больше коффа
                    if ($sug) 
                    {
                        $sug=0;
                        if ($elem['now']<>0) 
                        {
                            $aKoff[$grpN]['koff_sum']=$elem['koff_sum']+$elem['koff'];  
                            $aKoff[$grpN]['koff_okr']=round($aKoff[$grpN]['koff_sum'],0);  
                        }
                        $aKoff[$grpN]['now']++;
                        if (!empty($aVarianPoryadPlayers[$grpN][$elem['elem']]['play1']))
                        {
                            $aPravPoryadok[] = array('group' => $grpN,'krug'=>  $aVarianPoryadPlayers[$grpN][$elem['elem']]['krug'] , 'play1' => $aVarianPoryadPlayers[$grpN][$elem['elem']]['play1'], 'play2' =>$aVarianPoryadPlayers[$grpN][$elem['elem']]['play2'] );
                            $aKoff[$grpN]['elem'] ++;      
                        }
                        else
                        {
                            $sug=1;
                            $elem['prior']=2;
                        }
                    }
                    // nдля других єлеентов 
                } 
                else 
                    if ($elem['now']<>0) $aKoff[$grpN]['now'] ++; 
            }
            if ($elem['prior']>1 && $sug) 
            { // приоритет  меньшие группы
                if ($elem['elem']==$this_elem)  
                {
                    $sug=0;
                    $aPravPoryadok[] = array('group' => $grpN, 'krug'=>  $aVarianPoryadPlayers[$grpN][$elem['elem']]['krug'] , 'play1' => $aVarianPoryadPlayers[$grpN][$elem['elem']]['play1'], 'play2' =>$aVarianPoryadPlayers[$grpN][$elem['elem']]['play2'] );
                    $aKoff[$grpN]['elem'] ++;      
                    if ($elem['prior']==3) $this_elem++;
                }    
            }
         } 
    }
    // закончили цикл c порядком игр
   //exit;
    
    $sql = 'SELECT tp.is_command_num, tp.id as turn_id, p.name,p.id as play_id,tp.groups,tp.grp_num,tp.grp_mesto ,case when reiting>0 then reiting else start_reiting end as beg_reit  
    FROM `'.T_ETAPS_PLAYER_MESTA.'` tp,'.T_PLAYERS.' p where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and p.id=tp.player_id 
    ORDER BY is_command_num,tp.groups,tp.grp_num';
    $aPlayers=db_list($sql);
   // s($sql);
    $aGroups = array();
    $a=1;
    $aTemp=array();
    foreach ($aPlayers as $player) 
    {
        
        if ($player['groups']<>$a ) 
        {
            $aGroups[$player['groups']-1] =$aTemp;   
            $aTemp=array();
        }
        $aTemp[$player['grp_num']]=$player; 
        $a=$player['groups'];    
    }
    if (!empty($player['groups']))
    $aGroups[$player['groups']] =$aTemp;   
    $aTemp=array();
   // s($aPravPoryadok);
  //  s($aGroups);
    
  
        
        // пройдемся по порядку игр и запоним нужный порядок
        foreach ($aPravPoryadok as $num => $playThis) {
            $pl_id_1 = !empty($aGroups[$playThis['group']][$playThis['play1']]['play_id']) ? $aGroups[$playThis['group']][$playThis['play1']]['play_id'] : 0;
            $pl_id_2 = !empty($aGroups[$playThis['group']][$playThis['play2']]['play_id']) ? $aGroups[$playThis['group']][$playThis['play2']]['play_id'] : 0;
            $rt_id_1_beg = !empty($aGroups[$playThis['group']][$playThis['play1']]['beg_reit']) ? $aGroups[$playThis['group']][$playThis['play1']]['beg_reit'] : 0;
            $rt_id_2_beg = !empty($aGroups[$playThis['group']][$playThis['play2']]['beg_reit']) ? $aGroups[$playThis['group']][$playThis['play2']]['beg_reit'] : 0;
            // если это командые игры
            $command_num1 = !empty($aGroups[$playThis['group']][$playThis['play1']]['is_command_num']) ? $aGroups[$playThis['group']][$playThis['play1']]['is_command_num'] : 0;
            $command_num2 = !empty($aGroups[$playThis['group']][$playThis['play2']]['is_command_num']) ? $aGroups[$playThis['group']][$playThis['play2']]['is_command_num'] : 0;
          //  s('$pl_id_1=' . $pl_id_1 . ' $command_num1=' . $command_num1 . ' $pl_id_2=' . $pl_id_2 . ' $command_num2=' . $command_num2);
            $group_num = $playThis['group'];
            $pl_num_grp1 = $playThis['play1'];
            $pl_num_grp2 = $playThis['play2'];;
            $prim = 'Група ' . $group_num . ' ' . ' (коло ' . $playThis['krug'] . ')';//
            // если командные и игроки не с одной группы
            if (($command_num1 == 0 && $command_num2 == 0) || ($command_num1 > 0 && $command_num1 != $command_num2))
            {
                if (!empty($pl_num_grp1) && !empty($pl_num_grp2)){
                    $where = 'turnir_id='.$turnir_id.',pl_id_1='.$pl_id_1.',pl_id_2='.$pl_id_2.',etap_prim="'.$prim.'",
            rt_id_1_beg='.$rt_id_1_beg.',rt_id_2_beg='.$rt_id_2_beg.',group_num='.$group_num.',pl_num_grp1='.$pl_num_grp1.'
            ,pl_num_grp2='.$pl_num_grp2.',type_game=1, etap_id='.$etap_id.', auto=1';

                    $sql ='insert into '.T_REITING.'  SET '.$where  ;
                    db_query($sql);
                }
            }


            //  s('sqqqqql='.$sql);
        } 
    // обработка переноса игр
    $sql = 'select type_etap, (SELECT type_etap FROM bs_etaps_work e WHERE e.id=w.istochnik_posev) AS ist_type_etap from '.T_ETAPS.
        ' w where id = '.$etap_id;
    $aEtap = db_row($sql);
    $type_etap = $aEtap['type_etap'];
    $ist_type_etap = !empty($aEtap['ist_type_etap']) ?  $aEtap['ist_type_etap'] : 0;
    // если предыдущий этап группа и текущий этап группа то длеаем ветер, а и если поставили птичку переноса игр...
    if ($type_etap==1 && $ist_type_etap==1 and $form['is_perenos']>0)
    {
        setPernosGamesFromIstochn($form,$turnir_id,$etap_id);
    }

}
// переносим сыграные игры с предыдущего этапа
function setPernosGamesFromIstochn($form,$turnir_id,$etap_id)
{
    // пройдемся по всех играх этого этапа где есть пара живых игроков
    $sql='select * from '.T_REITING.' where etap_id='.$etap_id.' and pl_id_1>0 and pl_id_2>0';
    $aGamesThisEtap = db_list($sql);
    // найдем сыграные игры в предедущем этапе
    $sql='select * from '.T_REITING.' where etap_id='.$form['istochnik_posev'].' and pl_id_1>0 and pl_id_2>0';
    $aGamesPredEtap = db_list($sql);
    // пройдемся по масиву и запишем ключи в интерсеном формате , для быстрого поиск игр
    $aGamesPredEtapItog=[];
    if (!empty($aGamesPredEtap))
    foreach ($aGamesPredEtap as $predGame)
    {
       if ($predGame['pl_id_1']<$predGame['pl_id_2']) $key=$predGame['pl_id_1'].'-'.$predGame['pl_id_2'];
       else  $key=$predGame['pl_id_2'].'-'.$predGame['pl_id_1'];
        $aGamesPredEtapItog[$key] = $predGame;
    }
  // s($aGamesPredEtapItog);
   //пройдемся по играх в этом этапе и поищем игры в предыдущем , если найдем то запишем
    if (!empty($aGamesThisEtap))
    foreach ($aGamesThisEtap as $aGame)
    {
        if ($aGame['pl_id_1']<$aGame['pl_id_2']) $key=$aGame['pl_id_1'].'-'.$aGame['pl_id_2'];
        else  $key=$aGame['pl_id_2'].'-'.$aGame['pl_id_1'];
        //если нашли игру в предыдущем этапе групп переносим
          if (!empty($aGamesPredEtapItog[$key]))
          {   // если игрок 1 совпал с игроком 1 предыдущего этапа
              if ($aGamesPredEtapItog[$key]['pl_id_1']==$aGame['pl_id_1'])
              {
                  $setSql = 'set_1="'.$aGamesPredEtapItog[$key]['set_1'].'",set_2="'.$aGamesPredEtapItog[$key]['set_2'].'"
            ,win_player='.$aGamesPredEtapItog[$key]['win_player'].',
            lose_player='.$aGamesPredEtapItog[$key]['lose_player'].',
             perenos_etap='.$form['istochnik_posev'].'';
            }
              if ($aGamesPredEtapItog[$key]['pl_id_1']==$aGame['pl_id_2'])
              {
                  $setSql = 'set_1="'.$aGamesPredEtapItog[$key]['set_2'].'",set_2="'.$aGamesPredEtapItog[$key]['set_1'].'"
            ,win_player='.$aGamesPredEtapItog[$key]['win_player'].',
            lose_player='.$aGamesPredEtapItog[$key]['lose_player'].',
             perenos_etap='.$form['istochnik_posev'].'';
              }
              $sql ='update '.T_REITING.'  SET '.$setSql. ' where id='.$aGame['id']  ;
             // s($sql);
              db_query($sql);
              // ЗАПИШЕМ СЕТИ И ОЧКИ
              setOchkiSetsForGrp($aGamesPredEtapItog[$key]['win_player'],$aGamesPredEtapItog[$key]['lose_player'],$etap_id,$turnir_id);


          }

    }
}

function setOchkiSetsForGrp($win,$lose,$etap_id,$turnir_id)
{
    $sql = ' select * from '.T_REITING. ' r where (r.lose_player='.$win.' or r.win_player='.$win.')  and etap_id='.$etap_id.' and  r.turnir_id='.$turnir_id;
    $aWin = db_list($sql);
    //s($sql);
    // посчитаем к-во побед и сетов
    $win_set=0;
    $lose_set=0;
    $win_game=0;
    $lose_game=0;
    foreach ($aWin as $rec)
    {
        // когда игрок выигрывал
        if ($rec['win_player']==$win)
        {

            $is_L=1;
            if ($rec['pl_id_1']==$win)
            {
                if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_1'];
                $lose_set=$lose_set+$rec['set_2'];
            }else
            {
                $win_set=$win_set+$rec['set_2'];
                $lose_set=$lose_set+$rec['set_1'];
            }
            if ($is_L>0)   $win_game++;
        }
        //когда игрок проигрывал
        if ($rec['lose_player']==$win)
        {
            $is_L=1;
            if ($rec['pl_id_1']==$win)
            {
                if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_1'];
                $lose_set=$lose_set+$rec['set_2'];
            }else
            {
                if ($rec['set_2']=='L') {$rec['set_2']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_2'];
                $lose_set=$lose_set+$rec['set_1'];
            }
            if ($is_L>0)  $lose_game++;
        }
    }
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // выигравший игшрок
    $sql ='update  '.T_ETAPS_PLAYER_MESTA.' set `grp_ochki`='.($win_game*2+$lose_game).', grp_win_set='.$win_set.', 
    grp_lose_set='.$lose_set.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and player_id='.$win;

    /*
     $sql ='update  '.T_TURNIR_PLAYERS.' set `grp_ochki`='.($win_game*2+$lose_game).', grp_win_set='.$win_set.',
      grp_lose_set='.$lose_set.' where turnir_id='.$turnir_id.' and player_id='.$win;
    */
    db_query($sql);
//проигравший игрок
    $sql = ' select * from '.T_REITING. ' r where (r.lose_player='.$lose.' or r.win_player='.$lose.') and type_game=1 and etap_id='.$etap_id.' and r.turnir_id='.$turnir_id;
    $aLose = db_list($sql);
    // посчитаем к-во побед и сетов
    $win_set=0;
    $lose_set=0;
    $win_game=0;
    $lose_game=0;
    foreach ($aLose as $rec)
    {
        // когда игрок выигрывал
        if ($rec['win_player']==$lose)
        {

            $is_L=1;
            if ($rec['pl_id_1']==$lose)
            {
                if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_1'];
                $lose_set=$lose_set+$rec['set_2'];
            }else
            {
                $win_set=$win_set+$rec['set_2'];
                $lose_set=$lose_set+$rec['set_1'];
            }
            if ($is_L>0)  $win_game++;
        }
        //когда игрок проигрывал
        if ($rec['lose_player']==$lose)
        {
            $is_L=1;
            if ($rec['pl_id_1']==$lose)
            {
                if ($rec['set_1']=='L') {$rec['set_1']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_1'];
                $lose_set=$lose_set+$rec['set_2'];
            }else
            {
                if ($rec['set_2']=='L') {$rec['set_2']=0; $is_L=0;}
                $win_set=$win_set+$rec['set_2'];
                $lose_set=$lose_set+$rec['set_1'];
            }
            if ($is_L>0)  $lose_game++;
        }
    }
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $sql ='update  '.T_ETAPS_PLAYER_MESTA.' set `grp_ochki`='.($win_game*2+$lose_game).', grp_win_set='.$win_set.', 
    grp_lose_set='.$lose_set.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and player_id='.$lose;
    db_query($sql);

}
?>