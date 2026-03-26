<?php
// класс делает запрос типа query: insert, update, delete...
class SqlQuery 
{
  protected $module = '';
  protected $id = '';
  protected $fields_sql = ''; // поля дял запроса 
  protected $where = '';
  protected $aParent = array(); // 
  protected $type_module = ''; //
  protected $table_module = ''; //
  protected $table = ''; // для сложных запросов
  protected $sql = '';   // сам конечный запрос
  protected $field = '';   // сам конечный запрос
  protected $field_sql = '';   // сам конечный запрос
  protected $aFields = array();   //массив полей где ключ выступает синоним таблицы, полезно когда форма приходит с разными полями с вязаная с многоими таблицами
  protected $type_field = 'text';   // сам конечный запрос
  protected $lang_type = '';   // сам конечный запрос
  protected $module_sql = '';   // сам конечный запрос
  protected $name_field_sql = '';   // сам конечный запрос
  protected $fieldLinksUnion= '';   // связь по первисным и вторичным полям нескольких таблиц
  protected $resource = false;   // ресурс
  function __construct($table='')
    {
    $this->module= SystemClass::getModule();
    $this->id = poste('id');
    $this->aParent = ObjectRT::getAParent(); 
   // s($this->aParent);
    $this->table_module= $table ? $table : ObjectRT::getTableModule(); 
    $this->type_module = ObjectRT::getTypeModule();
      
    }

 //добавляем в массив поля для дальнейшего использования
 function addField($syn,$field,$value='')
 {//b
    $syn = $syn ? $syn : 'p';
    $this->aFields[$syn][$field] = $value;
 }//e
 function getFieldSqlSET($aSet)
 {
    $field_sql = '';
    $first=1;
    foreach ($aSet as $field =>$val)
    {
       if ($first==1) $first=0; else $field_sql .= ',';
       $field_sql .= $field .'="'.$val.'"'; 
    }
  //  s($field_sql);
    return $field_sql;
 }
 function update()
 { //begin
 //s($_POST);
    if (!empty($this->aFields)){
   // s('$this->aFields');
   //s($this->aFields);
    foreach ($this->aFields as $syn => $aSet)
    { 
      $aTable = ObjectRT::getTablePoSynon($syn);
      $table = $aTable['table']; 
     // s('$aTable=');
    //  s($aTable);
    //  s('$this->table_module='.$this->table_module);
        if ($table != $this->table_module) { // если есть сложная связь между несколькими таблицами, то найдем родительскую таблицу
                // ищем id связующей таблицы
              $oSelect = new SqlSelect();
              $id_table =  $oSelect->getIdUnionTable($syn);
             
            //  s('$id_table='.$id_table);
        }else $id_table = $this->id;
     $this->field_sql = $this->getFieldSqlSET($aSet);
   //  s(' $this->field_sql='. $this->field_sql);
     //s(' $id_table='. $id_table);
    // s('update `'.$table.'`   SET '.$this->field_sql.' where '.($this->where ? $this->where : ' id='.$id_table));
     if (empty($id_table)) { // если нет id значит это вставка и нужно добавить
        //  s('tyt11$table='.$table); 
        $this->insert_T();   
      //  s('yyyy');
        $id_table=$this->getInsertId();
         $_SESSION['last_insert_id'] =$id_table;
        ///  s('yyyy1111');
   //     s('$id_table='.$id_table);
        $aTable = ObjectRT::getTablePoSynon($syn);
        // сдесь конечно нужно усложнять логику, если много поле связуещих, но сдесь пока единственный параметр
      if (!empty($aTable['aFealdLinks'])){
        foreach ($aTable['aFealdLinks'] as $id =>$doc) 
         { // пример связуещее поле hd.acc нам нужен acc
           $aId= explode(".", $id); 
           $id_csv = $aId[1].'='. $id_table; // acc = значение которое вернулось от пред табицы
        //   s('$id_csv='.$id_csv);
         }
         }
        // добавим в основную таблицу новое значения для связуеще родительской таблицы по  полям
         if (!empty($this->id))
        db_query('update `'.$this->table_module.'`  SET '.$id_csv.' where  id='.$this->id);       
     }else // иначе изминяем значения
   // s('update `'.$table.'` SET '.$this->field_sql.' where '.($this->where ? $this->where : ' id='.$id_table));
      $this->resource = db_query('update `'.$table.'` SET '.$this->field_sql.' where '.($this->where ? $this->where : ' id='.$id_table));
    }
    }else
      $this->resource = db_query('update `'.$this->table_module.'` 
        SET '.$this->field_sql.' where '.($this->where ? $this->where : ' id='.$this->id));
 
 }//end
  function insert()
 { //begin
 //s('111 tyt  ');
     $this->resource = db_query('insert into `'.$this->table_module.'` 
        SET '.$this->field_sql);
 }//end
 
   function insert_T($table='',$field_sql='') //перегруженная функциЯ
 { //begin
 $table = (!empty($table) ? $table : $this->table_module);
 $field_sql = (!empty($field_sql) ? $field_sql : $this->field_sql);
// s('222 tyt  ');
//s('insert into `'.$table.'`   SET '.$field_sql);
if (!empty($field_sql))   $this->resource = db_query('insert into `'.$table.'`   SET '.$field_sql);
 }//end
   function delete()
 { //begin
     $this->resource = db_query('delete from `'.$this->table_module.'` 
        where '.($this->where ? $this->where : ' id='.$this->id));
 }//end
 function getInsertId()
 { // global $dsn;
   return db_insert_id();
 }
 function getError()
 {
    return $this->resource ? false : true ;
 }

  function setFields($Fields)
  {
    $this->field_sql = $Fields;
  }   
  function setWhere($where)
  {
    $this->where = $where;
  }
  function setTables($tables)
  {
    $this->table_module = $tables;
  }

}
