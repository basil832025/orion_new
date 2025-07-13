<?php
$form = poste('form');
$turnir_id=poste('turnir_id');
//s('triger');
$id_rec=poste('id');//s($form);
$win=0;
$lose=0;
 $set_1 =  $form['set_1']=='W' ? 3 : $form['set_1'];
 $set_2 =  $form['set_2']=='W' ? 3 : $form['set_2'];
 $set_1 = trim($set_1)=='' ? $set_1=0 :$set_1;
$set_2 = trim($set_2)=='' ? $set_2=0 :$set_2;

// победа 1 игрока
if ($set_1>$set_2) 
{
    $win = $form['pl_id_1'];
    $lose = $form['pl_id_2'];
   /* $win_win_set = $form['set_1'];  
    $win_lose_set = $form['set_2']; 
    $lose_win_set = $form['set_2'];  
    $lose_lose_set = $form['set_1'];  */
}
// победа 2 игрока
if ($set_2>$set_1) 
{
    $win = $form['pl_id_2'];
    $lose = $form['pl_id_1'];
 
   /* 
  $win_win_set = $form['set_2'];  
    $win_lose_set = $form['set_1']; 
    $lose_win_set = $form['set_1'];  
    $lose_lose_set = $form['set_2'];  
 */
}
if ($form['set_2']=='L' && $form['set_1']=='L')
{
  $win = $form['pl_id_1'];
    $lose = $form['pl_id_2'];  
}



// для новой версии узнаем этап
$sql = ' select etap_id,group_num,olimp16_num,win_player,lose_player from '.T_REITING. ' r where id='.$id_rec;
    $aReitngRow = db_row($sql);
    wLog($aReitngRow);
    $etap_id =$aReitngRow['etap_id'];
    $group_num =$aReitngRow['group_num'];
    $olimp16_num =$aReitngRow['olimp16_num'];
    $win_player =$aReitngRow['win_player'];
    $lose_player =$aReitngRow['lose_player'];
$sql = 'select * from '.T_ETAPS.
    ' w where id = '.$etap_id;
$aEtapType = db_row($sql);
$type_etap = $aEtapType['type_etap'];
$strPlayerGrp='';
    $sql_grp='';
    $cn_results=0;
   if ($win_player!=$win){ // если поменялся победитель тогда делаем проверки 
    
    if (!empty($group_num)) 
    {// если данный этап группа то нужно проверить есть ли игры для всех уастников группы
        $sql = 'select player_id from `'.T_ETAPS_PLAYER_MESTA.'` where groups='.$group_num.' and etap_id='.$etap_id;
        $aPlayerGrp = db_list($sql);
     //   s($aPlayerGrp);
        foreach ($aPlayerGrp as $aplGrp){
          if ($strPlayerGrp<>'') $strPlayerGrp.=',';  
        $strPlayerGrp.=$aplGrp['player_id'];
         }
             
        $sql_grp =  ' and (pl_id_1 in ('.$strPlayerGrp.') or pl_id_2 in ('.$strPlayerGrp.'))';
    
       // s($strPlayerGrp);
    }
    // проверим есть ли по следующему этапу уже начатые игры или результаты.
$sql ='select count(*) as cn from '.T_REITING.' r where r.etap_id  in (select id from bs_etaps_work where istochnik_posev= '.$etap_id.') 
  and turnir_id='.$turnir_id.' and COALESCE(win_player,0)>0 and perenos_etap=0 '.$sql_grp;
$cn_results=db_field($sql,'cn');
}
//$cn_results=0;
if ($cn_results==0) {
    
    
    $sql = 'update '.T_REITING. ' set win_player='.$win .', lose_player='.$lose.' where id='.$id_rec;
 //   s($sql);
    db_query($sql);
    // если это не групповой этап не имеет смысла делать ниже операции
   
if ($win<>0) 
{
    
if (!empty($group_num))
{      
    //
     setOchkiSetsForGrp($win,$lose,$etap_id,$turnir_id);

    //проверяем все ли введены результаты, если все то рассчет мест делаем и потом заполняем слеующий этап
  $sql = 'SELECT * FROM `bs_reiting` where etap_id='.$etap_id.' and group_num='.$group_num.' and ((set_1="0" and set_2="0")  )';  
  $aResAll = db_list($sql);
 // s($sql);
  // если нет нулевых результатов значит все заполнено
    if (empty($aResAll)) 
    {
     //   s('rasxhet');
      // эти 2 функции расспределяют места по групппе если все результаты заполнены
     $this_aResults= all_results($turnir_id,$etap_id);  
     sql_raschet($turnir_id, $etap_id,$this_aResults,$group_num);
     // распределим места согласно занятым местам змейкой 
     grpSetMestaAllZmeyka($etap_id);

     //***** тут мы заполняем следующие этапы людьми если они до этого виртуальные были
     // получаем отсортированных людей по местам в группе
     $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.
     ' and groups='.$group_num.' order by grp_mesto';
     $aPlayersGrp = db_list($sql);
     foreach ($aPlayersGrp as $aPlayer)
     {
        $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where groups_pred='.$group_num.' and 
        grp_num_pred='.$aPlayer['grp_mesto'].' and  etap_id_pred='.$etap_id;
        $aBud_etapsPlayer = db_list($sql); 
        //s($sql);
        // возмжо один игрок в нескольких последующих этапах выступает
        if (!empty($aBud_etapsPlayer))
        {
            foreach ($aBud_etapsPlayer as $aBudPlayer)
            {
                $sql ='update '.T_ETAPS_PLAYER_MESTA.' set player_id='.$aPlayer['player_id'].' 
                where id='.$aBudPlayer['id'];
                db_query($sql);
                // если следующий этап группы
                if (!empty($aBudPlayer['groups'])) {
                $sql = 'update '.T_REITING.' set pl_id_1='.$aPlayer['player_id'] .' 
                where etap_id='.$aBudPlayer['etap_id'].' and group_num='.$aBudPlayer['groups'].' and pl_num_grp1='.$aBudPlayer['grp_num']; 
            db_query($sql);
            
                $sql = 'update '.T_REITING.' set pl_id_2='.$aPlayer['player_id'] .' 
                where etap_id='.$aBudPlayer['etap_id'].' and group_num='.$aBudPlayer['groups'].' and pl_num_grp2='.$aBudPlayer['grp_num']; 
            db_query($sql);

            }
            else // если следующий этап 2х минуска
            {
               if (!empty($aBudPlayer['num_posev_olimp'])) 
               {
                // определим сколько людей на этапе
                $sql = 'select  cnt_people from '.T_ETAPS.' t  where  t.id='.$aBudPlayer['etap_id'];  
            $cnt_people=db_field($sql,'cnt_people'); 
            global $aVariants2minuska_16,$aVariants2minuska_8,$aVariantsOlimp_8,$aVariantsOlimp_16;
                   if ($type_etap==5 || $type_etap==4) $aVariants= ($cnt_people>8) ?  $aVariantsOlimp_16  : $aVariantsOlimp_8;
                   else
                       $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
                 // определяем номер игры данной пары 
                list($num,$playNum) = get_num_game_pars($aBudPlayer['num_posev_olimp'],$aVariants);
                   $sql = 'select * from '.T_REITING.' where etap_id='.$aBudPlayer['etap_id'].' and olimp16_num='.$num;
                   $Game = db_row($sql);
                   if (empty($Game))// если данной игры  не существует то значит игрок в след этапе
                   {
                       $winMin =  $aVariants[$num]['win'] ;
                       $aWin = explode(".", $winMin);
                       $playNum= $aWin[1];
                       $num= $aWin[0];


                       if (($cnt_people==3 || $cnt_people==2) && $num==5) { $num=7;$playNum=1;}
                       if ( $cnt_people==2 && $num==6) { $num=7;$playNum=2;}
                   }
                 $sqlPlayer = $playNum==1 ? 'pl_id_1='.$aPlayer['player_id'] : 'pl_id_2='.$aPlayer['player_id'];
                 $sql = 'update '.T_REITING.' set '.$sqlPlayer.'  
                where etap_id='.$aBudPlayer['etap_id'].' and olimp16_num='.$num; 
            db_query($sql);
                
               }
            }
            }
        }
        
       
     }
        $sql = 'select etap_id from '.T_ETAPS_PLAYER_MESTA.' where  etap_id_pred='.$etap_id. ' GROUP BY etap_id';
        $aBud_etaps = db_list($sql);
        if (!empty($aBud_etaps))
        foreach ($aBud_etaps as $BudaEtap)
        {
            //тут нужно перенести игры если есть перенос
            $sql = 'select * from '.T_ETAPS.
                ' w where id = '.$BudaEtap['etap_id'];
            $aEtap = db_row($sql);
           // s($aEtap);
            $type_etap_b = $aEtap['type_etap'];
            // если предыдущий этап группа и текущий этап группа то длеаем ветер, а и если поставили птичку переноса игр...
            if ($type_etap_b==1 &&  $aEtap['is_perenos']>0)
             {
                // s($aEtap);
                setPernosGamesFromIstochn($aEtap,$turnir_id,$BudaEtap['etap_id']);
            }
        }

     // если все игры во всех группах  сыграны и вариант с посевом по рейтингу, то нужно найти этих счастливиков
   if (!empty($group_num))
   { //  если данный этап группы
   
   //  set_mesta_poReiting();
  }   
     }
    
// s($sql);
   
   
    
   
}

// если это олимпийка 16 
if (!empty($olimp16_num)) {
    global $aVariants2minuska_16, $aVariants2minuska_8, $aVariantsOlimp_8, $aVariantsOlimp_16;
    wLog($olimp16_num);
    $sql = 'select count(*) as cnt from ' . T_ETAPS_PLAYER_MESTA . ' where turnir_id=' . $turnir_id . ' and etap_id=' . $etap_id;
    $cnt_people = db_field($sql, 'cnt');
    if ($type_etap == 5 || $type_etap == 4) $aVariants = ($cnt_people > 8) ? $aVariantsOlimp_16 : $aVariantsOlimp_8;
    else
        $aVariants = ($cnt_people > 8) ? $aVariants2minuska_16 : $aVariants2minuska_8;
    wLog($aVariants);
    $lostMin = $aVariants[$olimp16_num]['lost'];
    $aLost = explode(".", $lostMin);
    $winMin = $aVariants[$olimp16_num]['win'];
    $aWin = explode(".", $winMin);
    if (!empty($lostMin)) {
        $playNum = $aLost[1];
        $num = $aLost[0];
        $sql = 'select * from ' . T_REITING . ' where etap_id=' . $etap_id . ' and olimp16_num=' . $aLost[0];
        $Game = db_row($sql);
        //  s($Game);
        if (empty($Game))// если данной игры  не существует то значит игрок в след этапе
        {
            if (!empty($aVariants[$aLost[0]]['win']))
            {
                $win__ = $aVariants[$aLost[0]]['win'];
                //  s($win__);
                $aWin_ = explode(".", $win__);
                $playNum = $aWin_[1];
                $num = $aWin_[0];
                // на всякий случай еще раз проверим есть ли игра в олимпийке есть варианты с 2 переходом
                $sql = 'select * from '.T_REITING.' where etap_id='.$etap_id.' and olimp16_num='.$num;
                $Game = db_row($sql);
                if (empty($Game))// если данной игры  не существует то значит игрок в след этапе
                {
                    //     s('$num22='.$num);
                    if (!empty($aVariants[$num]['win']))
                    {
                        $win__ = $aVariants[$num]['win'];
                        $aWin_ = explode(".", $win__);
                        $playNum = $aWin_[1];
                        $num = $aWin_[0];
                    }

                    //     s('$num3='.$num);
                }
            }

        }
        if (!empty($num))
        {
            // проигравший игрок
            $sql = 'update ' . T_REITING . ' set pl_id_' . $playNum . '=' . $lose . '
                where etap_id=' . $etap_id . ' and olimp16_num=' . $num;
            db_query($sql);
        }

        //   $olimp16_num=$num;
    }
    if (!empty($winMin)) {
        // победитель игрок
        $sql = 'update ' . T_REITING . ' set pl_id_' . $aWin[1] . '=' . $win . '
                where etap_id=' . $etap_id . ' and olimp16_num=' . $aWin[0];
        db_query($sql);
    }
// s($olimp16_num)  ;
    if ($type_etap == 5 || $type_etap == 4)
    {
        if ($cnt_people>8)
        {
            mesta_olimp_16($olimp16_num,$cnt_people,$etap_id,$win,$lose);
        }
        else
        {
            mesta_olimp_8($olimp16_num,$cnt_people,$etap_id,$win,$lose);
        }
    } else
    {
        if ($cnt_people>8)
        {
            mesta_2x_minuska16($olimp16_num,$cnt_people,$etap_id,$win,$lose);
        }
        else
        {
            mesta_2x_minuska8($olimp16_num,$cnt_people,$etap_id,$win,$lose);
        }
    }





}
}else
{
    //если обнулилирезультат групп или 2х минуски нужно седлать кучу отмен игор или проверить нету ли встреч
      $win = $form['pl_id_1'];
    $lose = $form['pl_id_2'];
      if (!empty($group_num)) 
      {
        $sql='update '.T_ETAPS_PLAYER_MESTA.' set player_id=0 where etap_id  in (select id from bs_etaps_work where istochnik_posev= '.$etap_id.') and "'.$strPlayerGrp.'"<>"" 
        and  player_id in('.$strPlayerGrp.') ' ;
      
        db_query($sql);
        
          $sql='update '.T_REITING.' set pl_id_1=0 where etap_id  in (select id from bs_etaps_work where istochnik_posev= '.$etap_id.') 
          and "'.$strPlayerGrp.'"<>"" 
        and  pl_id_1 in('.$strPlayerGrp.') ' ;
      
        db_query($sql);
        $sql='update '.T_REITING.' set pl_id_2=0 where etap_id  in (select id from bs_etaps_work where istochnik_posev= '.$etap_id.') 
          and "'.$strPlayerGrp.'"<>"" 
        and  pl_id_2 in('.$strPlayerGrp.') ' ;
      
        db_query($sql);
        $sql='update '.T_ETAPS_PLAYER_MESTA.' set grp_mesto="" where etap_id='.$etap_id.' and groups='.$group_num ;
        db_query($sql);
        
        
        
      }
    // 1 если єто групповая встреча, нужно проверить нет ли уже игр в следующих этапах по игроках в след этапах
    // если есть игры, то результат нельзя менять
}
}
else
{ // если резудьтаты для даанной группы уже есть в след этапах результат недбзя менять иначе повлият на много факторов

 //window_mess('В следующем  этапе есть уже игры! Удалите игры со счетом или обунулите результаты!='.$cn_results);
    
}
// обновления статистики онлайн по 2 игрокам
//s('pl_id_1='.$form['pl_id_1']);

statistic_player_online($form['pl_id_1'],$turnir_id);
//s('pl_id_2='.$form['pl_id_2']);
statistic_player_online($form['pl_id_2'],$turnir_id);


?>