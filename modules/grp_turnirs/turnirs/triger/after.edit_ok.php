<?php
$aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
$turnir_id=$_POST['id'] ?? [];
$tables = $_POST['tableList'] ?? [];
$ins_id = $_SESSION['last_insert_id'];;
$turnir_id = $turnir_id ?: $_SESSION['last_insert_id'];

if (!empty($tables) && !empty($turnir_id)) {
    $list = implode(',', array_map('intval', $tables)); // строка вида "1,2,3"
    $sql = 'update bs_turnirs set selected_tables="'.$list.'" where id='.$turnir_id;
    db_query($sql);

}