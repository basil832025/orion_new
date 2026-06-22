<?php
require_once __DIR__ . '/../../teamplayers/func/func.teamplayers.php';
// класс описующий структуру модуля команд в турнире
class TurnirsTeamsObject extends ObjectRT 
{   
  function init ()
  {
$_SESSION['MESSAGE_AJAX'] = '';
$turnir_id = (int)poste('turnir_id');
if ($turnir_id <= 0) {
    $turnir_id = (int)poste('id');
}
 $league_id = poste('league_id');
 if (empty($league_id)) {
     $league_id = get('league_id');
 }
$menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
$virt = poste('virt');
      self::$table_class='table_mob_turn table_mob_player';
      self::$theed_tr_class='th_players_mob';
 $aTurnir = array();
  if (!empty($turnir_id))
  {
      $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
      $aTurnir= db_row($sql);
  }
  if (empty($league_id) && !empty($aTurnir['league_id'])) {
      $league_id = (int)$aTurnir['league_id'];
  }
  $menu_league = !empty($league_id) ? '&league_id='.$league_id : '';
      if (!empty($turnir_id) && !empty($league_id) && !empty($aTurnir) && empty($aTurnir['is_team_qual'])) {
          $teams_cnt = (int)db_field('SELECT COUNT(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` WHERE turnir_id='.(int)$turnir_id, 'cnt');
          $league_groups_cnt = (int)db_field('SELECT COUNT(*) as cnt FROM `bs_league_team_groups` WHERE league_id='.(int)$league_id, 'cnt');
          if ($teams_cnt > 0 && $league_groups_cnt == 0) {
              $qual_turnir_id = (int)db_field('SELECT id FROM `'.T_TURNIRS.'` WHERE league_id='.(int)$league_id.' AND is_team_qual=1 ORDER BY id ASC LIMIT 1', 'id');
              if ($qual_turnir_id > 0) {
                  $qual_leagues_count = (int)db_field('SELECT team_leagues_count FROM `'.T_TURNIRS.'` WHERE id='.$qual_turnir_id, 'team_leagues_count');
                  if ($qual_leagues_count > 0) {
                      auto_assign_league_groups($qual_turnir_id, (int)$league_id, $qual_leagues_count);
                  }
              }
          }
      }
      if ($turnir_id > 0) {
          $sql='select count(cnt_games) as cnt_g from '.T_TURNIR_PLAYERS.' t where turnir_id='.$turnir_id.' and cnt_games is not null';
          $cnt_g = db_field($sql,'cnt_g');
      } else {
          $cnt_g = 0;
          if (defined('ERROR_DB') && ERROR_DB) {
              wLog('Пустой turnir_id в turnirsteams (object.turnirsteams.php)', 'error','error');
          }
      }
        $url_back = !empty($virt) ? 'turnirsshtraph-list' : 'turnirs-list'.$menu_league;
      if ($_SESSION['is_mobile'] ){

          SystemClass::$Java_script_module='show_zag_left("#'.$url_back.'");';
      }else{
          $show_zag_left='show_zag_center();show_zag_left_big("#'.$url_back.'");';
          SystemClass::$Java_script_module=$show_zag_left;
      }
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20','width_mob'=>'22'));
      if (empty($virt))
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'20')); 

// Добавляем кнопку раскрытия/сворачивания игроков
$this->addFTL(array('name' => 'Гравці', 'type' => 'expand_collapse', 'width' => '50', 'name_field' => 'player_id'));

// Добавляем колонку логотипа команды перед названием команды
// no_sql => 1 - исключаем поле из SQL-запроса, так как это вычисляемое поле
$this->addFTL(array('name' => 'Логотип', 'name_field' => 'logo_team', 'width'=>'100', 'bd_field' => 'logo_team','type'=>'image', 'classAlign' => 'td_align_left', 'no_sql' => 1));

$this->addFTL(array('name'=>'Назва команди','type'=>'out_key',
    'oper' => 'edit','target'=>true, 'width_mob'=>'130',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'name',
    'width'=>'200','name_field'=>'player_id','classAlign'=>'text-start'));
if (empty($virt)) {
    $this->addFTL(array('name' => 'К-ть зіграних<br /> ігор', 'name_mob' => 'К-ть<br />зіграних<br /> ігор', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_games'));
    $this->addFTL(array('name' => 'К-ть виграних<br /> ігор', 'name_mob' => 'К-ть<br /> перемог', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_wins'));
    $this->addFTL(array('name' => 'К-ть поразок', 'name_mob' => 'К-ть<br />  поразок', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_lose'));
    $this->addFTL(array('name' => 'Місце', 'type' => 'field', 'width' => '9', 'name_field' => 'mesto'));
    if (!empty($league_id)) {
        $league_id_int = (int)$league_id;
        $league_group_sql = '(SELECT group_num FROM `bs_league_team_groups` ltg WHERE ltg.league_id='.$league_id_int.' AND ltg.team_id=p.player_id LIMIT 1)';
        if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10 && !empty($_SESSION['gt']['user_login']) && !empty($aTurnir['is_team_qual'])) {
            $this->addFTL(array('name' => 'Ліга', 'type' => 'team_league_select', 'width' => '9', 'name_field' => 'team_league_group', 'bd_field' => $league_group_sql, 'classAlign' => 'td_align_center'));
        } else {
            $this->addFTL(array('name' => 'Ліга', 'type' => 'get_func', 'width' => '9', 'name_field' => 'team_league_group', 'bd_field' => $league_group_sql, 'function' => 'get_team_league_group', 'classAlign' => 'td_align_center'));
        }
    }
    if (!empty($league_id))
    $this->addFTL(array('name' => 'Балів', 'type' => 'field', 'width' => '9', 'name_field' => 'points'));
}
      $this->addFTL(array('name'=>'Місто','type'=>'out_key_prostspr',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'city',
          'width'=>'80','name_field'=>'city', 'id_spis'=>'4'));
      $this->addFTL(array('name'=>'Рейтинг ФНТУ', 'name_field'=>'reiting_ukraine', 'width'=>'80', 'type'=>'get_func',
          'function'=>'get_team_reiting_ukraine', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
      if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10) {
          $this->addFTL(array('name'=>'Опл.<br /> член.<br />внес.', 'type'=>'get_func',
              'width'=>'40', 'name_field'=>'is_opl_reiting', 'function'=>'get_team_opl_summary_turnir', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
      }
          if (empty($virt)) {
              // Добавляем кнопку для управления игроками команды только для авторизованных пользователей
              if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10) {
                  $this->addFTL(array('name' => 'Додати гравців', 'type' => 'add_players', 'width' => '60', 'name_field' => 'player_id'));
              }
              $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'id'));
          }

//================================================================================================
// описание полей формы модуля при редактировании или добавления
     $this->addFF(array('name'=>'Лига','name_field'=>'league_id','type'=>'hidden'));
 $team_filter = ' and is_team=1 and not_use=0 ';
 if (!empty($turnir_id)) {
     $team_filter .= ' and not exists(select * from '.T_TURNIR_PLAYERS.' tp where tp.turnir_id='.(int)$turnir_id.' and tp.player_id=p.id) ';
 }
 $speedsearch_filter = ' is_team=1 and not_use=0 and ';
 if (!empty($turnir_id)) {
     $speedsearch_filter .= ' not exists(select * from '.T_TURNIR_PLAYERS.' tp where tp.turnir_id='.(int)$turnir_id.' and tp.player_id=m.id) and ';
 }
 $this->addFF(array('name'=>'Команда','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'player_id',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id',
                    'mess'=>'Виберіть команду',
                     'where'=>$team_filter,
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                      'width'=> '980',
                    'required'=>'Команда обов"язкова',
                    'speedsearch'=>array('min_letter'=>3,
                        'result_fields_dop'=>array('id','city','club','captain_id'),'table'=>T_PLAYERS,'where'=>$speedsearch_filter ),
                    'module'=>'teams',
                    'descr_table'=>array(
                        array('name'=>'Назва команди','return_id_val'=>'name', 'name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Місто','return_id_val'=>'city','name_field'=>'city','width'=>'80','filter'=>'1'),
                        array('name'=>'Клуб','return_id_val'=>'club','name_field'=>'club','width'=>'80','filter'=>'1'),
                    )
                    ));
$this->addFF(array('name'=>'Назва нової команди','name_field'=>'new_name', 'size'=>'40','type'=>'TextNoSQL'));
  
                                                                                     
// описание полей формы модуля при редактировании или добавления

      $sWhere =  ' AND EXISTS(SELECT * from  bs_players pl where pl.id=p.player_id AND pl.is_team=1) ';

$_SESSION['turnirsteams']['where'] =$sWhere;
if (!empty($_SESSION['turnirsteams']['sort']) && $_SESSION['turnirsteams']['sort'] == 'reiting_ukraine') {
    if (!empty($league_id)) {
        $_SESSION['turnirsteams']['sort'] = '(SELECT COALESCE(SUM(pp.reiting_ukraine),0) FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl INNER JOIN `'.T_PLAYERS.'` pp ON pp.id=tpl.player_id WHERE tpl.league_id='.(int)$league_id.' AND tpl.team_id=p.player_id AND (pp.is_team IS NULL OR pp.is_team=0) AND pp.not_use=0)';
    } else {
        $_SESSION['turnirsteams']['sort'] = '(SELECT COALESCE(SUM(pp.reiting_ukraine),0) FROM `'.T_PLAYERS.'` pp WHERE pp.team_id=p.player_id AND (pp.is_team IS NULL OR pp.is_team=0) AND pp.not_use=0)';
    }
}

  $this->setTableModule(T_TURNIR_PLAYERS);
      if (!empty($turnir_id)) {
          $name_turnir = db_row('select name,dat  from `' . T_TURNIRS .
              '` where id=' . $turnir_id);
          $turnir_name = htmlspecialchars(stripslashes((string)$name_turnir['name']), ENT_QUOTES, 'UTF-8');
          $date = new DateTimeImmutable($name_turnir['dat']);
          $tdat = $date->format('d.m.Y');
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
              $nameZ='<div class="compare_zagl">Статистика команд "'.$turnir_name.' ('.$tdat.$title. ')"</div>';
          else
              $nameZ='<div class="poriv_zag">Статистика команд  "'.$turnir_name.'" ('.$tdat.$title. ')</div>';

          self::$nameZList=$nameZ;

          $nameZList = '';
          self::$nameZEdit = 'Редагування команди турніру "' . $turnir_name . '" (' . $tdat . ')';
      }
if ($_SESSION['gt']['user_rule']<10 && empty($virt))
{
    $turnir_id_param = !empty($turnir_id) ? '&turnir_id='.(int)$turnir_id : '';
    $league_id_param = !empty($league_id) ? '&league_id='.$league_id : '';
    self::$submenu_list =array( 
       'teamstats' => array('menu_name'=>'Перерахувати статистику команд','module' => 'turnirsteams', 'action' => 'teamstats', 'post' => $turnir_id_param.$league_id_param),
       'report_ok' => array('menu_name'=>'Перерахувати рейтинг по даному турніру','module' => 'turnirsteams', 'action' => 'raschet', 'post' => $turnir_id_param.$league_id_param),
    );

    if (!empty($aTurnir['is_team_qual'])) {
        self::$submenu_list['assign_league_groups'] = array(
            'menu_name' => 'Розподілити команди по лігам',
            'module' => 'turnirsteams',
            'action' => 'assign_league_groups',
            'post' => $turnir_id_param.$league_id_param
        );
    }
}
    
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );


if (empty($virt)) self::InitMainMenu(); else{
    $post_return = '&virt=1';
    SystemClass::setPost_return($post_return);
    $_SESSION['POST_RETURN'] =$post_return;
}

// Подключаем JavaScript для раскрытия/сворачивания игроков и CSS для круглых логотипов
$action = SystemClass::getAction();
if ($action == 'list') {
    $js_code = ' 
    if (typeof jQuery !== "undefined" && typeof jQuery.fn.ready !== "undefined") {
        jQuery(document).ready(function($) {
            $(document).off("click", ".team-expand-btn").on("click", ".team-expand-btn", function() {
                var teamId = $(this).data("team-id");
                var $btn = $(this);
                var $playerRows = $(".team-player-row[data-team-id=\"" + teamId + "\"]");
                
                if ($playerRows.is(":visible")) {
                    $playerRows.slideUp(200);
                    $btn.html("▶");
                } else {
                    $playerRows.slideDown(200);
                    $btn.html("▼");
                }
            });
        });
    }';
    
    // Добавляем CSS для круглых логотипов
    $css_code = '
    <style>
        /* Высота строк как в teams-list (мобайл) */
        #parts_table_.table_mob_turn tr {
            height: 59px !important;
        }
        #parts_table_.table_mob_turn tr td {
            min-height: 59px !important;
            height: 59px !important;
        }
        /* Круглые логотипы для команд в турнире */
        #parts_table_ td img[src*="files"] {
            border-radius: 50% !important;
            object-fit: cover !important;
            width: 50px !important;
            height: 50px !important;
            display: block;
            margin: 0 auto;
        }
    </style>';
    $js_css = 'if(typeof document !== "undefined"){var style=document.createElement("style");style.innerHTML=\''.str_replace(array("\n", "\r", "'"), array(' ', '', "\\'"), trim($css_code)).'\';document.head.appendChild(style);}';
    
    $existing_js = SystemClass::getJava_script();
    SystemClass::setJava_script((!empty($existing_js) ? $existing_js : '').$js_code.$js_css);
}

  self::$aParent[0]= ['name_field'=>'turnir_id', 'table'=>T_TURNIRS, 'type'=>'Hidden'];
  self::$aParent[1]= ['name_field'=>'league_id', 'type'=>'Hidden'];
  }
}

/**
 * Подсчет суммарного рейтинга ФНТУ по игрокам команды
 * @param string $field
 * @param int $id
 * @param array $data
 * @return string
 */
function get_team_reiting_ukraine($field, $id, $data)
{
    $team_id = 0;

    if (!empty($data['player_id']) && is_numeric($data['player_id'])) {
        $team_id = (int)$data['player_id'];
    }

    if ($team_id <= 0 && !empty($data['id']) && is_numeric($data['id'])) {
        $team_id = (int)db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$data['id'], 'player_id');
    }

    if ($team_id <= 0 && !empty($id) && is_numeric($id)) {
        $team_id = (int)db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$id, 'player_id');
    }

    if ($team_id > 0) {
        $league_id = teamplayers_resolve_league_id(poste('league_id'), !empty($data['turnir_id']) ? $data['turnir_id'] : poste('turnir_id'));
        $total = teamplayers_sum_reiting_ukraine($team_id, $league_id);
        return '<span style="font-weight:700; font-size:16px; color:#0b5ed7;">'.$total.'</span>';
    }

    return '0';
}

/**
 * Оплата внеска по команде (оплачено/всего) для turnirsteams
 * @param string $field
 * @param int $id
 * @param array $data
 * @return string
 */
function get_team_opl_summary_turnir($field, $id, $data)
{
    $team_id = 0;

    if (!empty($data['player_id']) && is_numeric($data['player_id'])) {
        $team_id = (int)$data['player_id'];
    }

    if ($team_id <= 0 && !empty($data['id']) && is_numeric($data['id'])) {
        $team_id = (int)db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$data['id'], 'player_id');
    }

    if ($team_id <= 0 && !empty($id) && is_numeric($id)) {
        $team_id = (int)db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$id, 'player_id');
    }

    if ($team_id > 0) {
        $league_id = teamplayers_resolve_league_id(poste('league_id'), !empty($data['turnir_id']) ? $data['turnir_id'] : poste('turnir_id'));
        $result = teamplayers_opl_summary($team_id, $league_id);
        return $result['paid'].'/'.$result['total'];
    }

    return '0/0';
}

?>
