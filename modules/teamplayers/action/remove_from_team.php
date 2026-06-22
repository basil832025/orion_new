<?php
require_once __DIR__ . '/../func/func.teamplayers.php';
// Удаление игрока из команды (обнуление team_id)
$id = poste('id');
$team_id = teamplayers_request_param('team_id', 'TEAMPLAYERS_SAVE_TEAM_ID');
$turnir_id = teamplayers_request_param('turnir_id', 'TEAMPLAYERS_SAVE_TURNIR_ID');
$league_id = teamplayers_resolve_league_id(teamplayers_request_param('league_id', 'TEAMPLAYERS_SAVE_LEAGUE_ID'), $turnir_id);

if (!empty($id) && !empty($team_id)) {
    if (!empty($league_id)) {
        db_query('DELETE FROM `'.T_TEAM_PLAYERS_LEAGUE.'` WHERE league_id='.(int)$league_id.' AND team_id='.(int)$team_id.' AND player_id='.(int)$id);
    } else {
        // Проверяем, что игрок действительно в этой команде
        $player = db_row('SELECT id, team_id FROM `'.T_PLAYERS.'` WHERE id='.$id.' AND team_id='.$team_id);
        if (!empty($player)) {
            db_query('UPDATE `'.T_PLAYERS.'` SET team_id=NULL WHERE id='.$id);
        }
    }
    // Даже если игрок не найден, не показываем ошибку - просто обновляем список
}

// Устанавливаем редирект на список игроков команды
if (!empty($team_id)) {
    // Сохраняем team_id для установки после list_show()
    $_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID'] = $team_id;
    // Устанавливаем RedirectUrl через метод-сеттер
    ObjectRT::setRedirectUrl(array(
        'module' => 'teamplayers',
        'action' => 'list',
        'post_return' => 'teamplayers-list-team_id='.$team_id.(!empty($turnir_id) ? '&turnir_id='.$turnir_id : '').(!empty($league_id) ? '&league_id='.$league_id : '')
    ));
    SystemClass::setAction('list'); // Устанавливаем action=list для корректного отображения списка
}

$this->list_show();

// После list_show() устанавливаем правильный post_return для редиректа
if (!empty($_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID'])) {
    $team_id_return = $_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID'];
    $post_return_final = 'teamplayers-list-team_id='.$team_id_return;
    if (!empty($turnir_id)) {
        $post_return_final .= '&turnir_id='.$turnir_id;
    }
    if (!empty($league_id)) {
        $post_return_final .= '&league_id='.$league_id;
    }
    SystemClass::setPost_return($post_return_final);
    $_SESSION['POST_RETURN'] = $post_return_final;
    unset($_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID']);
}
?>
