<?php
// класс обрабатывает теги служебные
class TegBlock //extends WorkMaketSite
{
 private $html = ''; // текст для вывода обработанные
 public $aData = array(); // модуль
 private $aImgs = array();

 public function __construct($html='') // конструктор
 {
    $this->html = $html;
    $this->workTeg();
 }
function workTeg()
{ 
    $_SESSION['test']='';
      if (preg_match_all('#<\s*block(\s+[^>]*)?>(.*?)<\s*/block\s*>#is',$this->html, $aContens)){
               
                if (!empty($aContens)) {
                       foreach ($aContens[2] as $key => $value) {
                     $text_html=$where=$teg=$type=$sort=$action=$title=$title_where='';
                     $parametrs_teg = (!empty($aContens[1][$key]) ? $aContens[1][$key] : '');          
                    $parametrs_teg=preg_replace('#\s*([a-z_]+)=["]?([^"]*)["]?\s*#is','$' ."\\1=\"\\2\";",$parametrs_teg);
                     eval($parametrs_teg);
                     $type =  (!empty($type))? $type : 'list';
                     $module=  (!empty($module))? $module : '';
                     $teg=  (!empty($teg))? $teg : ''; 
                     $limit=  (!empty($limit))? $limit : ''; 
                     $ispages=  (!empty($ispages))? $ispages : ''; 
                     if (!empty($module)){   
                         $oBlock = new TegsWork($module);
                         $this->aImgs = $oBlock->aImgs;   

            if (!empty($action) && file_exists(ROOT.URL_SITE.'module_html/'.$module.'/action/'.$action.'.bas')){
               include ROOT.URL_SITE.'module_html/'.$module.'/action/'.$action.'.bas';
    
            }else{ 
     // пример #aurl_id[1]#=id в скобках какой id будет по счету в адрессной строке                
                   if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$where,$awhere)){
//p($awhere[1]);     
//s(GlobalData::$varible);
              foreach ($awhere[1] as $k => $v) {
$text='';
                  if (!empty($awhere[2][$k])) {
                    $txt= GlobalData::getVal($v,$awhere[2][$k]) ? GlobalData::getVal($v,$awhere[2][$k]) : 0;  
                     }else{
                  $txt= GlobalData::getVal($v) ? GlobalData::getVal($v) : 0;
                    }
                  $where=preg_replace('/#.*?#/is',$txt,$where,1);
                }
                
             }
            // p($_SESSION['form_calss_var']);
                 $where = $where ? $where .' and ' : '';    
                 //p($_SESSION);
                              //делаем запрос для данной таблицы
                        if ($teg){
                           $sql = 'select '.($oBlock->bd_string ? $oBlock->bd_string : '*').' from `'.$oBlock->module_table.'` t where '.$where.' active=1 and teg="'.$teg.'"'.(!empty($limit) ? ' limit '.$limit : '');
                        }else{
                           $sql = 'select  '.($oBlock->bd_string ? $oBlock->bd_string : '*').' from `'.$oBlock->module_table.'` t where '.$where.' active=1 '.(!empty($sort) ? ' order by '.$sort :'') .((!empty($limit) && empty($ispages)) ? ' limit '.$limit : '');
                        //echo $sql.'<br><br>'; 
                          }
                         if ($type=='row'){
                           $this->aData = db_row($sql);
                         //  echo $sql."<br>";
                         }else{
                            if (!empty($ispages) and PAGE_ID>0){
                                $this->aData = db_list($sql,PAGE_ID);
                                GlobalData::setVal('page_html',(!empty($_SESSION['pagging_html'])?$_SESSION['pagging_html']:''));
                                //    echo $_SESSION['pagging_html'];
                                }
                             else  
                                $this->aData = db_list($sql);
                        }
                        $sql='';
//обработка изображенией путей                        
                  $this->workImg($type);      
                 $oBlock->aData = $this->aData;   
                // p($oBlock->aData) ;
// обработка title ======================================                        
                     if (!empty($title) && $type=='row'){
                         GlobalData::setVal('title', (!empty($oBlock->aData['title']) ? $oBlock->aData['title'] : (!empty($oBlock->aData[$title]) ? $oBlock->aData[$title] : '')));
                     }elseif (!empty($title) && !empty($title_where)){
                                        // обработка переменных в выражении title where
      if (preg_match_all('/#(.*?)(?:\[(.*?)\])?#/is',$title_where,$awhere)){
         
              foreach ($awhere[1] as $k => $v) {
$text='';
                  if (!empty($awhere[2][$k])) {
                    $txt= GlobalData::getVal($v,$awhere[2][$k]) ? GlobalData::getVal($v,$awhere[2][$k]) : 0;   }else{
                  $txt= GlobalData::getVal($v) ? GlobalData::getVal($v) : 0;
                  }
                  $title_where=preg_replace('/#.*?#/is',$txt,$title_where,1);
                }
              }
    $sql = 'select '.($oBlock->bd_string ? $oBlock->bd_string : '*').' from `'.$oBlock->module_table.'` t where '.$title_where.' and active=1 limit 1';
    $title_data=db_row($sql);
               GlobalData::setVal('title', (!empty($title_data['title']) ? $title_data['title'] : (!empty($title_data[$title]) ? $title_data[$title] : '')));          
              GlobalData::setVal('keywords', (!empty($title_data['keywords']) ? $title_data['keywords'] : ''));          
               GlobalData::setVal('description', (!empty($title_data['description']) ? $title_data['description'] : ''));          
                        
                     } 
// КОНЕЦ обработка title ======================================                               
                  }
                  // пока для теста, но можно оставить и использовать в чтении переменной
                  $_SESSION['form_calss_var']['module']=$module;
                 // p($value);
                  GlobalData::setVal('aData',$oBlock->aData);
                  $oBlock->tegs_work($value);
                  $text_html= $oBlock->getHtml();
                   

                     
            }
                     ///p($aData);
                     $this->html = preg_replace('#<\s*block[^>]*>(.*?)<\s*/\s*block\s*>#is', $text_html, $this->html,1);
                
        
                  }
               }

            }
 }
 function workImg($type)
 {
 if ($type=='row'){
         foreach($this->aImgs as $field =>$v){
    $this->aData[$field.'_imgmini']  = (!empty($vData[$field.'_imgmini']) && file_exists(DIR_IMAGES .$vData[$field.'_imgmini'])) 
     ? DIR_IMAGES_ .$vData[$field.'_imgmini'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE_MINI .
        $vData[$field])) ? URL_FILES_SITE_MINI . $vData[$field] : '');
                                    
   $this->aData[$field.'_imgfull'] = (!empty($vData[$field.'_imgfull']) && file_exists(DIR_IMAGES .$vData[$field.'_imgfull'])) 
    ? DIR_IMAGES_ .$vData[$field.'_imgfull'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE .
             $vData[$field])) ? URL_FILES_SITE . $vData[$field] : '');
     }      
 } else {
   foreach ($this->aData as $k =>$vData)
   {
    foreach($this->aImgs as $field =>$v){
    $this->aData[$k][$field.'_imgmini']  = (!empty($vData[$field.'_imgmini']) && file_exists(DIR_IMAGES .$vData[$field.'_imgmini'])) 
     ? DIR_IMAGES_ .$vData[$field.'_imgmini'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE_MINI .
        $vData[$field])) ? URL_FILES_SITE_MINI . $vData[$field] : '');
                                    
   $this->aData[$k][$field.'_imgfull'] = (!empty($vData[$field.'_imgfull']) && file_exists(DIR_IMAGES .$vData[$field.'_imgfull'])) 
    ? DIR_IMAGES_ .$vData[$field.'_imgfull'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE .
             $vData[$field])) ? URL_FILES_SITE . $vData[$field] : '');
     }                          
   } 
 }
 }
 
 
 function getHtml()
 {
    return $this->html;
 }
 }