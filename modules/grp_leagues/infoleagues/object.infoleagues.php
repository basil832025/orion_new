<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class InfoleaguesObject extends ObjectRT
{
    //$this-> = 'tree';
    function init ()
    {
    //    s('tyt1');
        self::InitLeaguesMenu();
        self::$nameZ='"Результаты этапов::';
        self::$nameZList='';
        if ($_SESSION['gt']['user_rule']<=10)
            self::$submenu_list =array(
                // 'filter' => array('module' => 'tovs'),
                // 'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>'print_page(\'print_group\');'),
                //  'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>' print();'),

                // 'prava_user' => array('menu_name'=>'Рассчитать места', 'module' => 'groups', 'action' => 'raschetmest', 'post' => 'id='.poste('id')),

                //  'report_ok' => array('menu_name'=>'Пересчитать рейтинг по данному туриниру','module' => 'turnirsplayers', 'action' => 'raschet', 'post' => 'id='.poste('id')),
                'back' => array('module' => 'turnirs', 'action' => 'list'),
            );

      //  self::InitMainMenu();

        self::$aParent= array('name_field'=>'turnir_id',
            'table'=>T_TURNIRS,
            'type'=>'Hidden'
        );
    }


}
?>
