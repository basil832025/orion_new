<?php
$_SESSION['kernel']['action'] = (!empty($_SESSION['kernel']['action']) ? $_SESSION['kernel']['action'] :
    'list');

include_once 'object.turnirsteams.php';
$ObjTurnirsTeams = new TurnirsTeamsObject();
$ObjTurnirsTeams->init();
?>


