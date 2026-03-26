<?php
// Триггер для проверок перед сохранением игрока в команду
$team_id = poste('team_id');
$form = poste('form');
$id = poste('id');
$id = !empty($id) ? $id : 0;

// Проверка: должен быть выбран игрок
if (empty($form['player_id'])) {
    window_mess('Виберіть гравця!');
}

// Проверка: игрок не должен быть командой
if (!empty($form['player_id'])) {
    $check_player = db_row('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.$form['player_id']);
    if (!empty($check_player['is_team']) && $check_player['is_team'] == 1) {
        window_mess('Неможливо додати команду як гравця!');
    }
    
    // Проверка дубликатов: игрок не должен уже быть в этой команде (если это новая запись)
    if (empty($id) || $id == 0) {
        $existing_team = db_field('SELECT team_id FROM `'.T_PLAYERS.'` WHERE id='.$form['player_id'].' AND team_id='.$team_id, 'team_id');
        if (!empty($existing_team)) {
            window_mess('Цей гравець вже є в даній команді!');
        }
    }
}
?>


