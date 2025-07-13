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
    function init ()
    {
        $this->content = '<div class="container-fluid">Ширина екрану: '.$_SESSION['width_body'].'</div>';
    }
    function getContent ()
    {
        return $this->content;
    }
    function getSubMenu ()
    {
        return  $this->subMenu;
    }
    function getSubMenu2 ()
    {
        return  $this->subMenu2;
    }
    function getJavaScript ()
    {

        return $this->Java_script;
    }



    function list_show_()
    {

    }
}
//echo 'dsjksd';
?>