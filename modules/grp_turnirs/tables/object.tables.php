<?php


// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class TablesObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {

 
  self::$nameZ='"Результаты этапов::';   
 self::$nameZList='';   
if ($_SESSION['gt']['user_rule']<=10)
 self::$submenu_list =array( 
   // 'filter' => array('module' => 'tovs'),
  // 'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>'print_page(\'print_group\');'),
 //  'print1' =>array( 'module' => 'turnirsplayers', 'javascript'=>' print();'),
    
  // 'prava_user' => array('menu_name'=>'Рассчитать места', 'module' => 'groups', 'action' => 'raschetmest', 'post' => 'id='.poste('id')),
    
 //  'report_ok' => array('menu_name'=>'Пересчитать рейтинг по данному туриниру','module' => 'turnirsplayers', 'action' => 'raschet', 'post' => 'id='.poste('id')),
    'back' => array('module' => 'turnirs', 'action' => 'list'),
    );
 //s('ttyyy');
// $this->getSubMenu2Data();
 /*self::$subMenu2 = array('1'=>
                        array(
                                'name'=>'Группы',
                                'href'=>'#turnirs|list',
                                                       ),
                        '2'=>
                        array(
                                'name'=>'Первый финал',
                                'href'=>'#turnirsplayers|list|turnir_id='.poste('id'),
                            ),
                           '3'=>
                        array(
                                'name'=>'Второй финал ',
                                'href'=>'#groups|show|id='.poste('id'),
                         ), 
                        );
 
 */
 
self::InitMainMenu();

  self::$aParent= array('name_field'=>'turnir_id',
                  'table'=>T_TURNIRS,
                  'type'=>'Hidden'
                  );
  }

    
}
?>