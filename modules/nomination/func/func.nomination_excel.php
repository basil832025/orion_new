<?php
function get_zagolovok_nomination_progress($objWorkSheet,$name='',$name_period='')
{
    $objWorkSheet->getColumnDimension('A')->setWidth(6);
    $objWorkSheet->getColumnDimension('B')->setWidth(60);
    $objWorkSheet->getColumnDimension('C')->setWidth(20);
    $objWorkSheet->getColumnDimension('D')->setWidth(20);
    $objWorkSheet->getColumnDimension('E')->setWidth(20);
    $objWorkSheet->getColumnDimension('F')->setWidth(20);
    $objWorkSheet->getColumnDimension('G')->setWidth(20);
    $objWorkSheet->getStyle("A1:G1")->getFont()->setSize(16);
    $objWorkSheet->setCellValue('A1', $name.$name_period);
    $objWorkSheet->setTitle($name);
    $objWorkSheet->setCellValue('A2', '№')
        ->setCellValue('B2', 'ПІБ гравця')
        ->setCellValue('C2', 'Приріст рейтингу')
        ->setCellValue('D2', 'Турнірів')
        ->setCellValue('E2', 'Кількість перемог')
        ->setCellValue('F2', 'Кількість поразок')
        ->setCellValue('G2', '% перемог')
    ;

}
function get_zagolovok_nomination_bestPlayer($objWorkSheet,$name='',$name_period='')
{
    $objWorkSheet->getColumnDimension('A')->setWidth(6);
    $objWorkSheet->getColumnDimension('B')->setWidth(60);
    $objWorkSheet->getColumnDimension('C')->setWidth(20);
    $objWorkSheet->getColumnDimension('D')->setWidth(20);
    $objWorkSheet->getColumnDimension('E')->setWidth(20);
    $objWorkSheet->getColumnDimension('F')->setWidth(20);
    $objWorkSheet->getColumnDimension('G')->setWidth(20);
    $objWorkSheet->getStyle("A1:G1")->getFont()->setSize(16);
    $objWorkSheet->setCellValue('A1', $name.$name_period);
    $objWorkSheet->setTitle($name);
    $objWorkSheet->setCellValue('A2', '№')
        ->setCellValue('B2', 'ПІБ гравця')
        ->setCellValue('C2', 'Кількість зіграних
турнірів')
        ->setCellValue('D2', 'Кількість зіграних
ігор')
        ->setCellValue('E2', 'Кількість зіграних
сетів')
        ->setCellValue('F2', 'Кількість перемог')
        ->setCellValue('G2', '% перемог')
    ;

}
function set_data_nominationProgress($objWorkSheet,$aUsers)
{
    $name='';
    $numSheet = 3;
    $n=1;
    $sm_turnirs=0;
    foreach ($aUsers as $user)
    {
        $objWorkSheet->setCellValue('A'.$numSheet, $n)
            ->setCellValue('B'.$numSheet, $user['name'])
            ->setCellValue('C'.$numSheet, $user['diff_reit'])
            ->setCellValue('D'.$numSheet, $user['cnt_turnirs'])
            ->setCellValue('E'.$numSheet, $user['cnt_wins'])
            ->setCellValue('F'.$numSheet, $user['cnt_lose'])
            ->setCellValue('G'.$numSheet, $user['proc_wins']);
        $n++;$numSheet++;
    }

}
function set_data_nominationBestPlayer($objWorkSheet,$aUsers)
{
    $name='';
    $numSheet = 3;
    $n=1;
    $sm_turnirs=0;

    foreach ($aUsers as $user)
    {
        $objWorkSheet->setCellValue('A'.$numSheet, $n)
            ->setCellValue('B'.$numSheet, $user['name'])
            ->setCellValue('C'.$numSheet, $user['cnt_turnirs'])
            ->setCellValue('D'.$numSheet, $user['cnt_games'])
            ->setCellValue('E'.$numSheet, $user['cnt_sets'])
            ->setCellValue('F'.$numSheet, $user['cnt_wins'])
            ->setCellValue('G'.$numSheet, $user['proc_wins']);
        $n++;$numSheet++;
    }
 //   $cn_players=$n-1;

   /* $objWorkSheet->setCellValue('A'.$numSheet, '')
        ->setCellValue('B'.$numSheet, 'Загалом:')
        ->setCellValue('C'.$numSheet, 'кількість гравців: '.($cn_players))
        ->setCellValue('D'.$numSheet, 'відвідано турнірів: '.$sm_turnirs);*/

}