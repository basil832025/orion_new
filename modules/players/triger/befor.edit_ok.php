<?php
$form=poste('form');
$id=poste('id');

// Нормализация числовых полей под strict mode MySQL 8
$normalize_num = function($value, $is_int = false) {
    $value = trim((string)$value);
    if ($value === '') {
        return $is_int ? 0 : '0.00';
    }
    $value = str_replace(',', '.', $value);
    $value = preg_replace('/[^0-9\.\-]/', '', $value);
    if ($value === '' || $value === '-' || !is_numeric($value)) {
        return $is_int ? 0 : '0.00';
    }
    return $is_int ? (int)$value : number_format((float)$value, 2, '.', '');
};

$form['reiting_ukraine'] = $normalize_num(isset($form['reiting_ukraine']) ? $form['reiting_ukraine'] : '0');
$form['start_reiting'] = $normalize_num(isset($form['start_reiting']) ? $form['start_reiting'] : '0');
$_POST['form']['reiting_ukraine'] = $form['reiting_ukraine'];
$_POST['form']['start_reiting'] = $form['start_reiting'];
$_POST['reiting_ukraine'] = $form['reiting_ukraine'];
$_POST['start_reiting'] = $form['start_reiting'];

// Обязательные поля bs_players без default в MySQL 8 strict mode
$club_default = !empty($_SESSION['gt']['club']) ? (int)$_SESSION['gt']['club'] : 0;
$city_default = !empty($_SESSION['gt']['city']) ? (int)$_SESSION['gt']['city'] : 0;
$form['ispara'] = isset($form['ispara']) && $form['ispara'] !== '' ? (int)$form['ispara'] : 0;
$form['player_id_1'] = isset($form['player_id_1']) && $form['player_id_1'] !== '' ? (int)$form['player_id_1'] : 0;
$form['player_id_2'] = isset($form['player_id_2']) && $form['player_id_2'] !== '' ? (int)$form['player_id_2'] : 0;
$form['photo'] = isset($form['photo']) ? (string)$form['photo'] : '';
$form['ligas_photo'] = isset($form['ligas_photo']) ? (string)$form['ligas_photo'] : '';
$form['club'] = isset($form['club']) && $form['club'] !== '' ? (int)$form['club'] : $club_default;
$form['city_def'] = isset($form['city_def']) && $form['city_def'] !== '' ? (int)$form['city_def'] : $city_default;

$_POST['form']['ispara'] = $form['ispara'];
$_POST['form']['player_id_1'] = $form['player_id_1'];
$_POST['form']['player_id_2'] = $form['player_id_2'];
$_POST['form']['photo'] = $form['photo'];
$_POST['form']['ligas_photo'] = $form['ligas_photo'];
$_POST['form']['club'] = $form['club'];
$_POST['form']['city_def'] = $form['city_def'];

$_POST['ispara'] = $form['ispara'];
$_POST['player_id_1'] = $form['player_id_1'];
$_POST['player_id_2'] = $form['player_id_2'];
$_POST['photo'] = $form['photo'];
$_POST['ligas_photo'] = $form['ligas_photo'];
$_POST['club'] = $form['club'];
$_POST['city_def'] = $form['city_def'];
//s($form);
//s('id='.$id);
if (empty($form['name']) || mb_strlen($form['name'])<3)
    window_mess('Введіть ПІБ гравця (мінімум 3 символа) '.mb_strlen($form['name']));
// обеденим клона с главным аккаунтом
if (!empty($id) && !empty($form['player_id']) && $id!=$form['player_id'])
{
    //Поменяем по всем играм
    $sql = 'update '.T_REITING .' set pl_id_1='.$id. ' where pl_id_1='.$form['player_id'];
    db_query($sql);
    $sql = 'update '.T_REITING .' set pl_id_2='.$id. ' where pl_id_2='.$form['player_id'];
    db_query($sql);
    // поменяем по всем игрокам этапа
    $sql = 'update '.T_ETAPS_PLAYER_MESTA .' set player_id='.$id. ' where player_id='.$form['player_id'];
    db_query($sql);
    // поменям по всем игрокам турнира
    $sql = 'update '.T_TURNIR_PLAYERS .' set player_id='.$id. ' where player_id='.$form['player_id'];
    db_query($sql);
    // Удалим клона
    $sql = 'delete from '.T_PLAYERS .'  where id='.$form['player_id'];
    db_query($sql);


}
