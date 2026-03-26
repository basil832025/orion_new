<?php
$_SESSION['kernel']['action'] = (!empty($_SESSION['kernel']['action']) ? $_SESSION['kernel']['action'] :
    'list');

include_once 'object.teamplayers.php';
$ObjTeamPlayers = new TeamPlayersObject();
$ObjTeamPlayers->init();


