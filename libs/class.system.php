<?php
//phpinfo();
class SystemClass
{
	private static $module = 'players'; // модуль, котрый сейчас обрабатывается
	private static $action = 'list';  // действие модуля, которое сейчас обрабатывается
	private static $aPost = array();  // post массив
	private static $aFormPost = array();  // post массив по форме
	private static $isAjax = 0;  // является ли запрос аяксом
    private static $Java_script = '';  // массив javascripts функция
    public static $Java_script_module = '';  // массив javascripts функция
	public static $submenu = '';  // подменю кнопки
	private static $submenu2 = '';  // подменю кнопки
	public static $submenu2_html = '';  // подменю кнопки
	private static $mainmenu = '';  //  кнопки
    private static $menuTurnirs = [];  //  кнопки
    public static $menuTurnirs_module = [];  //  кнопки
	private static $profile = '';  //  кнопки
	private static $message_user = '';  // сообщения для позователя
	private static $content_body = '';  // перегрузка глобально контента, например с страницы авторизации до обычной админки
	private static $close_ = '1';  // по умолачнию закрывать окно всплывающее, 0 не закрывать
	private static $zagl_module = '';  // заглавие модуля
	private static $post_return = '';  // для возврата назад аяксу параметров нужных для отображаения в адрессной строке
	public static $post_return_dop = '';  // для возврата назад аяксу параметров нужных для отображаения в адрессной строке
	private static $post_return_noMA = '';  // для возврата назад аяксу параметров нужных для отображаения в адрессной строке
	private static $content = '';  // возвращаемая html страница после выполнения действия в модуле
	private static $isAvtoris = true;  // авторизирован ли пользователь
	//private static $page_id = 1;  // номер страницы при много
	private $logout = false;  // если пользователь отлогинился
	private $isAdminContent = false;  // маркер то что контент уже один раз загружен, тоесть основной content BODY
    
    public function __construct()
    {

        // для плагина fileinput удаления изображения из базы и диска
        if (!empty($_GET['isdelete'])) {
            self::$action = 'deleteImage';
            self::$module = '';
            self::$isAjax = 1;

        }

        if (!empty($_GET['q'])) {
            $ajax_url=explode('&',$_GET['q']);
            if (!empty($ajax_url))
            {
                $aPost=[];
                foreach ($ajax_url as $key => $elem)
                {
                    if ($key==0)  $aPost['q']=$elem;
                    else{
                        if (!empty($elem))
                        {
                            list($par_key,$par_val) = explode('=',$elem);
                            $aPost[$par_key] = $par_val;
                        }

                    }
                }
            }

            $_POST=$aPost;

        }
        self::$aPost = $_POST;
        self::$module = !empty(self::$aPost['module']) ? self::$aPost['module'] : self::$module;
        self::$action = !empty(self::$aPost['action']) ? self::$aPost['action'] : self::$action;
        self::$isAjax = !empty(self::$aPost['ajax_method']) ? self::$aPost['ajax_method'] : self::$isAjax;
        $_SESSION['width_body'] = !empty(self::$aPost['width_body']) ? self::$aPost['width_body'] : 1024;
        $_SESSION['is_mobile'] =  ($_SESSION['width_body']<768) ? true : false;
        $this->logout = !empty(self::$aPost['logout']) ? self::$aPost['logout'] : false;

         if (self::$module=='virtual') $this->clearLogs(); // читска логов
        self::$aFormPost = poste('form');
 
     }

   public function workModuleAction()
   { global $aModulesSettings,$globMenuArr,$globMenuArr_avtor; // глобальный массив модульных настроеек
        // проверяем в начале, что там с авторизациеей?)
         $this->isAvtorisUser();
        if (self::$isAvtoris && self::$isAjax)
       {// если авторизация прошла то запускаем обработку модулей
       $grp_module = !empty($aModulesSettings[self::$module]['path']) ? $aModulesSettings[self::$module]['path'] : '';    // запуск модуля
        if (self::$module && file_exists('modules/'.(!empty($grp_module) ? $grp_module :self::$module) .'/'  .self::$module .'.php')
       && !file_exists('modules/'.(!empty($grp_module) ? $grp_module :self::$module) .'/'  .'object.'.self::$module .'.php')){
        // для страниц код проверка пост
        // $_SESSION[self::$module]['page'] = ($page_id ? $page_id : (!empty($_SESSION[self::$module]['page']) ? $_SESSION[self::$module]['page'] : 1));
      include_once 'modules/'.(!empty($grp_module) ? $grp_module :self::$module).'/'  .self::$module .'.php';
      self::$content = ob_get_contents();
      ob_clean();
    } // конец if
    else{
      $ob = new ObjectRT(); // иницилизируем объект

     $ob->LoadObject(); // загружаем описание конкретного модуля (какие колонки табл будут отображаться, какие поля формы....)
     $objAction = new ActionModule(); // созадем объект обработчика действий
     $objAction->init(); // иницилиазируем
     self::$content =  $objAction->getContent(); // получаем результат действия в переменую контент для дальнейшего его отображения
 // wLog(self::$content);
    self::$Java_script =  $objAction->getJavaScript(); // получаем javascript код для выполнения
     self::$submenu = !empty(self::$submenu) ? self::$submenu : $objAction->getSubMenu(); // получаем подменю
     self::$submenu2 =  $ob->getSubMenu2() ?  $ob->getSubMenu2() :  $objAction->getSubMenu2(); // получаем подменю
     self:: $menuTurnirs=  $ob->getmenuTurnirs() ?  $ob->getmenuTurnirs() : ( $ob->getmenuLeagues() ? $ob->getmenuLeagues() :  self::$menuTurnirs_module); // получаем подменю
     self::$mainmenu =  $ob->getMainMenu(); // получаем главное меню
     self::$close_ =  $objAction->getClose(); //
     self::$submenu = submenu(self::$submenu);
     self::$submenu2 = !empty(self::$submenu2_html) ? self::$submenu2_html : submenu2(self::$submenu2);
     self::$menuTurnirs = menu_turnirs(self::$menuTurnirs,self::$module);
     self::$mainmenu = main_menu(self::$mainmenu,self::$module);
      }
        }else{
          unset($_SESSION);  
          $this->logout();
        }
        
        
   }
    public function returnResultAjax()
   { //   include "html/menu.php";
    //если успешная авториазция и еще не возращался основное тело админки, то вернем его
   if ((self::$isAvtoris  && empty($_SESSION['admin_content'])) || (self::$isAvtoris && !self::$isAjax))
 //  if ( (!self::$isAjax))
   {
        //unset($_POST['username']);
       include_once "html/svg.html";
       $svg_html  = ob_get_contents();
       ob_clean();

       include_once "html/content.html";
        $admin_html_login = ob_get_contents();
        self::$content_body = process_tegs($admin_html_login,$svg_html);
        ob_clean();
        $_SESSION['admin_content']=1;
        self::setJava_script('redirect_();');
    } 
    // для простеньких запросов выполняем то что нужно и возващаем по минимум
       $mess = !empty($_SESSION['MESSAGE_AJAX']) ? $_SESSION['MESSAGE_AJAX'] : self::getMessage_user();
        $java_script = !empty($_SESSION['JAVA_SCRIPT']) ? $_SESSION['JAVA_SCRIPT'] : (!empty(self::$Java_script_module) ? self::$Java_script_module : self::getJava_script()) ;
        $java_script = !empty($_SESSION['JAVA_SCRIPT_DOP']) ? $java_script.$_SESSION['JAVA_SCRIPT_DOP'] : $java_script;
        $Post_return__ = !empty($_SESSION['POST_RETURN']) ? $_SESSION['POST_RETURN'] : '' ;

        $_SESSION['JAVA_SCRIPT']='';
        if (!empty($_SESSION['MESSAGE_AJAX']))       unset($_SESSION['MESSAGE_AJAX']);
       if (!empty($_SESSION['JAVA_SCRIPT']))        unset($_SESSION['JAVA_SCRIPT']);
       if (!empty($_SESSION['POST_RETURN']))        unset($_SESSION['POST_RETURN']);
       if (self::$isAjax==2)
        {
            $cont = !empty($_SESSION['CONTENT_AJAX']) ? $_SESSION['CONTENT_AJAX'] : self::getContent();


            unset($_SESSION['CONTENT_AJAX']);

            if ($java_script=='json')
                 Ajax(array('items' => $cont

                ));
            else
                Ajax(array('content' => $cont,
                    'message_user' => $mess,
                    'java_script' => $java_script,
                    'post_return' => self::getPost_return(),
                ));
        }
    //   wLog(self::getContent());
        // самый главный запрос после отправки на аякс выполнение, тут все возвращается аяксу ответом
     if (self::$isAjax){
         Ajax(array('content' => self::getContent(),
        'submenu' => self::getSubmenu(),
        'submenu2' => self::getSubmenu2(),
        'mainmenu' => self::getMainmenu(),
        'menuTurinirs' => self::$menuTurnirs,
        //'module' => $module_,
        'message_user' => $mess,
        'action' => self::getAction(),
        'content_body' => self::getContent_body(),
        'close_' => self::getClose_(),
        'zagl_module' => self::getZaglModule(),
        'java_script' => $java_script,
        'post_return' => self::getPost_return().$Post_return__.self::$post_return_dop,
        //'profile' => self::getProfile(),
       // 'return_content_bool' =>   $_SESSION['kernel']['return_content_bool']
        ));
    }
    // если это редирект или просто первая загрузка возвращаем index.html
        include_once "html/index.html";
      $index_html  = ob_get_contents();
         ob_clean();
      print $this->process_tegs($index_html);
    
   } 
   
   public function isAvtorisUser()
   {
      $a= new auth_users(T_USERS); // создаем объект аунтификации 1 параметр таблица (по умолч. "users"),
      $a->time_session(TIME_SESSION_LOGIN); // устанавливаеться время сесии
      $a->start();  // запаускаем аунтификацию, пропустит или нет пользователя.
      $user_admin = $a->get_user_mas();
       if (empty($user_admin))
          self::$isAvtoris = false;  
       else
          self::$isAvtoris = true;  
    
    if (empty($_SESSION['gt']['user_rule'])) $_SESSION['gt']['user_rule']=10;
  /*   if (empty($user_admin))
    $_SESSION['admin_content']=false;
   */ 
    self::$isAvtoris = true;  
      
        
   if($this->logout) 
   {
    session_unset();
   // $a->logout();
    self::$isAvtoris = false;
   }  
   }
  public function getProfile() {
    $text = 'tt';
    if (!empty($_SESSION['gt']['user_name']))
    {
      $text = 'Профиль: '.$_SESSION['gt']['user_name'] ;  
    }
    return $text;
    }
  private function process_tegs($sContents) {

	// обработка тега галвного контента
    	//$sContents = preg_replace('#<\s*mainmenu\s*>.*?<\s*/\s*mainmenu\s*>#is', (!empty($mTegsTextGlob['mainmenu']) ? 1['mainmenu'] : '*****'), $sContents);

	return preg_replace('#<\s*content_html\s*>.*?<\s*/\s*content_html\s*>#is', self::$content_body, $sContents);

}
   
   public function logout ()
   {
      //  self::$content_body =  file_get_contents(ROOT_A.'html/admin.html'); 
     // self::setJava_script('redirect_();');
     self::setJava_script('redirect_url("'.URL_A.'#players-list");');
        
   }
   public static function getAction()
   {
        return self::$action;
   } 
   public static function setAction($action)
   {
        self::$action=$action;
   }  
   public static function getModule()
   {
        return self::$module;
   } 
      public static function setModule($module)
   {
        self::$module=$module;
   }  
    public static function getAPost($field='')
   {
    if ($field) 
    if (isset(self::$aPost[$field])) return self::$aPost[$field]; else return false; 
        return self::$aPost;
   } 
      public static function getAFormPost($field='')
   {
    if ($field) 
    if (isset(self::$aFormPost[$field])) return self::$aFormPost[$field]; else return false; 
       return self::$aFormPost;
   } 
      public static function getIsAjax()
   {
        return self::$isAjax;
   } 
      public static function getJava_script()
   {
        return self::$Java_script;
   } 
   public static function setJava_script($javascript)
   {
         self::$Java_script=$javascript;
   } 
      public static function getSubmenu()
   {
        return self::$submenu;
   } 
        public static function getSubmenu2()
   {
        return self::$submenu2;
   } 
       public static function getMainMenu()
   {
        return self::$mainmenu  ;
   } 
      public static function getMessage_user()
   {
        return self::$message_user;
   } 
      public static function getContent_body()
   {
        return self::$content_body;
   } 
         public static function getClose_()
   {
        return self::$close_;
   } 
         public static function getPost_return()
   {
        return self::$post_return;
   }
          public static function getPost_return_noMA()
   {
        return self::$post_return_noMA;
   }

          public static function setPost_return($post_return)
   {
         self::$post_return=$post_return;
   } 
           public static function setPost_return_noMA($post_return)
   {
         self::$post_return_noMA=$post_return;
   } 

   
         public static function getContent()
   {
        return self::$content;
   }
   static function getZaglModule()
   {
        return self::$zagl_module;
   }
    static function setZaglModule($zagl_module)
   {
         self::$zagl_module = $zagl_module;
   }
   // чистка логов
       private function clearLogs()
    {
        delete_file(ROOT_A.'error/error_' .date('d-m-Y') .'.html');
       exit;
    } 
    
    
}

?>