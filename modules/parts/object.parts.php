<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class PartsObject extends Object 
{   
  //$this-> = 'tree'; 
  function init ()
  {
 //   s('tytyt');
   // parent::init();
  //  self::$type_module = 'tree';
    // объявляем перечень колонок таблицы бд, которые будут участвовать в запроссах
    $this->addFieldBD('name');
    $this->addFieldBD('date_create','date');
    $this->addFieldBD('date_last_modif','date');
    $this->addFieldBD('url');
    $this->addFieldBD('pid','parent');
    $this->addFieldBD('link','parent');
    $this->addFieldBD('comment');
    $this->addFieldBD('title');
    $this->addFieldBD('keywords');
    $this->addFieldBD('Description');
    $this->addFieldBD('h1');
    $this->addFieldBD('img','img');
    $this->addFieldBD('active','checkbox');
    $this->addFieldBD('notice');
    $this->addFieldBD('text');
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20')); 
$this->addFTL(array('name'=>'Редакти-<br />ровать','type'=>'edit','width'=>'40')); 
$this->addFTL(array('name'=>'Название',
                  'type'=>'tree',
                  'name_field'=>'name',
                  'module'=>'_all_',
                  'table'=>T_MODULES,
                  'name_field_child'=>'parts_id',
                  'name_field_sql'=>'parts_modules_id',
                  'out_result_field'=>'mname'
                  ));
$this->addFTL(array('name'=>'Модуль','width'=>'100', 'type'=>'out_key',
                 'name_field'=>'parts_modules_id','table'=>T_MODULES,'out_resutl_field'=>'id'));
$this->addFTL(array('name'=>'Добавить<br />подразд.','type'=>'addsub', 'width'=>'60')); 
$this->addFTL(array('name'=>'Сорти<br />ровка','type'=>'sort','name_field'=>'pid','width'=>'40')); 
$this->addFTL(array('name'=>'Удалить','type'=>'delete','width'=>'40')); 
$this->addFTL(array('name'=>'Актив-<br />ность','type'=>'plus_minus','width'=>'60','name_field'=>'active')); 
$this->addFTL(array('name'=>'Старто-<br />вая','type'=>'plus_minus','width'=>'60','name_field'=>'is_home'));
//================================================================================================
// описание полей формы модуля при редактировании или добавления
// описание полей формы модуля при редактировании или добавления
 $this->addFF(array('name'=>'Название ','name_field'=>'name','required'=>'Название  объязательно'));
 $this->addFF(array('name'=>'Пренадлежит разделу','name_field'=>'pid','type'=>'parent','sort'=>'1',
                    'mess'=>'Выбирете группу '));
 $this->addFF(array(  'name'=>'Пренадлежит модулю',
                    'name_field'=>'parts_modules_id',
                    'type'=>'parent',
                    'table'=>T_MODULES,
                    'lang_type'=>LANG,
                    'mess'=>'Выбирете модуль'));
 $this->addFF(array(  'name'=>'Название URL','name_field'=>'url','required'=>'Название URL объязательно'));
                    
 $this->addFF(array(  'name'=>'Коментарий к разделу',
                    'name_field'=>'comment',
                    'type'=>'redaktor')); 
 $this->addFF(array('name'=>'Title страницы','name_field'=>'title'));
 $this->addFF(array('name'=>'Keywords', 'name_field'=>'keywords'));
 $this->addFF(array('name'=>'Description','name_field'=>'description'));
 $this->addFF(array('name'=>'Отоброжать на сайте','name_field'=>'active','type'=>'checkbox'));
 
  $this->setTableModule(T_PARTS);
  $this->setTypeModule('tree');
  
  self::$nameZ='Модуль: Структура сайта::';   
 self::$nameZList='Меню сайта';   
 self::$nameZEdit='Редактирование раздела'; 
 self::$submenu_list =array( 
    'back' => array('module' => 'parts', 'action' => 'parts_list'),
    );

  }  
}
?>