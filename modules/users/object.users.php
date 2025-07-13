<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class UsersObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {//$_SESSION['gt']['user_rule']
   //   s(      $_SESSION['gt']);
   // $this->addFTL(array('name'=>'№ заказа','type'=>'text','oper'=>'edit','width'=>'25','name_field'=>'player_id','bd_field'=>'player_id')); 
    $this->addFTL(array('name'=>'№','type'=>'number','width'=>'5')); 
   
    $this->addFTL(array('name'=>'Редагу-<br />вати','type'=>'edit','width'=>'40'));
    $this->addFTL(array('name'=>'ПІБ користувача','name_field'=>'user_name','bd_field'=>'user_name','filter'=>1));
 //   $this->addFTL(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','width'=>'100','filter'=>1,'speedsearch'=>5));
   $this->addFTL(array('name'=>'Посада','name_field'=>'user_job','bd_field'=>'user_job','width'=>'250'));
   $this->addFTL(array('name'=>'Еmail','name_field'=>'email','bd_field'=>'email','width'=>'150'));
   $this->addFTL(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','width'=>'80'));
   $this->addFTL(array('name'=>'Логін/email Ligas','name_field'=>'ligas_login_email','bd_field'=>'ligas_login_email','width'=>'80'));
   $this->addFTL(array('name'=>'Місто','name_field'=>'city','bd_field'=>'city','width'=>'80','type'=>'prostspr'));
   $this->addFTL(array('name'=>'Клуб','name_field'=>'club','bd_field'=>'club','width'=>'80','type'=>'prostspr'));
   $this->addFTL(array('name'=>'Активність','type'=>'plus_minus', 'name_field'=>'active','bd_field'=>'active','width'=>'30'));
  

$this->addFF(array('name'=>'ПІБ користувача','name_field'=>'user_name','bd_field'=>'user_name','required'=>'ПІБ користувача обов"язкове'));
$this->addFF(array('name'=>'Посада','name_field'=>'user_job','bd_field'=>'user_job','required'=>'Посада обов"язкова'));
$this->addFF(array('name'=>'Логін','name_field'=>'user_login','bd_field'=>'user_login','required'=>'Логін обов"язковий'));
$this->addFF(array('name'=>'Пароль','name_field'=>'user_pass_new','type'=>'TextNoSQL'));
$this->addFF(array('name'=>'Пароль','name_field'=>'user_pass','type'=>'Hidden', 'bd_field'=>'user_pass'));
$this->addFF(array('name'=>'Права користувача','name_field'=>'user_rule','type'=>'ProstSpr', 'id_spis'=>'1', 'bd_field'=>'user_rule'));
$this->addFF(array('name'=>'Місто по замовченню','name_field'=>'city','type'=>'ProstSpr', 'id_spis'=>'4', 'bd_field'=>'city'));
$this->addFF(array('name'=>'Клуб по замовченню','name_field'=>'club','type'=>'ProstSpr', 'id_spis'=>'3', 'bd_field'=>'club'));
$this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));
$this->addFF(array('name'=>'Email','name_field'=>'email','bd_field'=>'email'));
$this->addFF(array('name'=>'Активність','type'=>'Checkbox','name_field'=>'active','bd_field'=>'active'));
$this->addFF(array('name'=>'Примітка','name_field'=>'users_comments','bd_field'=>'users_comments'));
$this->addFF(array('name'=>'Логін/email Ligas','name_field'=>'ligas_login_email','bd_field'=>'ligas_login_email'));
$this->addFF(array('name'=>'Пароль Ligas','name_field'=>'ligas_password','type'=>'pass','bd_field'=>'ligas_password'));


//unset($_SESSION['players']['where']);



  $this->setTableModule(T_USERS);
    
  self::$nameZ='Користувачі';
 self::$nameZList='';
 self::$nameZEdit='Редагування користувача';
 
  self::$submenu_list =array( 
     'back' => array('module' => 'settings', 'action' => 'show'),
    );
 /*self::$submenu_list =array( 
  // 'filter' => array('module' => 'players'),
     );*/
   
  }
}