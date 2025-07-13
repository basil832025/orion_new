<?php
function get_zagolovok_statofyear($objWorkSheet,$name='',$name_period='')
{
  //  s('sasas');
    $objWorkSheet->getColumnDimension('A')->setWidth(6);
    $objWorkSheet->getColumnDimension('B')->setWidth(60);
    $objWorkSheet->getColumnDimension('C')->setWidth(50);
  //  $objWorkSheet->getColumnDimension('D')->setWidth(80);
    $objWorkSheet->getStyle("A1:P1")->getFont()->setSize(16);
    $objWorkSheet->setCellValue('A1', $name.$name_period);
    $objWorkSheet->setTitle($name);
    $objWorkSheet->setCellValue('A2', '№')
        ->setCellValue('B2', 'ПІБ гравця')
        ->setCellValue('C2', 'Телефон')
        ->setCellValue('D2', 'Всього')
        ->setCellValue('E2', 'Січень')
        ->setCellValue('F2', 'Лютий')
        ->setCellValue('G2', 'Березень')
        ->setCellValue('H2', 'Квітень')
        ->setCellValue('I2', 'Травень')
        ->setCellValue('J2', 'Червень')
        ->setCellValue('K2', 'Липень')
        ->setCellValue('L2', 'Серпень')
        ->setCellValue('M2', 'Вересень')
        ->setCellValue('N2', 'Жовтень')
        ->setCellValue('O2', 'Листопад')
        ->setCellValue('P2', 'Грудень')
    ;

}
function get_zagolovok_counts_turnits($objWorkSheet,$name='',$name_period='')
{
    $objWorkSheet->getColumnDimension('A')->setWidth(6);
    $objWorkSheet->getColumnDimension('B')->setWidth(60);
    $objWorkSheet->getColumnDimension('C')->setWidth(50);
    $objWorkSheet->getColumnDimension('D')->setWidth(80);
    $objWorkSheet->getStyle("A1:G1")->getFont()->setSize(16);
    $objWorkSheet->setCellValue('A1', $name.$name_period);
    $objWorkSheet->setTitle($name);
    $objWorkSheet->setCellValue('A2', '№')
        ->setCellValue('B2', 'ПІБ гравця')
        ->setCellValue('C2', 'Телефон/
Дата турніра')
        ->setCellValue('D2', 'Відвідав турнірів/
Назва турніра');

}

function set_data_counts_turnits($objWorkSheet,$aUsers,$aUsersNO)
{
    $name='';
    $numSheet = 3;
    $n=1;
    $sm_turnirs=0;
    foreach ($aUsers as $user)
    { $txt_name='';
        $txt_phone_dat = $user['dat'];
        $txt_turnir_name=$user['turnir_name'];
        if ($name!=$user['name']) {
            $sm_turnirs=$sm_turnirs+$user['cnt_turnirs'];
            $objWorkSheet->setCellValue('A'.$numSheet, $n)
                ->setCellValue('B'.$numSheet, $user['name'])
                ->setCellValue('C'.$numSheet, $user['phone'])
                ->setCellValue('D'.$numSheet, $user['cnt_turnirs']);

            $name=$user['name'];$n++;$nt=1;$numSheet++;
        }
        $objWorkSheet->setCellValue('A'.$numSheet, '')
            ->setCellValue('B'.$numSheet, $nt)
            ->setCellValue('C'.$numSheet, $user['dat'])
            ->setCellValue('D'.$numSheet, $user['turnir_name']);
        $nt++;$numSheet++;
    }
    $cn_players=$n-1;
    foreach ($aUsersNO as $user)
    {
        $objWorkSheet->setCellValue('A'.$numSheet, $n)
            ->setCellValue('B'.$numSheet, $user['name'])
            ->setCellValue('C'.$numSheet, $user['phone'])
            ->setCellValue('D'.$numSheet, $user['cnt_turnirs']);

        $n++;$numSheet++;
    }
    $objWorkSheet->setCellValue('A'.$numSheet, '')
        ->setCellValue('B'.$numSheet, 'Загалом:')
        ->setCellValue('C'.$numSheet, 'кількість гравців: '.($cn_players))
        ->setCellValue('D'.$numSheet, 'відвідано турнірів: '.$sm_turnirs);

}
function set_data_new_users($objWorkSheet,$aUsers)
{
    $name='';
    $numSheet = 3;
    $n=1;
    $sm_turnirs=0;
    foreach ($aUsers as $user)
    { $txt_name='';
        $txt_phone_dat = $user['dat'];
        $txt_turnir_name=$user['turnir_name'];
        if ($name!=$user['name']) {
            $sm_turnirs=$sm_turnirs+$user['cnt_turnirs'];
            $objWorkSheet->setCellValue('A'.$numSheet, $n)
                ->setCellValue('B'.$numSheet, $user['name'])
                ->setCellValue('C'.$numSheet, $user['phone'])
                ->setCellValue('D'.$numSheet, $user['cnt_turnirs']);

            $name=$user['name'];$n++;$nt=1;$numSheet++;
        }
        $objWorkSheet->setCellValue('A'.$numSheet, '')
            ->setCellValue('B'.$numSheet, $nt)
            ->setCellValue('C'.$numSheet, $user['dat'])
            ->setCellValue('D'.$numSheet, $user['turnir_name']);
        $nt++;$numSheet++;
    }
    $cn_players=$n-1;

    $objWorkSheet->setCellValue('A'.$numSheet, '')
        ->setCellValue('B'.$numSheet, 'Загалом:')
        ->setCellValue('C'.$numSheet, 'кількість гравців: '.($cn_players))
        ->setCellValue('D'.$numSheet, 'відвідано турнірів: '.$sm_turnirs);

}
function set_data_statOfYear($objWorkSheet,$aUsers)
{
    $name='';
    $numSheet = 3;
    $n=1;
    $sm_turnirs=0;
    $cnt_all=0;$cnt_1 =0;
    $cnt_2 =0;$cnt_3 =0;$cnt_4 =0;$cnt_5 =0;$cnt_6 =0;$cnt_7 =0;$cnt_8 =0;
    $cnt_9 =0;$cnt_10 =0;$cnt_11 =0;$cnt_12 =0;
    foreach ($aUsers as $user) {
        $cnt_all = $cnt_all + $user['cnt_all'];
        $cnt_1 = $cnt_1 + $user['cnt_1'];
        $cnt_2 = $cnt_2 + $user['cnt_2'];
        $cnt_3 = $cnt_3 + $user['cnt_3'];
        $cnt_4 = $cnt_4 + $user['cnt_4'];
        $cnt_5 = $cnt_5 + $user['cnt_5'];
        $cnt_6 = $cnt_6 + $user['cnt_6'];
        $cnt_7 = $cnt_7 + $user['cnt_7'];
        $cnt_8 = $cnt_8 + $user['cnt_8'];
        $cnt_9 = $cnt_9 + $user['cnt_9'];
        $cnt_10 = $cnt_10 + $user['cnt_10'];
        $cnt_11 = $cnt_11 + $user['cnt_11'];
        $cnt_12 = $cnt_12 + $user['cnt_12'];
        $objWorkSheet->setCellValue('A' . $numSheet, $n)
            ->setCellValue('B' . $numSheet, $user['name'])
            ->setCellValue('C' . $numSheet, $user['phone'])
            ->setCellValue('D' . $numSheet, $user['cnt_all'])
            ->setCellValue('E' . $numSheet, $user['cnt_1'])
            ->setCellValue('F' . $numSheet, $user['cnt_2'])
            ->setCellValue('G' . $numSheet, $user['cnt_3'])
            ->setCellValue('H' . $numSheet, $user['cnt_4'])
            ->setCellValue('I' . $numSheet, $user['cnt_5'])
            ->setCellValue('J' . $numSheet, $user['cnt_6'])
            ->setCellValue('K' . $numSheet, $user['cnt_7'])
            ->setCellValue('L' . $numSheet, $user['cnt_8'])
            ->setCellValue('M' . $numSheet, $user['cnt_9'])
            ->setCellValue('N' . $numSheet, $user['cnt_10'])
            ->setCellValue('O' . $numSheet, $user['cnt_11'])
            ->setCellValue('P' . $numSheet, $user['cnt_12']);
        $n++;$numSheet++;
    }
    $objWorkSheet->setCellValue('A' . $numSheet, '')
        ->setCellValue('B' . $numSheet, '')
        ->setCellValue('C' . $numSheet, 'Загалом:')
        ->setCellValue('D' . $numSheet, $cnt_all)
        ->setCellValue('E' . $numSheet, $cnt_1)
        ->setCellValue('F' . $numSheet, $cnt_2)
        ->setCellValue('G' . $numSheet, $cnt_3)
        ->setCellValue('H' . $numSheet, $cnt_4)
        ->setCellValue('I' . $numSheet, $cnt_5)
        ->setCellValue('J' . $numSheet, $cnt_6)
        ->setCellValue('K' . $numSheet, $cnt_7)
        ->setCellValue('L' . $numSheet, $cnt_8)
        ->setCellValue('M' . $numSheet, $cnt_9)
        ->setCellValue('N' . $numSheet, $cnt_10)
        ->setCellValue('O' . $numSheet, $cnt_11)
        ->setCellValue('P' . $numSheet, $cnt_12);

}