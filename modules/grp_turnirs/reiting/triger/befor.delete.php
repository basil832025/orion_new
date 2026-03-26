<?php
$id=poste('id');
$turnir_id=poste('turnir_id');
$sql ='select auto, match_id, pair_number from '.T_REITING.'  where id='.$id.' and turnir_id='.$turnir_id;
$game = db_row($sql);
$auto = !empty($game['auto']) ? (int)$game['auto'] : 0;
$match_id = !empty($game['match_id']) ? $game['match_id'] : '';
$pair_number = !empty($game['pair_number']) ? (int)$game['pair_number'] : 0;

// Проверяем, является ли игра автоматически созданной:
// 1. Поле auto > 0
// 2. Или есть match_id и pair_number (командные игры, созданные автоматически)
if ($auto > 0 || (!empty($match_id) && $pair_number > 0)) {
    // window_mess() прерывает выполнение через Ajax() и показывает модальное окно
    // Не нужно менять действие - window_mess() сам прервет выполнение
    window_mess('Данная игра создана автоматически. Удалять нельзя!');
    // window_mess() вызывает Ajax() который делает exit, поэтому код ниже не выполнится
}

?>
