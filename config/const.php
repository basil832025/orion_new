<?php
/*============================!!!!!!ВНИМАНИЕ!!!!!!=======================================
    сдесь собраны все константы системы, которыми вы можете управлять, но изменяйте их осторожно некоторые могут
    кординально изменить систему или привести ее к краху. Хорошо подумайте перед тем, как менять константу.
    Внимательно  прочитайте коментарий к констане
===========================!!!!!!ВНИМАНИЕ!!!!!!=========================================*/
 function is_session_started()
{
    if (php_sapi_name() === 'cli')
        return false;

    if (version_compare(phpversion(), '5.4.0', '>='))
        return session_status() === PHP_SESSION_ACTIVE;

    return session_id() !== '';
}
if (!is_session_started()) {
    $name_session_admin ='site_basil_adminsite';
    session_name($name_session_admin);
  //  session_set_cookie_params($cookie_options);
    session_start();
}
 
 
 //  $name_session_admin= 'site_basil_adminsiteRT';
 //   session_name($name_session_admin); 
//	session_start();
$rootPath = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__))), '/') . '/';
//echo $rootPath.'<br>';
$scriptPath = realpath(__DIR__); // путь при запуске через веб
//echo '$scriptPath='.$scriptPath;

  // путь к проэкту; если ваш сайт находится не в корне, а в каком-то каталоге глубже, то укажите путь к этому каталогу в этой константе, если вы не понимаете сути этой константы, то или не меняте ее или свяжитесь с создателем системы neo.basil@gmail.com
  define('PATH', ''); // первый символ пути не слеш например: my_site/admin/
  // путь к стартовой директории вычисляется автоматом  
  define('ROOT1', $_SERVER['DOCUMENT_ROOT'] .(substr($_SERVER['DOCUMENT_ROOT'],-1)=='/' ? '' : '/') .(PATH=='' || (substr(PATH, -1)=='/') ? PATH : PATH .'/'));
//echo ROOT1;
  define('ROOT', $rootPath);
  // путь к веб-сайту
  define('URL', 'https://' .$_SERVER['SERVER_NAME'] .(substr(PATH,0,1)=='/' ? '' : '/') .(PATH=='' || (substr(PATH, -1)=='/') ? PATH : PATH .'/'));

 //РЕЗЕРВНАЯ КОНСТАНТА  язык сайта, по умолчанию важный параметр, по которому строится зеркальные версии сайта если он зеркальный.
  define('LANG_DEFAULT_', 'ru');
  //РЕЗЕРВНАЯ КОНСТАНТА варианты структуры сайта: 1 - зеркальный; 2 - не зеркальный; 3 - смешаный (больше 2 языков должно быть)
  define('ZERK_', 1);

  // Выводить или не выводить ошибки работает только в тестовом режиме:
        // 0 вообще игнорировать ошибки. НЕ РЕКОМЕНДУЕТСЯ!!! рекомендованый режим 1
        // 1 записывать ошибки в файл и создавать копии файла ошибки, когда его размер достигнет больще 1 мб
        // 2 выводить на экран в сплывающем окне все ошибки
        // 3 записывать в файл, но не создавать копии файлов
        // 4 записывать в файл, и создавать логи подням в названии дата
  define('DISPLAY_ERRORS', 4);
    // Ошибки БАЗЫ данных MySql выводить или скрыть
  define('ERROR_DB', 1);
  // Минимальная версия PHP
  define("MIN_PHP","5");

  // секретный ключ для шифрования данных
  define('SECRET_KEY','SUpEbsdb7832mi9?..,sd89SecreT');
  // мыло админа системы, на которые будут отправляться разные служебные сообщения; резерв, если в БД нету такого email
  define('ADMIN_EMAIL', 'neo.basil@gmail.com');
  
    $Error_Ftp_Connect='';
   $ftp_Connect_glob='';
// папка откуда беруться шаблоны 
  define('SITE_NAME','agito');
  //define('SITE_NAME','portfolio');
  //define('SITE_NAME','rekonstr');
//  define('SITE_NAME','orh');
    // отоброжать ли всплывающее окно корзины заказов
  define('CART_VIEW',true);
  //константа шаблона сайта  
 // define('URL_SITE', 'sites/'.(!empty($_SESSION['url_site']) ? $_SESSION['url_site']: ((defined('SITE_NAME') && SITE_NAME!='') ? SITE_NAME:'')).'/');
  define('URL_SITE', '');

  /*================================================================================
  *         ВАЖНО! НЕ МЕНЯЙТЕ ПОСЛЕДУЕЩИЕ КОНСТАНТЫ ИХ ДОЛЖЕН МЕНЯТЬ ТОЛЬКО СОЗДАТЕЛЬ СИСТЕМЫ,
  *         ИЗМИНЕНИЯ МОЖЕТ ПРИВЕСТИ К НЕПРАВИЛЬНОЙ РАБОТЕ СИСТЕМЫ!!!
  *         СПАСИБО ЗА ПОНИМАНИЕ.
  * ================================================================================
  */
  // путь к cms системы не меняйте, пожалуйста, этот путь
  define('ROOT_A', ROOT .'');
 // echo (ROOT_A);
  // путь к веб-админке
  define('URL_A',  URL .'');
  //echo URL_A;
  // для редактора пути к папкам
  define('DIR_ROOT',        ROOT );
  define('DIR_IMAGES',    ROOT.URL_SITE.'uploads/images/');
  define('DIR_MEDIA',    ROOT.URL_SITE.'uploads/media/');
  define('DIR_FILES',        ROOT.URL_SITE.'uploads/files/');
  define('DIR_IMAGES_',    '/'.URL_SITE.'uploads/images/');
  define('DIR_MEDIA_',    '/'.URL_SITE.'uploads/media/');
  define('DIR_FILES_',        URL_SITE.'uploads/files/');
  define('DIR_FILES_SITE',        ROOT.URL_SITE.'uploads/files_site/');
  define('URL_FILES_SITE',        URL.URL_SITE.'uploads/files_site/');
  define('URL_FILES_SITE_',        URL_SITE.'uploads/files_site/');
  define('URL_FILES_SITE_MINI',        URL.URL_SITE.'uploads/files_site/mini/');
  define('DIR_FILES_SITE_MINI',        ROOT.URL_SITE.'uploads/files_site/mini/');
  define('URL_FILES_SITE_SMALL',        URL.URL_SITE.'uploads/files_site/small/');
  define('DIR_FILES_SITE_SMALL',        ROOT.URL_SITE.'uploads/files_site/small/');


  /* мыло создателя системы, ВНИМАНИЕ! не менянйте пожалуйста его никогда, на него будут отсылаться ошибки системы для того
   чтобы создатель мог мониторить работу системы и быстро реагировать на возникшую не штатную ситуацию, а также быстро
    исправить проблему, связаную с настройками хостинга или еще по каких-то причинах возникшую, Вы даже возможно и не
     успеете заметить эту проблему, как она будет исправлена.*/
  define('CREATER_SITE_EMAIL', '1245640@gmail.com');

  
?>