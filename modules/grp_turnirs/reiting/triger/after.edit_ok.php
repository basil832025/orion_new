<?php
$form = poste('form');
$turnir_id=poste('turnir_id');
//s('triger');
$id_rec=poste('id');//s($form);
$win=0;
$lose=0;
 $set_1 = ($form['set_1'] == 'W') ? 3 : (($form['set_1'] == 'L') ? 0 : $form['set_1']);
 $set_2 = ($form['set_2'] == 'W') ? 3 : (($form['set_2'] == 'L') ? 0 : $form['set_2']);
 $set_1 = trim((string)$set_1) == '' ? 0 : (int)$set_1;
$set_2 = trim((string)$set_2) == '' ? 0 : (int)$set_2;

// ГЛОБАЛЬНОЕ ИСПРАВЛЕНИЕ: Проверка и исправление порядка счета для командных игр
// Если это командная игра (pair_number=0), проверяем правильность порядка команд
if (!empty($id_rec)) {
    $game_check = db_row('SELECT match_id, etap_id, pair_number, pl_id_1, pl_id_2, team_a_id, team_b_id 
        FROM '.T_REITING.' WHERE id='.$id_rec);
    
    if (!empty($game_check)) {
        $pair_number_check = !empty($game_check['pair_number']) ? (int)$game_check['pair_number'] : 0;
        $is_team_game_check = ($pair_number_check == 0 || empty($game_check['pair_number']));
        
        // Для командных игр проверяем порядок команд
        if ($is_team_game_check && !empty($game_check['match_id']) && !empty($game_check['etap_id'])) {
            // Получаем правильный порядок команд из bs_team_pairs (первая пара)
            $correct_order = db_row('SELECT team_a_id, team_b_id FROM bs_team_pairs 
                WHERE match_id="'.addslashes($game_check['match_id']).'" 
                AND etap_id='.$game_check['etap_id'].' 
                AND pair_number > 0
                ORDER BY pair_number ASC
                LIMIT 1');
            
            if (!empty($correct_order)) {
                $correct_team_a_id = (int)$correct_order['team_a_id'];
                $correct_team_b_id = (int)$correct_order['team_b_id'];
                $pl_id_1_check = (int)$game_check['pl_id_1'];
                $pl_id_2_check = (int)$game_check['pl_id_2'];
                
                // Проверяем, соответствует ли порядок команд правильному порядку
                // Если порядок перепутан (pl_id_1 != correct_team_a_id), нужно поменять местами счет
                if ($pl_id_1_check != $correct_team_a_id || $pl_id_2_check != $correct_team_b_id) {
                    // Порядок перепутан - меняем местами счет
                    $temp_set = $set_1;
                    $set_1 = $set_2;
                    $set_2 = $temp_set;
                    
                    // Обновляем счет в базе данных с правильным порядком
                    db_query('UPDATE '.T_REITING.' SET set_1="'.addslashes($set_1).'", set_2="'.addslashes($set_2).'" WHERE id='.$id_rec);
                    
                    wLog('FIXED: Team game id='.$id_rec.' score swapped: new set_1='.$set_1.', set_2='.$set_2.' (pl_id_1='.$pl_id_1_check.' should be team_a_id='.$correct_team_a_id.')');
                }
            }
        }
    }
}

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
$sql = ' select etap_id,group_num,olimp16_num,win_player,lose_player,pair_number,match_id,team_a_id,team_b_id from '.T_REITING. ' r where id='.$id_rec;
    $aReitngRow = db_row($sql);
    wLog($aReitngRow);
    $etap_id =$aReitngRow['etap_id'];
    $group_num =$aReitngRow['group_num'];
    $olimp16_num =$aReitngRow['olimp16_num'];
    $win_player =$aReitngRow['win_player'];
    $lose_player =$aReitngRow['lose_player'];
    $pair_number = !empty($aReitngRow['pair_number']) ? (int)$aReitngRow['pair_number'] : 0;
    $match_id = !empty($aReitngRow['match_id']) ? $aReitngRow['match_id'] : '';
    $team_a_id = !empty($aReitngRow['team_a_id']) ? (int)$aReitngRow['team_a_id'] : 0;
    $team_b_id = !empty($aReitngRow['team_b_id']) ? (int)$aReitngRow['team_b_id'] : 0;
    
    // Проверяем, является ли это игрой игроков в командном турнире (pair_number > 0 и есть match_id)
    $is_team_player_game = (!empty($pair_number) && $pair_number > 0 && !empty($match_id));
    
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
        $sql = 'select player_id from `'.T_ETAPS_PLAYER_MESTA.'` where `groups`='.$group_num.' and etap_id='.$etap_id;
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
    // Проверяем, является ли это командной игрой (pair_number = 0 и есть match_id)
    $is_team_game = (!empty($match_id) && ($pair_number == 0 || empty($pair_number)));
    
    // Для командных игр (pair_number = 0) вызываем setOchkiSetsForGrp для пересчета очков команд
    // Для игр игроков в командном турнире (pair_number > 0) не вызываем setOchkiSetsForGrp,
    // так как очки для команд рассчитываются только на основе завершенных командных игр (где одна команда достигла 3 побед)
    if (!$is_team_player_game) {
        // Пересчитываем очки для обеих команд (победителя и проигравшего)
        setOchkiSetsForGrp($win,$lose,$etap_id,$turnir_id);
        setOchkiSetsForGrp($lose,$win,$etap_id,$turnir_id);
    }

    //проверяем все ли введены результаты, если все то рассчет мест делаем и потом заполняем слеующий этап
    // Для командных турниров проверяем только командные игры (pair_number = 0)
    if ($is_team_game) {
        // Для командных игр проверяем, все ли командные игры в группе завершены (set_1=3 или set_2=3 и есть win_player)
        $sql = 'SELECT * FROM `'.T_REITING.'` 
            WHERE etap_id='.$etap_id.' 
            AND group_num='.$group_num.' 
            AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
            AND (set_1 = "0" AND set_2 = "0" OR win_player = 0 OR win_player IS NULL)';
    } else {
        // Для индивидуальных турниров проверяем все игры
        $sql = 'SELECT * FROM `'.T_REITING.'` where etap_id='.$etap_id.' and group_num='.$group_num.' and ((set_1="0" and set_2="0")  )';
    }
    
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
     ' and `groups`='.$group_num.' order by grp_mesto';
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
        $sql='update '.T_ETAPS_PLAYER_MESTA.' set grp_mesto="" where etap_id='.$etap_id.' and `groups`='.$group_num ;
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

// Вспомогательная функция для создания игры из пары (используется для активации 4-й и 5-й игр)
if (!function_exists('createGameFromPair')) {
    function createGameFromPair($pair, $turnir_id, $etap_id, $match_id, $team_a_id, $team_b_id) {
        $team_a_player_id = (int)$pair['team_a_player_id'];
        $team_b_player_id = (int)$pair['team_b_player_id'];
        $pair_number = (int)$pair['pair_number'];
        
        if ($team_a_player_id <= 0 || $team_b_player_id <= 0) {
            wLog('ERROR: createGameFromPair - invalid player IDs: team_a_player='.$team_a_player_id.', team_b_player='.$team_b_player_id);
            return false;
        }
        
        // Получаем рейтинги игроков
        $player1_info = db_row('SELECT * FROM '.T_PLAYERS.' WHERE id='.$team_a_player_id);
        $player2_info = db_row('SELECT * FROM '.T_PLAYERS.' WHERE id='.$team_b_player_id);
        
        if (empty($player1_info) || empty($player2_info)) {
            wLog('ERROR: createGameFromPair - player not found: player1='.(!empty($player1_info) ? 'found' : 'NOT FOUND').', player2='.(!empty($player2_info) ? 'found' : 'NOT FOUND'));
            return false;
        }
        
        $sql_reit1 = 'SELECT id, end_reiting FROM `'.T_TURNIR_PLAYERS.'` WHERE turnir_id<'.$turnir_id.' AND player_id='.$team_a_player_id.' ORDER BY turnir_id DESC LIMIT 1';
        $aReit1 = db_row($sql_reit1);
        $reiting1 = !empty($aReit1['end_reiting']) ? $aReit1['end_reiting'] : '';
        $start1 = !empty($player1_info['start_reiting']) ? $player1_info['start_reiting'] : 0;
        $reiting1 = (!empty($reiting1) && $reiting1 > 0) ? $reiting1 : $start1;
        
        $sql_reit2 = 'SELECT id, end_reiting FROM `'.T_TURNIR_PLAYERS.'` WHERE turnir_id<'.$turnir_id.' AND player_id='.$team_b_player_id.' ORDER BY turnir_id DESC LIMIT 1';
        $aReit2 = db_row($sql_reit2);
        $reiting2 = !empty($aReit2['end_reiting']) ? $aReit2['end_reiting'] : '';
        $start2 = !empty($player2_info['start_reiting']) ? $player2_info['start_reiting'] : 0;
        $reiting2 = (!empty($reiting2) && $reiting2 > 0) ? $reiting2 : $start2;
        
        $insert_game = db_query('INSERT INTO '.T_REITING.' SET 
            pl_id_1='.$team_a_player_id.',
            pl_id_2='.$team_b_player_id.',
            turnir_id='.$turnir_id.',
            etap_id='.$etap_id.',
            match_id="'.addslashes($match_id).'",
            pair_number='.$pair_number.',
            team_a_id='.$team_a_id.',
            team_b_id='.$team_b_id.',
            rt_id_1_beg='.$reiting1.',
            rt_id_2_beg='.$reiting2.',
            diff_1=0,
            diff_2=0,
            set_1=0,
            set_2=0,
            groups_pred1=0,
            groups_pred2=0,
            grp_num_pred1=0,
            grp_num_pred2=0,
            mesto_all_pred1=0,
            mesto_all_pred2=0,
            no_send=0,
            break_1=0,
            break_2=0,
            start_game="",
            end_game="",
            table_game=0,
            auto=1');
        
        if ($insert_game) {
            wLog('Successfully created game from pair: match_id='.$match_id.', pair_number='.$pair_number.', pl_id_1='.$team_a_player_id.', pl_id_2='.$team_b_player_id);
            return true;
        } else {
            wLog('ERROR: Failed to create game from pair: match_id='.$match_id.', pair_number='.$pair_number);
            return false;
        }
    }
}

// Обновление счета командной игры на основе результата игры игроков
// Если игра игроков (есть match_id и pair_number > 0), пересчитываем счет командной игры
if (!empty($id_rec) && !empty($etap_id)) {
    // Проверяем, является ли это игрой игроков в командном матче
    $player_game = db_row('SELECT match_id, pair_number, team_a_id, team_b_id, pl_id_1, pl_id_2, set_1, set_2, etap_id 
        FROM '.T_REITING.' WHERE id='.$id_rec);

    // Если etap_id не пришел в POST, берем из записи игры
    if (empty($etap_id) && !empty($player_game['etap_id'])) {
        $etap_id = (int)$player_game['etap_id'];
    }
    
    if (!empty($player_game['match_id']) && !empty($player_game['pair_number']) && $player_game['pair_number'] > 0) {
        // Это игра игроков в командном матче
        // Находим командную игру (где pair_number = 0 или NULL) с тем же match_id
        $team_game = db_row('SELECT id, set_1, set_2, team_a_id, team_b_id 
            FROM '.T_REITING.' 
            WHERE match_id="'.addslashes($player_game['match_id']).'" 
            AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
            AND etap_id = '.$etap_id.'
            LIMIT 1');
        
        if (!empty($team_game)) {
            // Пересчитываем счет командной игры на основе всех сыгранных игр игроков
            // Получаем пары для матча
            $pairs = db_list('SELECT pair_number, team_a_id, team_b_id, team_a_player_id, team_b_player_id
                FROM bs_team_pairs
                WHERE match_id="'.addslashes($player_game['match_id']).'"
                AND etap_id='.$etap_id);
            $pairs_by_number = array();
            $pairs_by_players = array();
            foreach ($pairs as $pair) {
                $pair_number = (int)$pair['pair_number'];
                $pairs_by_number[$pair_number] = $pair;
                $pa = (int)$pair['team_a_player_id'];
                $pb = (int)$pair['team_b_player_id'];
                if ($pa > 0 && $pb > 0) {
                    $min_p = min($pa, $pb);
                    $max_p = max($pa, $pb);
                    $pairs_by_players[$min_p.'_'.$max_p] = $pair;
                }
            }

            // Получаем все игры игроков для данного match_id
            $all_player_games = db_list('SELECT r.id, r.pl_id_1, r.pl_id_2, r.set_1, r.set_2, r.win_player, r.pair_number
                FROM '.T_REITING.' r
                WHERE r.match_id="'.addslashes($player_game['match_id']).'" 
                AND r.pair_number > 0
                AND (r.set_1 IS NOT NULL AND r.set_2 IS NOT NULL)');
            
            $team_a_wins = 0;
            $team_b_wins = 0;
            
            foreach ($all_player_games as $pg) {
                $set_1 = !empty($pg['set_1']) && $pg['set_1'] != 'W' && $pg['set_1'] != 'L' ? (int)$pg['set_1'] : 0;
                $set_2 = !empty($pg['set_2']) && $pg['set_2'] != 'W' && $pg['set_2'] != 'L' ? (int)$pg['set_2'] : 0;
                
                // Обработка специальных значений
                if ($pg['set_1'] == 'W') $set_1 = 3;
                if ($pg['set_2'] == 'W') $set_2 = 3;
                if ($pg['set_1'] == 'L') $set_1 = 0;
                if ($pg['set_2'] == 'L') $set_2 = 0;

                // Если ничья или 0:0, никто не получает очко
                if ($set_1 == $set_2) {
                    continue;
                }

                $pl_id_1 = (int)$pg['pl_id_1'];
                $pl_id_2 = (int)$pg['pl_id_2'];
                $pair_number = (int)$pg['pair_number'];
                $pair = null;

                if (!empty($pairs_by_number[$pair_number])) {
                    $candidate = $pairs_by_number[$pair_number];
                    $pa = (int)$candidate['team_a_player_id'];
                    $pb = (int)$candidate['team_b_player_id'];
                    if (($pl_id_1 == $pa && $pl_id_2 == $pb) || ($pl_id_1 == $pb && $pl_id_2 == $pa)) {
                        $pair = $candidate;
                    }
                }

                if (empty($pair)) {
                    $min_p = min($pl_id_1, $pl_id_2);
                    $max_p = max($pl_id_1, $pl_id_2);
                    $key = $min_p.'_'.$max_p;
                    if (!empty($pairs_by_players[$key])) {
                        $pair = $pairs_by_players[$key];
                    }
                }

                if (empty($pair)) {
                    wLog('WARNING: team pair not found for game id='.$pg['id'].' match_id='.$player_game['match_id']);
                    continue;
                }

                $team_a_player_id = (int)$pair['team_a_player_id'];
                $team_b_player_id = (int)$pair['team_b_player_id'];

                if ($set_1 > $set_2) {
                    if ($pl_id_1 == $team_a_player_id) {
                        $team_a_wins++;
                    } elseif ($pl_id_1 == $team_b_player_id) {
                        $team_b_wins++;
                    }
                } else {
                    if ($pl_id_2 == $team_a_player_id) {
                        $team_a_wins++;
                    } elseif ($pl_id_2 == $team_b_player_id) {
                        $team_b_wins++;
                    }
                }
            }
            
            // Обновляем счет командной игры
            // Нужно правильно сопоставить команды из bs_team_pairs с командами из team_game
            // Получаем team_a_id и team_b_id из первой пары для сопоставления
            $first_pair = !empty($pairs) ? $pairs[0] : null;
            $pairs_team_a_id = !empty($first_pair) ? (int)$first_pair['team_a_id'] : 0;
            $pairs_team_b_id = !empty($first_pair) ? (int)$first_pair['team_b_id'] : 0;
            
            // Определяем, какой счет соответствует какой команде из team_game
            $team_game_set_1 = 0;
            $team_game_set_2 = 0;
            
            // Сопоставляем команды: team_a_wins соответствует команде pairs_team_a_id, team_b_wins - pairs_team_b_id
            // Нужно определить, какая команда в team_game соответствует pairs_team_a_id, а какая - pairs_team_b_id
            if ($team_game['team_a_id'] == $pairs_team_a_id && $team_game['team_b_id'] == $pairs_team_b_id) {
                // Порядок совпадает: team_game.team_a_id == pairs.team_a_id, team_game.team_b_id == pairs.team_b_id
                $team_game_set_1 = $team_a_wins;  // team_a_wins идет в set_1 (команда team_a_id)
                $team_game_set_2 = $team_b_wins;  // team_b_wins идет в set_2 (команда team_b_id)
            } elseif ($team_game['team_a_id'] == $pairs_team_b_id && $team_game['team_b_id'] == $pairs_team_a_id) {
                // Порядок обратный: team_game.team_a_id == pairs.team_b_id, team_game.team_b_id == pairs.team_a_id
                $team_game_set_1 = $team_b_wins;  // team_b_wins идет в set_1 (команда team_a_id, которая соответствует pairs_team_b_id)
                $team_game_set_2 = $team_a_wins;  // team_a_wins идет в set_2 (команда team_b_id, которая соответствует pairs_team_a_id)
            } else {
                // Если не совпадает полностью, используем исходную логику (для обратной совместимости)
                wLog('WARNING: team IDs mismatch in team game. team_game: team_a_id='.$team_game['team_a_id'].', team_b_id='.$team_game['team_b_id'].'. pairs: team_a_id='.$pairs_team_a_id.', team_b_id='.$pairs_team_b_id);
                $team_game_set_1 = $team_a_wins;
                $team_game_set_2 = $team_b_wins;
            }
            
            // Проверяем соответствие team_a_id и team_b_id
            if ($team_game['team_a_id'] == $team_game['team_b_id']) {
                // Если team_a_id и team_b_id одинаковы (не должно быть, но на всякий случай)
                wLog('WARNING: team_a_id == team_b_id in team game id='.$team_game['id']);
            } else {
                // Определяем победителя и проигравшего для командной игры
                $team_win_id = 0;
                $team_lose_id = 0;
                if ($team_game_set_1 == 3) {
                    // Команда A (team_a_id) победила
                    $team_win_id = $team_game['team_a_id'];
                    $team_lose_id = $team_game['team_b_id'];
                } elseif ($team_game_set_2 == 3) {
                    // Команда B (team_b_id) победила
                    $team_win_id = $team_game['team_b_id'];
                    $team_lose_id = $team_game['team_a_id'];
                }
                
                // Проверяем, достигла ли одна из команд 3 побед для завершения игры
                $end_game_value = '';
                $win_player_value = '';
                $lose_player_value = '';
                if ($team_game_set_1 == 3 || $team_game_set_2 == 3) {
                    // Одна из команд достигла 3 побед - игра завершена
                    $end_game_value = ', end_game="'.date('H:i:s').'", table_game=0';
                    if ($team_win_id > 0 && $team_lose_id > 0) {
                        $win_player_value = ', win_player='.$team_win_id.', lose_player='.$team_lose_id;
                    }
                    wLog('Team game finished: team_game_id='.$team_game['id'].', match_id='.$player_game['match_id'].', score='.$team_game_set_1.':'.$team_game_set_2.', winner='.$team_win_id.', loser='.$team_lose_id);
                } else {
                    // Игра еще не завершена - обнуляем end_game и win_player/lose_player если они были установлены ранее
                    $end_game_value = ', end_game="", table_game=0, win_player=0, lose_player=0';
                }
                
                // Обновляем счет командной игры, статус завершения и победителя
                $update_team_game_sql = 'UPDATE '.T_REITING.' 
                    SET set_1='.$team_game_set_1.', set_2='.$team_game_set_2.$end_game_value.$win_player_value.' 
                    WHERE id='.$team_game['id'];
                
                db_query($update_team_game_sql);
                
                // Если командная игра завершена (одна из команд достигла 3 побед), пересчитываем очки для команд
                if ($team_win_id > 0 && $team_lose_id > 0 && ($team_game_set_1 == 3 || $team_game_set_2 == 3)) {
                    // Получаем group_num и olimp16_num из командной игры
                    $team_game_info = db_row('SELECT group_num, olimp16_num FROM '.T_REITING.' WHERE id='.$team_game['id']);
                    $team_game_group = !empty($team_game_info['group_num']) ? $team_game_info['group_num'] : '';
                    $team_game_olimp16_num = !empty($team_game_info['olimp16_num']) ? $team_game_info['olimp16_num'] : '';
                    
                    // Если это олимпийка/двухминуска (olimp16_num не пустой), обрабатываем продвижение команд
                    if (!empty($team_game_olimp16_num)) {
                        // Это командная игра в олимпийской системе - обрабатываем продвижение команд
                        global $aVariants2minuska_16, $aVariants2minuska_8, $aVariantsOlimp_8, $aVariantsOlimp_16;
                        wLog('Team game in olimp system finished: olimp16_num='.$team_game_olimp16_num.', team_win_id='.$team_win_id.', team_lose_id='.$team_lose_id);
                        
                        $sql = 'select count(*) as cnt from ' . T_ETAPS_PLAYER_MESTA . ' where turnir_id=' . $turnir_id . ' and etap_id=' . $etap_id;
                        $cnt_people = db_field($sql, 'cnt');
                        if ($type_etap == 5 || $type_etap == 4) $aVariants = ($cnt_people > 8) ? $aVariantsOlimp_16 : $aVariantsOlimp_8;
                        else
                            $aVariants = ($cnt_people > 8) ? $aVariants2minuska_16 : $aVariants2minuska_8;
                        
                        $lostMin = $aVariants[$team_game_olimp16_num]['lost'];
                        $aLost = explode(".", $lostMin);
                        $winMin = $aVariants[$team_game_olimp16_num]['win'];
                        $aWin = explode(".", $winMin);
                        
                        if (!empty($lostMin)) {
                            $playNum = $aLost[1];
                            $num = $aLost[0];
                            $sql = 'select * from ' . T_REITING . ' where etap_id=' . $etap_id . ' and olimp16_num=' . $aLost[0];
                            $Game = db_row($sql);
                            if (empty($Game)) {
                                if (!empty($aVariants[$aLost[0]]['win'])) {
                                    $win__ = $aVariants[$aLost[0]]['win'];
                                    $aWin_ = explode(".", $win__);
                                    $playNum = $aWin_[1];
                                    $num = $aWin_[0];
                                    $sql = 'select * from '.T_REITING.' where etap_id='.$etap_id.' and olimp16_num='.$num;
                                    $Game = db_row($sql);
                                    if (empty($Game)) {
                                        if (!empty($aVariants[$num]['win'])) {
                                            $win__ = $aVariants[$num]['win'];
                                            $aWin_ = explode(".", $win__);
                                            $playNum = $aWin_[1];
                                            $num = $aWin_[0];
                                        }
                                    }
                                }
                            }
                            if (!empty($num)) {
                                // проигравшая команда
                                $sql = 'update ' . T_REITING . ' set pl_id_' . $playNum . '=' . $team_lose_id . '
                                    where etap_id=' . $etap_id . ' and olimp16_num=' . $num;
                                db_query($sql);
                            }
                        }
                        
                        if (!empty($winMin)) {
                            // победившая команда
                            $sql = 'update ' . T_REITING . ' set pl_id_' . $aWin[1] . '=' . $team_win_id . '
                                    where etap_id=' . $etap_id . ' and olimp16_num=' . $aWin[0];
                            db_query($sql);
                        }
                        
                        // Обновляем места для команд
                        if ($type_etap == 5 || $type_etap == 4) {
                            if ($cnt_people>8) {
                                mesta_olimp_16($team_game_olimp16_num,$cnt_people,$etap_id,$team_win_id,$team_lose_id);
                            } else {
                                mesta_olimp_8($team_game_olimp16_num,$cnt_people,$etap_id,$team_win_id,$team_lose_id);
                            }
                        } else {
                            if ($cnt_people>8) {
                                mesta_2x_minuska16($team_game_olimp16_num,$cnt_people,$etap_id,$team_win_id,$team_lose_id);
                            } else {
                                mesta_2x_minuska8($team_game_olimp16_num,$cnt_people,$etap_id,$team_win_id,$team_lose_id);
                            }
                        }
                    }
                    
                    if (!empty($team_game_group)) {
                        // Пересчитываем очки для всех команд в группе
                        recalculateAllTeamsInGroup($etap_id, $turnir_id, $team_game_group);
                        
                        // ЛОГИКА ДЛЯ КОМАНДНЫХ ТУРНИРОВ (аналогично индивидуальным турнирам)
                        // Проверяем, все ли командные игры в группе завершены
                        // Командная игра считается завершенной, если set_1=3 или set_2=3 и есть win_player
                        $sql_team_group = 'SELECT * FROM `'.T_REITING.'` 
                            WHERE etap_id='.$etap_id.' 
                            AND group_num='.$team_game_group.' 
                            AND (pair_number = 0 OR pair_number IS NULL OR pair_number = "")
                            AND (set_1 = "0" AND set_2 = "0" OR win_player = 0 OR win_player IS NULL)';
                        $aTeamGamesNotFinished = db_list($sql_team_group);
                        
                        // Если все командные игры в группе завершены (нет незавершенных)
                        if (empty($aTeamGamesNotFinished)) {
                            wLog('All team games in group '.$team_game_group.' are finished. Starting place distribution and next stage processing.');
                            
                            // Вызываем те же функции, что и для индивидуальных турниров
                            // these 2 functions расспределяют места по групппе если все результаты заполнены
                            $this_aResults = all_results($turnir_id, $etap_id);
                            sql_raschet($turnir_id, $etap_id, $this_aResults, $team_game_group);
                            // распределим места согласно занятым местам змейкой 
                            grpSetMestaAllZmeyka($etap_id);
                            
                            //***** тут мы заполняем следующие этапы командами если они до этого виртуальные были
                            // получаем отсортированных команд по местам в группе
                            $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where turnir_id='.$turnir_id.' and etap_id='.$etap_id.
                                ' and `groups`='.$team_game_group.' order by grp_mesto';
                            $aTeamsGrp = db_list($sql);
                            
                            foreach ($aTeamsGrp as $aTeam) {
                                $sql = 'select * from '.T_ETAPS_PLAYER_MESTA.' where groups_pred='.$team_game_group.' and 
                                    grp_num_pred='.$aTeam['grp_mesto'].' and  etap_id_pred='.$etap_id;
                                $aBud_etapsTeam = db_list($sql); 
                                
                                // возможно одна команда в нескольких последующих этапах выступает
                                if (!empty($aBud_etapsTeam)) {
                                    foreach ($aBud_etapsTeam as $aBudTeam) {
                                        $sql ='update '.T_ETAPS_PLAYER_MESTA.' set player_id='.$aTeam['player_id'].' 
                                            where id='.$aBudTeam['id'];
                                        db_query($sql);
                                        
                                        // если следующий этап группы
                                        if (!empty($aBudTeam['groups'])) {
                                            $sql = 'update '.T_REITING.' set pl_id_1='.$aTeam['player_id'].' 
                                                where etap_id='.$aBudTeam['etap_id'].' and group_num='.$aBudTeam['groups'].' and pl_num_grp1='.$aBudTeam['grp_num']; 
                                            db_query($sql);
                                            
                                            $sql = 'update '.T_REITING.' set pl_id_2='.$aTeam['player_id'].' 
                                                where etap_id='.$aBudTeam['etap_id'].' and group_num='.$aBudTeam['groups'].' and pl_num_grp2='.$aBudTeam['grp_num']; 
                                            db_query($sql);
                                        } else {
                                            // если следующий этап 2х минуска (для команд это также может быть)
                                            if (!empty($aBudTeam['num_posev_olimp'])) {
                                                // определим сколько команд на этапе
                                                $sql = 'select  cnt_people from '.T_ETAPS.' t  where  t.id='.$aBudTeam['etap_id'];  
                                                $cnt_people = db_field($sql,'cnt_people'); 
                                                global $aVariants2minuska_16,$aVariants2minuska_8,$aVariantsOlimp_8,$aVariantsOlimp_16;
                                                if ($type_etap==5 || $type_etap==4) $aVariants= ($cnt_people>8) ?  $aVariantsOlimp_16  : $aVariantsOlimp_8;
                                                else
                                                    $aVariants= ($cnt_people>8) ?  $aVariants2minuska_16  : $aVariants2minuska_8;
                                                // определяем номер игры данной пары 
                                                list($num,$playNum) = get_num_game_pars($aBudTeam['num_posev_olimp'],$aVariants);
                                                $sql = 'select * from '.T_REITING.' where etap_id='.$aBudTeam['etap_id'].' and olimp16_num='.$num;
                                                $Game = db_row($sql);
                                                if (empty($Game)) {
                                                    // если данной игры  не существует то значит команда в след этапе
                                                    $winMin =  $aVariants[$num]['win'] ;
                                                    $aWin = explode(".", $winMin);
                                                    $playNum= $aWin[1];
                                                    $num= $aWin[0];
                                                    
                                                    if (($cnt_people==3 || $cnt_people==2) && $num==5) { $num=7;$playNum=1;}
                                                    if ( $cnt_people==2 && $num==6) { $num=7;$playNum=2;}
                                                }
                                                $sqlTeam = $playNum==1 ? 'pl_id_1='.$aTeam['player_id'] : 'pl_id_2='.$aTeam['player_id'];
                                                $sql = 'update '.T_REITING.' set '.$sqlTeam.'  
                                                    where etap_id='.$aBudTeam['etap_id'].' and olimp16_num='.$num; 
                                                db_query($sql);
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Перенос игр на следующий этап, если нужно
                            $sql = 'select etap_id from '.T_ETAPS_PLAYER_MESTA.' where  etap_id_pred='.$etap_id. ' GROUP BY etap_id';
                            $aBud_etaps = db_list($sql);
                            if (!empty($aBud_etaps)) {
                                foreach ($aBud_etaps as $BudaEtap) {
                                    //тут нужно перенести игры если есть перенос
                                    $sql = 'select * from '.T_ETAPS.' w where id = '.$BudaEtap['etap_id'];
                                    $aEtap = db_row($sql);
                                    $type_etap_b = $aEtap['type_etap'];
                                    // если предыдущий этап группа и текущий этап группа то делаем ветер, а и если поставили птичку переноса игр...
                                    if ($type_etap_b==1 &&  $aEtap['is_perenos']>0) {
                                        setPernosGamesFromIstochn($aEtap,$turnir_id,$BudaEtap['etap_id']);
                                    }
                                }
                            }
                        }
                    }
                }
                
                wLog('Updated team game score: team_game_id='.$team_game['id'].', match_id='.$player_game['match_id'].', team_a_wins='.$team_a_wins.', team_b_wins='.$team_b_wins.', new_score='.$team_game_set_1.':'.$team_game_set_2);
                
                // АЛГОРИТМ ОБРАБОТКИ РЕЗУЛЬТАТОВ И ДОБАВЛЕНИЯ ДОПОЛНИТЕЛЬНЫХ ИГР
                // Подсчитываем только игры с результатом (где есть счет и не 0:0)
                // Игра считается сыгранной, если (set_1 > 0 OR set_2 > 0) AND NOT (set_1 = 0 AND set_2 = 0)
                $played_games_count = db_field('SELECT COUNT(*) as cnt FROM '.T_REITING.' r
                    WHERE r.match_id="'.addslashes($player_game['match_id']).'" 
                    AND r.etap_id='.$etap_id.'
                    AND r.pair_number > 0
                    AND r.pair_number <= 5
                    AND (
                        (r.set_1 > 0 OR r.set_2 > 0 OR r.set_1 = "W" OR r.set_2 = "W")
                        AND NOT (r.set_1 = "0" AND r.set_2 = "0")
                    )
                    AND (r.set_1 IS NOT NULL AND r.set_2 IS NOT NULL)', 'cnt');
                $played_games_count = !empty($played_games_count) ? (int)$played_games_count : 0;
                
                // Получаем актуальный счет командной игры из базы данных
                $current_team_game = db_row('SELECT set_1, set_2 FROM '.T_REITING.' 
                    WHERE id='.$team_game['id']);
                $current_team_game_set_1 = !empty($current_team_game['set_1']) ? (int)$current_team_game['set_1'] : 0;
                $current_team_game_set_2 = !empty($current_team_game['set_2']) ? (int)$current_team_game['set_2'] : 0;
                
                // ШАГ 2: Проверка результата после обновления
                if ($current_team_game_set_1 == 3 || $current_team_game_set_2 == 3) {
                    // Матч завершен, ничего не делаем
                    wLog('Match finished: team_game_id='.$team_game['id'].', score='.$current_team_game_set_1.':'.$current_team_game_set_2);
                } elseif (($played_games_count == 3 && $current_team_game_set_1 + $current_team_game_set_2 == 3) || ($current_team_game_set_1 + $current_team_game_set_2 == 3 && ($current_team_game_set_1 == 2 || $current_team_game_set_2 == 2))) {
                    // После завершения ровно 3-й игры (все 3 игры сыграны) ИЛИ счет 2:1/1:2 независимо от количества игр
                    // Второе условие необходимо для случая, когда игры были удалены, но счет командной игры остался 2:1
                    if (($current_team_game_set_1 == 2 && $current_team_game_set_2 == 1) || ($current_team_game_set_1 == 1 && $current_team_game_set_2 == 2)) {
                        // Счет 2:1 или 1:2 - автоматически активируем 4-ю игру из уже созданной пары
                        wLog('After 3 games (or score 2:1): score='.$current_team_game_set_1.':'.$current_team_game_set_2.', played_games_count='.$played_games_count.' - activating 4th game automatically');
                        
                        // Проверяем, существует ли уже 4-я игра в bs_reiting
                        $game_4_exists = db_row('SELECT id FROM '.T_REITING.' r
                            WHERE r.match_id="'.addslashes($player_game['match_id']).'" 
                            AND r.etap_id='.$etap_id.'
                            AND r.pair_number=4');
                        
                        if (empty($game_4_exists)) {
                            // Получаем пару 4 из bs_team_pairs (должна быть уже создана при сохранении составов)
                            $pair_4 = db_row('SELECT * FROM bs_team_pairs 
                                WHERE match_id="'.addslashes($player_game['match_id']).'" 
                                AND etap_id='.$etap_id.'
                                AND pair_number=4');
                            
                            if (!empty($pair_4)) {
                                // Создаем игру для пары 4 в bs_reiting
                                $created = createGameFromPair($pair_4, $turnir_id, $etap_id, $player_game['match_id'], $team_game['team_a_id'], $team_game['team_b_id']);
                                
                                if ($created) {
                                    $player1_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$pair_4['team_a_player_id'], 'name');
                                    $player2_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$pair_4['team_b_player_id'], 'name');
                                    $notification_msg = 'Автоматично активовано 4-ту гру: '.$player1_name.' vs '.$player2_name;
                                    
                                    if (empty($_SESSION['MESSAGE_AJAX'])) {
                                        $_SESSION['MESSAGE_AJAX'] = '';
                                    }
                                    $_SESSION['MESSAGE_AJAX'] .= (!empty($_SESSION['MESSAGE_AJAX']) ? '<br>' : '') . $notification_msg;
                                    
                                    if (class_exists('SystemClass') && method_exists('SystemClass', 'setMessage_user')) {
                                        SystemClass::setMessage_user($notification_msg);
                                    }
                                    
                                    wLog('Activated 4th game: '.$notification_msg);
                                }
                            } else {
                                // Пара 4 не найдена - показываем уведомление админу
                                $match_team_a = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$team_game['team_a_id'], 'name');
                                $match_team_b = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$team_game['team_b_id'], 'name');
                                $notification_msg = 'Після завершення 3-ї гри рахунок '.$current_team_game_set_1.':'.$current_team_game_set_2.' ('.$match_team_a.' vs '.$match_team_b.'). Потрібно зберегти склади обох команд, щоб автоматично створити 4-ту гру.';
                                
                                if (empty($_SESSION['MESSAGE_AJAX'])) {
                                    $_SESSION['MESSAGE_AJAX'] = '';
                                }
                                $_SESSION['MESSAGE_AJAX'] .= (!empty($_SESSION['MESSAGE_AJAX']) ? '<br>' : '') . $notification_msg;
                                
                                if (class_exists('SystemClass') && method_exists('SystemClass', 'setMessage_user')) {
                                    SystemClass::setMessage_user($notification_msg);
                                }
                                
                                wLog('After 3 games: pair 4 not found in bs_team_pairs');
                            }
                        }
                    }
                } elseif ($played_games_count == 4 && $current_team_game_set_1 + $current_team_game_set_2 == 4) {
                    // После завершения 4-й игры
                    if ($current_team_game_set_1 == 2 && $current_team_game_set_2 == 2) {
                        // Счет 2:2 - автоматически создаем 5-ю игру
                        wLog('After 4 games: score=2:2 - creating 5th game automatically');
                        
                        // Проверяем, не существует ли уже 5-я игра
                        $game_5_exists = db_row('SELECT id FROM '.T_REITING.' r
                            WHERE r.match_id="'.addslashes($player_game['match_id']).'" 
                            AND r.etap_id='.$etap_id.'
                            AND r.pair_number=5');
                        
                        if (empty($game_5_exists)) {
                            // Получаем пару 5 из bs_team_pairs (должна быть уже создана при сохранении составов)
                            $pair_5 = db_row('SELECT * FROM bs_team_pairs 
                                WHERE match_id="'.addslashes($player_game['match_id']).'" 
                                AND etap_id='.$etap_id.'
                                AND pair_number=5');
                            
                            if (!empty($pair_5)) {
                                // Создаем игру для пары 5 в bs_reiting
                                $created = createGameFromPair($pair_5, $turnir_id, $etap_id, $player_game['match_id'], $team_game['team_a_id'], $team_game['team_b_id']);
                                
                                if ($created) {
                                    $player1_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$pair_5['team_a_player_id'], 'name');
                                    $player2_name = db_field('SELECT name FROM '.T_PLAYERS.' WHERE id='.$pair_5['team_b_player_id'], 'name');
                                    $notification_msg = 'Автоматично активовано 5-ту гру (вирішальну): '.$player1_name.' vs '.$player2_name;
                                    
                                    if (empty($_SESSION['MESSAGE_AJAX'])) {
                                        $_SESSION['MESSAGE_AJAX'] = '';
                                    }
                                    $_SESSION['MESSAGE_AJAX'] .= (!empty($_SESSION['MESSAGE_AJAX']) ? '<br>' : '') . $notification_msg;
                                    
                                    if (class_exists('SystemClass') && method_exists('SystemClass', 'setMessage_user')) {
                                        SystemClass::setMessage_user($notification_msg);
                                    }
                                    
                                    wLog('Activated 5th game: '.$notification_msg);
                                } else {
                                    wLog('ERROR: Failed to create 5th game from pair');
                                }
                            } else {
                                wLog('ERROR: After 4 games with score 2:2, pair 5 not found in bs_team_pairs');
                            }
                        } else {
                            wLog('5th game already exists: game_id='.$game_5_exists['id']);
                        }
                    } elseif ($current_team_game_set_1 == 3 || $current_team_game_set_2 == 3) {
                        // После 4-й игры счет стал 3:1 или 1:3 - матч завершен
                        wLog('Match finished after 4 games: score='.$current_team_game_set_1.':'.$current_team_game_set_2);
                    }
                }
            }
        }
    }
}


?>
