<?php
$form=poste('form');
$id=poste('id');
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