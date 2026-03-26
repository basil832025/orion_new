<?php
// класс возвращает и обрабатывает для вывода поля формы
class SqlSelect 
{
  protected $module = '';
  protected $id = '';
  protected $fields_sql = ''; // поля дял запроса 
  protected $where = '';
  protected $order = ''; // 
  protected $group = ''; // 
  protected $aParent = array(); // 
  protected $type_module = ''; //
  protected $limit = ''; // 
  protected $table_module = ''; //
  protected $table = ''; // для сложных запросов
  protected $cntElem = 0; // общее количество элементов
  protected $page_items = 3;// Количество элементов на странице
  protected $page_number = 1; // Номер активной страници
  protected $page_count = 0; // Количество страниц
  protected $page_groups = 0;   // Количество групп линков 
  protected $page_link = 5; // Количество линков на одной странице
  protected $sql = '';   // сам конечный запрос
  protected $field = '';   // сам конечный запрос
  protected $BDfield = '';   // сам конечный запрос
  protected $type_field = 'text';   // сам конечный запрос
  protected $lang_type = '';   // сам конечный запрос
  protected $module_sql = '';   // сам конечный запрос
  protected $out_result_field = '';   // сам конечный запрос
  protected $name_field_sql = '';   // сам конечный запрос
  protected $fieldLinksUnion= '';   // связь по первисным и вторичным полям нескольких таблиц
  protected $parent_field= '';
  protected $out_module_id= '';
  protected $out_module_result= '';
  function __construct($page_number=1,$sql='')
    {
    $this->module= SystemClass::getModule();
    $this->id = poste('id');
    $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule(); 
    $this->type_module = ObjectRT::getTypeModule();
    $this->page_link = PAGE_GROUPS;
    $this->page_number = $page_number;
    $this->page_items = $_SESSION[$this->module]['page_items'];
    $this->sql=$sql;
    
    }
 //обработать все типы полей и вернуть Fields
 function workFields($aCols=array())
 {
    $this->fields_sql .= ObjectRT::getTableModuleSynon().'.id';
    
    // Для модуля reiting добавляем match_id и pair_number, если их нет в полях
    if ($this->module == 'reiting' && $this->table_module == T_REITING) {
        $this->fields_sql .= ', '.ObjectRT::getTableModuleSynon().'.match_id';
        $this->fields_sql .= ', '.ObjectRT::getTableModuleSynon().'.pair_number';
    }
    
            if ($aCols) {
                  // пройдемся по заголовкам таблицы
                foreach ($aCols as $val) {
                    $this->type_field = !empty($val['type']) ? strtolower($val['type']) : 'text';
                    
if ($this->type_field=='number' or $this->type_field=='edit' or $this->type_field=='vibor'
or $this->type_field=='delete' or $this->type_field == 'addsub' or $this->type_field == 'sort' or $this->type_field == 'anyaction' or $this->type_field == 'math_oper' or $this->type_field == 'add_players' or $this->type_field == 'expand_collapse' or $this->type_field == 'expand_match') continue;
 // Пропускаем поля, которые не должны попадать в SQL
 if (!empty($val['no_sql'])) continue;
 //s($this->type_field);
                    $this->BDfield = !empty($val['bd_field']) ? $val['bd_field'] :'';
                    $this->field = !empty($val['bd_field']) ? $val['bd_field'] :(!empty($val['name_field']) ? ObjectRT::getTableModuleSynon().'.'.$val['name_field'] : '');
                    $this->lang_type = !empty($val['lang_type']) ? $val['lang_type'] : '';
                    $this->table = !empty($val['table']) ? $val['table'] : '';
                    $this->module_sql = !empty($val['module']) ? $val['module'] : '';
                    $this->out_result_field = !empty($val['out_result_field']) ? $val['out_result_field'] : (!empty($val['name_field']) ? $val['name_field'] : '');
                    $this->parent_field = !empty($val['parent_field']) ? $val['parent_field'] : '';
                    $this->out_module_id = !empty($val['out_module_id']) ? $val['out_module_id'] : '';
                    $this->out_module_result = !empty($val['out_module_result']) ? $val['out_module_result'] : '';
                    //$this->id_value = !empty($val['id_value']) ? $val['id_value'] : '';
                    $this->name_field_sql = !empty($val['name_field_sql']) ? $val['name_field_sql'] : '';
       //             s($this->type_field);
                    if (method_exists($this, 'getField_'.$this->type_field)) {
                        $this->fields_sql .=', ';
                       call_user_func(array($this, 'getField_'. $this->type_field));
                    }
                    else
                    {
                        if (!empty($this->BDfield))   $this->fields_sql .=', ';
                      $this->getField_text();
                    }     

     }
     }
 }
 function getField_image()
 {
     $this->fields_sql .= '(select name from `' . T_FILES . '` f where f.id=' . $this->field .') as ' . $this->field;
 }
 function getField_imagefull()
 {
     $this->fields_sql .= '(select name from `' . T_FILES . '` f where f.id=' . $this->field .') as ' . $this->field;
     $this->fields_sql .= ', (select img_mini from `' . T_FILES . '` f where f.id=' . $this->field .') as ' . $this->field.'_imgmini';   
     $this->fields_sql .= ', (select img_full from `' . T_FILES . '` f where f.id=' . $this->field .') as ' . $this->field.'_imgfull';   
 }
 function getField_text()
 {
 if (!empty($this->lang_type))
    $this->fields_sql .= $this->field .'_'.$this->lang_type.' as ' .$this->out_result_field;
 else
    $this->fields_sql .=$this->field . (!empty($this->BDfield) ? ' as '. $this->out_result_field : ''); 
 }
 function getField_ProstSprEdit()
 {
 if (!empty($this->lang_type))
    $this->fields_sql .= $this->field .'_'.$this->lang_type.' as ' .$this->out_result_field;
 else
    $this->fields_sql .=$this->field . (!empty($this->BDfield) ? ' as '. $this->out_result_field : '');
 }
 function getField_goto_modact()
 {
 //s('anyact');
    $this->fields_sql .=$this->field . (!empty($this->BDfield) ? ' as '. $this->out_result_field : '');
 //s($this->fields_sql);
 }
 function getField_plus_minus()
 {
   $this->fields_sql .=$this->field; 
 }
 function getField_hidden()
 {
     $this->fields_sql .= '';
 }
 
 /* function getField_date()
 {
     $this->fields_sql .= $this->field;
 }*/
 
 function getField_parent()
 {
 if (!empty($this->lang_type))
    $this->fields_sql .= $this->field .'_'.$this->lang_type.' as ' .$this->out_result_field;
 else
    $this->fields_sql .=$this->field; 
 }
 function getField_out_key()
 {
     $dop_id = (!empty($this->out_module_id) ? '(select pp.'.$this->out_module_id.' from `'.$this->table.'` pp where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($this->parent_field) ? $this->parent_field : $this->field) . ') as '.$this->out_module_result.',' : '');

     $this->fields_sql .=
         $dop_id.(!empty($this->field) ?'(select pp.'.(!empty($this->lang_type) ? (!empty($this->out_result_field) ? $this->out_result_field : $this->field) .'_'.$this->lang_type .' as '.(!empty($this->out_result_field) ? $this->out_result_field : $this->field) :  (!empty($this->out_result_field) ? $this->out_result_field : $this->field) )
      .' from `' . (!empty($this->table) ? $this->table : 
      $this->table_module) . '` pp where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($this->parent_field) ? $this->parent_field : $this->field) . ') as ' . $this->field  : '') ;
 }
 function getField_onlybd_out_key()
 {
     $dop_id = (!empty($this->out_module_id) ? '(select pp.'.$this->out_module_id.' from `'.$this->table.'` pp where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($this->parent_field) ? $this->parent_field : $this->field) . ') as '.$this->out_module_result.',' : '');

     $this->fields_sql .=
         $dop_id.(!empty($this->field) ?'(select pp.'.(!empty($this->lang_type) ? (!empty($this->out_result_field) ? $this->out_result_field : $this->field) .'_'.$this->lang_type .' as '.(!empty($this->out_result_field) ? $this->out_result_field : $this->field) :  (!empty($this->out_result_field) ? $this->out_result_field : $this->field) )
      .' from `' . (!empty($this->table) ? $this->table :
      $this->table_module) . '` pp where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($this->parent_field) ? $this->parent_field : $this->field) . ') as ' . $this->field  : '') ;
 }
    function getField_out_key_prostspr()
    {
        $this->fields_sql .=
            (!empty($this->field) ?'(select
             (select value as name'
                .' from `' . T_SPRLIST_VALUES . '` p where p.id=' . (!empty($this->out_result_field) ? $this->out_result_field : $this->field)  . ') as ' . (!empty($this->out_result_field) ? $this->out_result_field : $this->field)  . '_name
                 from `' . (!empty($this->table) ? $this->table :
                    $this->table_module) . '` pp where pp.id=' .ObjectRT::getTableModuleSynon().'.'.(!empty($this->parent_field) ? $this->parent_field : $this->field) . ') as ' . $this->field.'_name'  : '') ;
   //s( $this->fields_sql);
    }
 function getField_ProstSpr()
 { 
    $this->fields_sql .= 
     (!empty($this->field) ? $this->field 
      .', (select value as name' 
      .' from `' . T_SPRLIST_VALUES . '` p where p.id=' . $this->field . ') as ' . $this->out_result_field . '_name' : '') ;
 }
 function getField_onlybd_ProstSpr()
 {
    $this->fields_sql .=
     (!empty($this->field) ? $this->field
      .', (select value as name'
      .' from `' . T_SPRLIST_VALUES . '` p where p.id=' . $this->field . ') as ' . $this->out_result_field . '_name' : '') ;
 }
 function getField_tree()
 {
    if (!empty($this->module_sql) && $this->module_sql=='_all_' && !empty($this->table))
        $this->fields_sql .= '(select '.(!empty($this->out_result_field) ? $this->out_result_field : 'name')
        .' from `' . $this->table . '` f where f.id=' .(!empty($this->name_field_sql) ? 
        $this->name_field_sql : $this->field) . ') as module, '.$this->field;
      else               
      $this->fields_sql .=$this->field;
    ///  s($this->fields_sql );
 }
  
 
 function getSql()
  {
    $this->sql = 'select '.$this->fields_sql
   .' FROM `'.$this->table_module.'` '.ObjectRT::getTableModuleSynon().' ' // синоним таблицы
   .$this->getTableUnions() // если есть таблицы для связи то соединяем
   .'WHERE 1=1 '.($this->where ? '  '.$this->where : '').$this->fieldLinksUnion // связь полей между несколькими таблицами 
   ;
  // wlog('tyt  '.$this->sql);
   }  
  function resultList()
  {
    
   if ($this->type_module == 'tree')
   {
    $this->sql = 'SELECT level, pid, ' . $this->fields_sql . ' FROM `' . $this->table_module . '` ' .ObjectRT::getTableModuleSynon().' ORDER by sort';
    //s($this->sql);
     return   get_tree_level(db_list($this->sql));
   }else
   {
    if ($this->sql=='')  $this->getSql();

    $is_team_league = !empty($_SESSION['reiting']['is_team_league']) ? (int)$_SESSION['reiting']['is_team_league'] : 0;
    if ($this->module == 'reiting' && $this->table_module == T_REITING && $is_team_league) {
        $synon = ObjectRT::getTableModuleSynon();
        $team_filter = ' AND ('.$synon.'.pair_number IS NULL OR '.$synon.'.pair_number=0)';

        $cnt_sql = 'SELECT COUNT(*) as cnt FROM ('.$this->sql.$team_filter.') as cnt_table';
        $this->cntElem = db_field($cnt_sql,'cnt');

        if(intval($this->cntElem/$this->page_items) == $this->cntElem/$this->page_items)
            $this->page_count = $this->cntElem/$this->page_items;
        else 
            $this->page_count = intval($this->cntElem/$this->page_items)+1;

        $this->page_number = $this->page_number<=$this->page_count?$this->page_number:1;
        $this->limit = " LIMIT ".($this->page_number-1)*$this->page_items.",".$this->page_items;

        $teams_sql = $this->sql
            .$team_filter
            .($this->order ? ' ORDER BY '.$this->order : '')
            .($this->group ? ' GROUP BY '.$this->group : '')
            .($this->limit ? ' '.$this->limit : '');
        $teams = db_list($teams_sql);
        if (empty($teams)) {
            return array();
        }

        $team_ids = array();
        $match_ids = array();
        foreach ($teams as $team_row) {
            if (!empty($team_row['id'])) {
                $team_ids[] = (int)$team_row['id'];
            }
            if (!empty($team_row['match_id'])) {
                $match_ids[] = $team_row['match_id'];
            }
        }
        $team_ids = array_values(array_unique($team_ids));
        $match_ids = array_values(array_unique($match_ids));

        $team_ids_sql = !empty($team_ids) ? implode(',', $team_ids) : '0';
        $match_ids_sql = '';
        if (!empty($match_ids)) {
            $match_ids_escaped = array_map(function($val){ return '"'.addslashes($val).'"'; }, $match_ids);
            $match_ids_sql = implode(',', $match_ids_escaped);
        }

        $page_filter = ' AND ('.$synon.'.id IN ('.$team_ids_sql.')';
        if (!empty($match_ids_sql)) {
            $page_filter .= ' OR ('.$synon.'.pair_number > 0 AND '.$synon.'.match_id IN ('.$match_ids_sql.'))';
        }
        $page_filter .= ')';

        $final_sql = $this->sql
            .$page_filter
            .($this->order ? ' ORDER BY '.$this->order : '')
            .($this->group ? ' GROUP BY '.$this->group : '');

        return db_list($final_sql);
    }

    $this->workLimitOrCntElem(); // получаем общее количество элементов и количество страниц вывода
   // s($this->sql);
    $final_sql = $this->sql 
    .($this->order ? ' ORDER BY '.$this->order : '')
    .($this->group ? ' GROUP BY '.$this->group : '')
    .($this->limit ? ' '.$this->limit : '');
    return db_list($final_sql);
   }
  } 
  function resultRow()
  {
    $this->getSql();
    return db_row(
    $this->sql
    .' limit 1'
    );
  }
  function workLimitOrCntElem()
  {
      // Для списка турниров считаем количество через упрощенный COUNT без
      // тяжелых вычисляемых полей из SELECT (ускоряет пагинацию).
      if ($this->module == 'turnirs' && $this->table_module == T_TURNIRS) {
          $sql = 'select count(*) as cnt FROM  `'.$this->table_module.'` '.ObjectRT::getTableModuleSynon().' '
              .$this->getTableUnions()
              .' where 1=1 ' .($this->where ?  '  '.$this->where : '').$this->fieldLinksUnion;
          $this->cntElem = db_field($sql,'cnt');
      } else {
          $sql = "SELECT COUNT(*) as cnt FROM (".$this->sql.") as cnt_table";
          $this->cntElem = db_field($sql,'cnt');
      }
  // s('$this->cntElem='.$this->cntElem);
  /*    
     $this->cntElem = db_field(
   'select count(*) as cnt FROM  
   `'.$this->table_module.'` '.Object::getTableModuleSynon().' ' // синоним таблицы
   .$this->getTableUnions() // если есть таблицы для связи то соединяем
   .' where 1=1 ' .($this->where ?  '  '.$this->where : '').$this->fieldLinksUnion, 'cnt');
   */
    // Рассчитываем количество страниц 
      if(intval($this->cntElem/$this->page_items) == $this->cntElem/$this->page_items)
        $this->page_count = $this->cntElem/$this->page_items;
      else 
        $this->page_count = intval($this->cntElem/$this->page_items)+1;
     
      // Проверяем текущю страницу 
      $this->page_number = $this->page_number<=$this->page_count?$this->page_number:1;
      
      // Строим запрос 
    
      $this->limit = " LIMIT ".($this->page_number-1)*$this->page_items.",".$this->page_items;
   
   }
  function setOrder($field,$sort = 'asc',$sort_default='')
  {
      // Для модуля reiting с командными турнирами используем специальную сортировку
      if ($this->module == 'reiting' && $this->table_module == T_REITING && !empty($sort_default)) {
          // Используем только sort_default для правильной группировки командных игр
          // Игнорируем поле сортировки для командных турниров
          $this->order = $sort_default;
      } else {
          $sort_default=$sort_default ? ','.$sort_default :'';
          if ($field)
              $this->order .= ' '.$field.' '.$sort.$sort_default;
      }
  } 
  function setFields($Fields)
  {
    $this->fields = $Fields;
  }   
  function setWhere($where)
  {
    $this->where = $where;
  //  s($where);
  }
  function setTables($tables)
  {
    $this->table_module = $tables;
  }
  function setGroups($groups)
  {
    $this->group = $groups;
  }
  function getCntElem()     
  {
    return $this->cntElem;
  }
  function getPageNumber()
  {
        return $this->page_number;
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
 function getIdUnionTable($syn) 
 {//b
    $aTable = ObjectRT::getTablePoSynon($syn);
   // s('$aTable===');
   // s($aTable);
       $fieldLinksUnion = ' 1=1 ';
    $table = (!empty($aTable['table'])) ? $aTable['table'] : $this->table_module;
                 foreach ($aTable['aFealdLinks'] as $id =>$doc)  
                $fieldLinksUnion.=' and '.$id.'='.$doc; 
              //  s('select '.$syn.'.id from `'.$this->table_module. '` '.Object::getTableModuleSynon() 
            //    .', `'.$table.'` '.$syn.' where '.$fieldLinksUnion .' and '.Object::getTableModuleSynon().'.id='.$this->id);
              return   $id_table = db_field('select '.$syn.'.id from `'.$this->table_module. '` '.ObjectRT::getTableModuleSynon() 
                .', `'.$table.'` '.$syn.' where '.$fieldLinksUnion .' and '.ObjectRT::getTableModuleSynon().'.id='.$this->id,'id');
       
 }//e
}
