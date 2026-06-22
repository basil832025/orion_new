<?php
 require_once __DIR__ . '/../func/func.teamplayers.php';
 $team_id = teamplayers_request_param('team_id', 'TEAMPLAYERS_SAVE_TEAM_ID');
 $turnir_id = teamplayers_request_param('turnir_id', 'TEAMPLAYERS_SAVE_TURNIR_ID');
 $league_id = teamplayers_request_param('league_id', 'TEAMPLAYERS_SAVE_LEAGUE_ID');
 $league_id = teamplayers_resolve_league_id($league_id, $turnir_id);
  $id = poste('id');
  $form = SystemClass::getAFormPost();
  
 if (!empty($form['player_id']) && $form['player_id'] > 0 && !empty($team_id)) {
    $player_id = $form['player_id'];

    if (!function_exists('get_ligs_player')) {
        require_once __DIR__ . '/../../grp_turnirs/turnirsplayers/func/func.turnirsplayers.php';
    }
    
    // Проверка: игрок не должен быть командой
    $check_player = db_row('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$player_id);
    if (!empty($check_player['is_team']) && $check_player['is_team'] == 1) {
        window_mess('Неможливо додати команду як гравця!');
        return;
    }
    
    // Проверка: игрок не должен уже быть в другой команде (если это новая запись)
    if (empty($id) || $id == 0) {
        if (!empty($league_id)) {
            $turnir_team_filter = !empty($turnir_id)
                ? ' AND EXISTS(SELECT * FROM `'.T_TURNIR_PLAYERS.'` ttp WHERE ttp.turnir_id='.(int)$turnir_id.' AND ttp.player_id=tpl.team_id) '
                : '';
            $existing_team = db_field('SELECT team_id FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl WHERE tpl.league_id='.(int)$league_id.' AND tpl.player_id='.(int)$player_id.' AND tpl.team_id<>'.(int)$team_id.$turnir_team_filter.' LIMIT 1', 'team_id');
        } else {
            $existing_team = db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$player_id.' AND team_id IS NOT NULL', 'team_id');
        }
        if (!empty($existing_team)) {
            window_mess('Цей гравець вже є в іншій команді!');
            return;
        }
    }
    
    if (!empty($league_id)) {
        $now = date('Y-m-d H:i:s');
        db_query('INSERT INTO `'.T_TEAM_PLAYERS_LEAGUE.'`
            (league_id, team_id, player_id, created_at, updated_at)
            VALUES ('.(int)$league_id.', '.(int)$team_id.', '.(int)$player_id.', "'.$now.'", "'.$now.'")
            ON DUPLICATE KEY UPDATE team_id=VALUES(team_id), updated_at=VALUES(updated_at)');
    } else {
        db_query('UPDATE `'.T_PLAYERS.'` SET team_id='.$team_id.' WHERE id='.$player_id);
    }

    if (function_exists('get_ligs_player')) {
        $id_reiting = db_field('SELECT id_reiting FROM `'.T_PLAYERS.'` WHERE id='.$player_id, 'id_reiting');
        if (!empty($id_reiting)) {
            $aPlayer = get_ligs_player($id_reiting);
            if (!empty($aPlayer) && is_array($aPlayer)) {
                $updates = [];

                if (array_key_exists('birthyear', $aPlayer)) {
                    $updates[] = 'god_rogd="'.addslashes($aPlayer['birthyear']).'"';
                }
                if (array_key_exists('ranking', $aPlayer)) {
                    $updates[] = 'reiting_ukraine="'.addslashes($aPlayer['ranking']).'"';
                }
                if (array_key_exists('expire', $aPlayer)) {
                    $updates[] = 'is_opl_reiting="'.(!empty($aPlayer['expire']) ? 1 : 0).'"';
                }
                if (array_key_exists('city', $aPlayer)) {
                    $updates[] = 'city="'.addslashes($aPlayer['city']).'"';
                }
                if (array_key_exists('sex', $aPlayer)) {
                    $updates[] = 'sex="'.addslashes($aPlayer['sex']).'"';
                }
                if (array_key_exists('fio', $aPlayer)) {
                    $updates[] = 'name_ligas="'.addslashes($aPlayer['fio']).'"';
                }
                if (array_key_exists('image', $aPlayer)) {
                    $updates[] = 'ligas_photo="'.addslashes($aPlayer['image']).'"';
                }

                if (!empty($updates)) {
                    db_query('UPDATE '.T_PLAYERS.' SET '.implode(',', $updates).' WHERE id='.$player_id);
                }
            }
        }
    }
    
    // Устанавливаем редирект на список игроков команды после сохранения
    // Сохраняем team_id и параметры турнира в сессии для использования после list_show() в edit_ok()
    if (!empty($team_id)) {
        $_SESSION['TEAMPLAYERS_SAVE_TEAM_ID'] = $team_id;
        // Сохраняем параметры турнира для корректного возврата
        if (!empty($turnir_id)) {
            $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'] = $turnir_id;
        }
        if (!empty($league_id)) {
            $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'] = $league_id;
        }
        
        // Формируем post_return с параметрами турнира, если они есть
        $post_return = 'teamplayers-list-team_id='.$team_id;
        if (!empty($turnir_id)) {
            $post_return .= '&turnir_id='.$turnir_id;
        }
        if (!empty($league_id)) {
            $post_return .= '&league_id='.$league_id;
        }
        
        // Устанавливаем RedirectUrl для обработки в edit_ok()
        ObjectRT::setRedirectUrl(array(
            'module' => 'teamplayers',
            'action' => 'list',
            'post_return' => $post_return
        ));
    }
} else {
    window_mess('Виберіть гравця!');
}

?>
