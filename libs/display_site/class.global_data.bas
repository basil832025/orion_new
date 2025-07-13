<?php
// класс обрабатывает теги служебные
class GlobalData 
{
 protected static $html = ''; // текст для вывода обработанные
 protected static $lang = ''; // текст для вывода обработанные
 protected static $id_form = ''; // текст для вывода обработанные
 protected static $post_action = ''; // текст для вывода обработанные
 protected static $post_module = ''; // текст для вывода обработанные
 protected static $aform = ''; // текст для вывода обработанные
 protected static $aurl = array(); // текст для вывода обработанные
 public static $varible = array(); // текст для вывода обработанные
 protected static $parts = array(); // 
 protected static $module_id =0; //
 protected static $module_title =''; //
 protected static $module_keywords =''; //
 protected static $module_description =''; //
 protected static $maket =''; // макет сайта
 protected static $shablon =''; //шаблон страницы сайта
 protected static $parts_id =0; //
 
 
 public function __construct() // конструктор
 {
   self::$lang=(defined('LANG')? LANG :$this->lang);
   self::$id_form=poste('id');
   self::$post_action=poste('action');
   self::$post_module=poste('module');
   self::$aform=poste('form');
   $this->get_url();
 } 
static function getParts() {
   $sql = 'select * FROM `'.T_PARTS.'` 
   where url="'.trim(FIRST_URL).'"';
      $parts = db_row($sql);
   if ($parts){
      self::$module_id = $parts['parts_modules_id'];
      self::$module_title= $parts['title'];;
      self::$module_keywords= $parts['keywords'];
      self::$module_description= $parts['description'];
      self::$maket = $parts['maket'];
      self::$shablon = $parts['shablon'];
      self::$parts_id = $parts['id'];
      self::setVal('parts_id',$parts['id']);
      self::setVal('parts',$parts);
      self::$parts=$parts;
      return true;
   }
   return false;
} 
 // получаем массив адрессной строки и также константы для внутрених модулей 1 части адресса, последней, номер страницы для многострочного вывода
function get_url($url_='') {
   $url_ = ($url_) ? $url_ : (gete('url_') ? gete('url_') : '');
   // отсекаем жестко все что  идет после точки (розширение файлов)
   $url_=preg_replace('#([\w\d _-]+)(\..*)?$#is',"\\1",$url_);
   //убираем страницы для списков и запоминаем номер страницы
   if (preg_match('#page_(\d+)#i', $url_,$aPage_)){
      define('PAGE_ID', $aPage_[1]);
      $url_ = preg_replace("#/page_(\d+)#i",'', $url_) ;
   }else{
      define('PAGE_ID', 1);
    }
    
      preg_match_all('#/([a-z\d\-_]+)#i',$url_, $aurl_all);
      preg_match_all('#/(([a-z\d\-]+)(?:_(\d*))?)#i',$url_, $aurl);
  define('FIRST_URL',(!empty($aurl_all[1][0]) ? $aurl_all[1][0] : 'index'));
  define('SECOND_URL',(!empty($aurl_all[1][1]) ? $aurl_all[1][1] : ''));
  define('CNT_ELEM',count($aurl[1])); // к-во елементов в адрессной строке
   $this->first_url=FIRST_URL;
   $this->setVal('aurl',$aurl[1]);
   $this->setVal('aurl_name',$aurl[2]);
   $this->setVal('aurl_id',(!empty($aurl[3]) ?$aurl[3] : array()));
   define('LAST_URL',!empty($aurl_all[1][CNT_ELEM-1]) ? $aurl_all[1][CNT_ELEM-1] : FIRST_URL);
   $this->last_url=LAST_URL;
   $this->setVal('last_url',LAST_URL);
   $this->setVal('first_url',FIRST_URL);
   $this->setVal('second_url',SECOND_URL);
    define('URL_', $url_);
   $this->url=URL_;
   
   self::$aurl=$aurl;
}
static function setVal($name,$val) {
   self::$varible[$name]=$val;
  // $_SESSION['form_calss_var'][$name]=$val;
}
static function getVal($name,$val='') {
   $var = !empty(self::$varible[$name]) ? self::$varible[$name] : '';
   // (!empty($_SESSION['form_calss_var'][$name]) ? $_SESSION['form_calss_var'][$name] :false);
  // p($var);
   //echo '====================================<br>';
  // $var =  (!empty($_SESSION['form_calss_var'][$name]) ? $_SESSION['form_calss_var'][$name] :false);
   if (!empty($val) && is_array($var)){
      return (!empty($var[$val]) ? $var[$val] : false);
   }
   return $var;
}


static function getAurl()
{
    return self::$aurl;
}
static function getHtml()
{
    return self::html;
}
static function getLang()
{
    return self::$lang;
}
static function getIdForm()
{
    return self::$id_form;
}
static function getPostAction()
{
    return self::$post_action;
}
static function getPostModule()
{
    return self::$post_module;
}
static function getAForm()
{
    return self::$aform;
}

static function getAparts($name)
{
    return self::$parts[$name];
}
static function getModuleId()
{
    return self::$module_id;
}
static function getModuleTitle()
{
    return self::$module_title;
}
static function getModuleKeywords()
{
    return self::$module_keywords;
}
static function getModuleDescr()
{
    return self::$module_description;
}
static function getMarket()
{
    return self::$maket;
}
static function getSHablon()
{
    return self::$shablon;
}
static function getPartsId()
{
    return self::$parts_id;
}

 }