<?php
// класс возвращает и обрабатывает для вывода поля формы
class FormSave extends ActionModule
{
  //public $content = ''; // вывод полей накапливается в эту переменную
//protected $module = ''; // массив данных запроса
 private $sql='';
 //private $aParent=array();
 protected $form = array(); // массив элементов формы
 protected $postButton=''; 


 
 public function __construct() // конструктор
  {
   // echo 'tyt';exit();
    $this->aEditField = ObjectRT::getAEditField(); 
    $this->module= SystemClass::getModule();
    $this->form= SystemClass::getAFormPost();
    $this->id = poste('id');
   $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule(); 
  //   $this->type_module = Object::getTypeModule();
    $this->aSpecField = ObjectRT::getASpecField();
   // $this->aData = $aData;

  } 
  
  public function Save()
  {global $aModulesSettings;
  //$cnt_elem = count($this->aEditField);
  $oQeury = new SqlQuery();
  //  s('$this->form');
//    s($this->form);
  $this->aEditField = array_merge($this->aSpecField, $this->aEditField);
  foreach ($this->aEditField as $fieldName => $v) {
    //s($v);
     $type_f = !empty($v['type']) ? strtolower($v['type']) : 'text';
    switch ($type_f) {
    case 'checkbox':
        $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],(!empty($this->form[$fieldName]) ?  1 : 0));
    break;
    case 'date':
        if (empty($v['readonly'])) {
        $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],date_for_sql_format($this->form[$fieldName]));
               
        }
    break;
                    
    case 'parent':
        if (!empty($v['sort'])) {
        // проверяем не поменялся ли раздел родитель
        $Alast_ = (isset($fieldName) ? db_row('select ' . $fieldName .
                            ', sort_new,level from `' . $this->table_module . '` where id=' . $this->id) : -1);
        $sort_elem = $Alast_['sort_new'];
        $level_elem = $Alast_['level'];
        if ($Alast_ != -1 && !empty($this->form[$fieldName]) && $Alast_[$fieldName] != $this->form[$fieldName])
        //если отличаються и есть новый родитель выолним серию изминений
        {
        $s_pid = (!empty($this->form[$fieldName]) ? $this->form[$fieldName] : '0');
        // узеаем последний sort детей  нового родителя 
        $sort = db_field('SELECT sort FROM `' . $this->table_module . '` WHERE ' . $fieldName .
                                '=' . $s_pid . ' ORDER by sort desc LIMIT 1', 'sort');
        if (!empty($sort))  $sort++; else $sort = 1;
        $old_parent_sort_new = '';
        $len_old_p_sort_new=1;
        if ($level_elem>1)  // если это не верхний уровень и есть родитель, то узнаем его нумерацию старого родителя 
        {
         $old_parent_sort_new = db_field('select sort_new from `'. $this->table_module .'` where id='.$Alast_[$fieldName] ,'sort_new');
         $len_old_p_sort_new = strlen($old_parent_sort_new)+2;
                                                                                    
        }
        //узнаем уровень и sort_new нового родителя если это не первый уровень Корень
        if ($this->form[$fieldName]!=0) 
        { 
        $Asort_new = db_row('select  sort_new,level from `' . $this->table_module .
                                '` where id = ' . $this->form[$fieldName]);
        $sort_new = $Asort_new['sort_new'];
        $level_parent  =$Asort_new['level']-$level_elem+1;
        } 
        else
        {
        $sort_new = '';
        $level_parent = 1-$level_elem;  
        }     
        //увеличваем levels для всех потомков и самого елемента на +1
        db_query('update `' . $this->table_module .
                                '`set level='.$level_parent.'+level,
                                sort_new=CONCAT("' . ($sort_new ? $sort_new.',':'') .'",
                                SUBSTRING(sort_new, '.($len_old_p_sort_new).',LENGTH(sort_new)))  where SUBSTRING(sort_new,1,LENGTH("' . $sort_elem . '"))="' . $sort_elem .
                                '"');
        $oQeury->addField($v['bd_field_syn'],'sort',$sort);
      
        }
        }
         $oQeury->addField($v['bd_field_syn'],$v['bd_field'],(isset($this->form[$fieldName]) ?  $this->form[$fieldName] : poste($fieldName)));
      
      break;
      case  'pass':

        $pass = (!empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName));
        if (!empty($pass) && !empty($v['shifr']))
        {
        switch ($v['shifr']){
        case 'md5':
        $pass = md5($pass);
        break;
        case 'md5_2':
        $pass = md5(md5($pass));
        break;
        }
        }
        $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],$pass);
        
        break;
        case  'out_key_prostspr':
        case  'prostspr':
          $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],(!empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName)));
        break;
         case  'out_key':
        // s($fieldName);
       //  s($v);
      //  $out_result_field=$v['out_result_field'];
         // $oQeury->addField($v['bd_field_syn'], $v['bd_field_short_name'],(!empty($this->form[$out_result_field]) ?  $this->form[$out_result_field] :( !empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName))));
          $oQeury->addField($v['bd_field_syn'], $v['bd_field_short_name'],( !empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName)));
        break;
        case  'radiobox':
        case  'text':
          $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],(!empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName)));
         break;
        case  'hidden': 
          $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],(!empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName)));
         break;
         }
        }
             if (!empty($this->aParent))
          { //s('$this->postButton='.$this->postButton);
            foreach ($this->aParent as $key =>$vParent)
            {  
         // получаем название родительского раздела
        $this->id_aParent = $this->getPostReturnId($key);
        $this->name_aParent = $this->getNameAperent($key);
        if (!empty($this->id_aParent)) {
           /* $lang = $this->getNameALang($key);
            if ($this->getNameATable($key))
                $this->name_list_parent = db_field('select '.($lang ? 'name_'.$lang .' as name' : 'name').'  from `' . $this->getNameATable($key) .
                                    '` where id=' . $this->id_aParent, 'name');
                */  
                 $oQeury->addField('',$this->name_aParent,$this->id_aParent);
                        
                  //  $this->aData[$this->name_aParent] = $this->id_aParent;        
        }  
        
        if (!empty($this->name_aParent) && !empty($this->id_aParent))
        {
           $this->postButton .= '&' . $this->name_aParent . '=' . $this->id_aParent;
        }
        }
        }
      $grp_module = !empty($aModulesSettings[$this->module]['path']) ? $aModulesSettings[$this->module]['path'] : '';    // запуск модуля

      // если есть более сложная обработка для сохранения в БД при сохранение, то подхгружаем данній тригер
        if (file_exists('modules/'.(!empty($grp_module) ? $grp_module :$this->module) .'/sql/save.php'))
            include_once 'modules/'.(!empty($grp_module) ? $grp_module :$this->module) .'/sql/save.php';
        else 
        { 
          //  $id = poste('id');
         //   if (!empty($id))
            $oQeury->update();
        //    else
        //    $oQeury->insert();
           
            }
//s('2222');
//s(SystemClass::getIsAjax());
if (SystemClass::getIsAjax()!=2) {
   // s('form_save_AJAX='.SystemClass::getIsAjax());
    $this->list_show();
}
        
  }
  }
  ?>