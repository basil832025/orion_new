<?php
include 'func.reports_excel.php';

function getSQLStatOfYear($Year,$sqlgrp='')
{
    $city = !empty($_SESSION['statofyaer']['filter']['city']) ? ' and t1.city='.$_SESSION['statofyaer']['filter']['city'] : '';
    $club = !empty($_SESSION['statofyaer']['filter']['club']) ? ' and t1.club='.$_SESSION['statofyaer']['filter']['club'] : '';
$sql = 'SELECT p.name,p.phone,
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where   tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.01.01" and "'.$Year.'.12.31") AS cnt_all, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where   tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.01.01" and "'.$Year.'.01.31") AS cnt_1, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.02.01" and LAST_DAY("'.$Year.'.02.01")) AS cnt_2, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.03.01" and "'.$Year.'.03.31") AS cnt_3, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.04.01" and "'.$Year.'.04.30") AS cnt_4, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.05.01" and "'.$Year.'.05.31") AS cnt_5, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.06.01" and "'.$Year.'.06.30") AS cnt_6, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.07.01" and "'.$Year.'.07.31") AS cnt_7, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.08.01" and "'.$Year.'.08.31") AS cnt_8, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.09.01" and "'.$Year.'.09.30") AS cnt_9, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.10.01" and "'.$Year.'.10.31") AS cnt_10, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.11.01" and "'.$Year.'.11.30") AS cnt_11, 
(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 where  tp1.player_id=p.id '.$city.$club.' AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$Year.'.12.01" and "'.$Year.'.12.31") AS cnt_12 
from bs_players p where ('.$sqlgrp.') and ispara=0 and not_use=0
ORDER BY 1';
s($sql);
    $aUsers = db_list($sql);
      //print($sql);
  //  print_r($aUsers);

//    echo "<br>";
    return $aUsers;
}


function getSQLNewUsers($dbeg,$dend,$sqlgrp='')
{
    $city = !empty($_SESSION['new_users']['filter']['city']) ? ' and t.city='.$_SESSION['new_users']['filter']['city'] : '';
    $club = !empty($_SESSION['new_users']['filter']['club']) ? ' and t.club='.$_SESSION['new_users']['filter']['club'] : '';

    //  $sqlgrp = 'p.grp=47 or p.grp=49';
    $sql ='SELECT t.id,p.name,(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 
where  tp1.player_id=p.id AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$dbeg.'" and "'.$dend.'") AS cnt_turnirs, 
t.dat,p.phone,t.name as turnir_name from bs_turnirplayers tp, bs_turnirs t,bs_players p where t.id=tp.turnir_id AND p.id=tp.player_id 
and t.ispara=0
and  ('.$sqlgrp.') AND t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'" '.$city.$club.'
AND NOT EXISTS(SELECT *  FROM bs_turnirplayers tp2,bs_turnirs t2  WHERE tp2.player_id=tp.player_id AND t2.id=tp2.turnir_id AND t2.dat<"'.$dbeg.'")
ORDER BY 3 DESC,p.name,dat';
    $aUsers = db_list($sql);
   //  s($sql);
     //s($aUsers);
    return $aUsers;
}

function getSQLCountTurnirs($dbeg,$dend,$sqlgrp='')
{
    $city = !empty($_SESSION['counts_turnirs']['filter']['city']) ? ' and t.city='.$_SESSION['counts_turnirs']['filter']['city'] : '';
    $club = !empty($_SESSION['counts_turnirs']['filter']['club']) ? ' and t.club='.$_SESSION['counts_turnirs']['filter']['club'] : '';

    //  $sqlgrp = 'p.grp=47 or p.grp=49';
    $sql ='SELECT t.id,p.name,(SELECT COUNT(*)  from bs_turnirplayers tp1, bs_turnirs t1 
where  tp1.player_id=p.id AND  t1.id=tp1.turnir_id and t1.dat  BETWEEN "'.$dbeg.'" and "'.$dend.'") AS cnt_turnirs, 
t.dat,p.phone,t.name as turnir_name from bs_turnirplayers tp, bs_turnirs t,bs_players p where t.id=tp.turnir_id AND p.id=tp.player_id
and t.ispara=0
and  ('.$sqlgrp.') AND t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'" '.$city.$club.'
ORDER BY 3 DESC,p.name,dat';
    $aUsers = db_list($sql);
//     s($sql);
    return $aUsers;
}


function getSQLCountTurnirs_no($dbeg,$dend,$sqlgrp='')
{
    $aUsers =[];
    $city = !empty($_SESSION['counts_turnirs']['filter']['city']) ? ' and p.city_def='.$_SESSION['counts_turnirs']['filter']['city'] : '';
    $club = !empty($_SESSION['counts_turnirs']['filter']['club']) ? ' and p.club='.$_SESSION['counts_turnirs']['filter']['club'] : '';
//if (empty($_SESSION['counts_turnirs']['filter']['city']) && empty($_SESSION['counts_turnirs']['filter']['club']))
{
    $sql ='SELECT p.name,
(SELECT COUNT(player_id)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id 
and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") 
and p.id=tp.player_id  and cnt_games>0) as cnt_turnirs,
p.phone, id
FROM `bs_players` p where  ('.$sqlgrp.') and ispara=0   and not_use=0 '.$city.$club.'
and not EXISTS(select * from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and p.id=tp.player_id and t.ispara=0
           and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and cnt_games>0 ) 
order by 1 , 3 DESC';
    $aUsers = db_list($sql);
  //  s($sql);
}
    //  $sqlgrp = 'p.grp=47 or p.grp=49';

   //
    return $aUsers;
}


function getMonUKR($mon)
{
    switch ($mon)
    {
        case 1 : return 'cічень'; break;
        case 2  : return 'лютий'; break;
        case 3 : return 'березень'; break;
        case 4 : return 'квітень'; break;
        case 5 : return 'травень'; break;
        case 6 : return 'червень'; break;
        case 7 : return 'липень'; break;
        case 8 : return 'серпень'; break;
        case 9 : return 'вересень'; break;
        case 10 : return 'жовтень'; break;
        case 11 : return 'листопад'; break;
        case 12 : return 'грудень'; break;
    }
}
function getCountTurnirsHeader($name='',$text='',$minYear='2023',$maxYear='2023',$aMonthsThisYear,$selectedMon=0,$selectedYear=0)
{
    $id_spis = 4; // міста
    $name_vibor = 'Виберіть місто';
    $name_all = 'Всі міста';
    $id = 'city-chosen-select';
    $name_field = 'city';
    $data_id =$_SESSION['nomination']['filter']['city'];
    $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
    $id_spis = 3; // клуби
    $name_vibor = 'Виберіть клуб';
    $id = 'club-chosen-select';
    $name_field = 'club';
    $name_all = 'Всі клуби';
    $data_id = $_SESSION['nomination']['filter']['club'];
    $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);

    /*    $id_spis = 4; // міста
        $name_vibor = 'Виберіть місто';
        $name_all = 'Всі міста';
        $id = 'city_nomination';
        $name_field = 'city';
        $data_id =$_SESSION['counts_turnirs']['filter']['city'];
        $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
        $id_spis = 3; // клуби
        $name_vibor = 'Виберіть клуб';
        $id = 'club_nomination';
        $name_field = 'club';
        $name_all = 'Всі клуби';
        $data_id = $_SESSION['counts_turnirs']['filter']['club'];
        $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
    */
    $content= '
<div class="container">
  <div class="row py-3">
    <div class="col-md-10">
    
    <div class="col_flo_left">
       <select class="form-select w-auto" id="month_nomination">';
   foreach ($aMonthsThisYear as $month)
   {
      // s($month);
       $selectedTxt= $month['mon']==$selectedMon ? 'selected' : '';
       $content.='<option '.$selectedTxt.' value="'.$month['mon'].'">'.getMonUKR($month['mon']).'</option>';
   }
       //   <option selected>Січень</option>

    $content.=' </select>
    </div>';

   $content.=' <div class="col_flo_left">
    <select class="form-select w-auto" id="year_nomination">';
   $cnYear = $maxYear-$minYear;

   for($y=$minYear; $y<=$maxYear;$y++)
   {
       $selectedTxt= $y==$selectedYear ? 'selected' : '';
       $content.='<option '.$selectedTxt.' value="'.$y.'">'.$y.'</option>';
   }
  $content.='</select> </div>'.$txtCity.$txtClub.'
    </div>
    <div class="col-md-2 ">
    </div>
    </div>

   
  
';
   //    <h2 class="text-left">'.$name.'</h2>
    //
    $content.= '<h6>'.$text.'</h6>';
    return $content;
}
function getStatOfYearHeader($name='',$text='',$minYear='2023',$maxYear='2023',$selectedYear=0)
{
    $id_spis = 4; // міста
    $name_vibor = 'Виберіть місто';
    $name_all = 'Всі міста';
    $id = 'city-chosen-select';
    $name_field = 'city';
    $data_id =$_SESSION['nomination']['filter']['city'];
    $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
    $id_spis = 3; // клуби
    $name_vibor = 'Виберіть клуб';
    $id = 'club-chosen-select';
    $name_field = 'club';
    $name_all = 'Всі клуби';
    $data_id = $_SESSION['nomination']['filter']['club'];
    $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);

    /*
    $id_spis = 4; // міста
    $name_vibor = 'Виберіть місто';
    $name_all = 'Всі міста';
    $id = 'city_nomination';
    $name_field = 'city';
    $data_id =$_SESSION['statofyaer']['filter']['city'];
    $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
    $id_spis = 3; // клуби
    $name_vibor = 'Виберіть клуб';
    $id = 'club_nomination';
    $name_field = 'club';
    $name_all = 'Всі клуби';
    $data_id = $_SESSION['statofyaer']['filter']['club'];
    $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
*/
    $content= '
<div class="container">
  <div class="row py-3">
    <div class="col-md-7">
    
    <div class="col_flo_left py-1"><h5>Період:</h5></div>';

    $content.=' <div class="col_flo_left">
    <select class="form-select w-auto" id="year_nomination">';
    $cnYear = $maxYear-$minYear;

    for($y=$minYear; $y<=$maxYear;$y++)
    {
        $selectedTxt= $y==$selectedYear ? 'selected' : '';
        $content.='<option '.$selectedTxt.' value="'.$y.'">'.$y.'</option>';
    }
    $content.='</select> </div>'.$txtCity.$txtClub.'
    </div>
    <div class="col-md-5 ">
      <h2 class="text-left">'.$name.'</h2>
    </div>
    </div>

   
  
';
    $content.= '<h6>'.$text.'</h6>';
    return $content;
}

function getNewUsersHeader($name='',$text='',$minYear='2023',$maxYear='2023',$aMonthsThisYear,$selectedMon=0,$selectedYear=0)
{
    $id_spis = 4; // міста
    $name_vibor = 'Виберіть місто';
    $name_all = 'Всі міста';
    $id = 'city-chosen-select';
    $name_field = 'city';
    $data_id =$_SESSION['nomination']['filter']['city'];
    $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
    $id_spis = 3; // клуби
    $name_vibor = 'Виберіть клуб';
    $id = 'club-chosen-select';
    $name_field = 'club';
    $name_all = 'Всі клуби';
    $data_id = $_SESSION['nomination']['filter']['club'];
    $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);

    /*  $id_spis = 4; // міста
      $name_vibor = 'Виберіть місто';
      $name_all = 'Всі міста';
      $id = 'city_nomination';
      $name_field = 'city';
      $data_id =$_SESSION['new_users']['filter']['city'];
      $txtCity = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
      $id_spis = 3; // клуби
      $name_vibor = 'Виберіть клуб';
      $id = 'club_nomination';
      $name_field = 'club';
      $name_all = 'Всі клуби';
      $data_id = $_SESSION['new_users']['filter']['club'];
      $txtClub = get_select($id_spis, $name_vibor, $id, $name_field, $data_id,$name_all);
  */
    $content= '
<div class="container">
  <div class="row py-3">
    <div class="col-md-12">
    
    
    <div class="col_flo_left">
       <select class="form-select w-auto" id="month_nomination">';
    foreach ($aMonthsThisYear as $month)
    {
        // s($month);
        $selectedTxt= $month['mon']==$selectedMon ? 'selected' : '';
        $content.='<option '.$selectedTxt.' value="'.$month['mon'].'">'.getMonUKR($month['mon']).'</option>';
    }
    //   <option selected>Січень</option>

    $content.=' </select>
    </div>';

    $content.=' <div class="col_flo_left">
    <select class="form-select w-auto" id="year_nomination">';
    $cnYear = $maxYear-$minYear;

    for($y=$minYear; $y<=$maxYear;$y++)
    {
        $selectedTxt= $y==$selectedYear ? 'selected' : '';
        $content.='<option '.$selectedTxt.' value="'.$y.'">'.$y.'</option>';
    }
    $content.='</select> </div>'.$txtCity.$txtClub.'
    </div>
    </div>

   
  
';
    $content.= '<h6>'.$text.'</h6>';
    return $content;
}

function getCountTurnirs($name_grp='',$aUsers=[],$aUsersNO=[])
{

    $content='<h4><span class="badge bg-light text-dark ">Група:</span>'.$name_grp.'</h4> ';
    $content .='<table class="table table-sm nomin_table">
  <thead>
    <tr>
      <th scope="col" >#</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Телефон/<br>Дата турніра</th>
      <th scope="col" class="text-center">Відвідав турнірів/<br>Назва турніра<br></th>
   
    </tr> 
   
  </thead>
  <tbody>';
    $n=1;

    $name='';$sm_turnirs=0;
    $cn_players=0;
  foreach ($aUsers as $user)
  { $txt_name='';
      $txt_phone_dat = $user['dat'];
      $txt_turnir_name=$user['turnir_name'];
      if ($name!=$user['name']) {
          $sm_turnirs=$sm_turnirs+$user['cnt_turnirs'];
          $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['phone'].'</td>
      <td class="text-center">'.$user['cnt_turnirs'].'</td>
      
    </tr>';
          $name=$user['name'];$n++;$nt=1;
      }

      $content.='<tr>
      <th scope="row"></th>
      <td>'.$nt.'</td>
      <td class="text-center">'.$user['dat'].'</td>
      <td class="text-center">'.$user['turnir_name'].'</td>
      
    </tr>';
      $nt++;
  }
    $cn_players=$n-1;
    foreach ($aUsersNO as $user)
    {
            $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['phone'].'</td>
      <td class="text-center">'.$user['cnt_turnirs'].'</td>
      
    </tr>';
            $n++;
    }
    $content.='<tr>
      <th scope="row"></th>
      <td class="fw-bold f14">Загалом:</td>
      <td class="text-center fw-bold f14">кількість гравців: '.($cn_players).'</td>
      <td class="text-center fw-bold f14">відвідано турнірів: '.$sm_turnirs.'</td>
      
    </tr>';

    $content .='</tbody>
</table>';
  //  ;
    return $content;
}
function getNewUsers($name_grp='',$aUsers=[])
{

    $content='<h4><span class="badge bg-light text-dark ">Група:</span>'.$name_grp.'</h4> ';
    $content .='<table class="table table-sm nomin_table">
  <thead>
    <tr>
      <th scope="col" >#</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Телефон/<br>Дата турніра</th>
      <th scope="col" class="text-center">Відвідав турнірів/<br>Назва турніра<br></th>
   
    </tr> 
   
  </thead>
  <tbody>';
    $n=1;

    $name='';$sm_turnirs=0;
    $cn_players=0;
    foreach ($aUsers as $user)
    { $txt_name='';
        $txt_phone_dat = $user['dat'];
        $txt_turnir_name=$user['turnir_name'];
        if ($name!=$user['name']) {
            $sm_turnirs=$sm_turnirs+$user['cnt_turnirs'];
            $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['phone'].'</td>
      <td class="text-center">'.$user['cnt_turnirs'].'</td>
      
    </tr>';
            $name=$user['name'];$n++;$nt=1;
        }

        $content.='<tr>
      <th scope="row"></th>
      <td>'.$nt.'</td>
      <td class="text-center">'.$user['dat'].'</td>
      <td class="text-center">'.$user['turnir_name'].'</td>
      
    </tr>';
        $nt++;
    }
    $cn_players=$n-1;
    $content.='<tr>
      <th scope="row"></th>
      <td class="fw-bold f14">Загалом:</td>
      <td class="text-center fw-bold f14">кількість гравців: '.($cn_players).'</td>
      <td class="text-center fw-bold f14">відвідано турнірів: '.$sm_turnirs.'</td>
      
    </tr>';

    $content .='</tbody>
</table>';
    //  ;
    return $content;
}

function getStatOfYear($name_grp='',$aUsers=[])
{

    $content='<h4><span class="badge bg-light text-dark ">Група:</span>'.$name_grp.'</h4> ';
    $content .='<div class="big-table">
<div class="container">
<table class="table  table-sm nomin_table">
  <thead>
    <tr>
      <th scope="col" >#</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Телефон</th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Всього</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Січень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Лютийv</th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Березень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Квітень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Травень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Червень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Липень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Серпень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Вересень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Жовтень</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Листопад</span></th>
      <th scope="col" class="text-center"><span class="rotate-sm-90">Грудень</span></th>
            
    </tr> 
   
  </thead>
  <tbody>';
    $n=1;

    $name='';$sm_turnirs=0;
    $cn_players=0;$cnt_all=0;$cnt_1 =0;
    $cnt_2 =0;$cnt_3 =0;$cnt_4 =0;$cnt_5 =0;$cnt_6 =0;$cnt_7 =0;$cnt_8 =0;
    $cnt_9 =0;$cnt_10 =0;$cnt_11 =0;$cnt_12 =0;
    foreach ($aUsers as $user)
    {
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
        $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['phone'].'</td>
      <td class="text-center">'.$user['cnt_all'].'</td>
      <td class="text-center">'.$user['cnt_1'].'</td>
      <td class="text-center">'.$user['cnt_2'].'</td>
      <td class="text-center">'.$user['cnt_3'].'</td>
      <td class="text-center">'.$user['cnt_4'].'</td>
      <td class="text-center">'.$user['cnt_5'].'</td>
      <td class="text-center">'.$user['cnt_6'].'</td>
      <td class="text-center">'.$user['cnt_7'].'</td>
      <td class="text-center">'.$user['cnt_8'].'</td>
      <td class="text-center">'.$user['cnt_9'].'</td>
      <td class="text-center">'.$user['cnt_10'].'</td>
      <td class="text-center">'.$user['cnt_11'].'</td>
      <td class="text-center">'.$user['cnt_12'].'</td>
      
    </tr>';
        $n++;
    }
    $cn_players=$n-1;
    $content.='<tr>
      <th scope="row"></th>
      <th scope="row"></th>
      <td class="fw-bold f14">Загалом:</td>
      <td class="text-center fw-bold f14">'.($cnt_all).'</td>
      <td class="text-center fw-bold f14">'.$cnt_1.'</td>
      <td class="text-center fw-bold f14">'.$cnt_2.'</td>
      <td class="text-center fw-bold f14">'.$cnt_3.'</td>
      <td class="text-center fw-bold f14">'.$cnt_4.'</td>
      <td class="text-center fw-bold f14">'.$cnt_5.'</td>
      <td class="text-center fw-bold f14">'.$cnt_6.'</td>
      <td class="text-center fw-bold f14">'.$cnt_7.'</td>
      <td class="text-center fw-bold f14">'.$cnt_8.'</td>
      <td class="text-center fw-bold f14">'.$cnt_9.'</td>
      <td class="text-center fw-bold f14">'.$cnt_10.'</td>
      <td class="text-center fw-bold f14">'.$cnt_11.'</td>
      <td class="text-center fw-bold f14">'.$cnt_12.'</td>
      
    </tr>';

    $content .='</tbody>
</table></div></div>';
    //  ;
    return $content;
}

