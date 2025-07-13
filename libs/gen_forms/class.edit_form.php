<?php
// класс возвращает и обрабатывает для вывода поля формы
class FormEdit extends ActionModule
{
  //public $content = ''; // вывод полей накапливается в эту переменную
//protected $module = ''; // массив данных запроса
 private $sql='';
 protected $thVdata = array(); // массив настроек для одного поля
 protected $postButton=''; 
protected $fieldLinksUnion= '';   // связь по первисным и вторичным полям нескольких таблиц

 
 public function __construct() // конструктор
  {
    $this->aEditField = ObjectRT::getAEditField(); 
    //s($this->aEditField);
    $this->subMenu=ObjectRT::getSubmenuEdit();
    $this->module= SystemClass::getModule();
    $this->id = poste('id');
    $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule(); 
    $this->nameZ=ObjectRT::getNameZ();
    $this->nameZEdit=ObjectRT::getNameZEdit();
    $this->type_module = ObjectRT::getTypeModule();
    $this->aSpecField = ObjectRT::getASpecField();
    $this->aTabs = ObjectRT::getATabs();
   // $this->aData = $aData;
  
  } 
  function form_show()
  {//$this->aParent,
       // s($_SESSION);
        $this->aEditField = array_merge($this->aSpecField, $this->aEditField);
        $this->sql_edit();
        $this->shablon_edit_header();
        $this->data_edit();
        $this->Java_script = implode(';', $this->Java_script);
       // $this->content .= '</tbody></table>';
        if (empty($this->subMenu)){
            $this->subMenu = array(
                'back' => array(
                    'module' => $this->module,
                    'action' => 'list',
                    'class' => 'ajax_back',
                    'post' => $this->postButton),
                'save' => array(
                    'module' => $this->module,
                    'action' => 'edit_ok',
                    'post' => $this->postButton),
                );
                }
  } 
    function sql_edit()
    {
       // global $language;
        $this->sql = '';
        $field_sql = ($this->type_module == 'tree' ? 'pid, level, ' : '');
         foreach ($this->aEditField as $v) {
            $type_f = !empty($v['type']) ? strtolower($v['type']) : 'text';
          //  $field_name = (!empty($v['out_result_field']) ? $v['out_result_field'] : $v['name_field']);
            //$BDfield = !empty($val['bd_field']) ? $val['bd_field'] :'';
                    $field = !empty($v['bd_field']) ? $v['bd_field'] :(!empty($v['name_field']) ? ObjectRT::getTableModuleSynon().'.'.$v['name_field'] : '');
                    $lang_type = !empty($v['lang_type']) ? $v['lang_type'] : '';
                    $table = !empty($v['table']) ? $v['table'] : '';
                   // $module_sql = !empty($val['module']) ? $val['module'] : '';
                    $out_result_field = !empty($v['out_result_field']) ? $v['out_result_field'] : (!empty($v['name_field']) ? $v['name_field'] : '');
             
            
          //  s($v['name_field'].'=name   type='.$type_f);
            switch ($type_f) {
                case 'date':
                       
                    $field_sql .= (!empty($field) ? 'DATE_FORMAT(' . $field .
                        ', "%d.%m.%Y") AS ' . $out_result_field : '') . ', ';
                       
                    break;
                case 'out_key':
                     $field_sql .= 
     (!empty($field) ?$field.',(select t.'.(!empty($lang_type) ? $out_result_field .'_'.$lang_type .' as '.$out_result_field : $out_result_field )
      .' FROM `' . (!empty($table) ? $table :  $this->table_module) 
      . '` t where t.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($v['parent_field']) ? $v['parent_field'] : 
      $field) . ') as ' . $v['name_field']   . '_name' : '').', ' ;
    //  s($field_sql);
                break;
                case 'out_key_prostspr':
                    $field_sql .= (!empty($field) ? $field.', ' : '');
                  /*  $field_sql .=
                        (!empty($field) ? '(select t.'. $out_result_field
                            .' FROM `' . (!empty($table) ? $table :  $this->table_module)
                            . '` t where t.id=' .ObjectRT::getTableModuleSynon().'.'
                            .(!empty($v['parent_field']) ? $v['parent_field'] :
                                $field) . ') as ' . $v['name_field'] : '').', ' ;*/
                  //    s('$field_sql='.$field_sql);
                    break;
                case 'radiooutkey':
                case 'textoutkey':
                     $field_sql .=
     (!empty($field) ?$field.',(select t.'.(!empty($lang_type) ? $out_result_field .'_'.$lang_type .' as '.$out_result_field : $out_result_field )
      .' FROM `' . (!empty($table) ? $table :  $this->table_module) 
      . '` t where t.id=' .(!empty($v['parent_field']) ? ObjectRT::getTableModuleSynon().'.'.$v['parent_field'] : 
      ObjectRT::getTableModuleSynon().'.'.$field) . ') as ' . $v['name_field']    : '').', ' ;
//s($field_sql);
                break;  
                      case 'checkboxout':
                     $field_sql .= 
     (!empty($field) ?$field.',(select t.'. $out_result_field 
      .' FROM `' . (!empty($table) ? $table :  $this->table_module) 
      . '` t where t.id=' .(!empty($v['parent_field']) ? $v['parent_field'] : 
      $field) . ') as ' . $v['name_field']    : '').', ' ;
//s($field_sql);
                break;     
                 
                case 'parent':
                            
                    $field_sql .= (!empty($field) ? $field .
                        ', (select t.'.(!empty($lang_type) ? 'name_'.$lang_type .' as name' : 'name').' FROM `' . (!empty($table) ? $table : $this->
                        table_module) . '` t where t.id=' . $field . ') as ' . $out_result_field .
                        '_name' : '') . ', ';
                    break;
                case 'prostspr':
                    // если это не стандартный справочник то указываем с какой таблицы берем
                    $field_sql .= (!empty($field) ? $field .', ' : '');
                            /*. ',
              (select t.value'  . ' as name FROM `' .
                        T_SPRLIST_VALUES . '` t where t.id=' . $field. ') as ' . $out_result_field .'_name' : '') . ', ';
                   */ break;
            case 'tab':
            break;

            case 'out_keynosql':
            case 'textnosql':
            $field_sql .='';
            break;
                default:
                $field_sql .= (!empty($field) ? (!empty($lang_type) ? $field.'_'.$lang_type.' as ' .$out_result_field :$field)  : '') . ', ';
            
            }
        }
        $this->sql .= 'select ' . $field_sql . ObjectRT::getTableModuleSynon().' .id from `' . $this->table_module .
        '` '.ObjectRT::getTableModuleSynon().' ' // синоним таблицы
   .$this->getTableUnions() // если есть таблицы для связи то соединяем
          //  '` m where  '.(!empty($this->id_aParent) && !empty($this->name_aParent) ? $this->name_aParent.'='.$this->id_aParent :   ' m.id=' . $this->id) ;
            .'  where   '.ObjectRT::getTableModuleSynon().'.id=' . $this->id .$this->fieldLinksUnion;
      //   s('sqqqql='.$this->sql);
        $this->aData = db_row($this->sql);
       //s($this->aData);
        //s($_POST);


      
        //if (!empty($this->id_aParent))
                  //  $this->aData[$name_aParent] = $this->id_aParent;
        //s($this->aData);
    }
    function data_edit($aData = array())
    {
        // 23.11.2020 ******************* ADD
        $this->aData = (!empty($aData) ? $aData : $this->aData);
        // 23.11.2020 *******************END ADD
        if ($this->aEditField) {
            
            
          if (!empty($this->aParent))
          { //s('$this->postButton='.$this->postButton);
            foreach ($this->aParent as $key =>$vParent)
            {
          
                    $this->id_aParent = $this->getPostReturnId($key);
                    $this->name_aParent = $this->getNameAperent($key);
                //    s('$this->id_aParent='.$this->id_aParent);
                //    s('$this->name_aParent.='. $this->name_aParent);
                    if (!empty($this->name_aParent)) {
                            if (!empty($this->id_aParent)) {
                            $this->postButton .= '&' . $this->name_aParent . '=' . $this->id_aParent;
                        } elseif ($this->name_aParent && !empty($this->aData[$this->name_aParent])) {
                            $this->postButton .= '&' . $this->name_aParent . '=' . $this->aData[$this->name_aParent];
                        }
                     //   s($this->postButton);
                    }
           }    
            }
            $page_id = poste('page_id');
            $this->postButton .= !empty($page_id) ? '&page_id='.$page_id : '';
            //
               if (empty($this->submenu)){
             $this->submenu = array(
                'back' => array(
                    'module' => $this->module,
                    'action' => 'list',
                    'class' => 'ajax_back',
                    'post' => $this->postButton),
                'save' => array(
                    'module' => $this->module,
                    'action' => 'edit_ok',
                    'post' => $this->postButton),
                );
                $this->setSubMenu($this->submenu);
            }
             $objEdirFieald = new formField($this->aEditField,$this->aData,$this->module,$this->id);
            //$objEdirFieald->init();
           $this->content.= $objEdirFieald->genFormFields();  
           $this->Java_script = $objEdirFieald->getJavaScript();
         //  s($this->Java_script);
   
        }
    }
     function shablon_edit_header()
    {
         SystemClass::setZaglModule('<div align="center" class="zagl">' . $this->nameZ . $this->
            nameZEdit . '</div>');
 $this->content ='<form id="form_edit_form" action="?" method="post" enctype="multipart/form-data"  class="g-3 needs-validation was-validated" novalidate="">
<input type="hidden" name="id" value="' . $this->id . '"/>
' .($this->
            type_module == 'tree' ? '
 <input type="hidden" name="pid" value="' . (!empty($this->aData['pid']) ? $this->
            aData['pid'] : 0) . '" />
 <input type="hidden" name="level" value="' . (!empty($this->aData['level']) ? $this->aData['level'] : 0) . '" />
       ' : '') . '  
 <table width="100%" cellpadding="0" cellspacing="0"  class="table table-light mob_edit_table table-bordered  border-info-subtle  table-striped f14"><tbody><div>
    ';
    if (!empty($this->aTabs)){
$this->content .= '<div id="navbar">    <ul>';
$fir=1;
foreach($this->aTabs as $k=>$v)
{
//<span class="notification">34</span>
 $this->content .= '<li class="'.($fir==1 ? 'active' :'inactive').'"><a title="'.$v['name'].'">'.$v['name'].'</a></li>';    
$fir=0;
    }
       $this->content .=  '</ul></div>';
    }
}
function setSubMenu($subMenu){
     $this->subMenu=$subMenu;
}   
 function getTableUnions()
  {
    $tabls = '';
    $aTablUnions = ObjectRT::getTablesUnion();
    if (!empty($aTablUnions)) 
    {
        foreach ($aTablUnions as $TabUn) 
        {
            
            if ($TabUn['table'] && !empty($TabUn['aFealdLinks']))
            {
               $tabls.=', `'.$TabUn['table'].'` '.$TabUn['synon'].' ';
              foreach ($TabUn['aFealdLinks'] as $id =>$doc)  
                $this->fieldLinksUnion.=' and '.$id.'='.$doc; 
            }
        }
    }

    return $tabls;
  }
  }
   
  ?>