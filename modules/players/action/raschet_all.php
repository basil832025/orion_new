<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class Raschet_AllAction extends ActionModule
{  protected  $content = '';
    protected  $is_new_player = 0; // если новые игроки на туринре
    protected  $is_new = 0; // первый раз на турнире для измен стартового рейтинга
    protected  $is_first = 1; // первый раз на турнире для измен стартового рейтинга
    protected  $subMenu = array();
    protected  $Java_script = ''; // джаваскрипт функции для данного действия например иницлизация функции календаря, или редактора контента

    function init ()
    {
        if (($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login'])))
        {

            s('HAKKER_HAKKER');
            s($_POST);
            s($_SERVER['REMOTE_ADDR']);
            s($_SERVER['HTTP_USER_AGENT']);
            exit;
            return;
        }
        $player_id = poste('player_id');
       // s($player_id);
        if (!empty($player_id))
        {
            $sql = 'SELECT t.id FROM '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.' t WHERE tp.player_id='.$player_id.' AND t.id=tp.turnir_id ORDER BY t.dat, t.id LIMIT 1';
            $_SESSION['RASSCHET_ALL']['idFirstTurnPlayer'] = db_field($sql,'id');
         //   s('idTurnirFirst='.$_SESSION['RASSCHET_ALL']['idFirstTurnPlayer']);
        }
        $_SESSION['RASSCHET_ALL']['nowRow'] = !empty($_SESSION['RASSCHET_ALL']['nowRow']) ? $_SESSION['RASSCHET_ALL']['nowRow'] : -1;
    //    s('DOOOOrasschet_all_sess');
     //   s($_SESSION['RASSCHET_ALL']);
    if (empty($_SESSION['RASSCHET_ALL']['TURNIRS'])) {
        $sql = 'select id from ' . T_TURNIRS . ' r WHERE r.date_raschet IS not null order by dat,id';
        $aTurnirs = db_list($sql);
        $_SESSION['RASSCHET_ALL']['TURNIRS'] = $aTurnirs;
    }else
        $aTurnirs =  $_SESSION['RASSCHET_ALL']['TURNIRS'];
        $all_rows =  count($aTurnirs);

// посылаем прогресс бар 1 чтобы сразу всплыло окно с прогрессбаром
        if (empty($_SESSION['RASSCHET_ALL']['isFirst'])) {
            $_SESSION['RASSCHET_ALL']['isFirst'] = 1;
       //     s('isFirst=0');
            progressBar(0,'1/'.$all_rows,'players','raschet_all');
        }
        // окно
        if ($_SESSION['RASSCHET_ALL']['isFirst']==1) {
            $_SESSION['RASSCHET_ALL']['isFirst'] = 2;
         //   s('isFirst=1');
            progressBar(1,'1/'.$all_rows,'players','raschet_all');
        }
      //  s('isFirst=2');
        //  s($sql);
        $this->is_first=1;
        //s('$_SESSIONRASSCHET_ALL_nowRow='.$_SESSION['RASSCHET_ALL']['nowRow']);
        //s('$all_rows='.$all_rows);
        // не поняное повторное отправление аяксом progressbar100 делаю заплатку пока
if ($_SESSION['RASSCHET_ALL']['nowRow']<($all_rows-1)) {
    $nRow = 0;
    foreach ($aTurnirs as $k => $turnir) {
        if ($_SESSION['RASSCHET_ALL']['nowRow'] >= $k) continue;
        $_SESSION['RASSCHET_ALL']['nowRow'] = $k;
        if (!empty($_SESSION['RASSCHET_ALL']['idFirstTurnPlayer']) && $_SESSION['RASSCHET_ALL']['idFirstTurnPlayer']>$turnir['id'])
        {
            continue;
        }
        else{
            $_SESSION['RASSCHET_ALL']['idFirstTurnPlayer']=0;
          //  s('idContinu000=0='.$turnir['id']);

        }
         //  s('do_rasch');
        $this->sql_raschet($turnir['id']);
        // запишем дату и время когда делали расчет первый раз в date_create потом date_last_modif
        $set = !empty($turnir['date_create']) ? 'date_last_modif' : 'date_create';
        $sql = 'update ' . T_TURNIRS . ' set ' . $set . '=now() where id=' . $turnir['id'];
        db_query($sql);
        // расчитать места
        $this->set_mesta_turnir($turnir['id']);
        $nRow++;
        $prc = round($_SESSION['RASSCHET_ALL']['nowRow'] * 100 / $all_rows); //

        if ($nRow > 10 && $prc < 100) progressBar($prc, $k . '/' . $all_rows, 'players', 'raschet_all');

    }
    progressBar(100, '', 'players', 'raschet_all');
}
        // расчет мест для игроков
        $sql='select * from '.T_PLAYERS.' where  ispara=0 and not_use=0 order by reiting desc';
        //   else $sql='select * from '.T_PLAYERS.' where ispara=0 order by reiting desc';
        $aMestaPlayers=db_list($sql);
        $num=1;
        foreach ($aMestaPlayers as $player)
        {
            db_query('update '.T_PLAYERS.' set num_reiting='.$num.' where id='.$player['id']);
            $num++;
        }
        /// END -------------рачет места для игроков
        $this->is_first = 0;
        unset($_SESSION['RASSCHET_ALL']);
      //  s('POSLErasschet_all_sess');
        //s($_SESSION['RASSCHET_ALL']);
        // s('rs');
        $this->list_show_rs();
    }
    function set_mesta_turnir ($turnir_id=0)
    {
        $sql = 'SELECT * FROM bs_etaps_work WHERE turnir_id='.$turnir_id.' AND istochnik_posev=0';
     //   s($sql);
        $aMesta = db_list($sql);
        if (!empty($aMesta))
        {
            foreach ($aMesta as $userMesto)
            {
                // входим в рекурсию чтобы проверять если там связаные етапы
                $this->setRecurMesta($userMesto['id'],$turnir_id,1);
            }
        }

    }
    function setRecurMesta ($etap_id,$turnir_id,$mestaFrom=1)
    {
        if (!empty($etap_id))
        {
            // запишем места для предущего этапа
            $this->setMesta($etap_id,$turnir_id,$mestaFrom);
            $sql = 'SELECT * FROM bs_etaps_work WHERE turnir_id='.$turnir_id.' AND istochnik_posev='.$etap_id.' ORDER BY mesto_from';
            $aEtaps = db_list($sql);
            if (!empty($aEtaps))
            {
                foreach ($aEtaps as $aEtap)
                {
                    $mestaFrom = $mestaFrom>$aEtap['mesto_from'] ? $mestaFrom : $aEtap['mesto_from'];
                    $this->setRecurMesta($aEtap['id'],$turnir_id,$mestaFrom);
           }
            }

        }

    }
    function setMesta ($etap_id,$turnir_id,$mestaFrom=1)
    {
        $sql = 'select * from bs_etaps_players_mesta where etap_id='.$etap_id;
     //   s($sql);
        $UserMesta = db_list($sql);
        if (!empty($UserMesta))
        {
            foreach ($UserMesta as $Usrmesta)
            {
                $mesto = $mestaFrom + ($Usrmesta['mesto_all']-1);
                $sql = 'update '.T_TURNIR_PLAYERS.' set mesto='.$mesto. ' where player_id='.$Usrmesta['player_id'].' and  turnir_id='.$turnir_id;
                //   s($sql);
                db_query($sql);
            }
        }

    }
    function getContent ()
    {
        return $this->content;
    }
    function getSubMneu ()
    {
        return  $this->subMenu;
    }
    function getJavaScript ()
    {

        return $this->Java_script;
    }
    function sql_raschet($turnir_id=0)
    {
    $this->id=$turnir_id;

        $sql = 'SELECT  (select tp.end_reiting from '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.'  tt WHERE  tt.id=tp.turnir_id AND   tp.player_id=p.id 
       AND ((SELECT tt1.dat FROM '.T_TURNIR_PLAYERS.' tp1, '.T_TURNIRS.'  tt1 WHERE tt1.id=tp1.turnir_id 
        AND tp1.player_id=p.id AND tp1.turnir_id='.$turnir_id.' AND CASE WHEN tt.dat=tt1.dat then tt1.id>tt.id else tt1.id<>tt.id end)>=tt.dat) 
order by tt.dat DESC, tt.id DESC  limit 1) as reit,p.start_reiting,p.id,reiting_ukraine 
FROM '.T_PLAYERS.' p ,bs_turnirplayers tpp 
            where  p.id=tpp.player_id  and tpp.turnir_id='.$this->id.' and
             exists(select * from `'.T_REITING.'` tp where (p.id=tp.pl_id_1 or p.id=tp.pl_id_2) and perenos_etap=0 and tp.turnir_id='.$this->id.' ) 
            order by 1 desc, start_reiting desc  ';
        $allPlayers_ = db_list($sql);
        $allPlayers =array();
        // этот прогон чтобы в масссиве id сооьевтовал игроку
        foreach ($allPlayers_ as  $k => $aPlayer)
        {
            $reiting = $aPlayer['reit'];
            if (empty($reiting)) // если эигрок играл 2 турнира в 1 день
            {
                $sql='SELECT end_reiting FROM '.T_TURNIR_PLAYERS.' tp2
         WHERE tp2.player_id='.$aPlayer['id'].' AND tp2.turnir_id<'.$turnir_id.' order by  tp2.turnir_id DESC  limit 1';
               // s($sql);
                $reiting = db_field($sql,'end_reiting');
              //  s('$reiting='.$reiting);
            }
            $start_reiting = $this->is_first==1 ? 0 : $aPlayer['start_reiting'];
            //$start_reiting = $this->is_first==1 ? 50 : $aPlayer['start_reiting'];
            $reiting_ukraine = $this->is_first==1 && !empty($aPlayer['reiting_ukraine']) && $aPlayer['reiting_ukraine']>0  ? $aPlayer['reiting_ukraine']*10 : 0;
            // если есть рейтинг украины то применяем его и расчет ведем от него умножанный на 10, если нет то стартовый рейтинг 50
            if (empty($reiting)) {
                $this->is_new_player=1;
                //   s($PlayId.' new _play');
                if ($this->is_first==1) {$this->is_new=1;}

                $reiting = !empty($reiting_ukraine) && $reiting_ukraine>0 ? $reiting_ukraine : $start_reiting;
                //   s('$reiting='.$reiting);
            }
            $aPlayer['reit'] = $reiting;
            $aPlayer['start_reiting'] = $start_reiting;
            $allPlayers[$aPlayer['id']]= $aPlayer;
        }
        // запустим проверку по туриниру и всех играх и запишем разницы рейтингов по играх дельты
        $this->proverkaTurnirs($allPlayers);


        foreach ($allPlayers as  $PlayId => $aPlayer)
        {// получим все результаты игр по данному турниру и игороку
            $sqlR = 'select * from '.T_REITING.' where (pl_id_1='.$PlayId.' or pl_id_2='.$PlayId.') and COALESCE(win_player,0)>0 and perenos_etap=0  and turnir_id='.$this->id;
            $allGames = db_list($sqlR);
            $this->is_new=0;
            if (!empty($allGames)) // если массив не пустой и игрок играл на данном турнире тогда естьсмысл продолжать
            {
//пройдем по массиву игр найдем всю статистику
                $diff=0; // дельта
                $cntGames=0;
                $cntWins=0;
                $cntLose=0;
                $smDiff=0;
                $sumSet=0;
                $sumSetWins=0;
                $sumSetLose=0;
                $reiting = $aPlayer['reit'];

                $start_reiting = $this->is_first==1 ? 0 : $aPlayer['start_reiting'];
                //$start_reiting = $this->is_first==1 ? 50 : $aPlayer['start_reiting'];
                $reiting_ukraine = $this->is_first==1 && !empty($aPlayer['reiting_ukraine']) && $aPlayer['reiting_ukraine']>0  ? $aPlayer['reiting_ukraine']*10 : 0;
                // если есть рейтинг украины то применяем его и расчет ведем от него умножанный на 10, если нет то стартовый рейтинг 50
                if (empty($reiting)) {
                    $this->is_new_player=1;
                    //   s($PlayId.' new _play');
                    if ($this->is_first==1) {$this->is_new=1;}

                    $reiting = !empty($reiting_ukraine) && $reiting_ukraine>0 ? $reiting_ukraine : $start_reiting;
                    //   s('$reiting='.$reiting);
                }
                // $reiting = (!empty($reiting) && $reiting>0) ? $reiting : $start_reiting; // если еще нет рейтинга берем стартовый
                foreach($allGames as $g => $aGame)
                { $reiting_2=0;
                    $set1 = 0;
                    $set2 = 0;
                    $Play2_id=0;
                    $cntGames++;
                    $diff=0;
                    // определяем игрок в записе 1 или 2
                    if ($aGame['pl_id_1']== $PlayId)
                    {   $set1 = $aGame['set_1'];
                        $set2 = $aGame['set_2'];
                        $set1 = $set1=='W' ? 3 : $set1;
                        $set2 = $set2=='W' ? 3 : $set2;
                        $set1 = $set1=='L' ? 0 : $set1;
                        $set2 = $set2=='L' ? 0 : $set2;
                        //подсчет сыграных сетов
                        $smSets1 = $set1=='W' ? 0 : $set1;
                        $smSets2 = $set2=='W' ? 0 : $set2;
                        $sumSet=$sumSet+($smSets1+$smSets2);
                        $sumSetWins=$sumSetWins+ $smSets1;
                        $sumSetLose=$sumSetLose+ $smSets2;

                        $Play2_id = $aGame['pl_id_2'];
                        $diff=$aGame['diff_1'];
                        //   $reiting_2 = $allPlayers[$aGame['pl_id_2']]['reit'];
                    }
                    // определяем игрок в записе 1 или 2
                    if ($aGame['pl_id_2']== $PlayId)
                    {
                        $set1 = $aGame['set_2'];
                        $set2 = $aGame['set_1'];
                        $set1 = $set1=='W' ? 3 : $set1;
                        $set2 = $set2=='W' ? 3 : $set2;
                        $set1 = $set1=='L' ? 0 : $set1;
                        $set2 = $set2=='L' ? 0 : $set2;
                        //подсчет сыграных сетов
                        $smSets1 = $set1=='W' ? 0 : $set1;
                        $smSets2 = $set2=='W' ? 0 : $set2;
                        $sumSet=$sumSet+($smSets1+$smSets2);
                        $sumSetWins=$sumSetWins+ $smSets1;
                        $sumSetLose=$sumSetLose+ $smSets2;

                        //   $reiting_2 = $allPlayers[$aGame['pl_id_1']]['reit'];
                        $Play2_id = $aGame['pl_id_1'];
                        $diff=$aGame['diff_2'];
                    }
                    if ($set1-$set2>0) $cntWins++; else $cntLose++;
                    $smDiff=$smDiff+$diff;

                    // функция перерасчитает рейтинги и обновит таблицу ретинга по 2 игрокам
                    //   list($diff1,$diff2) = $this->add_reiting_rec($PlayId,$Play2_id,$reiting,$reiting_2,$set1,$set2,$aGame['id']);


                } // end for $allGames
                $id_rec='';
                $sql = 'select id from '.T_TURNIR_PLAYERS.' where player_id='.$PlayId.' and turnir_id='.$this->id.' limit 1';
                //  s($sql);
                $id_rec=db_field($sql,'id');
                // s('$id_rec='.$id_rec);
                $reiting_end = ($reiting+$smDiff)<1 ? 1 : $reiting+$smDiff;
                $diff_round= round($reiting_end,0)-round($reiting,0);
                $where = 'player_id='.$PlayId.',turnir_id='.$this->id.',beg_reiting='.$reiting.',
                end_reiting='.($reiting_end).',diff='.$smDiff.',cnt_games='.$cntGames.',cnt_wins='.$cntWins.',cnt_lose='.$cntLose.
                    ', cnt_sets='.$sumSet.', cnt_sets_win='.$sumSetWins.', cnt_sets_lose='.$sumSetLose.', diff_round='.$diff_round;
                if (!empty($id_rec)) $sql= 'UPDATE '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id_rec;
                else  $sql= 'insert INTO '.T_TURNIR_PLAYERS.' SET ' .$where ;
                // s($sql);
                db_query($sql);
                // также обновим текущий рейтинг игрока и его статистику общую
                $this->updateStatisticPlayer($PlayId);
            } // end if $allGames
        } // end for $allPlayers
    }
    function updateStatisticPlayer($PlayId)
    {
        $sql='select 
  COALESCE((select tp.end_reiting from '.T_TURNIR_PLAYERS.' tp,'.T_TURNIRS.' t where t.id=tp.turnir_id and turnir_id='.$this->id.' and p.id=player_id order by t.dat desc limit 1),start_reiting) as reiting,
  (select sum(cnt_games) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as cnt_games, 
(select sum(cnt_wins) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as cnt_wins, 
(select sum(cnt_lose) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as cnt_lose,
(select min(end_reiting) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as min_reiting,
(select max(end_reiting) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as max_reiting,
(select avg(end_reiting) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as  avg_reiting,
(select count(id) from '.T_TURNIR_PLAYERS.' where p.id=player_id) as cnt_turnirs
from bs_players p where id='.$PlayId;
        $aPlayer = db_row($sql);

        //  s($sql);

        db_query('UPDATE '.T_PLAYERS.' SET 
    cnt_games='.$aPlayer['cnt_games'].',
    cnt_wins='.$aPlayer['cnt_wins'].',
    cnt_lose='.$aPlayer['cnt_lose'].',
    proc_wins='.round(($aPlayer['cnt_wins']/$aPlayer['cnt_games']*100),0).',
    reiting='.$aPlayer['reiting'].',
    '.($this->is_new==1 ?   'start_reiting='.$aPlayer['reiting'].',':'').'
    reiting_min='.$aPlayer['min_reiting'].',
    reiting_max='.$aPlayer['max_reiting'].',
    reiting_avg='.$aPlayer['avg_reiting'].',
    cnt_turnirs='.$aPlayer['cnt_turnirs'].' where id='.$PlayId);
    }
    function proverkaTurnirs($allPlayers)
    {
        // получим все результаты игр по данному турниру
        $sqlR = 'select * from '.T_REITING.' where   perenos_etap=0 and COALESCE(win_player,0)>0 and turnir_id='.$this->id;
        $allGames = db_list($sqlR);
        //  s($allPlayers);
        //  s($allGames);
        foreach($allGames as $g => $aGame)
        {
            if (!empty($aGame['pl_id_1']) && !empty($aGame['pl_id_2']))
            {
                $aPlayer1 = $allPlayers[$aGame['pl_id_1']];
                $aPlayer2 = $allPlayers[$aGame['pl_id_2']];
                $play1 = $aGame['pl_id_1'];
                $play2 = $aGame['pl_id_2'];
                $reiting1 = $aPlayer1['reit'];
                $reiting2 = $aPlayer2['reit'];
                // $start1 = $aPlayer1['start_reiting'];
                // $start2 = $aPlayer2['start_reiting'];
                $start1 = $this->is_first==1 ? 0 : $aPlayer1['start_reiting'];
           //     $start1 = $this->is_first==1 ? 50 : $aPlayer1['start_reiting'];
                $start2 = $this->is_first==1 ? 0 : $aPlayer2['start_reiting'];
           //     $start2 = $this->is_first==1 ? 50 : $aPlayer2['start_reiting'];
                $reiting_ukraine1 = $this->is_first==1 && !empty($aPlayer1['reiting_ukraine']) && $aPlayer1['reiting_ukraine']>0  ? $aPlayer1['reiting_ukraine']*10 : 0;
                $reiting_ukraine2 = $this->is_first==1 && !empty($aPlayer2['reiting_ukraine']) && $aPlayer2['reiting_ukraine']>0  ? $aPlayer2['reiting_ukraine']*10 : 0;

                $reiting1 = (!empty($reiting1) && $reiting1>0) ? $reiting1 :(!empty($reiting_ukraine1) && $reiting_ukraine1>0 ? $reiting_ukraine1 : $start1);
                $reiting2 = (!empty($reiting2) && $reiting2>0) ? $reiting2 :(!empty($reiting_ukraine2) && $reiting_ukraine2>0 ? $reiting_ukraine2 : $start2);

                //    $reiting1 = (!empty($reiting1) && $reiting1>0) ? $reiting1 : $start1; // если еще нет рейтинга берем стартовый
                //    $reiting2 = (!empty($reiting2) && $reiting2>0) ? $reiting2 : $start2; // если еще нет рейтинга берем стартовый

                $set1 = $aGame['set_1'];
                $set2 = $aGame['set_2'];
                $set_1_otkas = (($set1=='W' || $set2=='W')) ? $set_1_otkas=0 : $set_1_otkas = 1;
                $set1 = $set1=='W' ? 3 : $set1;
                $set2 = $set2=='W' ? 3 : $set2;
                $set1 = $set1=='L' ? 0 : $set1;
                $set2 = $set2=='L' ? 0 : $set2;
                $diff1=0;
                $diff2=0;
                if ($set_1_otkas && $set1>$set2) {
                    //делаем расчет дельты
                    if (($reiting1-$reiting2)<100) {
                        $diff1 = (100-($reiting1-$reiting2))/WIN_KOEF;
                      //  $diff2 = -(100-($reiting1-$reiting2))/20;
                        $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF;
                    }
                }

                // если победил 2 игрок
                if ($set_1_otkas && $set2>$set1) {
                    // проверяем не больше ли ретинг 100
                    if ($reiting2-$reiting1<100) {
                        $diff2 = (100-($reiting2-$reiting1))/WIN_KOEF;
                        $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF;
                        //$diff1 = -(100-($reiting2-$reiting1))/20;
                    }
                }

                // сравниваем показатели
                if (($diff1<>$aGame['diff_1']) or ($diff2<>$aGame['diff_2']) or ($reiting1<>$aGame['rt_id_1_beg'])
                    or ($reiting2<>$aGame['rt_id_2_beg']))
                {
                    $where = 'pl_id_1='.$play1.',  pl_id_2='.$play2.', rt_id_1_beg='.$reiting1.', 
            rt_id_2_beg='.$reiting2.',diff_1='.$diff1.', diff_2='.$diff2.',set_1="'.$aGame['set_1'].'",set_2="'.$aGame['set_2'].'"';
                    // s('Update '.T_REITING.' SET '.$where .' where id='.$aGame['id']);
                    db_query('Update '.T_REITING.' SET '.$where .' where id='.$aGame['id']);
                }
            }
        }
    }

    function list_show_rs()
    {    SystemClass::setAction('anyaction');
        SystemClass::setModule('players');
        //  $this->Java_script='reload_page_();';
        //   s('do');
     //   parent::list_show();
        //    s('posle');
        $post_return = 'players-list';
        SystemClass::setPost_return($post_return);




        // SystemClass::setJava_script($this->Java_script);

        // $objList = new ListTable();

        //   $objList->list_show();
        // //   $this->content=$objList->getContent();
        //   $this->subMenu=$objList->getSubMneu();
        //   $this->Java_script=$objList->getJavaScript();

    }
}
//echo 'dsjksd';
?>
