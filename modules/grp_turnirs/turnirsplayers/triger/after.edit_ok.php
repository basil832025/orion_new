<?php
$turnir_id=poste('turnir_id');
$form=poste('form');
// тригер что то делает но не чего не возвращаетs
$sql = 'SELECT count(*) as cnt FROM `'.T_TURNIR_PLAYERS.'` where turnir_id='.$turnir_id;
$cnt_players = db_field($sql,'cnt');
if (!empty($cnt_players) && $cnt_players>0) {
    $sql = 'update `' . T_TURNIRS . '` set cnt_players=' . $cnt_players . ' where id=' . $turnir_id;
    //s($sql);
    db_query($sql);
}
if (!empty($form['player_id'])){
    // записать стартовый рейтинг
    $sql = 'SELECT  (select tp.end_reiting from '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.'  tt WHERE  tt.id=tp.turnir_id AND   tp.player_id=p.id 
       AND ((SELECT tt1.dat FROM '.T_TURNIR_PLAYERS.' tp1, '.T_TURNIRS.'  tt1 WHERE tt1.id=tp1.turnir_id 
        AND tp1.player_id=p.id AND tp1.turnir_id='.$turnir_id.' AND CASE WHEN tt.dat=tt1.dat then tt1.id>tt.id else tt1.id<>tt.id end)>=tt.dat) 
order by tt.dat DESC, tt.id DESC  limit 1) as reit,
p.start_reiting,p.id,reiting_ukraine 
FROM '.T_PLAYERS.' p 
            where id = '.$form['player_id'].'  order by 1 desc, start_reiting desc  ';
    //   s($sql);
    $aPlayer = db_row($sql);
    if (!empty($aPlayer)){
        $reiting = $aPlayer['reit'];
   }
    $start_reiting = !empty($aPlayer['start_reiting']) ?  $aPlayer['start_reiting'] : 0;
    $reiting_ukraine =  !empty($aPlayer['reiting_ukraine']) && $aPlayer['reiting_ukraine']>0  ? $aPlayer['reiting_ukraine']*10 : 0;
    // если есть рейтинг украины то применяем его и расчет ведем от него умножанный на 10, если нет то стартовый рейтинг 50
    if (empty($reiting)) {
        $reiting = !empty($reiting_ukraine) && $reiting_ukraine > 0 ? $reiting_ukraine : $start_reiting;
    }
        $sql= 'UPDATE '.T_TURNIR_PLAYERS.' SET beg_reiting='.$reiting.' where turnir_id='.$turnir_id.' and player_id='.$form['player_id'];

         //    s($sql);
        db_query($sql);


}
