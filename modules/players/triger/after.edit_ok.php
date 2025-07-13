<?php
$form=poste('form');
$id=poste('id');
// если добавляем нового участника то даем ему стартовый рейтинг 50
if (empty($id))
{
    $sql = 'select  * from '.T_PLAYERS .' order by id desc limit 1';
    $aPlayer=db_row($sql);
 //   s($aPlayer);
    if ($aPlayer['reiting']==0 && $aPlayer['start_reiting']==0)
    {
        $sql = 'update '.T_PLAYERS .' set start_reiting=0 where id='.$aPlayer['id'];
        db_query($sql);
    }
}
if (!empty($form['player_id']))
{
   // s('afterPlayerid='.$form['player_id']); turnirsplayers-raschet-id=1203
    $turnir_id=0;
    $sql = 'SELECT t.id,t.dat  FROM '.T_TURNIR_PLAYERS.' tp, '.T_TURNIRS.' t 
     WHERE (tp.player_id='.$form['player_id'].'  OR tp.player_id='.$id.') AND t.id=tp.turnir_id ORDER BY t.dat , t.id LIMIT 1 ';
    $turnir_id = db_field($sql,'id');
    redirect_Ajax('turnirsplayers','turnirsplayers-raschet-id='.$turnir_id);

}


