<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class TurnirsObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
       $sql_leag='';
      $league_id = poste('league_id');
      if (!empty($league_id)){
         // $post_return = 'league_id='.$league_id;
        //  SystemClass::setPost_return($post_return);
         // $_SESSION['POST_RETURN'] =$post_return;
          $sql_leag =   ' and league_id='.$league_id.' ';
          self::$aParent[0]= ['name_field'=>'league_id', 'table'=>T_TURNIRS, 'type'=>'Hidden'];
      }

      $sWhere = ' AND virt=0 '.$sql_leag;
      self::$theed_tr_class='th_players_mob';
      self::$table_class='table_mob_turn';
      if ( empty($_SESSION['gt']['club'])) {
          $city = poste('city');
          $club = poste('club');
          $_SESSION['turnit']['filter']['city'] = isset($city) ? $city : (!empty($_SESSION['turnit']['filter']['city']) ? $_SESSION['turnit']['filter']['city'] : '');
          $_SESSION['turnit']['filter']['club'] = isset($club) ? $club : (!empty($_SESSION['turnit']['filter']['club']) ? $_SESSION['turnit']['filter']['club'] : '');
          $id_spis = 4; // міста
          $name_vibor = 'Виберіть місто';
          $name_all = 'Всі міста';
          $id = 'city-chosen-select';
          $name_field = 'city';
          $data_id = $_SESSION['turnit']['filter']['city'];
          //  SystemClass::setJava_script($this->Java_script);

          $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
          $id_spis = 3; // клуби
          $name_vibor = 'Виберіть клуб';
          $id = 'club-chosen-select';
          $name_field = 'club';
          $name_all = 'Всі клуби';
          $data_id = $_SESSION['turnit']['filter']['club'];
          $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
          if (!empty($_SESSION['turnit']['filter']['city'])) $sWhere .= ' and city=' . $_SESSION['turnit']['filter']['city'];
          if (!empty($_SESSION['turnit']['filter']['club'])) $sWhere .= ' and club=' . $_SESSION['turnit']['filter']['club'];
          $action =  SystemClass::getAction();

            $post_return=!empty($city) || !empty($club) ?  '&club='.$club.'&city='.$city : '';

          $_SESSION['POST_RETURN'] = $post_return;
       //   SystemClass::setPost_return($post_return);
          if ($action=='list'){
              $_SESSION['JAVA_SCRIPT'] = ' chosen_vibor_filter_turnir(200);';
              $_SESSION['MESSAGE_AJAX'] = '<div class="ms-5 w-100" style="text-shadow:none">' . $txtCity . $txtClub . '</div>';

          }
          }
      if ($_SESSION['is_mobile']) {
          self::$table_class='table_mob_player';
          $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
          $this->addFTL(array('name' => '<span class="f14 fw700 line14">Дата<br> турніру</span>', 'function'=>'get_dat_turn',
              'type' => 'get_func', 'name_field' => 'dat',
              'no_slash' => 1, 'width_mob' => '49','classAlign'=>'ws30','class'=>'break'));
          $this->addFTL(array('name' => '<span class="f14 fw700">Назва турніру</span>', 'type' => 'get_func', 'classAlign' => 'text-start', 'function' => 'get_name_turnir',

              'width' => '600', 'name_field' => 'name', 'no_slash' => 1));
          $this->addFTL(array('name' => '<span class="f14 fw700 line14">К-ть<br> гравців</span>', 'type' => 'field', 'width' => '90',
              'width_mob' => '23', 'name_field' => 'cnt_players'));
          $this->addFTL(array('name' => '<span class="f14 fw700">Клуб</span>', 'function'=>'get_name_club',
              'name_field' => 'club', 'bd_field' => 'club', 'width' => '80', 'width_mob' => '74',
              'type' => 'get_func'));
            $this->addFTL(array('type'=>'onlybd_ProstSpr', 'name_field'=>'city','bd_field'=>'city'));
            $this->addFTL(array('type'=>'onlybd_ProstSpr', 'name_field'=>'club','bd_field'=>'club'));




      }else {
// описание полей таблицы модуля    
          $this->addFTL(array('name' => '№', 'type' => 'number', 'width' => '20', 'width_mob' => '22'));
          $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
          $this->addFTL(array('name' => 'Дата турніру', 'name_mob' => 'Дата<br>турніру', 'type' => 'date', 'width' => '50', 'name_field' => 'dat', 'no_slash' => 1, 'width_mob' => '49'));
          $this->addFTL(array('name' => 'Назва турніру', 'type' => 'get_func', 'classAlign' => 'text-start', 'function' => 'get_name_turnir',

              'width' => '600', 'name_field' => 'name', 'no_slash' => 1));
          /*$this->addFTL(array('name'=>'Назва турніру',
                            'type'=>'tree',
                            'name_field'=>'name',
                            'module'=>'etapresult',
                            'action'=>'show',
                            'name_field_child'=>'turnir_id',
                            ));*/
          $this->addFTL(array('name' => 'Результати', 'name_mob' => 'Резу<br>льт<br>ати', 'type' => 'anyaction', 'width_mob' => '23', 'width' => '60', 'svg_desctop' => 'turn_result_mob|20|20', 'svg_mobile' => 'turn_result_mob|12|12', 'name_field_child' => 'turnir_id', 'action' => 'show', 'module' => 'etapresult'));
          $this->addFTL(array('name' => 'Ігри', 'type' => 'anyaction', 'width_mob' => '23', 'width' => '60', 'svg_desctop' => 'turn_game_mob|20|20', 'svg_mobile' => 'turn_game_mob|15|15', 'name_field_child' => 'turnir_id', 'action' => 'list', 'module' => 'reiting'));

          $this->addFTL(array('name' => 'Статистика<br />турніра<br />(Гравці)', 'name_mob' => 'Стат<br>ист<br>ика', 'type' => 'anyaction', 'width_mob' => '23', 'width' => '60', 'svg_desctop' => 'turn_stat_mob|20|20', 'svg_mobile' => 'turn_stat_mob|15|15', 'name_field_child' => 'turnir_id', 'action' => 'list', 'module' => 'turnirsplayers'));
          $this->addFTL(array('name' => 'К-ть гравців', 'name_mob' => 'К-ть<br>грав<br>ців', 'type' => 'field', 'width' => '90', 'width_mob' => '23', 'name_field' => 'cnt_players'));
          $this->addFTL(array('name' => 'Місто', 'name_field' => 'city', 'bd_field' => 'city', 'width' => '80', 'width_mob' => '44', 'type' => 'prostspr'));
          $this->addFTL(array('name' => 'Клуб', 'name_field' => 'club', 'bd_field' => 'club', 'width' => '80', 'width_mob' => '44', 'type' => 'prostspr'));

          $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
      }
//================================================================================================
// описание полей формы модуля при редактировании или добавления
      // 'pattern'=>'[0-9]{1,}' -- в данноим случае только цифры и минимум 1 символ
      // 'pattern'=>'[0-9]{1,2}' -- в данноим случае только цифры и минимум 1 символ и максимум 2
      // 'pattern'=>'.{1,2}' -- в данноим случае  минимум 1 символ и максимум 2
 $this->addFF(array('name'=>'Лига','name_field'=>'league_id','type'=>'hidden'));
 $this->addFF(array('name'=>'Назва турніру','name_field'=>'name','required'=>'Назва турніру обов"язкова (мінімум 3 символа)', 'pattern'=>'.{3,}' ));
 $this->addFF(array('name'=>'Дата турніру','name_field'=>'dat','type'=>'date','required'=>'Дата турнира объязательна'));
 //$this->addFF(array(  'name'=>'К-во участников','name_field'=>'cnt_players','required'=>'К-во игроков объязательно','required_custom'=>'noSpecialCaracters'));
 $this->addFF(array(  'name'=>'К-ть учасиків','name_field'=>'cnt_players','readonly'=>1));

  $this->addFF(array('name'=>'Рейтинг ФНТУ ЧОЛ(посів)','type'=>'Checkbox','name_field'=>'is_reiting','bd_field'=>'is_reiting'));
  $this->addFF(array('name'=>'Рейтин ФНТУ ЖІН(посів)','type'=>'Checkbox','name_field'=>'is_reiting_w','bd_field'=>'is_reiting_w'));
  $this->addFF(array('name'=>'ID турніру LIGAS','name_field'=>'turnir_id_ligas'));
 // $this->addFF(array('name'=>'SESSION  LIGAS','name_field'=>'ligas_session'));
  $this->addFF(array('name'=>'Кількість столів','name_field'=>'tables','size'=>'2','maxlength'=>2,'pattern'=>'[0-9]{1,2}', 'required'=>'Кількість столів обов"язкова'));
  $this->addFF(array('name'=>'Виберіть столи для турніра', 'name_field'=>'selected_tables','type' => 'func','function'=>'get_select_tables'));


  $club = !empty($_SESSION['gt']['club']) ? $_SESSION['gt']['club'] : 0;
 $city = !empty($_SESSION['gt']['city']) ? $_SESSION['gt']['city'] : 0;
 if ( !empty($_SESSION['gt']['club']))
     $this->addFF(array('name'=>'Клуб по замовченню','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club','disabled'=>1, 'def'=>$club));
else
    $this->addFF(array('name'=>'Клуб','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club'));
      if ( !empty($_SESSION['gt']['city']))
      $this->addFF(array('name'=>'Місто по замовченню','name_field'=>'city','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city','disabled'=>1,  'def'=>$city));
      else
      $this->addFF(array('name'=>'Місто','name_field'=>'city','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city'));

      $this->addFF(array('name'=>'Парний турнір','type'=>'Checkbox','name_field'=>'ispara','bd_field'=>'ispara'));
      $this->addFF(array('name'=>'Без обрахунку 0 рейтинга в LIGAS','type'=>'Checkbox','name_field'=>'is_no_send_ligas','bd_field'=>'is_no_send_ligas'));
  $this->addFF(array('name'=>'Шаблон до 3х перемог','type'=>'Checkbox','name_field'=>'is_shablon3','bd_field'=>'is_shablon3'));
  $this->addFF(array('name'=>'Шаблон до 2х перемог','type'=>'Checkbox','name_field'=>'is_shablon2','bd_field'=>'is_shablon2'));
  $this->addFF(array('name'=>'Тестовий турнір','type'=>'Checkbox','name_field'=>'group_id','bd_field'=>'group_id'));
  $this->addFF(array('name'=>'Командний турнір','type'=>'Checkbox','name_field'=>'is_command','bd_field'=>'is_command'));
  $this->addFF(array('name'=>'Домашня команда','name_field'=>'command_name1' ));
  $this->addFF(array('name'=>'Гостьова команда','name_field'=>'command_name2' ));

      $this->setTableModule(T_TURNIRS);
  if  (empty($_SESSION['turnirs']['sort']))  $_SESSION['turnirs']['sort']='dat';
if  (empty($_SESSION['turnirs']['sort_type']))  $_SESSION['turnirs']['sort_type']='desc';
 $_SESSION['turnirs']['sort_default']='id desc';
      if ($_SESSION['gt']['user_rule']<10 && !empty($_SESSION['gt']['club']))
      {
          $sWhere .=  ' and club= '. $_SESSION['gt']['club'];
      }
      $_SESSION['turnirs']['where'] =$sWhere;
      //$this->setTypeModule('tree');
   //   $_SESSION['JAVA_SCRIPT'] ='set_popover();';
  self::$nameZ='';
 self::$nameZList='';
     // self::$nameZList='<span class="zzagl">Турніри</span>';
      self::$nameZList='Турніри';
 self::$nameZEdit='::Редагування турніру';
      if ($_SESSION['gt']['user_rule']<10)
      self::$submenu_list =array(
   // 'help' => array('menu_name'=>'Перерахувати штраф рейтингу','module' => 'turnirs', 'class' =>'mess_shtraph', 'mess' =>'Ви дійсно хочите розрахувати систему штрафів за минулий місяць?', 'action' => 'raschet_shtraph'),
     'back' => array('module' => 'turnirs', 'action' => 'list'),
    );
    self::$submenu_edit = array(
    'back' => array('module' => 'turnirs', 'action' => 'list'),
    'save' => array('module' => 'turnirs', 'action' => 'edit_ok'),
  //  'report_ok' => array('module' => 'turnirs', 'action' => 'raschet')
    );
//      if (!empty($league_id))
          self::InitLeaguesMenu();
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
  }  
}
function get_dat_turnir($field,$id)
{
    $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and cnt_games is not null)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$id;
    $vData = db_row($sql);
    $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
    $date = new DateTimeImmutable($vData[$field]);
   if ($vData['cnt_g']>0  ){
       $class='blue_color';
       $title='Турнір порахований';
   }elseif($Work_turnir>0){
       $class='red_color';
       $title='Турнір розпочато';
   }else{
       $class= 'green_color';
       $title='Турнір ще не порахований';
   }
    $tdat = '
       <span class="f14 d-inline-block '.$class.'" data-bs-toggle="tooltip" title="'.$title.'"> 
'. $date->format('d.m.Y').'</span>';
    return $tdat;
}
function get_name_turnir($field,$id)
{
    $league_id = poste('league_id');
    $name='';
    $sql='select dat,date_raschet, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$id;
    $vData = db_row($sql);
    $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
   // $date = new DateTimeImmutable($vData[$field]);
    if ($vData['cnt_g']>0 && !empty($vData['date_raschet']) ){
        $class='blac_color';
        $title='Турнір порахований';
    }elseif($Work_turnir>0){
        $class='coral_color';
        $title='Турнір розпочато';
    }else{
        $class= 'green_color';
        $title='Турнір ще не порахований';
    }
    if ($_SESSION['is_mobile']) {$class.=' f12 fw700 nopodch';};

    $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$id,'cn');
    $turnirName=db_field('SELECT name  FROM '.T_TURNIRS.' r WHERE id='.$id,'name');
    $hrefLeague= !empty($league_id) ? '&league_id='.$league_id : '';
if ($Work_turnir>0){
        $name ='<span  id="catalog_name_id_29" data-bs-toggle="tooltip" title="'.$title.'"><a href="#etapresult-show-turnir_id='.$id.$hrefLeague.'" class="'.$class.' ajax_send ">'.$turnirName.'</a> </span>';
    }else
    {
        $name ='<span  id="catalog_name_id_29" data-bs-toggle="tooltip" title="'.$title.'"><a href="#turnirsplayers-list-turnir_id='.$id.$hrefLeague.'" class="'.$class.' ajax_send">'.$turnirName.'</a></span> ';
    }
    return $name;
}
function get_name_club($field,$id,$data)
{
    $class='f14';
    $name='';
   $name ='<div class="txt_coral f12">'.$data['club_name'].'</div><div class="f12">'.$data['city_name'].'</div>';

    return $name;
}

function get_select_tables($field,$id,$data){


   $selected_tables =     !empty($data['selected_tables']) ? json_encode(explode(',', $data['selected_tables'])) : json_encode([]);

    $_SESSION['JAVA_SCRIPT_DOP'] = ' select2_vibor_tables('.$selected_tables.'); ';
    $html='<select id="tableList" name="tableList[]" class="form-select" multiple><option value="0">первий</option></select>';
    return $html;
}
function get_dat_turn($field,$id,$data)
{
    $class='f14';
    $name='';

    if (!empty($data['dat']) && $data['dat']!='0000-00-00'){
        $date = new DateTimeImmutable($data['dat']);
        $tdat = $date->format('d.m');
        $tdatY =   $date->format('Y');
    }
   $name ='<div class="f12">'.$tdat.'</div><div class="f10">'.$tdatY.'</div>';

    return $name;
}


?>