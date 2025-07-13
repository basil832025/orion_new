<?php
$etap_id=poste('id');
$turnir_id=poste('turnir_id');
$sql ='select count(*) as cn from '.T_REITING.'  where etap_id='.$etap_id.' and turnir_id='.$turnir_id.' and COALESCE(set_2,0)>0 and COALESCE(set_1,0)>0 and perenos_etap=0';
$cn_results=db_field($sql,'cn');
if ($cn_results>0)
    window_mess('В даному етапі є вже ігри! Видаліть ігри з рахуноком або обнуліть результати!='.$cn_results);
else
{
    $sql ='delete from '.T_ETAPS_PLAYER_MESTA.'  where turnir_id='.$turnir_id .' and etap_id='.$etap_id;
    db_query($sql);
    $sql ='delete from '.T_REITING.'  where turnir_id='.$turnir_id.' and etap_id='.$etap_id ;
    db_query($sql);
}
?>
