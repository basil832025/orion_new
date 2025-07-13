<?php
// класс обрабатывает теги служебные
class FieldsModule //extends WorkMaketSite
{
 private $html = ''; // текст для вывода обработанные
 private $bd_string= ''; // текст для вывода обработанные
 private $module= ''; // текст для вывода обработанные
 private $module_table= ''; // текст для вывода обработанные
 private $module_name= ''; // текст для вывода обработанные
 private $aModul= array(); // текст для вывода обработанные
 private $form_master= array(); // текст для вывода обработанные
 private $module_id= 0; // текст для вывода обработанные
 private $workURL= 0; // текст для вывода обработанные
 private $aImgs= array(); // массив изображений тип 14


 public function __construct($module_='') // конструктор
 {
    $this->module = $module_;
    $this->module_id = GlobalData::getModuleId();
    $this->lang=GlobalData::getLang();
 }

// получаем инфу по данном модуле, если параметр пуст, то по id модуля ищем
function getModule() {
   $sql='select *, name_'.$this->lang.' as name from `'.T_MODULES.'` where '.($this->module  ? 'mname="'.$this->module .'"' : 'id='.$this->module_id).' limit 1';
  // s($sql);
   $this->aModul=db_row($sql);
    $this->module_name = $this->aModul['mname'];
   $this->module_type_view = $this->aModul['type_view'];
   $this->workURL =!empty($this->aModul['workURL']) ? $this->aModul['workURL'] :''; 
   switch ($this->aModul['lang_type']) {
      case 1:
         $postfix='_lz_'.$this->lang;
         break;
      case 2:
         $postfix='_clz';
         break;
      case 3:
         $postfix='_s';
         break;
      case 4:
         $postfix='_lbz';
         break;
   }
   $this->module_table = PREF. $this->aModul['table_name'].$postfix;

}
//получаем инфу по данной форме если парметр пуст, то выбирается главная форма модуля
function getFormMaster($form_teg='') {
   $sql='select * from `'.T_FORMMASTER.'` where module='.$this->aModul['id'].' and '.($form_teg ? 'form_teg="'.$form_teg.'"' : 'main_form=1');
   $this->form_master=db_row($sql);

}
// получчаем массиы полей данной формы
function getFormFealds() {
if (!empty($this->form_master['id'])){
    $sql='select * from `'.T_FORMFIELDS.'` where form_id='.$this->form_master['id'];
   $this->aFields=db_list($sql);
   if (!empty($this->aFields)){
   $cnt=count($this->aFields)-1;
   if ($this->aModul['type']==2) $this->bd_string .= 'level,sort,pid,';
   foreach ($this->aFields  as $key => $value) {
  // $bd_name=trim($value['name_bd']);  
   $bd_name='';  
      $bd_name=$bd_name? $value['name_bd'] : $value['name_field'] ; 
   switch ($value['type']) {
      case 1:
      if ($this->aModul['lang_type']==2){
            $this->bd_string .=$bd_name .'_'.$this->lang.' as ' .$bd_name.',';
     }else{
           $this->bd_string .=$bd_name.',';
     }
       break;
      case 12:
      if ($this->aModul['lang_type']==2){
            $this->bd_string .=$bd_name .'_'.$this->lang.' as ' .$bd_name.',';
     }else{
           $this->bd_string .=$bd_name.',';
     }
       break;
        case 8:
            $this->bd_string .='DATE_FORMAT('.$bd_name.', "%d.%m.%Y") AS '.$bd_name.','; 
        break;
    
      case 9:
            $this->bd_string .='(select sf.value_'.$this->lang.' FROM `'.T_SPRLIST_VALUES.'` sf where t.'.($bd_name).'=sf.id) as ' .$bd_name.'_name, '.$bd_name.',' ;
        break;
      case 14:
            $this->aImgs[$bd_name]=1;
            $this->bd_string .='(select f.name FROM `'.T_FILES.'` f where t.'.($bd_name).'=f.id) as ' .$bd_name.',' ;
            $this->bd_string .='(select f.img_mini FROM `'.T_FILES.'` f where t.'.($bd_name).'=f.id) as ' .$bd_name.'_imgmini,' ;
            $this->bd_string .='(select f.img_full FROM `'.T_FILES.'` f where t.'.($bd_name).'=f.id) as ' .$bd_name.'_imgfull,' ;

        break;
      case 20:
           $this->parts_field=$bd_name;
        break;
   default:
       $this->bd_string .=$bd_name.',';
   }
    
   }
   $this->bd_string .='id,active';
   }
}else{
   $this->bd_string = '*';
}

} 
 function getModule_name()
 {
   return $this->module_name ;
 }
 function getModule_table()
 {
   return $this->module_table;
 }
  function getBdString()
 {
   return $this->bd_string;
 }
 function getWorkURL()
 {
    return $this->workURL;
 }
 function getAimgs()
 {
    return $this->aImgs;
 } 
 }