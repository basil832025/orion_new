<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ShowAction extends ActionModule
{  protected  $content = '';
    protected  $subMenu = array();
    protected  $subMenu2 = array();
    protected  $aResults = array(); // результат игор для таблиц
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента


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
      //  $content='asa';
     // $content =  file_get_contents(ROOT.'modules/settings/html/settings_list.php',FILE_USE_INCLUDE_PATH);
      include ROOT.'modules/settings/html/settings_list.php';

  //    s('$content');
        $this->content=get_menu();
       // $post_return = 'nomination-show-type='.$type.'&year='.$this->this_year.'&month='.$this->this_month;
       // SystemClass::setPost_return($post_return);
        SystemClass::setZaglModule('Налаштування');

    }


    function getContent ()
    {
        return $this->content;
    }
}