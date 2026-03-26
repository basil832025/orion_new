<?php
// класс возвращает и обрабатывает для вывода поля формы
class FormAdd extends FormEdit
{
  //public $content = ''; // вывод полей накапливается в эту переменную
//protected $module = ''; // массив данных запроса
 private $sql='';
 protected $thVdata = array(); // массив настроек для одного поля
 protected $postButton=''; 


 
 public function __construct() // конструктор
  {
    $this->aEditField = ObjectRT::getAEditField(); 
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
 //  s('add_const');
  } 
  function addForm()
  {  // s('addForm');
        $this->isAdd = 1;
        $sql = '';
        $this->id = !empty($this->id) &&  trim($this->id)<>'' ? $this->id :'0';
        //
        if ( $this->type_module == 'tree') {
             $sort = db_field('SELECT sort FROM `' . $this->table_module . '` WHERE pid=' . $this->id . ' ORDER by sort desc LIMIT 1', 'sort');
            $sort_new = db_field('select  sort_new from `' . $this->table_module .
                '` where id = ' . $this->id, 'sort_new');

            if (!empty($sort)) {
                $sort++;
            } else {
                $sort = 1;
            }
            $level = poste('level');
            $level = !empty($level) ? $level + 1 : 1;
            $sql = 'level=' . ($level) . ', sort="' . $sort . '",pid=' . $this->id;

            // пренадлежность даного раздела
            $field_sql = '';
            $field_sql .= 'name as pid_name, ';


            $this->aData = db_row('SELECT ' . (!empty($field_sql) ? $field_sql . ' m.id' :
                'm.id') . ' FROM `' . $this->table_module . '` m WHERE m.id=' . $this->id);
            $this->aData['pid'] = $this->id;
        }
     
        foreach ($this->aEditField as $field => $v) {
            //$this->postButton .=(!empty($vData['butt_post']) && !empty($vData['name_field']) ? '&'.$vData['name_field'].'='.$this->aData[$vData['name_field']] : '');
            $type_f = !empty($v['type']) ? strtolower($v['type']) : 'text';
            if (isset($v['def_val'])){
                  $sql .= ($sql != '' ? ',' : '') . $v['bd_field_short_name'].'="'.$v['def_val'].'"';
                $this->aData[$field] = $v['def_val'];
        
              }  
            if ($v['name_field'] == 'date_create') {
                $sql .= ($sql != '' ? ',' : '') . 'date_create=now()';
            }
            if ($v['name_field'] == 'active') {
                $sql .= ($sql != '' ? ',' : '') . 'active=0';
                $this->aData['active'] = 0;
            }

            if ($type_f == 'parent') {
                //   $sql .=(!empty($v['name_field']) ? $v['name_field'].', (select p.name FROM `'.(!empty($v['table'])? $v['table'] : $this->table_module).'` p where p.id=m.'.$v['name_field'].') as '.$v['name_field'].'_name': '').', ';

                //  $sql.=($sql!='' ? ',':'').$v['name_field'].'=0';
                // $this->aData['active']=0;
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
            $lang = $this->getNameALang($key);
            if ($this->getNameATable($key))
                $this->name_list_parent = db_field('select '.($lang ? 'name_'.$lang .' as name' : 'name').'  from `' . $this->getNameATable($key) .
                                    '` where id=' . $this->id_aParent, 'name');
                                    
                $sql .=  (!empty($sql)? ',' :'') . $this->name_aParent . '=' . $this->id_aParent;
                $this->aData[$this->name_aParent] = $this->id_aParent;        
        }  
        
        if (!empty($this->name_aParent) && !empty($this->id_aParent))
        {
           $this->postButton .= '&' . $this->name_aParent . '=' . $this->id_aParent;
        }
        }
        }
        $id='';
  /*      if (!empty($sql)) {
            $sql = "INSERT INTO `" . $this->table_module . "`  SET " . $sql;
            db_query($sql);
            $id = db_insert_id();
        }
        if (!empty($id) and $this->type_module == 'tree') 
        {
            $sort_new = (!empty($this->id) ?  $sort_new . ',' :''). $id;
           db_query('update `' . $this->table_module . '`  SET sort_new="'.$sort_new.'" where id='.$id);
        }*/
        $this->id = $id;
       // s($this->id);
      //  s($sql);
      
       
    // 23.11.2020 *****************  в тригере на befor можно ставить значение по умолчанию 
      $aDataADD =  !empty($_SESSION['BEFOR_ADD']) ? $_SESSION['BEFOR_ADD'] : array();
     
      unset($_SESSION['BEFOR_ADD']);
        $this->shablon_edit_header();
        $this->data_edit($aDataADD);
        $this->Java_script = implode(';', $this->Java_script);
        $this->content .= '</table>';
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
                $this->setSubMenu($this->subMenu);
            }
  }
  }
  ?>
