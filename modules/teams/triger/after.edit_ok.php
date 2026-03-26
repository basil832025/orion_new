<?php
// Исправляем is_team=1 для команд после сохранения, если по какой-то причине оно не было установлено
// Это дополнительная защита на случай, если триггер befor.edit_ok.php не сработал правильно
$id = poste('id');
if (!empty($id) && is_numeric($id)) {
    $team_id = (int)$id;
    // Проверяем, что это команда (is_team должен быть 1)
    $current_is_team = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$team_id, 'is_team');
    if ($current_is_team != 1) {
        // Исправляем значение
        db_query('UPDATE `'.T_PLAYERS.'` SET is_team=1 WHERE id='.$team_id);
    }
}
