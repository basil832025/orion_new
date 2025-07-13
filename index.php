<?php
    #   * =======================================================================
    #   * Программа.............:    Система управления сайтом
    #   * Версия................:    27.03.2014.01
    #   * Назначение модуля.....:    Ядро системы
    #   * Author's Name ........:    Смык Василий Васильевич
    #   * Copyright(C) 2014.....:    Company Name   "BasilCompany"
    #   * Обратная связь  e-mail:    neo.basil@gmail.com
    #   * =======================================================================
   // иницилизируем и подключаем все константы и все файлы для работы

//unset($_SESSION);
//phpinfo(); exit;

   if (file_exists('config/init.php')){
        include_once 'config/init.php';

    }else{
        die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
    } 
    
 //   exit;
     /*  $_SESSION['kernel']['module'] = !empty($_POST['module']) ?  $_POST['module'] : 'parts';
       $_SESSION['kernel']['action'] = !empty($_POST['action'])  ?  $_POST['action'] : 'parts_list';
       $_SESSION['kernel']['java_script']='';
       $_SESSION['kernel']['close_']='1';
       //$_SESSION['kernel']['return_content_bool'] = !empty($_POST['return_content_bool']) ? $_POST['return_content_bool'] : 'false';
       $mTegsTextGlob['submenu'] = '';
       $mTegsTextGlob['content'] = '';*/
     // если это аякс запрос то иницилизируем  сесиям модули и действия а также посты 
        // подключений функций модулей общих и создание необходимых параметров для программы
    if (file_exists('func/modules.php')){
        include_once 'func/modules.php';

    }else{
        die('Произошел крах системы нету одного или нескольких служебных файлов функций!');
    }
    //s(ROOT_A.'update/zaplatka.php');
    if (file_exists(ROOT_A.'update/zaplatka.php'))
    { include_once ROOT_A.'update/zaplatka.php';}

//$_URL = explode('/',substr($_SERVER['REQUEST_URI'],1)); // Сохраняем 2 параметра
//s($_URL);


     $objSYS  = new SystemClass();
     $objSYS->workModuleAction();

     $objSYS->returnResultAjax();

    //===============================================================================================
  /*  if (!empty($user_admin) && !empty($_POST['ajax_method'])) {
        // запуск класса генератора модуля админки пока тест на новостях
    $ObjAGF = new admin_gen_form(); 
    // групировка модулей по папкам, например для глобального модуля настроек, размещаем пользователей, простые справочники....
   // s('modules/'.(!empty($_SESSION['kernel']['grp_module']) ? $_SESSION['kernel']['grp_module'] :$_SESSION['kernel']['module']) .'/'  .$_SESSION['kernel']['module'] .'.php');
    $grp_module = !empty($aModulesSettings[$_SESSION['kernel']['module']]['path']) ? $aModulesSettings[$_SESSION['kernel']['module']]['path'] : '';    // запуск модуля
    if (!empty($_SESSION['kernel']['module']) && file_exists('modules/'.(!empty($grp_module) ? $grp_module :$_SESSION['kernel']['module']) .'/'  .$_SESSION['kernel']['module'] .'.php')){
        // для страниц код проверка пост
        $page_id = post('page_id') ? post('page_id') : 1;
        
        $_SESSION[$_SESSION['kernel']['module']]['page'] = ($page_id ? $page_id : (!empty($_SESSION[$_SESSION['kernel']['module']]['page']) ? $_SESSION[$_SESSION['kernel']['module']]['page'] : 1));
        include_once 'modules/'.(!empty($grp_module) ? $grp_module :$_SESSION['kernel']['module']).'/'  .$_SESSION['kernel']['module'] .'.php';

    } // конец if
    
    $ObjAGF->start();
    $ObjAGF->shablon_show();
    
    
    // запоминаем весь контент
    $mTegsTextGlob['submenu'] = submenu($mTegsTextGlob['submenu']);
    $mTegsTextGlob['content'] = ob_get_contents();
    ob_clean();
   }*/
   // $user_admin - массив не пустой если успешная авторизация прошла 
   // $_SESSION['admin_content'] -  флаг чтобы повторно не выполянлся данный код, если раз загружен
   //&& !$admin_html_login -  временно убираю как буд-то излишок
   // вообщем данный код, если прошла успешная авторизация, то поменять контент, на внутриадминковый основной, иначе будет запрос на ввод логина и пароля
      /*  if ((!empty($user_admin)  && empty($_SESSION['admin_content'])) || (!empty($user_admin) && empty($_POST['ajax_method']))
        ){
        unset($_POST['username']);
        include_once "html/content.html";
        $admin_html_login = ob_get_contents();
        $admin_html_login = process_tegs($admin_html_login);
        ob_clean();
        $_SESSION['admin_content']=1;
    }  */
    // для простеньких запросов выполняем то что нужно и возващаем по минимум
  /*  if (!empty($_POST['ajax_method']) && $_POST['ajax_method']==2){
     Ajax(array('content' => $mTegsTextGlob['content'],
         'message_user' => (!empty($_SESSION['kernel']['message_user'])?$_SESSION['kernel']['message_user']:''),
         'java_script' => $_SESSION['kernel']['java_script'],
        'post_return' => (!empty($mTegsTextGlob['post_return']) ? $mTegsTextGlob['post_return'] : ''),
        ));
    exit;
 }
  */  
    
    // самый главный запрос после отправки на аякс выполнение, тут все возвращается аяксу ответом 
  /*   if (!empty($_POST['ajax_method'])){
        $admin_html_login_=$admin_html_login;
        $admin_html_login='';
      //  s($_SESSION['kernel']);
        $action_ = $_SESSION['kernel']['action'];
        $module_ = $_SESSION['kernel']['module'];
        unset($_SESSION['kernel']['action']);
        unset($_SESSION['kernel']['module']);
        //$tAction = $_SESSION['kernel']['action'];
      //  $_SESSION['kernel']['action'] = $_SESSION['kernel']['default_action'];
      //если это не стандартный запрос аякс, а например окно всплывающее то не будем запоминать действия и модуль
        if (!empty($_SESSION['kernel']['return_content_bool'])){
            $_SESSION['kernel']['module_prev'] = $module_;
            $_SESSION['kernel']['action_prev'] = $action_;
        }
       
        Ajax(array('content' => $mTegsTextGlob['content'],
        'submenu' => $mTegsTextGlob['submenu'],
        //'module' => $module_,
        'message_user' => (!empty($_SESSION['kernel']['message_user'])?$_SESSION['kernel']['message_user']:''),
        'action' => $action_,
        'content_body' => $admin_html_login_,
        'close_' => $_SESSION['kernel']['close_'],
        'java_script' => $_SESSION['kernel']['java_script'],
        'post_return' => (!empty($mTegsTextGlob['post_return']) ? $mTegsTextGlob['post_return'] : ''),
       // 'return_content_bool' =>   $_SESSION['kernel']['return_content_bool']
        ));

        exit;
    }
*/
    // считываеим стартовый файл html
 /*   include_once "html/index.html";
    $index_html  = ob_get_contents();
    
    ob_clean();
    // выводим все на экран прекрасный
    //ob_end_flush();
    //$time_end = getmicrotime();
    // print $time = $time_end - $time_start;

    print process_tegs($index_html);*/
   
      
    ?>