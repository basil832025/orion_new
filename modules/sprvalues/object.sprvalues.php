<?php
class sprvaluesObject extends ObjectRT
{
  //$this-> = 'tree'; 
  function init ()
  {
self::$aParent[0]= array('name_field'=>'id_spis',
                  'table'=>T_SPRLIST,
                  'type'=>'hidden',
                  );
      $id_spis = poste('id_spis');
     // $this->postButton= !empty($id_spis)  ? '&id_spis='.$id_spis : '';
    //  s('$postButton='.$postButton);
/************************************Информация по ребенку таблицах и полях*/
      $this->setTableModule(T_SPRLIST_VALUES);
      $this->addFTL(['name'=>'№','type'=>'number', 'width'=>'20']);
      $this->addFTL(['name'=>'id',
                  'type'=>'field',
                  'name_field'=>'id',
                  'width'=>'20']);
      $this->addFTL(['name'=>'Редагу-<br />вати',
                  'type'=>'edit',
                  'name_parent'=>'id_spis',
                  'width'=>'40']);
      $this->addFTL(['name'=>'Назва поля  ',
                  'type'=>'field',
                  'width'=>'300',
                  'oper'=>'edit',
                  //'name_parent'=>'id_spis',
                //  'lang_type'=>LANG, // данный параметр создается для тех полей которые зеркальные с языком
                  'name_field'=>'value']);

      $this->addFTL(['name'=>'Видалити',
                    'width'=>'60',
                  'type'=>'delete',
                  'name_field'=>'value']);
      $this->addFTL (['name'=>'Актив-<br />ність',
                  'type'=>'plus_minus',
                  'width'=>'60',
                  'name_field'=>'active']);
      $sWhere =  ' and id_spis= '.$id_spis;
      $_SESSION['sprvalues']['where'] =$sWhere;

      // name - название поля рус, name_field  -  в БД и формы назв поля
  //type - тип вывода поля, (по умолч text) 
  //width_left_col - ширина поля левой колнки,(по умолч 280)  
  //align_left_col -выравнивание левой колнки, (по умолч right) 
 // active - выводить ли поле, (по умолч 1)  
 //readonly - 1 readonly, (по умолч 0) 
 //rows- для редактора строчек (по умолч 15)   cols - для редактора колонок (по умолч 80) 
 //  required - поле обязательное и текст если не правильно заполнено
    //  $this->addFF(array('name'=>'Телефон','name_field'=>'phone','bd_field'=>'phone','required_custom'=>'onlyNumber'));

      $this->addFF(['name'=>'id',
                    'name_field'=>'id',
                    'readonly'=>1
                    ]);
      $this->addFF(['name'=>'Назва поля ',
                    'name_field'=>'value',
                    'bd_field'=>'value',
                 //   'lang_type'=>LANG,
                     'required'=>'Назва обов\'язково']);


      $this->addFF(['name'=>'Активність',
                    'name_field'=>'active',
                    'type'=>'checkbox']);


$sql = 'select name from `'.T_SPRLIST.'` where id='.$id_spis;
$name=db_field($sql,'name');
 self::$nameZ='Довідник '.$name;
 self::$nameZList='';
 self::$nameZEdit='';
 self::$TableWidth='700px';

   /*   self::$submenu_edit = array(
          'back' => array(
              'module' => 'sprvalues',
              'action' => 'list',
              'class' => 'ajax_back',
              'post' => $postButton),
          'save' => array(
              'module' => 'sprvalues',
              'action' => 'edit_ok',
              'post' => $postButton),
      );
*/

 self::$submenu_list =array( 
    'back' => array('module' => 'settings', 'action' => 'show', 'post' => ''),
     'add' => array('module' => 'sprvalues', 'action' => 'add', 'post' => ''),
     );
 }}
 /*
  self::$submenu_edit =array( 
    'back' => array('module' => 'sprvalues', 'action' => 'list', 'post' => ''),
     'save' => array('module' => 'sprvalues', 'action' => 'edit_ok', 'post' => ''),
     );
*/

?>