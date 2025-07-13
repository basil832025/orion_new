<?php
//s('turnirsUPshtraph');
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class turnirsshtraphObject extends ObjectRT
{
    //$this-> = 'tree';
    function init ()
    {
      //  s('turnirsUPshtraphIINIT');
        //    s($_SESSION['gt']);
//s('turnirs');\
        $sWhere = ' AND virt=1 ';
        self::$theed_tr_class='th_players_mob';
        self::$table_class='table_mob_turn';

        if ($_SESSION['is_mobile']) {
            self::$table_class='table_mob_player';
           // $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
            $this->addFTL(array('name' => '<span class="f14 fw700 line14">Дата<br> турніру</span>',  'type' => 'date', 'name_field' => 'dat',
                'no_slash' => 1, 'width_mob' => '49','classAlign'=>'ws30','class'=>'break'));
            $this->addFTL(array('name' => '<span class="f14 fw700">Назва турніру</span>', 'type' => 'get_func', 'classAlign' => 'text-start',
                'function' => 'get_name_turnir',
                'width' => '600', 'name_field' => 'name', 'no_slash' => 1));
            $this->addFTL(array('name' => '<span class="f14 fw700 line14">К-ть<br> гравців</span>', 'type' => 'field', 'width' => '90',
                'width_mob' => '23', 'name_field' => 'cnt_players'));





        }else {
// описание полей таблицы модуля
            $this->addFTL(array('name' => '№', 'type' => 'number', 'width' => '20', 'width_mob' => '22'));
            //      $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
            $this->addFTL(array('name' => 'Дата турніру', 'name_mob' => 'Дата<br>турніру', 'type' => 'date', 'width' => '50', 'name_field' => 'dat', 'no_slash' => 1, 'width_mob' => '49'));
            $this->addFTL(array('name' => 'Назва турніру', 'type' => 'get_func', 'classAlign' => 'text-start',
                'function' => 'get_name_turnir',
                'width' => '600', 'name_field' => 'name', 'no_slash' => 1));
            /*$this->addFTL(array('name'=>'Назва турніру',
                              'type'=>'tree',
                              'name_field'=>'name',
                              'module'=>'etapresult',
                              'action'=>'show',
                              'name_field_child'=>'turnir_id',
                              ));*/
            $this->addFTL(array('name' => 'К-ть гравців', 'name_mob' => 'К-ть<br>грав<br>ців', 'type' => 'field', 'width' => '90', 'width_mob' => '23', 'name_field' => 'cnt_players'));

            $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
        }

        $this->setTableModule(T_TURNIRS);
        if  (empty($_SESSION['turnirsshtraph']['sort']))  $_SESSION['turnirsshtraph']['sort']='dat';
        if  (empty($_SESSION['turnirsshtraph']['sort_type']))  $_SESSION['turnirsshtraph']['sort_type']='desc';
        $_SESSION['turnirsshtraph']['sort_default']='id desc';

        $_SESSION['turnirsshtraph']['where'] =$sWhere;
        //  s($_SESSION);
        //$this->setTypeModule('tree');
        //   $_SESSION['JAVA_SCRIPT'] ='set_popover();';
        self::$nameZ='';
        self::$nameZList='';
        // self::$nameZList='<span class="zzagl">Турніри</span>';
        self::$nameZList='Турніри';
        if ($_SESSION['gt']['user_rule']<10)
            self::$submenu_list =array(
                'report_ok' => array('menu_name'=>'Перерахувати штраф рейтингу','module' => 'turnirs', 'action' => 'raschet_shtraph'),
                'back' => array('module' => 'settings', 'action' => 'show'),
            );

        self::$aFilters=array(
            'name'=>'По имени',
            'articul'=>'По артикулам',
        );
    }
}
function get_name_turnir($field,$id)
{
    $name='';

    $class='blac_color';
    $title='Турнір порахований';
    if ($_SESSION['is_mobile']) {$class.=' f12 fw700 nopodch';};

    $turnirName=db_field('SELECT name  FROM '.T_TURNIRS.' r WHERE id='.$id,'name');

    {
        $name ='<span  id="catalog_name_id_29" data-bs-toggle="tooltip" "><a href="#turnirsplayers-list-turnir_id='.$id.'&virt=1" class="'.$class.' ajax_send">'.$turnirName.'</a></span> ';
    }
    return $name;
}


?>