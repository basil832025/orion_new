<?php
// класс обрабатывает теги служебные
class TegMenu //extends WorkMaketSite
{
 private $html = ''; // текст для вывода обработанные
 public $aData = array(); // модуль

 public function __construct($html='') // конструктор
 {
    $this->html = $html;
    $this->workTeg();
 }
function workTeg()
{ 
$_SESSION['test']='';
     if (preg_match_all('#<\s*menu(\s+[^>]*)?>(.*?)<\s*/menu\s*>#is',$this->html, $aContens)){
              
               if (!empty($aContens)) {
                  foreach ($aContens[2] as $key => $value_menu) {
                     $text_html= $where ='';
                     $parametrs_teg = (!empty($aContens[1][$key]) ? $aContens[1][$key] : '');   
                     $parametrs_teg=preg_replace('#\s*([a-z_]+)=["]?([^"]*)["]?\s*#is','$' ."\\1=\"\\2\";",$parametrs_teg);
                    //echo '===================<br>'; 
                     $max_level=10;
                     $level=1;
                     $module='parts';
                     eval($parametrs_teg);
               if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$where,$awhere)){
//p($awhere[1]);     
              foreach ($awhere[1] as $k => $v) {
$text='';
                  if (!empty($awhere[2][$k])) {
                    $txt= GlobalData::getVal($v,$awhere[2][$k]) ? GlobalData::getVal($v,$awhere[2][$k]) : 0;   }else{
                  $txt= GlobalData::getVal($v) ? GlobalData::getVal($v) : 0;
                    }
                  $where=preg_replace('/#.*?#/is',$txt,$where,1);
                }
                
             }
            // p($_SESSION['form_calss_var']);
                 $where = $where ? $where .' and ' : '';   
                     if (!empty($type) && file_exists(ROOT.URL_SITE.'module_html/'.$module.'/'.$type.'.html')){
                        $sql ='';
                        $text_html = file_get_contents(ROOT.URL_SITE.'module_html/'.$module.'/'.$type.'.html');
                        // если модуль сирукиура, то нужно добавить еще одно условие
                        if ($module=='parts' ){
                           $where = $where.'parts_type=1 and ';
                           
                        }
                        
                         $oMenu = new TegsWork($module);
                          // вернем массив меню
                     //   echo 'SELECT * FROM `' .$oMenu->module_table .'`  where '.$where.' active=1 and level<='.$max_level.' ORDER by sort';
                          $oMenu->aData=get_tree_level(db_list('SELECT * FROM `' .$oMenu->module_table .'`  where '.$where.' active=1 and level<='.$max_level.' ORDER by sort'),$level, ($level>1 ? $this->parts_id : 0));
                           
                        if ($level>1) {  GlobalData::setVal($type,$oMenu->aData ); 
                        }
                         $oMenu->tegs_work($text_html);
                         $text_html= $oMenu->getHtml();
                         //s($text_html);
                         GlobalData::setVal('aData',$oMenu->aData); 
                        //$oMenu->setVal('aData',$oMenu->aData);
                      }
                    $this->html = preg_replace('#<\s*menu[^>]*>(.*?)<\s*/\s*menu\s*>#is', $text_html, $this->html,1);
                  
                  }
               }

            } 
 }
 function getHtml()
 {
    return $this->html;
 }
 }