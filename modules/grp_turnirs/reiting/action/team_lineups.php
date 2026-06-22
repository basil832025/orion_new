<?php
require_once __DIR__ . '/../../../teamplayers/func/func.teamplayers.php';
// Действие для управления составами команд для конкретного матча (командная лига)
// Работает с конкретной записью из bs_reiting

class team_lineupsAction extends ActionModule {
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';
    
    function init() {
        // Сразу очищаем subMenu2, чтобы панель фильтров не выводилась
        $this->subMenu2 = array();
        
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login']))) {
            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        
        // Проверяем, сохраняем ли состав или пары
        // Проверяем как через poste(), так и напрямую через $_POST для надежности
        $save_lineup = poste('save_lineup');
        if (empty($save_lineup) && !empty($_POST['save_lineup'])) {
            $save_lineup = $_POST['save_lineup'];
        }
        
        $save_pairs = poste('save_pairs');
        if (empty($save_pairs) && !empty($_POST['save_pairs'])) {
            $save_pairs = $_POST['save_pairs'];
        }
        
        if (!empty($save_lineup)) {
            $this->saveLineup();
            return;
        }
        
        if (!empty($save_pairs)) {
            $this->savePairs();
            return;
        }
        
        // Отображаем интерфейс
        $this->show();
    }
    
    function saveLineup() {
        // Получаем параметры из POST, проверяя оба способа
        $game_id = poste('id');
        if (empty($game_id) && !empty($_POST['id'])) {
            $game_id = intval($_POST['id']);
        }
        
        $team_id = poste('team_id');
        if (empty($team_id) && !empty($_POST['team_id'])) {
            $team_id = intval($_POST['team_id']);
        }
        
        if (empty($game_id) || empty($team_id)) {
            window_mess('Помилка: не вказано гру або команду (game_id='.$game_id.', team_id='.$team_id.')');
            $this->show();
            return;
        }
        
        // Получаем информацию об игре
        $game = db_row('SELECT * FROM `'.T_REITING.'` WHERE id='.$game_id);
        if (empty($game)) {
            window_mess('Помилка: гру не знайдено');
            $this->show();
            return;
        }
        
        // Формируем match_id если его нет
        $match_id = $this->getMatchId($game);
        
        // Проверяем блокировку (через etap)
        // Проверяем, заблокирован ли состав для этого конкретного матча
        // Состав блокируется, если уже введен хотя бы один результат для игр этого матча
        $match_id = $this->getMatchId($game);
        $has_results = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.$game['etap_id'].'
            AND pair_number > 0
            AND ((set_1 > 0 OR set_2 > 0 OR set_1 = "W" OR set_2 = "W")
              AND NOT (set_1 = "0" AND set_2 = "0"))', 'cnt');
        $lineups_locked = !empty($has_results) && (int)$has_results > 0 ? 1 : 0;
        
        if (!empty($lineups_locked)) {
            window_mess('Помилка: склади заблоковані після введення першого результату');
            $this->show();
            return;
        }
        
        // Получаем текущие составы для этого матча (из bs_reiting.match_id или формируем структуру)
        $team_lineups = $this->getMatchLineups($match_id, $game);
        
        // Получаем игроков из POST как массив
        $players = array();
        $players_post = poste('players');
        // Если не получили через poste(), проверяем $_POST напрямую
        if (empty($players_post) && !empty($_POST['players'])) {
            $players_post = $_POST['players'];
        }
        
        if (is_array($players_post)) {
            $players = array_map('intval', $players_post);
            // Фильтруем пустые значения
            $players = array_filter($players, function($val) {
                return !empty($val) && $val > 0;
            });
            $players = array_values($players); // Переиндексируем массив
        } elseif (!empty($players_post)) {
            $players_decoded = json_decode($players_post, true);
            if (is_array($players_decoded)) {
                $players = array_map('intval', $players_decoded);
                $players = array_filter($players, function($val) {
                    return !empty($val) && $val > 0;
                });
                $players = array_values($players);
            }
        }
        
        // Определяем позиции для команды
        // Для команды A: A, B, C, D, E
        // Для команды B: Y, X, Z, W, V
        $team_a_labels = array('A', 'B', 'C', 'D', 'E');
        $team_b_labels = array('Y', 'X', 'Z', 'W', 'V');
        
        // Получаем текущие составы для определения команд A и B
        $current_lineups = $this->getMatchLineups($match_id, $game);
        $team_a_id = null;
        $team_b_id = null;
        
        if (!empty($current_lineups)) {
            $team_ids = array_keys($current_lineups);
            if (count($team_ids) >= 2) {
                // Если уже есть две команды, определяем A и B по минимальному/максимальному ID
                $team_a_id = min($team_ids);
                $team_b_id = max($team_ids);
            } elseif (count($team_ids) == 1 && $team_ids[0] != $team_id) {
                // Если есть другая команда, определяем A и B по ID
                $existing_team_id = $team_ids[0];
                if ($team_id < $existing_team_id) {
                    $team_a_id = $team_id;
                    $team_b_id = $existing_team_id;
                } else {
                    $team_a_id = $existing_team_id;
                    $team_b_id = $team_id;
                }
            }
        }
        
        // Если команды все еще не определены, определяем по game.pl_id_1 и pl_id_2
        if (empty($team_a_id) || empty($team_b_id)) {
            $game_info = db_row('SELECT pl_id_1, pl_id_2 FROM `'.T_REITING.'` WHERE id='.$game_id);
            
            if (!empty($game_info['pl_id_1'])) {
                $player1 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.$game_info['pl_id_1']);
                if (!empty($player1['is_team']) && $player1['is_team'] == 1) {
                    $team_a_id = $player1['id'];
                }
            }
            if (!empty($game_info['pl_id_2'])) {
                $player2 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.$game_info['pl_id_2']);
                if (!empty($player2['is_team']) && $player2['is_team'] == 1) {
                    if (empty($team_a_id)) {
                        $team_a_id = $player2['id'];
                    } else {
                        $team_b_id = $player2['id'];
                    }
                }
            }
        }
        
        // Если команды все еще не определены, используем текущий team_id как команду A
        if (empty($team_a_id)) {
            $team_a_id = $team_id;
        }
        if (empty($team_b_id) && $team_id != $team_a_id) {
            // Если сохраняем другую команду, это команда B
            $team_b_id = $team_id;
        }
        
        // Собираем все уникальные ID команд для определения A и B
        $all_team_ids = array_unique(array_filter([$team_a_id, $team_b_id, $team_id]));
        if (count($all_team_ids) >= 2) {
            // Команда с меньшим ID - это A, с большим - B
            $sorted_ids = array_values($all_team_ids);
            sort($sorted_ids);
            $team_a_id = $sorted_ids[0];
            $team_b_id = $sorted_ids[1];
        } elseif (count($all_team_ids) == 1) {
            // Пока только одна команда - это команда A
            $team_a_id = $all_team_ids[0];
        }
        
        // Определяем метки позиций: команда с меньшим ID - A, с большим - B
        $is_team_a = (!empty($team_b_id) && $team_id == min([$team_a_id, $team_b_id])) || (empty($team_b_id) && $team_id == $team_a_id);
        $labels = $is_team_a ? $team_a_labels : $team_b_labels;
        
        // Удаляем старый состав этой команды для этого матча
        db_query('DELETE FROM `bs_team_lineups` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id'].' AND team_id='.(int)$team_id);
        
        // Сохраняем новый состав в таблицу bs_team_lineups
        $saved_count = 0;
        $now = date('Y-m-d H:i:s');
        $match_id_escaped = addslashes($match_id);
        $etap_id = (int)$game['etap_id'];
        $team_id_int = (int)$team_id;
        
        foreach ($players as $index => $player_id) {
            if (empty($player_id) || $player_id <= 0) {
                continue;
            }
            
            $position = !empty($labels[$index]) ? $labels[$index] : chr(65 + $index); // Fallback к A, B, C...
            $position_order = $index;
            $player_id_int = (int)$player_id;
            
            $sql = 'INSERT INTO `bs_team_lineups` 
                    (etap_id, match_id, team_id, position, player_id, position_order, created_at, updated_at)
                    VALUES 
                    ('.$etap_id.', "'.$match_id_escaped.'", '.$team_id_int.', "'.addslashes($position).'", '.$player_id_int.', '.$position_order.', "'.$now.'", "'.$now.'")
                    ON DUPLICATE KEY UPDATE
                    player_id='.$player_id_int.',
                    updated_at="'.$now.'"';
           // s($sql);
            db_query($sql);
            $saved_count++;
        }
        
        // После сохранения показываем интерфейс с обновленными данными
        if ($saved_count > 0) {
            // Фиксируем порядок команд в записи матча, чтобы интерфейс не терял составы
            if (!empty($team_a_id) && !empty($team_b_id)) {
                $pl1_is_team = !empty($game['pl_id_1']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_1'], 'is_team') : 0;
                $pl2_is_team = !empty($game['pl_id_2']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_2'], 'is_team') : 0;
                $pl_update = ($pl1_is_team === 1 && $pl2_is_team === 1) ? ', pl_id_1='.(int)$team_a_id.', pl_id_2='.(int)$team_b_id : '';
                db_query('UPDATE `'.T_REITING.'` SET team_a_id='.(int)$team_a_id.', team_b_id='.(int)$team_b_id.$pl_update.' WHERE id='.(int)$game_id);
            }

            // После сохранения состава проверяем, есть ли составы обеих команд
            // Если да, автоматически создаем все 5 пар, если они еще не созданы
            $this->autoCreateAllPairs($match_id, $game, $team_a_id, $team_b_id);
            
            window_mess('Склад команди збережено! (Збережено гравців: '.$saved_count.')');
        } else {
            window_mess('Помилка: склад не збережено!');
        }
        
        // Важно: вызываем show() чтобы отобразить обновленные данные
        $this->show();
    }
    
    // Автоматическое создание всех 5 пар при наличии составов обеих команд
    function autoCreateAllPairs($match_id, $game, $team_a_id, $team_b_id) {
        if (empty($team_a_id) || empty($team_b_id) || empty($match_id)) {
            return;
        }
        
        // Проверяем, есть ли составы обеих команд
        $team_a_lineup = db_list('SELECT player_id, position FROM bs_team_lineups 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.(int)$game['etap_id'].'
            AND team_id='.(int)$team_a_id.'
            ORDER BY position_order');
        
        $team_b_lineup = db_list('SELECT player_id, position FROM bs_team_lineups 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.(int)$game['etap_id'].'
            AND team_id='.(int)$team_b_id.'
            ORDER BY position_order');
        
        // Если нет составов обеих команд, ничего не делаем
        if (empty($team_a_lineup) || count($team_a_lineup) < 3 || empty($team_b_lineup) || count($team_b_lineup) < 3) {
            return;
        }
        
        // Получаем игроков по позициям
        $players_a = array();
        $players_b = array();
        
        foreach ($team_a_lineup as $lineup) {
            $pos = $lineup['position'];
            if (in_array($pos, ['A', 'B', 'C'])) {
                $players_a[$pos] = (int)$lineup['player_id'];
            }
        }
        
        foreach ($team_b_lineup as $lineup) {
            $pos = $lineup['position'];
            if (in_array($pos, ['Y', 'X', 'Z'])) {
                $players_b[$pos] = (int)$lineup['player_id'];
            }
        }
        
        // Проверяем, есть ли уже пары для этого матча
        $existing_pairs = db_list('SELECT pair_number FROM bs_team_pairs 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.(int)$game['etap_id']);
        
        $existing_pair_numbers = array();
        foreach ($existing_pairs as $ep) {
            $existing_pair_numbers[] = (int)$ep['pair_number'];
        }
        
        // Определяем пары по ТЗ:
        // Пара 1: A - Y
        // Пара 2: B - X
        // Пара 3: C - Z
        // Пара 4: A - X (дополнительная)
        // Пара 5: B - Y (дополнительная)
        $pairs_to_create = array(
            1 => array('a_pos' => 'A', 'b_pos' => 'Y'),
            2 => array('a_pos' => 'B', 'b_pos' => 'X'),
            3 => array('a_pos' => 'C', 'b_pos' => 'Z'),
            4 => array('a_pos' => 'A', 'b_pos' => 'X'),
            5 => array('a_pos' => 'B', 'b_pos' => 'Y')
        );
        
        $created_count = 0;
        $now = date('Y-m-d H:i:s');
        $etap_id = (int)$game['etap_id'];
        $match_id_escaped = addslashes($match_id);
        
        foreach ($pairs_to_create as $pair_number => $pair_config) {
            // Пропускаем, если пара уже существует
            if (in_array($pair_number, $existing_pair_numbers)) {
                continue;
            }
            
            $player_a_id = !empty($players_a[$pair_config['a_pos']]) ? (int)$players_a[$pair_config['a_pos']] : 0;
            $player_b_id = !empty($players_b[$pair_config['b_pos']]) ? (int)$players_b[$pair_config['b_pos']] : 0;
            
            // Создаем пару только если оба игрока найдены
            if ($player_a_id > 0 && $player_b_id > 0) {
                $sql = 'INSERT INTO `bs_team_pairs` 
                        (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                        VALUES 
                        ('.$etap_id.', "'.$match_id_escaped.'", '.$pair_number.', '.$team_a_id.', '.$team_b_id.', '.$player_a_id.', '.$player_b_id.', "'.$now.'", "'.$now.'")
                        ON DUPLICATE KEY UPDATE
                        team_a_player_id='.$player_a_id.',
                        team_b_player_id='.$player_b_id.',
                        updated_at="'.$now.'"';
            //    s($sql);
                db_query($sql);
                $created_count++;
            }
        }
        
        if ($created_count > 0) {
            wLog('Auto-created '.$created_count.' pairs for match_id='.$match_id);
        }
    }
    
    function savePairs() {
        $game_id = poste('id');
        // Получаем пары из POST, проверяя оба способа
        $pairs = poste('pairs');
        if (empty($pairs) && !empty($_POST['pairs'])) {
            $pairs = $_POST['pairs'];
        }
        
        // Диагностика: логируем полученные данные
        wLog('ДИАГНОСТИКА savePairs: game_id='.$game_id);
        wLog('ДИАГНОСТИКА savePairs: pairs from poste(): '.json_encode($pairs));
        wLog('ДИАГНОСТИКА savePairs: $_POST[pairs]: '.json_encode($_POST['pairs'] ?? 'NOT SET'));
        wLog('ДИАГНОСТИКА savePairs: save_pairs from poste(): '.poste('save_pairs'));
        
        if (empty($game_id)) {
            window_mess('Помилка: не вказано гру');
            $this->show();
            return;
        }
        
        // Получаем информацию об игре
        $game = db_row('SELECT * FROM `'.T_REITING.'` WHERE id='.$game_id);
        if (empty($game)) {
            window_mess('Помилка: гру не знайдено');
            $this->show();
            return;
        }
        
        $match_id = $this->getMatchId($game);
        
        // Проверяем, заблокированы ли пары для этого конкретного матча
        // Пары блокируются, если уже введен хотя бы один результат для игр этого матча
        $has_results = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.$game['etap_id'].'
            AND pair_number > 0
            AND ((set_1 > 0 OR set_2 > 0 OR set_1 = "W" OR set_2 = "W")
              AND NOT (set_1 = "0" AND set_2 = "0"))', 'cnt');
        $lineups_locked = !empty($has_results) && (int)$has_results > 0 ? 1 : 0;
        
        if (!empty($lineups_locked)) {
            window_mess('Помилка: пари заблоковані після введення першого результату');
            $this->show();
            return;
        }
        
        // Получаем team_lineups для определения команд
        $team_lineups = $this->getMatchLineups($match_id, $game);
        if (empty($team_lineups) || count($team_lineups) < 2) {
            window_mess('Помилка: спочатку потрібно зберегти склади обох команд');
            $this->show();
            return;
        }
        
        // ВАЖНО: Используем team_a_id и team_b_id из основной командной игры,
        // чтобы порядок команд в парах совпадал с порядком в командной игре
        $team_a_id = !empty($game['team_a_id']) ? (int)$game['team_a_id'] : 0;
        $team_b_id = !empty($game['team_b_id']) ? (int)$game['team_b_id'] : 0;
        
        // Если team_a_id и team_b_id не определены в командной игре, определяем из team_lineups
        if (empty($team_a_id) || empty($team_b_id)) {
            $team_ids = array_keys($team_lineups);
            if (count($team_ids) >= 2) {
                $team_a_id = (int)$team_ids[0];
                $team_b_id = (int)$team_ids[1];
            }
        }

        // Фиксируем порядок команд в записи матча при сохранении пар
        if (!empty($team_a_id) && !empty($team_b_id)) {
            $pl1_is_team = !empty($game['pl_id_1']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_1'], 'is_team') : 0;
            $pl2_is_team = !empty($game['pl_id_2']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_2'], 'is_team') : 0;
            $pl_update = ($pl1_is_team === 1 && $pl2_is_team === 1) ? ', pl_id_1='.(int)$team_a_id.', pl_id_2='.(int)$team_b_id : '';
            db_query('UPDATE `'.T_REITING.'` SET team_a_id='.(int)$team_a_id.', team_b_id='.(int)$team_b_id.$pl_update.' WHERE id='.(int)$game_id);
        }
        
        // ГЛОБАЛЬНОЕ ИСПРАВЛЕНИЕ: Проверяем принадлежность игроков к правильным командам
        // Получаем игроков команды A и команды B из bs_team_lineups
        $team_a_player_ids = array();
        $team_b_player_ids = array();
        $lineups_check = db_list('SELECT team_id, player_id FROM `bs_team_lineups`
            WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id'].'
            ORDER BY team_id, position_order ASC');
        
        foreach ($lineups_check as $lineup_check) {
            $lineup_team_id = (int)$lineup_check['team_id'];
            $lineup_player_id = (int)$lineup_check['player_id'];
            
            if ($lineup_team_id == $team_a_id) {
                $team_a_player_ids[] = $lineup_player_id;
            } elseif ($lineup_team_id == $team_b_id) {
                $team_b_player_ids[] = $lineup_player_id;
            }
        }
        
        // Если составы еще не сохранены, определяем игроков по team_id в таблице players
        $league_id_fallback = teamplayers_resolve_league_id(!empty($game['league_id']) ? $game['league_id'] : poste('league_id'), !empty($game['turnir_id']) ? $game['turnir_id'] : poste('turnir_id'));
        if (empty($team_a_player_ids) && !empty($team_a_id)) {
            $team_a_players = teamplayers_list($team_a_id, $league_id_fallback, 'p.id');
            foreach ($team_a_players as $player) {
                $team_a_player_ids[] = (int)$player['id'];
            }
        }
        if (empty($team_b_player_ids) && !empty($team_b_id)) {
            $team_b_players = teamplayers_list($team_b_id, $league_id_fallback, 'p.id');
            foreach ($team_b_players as $player) {
                $team_b_player_ids[] = (int)$player['id'];
            }
        }
        
        // Формируем массив пар из POST данных с проверкой принадлежности игроков к командам
        $team_pairs = array();
        if (!empty($pairs) && is_array($pairs)) {
            foreach ($pairs as $pair_num => $pair_data) {
                // Проверяем, что данные пары корректны
                $team_a_player_id_post = !empty($pair_data['team_a_player_id']) ? (int)$pair_data['team_a_player_id'] : 0;
                $team_b_player_id_post = !empty($pair_data['team_b_player_id']) ? (int)$pair_data['team_b_player_id'] : 0;
                
                wLog('ДИАГНОСТИКА savePairs: пара '.$pair_num.', team_a_player_id_post='.$team_a_player_id_post.', team_b_player_id_post='.$team_b_player_id_post);
                
                // Пропускаем пары, где не выбраны оба игрока
                if ($team_a_player_id_post > 0 && $team_b_player_id_post > 0) {
                    // ИСПРАВЛЕНИЕ: Проверяем, к какой команде принадлежат игроки
                    // Если игроки перепутаны местами, меняем их местами
                    $team_a_player_id = $team_a_player_id_post;
                    $team_b_player_id = $team_b_player_id_post;
                    
                    $player_a_belongs_to_team_a = in_array($team_a_player_id_post, $team_a_player_ids);
                    $player_a_belongs_to_team_b = in_array($team_a_player_id_post, $team_b_player_ids);
                    $player_b_belongs_to_team_a = in_array($team_b_player_id_post, $team_a_player_ids);
                    $player_b_belongs_to_team_b = in_array($team_b_player_id_post, $team_b_player_ids);
                    
                    // Если игроки перепутаны местами, меняем их
                    if (!$player_a_belongs_to_team_a && $player_a_belongs_to_team_b && 
                        !$player_b_belongs_to_team_b && $player_b_belongs_to_team_a) {
                        // Игроки перепутаны - меняем местами
                        $team_a_player_id = $team_b_player_id_post;
                        $team_b_player_id = $team_a_player_id_post;
                        
                        wLog('ИСПРАВЛЕНО: Пара '.$pair_num.' - игроки перепутаны местами. Было: team_a_player_id='.$team_a_player_id_post.', team_b_player_id='.$team_b_player_id_post.'. Стало: team_a_player_id='.$team_a_player_id.', team_b_player_id='.$team_b_player_id);
                    } elseif (!$player_a_belongs_to_team_a && !$player_b_belongs_to_team_b) {
                        // Оба игрока не принадлежат к своим командам - возможна ошибка в данных
                        wLog('ПРЕДУПРЕЖДЕНИЕ: Пара '.$pair_num.' - игроки могут быть перепутаны. team_a_player_id='.$team_a_player_id_post.' (принадлежит к team_a: '.($player_a_belongs_to_team_a ? 'да' : 'нет').', к team_b: '.($player_a_belongs_to_team_b ? 'да' : 'нет').'), team_b_player_id='.$team_b_player_id_post.' (принадлежит к team_a: '.($player_b_belongs_to_team_a ? 'да' : 'нет').', к team_b: '.($player_b_belongs_to_team_b ? 'да' : 'нет').')');
                    }
                    
                    $team_pairs[] = array(
                        'match_id' => $match_id,
                        'team_a_id' => $team_a_id,
                        'team_b_id' => $team_b_id,
                        'team_a_player_id' => $team_a_player_id,
                        'team_b_player_id' => $team_b_player_id,
                        'pair_number' => (int)$pair_num
                    );
                }
            }
        }
        
        wLog('ДИАГНОСТИКА savePairs: обработано пар: '.count($team_pairs));
        
        // Если пары пустые, возможно, данные пришли в другом формате
        if (empty($team_pairs) && !empty($pairs)) {
            wLog('ДИАГНОСТИКА savePairs: ПРОБЛЕМА - пары пустые, но $pairs не пустой');
            window_mess('Помилка: не вдалося обробити дані пар. Перевірте, що обрані обидва гравці для кожної пари.');
            $this->show();
            return;
        }
        
        if (empty($team_pairs)) {
            wLog('ДИАГНОСТИКА savePairs: ПРОБЛЕМА - пары пустые, $pairs тоже пустой');
            window_mess('Помилка: не отримано даних пар для збереження.');
            $this->show();
            return;
        }
        
        // Сортируем по номеру пары
        usort($team_pairs, function($a, $b) {
            return $a['pair_number'] - $b['pair_number'];
        });
        
        // Убеждаемся, что match_id правильно определен
        if (empty($match_id)) {
            $match_id = $this->getMatchId($game);
        }
        
        // Удаляем старые пары для этого матча перед сохранением новых
        db_query('DELETE FROM `bs_team_pairs` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id']);
        
        // Сохраняем пары в отдельную таблицу bs_team_pairs
        $saved_count = 0;
        $now = date('Y-m-d H:i:s');
        
        foreach ($team_pairs as $pair) {
            $pair_number = (int)$pair['pair_number'];
            $team_a_id = (int)$pair['team_a_id'];
            $team_b_id = (int)$pair['team_b_id'];
            $team_a_player_id = (int)$pair['team_a_player_id'];
            $team_b_player_id = (int)$pair['team_b_player_id'];
            $match_id_escaped = addslashes($match_id);
            $etap_id = (int)$game['etap_id'];
            
            $sql = 'INSERT INTO `bs_team_pairs` 
                    (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                    VALUES 
                    ('.$etap_id.', "'.$match_id_escaped.'", '.$pair_number.', '.$team_a_id.', '.$team_b_id.', '.$team_a_player_id.', '.$team_b_player_id.', "'.$now.'", "'.$now.'")
                    ON DUPLICATE KEY UPDATE
                    team_a_player_id='.$team_a_player_id.',
                    team_b_player_id='.$team_b_player_id.',
                    updated_at="'.$now.'"';
          //  s($sql);
            db_query($sql);
            $saved_count++;
        }
        
        if ($saved_count > 0) {
            window_mess('Пари гравців збережено! (Збережено пар: '.$saved_count.')');
        } else {
            window_mess('Помилка: пари не збереглися!');
        }
        
        $this->show();
    }
    
    // Получаем или формируем match_id для игры
    function getMatchId($game) {
        if (!empty($game['match_id'])) {
            return $game['match_id'];
        }
        
        // Формируем match_id из etap_id + team_a_id + team_b_id
        $team_a_id = !empty($game['team_a_id']) ? $game['team_a_id'] : 0;
        $team_b_id = !empty($game['team_b_id']) ? $game['team_b_id'] : 0;
        
        // Если команды не определены, определяем их из pl_id_1 и pl_id_2
        if (empty($team_a_id) || empty($team_b_id)) {
            // Проверяем, являются ли игроки командами
            if (!empty($game['pl_id_1'])) {
                $player1 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_1']);
                if (!empty($player1['is_team']) && $player1['is_team'] == 1) {
                    $team_a_id = $player1['id'];
                }
            }
            if (!empty($game['pl_id_2'])) {
                $player2 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_2']);
                if (!empty($player2['is_team']) && $player2['is_team'] == 1) {
                    $team_b_id = $player2['id'];
                }
            }
        }
        
        if (empty($team_a_id) || empty($team_b_id)) {
            // Если не удалось определить команды, используем ID игры
            return 'match_'.$game['id'];
        }
        
        // Формируем match_id: всегда меньший ID команды сначала
        $min_team = min($team_a_id, $team_b_id);
        $max_team = max($team_a_id, $team_b_id);
        return 'match_'.$game['etap_id'].'_'.$min_team.'_'.$max_team;
    }
    
    // Получаем составы для конкретного матча из таблицы bs_team_lineups
    function getMatchLineups($match_id, $game) {
        // Загружаем составы из отдельной таблицы вместо JSON
        $sql = 'SELECT team_id, player_id, position, position_order
                FROM `bs_team_lineups`
                WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id'].'
                ORDER BY team_id, position_order ASC';
        $lineups_list = db_list($sql);
        
        if (empty($lineups_list)) {
            return array();
        }
        
        // Преобразуем в формат массива: {team_id: [player_id_1, player_id_2, ...]}
        $result = array();
        foreach ($lineups_list as $lineup) {
            $team_id = (int)$lineup['team_id'];
            $player_id = (int)$lineup['player_id'];
            
            if (!isset($result[$team_id])) {
                $result[$team_id] = array();
            }
            
            // Добавляем игрока в порядке позиции (position_order)
            $result[$team_id][] = $player_id;
        }
        
        return $result;
    }
    
    // Получаем пары для конкретного матча из таблицы bs_team_pairs
    function getMatchPairs($match_id, $game) {
        // Загружаем пары из отдельной таблицы вместо JSON
        $sql = 'SELECT pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, match_id
                FROM `bs_team_pairs`
                WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id'].'
                ORDER BY pair_number ASC';
        $pairs_list = db_list($sql);
        
        if (empty($pairs_list)) {
            return array();
        }
        
        // Преобразуем в формат массива с ключами по pair_number для совместимости
        $result = array();
        foreach ($pairs_list as $pair) {
            $result[] = array(
                'pair_number' => (int)$pair['pair_number'],
                'team_a_id' => (int)$pair['team_a_id'],
                'team_b_id' => (int)$pair['team_b_id'],
                'team_a_player_id' => (int)$pair['team_a_player_id'],
                'team_b_player_id' => (int)$pair['team_b_player_id'],
                'match_id' => $pair['match_id']
            );
        }
        
        return $result;
    }
    
    function show() {
        $game_id = poste('id'); // ID записи из bs_reiting
        $turnir_id = poste('turnir_id');
        $league_id = poste('league_id');
        $etap_id = poste('etap_id');

        if (empty($game_id)) {
            window_mess('Помилка: не вказано гру');
            $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: не вказано гру</div></div>';
            return;
        }

        // Получаем информацию об игре
        $game = db_row('SELECT * FROM `'.T_REITING.'` WHERE id='.$game_id);
        if (empty($game)) {
            window_mess('Помилка: гру не знайдено');
            $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: гру не знайдено</div></div>';
            return;
        }
        
        if (empty($turnir_id)) {
            $turnir_id = !empty($game['turnir_id']) ? $game['turnir_id'] : 0;
        }
        if (empty($etap_id)) {
            $etap_id = !empty($game['etap_id']) ? $game['etap_id'] : 0;
        }
        
        if (empty($etap_id)) {
            window_mess('Помилка: не вказано етап для цієї гри');
            $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: не вказано етап для цієї гри</div></div>';
            return;
        }
        
        // Проверяем, что это командная лига
        if (empty($league_id) && !empty($turnir_id)) {
            $league_id = db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
        }
        
        $is_team_league = 0;
        if (!empty($league_id)) {
            $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
        }

        if (!$is_team_league) {
            window_mess('Помилка: це не командна ліга');
            $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: це не командна ліга</div></div>';
            return;
        }
        
        // Определяем команды
        $team_a_id = !empty($game['team_a_id']) ? $game['team_a_id'] : 0;
        $team_b_id = !empty($game['team_b_id']) ? $game['team_b_id'] : 0;
        $team_a_name = '';
        $team_b_name = '';
        
        // Если команды не определены, определяем их из pl_id_1 и pl_id_2
        if (empty($team_a_id) || empty($team_b_id)) {
            if (!empty($game['pl_id_1'])) {
                $player1 = db_row('SELECT id, is_team, name FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_1']);
                if (!empty($player1) && !empty($player1['is_team']) && $player1['is_team'] == 1) {
                    $team_a_id = $player1['id'];
                    $team_a_name = !empty($player1['name']) ? $player1['name'] : 'Команда A';
                }
            }
            if (!empty($game['pl_id_2'])) {
                $player2 = db_row('SELECT id, is_team, name FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_2']);
                if (!empty($player2) && !empty($player2['is_team']) && $player2['is_team'] == 1) {
                    $team_b_id = $player2['id'];
                    $team_b_name = !empty($player2['name']) ? $player2['name'] : 'Команда B';
                }
            }
        }
        
        // Если команды все еще не определены, получаем имена из БД
        if (empty($team_a_name) && !empty($team_a_id)) {
            $team_a_info = db_row('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_a_id);
            $team_a_name = !empty($team_a_info['name']) ? $team_a_info['name'] : 'Команда A';
        }
        if (empty($team_b_name) && !empty($team_b_id)) {
            $team_b_info = db_row('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_b_id);
            $team_b_name = !empty($team_b_info['name']) ? $team_b_info['name'] : 'Команда B';
        }
        
        // Если имена все еще пустые, пытаемся получить из pl_id напрямую
        if (empty($team_a_name) && !empty($game['pl_id_1'])) {
            $pl1_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_1'], 'name');
            if (!empty($pl1_name)) {
                $team_a_name = $pl1_name;
            }
        }
        if (empty($team_b_name) && !empty($game['pl_id_2'])) {
            $pl2_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$game['pl_id_2'], 'name');
            if (!empty($pl2_name)) {
                $team_b_name = $pl2_name;
            }
        }
        
        if (empty($team_a_id) || empty($team_b_id)) {
            // Если команды не определены через is_team, используем pl_id_1 и pl_id_2 напрямую
            // В командных турнирах pl_id_1 и pl_id_2 могут указывать на команды
            if (!empty($game['pl_id_1']) && !empty($game['pl_id_2'])) {
                $team_a_id = $game['pl_id_1'];
                $team_b_id = $game['pl_id_2'];
                
                // Получаем имена команд
                if (empty($team_a_name)) {
                    $team_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_a_id, 'name');
                    if (empty($team_a_name)) {
                        $team_a_name = 'Команда A';
                    }
                }
                if (empty($team_b_name)) {
                    $team_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_b_id, 'name');
                    if (empty($team_b_name)) {
                        $team_b_name = 'Команда B';
                    }
                }
            } else {
                window_mess('Помилка: не вдалося визначити команди для цієї гри (pl_id_1='.$game['pl_id_1'].', pl_id_2='.$game['pl_id_2'].')');
                $this->content = '<div class="container-fluid"><div class="alert alert-danger">Помилка: не вдалося визначити команди для цієї гри. pl_id_1='.$game['pl_id_1'].', pl_id_2='.$game['pl_id_2'].'</div></div>';
                return;
            }
        }
        
        // Сохраняем оригинальные ID команд до возможной смены
        $original_team_a_id = $team_a_id;
        $original_team_b_id = $team_b_id;
        
        // Получаем match_id (match_id всегда формируется одинаково - по минимальному и максимальному ID)
        $match_id = $this->getMatchId($game);
        
        // Получаем сохраненные составы и пары для этого матча
        $team_lineups = $this->getMatchLineups($match_id, $game);
        $team_pairs = $this->getMatchPairs($match_id, $game);
        
        // Обрабатываем смену команд местами (если пришел параметр swap_teams)
        $swap_teams = poste('swap_teams');
        if (!empty($swap_teams) && $swap_teams == '1') {
            // Сохраняем составы с оригинальными ключами (до смены команд)
            $original_lineup_a = !empty($team_lineups[$original_team_a_id]) ? $team_lineups[$original_team_a_id] : array();
            $original_lineup_b = !empty($team_lineups[$original_team_b_id]) ? $team_lineups[$original_team_b_id] : array();
            
            // Сохраняем пары с оригинальными командами
            $original_pairs = $team_pairs;
            
            // Меняем местами команды
            $temp_id = $team_a_id;
            $temp_name = $team_a_name;
            $team_a_id = $team_b_id;
            $team_a_name = $team_b_name;
            $team_b_id = $temp_id;
            $team_b_name = $temp_name;
            
            // Меняем местами составы в массиве team_lineups
            // После смены: team_a_id теперь указывает на бывшую команду B, team_b_id - на бывшую команду A
            $new_team_lineups = array();
            // Новая команда A (бывшая команда B) получает состав бывшей команды B
            $new_team_lineups[$team_a_id] = $original_lineup_b;
            // Новая команда B (бывшая команда A) получает состав бывшей команды A
            $new_team_lineups[$team_b_id] = $original_lineup_a;
            $team_lineups = $new_team_lineups;
            
            // Обновляем пары: меняем местами team_a_player_id и team_b_player_id в каждой паре
            $new_team_pairs = array();
            foreach ($original_pairs as $pair) {
                $new_pair = $pair;
                // Меняем местами ID игроков в паре
                $temp_player = !empty($pair['team_a_player_id']) ? $pair['team_a_player_id'] : null;
                $new_pair['team_a_player_id'] = !empty($pair['team_b_player_id']) ? $pair['team_b_player_id'] : null;
                $new_pair['team_b_player_id'] = $temp_player;
                // Обновляем ID команд в паре
                $new_pair['team_a_id'] = $team_a_id;
                $new_pair['team_b_id'] = $team_b_id;
                $new_team_pairs[] = $new_pair;
            }
            $team_pairs = $new_team_pairs;
            
            // Обновляем данные в БД
            // Формируем новый match_id (он должен остаться тем же, так как команды те же, просто поменялись местами)
            // Обновляем team_lineups в таблице bs_team_lineups
            // Удаляем старые записи для этого матча
            db_query('DELETE FROM `bs_team_lineups` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id']);
            
            // Определяем метки позиций для команд
            $team_a_labels = array('A', 'B', 'C', 'D', 'E');
            $team_b_labels = array('Y', 'X', 'Z', 'W', 'V');
            
            // Сохраняем обновленные составы в таблицу bs_team_lineups
            $now = date('Y-m-d H:i:s');
            $match_id_escaped = addslashes($match_id);
            $etap_id = (int)$game['etap_id'];
            
            foreach ($team_lineups as $team_id => $players) {
                $team_id_int = (int)$team_id;
                $is_team_a = ($team_id == $team_a_id);
                $labels = $is_team_a ? $team_a_labels : $team_b_labels;
                
                foreach ($players as $index => $player_id) {
                    if (empty($player_id) || $player_id <= 0) {
                        continue;
                    }
                    
                    $position = !empty($labels[$index]) ? $labels[$index] : chr(65 + $index);
                    $position_order = $index;
                    $player_id_int = (int)$player_id;
                    
                    $sql = 'INSERT INTO `bs_team_lineups` 
                            (etap_id, match_id, team_id, position, player_id, position_order, created_at, updated_at)
                            VALUES 
                            ('.$etap_id.', "'.$match_id_escaped.'", '.$team_id_int.', "'.addslashes($position).'", '.$player_id_int.', '.$position_order.', "'.$now.'", "'.$now.'")';
                 //  s($sql);
                    db_query($sql);
                }
            }
            
            // Обновляем пары в таблице bs_team_pairs (match_id не меняется при смене команд местами, только игроки)
            if (!empty($team_pairs)) {
                $now = date('Y-m-d H:i:s');
                // Удаляем старые пары для этого матча
                db_query('DELETE FROM `bs_team_pairs` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$game['etap_id']);
                
                // Сохраняем обновленные пары
                foreach ($team_pairs as $pair) {
                    $pair_number = (int)$pair['pair_number'];
                    $team_a_id = (int)$pair['team_a_id'];
                    $team_b_id = (int)$pair['team_b_id'];
                    $team_a_player_id = (int)$pair['team_a_player_id'];
                    $team_b_player_id = (int)$pair['team_b_player_id'];
                    $match_id_escaped = addslashes($match_id);
                    $etap_id = (int)$game['etap_id'];
                    
                    $sql = 'INSERT INTO `bs_team_pairs` 
                            (etap_id, match_id, pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id, created_at, updated_at)
                            VALUES 
                            ('.$etap_id.', "'.$match_id_escaped.'", '.$pair_number.', '.$team_a_id.', '.$team_b_id.', '.$team_a_player_id.', '.$team_b_player_id.', "'.$now.'", "'.$now.'")
                            ON DUPLICATE KEY UPDATE
                            team_a_player_id='.$team_a_player_id.',
                            team_b_player_id='.$team_b_player_id.',
                            updated_at="'.$now.'"';
                  //  s($sql);
                    db_query($sql);
                }
            }

            // Обновляем порядок команд в основной записи матча,
            // чтобы последующие сохранения не сбрасывали составы после swap
            $pl1_is_team = !empty($game['pl_id_1']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_1'], 'is_team') : 0;
            $pl2_is_team = !empty($game['pl_id_2']) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_2'], 'is_team') : 0;
            $pl_update = ($pl1_is_team === 1 && $pl2_is_team === 1) ? ', pl_id_1='.(int)$team_a_id.', pl_id_2='.(int)$team_b_id : '';
            db_query('UPDATE `'.T_REITING.'` SET team_a_id='.(int)$team_a_id.', team_b_id='.(int)$team_b_id.$pl_update.' WHERE id='.(int)$game_id);
        }
        
        // Проверяем, заблокирован ли состав для этого конкретного матча
        // Состав блокируется, если уже введен хотя бы один результат для игр этого матча
        $match_id = $this->getMatchId($game);
        $has_results = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` 
            WHERE match_id="'.addslashes($match_id).'" 
            AND etap_id='.$etap_id.'
            AND pair_number > 0
            AND ((set_1 > 0 OR set_2 > 0 OR set_1 = "W" OR set_2 = "W")
              AND NOT (set_1 = "0" AND set_2 = "0"))', 'cnt');
        $lineups_locked = !empty($has_results) && (int)$has_results > 0 ? 1 : 0;
        
        // Проверяем, что имена команд определены
        if (empty($team_a_name)) {
            $team_a_name = 'Команда A';
        }
        if (empty($team_b_name)) {
            $team_b_name = 'Команда B';
        }
        
        // Скрываем всю панель фильтров через CSS - только для страницы team_lineups
        // Используем специфичный класс контейнера team_lineups для ограничения области действия стилей
        $content = '<style id="hide-filter-panel-team-lineups">
            /* Скрываем всю панель фильтров: поиск, группы, вкладки статусов игр */
            /* Применяем стили только когда страница содержит контент team_lineups */
            body:has(#team-lineups-container) .submenu_list input[name="fio_search"],
            body:has(#team-lineups-container) .submenu_list input[id="search_field_games"],
            body:has(#team-lineups-container) .submenu_list select#search_field_games_select,
            body:has(#team-lineups-container) .submenu_list select[name="groups"],
            body:has(#team-lineups-container) .submenu_list select[id="etap-chosen-select"],
            body:has(#team-lineups-container) .submenu_list .speedsearch,
            body:has(#team-lineups-container) .submenu_list .speedsearch_panel,
            body:has(#team-lineups-container) .submenu_list .filter_panel,
            body:has(#team-lineups-container) .submenu_list .search_panel,
            body:has(#team-lineups-container) .submenu_list form[name="speedsearch"],
            body:has(#team-lineups-container) .submenu_list form[action*="speedsearch"],
            body:has(#team-lineups-container) .submenu_list .groups_dropdown,
            /* Вкладки статусов игр */
            body:has(#team-lineups-container) .submenu_list a[href*="filter=nogame"],
            body:has(#team-lineups-container) .submenu_list a[href*="filter=start"],
            body:has(#team-lineups-container) .submenu_list a[href*="filter=finish"],
            body:has(#team-lineups-container) .submenu_list a[href*="#reiting-list"][href*="filter="],
            /* Скрываем строки таблицы с фильтрами */
            body:has(#team-lineups-container) table.submenu_list tr:has(input[name="fio_search"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(input[id="search_field_games"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(select#search_field_games_select),
            body:has(#team-lineups-container) table.submenu_list tr:has(select[name="groups"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(select[id="etap-chosen-select"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(.speedsearch),
            body:has(#team-lineups-container) table.submenu_list tr:has(a[href*="filter="]),
            /* Скрываем через родительские элементы */
            body:has(#team-lineups-container) tr:has(input[name="fio_search"]),
            body:has(#team-lineups-container) tr:has(input[id="search_field_games"]),
            body:has(#team-lineups-container) tr:has(select#search_field_games_select),
            body:has(#team-lineups-container) tr:has(select[name="groups"]),
            body:has(#team-lineups-container) tr:has(select[id="etap-chosen-select"]),
            body:has(#team-lineups-container) tr:has(a[href*="filter="]),
            body:has(#team-lineups-container) td:has(input[name="fio_search"]),
            body:has(#team-lineups-container) td:has(input[id="search_field_games"]),
            body:has(#team-lineups-container) td:has(select#search_field_games_select),
            body:has(#team-lineups-container) td:has(select[name="groups"]),
            body:has(#team-lineups-container) td:has(select[id="etap-chosen-select"]),
            body:has(#team-lineups-container) td:has(a[href*="filter="]),
            /* Дополнительные селекторы для подстраховки */
            body:has(#team-lineups-container) .submenu_list input[name="fio_search"],
            body:has(#team-lineups-container) .submenu_list input[id="search_field_games"],
            body:has(#team-lineups-container) .submenu_list select#search_field_games_select,
            body:has(#team-lineups-container) .submenu_list select[name="groups"],
            body:has(#team-lineups-container) .submenu_list select[id="etap-chosen-select"],
            body:has(#team-lineups-container) .submenu_list .speedsearch,
            body:has(#team-lineups-container) .submenu_list a[href*="filter="],
            /* Скрываем всю строку/блок с фильтрами */
            body:has(#team-lineups-container) table.submenu_list tr:has(input[name="fio_search"]):has(select[name="groups"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(a[href*="filter="]),
            /* Если фильтры в отдельном блоке */
            body:has(#team-lineups-container) div:has(> input[name="fio_search"]),
            body:has(#team-lineups-container) div:has(> input[id="search_field_games"]),
            body:has(#team-lineups-container) div:has(> select#search_field_games_select),
            body:has(#team-lineups-container) div:has(> select[name="groups"]),
            body:has(#team-lineups-container) div:has(> select[id="etap-chosen-select"]),
            body:has(#team-lineups-container) div:has(> a[href*="filter="]),
            /* Скрываем элементы фильтров через более общие селекторы */
            body:has(#team-lineups-container) .col_flo_left,
            body:has(#team-lineups-container) .input__wrapper,
            body:has(#team-lineups-container) .chosen-container,
            body:has(#team-lineups-container) .Line_Menu,
            body:has(#team-lineups-container) .bigMenu,
            body:has(#team-lineups-container) .filter_status_tabs,
            body:has(#team-lineups-container) .game_status_filters {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            /* Скрываем родительские строки таблицы */
            body:has(#team-lineups-container) table.submenu_list tr:has(input[name="fio_search"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(input[id="search_field_games"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(select[name="groups"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(select[id="etap-chosen-select"]),
            body:has(#team-lineups-container) table.submenu_list tr:has(a[href*="filter="]),
            body:has(#team-lineups-container) table.submenu_list tr:has(.speedsearch) {
                display: none !important;
                height: 0 !important;
                line-height: 0 !important;
                border: none !important;
            }
        </style>';
        
        // Формируем HTML для интерфейса
        // Добавляем идентификатор контейнера для применения стилей только к этой странице
        $content .= '<div class="container-fluid" id="team-lineups-container">';
        $content .= '<h4>Управління складами команд для матчу</h4>';
        
        // Определение команд A и B (можно поменять местами)
        $content .= '<div class="alert alert-info">';
        $content .= '<strong>Визначення команд:</strong> ';
        $content .= '<button type="button" class="btn btn-sm btn-secondary" onclick="swapTeams()" id="swap_teams_btn">Поміняти місцями команди A і B</button>';
        $content .= '<input type="hidden" id="team_a_current_id" value="'.$team_a_id.'">';
        $content .= '<input type="hidden" id="team_b_current_id" value="'.$team_b_id.'">';
        $content .= '<input type="hidden" id="team_a_current_name" value="'.htmlspecialchars($team_a_name).'">';
        $content .= '<input type="hidden" id="team_b_current_name" value="'.htmlspecialchars($team_b_name).'">';
        $content .= '</div>';
        
        $content .= '<h5 id="match_title">Команда A: '.$team_a_name.' vs Команда B: '.$team_b_name.'</h5>';
        
        // Добавляем кнопку для исправления пар (если есть пары)
        if (!empty($team_pairs)) {
            $content .= '<div class="alert alert-info mt-2 mb-3">';
            $content .= '<a href="#reiting-fix_team_pairs-&id='.$game_id.'&turnir_id='.$turnir_id.'&etap_id='.$etap_id.'&league_id='.$league_id.'" class="ajax_send btn btn-warning btn-sm">Проверить и исправить пары (если игроки перепутаны)</a>';
            $content .= '</div>';
        }

        $content .= '<div class="row mt-3">';
        $content .= '<div class="col-12">';
        
        $content .= '<div class="card mb-3">';
        $content .= '<div class="card-header bg-primary text-white"><h5 id="match_header">Матч: Команда A ('.$team_a_name.') vs Команда B ('.$team_b_name.')</h5></div>';
        $content .= '<div class="card-body">';
        
        // Получаем составы для этих команд
        // Если была смена команд, team_lineups уже содержит правильные ключи (после смены)
        $lineup_a = !empty($team_lineups[$team_a_id]) ? $team_lineups[$team_a_id] : array();
        $lineup_b = !empty($team_lineups[$team_b_id]) ? $team_lineups[$team_b_id] : array();
        
        // Команда A
        $content .= '<div class="row">';
        $content .= '<div class="col-md-6">';
        $content .= '<h6 id="team_a_title">Команда A: '.$team_a_name.'</h6>';
        $content .= '<p class="text-muted small"><strong>Порядок гравців:</strong> A, B, C</p>';
        $content .= '<form id="lineup_form_a" method="post" action="#" class="ajax_form">';
        $content .= '<input type="hidden" name="action" value="team_lineups">';
        $content .= '<input type="hidden" name="module" value="reiting">';
        $content .= '<input type="hidden" name="id" value="'.$game_id.'">';
        $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
        $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
        $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
        $content .= '<input type="hidden" name="team_id" value="'.$team_a_id.'">';
        
        // Получаем игроков команды A
        $players_a = teamplayers_list($team_a_id, $league_id, 'p.id, p.name, p.phone, p.city');
        
        // Определяем текущих выбранных игроков для позиций A, B, C
        $player_a_selected = !empty($lineup_a[0]) ? $lineup_a[0] : '';
        $player_b_selected = !empty($lineup_a[1]) ? $lineup_a[1] : '';
        $player_c_selected = !empty($lineup_a[2]) ? $lineup_a[2] : '';
        
        // Позиция A
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>A:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_a_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця A --</option>';
        foreach ($players_a as $player) {
            $selected = ($player_a_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        // Позиция B
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>B:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_b_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця B --</option>';
        foreach ($players_a as $player) {
            $selected = ($player_b_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        // Позиция C
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>C:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_c_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця C --</option>';
        foreach ($players_a as $player) {
            $selected = ($player_c_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        if (!empty($lineups_locked)) {
            $content .= '<span class="badge bg-warning">Склад заблоковано (після введення результату)</span>';
        } else {
            $content .= '<button type="submit" class="btn btn-primary">Зберегти склад команди A</button>';
            $content .= '<input type="hidden" name="save_lineup" value="1">';
        }
        
        $content .= '</form>';
        $content .= '</div>';
        
        // Команда B
        $content .= '<div class="col-md-6">';
        $content .= '<h6 id="team_b_title">Команда B: '.$team_b_name.'</h6>';
        $content .= '<p class="text-muted small"><strong>Порядок гравців:</strong> Y, X, Z</p>';
        $content .= '<form id="lineup_form_b" method="post" action="#" class="ajax_form">';
        $content .= '<input type="hidden" name="action" value="team_lineups">';
        $content .= '<input type="hidden" name="module" value="reiting">';
        $content .= '<input type="hidden" name="id" value="'.$game_id.'">';
        $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
        $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
        $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
        $content .= '<input type="hidden" name="team_id" value="'.$team_b_id.'">';
        
        // Получаем игроков команды B
        $players_b = teamplayers_list($team_b_id, $league_id, 'p.id, p.name, p.phone, p.city');
        
        // Определяем текущих выбранных игроков для позиций Y, X, Z
        // Y - первый в списке (индекс 0), X - второй (индекс 1), Z - третий (индекс 2)
        $player_y_selected = !empty($lineup_b[0]) ? $lineup_b[0] : '';
        $player_x_selected = !empty($lineup_b[1]) ? $lineup_b[1] : '';
        $player_z_selected = !empty($lineup_b[2]) ? $lineup_b[2] : '';
        
        // Позиция Y
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>Y:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_y_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця Y --</option>';
        foreach ($players_b as $player) {
            $selected = ($player_y_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        // Позиция X
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>X:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_x_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця X --</option>';
        foreach ($players_b as $player) {
            $selected = ($player_x_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        // Позиция Z
        $content .= '<div class="mb-3">';
        $content .= '<label class="form-label"><strong>Z:</strong> Виберіть гравця</label>';
        $content .= '<select name="players[]" class="form-control" id="player_z_pos" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
        $content .= '<option value="">-- Виберіть гравця Z --</option>';
        foreach ($players_b as $player) {
            $selected = ($player_z_selected == $player['id']) ? 'selected' : '';
            $city_name = '';
            if (!empty($player['city']) && is_numeric($player['city'])) {
                $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
            }
            $city_display = !empty($city_name) ? ' ('.$city_name.')' : '';
            $content .= '<option value="'.$player['id'].'" '.$selected.'>'.$player['name'].$city_display.'</option>';
        }
        $content .= '</select>';
        $content .= '</div>';
        
        if (!empty($lineups_locked)) {
            $content .= '<span class="badge bg-warning">Склад заблоковано (після введення результату)</span>';
        } else {
            $content .= '<button type="submit" class="btn btn-primary">Зберегти склад команди B</button>';
            $content .= '<input type="hidden" name="save_lineup" value="1">';
        }
        
        $content .= '</form>';
        $content .= '</div>';
        $content .= '</div>'; // row
        
        // Раздел формирования пар
        if (!empty($lineup_a) && !empty($lineup_b)) {
            $content .= '<hr>';
            $content .= '<h6>Формування пар гравців</h6>';
            
            // Проверяем количество игроков
            if (count($lineup_a) == count($lineup_b)) {
                $content .= '<form id="pairs_form" method="post" action="#" class="ajax_form">';
                $content .= '<input type="hidden" name="action" value="team_lineups">';
                $content .= '<input type="hidden" name="module" value="reiting">';
                $content .= '<input type="hidden" name="id" value="'.$game_id.'">';
                $content .= '<input type="hidden" name="turnir_id" value="'.$turnir_id.'">';
                $content .= '<input type="hidden" name="league_id" value="'.$league_id.'">';
                $content .= '<input type="hidden" name="etap_id" value="'.$etap_id.'">';
                $content .= '<input type="hidden" name="save_pairs" value="1">';
                
                // Получаем текущий счет матча для определения, нужны ли дополнительные пары
                // Ищем игры этого матча по match_id или по командам и этапу
                $game_results = db_list('SELECT r.pair_number, r.win_player, r.pl_id_1, r.pl_id_2 
                    FROM `'.T_REITING.'` r
                    WHERE (r.match_id="'.$match_id.'" OR (r.etap_id='.$etap_id.' AND ((r.pl_id_1='.$team_a_id.' AND r.pl_id_2='.$team_b_id.') OR (r.pl_id_1='.$team_b_id.' AND r.pl_id_2='.$team_a_id.'))))
                    AND r.pair_number IS NOT NULL
                    ORDER BY r.pair_number');
                
                // Подсчитываем победы команд
                $team_a_wins = 0;
                $team_b_wins = 0;
                $played_pairs = 0;
                foreach ($game_results as $result) {
                    if (!empty($result['win_player'])) {
                        if ($result['win_player'] == $result['pl_id_1'] && $result['pl_id_1'] == $team_a_id) {
                            $team_a_wins++;
                        } elseif ($result['win_player'] == $result['pl_id_1'] && $result['pl_id_1'] == $team_b_id) {
                            $team_b_wins++;
                        } elseif ($result['win_player'] == $result['pl_id_2'] && $result['pl_id_2'] == $team_a_id) {
                            $team_a_wins++;
                        } elseif ($result['win_player'] == $result['pl_id_2'] && $result['pl_id_2'] == $team_b_id) {
                            $team_b_wins++;
                        }
                        $played_pairs++;
                    }
                }
                
                // Определяем, нужны ли дополнительные пары (4-я или 5-я)
                $need_fourth_pair = ($played_pairs == 3 && (($team_a_wins == 2 && $team_b_wins == 1) || ($team_a_wins == 1 && $team_b_wins == 2)));
                $need_fifth_pair = ($played_pairs == 4 && ($team_a_wins == 2 && $team_b_wins == 2));
                
                $content .= '<div class="alert alert-warning">';
                $content .= '<strong>Поточний рахунок:</strong> Команда A '.$team_a_wins.' : '.$team_b_wins.' Команда B';
                if ($need_fourth_pair) {
                    $content .= '<br><strong>Потрібна 4-та пара (перехресна): A-X, B-Y</strong>';
                } elseif ($need_fifth_pair) {
                    $content .= '<br><strong>Потрібна 5-та пара (вирішальна): A-X, B-Y</strong>';
                }
                $content .= '</div>';
                
                $content .= '<div class="table-responsive">';
                $content .= '<table class="table table-bordered">';
                $content .= '<thead><tr><th>№ пари</th><th>Гравець команди A<br/>(A, B, C...)</th><th>vs</th><th>Гравець команди B<br/>(X, Y, Z...)</th><th>Конфігурація</th></tr></thead>';
                $content .= '<tbody>';
                
                // Всегда показываем все 5 пар (первые 3 обязательные, 4-я и 5-я создаются заранее для активации при необходимости)
                $num_pairs = 5;
                
                // Массивы для обозначений
                // Команда A: A (1-й), B (2-й), C (3-й)
                $team_a_labels = array('A', 'B', 'C', 'D', 'E');
                // Команда B: Y (1-й), X (2-й), Z (3-й) - согласно порядку "Y, X, Z"
                $team_b_labels = array('Y', 'X', 'Z', 'W', 'V');
                
                // Преобразуем массив пар в ассоциативный массив по номеру пары для быстрого доступа
                $pairs_by_number = array();
                if (!empty($team_pairs)) {
                    foreach ($team_pairs as $pair) {
                        if (!empty($pair['pair_number'])) {
                            $pairs_by_number[$pair['pair_number']] = $pair;
                        }
                    }
                }
                
                for ($i = 1; $i <= $num_pairs; $i++) {
                    // Ищем пару по номеру пары, а не по индексу массива
                    $pair = !empty($pairs_by_number[$i]) ? $pairs_by_number[$i] : null;
                    $selected_a = !empty($pair['team_a_player_id']) ? $pair['team_a_player_id'] : '';
                    $selected_b = !empty($pair['team_b_player_id']) ? $pair['team_b_player_id'] : '';
                    
                    // Определяем конфигурацию для пары
                    $pair_config = '';
                    if ($i == 1) {
                        // Первая пара: A-Y
                        $pair_config = 'A-Y';
                    } elseif ($i == 2) {
                        // Вторая пара: B-X
                        $pair_config = 'B-X';
                    } elseif ($i == 3) {
                        // Третья пара: C-Z
                        $pair_config = 'C-Z';
                    } elseif ($i == 4) {
                        // 4-я пара (при счете 2:1 или 1:2 после 3-й игры): A-X
                        $pair_config = 'A-X (додаткова)';
                    } elseif ($i == 5) {
                        // 5-я пара (при счете 2:2 после 4-й игры): B-Y (решающая)
                        $pair_config = 'B-Y (вирішальна)';
                    }
                    
                    // Все пары отображаются (первые 3 обязательные, 4-я и 5-я создаются заранее)
                    $content .= '<tr>';
                    $content .= '<td>'.$i.'</td>';
                    $content .= '<td><select name="pairs['.$i.'][team_a_player_id]" class="form-control team_a_player_select" data-pair="'.$i.'" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
                    $content .= '<option value="">-- Виберіть гравця --</option>';
                    $player_index = 0;
                    foreach ($lineup_a as $player_id) {
                        $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
                        $sel = ($selected_a == $player_id) ? 'selected' : '';
                        $label = !empty($team_a_labels[$player_index]) ? $team_a_labels[$player_index].' - ' : '';
                        $content .= '<option value="'.$player_id.'" '.$sel.' data-label="'.$team_a_labels[$player_index].'">'.$label.$player_name.'</option>';
                        $player_index++;
                    }
                    $content .= '</select></td>';
                    $content .= '<td class="text-center">vs</td>';
                    $content .= '<td><select name="pairs['.$i.'][team_b_player_id]" class="form-control team_b_player_select" data-pair="'.$i.'" '.(!empty($lineups_locked) ? 'disabled' : '').'>';
                    $content .= '<option value="">-- Виберіть гравця --</option>';
                    $player_index = 0;
                    foreach ($lineup_b as $player_id) {
                        $player_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'name');
                        $sel = ($selected_b == $player_id) ? 'selected' : '';
                        $label = !empty($team_b_labels[$player_index]) ? $team_b_labels[$player_index].' - ' : '';
                        $content .= '<option value="'.$player_id.'" '.$sel.' data-label="'.$team_b_labels[$player_index].'">'.$label.$player_name.'</option>';
                        $player_index++;
                    }
                    $content .= '</select></td>';
                    $content .= '<td class="text-center"><small id="config_'.$i.'">'.$pair_config.'</small></td>';
                    $content .= '</tr>';
                }
                
                $content .= '</tbody>';
                $content .= '</table>';
                $content .= '</div>';
                
                if (empty($lineups_locked)) {
                    $content .= '<button type="submit" class="btn btn-success">Зберегти пари</button>';
                    $content .= '<button type="button" class="btn btn-secondary ms-2" onclick="autoPairsAll()">Автоматично всі 5 пар: A-Y, B-X, C-Z, A-X, B-Y</button>';
                } else {
                    $content .= '<span class="badge bg-warning">Пари заблоковані (після СТАРТ)</span>';
                }
                
                $content .= '</form>';
            } else {
                $content .= '<div class="alert alert-warning">Кількість гравців у командах не співпадає (A: '.count($lineup_a).', B: '.count($lineup_b).')</div>';
            }
        }
        
        $content .= '</div>'; // card-body
        $content .= '</div>'; // card
        $content .= '</div>'; // col-12
        $content .= '</div>'; // row

        // JavaScript для автоматического формирования пар
        $js = '
        <script>
        function autoPairsFourth() {
            // Для 4-й пары: A-X (1-й игрок команды A vs 1-й игрок команды B)
            var form = document.getElementById("pairs_form");
            if (!form) return;
            
            var selectA4 = form.querySelector("select[name=\'pairs[4][team_a_player_id]\']");
            var selectB4 = form.querySelector("select[name=\'pairs[4][team_b_player_id]\']");
            var selectA1 = form.querySelector("select[name=\'pairs[1][team_a_player_id]\']");
            var selectB2 = form.querySelector("select[name=\'pairs[2][team_b_player_id]\']"); // X был во 2-й паре
            
            if (selectA4 && selectB4 && selectA1 && selectB2) {
                // 4-я пара A-X: берем игрока A (1-й игрок команды A из 1-й пары) 
                // и игрока X (1-й игрок команды B, он был во 2-й паре)
                if (selectA1.value) selectA4.value = selectA1.value; // A (1-й игрок команды A)
                
                // X - это 1-й игрок команды B, он был во 2-й паре (там были B vs X, значит X - это 2-й элемент, но это первый игрок команды B)
                // Получаем опции из первого селекта команды B (1-я пара), берем 1-й элемент (индекс 0) - это X
                var selectB1 = form.querySelector("select[name=\'pairs[1][team_b_player_id]\']");
                if (selectB1) {
                    var optionsB = Array.from(selectB1.options).filter(function(opt) { return opt.value !== ""; });
                    // Во 2-й паре был B vs X, значит X был во 2-м селекте команды B
                    // Но X - это 1-й игрок команды B, значит он должен быть 1-м в списке опций
                    // Проверяем, что во 2-й паре был выбран именно X
                    if (selectB2.value) {
                        selectB4.value = selectB2.value; // X из 2-й пары
                    } else if (optionsB.length > 0 && optionsB[0].value) {
                        // Если во 2-й паре еще не выбран, берем 1-й игрока команды B из состава
                        selectB4.value = optionsB[0].value; // X (1-й игрок команды B)
                    }
                }
            }
        }
        
        function autoPairsFifth() {
            // Для 5-й пары: B-Y (2-й игрок команды A vs 2-й игрок команды B)
            var form = document.getElementById("pairs_form");
            if (!form) return;
            
            var selectA5 = form.querySelector("select[name=\'pairs[5][team_a_player_id]\']");
            var selectB5 = form.querySelector("select[name=\'pairs[5][team_b_player_id]\']");
            var selectA2 = form.querySelector("select[name=\'pairs[2][team_a_player_id]\']");
            var selectB1 = form.querySelector("select[name=\'pairs[1][team_b_player_id]\']"); // Y был в 1-й паре (2-й по счету)
            
            if (selectA5 && selectB5 && selectA2 && selectB1) {
                // 5-я пара B-Y: берем игрока B (2-й игрок команды A из 2-й пары) 
                // и игрока Y (2-й игрок команды B, он был в 1-й паре как 2-й элемент)
                if (selectA2.value) selectA5.value = selectA2.value; // B (2-й игрок команды A)
                
                // Y - это 2-й игрок команды B, он был в 1-й паре (там были A vs Y, значит Y - это 2-й элемент)
                // Получаем опции из selectB1, берем 2-й элемент (индекс 1)
                var optionsB = Array.from(selectB1.options).filter(function(opt) { return opt.value !== ""; });
                if (optionsB.length > 1 && optionsB[1].value) {
                    selectB5.value = optionsB[1].value; // Y (2-й игрок команды B)
                }
            }
        }
        
        function autoPairs() {
            var form = document.getElementById("pairs_form");
            if (!form) return;
            
            // Получаем все селекты пар
            var selectsA = Array.from(form.querySelectorAll("select[name*=\'[team_a_player_id]\']")).filter(function(s) {
                return s.closest("tr").style.display !== "none";
            });
            var selectsB = Array.from(form.querySelectorAll("select[name*=\'[team_b_player_id]\']")).filter(function(s) {
                return s.closest("tr").style.display !== "none";
            });
            
            // Первые 3 пары: A-Y, B-X, C-Z
            // Все селекты пары содержат одинаковые опции (весь состав команды),
            // но нам нужно выбрать по позициям (A, B, C для команды A и X, Y, Z для команды B)
            var pair_configs = [
                {a_label: "A", b_label: "Y"}, // Пара 1: A-Y (A vs Y)
                {a_label: "B", b_label: "X"}, // Пара 2: B-X (B vs X)
                {a_label: "C", b_label: "Z"}  // Пара 3: C-Z (C vs Z)
            ];
            
            // Используем первый селект каждой команды как источник опций (они одинаковые во всех парах)
            var firstSelectA = selectsA.length > 0 ? selectsA[0] : null;
            var firstSelectB = selectsB.length > 0 ? selectsB[0] : null;
            
            if (!firstSelectA || !firstSelectB) return;
            
            // Находим опции по data-label для каждой позиции
            var optionsByLabelA = {};
            var optionsByLabelB = {};
            
            Array.from(firstSelectA.options).forEach(function(opt) {
                if (opt.value !== "" && opt.hasAttribute("data-label")) {
                    var label = opt.getAttribute("data-label");
                    optionsByLabelA[label] = opt.value;
                }
            });
            
            Array.from(firstSelectB.options).forEach(function(opt) {
                if (opt.value !== "" && opt.hasAttribute("data-label")) {
                    var label = opt.getAttribute("data-label");
                    optionsByLabelB[label] = opt.value;
                }
            });
            
            // Устанавливаем пары по позициям
            for (var i = 0; i < Math.min(selectsA.length, selectsB.length, 3); i++) {
                var config = pair_configs[i];
                
                // Находим value для позиции A/B/C из первого селекта команды A
                if (optionsByLabelA[config.a_label] && selectsA[i]) {
                    selectsA[i].value = optionsByLabelA[config.a_label];
                }
                
                // Находим value для позиции X/Y/Z из первого селекта команды B
                if (optionsByLabelB[config.b_label] && selectsB[i]) {
                    selectsB[i].value = optionsByLabelB[config.b_label];
                }
            }
        }
        
        function autoPairsAll() {
            // Автоматически заполняем все 5 пар:
            // Пара 1: A - Y
            // Пара 2: B - X
            // Пара 3: C - Z
            // Пара 4: A - X
            // Пара 5: B - Y
            var form = document.getElementById("pairs_form");
            if (!form) return;
            
            // Получаем все селекты пар
            var selectsA = Array.from(form.querySelectorAll("select[name*=\'[team_a_player_id]\']"));
            var selectsB = Array.from(form.querySelectorAll("select[name*=\'[team_b_player_id]\']"));
            
            // Используем первый селект каждой команды как источник опций
            var firstSelectA = selectsA.length > 0 ? selectsA[0] : null;
            var firstSelectB = selectsB.length > 0 ? selectsB[0] : null;
            
            if (!firstSelectA || !firstSelectB) return;
            
            // Находим опции по data-label для каждой позиции
            var optionsByLabelA = {};
            var optionsByLabelB = {};
            
            Array.from(firstSelectA.options).forEach(function(opt) {
                if (opt.value !== "" && opt.hasAttribute("data-label")) {
                    var label = opt.getAttribute("data-label");
                    optionsByLabelA[label] = opt.value;
                }
            });
            
            Array.from(firstSelectB.options).forEach(function(opt) {
                if (opt.value !== "" && opt.hasAttribute("data-label")) {
                    var label = opt.getAttribute("data-label");
                    optionsByLabelB[label] = opt.value;
                }
            });
            
            // Конфигурация всех 5 пар
            var all_pairs_config = [
                {a_label: "A", b_label: "Y"}, // Пара 1: A-Y
                {a_label: "B", b_label: "X"}, // Пара 2: B-X
                {a_label: "C", b_label: "Z"}, // Пара 3: C-Z
                {a_label: "A", b_label: "X"}, // Пара 4: A-X
                {a_label: "B", b_label: "Y"}  // Пара 5: B-Y
            ];
            
            // Устанавливаем все 5 пар
            for (var i = 0; i < Math.min(selectsA.length, selectsB.length, 5); i++) {
                var config = all_pairs_config[i];
                
                if (selectsA[i] && optionsByLabelA[config.a_label]) {
                    selectsA[i].value = optionsByLabelA[config.a_label];
                }
                
                if (selectsB[i] && optionsByLabelB[config.b_label]) {
                    selectsB[i].value = optionsByLabelB[config.b_label];
                }
            }
        }
        
        function autoPairsFifth() {
            // Для 5-й пары: B-Y (2-й игрок команды A vs 2-й игрок команды B)
            var form = document.getElementById("pairs_form");
            if (!form) return;
            
            var selectA5 = form.querySelector("select[name=\'pairs[5][team_a_player_id]\']");
            var selectB5 = form.querySelector("select[name=\'pairs[5][team_b_player_id]\']");
            var selectA2 = form.querySelector("select[name=\'pairs[2][team_a_player_id]\']");
            var selectB2 = form.querySelector("select[name=\'pairs[2][team_b_player_id]\']");
            
            if (selectA5 && selectB5 && selectA2 && selectB2) {
                // 5-я пара B-Y: берем игрока B (2-й игрок команды A из 2-й пары) и игрока Y (2-й игрок команды B из 1-й пары, так как Y - это 2-й в первой паре)
                // Но на самом деле Y - это 2-й игрок команды B по порядку из состава
                // Получаем из 2-й пары: там был B (2-й A) и X (1-й B), значит Y - это нужно найти как 2-й из состава B
                var selectB1 = form.querySelector("select[name=\'pairs[1][team_b_player_id]\']");
                if (selectA2.value) selectA5.value = selectA2.value; // B (2-й игрок команды A)
                // Y - это 2-й игрок команды B, он был в 1-й паре как второй элемент
                // Находим опцию с label="Y" в selectB1 или берем 2-й вариант из списка команды B
                if (selectB1) {
                    var optionsB = Array.from(selectB1.options).filter(function(opt) { return opt.value !== ""; });
                    if (optionsB.length > 1 && optionsB[1].value) {
                        selectB5.value = optionsB[1].value; // Y (2-й игрок команды B)
                    }
                }
            }
        }
        
        // Функция для предотвращения дублей игроков в выпадающих списках
        function preventPlayerDuplicates() {
            // Обработчики для команды A
            var selectA = document.getElementById("player_a_pos");
            var selectB = document.getElementById("player_b_pos");
            var selectC = document.getElementById("player_c_pos");
            
            function updateTeamALists(changedSelect, otherSelect1, otherSelect2) {
                var selectedValue = changedSelect.value;
                
                // Восстанавливаем все опции в других списках
                otherSelect1.querySelectorAll("option").forEach(function(opt) {
                    if (opt.value && opt.value !== "") {
                        opt.style.display = "";
                        opt.disabled = false;
                    }
                });
                otherSelect2.querySelectorAll("option").forEach(function(opt) {
                    if (opt.value && opt.value !== "") {
                        opt.style.display = "";
                        opt.disabled = false;
                    }
                });
                
                // Скрываем выбранного игрока в других списках
                if (selectedValue) {
                    otherSelect1.querySelectorAll("option[value=\'" + selectedValue + "\']").forEach(function(opt) {
                        if (opt.value === selectedValue) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect2.querySelectorAll("option[value=\'" + selectedValue + "\']").forEach(function(opt) {
                        if (opt.value === selectedValue) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
                
                // Если в других списках уже выбраны игроки, скрываем их тоже
                if (otherSelect1.value) {
                    changedSelect.querySelectorAll("option[value=\'" + otherSelect1.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect1.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect2.querySelectorAll("option[value=\'" + otherSelect1.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect1.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
                if (otherSelect2.value) {
                    changedSelect.querySelectorAll("option[value=\'" + otherSelect2.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect2.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect1.querySelectorAll("option[value=\'" + otherSelect2.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect2.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
            }
            
            if (selectA && selectB && selectC) {
                selectA.addEventListener("change", function() {
                    updateTeamALists(selectA, selectB, selectC);
                });
                selectB.addEventListener("change", function() {
                    updateTeamALists(selectB, selectA, selectC);
                });
                selectC.addEventListener("change", function() {
                    updateTeamALists(selectC, selectA, selectB);
                });
                
                // Инициализация при загрузке
                updateTeamALists(selectA, selectB, selectC);
                updateTeamALists(selectB, selectA, selectC);
                updateTeamALists(selectC, selectA, selectB);
            }
            
            // Обработчики для команды B
            var selectY = document.getElementById("player_y_pos");
            var selectX = document.getElementById("player_x_pos");
            var selectZ = document.getElementById("player_z_pos");
            
            function updateTeamBLists(changedSelect, otherSelect1, otherSelect2) {
                var selectedValue = changedSelect.value;
                
                // Восстанавливаем все опции в других списках
                otherSelect1.querySelectorAll("option").forEach(function(opt) {
                    if (opt.value && opt.value !== "") {
                        opt.style.display = "";
                        opt.disabled = false;
                    }
                });
                otherSelect2.querySelectorAll("option").forEach(function(opt) {
                    if (opt.value && opt.value !== "") {
                        opt.style.display = "";
                        opt.disabled = false;
                    }
                });
                
                // Скрываем выбранного игрока в других списках
                if (selectedValue) {
                    otherSelect1.querySelectorAll("option[value=\'" + selectedValue + "\']").forEach(function(opt) {
                        if (opt.value === selectedValue) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect2.querySelectorAll("option[value=\'" + selectedValue + "\']").forEach(function(opt) {
                        if (opt.value === selectedValue) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
                
                // Если в других списках уже выбраны игроки, скрываем их тоже
                if (otherSelect1.value) {
                    changedSelect.querySelectorAll("option[value=\'" + otherSelect1.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect1.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect2.querySelectorAll("option[value=\'" + otherSelect1.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect1.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
                if (otherSelect2.value) {
                    changedSelect.querySelectorAll("option[value=\'" + otherSelect2.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect2.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                    otherSelect1.querySelectorAll("option[value=\'" + otherSelect2.value + "\']").forEach(function(opt) {
                        if (opt.value === otherSelect2.value) {
                            opt.style.display = "none";
                            opt.disabled = true;
                        }
                    });
                }
            }
            
            if (selectY && selectX && selectZ) {
                selectY.addEventListener("change", function() {
                    updateTeamBLists(selectY, selectX, selectZ);
                });
                selectX.addEventListener("change", function() {
                    updateTeamBLists(selectX, selectY, selectZ);
                });
                selectZ.addEventListener("change", function() {
                    updateTeamBLists(selectZ, selectY, selectX);
                });
                
                // Инициализация при загрузке
                updateTeamBLists(selectY, selectX, selectZ);
                updateTeamBLists(selectX, selectY, selectZ);
                updateTeamBLists(selectZ, selectY, selectX);
            }
        }
        
        function swapTeams() {
            var teamAId = document.getElementById("team_a_current_id").value;
            var teamBId = document.getElementById("team_b_current_id").value;
            var teamAName = document.getElementById("team_a_current_name").value;
            var teamBName = document.getElementById("team_b_current_name").value;
            
            // Получаем текущие выбранные игроки из команды A (позиции A, B, C)
            var selectA = document.getElementById("player_a_pos");
            var selectB = document.getElementById("player_b_pos");
            var selectC = document.getElementById("player_c_pos");
            var playerA_val = selectA ? selectA.value : "";
            var playerB_val = selectB ? selectB.value : "";
            var playerC_val = selectC ? selectC.value : "";
            
            // Получаем текущие выбранные игроки из команды B (позиции Y, X, Z)
            var selectY = document.getElementById("player_y_pos");
            var selectX = document.getElementById("player_x_pos");
            var selectZ = document.getElementById("player_z_pos");
            var playerY_val = selectY ? selectY.value : "";
            var playerX_val = selectX ? selectX.value : "";
            var playerZ_val = selectZ ? selectZ.value : "";
            
            // Сохраняем текущие значения для восстановления после смены команд
            var swappedData = {
                team_a_players: [playerA_val, playerB_val, playerC_val],
                team_b_players: [playerY_val, playerX_val, playerZ_val],
                team_a_id: teamAId,
                team_b_id: teamBId
            };
            sessionStorage.setItem("swap_teams_data", JSON.stringify(swappedData));
            
            // Получаем параметры из URL hash
            var hash = window.location.hash;
            var hashParts = hash.split("-");
            var paramsPart = hashParts[hashParts.length - 1] || "";
            var params = new URLSearchParams(paramsPart);
            
            // Получаем текущие параметры
            var game_id = params.get("id") || "";
            var turnir_id = params.get("turnir_id") || "";
            var etap_id = params.get("etap_id") || "";
            var league_id = params.get("league_id") || "";
            
            // Формируем новый hash с параметром swap_teams
            // Правильный формат для hash: #module-action-param1=value1&param2=value2
            var params = [];
            if (game_id) params.push("id=" + encodeURIComponent(game_id));
            if (turnir_id) params.push("turnir_id=" + encodeURIComponent(turnir_id));
            if (etap_id) params.push("etap_id=" + encodeURIComponent(etap_id));
            if (league_id) params.push("league_id=" + encodeURIComponent(league_id));
            params.push("swap_teams=1");
            
            var newHash = "#reiting-team_lineups-" + params.join("&");
            
            // Устанавливаем hash и вызываем send_ajax БЕЗ параметров
            // send_ajax без параметров автоматически возьмет href из window.location.hash
            window.location.hash = newHash;
            
            // Вызываем send_ajax БЕЗ параметров, чтобы он взял hash из window.location.hash
            if (typeof send_ajax === "function") {
                try {
                    // send_ajax с пустой строкой берет hash из window.location.hash
                    send_ajax("");
                } catch(e) {
                    console.error("Error calling send_ajax:", e);
                    // Если ошибка, просто перезагружаем страницу
                    window.location.reload();
                }
            } else {
                // Если send_ajax недоступен, просто перезагружаем
                window.location.reload();
            }
        }
        
        // Функция для восстановления выбранных игроков после смены команд
        function restoreSwappedPlayers() {
            var swapData = sessionStorage.getItem("swap_teams_data");
            if (!swapData) return;
            
            try {
                var data = JSON.parse(swapData);
                
                // После смены команд:
                // - Игроки бывшей команды A (A, B, C) должны перейти в новую команду B (справа, Y, X, Z)
                // - Игроки бывшей команды B (Y, X, Z) должны перейти в новую команду A (слева, A, B, C)
                
                // Для команды A (теперь содержит игроков бывшей команды B)
                // Устанавливаем игроков Y, X, Z из бывшей команды B
                var selectA_new = document.getElementById("player_a_pos");
                var selectB_new = document.getElementById("player_b_pos");
                var selectC_new = document.getElementById("player_c_pos");
                
                if (selectA_new && data.team_b_players[0]) {
                    // Y (бывшая команда B, позиция 0) -> A (новая команда A, позиция 0)
                    var optionY = selectA_new.querySelector("option[value=\'" + data.team_b_players[0] + "\']");
                    if (optionY && !optionY.disabled) {
                        selectA_new.value = data.team_b_players[0];
                        selectA_new.dispatchEvent(new Event("change")); // Триггерим событие для обновления других списков
                    }
                }
                if (selectB_new && data.team_b_players[1]) {
                    // X (бывшая команда B, позиция 1) -> B (новая команда A, позиция 1)
                    var optionX = selectB_new.querySelector("option[value=\'" + data.team_b_players[1] + "\']");
                    if (optionX && !optionX.disabled) {
                        selectB_new.value = data.team_b_players[1];
                        selectB_new.dispatchEvent(new Event("change"));
                    }
                }
                if (selectC_new && data.team_b_players[2]) {
                    // Z (бывшая команда B, позиция 2) -> C (новая команда A, позиция 2)
                    var optionZ = selectC_new.querySelector("option[value=\'" + data.team_b_players[2] + "\']");
                    if (optionZ && !optionZ.disabled) {
                        selectC_new.value = data.team_b_players[2];
                        selectC_new.dispatchEvent(new Event("change"));
                    }
                }
                
                // Для команды B (теперь содержит игроков бывшей команды A)
                // Устанавливаем игроков A, B, C из бывшей команды A
                var selectY_new = document.getElementById("player_y_pos");
                var selectX_new = document.getElementById("player_x_pos");
                var selectZ_new = document.getElementById("player_z_pos");
                
                if (selectY_new && data.team_a_players[0]) {
                    // A (бывшая команда A, позиция 0) -> Y (новая команда B, позиция 0)
                    var optionA = selectY_new.querySelector("option[value=\'" + data.team_a_players[0] + "\']");
                    if (optionA && !optionA.disabled) {
                        selectY_new.value = data.team_a_players[0];
                        selectY_new.dispatchEvent(new Event("change"));
                    }
                }
                if (selectX_new && data.team_a_players[1]) {
                    // B (бывшая команда A, позиция 1) -> X (новая команда B, позиция 1)
                    var optionB = selectX_new.querySelector("option[value=\'" + data.team_a_players[1] + "\']");
                    if (optionB && !optionB.disabled) {
                        selectX_new.value = data.team_a_players[1];
                        selectX_new.dispatchEvent(new Event("change"));
                    }
                }
                if (selectZ_new && data.team_a_players[2]) {
                    // C (бывшая команда A, позиция 2) -> Z (новая команда B, позиция 2)
                    var optionC = selectZ_new.querySelector("option[value=\'" + data.team_a_players[2] + "\']");
                    if (optionC && !optionC.disabled) {
                        selectZ_new.value = data.team_a_players[2];
                        selectZ_new.dispatchEvent(new Event("change"));
                    }
                }
                
                // Очищаем sessionStorage
                sessionStorage.removeItem("swap_teams_data");
                
                // Обновляем предотвращение дублей после установки значений
                if (typeof preventPlayerDuplicates === "function") {
                    setTimeout(function() {
                        preventPlayerDuplicates();
                    }, 200);
                }
            } catch(e) {
                console.error("Error restoring swapped players:", e);
                sessionStorage.removeItem("swap_teams_data");
            }
        }

        jQuery(document).ready(function($) {
            // Убираем swap_teams из hash после выполнения смены, чтобы не повторять swap при каждом сохранении
            if (window.location.hash.indexOf("swap_teams=1") !== -1) {
                var hash = window.location.hash;
                var lastDash = hash.lastIndexOf("-");
                if (lastDash !== -1) {
                    var base = hash.substring(0, lastDash + 1);
                    var paramsPart = hash.substring(lastDash + 1);
                    var params = new URLSearchParams(paramsPart);
                    params.delete("swap_teams");
                    var newParams = params.toString();
                    var newHash = base + newParams;
                    if (newHash.endsWith("-")) {
                        newHash = newHash.slice(0, -1);
                    }
                    window.history.replaceState(null, "", newHash);
                }
            }

            // Восстанавливаем выбранных игроков после смены команд (если была)
            if (typeof restoreSwappedPlayers === "function") {
                restoreSwappedPlayers();
            }
            
            // Инициализируем предотвращение дублей при загрузке страницы
            if (typeof preventPlayerDuplicates === "function") {
                preventPlayerDuplicates();
            }
            
            $(".ajax_form").on("submit", function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                
                if (form.attr("id") === "lineup_form_a" || form.attr("id") === "lineup_form_b") {
                    // Для команды A: собираем значения из select с id player_a_pos, player_b_pos, player_c_pos
                    // Для команды B: собираем значения из select с id player_y_pos, player_x_pos, player_z_pos
                    var players = [];
                    if (form.attr("id") === "lineup_form_a") {
                        var selectA = document.getElementById("player_a_pos");
                        var selectB = document.getElementById("player_b_pos");
                        var selectC = document.getElementById("player_c_pos");
                        if (selectA && selectA.value) players.push(selectA.value);
                        if (selectB && selectB.value) players.push(selectB.value);
                        if (selectC && selectC.value) players.push(selectC.value);
                    } else if (form.attr("id") === "lineup_form_b") {
                        var selectY = document.getElementById("player_y_pos");
                        var selectX = document.getElementById("player_x_pos");
                        var selectZ = document.getElementById("player_z_pos");
                        if (selectY && selectY.value) players.push(selectY.value);
                        if (selectX && selectX.value) players.push(selectX.value);
                        if (selectZ && selectZ.value) players.push(selectZ.value);
                    }
                    formData.delete("players[]");
                    for (var j = 0; j < players.length; j++) {
                        formData.append("players[]", players[j]);
                    }
                }
                
                // Убеждаемся, что save_lineup или save_pairs передается в FormData
                if (form.attr("id") === "lineup_form_a" || form.attr("id") === "lineup_form_b") {
                    // Проверяем, есть ли save_lineup в форме
                    if (!formData.has("save_lineup")) {
                        formData.append("save_lineup", "1");
                    }
                } else if (form.attr("id") === "pairs_form") {
                    // Для формы пар проверяем, что save_pairs есть
                    if (!formData.has("save_pairs")) {
                        formData.append("save_pairs", "1");
                    }
                    // Убеждаемся, что данные пар правильно передаются
                    // FormData автоматически собирает данные из select элементов с name="pairs[1][team_a_player_id]" и т.д.
                    // Но нужно проверить, что все пары передаются
                    var pairsData = {};
                    form.find("select[name*=\'[team_a_player_id]\']").each(function() {
                        var name = $(this).attr("name");
                        var match = name.match(/pairs\[(\d+)\]\[team_a_player_id\]/);
                        if (match && match[1]) {
                            var pairNum = match[1];
                            if (!pairsData[pairNum]) pairsData[pairNum] = {};
                            pairsData[pairNum].team_a_player_id = $(this).val();
                        }
                    });
                    form.find("select[name*=\'[team_b_player_id]\']").each(function() {
                        var name = $(this).attr("name");
                        var match = name.match(/pairs\[(\d+)\]\[team_b_player_id\]/);
                        if (match && match[1]) {
                            var pairNum = match[1];
                            if (!pairsData[pairNum]) pairsData[pairNum] = {};
                            pairsData[pairNum].team_b_player_id = $(this).val();
                        }
                    });
                    console.log("Pairs form data:", pairsData);
                }
                
                // Важно: добавляем ajax_method=1, чтобы система определила запрос как AJAX
                if (!formData.has("ajax_method")) {
                    formData.append("ajax_method", "1");
                }
                
                // Используем index.php или текущий URL для AJAX запроса
                var ajaxUrl = window.location.pathname;
                if (ajaxUrl.indexOf("index.php") === -1) {
                    ajaxUrl = "index.php";
                }
                
                // Добавляем логирование для отладки
                console.log("Sending form data:", {
                    formId: form.attr("id"),
                    hasSaveLineup: formData.has("save_lineup"),
                    hasSavePairs: formData.has("save_pairs"),
                    players: players
                });
                
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    success: function(response) {
                        console.log("AJAX response:", response);
                        if (response && response.content) {
                            // Обновляем контент через систему content_return
                            if (typeof content_return === "function") {
                                window.json = response;
                                content_return();
                            } else {
                                // Если content_return недоступна, перезагружаем страницу
                                location.reload();
                            }
                        } else if (response && response.message_user) {
                            // Показываем сообщение, если оно есть
                            if (typeof window_mess === "function") {
                                window_mess(response.message_user);
                            } else {
                                alert(response.message_user);
                            }
                            // Перезагружаем страницу для обновления данных после задержки
                            setTimeout(function() {
                                // Если есть hash с параметрами, перезагружаем с ним
                                var currentHash = window.location.hash;
                                if (currentHash) {
                                    window.location.hash = currentHash;
                                    location.reload();
                                } else {
                                    location.reload();
                                }
                            }, 1500);
                        } else {
                            // Если нет контента и нет сообщения, просто перезагружаем
                            console.warn("No content in response, reloading page");
                            location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        console.error("Response text:", xhr.responseText);
                        alert("Помилка збереження: " + error + ". Спробуйте ще раз.");
                        // Не перезагружаем при ошибке, чтобы пользователь мог попробовать снова
                    }
                });
            });
        });
        </script>
        ';
        
        // Добавляем скрипт для скрытия панели фильтров - только для страницы team_lineups
        $hide_filters_js = '
        <script>
        (function() {
            // Проверяем, что мы на странице team_lineups
            function isTeamLineupsPage() {
                return document.getElementById("team-lineups-container") !== null ||
                       window.location.hash.indexOf("team_lineups") !== -1 ||
                       document.querySelector("#team-lineups-container") !== null;
            }
            
            function hideFilterPanel() {
                // Выполняем скрытие только если мы на странице team_lineups
                if (!isTeamLineupsPage()) {
                    return;
                }
                // Функция для рекурсивного скрытия элемента и его родителей
                function hideElementAndParents(selector) {
                    var elements = document.querySelectorAll(selector);
                    elements.forEach(function(el) {
                        if (el) {
                            // Скрываем сам элемент
                            el.style.display = "none";
                            el.style.visibility = "hidden";
                            el.style.height = "0";
                            el.style.overflow = "hidden";
                            el.style.margin = "0";
                            el.style.padding = "0";
                            // Скрываем родительские элементы
                            var parent = el.parentElement;
                            while (parent && parent.tagName !== "BODY" && parent.tagName !== "HTML") {
                                // Проверяем, есть ли в родителе другие важные элементы
                                var hasOtherElements = false;
                                var children = parent.children;
                                for (var i = 0; i < children.length; i++) {
                                    if (children[i] !== el && 
                                        !children[i].matches("input[name=\'fio_search\'], select[name=\'groups\'], .speedsearch")) {
                                        hasOtherElements = true;
                                        break;
                                    }
                                }
                                // Если в родителе только фильтры, скрываем его тоже
                                if (!hasOtherElements && (parent.tagName === "TR" || parent.tagName === "TD" || parent.tagName === "DIV")) {
                                    parent.style.display = "none";
                                    parent.style.height = "0";
                                }
                                parent = parent.parentElement;
                            }
                        }
                    });
                }
                
                // Скрываем все элементы фильтров
                hideElementAndParents("input[name=\'fio_search\']");
                hideElementAndParents("input[id=\'search_field_games\']");
                hideElementAndParents("select#search_field_games_select");
                hideElementAndParents("select[name=\'groups\']");
                hideElementAndParents("select[id=\'etap-chosen-select\']");
                hideElementAndParents(".speedsearch");
                hideElementAndParents(".speedsearch_panel");
                hideElementAndParents(".filter_panel");
                // Скрываем вкладки статусов игр
                hideElementAndParents("a[href*=\'filter=\']");
                hideElementAndParents("a[href*=\'#reiting-list\'][href*=\'filter=\']");
                hideElementAndParents(".filter_status_tabs");
                hideElementAndParents(".game_status_filters");
                hideElementAndParents(".Line_Menu");
                hideElementAndParents(".bigMenu");
                hideElementAndParents(".active_filter_game");
                // Скрываем выпадающий список этапов
                hideElementAndParents(".col_flo_left");
                hideElementAndParents(".input__wrapper");
                hideElementAndParents(".chosen-container");
                hideElementAndParents("#etap-chosen-select");
                
                // jQuery подход
                if (typeof jQuery !== "undefined") {
                    // Скрываем поиск и группы
                    jQuery("input[name=\'fio_search\'], input[id=\'search_field_games\'], select#search_field_games_select").closest("tr, td, div, form, table, .col_flo_left, .input__wrapper").hide();
                    jQuery("select[name=\'groups\'], select[id=\'etap-chosen-select\']").closest("tr, td, div, form, table, .chosen-container").hide();
                    jQuery(".speedsearch").closest("tr, td, div, form, table").hide();
                    jQuery(".speedsearch_panel").hide();
                    jQuery(".filter_panel").hide();
                    // Скрываем вкладки статусов игр
                    jQuery("a[href*=\'filter=\'], a[href*=\'#reiting-list\'][href*=\'filter=\']").closest("tr, td, div, span, .Line_Menu").hide();
                    jQuery(".filter_status_tabs").hide();
                    jQuery(".game_status_filters").hide();
                    jQuery(".Line_Menu").hide();
                    jQuery(".bigMenu").hide();
                    // Скрываем выпадающий список этапов
                    jQuery(".col_flo_left, .input__wrapper, .chosen-container").hide();
                    // Скрываем всю строку таблицы, содержащую фильтры
                    jQuery("tr:has(input[name=\'fio_search\']), tr:has(input[id=\'search_field_games\']), tr:has(select#search_field_games_select)").hide();
                    jQuery("tr:has(select[name=\'groups\']), tr:has(select[id=\'etap-chosen-select\'])").hide();
                    jQuery("tr:has(a[href*=\'filter=\'])").hide();
                    // Скрываем всю таблицу submenu_list, если она содержит только фильтры
                    jQuery("table.submenu_list").each(function() {
                        var $table = jQuery(this);
                        var $rows = $table.find("tr");
                        var allHidden = true;
                        $rows.each(function() {
                            var $row = jQuery(this);
                            if (!$row.is(":hidden") && $row.find("input, select, a[href*=\'filter=\']").length > 0) {
                                $row.hide();
                            } else if (!$row.is(":hidden")) {
                                allHidden = false;
                            }
                        });
                        if (allHidden || $rows.filter(":visible").length === 0) {
                            $table.hide();
                        }
                    });
                    // Дополнительно: скрываем все элементы с текстом фильтров
                    jQuery("a:contains(\'Всі ігри\'), a:contains(\'Не розпочато\'), a:contains(\'В процесі\'), a:contains(\'Завершені\')").closest("tr, td, div, span").hide();
                }
            }
            
            // Скрываем сразу, только если на странице team_lineups
            if (isTeamLineupsPage()) {
                hideFilterPanel();
                
                // Скрываем после загрузки DOM
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", function() {
                        if (isTeamLineupsPage()) hideFilterPanel();
                    });
                } else {
                    setTimeout(function() {
                        if (isTeamLineupsPage()) hideFilterPanel();
                    }, 0);
                }
                
                // Скрываем после AJAX обновлений
                if (typeof jQuery !== "undefined") {
                    jQuery(document).ready(function() {
                        if (isTeamLineupsPage()) {
                            hideFilterPanel();
                            setTimeout(hideFilterPanel, 100);
                            setTimeout(hideFilterPanel, 500);
                        }
                    });
                    jQuery(document).ajaxComplete(function() {
                        if (isTeamLineupsPage()) {
                            setTimeout(hideFilterPanel, 100);
                            setTimeout(hideFilterPanel, 500);
                        }
                    });
                }
                
                // Используем MutationObserver для отслеживания изменений DOM
                // Но только если мы на странице team_lineups
                if (typeof MutationObserver !== "undefined") {
                    var observer = new MutationObserver(function(mutations) {
                        if (isTeamLineupsPage()) {
                            hideFilterPanel();
                        }
                    });
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }
            }
        })();
        </script>';

        $this->content = $content . $js . $hide_filters_js;
        $this->subMenu = array(
            'back' => array('module' => 'reiting', 'action' => 'list', 'post' => 'turnir_id='.$turnir_id.'&league_id='.$league_id.(!empty($etap_id) ? '&etap_id='.$etap_id : ''))
        );
        // Очищаем subMenu2, чтобы убрать панель фильтров
        $this->subMenu2 = array();
    }
    
    function getSubMenu2() {
        // Возвращаем пустой массив, чтобы панель фильтров не выводилась
        return array();
    }
    
    function getContent() {
        return !empty($this->content) ? $this->content : '<div class="container-fluid"><div class="alert alert-danger">Помилка: контент не сформовано</div></div>';
    }
    
    function getSubMneu() {
        return $this->subMenu;
    }
    
    function getSubMenu() {
        return $this->subMenu;
    }
    
    function getJavaScript() {
        return $this->Java_script;
    }
}
?>
