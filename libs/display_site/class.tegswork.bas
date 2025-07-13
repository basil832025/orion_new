<?php
// класс обрабатывает теги служебные
class TegsWork //extends WorkMaketSite
{
 private $html = ''; // текст для вывода обработанные
 private $module = ''; // модуль
 public $module_name = ''; // модуль
 public $module_table = ''; // модуль
 public $bd_string = ''; // модуль
 public $aData = array(); // модуль
 public $aImgs = array();
 public $workURL =''; // если 1 то обрабатывать не по id  а по url запросы напрмер для каталога товаро пример grp_23.html или /kuhni/
public   $page= 0;  // Номер акивной страницы
 
 public function __construct($module='-1') // конструктор
 {
    
    $this->aurl = GlobalData::getAurl();
    $this->module_id = GlobalData::getModuleId();
    $this->module_title = GlobalData::getModuleTitle();
    $this->module_keywords = GlobalData::getModuleKeywords();
    $this->module_description = GlobalData::getModuleDescr();
    $this->maket = GlobalData::getMarket();
    $this->shablon = GlobalData::getSHablon();
    $this->parts_id = GlobalData::getPartsId();
    $this->lang=GlobalData::getLang();
    if ($module<>'-1'){ 
        $oModuleFields = new FieldsModule($module);    
        $oModuleFields->getModule($module);
        $oModuleFields->getFormMaster();
        $oModuleFields->getFormFealds();
        $this->bd_string = $oModuleFields->getBdString();
        $this->module_name = $oModuleFields->getModule_name();
        $this->module_table = $oModuleFields->getModule_table();
        $this->workURL = $oModuleFields->getWorkURL();
        $this->aImgs = $oModuleFields->getAimgs();
    }
   // $this->tegs_work(); // запускаем обработку тегов
   // s($this->aurl);
 } 
 function tegs_work($html='') {
    
 $this->html = $html;
   $tegs['content'] = array(0);
   //$tegs['body'] = array(0); // полдключать этот файл если нет шаблона тега, и игнорировать все спец теги
   //  $tegs['shablon'] = array(0); // использовать ли спец теги, без этого тега будем все теги игнорировать спец.
   $tegs['no_shablon'] = array(0); // не использовать спец. теги
   $tegs['block'] = array(0); // модули подключение от даной ссылки БЛОКИ НЕ ДОЛЖНЫ ПЕРЕСЕКАТЬСЯ!!!
   $tegs['files'] = array(0); // изображения множество
   $tegs['menu'] = array(0);
   // $tegs['form_list'] = array(2); //  подключение внешних форм параметры module и action тоесть форма храниться в файле, например form_....html
   $tegs['ifempty'] = array(0); // условие, проверки на существование в одном блоке 1 условие
   $tegs['data_list'] = array(0); // цикл
   $tegs['if'] = array(0); // условие, есть еще подтег внутри = <else> в теге параметры is="условие "
   
   $tegs['html_load'] = array(0); // загрузить для обработки другой файл
   $tegs['##'] = array(0); // переменная в вібраном модуле
   $tegs['#_#'] = array(0); // переменная в вібраном модуле htmlspecialchars()
   $tegs['@@'] = array(0); // переменная сохраненная в массиве переменніх
   $tegs['const'] = array(0); // константі определенніе в php
   $tegs['title'] = array(0); // заглаваие для сайта
   $tegs['description'] = array(0); // описание для сайтов
   $tegs['keywords'] = array(0); // ключевые слова
   $tegs['page_html'] = array(0); // страницы 1,2 3,...
   $tegs['php'] = array(0); // страницы 1,2 3,...
   
  /* if (!empty($_SESSION['test'])){
   if ($_SESSION['test']=='elem_mcat'){
    s($this->aData);
    s($this->html);
   }
   }*/
   foreach ($tegs as $key_teg => $value_teg) {
        $this->key_teg = $key_teg;
        if ($key_teg=='##') $key_teg = 'var';
        if ($key_teg=='#_#') $key_teg = 'varHCh';
        if ($key_teg=='@@') $key_teg = 'var_';
         if (method_exists($this, 'get'.$key_teg)) {
                call_user_func(array($this, 'get'.$key_teg));
            }
    }
} 
function getContent()
{
    $oTegContent = new TegContent($this->html);
    $oTegContent->setModule_name($this->module_name);
    $oTegContent->setModule_table($this->module_table);
    $oTegContent->setWorkURL($this->workURL);
    $oTegContent->workTeg();
    $this->html = $oTegContent->getHtml();
} 
function getBlock()
{
    $oTegBlock = new TegBlock($this->html);
     $this->html = $oTegBlock->getHtml(); 
}

function getMenu()
{
    $oTegMenu = new TegMenu($this->html);
     $this->html = $oTegMenu->getHtml(); 
}
function getIf()
{
      $oIfElse = new TegIfElse($this->html,$this->aData);
      $this->html =  $oIfElse->getHtml();
}

function getConst()
{
                if (preg_match_all('#<\s*'.$this->key_teg.'\s*>(.*?)<\s*/'.$this->key_teg.'\s*>#i',$this->html, $aContens)){ //p($aContens);
               if (!empty($aContens)) {
                  foreach ($aContens[1] as $key => $val) {
                     $text='';
                            $val=trim($val);
                     if (!empty($val) && defined($val)){
                        $text=constant($val);
                     }
                         $this->html = preg_replace('#<\s*'.$this->key_teg.'\s*>(.*?)<\s*/'.$this->key_teg.'\s*>#i', $text, $this->html,1);
                  }
               }

            }
}
function getVar()
{
            if (preg_match_all('|##(.*?)##|',$this->html, $aContens)){
               if (!empty($aContens)) {
                  foreach ($aContens[1] as $key => $val) {
                     $text='';
                      $val=trim($val);
                      
                     // echo $val.'='.$this->aData[$val].'<br>';
                     if (!empty($val) && !empty($this->aData[$val])){
                         $text=$this->aData[$val];
                     }
                     $this->html = preg_replace('|##(.*?)##|', $text, $this->html,1);
                  }
               }
          } 
}
function getvarHCh()
{
       if (preg_match_all('|#_#(.*?)#_#|',$this->html, $aContens)){
               if (!empty($aContens)) {
                  foreach ($aContens[1] as $key => $val) {
                     $text='';
                            $val=trim($val);
                     if (!empty($val) && !empty($this->aData[$val])){
                        $text=$this->aData[$val];
                     }
                     $this->html = preg_replace('|#_#(.*?)#_#|', htmlspecialchars($text), $this->html,1);
                  }
               }

}
}
function getvar_()
{
       if (preg_match_all('|@@(.*?)(?:\[(.*?)\])?@@|',$this->html, $aContens)){ //p($aContens);
               if (!empty($aContens[1])) {
                  foreach ($aContens[1] as $key => $val) {
                     $txt='';
                     $val=trim($val);
                     // перемеенной присвоинно значение
                     if (strpos($val,'=')){
                        $avar = explode('=',$val);
                        $txt=GlobalData::setVal($avar[0],$avar[1]);
                     }else{
                      if (!empty($aContens[2][$key])) {
                           $txt=GlobalData::getVal($val,trim($aContens[2][$key]));
                       }else{
                       $txt=GlobalData::getVal($val);
                  }    
                     }  
                      $this->html = preg_replace('|@@(.*?)@@|', $txt, $this->html,1);
                //   echo "#####\n\n";
                  }
               }

            }

}

function getdata_list()
{
      if (preg_match('#<\s*'.$this->key_teg.'(\s+[^>]*)?>(.*)<\s*/'.$this->key_teg.'\s*>#is',$this->html, $aContens)){
        //s($aContens);
               $text = $key='';
               //   p($aContens);
               // цикл обработка повторяещего кода с обработкой тегов
               $parametrs_teg = (!empty($aContens[1]) ? $aContens[1] : '');
               $parametrs_teg=preg_replace('#\s*([a-z_]+)=["\']?([\w\d_]+)["\']?\s*#i','$' ."\\1='\\2'; ",$parametrs_teg);
               eval($parametrs_teg);
               $loop = isset($loop) ? $loop :'';
               $value = isset($value) ? $value :'';
               $key = isset($key) ? $key :'';

        $_SESSION['test']=$value;
        
               eval('
                if (!empty($this->aData)){
               foreach ($this->aData as '.($key ? '$'.$key.' => ' : '').' $'.$value.') {
                        $oData = new TegsWork();
                        $oData->aData=$'.$value.';
                        GlobalData::setVal("'.$value.'",$'.$value.');
                           $oData->tegs_work($aContens[2]);
                        $text.= $oData->getHtml();
                }}');

               //echo $text;
               $this->html = preg_replace('#<\s*'.$this->key_teg.'[^>]*>(.*)<\s*/\s*'.$this->key_teg.'\s*>#is', $text, $this->html,1);
              
            }
       
}

function gethtml_load()
{
      if (preg_match_all('#<\s*'.$this->key_teg.'(\s+[^>]*)?>(.*?)<\s*/'.$this->key_teg.'\s*>#is',$this->html, $aContens)){// p($aContens);
               if (!empty($aContens)) {
                  foreach ($aContens[2] as $key => $val) {
                     $text=$module= $file='';
                     $parametrs_teg = (!empty($aContens[1][$key]) ? $aContens[1][$key] : '');                //   $parametrs_teg=preg_replace('#\s*([a-z_]+)=["\']?([\w\d_]+)["\']?\s*#i','$' ."\\1='\\2'; ",$parametrs_teg); 
                         $parametrs_teg=preg_replace('#\s*([a-z_]+)=["\']?([^"\']*)["\']?\s*#is','$' ."\\1='\\2';",$parametrs_teg);
                     eval($parametrs_teg);
                    if( file_exists(ROOT.URL_SITE.'module_html/'.$module.'/'.$file.'.html')){
               $sContent = file_get_contents(ROOT.URL_SITE.'module_html/'.$module.'/'.$file.'.html');
                $oContent = new TegsWork(); 
                    
                $oContent->tegs_work($sContent);
                $text=$oContent->getHtml(); 
                    }
                     $this->html = preg_replace('#<\s*'.$this->key_teg.'(\s+[^>]*)?>(.*?)<\s*/'.$this->key_teg.'\s*>#is', $text, $this->html,1);
                  }
               }

            }
}
function getPage_html()
{
     $this->html = preg_replace('#<\s*'.$this->key_teg.'[^>]*>(.*?)<\s*/\s*'.$this->key_teg.'\s*>#is', GlobalData::getVal('page_html'), $this->html,1);
        
}
function gettitle()
{
      if (preg_match('#<\s*'.$this->key_teg.'\s*>(.*?)<\s*/'.$this->key_teg.'\s*>#i',$this->html, $aContens)){// p($aContens);
               if (!empty($aContens)) {
                         $text='';
                         $title = (GlobalData::getVal('title') ? GlobalData::getVal('title') : (GlobalData::getModuleTitle() ? GlobalData::getModuleTitle() : GlobalData::getAparts('name')));
                  $this->html = preg_replace('#<\s*'.$this->key_teg.'\s*>(.*?)<\s*/'.$this->key_teg.'\s*>#i', '<title>'.$title.'</title>', $this->html,1);
               
               }

            }
    
}
function getdescription()
{
      if (preg_match('#<meta.*?name="description".*?>#i',$this->html, $aContens)){// p($aContens);
               if (!empty($aContens)) {
                         $text='';
                         $description = (GlobalData::getVal('description') ? GlobalData::getVal('description') : (GlobalData::getModuleDescr() ? GlobalData::getModuleDescr() : ''));
                  $this->html = preg_replace('#<meta.*?name="description".*?>#i', '<meta content="'.$description.'" name="description">', $this->html,1);
               
               }

            }
}
function getkeywords()
{
    
            if (preg_match('#<meta.*?name="keywords".*?>#i',$this->html, $aContens)){// p($aContens);
               if (!empty($aContens)) {
                         $text='';
                         $keywords = (GlobalData::getVal('keywords') ? GlobalData::getVal('keywords') : (GlobalData::getModuleKeywords() ? GlobalData::getModuleKeywords() : ''));
                  $this->html = preg_replace('#<meta.*?name="keywords".*?>#i', '<meta content="'.$keywords.'" name="keywords">', $this->html,1);
               
               }

            }
}
function getPhp()
{
       $text ='';
           if (preg_match('#<\s*'.$this->key_teg.'\s*[^>]*?>(.*)<\s*/'.$this->key_teg.'\s*>#is',$this->html, $aContens)){
                 
                 if (!empty($aContens[1])) 
                  eval($aContens[1]);
               }
   $this->html = preg_replace('#<\s*'.$this->key_teg.'[^>]*>(.*)<\s*/\s*'.$this->key_teg.'\s*>#is', $text, $this->html,1);
          
}
function getFiles()
{
     if (preg_match('#<\s*'.$this->key_teg.'(\s+[^>]*)?>(.*)<\s*/'.$this->key_teg.'\s*>#is',$this->html, $aContens)){
      $parametrs_teg = (!empty($aContens[1]) ? $aContens[1] : '');
            // echo  $parametrs_teg=preg_replace('#\s*([a-z_]+)=["]?([^"]*)["]?\s*#is','$' ."\\1=\"\\2\";",$parametrs_teg);
               //p(GlobalData::$varible);
       $parametrs_teg=preg_replace('#\s*([a-z_]+)=["]?([^"]*)["]?\s*#is','$' ."\\1=\"\\2\";",$parametrs_teg);
                     // echo    $parametrs_teg;
               eval($parametrs_teg);
               $is = isset($is) ? $is :'';
               $valueFile = isset($valuefile) ? $valuefile :'';        
               if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$is,$aIs)){
              foreach ($aIs[1] as $key => $val) {
$text=''; 
                  if (!empty($aIs[2][$key])) {$txt= 'GlobalData::getVal("'.trim($val).'","'.trim($aIs[2][$key]).'")';   }
                  else{
                  $txt= 'GlobalData::getVal("'.trim($value).'")';
                  }
                  
  
                  $parametrs_teg=preg_replace('/#.*?#/is',$txt,$is,1);
                  $parametrs_teg=preg_replace('#\s*([a-z_]+)=(.*)\s*#is','$' .'\\1=\\2; ',$parametrs_teg);
                 }
                      
             }  
               eval($parametrs_teg);
                  $id_elem = isset($id_elem) ? $id_elem :'0';
               $key = isset($key) ? $key :'elem';
             //  $value = isset($value) ? $value :'elemFiles';
               if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$id_elem,$awhere)){
         foreach ($awhere[1] as $k => $v) {
$text='';
                  if (!empty($awhere[2][$k])) {
                    $txt= GlobalData::getVal($v,$awhere[2][$k]) ? GlobalData::getVal($v,$awhere[2][$k]) : 0;  
                     }else{
                  $txt= GlobalData::getVal($v) ? GlobalData::getVal($v) : 0;
                    }
                  $id_elem=preg_replace('/#.*?#/is',$txt,$id_elem,1);
                }
                
             }
               
     $sql = 'select  id,id_elem,name,img_mini,img_full from '.T_FILES.' where id_elem='.$id_elem.' and module="'.$this->module_name.'"';
     $aFiles = db_list($sql);
            foreach($aFiles as $field =>$vData){
    $aFiles[$field]['img_mini']  = (!empty($vData['img_mini']) && file_exists(DIR_IMAGES .$vData['img_mini'])) 
     ? DIR_IMAGES_ .$vData['img_mini'] : ((!empty($vData['name']) && file_exists(DIR_FILES_SITE_MINI .
        $vData['name'])) ? URL_FILES_SITE_MINI . $vData['name'] : '');
                                    
   $aFiles[$field]['img_full'] = (!empty($vData['img_full']) && file_exists(DIR_IMAGES .$vData['img_full'])) 
    ? DIR_IMAGES_ .$vData['img_full'] : ((!empty($vData['name']) && file_exists(DIR_FILES_SITE .
             $vData['name'])) ? URL_FILES_SITE . $vData['name'] : '');
     }  
    // p($aFiles);
     $text='';
        eval('
                if (!empty($aFiles)){
               foreach ($aFiles as $'.$valueFile.') {
                        $oFiles = new TegsWork();
                        $oFiles->aData=$'.$valueFile.';
                        GlobalData::setVal("'.$valueFile.'",$'.$valueFile.');
                        $oFiles->tegs_work($aContens[2]);
                        $text.= $oFiles->getHtml();
                }}');
     $this->html = preg_replace('#<\s*'.$this->key_teg.'[^>]*>(.*)<\s*/\s*'.$this->key_teg.'\s*>#is', $text, $this->html,1);
        
     }
}
function getHtml()
{
    return $this->html;
}
function getModule_table()
{
    return $this->module_table;
}
}

?>