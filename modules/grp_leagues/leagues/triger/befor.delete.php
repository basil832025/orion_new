<?php
$league_id =poste('id');

$sql ='select count(*) as cn from '.T_TURNIRS.'  where  league_id='.$league_id;
$cn_results=db_field($sql,'cn');

if ($cn_results>0 )     window_mess('В цій лізі є турніри. Видалять спочатку всі турніри!!!=' . $cn_results);