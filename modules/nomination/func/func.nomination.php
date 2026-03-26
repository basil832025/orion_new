<?php
include 'func.nomination_excel.php';
function getSQLBestPlayer($dbeg,$dend,$sqlgrp='')
{
    $city = !empty($_SESSION['nomination']['filter']['city']) ? ' and city='.$_SESSION['nomination']['filter']['city'] : '';

    $club = !empty($_SESSION['nomination']['filter']['club']) ? ' and club='.$_SESSION['nomination']['filter']['club'] : '';
    $sql ='SELECT p.name,
(SELECT COUNT(player_id)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id  and cnt_games>0) as cnt_turnirs,
(SELECT sum(cnt_games)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") 
and p.id=tp.player_id) as cnt_games,
(SELECT sum(cnt_sets)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id) as cnt_sets,
(SELECT ROUND((sum(cnt_wins)/sum(cnt_games))*100,2)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and
 (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'" ) and p.id=tp.player_id) as proc_wins,
(SELECT sum(cnt_wins)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and 
(t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id) as cnt_wins,    
id
FROM `bs_players` p where p.reiting>0 and ('.$sqlgrp.')
and EXISTS(select * from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and p.id=tp.player_id and cnt_games>0 and t.ispara=0
            '.$club.$city.' 
           and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'"))
order by 2 desc, 3 desc, 4 desc, 5 desc limit 10';
    $aUsers = db_list($sql);
   //  s($sql);
    return $aUsers;
}
function getSQLBestDiff($dbeg,$dend,$sqlgrp='')
{
    $club = !empty($_SESSION['nomination']['filter']['club']) ? ' and club='.$_SESSION['nomination']['filter']['club'] : '';
    $city = !empty($_SESSION['nomination']['filter']['city']) ? ' and city='.$_SESSION['nomination']['filter']['city'] : '';

    //  $sqlgrp = 'p.grp=47 or p.grp=49';
    $sql ='SELECT p.name,
(SELECT sum(tp.end_reiting-tp.beg_reiting)  from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'" ) 
and p.id=tp.player_id ) as diff_reit,
(SELECT ROUND((sum(cnt_wins)/sum(cnt_games))*100,2)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and 
(t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id) as proc_wins,
(SELECT COUNT(player_id)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id  and cnt_games>0) as cnt_turnirs,
(SELECT sum(cnt_wins)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id) as cnt_wins,
(SELECT sum(cnt_lose)  from bs_turnirplayers tp , bs_turnirs t where t.id=tp.turnir_id and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and p.id=tp.player_id) as cnt_lose,
id
FROM `bs_players` p where p.reiting>0 and ('.$sqlgrp.')
and EXISTS(select * from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and p.id=tp.player_id  and t.ispara=0
'.$club.$city.' 
           
            and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'") and cnt_games>0)
order by 2 desc, 3 desc limit 10';
    $aUsers = db_list($sql);
   // s($sql);
    return $aUsers;
}


function getMonUKR($mon)
{
    switch ($mon)
    {
        case 1 : return 'Січень'; break;
        case 2  : return 'Лютий'; break;
        case 3 : return 'Березень'; break;
        case 4 : return 'Квітень'; break;
        case 5 : return 'Травень'; break;
        case 6 : return 'Червень'; break;
        case 7 : return 'Липень'; break;
        case 8 : return 'Серпень'; break;
        case 9 : return 'Вересень'; break;
        case 10 : return 'Жовтень'; break;
        case 11 : return 'Листопад'; break;
        case 12 : return 'Грудень'; break;
    }
}
function getNominationHeader($name='',$text='',$minYear='2023',$maxYear='2023',$aMonthsThisYear=array(),$selectedMon=0,$selectedYear=0)
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

//<div class="col_flo_left py-1"><h5>Період:</h5></div>
//
    $content= '
<div class="container">
<div class="row ">
<h6 class="text_nominat">'.$text.'</h6>
</div>
  <div class="row py-3 mb40">
    <div class="col-md">
    
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

    return $content;
}
function getNomination($name_grp='',$aUsers=[])
{

    $content='<div class="mt40"><h4 ><span class="nomin_grp_name ">Група: </span>'.$name_grp.'</h4></div> ';
    if ($_SESSION['is_mobile'] ) {
        $content .='<table class="table table-sm nomin_table">
  <thead>
    <tr >
      <th scope="col" class="align-middle text-center">&nbsp;&nbsp;&nbsp;№&nbsp;&nbsp;</th>
      <th scope="col" class="align-middle text-center">ПІБ гравця</th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90"> Приріст<br>рейтингу</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br>  турнірів</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br> перемог</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br> поразок</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">%<br> перемог</span></th>
    </tr>
  </thead>
  <tbody>';
    }else
    {
        $content .='<table class="table nominat">
  <thead>
    <tr>
      <th scope="col"  class="text-center">№</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center"> Приріст рейтингу</th>
      <th scope="col" class="text-center">Кількість  турнірів</th>
      <th scope="col" class="text-center">Кількість перемог</th>
      <th scope="col" class="text-center">Кількість поразок</th>
      <th scope="col" class="text-center">% перемог</th>
    </tr>
  </thead>
  <tbody>';
    }

    $n=1;
  foreach ($aUsers as $user)
  {
      $content.='<tr>
      <th scope="row" class="text-center align-middle">'.$n.'</th>
      <td class="nomin_name align-middle">'.$user['name'].'</td>
      <td class="text-center align-middle">'.$user['diff_reit'].'</td>
      <td class="text-center align-middle">'.$user['cnt_turnirs'].'</td>
      <td class="text-center align-middle">'.$user['cnt_wins'].'</td>
      <td class="text-center align-middle">'.$user['cnt_lose'].'</td>
      <td class="text-center align-middle">'.$user['proc_wins'].'</td>
    </tr>';
$n++;
  }

$content .='</tbody>
</table>';
  //  ;
    return $content;
}
function getNominationBestPlayer($name_grp='',$aUsers=[])
{

    $content = '<div class="mt40"><h4><span class="nomin_grp_name">Група: </span>' . $name_grp . '</h4> </div>';

    if ($_SESSION['is_mobile']) {
        $content .= '<table class="table table-sm nomin_table">
  <thead>
    <tr>
      <th scope="col" class="align-middle text-center">&nbsp;&nbsp;&nbsp;№&nbsp;&nbsp;</th>
      <th scope="col" class="align-middle text-center">ПІБ гравця</th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90"> Кількість<br>  турнірів</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br>  ігор</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br> зіграних<br> сетів</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">Кількість<br> перемог</span></th>
      <th scope="col" class="text-center" width="40px"><span class="rotate_nomin-sm-90">%<br> перемог</span></th>
    </tr>
  </thead>
  <tbody>';
    } else
    {
        $content .='<table class="table nominat">
  <thead>
    <tr>
      <th scope="col"  class="text-center">№</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Кількість  турнірів</th>
      <th scope="col" class="text-center">Кількість  ігор</th>
      <th scope="col" class="text-center">Кількість  сетів</th>
      <th scope="col" class="text-center">Кількість перемог</th>
      <th scope="col" class="text-center">% перемог</th>
    </tr>
  </thead>
  <tbody>';
    }

    $n=1;
    foreach ($aUsers as $user)
    {
        $content.='<tr>
      <th scope="row" class="align-middle text-center">'.$n.'</th>
      <td class="align-middle">'.$user['name'].'</td>
      <td class="text-center align-middle">'.$user['cnt_turnirs'].'</td>
      <td class="text-center align-middle">'.$user['cnt_games'].'</td>
      <td class="text-center align-middle">'.$user['cnt_sets'].'</td>
      <td class="text-center align-middle">'.$user['cnt_wins'].'</td>
      <td class="text-center align-middle">'.$user['proc_wins'].'</td>
    </tr>';
        $n++;
    }

    $content .='</tbody>
</table>';
    //  ;
    return $content;
}
