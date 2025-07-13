<?php
function show_2xMinuska8($etap_id,$turnir_id,$aResults,$aMesta,$cntP=0)
{
    $hd6 = ($cntP<7) ?  'hide'  : '';
    $hd4 = ($cntP<5) ?  'hide'  : '';
    $w33 = ($cntP<7) ?  'w560'  : '';
    $w860 = ($cntP<5) ?  'w860'  : '';
    $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
    $mesta_html=' <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>';
$content = '
<style>
    @import url("../../../../css/2xminuska.css?ver=112324");
</style>

<div class="content2xminus">
<div class="big-table2">
<div class="row">
' .($_SESSION['is_mobile'] ? $mesta_html : '').'
<div class="col">

 <div class="border_grp_2xminus'.$ispara.'">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="t-grid-levels">
        <div class="t-grid-levels__wrapper_vosem ">

            <div class="wrap2 '.$hd4.'">
                <button class="t-grid-levels__button _active _selected " data-level="1">
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
        <div class="t-grid-column'.$ispara.' _active '.$hd4.'">

            '.GamePara(1,1,8,$aResults).'

            '.GamePara(2,5,4,$aResults).'

            '.GamePara(3,3,6,$aResults).'

            '.GamePara(4,7,2,$aResults).'


        </div>

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
    </div>
    
    ';
    if ($cntP>3){
    if ($cntP==4){
        $content .= '
<div class="row">
<div class="col">
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';

        $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце</span>
                         </button>
                    </div>
' . GamePara(12, '10', '11', $aResults, 1);

        $content .= '
</div>
</div>
</div>
</div>
</div>
';
    }else {
        $content .= '
<div class="row">
<div class="col">
    <div class="border_grp_2xminus'.$ispara.'">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="t-grid-levels">
        <div class="t-grid-levels__wrapper_vosem ">


            <div class="wrap2 ' . $hd6 . '">
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
                    <span class="d-txt">за 3 місце</span>
                </button>
            </div>
        </div>
    </div>
    <div class="t-grid-columns">';
        $content .= '
        <div class="t-grid-column_niz'.$ispara.' _active ' . $hd6 . '">
            ' . GamePara(8, -1, -2, $aResults) . '
            ' . GamePara(9, -3, -4, $aResults) . '

        </div>';
        $content .= '

        <div class="t-grid-column_niz'.$ispara.'">
            ' . GamePara(10, -6, 8, $aResults) . '
            ' . GamePara(11, -5, 9, $aResults) . '
        </div>



        <div class="t-grid-column_niz'.$ispara.'">

            ' . GamePara(12, '10', '11', $aResults, 1) . '
        </div>
    </div>
    </div>
    </div>
</div>';
    }}
    if ($cntP>5) {
        $content .= '
<div class="row">
<div class="col">

    <div class="td_5_8mesta'.$ispara.'">
        <div class="t-grid-columns">
                     <div class="t-grid-column_niz2'.$ispara.'">
                                    <div class="wrap2 _first">
                                                            <button class="t-grid-levels__button" data-level="0">
                                                                <span class="d-txt">за 5 місце</span>
                                                             </button>
                                           </div>
                         ' . GamePara(14, -10, -11, $aResults, 1);
        if ($cntP > 7)
            $content .= '
                                    <div class="wrap2 _first">
                                                            <button class="t-grid-levels__button" data-level="0">
                                                                <span class="d-txt">за 7 місце</span>
                                                             </button>
                                                        </div>
                            ' . GamePara(13, -8, -9, $aResults, 1);
        $content .= '
                        </div>
        
        
    </div>
    </div>
    </div>
    ';
    }
    $content.='  
    </div> </div>
    

    </div>
   </div>
    ';
    return $content;
    }
    function show_2xMinuska8_to_2($etap_id,$turnir_id,$aResults,$aMesta,$cntP=0)
    {
        $ispara = !empty($_SESSION['is_para_minus_olimp']) ? '_ispara' : '';
        $hd6 = ($cntP<7) ?  'hide'  : '';
        $hd4 = ($cntP<5) ?  'hide'  : '';
        $w33 = ($cntP<7) ?  'w560'  : '';
        $w860 = ($cntP<5) ?  'w860'  : '';
    $content = '
    <style>
        @import url("../../../../css/2xminuska.css?ver=1114");
    </style>
    <div class="content2xminus">
    <div class="row">
<div class="col">
<div class="row">
<div class="col">
 <div class="border_grp_2xminus">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="t-grid-levels">
        <div class="t-grid-levels__wrapper_vosem ' .$w33.'">

            <div class="wrap2 '.$hd4.'">
                <button class="t-grid-levels__button _active _selected " data-level="1">
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
        <div class="t-grid-column'.$ispara.' _active '.$hd4.'">

            '.GamePara(1,1,8,$aResults).'

            '.GamePara(2,5,4,$aResults).'

            '.GamePara(3,3,6,$aResults).'

            '.GamePara(4,7,2,$aResults).'


        </div>

        <div class="t-grid-column'.$ispara.'">
            '.GamePara(5,'','',$aResults).'
            '.GamePara(6,'','',$aResults).'
        </div>

        <div class="t-grid-column'.$ispara.'" >
            '.GamePara(7,'','',$aResults,1).'
        </div>
        

    </div>
    </div>
    </div>
    <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>
    </div>
    ';
        if ($cntP>3){
            if ($cntP==4){
                $content .= '
 <div class="td_5_8mesta'.$ispara.' marr30 mart60">

<div class="t-grid-columns">

<div class="t-grid-column_niz2'.$ispara.'">';

                $content .= '<div class="wrap2 _first">
                        <button class="t-grid-levels__button" data-level="0">
                            <span class="d-txt">за 3 місце</span>
                         </button>
                    </div>
' . GamePara(12, '10', '11', $aResults, 1);

                $content .= '
</div>
</div>
</div>
';
            }else {
                $content .= '
    <div class="border_grp_2xminus'.$ispara.'">
    <!--  веррхння полоса с видами финалов  --!>
    <div class="t-grid-levels">
        <div class="t-grid-levels__wrapper_vosem ' . $w33 . '">


            <div class="wrap2 ' . $hd6 . '">
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
                    <span class="d-txt">за 3 місце</span>
                </button>
            </div>
        </div>
    </div>
    <div class="t-grid-columns">';
                $content .= '
        <div class="t-grid-column_niz'.$ispara.' _active ' . $hd6 . '">
            ' . GamePara(8, -1, -2, $aResults) . '
            ' . GamePara(9, -3, -4, $aResults) . '

        </div>';
                $content .= '

        <div class="t-grid-column_niz'.$ispara.'">
            ' . GamePara(10, -6, 8, $aResults) . '
            ' . GamePara(11, -5, 9, $aResults) . '
        </div>



        <div class="t-grid-column_niz'.$ispara.'">

            ' . GamePara(12, '10', '11', $aResults, 1) . '
        </div>
    </div>
</div>';
            }}

      $content.=' </div> 
        </div>


        ';
        return $content;
        }
        function show_2xMinuska8_to_1($etap_id,$turnir_id,$aResults,$aMesta)
        {
            $mesta_html=' <div class="col">
    '.get_mesta2xminuska16($aMesta).'
    </div>';
        $content = '
        <style>
            @import url("../../../../css/2xminuska.css?ver=140725");
        </style>
        <div class="content2xminus">
        <div class="big-table2">
        <div class="row">
' .($_SESSION['is_mobile'] ? $mesta_html : '').'
<div class="col">
            <!--  веррхння полоса с видами финалов  --!>
             <div class="border_grp_2xminus">
            <div class="t-grid-levels">
                <div class="t-grid-levels__wrapper_vosem">

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
                <div class="t-grid-column _active">

                    ' .GamePara(1,1,8,$aResults).'

                    '.GamePara(2,5,4,$aResults).'

                    '.GamePara(3,3,6,$aResults).'

                    '.GamePara(4,7,2,$aResults).'


                </div>

                <div class="t-grid-column">
                    '.GamePara(5,'','',$aResults).'
                    '.GamePara(6,'','',$aResults).'
                </div>

                <div class="t-grid-column" data-level="0">
                    '.GamePara(7,'','',$aResults,1).'
                </div>
               

            </div>
         
       
</div> '.(!$_SESSION['is_mobile'] ? '</div>'.$mesta_html : '').'
    
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
' . GamePara(11, -5, -6, $aResults, 1);

                $content .= '
</div>
</div>
</div>

';

            return $content;
            }