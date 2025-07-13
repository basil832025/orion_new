<?php
$form=poste('form');


if (empty($form['name']) || mb_strlen($form['name'])<3)
window_mess('Введіть назву турніру (мінімум 3 символа) '.mb_strlen($form['name']));

if (empty($form['dat']))
    window_mess('Введіть дату турніру '.mb_strlen($form['name']));

if (empty($form['tables']) || $form['tables']=0)
    window_mess('Введіть кількість столів');

if (empty($form['city']) || $form['city']=0)
    window_mess('Виберіть місто');
if (empty($form['club']) || $form['club']=0)
    window_mess('Виберіть клуб');



?>