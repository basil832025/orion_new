<?php
function GamePara($numGame,$num1,$num2,$aResults_,$aPlayers,$ANoPlayerSeyan)
{$connent='';
    if (!empty($aResults_[$numGame])){
        $aResults=$aResults_[$numGame];
        $class_first = ''; $class_sec = '';
        $mesto_all_1 =!empty($aResults['mesto_all_1']) ? '<div class="t-grid-team__mesto">'.$aResults['mesto_all_1'].'</div>'  : '';
        $mesto_all_2 =!empty($aResults['mesto_all_2']) ?'<div class="t-grid-team__mesto">'.$aResults['mesto_all_2'].'</div>' : '';
        $html_num1 = !empty($num1) ?  '<div class="t-grid-team__logo">'.$num1.'</div>' : '';
        $html_num2 = !empty($num2) ? '<div class="t-grid-team__logo">'.$num2.'</div>': '';
        $first_div = '<div class="t-grid-team__score"></div>';
        $second_div = '<div class="t-grid-team__game"></div>';

        $work_game_css='';
        // идет игра на столе
     //   $work_game_css = $aResults['table_game']>0 ? 't-grid-team_table' : '';
    $pl_id_2='';$pl_id_1='';$name2='';$name1='';
    $row_id_2 = !empty($aResults['row_id_2']) ? (int)$aResults['row_id_2'] : 0;
    $row_id_1 = !empty($aResults['row_id_1']) ? (int)$aResults['row_id_1'] : 0;

    $pl_id_2 = !empty($aResults['pl_id_2']) ? $aResults['pl_id_2'] : 0;
  //  $id_select = 'PlayeridSetka_'.$pl_id_2.'_'.$num2;
  //  $name2 = getSpisPlayerEdit($allPlayers,$pl_id_2,$ANoPlayerSeyan,$id_select);
    $name2 = !empty($aResults['name2']) ?   $aResults['name2'] : '';
        $id_select2 = 'PlayeridSetka_'.$pl_id_2.'_'.$num2;

        $name2 = '<select class="chosen-select" tabindex="5" name="player_id_1" id="'.$id_select2.'" data-old-player="'.(int)$pl_id_2.'" data-mesto="'.(int)$num2.'" data-row-id="'.$row_id_2.'">';
        //<option value="'.$pl_id_2.'">'.$name2.'</option></select>
        if (!empty($ANoPlayerSeyan))
        {
            $name2.='<optgroup label="Не сіяні гравці">';
            foreach ($ANoPlayerSeyan as $player)
            {
                $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine'] ?
                    ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
                $name2.='
        
		<option   value="'.$player['player_id'].'" >'.$player['name'].'<span class="f10">'.$strReiting.'</span> </option>';

            }
            $name2.=  '</optgroup>';
        }
        $name2 .= '<optgroup label="Гравці сіяні на етапі">';
        foreach ($aPlayers as $player)
        {
            // s($player);
            $selected ='';
            //   s('$idPlayer='.$idPlayer);
            if ($pl_id_2!=0)
                $selected= $player['player_id']==$pl_id_2 ? 'selected="selected"' : '';
            $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine']>0 ?
                ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
            if ($pl_id_2==0)
                $name2.='
        <option selected="selected" id="opt_0'.'" value="0"></option>';

            $name2.='
        <option '.$selected.' id="opt_'.$player['player_id'].'" value="'.$player['player_id'].'">'.$player['name'].'<span class="f10">'.$strReiting.'</span></option>';

        }
        $name2.=  '</optgroup></select>';


    $pl_id_1 = !empty($aResults['pl_id_1']) ? $aResults['pl_id_1'] : 0;
        $id_select1 = 'PlayeridSetka_'.$pl_id_1.'_'.$num1;

        //         $name1 = $aResults['name1'];
   // $id_select = 'PlayeridSetka_'.$pl_id_1.'_'.$num1;
   // $name1 = getSpisPlayerEdit($allPlayers,$pl_id_1,$ANoPlayerSeyan,$id_select);
       // $name1 = !empty($aResults['name1']) ? $aResults['name1'] : '';
     //   $name1 = '<select class="chosen-select form-control">';//<option value="'.$pl_id_1.'">'.$name1.'</option></select>

        $name1 = '<select class="chosen-select" tabindex="5" name="player_id_2" id="'.$id_select1.'" data-old-player="'.(int)$pl_id_1.'" data-mesto="'.(int)$num1.'" data-row-id="'.$row_id_1.'">';
        //<option value="'.$pl_id_2.'">'.$name2.'</option></select>
        if (!empty($ANoPlayerSeyan))
        {
            $name1.='<optgroup label="Не сіяні гравці">';
            foreach ($ANoPlayerSeyan as $player)
            {
                $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine'] ?
                    ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
                $name1.='
        
		<option   value="'.$player['player_id'].'" >'.$player['name'].'<span class="f10">'.$strReiting.'</span> </option>';

            }
            $name1.=  '</optgroup>';
        }
        $name1 .= '<optgroup label="Гравці сіяні на етапі">';
        foreach ($aPlayers as $player)
        {
            // s($player);
            $selected ='';
            //   s('$idPlayer='.$idPlayer);
            if ($pl_id_1!=0)
                $selected= $player['player_id']==$pl_id_1 ? 'selected="selected"' : '';
            $strReiting =  !empty($player['beg_reit']) || $player['reiting_ukraine']>0 ?
                ' ('.$player['reiting_ukraine'].'-РФНТУ ) ('.$player['beg_reit'].'-РКлубу)' : '';
            if ($pl_id_1==0)
                $name1.='
        <option selected="selected" id="opt_0'.'" value="0"></option>';

            $name1.='
        <option '.$selected.' id="opt_'.$player['player_id'].'" value="'.$player['player_id'].'">'.$player['name'].'<span class="f10">'.$strReiting.'</span></option>';

        }
        $name1.=  '</optgroup></select>';



        $connent='<div>Пара '.$numGame.'<div class="t-grid-match_new _with-additional" data-match-label="A03">
<div class="t-grid-team_new '.$work_game_css.' '.$class_first.'">
'.$html_num1.'
<div class="t-grid-team__name_ " data-player="'.$pl_id_1.'"><div class="supper-wrapper">'.$name1.'</div></div>
    
</div>
<div class="t-grid-team_new '.$work_game_css.' '.$class_sec.'">
'.$html_num2.'
<div class="t-grid-team__name_" data-player="'.$pl_id_2.'"><div class="supper-wrapper">'.$name2.'</div></div>

</div>
</div>';
    }
    return $connent;
}

function show_2xMinuska8($etap_id,$turnir_id,$aResults)
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
tp.`groups` as `groups`,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  turnir_id='.$turnir_id.' and etap_id='.$etap_id.'
ORDER BY tp.`groups`,tp.grp_num';
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
    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=1");
    </style>
   
  <div class="content2xminus">  
    <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper">
                                    <div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">Гравці</span>
                         </button>
                    </div>
                                  
               </div>
   </div>
        
 <!--  веррхний финал  --!>       
<div >
<div class="t-grid-column_new _active">


' .GamePara(1,1,8,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(2,5,4,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(3,3,6,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(4,7,2,$aResults,$aPlayers,$ANoPlayerSeyan).'


</div>

</div>
</div>
 ';
    return $content;
}
function show_2xMinuska($etap_id,$turnir_id,$aResults)
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
tp.`groups` as `groups`,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  turnir_id='.$turnir_id.' and etap_id='.$etap_id.'
ORDER BY tp.`groups`,tp.grp_num';
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
    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=11");
    </style>
  <div class="content2xminus">  
    <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper">
                                    <div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">Гравці</span>
                         </button>
                    </div>
                                  
               </div>
   </div>
        
 <!--  веррхний финал  --!>       
<div >
<div class="t-grid-column_new _active">

' .GamePara(1,1,16,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(2,9,8,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(3,5,12,$aResults,$aPlayers,$ANoPlayerSeyan).'

'.GamePara(4,13,4,$aResults,$aPlayers,$ANoPlayerSeyan).'
'.GamePara(5,3,14,$aResults,$aPlayers,$ANoPlayerSeyan).'
'.GamePara(6,11,6,$aResults,$aPlayers,$ANoPlayerSeyan).'
'.GamePara(7,7,10,$aResults,$aPlayers,$ANoPlayerSeyan).'
'.GamePara(8,15,2,$aResults,$aPlayers,$ANoPlayerSeyan).'

</div>

</div>
    
</div>
 ';
    return $content;
}


?>
