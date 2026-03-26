<?php
/**
 * Обработка начала игры для командных лиг
 * Создает игры в bs_reiting на основе пар из bs_team_pairs
 */

class start_team_gameAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';
    protected $turnir_id = '';
    protected $etap_id = '';
    protected $league_id = '';

    function init()
    {
        $game_id = poste('id');
        $this->turnir_id = poste('turnir_id');
        $this->etap_id = poste('etap_id');
        $this->league_id = poste('league_id');

        if (empty($game_id) || empty($this->turnir_id) || empty($this->etap_id)) {
            wLog('ERROR: Missing required parameters - game_id: '.$game_id.', turnir_id: '.$this->turnir_id.', etap_id: '.$this->etap_id);
            window_mess('Помилка: не вказано необхідні параметри');
            $this->show();
            return;
        }

        $game = db_row('SELECT * FROM `'.T_REITING.'` WHERE id='.$game_id);
        if (empty($game)) {
            wLog('ERROR: Game not found for game_id: '.$game_id);
            window_mess('Помилка: гру не знайдено');
            $this->show();
            return;
        }

        $is_team_league = 0;
        if (!empty($this->league_id)) {
            $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$this->league_id, 'is_team_league');
        } elseif (!empty($this->turnir_id)) {
            $league_id_from_turnir = db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$this->turnir_id, 'league_id');
            if (!empty($league_id_from_turnir)) {
                $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id_from_turnir, 'is_team_league');
            }
        }

        if (!$is_team_league) {
            $now = date('H:i:s');
            db_query('UPDATE `'.T_REITING.'` SET start_game="'.$now.'" WHERE id='.$game_id);
            window_mess('Гру розпочато!');
            $this->show();
            return;
        }

        $match_id = '';
        $team_a_id = 0;
        $team_b_id = 0;
        
        if (!empty($game['match_id'])) {
            $match_id = $game['match_id'];
            // Получаем team_a_id и team_b_id для обновления командной игры
            $team_a_id = !empty($game['team_a_id']) ? (int)$game['team_a_id'] : 0;
            $team_b_id = !empty($game['team_b_id']) ? (int)$game['team_b_id'] : 0;
        } else {
            // Пытаемся получить team_a_id и team_b_id из полей игры
            $team_a_id = !empty($game['team_a_id']) ? (int)$game['team_a_id'] : 0;
            $team_b_id = !empty($game['team_b_id']) ? (int)$game['team_b_id'] : 0;
            
            // Если team_a_id и team_b_id не заполнены, получаем их из pl_id_1 и pl_id_2
            if (empty($team_a_id) || empty($team_b_id)) {
                if (!empty($game['pl_id_1'])) {
                    $player1 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_1']);
                    if (!empty($player1['is_team']) && $player1['is_team'] == 1) {
                        $team_a_id = (int)$player1['id'];
                    }
                }
                if (!empty($game['pl_id_2'])) {
                    $player2 = db_row('SELECT id, is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$game['pl_id_2']);
                    if (!empty($player2['is_team']) && $player2['is_team'] == 1) {
                        if (empty($team_a_id)) {
                            $team_a_id = (int)$player2['id'];
                        } else {
                            $team_b_id = (int)$player2['id'];
                        }
                    }
                }
            }
            
            if (!empty($team_a_id) && !empty($team_b_id) && $team_a_id > 0 && $team_b_id > 0) {
                $min_team = min($team_a_id, $team_b_id);
                $max_team = max($team_a_id, $team_b_id);
                $match_id = 'match_'.$this->etap_id.'_'.$min_team.'_'.$max_team;
            } else {
                wLog('ERROR: Cannot generate match_id - team_a_id: '.$team_a_id.', team_b_id: '.$team_b_id);
            }
        }

        if (empty($match_id)) {
            wLog('ERROR: match_id is empty after all attempts');
            window_mess('Помилка: не вдалося визначити match_id. Перевірте, чи в грі вказані команди (team_a_id, team_b_id)');
            $this->show();
            return;
        }

        // Получаем только первые 3 пары для создания игр (пары 4 и 5 будут созданы автоматически после завершения 3-й или 4-й игры)
        $pairs_query = 'SELECT * FROM `bs_team_pairs` WHERE match_id="'.addslashes($match_id).'" AND etap_id='.$this->etap_id.' AND pair_number <= 3 ORDER BY pair_number ASC';
        $pairs = db_list($pairs_query);

        if (empty($pairs)) {
            window_mess('Помилка: не знайдено пар для цього матчу. Спочатку сформуйте пари!');
            $this->show();
            return;
        }
        
        // ВАЖНО: Получаем team_a_id и team_b_id из первой пары, чтобы синхронизировать порядок команд
        // в team_game с порядком в bs_team_pairs
        $first_pair = $pairs[0];
        $pairs_team_a_id = (int)$first_pair['team_a_id'];
        $pairs_team_b_id = (int)$first_pair['team_b_id'];
        
        // Обновляем team_a_id и team_b_id из bs_team_pairs для правильного сопоставления
        if ($pairs_team_a_id > 0 && $pairs_team_b_id > 0) {
            $team_a_id = $pairs_team_a_id;
            $team_b_id = $pairs_team_b_id;
            $team_a_name_log = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_a_id, 'name');
            $team_b_name_log = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_b_id, 'name');
            wLog('ДИАГНОСТИКА: Используем team_a_id='.$team_a_id.' ('.$team_a_name_log.'), team_b_id='.$team_b_id.' ('.$team_b_name_log.') из bs_team_pairs');
        }

        // Обновляем командную игру, устанавливая ей match_id для правильной группировки
        // ВАЖНО: Также обновляем pl_id_1 и pl_id_2, чтобы они соответствовали team_a_id и team_b_id
        // Это необходимо, так как при отображении используются pl_id_1 и pl_id_2 для получения имен команд
        // Это нужно сделать ПЕРЕД созданием игр игроков, чтобы сортировка работала правильно
        // ВАЖНО: start_game для командной игры ставим только ПОСЛЕ успешного создания игр пар
        $start_game_time = date('H:i:s');
        
        // ДИАГНОСТИКА: Проверяем текущее состояние командной игры
        $current_team_game = db_row('SELECT id, pl_id_1, pl_id_2, team_a_id, team_b_id, match_id, start_game FROM `'.T_REITING.'` WHERE id='.$game_id);
        if (!empty($current_team_game)) {
            $current_team_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$current_team_game['pl_id_1'], 'name');
            $current_team_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$current_team_game['pl_id_2'], 'name');
            wLog('ДИАГНОСТИКА: Текущее состояние командной игры id='.$game_id.':');
            wLog('  pl_id_1='.$current_team_game['pl_id_1'].' ('.$current_team_a_name.'), pl_id_2='.$current_team_game['pl_id_2'].' ('.$current_team_b_name.')');
            wLog('  team_a_id='.$current_team_game['team_a_id'].', team_b_id='.$current_team_game['team_b_id']);
            wLog('  Должно быть: pl_id_1='.$team_a_id.' ('.$team_a_name_log.'), pl_id_2='.$team_b_id.' ('.$team_b_name_log.')');
        }
        
        if (empty($game['match_id']) && $team_a_id > 0 && $team_b_id > 0) {
            $update_team_game = db_query('UPDATE `'.T_REITING.'` SET match_id="'.addslashes($match_id).'", team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id.', pair_number=0 WHERE id='.$game_id);
            if ($update_team_game) {
                wLog('ДИАГНОСТИКА: Обновлена командная игра id='.$game_id.' с match_id='.$match_id.', team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id);
                
                // Проверяем обновленную игру
                $updated_game = db_row('SELECT id, pl_id_1, pl_id_2, team_a_id, team_b_id FROM `'.T_REITING.'` WHERE id='.$game_id);
                if (!empty($updated_game)) {
                    $updated_team_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$updated_game['pl_id_1'], 'name');
                    $updated_team_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$updated_game['pl_id_2'], 'name');
                    wLog('ДИАГНОСТИКА: Проверка обновленной командной игры id='.$game_id.':');
                    wLog('  pl_id_1='.$updated_game['pl_id_1'].' ('.$updated_team_a_name.'), pl_id_2='.$updated_game['pl_id_2'].' ('.$updated_team_b_name.')');
                    wLog('  team_a_id='.$updated_game['team_a_id'].', team_b_id='.$updated_game['team_b_id']);
                }
            } else {
                wLog('ERROR: Failed to update team game id='.$game_id.' with match_id='.$match_id);
            }
        } elseif (!empty($game['match_id']) && $game['match_id'] != $match_id) {
            // Если match_id не совпадает, обновляем его
            $update_team_game = db_query('UPDATE `'.T_REITING.'` SET match_id="'.addslashes($match_id).'", team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id.', pair_number=0 WHERE id='.$game_id);
            if ($update_team_game) {
                wLog('ДИАГНОСТИКА: Обновлен match_id для командной игры id='.$game_id.' с '.$game['match_id'].' на '.$match_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id);
            }
        } elseif (empty($game['start_game'])) {
            // Если match_id уже есть, но start_game не установлен, обновляем только team_a_id/team_b_id
            // start_game будет установлен после успешного создания игр пар
            $update_team_game = db_query('UPDATE `'.T_REITING.'` SET team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id.' WHERE id='.$game_id);
            if ($update_team_game) {
                wLog('ДИАГНОСТИКА: Подготовлена командная игра id='.$game_id.' перед созданием игр пар, pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id);
            }
        } elseif (!empty($game['team_a_id']) && !empty($game['team_b_id'])) {
            // Если match_id и start_game уже есть, но team_a_id/team_b_id не совпадают с bs_team_pairs, обновляем их
            if ($game['team_a_id'] != $team_a_id || $game['team_b_id'] != $team_b_id || $game['pl_id_1'] != $team_a_id || $game['pl_id_2'] != $team_b_id) {
                $update_team_game = db_query('UPDATE `'.T_REITING.'` SET team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', pl_id_1='.$team_a_id.', pl_id_2='.$team_b_id.' WHERE id='.$game_id);
                if ($update_team_game) {
                    wLog('ДИАГНОСТИКА: Обновлены team_a_id/team_b_id для командной игры id='.$game_id.':');
                    wLog('  team_a_id: '.$game['team_a_id'].' -> '.$team_a_id.', team_b_id: '.$game['team_b_id'].' -> '.$team_b_id);
                    wLog('  pl_id_1: '.$game['pl_id_1'].' -> '.$team_a_id.', pl_id_2: '.$game['pl_id_2'].' -> '.$team_b_id);
                }
            } else {
                wLog('ДИАГНОСТИКА: Командная игра id='.$game_id.' уже имеет правильные team_a_id/team_b_id, обновление не требуется');
            }
        }

        $etap = db_row('SELECT lineups_locked FROM `'.T_ETAPS.'` WHERE id='.$this->etap_id);
        $lineups_locked = !empty($etap['lineups_locked']) ? $etap['lineups_locked'] : 0;

        if (empty($lineups_locked)) {
            // НЕ блокируем все составы этапа - блокировка проверяется для каждого матча отдельно
            // по наличию игр с pair_number > 0 для конкретного match_id
            // db_query('UPDATE `'.T_ETAPS.'` SET lineups_locked=1 WHERE id='.$this->etap_id);
        }

        $turnir_info = db_row('SELECT id, date_create, dat FROM `'.T_TURNIRS.'` WHERE id='.$this->turnir_id);
        $start_game = date('H:i:s');
        $created_count = 0;

        $expected_pairs = count($pairs);
        $errors = array();

        // Удаляем старые игры игроков, которые не соответствуют текущим парам
        // Это необходимо, если админ изменил пары перед нажатием "Розпочати гру"
        // Удаляем только игры игроков (pair_number > 0), не трогая основную командную игру (pair_number = 0)
        db_query('START TRANSACTION');

        $delete_old_games_sql = 'DELETE FROM `'.T_REITING.'` 
            WHERE turnir_id='.$this->turnir_id.' 
            AND etap_id='.$this->etap_id.' 
            AND match_id="'.addslashes($match_id).'" 
            AND pair_number > 0';

        if (!db_query($delete_old_games_sql)) {
            $errors[] = 'delete_old_games_failed';
            wLog('ERROR: Failed to delete old player games for match_id='.$match_id);
        } else {
            wLog('Deleted old player games for match_id='.$match_id.' before creating new games');
        }

        foreach ($pairs as $pair) {
            if (!empty($errors)) {
                break;
            }
            $team_a_player_id = (int)$pair['team_a_player_id'];
            $team_b_player_id = (int)$pair['team_b_player_id'];
            $pair_number = (int)$pair['pair_number'];
            // ВАЖНО: Используем team_a_id и team_b_id из первой пары (которые синхронизированы с командной игрой),
            // а не из каждой пары отдельно, чтобы порядок команд был единообразным
            // $team_a_id и $team_b_id уже установлены из первой пары выше

            // ДИАГНОСТИКА: Получаем имена игроков для логирования
            $player_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_a_player_id, 'name');
            $player_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_b_player_id, 'name');
            $team_a_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_a_id, 'name');
            $team_b_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$team_b_id, 'name');
            
            wLog('ДИАГНОСТИКА: Обработка пары '.$pair_number.':');
            wLog('  team_a_id='.$team_a_id.' ('.$team_a_name.'), team_b_id='.$team_b_id.' ('.$team_b_name.')');
            wLog('  team_a_player_id='.$team_a_player_id.' ('.$player_a_name.') -> должен быть в pl_id_1 (колонка Команда/Пара 1)');
            wLog('  team_b_player_id='.$team_b_player_id.' ('.$player_b_name.') -> должен быть в pl_id_2 (колонка Команда/Пара 2)');

            if (empty($team_a_player_id) || empty($team_b_player_id)) {
                wLog('ERROR: Empty player IDs for pair '.$pair_number);
                $errors[] = 'empty_player_ids_pair_'.$pair_number;
                break;
            }

            // Ищем существующую игру по match_id и pair_number
            $existing_game = db_row('SELECT id, pl_id_1, pl_id_2, team_a_id, team_b_id FROM `'.T_REITING.'` WHERE turnir_id='.$this->turnir_id.' AND etap_id='.$this->etap_id.' AND match_id="'.addslashes($match_id).'" AND pair_number='.$pair_number);
            
            if (!empty($existing_game)) {
                $existing_pl1_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$existing_game['pl_id_1'], 'name');
                $existing_pl2_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$existing_game['pl_id_2'], 'name');
                wLog('ДИАГНОСТИКА: Найдена существующая игра id='.$existing_game['id'].' для пары '.$pair_number.':');
                wLog('  ТЕКУЩИЕ данные: pl_id_1='.$existing_game['pl_id_1'].' ('.$existing_pl1_name.'), pl_id_2='.$existing_game['pl_id_2'].' ('.$existing_pl2_name.')');
                wLog('  team_a_id='.$existing_game['team_a_id'].', team_b_id='.$existing_game['team_b_id']);
                wLog('  ОБНОВЛЯЕМ на: pl_id_1='.$team_a_player_id.' ('.$player_a_name.'), pl_id_2='.$team_b_player_id.' ('.$player_b_name.')');
                wLog('  team_a_id='.$team_a_id.', team_b_id='.$team_b_id);
                
                // Обновляем существующую игру, включая pl_id_1 и pl_id_2
                $update_result = db_query('UPDATE `'.T_REITING.'` SET 
                    pl_id_1='.$team_a_player_id.',
                    pl_id_2='.$team_b_player_id.',
                    start_game="'.$start_game.'", 
                    match_id="'.addslashes($match_id).'", 
                    pair_number='.$pair_number.', 
                    team_a_id='.$team_a_id.', 
                    team_b_id='.$team_b_id.' 
                    WHERE id='.$existing_game['id']);
                if ($update_result) {
                    wLog('ДИАГНОСТИКА: Успешно обновлена игра id='.$existing_game['id'].' для пары '.$pair_number);
                    $created_count++;
                } else {
                    wLog('ERROR: Failed to update existing game id='.$existing_game['id'].' for pair '.$pair_number);
                    $errors[] = 'update_existing_game_failed_pair_'.$pair_number;
                }
                continue;
            }

            $player1 = db_row('SELECT * FROM `'.T_PLAYERS.'` WHERE id='.$team_a_player_id);
            $player2 = db_row('SELECT * FROM `'.T_PLAYERS.'` WHERE id='.$team_b_player_id);

            if (empty($player1) || empty($player2)) {
                wLog('ERROR: Player not found for pair '.$pair_number.': player1='.(!empty($player1) ? 'found' : 'NOT FOUND').', player2='.(!empty($player2) ? 'found' : 'NOT FOUND'));
                $errors[] = 'player_not_found_pair_'.$pair_number;
                break;
            }

            $sql_reit1 = 'SELECT id, end_reiting FROM `'.T_TURNIR_PLAYERS.'` WHERE turnir_id<'.$this->turnir_id.' AND player_id='.$team_a_player_id.' ORDER BY turnir_id DESC LIMIT 1';
            $aReit1 = db_row($sql_reit1);
            $reiting1 = !empty($aReit1['end_reiting']) ? $aReit1['end_reiting'] : '';
            $start1 = $player1['start_reiting'];
            $reiting1 = (!empty($reiting1) && $reiting1 > 0) ? $reiting1 : $start1;

            $sql_reit2 = 'SELECT id, end_reiting FROM `'.T_TURNIR_PLAYERS.'` WHERE turnir_id<'.$this->turnir_id.' AND player_id='.$team_b_player_id.' ORDER BY turnir_id DESC LIMIT 1';
            $aReit2 = db_row($sql_reit2);
            $reiting2 = !empty($aReit2['end_reiting']) ? $aReit2['end_reiting'] : '';
            $start2 = $player2['start_reiting'];
            $reiting2 = (!empty($reiting2) && $reiting2 > 0) ? $reiting2 : $start2;

            $insert_sql = 'INSERT INTO `'.T_REITING.'` SET 
                pl_id_1='.$team_a_player_id.',
                pl_id_2='.$team_b_player_id.',
                turnir_id='.$this->turnir_id.',
                etap_id='.$this->etap_id.',
                match_id="'.addslashes($match_id).'",
                pair_number='.$pair_number.',
                team_a_id='.$team_a_id.',
                team_b_id='.$team_b_id.',
                rt_id_1_beg='.$reiting1.',
                rt_id_2_beg='.$reiting2.',
                diff_1=0,
                diff_2=0,
                set_1=0,
                set_2=0,
                no_send=0,
                break_1=0,
                break_2=0,
                start_game="'.$start_game.'",
                end_game="",
                table_game=0';

            wLog('ДИАГНОСТИКА: Создаем новую игру для пары '.$pair_number.':');
            wLog('  pl_id_1='.$team_a_player_id.' ('.$player_a_name.') -> колонка Команда/Пара 1 (команда '.$team_a_name.')');
            wLog('  pl_id_2='.$team_b_player_id.' ('.$player_b_name.') -> колонка Команда/Пара 2 (команда '.$team_b_name.')');
            wLog('  team_a_id='.$team_a_id.', team_b_id='.$team_b_id.', match_id='.$match_id);
            wLog('  SQL: '.$insert_sql);
            
            $result = db_query($insert_sql);
            if ($result) {
                $new_game_id = db_insert_id();
                wLog('ДИАГНОСТИКА: Успешно создана игра id='.$new_game_id.' для пары '.$pair_number);
                
                // Проверяем созданную игру
                $check_game = db_row('SELECT id, pl_id_1, pl_id_2, team_a_id, team_b_id, match_id, pair_number FROM `'.T_REITING.'` WHERE id='.$new_game_id);
                if (!empty($check_game)) {
                    $check_pl1_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$check_game['pl_id_1'], 'name');
                    $check_pl2_name = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$check_game['pl_id_2'], 'name');
                    wLog('ДИАГНОСТИКА: Проверка созданной игры id='.$new_game_id.':');
                    wLog('  pl_id_1='.$check_game['pl_id_1'].' ('.$check_pl1_name.'), pl_id_2='.$check_game['pl_id_2'].' ('.$check_pl2_name.')');
                    wLog('  team_a_id='.$check_game['team_a_id'].', team_b_id='.$check_game['team_b_id']);
                }
                $created_count++;
            } else {
                wLog('Помилка створення гри для пари '.$pair_number.': '.$insert_sql);
                $errors[] = 'insert_game_failed_pair_'.$pair_number;
            }
        }

        if (empty($errors) && $created_count >= $expected_pairs && $created_count > 0) {
            $final_start_game = (!empty($game['start_game']) && $game['start_game'] != '00:00:00') ? $game['start_game'] : $start_game_time;
            $finalize_team_game_sql = 'UPDATE `'.T_REITING.'` SET start_game="'.$final_start_game.'", match_id="'.addslashes($match_id).'", team_a_id='.(int)$team_a_id.', team_b_id='.(int)$team_b_id.', pl_id_1='.(int)$team_a_id.', pl_id_2='.(int)$team_b_id.', pair_number=0 WHERE id='.(int)$game_id;
            if (!db_query($finalize_team_game_sql)) {
                $errors[] = 'finalize_team_game_failed';
                wLog('ERROR: Failed to finalize team game id='.$game_id.' for match_id='.$match_id);
            }
        }

        if (empty($errors)) {
            db_query('COMMIT');
            window_mess('Гру розпочато! Створено ігор: '.$created_count);
        } else {
            db_query('ROLLBACK');
            wLog('ERROR: start_team_game rollback for match_id='.$match_id.', errors='.implode(',', $errors).', created_count='.$created_count.', expected_pairs='.$expected_pairs);
            wLog('ERROR: No games created for match_id: '.$match_id.', etap_id: '.$this->etap_id);
            window_mess('Помилка: не вдалося створити ігри. Перевірте, чи створені пари для цього матчу.');
        }

        $this->show();
    }

    function show()
    {
        // Формируем post_return для обновления списка игр
        $post_return = 'reiting-list-&turnir_id='.$this->turnir_id.'&etap_id='.$this->etap_id;
        if (!empty($this->league_id)) {
            $post_return .= '&league_id='.$this->league_id;
        }
        
        // Устанавливаем post_return для правильного обновления страницы
        SystemClass::setPost_return($post_return);
        $_SESSION['POST_RETURN'] = $post_return;
        SystemClass::setAction('list');
        SystemClass::setModule('reiting');
        
        // Оставляем контент пустым - страница обновится через JavaScript
        $this->content = '';
        
        // Полностью перезагружаем страницу после небольшой задержки,
        // чтобы модальное окно успело показаться
        // Это необходимо, чтобы обновились пары после создания игр игроков
        // Устанавливаем hash перед перезагрузкой, чтобы после перезагрузки открылась правильная страница
        $this->Java_script = 'if (document.location.hash != "#'.$post_return.'") { document.location.hash = "#'.$post_return.'"; } setTimeout(function(){ window.location.reload(); }, 500);';
    }

    function getContent()
    {
        // Если контент пустой, возвращаем минимальный контент для AJAX-ответа
        if (empty($this->content)) {
            return '<div id="content_main"></div>';
        }
        return $this->content;
    }

    function getSubMneu()
    {
        return $this->subMenu;
    }

    function getJavaScript()
    {
        return $this->Java_script;
    }
}
