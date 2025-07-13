<?php
 function getmicrotime(){
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
// для проверки скорости загрузки страницы (тест)    
    $time_start = getmicrotime();
    ob_start(); // поглощаем все выводы

    global $URLJS,$ERROR_BASE_DATA,$user_admin;
    $AdminSite=1;
    $admin_html_login='';
    // подключение всех конфигурационных файлов и констант, а также функций для работы с системой
    if (file_exists('config/access.php') && file_exists('config/const.php')){
        include_once 'config/access.php';
         include_once 'config/const.php';
        include_once 'config/const_admin.php';
       
    }else{
        die('Произошел крах системы нету одного или нескольких служебных файлов!');
    }
    
    // подключений функции главные
    //exit;
    if (file_exists('func/main_func.php')){
        include_once 'func/main_func.php';

    }else{
        die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
    }
    // подключений функций MYSQL
    if (file_exists('func/mysql.php')){
        include_once 'func/mysql.php';

    }else{
        die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
    }
    
  // конец if
    // вывод ошибок php. указание пользовательской функции обработок ошибок
    if (file_exists('func/error_func.php')){
        include_once 'func/error_func.php';
    }else{
        die('Произошел крах системы нету файла обработки ошибок!');
    }
 
    // дополниетельные или вспомогательные функции
    if (file_exists('func/dop_func.php')){
        include_once 'func/dop_func.php';

    }
    // s('init');    // проверка если толк продолжать дальше, а также проверка БД и Фтп
  /*  if (false!==$check_error=check_module_php()) {
        print $check_error;
        exit;
    }*/
   // выбор языка
    $language = post('lang_') ? post('lang_') : LANG_DEFAULT;
    define('LANG',$language);
    // подключаем класс генератора админ модулей

    
    //авторизация
    $_SESSION['avtoriz_'] = 1;
    // вывод страниц для всех модулей
    $pagging_html='';

   // проверка для редактора каталогов и созадние если нету
if (!is_dir(DIR_ROOT .DIR_IMAGES)){
    create_dir(DIR_ROOT .DIR_IMAGES);
}
if (!is_dir(DIR_ROOT .DIR_FILES)){
    create_dir(DIR_ROOT .DIR_FILES);
}
    //подключение констант базы данных после определение языка
    if (file_exists('config/const_db.php')){
        include_once 'config/const_db.php';
    }
      // скрипт урл
   // $URLJS = get('script_url') ? get('script_url') : '';
    $ufiles = get('ufiles') ? get('ufiles') : (post('ufiles') ? post('ufiles') : '');
    $ajax_upload = get('ajax_upload') ? get('ajax_upload') : (post('ajax_upload') ? post('ajax_upload') : '');
    // массив где ключи теги программы, а значения на что заменяем
     
    // проверка авторизация
      //  include_once "func/login.php";
        
     /*   if (!empty($URLJS)){
        // опредедение и загрузка виртуальных скриптов
        include_once 'func/script.php';
        exit;
    }*/
       if (!empty($ufiles)) {
        include_once 'func/ufiles.php';
    }
    // путь к детям модуля
   // $_SESSION['kernel']['grp_module']  = post('grp_module') ? post('grp_module') : ''; 
     //   test_create_table(T_USERS,'users');
        
    include_once "libs/auth_users.php";
    
    include_once 'libs/class.system.php';
    include_once 'libs/class_upload.php';
    include_once 'libs/gen_forms/class.object.php';
    
    //include_once 'libs/admin_gen_form.php';
    include_once 'libs/gen_forms/class.action.php';
    
    include_once 'libs/gen_forms/class.table_list.php';
    
    include_once 'libs/gen_forms/class.edit_form.php';
    include_once 'libs/gen_forms/class.add_form.php';
    
    include_once 'libs/gen_forms/class.form_save.php';
    
    include_once 'libs/gen_forms/class.forms_fields.php'; // класс вывода полей формы
    
    include_once 'libs/mysql/class.select.php'; // класс вывода полей формы
   
    include_once 'libs/mysql/class.query.php'; // класс вывода полей формы
  //  s($_SESSION);
  //   session_unset();
  //  session_destroy();
   /* if (!empty($ajax_upload)) {
        include_once 'func/uafiles.php';
    }*/ // конец if 

?>