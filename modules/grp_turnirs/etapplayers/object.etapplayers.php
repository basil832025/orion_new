<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class EtapPlayersObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
//s('tyt');
//s($_POST);

$etap_id = poste('etap_id');
//s($etap_id);
$type_etap=0;
if (!empty($etap_id))
{
$sql = 'select type_etap from '.T_ETAPS. ' where id = '.$etap_id;
//s($sql);
$type_etap = db_field($sql,'type_etap');
}

// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20')); 
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'20','postHref'=>'&turnir_id='.poste('turnir_id'))); 
//$this->addFTL(array('name'=>'Дата турнира','type'=>'out_key',
 //   'table'=>T_TURNIRS, 'parent_field'=>'turnir_id','out_result_field'=>'dat',
 //   'width'=>'20','name_field'=>'dat'));
$this->addFTL(array('name'=>'ПІБ гравця','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'name',
    'width'=>'200','name_field'=>'player_id')); 
 $this->addFTL(array('name'=>'ID рей. Укр','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'id_reiting',
    'width'=>'20','name_field'=>'id_reiting')); 
 $this->addFTL(array('name'=>'Рейтинг ФНТУ','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting_ukraine',
    'width'=>'20','name_field'=>'reiting_ukraine'));      
 $this->addFTL(array('name'=>'Рейтинг клубу','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting',
    'width'=>'20','name_field'=>'reiting'));
      $this->addFTL(array('name'=>'Стартовий рейтинг','type'=>'out_key',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'start_reiting',
          'width'=>'20','name_field'=>'start_reiting'));
      $this->addFTL(array('name'=>'Стать','type'=>'out_key',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'sex',
    'width'=>'20','name_field'=>'sex'));      
$this->addFTL(array('name'=>'Група','type'=>'field','width'=>'9','name_field'=>'groups'));
$this->addFTL(array('name'=>'Номер <br />в групі','type'=>'field','width'=>'9','name_field'=>'grp_num'));
$this->addFTL(array('name'=>'Очки','type'=>'field','width'=>'9','name_field'=>'grp_ochki'));
$this->addFTL(array('name'=>'Місце','type'=>'field','width'=>'9','name_field'=>'grp_mesto'));
if ($type_etap<>1) {
$this->addFTL(array('name'=>'Місце посіву сітки','type'=>'field','width'=>'9','name_field'=>'num_posev_olimp'));
$this->addFTL(array('name'=>'Посів','type'=>'sort','width'=>'40','name_field'=>'num_posev_olimp',
'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'name','name_field_sql'=>'etap_id='.poste('etap_id'))); 
}
$this->addFTL(array('name'=>'Видалити','type'=>'delete','width'=>'40','name_field'=>'id'));
//================================================================================================
//================================================================================================
if ($type_etap<>1){
if  (empty($_SESSION['etapplayers']['sort']))  $_SESSION['etapplayers']['sort']='num_posev_olimp';
if  (empty($_SESSION['etapplayers']['sort_type']))  $_SESSION['etapplayers']['sort_type']='asc';
}else
{
if  (empty($_SESSION['etapplayers']['sort']))  $_SESSION['etapplayers']['sort']='groups,grp_mesto,grp_num';
if  (empty($_SESSION['etapplayers']['sort_type']))  $_SESSION['etapplayers']['sort_type']='asc';
    
} 
//================================================================================================
// описание полей формы модуля при редактировании или добавления
$this->addFF(array('name'=>'Гравець','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'player_id',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id',
                    'mess'=>'Вибиріть гравця',
                    'table'=>T_PLAYERS,
                    'where'=>'
                     and not exists(select * from '.T_ETAPS_PLAYER_MESTA.' e where etap_id='.poste('etap_id').' and e.player_id=p.id) 
                     and exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=p.id) 
                     ',
                    
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                    'required'=>'Гравець  обов"язково',
                   'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'where'=>'exists(select * from '.T_TURNIR_PLAYERS.' tp where turnir_id='.poste('turnir_id').' and tp.player_id=m.id) and ','table'=>T_PLAYERS ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця 1','name_field'=>'name','width'=>'250','filter'=>'1'),
                      //  array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                      //  array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )
                    ));
//$this->addFF(array('name'=>'Группа','name_field'=>'groups','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
//$this->addFF(array('name'=>'Номер в группе','name_field'=>'grp_num','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
//$this->addFF(array('name'=>'Очки','name_field'=>'grp_ochki','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));
                    
//$this->addFF(array('name'=>'Место','name_field'=>'grp_mesto', 'required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));

                  
 $this->addFF(array('name'=>'Рейтинг ФНТУ гравця','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'reiting_ukraine',
                    'out_result_field'=>'reiting_ukraine',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рік народження','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'god_rogd',
                    'out_result_field'=>'god_rogd',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
  
 $this->addFF(array('name'=>'Стартовий рейтинг для посіву','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'start_reiting',
                    'out_result_field'=>'start_reiting',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  
 
  $this->addFF(array('name'=>'Стать m/f','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'sex',
                    'out_result_field'=>'sex',
                    'bd_field'=>'player_id',
                    'size'=>1,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
                                         
$this->addFF(array('name'=>'Група','name_field'=>'groups','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
$this->addFF(array('name'=>'Номер в групі','name_field'=>'grp_num','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
$this->addFF(array('name'=>'Очки','name_field'=>'grp_ochki','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));
                    
$this->addFF(array('name'=>'Місце','name_field'=>'grp_mesto', 'required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));
$this->addFF(array('name'=>'Місце посіву сітки','name_field'=>'num_posev_olimp', 'required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));

  
                                                                                     
// описание полей формы модуля при редактировании или добавления

  $this->setTableModule(T_ETAPS_PLAYER_MESTA);
  //$this->setTypeModule('tree');
  
  //self::$nameZ='Список участников турнира::';   
 //self::$nameZList='Статистика';   
if ($_SESSION['gt']['user_rule']<=10)
 self::$submenu_list =array( 
   // 'filter' => array('module' => 'tovs'),
    'back' => array('module' => 'etaps', 'action' => 'list', 'post' => '&turnir_id='.poste('turnir_id').'&'),
  
  // 'filter' => array('menu_name'=>'Экспорт в Excel новых игроков', 'module' => 'turnirsplayers', 'action' => 'toexcel', 'post' => 'id='.poste('turnir_id')),
     'filter' => array('menu_name'=>'Заповнити ігри', 'module' => 'etaps', 'action' => 'setgames', 'post' => '&etap_id='.poste('etap_id').'&turnir_id='.poste('turnir_id').'&'),
  
    );
 $_SESSION['wintype']['turnirsplayers']['addm'] =array(
                        array('name'=>'ПІБ гравці 2','name_field'=>'name','width'=>'250','filter'=>'1'),
                    //    array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                    //    array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                     );  
    
 self::$aFilters=array(
    'name'=>'По імені',
    'articul'=>'По артикулам',
 );
 /*    self::$submenu_edit = array(
    'back' => array('module' => 'turnirsplayers', 'action' => 'list'),
    'save' => array('module' => 'turnirsplayers', 'action' => 'edit_ok'),
      );
 */
 
self::InitMainMenu();
  self::$aParent[0]= array('name_field'=>'etap_id','table'=>T_ETAPS_PLAYER_MESTA, 'type'=>'Hidden' );
   self::$aParent[1]= array('name_field'=>'turnir_id','table'=>T_TURNIRS, 'type'=>'Hidden'  );
   self::$aParent[2]= ['name_field'=>'league_id',  'type'=>'Hidden'];
  }
    
}
?>