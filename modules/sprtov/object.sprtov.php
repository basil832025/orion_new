<?php
//s('turnirsUP');
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class SprTovObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {

    $type_id = poste('type_id');
    $type_id = !empty($type_id) ? $type_id : 1;
//s('turnirs');
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20')); 
$this->addFTL(array('name'=>'Редакти-<br />ровать!','type'=>'edit','width'=>'40'));
$name =  ($type_id==1) ? 'Название абонемента' : 'Название услуги'; 
$name_cnt =  ($type_id==1) ? 'Количество тренеровок' : 'Количество'; 
$this->addFTL(array('name'=>$name,'type'=>'field','width'=>'200','name_field'=>'name'));  
if ($type_id==1) 
{                  
$this->addFTL(array('name'=>'К-во месяцев','type'=>'field','width'=>'60','name_field'=>'cnt_mount'));                    
$this->addFTL(array('name'=>'К-во часов ','type'=>'field','width'=>'90','name_field'=>'cnt_hour'));                    
}
$this->addFTL(array('name'=>'Цена ','type'=>'field','width'=>'90','name_field'=>'summa'));      
if ($type_id==1) 
{                
$this->addFTL(array('name'=>'Время с ','type'=>'field','width'=>'90','name_field'=>'time_from'));                    
$this->addFTL(array('name'=>'Время по ','type'=>'field','width'=>'90','name_field'=>'time_to'));                    
}   
if ($type_id==2) 
{                  
$this->addFTL(array('name'=>'Еденица <br>измерения','type'=>'field','width'=>'90','name_field'=>'ed'));                    
}
$this->addFTL(array('name'=>$name_cnt,'type'=>'field','width'=>'90','name_field'=>'cnt'));                    

$this->addFTL(array('name'=>'Удалить','type'=>'delete','width'=>'40','name_field'=>'name')); 
//================================================================================================
// описание полей формы модуля при редактировании или добавления
 $this->addFF(array('name'=>$name,'name_field'=>'name','required'=>'Название абонемента объязательно'));
 if ($type_id==1) 
{ 
 $this->addFF(array('name'=>'К-во месяцев','name_field'=>'cnt_mount','size'=>'2','maxlength'=>2,'required_custom'=>'onlyNumber'));
 $this->addFF(array('name'=>'К-во часов','name_field'=>'cnt_hour','size'=>'2','maxlength'=>2,'required_custom'=>'onlyNumber'));
 }
 //$this->addFF(array(  'name'=>'К-во участников','name_field'=>'cnt_players','required'=>'К-во игроков объязательно','required_custom'=>'noSpecialCaracters'));
 $this->addFF(array(  'name'=>'Цена','name_field'=>'summa','size'=>'6','maxlength'=>6,'required_custom'=>'onlyNumber'));
 $this->addFF(array(  'name'=>'тип ', 'type'=>'hidden', 'name_field'=>'type_poslugy'));
if ($type_id==1) 
{ 
 $this->addFF(array('name'=>'Время с','name_field'=>'time_from','size'=>'5','maxlength'=>5));
 $this->addFF(array('name'=>'Время по','name_field'=>'time_to','size'=>'5','maxlength'=>5));
 }
 if ($type_id==2) 
{ 
 $this->addFF(array('name'=>'Еденица <br>измерения','name_field'=>'ed','size'=>'10','maxlength'=>10,));                    
   $this->addFF(array(  'name'=>$name_cnt,'name_field'=>'cnt','size'=>'6','maxlength'=>6,'required_custom'=>'onlyNumber'));
 
 }  
              
  $this->setTableModule(T_SPRTOV);
//  if  (empty($_SESSION['sprtov']['sort']))  $_SESSION['sprtov']['sort']='dat';
//if  (empty($_SESSION['sprtov']['sort_type']))  $_SESSION['sprtov']['sort_type']='desc';
  //$this->setTypeModule('tree');
    self::$aParent[0]= array('name_field'=>'type_id',
                  
                  'type'=>'Hidden'
                  );
 $this->getSubMenu2Data($type_id);
  self::$nameZ='Абонементы/услуги::';   
 self::$nameZList='Список';   
 self::$nameZEdit='Редактирование услуги/абонемента'; 
 self::$submenu_list =array( 
     'back' => array('module' => 'sprtov', 'action' => 'list'),
 //    'add' => array('module' => 'sprtov', 'action' => 'add', 'post' => 'type_id='.$type_id),
    );
     self::$submenu_edit = array(
    'back' => array('module' => 'sprtov', 'action' => 'list'),
    'save' => array('module' => 'sprtov', 'action' => 'edit_ok'),
  //  'report_ok' => array('module' => 'turnirs', 'action' => 'raschet')
    );
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
 
 $sWhere = !empty($type_id)  ? ' and type_poslugy='.$type_id : '';                  
$_SESSION['sprtov']['where'] =$sWhere;    
 
  } 
    
     function getSubMenu2Data($type_id)
  { 
    $submenu2 = array();
     $submenu2[1]['name'] = 'Абонементы'; 
     $submenu2[1]['href'] = '#sprtov-list-type_id=1'; 
     $submenu2[1]['class'] = 'black_color'; 
     $submenu2[2]['name'] = 'Услуги'; 
     $submenu2[2]['href'] = '#sprtov-list-type_id=2'; 
     $submenu2[2]['class'] = 'black_color';
   
        if (1==$type_id)  $submenu2[1]['class'] = 'red_color'; else   $submenu2[2]['class'] = 'red_color';  
   
   //         
       //   s($submenu2);
  self::$subMenu2 = $submenu2;
  }   
}
?>