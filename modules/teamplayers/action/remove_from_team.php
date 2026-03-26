<?php
// Удаление игрока из команды (обнуление team_id)
$id = poste('id');
$team_id = poste('team_id');

if (!empty($id) && !empty($team_id)) {
    // Проверяем, что игрок действительно в этой команде
    $player = db_row('SELECT id, team_id FROM `'.T_PLAYERS.'` WHERE id='.$id.' AND team_id='.$team_id);
    if (!empty($player)) {
        // Обнуляем team_id
        db_query('UPDATE `'.T_PLAYERS.'` SET team_id=NULL WHERE id='.$id);
        // Не показываем модальное окно - просто обновляем список
        // window_mess('Гравця видалено з команди'); // Убрано
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
        'post_return' => 'teamplayers-list-team_id='.$team_id
    );
    SystemClass::setAction('list'); // Устанавливаем action=list для корректного отображения списка
}

$this->list_show();

// После list_show() устанавливаем правильный post_return для редиректа
if (!empty($_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID'])) {
    $team_id_return = $_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID'];
    $post_return_final = 'teamplayers-list-team_id='.$team_id_return;
    SystemClass::setPost_return($post_return_final);
    $_SESSION['POST_RETURN'] = $post_return_final;
    unset($_SESSION['TEAMPLAYERS_REMOVE_TEAM_ID']);
}
?>


