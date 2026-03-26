<?php
//s('turnirsUP');
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class LeaguesObject extends ObjectRT
{
    //$this-> = 'tree';
    function init()
    {
        $action =  SystemClass::getAction();
        $league_id = poste('league_id'); // Определяем league_id из POST
        $sWhere='';
        if ( empty($_SESSION['gt']['club'])) {
            $city = poste('city');
            $club = poste('club');
            $_SESSION['turnit']['filter']['city'] = isset($city) ? $city : (!empty($_SESSION['turnit']['filter']['city']) ? $_SESSION['turnit']['filter']['city'] : '');
            $_SESSION['turnit']['filter']['club'] = isset($club) ? $club : (!empty($_SESSION['turnit']['filter']['club']) ? $_SESSION['turnit']['filter']['club'] : '');
            
            // Показываем фильтры только на странице списка лиг, а не на странице конкретной лиги
            if ($action=='list' && empty($league_id)){
                $id_spis = 4; // міста
                $name_vibor = 'Виберіть місто';
                $name_all = 'Всі міста';
                $id = 'city-chosen-select';
                $name_field = 'city';
                $data_id = $_SESSION['turnit']['filter']['city'];
                //  SystemClass::setJava_script($this->Java_script);

                $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
                $id_spis = 3; // клуби
                $name_vibor = 'Виберіть організатора';
                $id = 'club-chosen-select';
                $name_field = 'club';
                $name_all = 'Всі організатори';
                $data_id = $_SESSION['turnit']['filter']['club'];
                $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
                
                $_SESSION['JAVA_SCRIPT'] = ' chosen_vibor_filter_turnir(200);';
                $_SESSION['MESSAGE_AJAX'] = '<div class="ms-5 w-100" style="text-shadow:none">' . $txtCity . $txtClub . '</div>';
            } else if ($action=='list' && !empty($league_id)){
                // Если есть league_id, явно очищаем фильтры, чтобы они не отображались
                unset($_SESSION['MESSAGE_AJAX']);
                $_SESSION['JAVA_SCRIPT'] = '';
            }
            
            if (!empty($_SESSION['turnit']['filter']['city'])) $sWhere .= ' and city=' . $_SESSION['turnit']['filter']['city'];
            if (!empty($_SESSION['turnit']['filter']['club'])) $sWhere .= ' and club=' . $_SESSION['turnit']['filter']['club'];


            $post_return=!empty($city) || !empty($club) ?  '&club='.$club.'&city='.$city : '';

            $_SESSION['POST_RETURN'] = $post_return;
            //   SystemClass::setPost_return($post_return);
        }

        $this->addFTL(array('name' => '№', 'type' => 'number', 'width' => '20', 'width_mob' => '22'));
        $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
        $this->addFTL(array('name' => 'Дата створення', 'name_mob' => 'Дата<br>створення', 'type' => 'date', 'width' => '50', 'name_field' => 'dat', 'no_slash' => 1, 'width_mob' => '49'));
        $this->addFTL(array('name' => 'Назва ліги', 'classAlign' => 'text-start', 'width' => '600',
            'type'=>'text', 'type' => 'get_func','function' => 'get_name_league',
            'name_field' => 'name', 'no_slash' => 1));
        $this->addFTL(array('name' => 'Організатор', 'name_field' => 'club', 'bd_field' => 'club', 'width' => '80', 'width_mob' => '44', 'type' => 'prostspr'));
        $this->addFTL(array('name' => 'Місто', 'name_field' => 'city', 'bd_field' => 'city', 'width' => '80', 'width_mob' => '44', 'type' => 'prostspr'));

        $this->addFTL(array('name'=>'Статус','name_field'=>'status','bd_field'=>'status','width'=>'80','type'=>'prostspr'));
        $this->addFTL(array('type'=>'onlybd_ProstSpr', 'name_field'=>'club','bd_field'=>'club','no_sql'=>1));
        $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));


        $this->addFF(array('name'=>'Назва ліги','name_field'=>'name','required'=>'Назва ліги обов"язкова (мінімум 3 символа)', 'pattern'=>'.{3,}' ));
        $this->addFF(array('name'=>'Дата створення ліги','name_field'=>'dat','type'=>'date','required'=>'Дата ліги объязательна'));
        $this->addFF(array('name'=>'Статус','name_field'=>'status','type'=>'ProstSpr', 'id_spis'=>'7', 'bd_field'=>'status'));
        $this->addFF(array('name'=>'Організатор','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club'));
        $this->addFF(array('name'=>'Місто','name_field'=>'city','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city'));

        $this->addFF(array('name'=>'Командная ліга','type'=>'Checkbox','name_field'=>'is_team_league','bd_field'=>'is_team_league'));

        $this->addFF(array('name'=>'Інформація по турніру','name_field'=>'dop_info','type'=>'Redaktor', 'id_spis'=>'7', 'bd_field'=>'dop_info'));
        $this->setTableModule('bs_leagues');
        $_SESSION['leagues']['where'] =$sWhere;
        self::$nameZ='';
        self::$nameZList='';
        // self::$nameZList='<span class="zzagl">Турніри</span>';
        self::$nameZList='Турніри';
        self::$nameZEdit='::Редагування турніру';
        if ($_SESSION['gt']['user_rule']<10)
            self::$submenu_list =array(
               // 'help' => array('menu_name'=>'Перерахувати штраф рейтингу','module' => 'turnirs', 'class' =>'mess_shtraph', 'mess' =>'Ви дійсно хочите розрахувати систему штрафів за минулий місяць?', 'action' => 'raschet_shtraph'),
                'back' => array('module' => 'leagues', 'action' => 'list'),
            );
      //  self::InitLeaguesMenu();
        self::$submenu_edit = array(
            'back' => array('module' => 'leagues', 'action' => 'list'),
            'save' => array('module' => 'leagues', 'action' => 'edit_ok'),
        );
    }
}
function get_name_league($field,$id)
{
    $name='';
    $sql='select (SELECT teg FROM `bs_spr-spis-values` WHERE  STATUS=id) AS status,name    
  from bs_leagues r  where  r.id='.$id;
    $vData = db_row($sql);
   // $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
    // $date = new DateTimeImmutable($vData[$field]);
    if ($vData['status']=='finish' ){
        $class='blac_color';
        $title='Ліга завершена';
    }elseif($vData['status']=='active' ){
        $class='coral_color';
        $title='Ліга розпочата';
    }else{
        $class= 'green_color';
        $title='Ліга не розпочата';
    }
    if ($_SESSION['is_mobile']) {$class.=' f12 fw700 nopodch';};

     $turnirName=$vData['name'];

        $name ='<span  id="catalog_name_id_29" data-bs-toggle="tooltip" title="'.$title.'"><a href="#turnirs-list-league_id='.$id.'" class="'.$class.' ajax_send">'.$turnirName.'</a></span> ';

    return $name;
}
