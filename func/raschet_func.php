<?php
//s('raschet_func');
function raschet_shtraph($turnir_id=0){
    $sql='SELECT  tpp.player_id,tpp.end_reiting 
FROM '.T_TURNIR_PLAYERS.' tpp 
            where  tpp.turnir_id='.$turnir_id.' order by 1 desc, end_reiting desc';
    $allPlayers = db_list($sql);
    if (!empty($allPlayers)){
        foreach ($allPlayers as $player)
        {
            updateStatisticPlayer($player['player_id'],$turnir_id);
        }
    }
}
// ������� � ������ ��������� ��� ������ ������� ����� ������� �� ��� ���������� � � ������� ��������
function add_players_to_command_turnirs($turnir_id=0)
{
    // ������� ���� ���������� ������� ������� ������ ���� 1 ���� � ������� � ������
    $sql ='SELECT pl_id_1 AS player_id
FROM bs_reiting
WHERE turnir_id = '.$turnir_id.'
  AND pair_number > 0

UNION

SELECT pl_id_2 AS player_id
FROM bs_reiting
WHERE turnir_id = '.$turnir_id.'
  AND pair_number > 0;
';
  //  s($sql);
    $aPlayers = db_list($sql);
 //   s($aPlayers);
    $values = [];
// ��������� ���������� ������� IGNORE ������ �� ������
    foreach ($aPlayers as $playerId) {
        $values[] = '(' . (int)$playerId['player_id'] . ', ' . (int)$turnir_id . ')';
    }
   // s($values);
    if ($values) {
        $sql = "
        INSERT IGNORE INTO bs_turnirplayers (player_id, turnir_id)
        VALUES " . implode(',', $values);
      //  s($sql);
        db_query($sql);
    }


}
function sql_raschet($turnir_id=0,$is_team_league=0)
{

    //  $this->id=$turnir_id;
    $is_first=1;
    $is_new=1;
    $is_new_player=1;
    $sql = 'SELECT  (select tp.end_reiting from '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.'  tt WHERE  tt.id=tp.turnir_id AND   tp.player_id=p.id AND tt.date_raschet IS NOT null
       AND ((SELECT tt1.dat FROM '.T_TURNIR_PLAYERS.' tp1, '.T_TURNIRS.'  tt1 WHERE tt1.id=tp1.turnir_id AND tt1.date_raschet IS NOT null
        AND tp1.player_id=p.id AND tp1.turnir_id='.$turnir_id.' AND CASE WHEN tt.dat=tt1.dat then tt1.id>tt.id else tt1.id<>tt.id end)>=tt.dat) 
order by tt.dat DESC, tt.id DESC  limit 1) as reit,p.start_reiting,p.id,reiting_ukraine 
FROM '.T_PLAYERS.' p,bs_turnirplayers tpp 
            where  p.id=tpp.player_id  and tpp.turnir_id='.$turnir_id.' 
            and p.is_team=0  
				AND  exists(select * from `'.T_REITING.'` tp where (p.id=tp.pl_id_1 or p.id=tp.pl_id_2) and perenos_etap=0  and tp.turnir_id='.$turnir_id.' ) 
            order by 1 desc, start_reiting desc  ';
    // debug: s($sql);
    $allPlayers_ = db_list($sql);
    $allPlayers =array();
 //   s('dooooooo');
  //  s($allPlayers_);
    // ���� ������ ����� � �������� id ����������� ������
    foreach ($allPlayers_ as  $k => $aPlayer)
    {
        $reiting = $aPlayer['reit'];
        if (empty($reiting)) // ���� ������ ����� 2 ������� � 1 ����
        {
            $sql='SELECT end_reiting FROM '.T_TURNIR_PLAYERS.' tp2
         WHERE tp2.player_id='.$aPlayer['id'].' AND tp2.turnir_id<'.$turnir_id.' order by  tp2.turnir_id DESC  limit 1';
            // s($sql);
            $reiting = db_field($sql,'end_reiting');
            //   s('$reiting='.$reiting);
        }
        $start_reiting = $is_first==1 ? 0 : $aPlayer['start_reiting'];
        $reiting_ukraine = $is_first==1 && !empty($aPlayer['reiting_ukraine']) && $aPlayer['reiting_ukraine']>0  ? $aPlayer['reiting_ukraine']*10 : 0;
        // ���� ���� ������� ������� �� ��������� ��� � ������ ����� �� ���� ���������� �� 10, ���� ��� �� ��������� ������� 50
        if (empty($reiting)) {
            $is_new_player=1;
            //   s($PlayId.' new _play');
            if ($is_first==1) {$is_new=1;}

            $reiting = !empty($reiting_ukraine) && $reiting_ukraine>0 ? $reiting_ukraine : $start_reiting;
            //   s('$new_reiting='.$reiting);
        }
        $aPlayer['reit'] = $reiting;
        $aPlayer['start_reiting'] = $start_reiting;
        $allPlayers[$aPlayer['id']]= $aPlayer;
    }
   // �������� �������� �� �������� � ���� ����� � ������� ������� ��������� �� ����� ������
    proverkaTurnirs($allPlayers,$turnir_id,$is_team_league);
  //  s('posle proverkaTurnirs');

    foreach ($allPlayers as  $PlayId => $aPlayer)
    {// ������� ��� ���������� ��� �� ������� ������� � �������
        $sqlR = 'select * from '.T_REITING.' where (pl_id_1='.$PlayId.' or pl_id_2='.$PlayId.') and COALESCE(win_player,0)>0 and perenos_etap=0 and turnir_id='.$turnir_id;
    //  s($sqlR);
        $allGames = db_list($sqlR);
    //    s($allGames);
        $is_new=0;
        if (!empty($allGames)) // ���� ������ �� ������ � ����� ����� �� ������ ������� ����� ��������� ����������
        {
//������� �� ������� ��� ������ ��� ����������
            $diff=0; // ������
            $cntGames=0;
            $cntWins=0;
            $cntLose=0;
            $smDiff=0;
            $sumSet=0;
            $sumSetWins=0;
            $sumSetLose=0;
            $reiting = $aPlayer['reit'];

            $start_reiting = $is_first==1 ? 0 : $aPlayer['start_reiting'];
            $reiting_ukraine = $is_first==1 && !empty($aPlayer['reiting_ukraine']) && $aPlayer['reiting_ukraine']>0  ? $aPlayer['reiting_ukraine']*10 : 0;
            // ���� ���� ������� ������� �� ��������� ��� � ������ ����� �� ���� ���������� �� 10, ���� ��� �� ��������� ������� 50
            if (empty($reiting)) {
                $is_new_player=1;
                //   s($PlayId.' new _play');
                if ($is_first==1) {$is_new=1;}

                $reiting = !empty($reiting_ukraine) && $reiting_ukraine>0 ? $reiting_ukraine : $start_reiting;
                //   s('$reiting='.$reiting);
            }
            // $reiting = (!empty($reiting) && $reiting>0) ? $reiting : $start_reiting; // ���� ��� ��� �������� ����� ���������
            foreach($allGames as $g => $aGame)
            { $reiting_2=0;
                $set1 = 0;
                $set2 = 0;
                $Play2_id=0;
                $cntGames++;
                $diff=0;
                // ���������� ����� � ������ 1 ��� 2
                if ($aGame['pl_id_1']== $PlayId)
                {   $set1 = $aGame['set_1'];
                    $set2 = $aGame['set_2'];
                    $set1 = $set1=='W' ? 3 : $set1;
                    $set2 = $set2=='W' ? 3 : $set2;
                    $set1 = $set1=='L' ? 0 : $set1;
                    $set2 = $set2=='L' ? 0 : $set2;
                    //������� �������� �����
                    $smSets1 = $set1=='W' ? 0 : $set1;
                    $smSets2 = $set2=='W' ? 0 : $set2;
                    $sumSet=$sumSet+($smSets1+$smSets2);
                    $sumSetWins=$sumSetWins+ $smSets1;
                    $sumSetLose=$sumSetLose+ $smSets2;

                    $Play2_id = $aGame['pl_id_2'];
                    $diff=$aGame['diff_1'];
                    //   $reiting_2 = $allPlayers[$aGame['pl_id_2']]['reit'];
                }
                // ���������� ����� � ������ 1 ��� 2
                if ($aGame['pl_id_2']== $PlayId)
                {
                    $set1 = $aGame['set_2'];
                    $set2 = $aGame['set_1'];
                    $set1 = $set1=='W' ? 3 : $set1;
                    $set2 = $set2=='W' ? 3 : $set2;
                    $set1 = $set1=='L' ? 0 : $set1;
                    $set2 = $set2=='L' ? 0 : $set2;
                    //������� �������� �����
                    $smSets1 = $set1=='W' ? 0 : $set1;
                    $smSets2 = $set2=='W' ? 0 : $set2;
                    $sumSet=$sumSet+($smSets1+$smSets2);
                    $sumSetWins=$sumSetWins+ $smSets1;
                    $sumSetLose=$sumSetLose+ $smSets2;

                    //   $reiting_2 = $allPlayers[$aGame['pl_id_1']]['reit'];
                    $Play2_id = $aGame['pl_id_1'];
                    $diff=$aGame['diff_2'];
                }
                //   if (!is_numeric($set2)) s('$set2='.$set2.'==');
                if ($set1-$set2>0) $cntWins++; else $cntLose++;
                $smDiff=$smDiff+$diff;

                // ������� ������������� �������� � ������� ������� ������� �� 2 �������
                //   list($diff1,$diff2) = $this->add_reiting_rec($PlayId,$Play2_id,$reiting,$reiting_2,$set1,$set2,$aGame['id']);


            } // end for $allGames
            $id_rec='';
            $sql = 'select id from '.T_TURNIR_PLAYERS.' where player_id='.$PlayId.' and turnir_id='.$turnir_id.' limit 1';
            //  s($sql);
            $id_rec=db_field($sql,'id');
            // s('$id_rec='.$id_rec);
            $reiting_end = ($reiting+$smDiff)<1 ? 1 : ($reiting+$smDiff);
            $diff_round= round($reiting_end,0)-round($reiting,0);
            $where = 'player_id='.$PlayId.',turnir_id='.$turnir_id.',beg_reiting='.$reiting.',end_reiting='.($reiting_end).',
         diff='.$smDiff.',cnt_games='.$cntGames.',cnt_wins='.$cntWins.',cnt_lose='.$cntLose.
                ', cnt_sets='.$sumSet.', cnt_sets_win='.$sumSetWins.', cnt_sets_lose='.$sumSetLose.', diff_round='.$diff_round.', beg_reiting_fntu='.$reiting_ukraine;
            if (!empty($id_rec)) $sql= 'UPDATE '.T_TURNIR_PLAYERS.' SET '.$where .' where id='.$id_rec;
            else  $sql= 'insert INTO '.T_TURNIR_PLAYERS.' SET '.$where ;
            //     s($sql);
            db_query($sql);
            // ����� ������� ������� ������� ������ � ��� ���������� �����
            updateStatisticPlayer($PlayId,$turnir_id);
        } // end if $allGames
    } // end for $allPlayers
 //   s('eneeessssddd');
}
function updateStatisticPlayer($PlayId,$turnir_id)
{
    $is_new = 1;
    $sql='select 
  COALESCE((select tp.end_reiting from '.T_TURNIR_PLAYERS.' tp,'.T_TURNIRS.' t where t.id=tp.turnir_id and turnir_id='.$turnir_id.'
   and p.id=player_id order by t.dat desc limit 1),start_reiting) as reiting,
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
    $proc_wins= !empty($aPlayer['cnt_games']) ? round(($aPlayer['cnt_wins']/$aPlayer['cnt_games']*100),0) : 0 ;
    db_query('UPDATE '.T_PLAYERS.' SET 
    cnt_games='.$aPlayer['cnt_games'].',
    cnt_wins='.$aPlayer['cnt_wins'].',
    cnt_lose='.$aPlayer['cnt_lose'].',
    proc_wins='.$proc_wins.',
    reiting='.$aPlayer['reiting'].',
    '.($is_new==1 ?   'start_reiting='.$aPlayer['reiting'].',':'').'
    reiting_min='.$aPlayer['min_reiting'].',
    reiting_max='.$aPlayer['max_reiting'].',
    reiting_avg='.$aPlayer['avg_reiting'].',
    cnt_turnirs='.$aPlayer['cnt_turnirs'].' where id='.$PlayId);
}
function proverkaTurnirs($allPlayers,$turnir_id,$is_team_league=0)
{
    $is_first=1;
    $sql = 'select id,date_create,dat from ' . T_TURNIRS . ' r WHERE  id=' . $turnir_id;
    // s($sql);
    $aTurnirs = db_row($sql);
    //  $aTurnirs['dat']= date_for_firebird_format($aTurnirs['dat']);
    // s($aTurnirs);
    $sql_='';
    if ($is_team_league>0) $sql_ = ' and pair_number>0 ';
    // ������� ��� ���������� ��� �� ������� �������
    $sqlR = 'select * from '.T_REITING.' where  perenos_etap=0 and COALESCE(win_player,0)>0 and turnir_id='.$turnir_id.' '.$sql_;
    $allGames = db_list($sqlR);
    //  s($allPlayers);
    //  s($allGames);
    foreach($allGames as $g => $aGame)
    {
        if (!empty($aGame['pl_id_1']) && !empty($aGame['pl_id_2']))
        {
            if (empty($allPlayers[$aGame['pl_id_1']]) || empty($allPlayers[$aGame['pl_id_2']])) {
                continue;
            }
            $aPlayer1 = $allPlayers[$aGame['pl_id_1']];
            $aPlayer2 = $allPlayers[$aGame['pl_id_2']];
            $play1 = $aGame['pl_id_1'];
            $play2 = $aGame['pl_id_2'];
            $reiting1 = $aPlayer1['reit'];
            $reiting2 = $aPlayer2['reit'];
            // $start1 = $aPlayer1['start_reiting'];
            // $start2 = $aPlayer2['start_reiting'];
            $start1 = $is_first==1 ? 0 : $aPlayer1['start_reiting'];
            $start2 = $is_first==1 ? 0 : $aPlayer2['start_reiting'];
            $reiting_ukraine1 = $is_first==1 && !empty($aPlayer1['reiting_ukraine']) && $aPlayer1['reiting_ukraine']>0  ? $aPlayer1['reiting_ukraine']*10 : 0;
            $reiting_ukraine2 = $is_first==1 && !empty($aPlayer2['reiting_ukraine']) && $aPlayer2['reiting_ukraine']>0  ? $aPlayer2['reiting_ukraine']*10 : 0;

            $reiting1 = (!empty($reiting1) && $reiting1>0) ? $reiting1 :(!empty($reiting_ukraine1) && $reiting_ukraine1>0 ? $reiting_ukraine1 : $start1);
            $reiting2 = (!empty($reiting2) && $reiting2>0) ? $reiting2 :(!empty($reiting_ukraine2) && $reiting_ukraine2>0 ? $reiting_ukraine2 : $start2);

            //    $reiting1 = (!empty($reiting1) && $reiting1>0) ? $reiting1 : $start1; // ���� ��� ��� �������� ����� ���������
            //    $reiting2 = (!empty($reiting2) && $reiting2>0) ? $reiting2 : $start2; // ���� ��� ��� �������� ����� ���������

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
                //������ ������ ������
                if (($reiting1-$reiting2)<100) {
                    $diff1 = (100-($reiting1-$reiting2))/WIN_KOEF;
                    if (strtotime($aTurnirs['dat'])>=strtotime('01.06.2024')){
                        $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF_NEW;
                        // s('dat='.$aTurnirs['dat'].' LOSE_KOEF_NEW='.LOSE_KOEF_NEW);
                    } else{
                        $diff2 = -(100-($reiting1-$reiting2))/LOSE_KOEF;
                        //  s('dat='.$aTurnirs['dat'].' LOSE_KOEF='.LOSE_KOEF);

                    }

                    // $diff2 = -(100-($reiting1-$reiting2))/20;
                }
            }

            // ���� ������� 2 �����
            if ($set_1_otkas && $set2>$set1) {
                // ��������� �� ������ �� ������ 100
                if ($reiting2-$reiting1<100) {
                    $diff2 = (100-($reiting2-$reiting1))/WIN_KOEF;
                    if (strtotime($aTurnirs['dat'])>=strtotime('01.06.2024')){
                        $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF_NEW;
                        // s('dat='.$aTurnirs['dat'].' LOSE_KOEF_NEW='.LOSE_KOEF_NEW);
                    } else{
                        $diff1 = -(100-($reiting2-$reiting1))/LOSE_KOEF;
                        //  s('dat='.$aTurnirs['dat'].' LOSE_KOEF='.LOSE_KOEF);

                    }
                }
            }

            // ���������� ����������
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
// ������������ ���� �� ����� ������� ��� ��� ��� topplayersLeagueObject
function set_points_turnir ($league_id=0,$is_team_league=0){
    global $ochki_top_ligs; // ���������� ������ �� �������� ���������� ����� �� ������
    $sql_='';
    if ($is_team_league>0) $sql_= 'AND EXISTS(SELECT * from  bs_players p where p.id=tp.player_id AND p.is_team=1) ';
    $sql = 'SELECT player_id, (SELECT NAME FROM bs_players WHERE player_id=id) AS NAME, SUM(tp.points) AS points,
COUNT(tp.turnir_id) AS turnirs,
SUM(tp.cnt_games) AS cnt_games,SUM(tp.cnt_wins) AS cnt_wins,SUM(tp.cnt_lose) AS cnt_lose,
SUM(tp.cnt_sets) AS cnt_sets,SUM(tp.cnt_sets_win) AS cnt_sets_win,SUM(tp.cnt_sets_lose) AS cnt_sets_lose

 FROM '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.' t  WHERE t.league_id='.$league_id.' AND t.id=tp.turnir_id '.$sql_.' 
GROUP BY player_id ORDER BY points desc';
      // debug: s($sql);
    $UserMesta = db_list($sql);
  //  s($UserMesta);
    if (!empty($UserMesta))
    {

        foreach ($UserMesta as $row) {
            $upsertSql = '
    INSERT INTO bs_top_players
        (league_id, player_id, turnirs, points, cnt_games, cnt_wins, cnt_lose, cnt_sets, cnt_sets_win, cnt_sets_lose)
    VALUES
        ('.$league_id.', '.$row['player_id'].', '.$row['turnirs'].', '.$row['points'].', '.$row['cnt_games'].', 
        '.$row['cnt_wins'].', '.$row['cnt_lose'].', '.$row['cnt_sets'].', '.$row['cnt_sets_win'].', '.$row['cnt_sets_lose'].')
    ON DUPLICATE KEY UPDATE
        turnirs       = VALUES(turnirs),
        points        = VALUES(points),
        cnt_games     = VALUES(cnt_games),
        cnt_wins      = VALUES(cnt_wins),
        cnt_lose      = VALUES(cnt_lose),
        cnt_sets      = VALUES(cnt_sets),
        cnt_sets_win  = VALUES(cnt_sets_win),
        cnt_sets_lose = VALUES(cnt_sets_lose)
';

 //   s($upsertSql);
            // ������ ���� ������. ���� � ���� ���� ����� insertOrUpdate � ����� ��.
            db_query($upsertSql);
        }

    }
}
// ����� �� ������� ������������ ������ �� ����� ����� ������� ����� � ���������� ���������� �����
function getPointsForPlace(int $place, array $rules): int
{
    foreach ($rules as $key => $points) {
        if (strpos($key, '-') !== false) {
            // ��������
            [$start, $end] = explode('-', $key);
            if ($place >= (int)$start && $place <= (int)$end) {
                return $points;
            }
        } else {
            // ���������� �����
            if ($place === (int)$key) {
                return $points;
            }
        }
    }

    // ���� ������ �� �������
    return 0;
}
// ������������ ����� ��� ������
function set_mesta_turnir ($turnir_id=0)
{
    $sql = 'SELECT * FROM bs_etaps_work WHERE turnir_id='.$turnir_id.' AND istochnik_posev=0';
    // debug: s($sql);
    $aMesta = db_list($sql);
    if (!empty($aMesta))
    {
        foreach ($aMesta as $userMesto)
        {
            // ������ � �������� ����� ��������� ���� ��� �������� �����
           setRecurMesta($userMesto['id'],$turnir_id,1);
        }
    }

}

function recalculate_team_turnir_stats($turnir_id=0)
{
    $turnir_id = (int)$turnir_id;
    if ($turnir_id <= 0) {
        return;
    }

    $sql = 'SELECT DISTINCT tp.player_id
            FROM '.T_TURNIR_PLAYERS.' tp
            INNER JOIN '.T_PLAYERS.' p ON p.id=tp.player_id
            WHERE tp.turnir_id='.$turnir_id.' AND p.is_team=1';
    $teams = db_list($sql);
    if (empty($teams)) {
        return;
    }

    foreach ($teams as $team) {
        $team_id = !empty($team['player_id']) ? (int)$team['player_id'] : 0;
        if ($team_id <= 0) {
            continue;
        }

        $sql = 'SELECT pl_id_1, pl_id_2, set_1, set_2, win_player, lose_player
                FROM '.T_REITING.'
                WHERE turnir_id='.$turnir_id.'
                  AND COALESCE(win_player,0)>0
                  AND (pair_number=0 OR pair_number IS NULL OR pair_number="")
                  AND (pl_id_1='.$team_id.' OR pl_id_2='.$team_id.')';
        $games = db_list($sql);

        $cnt_games = 0;
        $cnt_wins = 0;
        $cnt_lose = 0;
        $cnt_sets_win = 0;
        $cnt_sets_lose = 0;

        if (!empty($games)) {
            foreach ($games as $game) {
                $raw_set_1 = isset($game['set_1']) ? trim((string)$game['set_1']) : '';
                $raw_set_2 = isset($game['set_2']) ? trim((string)$game['set_2']) : '';

                $set_1 = ($raw_set_1 === 'W') ? 3 : (($raw_set_1 === 'L') ? 0 : (int)$raw_set_1);
                $set_2 = ($raw_set_2 === 'W') ? 3 : (($raw_set_2 === 'L') ? 0 : (int)$raw_set_2);

                if ($set_1 === $set_2) {
                    continue;
                }

                $cnt_games++;

                if ((int)$game['pl_id_1'] === $team_id) {
                    $team_sets_win = $set_1;
                    $team_sets_lose = $set_2;
                } else {
                    $team_sets_win = $set_2;
                    $team_sets_lose = $set_1;
                }

                $cnt_sets_win += $team_sets_win;
                $cnt_sets_lose += $team_sets_lose;

                if (!empty($game['win_player']) && (int)$game['win_player'] === $team_id) {
                    $cnt_wins++;
                } else {
                    $cnt_lose++;
                }
            }
        }

        $sql = 'UPDATE '.T_TURNIR_PLAYERS.' SET
                cnt_games='.$cnt_games.',
                cnt_wins='.$cnt_wins.',
                cnt_lose='.$cnt_lose.',
                cnt_sets_win='.$cnt_sets_win.',
                cnt_sets_lose='.$cnt_sets_lose.',
                cnt_sets='.($cnt_sets_win + $cnt_sets_lose).'
                WHERE turnir_id='.$turnir_id.' AND player_id='.$team_id;
        db_query($sql);
    }
}

function setRecurMesta ($etap_id,$turnir_id,$mestaFrom=1)
{
    if (!empty($etap_id))
    {
        // ������� ����� ��� ��������� �����
        setMesta($etap_id,$turnir_id,$mestaFrom);
        $sql = 'SELECT * FROM bs_etaps_work WHERE turnir_id='.$turnir_id.' AND istochnik_posev='.$etap_id.' ORDER BY mesto_from';
        $aEtaps = db_list($sql);
        if (!empty($aEtaps))
        {
            foreach ($aEtaps as $aEtap)
            {
                $mestaFrom = $mestaFrom>$aEtap['mesto_from'] ? $mestaFrom : $aEtap['mesto_from'];
                setRecurMesta($aEtap['id'],$turnir_id,$mestaFrom);
            }
        }

    }

}
function setMesta ($etap_id,$turnir_id,$mestaFrom=1)
{
    global $ochki_top_ligs; // ���������� ������ �� �������� ���������� ����� �� ������
    $sql = 'select * from bs_etaps_players_mesta where etap_id='.$etap_id;
    //  s($sql);
    $UserMesta = db_list($sql);
    if (!empty($UserMesta))
    {
        foreach ($UserMesta as $Usrmesta)
        {
            $mesto = $mestaFrom + ($Usrmesta['mesto_all']-1);
            $points = getPointsForPlace($mesto, $ochki_top_ligs);
            $sql = 'update '.T_TURNIR_PLAYERS.' set mesto='.$mesto.', points='.$points.'  where player_id='.$Usrmesta['player_id'].' and  turnir_id='.$turnir_id;
            //   s($sql);
            db_query($sql);
        }
    }

}
