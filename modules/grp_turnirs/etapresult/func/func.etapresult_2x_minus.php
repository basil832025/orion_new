<?php
function GamePara($numGame,$num1,$num2,$aResults_,$show_mesto=0,$noGame=0,$class='t-grid-match')
{
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? 1 : 0;
   // s('ispara='.$ispara);
    $ispara_class_match = !empty($ispara) ? ' t-grid-match_ispara ' : 't-grid-match';
    $connent='<div class="'.$ispara_class_match.' t-grid-match_noMatch _with-additional" data-match-label="A03">

</div>';
 //   if (!empty($aResults_[$numGame]))
    {
        $aResults=$aResults_[$numGame];
        $mesto1Show=true;
        $mesto2Show=true;

if (!empty($aResults['mesto_all_1']) && !empty($aResults['mesto_all_2']))
{
    if (abs($aResults['mesto_all_1']-$aResults['mesto_all_2'])>1)
    {
        if ($aResults['mesto_all_1']>$aResults['mesto_all_2'])
        {
            $mesto1Show=true;
            $mesto2Show=false;
        }else
        {
            $mesto1Show=false;
            $mesto2Show=true;

        }
    }
}
    if ($ispara){
        if (!empty($aResults['name1'])){
            list($firstFIO,$secondFIO) = explode('-',$aResults['name1']);
            $firstFIO = mb_strlen ($firstFIO)>17 ? full_name_to_short($firstFIO, 'A b.') : $firstFIO;
            $secondFIO = mb_strlen ($secondFIO)>17 ? full_name_to_short($secondFIO, 'A b.') : $secondFIO;
            $aResults['name1'] = $firstFIO.'-<br>'.$secondFIO;
        }
        if (!empty($aResults['name2'])){
            list($firstFIO,$secondFIO) = explode('-',$aResults['name2']);

            $firstFIO = mb_strlen ($firstFIO)>17 ? full_name_to_short($firstFIO, 'A b.') : $firstFIO;
            $secondFIO = mb_strlen ($secondFIO)>17 ? full_name_to_short($secondFIO, 'A b.') : $secondFIO;
            $aResults['name2'] = $firstFIO.'-<br>'.$secondFIO;
        }
    }
    $class_first = 'none_res'; $class_sec = '';$t_grid_team='';$class_noreult='';$t_grid_team_noreult =' t-grid-team_noreult ';
    $class_no_result = ' t-grid-match_noMatch ';
    $first_div='';
    $ispara_class = !empty($ispara) ? ' ispara ' : '';
    $ispara_class_name = !empty($ispara) ? ' t-grid-team__name_ispara ' : '';
    $mesto_all_1 = $show_mesto && !empty($aResults['mesto_all_1'])  && $mesto1Show ? '<div class="t-grid-team__mesto">'.$aResults['mesto_all_1'].'</div>'  : '';
    $mesto_all_2 = $show_mesto && !empty($aResults['mesto_all_2']) && $mesto2Show ?'<div class="t-grid-team__mesto">'.$aResults['mesto_all_2'].'</div>' : '';
    $html_num1 = !empty($num1) ?  '<div class="t-grid-team__logo">'.$num1.'</div>' : '';
    $html_num2 = !empty($num2) ? '<div class="t-grid-team__logo">'.$num2.'</div>': '';
        $second_div = ($noGame==0) ? '<div class="t-grid-team__game">'.$numGame.'</div>' : '';
        if ( $aResults['table_game']>0)
        {
            $first_div =    '<div  class="t-grid-team_table">'.$aResults['table_game'].'</div>';
            $second_div = ($noGame==0) ? '<div class="t-grid-team__game">0 : 0</div>' : '';
        }
      /*  else
            $first_div = !empty($aResults['name1']) && !empty($aResults['name2']) ? '<div class="t-grid-team__score">'.$aResults['set_1'].' : '.$aResults['set_2'].'</div>' : '';

*/
    if ($aResults['set_1']>$aResults['set_2']) 
    { $class_first = '_highlighted up'; $class_sec = '_lose down';$class_no_result='';$t_grid_team_noreult ='';
    //    $first_div = '<div class="t-grid-team__score">'.$aResults['set_1'].' : '.$aResults['set_2'].'</div>';
        $first_div = '<div class="t-grid-team__score">'.$aResults['set_1'].'</div>';
        $second_div = '<div class="t-grid-team__score">'.$aResults['set_2'].'</div>';
        $html_num1='';$html_num2='';
    }
    elseif  ($aResults['set_1']<$aResults['set_2']) {
        $class_first = '_lose up'; $class_sec = '_highlighted down';$class_no_result='';$t_grid_team_noreult ='';
       //  $second_div = '<div class="t-grid-team__score">'.$aResults['set_1'].' : '.$aResults['set_2'].'</div>';
        $first_div = '<div class="t-grid-team__score">'.$aResults['set_1'].'</div>';
        $second_div = '<div class="t-grid-team__score">'.$aResults['set_2'].'</div>';
//   $first_div  = '<div class="t-grid-team__game">'.$numGame.'</div>';
        $html_num1='';$html_num2='';
        } 
  // идет игра на столе

 /*   if (!empty($ispara)){
        if (!empty($aResults['name1']))
        $aResults['name1'] = str_replace('-','-<br>',$aResults['name1']);

        if (!empty($aResults['name2']))
           $aResults['name2'] = str_replace('-','-<br>',$aResults['name2']);
    }*/
        $class = !empty($ispara) ? $class.'_ispara ' : $class;
        //$work_game_css
    $connent='<div class="'.$class.$class_no_result.' _with-additional" data-match-label="A03">
<div class="t-grid-team  '.$ispara_class.$t_grid_team_noreult.' '.$class_first.'">
'.$html_num1.'
<div class="'.$ispara_class_name.' t-grid-team__name " data-player="'.$aResults['pl_id_1'].'">'.$aResults['name1'].'</div>
'.$first_div.$mesto_all_1.'

</div>
<div class=" t-grid-team  '.$ispara_class.$t_grid_team_noreult.' '.$class_sec.'">
'.$html_num2.'
<div class="'.$ispara_class_name.' t-grid-team__name " data-player="'.$aResults['pl_id_2'].'">'.$aResults['name2'].'</div>
'.$second_div.$mesto_all_2.'
</div>
</div>';
}
return $connent;
}
function get_mesta2xminuska16($aMesta)
{
    $content='';
    //s($aMesta);
    if (!empty($aMesta))
    { 
        $content='<div class="column_mesta">
        <div class="misca"> Місця:</div>
        <ul>';
      $height = $_SESSION['is_mobile']  ? 24 : 32 ;
        foreach ($aMesta as $Mesto)
        { 
         $color=' class="mestoPar" ';
            $img_medal='';
             switch ($Mesto['mesto_all']) {
case 1:
 $color =  ' class="mesto1" ';
// $img_medal = '<img height="15px" src="css/images/gold.png"></img> ';
 $img_medal = '<img height="'.$height. 'px" src="../../../../img/1mesto6.png"></img> ';
break;
case 2:
 $color =  ' class="mesto2" ';
    $img_medal = '<img height='.$height. 'px" src="../../../../img/2mesto6.png"></img> ';
    //$img_medal = '<img height="15px" src="css/images/2mesto.png"></img> ';

 break;
case 3:
 $color =  ' class="mesto3" ';
    $img_medal = '<img height="'.$height. 'px" src="../../../../img/3mesto6.png"></img> ';
    //$img_medal = '<img height="15px" src="css/images/3mesto.png"></img> ';

 break;
case 4:
$content.='</ul><div class="clgr"></div><ul>';

break;

}    
 if ( $Mesto['mesto_all']>3) {
         $color =  ' class="mestoNoPar" ';
     $content.='<li '.$color.'>'.$Mesto['mesto_all'].'<span class="ml10"></span>-<span class="ml10"></span>'.$Mesto['name'].'</li>';

 } else
$content.='<li >'.$img_medal.'<div '.$color.'><span class="ml10"></span>-<span class="ml10"></span>'.$Mesto['name'].'</div></li>';
   }
        $content.='</ul>
        </div>';
}
return $content;
}

function show_2xMinuska($etap_id,$turnir_id,$aResults,$aMesta,$cntP=0)
{
    $hd12 = ($cntP<13) ?  'hide'  : '';
    $hd9 = ($cntP<10) ?  'hide'  : '';
    $w12 = ($cntP<13) ?  'w1040'  : 'w1340';
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
    $mesta_html=' <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>';
    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=112154");
    </style>
  <div class="content2xminus">  
  <div class="big-table2">
  <div class="row">
  ' .($_SESSION['is_mobile'] ? $mesta_html : '').'
<div class="col">
 <div class="border_grp_2xminus16">
    <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper">
                                    <div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">1/8 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">1/4 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">1/2 фіналу</span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">Фінал</span>
                          </button>   
                    </div>
                            </div>
        </div>
        
 <!--  веррхний финал  --!>       
<div class="t-grid-columns">
<div class="t-grid-column'.$ispara.' _active">

'.GamePara(1,1,16,$aResults).'

'.GamePara(2,9,8,$aResults).'

'.GamePara(3,5,12,$aResults).'

'.GamePara(4,13,4,$aResults).'
'.GamePara(5,3,14,$aResults).'
'.GamePara(6,11,6,$aResults).'
'.GamePara(7,7,10,$aResults).'
'.GamePara(8,15,2,$aResults).'

</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(9,'','',$aResults).'
'.GamePara(10,'','',$aResults).'
'.GamePara(11,'','',$aResults).'
'.GamePara(12,'','',$aResults).'
</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(13,'','',$aResults).'
'.GamePara(14,'','',$aResults).'
</div>

<div class="t-grid-column'.$ispara.'" data-level="4">
'.GamePara(15,'','',$aResults,1,0,'t-grid-match_new').'
</div>
 </div>

</div>
</div>
<div class="col">
     '.(!$_SESSION['is_mobile'] ? $mesta_html : '').'
    </div>
  
    </div>
<div class="border_grp_2xminus16_to3mesto'.$ispara.'">    
<!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper ">
                                    <div class="wrap2 _first '.$hd12.'">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце 1/16 </span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">за 3 місце 1/8 </span>
                         </button>
                    </div>
                                <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">за 3 місце  1/4 </span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">за 3 місце 1/2 </span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">за 3 місце </span>
                          </button>   
                    </div>
                            </div>
        </div>
<div class="t-grid-columns">';

$content.='
<div class="t-grid-column_niz'.$ispara.' _active '.$hd12.'">
'.GamePara(16,-1,-2,$aResults).'
'.GamePara(17,-3,-4,$aResults).'
'.GamePara(18,-5,-6,$aResults).'
'.GamePara(19,-7,-8,$aResults).'
</div>';
$content.='
<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(20,-12,16,$aResults).'
'.GamePara(21,-11,17,$aResults).'
'.GamePara(22,-10,18,$aResults).'
'.GamePara(23,-9,19,$aResults).'


</div>

<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(24,20,21,$aResults).'
'.GamePara(25,22,23,$aResults).'

</div>

<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(26,-13,24,$aResults).'
'.GamePara(27,-14,25,$aResults).'
</div>

<div class="t-grid-column_niz'.$ispara.'">

'.GamePara(28,'26','27',$aResults,1).'
</div>
</div>
</div>
<!--  таблицці с местами --!>  
<div class="nig_mesta_td ">
<div class="container-fluid border_grp_2xminus16_to9mesto'.$ispara.'"> 
<div class="row align-items-center">
 <div class="col">
        <div class="td_5_8mesta'.$ispara.' marr30">
        
                <div class="t-grid-columns">
                
                        <div class="t-grid-column_niz2">
                                <div class="wrap2 _first">
                                                        <button class="t-grid-levels__button" data-level="0">
                                                            <span class="d-txt">за 5 місце</span>
                                                         </button>
                                       </div>
                        '.GamePara(36,-26,-27,$aResults,1).'
                                <div class="wrap2 _first">
                                                        <button class="t-grid-levels__button" data-level="0">
                                                            <span class="d-txt">за 7 місце</span>
                                                         </button>
                                                    </div>
                        '.GamePara(35,-24,-25,$aResults,1).'
                         </div>
                </div>
                </div>
        </div>';
    if ($cntP==10){
        $content .= '
<div class="col-7">
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';

        $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 9 місце</span>
                         </button>
                    </div>
' . GamePara(34,32,33,$aResults,1);

        $content .= '
</div>
</div>
</div>
</div>
';
    }else{
        if ($cntP>10){
    $content .='
<div class="col-7">
        <div class="t-grid-levels__wrapper_vosem">
            <div class="wrap2 _first">
                                    <button class="t-grid-levels__button" data-level="0">
                                        <span class="d-txt">Місця з 9-12</span>
                                     </button>
                                </div>
              
        </div>
    <div class="t-grid-columns '.$hd9.'">
    
        <div class="t-grid-column_niz2'.$ispara.'">
        '.GamePara(32,-20,-21,$aResults,0).'
        '.GamePara(33,-22,-23,$aResults,0).'
        
        </div>
            <div class="t-grid-column_niz2'.$ispara.'">
            '.GamePara(34,32,33,$aResults,1);
                if ($cntP>11)
                $content .= '
                <div class="td_11mesta ">
                '.GamePara(38,-32,-33,$aResults,1).'
                </div>';
                $content .= '
            </div>
            </div>
            </div>
    </div>';}}
    $content .='                
</div>
</div>
';
    if ($cntP>13){
    if ($cntP==14){
        $content .= '
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';

        $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 13 місце</span>
                         </button>
                    </div>
' . GamePara(31, 29, 30, $aResults, 1);

        $content .= '
</div>
</div>
</div>
';
    }else {
        $content .= '
<div class="nig_mesta_td ' . $hd12 . '">
    <div class="border_grp_2xminus16_to13mesto'.$ispara.'"> 
        <div class="t-grid-levels__wrapper_vosem">
        <div class="wrap2 _first">
                                <button class="t-grid-levels__button" data-level="0">
                                    <span class="d-txt">Місця з 13-16</span>
                                 </button>
                            </div>
          
             </div>
        <div class="t-grid-columns">';

        $content .= '
            <div class="t-grid-column_niz2'.$ispara.'">
            ' . GamePara(29, -16, -17, $aResults) . '
            ' . GamePara(30, -18, -19, $aResults) . '
            </div>';
        $content .= '
            <div class="t-grid-column_niz2'.$ispara.'">
            ' . GamePara(31, 29, 30, $aResults, 1);
        if ($cntP > 15)
            $content .= '
            <div class="td_11mesta ">
            ' . GamePara(37, -29, -30, $aResults, 1) . '
            </div>';
        $content .= '
            </div>
        </div>
    </div>
    </div>
</div>';
    }}





    $content.='
    </div>
 ';
    return $content;
 }
 function show_2xMinuska_to_2($etap_id,$turnir_id,$aResults,$aMesta)
{   $hd12 =  '';
    $hd9 =  '';
    $w12 = 'w1340';

    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=1115");
    </style>
  <div class="content2xminus">  
   <div class="row">
<div class="col">
 <div class="border_grp_2xminus16">
    <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper">
                                    <div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">1/8 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">1/4 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">1/2 фіналу</span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">Фінал</span>
                          </button>   
                    </div>
                            </div>
        </div>
        
 <!--  веррхний финал  --!>       
<div class="t-grid-columns">
<div class="t-grid-column' .$ispara.' _active">

'.GamePara(1,1,16,$aResults).'

'.GamePara(2,9,8,$aResults).'

'.GamePara(3,5,12,$aResults).'

'.GamePara(4,13,4,$aResults).'
'.GamePara(5,3,14,$aResults).'
'.GamePara(6,11,6,$aResults).'
'.GamePara(7,7,10,$aResults).'
'.GamePara(8,15,2,$aResults).'

</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(9,'','',$aResults).'
'.GamePara(10,'','',$aResults).'
'.GamePara(11,'','',$aResults).'
'.GamePara(12,'','',$aResults).'
</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(13,'','',$aResults).'
'.GamePara(14,'','',$aResults).'
</div>

<div class="t-grid-column'.$ispara.'" data-level="4">
'.GamePara(15,'','',$aResults,1,0,'t-grid-match_new').'
</div>
 </div>

</div>
</div>
<div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>
  
    </div>
<div class="border_grp_2xminus16_to3mesto'.$ispara.'">    
<!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper '.$w12.'">
                                    <div class="wrap2 _first '.$hd12.'">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце 1/16 </span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">за 3 місце 1/8 </span>
                         </button>
                    </div>
                                <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">за 3 місце  1/4 </span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">за 3 місце 1/2 </span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">за 3 місце </span>
                          </button>   
                    </div>
                            </div>
        </div>
<div class="t-grid-columns">';

    $content.='
<div class="t-grid-column_niz'.$ispara.' _active '.$hd12.'">
'.GamePara(16,-1,-2,$aResults).'
'.GamePara(17,-3,-4,$aResults).'
'.GamePara(18,-5,-6,$aResults).'
'.GamePara(19,-7,-8,$aResults).'
</div>';
    $content.='
<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(20,-12,16,$aResults).'
'.GamePara(21,-11,17,$aResults).'
'.GamePara(22,-10,18,$aResults).'
'.GamePara(23,-9,19,$aResults).'


</div>

<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(24,20,21,$aResults).'
'.GamePara(25,22,23,$aResults).'

</div>

<div class="t-grid-column_niz'.$ispara.'">
'.GamePara(26,-13,24,$aResults).'
'.GamePara(27,-14,25,$aResults).'
</div>

<div class="t-grid-column_niz'.$ispara.'">

'.GamePara(28,'26','27',$aResults,1).'
</div>
</div>
</div>
</div>


 ';
    return $content;
 }

function show_2xMinuska_2_pl($etap_id,$turnir_id,$aResults,$aMesta)
{
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';

    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=1115");
    </style>
  <div class="content2xminus">  
  <div class="row">
<div class="col">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="td_5_8mesta' .$ispara.' ">
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper_vosem">
           
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">Фінал</span>
                          </button>   
                    </div>
                            </div>
        </div>
        
 <!--  веррхний финал  --!>       
<div class="t-grid-columns">


<div class="t-grid-column'.$ispara.'" data-level="3">
'.GamePara(7,'','',$aResults,1,1).'
</div>
</div>
</div>
</div>
 <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>
</div>
</div>

 ';
    return $content;
}
  function show_2xMinuska_to_1($etap_id,$turnir_id,$aResults,$aMesta,$cnt_people)
{
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
    $mesta_html=' <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>';
    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=112154");
    </style>
  <div class="content2xminus">  
  <div class="big-table2">
     <div class="row">
       ' .($_SESSION['is_mobile'] ? $mesta_html : '').'
<div class="col">
 <div class="border_grp_2xminus16">
    <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper">
                                    <div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">1/8 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">1/4 фіналу</span>
                         </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">1/2 фіналу</span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">Фінал</span>
                          </button>   
                    </div>
                            </div>
        </div>
        
 <!--  веррхний финал  --!>       
<div class="t-grid-columns">
<div class="t-grid-column' .$ispara.' _active">

'.GamePara(1,1,16,$aResults).'

'.GamePara(2,9,8,$aResults).'

'.GamePara(3,5,12,$aResults).'

'.GamePara(4,13,4,$aResults).'
'.GamePara(5,3,14,$aResults).'
'.GamePara(6,11,6,$aResults).'
'.GamePara(7,7,10,$aResults).'
'.GamePara(8,15,2,$aResults).'

</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(9,'','',$aResults).'
'.GamePara(10,'','',$aResults).'
'.GamePara(11,'','',$aResults).'
'.GamePara(12,'','',$aResults).'
</div>
<div class="t-grid-column'.$ispara.'">
'.GamePara(13,'','',$aResults).'
'.GamePara(14,'','',$aResults).'
</div>

<div class="t-grid-column'.$ispara.'" data-level="3">
'.GamePara(15,'','',$aResults,1).'
</div>
  

</div>
</div>

</div>
' .(!$_SESSION['is_mobile'] ? $mesta_html : '').'
</div>

</div>



 ';
    $content .= '
  <div class="container-fluid "> 
   <div class="row align-items-center">
 <div class="col">';

    $content .= '
<div class="td_5_8mesta marr30 ">

<div class="t-grid-columns">

<div class="t-grid-column_niz2">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце</span>
                         </button>
                    </div>
' .($cnt_people>8 ? GamePara(26,-13,-14,$aResults,1) : GamePara(11, -5, -6, $aResults, 1)) ;

    $content .= '
</div>
</div>
</div>
</div>
';


    return $content;
 }
?>