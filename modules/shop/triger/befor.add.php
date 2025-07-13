<?php


$type_id=poste('type_id');


    $aDataADD = array('cnt'=>1,'type_tov'=>$type_id,'date_shop'=>date('d.m.Y'),'date_start'=>date('d.m.Y'));

$_SESSION['BEFOR_ADD']=$aDataADD;
 ?>   