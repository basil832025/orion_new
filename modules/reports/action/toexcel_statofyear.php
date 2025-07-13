<?php
/** Error reporting */
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
 define('PATH', '');
define('ROOT', $_SERVER['DOCUMENT_ROOT'] .(substr($_SERVER['DOCUMENT_ROOT'],-1)=='/' ? '' : '/') .(PATH=='' || (substr(PATH, -1)=='/') ? PATH : PATH .'/'));

  // путь к веб-сайту
  define('URL', 'https://' .$_SERVER['SERVER_NAME'] .(substr(PATH,0,1)=='/' ? '' : '/') .(PATH=='' || (substr(PATH, -1)=='/') ? PATH : PATH .'/'));

date_default_timezone_set('Europe/Kiev');
// 
define('ROOT_A', ROOT .'');
  // путь к веб-админке
  define('URL_A',  URL .'');
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
include_once ROOT_A.'config/access.php';
include_once ROOT_A.'config/const_admin.php';
//include_once ROOT_A.'config/const.bas';
 include_once ROOT_A.'func/main_func.php';
  include_once ROOT_A.'func/mysql.php';
    include_once ROOT_A.'func/error_func.php';
     include_once ROOT_A.'config/const_db.php';
     include_once ROOT_A.'modules/reports/func/func.reports.php';
//
require_once ROOT_A.'libs/phpexcel/PHPExcel.php';
//echo ROOT_A;exit;
//$validLocale = PHPExcel_Settings::setLocale('ru');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//echo 'tyt';exit;
// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");

$year = gete('year');
$city = gete('city');
$club = gete('club');
$_SESSION['statofyaer']['filter']['city'] = isset($city)  && $city !='' ? $city : (!empty($_SESSION['statofyaer']['filter']['city']) ? $_SESSION['statofyaer']['filter']['city'] : '0');
$_SESSION['statofyaer']['filter']['club'] = isset($club) && $club !='' ? $club : (!empty($_SESSION['statofyaer']['filter']['club']) ? $_SESSION['statofyaer']['filter']['club'] : '0');
if (empty($city)) $txtCity= 'Всі міста';
else
{
    $sql = 'select value from `'.T_SPRLIST_VALUES.'` where id='.$city;
    //   echo ($sql);exit;
    $txtCity = db_field($sql,'value');
}
if (empty($club)) $txtClub= 'Всі клуби';
else
{
    $sql = 'select value from `' . T_SPRLIST_VALUES . '` where id=' . $club;
    $txtClub = db_field($sql, 'value');
}
// Rename worksheet
//$objPHPExcel->getActiveSheet()->setTitle('Діти середні');
$name_period = ' за  '.$year.' рік  '.$txtCity. ' '.$txtClub;

$objWorkSheet = $objPHPExcel->createSheet(0); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Діти-молодша',$name_period);
$sqlgrp = 'p.grp=45';// діти середняв
s('$sqlgrp='.$sqlgrp);
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}

$objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Діти середні',$name_period);
$sqlgrp = 'p.grp=46';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}


$objWorkSheet = $objPHPExcel->createSheet(2); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Діти старші',$name_period);
$sqlgrp = 'p.grp=47';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}



$objWorkSheet = $objPHPExcel->createSheet(3); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Діти-ранок',$name_period);
$sqlgrp = 'p.grp=49';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}

$objWorkSheet = $objPHPExcel->createSheet(4); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'ШВСМ',$name_period);
$sqlgrp = 'p.grp=48';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}

$objWorkSheet = $objPHPExcel->createSheet(5); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Дорослі',$name_period);
$sqlgrp = 'p.grp=51';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers) )
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}

$objWorkSheet = $objPHPExcel->createSheet(6); //Setting index when creating
get_zagolovok_statofyear($objWorkSheet,'Загальна',$name_period);
$sqlgrp = 'p.grp=52';// діти середняв
$aUsers = getSQLStatOfYear($year,$sqlgrp);

if (!empty($aUsers))
{
    set_data_statOfYear($objWorkSheet,$aUsers);
}





$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2003)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'Статистика_за_рік_'.DATE('dmy_Hi').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
