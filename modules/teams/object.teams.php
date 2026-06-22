<?php
// класс описующий структуру модуля команд
class TeamsObject extends ObjectRT 
{

  function init ()
  {
      require_once __DIR__ . '/../teamplayers/func/func.teamplayers.php';

      $fio_search = poste('fio_search');
      $action=SystemClass::getAction();
      $league_id = teams_get_selected_league_id();
      $team_leagues = teams_get_filter_leagues();
      if ($league_id <= 0 && !empty($team_leagues[0]['id'])) {
          $league_id = (int)$team_leagues[0]['id'];
      }
      if ($league_id > 0 && !teams_filter_league_exists($team_leagues, $league_id) && !empty($team_leagues[0]['id'])) {
          $league_id = (int)$team_leagues[0]['id'];
      }
      $_SESSION['teams']['league_id'] = $league_id;
      self::$theed_tr_class='th_players_mob';
      if ($action=='list') {
          $_SESSION['MESSAGE_AJAX'] = teams_get_list_filter_html($team_leagues, $league_id, $fio_search);
          $teams_league_js = 'setTimeout(function(){var s=jQuery("#teams_league_filter");if(s.length&&jQuery.fn.chosen){var w=s.closest(".teams-list-filters").data("chosen-width")||"430px";if(s.data("chosen")){s.chosen("destroy");}s.chosen({width:w,no_results_text:"Співпадінь не знайдено",placeholder_text_single:s.attr("data-placeholder")||"Виберіть лігу"});s.off("change.teamsLeague").on("change.teamsLeague",function(){var leagueId=jQuery(this).val();if(leagueId){document.location.hash="#teams-list-league_id="+leagueId;send_ajax("","list","teams","&league_id="+leagueId);}});}},150);';
          SystemClass::$Java_script_module .= $teams_league_js;
          $_SESSION['JAVA_SCRIPT'] = (!empty($_SESSION['JAVA_SCRIPT']) ? $_SESSION['JAVA_SCRIPT'] : '').$teams_league_js;
      }
      
      if ($_SESSION['is_mobile']) {
          self::$table_class='table_mob_player table_teams_mob';
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => '<span class="f14 fw700 line14">Ред-ти</span>', 'type' => 'edit', 'width' => '40'));
          }
          $this->addFTL(array('name'=>'№','type'=>'number','width'=>'20'));
          $this->addFTL(array('name' => 'Ігроки', 'type' => 'expand_collapse', 'width' => '50', 'name_field' => 'id'));
          $this->addFTL(array('name' => '<span class="f14 fw700">Назва команди</span>', 'name_field' => 'name', 'oper' => 'edit', 'width'=>'200', 'bd_field' => 'name', 'filter' => 1, 'classAlign' => 'text-start', 'no_edit_table' => 1, 'class' => 'team-name-cell'));
          $this->addFTL(array('name' => 'Логотип', 'name_field' => 'logo', 'width'=>'100', 'bd_field' => 'logo','type'=>'image', 'classAlign' => 'td_align_left'));
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'Капітан', 'name_field' => 'captain_id', 'bd_field' => 'captain_id', 'width' => '150', 'type' => 'out_key', 'table'=>T_PLAYERS, 'parent_field'=>'captain_id', 'out_result_field'=>'name', 'where'=>' and is_team=0 '));
          }
          $this->addFTL(array('name' => 'Місто', 'name_field' => 'city', 'type' => 'ProstSpr', 'id_spis' => '4', 'bd_field' => 'city', 'width' => '80'));
          $this->addFTL(array('name' => 'Клуб', 'name_field' => 'club', 'type' => 'ProstSpr', 'id_spis' => '3', 'bd_field' => 'club', 'width' => '80'));
          $this->addFTL(array('name' => 'Рейтинг<br />ФНТУ', 'name_field' => 'reiting_ukraine', 'width' => '80', 'type' => 'get_func',
              'function' => 'get_team_reiting_ukraine_sum', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'Опл.<br />внес.', 'name_field' => 'is_opl_reiting', 'width' => '70', 'type' => 'get_func',
                  'function' => 'get_team_opl_summary', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
          }
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'К-ть<br /> гравців', 'name_field' => 'cnt_players', 'width' => '80', 'type' => 'get_func', 'function' => 'get_cnt_players', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
              $this->addFTL(array('name' => 'Додати<br />гравців', 'type' => 'add_players', 'width' => '80', 'name_field' => 'id'));
          }
      }else{
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'Редагу-<br />вати', 'type' => 'edit', 'width' => '40'));
          }
          $this->addFTL(array('name'=>'№','type'=>'number','width'=>'20'));
          // Добавляем кнопку раскрытия/сворачивания (показываем всегда, но активируем только если есть игроки)
          $this->addFTL(array('name' => 'Ігроки', 'type' => 'expand_collapse', 'width' => '50', 'name_field' => 'id'));
         $this->addFTL(array('name' => 'Назва команди', 'name_field' => 'name', 'oper' => 'edit', 'width'=>'200', 'bd_field' => 'name', 'filter' => 1, 'classAlign' => 'text-start', 'no_edit_table' => 1, 'class' => 'team-name-cell'));
          $this->addFTL(array('name' => 'Логотип', 'name_field' => 'logo', 'width'=>'100', 'bd_field' => 'logo','type'=>'image', 'classAlign' => 'td_align_left'));
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'Капітан', 'name_field' => 'captain_id', 'bd_field' => 'captain_id', 'width' => '150', 'type' => 'out_key', 'table'=>T_PLAYERS, 'parent_field'=>'captain_id', 'out_result_field'=>'name', 'where'=>' and is_team=0 '));
          }
          $this->addFTL(array('name' => 'Місто', 'name_field' => 'city', 'type' => 'ProstSpr', 'id_spis' => '4', 'bd_field' => 'city', 'width' => '80'));
          $this->addFTL(array('name' => 'Клуб', 'name_field' => 'club', 'type' => 'ProstSpr', 'id_spis' => '3', 'bd_field' => 'club', 'width' => '80'));
          $this->addFTL(array('name' => 'Рейтинг<br />ФНТУ', 'name_field' => 'reiting_ukraine', 'width' => '80', 'type' => 'get_func',
              'function' => 'get_team_reiting_ukraine_sum', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'Опл.<br />внес.', 'name_field' => 'is_opl_reiting', 'width' => '70', 'type' => 'get_func',
                  'function' => 'get_team_opl_summary', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
          }
          // Добавляем колонку для показа количества игроков в команде
          // Используем get_func для вычисления количества игроков через функцию get_cnt_players
          // no_sql=1 предотвращает включение поля в SQL запрос, так как его нет в таблице
          if ($_SESSION['gt']['user_rule']<10) {
              $this->addFTL(array('name' => 'К-ть<br /> гравців', 'name_field' => 'cnt_players', 'width' => '80', 'type' => 'get_func', 'function' => 'get_cnt_players', 'no_sql' => 1, 'classAlign' => 'td_align_center'));
              // Добавляем кнопку "Додати гравців" в список
              $this->addFTL(array('name' => 'Додати<br />гравців', 'type' => 'add_players', 'width' => '80', 'name_field' => 'id'));
          }
      }
      
      if ($_SESSION['gt']['user_rule']==1) {
          if ($_SESSION['is_mobile']) {
              $this->addFTL(array('name' => '<span class="f14 fw700 line14">Вид-ти</span>', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
          }else
          $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'name'));
      }

// Форма редактирования/добавления
// ВАЖНО: Скрытое поле is_team устанавливается в триггере befor.edit_ok.php
// Не используем default_value, так как оно может не работать для hidden полей
$this->addFF(array('name'=>'is_team','name_field'=>'is_team','bd_field'=>'is_team','type'=>'hidden'));
$this->addFF(array('name'=>'Назва команди','name_field'=>'name','bd_field'=>'name','required'=>'Назва команди обов"язкова', 'pattern'=>'.{3,}'));
$this->addFF(array('name'=>'Логотип команди','name_field'=>'logo','bd_field'=>'logo', 'type'=>'img'));
$this->addFF(array('name'=>'Капітан команди','name_field'=>'captain_id','type'=>'out_keynosql',
          'out_result_field'=>'name',
          'bd_field'=>'captain_id',
          'mess'=>'Виберіть капітана команди',
          'where'=>' and is_team=0 and not_use=0 and ispara=0 ',
          'table'=>T_PLAYERS,
          'no_vubor' => '',
          'width'=> '500',
          'speedsearch'=>array('min_letter'=>3,
              'result_fields_dop'=>array('id'),'table'=>T_PLAYERS,'where'=>' is_team=0 and not_use=0 and ispara=0 and ' ),
          'module'=>'players',
          'descr_table'=>array(
              array('name'=>'ПІБ гравця','name_field'=>'name','width'=>'250','filter'=>'1'),
              array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
              array('name'=>'Місто','name_field'=>'city_def','width'=>'50','filter'=>'1'),
          )
      ));
$this->addFF(array('name'=>'Місто','name_field'=>'city','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city'));
$this->addFF(array('name'=>'Клуб','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club'));

// Создаем подменю для управления игроками команды
if ($_SESSION['gt']['user_rule']<10) {
    $team_id = poste('id');
    if (!empty($team_id) && SystemClass::getAction() == 'edit') {
        self::$submenu_edit = array(
            'team_players' => array(
                'menu_name' => 'Управління гравцями команди',
                'module' => 'teamplayers',
                'action' => 'list',
                'post' => 'team_id='.$team_id
            ),
        );
    }
}

//поиск с фильтрацией
$strSearch='';
if (!empty($fio_search))
{
    $strSearch = ' AND p.name LIKE "%'.$fio_search.'%"';
}

// Фильтр только команды (is_team=1)
$strLeague = '';
if (!empty($league_id)) {
    $strLeague = ' and exists(select * from `'.T_TEAM_PLAYERS_LEAGUE.'` tpl where tpl.league_id='.(int)$league_id.' and tpl.team_id=p.id) ';
}
if ($_SESSION['gt']['user_rule']<>1)   
    $_SESSION['teams']['where']=' and p.is_team=1 and p.not_use=0 '.$strSearch.$strLeague;
else 
    $_SESSION['teams']['where']=' and p.is_team=1 '.$strSearch.$strLeague;
$_SESSION['teams']['sort_default']=' name asc';
if (!empty($league_id)) {
    $_SESSION['POST_RETURN'] = 'teams-list-league_id='.(int)$league_id;
    SystemClass::setPost_return('teams-list-league_id='.(int)$league_id);
}

$this->setTableModule(T_PLAYERS);
    
self::$nameZ='';
self::$nameZList='Команди';
self::$nameZEdit='Редагування команди';
    
if ($_SESSION['gt']['user_rule']<10)
    self::$submenu_list =array(
        'back' => array('module' => 'leagues', 'action' => 'list'),
    );

// JavaScript для раскрытия/сворачивания игроков теперь встроен прямо в HTML через onclick
// Это гарантирует работу без зависимости от порядка загрузки скриптов

// Добавляем CSS для исправления чередования строк и круглых логотипов
if ($action == 'list') {
    $css_code = '
    <style>
        /* Исключаем строки игроков из striped паттерна */
        #parts_table_ > tbody > tr.team-player-row {
            background-color: #f8f9fa !important;
        }
        /* Полностью отключаем автоматическое чередование Bootstrap для строк команд */
        #parts_table_.table-striped > tbody > tr.team-row:nth-of-type(odd),
        #parts_table_.table-striped > tbody > tr.team-row:nth-of-type(even),
        #parts_table_.table-striped > tbody > tr.team-row {
            --bs-table-striped-bg: transparent !important;
            background-color: transparent !important;
        }
        /* Принудительно наследуем фон строки для ячеек команд */
        #parts_table_.table-striped > tbody > tr.team-row > * {
            --bs-table-accent-bg: transparent !important;
            background-color: inherit !important;
        }
        /* Зебра только для строк команд */
        #parts_table_ > tbody > tr.team-row-odd > * {
            background-color: rgba(0,0,0,.05) !important;
        }
        #parts_table_ > tbody > tr.team-row-even > * {
            background-color: transparent !important;
        }
        /* Вертикальное выравнивание в строках игроков (мобайл) */
        #parts_table_ > tbody > tr.team-player-row > * {
            vertical-align: middle !important;
        }
        /* Мобайл: общий размер шрифта для ячеек */
        #parts_table_.table_mob_player td,
        #parts_table_.table_mob_player td a {
            font-size: 10px !important;
            line-height: 12px !important;
        }
        /* Мобайл: размер шрифта для заголовков */
        #parts_table_.table_mob_player th {
            font-size: 10px !important;
            line-height: 12px !important;
        }
        /* Круглые логотипы для команд */
        #parts_table_ td img[src*="files"] {
            border-radius: 50% !important;
            object-fit: cover !important;
            width: 50px !important;
            height: 50px !important;
            display: block;
            margin: 0 auto;
        }
    </style>';
    // Добавляем CSS через JavaScript
    $js_css = 'if(typeof document !== "undefined"){var style=document.createElement("style");style.innerHTML=\''.str_replace(array("\n", "\r", "'"), array(' ', '', "\\'"), trim($css_code)).'\';document.head.appendChild(style);}';
    $js_code = 'if(typeof document !== "undefined"){(function(){var run=function(){var table=document.getElementById("parts_table_");if(table){table.classList.remove("table-striped");}var rows=document.querySelectorAll("#parts_table_ tbody tr.team-row");var i=0;rows.forEach(function(row){i++;row.classList.remove("team-row-odd","team-row-even");row.classList.add(i%2?"team-row-odd":"team-row-even");});};if(typeof jQuery!=="undefined"){jQuery(function(){run();});}else{if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",run);}else{run();}}})();}';
    $existing_js = SystemClass::getJava_script();
    SystemClass::setJava_script((!empty($existing_js) ? $existing_js : '').$js_css.$js_code);
}
   
  }
}

function teams_get_selected_league_id()
{
    $league_id = (int)poste('league_id');
    if ($league_id <= 0) {
        $league_id = (int)get('league_id');
    }
    if ($league_id <= 0 && !empty($_SESSION['teams']['league_id'])) {
        $league_id = (int)$_SESSION['teams']['league_id'];
    }

    return $league_id;
}

function teams_get_filter_leagues()
{
    return db_list('SELECT l.id, l.name, l.dat
        FROM `bs_leagues` l
        WHERE COALESCE(l.is_team_league,0)=1
          AND EXISTS(
              SELECT 1
              FROM `'.T_SPRLIST_VALUES.'` sv
              WHERE sv.id=l.status
                AND sv.teg IN ("active","finish")
          )
        ORDER BY l.dat DESC, l.id DESC');
}

function teams_filter_league_exists($team_leagues, $league_id)
{
    foreach ($team_leagues as $league) {
        if ((int)$league['id'] === (int)$league_id) {
            return true;
        }
    }

    return false;
}

function teams_get_list_filter_html($team_leagues, $league_id, $fio_search)
{
    $search_value = htmlspecialchars((string)$fio_search, ENT_QUOTES, 'UTF-8');
    $is_mobile = !empty($_SESSION['is_mobile']);
    $wrap_style = $is_mobile
        ? 'display:flex;align-items:flex-start;gap:8px;flex-direction:column;margin-left:0;text-shadow:none;max-width:100%;width:100%;'
        : 'display:flex;align-items:center;gap:12px;flex-wrap:nowrap;margin-left:20px;text-shadow:none;max-width:100%;width:auto;';
    $search_width = $is_mobile ? '100%' : '280px';
    $search_min_width = $is_mobile ? '100%' : '280px';
    $league_width = $is_mobile ? '100%' : '430px';
    $chosen_width = $is_mobile ? '100%' : '430px';
    $search_padding = $is_mobile ? '48px' : '54px';
    $icon_left = $is_mobile ? '12px' : '14px';
    $html = '<div class="teams-list-filters" data-chosen-width="'.$chosen_width.'" style="'.$wrap_style.'">';

    $html .= '<div class="input__wrapper" style="margin-left:0;width:'.$search_width.'!important;min-width:'.$search_min_width.';max-width:'.$search_width.';flex:0 0 '.$search_width.';"><svg class="input__icon_player" style="left:'.$icon_left.';"><use xlink:href="#poisk"></use></svg><input type="text" class="form-control" placeholder="&#1055;&#1086;&#1096;&#1091;&#1082; &#1082;&#1086;&#1084;&#1072;&#1085;&#1076;&#1080;" id="search_field_teams" style="margin-left:0;width:100%!important;max-width:'.$search_width.';padding-left:'.$search_padding.';" speeds="0" value="'.$search_value.'"></div>';

    if (!empty($team_leagues)) {
        $html .= '<select class="chosen-select" id="teams_league_filter" data-placeholder="&#1042;&#1080;&#1073;&#1077;&#1088;&#1110;&#1090;&#1100; &#1083;&#1110;&#1075;&#1091;" style="width:'.$league_width.'!important;min-width:'.$league_width.';max-width:'.$league_width.';flex:0 0 '.$league_width.';">';
        foreach ($team_leagues as $league) {
            $id = (int)$league['id'];
            $selected = ($id === (int)$league_id) ? ' selected="selected"' : '';
            $name = htmlspecialchars($league['name'], ENT_QUOTES, 'UTF-8');
            $html .= '<option value="'.$id.'"'.$selected.'>'.$name.'</option>';
        }
        $html .= '</select>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Функция для подсчета количества игроков в команде
 * @param string $field - имя поля (не используется, но требуется для совместимости)
 * @param int $id - ID команды
 * @param array $data - массив данных команды
 * @return string - количество игроков в виде строки
 */
function get_cnt_players($field, $id, $data)
{
    // Получаем ID команды из параметра $id или из массива $data
    $team_id = !empty($id) ? (int)$id : (!empty($data['id']) ? (int)$data['id'] : 0);
    
    if ($team_id > 0) {
        // Подсчитываем количество игроков в команде
        $league_id = teams_get_selected_league_id();
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COUNT(*) as cnt');
        $result = db_row($sql);
        
        // Проверяем результат запроса
        if ($result !== false && is_array($result) && array_key_exists('cnt', $result)) {
            // Возвращаем количество игроков
            return (string)(int)$result['cnt'];
        }
    }
    
    // Если что-то пошло не так, возвращаем 0
    return '0';
}

/**
 * Функция для подсчета суммарного рейтинга ФНТУ команды
 * @param string $field
 * @param int $id
 * @param array $data
 * @return string
 */
function get_team_reiting_ukraine_sum($field, $id, $data)
{
    $team_id = !empty($id) ? (int)$id : (!empty($data['id']) ? (int)$data['id'] : 0);

    if ($team_id > 0) {
        $league_id = teams_get_selected_league_id();
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COALESCE(SUM(p.reiting_ukraine),0) as total');
        $result = db_row($sql);
        if ($result !== false && is_array($result) && array_key_exists('total', $result)) {
            $total = (int)$result['total'];
            return '<span style="font-weight:700; font-size:16px; color:#0b5ed7;">'.$total.'</span>';
        }
    }

    return '0';
}

/**
 * Функция для подсчета оплаты внеска по команде (оплачено/всего)
 * @param string $field
 * @param int $id
 * @param array $data
 * @return string
 */
function get_team_opl_summary($field, $id, $data)
{
    $team_id = !empty($id) ? (int)$id : (!empty($data['id']) ? (int)$data['id'] : 0);

    if ($team_id > 0) {
        $league_id = teams_get_selected_league_id();
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COUNT(*) as total, SUM(CASE WHEN p.is_opl_reiting=1 THEN 1 ELSE 0 END) as paid');
        $result = db_row($sql);
        if ($result !== false && is_array($result) && array_key_exists('total', $result)) {
            $total = (int)$result['total'];
            $paid = !empty($result['paid']) ? (int)$result['paid'] : 0;
            return $paid.'/'.$total;
        }
    }

    return '0/0';
}
