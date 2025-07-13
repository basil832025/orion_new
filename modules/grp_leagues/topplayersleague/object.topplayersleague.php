<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class topplayersLeagueObject extends ObjectRT
{

    //$this-> = 'tree';
    function init ()
    {//$_SESSION['gt']['user_rule']s($_SESSION['gt']['user_rule']);
        //    s($_POST);
        //  s($_FILES);
   //     wLog('tyt');
        $fio_search = poste('fio_search');
        $action=SystemClass::getAction();
        self::$theed_tr_class='th_players_mob';
        if (empty($fio_search) && $action=='list')
            $_SESSION['MESSAGE_AJAX']='<div class="input__wrapper"><svg class="input__icon_player"><use xlink:href="#poisk"></use></svg><input type="text" class="form-control" placeholder="Пошук гравця" id="search_field_players" style="margin-left: 20px; width:425px;" speeds="0"    value="'.$fio_search.'"></div>';
        // $this->addFTL(array('name'=>'№ заказа','type'=>'text','oper'=>'edit','width'=>'25','name_field'=>'player_id','bd_field'=>'player_id'));
        if ($_SESSION['is_mobile'])
            $this->addFTL(array('name'=>'<span class="f14 fw700 line14">№</span><br><span class="f12 fw400"> в р-нгу</span>','type'=>'number','class'=>'colelemPlayernum', 'width_mob'=>'54', 'width'=>'5'));
        else
            $this->addFTL(array('name'=>'№','type'=>'number','width'=>'5'));


        if ($_SESSION['is_mobile']) {
            self::$table_class='table_mob_player';
            if ($_SESSION['gt']['user_rule']<10) {
                $this->addFTL(array('name' => '<span class="f14 fw700 line14">Ред-ти</span>', 'type' => 'edit', 'width' => '40'));
            }
            $this->addFTL(array('name' => '<span class="f14 fw700">ПІБ гравця</span>', 'width_mob'=>'210', 'type'=>'get_func','name_field' => 'name', 'bd_field' => 'name', 'function'=>'get_name_player', 'classAlign' => 'text-center'));
            $this->addFTL(array('name'=>'Рейтинг<br /> клубу','name_mob'=>'<span class="f14 fw700 line14">Р-нг<br>клубу</span>', 'class'=>'colelemPlayer', 'width_mob'=>'64', 'name_field'=>'reiting','bd_field'=>'reiting','width'=>'20','no_edit_table'=>1,'round'=>0));
            $this->addFTL(array('name'=>'Рейтинг<br /> ФНТУ','name_mob'=>'<span class="f14 fw700 line14">Р-нг<br>ФНТУ</span>','class'=>'colelemPlayer2', 'width_mob'=>'64', 'name_field'=>'reiting_ukraine','bd_field'=>'reiting_ukraine','width'=>'20','no_edit_table'=>1));
            $this->addFTL(array('type'=>'onlybd', 'name_field'=>'sex','bd_field'=>'sex'));
            $this->addFTL(array('type'=>'onlybd', 'name_field'=>'birthday','bd_field'=>'birthday'));
            $this->addFTL(array('type'=>'onlybd', 'name_field'=>'god_rogd','bd_field'=>'god_rogd'));

        }else{
            if ($_SESSION['gt']['user_rule']<10) {
                $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
            }
//         $this->addFTL(array('name' => 'Фото', 'name_field' => 'photo', 'width'=>'200', 'bd_field' => 'photo','type'=>'image', 'filter' => 1, 'classAlign' => 'td_align_left'));
            $this->addFTL(array('name' => 'ПІБ гравця', 'target'=>true, 'name_field' => 'name', 'oper' => 'edit', 'width'=>'200','action'=>'statistics', 'bd_field' => 'name', 'filter' => 1, 'classAlign' => 'text-start'));

            //   $this->addFTL(array('name' => 'ПІБ гравця', 'type'=>'anyaction', 'name_field' => 'name',          'action'=>'statistics','module'=>'players','name_field_child'=>'turnir_id','bd_field' => 'name', 'filter' => 1, 'classAlign' => 'td_align_left'));

            if ($_SESSION['gt']['user_rule']<10) {

                //   $this->addFTL(array('name' => 'ПІБ гравця', 'name_field' => 'name', 'oper' => 'edit','width'=>'200', 'bd_field' => 'name', 'filter' => 1, 'classAlign' => 'td_align_left'));
                $this->addFTL(array('name' => 'Телефон','name_mob'=>'Теле-<br>фон', 'name_field' => 'phone', 'bd_field' => 'phone','width'=>'100','class'=>'perenos_word'));

            }//else

            $this->addFTL(array('name'=>'Рейтинг<br /> клубу','name_mob'=>'Р-нг<br>клубу', 'name_field'=>'reiting','bd_field'=>'reiting','width'=>'20','no_edit_table'=>1,'round'=>0));
            $this->addFTL(array('name'=>'Місце в<br /> рейтингу','name_mob'=>'Місце<br>в р-гу','name_field'=>'num_reiting','bd_field'=>'num_reiting','width'=>'20','no_edit_table'=>1));
            $this->addFTL(array('name' => 'Група', 'name_field' => 'grp', 'type' => 'ProstSpr', 'id_spis' => '2', 'bd_field' => 'grp', 'width' => '80'));
            if ($_SESSION['gt']['user_rule']<10) {
                $this->addFTL(array('name' => 'Місто', 'name_field' => 'city_def', 'type' => 'ProstSpr', 'id_spis' => '2', 'bd_field' => 'city_def', 'width' => '80'));
                $this->addFTL(array('name' => 'Клуб', 'name_field' => 'club', 'type' => 'ProstSpr', 'id_spis' => '2', 'bd_field' => 'club', 'width' => '80'));
            }
            $this->addFTL(array('name'=>'К-ть<br />турнірів','name_mob'=>'К-ть<br>турні<br>рів','name_field'=>'cnt_turnirs','bd_field'=>'cnt_turnirs','width'=>'30','no_edit_table'=>1));

            //   $this->addFTL(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','width'=>'100','filter'=>1,'speedsearch'=>5));
            $this->addFTL(array('name'=>'К-ть<br />ігор','name_field'=>'cnt_games','bd_field'=>'cnt_games','width'=>'45','no_edit_table'=>1));
            $this->addFTL(array('name'=>'К-ть перемог','name_mob'=>'К-ть<br>пере<br>мог','name_field'=>'cnt_wins','bd_field'=>'cnt_wins','width'=>'30','no_edit_table'=>1));
            $this->addFTL(array('name'=>'К-ть пораз.','name_mob'=>'К-ть<br>пора<br>зок','name_field'=>'cnt_lose','bd_field'=>'cnt_lose','width'=>'30','no_edit_table'=>1));
            $this->addFTL(array('name'=>'% перемог','name_mob'=>'%<br>пере<br>мог','name_field'=>'proc_wins','bd_field'=>'proc_wins','width'=>'30','no_edit_table'=>1));
            $this->addFTL(array('name'=>'Р-нг мін','name_mob'=>'Р-нг<br> мін','name_field'=>'reiting_min','bd_field'=>'reiting_min','width'=>'60','no_edit_table'=>1,'round'=>0));
            $this->addFTL(array('name'=>'Р-нг макс','name_mob'=>'Р-нг<br> макс','name_field'=>'reiting_max','bd_field'=>'reiting_max','width'=>'60','no_edit_table'=>1,'round'=>0));
            $this->addFTL(array('name'=>'Р-нг AVG','name_mob'=>'Р-нг<br> AVG','name_field'=>'reiting_avg','bd_field'=>'reiting_avg','width'=>'60','no_edit_table'=>1,'round'=>0));
            //$this->addFTL(array('name'=>'Удалить','type'=>'delete','width'=>'40','name_field'=>'name'));
            //   $this->addFTL(array('name'=>'Год<br /> рождения','name_field'=>'god_rogd','bd_field'=>'god_rogd','width'=>'30','filter'=>1));
            //  $this->addFTL(array('name'=>'Дата<br /> регистрации','name_field'=>'dat','bd_field'=>'dat','width'=>'70'));
            $this->addFTL(array('name'=>'Рейтинг<br /> ФНТУ','name_mob'=>'Р-нг<br>ФНТУ','name_field'=>'reiting_ukraine','bd_field'=>'reiting_ukraine','width'=>'20','no_edit_table'=>1));
            $this->addFTL(array('name'=>'Стартовий<br />рейтинг','name_mob'=>'Старт<br>р-нг','name_field'=>'start_reiting','bd_field'=>'start_reiting','width'=>'20','round'=>0));
            //  if (!$_SESSION['is_mobile']) {
            $this->addFTL(array('name' => 'Стать', 'name_field' => 'sex', 'bd_field' => 'sex', 'width' => '30', 'is_img' => 1));
            if ($_SESSION['gt']['user_rule'] < 10)
                $this->addFTL(array('name' => 'День народження', 'name_field' => 'birthday', 'type' => 'date', 'bd_field' => 'birthday', 'width' => '50'));
            else
                $this->addFTL(array('name' => 'Рік народження', 'name_mob' => 'Рік<br>нар.', 'name_field' => 'birthday', 'type' => 'date', 'onlyYear' => 1, 'bd_field' => 'birthday', 'width' => '50'));

            if ($_SESSION['gt']['user_rule'] < 10) {
            }
        }
        //  $this->addFTL(array('name'=>'Подв.<br />оплаты','type'=>'plus_minus','name_field'=>'podtv','width'=>'80'));
        if ($_SESSION['gt']['user_rule']==1) {
            if ($_SESSION['is_mobile']) {
                $this->addFTL(array('name' => '<span class="f14 fw700 line14">Вид-ти</span>', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
            }else
                $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
        }
        $this->addFF(array('name'=>'ПІБ гравця','name_field'=>'name','bd_field'=>'name','required'=>'ПІБ гравця обов"язкове', 'pattern'=>'.{3,}'));
        $this->addFF(array('name'=>'Фото гравця','name_field'=>'photo','bd_field'=>'photo', 'type'=>'img'));
        $this->addFF(array('name'=>'Група','name_field'=>'grp','type'=>'ProstSpr', 'id_spis'=>'2', 'bd_field'=>'grp'));
        $this->addFF(array('name'=>'День народження','name_field'=>'birthday','type'=>'date','bd_field'=>'birthday', 'width'=>'50'));
        $this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));
        $this->addFF(array('name'=>'ID гравця Ligas','name_field'=>'id_reiting','bd_field'=>'id_reiting'));
        $this->addFF(array('name'=>'ПІБ гравця Ligas','name_field'=>'name_ligas','bd_field'=>'name_ligas'));
        $this->addFF(array('name'=>'Рік народженння Ligas','name_field'=>'god_rogd','bd_field'=>'god_rogd','readonly'=>1 ));
        $this->addFF(array('name'=>'Місто Ligas','name_field'=>'city','bd_field'=>'city'));
        $this->addFF(array('name'=>'Стать Ligas', 'type'=>'radiobox', 'name_field'=>'sex','bd_field'=>'sex',
            'valRadio'=>[
                ['name'=>'Чоловік','val'=>'m'],
                ['name'=>'Жінка','val'=>'f'],
            ]
        ));
        $this->addFF(array('name'=>'Рейтинг ФНТУ Ligas','name_field'=>'reiting_ukraine','bd_field'=>'reiting_ukraine','required_custom'=>'onlyNumber'));
        $this->addFF(array('name'=>'Рейтинг стартовий','name_field'=>'start_reiting','bd_field'=>'start_reiting','required_custom'=>'onlyNumber'));
//$this->addFF(array('name'=>'Рейтинг клубу','name_field'=>'reiting','bd_field'=>'reiting', 'required_custom'=>'onlyNumber'));
        $this->addFF(array('name'=>'Сплачений членский внесок','type'=>'Checkbox','name_field'=>'is_opl_reiting','bd_field'=>'is_opl_reiting'));
        $this->addFF(array('name'=>'НЕАКТИНВИЙ гравець','type'=>'Checkbox','name_field'=>'not_use','bd_field'=>'not_use'));
//$this->addFF(array('name'=>'Стать m/f','name_field'=>'sex','bd_field'=>'sex','size'=>'1','maxlength'=>1));
        $this->addFF(array('name'=>'Клуб по-замовченню','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club'));
        $this->addFF(array('name'=>'Місто по-замовченню','name_field'=>'city_def','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city_def'));

        $this->addFF(array('name'=>'Дата реєстрації','name_field'=>'dat','type'=>'date','bd_field'=>'dat'));

        $this->addFF(array('name'=>'Примітка','name_field'=>'prim','bd_field'=>'prim'));
        if ($_SESSION['gt']['user_rule']==1)
            $this->addFF(array('name'=>'КЛОН ГРАВЦЯ (для об"єднання з основним)','width'=>'250',
                'type'=>'out_keynosql',
                'name_field'=>'player_id',
                'out_result_field'=>'name',
                'bd_field'=>'player_id',
                'mess'=>'Виберіть гравця',
                'where'=>' and ispara=0 ',
                'table'=>T_PLAYERS,
                'no_vubor' => '',
                'width'=> '500', // ширина окна
                //  'required'=>'Гравець обов"язково',
                'speedsearch'=>array('min_letter'=>3,
                    'result_fields_dop'=>array('id'),'table'=>T_PLAYERS,'where'=>' ispara=0  and ' ),
                'module'=>'players',
                'descr_table'=>array(
                    array('name'=>'ПІБ гравця','name_field'=>'name','width'=>'250','filter'=>'1'),
                    array('name'=>'Рік народження','name_field'=>'god_rogd','width'=>'20'),
                    array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                    array('name'=>'ID Ligas','name_field'=>'id_reiting','width'=>'50','filter'=>'1'),
                    array('name'=>'ПІБ Ligas','name_field'=>'name_ligas','width'=>'50','filter'=>'1'),
                    array('name'=>'Рейтинг ФНТУ','name_field'=>'reiting_ukraine','width'=>'50','filter'=>'1'),
                    array('name'=>'Місто','name_field'=>'city','width'=>'50','filter'=>'1'),
                    array('name'=>'Стать m/f','name_field'=>'sex','width'=>'50','filter'=>'1'),
                    array('name'=>'Примітка по гравцю','name_field'=>'prim','width'=>'50','filter'=>'1'),

                )
            ));
        //    $this->addFF(array('name'=>'Фото гравця','name_field'=>'photo','bd_field'=>'photo', 'type'=>'img'));

//поиск с фильтраци

        $strSearch='';
        if (!empty($fio_search))
        {
            //s($fio_search);
            $strSearch = ' AND name LIKE "%'.$fio_search.'%"';
            // $dop_where = $strSearch;
        }
//unset($_SESSION['players']['where']);
        if  (empty($_SESSION['players']['sort']))  $_SESSION['players']['sort']='reiting';
        if  (empty($_SESSION['players']['sort_type']))  $_SESSION['players']['sort_type']='desc';
//if ($_SESSION['gt']['user_rule']<>1)   $_SESSION['players']['where']=' and ispara=0 and not_use=0 and exists(select * from bs_turnirplayers where player_id=p.id)';
        if ($_SESSION['gt']['user_rule']<>1)   $_SESSION['players']['where']=' and num_reiting>0 and ispara=0 and not_use=0 '.$strSearch;
        else $_SESSION['players']['where']=' and ispara=0 '.$strSearch;
        $_SESSION['players']['sort_default']=' num_reiting asc';

        $this->setTableModule(T_PLAYERS);

        self::$nameZ='';
        //self::$nameZList='<span class="zzagl">Гравці</span>';
        self::$nameZList='Гравці';
        self::$nameZEdit='Редагування гравця';
        if ($_SESSION['gt']['user_rule']<10)
            self::$submenu_list =array(
                'back' => array('module' => 'turnirs', 'action' => 'list'),
            );
        if ($_SESSION['gt']['user_rule']==1)
            self::$submenu_list =array(
                'back' => array('module' => 'turnirs', 'action' => 'list'),
                // 'report_ok' => array('menu_name'=>'Перерахувати рейтинг по всім турнірам','module' => 'players', 'action' => 'raschet_all'),
                'truck' => array('menu_name'=>'Отримати рейтинг ФНТУ на сьогодні','module' => 'players', 'action' => 'get_reiting'),

            );
        if ($_SESSION['gt']['user_rule']<10 && $_SESSION['gt']['user_rule']>1)
            self::$submenu_list =array(
                'back' => array('module' => 'turnirs', 'action' => 'list'),
//              'report_ok' => array('menu_name'=>'Перерахувати рейтинг по всім турнірам','module' => 'players', 'action' => 'raschet_all'),
                'truck' => array('menu_name'=>'Отримати актуальний рейтинг ФНТУ по всіх гравцях','module' => 'players', 'action' => 'get_reiting'),

            );

        /*self::$submenu_list =array(
         // 'filter' => array('module' => 'players'),
            );*/
        self::InitLeaguesMenu();
    }
}
function get_name_player($field,$id,$data)
{
    $class='f14';
    $name='';
    if ($data['sex']=='f') {
        $sex= '<svg width="10px" height="14px" > <use xlink:href="#woman" ></use></svg>';
    } else{
        $sex= '<svg width="14px" height="14px"> <use xlink:href="#man" ></use></svg>';

    }
    $tdat='';
    if (!empty($data['god_rogd']) ) {
        $tdat =$data['god_rogd'];
    }
    if (!empty($data['birthday']) && $data['birthday']!='0000-00-00'){
        $date = new DateTimeImmutable($data['birthday']);
        $tdat = $date->format('d.m.Y');
        $tdat =   $date->format('Y');
    }
    $tdat = !empty($tdat) ? '<div class="godrord_mob"> - '.$tdat.'</div>' : '';
    $name ='<a href="#players-statistics-id='.$id.'" target="_blank" class="'.$class.'  ">'.$data['name'].'</a><div class="svgSex_player"><div class="sexMob">'.$sex.'</div>  '.$tdat.'</div>';

    return $name;
}