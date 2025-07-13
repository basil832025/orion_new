<?php
function show_Olimp8($etap_id,$turnir_id,$aResults,$aMesta,$cnt_people)
{   $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';

    $hd5 = ($cnt_people<5) ?  'hide'  : '';
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
 <div class="border_grp_2xminus">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="t-grid-levels">
        <div class="t-grid-levels__wrapper_vosem">

            <div class="wrap2 '.$hd5.'">
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
    <div class="t-grid-columns">';
    if ($cnt_people>4) {
        $content .= '
        <div class="t-grid-column'.$ispara.' _active">

            ' . GamePara(1, 1, 8, $aResults) . '

            ' . GamePara(2, 5, 4, $aResults) . '

            ' . GamePara(3, 3, 6, $aResults) . '

            ' . GamePara(4, 7, 2, $aResults) . '


        </div>';
    }
    $content .= '
        <div class="t-grid-column'.$ispara.'">
            '.GamePara(5,'','',$aResults).'
            '.GamePara(6,'','',$aResults).'
        </div>

        <div class="t-grid-column'.$ispara.'" data-level="3">
            '.GamePara(7,'','',$aResults,1).'
        </div>
        

    </div>
    </div>
    </div>
  '.(!$_SESSION['is_mobile'] ? $mesta_html : '').'
</div> ';
    if ($cnt_people>3) {
        $content .= '
  <div class="container-fluid border_grp_2xminus16_to9mesto'.$ispara.'"> 
   <div class="row align-items-center">
 <div class="col">';
        $hd8_ = ($cnt_people == 8 || $cnt_people == 6) ? '' : 'mart60';
        $content .= '
<div class="td_5_8mesta'.$ispara.' marr30 ' . $hd8_ . '">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце</span>
                         </button>
                    </div>
' . GamePara(11, -5, -6, $aResults, 1);
        if ($cnt_people > 7) {
            $content .= '
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 7 місце</span>
                         </button>
                    </div>
' . GamePara(12, -8, -9, $aResults, 1);
        } elseif ($cnt_people == 6) {
            $content .= '
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 5 місце</span>
                         </button>
                    </div>
' . GamePara(10, '', '', $aResults, 1);
        }
        $content .= '
</div>
</div>
</div>
</div>';
    }
    if ($cnt_people>6) {
        $content .= '
<div class="col-7">
<div class="t-grid-levels__wrapper_vosem">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">Місця з 5-8</span>
                         </button>
                    </div>
  
     </div>
<div class="t-grid-columns ">';

        $content .= '
<div class="t-grid-column_niz2'.$ispara.'">
' . GamePara(8, -1, -2, $aResults) . '
' . GamePara(9, -3, -4, $aResults) . '

</div>';

        $content .= '
<div class="t-grid-column_niz2'.$ispara.'">
' . GamePara(10, '', '', $aResults, 1) . '

</div>
</div>
</div>';
    }
    $content .= '
</div>
</div>
</div>

</div>
 ';
    return $content;
}
function show_Olimp($etap_id,$turnir_id,$aResults,$aMesta,$cnt_people)
{
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
    $mesta_html=' <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>';

    $content = '
    <style>
    @import url("../../../../css/2xminuska.css?ver=1612124");
    </style>
  <div class="content2xminus">  
  <div class="big-table2">
    <!--  веррхння полоса с видами финалов  --!>
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
 '.(!$_SESSION['is_mobile'] ? $mesta_html : '').'
    </div>
 <div class="container-fluid border_grp_2xminus16_to9mesto'.$ispara.'"> 
 <div class="row align-items-center">
 <div class="col">
<div class="td_5_8mesta'.$ispara.' marr30">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце</span>
                         </button>
                    </div>
'.GamePara(26,-13,-14,$aResults,1).'
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 7 місце</span>
                         </button>
                    </div>
'.GamePara(31,-23,-24,$aResults,1).'


</div>
</div>
</div>
</div>
<div class="col-7">
<div class="t-grid-levels__wrapper_vosem">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">Місця з 5-8</span>
                         </button>
                    </div>
  
     </div>
<div class="t-grid-columns ">

<div class="t-grid-column_niz2'.$ispara.'">
'.GamePara(23,-9,-10,$aResults).'
'.GamePara(24,-11,-12,$aResults).'

</div>
<div class="t-grid-column_niz2'.$ispara.'">
'.GamePara(25,'','',$aResults,1).'

</div>
</div>
</div>
</div>
</div>';
    if ($cnt_people>9) {
        $hd13_11m = ($cnt_people>12) ?  'mart150'  : 'mart60';
        $content .= '   
 <div class="container-fluid border_grp_2xminus16_to3mesto">
 <div class="row align-items-center">
 <div class="col">
 ';
        if ($cnt_people > 11){
        $content .= ' 
  <div class="td_5_8mesta'.$ispara.'  ">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';


            $content .= '
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 11 місце</span>
                         </button>
                    </div>
' . GamePara(30, -20, -21, $aResults, 1);


        $content .= '
</div>
</div>
</div>
</div>';
        }
        $hd12 = ($cnt_people<13) ?  'hide'  : '';
        $hd10 = ($cnt_people<11) ?  'hide'  : '';
$content .= '
<div class="col-8">
            <!--  веррхння полоса с видами финалов  --!>
<div class="t-grid-levels">
            <div class="t-grid-levels__wrapper_vosem">       
                                    <div class="wrap2 '.$hd12.'">
                        <button class="t-grid-levels__button _active _selected" data-level="1">
                            <span class="d-txt">за 9 місце 1/4</span>
                         </button>
                    </div>          <div class="wrap2 '.$hd10.'">
                        <button class="t-grid-levels__button" data-level="2">
                            <span class="d-txt">за 9 місце 1/2</span>
                        </button>
                    </div>
                                    <div class="wrap2">
                        <button class="t-grid-levels__button" data-level="3">
                            <span class="d-txt">за 9 місце</span>
                          </button>   
                    </div>
                            </div>
        </div>   

    <!--  веррхний финал  --!>
    <div class="t-grid-columns">';
        if ($cnt_people > 12) {
            $content .= '
        <div class="t-grid-column'.$ispara.' _active">
' . GamePara(16, -1, -2, $aResults) . '
' . GamePara(17, -3, -4, $aResults) . '
' . GamePara(18, -5, -6, $aResults) . '
' . GamePara(19, -7, -8, $aResults) . '
       </div>';
        }
        if ($cnt_people > 10) {
            $content .= '

        <div class="t-grid-column'.$ispara.'">
          ' . GamePara(20, '', '', $aResults) . '
        ' . GamePara(21, '', '', $aResults) . '
        </div>';
        }
        $content .= '
      <div class="t-grid-column'.$ispara.'" data-level="3">
           ' . GamePara(22, '', '', $aResults, 1) . '
        </div>
        

    </div>
    </div>
    </div>
    </div>

    ';
    }
    if ($cnt_people>13) {
        if ($cnt_people==14){
            $content .= '
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns'.$ispara.'">

<div class="t-grid-column_niz2'.$ispara.'">';

                $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 13 місце</span>
                         </button>
                    </div>
                    
' . GamePara(29, -17, -18, $aResults, 1) ;

            $content .= '
</div>
</div>
</div>
';
        }else
        {
        
        
        $content .= '
 <div class="container-fluid border_grp_2xminus16_to9mesto'.$ispara.'"> 
  <div class="row align-items-center">';
        if ($cnt_people>15) {
            $content .= '
<div class="col">
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';


            if ($cnt_people > 15)
                $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 15 місце</span>
                         </button>
                    </div>
' . GamePara(32, -27, -28, $aResults, 1);

            $content .= '
</div>
</div>
</div>
</div>
';
        }

        
        $content .= '
<div class="col-7">
<div class="t-grid-levels__wrapper_vosem">
<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">Місця з 13-16</span>
                         </button>
                    </div>
  
     </div>
<div class="t-grid-columns ">';
        if ($cnt_people > 14) {
            $content .= '

<div class="t-grid-column_niz2'.$ispara.'">
' . GamePara(27, -16, -17, $aResults) . '
' . GamePara(28, -18, -19, $aResults) . '

</div>';
        }
        $content .= '
<div class="t-grid-column_niz2'.$ispara.'">
' . GamePara(29, '', '', $aResults, 1) . '

</div>
</div>
</div>
</div>

';  }
    }
        $content .= '
</div>
</div>
 ';

    return $content;
}
