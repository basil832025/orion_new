<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ReitingObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
  //    s($_SESSION['gt']);
$etap_id = poste('etap_id');
$turnir_id = poste('turnir_id');
$filter = poste('filter');

      $post_return = '&filter='.$filter;
      SystemClass::$post_return_dop =$post_return;

      if ($_SESSION['is_mobile'] ){

          SystemClass::$Java_script_module='show_zag_left("#turnirs-list");chosen_vibor_filter_turnir();';
      }else
      {
          $show_zag_left='show_zag_center();show_zag_left_big("#turnirs-list");';
          SystemClass::$Java_script_module=' chosen_vibor_filter_turnir(240);'.$show_zag_left;
      }


/*      if (!$_SESSION['is_mobile'])
      $_SESSION['JAVA_SCRIPT'] = ' chosen_vibor_filter_turnir(200);';
      else
      $_SESSION['JAVA_SCRIPT'] = ' chosen_vibor_filter_turnir();';*/
      self::$theed_tr_class='th_games_mob';
      self::$table_class='table_mob_turn';

      $postButton= !empty($etap_id)  ? '&etap_id='.$etap_id : '';
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'5')); 
//s($postButton);
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'5', 'postButton'=>$postButton)); 
//$this->addFTL(array('name'=>'Дата турнира','type'=>'out_key',
  //  'table'=>T_TURNIRS, 'parent_field'=>'turnir_id','out_result_field'=>'dat',
 //   'width'=>'20','name_field'=>'dat'));
/*$this->addFTL(array('name'=>'Рейтинг ФНТУ<br />1 гравця','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'pl_id_1','out_result_field'=>'reiting_ukraine',
    'width'=>'10','name_field'=>'reiting_ukraine1'));
      $this->addFTL(array('name'=>'Рейтинг КЛУБУ<br />1 гравця','type'=>'field','width'=>'9','name_field'=>'rt_id_1_beg'));
*/
$this->addFTL(array('name'=>'Гравець 1 <br>Дельта<br> рейтингу','name_mob'=>'Гравець 1<br>&#916; р-гу', 'rorate_90'=>1, 'type'=>'field','width'=>'9','name_field'=>'diff_1'));

$this->addFTL(array('name'=>'Гравець 1 <br>(Рейтинг Клубу/ФНТУ)','type'=>'get_func',
    'function'=>'get_player',    'width'=>'200','name_field'=>'pl_id_1'));
    
/*$this->addFTL(array('name'=>'Игрок 1','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'pl_id_1','out_result_field'=>'name',
    'width'=>'100','name_field'=>'pl_id_1'));*/
$this->addFTL(array('name'=>'1 раху-<br />нок','type'=>'field','width'=>'9','name_field'=>'set_1','bd_field'=>'case when set_2="0" and  set_1="0" then "" else set_1 end',));


    //  $this->addFTL(array('name'=>'Порів-<br>няння<br />гравців','type'=>'win_users','width'=>'20', 'action'=>'statistics','module'=>'players','func_user'=>'compare_players'));
      $this->addFTL(array('name'=>'&nbsp;','type'=>'win_users','width'=>'20', 'action'=>'statistics','module'=>'players','func_user'=>'compare_players'));
       $this->addFTL(array('name'=>'2 раху-<br />нок','type'=>'field','width'=>'9','name_field'=>'set_2','bd_field'=>'case when set_2="0" and  set_1="0" then "" else set_2 end',));

$this->addFTL(array('name'=>'Гравець 2<br>(Рейтинг Клубу/ФНТУ)','type'=>'get_func',
    'function'=>'get_player',  'width'=>'200','name_field'=>'pl_id_2'));
 /*  
$this->addFTL(array('name'=>'Игрок 2','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'pl_id_2','out_result_field'=>'name',
    'width'=>'100','name_field'=>'pl_id_2')); */   
$this->addFTL(array('name'=>'Гравець 2<br>Дельта<br> рейтингу','name_mob'=>'Гравець 2<br>&#916; р-гу','rorate_90'=>1,'type'=>'field','width'=>'9','name_field'=>'diff_2'));
      $this->addFTL(array('name'=>'Статус','type'=>'win_users','width_mob'=>'48', 'width'=>'60','name_field'=>'start_game','bd_field'=>'start_game',
          'action'=>'setresultwin','module'=>'reiting','func_user'=>'start_game_name'));
/* $this->addFTL(array('name'=>'Рейтинг КЛУБУ<br />2 гравця','type'=>'field','width'=>'9','name_field'=>'rt_id_2_beg'));
$this->addFTL(array('name'=>'Рейтинг ФНТУ<br />2 гравця','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'pl_id_2','out_result_field'=>'reiting_ukraine',
    'width'=>'10','name_field'=>'reiting_ukraine2'));*/
      if ($_SESSION['gt']['user_rule']<10) {
          $this->addFTL(array('name' => 'No_S', 'type' => 'field', 'width' => '5', 'name_field' => 'no_send'));
      }
//$this->addFTL(array('name'=>'Группа','type'=>'field','width'=>'5','name_field'=>'group_num'));
      if ($_SESSION['is_mobile']) {
          $this->addFTL(array('name'=>'Етап','type'=>'get_func',
              'function'=>'get_etap_prim',  'width'=>'200'));
      /*    $this->addFTL(array('name' => 'Етапи', 'type' => 'out_key',
              'table' => T_ETAPS, 'parent_field' => 'etap_id', 'out_result_field' => 'name_etap',
              'width' => '80', 'name_field' => 'name_etap'));*/
  //        $this->addFTL(array('name' => 'Етап примітка', 'name_mob' => 'Етап<br>примітка', 'type' => 'field', 'width' => '150', 'name_field' => 'etap_prim'));
          $this->addFTL(array('type' => 'onlybd_out_key', 'table' => T_ETAPS, 'parent_field' => 'etap_id','out_result_field' => 'name_etap',
              'name_field' => 'name_etap'));
          $this->addFTL(array('type' => 'onlybd', 'name_field' => 'etap_prim', 'bd_field' => 'etap_prim'));
      }else{
          $this->addFTL(array('name' => 'Етапи', 'type' => 'out_key',
              'table' => T_ETAPS, 'parent_field' => 'etap_id', 'out_result_field' => 'name_etap',
              'width' => '80', 'name_field' => 'name_etap'));
          $this->addFTL(array('name' => 'Етап примітка', 'name_mob' => 'Етап<br>примітка', 'type' => 'field', 'width' => '150', 'name_field' => 'etap_prim'));

      }
$this->addFTL(array('name'=>'Видалить','type'=>'delete','width'=>'20','name_field'=>'id'));
//================================================================================================
// описание полей формы модуля при редактировании или добавления
$this->addFF(array('name'=>'Гравець 1','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'pl_id_1',
                    'out_result_field'=>'name',
                    'bd_field'=>'pl_id_1',
                    'mess'=>'Виберіть гравця 1',
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'where'=>' and exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=p.id) ',
                     'width'=> '500', // ширина окна
                    'required'=>'Гравець 1 обов"язково',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'where'=>'exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=m.id) and ','table'=>T_PLAYERS ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця 1','name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Рік народження','name_field'=>'god_rogd','width'=>'20'),
                      //  array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )
                    ));


 $this->addFF(array('name'=>'Рахунок 1','name_field'=>'set_1','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
  $this->addFF(array('name'=>'Рахунок 2','name_field'=>'set_2','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
$this->addFF(array('name'=>'Гравець 2','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'pl_id_2',
                    'out_result_field'=>'name',
                    'bd_field'=>'pl_id_2',
                    'mess'=>'Виберіть гравця 2',
                    'table'=>T_PLAYERS,
                      'where'=>' and exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=p.id) ',
                  
                     'no_vubor' => '',
                      'width'=> '500', // ширина окна
                    'required'=>'Гравець 2 обов"язково',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'where'=>'exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=m.id) and ','table'=>T_PLAYERS ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця','name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Рік народження','name_field'=>'god_rogd','width'=>'20'),
                    //    array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                     )
                    ));
  $this->addFF(array('name'=>'No_send','name_field'=>'no_send', 'type'=>'Checkbox','bd_field'=>'no_send'));
  $this->addFF(array('name'=>'Неявка 1 учасника на гру </br>(Тех. поразка)','name_field'=>'break_1', 'type'=>'Checkbox','bd_field'=>'break_1'));
  $this->addFF(array('name'=>'Неявка 2 учасника на гру </br>(Тех. поразка)','name_field'=>'break_2', 'type'=>'Checkbox','bd_field'=>'break_2'));
                  
  $this->setTableModule(T_REITING);
  //$this->setTypeModule('tree');

  $_SESSION['reiting']['sort']='end_game, IF(pl_id_1>0 AND pl_id_2>0,0,1),table_game, id';
  $_SESSION['reiting']['sort_type']='asc';
      $turnir_id = poste('turnir_id');
      $name_turnir =db_row('select name,dat  from `' . T_TURNIRS .
          '` where id=' . $turnir_id);
      $date = new DateTimeImmutable($name_turnir['dat']);
      $tdat = $date->format('d.m.Y');
      self::$nameZ=' ';

      $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$turnir_id;
      $vData = db_row($sql);
      $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$turnir_id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
      if ($vData['cnt_g']>0  ){
          $title='';
      }elseif($Work_turnir>0){

          $title=' - в процесі';
      }else{

          $title=' - не розпочато';
      }

      if ($_SESSION['is_mobile'] )
          $nameZ='<div class="compare_zagl">Ігри турніру  "'.$name_turnir['name'].' ('.$tdat.$title. ')"</div>';
      else
          $nameZ='<div class="poriv_zag">Ігри турніру  "'.$name_turnir['name'].'" ('.$tdat.$title. ')</div>';

      self::$nameZList=$nameZ;


      self::$nameZ='';

 self::$nameZEdit='::редагування гри';
 if ($_SESSION['gt']['user_rule']<10)
 self::$submenu_list =array( 
   //filter' => array('module' => 'tovs'),
    'back' => array('module' => 'turnirs', 'action' => 'list'),
    'truck' => array('menu_name'=>'Відправить результати','module' => 'reiting', 'action' => 'put_results', 'post' => 'id='.poste('turnir_id')),
 
    'filter' => array('menu_name'=>'Експорт в Excel результатів', 'http' => 'modules/reiting/action/toexcel.php?id='.poste('turnir_id')),
 
    );
      $this->getSubMenu2Data($etap_id);
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
 
self::InitMainMenu();
  self::$aParent[0]= array('name_field'=>'turnir_id',
                  'table'=>T_TURNIRS,
                  'type'=>'Hidden'
                  );
      self::$aParent[1]= array('name_field'=>'etap_id',
          'type'=>'Hidden'
      );
      self::$aParent[2]= ['name_field'=>'league_id',  'type'=>'Hidden'];
      $dop_where='';
  //    s('filter='.$filter);
 if (!empty($filter))
 {
     switch ($filter){
         case 'nogame': $dop_where=' and (table_game=0 and (set_1=0 and set_2=0))'; break;
         case 'start': $dop_where=' and (table_game>0 and (set_1=0 and set_2=0))';break;
         case 'finish': $dop_where=' and (table_game=0 and (set_1<>0 or set_2<>0 or set_1="W" or set_2="W"))';break;
         default: $dop_where='';
     }
//s('$dop_where='.$dop_where);
 }

//поиск с фильтраци
      $fio_search = poste('fio_search');
 if (!empty($fio_search))
 {
     //s($fio_search);
     $strSearch = ' AND 
(  pl_id_1 IN (SELECT pl.id from bs_players pl, bs_turnirplayers tp WHERE tp.player_id=pl.id AND pl.name LIKE "%'.$fio_search.'%") or
  pl_id_2 IN (SELECT pl.id from bs_players pl, bs_turnirplayers tp WHERE tp.player_id=pl.id AND pl.name LIKE "%'.$fio_search.'%"))';
     $dop_where = $strSearch;
 }
$sWhere =  ' and perenos_etap=0 '. (!empty($etap_id)  ? ' and etap_id='.poste('etap_id') : '').$dop_where;
$_SESSION['reiting']['where'] =$sWhere;                  
  self::$submenu_edit = array(
                'back' => array(
                    'module' => 'reiting',
                    'action' => 'list',
                    'class' => 'ajax_back',
                    'post' => 'turnir_id='.poste('turnir_id').$postButton),
                'save' => array(
                    'module' => 'reiting',
                    'action' => 'edit_ok',
                    'post' => 'turnir_id='.poste('turnir_id').$postButton),
                );              
                  
  }
     function getSubMenu2Data($etap_id)
  {
      $filter=poste('filter');
      $filter = !empty($filter) ? $filter : 'all';
    $submenu2 = array('bigMenu'=>[]);
    $bigMunu['Active_name']='Всі ігри';
    $bigMunu['Search_field']='
<div class="col_flo_left"><div class="input__wrapper"><svg class="input__icon"><use xlink:href="#poisk"></use></svg> <input type="text" class="form-control" placeholder="Пошук гравця по ПІБ" id="search_field_games" style="width:100%;"speeds="0"  value="">
         
        </div></div>';
    $bigMunu['Button_name']='Фільтр етапів';
    $bigMunu['Button_Menu'] =[];
    $bigMunu['Line_Menu'] =[];
    $sql = 'SELECT * FROM `'.T_ETAPS.'` where turnir_id='.poste('turnir_id');
      $etapsArr = db_list($sql);
      $name_vibor = 'Виберіть етап';
      $name_all = 'Всі ігри';
      $id = 'etap-chosen-select';
      $name_field = 'etap_id';
     // $data_id = $_SESSION['reiting']['filter']['etap'];
      // s($this->Java_script);
      //  SystemClass::setJava_script($this->Java_script);

       if (!empty($etapsArr))
      {
        $submenu2Temp[]['name'] = 'Всі ігри';
        // if (empty($etap_id))  $submenu2Temp['class'] = 'red_color';
           $submenu2Temp[]['href'] = '#reiting-list-turnir_id='.poste('turnir_id');
          $bigMunu['Button_Menu'][] =$submenu2Temp;
        foreach ($etapsArr as $val){
     //      if (!empty($etap_id) && $val['id']==$etap_id)  $submenu2Temp['class'] = 'red_color'; else   $submenu2Temp['class'] = 'black_color';
           if (!empty($etap_id) && $val['id']==$etap_id)  $bigMunu['Active_name']=$val['name_etap'];
           $submenu2Temp[]['name'] = $val['name_etap'];
           $submenu2Temp[]['href'] = '#reiting-list-etap_id='.$val['id'].'&turnir_id='.poste('turnir_id');

        }
          $txtEatp = get_select_submenu2($etapsArr, $id, $name_field, $etap_id,$name_all);

          $bigMunu['Button_Menu'] =$txtEatp;
      }
      $etap_str = !empty($etap_id) ? ' and etap_id='.$etap_id : '';
      $etap_href = !empty($etap_id) ? '&etap_id='.$etap_id : '';
      // Добавим фильтр в меню по играм
      $sql = 'SELECT * FROM `'.T_REITING.'` where   perenos_etap=0 and turnir_id='.poste('turnir_id').$etap_str;
      $aGames = db_list($sql);
      $allGame = count($aGames);
      $gamesNoStart=0;
      $gamesStart=0;
      $gamesFinish=0;
      if(!empty($aGames))
      foreach ($aGames as $game)
      {
          if ($game['table_game']>0) $gamesStart++;
          if ($game['table_game']==0 && $game['set_1']=='0' & $game['set_2']=='0') $gamesNoStart++;
          if ($game['table_game']==0 && ($game['set_1']!='0' || $game['set_2']!='0')) $gamesFinish++;
      }
      if (!empty($filter) && 'all'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Всі ігри ('.$allGame.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.poste('turnir_id').'&filter=all'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      if (!empty($filter) && 'nogame'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Не розпочато ('.$gamesNoStart.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.poste('turnir_id').'&filter=nogame'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      if (!empty($filter) && 'start'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='В процесі ('.$gamesStart.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.poste('turnir_id').'&filter=start'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      if (!empty($filter) && 'finish'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Завершені ('.$gamesFinish.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.poste('turnir_id').'&filter=finish'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
   //   s($submenu2);
      $submenu2['bigMenu']=$bigMunu;
      if (!$_SESSION['is_mobile'])
          self::$subMenu2 = $submenu2;
      else
         $this->subMenu2Mob($bigMunu,$allGame,$etap_href,$gamesNoStart,$gamesStart,$gamesFinish);

  }
    function subMenu2Mob($bigMunu,$allGame,$etap_href,$gamesNoStart,$gamesStart,$gamesFinish){
        $filter=poste('filter');
        $filter = !empty($filter) ? $filter : 'all';
        $str ='';

        if (!empty($filter) && 'all'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<div class="container"><div class="row justify-content-center"><div class="col"><div class="mob_reiting_game_menu1"> <a href="#reiting-list-turnir_id='.poste('turnir_id').'&filter=all'.$etap_href.'" class="ajax_send ' . $class . '">Всі ігри ('.$allGame.')</a>';
        if (!empty($filter) && 'nogame'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<a href="#reiting-list-turnir_id='.poste('turnir_id').'&filter=nogame'.$etap_href.'" class="ajax_send ' . $class . '">Не розпочато ('.$gamesNoStart.')</a>
    </div>';
        if (!empty($filter) && 'start'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<div class="mob_reiting_game_menu1"> <a href="#reiting-list-turnir_id='.poste('turnir_id').'&filter=start'.$etap_href.'" class="ajax_send ' . $class . '">В процесі ('.$gamesStart.')</a>
    ';
        if (!empty($filter) && 'finish'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<a href="#reiting-list-turnir_id='.poste('turnir_id').'&filter=finish'.$etap_href.'" class="ajax_send ' . $class . '">Завершені ('.$gamesFinish.')</a>
    </div>';
        $str.='</div></div></div>
<div class="col_flo_left1"><div class="input__wrapper"><svg class="input__icon"><use xlink:href="#poisk"></use></svg>
<input type="text" class="form-control" placeholder="Пошук гравця по ПІБ" id="search_field_games" style="width:100%;"speeds="0"  value=""></div></div>
'.$bigMunu['Button_Menu'];
        SystemClass::$submenu2_html = $str;
    }
}

function compare_players($id)
{
    $sql='select id,pl_id_1,
       pl_id_2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game,no_send,break_1,break_2,set_2,set_1,end_game      
  from '.T_REITING.' r  where  id='.$id;

    $text='';
    $aResults = db_row($sql);
    if ( $aResults['pl_id_1']>0 && $aResults['pl_id_2']>0) // если есть уже известные игроки
    {
        $text = '<a  href="#players-statistics-id=' . $aResults['pl_id_1'] . '&compare_id=' . $aResults['pl_id_2'] . '" target="_blank" class="coral_color">VS</span>';
    }
    return $text;
}
function start_game_name($id)
{
     $sql='select id,(select  p.name from  bs_players p where p.id=r.pl_id_1) as name1,pl_id_1,
        (select  p.name from  bs_players p where p.id=r.pl_id_2) as name2,pl_id_2,
  group_num, type_game, olimp16_num, etap_prim, start_game,r.table_game,no_send,break_1,break_2,set_2,set_1,end_game,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  id='.$id;
 //s($sql);
 $text='';
   $aResults = db_row($sql); 
   if ( $aResults['pl_id_1']>0 && $aResults['pl_id_2']>0) // если есть уже известные игроки
   {
      // s($aResults);
    if ($aResults['set_1']==0 && $aResults['set_2']==0 && $aResults['break_1']==0 && $aResults['break_2']==0)
        if ($_SESSION['gt']['user_rule']<10)
     $text = '<span class="blue tableBig" post_string="&turnir_id='.poste('turnir_id').'&etap_id='.poste('etap_id').'" newgame="'.$id.'">Розпочати<br> гру</span>';
    else
     $text = '<span class="blue" newgame="'.$id.'">Очікує <br> початку</span>';
    if ($aResults['set_1']!=0 || $aResults['set_2']!=0 || $aResults['break_1']!=0 || $aResults['break_2']!=0)
     {    if (!empty($aResults['end_game']))
             {$sMin_sec='';
                if (!empty($aResults['start_game']))
                {


                    $time_finish= strtotime($aResults['end_game']);
                    $time_start= strtotime($aResults['start_game']);
                    $date = strtotime($aResults['start_game']);
                    $aResults['start_game']= Date('H:i',$date);
                    // Функция abs нужна, чтобы не проверять какая из двух дат больше
                    $seconds = abs($time_finish - $time_start);
// Количество минут нужно округлить в меньшую сторону,
// чтобы узнать точное количество прошедших минут
                    $minutes = floor($seconds / 60);
                    $sec =$seconds - ($minutes * 60 );
                    $sec = $sec<10 ? '0'.$sec : $sec;
                    $minutes = $minutes<10 ? '0'.$minutes : $minutes;
                    $sMin_sec='<br> Трив.: '.$minutes.':'.$sec;
                }
                         $start_txt = (!empty($aResults['start_game'])) ? 'Старт '.$aResults['start_game']  : '';
           $text = $start_txt.//'<br> Фініш '.$aResults['end_game'].
               $sMin_sec;
           }
           else
            $text ='Гра<br> завершена!';
        
     }
       $date = strtotime($aResults['start_game']);
       $aResults['start_game']= Date('H:i',$date);
   if ($aResults['table_game']!=0) 
     $text = '<span class="coral_color tableBig" post_string="&turnir_id='.poste('turnir_id').'&etap_id='.poste('etap_id').'" 
     newgame="'.$id.'">Старт '.$aResults['start_game'].'<br> Т'.$aResults['table_game'].'</span>';
  
   }
   else 
   {
        $text = 'Визначення<br>гравців';
   }
    return $text;
}
function get_select_submenu2($aProstSpr,$id,$name_field,$data_id,$name_all)
{
  $class =   ($_SESSION['is_mobile']) ? 'col_flo_left1' : 'col_flo_left';
    // $aProstSpr[0]=['id'=>0,'id_spis'=>$id_spis,'name'=>$name_vibor,'active'=>0];
    // $aProstSpr= array_merge($aProstSpr,$aProstSpr_);
    $sSpis = '<div class="'.$class.'"><select   class="form-select w-auto"    tabindex="5"  name=form['.$name_field.']" id="'.$id.'">
    <option value="0">'.$name_all.'</option>
    ';$selected='';
    foreach ($aProstSpr as $elem)
    {
        if (!empty($data_id))
            $selected= $elem['id']==$data_id ? 'selected="selected"' : '';
        $sSpis.='<option '.$selected.' value="'.$elem['id'].'" >'.$elem['name_etap'].'</option>';

    }
    $sSpis.=  '</select></div>';
    return $sSpis;
}
?>