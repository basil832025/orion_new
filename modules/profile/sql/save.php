<?php

  $id = poste('id');
  $form = poste('form');
  
  $active = !empty($form['active']) ? 1 : 0;
  $Pass_new = !empty($form['user_pass_new']) ? 'user_pass="'.md5(md5($form['user_pass_new'])).'",' : '';
$where =' 
 user_name="'.$form['user_name'].'",
 user_job="'.$form['user_job'].'",
 user_login="'.$form['user_login'].'",
 '.$Pass_new.'
 phone="'.$form['phone'].'",
 email="'.$form['email'].'",
 users_comments="'.$form['users_comments'].'",
 ligas_login_email="'.$form['ligas_login_email'].'",
 ligas_password="'.$form['ligas_password'].'",
 active="'.$active.'"';
 
 if (!empty($id) && $id>0)
 {
    db_query('Update '.T_USERS.' SET '.$where .' where id='.$id);
 } else
 {
  //  db_query('INSERT INTO '.T_USERS.' SET '.$where);
 }
?>