<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ShowAction extends ActionModule
{  protected  $content = '';
    protected  $subMenu = array();
    protected  $subMenu2 = array();
    protected  $aResults = array(); // результат игор для таблиц
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    protected  $etap_id = 0; //
    protected  $turnir_id = 0; //
    protected  $minYear = 2023; //
    protected  $maxYear = 2023; //
    protected  $aMonthsThisYear = []; //
    protected  $this_month = 0; //
    protected  $this_year = 2023; //

    function init ()
    {
        if ($_SESSION['is_mobile']) {
            $this->Java_script .= ' vubor_mes_year(140);show_zag_center();';
        }else
            $this->Java_script .= ' vubor_mes_year(200);show_zag_center();';

        // s($this->Java_script);
        SystemClass::setJava_script($this->Java_script);
        $type= poste('type');
        $attr_month= poste('month');
        $attr_year= poste('year');
        $attr_year_v= poste('year_v');
        $city = poste('city');
        $club = poste('club');
        $_SESSION['nomination']['filter']['city'] = isset($city) && $city!='' ? $city : (!empty($_SESSION['nomination']['filter']['city']) ? $_SESSION['nomination']['filter']['city'] : '56');
        $_SESSION['nomination']['filter']['club'] = isset($club) && $club!='' ? $club : (!empty($_SESSION['nomination']['filter']['club']) ? $_SESSION['nomination']['filter']['club'] : '0');
       // s('city='.$_SESSION['nomination']['filter']['city']);
        if (empty($attr_month) && empty($attr_year) && empty($attr_year_v)) $this->getMonthsThisYearDefault();
        $this->this_year= !empty($attr_year_v) ? $attr_year_v : ( !empty($attr_year) ? $attr_year : $this->this_year);
        // получим месяца в которых были турниры по выбраному году
        $this->getMonthsThisYear($this->this_year);

        if (!empty($attr_year_v))
        {
            $last_mon = end($this->aMonthsThisYear);
           // $year_href=$attr_year_v;
           // $mon_href=$last_mon;
         //   s('mon='.$last_mon['mon']);
        }
        $this->this_month= !empty($last_mon['mon']) ? $last_mon['mon'] : (!empty($attr_month) ? $attr_month : $this->this_month);
      //  s('this_month='.$this->this_month);
        // получаем минимальный год и максимальный
        $this->getYesrs();
        if (empty($type) || $type=='pm')
        {
            $type='pm';
            $this->bestPrurist();
        }
       else
            $this->bestPlayers();
       $TcITY= !empty($_SESSION['nomination']['filter']['city']) ? '&city='.$_SESSION['nomination']['filter']['city'] : '';
       $TClub= !empty($_SESSION['nomination']['filter']['club']) ? '&club='.$_SESSION['nomination']['filter']['club'] : '';
        $post_return = 'nomination-show-type='.$type.'&year='.$this->this_year.'&month='.$this->this_month.$TcITY.$TClub;
        SystemClass::setPost_return($post_return);
   //     SystemClass::setZaglModule('Цікава статистика');
        $dop_post='';
        if (!empty($attr_month)&&!empty($attr_year)) $dop_post='&year='.$attr_year.'&month='.$attr_month;
        $class_progres= (empty($type) || 'pm'==$type) ? 'nomin_menu_active' : 'nomin_menu';
        $class_bestPlayer= (!empty($type) && 'bp'==$type) ? 'nomin_menu_active' : 'nomin_menu';


        //
        if ($_SESSION['is_mobile'] ) {
            $zagl2  ='<span class="compare_zagl">Номінації</span>';
        }else
        $zagl2 = '<a href="'.'#nomination-show-type=pm'.$dop_post.$TcITY.$TClub.'" class="ajax_send '.$class_progres.'">Прогрес місяця</a>
<a href="'.'#nomination-show-type=bp'.$dop_post.$TcITY.$TClub.'" class="ajax_send '.$class_bestPlayer.' marl45">Найактивніший гравець</a>';
        SystemClass::setZaglModule($zagl2) ;

        $cnDaysMount = cal_days_in_month(CAL_GREGORIAN, $this->this_month, $this->this_year);

        $mon = $this->this_month<10 ? '0'.$this->this_month : $this->this_month;
        $dbeg = $this->this_year.'.'.$mon.'.01';
        $dend = $this->this_year.'.'.$mon.'.'.$cnDaysMount;
        if ($_SESSION['gt']['user_rule']<10) {

            $submenu_list = array(
                //filter' => array('module' => 'tovs'),
                'back' => array('module' => 'settings', 'action' => 'show', 'post' => ''),
                'filter' => array('menu_name' => 'Експорт в Excel результатів',
                    'http' => 'modules/nomination/action/toexcel_nomination.php?dbeg=' . $dbeg . '&dend=' . $dend . '&type=' . $type . $TcITY . $TClub)

            );
            SystemClass::$submenu = $submenu_list;
        }
    }

    function getMonthsThisYearDefault ()
    {
        $sql='select  MONTH(dat) mon,YEAR(dat) AS year     
  from '.T_TURNIRS.' r  where (select count(cnt_games) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and cnt_games is not null)>0 order by dat desc limit 1';
        $vData = db_row($sql);
        $this->this_year=$vData['year'];
        $this->this_month=$vData['mon'];
    }
    function getMonthsThisYear ($year)
    {
        $sql='select MONTH(dat) AS mon FROM bs_turnirs WHERE dat BETWEEN "'.$year.'.01.01" AND "'.$year.'.12.31"  GROUP BY 1 ORDER BY 1';
        $this->aMonthsThisYear=db_list($sql);

    }
    function getYesrs ()
    {
        $sql = 'select min(dat) as min_dat,MAX(dat) as max_dat FROM bs_turnirs ';
        $aMinMax=db_row($sql);
        $this->maxYear=substr($aMinMax['max_dat'],0,4);
        $this->minYear=substr($aMinMax['min_dat'],0,4);
    }
    function bestPlayers ()
    {
        $cnDaysMount = cal_days_in_month(CAL_GREGORIAN, $this->this_month, $this->this_year);
        $mon = $this->this_month<10 ? '0'.$this->this_month : $this->this_month;
        $dbeg = $this->this_year.'.'.$mon.'.01';
        $dend = $this->this_year.'.'.$mon.'.'.$cnDaysMount;

        $name ='Найактивніший гравець';
        $text='Критерій: Найбільша кількість відвіданих за місяць турнірів.<br> Якщо в гравців однакова кількість відвіданих турнірів, то переможець визначається за кількістю зіграних ігор, потім за кількістю зіграних сетів, потім за % перемог.					
';
        $this->content=getNominationHeader($name,$text,$this->minYear,$this->maxYear,$this->aMonthsThisYear,$this->this_month,$this->this_year);

        $sqlgrp = 'p.grp=45';// діти молодша
        $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-молодша';
            $this->content.=getNominationBestPlayer($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=46';// діти середняв
        $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-середня';
            $this->content.=getNominationBestPlayer($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=47 or p.grp=49';// діти старша та ранок
        $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-старша+Діти-ранок';
            $this->content.=getNominationBestPlayer($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=48 or p.grp=51';// дорослі та ВСМ
        $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Дорослі+ШВСМ';
            $this->content.=getNominationBestPlayer($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=52';// загальна
        $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Загальна';
            $this->content.=getNominationBestPlayer($name_grp,$aUsers);
        }

        $this->content.='</div><div class="martop80"></div>';
    }
    function bestPrurist ()
    {
        $cnDaysMount = cal_days_in_month(CAL_GREGORIAN, $this->this_month, $this->this_year);
        $mon = $this->this_month<10 ? '0'.$this->this_month : $this->this_month;
        $dbeg = $this->this_year.'.'.$mon.'.01';
        $dend = $this->this_year.'.'.$mon.'.'.$cnDaysMount;
  ////      s('$dbeg='.$dbeg);
   //     s('$dend='.$dend);
        $name ='Прогрес місяця';
        $text='Критерій: Найбільший приріст рейтингу за підсумками місяця.<br> Якщо в гравців приріст рейтинга однаковий, то переможець визначається за % перемог.					
';
        $this->content=getNominationHeader($name,$text,$this->minYear,$this->maxYear,$this->aMonthsThisYear,$this->this_month,$this->this_year);

        $sqlgrp = 'p.grp=45';// діти молодша
        $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-молодша';
            $this->content.=getNomination($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=46';// діти середняв
        $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-середня';
            $this->content.=getNomination($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=47 or p.grp=49';// діти старша та ранок
        $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Діти-старша+Діти-ранок';
            $this->content.=getNomination($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=48 or p.grp=51';// дорослі та ВСМ
        $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Дорослі+ШВСМ';
            $this->content.=getNomination($name_grp,$aUsers);
        }

        $sqlgrp = 'p.grp=52';// загальна
        $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers))
        {
            $name_grp = 'Загальна';
            $this->content.=getNomination($name_grp,$aUsers);
        }

        $this->content.='</div>
<div class="martop80"></div>';
    }
    function getContent ()
    {
        return $this->content;
    }
}