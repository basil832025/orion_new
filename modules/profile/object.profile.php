<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ProfileObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {//$_SESSION['gt']['user_rule']s($_SESSION['gt']['user_rule']);
   // $this->addFTL(array('name'=>'№ заказа','type'=>'text','oper'=>'edit','width'=>'25','name_field'=>'player_id','bd_field'=>'player_id')); 
 //s($_SESSION['gt']);

      $this->addFF(array('name'=>'ПІБ користувача','name_field'=>'user_name','bd_field'=>'user_name','required'=>'ПІБ користувача обов"язкове'));
      $this->addFF(array('name'=>'Посада','name_field'=>'user_job','bd_field'=>'user_job','required'=>'Посада обов"язкова'));
      $this->addFF(array('name'=>'Логін','name_field'=>'user_login','bd_field'=>'user_login','required'=>'Логін обов"язковий'));
      $this->addFF(array('name'=>'Пароль','name_field'=>'user_pass_new','type'=>'TextNoSQL'));
      $this->addFF(array('name'=>'Пароль','name_field'=>'user_pass','type'=>'Hidden', 'bd_field'=>'user_pass'));
  //    $this->addFF(array('name'=>'Права користувача','name_field'=>'user_rule','type'=>'ProstSpr', 'id_spis'=>'1', 'bd_field'=>'user_rule'));
      $this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));
      $this->addFF(array('name'=>'Email','name_field'=>'email','bd_field'=>'email'));
      $this->addFF(array('name'=>'Активність','type'=>'Checkbox','name_field'=>'active','bd_field'=>'active'));
      $this->addFF(array('name'=>'Примітка','name_field'=>'users_comments','bd_field'=>'users_comments'));
      $this->addFF(array('name'=>'Логін/email Ligas','name_field'=>'ligas_login_email','bd_field'=>'ligas_login_email'));
      $this->addFF(array('name'=>'Пароль Ligas','name_field'=>'ligas_password','type'=>'pass','bd_field'=>'ligas_password'));


//unset($_SESSION['players']['where']);



  $this->setTableModule(T_USERS);
    
  self::$nameZ='';
 self::$nameZList='Список пользователей';   
 self::$nameZEdit='Редагування користувача';
 self::$redirectUrl['action']='anyaction';
 self::$redirectUrl['module']='turnirs';
  self::$submenu_edit =array( 
     'back' => array('module' => 'turnirs', 'action' => 'list'),
        'save' => array(
                    'module' => 'profile',
                    'action' => 'edit_ok')
                   
                
    );
 /*self::$submenu_list =array( 
  // 'filter' => array('module' => 'players'),
     );*/
   
  }
}