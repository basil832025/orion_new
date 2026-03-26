<?php
/**
 * Action для получения детальных результатов командного матча
 * Возвращает все игры игроков для конкретного матча
 */

class match_detailsAction extends ActionModule {
    protected $content = '';
    
    function init() {
        // Результаты доступны всем пользователям (включая неавторизованных)
        $match_id = poste('match_id');
        $etap_id = poste('etap_id');
        $turnir_id = poste('turnir_id');
        $team_a_id = poste('team_a_id');
        $team_b_id = poste('team_b_id');
        
        if (empty($match_id) || empty($etap_id)) {
            $this->content = json_encode(array('error' => 'Не указаны необходимые параметры'));
            return;
        }
        
        // Получаем информацию о командной игре
        $team_game = db_row('SELECT r.*, 
                p1.name as pl_id_1_name, 
                p2.name as pl_id_2_name,
                r.team_a_id,
                r.team_b_id
            FROM '.T_REITING.' r
            LEFT JOIN '.T_PLAYERS.' p1 ON p1.id = r.pl_id_1
            LEFT JOIN '.T_PLAYERS.' p2 ON p2.id = r.pl_id_2
            WHERE r.match_id="'.addslashes($match_id).'" 
            AND r.etap_id='.(int)$etap_id.'
            AND (r.pair_number = 0 OR r.pair_number IS NULL OR r.pair_number = "")
            LIMIT 1');
        
        if (empty($team_game)) {
            $this->content = json_encode(array('error' => 'Командная игра не найдена'));
            return;
        }
        
        // Получаем team_a_id и team_b_id из командной игры
        $team_a_id = !empty($team_game['team_a_id']) ? (int)$team_game['team_a_id'] : 0;
        $team_b_id = !empty($team_game['team_b_id']) ? (int)$team_game['team_b_id'] : 0;
        
        // Если team_a_id и team_b_id не определены, пытаемся получить из pl_id_1 и pl_id_2
        if (empty($team_a_id) || empty($team_b_id)) {
            if (!empty($team_game['pl_id_1'])) {
                $p1_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_game['pl_id_1'], 'is_team');
                if (!empty($p1_is_team) && $p1_is_team == 1) {
                    $team_a_id = $team_game['pl_id_1'];
                }
            }
            if (!empty($team_game['pl_id_2'])) {
                $p2_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_game['pl_id_2'], 'is_team');
                if (!empty($p2_is_team) && $p2_is_team == 1) {
                    if (empty($team_a_id)) {
                        $team_a_id = $team_game['pl_id_2'];
                    } else {
                        $team_b_id = $team_game['pl_id_2'];
                    }
                }
            }
        }
        
        // Определяем порядок команд по командной игре (pair_number=0)
        $left_team_id = 0;
        $right_team_id = 0;
        $team_left_candidate = !empty($team_game['pl_id_1']) ? (int)$team_game['pl_id_1'] : 0;
        $team_right_candidate = !empty($team_game['pl_id_2']) ? (int)$team_game['pl_id_2'] : 0;
        $is_left_team = !empty($team_left_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_left_candidate, 'is_team') : 0;
        $is_right_team = !empty($team_right_candidate) ? (int)db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_right_candidate, 'is_team') : 0;
        if ($is_left_team == 1 && $is_right_team == 1) {
            $left_team_id = $team_left_candidate;
            $right_team_id = $team_right_candidate;
        } else {
            $left_team_id = !empty($team_game['team_a_id']) ? (int)$team_game['team_a_id'] : 0;
            $right_team_id = !empty($team_game['team_b_id']) ? (int)$team_game['team_b_id'] : 0;
        }
        if (empty($left_team_id) || empty($right_team_id)) {
            $left_team_id = $team_a_id;
            $right_team_id = $team_b_id;
        }

        // Получаем названия команд по порядку из командной игры
        $team_a_name_db = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$left_team_id, 'name');
        $team_b_name_db = db_field('SELECT name FROM `'.T_PLAYERS.'` WHERE id='.$right_team_id, 'name');
        $team_a_name = !empty($team_a_name_db) ? $team_a_name_db : 'Команда A';
        $team_b_name = !empty($team_b_name_db) ? $team_b_name_db : 'Команда B';

        // Определяем счет командной игры относительно порядка командной игры
        $team_score_1 = $team_game['set_1'];
        $team_score_2 = $team_game['set_2'];
        if (!empty($team_game['pl_id_1']) && !empty($team_game['pl_id_2'])) {
            if ($team_game['pl_id_1'] == $right_team_id && $team_game['pl_id_2'] == $left_team_id) {
                $temp_score = $team_score_1;
                $team_score_1 = $team_score_2;
                $team_score_2 = $temp_score;
            }
        } elseif (!empty($team_game['team_a_id']) && !empty($team_game['team_b_id'])) {
            if ($team_game['team_a_id'] == $right_team_id && $team_game['team_b_id'] == $left_team_id) {
                $temp_score = $team_score_1;
                $team_score_1 = $team_score_2;
                $team_score_2 = $temp_score;
            }
        }
        
        // Получаем все игры игроков для этого матча
        $player_games = db_list('SELECT r.*, 
                p1.name as player_1_name,
                p2.name as player_2_name,
                tp.pair_number,
                tp.team_a_player_id,
                tp.team_b_player_id,
                tp.team_a_id as pair_team_a_id,
                tp.team_b_id as pair_team_b_id,
                (SELECT position FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id = tp.team_a_player_id LIMIT 1) as team_a_pos,
                (SELECT position FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id = tp.team_b_player_id LIMIT 1) as team_b_pos
            FROM '.T_REITING.' r
            INNER JOIN bs_team_pairs tp ON (
                tp.match_id = r.match_id 
                AND tp.pair_number = r.pair_number
                AND tp.etap_id = '.$etap_id.'
            )
            LEFT JOIN '.T_PLAYERS.' p1 ON p1.id = r.pl_id_1
            LEFT JOIN '.T_PLAYERS.' p2 ON p2.id = r.pl_id_2
            WHERE r.match_id="'.addslashes($match_id).'" 
            AND r.etap_id='.(int)$etap_id.'
            AND r.pair_number > 0
            ORDER BY r.pair_number ASC');

        // ВАЖНО: Проверяем фактический порядок игроков после обработки всех игр
        // Определяем, к какой команде относится player_1_name из первой игры после обработки
        // Это нужно сделать после того, как мы определили final_player_1_name и final_player_2_name
        
        // Формируем данные для модального окна
        $response = array(
            'success' => true,
            'team_game' => array(
                'team_a_name' => $team_a_name,
                'team_b_name' => $team_b_name,
                'score' => $team_score_1.':'.$team_score_2,
                'status' => (!empty($team_game['end_game']) ? 'Завершено' : 'В процессе')
            ),
            'player_games' => array()
        );
        
        foreach ($player_games as $game) {
            $player_1_id = !empty($game['pl_id_1']) ? (int)$game['pl_id_1'] : 0;
            $player_2_id = !empty($game['pl_id_2']) ? (int)$game['pl_id_2'] : 0;

            // Определяем позиции игроков по факту (по lineups)
            $pos_1 = !empty($player_1_id) ? db_field('SELECT position FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id='.$player_1_id.' LIMIT 1', 'position') : '';
            $pos_2 = !empty($player_2_id) ? db_field('SELECT position FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id='.$player_2_id.' LIMIT 1', 'position') : '';

            $player_1_team_id = 0;
            if (!empty($player_1_id)) {
                $player_1_team_id = (int)db_field('SELECT team_id FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id='.$player_1_id.' LIMIT 1', 'team_id');
                if (empty($player_1_team_id)) {
                    $player_1_team_id = (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$player_1_id, 'team_id');
                }
            }
            $player_2_team_id = 0;
            if (!empty($player_2_id)) {
                $player_2_team_id = (int)db_field('SELECT team_id FROM bs_team_lineups WHERE match_id="'.addslashes($match_id).'" AND etap_id='.(int)$etap_id.' AND player_id='.$player_2_id.' LIMIT 1', 'team_id');
                if (empty($player_2_team_id)) {
                    $player_2_team_id = (int)db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$player_2_id, 'team_id');
                }
            }

            $final_player_1_name = !empty($game['player_1_name']) ? $game['player_1_name'] : '';
            $final_player_2_name = !empty($game['player_2_name']) ? $game['player_2_name'] : '';
            $final_pos_1 = $pos_1;
            $final_pos_2 = $pos_2;
            $final_score = $game['set_1'].':'.$game['set_2'];

            if (!empty($left_team_id) && !empty($right_team_id) && !empty($player_1_team_id) && !empty($player_2_team_id)) {
                if ($player_1_team_id == $right_team_id && $player_2_team_id == $left_team_id) {
                    // Игроки стоят наоборот, меняем местами отображение и счет
                    $final_player_1_name = !empty($game['player_2_name']) ? $game['player_2_name'] : $final_player_1_name;
                    $final_player_2_name = !empty($game['player_1_name']) ? $game['player_1_name'] : $final_player_2_name;
                    $final_pos_1 = $pos_2;
                    $final_pos_2 = $pos_1;
                    $final_score = $game['set_2'].':'.$game['set_1'];
                }
            }
            
            $response['player_games'][] = array(
                'pair_number' => (int)$game['pair_number'],
                'player_1_name' => $final_player_1_name,
                'player_2_name' => $final_player_2_name,
                'position_1' => $final_pos_1,
                'position_2' => $final_pos_2,
                'score' => $final_score,
                'start_game' => !empty($game['start_game']) ? $game['start_game'] : '',
                'end_game' => !empty($game['end_game']) ? $game['end_game'] : ''
            );
        }
        
        // Порядок команд фиксирован по командной игре, дополнительных перестановок не требуется
        
        header('Content-Type: application/json; charset=utf-8');
        $this->content = json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    function getContent() {
        return $this->content;
    }
}
?>
