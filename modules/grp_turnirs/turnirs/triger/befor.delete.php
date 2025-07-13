<?php
$turnir_id=poste('id');
$sql ='select * from '.T_TURNIRS.'  where  id='.$turnir_id;
$aTurnir=db_row($sql);


$sql ='select count(*) as cn from '.T_ETAPS.'  where  turnir_id='.$turnir_id;
$cn_results=db_field($sql,'cn');
if ($cn_results>0 && empty($aTurnir['group_id'])) {
 window_mess('В цьому турнірі є етапи. Видалять спочатку всі етапи!!!=' . $cn_results);
}else {
 if (!empty($aTurnir['group_id'])) {
  $sql = 'delete from ' . T_ETAPS_PLAYER_MESTA . '  where turnir_id=' . $turnir_id;
  db_query($sql);
  $sql = 'delete from ' . T_REITING . '  where turnir_id=' . $turnir_id;
  db_query($sql);
  $sql = 'delete from ' . T_ETAPS . '  where turnir_id=' . $turnir_id;
  db_query($sql);

 }

 $sql = 'delete from ' . T_TURNIR_PLAYERS . ' where turnir_id=' . $turnir_id;
 db_query($sql);
}

?>
