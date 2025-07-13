<?php

;
$club = !empty($_SESSION['gt']['club']) ? $_SESSION['gt']['club'] : 0;
$city = !empty($_SESSION['gt']['city']) ? $_SESSION['gt']['city'] : 0;


$aDataADD = array('sex' => 'm', 'grp' => 52,'club'=>$club,'city_def'=>$city);

$_SESSION['BEFOR_ADD'] = $aDataADD;
