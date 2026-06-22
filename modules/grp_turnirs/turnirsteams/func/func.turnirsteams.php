<?php
require_once __DIR__ . '/../../../teamplayers/func/func.teamplayers.php';

function setPlayersLigasInfo($turnir_id,$team){
    $turnir_id = (int)$turnir_id;
    $team_id = (int)$team;

    if ($team_id <= 0) {
        return;
    }

    if (!function_exists('get_ligs_player')) {
        $ligas_func_path = dirname(__DIR__) . '/turnirsplayers/func/func.turnirsplayers.php';
        if (file_exists($ligas_func_path)) {
            require_once $ligas_func_path;
        } else {
            return;
        }
    }

    $league_id = teamplayers_resolve_league_id(0, $turnir_id);
    $players = teamplayers_list($team_id, $league_id, 'p.id, p.id_reiting');
    if (empty($players)) {
        return;
    }

    foreach ($players as $player) {
        $player_id = !empty($player['id']) ? (int)$player['id'] : 0;
        $id_reiting = !empty($player['id_reiting']) ? trim($player['id_reiting']) : '';

        if ($player_id <= 0 || $id_reiting === '') {
            continue;
        }

        $aPlayer = get_ligs_player($id_reiting);
        if (empty($aPlayer) || !is_array($aPlayer)) {
            continue;
        }

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

function get_team_league_group($field, $id, $data)
{
    $team_id = 0;
    if (!empty($data['player_id']) && is_numeric($data['player_id'])) {
        $team_id = (int)$data['player_id'];
    }
    if ($team_id <= 0 && !empty($data['id']) && is_numeric($data['id'])) {
        $team_id = (int)db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$data['id'], 'player_id');
    }

    if ($team_id <= 0) {
        return '';
    }

    $league_id = poste('league_id');
    if (empty($league_id)) {
        $league_id = get('league_id');
    }
    if (empty($league_id)) {
        $turnir_id = poste('turnir_id');
        if (empty($turnir_id) && !empty($data['turnir_id'])) {
            $turnir_id = (int)$data['turnir_id'];
        }
        if (!empty($turnir_id)) {
            $league_id = db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.(int)$turnir_id, 'league_id');
        }
    }

    $league_id = (int)$league_id;
    if ($league_id <= 0) {
        return '';
    }

    $group_num = (int)db_field('SELECT group_num FROM `bs_league_team_groups` WHERE league_id='.$league_id.' AND team_id='.$team_id.' LIMIT 1', 'group_num');
    if ($group_num <= 0) {
        return '';
    }

    return 'Ліга '.$group_num;
}

function auto_assign_league_groups($turnir_id, $league_id, $leagues_count)
{
    $turnir_id = (int)$turnir_id;
    $league_id = (int)$league_id;
    $leagues_count = (int)$leagues_count;

    if ($turnir_id <= 0 || $league_id <= 0 || $leagues_count < 1) {
        return false;
    }

    $ordered_team_ids = array();
    $groups_data = array();
    $max_place = 0;

    $etap_id = (int)db_field('SELECT id FROM '.T_ETAPS.' WHERE turnir_id='.$turnir_id.' AND type_etap IN (1,66) ORDER BY id DESC LIMIT 1', 'id');
    if ($etap_id > 0) {
        $sql = 'SELECT tp.player_id, tp.groups,
                       COALESCE(NULLIF(tp.grp_mesto,0), tp.grp_num) AS place,
                       p.name
                FROM `'.T_ETAPS_PLAYER_MESTA.'` tp
                INNER JOIN `'.T_PLAYERS.'` p ON p.id=tp.player_id
                WHERE tp.turnir_id='.$turnir_id.' AND tp.etap_id='.$etap_id.'
                      AND tp.player_id>0 AND p.is_team=1 AND p.not_use=0
                ORDER BY tp.groups ASC, place ASC, p.name ASC';
        $rows = db_list($sql);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $group_num = !empty($row['groups']) ? (int)$row['groups'] : 0;
                $place = !empty($row['place']) ? (int)$row['place'] : 0;
                $team_id = !empty($row['player_id']) ? (int)$row['player_id'] : 0;
                if ($group_num <= 0 || $place <= 0 || $team_id <= 0) {
                    continue;
                }
                if (!isset($groups_data[$group_num])) {
                    $groups_data[$group_num] = array();
                }
                if (empty($groups_data[$group_num][$place])) {
                    $groups_data[$group_num][$place] = $team_id;
                }
                if ($place > $max_place) {
                    $max_place = $place;
                }
            }
        }
    }

    if (!empty($groups_data) && $max_place > 0) {
        $group_nums = array_keys($groups_data);
        sort($group_nums);
        for ($place = 1; $place <= $max_place; $place++) {
            $iter = ($place % 2 == 1) ? $group_nums : array_reverse($group_nums);
            foreach ($iter as $group_num) {
                if (!empty($groups_data[$group_num][$place])) {
                    $ordered_team_ids[] = $groups_data[$group_num][$place];
                }
            }
        }
    }

    if (empty($ordered_team_ids)) {
        $sql = 'SELECT tp.player_id, tp.points, tp.cnt_wins, tp.cnt_sets_win, tp.mesto, p.name
                FROM `'.T_TURNIR_PLAYERS.'` tp
                INNER JOIN `'.T_PLAYERS.'` p ON p.id=tp.player_id
                WHERE tp.turnir_id='.$turnir_id.' AND p.is_team=1 AND p.not_use=0
                ORDER BY (tp.mesto IS NULL OR tp.mesto=0) ASC, tp.mesto ASC, tp.points DESC, tp.cnt_wins DESC, tp.cnt_sets_win DESC, p.name';
        $teams = db_list($sql);
        if (empty($teams)) {
            return false;
        }
        foreach ($teams as $team) {
            if (!empty($team['player_id'])) {
                $ordered_team_ids[] = (int)$team['player_id'];
            }
        }
    }

    $total_teams = count($ordered_team_ids);
    if ($total_teams <= 0) {
        return false;
    }

    db_query('DELETE FROM `bs_league_team_groups` WHERE league_id='.$league_id);

    $base_per_league = (int)floor($total_teams / $leagues_count);
    $remainder = $total_teams % $leagues_count;
    $idx = 0;

    for ($league_num = 1; $league_num <= $leagues_count; $league_num++) {
        $limit = $base_per_league + ($league_num <= $remainder ? 1 : 0);
        for ($i = 0; $i < $limit; $i++) {
            if (!isset($ordered_team_ids[$idx])) {
                break 2;
            }
            $team_id = (int)$ordered_team_ids[$idx];
            $idx++;
            if ($team_id <= 0) {
                continue;
            }
            $sql_ins = 'INSERT INTO `bs_league_team_groups`
                (league_id, team_id, group_num, turnir_id)
                VALUES ('.$league_id.', '.$team_id.', '.$league_num.', '.$turnir_id.')
                ON DUPLICATE KEY UPDATE
                    group_num=VALUES(group_num),
                    turnir_id=VALUES(turnir_id),
                    date_update=NOW()';
            db_query($sql_ins);
        }
    }

    return true;
}
