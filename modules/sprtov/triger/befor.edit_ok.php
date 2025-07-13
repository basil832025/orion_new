<?php

$type_id=poste('type_id');
$form=poste('form');
    // проверка корректности цені
   if (!empty($form['summa'])) 
  { $isNoMount = false;
    if (!is_numeric($form['summa'])) 
      $isNoMount = true;
    if ($isNoMount)  
     window_mess('Данное знаение не похоже на цену!');
   } 

    //проверка корректности месяца
  if (!empty($form['cnt_mount'])) 
  { $isNoMount = false;
    if (!is_numeric($form['cnt_mount'])) 
      $isNoMount = true;
     else 
    if ($form['cnt_mount']>12) $isNoMount = true; 
     if ($isNoMount)  
     window_mess('Данное знаение не похоже на месяц!');
   }
   // проверка корректности часов
   if (!empty($form['cnt_hour'])) 
  { $isNoMount = false;
    if (!is_numeric($form['cnt_hour'])) 
      $isNoMount = true;
    if ($isNoMount)  
     window_mess('Данное знаение не похоже на часы!');
   }
 
     // проверка корректности время с
   if (!empty($form['time_from'])) 
  { $isNoMount = false;
  $time = $form['time_from'];
  if (strlen($time)<5) $isNoMount = true;
  $hour = substr($time,0,2);
  $razd = substr($time,2,1);
  $minut = substr($time,3,2);
  if ($razd!=':' || !is_numeric($hour) || (is_numeric($hour) && $hour>23) || !is_numeric($minut) || (is_numeric($minut) && $minut>59))
  $isNoMount = true;
  /*  if (!is_numeric($form['summa'])) 
      $isNoMount = true;*/
    if ($isNoMount)  
     window_mess('[Время с] должно быть в формате ЧЧ:ММ (Пример 08:00)! '.$hour.$razd.$minut);
   } 
      // проверка корректности время по
   if (!empty($form['time_to'])) 
  { $isNoMount = false;
  $time = $form['time_to'];
  if (strlen($time)<5) $isNoMount = true;
  $hour = substr($time,0,2);
  $razd = substr($time,2,1);
  $minut = substr($time,3,2);
  if ($razd!=':' || !is_numeric($hour) || (is_numeric($hour) && $hour>23) || !is_numeric($minut) || (is_numeric($minut) && $minut>59))
  $isNoMount = true;
  /*  if (!is_numeric($form['summa'])) 
      $isNoMount = true;*/
    if ($isNoMount)  
     window_mess('[Время по] должно быть в формате ЧЧ:ММ (Пример 08:00)! '.$hour.$razd.$minut);
   } 
  

        // проверка корректности цені
   if (!empty($form['cnt'])) 
  { $isNoMount = false;
    if (!is_numeric($form['cnt'])) 
      $isNoMount = true;
    if ($isNoMount)  
     window_mess('Данное знаение не похоже на количество!');
   } 
    
 //window_mess('ВСЕ ОК');
?>