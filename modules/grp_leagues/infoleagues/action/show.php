<?php


// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ShowAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $subMenu2 = array();
    protected $aResults = array(); // результат игор для таблиц
    protected $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента
    protected $etap_id = 0; //
    protected $turnir_id = 0; //

    function init()
    {
      //  s('tyt2');
        $turnir_id = poste('turnir_id');
        $league_id = poste('league_id');
        $menu_league = !empty($league_id) ? '&league_id=' . $league_id : '';

        //     self::$nameZList='<div class="poriv_zag"> Гравці турніру (статистика гравців) "' . $name_turnir['name'] . '" (' . $tdat . ') </div>';

        $name_turnir = db_row('select name,dat,dop_info,status  from `bs_leagues` where id=' . $league_id);
        $date = new DateTimeImmutable($name_turnir['dat']);
        $tdat = $date->format('d.m.Y');

        $statusA = db_row('select value,teg from `bs_spr-spis-values` where id='.$name_turnir['status']);
        s($statusA);
        if ($statusA['teg']=='finish' ){
            $class='blac_color';
            $title=' завершена';
        }elseif($statusA['teg']=='active' ){
            $class='coral_color';
            $title=' розпочата';
        }else{
            $class= 'green_color';
            $title=' не розпочата';
        }
        if ($_SESSION['is_mobile'])
            $nameZ = '<div class="compare_zagl">Інформація по лізі "' . $name_turnir['name'] . ' (' . $tdat  . ')" <span class="'.$class.'">'.$title.'</span></div>';
        else
            $nameZ = '<div class="poriv_zag">Інформація по лізі "' . $name_turnir['name'] . '" (' . $tdat  . ') <span class="'.$class.'">'.$title.'</span></div>';
       s($nameZ);
        SystemClass::setZaglModule($nameZ);
        // выводим таблицы
        $this->content = '<div class="container">'.$name_turnir['dop_info'].'</div>';
        if ($_SESSION['is_mobile']) {

        } else {
            $show_zag_left = 'show_zag_center();show_zag_left_big("#turnirs-list' . $menu_league . '");';
        }
        $this->Java_script .= ' getTables();show_zag_left("#turnirs-list' . $menu_league . '");' . $show_zag_left;
        SystemClass::setJava_script($this->Java_script);


        // $this->list_show();
    }

    function getContent()
    {
        return $this->content;
    }

    function getSubMenu()
    {
        return $this->subMenu;
    }

    function getSubMenu2()
    {
        return $this->subMenu2;
    }

    function getJavaScript()
    {

        return $this->Java_script;
    }


    function list_show_()
    {  // SystemClass::setAction('anyaction');
        //   SystemClass::setModule('groups');
        //  $this->Java_script='reload_page_();';
        //   parent::list_show();
        $post_return = 'groups|show|turnir_id=' . $this->id;
        SystemClass::setPost_return($post_return);
        // $this->subMenu= self::$subMenu;
        $this->Java_script .= ' getTables();';
        SystemClass::setJava_script($this->Java_script);

        // $objList = new ListTable();

        //   $objList->list_show();
        // //   $this->content=$objList->getContent();
        //   $this->subMenu=$objList->getSubMneu();
        //   $this->Java_script=$objList->getJavaScript();

    }
}
//echo 'dsjksd';
