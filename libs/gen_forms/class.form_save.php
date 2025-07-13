<?php
// класс возвращает и обрабатывает для вывода поля формы
class FormSave extends ActionModule
{
  //public $content = ''; // вывод полей накапливается в эту переменную
//protected $module = ''; // массив данных запроса
 private $sql='';
 //private $aParent=array();
 protected $form = []; // массив элементов формы
 protected $files = []; // массив элементов формы
 protected $postButton='';


 
 public function __construct() // конструктор
  {
   // echo 'tyt';exit();
    $this->aEditField = ObjectRT::getAEditField(); 
    $this->module= SystemClass::getModule();
    $this->form= SystemClass::getAFormPost();
    $this->files = isset($_FILES) ? $_FILES : [];
  //  s($this->form);
   // s($_FILES);
    $this->id = poste('id');
   $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule(); 
  //   $this->type_module = Object::getTypeModule();
    $this->aSpecField = ObjectRT::getASpecField();
   // $this->aData = $aData;

  } 
  
  public function SaveIMAGE($fieldName)
  {
   //   s('$fieldName====' . $fieldName);
    //  s($this->files[$fieldName]);
     // s($_POST);
     // s($_POST[$fieldName]);
      $form_img_option = poste($fieldName) ? poste($fieldName) : [];
      $type_view = $form_img_option['type_view_'];
     //   S('$form_img_option');
    //    S($form_img_option);
      $max_w = ($form_img_option['max_w']) ? $form_img_option['max_w'] : 800;
      $max_h = ($form_img_option['max_h']) ? $form_img_option['max_h'] : 800;
      $file_size = $this->files[$fieldName]['size']; //размер файла
      $file_type = $this->files[$fieldName]['type']; //тип файла

      $options_uploads_class = array(
          'upload_dir' => DIR_FILES_SITE,
          'upload_url' => URL_FILES_SITE,
          'thumbnails_dir' => DIR_FILES_SITE_SMALL,
          'thumbnails_url' => URL_FILES_SITE_SMALL,
          'mini_dir' => DIR_FILES_SITE_MINI,
          'mini_url' => URL_FILES_SITE_MINI,
          'thumbnail_max_width' => 100,
          'thumbnail_max_height' => 100,
          'mini_max_width' => $max_w,
          'mini_max_height' => $max_h,
          'field_name' => 'file',
          'name_field' => $fieldName,
          'type_view' => $type_view,
          'id_elem' => $this->id,
          'module' => $this->module,
          'file_size' => $file_size,
          'file_type' => $file_type,
          'name_file' => $this->files[$fieldName]['name'], // оригинальное имя файла,
          'file_data' => $this->files[$fieldName]['tmp_name'], // оригинальное имя файла,


      );
// объект класса загрузки файлов
      $upload_handler = new UploadHandler($options_uploads_class);
      $upload_handler->set_result = 0;
//s($_SERVER);s($_POST);exit;
      switch ($_SERVER['REQUEST_METHOD']) {

          case 'POST':
// log_write($GLOBALS);
//s($_POST);
              if (!empty($_REQUEST['delete'])) {
                  $upload_handler->delete();
            //      exit;
              }
              $upload_handler->post();
          //    exit;
              break;
          default:
              header('HTTP/1.0 405 Method Not Allowed');

      }
  }
  public function Save()
  { global  $aModulesSettings;
  //$cnt_elem = count($this->aEditField);
  $oQeury = new SqlQuery();
  //  s('$this->form');
 //  s($this->form);
  $this->aEditField = array_merge($this->aSpecField, $this->aEditField);
  foreach ($this->aEditField as $fieldName => $v) {
   // s($v);
     $type_f = !empty($v['type']) ? strtolower($v['type']) : 'text';
    switch ($type_f) {
    case 'img':
        if (!empty($this->files)){
            $this->SaveIMAGE($fieldName);
        }

        break;
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
            $field = (!empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName));
            if (!empty($field))
          $oQeury->addField($v['bd_field_syn'],$v['bd_field_short_name'],$field);
        break;
         case  'out_key':
        // s($fieldName);
       //  s($v);
      //  $out_result_field=$v['out_result_field'];
         // $oQeury->addField($v['bd_field_syn'], $v['bd_field_short_name'],(!empty($this->form[$out_result_field]) ?  $this->form[$out_result_field] :( !empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName))));
          $oQeury->addField($v['bd_field_syn'], $v['bd_field_short_name'],( !empty($this->form[$fieldName]) ? $this->form[$fieldName] : poste($fieldName)));
        break;
        case  'radiobox':
        case  'prostspredit':
        case  'text':
           if (empty($v['readonly']))
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
//
//SystemClass::getIsAjax());
if (SystemClass::getIsAjax()!=2) {
   // s('form_save_AJAX='.SystemClass::getIsAjax());
    $this->list_show();
}
        
  }
  }
  ?>