<?php

$turnir_id=poste('turnir_id');
$jsonGame=isset($_POST['jsonGame']) ? $_POST['jsonGame'] : '';

//s('$jsonGame='.$jsonGame);
$jsonGame = json_decode($jsonGame);

//s('$jsonGame');
//s($jsonGame);

$sql = 'select tables,dat from '.T_TURNIRS.' t where t.id='.$turnir_id;
$aTables = db_row($sql);
$tables_cnt = $aTables['tables'];
$dat = $date = date('Y-m-d');
// выводим таблицы
$aJson = getTablesAll($tables_cnt,$turnir_id,$dat,true,$jsonGame);


$aJson = json_encode($aJson,JSON_UNESCAPED_UNICODE );
if (!empty($aJson)) {
    $_SESSION['CONTENT_AJAX'] = $aJson;
    $_SESSION['MESSAGE_AJAX']='';
}  else
{
    $_SESSION['MESSAGE_AJAX']='';
    $_SESSION['CONTENT_AJAX']='777';
}

