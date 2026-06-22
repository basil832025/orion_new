<?php
require_once __DIR__ . '/../func/func.teamplayers.php';

$team_id = teamplayers_request_param('team_id', 'TEAMPLAYERS_SAVE_TEAM_ID');
$turnir_id = teamplayers_request_param('turnir_id', 'TEAMPLAYERS_SAVE_TURNIR_ID');
$league_id = teamplayers_resolve_league_id(teamplayers_request_param('league_id', 'TEAMPLAYERS_SAVE_LEAGUE_ID'), $turnir_id);
$message = '';
$message_class = 'alert-info';

if (empty($_SESSION['gt']['user_rule']) || (int)$_SESSION['gt']['user_rule'] >= 10) {
    $message = 'Помилка: недостатньо прав для заповнення складу команди.';
    $message_class = 'alert-danger';
} elseif ($team_id <= 0 || $league_id <= 0) {
    $message = 'Помилка: не вказано команду або лігу.';
    $message_class = 'alert-danger';
} else {
    $prev_league_id = teamplayers_get_previous_league_id($league_id, $team_id);

    if ($prev_league_id <= 0) {
        $message = 'Не знайдено попередню лігу зі складом цієї команди.';
        $message_class = 'alert-warning';
    } else {
        $source_count = teamplayers_count($team_id, $prev_league_id);
        $before_count = teamplayers_count($team_id, $league_id);
        $turnir_team_filter = !empty($turnir_id)
            ? ' AND EXISTS(SELECT * FROM `'.T_TURNIR_PLAYERS.'` ttp WHERE ttp.turnir_id='.(int)$turnir_id.' AND ttp.player_id=tpl_current.team_id) '
            : '';
        $conflict_count = 0;

        if ($source_count > 0) {
            $conflict_count = (int)db_field('SELECT COUNT(*) as cnt
                FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl_prev
                INNER JOIN `'.T_PLAYERS.'` p ON p.id=tpl_prev.player_id
                WHERE tpl_prev.league_id='.(int)$prev_league_id.'
                  AND tpl_prev.team_id='.(int)$team_id.'
                  AND (p.is_team IS NULL OR p.is_team=0)
                  AND p.not_use=0
                  AND EXISTS(
                      SELECT * FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl_current
                      WHERE tpl_current.league_id='.(int)$league_id.'
                        AND tpl_current.player_id=tpl_prev.player_id
                        AND tpl_current.team_id<>'.(int)$team_id.
                        $turnir_team_filter.'
                  )', 'cnt');

            db_query('INSERT IGNORE INTO `'.T_TEAM_PLAYERS_LEAGUE.'`
                (league_id, team_id, player_id, created_at, updated_at)
                SELECT '.(int)$league_id.', '.(int)$team_id.', tpl.player_id, NOW(), NOW()
                FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl
                INNER JOIN `'.T_PLAYERS.'` p ON p.id=tpl.player_id
                WHERE tpl.league_id='.(int)$prev_league_id.'
                  AND tpl.team_id='.(int)$team_id.'
                  AND (p.is_team IS NULL OR p.is_team=0)
                  AND p.not_use=0
                  AND NOT EXISTS(
                      SELECT * FROM `'.T_TEAM_PLAYERS_LEAGUE.'` tpl_current
                      WHERE tpl_current.league_id='.(int)$league_id.'
                        AND tpl_current.player_id=tpl.player_id
                        AND tpl_current.team_id<>'.(int)$team_id.
                        $turnir_team_filter.'
                  )');
        }

        $after_count = teamplayers_count($team_id, $league_id);
        $added_count = max(0, $after_count - $before_count);
        $prev_league_name = db_field('SELECT name FROM `bs_leagues` WHERE id='.(int)$prev_league_id, 'name');
        $prev_league_title = !empty($prev_league_name) ? $prev_league_name : ('ID '.$prev_league_id);
        $prev_league_title = htmlspecialchars($prev_league_title, ENT_QUOTES, 'UTF-8');

        if ($source_count <= 0) {
            $message = 'У попередній лізі "'.$prev_league_title.'" немає активних гравців цієї команди.';
            $message_class = 'alert-warning';
        } elseif ($added_count > 0) {
            $message = 'Склад заповнено з попередньої ліги "'.$prev_league_title.'". Додано гравців: '.$added_count.' з '.$source_count.'.';
            if ($conflict_count > 0) {
                $message .= ' Пропущено гравців в інших командах: '.$conflict_count.'.';
            }
            $message_class = 'alert-success';
        } elseif ($conflict_count > 0) {
            $message = 'Нових гравців не додано: '.$conflict_count.' гравців вже є в інших командах поточного турніру.';
            $message_class = 'alert-warning';
        } else {
            $message = 'Нових гравців не додано: склад уже заповнений або гравці вже прив\'язані в поточній лізі.';
            $message_class = 'alert-warning';
        }
    }
}

if (!empty($message)) {
    $_SESSION['MESSAGE_AJAX'] = '<div class="alert '.$message_class.'" style="margin:8px 0;">'.$message.'</div>';
}

$post_return = 'teamplayers-list-team_id='.$team_id;
if (!empty($turnir_id)) {
    $post_return .= '&turnir_id='.$turnir_id;
}
if (!empty($league_id)) {
    $post_return .= '&league_id='.$league_id;
}

ObjectRT::setRedirectUrl(array(
    'module' => 'teamplayers',
    'action' => 'list',
    'post_return' => $post_return
));
SystemClass::setAction('list');
SystemClass::setPost_return($post_return);
$_SESSION['POST_RETURN'] = $post_return;

$this->list_show();
?>
