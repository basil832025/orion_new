<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class Counts_turnirsAction extends ActionModule
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
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {

            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        $this->Java_script.=' vubor_mes_year();show_zag_center();';
        // s($this->Java_script);
        SystemClass::setJava_script($this->Java_script);
        $type= poste('type');
        $attr_month= poste('month');
        $attr_year= poste('year');
        $attr_year_v= poste('year_v');
        $city = poste('city');
        $club = poste('club');
        $st = isset($city) ? $city : 1;
        $scl = isset($club) ? $club : 2 ;
       // s('city='.$st);
      //  s('club='.$scl);
        $_SESSION['counts_turnirs']['filter']['city'] = isset($city) && $city!='' ? $city : (!empty($_SESSION['counts_turnirs']['filter']['city']) ? $_SESSION['counts_turnirs']['filter']['city'] : '0');
        $_SESSION['counts_turnirs']['filter']['club'] = isset($club) && $club!='' ? $club : (!empty($_SESSION['counts_turnirs']['filter']['club']) ? $_SESSION['counts_turnirs']['filter']['club'] : '0');

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
        $this->this_month= !empty($last_mon['mon']) ? $last_mon['mon'] : (isset($attr_month) && $attr_month!='' ? $attr_month : $this->this_month);
     //   s('this_month='.$this->this_month);
        // получаем минимальный год и максимальный
        $this->getYesrs();
            $type='pm';
        $cnDaysMount = cal_days_in_month(CAL_GREGORIAN, $this->this_month, $this->this_year);
        $mon = $this->this_month<10 ? '0'.$this->this_month : $this->this_month;
        $dbeg = $this->this_year.'.'.$mon.'.01';
        $dend = $this->this_year.'.'.$mon.'.'.$cnDaysMount;

        $this->reportMain($dbeg,$dend);
        $TcITY= !empty($_SESSION['counts_turnirs']['filter']['city']) ? '&city='.$_SESSION['counts_turnirs']['filter']['city'] : '';
        $TClub= !empty($_SESSION['counts_turnirs']['filter']['club']) ? '&club='.$_SESSION['counts_turnirs']['filter']['club'] : '';

        $post_return = 'reports-counts_turnirs-year='.$this->this_year.'&month='.$this->this_month.$TcITY.$TClub;
 //    s('$post_return='.$post_return);
        SystemClass::setPost_return($post_return);

        SystemClass::setZaglModule('Звіт::Кількість відвідувань турнірів');
        $submenu_list =array(
            //filter' => array('module' => 'tovs'),
            'back' => array('module' => 'settings', 'action' => 'show',  'post' => ''),
            'filter' => array('menu_name'=>'Експорт в Excel результатів',
            'http' => 'modules/reports/action/toexcel_counts_turnirs.php?dbeg='.$dbeg.'&dend='.$dend.$TcITY.$TClub)

        );
        SystemClass::$submenu = $submenu_list;


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
    function reportMain ($dbeg,$dend)
    {
     ////      s('$dbeg='.$dbeg);
   //     s('$dend='.$dend);
        $name ='Кількість відвідуваннь турнірів';
        $text='';
        $this->content=getCountTurnirsHeader($name,$text,$this->minYear,$this->maxYear,$this->aMonthsThisYear,$this->this_month,$this->this_year);
        $sqlgrp = 'p.grp=45';// діти молодша
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);
        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Діти-молодша';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }
        $sqlgrp = 'p.grp=46';// діти середняв
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Діти-середня';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }

        $sqlgrp = 'p.grp=47';// діти старша
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Діти-старша';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }
        $sqlgrp = 'p.grp=49';// ранок
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Діти-ранок';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }


        $sqlgrp = 'p.grp=48';//  ВСМ
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'ШВСМ';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }
        $sqlgrp = 'p.grp=51';// дорослі та ВСМ
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Дорослі';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }

        $sqlgrp = 'p.grp=52';// загальна
        $aUsers = getSQLCountTurnirs($dbeg,$dend,$sqlgrp);
        $aUsersNO = getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp);

        if (!empty($aUsers) || !empty($aUsersNO))
        {
            $name_grp = 'Загальна';
            $this->content.=getCountTurnirs($name_grp,$aUsers,$aUsersNO);
        }

        $this->content.='</div>';
    }
    function getContent ()
    {
        return $this->content;
    }
}