<?php
// класс обрабатывает теги служебные
class TegContent 
{
 private $html = ''; // текст для вывода обработанные
 private $module_name = ''; // модуль
 private $shablon = ''; // модуль
 private $workURL = ''; // модуль
 private $module_table = ''; // модуль
 private $aurl = array(); // модуль

 public function __construct($html='') // конструктор
 {
    $this->html = $html;
    $this->aurl = GlobalData::getAurl();
    $this->shablon = GlobalData::getSHablon();
 }
function workTeg()
{ 
    $sContent='';
   // echo '<br>'.LAST_URL;
$_SESSION['pagging_html']='';
if (preg_match('#<\s*content(\s+[^>]*)?>(.*)<\s*/content\s*>#is',$this->html, $aContens)){ 
            //  поиск по действию если есть то выполняем, не стандартное выполнение кода
  if (!empty($this->aurl[2][CNT_ELEM-1]) && file_exists(ROOT.URL_SITE.'module_html/'.$this->module_name.'/action/'.$this->aurl[2][CNT_ELEM-1].'.bas')){
     ob_start(); 
      include ROOT.URL_SITE.'module_html/'.$this->module_name.'/action/'.$this->aurl[2][CNT_ELEM-1].'.bas';  
      $sContent = ob_get_contents();
      ob_clean();
            }
            //
            //exit;
        $isUrlObr=1;
       // echo $this->aurl[2][CNT_ELEM-1];
           // провряем есть ли файл ссовпадением до подчеркивания например grp_23.html grp.html это наш файл который обрабтываем
            if (!empty($this->aurl[2][CNT_ELEM-1]) && file_exists(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->aurl[2][CNT_ELEM-1].'.html')){
               $sContent = file_get_contents(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->aurl[2][CNT_ELEM-1].'.html');
              $oContent = new TegsWork();
              $oContent->tegs_work($sContent);
              $sContent= $oContent->getHtml();
 
              //$oContent->parts_id =$this->parts_id; 
              //$oContent->varible=$this->varible;    
            }else{
              //echo 'aa';
            // echo  $this->workURL.'*************';
                // если нужно проверяять по урл (по настройке модуля)) то проверям по полю url в таблице 
             if ($this->workURL) 
             {
                   
               $sql='select id from `'.$this->module_table.'` where url="'.LAST_URL.'"';
               $id_modl=db_field($sql,'id');
                    GlobalData::setVal('aurl_id',(!empty($id_modl) ?$id_modl : 0)); 
                //$id_modl>0 && 
                 if (file_exists(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->workURL.'.html')){
               $sContent = file_get_contents(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->workURL.'.html');
              $oContent = new TegsWork();
              $oContent->tegs_work($sContent);
              $sContent= $oContent->getHtml();
               $isUrlObr=0;  
            }     
             } 
            if ($isUrlObr){    
            if (file_exists(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->shablon.'.html')){
                $sContent='';
               // для не стандартных обработок выполняем действие, а потом обычный шаблон
            if (file_exists(ROOT.URL_SITE.'module_html/'.$this->module_name.'/action/'.$this->shablon.'.bas')){
                include ROOT.URL_SITE.'module_html/'.$this->module_name.'/action/'.$this->shablon.'.bas';
            }
            // если после действия шаблон пустой, то выолняем стандартный шаблон
              if ($sContent=='' )
               $sContent = file_get_contents(ROOT.URL_SITE.'module_html/'.$this->module_name.'/'.$this->shablon.'.html');
              $oContent = new TegsWork();
              $oContent->tegs_work($sContent);
              $sContent= $oContent->getHtml();
        
            }
            }
            } 
            $this->html = preg_replace('#<\s*content[^>]*>(.*?)<\s*/\s*content\s*>#is', $sContent, $this->html);
          }  
 }
 function getHtml()
 {
    return $this->html;
 }
 function setWorkURL($workURL='')
 {
    $this->workURL = $workURL;
 }
 function setModule_name($module_name='')
 {
    $this->module_name = $module_name;
 }
 function setModule_table($module_table='')
 {
    $this->module_table = $module_table;
 }
 
 }
 