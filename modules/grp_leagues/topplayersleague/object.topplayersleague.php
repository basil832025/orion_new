<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class topplayersLeagueObject extends ObjectRT
{
    //$this-> = 'tree';
    function init ()
    {
//s('tyt');
// узнаем парный ли этот турнир
//s($_POST);
        $ispara=0;
        $turnir_id = poste('turnir_id');
        $league_id = poste('league_id');
        $sWhere = ' and  league_id='.$league_id;
        $_SESSION['topplayersleague']['where'] =$sWhere;
        $_SESSION['topplayersleague']['sort_default']=' points desc';
        if  (empty($_SESSION['topplayersleague']['sort']))  $_SESSION['topplayersleague']['sort']='points';
            if  (empty($_SESSION['topplayersleague']['sort_type']))  $_SESSION['topplayersleague']['sort_type']='desc';
        self::InitLeaguesMenu();
        $virt = poste('virt');
//s('$virt='.$virt);
        self::$table_class='table_mob_turn';
        if (!empty($turnir_id))
        {
            $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
            $aTurnir= db_row($sql);

        }

        $url_back = 'leagues-list';
        if ($_SESSION['is_mobile'] ){

            SystemClass::$Java_script_module='show_zag_left("#'.$url_back.'");';
        }else{
            $show_zag_left='show_zag_center();show_zag_left_big("#'.$url_back.'");';
            SystemClass::$Java_script_module=$show_zag_left;
        }
// описание полей таблицы модуля
        $this->addFTL(array('name'=>'№','type'=>'number','width'=>'20','width_mob'=>'22'));

        $this->addFTL(array('name'=>'ПІБ гравця','type'=>'out_key',
            'oper' => 'edit','target'=>true, 'width_mob'=>'130',
            'action'=>'statistics','module'=>'players', 'out_module_id'=>'id', 'out_module_result'=>'id_pl',
            'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'name',
            'width'=>'200','name_field'=>'player_id','classAlign'=>'text-start'));

            $this->addFTL(array('name' => 'Балів', 'type' => 'field', 'width' => '9', 'name_field' => 'points'));
            $this->addFTL(array('name' => 'К-ть зіграних<br /> турнірів', 'name_mob' => 'К-ть<br />зіграних<br /> турнірів', 'type' => 'field', 'width' => '9', 'name_field' => 'turnirs'));
            $this->addFTL(array('name' => 'К-ть зіграних<br /> ігор', 'name_mob' => 'К-ть<br />зіграних<br /> ігор', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_games'));
            $this->addFTL(array('name' => 'К-ть виграних<br /> ігор', 'name_mob' => 'К-ть<br /> перемог', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_wins'));
            $this->addFTL(array('name' => 'К-ть поразок', 'name_mob' => 'К-ть<br />  поразок', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_lose'));
            $this->addFTL(array('name' => 'К-ть сетів', 'name_mob' => 'К-ть<br />  сетів', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_sets'));
            $this->addFTL(array('name' => 'К-ть виграних<br /> сетів', 'name_mob' => 'К-ть виграних<br />  сетів', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_sets_win'));
            $this->addFTL(array('name' => 'К-ть програних<br /> сетів', 'name_mob' => 'К-ть програних<br />  сетів', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_sets_lose'));


        /*     $this->addFTL(array('name'=>'Поточний рейтинг<br /> клубу','name_mob'=>'Поточ<br />ний р-нг<br /> клубу','type'=>'out_key',
                 'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting',
                 'width'=>'20','name_field'=>'reiting','round'=>0));*/
        $this->addFTL(array('name'=>'Рейтинг ФНТУ','type'=>'out_key',
            'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting_ukraine',
            'width'=>'20','name_field'=>'reiting_ukraine'));

        if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] == 1) {
            $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'id'));
        }

//================================================================================================
//================================================================================================


// описание полей формы модуля при редактировании или добавления

        $this->setTableModule('bs_top_players');
        //$this->setTypeModule('tree');
        if (!empty($league_id)) {
            $name_turnir = db_row('select name,dat  from `bs_leagues` where id=' . $league_id);

            if ($_SESSION['is_mobile'] )
                $nameZ='<div class="compare_zagl">Топ гравців ліги "'.$name_turnir['name'].'"</div>';
            else
                $nameZ='<div class="poriv_zag">Топ гравців ліги  "'.$name_turnir['name'].'" </div>';

            self::$nameZList=$nameZ;



            $nameZList = '';

            //    SystemClass::setZaglModule($nameZList);
        }


        self::$aFilters=array(
            'name'=>'По имени',
            'articul'=>'По артикулам',
        );
        /*    self::$submenu_edit = array(
           'back' => array('module' => 'turnirsplayers', 'action' => 'list'),
           'save' => array('module' => 'turnirsplayers', 'action' => 'edit_ok'),
             );
        */


        self::$aParent[0]= ['name_field'=>'turnir_id', 'table'=>T_TURNIRS, 'type'=>'Hidden'];
        self::$aParent[1]= ['name_field'=>'league_id', 'type'=>'Hidden'];
    }

}

?>
