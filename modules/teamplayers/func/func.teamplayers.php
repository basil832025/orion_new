<?php

if (!function_exists('teamplayers_resolve_league_id')) {
    function teamplayers_resolve_league_id($league_id = 0, $turnir_id = 0)
    {
        $league_id = (int)$league_id;
        if ($league_id > 0) {
            return $league_id;
        }

        $turnir_id = (int)$turnir_id;
        if ($turnir_id > 0) {
            return (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
        }

        return 0;
    }
}

if (!function_exists('teamplayers_request_param')) {
    function teamplayers_request_param($name, $session_key = '')
    {
        $value = poste($name);
        if (!empty($value)) {
            return (int)$value;
        }

        $form = poste('form');
        if (!empty($form[$name])) {
            return (int)$form[$name];
        }

        $value = get($name);
        if (!empty($value)) {
            return (int)$value;
        }

        if (in_array($name, array('team_id', 'turnir_id', 'league_id'))) {
            foreach ($_POST as $post_value) {
                if (is_scalar($post_value) &&
                    preg_match('/'.preg_quote($name, '/').'=(\d+)/', (string)$post_value, $matches)) {
                    return (int)$matches[1];
                }
            }
        }

        if (!empty($session_key) && !empty($_SESSION[$session_key])) {
            return (int)$_SESSION[$session_key];
        }

        $sources = array();
        if (!empty($_SESSION['POST_RETURN'])) {
            $sources[] = $_SESSION['POST_RETURN'];
        }
        if (class_exists('SystemClass')) {
            $sources[] = SystemClass::getPost_return_noMA();
        }
        if (!empty($_SERVER['REQUEST_URI'])) {
            $sources[] = $_SERVER['REQUEST_URI'];
        }
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $sources[] = $_SERVER['HTTP_REFERER'];
        }
        foreach ($_POST as $post_value) {
            if (is_scalar($post_value)) {
                $sources[] = $post_value;
            }
        }

        foreach ($sources as $source) {
            if (preg_match('/(?:^|[?&#\\-])'.preg_quote($name, '/').'=(\d+)/', (string)$source, $matches)) {
                return (int)$matches[1];
            }
            if (in_array($name, array('team_id', 'turnir_id', 'league_id')) &&
                preg_match('/'.preg_quote($name, '/').'=(\d+)/', (string)$source, $matches)) {
                return (int)$matches[1];
            }
        }

        return 0;
    }
}

if (!function_exists('teamplayers_get_players_sql')) {
    function teamplayers_get_players_sql($team_id, $league_id, $fields = 'p.*')
    {
        $team_id = (int)$team_id;
        $league_id = (int)$league_id;

        if ($league_id > 0) {
            return 'SELECT '.$fields.'
                FROM `'.T_PLAYERS.'` p
                INNER JOIN `'.T_TEAM_PLAYERS_LEAGUE.'` tpl ON tpl.player_id=p.id
                WHERE tpl.league_id='.$league_id.'
                  AND tpl.team_id='.$team_id.'
                  AND (p.is_team IS NULL OR p.is_team=0)
                  AND p.not_use=0';
        }

        return 'SELECT '.$fields.'
            FROM `'.T_PLAYERS.'` p
            WHERE p.team_id='.$team_id.'
              AND (p.is_team IS NULL OR p.is_team=0)
              AND p.not_use=0';
    }
}

if (!function_exists('teamplayers_count')) {
    function teamplayers_count($team_id, $league_id = 0)
    {
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COUNT(*) as cnt');
        return (int)db_field($sql, 'cnt');
    }
}

if (!function_exists('teamplayers_sum_reiting_ukraine')) {
    function teamplayers_sum_reiting_ukraine($team_id, $league_id = 0)
    {
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COALESCE(SUM(p.reiting_ukraine),0) as total');
        return (int)db_field($sql, 'total');
    }
}

if (!function_exists('teamplayers_opl_summary')) {
    function teamplayers_opl_summary($team_id, $league_id = 0)
    {
        $sql = teamplayers_get_players_sql($team_id, $league_id, 'COUNT(*) as total, SUM(CASE WHEN p.is_opl_reiting=1 THEN 1 ELSE 0 END) as paid');
        $row = db_row($sql);

        return array(
            'total' => (!empty($row['total']) ? (int)$row['total'] : 0),
            'paid' => (!empty($row['paid']) ? (int)$row['paid'] : 0),
        );
    }
}

if (!function_exists('teamplayers_list')) {
    function teamplayers_list($team_id, $league_id = 0, $fields = 'p.id, p.id_reiting, p.name, p.phone, p.city, p.reiting_ukraine, p.is_opl_reiting')
    {
        return db_list(teamplayers_get_players_sql($team_id, $league_id, $fields).' ORDER BY p.name');
    }
}

if (!function_exists('teamplayers_get_previous_league_id')) {
    function teamplayers_get_previous_league_id($league_id, $team_id = 0)
    {
        $league_id = (int)$league_id;
        $team_id = (int)$team_id;

        if ($league_id <= 0) {
            return 0;
        }

        $current_league = db_row('SELECT id, dat FROM `bs_leagues` WHERE id='.$league_id);
        if (empty($current_league)) {
            return 0;
        }

        $team_join = '';
        $team_where = '';
        if ($team_id > 0) {
            $team_join = ' INNER JOIN `'.T_TEAM_PLAYERS_LEAGUE.'` tpl ON tpl.league_id=l.id ';
            $team_where = ' AND tpl.team_id='.$team_id.' ';
        }

        $current_dat = !empty($current_league['dat']) ? (string)$current_league['dat'] : '';
        if ($current_dat !== '' && $current_dat !== '0000-00-00' && $current_dat !== '0000-00-00 00:00:00') {
            $prev_league = db_row('SELECT l.id
                FROM `bs_leagues` l
                '.$team_join.'
                WHERE l.id<>'.$league_id.'
                  AND COALESCE(l.is_team_league,0)=1
                  '.$team_where.'
                  AND (l.dat<"'.addslashes($current_dat).'" OR (l.dat="'.addslashes($current_dat).'" AND l.id<'.$league_id.'))
                GROUP BY l.id, l.dat
                ORDER BY l.dat DESC, l.id DESC
                LIMIT 1');
            $prev_id = !empty($prev_league['id']) ? (int)$prev_league['id'] : 0;
            if ($prev_id > 0) {
                return $prev_id;
            }
        }

        $prev_league = db_row('SELECT l.id
            FROM `bs_leagues` l
            '.$team_join.'
            WHERE l.id<'.$league_id.'
              AND COALESCE(l.is_team_league,0)=1
              '.$team_where.'
            GROUP BY l.id
            ORDER BY l.id DESC
            LIMIT 1');

        return !empty($prev_league['id']) ? (int)$prev_league['id'] : 0;
    }
}

?>
