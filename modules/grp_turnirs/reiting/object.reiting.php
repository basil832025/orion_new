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
      $league_id = poste('league_id');
      $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
      
      // Определяем, является ли это командной лигой
      $is_team_league = 0;
      if (!empty($league_id)) {
          $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
      } elseif (!empty($turnir_id)) {
          // Если нет league_id, пытаемся получить из турнира
          $league_id_from_turnir = db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
          if (!empty($league_id_from_turnir)) {
              $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id_from_turnir, 'is_team_league');
              $league_id = $league_id_from_turnir;
          }
      }
      $_SESSION['reiting']['is_team_league'] = $is_team_league ? 1 : 0;
      $is_para = 0;
      if (!empty($turnir_id)) {
          $is_para = (int)db_field('SELECT ispara FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'ispara');
      }
      $_SESSION['reiting']['ispara'] = $is_para ? 1 : 0;
      $post_return = '&filter='.$filter;
      SystemClass::$post_return_dop =$post_return;

      if ($_SESSION['is_mobile'] ){

          SystemClass::$Java_script_module='show_zag_left("#turnirs-list'.$menu_league.'");chosen_vibor_filter_turnir();';
      }else
      {
          $show_zag_left='show_zag_center();show_zag_left_big("#turnirs-list'.$menu_league.'");';
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
// Для командного турнира добавляем кнопку раскрытия/сворачивания игр игроков
if ($is_team_league) {
    $this->addFTL(array('name'=>'*','type'=>'expand_match','width'=>'5'));
}
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'25', 'postButton'=>$postButton)); 
//$this->addFTL(array('name'=>'Дата турнира','type'=>'out_key',
  //  'table'=>T_TURNIRS, 'parent_field'=>'turnir_id','out_result_field'=>'dat',
 //   'width'=>'20','name_field'=>'dat'));
/*$this->addFTL(array('name'=>'Рейтинг ФНТУ<br />1 гравця','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'pl_id_1','out_result_field'=>'reiting_ukraine',
    'width'=>'10','name_field'=>'reiting_ukraine1'));
      $this->addFTL(array('name'=>'Рейтинг КЛУБУ<br />1 гравця','type'=>'field','width'=>'9','name_field'=>'rt_id_1_beg'));
*/
// Для командного турнира используем другие названия колонок и убираем дельту рейтинга
if ($is_team_league) {
    // Командный турнир: колонки для команд и пар игроков (без дельты рейтинга)
    $this->addFTL(array('name'=>'Команда/Пара 1<br>(Рейтинг)','type'=>'get_func',
        'function'=>'get_player',    'width'=>'200','name_field'=>'pl_id_1'));
    $this->addFTL(array('name'=>'1 раху-<br />нок','type'=>'get_func','width'=>'9','name_field'=>'set_1','function'=>'get_team_score'));
    $this->addFTL(array('name'=>'&nbsp;','type'=>'win_users','width'=>'20', 'action'=>'statistics','module'=>'players','func_user'=>'compare_players'));
    $this->addFTL(array('name'=>'2 раху-<br />нок','type'=>'get_func','width'=>'9','name_field'=>'set_2','function'=>'get_team_score'));
    $this->addFTL(array('name'=>'Команда/Пара 2<br>(Рейтинг)','type'=>'get_func',
        'function'=>'get_player',  'width'=>'200','name_field'=>'pl_id_2'));
} else {
    // Обычный турнир: стандартные названия с дельтой рейтинга
    $this->addFTL(array('name'=>'Гравець 1 <br>Дельта<br> рейтингу','name_mob'=>'Гравець 1<br>&#916; р-гу', 'rorate_90'=>1, 'type'=>'field','width'=>'9','name_field'=>'diff_1'));
    $this->addFTL(array('name'=>'Гравець 1 <br>(Рейтинг Клубу/ФНТУ)','type'=>'get_func',
        'function'=>'get_player',    'width'=>'200','name_field'=>'pl_id_1'));
    $this->addFTL(array('name'=>'1 раху-<br />нок','type'=>'field','width'=>'9','name_field'=>'set_1','bd_field'=>'case when set_2="0" and  set_1="0" then "" else set_1 end',));
    $this->addFTL(array('name'=>'&nbsp;','type'=>'win_users','width'=>'20', 'action'=>'statistics','module'=>'players','func_user'=>'compare_players'));
    $this->addFTL(array('name'=>'2 раху-<br />нок','type'=>'field','width'=>'9','name_field'=>'set_2','bd_field'=>'case when set_2="0" and  set_1="0" then "" else set_2 end',));
    $this->addFTL(array('name'=>'Гравець 2<br>(Рейтинг Клубу/ФНТУ)','type'=>'get_func',
        'function'=>'get_player',  'width'=>'200','name_field'=>'pl_id_2'));
    $this->addFTL(array('name'=>'Гравець 2<br>Дельта<br> рейтингу','name_mob'=>'Гравець 2<br>&#916; р-гу','rorate_90'=>1,'type'=>'field','width'=>'9','name_field'=>'diff_2'));
}
      // Для командных лиг используем start_team_game, для обычных - setresultwin
      $start_action = $is_team_league ? 'start_team_game' : 'setresultwin';
      $this->addFTL(array('name'=>'Статус','type'=>'win_users','width_mob'=>'48', 'width'=>'60','name_field'=>'start_game','bd_field'=>'start_game',
          'action'=>$start_action,'module'=>'reiting','func_user'=>'start_game_name'));
// Для командной лиги добавляем кнопку управления составами команд для каждого матча
if ($is_team_league && $_SESSION['gt']['user_rule']<10) {
    $this->addFTL(array('name'=>'Склади<br />команд','type'=>'anyaction',  'width'=>'80','img'=>'reports','name_field_child'=>'id','action'=>'team_lineups','module'=>'reiting', 'title'=>'Управління складами команд для матчу'));
}
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

  // Для командного турнира сортируем: игры игроков должны следовать сразу после соответствующих командных игр
  if ($is_team_league) {
      // Группируем игры по match_id, чтобы игры игроков следовали сразу после командных игр с тем же match_id
      // Сначала определяем тип игры: 0 - командная игра (pair_number=0 или NULL), 1 - игра игроков (pair_number>0)
      // Затем сортируем по match_id, чтобы игры игроков группировались под командными играми
      // Важно: match_id должен быть одинаковым для командной игры и всех её игр игроков
      // НЕ устанавливаем sort здесь - он будет установлен позже как sort_default
      // $_SESSION['reiting']['sort']='IF(pair_number IS NULL OR pair_number=0 OR pair_number="", 0, 1), IF(match_id IS NULL OR match_id="", CONCAT("zzz_", id), match_id), pair_number, table_game, id';
      // $_SESSION['reiting']['sort_type']='asc';
  } else {
      $_SESSION['reiting']['sort']='end_game, IF(pl_id_1>0 AND pl_id_2>0,0,1),table_game, id';
      $_SESSION['reiting']['sort_type']='asc';
  }
      $turnir_id = poste('turnir_id');
      // Проверяем наличие turnir_id перед выполнением запросов
      if (!empty($turnir_id) && $turnir_id > 0) {
          $name_turnir =db_row('select name,dat  from `' . T_TURNIRS .
              '` where id=' . (int)$turnir_id);
          if (!empty($name_turnir)) {
              $turnir_name = htmlspecialchars(stripslashes((string)$name_turnir['name']), ENT_QUOTES, 'UTF-8');
              $date = new DateTimeImmutable($name_turnir['dat']);
              $tdat = $date->format('d.m.Y');
              self::$nameZ=' ';

              $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
      from '.T_TURNIRS.' r  where  r.id='.(int)$turnir_id;
              $vData = db_row($sql);
              $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.(int)$turnir_id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
              if (!empty($vData) && $vData['cnt_g']>0  ){
                  $title='';
              }elseif(!empty($Work_turnir) && $Work_turnir>0){

                  $title=' - в процесі';
              }else{

                  $title=' - не розпочато';
              }
          } else {
              self::$nameZ=' ';
              $title='';
          }
      } else {
          self::$nameZ=' ';
          $title='';
      }

      if (!empty($turnir_id) && $turnir_id > 0 && !empty($name_turnir)) {
          if ($_SESSION['is_mobile'] )
              $nameZ='<div class="compare_zagl">Ігри турніру  "'.$turnir_name.' ('.$tdat.$title. ')"</div>';
          else
              $nameZ='<div class="poriv_zag">Ігри турніру  "'.$turnir_name.'" ('.$tdat.$title. ')</div>';
      } else {
          $nameZ='<div class="poriv_zag">Ігри</div>';
      }

      self::$nameZList=$nameZ;


      self::$nameZ='';

 self::$nameZEdit='::редагування гри';
      $league_id_param = !empty($league_id) ? '&league_id='.$league_id : '';
 if ($_SESSION['gt']['user_rule']<10)
 self::$submenu_list =array( 
   //filter' => array('module' => 'tovs'),
    'back' => array('module' => 'turnirs', 'action' => 'list'),
    'truck' => array('menu_name'=>'Відправить результати','module' => 'reiting', 'action' => 'put_results', 'post' => 'id='.poste('turnir_id').$league_id_param),
 
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
      if (!empty($_SESSION['reiting']['ispara'])) {
          $pair_id = (int)$fio_search;
          if ($pair_id > 0) {
              $strSearch = ' AND (pl_id_1='.$pair_id.' OR pl_id_2='.$pair_id.')';
          } else {
              $strSearch = '';
          }
      } elseif (!empty($is_team_league)) {
          $team_id = (int)$fio_search;
          if ($team_id > 0) {
              $strSearch = ' AND ( ( (pair_number IS NULL OR pair_number=0) AND (pl_id_1='.$team_id.' OR pl_id_2='.$team_id.') ) OR (pair_number > 0) )';
          } else {
              $strSearch = '';
          }
      } else {
          $player_id = (int)$fio_search;
          if ($player_id > 0) {
              $strSearch = ' AND (pl_id_1='.$player_id.' OR pl_id_2='.$player_id.')';
          } else {
              $strSearch = '';
          }
      }
      $dop_where = $strSearch;
  }
 $etap_filter = !empty($etap_id) ? ' and etap_id='.(int)$etap_id : '';
 if (!empty($_SESSION['reiting']['ispara']) && !empty($fio_search)) {
     $etap_filter = '';
 }
 $sWhere =  ' and perenos_etap=0 '.$etap_filter.$dop_where;
$_SESSION['reiting']['where'] =$sWhere;
// Для командных турниров устанавливаем специальную сортировку по match_id и pair_number
if ($is_team_league) {
    // Сортировка по статусу игры:
    // 1. "В процесі" (In progress) - start_game заполнен, но нет win_player и нет end_game - приоритет 1
    // 2. "Немає пар" (No pairs) - командная игра без пар - приоритет 2
    // 3. "Завершені" (Completed) - win_player > 0 или end_game заполнен - приоритет 3
    // Затем группировка по match_id и pair_number
    // Очищаем sort, чтобы использовать только sort_default
    $_SESSION['reiting']['sort'] = '';
    $_SESSION['reiting']['sort_default'] = '
        CASE 
            WHEN (start_game IS NOT NULL AND start_game != "" AND start_game != "00:00:00" 
                  AND (COALESCE(win_player, 0) = 0 OR win_player IS NULL) 
                  AND (end_game IS NULL OR end_game = "" OR end_game = "00:00:00")) 
            THEN 1
            WHEN ((pair_number = 0 OR pair_number IS NULL) 
                  AND match_id IS NOT NULL 
                  AND match_id != ""
                  AND (start_game IS NULL OR start_game = "" OR start_game = "00:00:00")
                  AND (COALESCE(win_player, 0) = 0 OR win_player IS NULL))
            THEN 2
            WHEN (COALESCE(win_player, 0) > 0 OR (end_game IS NOT NULL AND end_game != "" AND end_game != "00:00:00"))
            THEN 3
            ELSE 2
        END,
        IF(pair_number IS NULL OR pair_number=0 OR pair_number="", 0, 1), 
        IF(match_id IS NULL OR match_id="", CONCAT("zzz_", id), match_id), 
        pair_number, 
        table_game, 
        id';
}                  
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
      $turnir_id = poste('turnir_id');
      // Проверяем наличие turnir_id перед выполнением запросов
      if (empty($turnir_id) || $turnir_id <= 0) {
          // Если turnir_id не передан, возвращаем пустые данные с правильной структурой
          // Button_Menu должен быть строкой, а не массивом
          $submenu2 = array('bigMenu'=>array(
              'Active_name'=>'Всі ігри',
              'Search_field'=>'',
              'Button_name'=>'Фільтр етапів',
              'Button_Menu'=>'',  // Пустая строка, а не массив
              'Line_Menu'=>array()
          ));
          if (!$_SESSION['is_mobile'])
              self::$subMenu2 = $submenu2;
          return;
      }
      
      $filter=poste('filter');
      $filter = !empty($filter) ? $filter : 'all';
    $submenu2 = array('bigMenu'=>[]);
     $bigMunu['Active_name']='Всі ігри';
     $league_id = poste('league_id');
     $league_id = !empty($league_id) ? (int)$league_id : 0;
     if (empty($league_id) && !empty($turnir_id)) {
         $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'league_id');
     }
     $is_team_league_local = 0;
     if (!empty($league_id)) {
         $is_team_league_local = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.(int)$league_id, 'is_team_league');
     }
     $is_para_local = !empty($turnir_id) ? (int)db_field('SELECT ispara FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'ispara') : 0;
     $search_placeholder = $is_para_local ? 'Пошук пари' : (!empty($is_team_league_local) ? 'Пошук команди' : 'Пошук гравця по ПІБ');
     $search_options = '';
         if (!empty($turnir_id)) {
         if (!empty($is_para_local)) {
             $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                 .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                 .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.ispara=1 ORDER BY p.name';
             $search_list = db_list($sql);
             if (!empty($search_list)) {
                 foreach ($search_list as $row) {
                     if (!empty($row['name'])) {
                         $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                     }
                 }
             }
         } elseif (!empty($is_team_league_local)) {
             $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                 .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                 .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.is_team=1 ORDER BY p.name';
             $search_list = db_list($sql);
             if (!empty($search_list)) {
                 foreach ($search_list as $row) {
                     if (!empty($row['name'])) {
                         $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                     }
                 }
             }
         } else {
             $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                 .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                 .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.is_team=0 ORDER BY p.name';
             $search_list = db_list($sql);
             if (!empty($search_list)) {
                 foreach ($search_list as $row) {
                     if (!empty($row['name'])) {
                         $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                     }
                 }
             }
         }
     }
     $default_option = !empty($is_para_local) ? 'Всі пари' : (!empty($is_team_league_local) ? 'Всі команди' : 'Всі гравці');
     $bigMunu['Search_field']='
 <div class="col_flo_left"><select class="form-select w-auto" tabindex="5" id="search_field_games_select" data-placeholder="'.$search_placeholder.'">
 <option value="">'.$default_option.'</option>
 '.$search_options.'
 </select></div>';
    $bigMunu['Button_name']='Фільтр етапів';
    $bigMunu['Button_Menu'] =[];
    $bigMunu['Line_Menu'] =[];
    $sql = 'SELECT * FROM `'.T_ETAPS.'` where turnir_id='.(int)$turnir_id;
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
        foreach ($etapsArr as $val){
           if (!empty($etap_id) && $val['id']==$etap_id)  $bigMunu['Active_name']=$val['name_etap'];
        }
          $txtEatp = get_select_submenu2($etapsArr, $id, $name_field, $etap_id,$name_all);
          // Убеждаемся, что $txtEatp - строка
          $bigMunu['Button_Menu'] = is_string($txtEatp) ? $txtEatp : '';
      } else {
          // Если нет этапов, устанавливаем пустую строку
          $bigMunu['Button_Menu'] = '';
      }
      $etap_str = !empty($etap_id) ? ' and etap_id='.(int)$etap_id : '';
      $etap_href = !empty($etap_id) ? '&etap_id='.$etap_id : '';
      // Добавим фильтр в меню по играм
      $sql = 'SELECT * FROM `'.T_REITING.'` where   perenos_etap=0 and turnir_id='.(int)$turnir_id.$etap_str;
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
       $league_id = poste('league_id');
      $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
      
      // Инициализируем массив для каждого элемента меню
      $submenu2Temp = array();
      if (!empty($filter) && 'all'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Всі ігри ('.$allGame.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=all'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      
      $submenu2Temp = array();
      if (!empty($filter) && 'nogame'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Не розпочато ('.$gamesNoStart.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=nogame'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      
      $submenu2Temp = array();
      if (!empty($filter) && 'start'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='В процесі ('.$gamesStart.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=start'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
      
      $submenu2Temp = array();
      if (!empty($filter) && 'finish'==$filter)  $submenu2Temp['class'] = 'active_filter_game'; else   $submenu2Temp['class'] = '';
      $submenu2Temp['name']='Завершені ('.$gamesFinish.')';
      $submenu2Temp['href']='#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=finish'.$etap_href;
      $bigMunu['Line_Menu'][]=$submenu2Temp;
   //   s($submenu2);
      $submenu2['bigMenu']=$bigMunu;
      if (!$_SESSION['is_mobile'])
          self::$subMenu2 = $submenu2;
      else
         $this->subMenu2Mob($bigMunu,$allGame,$etap_href,$gamesNoStart,$gamesStart,$gamesFinish,$turnir_id);

  }
    function subMenu2Mob($bigMunu,$allGame,$etap_href,$gamesNoStart,$gamesStart,$gamesFinish,$turnir_id){
        $filter=poste('filter');
        $filter = !empty($filter) ? $filter : 'all';
        $str ='';
        $league_id = poste('league_id');
        $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
        if (!empty($filter) && 'all'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<div class="container"><div class="row justify-content-center"><div class="col"><div class="mob_reiting_game_menu1"> <a href="#reiting-list-turnir_id='.$turnir_id.'&filter=all'.$etap_href.'" class="ajax_send ' . $class . '">Всі ігри ('.$allGame.')</a>';
        if (!empty($filter) && 'nogame'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<a href="#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=nogame'.$etap_href.'" class="ajax_send ' . $class . '">Не розпочато ('.$gamesNoStart.')</a>
    </div>';
        if (!empty($filter) && 'start'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<div class="mob_reiting_game_menu1"> <a href="#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=start'.$etap_href.'" class="ajax_send ' . $class . '">В процесі ('.$gamesStart.')</a>
    ';
        if (!empty($filter) && 'finish'==$filter)  $class = 'active_filter_game'; else   $class = '';
        $str .= '<a href="#reiting-list-turnir_id='.$turnir_id.$menu_league.'&filter=finish'.$etap_href.'" class="ajax_send ' . $class . '">Завершені ('.$gamesFinish.')</a>
    </div>';
        $league_id = poste('league_id');
        $league_id = !empty($league_id) ? (int)$league_id : 0;
        if (empty($league_id) && !empty($turnir_id)) {
            $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'league_id');
        }
        $is_team_league_local = 0;
        if (!empty($league_id)) {
            $is_team_league_local = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.(int)$league_id, 'is_team_league');
        }
        $is_para_local = !empty($turnir_id) ? (int)db_field('SELECT ispara FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'ispara') : 0;
        $search_placeholder = $is_para_local ? 'Пошук пари' : (!empty($is_team_league_local) ? 'Пошук команди' : 'Пошук гравця по ПІБ');
        $search_options = '';
        if (!empty($turnir_id)) {
            if (!empty($is_para_local)) {
                $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                    .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                    .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.ispara=1 ORDER BY p.name';
                $search_list = db_list($sql);
                if (!empty($search_list)) {
                    foreach ($search_list as $row) {
                        if (!empty($row['name'])) {
                            $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                        }
                    }
                }
            } elseif (!empty($is_team_league_local)) {
                $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                    .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                    .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.is_team=1 ORDER BY p.name';
                $search_list = db_list($sql);
                if (!empty($search_list)) {
                    foreach ($search_list as $row) {
                        if (!empty($row['name'])) {
                            $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                        }
                    }
                }
            } else {
                $sql = 'SELECT DISTINCT p.id, p.name FROM `'.T_PLAYERS.'` p '
                    .'INNER JOIN `'.T_TURNIR_PLAYERS.'` tp ON tp.player_id=p.id '
                    .'WHERE tp.turnir_id='.(int)$turnir_id.' AND p.is_team=0 ORDER BY p.name';
                $search_list = db_list($sql);
                if (!empty($search_list)) {
                    foreach ($search_list as $row) {
                        if (!empty($row['name'])) {
                            $search_options .= '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                        }
                    }
                }
            }
        }
        $default_option = !empty($is_para_local) ? 'Всі пари' : (!empty($is_team_league_local) ? 'Всі команди' : 'Всі гравці');
        $str.='</div></div></div>
<div class="col_flo_left1"><select class="form-select w-auto" tabindex="5" id="search_field_games_select" data-placeholder="'.$search_placeholder.'">
<option value="">'.$default_option.'</option>
'.$search_options.'
</select></div>
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
  r.turnir_id, r.etap_id, r.match_id, r.team_a_id, r.team_b_id, r.pair_number,
(select w.name_etap from bs_etaps_work w where w.id=r.etap_id ) as name_etap      
  from '.T_REITING.' r  where  id='.$id;
 //s($sql);
 $text='';
   $aResults = db_row($sql); 
   
   // Определяем, является ли это командной лигой
   $is_team_league = 0;
   $league_id_from_turnir = null;
   if (!empty($aResults['turnir_id'])) {
       $league_id_from_turnir = db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$aResults['turnir_id'], 'league_id');
       if (!empty($league_id_from_turnir)) {
           $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id_from_turnir, 'is_team_league');
       }
   }
   
   // Определяем тип игры:
   // - Командная игра: pair_number = 0 или NULL, есть match_id (это матч между командами)
   // - Игра игроков: pair_number > 0 (это игра между отдельными игроками в рамках командного матча)
   // - Обычная игра: нет match_id и pair_number
   $pair_number = !empty($aResults['pair_number']) ? (int)$aResults['pair_number'] : 0;
   $is_team_game = false; // Командная игра (между командами)
   
   if ($pair_number > 0) {
       // Это игра игроков (pair_number > 0) - используем стандартное окно ввода счета
       $is_team_game = false;
   } elseif (!empty($aResults['match_id']) && $pair_number == 0) {
       // Это командная игра (pair_number = 0 и есть match_id) - используем start_team_game
       $is_team_game = true;
   } elseif ($is_team_league && (!empty($aResults['team_a_id']) && !empty($aResults['team_b_id']))) {
       // Если лига командная и есть team_a_id/team_b_id, это командная игра
       $is_team_game = true;
   } elseif ($is_team_league && (!empty($aResults['pl_id_1']) && !empty($aResults['pl_id_2']))) {
       // Дополнительная проверка: если лига командная, проверяем, являются ли игроки командами
       $p1_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_1'], 'is_team');
       $p2_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_2'], 'is_team');
       if (!empty($p1_is_team) && $p1_is_team == 1 && !empty($p2_is_team) && $p2_is_team == 1 && $pair_number == 0) {
           // Оба игрока являются командами и pair_number = 0 - это командная игра
           $is_team_game = true;
       }
   }
   
   // Определяем действие для кнопки
   $action_name = $is_team_game ? 'start_team_game' : 'setresultwin';
   $league_id_param = '';
   if ($is_team_game && !empty($league_id_from_turnir)) {
       $league_id_param = '&league_id='.$league_id_from_turnir;
   }
   
   if ( $aResults['pl_id_1']>0 && $aResults['pl_id_2']>0) // если есть уже известные игроки
   {
      // s($aResults);
    if ($aResults['set_1']==0 && $aResults['set_2']==0 && $aResults['break_1']==0 && $aResults['break_2']==0)
        if ($_SESSION['gt']['user_rule']<10) {
            // Для командных игр (pair_number = 0) используем start_team_game
            // Для игр игроков (pair_number > 0) - стандартное окно ввода счета через tables
            if ($is_team_game) {
                // Командная игра - проверяем, начата ли игра и сформированы ли игры
                $match_id = '';
                if (!empty($aResults['match_id'])) {
                    $match_id = $aResults['match_id'];
                } else {
                    // Формируем match_id из etap_id и команд, если его нет
                    $team_a_id = !empty($aResults['team_a_id']) ? (int)$aResults['team_a_id'] : 0;
                    $team_b_id = !empty($aResults['team_b_id']) ? (int)$aResults['team_b_id'] : 0;
                    
                    // Если team_a_id и team_b_id не определены, пытаемся определить из pl_id_1 и pl_id_2
                    if (empty($team_a_id) || empty($team_b_id)) {
                        if (!empty($aResults['pl_id_1'])) {
                            $p1_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_1'], 'is_team');
                            if (!empty($p1_is_team) && $p1_is_team == 1) {
                                $team_a_id = $aResults['pl_id_1'];
                            }
                        }
                        if (!empty($aResults['pl_id_2'])) {
                            $p2_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_2'], 'is_team');
                            if (!empty($p2_is_team) && $p2_is_team == 1) {
                                if (empty($team_a_id)) {
                                    $team_a_id = $aResults['pl_id_2'];
                                } else {
                                    $team_b_id = $aResults['pl_id_2'];
                                }
                            }
                        }
                    }
                    
                    if ($team_a_id > 0 && $team_b_id > 0) {
                        $min_team = min($team_a_id, $team_b_id);
                        $max_team = max($team_a_id, $team_b_id);
                        $match_id = 'match_'.$aResults['etap_id'].'_'.$min_team.'_'.$max_team;
                    }
                }
                
                // Проверяем наличие пар для этого матча (нужны хотя бы первые 3 пары)
                $has_pairs = false;
                if (!empty($match_id)) {
                    $pairs_count = db_field('SELECT COUNT(*) as cnt FROM `bs_team_pairs` 
                        WHERE match_id="'.addslashes($match_id).'" 
                        AND etap_id='.$aResults['etap_id'].'
                        AND pair_number <= 3', 'cnt');
                    $has_pairs = !empty($pairs_count) && (int)$pairs_count >= 3;
                }
                
                // Проверяем, есть ли сформированные игры игроков (pair_number > 0) для этого match_id
                // ВАЖНО: Проверяем соответствие игр игроков текущим парам из bs_team_pairs
                // Если пары изменены, старые игры игроков не должны учитываться
                $has_player_games = false;
                $player_games_started = false;
                if (!empty($match_id)) {
                    // Получаем текущие пары из bs_team_pairs
                    $current_pairs = db_list('SELECT pair_number, team_a_player_id, team_b_player_id 
                        FROM `bs_team_pairs` 
                        WHERE match_id="'.addslashes($match_id).'" 
                        AND etap_id='.$aResults['etap_id'].'
                        AND pair_number > 0 
                        AND pair_number <= 3');
                    
                    if (!empty($current_pairs) && count($current_pairs) > 0) {
                        // Проверяем, есть ли игры игроков, которые соответствуют текущим парам
                        $matching_games_count = 0;
                        $started_matching_games = 0;
                        
                        foreach ($current_pairs as $pair) {
                            $pair_num = (int)$pair['pair_number'];
                            $team_a_player_id = (int)$pair['team_a_player_id'];
                            $team_b_player_id = (int)$pair['team_b_player_id'];
                            
                            // Проверяем, есть ли игра игроков для этой пары с правильными игроками
                            $game_exists = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
                                WHERE match_id="'.addslashes($match_id).'" 
                                AND etap_id='.$aResults['etap_id'].'
                                AND pair_number = '.$pair_num.'
                                AND ((pl_id_1 = '.$team_a_player_id.' AND pl_id_2 = '.$team_b_player_id.')
                                    OR (pl_id_1 = '.$team_b_player_id.' AND pl_id_2 = '.$team_a_player_id.'))', 'cnt');
                            
                            if (!empty($game_exists) && (int)$game_exists > 0) {
                                $matching_games_count++;
                                
                                // Проверяем, начата ли эта игра
                                $game_started = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
                                    WHERE match_id="'.addslashes($match_id).'" 
                                    AND etap_id='.$aResults['etap_id'].'
                                    AND pair_number = '.$pair_num.'
                                    AND ((pl_id_1 = '.$team_a_player_id.' AND pl_id_2 = '.$team_b_player_id.')
                                        OR (pl_id_1 = '.$team_b_player_id.' AND pl_id_2 = '.$team_a_player_id.'))
                                    AND start_game IS NOT NULL 
                                    AND start_game != "" 
                                    AND start_game != "00:00:00"', 'cnt');
                                
                                if (!empty($game_started) && (int)$game_started > 0) {
                                    $started_matching_games++;
                                }
                            }
                        }
                        
                        // Игры игроков существуют только если они соответствуют ВСЕМ текущим парам
                        // Если хотя бы для одной пары нет соответствующей игры, считаем, что игры игроков не созданы
                        $has_player_games = ($matching_games_count > 0) && ($matching_games_count >= count($current_pairs));
                        $player_games_started = ($started_matching_games > 0) && ($started_matching_games >= count($current_pairs));
                    }
                }
                
                // Проверяем, введен ли хотя бы один результат (первый результат)
                $has_first_result = false;
                if (!empty($match_id)) {
                    $results_count = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
                        WHERE match_id="'.addslashes($match_id).'" 
                        AND etap_id='.$aResults['etap_id'].'
                        AND pair_number > 0
                        AND ((set_1 > 0 OR set_2 > 0 OR set_1 = "W" OR set_2 = "W")
                          AND NOT (set_1 = "0" AND set_2 = "0"))
                        AND (set_1 IS NOT NULL AND set_2 IS NOT NULL)', 'cnt');
                    $has_first_result = !empty($results_count) && (int)$results_count > 0;
                }
                
                // Для командных игр: если игры созданы/запущены, показываем "В процесі" вместо "Розпочати гру"
                if (!$has_pairs) {
                    // Командная игра без пар - показываем сообщение
                    $text = '<span class="text-muted" style="font-size: 0.85em;">Немає пар<br>(потрібно сформувати)</span>';
                } else {
                    $team_game_started = !empty($aResults['start_game']) && $aResults['start_game'] != '00:00:00';
                    // ВАЖНО: не показываем "В процесі" только по start_game командной игры,
                    // если игры пар еще не созданы (иначе теряется кнопка "Розпочати гру")
                    if ($player_games_started || ($team_game_started && $has_player_games)) {
                        $text = 'В процесі';
                    } elseif (!$has_first_result) {
                        // Первый результат еще не введен - показываем "Розпочати гру"
                        $etap_id_for_action = !empty($aResults['etap_id']) ? $aResults['etap_id'] : poste('etap_id');
                        $turnir_id_for_action = !empty($aResults['turnir_id']) ? $aResults['turnir_id'] : poste('turnir_id');
                        $text = '<span class="blue tableBig team-game" post_string="&turnir_id='.$turnir_id_for_action.'&etap_id='.$etap_id_for_action.$league_id_param.'" newgame="'.$id.'" data-action="start_team_game" data-module="reiting">Розпочати<br> гру</span>';
                    } else {
                        $text = '';
                    }
                }
            } else {
                // Игра игроков или обычный матч - стандартная логика через tables/setresultwin (окно ввода счета)
                $text = '<span class="blue tableBig" post_string="&turnir_id='.poste('turnir_id').'&etap_id='.poste('etap_id').'" newgame="'.$id.'">Розпочати<br> гру</span>';
            }
        } else {
     $text = '<span class="blue" newgame="'.$id.'">Очікує <br> початку</span>';
        }
    $raw_set_1 = isset($aResults['set_1']) ? trim((string)$aResults['set_1']) : '';
    $raw_set_2 = isset($aResults['set_2']) ? trim((string)$aResults['set_2']) : '';
    $set_1_is_tech = ($raw_set_1 === 'W' || $raw_set_1 === 'L');
    $set_2_is_tech = ($raw_set_2 === 'W' || $raw_set_2 === 'L');
    $set_1_norm = ($raw_set_1 === 'W') ? 3 : (($raw_set_1 === 'L') ? 0 : (int)$raw_set_1);
    $set_2_norm = ($raw_set_2 === 'W') ? 3 : (($raw_set_2 === 'L') ? 0 : (int)$raw_set_2);

    if ($set_1_norm != 0 || $set_2_norm != 0 || $set_1_is_tech || $set_2_is_tech || $aResults['break_1']!=0 || $aResults['break_2']!=0)
     {
        // Используем нормализованные значения с учетом технических результатов W/L
        $set_1_val = $set_1_norm;
        $set_2_val = $set_2_norm;
        
        // Дополнительная проверка: является ли это командной игрой?
        // Если есть match_id и pair_number = 0, или если лига командная и оба игрока - команды
        $is_team_game_check = false;
        if (!empty($aResults['match_id']) && $pair_number == 0) {
            $is_team_game_check = true;
        } elseif ($is_team_league && $pair_number == 0) {
            // Проверяем, являются ли оба игрока командами
            if (!empty($aResults['pl_id_1']) && !empty($aResults['pl_id_2'])) {
                $p1_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_1'], 'is_team');
                $p2_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$aResults['pl_id_2'], 'is_team');
                if (!empty($p1_is_team) && $p1_is_team == 1 && !empty($p2_is_team) && $p2_is_team == 1) {
                    $is_team_game_check = true;
                }
            }
        }
        
        // Используем более строгую проверку: либо $is_team_game, либо $is_team_game_check
        $is_team_game_final = $is_team_game || $is_team_game_check;
        
        // Для командных игр проверяем, достигла ли одна из команд 3 побед
        if ($is_team_game_final) {
            // Проверяем, достигла ли одна из команд 3 побед
            // Для командных игр: матч завершается, когда одна команда достигает 3 побед
            if ($set_1_val >= 3 || $set_2_val >= 3) {
                // Одна из команд достигла 3 побед - игра завершена, всегда показываем "Гра завершена!"
                $text ='Гра<br> завершена!';
            } else {
                // Есть счет, но еще нет 3 побед - игра в процессе
                $text ='В процесі';
            }
        } else {
            // Обычная игра (не командная) - используем стандартную логику
            if (!empty($aResults['end_game']))
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
                             $start_txt = (!empty($aResults['start_game']) ? 'Старт '.$aResults['start_game']  : '');
                   $text = $start_txt.//'<br> Фініш '.$aResults['end_game'].
                       $sMin_sec;
                   }
                   else
                    $text ='Гра<br> завершена!';
        }
     }
       $date = strtotime($aResults['start_game']);
       $aResults['start_game']= Date('H:i',$date);
   
   // Проверяем table_game, но не перезаписываем статус для завершенных командных игр
   if ($aResults['table_game']!=0) {
       // Проверяем, является ли это завершенной командной игрой (>= 3 побед)
        $set_1_check = $set_1_norm;
        $set_2_check = $set_2_norm;
       $pair_num_check = !empty($aResults['pair_number']) ? (int)$aResults['pair_number'] : 0;
       $is_team_check = (!empty($aResults['match_id']) && $pair_num_check == 0) || ($is_team_league && $pair_num_check == 0);
       
       // Если это завершенная командная игра (>= 3 побед), не перезаписываем статус
       if ($is_team_check && ($set_1_check >= 3 || $set_2_check >= 3) && !empty($text)) {
           // Статус уже установлен как "Гра завершена!" - не перезаписываем
       } else {
           // Для других случаев показываем информацию о столе
           $text = '<span class="coral_color tableBig" post_string="&turnir_id='.poste('turnir_id').'&etap_id='.poste('etap_id').'" 
           newgame="'.$id.'">Старт '.$aResults['start_game'].'<br> Т'.$aResults['table_game'].'</span>';
       }
   }
  
   }
   else 
   {
        // Для командных турниров используем "Визначити команди", для обычных - "Визначення гравців"
        if ($is_team_league) {
            $text = 'Визначити<br>команди';
        } else {
            $text = 'Визначення<br>гравців';
        }
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
