<?php
include_once 'func.etapresult_2x_minus.php';
include_once 'func.etapresult_2x_minuska8.php';
include_once 'func.etapresult_olimp.php';
include_once dirname(__DIR__, 2).'/reiting/func/func.reiting.php';

function etap_group_label($turnir_id, $league_id)
{
    $turnir_id = (int)$turnir_id;
    $league_id = (int)$league_id;
    if ($turnir_id <= 0 && $league_id <= 0) {
        return 'Група';
    }

    $turnir = null;
    if ($turnir_id > 0) {
        $turnir = db_row('SELECT league_id, is_team_qual FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id);
        if ($league_id <= 0 && !empty($turnir['league_id'])) {
            $league_id = (int)$turnir['league_id'];
        }
    }

    if ($league_id <= 0) {
        return 'Група';
    }

    $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
    $is_team_qual = !empty($turnir['is_team_qual']) ? 1 : 0;

    return ($is_team_league === 1 && $is_team_qual === 0) ? 'Ліга' : 'Група';
}

function is_team_league_turnir($turnir_id)
{
    $turnir_id = (int)$turnir_id;
    if ($turnir_id <= 0) {
        return false;
    }

    static $cache = array();
    if (array_key_exists($turnir_id, $cache)) {
        return $cache[$turnir_id];
    }

    $row = db_row('SELECT l.is_team_league FROM `'.T_TURNIRS.'` t LEFT JOIN `bs_leagues` l ON l.id=t.league_id WHERE t.id='.$turnir_id.' LIMIT 1');
    $cache[$turnir_id] = !empty($row['is_team_league']) && (int)$row['is_team_league'] === 1;

    return $cache[$turnir_id];
}

function render_team_name_link($name, $player_id, $turnir_id)
{
    $player_id = (int)$player_id;
    if ($player_id <= 0 || !is_team_league_turnir($turnir_id)) {
        return $name;
    }

    static $is_team_cache = array();
    if (!array_key_exists($player_id, $is_team_cache)) {
        $is_team_cache[$player_id] = (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$player_id.' LIMIT 1', 'is_team') === 1;
    }

    if (!$is_team_cache[$player_id]) {
        return $name;
    }

    $safe_name = htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8');
    return '<span class="team-roster-link" data-team-id="'.$player_id.'" data-turnir-id="'.(int)$turnir_id.'" data-team-name="'.$safe_name.'" onclick="showTeamRoster(this, event)" style="cursor:pointer; text-decoration: underline dotted;">'.$safe_name.'</span>';
}

function etapresult_get_display_place_map($turnir_id, $etap_id, $players)
{
    if (empty($players) || !function_exists('ranking_get_group_places_map')) {
        return array();
    }

    $results = all_results($turnir_id, $etap_id);
    $played_groups = etapresult_get_played_groups_map($turnir_id, $etap_id);
    $grouped_players = array();

    foreach ($players as $player) {
        $group_num = isset($player['groups']) ? (int)$player['groups'] : 0;
        $grp_num = isset($player['grp_num']) ? (int)$player['grp_num'] : 0;
        if ($group_num <= 0 || $grp_num <= 0 || empty($player['player_id'])) {
            continue;
        }
        $grouped_players[$group_num][] = $player;
    }

    $display_map = array();
    foreach ($grouped_players as $group_num => $group_players) {
        if (empty($played_groups[$group_num])) {
            continue;
        }

        $places_map = ranking_get_group_places_map($group_players, $group_num, $results);
        foreach ($places_map as $grp_num => $place_data) {
            $display_map[$group_num][$grp_num] = (int)$place_data['display_place'];
        }
    }

    return $display_map;
}

function etapresult_get_played_groups_map($turnir_id, $etap_id)
{
    $turnir_id = (int)$turnir_id;
    $etap_id = (int)$etap_id;
    if ($turnir_id <= 0 || $etap_id <= 0) {
        return array();
    }

    static $cache = array();
    $cache_key = $turnir_id.'_'.$etap_id;
    if (array_key_exists($cache_key, $cache)) {
        return $cache[$cache_key];
    }

    $sql = 'SELECT DISTINCT COALESCE(group_num,0) AS group_num
        FROM '.T_REITING.'
        WHERE turnir_id='.$turnir_id.'
          AND etap_id='.$etap_id.'
          AND type_game=1
          AND (
              COALESCE(win_player,0)>0
              OR COALESCE(lose_player,0)>0
              OR TRIM(COALESCE(set_1,"")) NOT IN ("","0")
              OR TRIM(COALESCE(set_2,"")) NOT IN ("","0")
          )';

    $played_groups = array();
    $rows = db_list($sql);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $played_groups[(int)$row['group_num']] = 1;
        }
    }

    $cache[$cache_key] = $played_groups;
    return $played_groups;
}

function etapresult_mark_duplicate_places($players)
{
    if (empty($players)) {
        return $players;
    }

    $place_counts = array();
    foreach ($players as $player) {
        $place = isset($player['grp_mesto']) ? trim((string)$player['grp_mesto']) : '';
        if ($place === '') {
            continue;
        }
        if (!isset($place_counts[$place])) {
            $place_counts[$place] = 0;
        }
        $place_counts[$place]++;
    }

    foreach ($players as $key => $player) {
        $place = isset($player['grp_mesto']) ? trim((string)$player['grp_mesto']) : '';
        $players[$key]['grp_mesto_is_duplicate'] = ($place !== '' && !empty($place_counts[$place]) && $place_counts[$place] > 1) ? 1 : 0;
    }

    return $players;
}

function etapresult_get_match_points($raw_set_for, $raw_set_against)
{
    $set_for = tie_set_to_int($raw_set_for);
    $set_against = tie_set_to_int($raw_set_against);

    if ($raw_set_for === 'W') {
        return 2;
    }

    if ($raw_set_for === 'L') {
        return 0;
    }

    if ($set_for === 0 && $set_against === 0) {
        return '';
    }

    return ($set_for > $set_against) ? 2 : 1;
}



 function all_results_2xminuska ($etap_id,$turnir_id)
 {
      $aResultsNEW=array();
      //получаем результаты 
    $sql='SELECT (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_1) as name1,
                 (select  p.name from  '.T_PLAYERS.' p where p.id=r.pl_id_2) as name2,
                 (SELECT   p.ispara from bs_turnirs p where p.id=r.turnir_id) as ispara,
                   case when r.pl_id_1>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_1 and etap_id='.$etap_id.') else 0 end as mesto_all_1,
                 case when r.pl_id_2>0 then (select  p.mesto_all from  '.T_ETAPS_PLAYER_MESTA.' p where p.player_id=r.pl_id_2 and etap_id='.$etap_id.') else 0 end as mesto_all_2,

     r.* FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=2 
    order by olimp16_num;';
    $aResults = db_list($sql);
 //   s($aResults);
    // пройдемся по всем резульаьам обработаем 
    foreach ($aResults as $aRec)
    {
        $group_label = etap_group_label($turnir_id, poste('league_id'));
        if (empty($aRec['name1']))
            $aRec['name1'] = !empty($aRec['groups_pred1']) ? $group_label.' '.$aRec['groups_pred1']. ' місце '.$aRec['grp_num_pred1'] : '';
        if (empty($aRec['name2']))
            $aRec['name2'] = !empty($aRec['groups_pred2']) ? $group_label.' '.$aRec['groups_pred2']. ' місце '.$aRec['grp_num_pred2'] : '';

        $aResultsNEW[$aRec['olimp16_num']] = $aRec;
    }
    return $aResultsNEW;
 }
 function Mesta_2xminuska ($etap_id,$turnir_id)
 {
     $sql='SELECT (select  p.name from  '.T_PLAYERS.' p where p.id=r.player_id) as name,mesto_all
          FROM '.T_ETAPS_PLAYER_MESTA.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.'  and COALESCE(mesto_all,0)>0
    order by mesto_all;';
    $aResults = db_list($sql);
  
    return $aResults;
 }
 function all_results_table_comm ($etap_id,$turnir_id)
   {
     $aResultsNEW=[];
     $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where etap_id='.$etap_id;
     $playersMesta=db_list($sql);
     // в ключи масива загонем номер групи и начальний порядок
       $playersMestaNEW=[];
     foreach ($playersMesta as $elem)
     {
         $playersMestaNEW[$elem['groups']][$elem['grp_num']] = $elem;
     }
  //  s($playersMestaNEW);
     //получаем результаты
    $sql='SELECT * FROM '.T_REITING.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    // ВАЖНО: Используем grp_mesto (новое место после сортировки) вместо grp_num (старая позиция)
    // Если grp_mesto пустое (игры еще не все сыграны), используем grp_num
    $sql='SELECT  
        (SELECT COALESCE(NULLIF(m.grp_mesto, 0), m.grp_num) FROM '.T_ETAPS_PLAYER_MESTA.' m WHERE r.pl_id_1=m.player_id AND m.etap_id=r.etap_id AND m.turnir_id=r.turnir_id AND (COALESCE(m.groups,0)=COALESCE(r.group_num,0) OR COALESCE(r.group_num,0)=0) ORDER BY m.id DESC LIMIT 1) AS mesto1, 
        (SELECT COALESCE(NULLIF(m.grp_mesto, 0), m.grp_num) FROM '.T_ETAPS_PLAYER_MESTA.' m WHERE r.pl_id_2=m.player_id AND m.etap_id=r.etap_id AND m.turnir_id=r.turnir_id AND (COALESCE(m.groups,0)=COALESCE(r.group_num,0) OR COALESCE(r.group_num,0)=0) ORDER BY m.id DESC LIMIT 1) AS mesto2,
        r.* 
    FROM '.T_REITING.' r 
    WHERE turnir_id='.$turnir_id.' AND etap_id='.$etap_id.' AND type_game=1 
    ORDER BY group_num, pl_num_grp1, pl_num_grp2;';
    $aResults = db_list($sql);
  //  s($sql);
       //s($aResults);
 // поменяем проходом местами 2 игры чтобы были первыми все
       //
       $aTemp=[];
    foreach ($aResults as $aRec){
        if ($aRec['pl_num_grp1']<$aRec['pl_num_grp2']){
            $aTemp[]=$aRec;
        }else{
            $aTempRec=$aRec;
            $aRec['pl_num_grp2']=$aTempRec['pl_num_grp1'];
            $aRec['pl_num_grp1']=$aTempRec['pl_num_grp2'];
            $aRec['pl_id_1']=$aTempRec['pl_id_2'];
            $aRec['pl_id_2']=$aTempRec['pl_id_1'];
            $aRec['set_1']=$aTempRec['set_2'];
            $aRec['set_2']=$aTempRec['set_1'];
            $aRec['win_player']=$aTempRec['lose_player'];
            $aRec['lose_player']=$aTempRec['win_player'];
            $aRec['break_1']=$aTempRec['break_2'];
            $aRec['break_2']=$aTempRec['break_1'];
            $aTemp[]=$aRec;
        }
    }
    $aResults=$aTemp;
    
       // ВАЖНО: Создаем маппинг между grp_num (старая позиция) и grp_mesto (новое место)
       // Это нужно для правильной индексации $aResultsNEW после сортировки команд по местам
       $grp_num_to_mesto = array();
       foreach ($playersMesta as $elem) {
           if (!empty($elem['grp_mesto']) && $elem['grp_mesto'] > 0) {
               // Если есть grp_mesto (новое место), используем его для индексации
               $grp_num_to_mesto[$elem['grp_num']] = $elem['grp_mesto'];
           } else {
               // Если grp_mesto пустое, используем grp_num (старая позиция)
               $grp_num_to_mesto[$elem['grp_num']] = $elem['grp_num'];
           }
       }
       
       // пройдемся по всем резульаьам обработаем
    foreach ($aResults as $aRec)
    {
        // Используем ту же логику, что и в all_results_table() - сначала проверяем mesto1/mesto2, потом pl_num_grp1/pl_num_grp2
        // ВАЖНО: mesto1 и mesto2 теперь содержат grp_mesto (если есть), иначе grp_num
        if (!empty($aRec['mesto1']))
        {
            $pl_num_grp1 = $aRec['mesto1'];
            $pl_num_grp2 = $aRec['mesto2'];
        }else
        {
            $pl_num_grp1 = $aRec['pl_num_grp1'];
            $pl_num_grp2 = $aRec['pl_num_grp2'];
        }

        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['first_res'] = $aRec['set_1'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['second_res'] = $aRec['set_2'];
       $set_1 = tie_set_to_int($aRec['set_1']);
       $set_2 = tie_set_to_int($aRec['set_2']);
       $table_active = $aRec['table_game']>0 ? '<div class="t-grid-team_table1">T'.$aRec['table_game'].'</div>' : '';
        if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){
            if ($set_1 > $set_2 ){
                $ochko = 2 ;
                $ochko2 = 1 ;
                $colorClass='green_color';
            }else
            {
              $ochko = 1 ;
              $ochko2 = 2 ;
                $colorClass='coral_color';
            }
            if ($aRec['set_1']=='W' ){
                $ochko = 2 ;
                $ochko2 = 0 ;
                $colorClass='green_color';
            }else if ($aRec['set_1']=='L' )
            {
              $ochko = 0;
              $ochko2 = 2;
                $colorClass='coral_color';
            }
            
        //
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['ochko'] = $ochko;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['ochko'] = $ochko2;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
            // Сохраняем ID игры и match_id для показа детальных результатов
            $game_id = !empty($aRec['id']) ? (int)$aRec['id'] : 0;
            
            // Для командных турниров формируем match_id из etap_id и ID команд
            $match_id = '';
            if (!empty($aRec['match_id'])) {
                $match_id = $aRec['match_id'];
            } elseif (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2'])) {
                // Формируем match_id из etap_id и команд (как в team_lineups.php)
                $min_team = min((int)$aRec['pl_id_1'], (int)$aRec['pl_id_2']);
                $max_team = max((int)$aRec['pl_id_1'], (int)$aRec['pl_id_2']);
                $match_id = 'match_'.$etap_id.'_'.$min_team.'_'.$max_team;
            }
            
            $match_id_attr = !empty($match_id) ? ' data-match-id="'.htmlspecialchars($match_id).'"' : '';
            $team_a_id_attr = !empty($aRec['pl_id_1']) ? ' data-team-a-id="'.(int)$aRec['pl_id_1'].'"' : '';
            $team_b_id_attr = !empty($aRec['pl_id_2']) ? ' data-team-b-id="'.(int)$aRec['pl_id_2'].'"' : '';
            $etap_id_attr = ' data-etap-id="'.$etap_id.'"';
            
            // Делаем ячейку кликабельной для командных игр (если есть match_id или обе команды)
            $clickable_class = (!empty($match_id) || (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2']))) ? ' team-result-clickable' : '';
            $onclick_attr = (!empty($match_id) || (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2']))) ? ' onclick="showTeamMatchDetails(this, event)" style="cursor: pointer;"' : '';
            
            // Для командных турниров определяем очки: победа - 2 очка, поражение - 1 очко
            // $set_1 и $set_2 уже нормализованы, поэтому используем $ochko и $ochko2
            // $ochko = 1 означает победу для pl_num_grp1, $ochko2 = 0 означает поражение для pl_num_grp2
            // Но для командных турниров нужно: победа = 2 очка, поражение = 1 очко
            $team_ochko = '';
            if (($set_1 > $set_2) || $aRec['set_1'] == 'W') {
                // pl_num_grp1 (строка) выиграл - 2 очка
                $team_ochko = 2;
            } elseif (($set_2 > $set_1) || $aRec['set_2'] == 'W' || $aRec['set_1'] == 'L') {
                // pl_num_grp1 (строка) проиграл: обычное поражение = 1 очко, техническое (L) = 0
                $team_ochko = ($aRec['set_1'] == 'L') ? 0 : 1;
            }
            
            $ochko_html = $team_ochko !== '' ? '<br />'.$team_ochko : '';
            
            // Сохраняем очки и сеты для пересчета итоговых значений
            // Для pl_num_grp1: set_1 - его сеты, set_2 - сеты противника
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['team_ochko'] = $team_ochko;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['team_win_set'] = $set_1;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['team_lose_set'] = $set_2;
            
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] =
                '<div'.$onclick_attr.$match_id_attr.$team_a_id_attr.$team_b_id_attr.$etap_id_attr.' class="team-result'.$clickable_class.'" data-debug-match="'.$match_id.'"><span class="'.$colorClass.'">'.$aRec['set_1'] .':'. $aRec['set_2'].'</span>'.$ochko_html.'</div>
            ';
            
            // Сохраняем также данные игры для использования в модальном окне
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['game_id'] = $game_id;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['match_id'] = $match_id;
            // ВАЖНО: Сохраняем ID команд из записи игры (pl_id_1 и pl_id_2)
            // Эти ID будут использованы для формирования match_id и поиска игры в базе
            // Но в table_comm мы переопределим data-team-a-id и data-team-b-id на основе текущего порядка команд в таблице
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['team_a_id'] = !empty($aRec['pl_id_1']) ? (int)$aRec['pl_id_1'] : 0;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['team_b_id'] = !empty($aRec['pl_id_2']) ? (int)$aRec['pl_id_2'] : 0;
        } else
        {
                if ($aRec['table_game']>0 )
                  $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = 't-grid-team_table';
                else
                    $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
                $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = $table_active;

        }

    // теперь перевоачиваем для нижней части таблицы
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['first_res'] = $aRec['set_2'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['second_res'] = $aRec['set_1'];
        $set_1_rev = tie_set_to_int($aRec['set_1']);
        $set_2_rev = tie_set_to_int($aRec['set_2']);
        if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){
           
            if ($set_2_rev > $set_1_rev ){
                $ochko_rev = 2 ;
                $ochko2_rev = 1 ;
                $colorClass_rev='green_color';
            }else
            {
              $ochko_rev = 1 ;
              $ochko2_rev = 2 ;
                $colorClass_rev='coral_color';
            }
            if ($aRec['set_2']=='W' ){
                $ochko_rev = 2 ;
                $ochko2_rev = 0 ;
                $colorClass_rev='green_color';
            }else if ($aRec['set_2']=='L' )
            {
              $ochko_rev = 0;
              $ochko2_rev = 2;
                $colorClass_rev='coral_color';
            }
            
        //
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['ochko'] = $ochko_rev;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['ochko'] = $ochko2_rev;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';
            
            // Для обратной связи также формируем кликабельные элементы
            $match_id_rev = '';
            if (!empty($aRec['match_id'])) {
                $match_id_rev = $aRec['match_id'];
            } elseif (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2'])) {
                $min_team = min((int)$aRec['pl_id_1'], (int)$aRec['pl_id_2']);
                $max_team = max((int)$aRec['pl_id_1'], (int)$aRec['pl_id_2']);
                $match_id_rev = 'match_'.$etap_id.'_'.$min_team.'_'.$max_team;
            }
            
            $match_id_attr_rev = !empty($match_id_rev) ? ' data-match-id="'.htmlspecialchars($match_id_rev).'"' : '';
            $team_a_id_attr_rev = !empty($aRec['pl_id_2']) ? ' data-team-a-id="'.(int)$aRec['pl_id_2'].'"' : '';
            $team_b_id_attr_rev = !empty($aRec['pl_id_1']) ? ' data-team-b-id="'.(int)$aRec['pl_id_1'].'"' : '';
            $etap_id_attr_rev = ' data-etap-id="'.$etap_id.'"';
            
            $clickable_class_rev = (!empty($match_id_rev) || (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2']))) ? ' team-result-clickable' : '';
            $onclick_attr_rev = (!empty($match_id_rev) || (!empty($aRec['pl_id_1']) && !empty($aRec['pl_id_2']))) ? ' onclick="showTeamMatchDetails(this, event)" style="cursor: pointer;"' : '';
            
            // Для командных турниров определяем очки для обратной ячейки (pl_num_grp2, pl_num_grp1)
            // Здесь pl_num_grp2 - это строка, а pl_num_grp1 - это колонка
            // Счет перевернут: set_2:set_1 (где set_2 относится к pl_num_grp2, а set_1 - к pl_num_grp1)
            // Если set_2 > set_1, то pl_num_grp2 (строка) выиграл - 2 очка
            // Если set_2 < set_1, то pl_num_grp2 (строка) проиграл - 1 очко
            $team_ochko_rev = '';
            if (($set_2_rev > $set_1_rev) || $aRec['set_2'] == 'W') {
                // pl_num_grp2 (строка) выиграл - 2 очка
                $team_ochko_rev = 2;
            } elseif (($set_1_rev > $set_2_rev) || $aRec['set_2'] == 'L' || $aRec['set_1'] == 'W') {
                // pl_num_grp2 (строка) проиграл: обычное поражение = 1 очко, техническое (L) = 0
                $team_ochko_rev = ($aRec['set_2'] == 'L') ? 0 : 1;
            }
            
            $ochko_html_rev = $team_ochko_rev !== '' ? '<br />'.$team_ochko_rev : '';
            
            // Сохраняем очки и сеты для пересчета итоговых значений (обратная ячейка)
            // Для pl_num_grp2: set_2 - его сеты, set_1 - сеты противника
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['team_ochko'] = $team_ochko_rev;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['team_win_set'] = $set_2_rev;
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['team_lose_set'] = $set_1_rev;
            
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] =
                '<div'.$onclick_attr_rev.$match_id_attr_rev.$team_a_id_attr_rev.$team_b_id_attr_rev.$etap_id_attr_rev.' class="team-result'.$clickable_class_rev.'"><span class="'.$colorClass_rev.'">'.$aRec['set_2'] .':'. $aRec['set_1'].'</span>'.$ochko_html_rev.'</div>
            ';
        }else {
            if ($aRec['table_game']>0 )
                $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = 't-grid-team_table';
            else
                $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] = $table_active;
        }

    }
     //  s($aResultsNEW);
    return $aResultsNEW;

   } 
 function all_results_table ($etap_id,$turnir_id)
   {
     $aResultsNEW=[];
     $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where etap_id='.$etap_id;
     $playersMesta=db_list($sql);
     // в ключи масива загонем номер групи и начальний порядок
       $playersMestaNEW=[];
     foreach ($playersMesta as $elem)
     {
         $playersMestaNEW[$elem['groups']][$elem['grp_num']] = $elem;
     }
   //  s($playersMestaNEW);
      //получаем результаты
    $sql='SELECT * FROM '.T_REITING.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $sql='SELECT  
 (SELECT COALESCE(NULLIF(m.grp_mesto, 0), m.grp_num) FROM '.T_ETAPS_PLAYER_MESTA.' m WHERE r.pl_id_1=m.player_id AND m.etap_id=r.etap_id AND m.turnir_id=r.turnir_id AND (COALESCE(m.groups,0)=COALESCE(r.group_num,0) OR COALESCE(r.group_num,0)=0) ORDER BY m.id DESC LIMIT 1) AS mesto1, 
 (SELECT COALESCE(NULLIF(m.grp_mesto, 0), m.grp_num) FROM '.T_ETAPS_PLAYER_MESTA.' m WHERE r.pl_id_2=m.player_id AND m.etap_id=r.etap_id AND m.turnir_id=r.turnir_id AND (COALESCE(m.groups,0)=COALESCE(r.group_num,0) OR COALESCE(r.group_num,0)=0) ORDER BY m.id DESC LIMIT 1) AS mesto2,
 r.* 
FROM '.T_REITING.' r where turnir_id='.$turnir_id.' and etap_id='.$etap_id.' and type_game=1 order by group_num, pl_num_grp1,pl_num_grp2;';
    $aResults = db_list($sql);
 //   s($sql);
       //s($aResults);
    // пройдемся по всем резульаьам обработаем
    foreach ($aResults as $aRec)
    {
        if (!empty($aRec['mesto1']))
        {
            $pl_num_grp1 = $aRec['mesto1'];
            $pl_num_grp2 = $aRec['mesto2'];
        //    s($aRec);
        //    s('$pl_num_grp1='.$pl_num_grp1. ' $pl_num_grp2='.$pl_num_grp2);
        }else
        {
            $pl_num_grp1 = $aRec['pl_num_grp1'];
            $pl_num_grp2 = $aRec['pl_num_grp2'];
        }

        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['first_res'] = $aRec['set_1'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['second_res'] = $aRec['set_2'];
       $set_1 = tie_set_to_int($aRec['set_1']);
       $set_2 = tie_set_to_int($aRec['set_2']);
       $table_active = $aRec['table_game']>0 ? '<div class="t-grid-team_table1">T'.$aRec['table_game'].'</div>' : '';
        if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){

            if ($set_1 > $set_2 ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else
            {
              $ochko = 1 ;
                $colorClass='coral_color';
            }
            if ($aRec['set_1']=='W' ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else if ($aRec['set_1']=='L' )
            {
              $ochko = 0;
                $colorClass='coral_color';
            }

        //
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = '<span class="'.$colorClass.'">'.$aRec['set_1'] .':'. $aRec['set_2'].'</span><br />'.$ochko;
        } else
        {
                if ($aRec['table_game']>0 )
                  $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = 't-grid-team_table';
                else
                    $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['active'] = '';
                $aResultsNEW[$aRec['group_num']][$pl_num_grp1][$pl_num_grp2]['itog'] = $table_active;

        }

    // теперь перевоачиваем для нижней части таблицы
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['first_res'] = $aRec['set_2'];
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['second_res'] = $aRec['set_1'];
         $set_1 = tie_set_to_int($aRec['set_1']);
           $set_2 = tie_set_to_int($aRec['set_2']);
    if (isset($aRec['set_1']) && ($aRec['set_1']>0 || $aRec['set_2']>0 || $aRec['set_1']=='L' || $aRec['set_2']=='L')){


         if ($set_1 > $set_2 ){
                $ochko = 1 ;
                $colorClass='coral_color';
            }else
            {
              $ochko = 2 ;
                $colorClass='green_color';
            }
            if ($aRec['set_2']=='W' ){
                $ochko = 2 ;
                $colorClass='green_color';
            }else if ($aRec['set_2']=='L' )
            {
              $ochko = 0;
                $colorClass='coral_color';
            }
        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] = '<span class="'.$colorClass.'">'.$aRec['set_2'] .':'. $aRec['set_1'].'</span><br />'.$ochko;
        }else {


        if ($aRec['table_game']>0 )
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = 't-grid-team_table';
        else
            $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['active'] = '';


        $aResultsNEW[$aRec['group_num']][$pl_num_grp2][$pl_num_grp1]['itog'] = $table_active;

    }

    }
     //  s($aResultsNEW);
    return $aResultsNEW;

   }
   // пройтись по таблицам всем
  function all_tables_comm($etap_id,$turnir_id,$aResults, &$javascript = '')
  {
   
  
  /// old старый код  
 /*   $sql = 'SELECT case when reiting>0 then reiting else start_reiting end as beg_reit,reiting_ukraine,tp.id as turn_id, 
p.name,tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp,'.T_PLAYERS.' p where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and p.id=tp.player_id 
ORDER BY tp.groups,tp.grp_num';
*/
$sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
tp.id as turn_id,
(SELECT COUNT(*) AS cnt_win FROM bs_reiting r where turnir_id=tp.turnir_id and etap_id=tp.etap_id and win_player=tp.player_id) as cnt_win,
tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id,is_command_num   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.'
ORDER BY tp.is_command_num,tp.grp_num';
//ORDER BY tp.groups,tp.grp_num';

$aPlayers = db_list($sql);
$display_place_map = etapresult_get_display_place_map($turnir_id, $etap_id, $aPlayers);
$played_groups_map = etapresult_get_played_groups_map($turnir_id, $etap_id);
$group_label = etap_group_label($turnir_id, poste('league_id'));
//s($sql);
if (!empty($aPlayers)){
$aGroups = array();
$a=0;
$aTemp=array();
$aComm1=[];
$aComm2=[];
$whoCommand=0;
foreach ($aPlayers  as $k=> $player) 
{
   $player['grp_mesto_internal'] = (int)$player['grp_mesto'];
   $player['name'] = $player['grp_mesto']==0 && $player['player_id']==0 ? $group_label.' '.$player['groups_pred']. ' місце '.$player['grp_num_pred'] : $player['name'];
   $player['beg_reit'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' :round($player['beg_reit'],0);
   $player['reiting_ukraine'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['reiting_ukraine'];
   $group_num = (int)$player['groups'];
   $display_place = isset($display_place_map[$group_num][(int)$player['grp_num']])
       ? (int)$display_place_map[$group_num][(int)$player['grp_num']]
       : (int)$player['grp_mesto'];
   $player['grp_mesto'] = empty($played_groups_map[$group_num]) || $display_place==0 ? '' : $display_place;
   // Для очков всегда показываем числовое значение, даже если это 0
   $player['grp_ochki'] = (int)$player['grp_ochki'];
   $player['grp_win_set'] = $player['grp_win_set']==0 && $player['grp_lose_set']==0 ? '' : $player['grp_win_set'];
   $player['grp_lose_set'] =$player['grp_win_set']==0 &&  $player['grp_lose_set']==0 ? '' : $player['grp_lose_set'];
 //  s($player['groups']);
  if ($player['is_command_num']>0 && $player['is_command_num']<>$a ) {
    $aGroups[$player['is_command_num']-1] =$aTemp;
 //   s('a='.$a);
    if ($a==1) $aComm1=$aTemp;
    $aTemp=array();
  }
  if ($player['is_command_num']>0)
  {
      // ВАЖНО: Используем grp_mesto (новое место) для индексации, если оно есть, иначе grp_num (старая позиция)
      // Это нужно для правильного отображения команд в таблице после сортировки по местам
      $index_key = (!empty($player['grp_mesto_internal']) && $player['grp_mesto_internal'] > 0) ? $player['grp_mesto_internal'] : $player['grp_num'];
      $aTemp[$index_key] = $player;
      $a=$player['is_command_num'];
  }  
} 

    $aGroups[$player['is_command_num']] =$aTemp;
    if ($a==2) $aComm2=$aTemp;
    $aTemp=array();
  //  s($aComm1);
  //  s($aComm2);
 //s($aGroups);
    /*<style>
         @import url("css/print.css?ver=1");
         </style> */
    $Tables_content='  <div class="print_group">
         <div class="Section1"> 
           
 
  <div class="container-fluid">

  
  ';
    $str_add_player='';
  $sql ='SELECT 
count(*) as cnt  
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  tp.`groups`=0 and etap_id='.$etap_id.' and turnir_id='.$turnir_id;

$Cnt_new = db_field($sql,'cnt');
$minGrp=0;
 // здесь определимся какие группы меньше всего
   $group_label = etap_group_label($turnir_id, poste('league_id'));
   $table_class = $group_label === 'Ліга' ? ' league-results-table' : '';

   foreach ($aGroups as $grp => $aPlay)
  {
    $cnGrp =count($aPlay);
    if ($minGrp==0 || $cnGrp<$minGrp) $minGrp=$cnGrp;
 }  
   
 //foreach ($aGroups as $grp => $aPlay)

   // $cnGrp =count($aPlay);
   // s('$grp='.$grp);
   // s($aResults);
  //  if ($cnGrp==3) $ConTable =  table3($aPlay,$aResults[$grp]);
 //   if ($cnGrp==4) $ConTable =  table4($aPlay,$aResults[$grp]);
 ////   if ($cnGrp==5) $ConTable =  table5($aPlay,$aResults[$grp]);
//    if ($cnGrp==6) $ConTable =  table6($aPlay,$aResults[$grp]);
    //if ($cnGrp>6)
    //    $ConTable =  ;
    
    $str_add_player='';
   /* if (!empty($Cnt_new) && $minGrp==$cnGrp)
    {
        
        $str_add_player = '<span style="width:20px;cursor: pointer;" id="per_v_rozdel" module="etaps" action="add_playergrp" post_string="&etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp.'"  return_content_bool="" blok="" class="ajax_send" field_result="player_id" field_result_name="name" wintype="1" width_="500"><img width="35px" alt="Додати" title="Додати" src="img/slug_small/add.png" border="0"></span>';
    }*/
   $Tables_content .=' <div class="row">
  <div class="col">
 '.table_comm($aComm1,$aComm2 ,$aResults[1],'  ',$turnir_id,$etap_id ) .'

  </div>
  </div>
  ';
 //}
 $Tables_content .='
  </div>
  </div>
  
</div>

  
  ';
  
    // Добавляем JavaScript код для кликабельных элементов
    $js_code = '
function showTeamMatchDetails(element, event) {
    // Предотвращаем всплытие события, чтобы не срабатывали другие обработчики (например, из раздела "столы")
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    var matchId = element.getAttribute("data-match-id");
    var etapId = element.getAttribute("data-etap-id");
    var teamAId = element.getAttribute("data-team-a-id");
    var teamBId = element.getAttribute("data-team-b-id");
    var turnirId = '.$turnir_id.';
    
    // Если match_id не указан, формируем его из etap_id и команд
    if (!matchId && etapId && teamAId && teamBId) {
        var minTeam = Math.min(parseInt(teamAId), parseInt(teamBId));
        var maxTeam = Math.max(parseInt(teamAId), parseInt(teamBId));
        matchId = "match_" + etapId + "_" + minTeam + "_" + maxTeam;
        element.setAttribute("data-match-id", matchId);
    }
    
    if (!matchId || !etapId) {
        alert("Помилка: не вдалося визначити параметри матчу (match_id=" + matchId + ", etap_id=" + etapId + ")");
        return;
    }
    
    // Показываем окно загрузки
    var loadingModal = document.getElementById("staticBackdrop");
    if (loadingModal) {
        var bsModal = new bootstrap.Modal(loadingModal);
        bsModal.show();
    }
    
    // Загружаем детальные результаты
    var formData = new FormData();
    formData.append("ajax_method", "1");
    formData.append("module", "etapresult");
    formData.append("action", "match_details");
    formData.append("match_id", matchId);
    formData.append("etap_id", etapId);
    formData.append("turnir_id", turnirId);
    formData.append("team_a_id", teamAId);
    formData.append("team_b_id", teamBId);
    formData.append("return_content_bool", "1");
    
    fetch("", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.text();
    })
    .then(text => {
        
        // Закрываем окно загрузки
        if (loadingModal) {
            var bsModal = bootstrap.Modal.getInstance(loadingModal);
            if (bsModal) {
                bsModal.hide();
            }
        }
        
        try {
            var data = JSON.parse(text);
            
            if (data.error) {
                alert("Ошибка: " + data.error);
                return;
            }
            
            if (!data.success) {
                alert("Ошибка при получении данных");
                return;
            }
            
            // Создаем модальное окно с результатами
            showMatchDetailsModal(data);
        } catch (e) {
            // Если ответ не JSON, возможно, это HTML ошибка
            if (text.indexOf("<") !== -1) {
                alert("Ошибка: получен HTML вместо JSON. Возможно, action не найден.");
            } else {
                alert("Ошибка при обработке ответа сервера: " + e.message);
            }
        }
    })
    .catch(error => {
        if (loadingModal) {
            var bsModal = bootstrap.Modal.getInstance(loadingModal);
            if (bsModal) {
                bsModal.hide();
            }
        }
        alert("Ошибка при загрузке данных");
    });
}

function showTeamRoster(element, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    var teamId = element.getAttribute("data-team-id");
    var turnirId = element.getAttribute("data-turnir-id") || '.$turnir_id.';

    if (!teamId) {
        alert("Помилка: не вдалося визначити команду");
        return;
    }

    if (typeof window.__teamRosterRequestId === "undefined") {
        window.__teamRosterRequestId = 0;
    }
    var requestId = ++window.__teamRosterRequestId;

    var formData = new FormData();
    formData.append("ajax_method", "1");
    formData.append("module", "etapresult");
    formData.append("action", "team_roster");
    formData.append("team_id", teamId);
    formData.append("turnir_id", turnirId);
    formData.append("return_content_bool", "1");

    fetch("", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.text();
    })
    .then(text => {
        if (requestId !== window.__teamRosterRequestId) {
            return;
        }
        try {
            var data = JSON.parse(text);
            var rosterData = data;

            if (data.content && typeof data.content === "string") {
                rosterData = JSON.parse(data.content);
            }

            if (rosterData.error || !rosterData.success) {
                alert("Помилка: " + (rosterData.error || "не вдалося отримати склад команди"));
                return;
            }

            showTeamRosterModal(rosterData);
        } catch (e) {
            alert("Помилка при обробці відповіді сервера: " + e.message);
        }
    })
    .catch(function() {
        alert("Помилка при завантаженні складу команди");
    });
}

function showTeamRosterModal(data) {
    var playersHtml = "";
    if (data.players && data.players.length > 0) {
        data.players.forEach(function(player, index) {
            playersHtml += "<tr>" +
                "<td class=\"text-center\" style=\"width:60px;\">" + (index + 1) + "</td>" +
                "<td>" + player.name + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting || "") + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting_ukraine || "") + "</td>" +
                "</tr>";
        });
    } else {
        playersHtml = "<tr><td colspan=\"4\" class=\"text-center text-muted\" style=\"padding:18px;\">У команди поки немає активних гравців</td></tr>";
    }

    var modalElement = document.getElementById("teamRosterModal");
    if (!modalElement) {
        var modalHtml = "<div class=\"modal fade\" id=\"teamRosterModal\" tabindex=\"-1\" aria-hidden=\"true\">" +
            "<div class=\"modal-dialog modal-dialog-centered\" style=\"max-width:700px;\">" +
            "<div class=\"modal-content\">" +
            "<div class=\"modal-header\">" +
            "<h5 class=\"modal-title team-roster-title\" style=\"flex:1; text-align:center;\"></h5>" +
            "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Закрити\"></button>" +
            "</div>" +
            "<div class=\"modal-body\" style=\"padding:0;\">" +
            "<table class=\"table table-sm mb-0\">" +
            "<thead class=\"table-light\"><tr><th class=\"text-center\" style=\"width:60px;\">№</th><th>Гравець</th><th class=\"text-center\" style=\"width:120px;\">Рейт. клубу</th><th class=\"text-center\" style=\"width:120px;\">Рейт. ФНТУ</th></tr></thead>" +
            "<tbody class=\"team-roster-body\"></tbody>" +
            "</table>" +
            "</div>" +
            "<div class=\"modal-footer\" style=\"justify-content:center;\">" +
            "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">ОК</button>" +
            "</div>" +
            "</div></div></div>";
        document.body.insertAdjacentHTML("beforeend", modalHtml);
        modalElement = document.getElementById("teamRosterModal");
        modalElement.addEventListener("hidden.bs.modal", function() {
            var modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.dispose();
            }
        });
    }

    var titleElement = modalElement.querySelector(".team-roster-title");
    var bodyElement = modalElement.querySelector(".team-roster-body");
    if (titleElement) {
        titleElement.textContent = "Склад команди: " + (data.team_name || "");
    }
    if (bodyElement) {
        bodyElement.innerHTML = playersHtml;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

function showMatchDetailsModal(data) {
    // Закрываем и удаляем все существующие модальные окна перед открытием нового
    // Закрываем модальное окно из раздела "столы" (staticBackdrop)
    var staticBackdropModal = document.getElementById("staticBackdrop");
    if (staticBackdropModal) {
        var bsModalInstance = bootstrap.Modal.getInstance(staticBackdropModal);
        if (bsModalInstance) {
            bsModalInstance.hide();
        }
        // Удаляем после закрытия
        staticBackdropModal.addEventListener("hidden.bs.modal", function() {
            setTimeout(function() {
                if (staticBackdropModal && staticBackdropModal.parentNode) {
                    staticBackdropModal.remove();
                }
            }, 100);
        }, { once: true });
    }
    
    // Удаляем существующее модальное окно с результатами, если есть
    var existingModal = document.getElementById("matchDetailsModal");
    if (existingModal) {
        var existingBsModal = bootstrap.Modal.getInstance(existingModal);
        if (existingBsModal) {
            existingBsModal.hide();
        }
        setTimeout(function() {
            if (existingModal && existingModal.parentNode) {
                existingModal.remove();
            }
        }, 100);
    }
    
    // Формируем HTML для игр игроков
    var gamesHtml = "";
    if (data.player_games && data.player_games.length > 0) {
        data.player_games.forEach(function(game) {
            var pairLabel = "";
            if (game.pair_number == 1) pairLabel = " (A-Y)";
            else if (game.pair_number == 2) pairLabel = " (B-X)";
            else if (game.pair_number == 3) pairLabel = " (C-Z)";
            else if (game.pair_number == 4) pairLabel = " (A-X, додаткова)";
            else if (game.pair_number == 5) pairLabel = " (B-Y, вирішальна)";
            
            gamesHtml += "<div style=\"padding: 15px; border-bottom: 1px solid #dee2e6;\">";
            gamesHtml += "<div style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;\">";
            gamesHtml += "<div style=\"flex: 1;\">";
            gamesHtml += "<div style=\"font-weight: 600;\">" + game.player_1_name + (game.position_1 ? " (" + game.position_1 + ")" : "") + "</div>";
            gamesHtml += "</div>";
            gamesHtml += "<div style=\"margin: 0 20px; font-weight: 700; font-size: 18px;\">VS</div>";
            gamesHtml += "<div style=\"flex: 1; text-align: right;\">";
            gamesHtml += "<div style=\"font-weight: 600;\">" + game.player_2_name + (game.position_2 ? " (" + game.position_2 + ")" : "") + "</div>";
            gamesHtml += "</div>";
            gamesHtml += "</div>";
            gamesHtml += "<div style=\"text-align: center; font-size: 20px; font-weight: 700; color: #198754; margin: 10px 0;\">" + game.score + "</div>";
            if (game.start_game || game.end_game) {
                gamesHtml += "<div style=\"text-align: center; font-size: 12px; color: #6c757d;\">";
                if (game.start_game) gamesHtml += "Старт: " + game.start_game;
                if (game.end_game) gamesHtml += (game.start_game ? " | " : "") + "Фініш: " + game.end_game;
                gamesHtml += "</div>";
            }
            gamesHtml += "</div>";
        });
    } else {
        gamesHtml = "<div style=\"padding: 20px; text-align: center; color: #6c757d;\">Игры игроков еще не сыграны</div>";
    }
    
    var modalHtml = \'<div class="modal fade" id="matchDetailsModal" tabindex="-1" aria-labelledby="matchDetailsModalLabel" aria-hidden="true">\' +
        \'<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 700px;">\' +
        \'<div class="modal-content">\' +
        \'<div class="modal-header" style="border-bottom: 1px solid #dee2e6;">\' +
        \'<h5 class="modal-title" id="matchDetailsModalLabel" style="flex: 1; text-align: center; font-size: 20px; font-weight: 600;">Игры</h5>\' +
        \'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>\' +
        \'</div>\' +
        \'<div class="modal-body" style="padding: 0; max-height: 70vh; overflow-y: auto;">\' +
        \'<div style="padding: 20px; background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: center;">\' +
        \'<div style="font-size: 18px; font-weight: 600; margin-bottom: 5px;">\' + data.team_game.team_a_name + \' <span style="margin: 0 15px; font-weight: 700;">VS</span> \' + data.team_game.team_b_name + \'</div>\' +
        \'<div style="font-size: 24px; font-weight: 700; color: #198754;">\' + data.team_game.score + \'</div>\' +
        \'<div style="font-size: 14px; color: #6c757d; margin-top: 5px;">\' + data.team_game.status + \'</div>\' +
        \'</div>\' +
        gamesHtml +
        \'</div>\' +
        \'<div class="modal-footer" style="justify-content: center; border-top: 1px solid #dee2e6; padding: 15px;">\' +
        \'<button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="min-width: 100px;">ОК</button>\' +
        \'</div>\' +
        \'</div>\' +
        \'</div>\' +
        \'</div>\';
    
    // Добавляем модальное окно в body
    document.body.insertAdjacentHTML("beforeend", modalHtml);
    
    // Показываем модальное окно
    var modalElement = document.getElementById("matchDetailsModal");
    var modal = new bootstrap.Modal(modalElement);
    
    // Удаляем модальное окно после закрытия
    modalElement.addEventListener("hidden.bs.modal", function() {
        setTimeout(function() {
            if (modalElement) {
                modal.dispose();
                modalElement.remove();
            }
        }, 300);
    }, { once: true });
    
    modal.show();
}
';

    // Устанавливаем JavaScript через параметр по ссылке
    if (isset($javascript)) {
        $javascript = $js_code;
    }
    
    // JavaScript передается через параметр $javascript, который будет выполнен через ajax.js
    // НЕ добавляем <script> тег в HTML, чтобы не нарушать верстку

  return $Tables_content;
  }
  }
  function all_tables($etap_id,$turnir_id,$aResults, &$javascript = '')
  {


  /// old старый код
 /*   $sql = 'SELECT case when reiting>0 then reiting else start_reiting end as beg_reit,reiting_ukraine,tp.id as turn_id,
p.name,tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp,'.T_PLAYERS.' p where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and p.id=tp.player_id
ORDER BY tp.groups,tp.grp_num';
*/
$sql ='SELECT 
(select  case when reiting>0 then reiting else start_reiting end from '.T_PLAYERS.' p where p.id=tp.player_id) as beg_reit,
(select  reiting_ukraine from  '.T_PLAYERS.' p where p.id=tp.player_id) as reiting_ukraine,
(select  name from '.T_PLAYERS.' p where p.id=tp.player_id) as name,
tp.id as turn_id,
tp.groups,tp.grp_num,grp_win_set, grp_lose_set,grp_ochki,grp_mesto,groups_pred,grp_num_pred,player_id,is_command_num   
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.'
ORDER BY tp.groups,tp.grp_mesto,tp.grp_num';
//ORDER BY tp.groups,tp.grp_num';

$aPlayers = db_list($sql);
$display_place_map = etapresult_get_display_place_map($turnir_id, $etap_id, $aPlayers);
$played_groups_map = etapresult_get_played_groups_map($turnir_id, $etap_id);
//s($sql);
if (!empty($aPlayers)){
$aGroups = array();
$a=1;
$aTemp=array();
foreach ($aPlayers  as $k=> $player)
{
   $group_label = etap_group_label($turnir_id, poste('league_id'));
   $player['grp_mesto_internal'] = (int)$player['grp_mesto'];
   $player['name'] = $player['grp_mesto']==0 && $player['player_id']==0 ? $group_label.' '.$player['groups_pred']. ' місце '.$player['grp_num_pred'] : $player['name'];
   $player['beg_reit'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' :round($player['beg_reit'],0);
   $player['reiting_ukraine'] = $player['grp_mesto']==0 && $player['player_id']==0 ? '' : $player['reiting_ukraine'];
   $group_num = (int)$player['groups'];
   $display_place = isset($display_place_map[$group_num][(int)$player['grp_num']])
       ? (int)$display_place_map[$group_num][(int)$player['grp_num']]
       : (int)$player['grp_mesto'];
   $player['grp_mesto'] = empty($played_groups_map[$group_num]) || $display_place==0 ? '' : $display_place;
   // Для очков всегда показываем числовое значение, даже если это 0
   $player['grp_ochki'] = (int)$player['grp_ochki'];
   $player['grp_win_set'] = $player['grp_win_set']==0 && $player['grp_lose_set']==0 ? '' : $player['grp_win_set'];
   $player['grp_lose_set'] =$player['grp_win_set']==0 &&  $player['grp_lose_set']==0 ? '' : $player['grp_lose_set'];
 //  s($player['groups']);
  if ($player['groups']>0 && $player['groups']<>$a ) {
    $aGroups[$player['groups']-1] =$aTemp;
    $aTemp=array();
  }
  if ($player['groups']>0)
  {
      if (!empty($player['grp_mesto_internal']))
      $aTemp[$player['grp_mesto_internal']]=$player;
      else
      $aTemp[$player['grp_num']]=$player;
      $a=$player['groups'];
  }
}

    $aGroups[$player['groups']] =$aTemp;
    $aTemp=array();
    foreach ($aGroups as $group_key => $group_players) {
        $aGroups[$group_key] = etapresult_mark_duplicate_places($group_players);
    }
 //s($aGroups);
    /*<style>
         @import url("css/print.css?ver=1");
         </style> */
    $Tables_content='  <div class="print_group">
         <div class="Section1"> 
           
 
  <div class="container-fluid">

  
  ';
    $str_add_player='';
  $sql ='SELECT 
count(*) as cnt  
 FROM `'.T_ETAPS_PLAYER_MESTA.'` tp  where  tp.`groups`=0 and etap_id='.$etap_id.' and turnir_id='.$turnir_id;

$Cnt_new = db_field($sql,'cnt');
$minGrp=0;
 // здесь определимся какие группы меньше всего
  foreach ($aGroups as $grp => $aPlay)
 {
    $cnGrp =count($aPlay);
    if ($minGrp==0 || $cnGrp<$minGrp) $minGrp=$cnGrp;
 }

 foreach ($aGroups as $grp => $aPlay)
 {
    $cnGrp =count($aPlay);
   // s('$grp='.$grp);
   // s($aResults);
  //  if ($cnGrp==3) $ConTable =  table3($aPlay,$aResults[$grp]);
 //   if ($cnGrp==4) $ConTable =  table4($aPlay,$aResults[$grp]);
 ////   if ($cnGrp==5) $ConTable =  table5($aPlay,$aResults[$grp]);
//    if ($cnGrp==6) $ConTable =  table6($aPlay,$aResults[$grp]);
    //if ($cnGrp>6)
    //    $ConTable =  ;

    $str_add_player='';
    if (!empty($Cnt_new) && $minGrp==$cnGrp)
    {

        $str_add_player = '<span style="width:20px;cursor: pointer;" id="per_v_rozdel" module="etaps" action="add_playergrp" post_string="&etap_id='.$etap_id.'&turnir_id='.$turnir_id.'&grp='.$grp. '"  return_content_bool="" blok="" class="ajax_send" field_result="player_id" field_result_name="name" wintype="1" width_="500"><img width="35px" alt="Додати" title="Додати" src="../../../../img/slug_small/add.png" border="0"></span>';
    }
   // Трансформируем структуру результатов для функции table()
   // table() ожидает структуру [$n][$pl_this_com], где $pl_this_com = $pl_this + $cnt_players
   // но результаты формируются как [$pl_num_grp1][$pl_num_grp2]
   $aResultsTransformed = array();
   if (!empty($aResults[$grp])) {
       foreach ($aResults[$grp] as $pl1 => $aPl1Results) {
           if (!empty($aPl1Results)) {
               foreach ($aPl1Results as $pl2 => $aPl2Result) {
                   // Преобразуем ключи: $pl2 -> $pl2 + $cnt_players (это будет $pl_this_com)
                   $pl_this_com = $pl2 + $cnGrp;
                   $aResultsTransformed[$pl1][$pl_this_com] = $aPl2Result;
               }
           }
       }
   }
   
   $Tables_content .=' <div class="row">
  <div class="col">
   '.table($aPlay,$aResultsTransformed,'  <div class="zagolovokGrp"> '.$group_label.' '.$grp.'</div>'.$str_add_player, $turnir_id, ($group_label === 'Ліга' ? ' league-results-table' : '') ) .'

  </div>
  </div>
  ';
 }
 $Tables_content .='
  </div>
  </div>
  
</div>

  
  ';
  
  // Проверяем, есть ли командные игры (по наличию match_id или onclick атрибутов в itog)
  $has_team_games = false;
  $match_id_found = false;
  $onclick_found = false;
  foreach ($aResults as $grp => $aGrpResults) {
      if (!empty($aGrpResults)) {
          foreach ($aGrpResults as $pl1 => $aPl1Results) {
              if (!empty($aPl1Results)) {
                  foreach ($aPl1Results as $pl2 => $aPl2Result) {
                      // Проверяем наличие match_id
                      if (!empty($aPl2Result['match_id']) && $aPl2Result['match_id'] != '0') {
                          $has_team_games = true;
                          $match_id_found = true;
                          break 3;
                      }
                      // Также проверяем наличие onclick в itog (для командных игр)
                      if (!empty($aPl2Result['itog']) && strpos($aPl2Result['itog'], 'onclick="showTeamMatchDetails') !== false) {
                          $has_team_games = true;
                          $onclick_found = true;
                          break 3;
                      }
                  }
              }
          }
      }
  }
  
  if (!$has_team_games && !empty($aResults)) {
      // Пробуем найти любые данные в результатах
      $sample_keys = array_keys($aResults);
  }
  
  // Если есть командные игры, добавляем JavaScript для модального окна
  if ($has_team_games || is_team_league_turnir($turnir_id)) {
      $js_code = '
if (typeof window.showTeamRoster !== "function") {
window.showTeamRoster = function(element, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    var teamId = element.getAttribute("data-team-id");
    var turnirId = element.getAttribute("data-turnir-id") || '.$turnir_id.';

    if (!teamId) {
        alert("Помилка: не вдалося визначити команду");
        return;
    }

    if (typeof window.__teamRosterRequestId === "undefined") {
        window.__teamRosterRequestId = 0;
    }
    var requestId = ++window.__teamRosterRequestId;

    var formData = new FormData();
    formData.append("ajax_method", "1");
    formData.append("module", "etapresult");
    formData.append("action", "team_roster");
    formData.append("team_id", teamId);
    formData.append("turnir_id", turnirId);
    formData.append("return_content_bool", "1");

    fetch("", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.text();
    })
    .then(text => {
        if (requestId !== window.__teamRosterRequestId) {
            return;
        }
        try {
            var data = JSON.parse(text);
            var rosterData = data;
            if (data.content && typeof data.content === "string") {
                rosterData = JSON.parse(data.content);
            }

            if (rosterData.error || !rosterData.success) {
                alert("Помилка: " + (rosterData.error || "не вдалося отримати склад команди"));
                return;
            }

            window.showTeamRosterModal(rosterData);
        } catch (e) {
            alert("Помилка при обробці відповіді сервера: " + e.message);
        }
    })
    .catch(function() {
        alert("Помилка при завантаженні складу команди");
    });
};

window.showTeamRosterModal = function(data) {
    var playersHtml = "";
    if (data.players && data.players.length > 0) {
        data.players.forEach(function(player, index) {
            playersHtml += "<tr>" +
                "<td class=\"text-center\" style=\"width:60px;\">" + (index + 1) + "</td>" +
                "<td>" + player.name + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting || "") + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting_ukraine || "") + "</td>" +
                "</tr>";
        });
    } else {
        playersHtml = "<tr><td colspan=\"4\" class=\"text-center text-muted\" style=\"padding:18px;\">У команди поки немає активних гравців</td></tr>";
    }

    var modalElement = document.getElementById("teamRosterModal");
    if (!modalElement) {
        var modalHtml = "<div class=\"modal fade\" id=\"teamRosterModal\" tabindex=\"-1\" aria-hidden=\"true\">" +
            "<div class=\"modal-dialog modal-dialog-centered\" style=\"max-width:700px;\">" +
            "<div class=\"modal-content\">" +
            "<div class=\"modal-header\">" +
            "<h5 class=\"modal-title team-roster-title\" style=\"flex:1; text-align:center;\"></h5>" +
            "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Закрити\"></button>" +
            "</div>" +
            "<div class=\"modal-body\" style=\"padding:0;\">" +
            "<table class=\"table table-sm mb-0\">" +
            "<thead class=\"table-light\"><tr><th class=\"text-center\" style=\"width:60px;\">№</th><th>Гравець</th><th class=\"text-center\" style=\"width:120px;\">Рейт. клубу</th><th class=\"text-center\" style=\"width:120px;\">Рейт. ФНТУ</th></tr></thead>" +
            "<tbody class=\"team-roster-body\"></tbody>" +
            "</table>" +
            "</div>" +
            "<div class=\"modal-footer\" style=\"justify-content:center;\">" +
            "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">ОК</button>" +
            "</div>" +
            "</div></div></div>";
        document.body.insertAdjacentHTML("beforeend", modalHtml);
        modalElement = document.getElementById("teamRosterModal");
        modalElement.addEventListener("hidden.bs.modal", function() {
            var modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.dispose();
            }
        });
    }

    var titleElement = modalElement.querySelector(".team-roster-title");
    var bodyElement = modalElement.querySelector(".team-roster-body");
    if (titleElement) {
        titleElement.textContent = "Склад команди: " + (data.team_name || "");
    }
    if (bodyElement) {
        bodyElement.innerHTML = playersHtml;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
};
}

// Функция для показа детальных результатов командного матча
function showTeamMatchDetails(element, event) {
    // Предотвращаем всплытие события, чтобы не срабатывали другие обработчики (например, из раздела "столы")
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    var matchId = element.getAttribute("data-match-id");
    var etapId = element.getAttribute("data-etap-id");
    var teamAId = element.getAttribute("data-team-a-id");
    var teamBId = element.getAttribute("data-team-b-id");
    var turnirId = '.$turnir_id.';
    
    if (!matchId && etapId && teamAId && teamBId) {
        var minTeam = Math.min(parseInt(teamAId), parseInt(teamBId));
        var maxTeam = Math.max(parseInt(teamAId), parseInt(teamBId));
        matchId = "match_" + etapId + "_" + minTeam + "_" + maxTeam;
    }
    
    if (!matchId || !etapId) {
        alert("Помилка: не вдалося визначити параметри матчу");
        return;
    }
    
    var loadingModal = document.getElementById("staticBackdrop");
    if (loadingModal) {
        var bsModal = new bootstrap.Modal(loadingModal);
        bsModal.show();
    }
    
    var formData = new FormData();
    formData.append("ajax_method", "1");
    formData.append("module", "etapresult");
    formData.append("action", "match_details");
    formData.append("match_id", matchId);
    formData.append("etap_id", etapId);
    formData.append("turnir_id", turnirId);
    formData.append("team_a_id", teamAId);
    formData.append("team_b_id", teamBId);
    formData.append("return_content_bool", "1");
    
    fetch("", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        return response.text();
    })
    .then(text => {
        
        if (loadingModal) {
            var bsModal = bootstrap.Modal.getInstance(loadingModal);
            if (bsModal) bsModal.hide();
        }
        
        try {
            var data = JSON.parse(text);
            
            // Проверяем, обернут ли ответ в общий AJAX формат (с полем content)
            var matchData = data;
            if (data.content && typeof data.content === "string") {
                // Ответ обернут в общий AJAX формат, нужно распарсить content
                try {
                    matchData = JSON.parse(data.content);
                } catch (e) {
                    alert("Ошибка при обработке данных: не удалось распарсить content");
                    return;
                }
            }
            
            if (matchData.error || !matchData.success) {
                alert("Ошибка: " + (matchData.error || "при получении данных"));
                return;
            }
            showMatchDetailsModal(matchData);
        } catch (e) {
            alert("Ошибка при обработке ответа сервера: " + e.message);
        }
    })
    .catch(error => {
        if (loadingModal) {
            var bsModal = bootstrap.Modal.getInstance(loadingModal);
            if (bsModal) bsModal.hide();
        }
        alert("Ошибка при загрузке данных");
    });
}

function showMatchDetailsModal(data) {
    // Закрываем и удаляем все существующие модальные окна перед открытием нового
    // Закрываем модальное окно из раздела "столы" (staticBackdrop)
    var staticBackdropModal = document.getElementById("staticBackdrop");
    if (staticBackdropModal) {
        var bsModalInstance = bootstrap.Modal.getInstance(staticBackdropModal);
        if (bsModalInstance) {
            bsModalInstance.hide();
        }
        // Удаляем после закрытия
        staticBackdropModal.addEventListener("hidden.bs.modal", function() {
            setTimeout(function() {
                if (staticBackdropModal && staticBackdropModal.parentNode) {
                    staticBackdropModal.remove();
                }
            }, 100);
        }, { once: true });
    }
    
    // Удаляем существующее модальное окно с результатами, если есть
    var existingModal = document.getElementById("matchDetailsModal");
    if (existingModal) {
        var existingBsModal = bootstrap.Modal.getInstance(existingModal);
        if (existingBsModal) {
            existingBsModal.hide();
        }
        setTimeout(function() {
            if (existingModal && existingModal.parentNode) {
                existingModal.remove();
            }
        }, 100);
    }
    
    var gamesHtml = "";
    if (data.player_games && data.player_games.length > 0) {
        data.player_games.forEach(function(game) {
            gamesHtml += "<div style=\"padding: 15px; border-bottom: 1px solid #dee2e6;\">";
            gamesHtml += "<div style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;\">";
            gamesHtml += "<div style=\"flex: 1;\"><div style=\"font-weight: 600;\">" + game.player_1_name + (game.position_1 ? " (" + game.position_1 + ")" : "") + "</div></div>";
            gamesHtml += "<div style=\"margin: 0 20px; font-weight: 700; font-size: 18px;\">VS</div>";
            gamesHtml += "<div style=\"flex: 1; text-align: right;\"><div style=\"font-weight: 600;\">" + game.player_2_name + (game.position_2 ? " (" + game.position_2 + ")" : "") + "</div></div>";
            gamesHtml += "</div>";
            gamesHtml += "<div style=\"text-align: center; font-size: 20px; font-weight: 700; color: #198754; margin: 10px 0;\">" + game.score + "</div>";
            if (game.start_game || game.end_game) {
                gamesHtml += "<div style=\"text-align: center; font-size: 12px; color: #6c757d;\">";
                if (game.start_game) gamesHtml += "Старт: " + game.start_game;
                if (game.end_game) gamesHtml += (game.start_game ? " | " : "") + "Фініш: " + game.end_game;
                gamesHtml += "</div>";
            }
            gamesHtml += "</div>";
        });
    } else {
        gamesHtml = "<div style=\"padding: 20px; text-align: center; color: #6c757d;\">Игры игроков еще не сыграны</div>";
    }
    
    var modalHtml = \'<div class="modal fade" id="matchDetailsModal" tabindex="-1">\' +
        \'<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 700px;">\' +
        \'<div class="modal-content">\' +
        \'<div class="modal-header"><h5 class="modal-title" style="flex: 1; text-align: center;">Игры</h5>\' +
        \'<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>\' +
        \'<div class="modal-body" style="padding: 0;">\' +
        \'<div style="padding: 20px; background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: center;">\' +
        \'<div style="font-size: 18px; font-weight: 600; margin-bottom: 5px;">\' + data.team_game.team_a_name + \' <span style="margin: 0 15px;">VS</span> \' + data.team_game.team_b_name + \'</div>\' +
        \'<div style="font-size: 24px; font-weight: 700; color: #198754;">\' + data.team_game.score + \'</div>\' +
        \'<div style="font-size: 14px; color: #6c757d; margin-top: 5px;">\' + data.team_game.status + \'</div></div>\' +
        gamesHtml + \'</div>\' +
        \'<div class="modal-footer" style="justify-content: center;">\' +
        \'<button type="button" class="btn btn-primary" data-bs-dismiss="modal">ОК</button></div></div></div></div>\';
    
    document.body.insertAdjacentHTML("beforeend", modalHtml);
    var modalElement = document.getElementById("matchDetailsModal");
    var modal = new bootstrap.Modal(modalElement);
    modalElement.addEventListener("hidden.bs.modal", function() {
        setTimeout(function() {
            if (modalElement) {
                modal.dispose();
                modalElement.remove();
            }
        }, 300);
    }, { once: true });
    modal.show();
}
';
      // Устанавливаем JavaScript через параметр по ссылке
      if (isset($javascript)) {
          $javascript = $js_code;
      }
  } else {
      // Если нет командных игр, но параметр передан, обнуляем его
      if (isset($javascript)) {
          $javascript = '';
      }
  }
  
  return $Tables_content;
  }
  }
  function mesta_com($aComm1,$aComm2,$command_name1,$command_name2,$etap_id){
      //проверяем все ли введены результаты, если все то рассчет мест делаем и потом заполняем слеующий этап
      $sql = 'SELECT * FROM `bs_reiting` where etap_id='.$etap_id.'  and ((set_1="0" and set_2="0")  )';
      $aResAll = db_list($sql);
      // s($sql);
      $html='';
      // если нет нулевых результатов значит все заполнено
      if (empty($aResAll)) {


          $all_ochki_comm1 = 0;
          $all_ochki_comm2 = 0;
          $all_set1=0;
          $all_set2=0;
          foreach ($aComm1 as $n => $aPl) {
              //   s($aPl);
              $all_ochki_comm1 += $aPl['cnt_win'];
              $all_set1+=$aPl['grp_win_set'];

          }
          foreach ($aComm2 as $n => $aPl) {
              $all_ochki_comm2 += $aPl['cnt_win'];
              $all_set2+=$aPl['grp_win_set'];

          }
          if ($all_ochki_comm1 > $all_ochki_comm2) {
              $txt1 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів';
              $txt2 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів';
          } elseif ($all_ochki_comm1 < $all_ochki_comm2) {

              $txt1 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів';
              $txt2 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів';
          }else{// если равное количесвто очей то определяем по сетам
                if ($all_set1>$all_set2){
                    $txt1 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set1.')';
                    $txt2 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set2.')';
                }else{
                    $txt1 = 'Команда "' . $command_name2 . '" - ' . $all_ochki_comm2 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set2.')';
                    $txt2 = 'Команда "' . $command_name1 . '" - ' . $all_ochki_comm1 . ' балів<br>&nbsp;&nbsp;&nbsp; (виграно сетів '.$all_set1.')';

                }
          }
          $html = '
 <style>
    @import url("../../../../css/2xminuska.css?ver=112154");
    </style>
<div class="column_mesta column_mesta_comm">
        <div class="misca"> Місця:</div>
        <ul><li><img height="32px" src="../../../../img/1mesto6.png"> 
        <div class="mesto1 mesto1comm">
        <span class="ml10"></span>-<span class="ml10">' . $txt1 . '</span></div></li>
        <li><img height="32px&quot;" src="../../../../img/2mesto6.png"> 
        <div class="mesto2 mesto2comm"><span class="ml10"></span>-<span class="ml10">' . $txt2 . '</span></div></li>
   </ul>
        </div>';
      }
     return $html;
  }
function table_comm($aComm1,$aComm2, $aResults,$zagl,$turnir_id,$etap_id)
{
    // s($aComm1);
     //  s($aResults);
    $aPlay=$aComm1;
    $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
    $aTurnir= db_row($sql);
    $is_command = $aTurnir['is_command'];
    $command_name1= $_SESSION['command_name1'] = $aTurnir['command_name1'];
    $command_name2= $_SESSION['command_name2'] = $aTurnir['command_name2'];
    $all_reit2 = 0;
    $all_reit_ligas2 = 0;
    $zagl ='<div class="zagolovokGrp"> Команда "'.$command_name1.'" проти команди "'.$command_name2.'" </div>';
   // s($aComm2);
    $cnt_players =count($aComm1);
    $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
        if ($av!=$aVar['krug'])
        {
            $porKrug=1; $av=$aVar['krug'];
        }
        $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
        $porKrug++;
    }
    $porKrug--;
   // s('$porKrug='.$porKrug);
  //  s($aPorGameTable);
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.mesta_com($aComm1,$aComm2,$command_name1,$command_name2,$etap_id).'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
     <tr>
  <th class="num1 ft14 fw700"></th>
  <th class="text-center ft14 fw700 num2"></th>
  <th class="fio ft14 fw700 text-end">
 №
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {

        $content .= '<th class="num fw700 ft14 text-center"><span >'.$n.'</span></th>';
    }

    $content .= '<th class="num ft14 fw700 text-center"></th>
  <th class="num2 ft14 fw700 text-center"></th>
  
  </tr></thead>
    <tr>
  <th class="num1 ft14 fw700"></th>
  <th class="text-center ft14 fw700 num2 align-content-center">Команди</th>
  <th class="fio ft14 fw700 text-end command2 align-content-center">
        Рейтинг "'.$command_name2. '"
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {
        $all_reit2+=$aPl['beg_reit'];
        $all_reit_ligas2+=$aPl['reiting_ukraine'];
        $content .= '<th class="num_comm fw700 ft14 text-center"><span >'.$aPl['beg_reit'].'<br>'.$aPl['reiting_ukraine'].'</span></th>';
    }

    $content .= '<th class="num ft14 fw700 text-center command2">'.$all_reit2.'<br>'.$all_reit_ligas2.'</th>
  <th class="num2 ft14 fw700 text-center"></th>
  
  </tr>
  <tr>
  <th class="num1 ft14 fw700 "></th>
  <th class="text-center align-bottom ft14 fw700 num2 command1 th_color_rose align-content-center">Рейтинг <br>"'.$command_name1.'"</th>
  <th class="fio ft14 fw700">
  <div class="max_heig">

<div class="up_comman command2">
Команда: "'.$command_name2.'"
</div>
<div class="down_comman command1">
Команда: "'.$command_name1.'"
</div>


</div>
  </th>';
    foreach ($aComm2 as $n => $aPl)
    {
        $aPl['name'] = str_replace(' ','<br>',$aPl['name']);
        $content .= '<th class="num_comm fw700 ft14 text-center align-bottom"><span class="rotate-sm-90-main command2">'.$aPl['name'].'</span></th>';
    }

    $content .= '<th class="num_comm ft14 fw700 text-center command1 ft14 align-content-center ">Очки <br>"'.$command_name1.'"</th>
  <th class="num2 ft14 fw700 text-center command1 ft14 align-content-center">Віднош.<br> "'.$command_name1.'"</th>
  
  </tr>
  ';
//s('$aPlay');

//s($aPlay);
   // $cnt_peop=count($aComm2);
   // $max_ochki=$cnt_peop*2;
  //  s('$cnt_peop='.$cnt_peop);
    $all_ochki_comm1= 0;
    $all_set1 = 0;
    $all_set_win1 = 0;
    $all_set_win2 = 0;
    $all_set_lose1 = 0;
    $all_set_lose2 = 0;
    $all_set2 = 0;
    $all_ochki_comm2= 0;
    $all_reit1 = 0;
    $all_reit2 = 0;
    $all_reit_ligas1 = 0;
    $all_reit_ligas2 = 0;
    foreach ($aPlay as $n => $aPl)
    {
        $aPlay[$n]['grp_ochki'] = isset($aPlay[$n]['grp_ochki']) ? (int)$aPlay[$n]['grp_ochki'] : 0;
        $aPlay[$n]['grp_win_set'] = isset($aPlay[$n]['grp_win_set']) ? (int)$aPlay[$n]['grp_win_set'] : 0;
        $aPlay[$n]['grp_lose_set'] = isset($aPlay[$n]['grp_lose_set']) ? (int)$aPlay[$n]['grp_lose_set'] : 0;
        $aPlay[$n]['beg_reit'] = isset($aPlay[$n]['beg_reit']) && $aPlay[$n]['beg_reit'] !== '' ? (float)$aPlay[$n]['beg_reit'] : 0;
        $aPlay[$n]['reiting_ukraine'] = isset($aPlay[$n]['reiting_ukraine']) && $aPlay[$n]['reiting_ukraine'] !== '' ? (float)$aPlay[$n]['reiting_ukraine'] : 0;
        $all_ochki_comm1 += $aPlay[$n]['grp_ochki'];
        $all_set1 += $aPlay[$n]['grp_win_set'] - $aPlay[$n]['grp_lose_set'];
        $all_set_win1 += $aPlay[$n]['grp_win_set'];
        $all_set_lose1 += $aPlay[$n]['grp_lose_set'];
        $all_reit1 += $aPlay[$n]['beg_reit'];
        $all_reit_ligas1 += $aPlay[$n]['reiting_ukraine'];
        $team_name_html = render_team_name_link($aPlay[$n]['name'], !empty($aPlay[$n]['player_id']) ? $aPlay[$n]['player_id'] : 0, $turnir_id);
        $content .= '<tr>
      <td class="text-center ft14 align-middle">'.$aPlay[$n]['grp_num'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['beg_reit'].'<br />'.$aPlay[$n]['reiting_ukraine'].'</td>
      <td class="align-middle ft14 fio command1">'.$team_name_html.'</td>';
        // ВАЖНО: Получаем player_id текущей команды (строки) из $aPlay по индексу $n
        // Индекс $n теперь соответствует grp_mesto (новое место), а не grp_num (старая позиция)
        $current_team_id = !empty($aPlay[$n]['player_id']) ? (int)$aPlay[$n]['player_id'] : 0;
        
        foreach ($aComm2 as $col_key => $aPl2) {
            // ВАЖНО: Получаем player_id команды-оппонента (столбца) из $aComm2 по текущему ключу
            // Ключ соответствует grp_mesto (новое место) или grp_num (старая позиция)
            $opponent_team_id = !empty($aPl2['player_id']) ? (int)$aPl2['player_id'] : 0;

            $cell = (!empty($aResults[$n]) && !empty($aResults[$n][$col_key])) ? $aResults[$n][$col_key] : array();

            if (!empty($current_team_id) && !empty($opponent_team_id)) {
                $cell_team_a = !empty($cell['team_a_id']) ? (int)$cell['team_a_id'] : 0;
                $cell_team_b = !empty($cell['team_b_id']) ? (int)$cell['team_b_id'] : 0;

                if (empty($cell) || $cell_team_a !== $current_team_id || $cell_team_b !== $opponent_team_id) {
                    $reverse = (!empty($aResults[$col_key]) && !empty($aResults[$col_key][$n])) ? $aResults[$col_key][$n] : array();
                    $rev_team_a = !empty($reverse['team_a_id']) ? (int)$reverse['team_a_id'] : 0;
                    $rev_team_b = !empty($reverse['team_b_id']) ? (int)$reverse['team_b_id'] : 0;

                    if (!empty($reverse) && $rev_team_a === $current_team_id && $rev_team_b === $opponent_team_id) {
                        $cell = $reverse;
                    }
                }
            }

            $itog_html = !empty($cell['itog']) ? $cell['itog'] : '';
            $active_class = !empty($cell['active']) ? $cell['active'] : '';

            // ВАЖНО: Переопределяем data-team-a-id и data-team-b-id на основе текущего порядка команд в таблице
            // Это нужно для правильного отображения модального окна после сортировки по местам
            if (!empty($itog_html) && !empty($current_team_id) && !empty($opponent_team_id)) {
                // Заменяем старые data-team-a-id и data-team-b-id на новые, соответствующие текущему порядку в таблице
                $itog_html = preg_replace(
                    '/data-team-a-id="[^"]*"/',
                    'data-team-a-id="'.$current_team_id.'"',
                    $itog_html
                );
                $itog_html = preg_replace(
                    '/data-team-b-id="[^"]*"/',
                    'data-team-b-id="'.$opponent_team_id.'"',
                    $itog_html
                );
            }

            $content .='<td class="text-center ft14 align-middle min_width_td '.$active_class.'">
                <div>'.$itog_html.'</div>
                </td>';
        }
        //if ($cnt_players==$n) $content .='<td class="zach"></td>';
        $content .='
      <td class="text-center ft14 align-middle min_width_td command1 fw700">'.$aPlay[$n]['grp_ochki'].'</td> 
      <td class="text-center ft14 align-middle min_width_td_vid command1">'.$aPlay[$n]['grp_win_set'].'-'.$aPlay[$n]['grp_lose_set'].'</td>
      
      </tr> ';
    }
// итого по второй команде очки
    $content .= '<tr>
        <td  > </td>
        <td  class="text-center command1 ft14"> '.$all_reit1.'<br>'.$all_reit_ligas1.'</td>
            <td   class="text-end command2 ft14 align-middle fw700">Очки "'.$command_name2.'"</td>';
    foreach ($aComm2 as $n => $aPl)
    {
        $aPl['grp_ochki'] = isset($aPl['grp_ochki']) ? (int)$aPl['grp_ochki'] : 0;
        $aPl['grp_win_set'] = isset($aPl['grp_win_set']) ? (int)$aPl['grp_win_set'] : 0;
        $aPl['grp_lose_set'] = isset($aPl['grp_lose_set']) ? (int)$aPl['grp_lose_set'] : 0;
        $all_ochki_comm2+=$aPl['grp_ochki'];

//$aPl['beg_reit'].'<br>'.$aPl['reiting_ukraine']
        $content .= '<td class="text-center ft14 align-middle min_width_td command2 fw700"><span >'.$aPl['grp_ochki'].'</span></td>';
    }
    $content .='
            <td class="text-center ft14 align-middle min_width_td_vid t-grid-team_table_res"><div></div><div class="left_ochko">'.$all_ochki_comm1.'</div><div class="right_ochko">'.$all_ochki_comm2.'</div></td>
            <td class="text-center ft14 align-middle min_width_td_vid command1">'.$all_set_win1.'-'.$all_set_lose1.'</td>
            
    </tr>';
    // итого по второй команде соотно
    $content .= '<tr>
            
            <td colspan="3" class="text-end command2 ft14">Відношення сетів "'.$command_name2.'"</td>';
    foreach ($aComm2 as $n => $aPl)
    {
        $aPl['grp_win_set'] = isset($aPl['grp_win_set']) ? (int)$aPl['grp_win_set'] : 0;
        $aPl['grp_lose_set'] = isset($aPl['grp_lose_set']) ? (int)$aPl['grp_lose_set'] : 0;
        $all_set2+=$aPl['grp_win_set']-$aPl['grp_lose_set'];
        $all_set_win2+=$aPl['grp_win_set'];
        $all_set_lose2+=$aPl['grp_lose_set'];
        $content .= '<td class="text-center ft14 align-middle min_width_td command2"><span >'.$aPl['grp_win_set'].'-'.$aPl['grp_lose_set'].'</span></td>';
    }
    $allSet = $all_set1>$all_set2 ? $all_set1 : $all_set2;
    $clas = $all_set1>$all_set2 ? 'command1' : 'command2';
    $content .='
            <td class="text-center ft14 align-middle min_width_td_vid command2">'.$all_set_win2.'-'.$all_set_lose2.'</td>
            <td class="text-center ft14 align-middle min_width_td_vid"><div class="'.$clas.'">'.$allSet.'</div></td>
            
    </tr>';
    $content.='</table></div>
  </div></div>
  ';
    
    // JavaScript код теперь формируется в all_tables_comm(), не здесь
    // Возвращаем только HTML контент таблицы
    return $content;
}
function table($aPlay,$aResults,$zagl, $turnir_id = 0, $table_class = '')
 {
  //  s($aPlay);
 //   s($aResults);
   $cnt_players =count($aPlay);
     $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
     if ($av!=$aVar['krug'])
     {
       $porKrug=1; $av=$aVar['krug'];
     }
     $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
     $porKrug++;
    }
    $porKrug--;
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.'
<table class="table bordered2 table-hover table-bordered rounded-pill border-light-subtle'.htmlspecialchars((string)$table_class, ENT_QUOTES, 'UTF-8').'">
  <thead class="th_color_rose">
  <tr>
  <th class="num1 ft14 fw700">№</th>
  <th class="text-center ft14 fw700 num2">Рейтинг</th>
  <th class="fio ft14 fw700">ПІБ</th>';
  foreach ($aPlay as $n => $aPl)    $content .= '<th class="num fw700 ft14 text-center">'.$n.'</th>';
  $content .= '<th class="num ft14 fw700 text-center">Очки</th>
  <th class="num2 ft14 fw700 text-center">Віднош.</th>
  <th class="num2 ft14 fw700 text-center">Місце</th>
  </tr></thead>';
    foreach ($aPlay as $n => $aPl)
    {
     $team_name_html = render_team_name_link($aPl['name'], !empty($aPl['player_id']) ? $aPl['player_id'] : 0, (int)$turnir_id);
     $place_cell_class = !empty($aPl['grp_mesto_is_duplicate']) ? ' duplicate-place' : '';
     $content .= '<tr>
  <td class="text-center ft14 align-middle">'.$n.'</td>
  <td class="text-center ft14 align-middle min_width_td">'.($aPl['beg_reit'] ? $aPl['beg_reit'].'<br />'.$aPl['reiting_ukraine'] : '').'</td>
  <td class="align-middle ft14 fio">'.$team_name_html.'</td>';
    $pl_this=1;
    while($pl_this<=$cnt_players)
        /// foreach ($aResults[$n] as $game => $aGame )
    {
        // Если игрок играет сам с собой (диагональная ячейка), добавляем пустую ячейку с классом "zach"
        if ($pl_this == $n) {
            $content .='<td class="zach"></td>';
        } else {
            $pl_this_com=$pl_this+$cnt_players;
            $active = isset($aResults[$n][$pl_this_com]['active']) ? $aResults[$n][$pl_this_com]['active'] : '';
            $itog = isset($aResults[$n][$pl_this_com]['itog']) ? $aResults[$n][$pl_this_com]['itog'] : '';
            $content .='<td class="text-center ft14 align-middle min_width_td '.$active.'">
            <div>'.$itog.'</div>
            </td>';
        }
        $pl_this++;
    }
    //if ($cnt_players==$n) $content .='<td class="zach"></td>';
     $content .='
  <td class="text-center ft14 align-middle min_width_td fw700">'.$aPl['grp_ochki'].'</td> 
  <td class="text-center ft14 align-middle min_width_td_vid">'.$aPl['grp_win_set'].'-'.$aPl['grp_lose_set'].'</td>
  <td class="text-center ft14 align-middle min_width_td'.$place_cell_class.'">'.$aPl['grp_mesto'].'</td>
   
  </tr> ';
    }
    $content.='</table></div>
  </div></div>
  ';
     return $content;
}
function table3($aPlay,$aResults)
 {
  $cnt_players =count($aPlay);
     $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
     if ($av!=$aVar['krug'])
     {
       $porKrug=1; $av=$aVar['krug'];
     }
     $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
     $porKrug++;
    }
    $porKrug--;
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
  <tr>
  <th class="num1 ft14 fw700">№</th>
  <th class="text-center ft14 fw700 num2">Рейтинг</th>
  <th class="fio ft14 fw700">ПІБ</th>';
  foreach ($aPlay as $n => $aPl)    $content .= '<th class="num fw700 ft14 text-center">'.$n.'</th>';
  $content .= '<th class="num ft14 fw700 text-center">Очки</th>
  <th class="num2 ft14 fw700 text-center">Віднош.</th>
  <th class="num2 ft14 fw700 text-center">Місце</th>
  </tr></thead>';
    foreach ($aPlay as $n => $aPl)
    {
     $content .= '<tr>
  <td class="text-center ft14 align-middle">'.$n.'</td>
  <td class="text-center ft14 align-middle min_width_td">'.($aPl['beg_reit'] ? $aPl['beg_reit'].'<br />'.$aPl['reiting_ukraine'] : '').'</td>
  <td class="align-middle ft14 fio">'.$aPl['name'].'</td>';
    $pl_this=1;
    while($pl_this<=$cnt_players)
        /// foreach ($aResults[$n] as $game => $aGame )
    {
        // Если игрок играет сам с собой (диагональная ячейка), добавляем пустую ячейку с классом "zach"
        if ($pl_this == $n) {
            $content .='<td class="zach"></td>';
        } else {
            $pl_this_com=$pl_this+$cnt_players;
            $active = isset($aResults[$n][$pl_this_com]['active']) ? $aResults[$n][$pl_this_com]['active'] : '';
            $itog = isset($aResults[$n][$pl_this_com]['itog']) ? $aResults[$n][$pl_this_com]['itog'] : '';
            $content .='<td class="text-center ft14 align-middle min_width_td '.$active.'">
            <div>'.$itog.'</div>
            </td>';
        }
        $pl_this++;
    }
    //if ($cnt_players==$n) $content .='<td class="zach"></td>';
    $content .='
  <td class="text-center ft14 align-middle min_width_td fw700">'.$aPl['grp_ochki'].'</td> 
  <td class="text-center ft14 align-middle min_width_td_vid">'.$aPl['grp_win_set'].'-'.$aPl['grp_lose_set'].'</td>
  <td class="text-center ft14 align-middle min_width_td">'.$aPl['grp_mesto'].'</td>
  
  </tr> ';
    }
  $content.='</table></div>
  </div></div>
  ';
     if (!$_SESSION['is_mobile']  && $_SESSION['gt']['user_rule']==100) {
         $content .= '
  3 учасника
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  </tr>
  <tr>  <td rowspan="2">Учасн.</td>  <td>2-3</td>  <td>1-3</td>  <td>1-2</td>    </tr>
  </table>
 ';  
 }
     return $content;
 }   
  function table4($aPlay,$aResults)
 {
   $cnt_players =count($aPlay);
     $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
     if ($av!=$aVar['krug'])
     {
       $porKrug=1; $av=$aVar['krug'];
     }
     $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
     $porKrug++;
    }
    $porKrug--;
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
  <tr>
  <th class="num1 ft14 fw700">№</th>
  <th class="text-center ft14 fw700 num2">Рейтинг</th>
  <th class="fio ft14 fw700">ПІБ</th>';
  foreach ($aPlay as $n => $aPl)    $content .= '<th class="num fw700 ft14 text-center">'.$n.'</th>';
  $content .= '<th class="num ft14 fw700 text-center">Очки</th>
  <th class="num2 ft14 fw700 text-center">Віднош.</th>
  <th class="num2 ft14 fw700 text-center">Місце</th>
  </tr></thead>';

  foreach ($aPlay as $n => $aPl)
  {
      $content .= '<tr>
      <td class="text-center ft14 align-middle">'.$aPlay[$n]['grp_num'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['beg_reit'].'<br />'.$aPlay[$n]['reiting_ukraine'].'</td>
      <td class="align-middle ft14 fio">'.$aPlay[$n]['name'].'</td>';
      $pl_this=1;
      while($pl_this<=$cnt_players)
     /// foreach ($aResults[$n] as $game => $aGame )
      {
         if ($pl_this == $n)
            $content .='<td class="zach"></td>';
         else
            $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$n][$pl_this]['active'].'">'.$aResults[$n][$pl_this]['itog'].'</td>';
         $pl_this++;
      }
      //if ($cnt_players==$n) $content .='<td class="zach"></td>';
       $content .='
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_ochki'].'</td> 
      <td class="text-center ft14 align-middle min_width_td_vid">'.$aPlay[$n]['grp_win_set'].'-'.$aPlay[$n]['grp_lose_set'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_mesto'].'</td>
      </tr> ';
  }
  $content.='</table></div>
  </div></div>
  ';
     if (!$_SESSION['is_mobile']  && $_SESSION['gt']['user_rule']==100) {
         $content .= '
   <div class="col">
  ' . $cnt_players . ' учасники
  <table class="mini_table">
  <tr>  <th>Коло</th>';
         if ($cnt_players % 2 == 0) $cnt_players--;
         $cnt_kol=ceil($cnt_players/2);
         for($kol=1; $kol<=$cnt_kol; $kol++)
         {
            $content .= '<th>'.$kol.'</th>';
         }
         $content .= '</tr>';
         $krug=0;
         foreach ($aPorGameTable as $num_por => $aPor)
         {
             if ($num_por==1) $krug++;
             $content .= '<tr>';
             if ($num_por==1) $content .= '<td rowspan="2">'.$krug.'</td>';
             $content .= '<td>';
             foreach ($aPor as $k => $por)
             {
                 $content .= $por['num'].' - '.$por['num2'].'<br>';
             }
             $content .= '</td>';
             foreach ($aPorGameTable as $num_por2 => $aPor2)
             {
                 if (($num_por2==1) && ($num_por==1)) $content .= '<td rowspan="2"></td>';
                 if (($num_por2>1) && ($num_por==1))
                 {
                     $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$aPorGameTable[1][$krug]['num']][$aPorGameTable[$num_por2][$krug]['num']]['active'].'">'.$aResults[$aPorGameTable[1][$krug]['num']][$aPorGameTable[$num_por2][$krug]['num']]['itog'].'</td>';
                 }
                 if (($num_por2==1) && ($num_por>1))
                 {
                     $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$aPorGameTable[$num_por][$krug]['num']][$aPorGameTable[1][$krug]['num']]['active'].'">'.$aResults[$aPorGameTable[$num_por][$krug]['num']][$aPorGameTable[1][$krug]['num']]['itog'].'</td>';
                 }
                 if (($num_por2>1) && ($num_por>1))
                 {
                     if ($aPorGameTable[$num_por][$krug]['num'] == $aPorGameTable[$num_por2][$krug]['num'])
                         $content .='<td class="zach"></td>';
                     else
                         $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$aPorGameTable[$num_por][$krug]['num']][$aPorGameTable[$num_por2][$krug]['num']]['active'].'">'.$aResults[$aPorGameTable[$num_por][$krug]['num']][$aPorGameTable[$num_por2][$krug]['num']]['itog'].'</td>';
                 }
             }
             $content .= '</tr>';
         }
         $content .= '</table>';
     }
     return $content;
 } 
  
  function table5($aPlay,$aResults)
 {
  //  s($aPlay);
 //   s($aResults);
   $cnt_players =count($aPlay);
     $sql = 'SELECT * FROM `'.T_GROUP_PORYADOK.'` p where p.players='.$cnt_players.' order by krug,num';
    $aVarGrp_= db_list($sql);
    $aPorGameTable = array();

    $av=0;
    foreach ($aVarGrp_ as $aVar)
    {
     if ($av!=$aVar['krug'])
     {
       $porKrug=1; $av=$aVar['krug'];
     }
     $aPorGameTable[$porKrug][$aVar['krug']] = $aVar;
     $porKrug++;
    }
    $porKrug--;
    $content = '
<div class="big-table">  
<div class="big-table_left">  
<div class="obertka_table">
'.$zagl.'
<table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <thead class="th_color_rose">
  <tr>
  <th class="num1 ft14 fw700">№</th>
  <th class="text-center ft14 fw700 num2">Рейтинг</th>
  <th class="fio ft14 fw700">ПІБ</th>';
  foreach ($aPlay as $n => $aPl)    $content .= '<th class="num fw700 ft14 text-center">'.$n.'</th>';
  $content .= '<th class="num ft14 fw700 text-center">Очки</th>
  <th class="num2 ft14 fw700 text-center">Віднош.</th>
  <th class="num2 ft14 fw700 text-center">Місце</th>
  </tr></thead>';

  foreach ($aPlay as $n => $aPl)
  {
      $content .= '<tr>
      <td class="text-center ft14 align-middle">'.$aPlay[$n]['grp_num'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['beg_reit'].'<br />'.$aPlay[$n]['reiting_ukraine'].'</td>
      <td class="align-middle ft14 fio">'.$aPlay[$n]['name'].'</td>';
      $pl_this=1;
      while($pl_this<=$cnt_players)
     /// foreach ($aResults[$n] as $game => $aGame )
      {
         if ($pl_this == $n)
            $content .='<td class="zach"></td>';
         else
            $content .='<td class="text-center ft14 align-middle min_width_td '.$aResults[$n][$pl_this]['active'].'">'.$aResults[$n][$pl_this]['itog'].'</td>';
         $pl_this++;
      }
      //if ($cnt_players==$n) $content .='<td class="zach"></td>';
       $content .='
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_ochki'].'</td> 
      <td class="text-center ft14 align-middle min_width_td_vid">'.$aPlay[$n]['grp_win_set'].'-'.$aPlay[$n]['grp_lose_set'].'</td>
      <td class="text-center ft14 align-middle min_width_td">'.$aPlay[$n]['grp_mesto'].'</td>
      </tr> ';
  }
  $content.='</table></div>
  </div></div>
  ';
     if (!$_SESSION['is_mobile']  && $_SESSION['gt']['user_rule']==100) {
         $content .= '
   <div class="col">
  ' . $cnt_players . ' учасники
  <table class="table  bordered2 table-hover table-bordered  rounded-pill  border-light-subtle">
  <tr>  
<thead class="th_color_rose">
  <th>Коло</th>';
         if ($cnt_players % 2 == 0) $cnt_players--;
         for ($i = 1; $i <= $cnt_players; $i++)
             $content .= '<th>' . $i . '</th>';
         $content .= '</thead></tr>';
         $fir = 1;
         foreach ($aPorGameTable as $row => $aGames) {
             $content .= '  <tr>';
             if ($fir == 1) {
                 $fir = 0;
                 $content .= '<td rowspan="' . $porKrug . '">Участ</td>';
             }
             foreach ($aGames as $aGame) {
                 $content .= '<td>' . $aGame['play1'] . '-' . $aGame['play2'] . '</td> ';
             }
             $content .= '</tr>';
         }
         $content .= '
  </table>
  </div>
  </div>
 ';
     }
 return $content;
}
  
  function table6($aPlay,$aResults)
 { 
     $content = '  <table class="group_table">
  <tr>
  <th>№</th><th>Рей-<br />тинг</th><th class="fio">ПІБ</th>  <th class="num">1</th>  <th class="num">2</th>  <th class="num">3</th>  <th class="num"> 4</th>
  <th class="num">5</th>  <th class="num">6</th>  <th class="num">Очки</th>  <th class="num">Спів.</th>  <th class="num">Місце</th>
  </tr>        
  <tr><td>1</td><td >'.$aPlay[1]['beg_reit'].'<br />'.$aPlay[1]['reiting_ukraine'].'</td><td >'.$aPlay[1]['name'].'</td><td class="zach"></td><td >'.$aResults[1][2]['itog'].'</td><td >'.$aResults[1][3]['itog'].'</td><td >'.$aResults[1][4]['itog'].'</td><td >'.$aResults[1][5]['itog'].'</td> <td >'.$aResults[1][6]['itog'].'</td><td >'.$aPlay[1]['grp_ochki'].'</td> <td >'.$aPlay[1]['grp_win_set'].'-'.$aPlay[1]['grp_lose_set'].'</td><td >'.$aPlay[1]['grp_mesto'].'</td></tr> 
  <tr><td>2</td><td >'.$aPlay[2]['beg_reit'].'<br />'.$aPlay[2]['reiting_ukraine'].'</td><td >'.$aPlay[2]['name'].'</td><td >'.$aResults[2][1]['itog'].'</td><td class="zach"></td><td >'.$aResults[2][3]['itog'].'</td><td >'.$aResults[2][4]['itog'].'</td><td >'.$aResults[2][5]['itog'].'</td><td >'.$aResults[2][6]['itog'].'</td><td >'.$aPlay[2]['grp_ochki'].'</td> <td >'.$aPlay[2]['grp_win_set'].'-'.$aPlay[2]['grp_lose_set'].'</td><td >'.$aPlay[2]['grp_mesto'].'</td></tr> 
  <tr><td>3</td><td >'.$aPlay[3]['beg_reit'].'<br />'.$aPlay[3]['reiting_ukraine'].'</td><td >'.$aPlay[3]['name'].'</td><td >'.$aResults[3][1]['itog'].'</td><td >'.$aResults[3][2]['itog'].'</td><td class="zach"></td><td >'.$aResults[3][4]['itog'].'</td><td >'.$aResults[3][5]['itog'].'</td><td >'.$aResults[3][6]['itog'].'</td><td >'.$aPlay[3]['grp_ochki'].'</td> <td >'.$aPlay[3]['grp_win_set'].'-'.$aPlay[3]['grp_lose_set'].'</td><td >'.$aPlay[3]['grp_mesto'].'</td></tr> 
  <tr><td>4</td><td >'.$aPlay[4]['beg_reit'].'<br />'.$aPlay[4]['reiting_ukraine'].'</td><td >'.$aPlay[4]['name'].'</td><td >'.$aResults[4][1]['itog'].'</td><td >'.$aResults[4][2]['itog'].'</td><td >'.$aResults[4][3]['itog'].'</td><td class="zach"></td><td >'.$aResults[4][5]['itog'].'</td><td >'.$aResults[4][6]['itog'].'</td><td >'.$aPlay[4]['grp_ochki'].'</td> <td >'.$aPlay[4]['grp_win_set'].'-'.$aPlay[4]['grp_lose_set'].'</td><td >'.$aPlay[4]['grp_mesto'].'</td></tr> 
  <tr><td>5</td><td >'.$aPlay[5]['beg_reit'].'<br />'.$aPlay[5]['reiting_ukraine'].'</td><td >'.$aPlay[5]['name'].'</td><td >'.$aResults[5][1]['itog'].'</td><td >'.$aResults[5][2]['itog'].'</td><td >'.$aResults[5][3]['itog'].'</td><td >'.$aResults[5][4]['itog'].'</td><td class="zach"></td><td >'.$aResults[5][6]['itog'].'</td><td >'.$aPlay[5]['grp_ochki'].'</td> <td >'.$aPlay[5]['grp_win_set'].'-'.$aPlay[5]['grp_lose_set'].'</td><td >'.$aPlay[5]['grp_mesto'].'</td></tr> 
  <tr><td>6</td><td >'.$aPlay[6]['beg_reit'].'<br />'.$aPlay[6]['reiting_ukraine'].'</td><td >'.$aPlay[6]['name'].'</td><td >'.$aResults[6][1]['itog'].'</td><td >'.$aResults[6][2]['itog'].'</td><td >'.$aResults[6][3]['itog'].'</td><td >'.$aResults[6][4]['itog'].'</td><td >'.$aResults[6][5]['itog'].'</td><td class="zach"></td><td >'.$aPlay[6]['grp_ochki'].'</td> <td >'.$aPlay[6]['grp_win_set'].'-'.$aPlay[6]['grp_lose_set'].'</td><td >'.$aPlay[6]['grp_mesto'].'</td></tr> 
 </table>
  </td>';
     if ($_SESSION['gt']['user_rule']<10)
         $content .='<td>
  5 учасників
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  <th>IV</th><th>V</th>  </tr>
  <tr>  <td rowspan="2">Учасн</td>  <td>2-4</td>  <td>1-4</td>  <td>1-3</td>  <td>2-3</td> <td>1-2</td>  </tr>
   <tr>    <td>1-5</td>  <td>3-5</td>  <td>2-5</td>  <td>4-5</td>  <td>3-4</td> </tr>
  </table>
  6 участників
  <table class="mini_table">
  <tr>  <th>Коло</th>  <th>I</th>  <th>II</th>  <th>III</th>  <th>IV</th>  <th>V</th>  </tr>
  <tr>  <td rowspan="3">Учасн</td>  <td>2-4</td>  <td>1-4</td>  <td>1-3</td>  <td>2-3</td>  <td>1-2</td>  </tr>
   <tr>    <td>1-5</td>  <td>2-6</td>  <td>2-5</td>  <td>1-6</td>  <td>3-4</td>  </tr> 
   <tr>  <td>3-6</td>  <td>3-5</td>  <td>4-6</td>  <td>4-5</td>  <td>5-6</td>  </tr> 
  </table>
';
return $content;
}  

?>
