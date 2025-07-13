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
//
require_once ROOT_A.'libs/phpexcel/PHPExcel.php';
//echo ROOT_A;exit;

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


// Add some data
//$titleText = '{Id203}';
//$objPHPExcel->setActiveSheetIndex(0)
 //           ->setCellValue('A1', $titleText);
//$aSheet = $objPHPExcel->getActiveSheet();
//$aSheet->getStyleByColumnAndRow(1,3)->getSelectedCells()->setWidth('100px');
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A1', 'tournament_id');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A2', 'Наименование товара');
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('B1', 'tournament_name');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('B2', 'Ед. изм.');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('C1', 'tournament_date');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('D1', 'match_id');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('E1', 'user1_ligas_id');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('F1', 'user1_name');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('G1', 'user1_date');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('H1', 'user1_city');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('I1', 'user2_ligas_id');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('J1', 'user2_name');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('K1', 'user2_date');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('L1', 'user2_city');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('M1', 'user1_score');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('N1', 'user2_score');
$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('O1', 'match_status');
//$objPHPExcel->setActiveSheetIndex(0)  ->setCellValue('C2', 'Кол-во');
//$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('D2', 'Количество');

$turnir_id = gete('id');

   $sql ='SELECT t.id,t.name as turn_name,t.dat,r.id as match_id, 
(select id_reiting from bs_players where id=r.pl_id_1) as user1_ligas_id,
(select name_ligas from bs_players where id=r.pl_id_1) as user1_name,
(select god_rogd from bs_players where id=r.pl_id_1) as user1_date,
(select city from bs_players where id=r.pl_id_1) as user1_city,
(select id_reiting from bs_players where id=r.pl_id_2) as user2_ligas_id,
(select name_ligas from bs_players where id=r.pl_id_2) as user2_name,
(select god_rogd from bs_players where id=r.pl_id_2) as user2_date,
(select city from bs_players where id=r.pl_id_2) as user2_city,
set_1 as user1_score,
 set_2 as user2_score 
from bs_turnirs t,bs_reiting r where t.id='.$turnir_id.' and r.turnir_id=t.id and r.no_send=0 order by r.id';
 $aPlayersRes = db_list($sql);
 


if (!empty($aPlayersRes)){
//$objPHPExcel->setActiveSheetIndex(0)
    $i=2;
   // p ($_SESSION['all_b52']);
     foreach($aPlayersRes  as $k=> $v){

   $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(0,$i,$v['id']); 
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(1,$i,$v['turn_name']);      
          
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(2,$i,$v['dat']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(3,$i,$v['match_id']);           
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(4,$i,$v['user1_ligas_id']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(5,$i,$v['user1_name']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(6,$i,$v['user1_date']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(7,$i,$v['user1_city']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(8,$i,$v['user2_ligas_id']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(9,$i,$v['user2_name']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(10,$i,$v['user2_date']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(11,$i,$v['user2_city']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(12,$i,$v['user1_score']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(13,$i,$v['user2_score']);        
    $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(14,$i,'finished');        
          
   // $objPHPExcel->setActiveSheetIndex(0) ->setCellValueByColumnAndRow(3,$i,$v['cnt_tovs']);        
     $i++;

    }
}


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('PlayersResults');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2003)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'PlayersResults_'.DATE('dmy_Hi').'_CLUB_VOLIA_KIEV.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
