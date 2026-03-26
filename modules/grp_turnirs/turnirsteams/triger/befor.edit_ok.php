<?php
// Триггер для проверок перед сохранением команды в турнир
// Создание новой команды обрабатывается в sql/save.php
$turnir_id = poste('turnir_id');
$form = poste('form');
$id = poste('id');
$id = !empty($id) ? $id : 0;

// Проверка: должна быть выбрана команда или указано название новой
if (empty($form['player_id']) && empty($form['new_name'])) {
    window_mess('Виберіть існуючу команду або введіть назву нової команди!');
}

// Проверка дубликатов: команда не должна быть уже добавлена в турнир
if (!empty($form['player_id'])) {
    $sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where id<>'.$id.' and player_id='.$form['player_id'].' and turnir_id='.$turnir_id;
    $cn = db_field($sql,'cnt');
    if ($cn > 0) {
        window_mess('Дана команда вже є в списку команд в даному турнірі');
    }
}
?>

