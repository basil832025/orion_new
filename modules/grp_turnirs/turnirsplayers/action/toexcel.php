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
//echo  ROOT_A.'func/main_func.bas';
require_once ROOT_A.'libs/phpexcel/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// Add some data
//$titleText = '{Id203}';
//$objPHPExcel->setActiveSheetIndex(0)
 //           ->setCellValue('A1', $titleText);
//$aSheet = $objPHPExcel->getActiveSheet();
//$aSheet->getStyleByColumnAndRow(1,3)->getSelectedCells()->setWidth('100px');
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A1', 'ФИО нового игрока');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A2', 'Наименование товара');
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('B1', 'ID Ligas');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('B2', 'Ед. изм.');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('C1', 'Город');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('D1', 'Год рождения');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('E1', 'Лет');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('F1', 'Игрок < 18');
//$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('C2', 'Кол-во');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('D2', 'Количество');
//print_r($_GET); exit;
$turnir_id = gete('id');
//echo 'tyt';exit;
   $sql ='SELECT p.`name`,p.city,p.god_rogd,p.id_reiting,t.is_opl_this,YEAR(NOW())-god_rogd as let FROM `bs_turnirplayers` t,bs_players p where turnir_id='.$turnir_id.' and t.player_id=p.id 
and (   is_opl_this=1)
order by name'; 
//exit;
 $aNewPlayers = db_list($sql);
 //exit();


if (!empty($aNewPlayers)){
//$objPHPExcel->setActiveSheetIndex(0)
    $i=2;
   // p ($_SESSION['all_b52']);
     foreach($aNewPlayers  as $k=> $v){

    if ($v['let']<18) $is18= 'Да'; else $is18='';
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(0,$i,$v['name']); 
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(1,$i,$v['id_reiting']);      
          
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(2,$i,$v['city']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(3,$i,$v['god_rogd']);           
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(4,$i,$v['let']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(5,$i,$is18);        
   // $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(3,$i,$v['cnt_tovs']);        
     $i++;

    }
}


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('NewPlayers');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2003)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'NewPlayers_'.DATE('dmy_Hi').'_CLUB_VOLIA_KIEV.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
