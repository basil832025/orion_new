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
     include_once ROOT_A.'modules/nomination/func/func.nomination.php';
//
require_once ROOT_A.'libs/phpexcel/PHPExcel.php';
//echo ROOT_A;exit;
//$validLocale = PHPExcel_Settings::setLocale('ru');

// Create new PHPExcel object


    $dbeg = gete('dbeg');
$dend = gete('dend');
$type = gete('type');
$city = gete('city');
$club = gete('club');
$_SESSION['nomination']['filter']['city'] = isset($city) ? $city : (!empty($_SESSION['nomination']['filter']['city']) ? $_SESSION['nomination']['filter']['city'] : '0');
$_SESSION['nomination']['filter']['club'] = isset($club) ? $club : (!empty($_SESSION['nomination']['filter']['club']) ? $_SESSION['nomination']['filter']['club'] : '0');

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
//$this->getYesrs();
if (empty($type) || $type=='pm')
{
    $type='pm';
    bestPrurist($dbeg,$dend,$txtCity,$txtClub);
}
else
    bestPlayers($dbeg,$dend,$txtCity,$txtClub);


function bestPlayers ($dbeg,$dend,$city='',$club='')
{
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


    $name_period = ' з '.date_for_firebird_format($dbeg).' по '.date_for_firebird_format($dend). ' '.$city. ' '.$club;
    $objWorkSheet = $objPHPExcel->createSheet(0); //Setting index when creating

    get_zagolovok_nomination_bestPlayer($objWorkSheet,'Діти-молодша',$name_period);
    $sqlgrp = 'p.grp=45';// діти молодша
    $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationBestPlayer($objWorkSheet,$aUsers);
    }
    $objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating

    get_zagolovok_nomination_bestPlayer($objWorkSheet,'Діти-середня',$name_period);
    $sqlgrp = 'p.grp=46';// діти середняв
    $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationBestPlayer($objWorkSheet,$aUsers);
    }

    $objWorkSheet = $objPHPExcel->createSheet(2); //Setting index when creating

    get_zagolovok_nomination_bestPlayer($objWorkSheet,'Діти-старша+Діти-ранок',$name_period);
    $sqlgrp = 'p.grp=47 or p.grp=49';// діти старша та ранок
    $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationBestPlayer($objWorkSheet,$aUsers);
    }


    $objWorkSheet = $objPHPExcel->createSheet(3); //Setting index when creating

    get_zagolovok_nomination_bestPlayer($objWorkSheet,'Дорослі+ШВСМ',$name_period);
    $sqlgrp = 'p.grp=48 or p.grp=51';// дорослі та ВСМ
    $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationBestPlayer($objWorkSheet,$aUsers);
    }

    $objWorkSheet = $objPHPExcel->createSheet(4); //Setting index when creating

    get_zagolovok_nomination_bestPlayer($objWorkSheet,'Загальна',$name_period);
    $sqlgrp = 'p.grp=52';// загальна
    $aUsers = getSQLBestPlayer($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationBestPlayer($objWorkSheet,$aUsers);
    }

    $objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2003)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.'Найактивніший гравець'.DATE('dmy_Hi').'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}
function bestPrurist ($dbeg,$dend,$city='',$club='')
{
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
    $name_period = ' з '.date_for_firebird_format($dbeg).' по '.date_for_firebird_format($dend). ' '.$city. ' '.$club;

    $objWorkSheet = $objPHPExcel->createSheet(0); //Setting index when creating
    get_zagolovok_nomination_progress($objWorkSheet,'Діти-молодша',$name_period);
    $sqlgrp = 'p.grp=45';// діти молодша
    $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationProgress($objWorkSheet,$aUsers);
    }

    $objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
    get_zagolovok_nomination_progress($objWorkSheet,'Діти-середня',$name_period);
    $sqlgrp = 'p.grp=46';// діти середняв
    $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationProgress($objWorkSheet,$aUsers);
    }
    $objWorkSheet = $objPHPExcel->createSheet(2); //Setting index when creating

    get_zagolovok_nomination_progress($objWorkSheet,'Діти-старша+Діти-ранок',$name_period);
    $sqlgrp = 'p.grp=47 or p.grp=49';// діти старша та ранок
    $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationProgress($objWorkSheet,$aUsers);
    }

    $objWorkSheet = $objPHPExcel->createSheet(3); //Setting index when creating

    get_zagolovok_nomination_progress($objWorkSheet,'Дорослі+ШВСМ',$name_period);
    $sqlgrp = 'p.grp=48 or p.grp=51';// дорослі та ВСМ
    $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationProgress($objWorkSheet,$aUsers);
    }

    $objWorkSheet = $objPHPExcel->createSheet(4); //Setting index when creating

    get_zagolovok_nomination_progress($objWorkSheet,'Загальна',$name_period);
    $sqlgrp = 'p.grp=52';// загальна
    $aUsers = getSQLBestDiff($dbeg,$dend,$sqlgrp);

    if (!empty($aUsers))
    {
        set_data_nominationProgress($objWorkSheet,$aUsers);
    }
  $objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2003)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.'Прогрес місяця'.DATE('dmy_Hi').'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}



