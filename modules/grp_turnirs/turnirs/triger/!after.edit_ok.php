<?php
$aCntPlayers=array(3=>3,4=>6,5=>10,6=>15);
$turnir_id=poste('id');
if (!empty($turnir_id))
{
    //проверим если есть хоть 1 результат то удалять ничего нельзя
 $sql ='select count(*) as cn from '.T_REITING.'  where turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0';
$cn_results=db_field($sql,'cn');
if ($cn_results==0) 
{
$sql = 'select t.is_reiting,t.is_reiting_w,t.group_id_old,v.* from '.T_TURNIRS.' t, '.T_TURNIR_VARIANTS.' v where t.id='.$turnir_id.' and v.id=group_id ';
$aVariants = db_row($sql);
//s($sql);
//s($aVariants);
$cnGrp = $aVariants['cntGroups'];
$group_id_old = $aVariants['group_id_old'];
$is_reiting = $aVariants['is_reiting'] ? $aVariants['is_reiting'] : $aVariants['is_reiting_w'];

if ($group_id_old==0) {
 $sql = 'SELECT  '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit,tp.id as turn_id, p.*  
 FROM `'.T_TURNIR_PLAYERS.'` tp,'.T_PLAYERS.' p where turnir_id='.$turnir_id.' and p.id=tp.player_id 
 ORDER BY 1 desc, 2 desc';
 }else
 $sql = 'SELECT groups,grp_num, '.($is_reiting>0 ? 'reiting_ukraine, ' :'').'  case when reiting>0 then reiting else start_reiting end as beg_reit,tp.id as turn_id, p.*  
 FROM `'.T_TURNIR_PLAYERS.'` tp,'.T_PLAYERS.' p where turnir_id='.$turnir_id.' and p.id=tp.player_id 
 ORDER BY 1, 2 ';
//s($sql);
 {
    // если это добавления игрока тогда
    
 }
 $aPlayers = db_list($sql);
 //s($sql);
//s($aPlayers);
$cntPlayers=count($aPlayers);
//s('$cntPlayers='.$cntPlayers);
$zmeyka = 1; $n=1; $numPl=1;
if ($group_id_old==0) {
// пройдемся по всем игрокам и заполним группы по рейтингу змейкой
foreach ($aPlayers as $por => $player) 
{
   $sug = true;
   if ($n<1) $n=1; 
   if ($n>$cnGrp) $n=$cnGrp; 
   // змейка в прямом порядке
   if ($sug && $zmeyka==1 && $n<=$cnGrp) 
   {
    $sql = 'update '.T_TURNIR_PLAYERS .' set groups='.$n.', grp_num='.$numPl.' where id='.$player['turn_id'];
  db_query($sql);
   // s($sql);
    if ($n==$cnGrp) 
    {
        $zmeyka=2; $numPl++; $sug=0;
    }
     $n++;
   }
    // змейка в обратном  порядке
   if ($sug && $zmeyka==2 && $n>=1) 
   {
    $sql = 'update '.T_TURNIR_PLAYERS .' set groups='.$n.', grp_num='.$numPl.' where id='.$player['turn_id'];
 db_query($sql);
 //  s($sql);
    if ($n==1) 
    {
        $zmeyka=1; $numPl++; $sug=0;
    }
     $n--;
   }
}
}
//заполним порядок игр согласно таблиц поряядка в группах
$sql = 'SELECT  v.* from bs_turnirs t,bs_turnirs_variants v where v.id=t.group_id and  t.id='.$turnir_id;
$aVariants = db_row ($sql);
//$aVarGrp2=array();
 $sql = 'SELECT * FROM `bs_group_poryadok` p where p.players='.$aVariants['people1']; 
$aVarGrp[$aVariants['people1']]= db_list($sql);
if ($aVariants['people2']>0) {
     $sql = 'SELECT * FROM `bs_group_poryadok` p where p.players='.$aVariants['people2']; 
  $aVarGrp[$aVariants['people2']] = db_list($sql);  
}
$sql = 'SELECT groups,count(grp_num) as cnPlayer FROM `bs_turnirplayers` tp where turnir_id='.$turnir_id.' 
GROUP BY groups ORDER BY 2 asc,groups';
$aPoryadPlayer= db_list($sql);
$cn = count($aPoryadPlayer);
$aVarianPoryadPlayers=array();
$aKoff = array();
$koff_=0;
$prior=0;
//s($aPoryadPlayer);
foreach ($aPoryadPlayer  as $n => $group )
{
    $koff=round($aVariants['cntGames']/$aCntPlayers[$group['cnPlayer']],2);
    if ($koff_<>$koff) {$koff_=$koff; $prior++;}
  $aVarianPoryadPlayers[$group['groups']] = $aVarGrp[$group['cnPlayer']];  
  $aKoff[$group['groups']]['koff'] = $koff;
  $aKoff[$group['groups']]['koff_okr'] = round($koff,0);
  $aKoff[$group['groups']]['koff_sum'] = $koff;
  $aKoff[$group['groups']]['now'] = 0;
  $aKoff[$group['groups']]['elem'] = 0;
  $aKoff[$group['groups']]['prior'] = $prior;
  $aKoff[$group['groups']]['max_game'] = $aCntPlayers[$group['cnPlayer']];
  if ($cn-1==$n && $prior>1) $aKoff[$group['groups']]['prior'] = $prior+1;
}
$aPravPoryadok=array();
//s($aKoff);exit;
//s('$prior='.$prior);//exit;
//s($aVarianPoryadPlayers);exit;
$this_elem=0;
if ($prior==1) $kf=0; else $kf=1;
// пройдемся по всем играм
for ($i = 1; $i <= $aVariants['cntGames']; $i++) {
//    s('game='.$i);
   // маленький проход по массиву где есть кофф и текущие маркеры
   $sug=1;
   foreach ($aKoff as $grpN => $elem){
    if ($elem['prior']==1) { // приоритет наибольшие группы
   // s($elem['elem'].' por1 '.$grpN.' '.$elem['now'].' $elem[koff_okr]='.$elem['koff_okr']);
     if ($elem['now']==0 || ($elem['now']-$kf)==$elem['koff_okr'])  { // если это 1 элемент или больше коффа
        if ($sug) 
        { $sug=0;
          // if ($elem['now']>=$elem['koff']) 
          // $elem['now']=1;
          if ($elem['now']<>0) {
            
            $aKoff[$grpN]['koff_sum']=$elem['koff_sum']+$elem['koff'];  
            $aKoff[$grpN]['koff_okr']=round($aKoff[$grpN]['koff_sum'],0);  
            }
          $aKoff[$grpN]['now']++;
         // $aKoff[$grpN]['now'] ++;
        // s('vnut '.$elem['elem'].' por1 '.$grpN);
         if (!empty($aVarianPoryadPlayers[$grpN][$elem['elem']]['play1'])){
            $aPravPoryadok[] = array('group' => $grpN, 'play1' => $aVarianPoryadPlayers[$grpN][$elem['elem']]['play1'], 'play2' =>$aVarianPoryadPlayers[$grpN][$elem['elem']]['play2'] );
            $aKoff[$grpN]['elem'] ++;      
            }else
            {
                $sug=1;
                $elem['prior']=2;
                
            }
        }
        // nдля других єлеентов 
       } else if ($elem['now']<>0) $aKoff[$grpN]['now'] ++; 
     };
     if ($elem['prior']>1 && $sug) 
     { // приоритет  меньшие группы
        if ($elem['elem']==$this_elem)  
        { $sug=0;
      //  s($grpN);
       // s($elem['elem']);
    //    s($elem['elem'].' por22 '.$grpN);
        
            $aPravPoryadok[] = array('group' => $grpN, 'play1' => $aVarianPoryadPlayers[$grpN][$elem['elem']]['play1'], 'play2' =>$aVarianPoryadPlayers[$grpN][$elem['elem']]['play2'] );
            $aKoff[$grpN]['elem'] ++;      
            if ($elem['prior']==3) $this_elem++;
        }    
     }
   
     
   } 
    // s('$i='.$i);
  // s($aKoff);  
}
// закончили цикл c порядком игр
//s($aPravPoryadok);exit;

$sql = 'SELECT tp.id as turn_id, p.name,p.id as play_id,tp.groups,tp.grp_num,case when reiting>0 then reiting else start_reiting end as beg_reit  
 FROM `'.T_TURNIR_PLAYERS.'` tp,'.T_PLAYERS.' p where turnir_id='.$turnir_id.' and p.id=tp.player_id 
ORDER BY tp.groups,tp.grp_num';
$aPlayers=db_list($sql);
$aGroups = array();
$a=1;
$aTemp=array();
foreach ($aPlayers as $player) 
{
   
  if ($player['groups']<>$a ) {
    $aGroups[$player['groups']-1] =$aTemp;   
    $aTemp=array();
  }
  $aTemp[$player['grp_num']]=$player; 
  $a=$player['groups'];    
} 
    $aGroups[$player['groups']] =$aTemp;   
    $aTemp=array();
//s($aGroups);

// удалим предідущий варианты заполнения
if ($cn_results==0){
 $sql ='delete from '.T_REITING.'  where turnir_id='.$turnir_id ;
    db_query($sql);

// пройдемся по порядку игр и запоним нужный порядок
foreach ($aPravPoryadok as $num => $playThis)
{
   $pl_id_1= $aGroups[$playThis['group']][$playThis['play1']]['play_id'];
   $pl_id_2=$aGroups[$playThis['group']][$playThis['play2']]['play_id'];
   $rt_id_1_beg=$aGroups[$playThis['group']][$playThis['play1']]['beg_reit'];
   $rt_id_2_beg=$aGroups[$playThis['group']][$playThis['play2']]['beg_reit'];;
   $group_num=$playThis['group'];
   $pl_num_grp1=$playThis['play1'];
   $pl_num_grp2=$playThis['play2'];;
    $where = 'turnir_id='.$turnir_id.',pl_id_1='.$pl_id_1.',pl_id_2='.$pl_id_2.',
    rt_id_1_beg='.$rt_id_1_beg.',rt_id_2_beg='.$rt_id_2_beg.',group_num='.$group_num.',pl_num_grp1='.$pl_num_grp1.'
    ,pl_num_grp2='.$pl_num_grp2.',type_game=1'; 
      
    $sql ='insert into '.T_REITING.'  SET '.$where  ;
    db_query($sql);
  //  s($sql);
} 
} 
}

}
?>