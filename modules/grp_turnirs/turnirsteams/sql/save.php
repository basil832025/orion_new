<?php
 $turnir_id = (int)poste('turnir_id');
 $league_id = (int)poste('league_id');
  $id = (int)poste('id');
  $form = SystemClass::getAFormPost();
  
$mesto = !empty($form['mesto']) ? $form['mesto'] : 0;
$where = 'turnir_id='.$turnir_id.', ispara=0, player_id_1=0, player_id_2=0, cnt_sets=0, cnt_sets_win=0, cnt_sets_lose=0, mesto="'.$mesto.'", league_id="'.$league_id.'"';

// Создаем новую команду, если указано new_name
$player_id = 0;
if (!empty($form['new_name']) && (empty($form['player_id']) || $form['player_id'] == 0)) {
    // Создаем новую команду в bs_players
    $new_name_escaped = addslashes(trim($form['new_name']));
    $club_default = !empty($_SESSION['gt']['club']) ? (int)$_SESSION['gt']['club'] : 0;
    $city_default = !empty($_SESSION['gt']['city']) ? (int)$_SESSION['gt']['city'] : 0;
    db_query("INSERT INTO " . T_PLAYERS . " SET name='" . $new_name_escaped . "', is_team=1, not_use=0, ispara=0, player_id_1=0, player_id_2=0, photo='', ligas_photo='', club=".$club_default.", city_def=".$city_default.", dat=now()");
    $player_id = db_insert_id();
} elseif (!empty($form['player_id']) && $form['player_id'] > 0) {
    $player_id = $form['player_id'];
}

if ($player_id > 0) {
    $where .= ' , player_id='.$player_id;
    
    //если это изменения
    if (!empty($id) && $id>0) 
    {
     db_query('Update '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id);
     }
    else
    {
     db_query('INSERT INTO '.T_TURNIR_PLAYERS.' SET '.$where);
     setPlayersLigasInfo($turnir_id, $player_id);
    }

    $post_return = 'turnirsteams-list-&turnir_id='.$turnir_id;
    if (!empty($league_id)) {
        $post_return .= '&league_id='.$league_id;
    }
    ObjectRT::setRedirectUrl(array(
        'module' => 'turnirsteams',
        'action' => 'list',
        'post_return' => $post_return
    ));
    SystemClass::setPost_return($post_return);
    $_SESSION['POST_RETURN'] = $post_return;
} else {
    window_mess('Виберіть існуючу команду або введіть назву нової команди!');
}

?>
