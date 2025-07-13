<?php
//include_once 'func.etapresult_2x_minus.php';



 function all_results_2xminuska ($etap_id,$turnir_id)
 {
     global $aVariants2minuska_16,$aVariants2minuska_8;
      $aResultsNEW=array();
      //получаем результаты 
  /*  $sql='SELECT (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_1) as name1,
                 (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_2) as name2,
                   case when r.pl_id_1>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_1 and
                   etap_id='.$etap_id.') else 0 end as mesto_all_1,
                 case when r.pl_id_2>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_2 and etap_id='.$etap_id.') else 0 end as mesto_all_2,

     r.* FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=2 
    order by olimp16_num;';*/
     // получаем отсортированных людей по местам в группе
     $sql = 'select (select  p.name from  '.T_PLAYERS.' p where p.id=e.player_id) as name,
      player_id,num_posev_olimp from '.T_ETAPS_PLAYER_MESTA.' e where num_posev_olimp>0  and  etap_id='.$etap_id.
         '  order by num_posev_olimp';
     $aPlayersGrp = db_list($sql);
     $cnt_people = count ($aPlayersGrp);
     $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
    //s($aPlayersGrp);
    // пройдемся по всем резульаьам обработаем
    foreach ($aPlayersGrp as $aPlayer)
    {
        // определяем номер игры данной пары
        list($num,$playNum,$Player2) = get_num_game_pars_one($aPlayer['num_posev_olimp'],$aVariants);
       // s('$num='.$num.' $playNum='.$playNum.' $Player2='.$Player2);
        if ($playNum==1)
        {
            $aPlayer['mesto_all_1'] = $aPlayer['num_posev_olimp'];
            $aPlayer['pl_id_1'] = $aPlayer['player_id'];
            $aPlayer['name1'] = $aPlayer['name'];
            $aPlayer['mesto_all_1'] = $aPlayer['num_posev_olimp'];
        }
        if ($playNum==2)
        {
            $aPlayer['mesto_all_2'] = $aPlayer['num_posev_olimp'];
            $aPlayer['pl_id_2'] = $aPlayer['player_id'];
            $aPlayer['name2'] = $aPlayer['name'];
            $aPlayer['mesto_all_2'] = $aPlayer['num_posev_olimp'];
        }
//s($aPlayer);
     // $aResultsNEW[$aPlayer['num_posev_olimp']] = $aPlayer;
        if (!empty($aResultsNEW[$num]))
        {
            $aResultsNEW[$num]  =array_merge($aResultsNEW[$num],$aPlayer);
        }else
          $aResultsNEW[$num] = $aPlayer;
     // s($aResultsNEW);


    }
  //  s($aResultsNEW);
    return $aResultsNEW;
 }


   // пройтись по таблицам всем
  function all_tables($etap_id,$turnir_id,$aResults)
  {
      $sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,player_id
 FROM bs_turnirplayers tp  where  turnir_id='.$turnir_id.' 
ORDER BY reiting_ukraine desc, beg_reit desc';
      $aPlayers_ALL = db_list($sql);


$sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
tp.id as turn_id,
tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  turnir_id='.$turnir_id.' and etap_id='.$etap_id.'
ORDER BY tp.groups,tp.grp_num';
$aPlayers = db_list($sql);
//s($aPlayers);
// поищем несеяных игроокрв
      $ANoPlayerSeyan =[];
      foreach ($aPlayers_ALL as $Play)
      {
       //   s($Play);
          $found_key = array_search($Play['player_id'], array_column($aPlayers, 'player_id'));
          //если игрок на этом этапе незадействован тогдла его в не сеяные добвим
          if ($found_key===false) {
              $ANoPlayerSeyan[]=$Play;
          }
      }
//s($sql);
if (!empty($aPlayers)){
$aGroups = array();
$a=1;
$aTemp=array();
foreach ($aPlayers  as $k=> $player) 
{
   $player['name'] = $player['grp_mesto']==0 && $player['player_id']==0 ? 'Група '.$player['groups_pred']. ' місце '.$player['grp_num_pred'] : $player['name'];
   $player['beg_reit'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['beg_reit'];
   $player['reiting_ukraine'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['reiting_ukraine'];
 //  s($player['groups']);
  if ($player['groups']>0 && $player['groups']<>$a ) {
    $aGroups[$player['groups']-1] =$aTemp;   
    $aTemp=array();
  }
  if ($player['groups']>0)
  {
      $aTemp[$player['grp_num']]=$player; 
      $a=$player['groups'];  
  }  
} 

    $aGroups[$player['groups']] =$aTemp;   
    $aTemp=array();
 //s($aGroups);
    /*<style>
         @import url("css/print.css?ver=1");
         </style>*/
    $Tables_content='   <div class="print_group">
         <div class="Section1"> 
         <div class="group_table_etap_container row mt-3">
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

        $ConTable =  table($aPlay,$aPlayers,$ANoPlayerSeyan);
    
    $str_add_player='';
    if (!empty($Cnt_new) && $minGrp==$cnGrp)
    {
        
        $str_add_player = '<span style="width:20px;cursor: pointer;" id="per_v_rozdel" module="etaps" action="add_playergrp" post_string="&etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp. '"  return_content_bool="" blok="" class="ajax_send" field_result="player_id" field_result_name="name" wintype="1" width_="500"><img width="35px" alt="Додати" title="Додати" src="../../../../img/slug_small/add.png" border="0"></span>';
    }
   $Tables_content .=' 
 
 <div class="col-md-6 col-lg-4 col-xl-3 ng-star-inserted  group_table_etap_blok">
  <span class="zagolovokGrp"> Група '.$grp.'</span> '.$str_add_player .$ConTable .'
  </div>';
 } 
 $Tables_content .='
 
</div>
</div>
  
  ';
  return $Tables_content;
  }
  }
  function table($aPlay,$allPlayers=[],$ANoPlayerSeyan=[])
 { 
  //  s($aPlay);
   // s($aResults);
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
    $content = '  <table class="group_table_etap ">
  <tr>
  <th>№</th>
    <th class="fio">ПІБ</th>';
  $content .= '
  </tr>';  
  
  foreach ($aPlay as $n => $aPl)   
  {
      $id_select = 'PlayeridGrp_'.$aPl['player_id'].'_'.$aPl['groups'].'_'.$aPl['grp_num'];
      $content .= '<tr>
      <td>'.$n.'</td>
      <td><div class="supper-wrapper">'.getSpisPlayerEdit($allPlayers,$aPl['player_id'],$ANoPlayerSeyan,$id_select).'</div></td>';

      //if ($cnt_players==$n) $content .='<td class="zach"></td>';
       $content .='
       </tr> ';
  }      
  
 $content .=  '
   </table>
 
 ';  
 return $content;
 }
 function getSpisPlayerEdit($aPlayers, $idPlayer,$ANoPlayerSeyan,$id_select)
 {
//s($aPlayers);
     $sSpisPlayer = '<select class="chosen-select " tabindex="5" name="player" id="'.$id_select.'">';
   if (!empty($ANoPlayerSeyan))
   {
       $sSpisPlayer.='<optgroup label="Не сіяні гравці">';
       foreach ($ANoPlayerSeyan as $player)
       {
           $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine'] ?
               ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
           $sSpisPlayer.='
        
		<option   value="'.$player['player_id'].'" >'.$player['name'].'<span class="f10">'.$strReiting.'</span> </option>';

       }
       $sSpisPlayer.=  '</optgroup>';
   }

     
     $sSpisPlayer .= '<optgroup label="Гравці сіяні на етапі">';
     foreach ($aPlayers as $player)
     {
        // s($player);
         $selected ='';
      //   s('$idPlayer='.$idPlayer);
         if ($idPlayer!=0)
            $selected= $player['player_id']==$idPlayer ? 'selected="selected"' : '';
         $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine']>0 ?
             ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
         if ($idPlayer==0)
         $sSpisPlayer.='
        <option selected="selected" id="opt_0'.'" value="0"></option>';

        $sSpisPlayer.='
        <option '.$selected.' id="opt_'.$player['player_id'].'" value="'.$player['player_id'].'">'.$player['name'].'<span class="f10">'.$strReiting.'</span></option>';

     }
     $sSpisPlayer.=  '</optgroup></select>';

    // s($sSpisPlayer);
     return $sSpisPlayer;
 }


?>