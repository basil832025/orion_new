<?php
//s('turnirsUP');
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class ShopObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
    
    $type_id = poste('type_id');
    $type_id = !empty($type_id) ? $type_id : 1;
    $name =  ($type_id==1) ? 'Название абонемента' : 'Название услуги'; 
//s('turnirs');
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20')); 
$this->addFTL(array('name'=>'Редакти-<br />ровать','type'=>'edit','width'=>'40','postButton'=>'&type_id='.$type_id));
$this->addFTL(array('name'=>'Дата покупки','type'=>'date','width'=>'100','name_field'=>'date_shop'));
$this->addFTL(array('name'=>'ФИО клиента','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'acc','out_result_field'=>'name',
    'width'=>'200','name_field'=>'acc')); 
$this->addFTL(array('name'=>'Название услуги','type'=>'out_key',
    'table'=>T_SPRTOV, 'parent_field'=>'tov','out_result_field'=>'name',
    'width'=>'200','name_field'=>'tov')); 
$this->addFTL(array('name'=>'Тип услуги','type'=>'field','width'=>'9','name_field'=>'type_tov'));                    
$this->addFTL(array('name'=>'Сумма ','type'=>'field','width'=>'90','name_field'=>'summa')); 
$this->addFTL(array('name'=>'Еденица <br>измерения','type'=>'field','width'=>'90','name_field'=>'ed')); 
$this->addFTL(array('name'=>'К-во услуг','type'=>'field','width'=>'90','name_field'=>'cnt_tov'));  
$this->addFTL(array('name'=>'Остаток услуг','type'=>'field','width'=>'90','name_field'=>'ost_tov'));  
$this->addFTL(array('name'=>'Дата начала','type'=>'date','width'=>'100','name_field'=>'date_start'));
$this->addFTL(array('name'=>'Дата окончания','type'=>'date','width'=>'100','name_field'=>'date_stop'));

$this->addFTL(array('name'=>'Удалить','type'=>'delete','width'=>'40','name_field'=>'name')); 
//================================================================================================
 $this->addFF(array(  'name'=>'тип ', 'type'=>'hidden', 'name_field'=>'type_tov'));
$this->addFF(array('name'=>'Клиент','width'=>'250', 
                    'type'=>'out_key',
                    'name_field'=>'acc',
                    'out_result_field'=>'name',
                    'bd_field'=>'acc',
                    'mess'=>'Выбирете Клиента',
                  //   'where'=>' and ispara=0 ',
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                    'required'=>'Игрок  объязательно',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id','phone','prim'),'table'=>T_PLAYERS,'where'=>' ispara=0  and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ФИО клиента','name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                        array('name'=>'Телефон','name_field'=>'phone','return_id_val'=>'phone', 'width'=>'50','filter'=>'1'),
                        array('name'=>'Примечание','name_field'=>'prim','return_id_val'=>'prim', 'width'=>'50','filter'=>'1'),
                     //   array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )
                    ));
   $this->addFF(array('name'=>'Телефон игрока','width'=>'250', 
                    'type'=>'TextOutKey',
                    'name_field'=>'phone',
                    'out_result_field'=>'phone',
                    'bd_field'=>'acc',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  
     $this->addFF(array('name'=>'Примечание по игроку','width'=>'250', 
                    'type'=>'TextOutKey',
                    'name_field'=>'prim',
                    'out_result_field'=>'prim',
                    'bd_field'=>'acc',
                    
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));    
   $this->addFF(array('name'=>'ФИО нового клиента','name_field'=>'new_name', 'size'=>'40','type'=>'TextNoSQL'));
                                        
// описание полей формы модуля при редактировании или добавления
// $this->addFF(array('name'=>$name,'name_field'=>'name','required'=>'Название объязательно'));
 if ($type_id==1) 
{ 
 $this->addFF(array('name'=>'Название абонемента','width'=>'250', 
                    'type'=>'out_key',
                    'name_field'=>'tov',
                    'out_result_field'=>'name',
                    'bd_field'=>'tov',
                    'mess'=>'Выбирете абонемент',
                  //   'where'=>' and ispara=0 ',
                    'table'=>T_SPRTOV,
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                    'required'=>'Абонемент  объязательно',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'table'=>T_SPRTOV,'where'=>' type_poslugy=1  and ' ),
                    'module'=>'sprtov',
                    'descr_table'=>array(
                        array('name'=>'Название абонемента','name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Сумма','name_field'=>'summa','return_id_val'=>'summa', 'width'=>'20'),
                        array('name'=>'К-во месяцев','name_field'=>'cnt_mount',  'width'=>'50','filter'=>'1'),
                        array('name'=>'К-во часов','name_field'=>'cnt_hour','return_id_val'=>'cnt_tov', 'width'=>'50','filter'=>'1'),
                        
                    )
                    ));   
  $this->addFF(array('name'=>'Дата покупки','type'=>'date','width'=>'100','name_field'=>'date_shop'));                  
  $this->addFF(array('name'=>'Дата старта','type'=>'date','width'=>'100','name_field'=>'date_start'));                  
 }    
  $this->addFF(array(  'name'=>'Сумма','name_field'=>'summa','size'=>'6','maxlength'=>6,'required_custom'=>'onlyNumber'));
  $this->addFF(array(  'name'=>'К-во часов','name_field'=>'cnt_tov','size'=>'6','maxlength'=>6,'required_custom'=>'onlyNumber'));
      
  $this->setTableModule(T_SHOPS);
//  if  (empty($_SESSION['sprtov']['sort']))  $_SESSION['sprtov']['sort']='dat';
//if  (empty($_SESSION['sprtov']['sort_type']))  $_SESSION['sprtov']['sort_type']='desc';
  //$this->setTypeModule('tree');
    self::$aParent[0]= array('name_field'=>'type_id',
                  
                  'type'=>'Hidden'
                  );
 $this->getSubMenu2Data($type_id);
  self::$nameZ='Продажи::';   
 self::$nameZList='Список';   
 self::$nameZEdit='Редактирование продажи'; 
 self::$submenu_list =array( 
     'back' => array('module' => 'shop', 'action' => 'list'),
 //    'add' => array('module' => 'sprtov', 'action' => 'add', 'post' => 'type_id='.$type_id),
    );
     self::$submenu_edit = array(
    'back' => array('module' => 'shop', 'action' => 'list'),
    'save' => array('module' => 'shop', 'action' => 'edit_ok'),
  //  'report_ok' => array('module' => 'turnirs', 'action' => 'raschet')
    );
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
 
 $sWhere = !empty($type_id)  ? ' and type_tov='.$type_id : '';                  
$_SESSION['shop']['where'] =$sWhere;    
 
  } 
    
     function getSubMenu2Data($type_id)
  {  
    $submenu2 = array();
     $submenu2[1]['name'] = 'Абонементы'; 
     $submenu2[1]['href'] = '#shop-list-type_id=1'; 
     $submenu2[1]['class'] = 'black_color'; 
     $submenu2[2]['name'] = 'Услуги'; 
     $submenu2[2]['href'] = '#shop-list-type_id=2'; 
     $submenu2[2]['class'] = 'black_color';
   
        if (1==$type_id)  $submenu2[1]['class'] = 'red_color'; else   $submenu2[2]['class'] = 'red_color';  
   
   //         
       //   s($submenu2);
  self::$subMenu2 = $submenu2;
  }   
}
?>