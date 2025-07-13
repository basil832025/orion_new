<?php
function getSQLBestPlayer($dbeg,$dend,$sqlgrp='')
{
    //  $sqlgrp = 'p.grp=47 or p.grp=49';
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
and EXISTS(select * from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and p.id=tp.player_id and cnt_games>0
           and (t.dat BETWEEN "'.$dbeg.'" and "'.$dend.'"))
order by 2 desc, 3 desc, 4 desc, 5 desc limit 10';
    $aUsers = db_list($sql);
    // s($sql);
    return $aUsers;
}
function getSQLBestDiff($dbeg,$dend,$sqlgrp='')
{
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
and EXISTS(select * from bs_turnirplayers tp, bs_turnirs t where t.id=tp.turnir_id and p.id=tp.player_id 
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
function getNominationHeader($name='',$text='',$minYear='2023',$maxYear='2023',$aMonthsThisYear,$selectedMon=0,$selectedYear=0)
{
    $content= '
<div class="container">
  <div class="row py-3">
    <div class="col-md-6">
    
    <div class="col_flo_left py-1"><h5>Період:</h5></div>
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
  $content.='</select> </div>
    </div>
    <div class="col-md-6 ">
      <h2 class="text-left">'.$name.'</h2>
    </div>
    </div>

   
  
';
    $content.= '<h6>'.$text.'</h6>';
    return $content;
}
function getNomination($name_grp='',$aUsers=[])
{

    $content='<h4><span class="badge bg-light text-dark ">Група.:</span>'.$name_grp.'</h4> ';
    $content .='<table class="table ">
  <thead>
    <tr>
      <th scope="col" >#</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Приріст рейтингу</th>
      <th scope="col" class="text-center"Кількість зіграних<br> турнірів</th>
      <th scope="col" class="text-center">Кількість перемог</th>
      <th scope="col" class="text-center">Кількість поразок</th>
      <th scope="col" class="text-center">% перемог</th>
    </tr>
  </thead>
  <tbody>';
    $n=1;
  foreach ($aUsers as $user)
  {
      $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['diff_reit'].'</td>
      <td class="text-center">'.$user['cnt_turnirs'].'</td>
      <td class="text-center">'.$user['cnt_wins'].'</td>
      <td class="text-center">'.$user['cnt_lose'].'</td>
      <td class="text-center">'.$user['proc_wins'].'</td>
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

    $content='<h4><span class="badge bg-light text-dark ">Група:</span>'.$name_grp.'</h4> ';
    $content .='<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">ПІБ гравця</th>
      <th scope="col" class="text-center">Кількість зіграних<br> турнірів</th>
      <th scope="col" class="text-center">Кількість зіграних<br> ігор</th>
      <th scope="col" class="text-center">Кількість зіграних<br> сетів</th>
      <th scope="col" class="text-center">Кількість перемог</th>
      <th scope="col" class="text-center">% перемог</th>
    </tr>
  </thead>
  <tbody>';
    $n=1;
    foreach ($aUsers as $user)
    {
        $content.='<tr>
      <th scope="row">'.$n.'</th>
      <td>'.$user['name'].'</td>
      <td class="text-center">'.$user['cnt_turnirs'].'</td>
      <td class="text-center">'.$user['cnt_games'].'</td>
      <td class="text-center">'.$user['cnt_sets'].'</td>
      <td class="text-center">'.$user['cnt_wins'].'</td>
      <td class="text-center">'.$user['proc_wins'].'</td>
    </tr>';
        $n++;
    }

    $content .='</tbody>
</table>';
    //  ;
    return $content;
}
