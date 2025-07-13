<?php


//$turnir_id=poste('turnir_id');
$league_id = poste('league_id');
$aDataADD = array('is_shablon3'=>1,'is_shablon2'=>1,'is_no_send_ligas'=>1,'league_id'=>$league_id);

$_SESSION['BEFOR_ADD']=$aDataADD;