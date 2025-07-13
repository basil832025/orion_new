<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class TurnirsPlayersObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
//s('tyt');
// узнаем парный ли этот турнир
//s($_POST);  
$ispara=0;
$turnir_id = poste('turnir_id');

$virt = poste('virt');
//s('$virt='.$virt);
      self::$table_class='table_mob_turn';
if (!empty($turnir_id))
{
    $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
    $aTurnir= db_row($sql);
    $ispara = $aTurnir['ispara'];
    $is_command = $aTurnir['is_command'];
    $command_name1= $_SESSION['command_name1'] = $aTurnir['command_name1'];
    $command_name2= $_SESSION['command_name2'] = $aTurnir['command_name2'];
    // если нет хоть одного названия то командный не командный
    $is_command = (!empty($is_command) && !empty($command_name1) && !empty($command_name2)) ? 1 : 0;
    }
      $sql='select count(cnt_games) as cnt_g from '.T_TURNIR_PLAYERS.' t where turnir_id='.$turnir_id.' and cnt_games is not null';
      $cnt_g = db_field($sql,'cnt_g');
        $url_back = !empty($virt) ? 'turnirsshtraph-list' : 'turnirs-list';
      if ($_SESSION['is_mobile'] ){

          SystemClass::$Java_script_module='show_zag_left("#'.$url_back.'");';
      }else{
          $show_zag_left='show_zag_center();show_zag_left_big("#'.$url_back.'");';
          SystemClass::$Java_script_module=$show_zag_left;
      }
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'20','width_mob'=>'22'));
      if (empty($virt))
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'20')); 
//$this->addFTL(array('name'=>'Дата турнира','type'=>'out_key',
 //   'table'=>T_TURNIRS, 'parent_field'=>'turnir_id','out_result_field'=>'dat',
 //   'width'=>'20','name_field'=>'dat'));
 //     $this->addFTL(array('name' => 'ПІБ гравця', 'name_field' => 'name', 'oper' => 'edit','width'=>'200','action'=>'statistics', 'bd_field' => 'name', 'filter' => 1, 'classAlign' => 'td_align_left'));
 if (!empty($is_command))
     $this->addFTL(array('name' => '<span class="f14 fw700 line14">Команда</span>', 'function'=>'get_comm_name',  'type' => 'get_func',
         'name_field' => 'is_command_num','width'=>'90',
         'no_slash' => 1, 'width_mob' => '49','classAlign'=>'ws30','class'=>'break'));

$this->addFTL(array('name'=>'ПІБ гравця','type'=>'out_key',
    'oper' => 'edit','target'=>true, 'width_mob'=>'130',
  'action'=>'statistics','module'=>'players', 'out_module_id'=>'id', 'out_module_result'=>'id_pl',
    'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'name',
    'width'=>'200','name_field'=>'player_id','classAlign'=>'text-start'));
      if ($_SESSION['gt']['user_rule']<10) {
          $this->addFTL(array('name' => 'Телефон гравця', 'type' => 'out_key',
              'table' => T_PLAYERS, 'parent_field' => 'player_id', 'out_result_field' => 'phone',
              'width' => '150', 'name_field' => 'phone', 'classAlign' => 'text-start'));
      }
if ($cnt_g>0)
  $this->addFTL(array('name'=>'Рейтинг до<br /> турніру','name_mob'=>'Р-нг до<br>турніру','type'=>'field','width'=>'9','name_field'=>'beg_reiting','round'=>0));
else
    $this->addFTL(array('name'=>'Рейтинг до<br /> турніру.','name_mob'=>'Р-нг до<br>турніру','type'=>'out_key',
        'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting',
        'width'=>'20','name_field'=>'reiting','round'=>0));

//$this->addFTL(array('name'=>'Дельта','type'=>'math_oper','width'=>'9','oper'=>'-',  'name_field1'=>'end_reiting', 'name_field2'=>'beg_reiting',  'round'=>0));
$this->addFTL(array('name'=>'Приріст рейтингу','name_mob'=>'Приріст<br>рейтингу','width'=>'9', 'name_field'=>'diff_round','round'=>0 ));
$this->addFTL(array('name'=>'Рейтинг після<br />турніру','name_mob'=>'Р-нг<br>після<br>турніру','type'=>'field','width'=>'9','name_field'=>'end_reiting','round'=>0));
if (empty($virt)) {
    $this->addFTL(array('name' => 'К-ть зіграних<br /> ігор', 'name_mob' => 'К-ть<br />зіграних<br /> ігор', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_games'));
    $this->addFTL(array('name' => 'К-ть виграних<br /> ігор', 'name_mob' => 'К-ть<br /> перемог', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_wins'));
    $this->addFTL(array('name' => 'К-ть поразок', 'name_mob' => 'К-ть<br />  поразок', 'type' => 'field', 'width' => '9', 'name_field' => 'cnt_lose'));
    $this->addFTL(array('name' => 'Місце', 'type' => 'field', 'width' => '9', 'name_field' => 'mesto'));
}
 /*     $this->addFTL(array('name'=>'Поточний рейтинг<br /> клубу','name_mob'=>'Поточ<br />ний р-нг<br /> клубу','type'=>'out_key',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting',
          'width'=>'20','name_field'=>'reiting','round'=>0));*/
      $this->addFTL(array('name'=>'Рейтинг ФНТУ','type'=>'out_key',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'reiting_ukraine',
          'width'=>'20','name_field'=>'reiting_ukraine'));
//$this->addFTL(array('name'=>'Група','name_field'=>'grp','type'=>'ProstSpr', 'id_spis'=>'2', 'bd_field'=>'grp'));
      if ($_SESSION['gt']['user_rule']<10)
      {
      $this->addFTL(array('name'=>'Група','type'=>'out_key_prostspr',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'grp',
          'width'=>'20','name_field'=>'grp'));
//$this->addFTL(array('name'=>'Группа','type'=>'field','width'=>'9','name_field'=>'groups'));  
//$this->addFTL(array('name'=>'Номер <br />в группе','type'=>'field','width'=>'9','name_field'=>'grp_num'));  
//$this->addFTL(array('name'=>'Очков','type'=>'field','width'=>'9','name_field'=>'grp_ochki'));  
//$this->addFTL(array('name'=>'Место','type'=>'field','width'=>'9','name_field'=>'grp_mesto'));  
          $this->addFTL(array('name'=>'ID Ligas','type'=>'out_key',
              'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'id_reiting',
              'width'=>'20','name_field'=>'id_reiting'));

          $this->addFTL(array('name'=>'Стать','type'=>'out_key',
              'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'sex',
              'width'=>'30','name_field'=>'sex','is_img'=>1));
          $this->addFTL(array('name'=>'Опл.<br /> член.<br />внес.','type'=>'out_key',
              'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'is_opl_reiting',
              'width'=>'20','name_field'=>'is_opl_reiting','check_elem'=>1));
         // $this->addFTL(array('name'=>'Опл. внес.','type'=>'field','width'=>'9','name_field'=>'is_opl_this','check_elem'=>1));
          if (empty($virt)) {
              $this->addFTL(array('name' => 'Опл. внес.', 'type' => 'plus_minus', 'name_field' => 'is_opl_this', 'bd_field' => 'is_opl_this', 'width' => '30'));


//          $this->addFTL(array('name'=>'No send','type'=>'field','width'=>'9','name_field'=>'new_player'));

              $this->addFTL(array('name' => 'Оплата', 'type' => 'field', 'width' => '9', 'name_field' => 'grn'));

              $this->addFTL(array('name' => 'Видалити', 'type' => 'delete', 'width' => '40', 'name_field' => 'id'));
          }
}
//================================================================================================
//================================================================================================

if ($ispara)
{
    $this->setIspara();
}
 else
 {
//================================================================================================
// описание полей формы модуля при редактировании или добавления
     $this->addFF(array('name'=>'Лига','name_field'=>'league_id','type'=>'hidden'));
$this->addFF(array('name'=>'Гравець','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'player_id',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id',
                    'mess'=>'Виберіть гравця',
                     'where'=>' and ispara=0 ',
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'width'=> '980', // ширина окна
                    'required'=>'Гравець обов"язково',
                    'speedsearch'=>array('min_letter'=>3,
                        'result_fields_dop'=>array('id','id_reiting','name_ligas','reiting_ukraine','god_rogd',
                        'city','phone','start_reiting','sex','is_opl_reiting','prim'),'table'=>T_PLAYERS,'where'=>' ispara=0  and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця','return_id_val'=>'name', 'name_field'=>'name','width'=>'250','filter'=>'1'),
                        array('name'=>'Рік народження','return_id_val'=>'god_rogd','name_field'=>'god_rogd','width'=>'20'),
                        array('name'=>'Телефон','return_id_val'=>'phone','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'ID Ligas','return_id_val'=>'id_reiting','name_field'=>'id_reiting','width'=>'50','filter'=>'1'),
                        array('name'=>'ПІБ Ligas','return_id_val'=>'name_ligas','name_field'=>'name_ligas','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг ФНТУ','return_id_val'=>'reiting_ukraine','name_field'=>'reiting_ukraine','width'=>'50','filter'=>'1'),
                        array('name'=>'Місто','return_id_val'=>'city','name_field'=>'city','width'=>'50','filter'=>'1'),
                        array('name'=>'Стать m/f','return_id_val'=>'sex','name_field'=>'sex','width'=>'50','filter'=>'1'),
                        array('name'=>'Примітка по гравцю','return_id_val'=>'prim','name_field'=>'prim','width'=>'50','filter'=>'1'),
                        
                    )
                    ));
if (!empty($is_command))
     $this->addFF(array('name'=>'До якого клубу належить гравець', 'type'=>'radiobox', 'name_field'=>'is_command_num','bd_field'=>'is_command_num',
         'valRadio'=>[
             ['name'=>$command_name1,'val'=>'1'],
             ['name'=>$command_name2,'val'=>'2'],
         ]
     ));
  //   $this->addFF(array('name'=>'Група','name_field'=>'grp','type'=>'out_key_prostspr', 'id_spis'=>'2', 'out_result_field'=>'grp', 'bd_field'=>'player_id','table'=>T_PLAYERS));
//$this->addFF(array('name'=>'Группа','name_field'=>'groups','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
//$this->addFF(array('name'=>'Номер в группе','name_field'=>'grp_num','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>1));
//$this->addFF(array('name'=>'Очки','name_field'=>'grp_ochki','required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));
                    
//$this->addFF(array('name'=>'Место','name_field'=>'grp_mesto', 'required_custom'=>'onlyNumber','size'=>'1','maxlength'=>2));
$this->addFF(array('name'=>'ID Ligas','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'id_reiting',
                    'out_result_field'=>'id_reiting',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));
 $this->addFF(array('name'=>'ПІБ Ligas','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'name_ligas',
                    'out_result_field'=>'name_ligas',
                    'bd_field'=>'player_id',
                    'size'=>50,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рейтинг ФНТУ Ligas','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'reiting_ukraine',
                    'out_result_field'=>'reiting_ukraine',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рік народження Ligas','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'god_rogd',
                    'out_result_field'=>'god_rogd',
                    'readonly'=>1,
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
  $this->addFF(array('name'=>'Місто Ligas','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'city',
                    'out_result_field'=>'city',
                    'bd_field'=>'player_id',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));
     $this->addFF(array('name'=>'Стать Ligas','width'=>'250',
         'type'=>'RadioOutKey',
         'name_field'=>'sex',
         'out_result_field'=>'sex',
         'bd_field'=>'player_id',
         'valRadio'=>[
             ['name'=>'Чоловік','val'=>'m'],
             ['name'=>'Жінка','val'=>'f'],
         ],
         'size'=>1,
         'table'=>T_PLAYERS,
         'no_vubor' => '',
         'module'=>'players',
     ));
     $this->addFF(array('name'=>'сплачений членський внесок Ligas?','width'=>'250',
         'type'=>'checkboxout',
         'name_field'=>'is_opl_reiting',
         'out_result_field'=>'is_opl_reiting',
         'bd_field'=>'player_id',
         'size'=>10,
         'table'=>T_PLAYERS,
         'no_vubor' => '',
         'module'=>'players',
     ));

     $this->addFF(array('name'=>'Телефон гравця','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'phone',
                    'out_result_field'=>'phone',
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

   $this->addFF(array('name'=>'Примітка по гравцю','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'prim',
                    'out_result_field'=>'prim',
                    'bd_field'=>'player_id',
                    
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                                          
$this->addFF(array('name'=>'ПІБ нового гравця','name_field'=>'new_name', 'size'=>'40','type'=>'TextNoSQL'));
  //'pattern'=>".{0}|[0-9]{1,5}" только цифры от 1 до 5 или пусто
 $this->addFF(array('name'=>'Оплата грн','name_field'=>'grn', 'size'=>'2','pattern'=>".{0}|.{0.0}|[0-9]{1,5}",'required'=>'Абл 0 або суму 5 цифр'));

$this->addFF(array('name'=>'Сплачений внесок на цьому турніру','name_field'=>'is_opl_this', 'type'=>'Checkbox','bd_field'=>'is_opl_this'));
$this->addFF(array('name'=>'Гравець знявся з турніру','name_field'=>'break', 'type'=>'Checkbox','bd_field'=>'break'));
$this->addFF(array('name'=>'Забрати розрахунок рейтингу в Ligas','name_field'=>'new_player', 'type'=>'Checkbox','bd_field'=>'new_player'));
     $this->addFF(array('name'=>'Місце в турнірі','name_field'=>'mesto', 'size'=>'2'));

  
}  
                                                                                     
// описание полей формы модуля при редактировании или добавления

  $this->setTableModule(T_TURNIR_PLAYERS);
  //$this->setTypeModule('tree');
      if (!empty($turnir_id)) {
          $name_turnir = db_row('select name,dat  from `' . T_TURNIRS .
              '` where id=' . $turnir_id);
          $date = new DateTimeImmutable($name_turnir['dat']);
          $tdat = $date->format('d.m.Y');
        //  self::$nameZ = ' '
          $sql='select dat, (select count(end_reiting) from '.T_TURNIR_PLAYERS.' t where r.id=t.turnir_id and end_reiting<>0)  as cnt_g   
  from '.T_TURNIRS.' r  where  r.id='.$turnir_id;
          $vData = db_row($sql);
          $Work_turnir=db_field('SELECT COUNT(*) AS cn FROM bs_reiting r WHERE turnir_id='.$turnir_id.' AND (r.table_game>0 OR COALESCE(r.win_player,0)>0)','cn');
          if ($vData['cnt_g']>0  ){
                $title='';
          }elseif($Work_turnir>0){

              $title=' - в процесі';
          }else{

              $title=' - не розпочато';
          }
          if ($_SESSION['is_mobile'] )
              $nameZ='<div class="compare_zagl">Статистика гравців "'.$name_turnir['name'].' ('.$tdat.$title. ')"</div>';
          else
              $nameZ='<div class="poriv_zag">Статистика гравців  "'.$name_turnir['name'].'" ('.$tdat.$title. ')</div>';

          self::$nameZList=$nameZ;



          $nameZList = '';
          self::$nameZEdit = 'Редагування гравця турніру "' . $name_turnir['name'] . '" (' . $tdat . ')';
      //    SystemClass::setZaglModule($nameZList);
      }
if ($_SESSION['gt']['user_rule']<10 && empty($virt))
 self::$submenu_list =array( 
   // 'filter' => array('module' => 'tovs'),
  //  'back' => array('module' => 'turnirs', 'action' => 'list'),
  
  // 'filter' => array('menu_name'=>'Экспорт в Excel новых игроков', 'module' => 'turnirsplayers', 'action' => 'toexcel', 'post' => 'id='.poste('turnir_id')),
   'truck' => array('menu_name'=>'Отримати данні по гравцям з Ligas','module' => 'turnirsplayers', 'action' => 'import_ligas', 'post' => 'id='.poste('turnir_id')),
   'filter' => array('menu_name'=>'Експорт в Excel нових гравців', 'http' => 'modules/turnirsplayers/action/toexcel.php?id='.poste('turnir_id')),
  // 'prava_user' => array('menu_name'=>'Группы данного турнира', 'module' => 'groups', 'action' => 'show', 'post' => 'id='.poste('turnir_id')),
    
   'report_ok' => array('menu_name'=>'Перерахувати рейтинг по даному турніру','module' => 'turnirsplayers', 'action' => 'raschet', 'post' => 'id='.poste('turnir_id')),
 //    'add' => array('menu_name'=>'Добавить участников турнира','wintype'=>1, 'module' => 'turnirsplayers', 'action' => 'addm', 'post' => 'wintype=1&actionmany=addm&id='.poste('turnir_id').'&'),
  
    );
/* $_SESSION['wintype']['players']['list'] =array(
                        array('name'=>'ПІБ гравця','name_field'=>'name','width'=>'250','filter'=>'1'),
                    //    array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                    //    array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                     );  */
    
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
 /*    self::$submenu_edit = array(
    'back' => array('module' => 'turnirsplayers', 'action' => 'list'),
    'save' => array('module' => 'turnirsplayers', 'action' => 'edit_ok'),
      );
 */


if (empty($virt)) self::InitMainMenu(); else{
    $post_return = '&virt=1';
    SystemClass::setPost_return($post_return);
    $_SESSION['POST_RETURN'] =$post_return;
}
  self::$aParent[0]= ['name_field'=>'turnir_id', 'table'=>T_TURNIRS, 'type'=>'Hidden'];
  self::$aParent[1]= ['name_field'=>'league_id', 'type'=>'Hidden'];
  }
 function setIspara ()
 {
    
 
//================================================================================================
// описание полей формы модуля при редактировании или добавления
     $this->addFF(array('name'=>'Лига','name_field'=>'league_id','type'=>'hidden'));
$this->addFF(array('name'=>'Пара','width'=>'250', 
                    'type'=>'out_key',
                    'name_field'=>'player_id',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id',
                    'mess'=>'Вибиріть пару',
                     'where'=>' and ispara=1 ',
                    
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                    'required'=>'Пара  обов"язково',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'table'=>T_PLAYERS,
                    'where'=>' ispara=1 and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ пари','name_field'=>'name','width'=>'250','filter'=>'1'),
                      //  array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                      //  array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )

                    ));
 // 1 игрок инфа==========================================================================                   
$this->addFF(array('name'=>'Гравець 1','width'=>'250',
                    'type'=>'out_key',
                     
                    'name_field'=>'player_id_1',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id_1',
                    'mess'=>'Виберіть гравця 1',
                    'table'=>T_PLAYERS,
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                   // 'required'=>'Игрок  объязательно',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array(
                    'id_reiting_1'=>'id_reiting','name_ligas_1'=>'name_ligas','reiting_ukraine_1'=>'reiting_ukraine',
                    'god_rogd_1'=>'god_rogd','city_1'=>'city','phone_1'=>'phone','start_reiting_1'=>'start_reiting',
                    'sex_1'=>'sex','is_opl_reiting_1'=>'is_opl_reiting','prim_1'=>'prim'                    
                    ),
                 
                    'table'=>T_PLAYERS,
                    'where'=>'  ispara=0  and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця 1','name_field'=>'name','width'=>'250','filter'=>'1'),
                      //  array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                      //  array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )
                    ));
$this->addFF(array('name'=>'ID Ligas гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'id_reiting_1',
                    'out_result_field'=>'id_reiting',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));
 $this->addFF(array('name'=>'ПІБ Ligas гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'name_ligas_1',
                    'out_result_field'=>'name_ligas',
                    'bd_field'=>'player_id_1',
                    'size'=>50,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рейтинг ФНТУ гравця  1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'reiting_ukraine_1',
                    'out_result_field'=>'reiting_ukraine',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рік народження гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'god_rogd_1',
                    'out_result_field'=>'god_rogd',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
  $this->addFF(array('name'=>'Місто гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'city_1',
                    'out_result_field'=>'city',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                       
 $this->addFF(array('name'=>'Телефон гравця  1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'phone_1',
                    'out_result_field'=>'phone',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  
 $this->addFF(array('name'=>'Стартовий рейтинг для посіву гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'start_reiting_1',
                    'out_result_field'=>'start_reiting',
                    'bd_field'=>'player_id_1',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  

  $this->addFF(array('name'=>'Стать m/f гравця 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'sex_1',
                    'out_result_field'=>'sex',
                    'bd_field'=>'player_id_1',
                    'size'=>1,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
   $this->addFF(array('name'=>'Примітка по гравцю 1','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'prim_1',
                    'out_result_field'=>'prim',
                    'bd_field'=>'player_id_1',
                    
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                                          
$this->addFF(array('name'=>'ПІБ нового гравця 1','name_field'=>'new_name_1', 'size'=>'40','type'=>'TextNoSQL'));
 // 2 игрок инфа==========================================================================                   
$this->addFF(array('name'=>'Гравець 2','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'player_id_2',
                    'out_result_field'=>'name',
                    'bd_field'=>'player_id_2',
                    'mess'=>'Виберіть гравця 2',
                    'table'=>T_PLAYERS,
                     'where'=>' and ispara=0 ',
                     'no_vubor' => '',
                     'width'=> '500', // ширина окна
                   // 'required'=>'Игрок  объязательно',
                    'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array(
                    'id_reiting_2'=>'id_reiting','name_ligas_2'=>'name_ligas','reiting_ukraine_2'=>'reiting_ukraine',
                    'god_rogd_2'=>'god_rogd','city_2'=>'city','phone_2'=>'phone','start_reiting_2'=>'start_reiting',
                    'sex_2'=>'sex','is_opl_reiting_2'=>'is_opl_reiting','prim_2'=>'prim'                    
                    ),'table'=>T_PLAYERS,
                    'where'=>'  ispara=0  and ' ),
                    'module'=>'players',
                    'descr_table'=>array(
                        array('name'=>'ПІБ гравця 2','name_field'=>'name','width'=>'250','filter'=>'1'),
                      //  array('name'=>'Год рождения','name_field'=>'god_rogd','width'=>'20'),
                      //  array('name'=>'Телефон','name_field'=>'phone','width'=>'50','filter'=>'1'),
                        array('name'=>'Рейтинг','name_field'=>'reiting','width'=>'50','filter'=>'1'),
                        
                    )
                    ));
$this->addFF(array('name'=>'ID Ligas гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'id_reiting_2',
                    'out_result_field'=>'id_reiting',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));
 $this->addFF(array('name'=>'ПІБ Ligas гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'name_ligas_2',
                    'out_result_field'=>'name_ligas',
                    'bd_field'=>'player_id_2',
                    'size'=>50,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рейтинг ФНТУ гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'reiting_ukraine_2',
                    'out_result_field'=>'reiting_ukraine',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                    
 $this->addFF(array('name'=>'Рік народження гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'god_rogd_2',
                    'out_result_field'=>'god_rogd',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
  $this->addFF(array('name'=>'Місто гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'city_2',
                    'out_result_field'=>'city',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                       
 $this->addFF(array('name'=>'Телефон  гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'phone_2',
                    'out_result_field'=>'phone',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  
 $this->addFF(array('name'=>'Стартовий рейтинг для посіву гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'start_reiting_2',
                    'out_result_field'=>'start_reiting',
                    'bd_field'=>'player_id_2',
                    'size'=>10,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));  

  $this->addFF(array('name'=>'Стать m/f гравця 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'sex_2',
                    'out_result_field'=>'sex',
                    'bd_field'=>'player_id_1',
                    'size'=>1,
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     )); 
   $this->addFF(array('name'=>'Примітка по гравцю 2','width'=>'250',
                    'type'=>'TextOutKey',
                    'name_field'=>'prim_2',
                    'out_result_field'=>'prim',
                    'bd_field'=>'player_id_2',
                    
                     'table'=>T_PLAYERS,
                     'no_vubor' => '',
                       'module'=>'players',
                     ));                                          
$this->addFF(array('name'=>'ПІБ нового гравця 2','name_field'=>'new_name_2', 'size'=>'40','type'=>'TextNoSQL'));

  
 $this->addFF(array('name'=>'Оплата грн','name_field'=>'grn', 'size'=>'2'));
 }   
}
function get_comm_name($field,$id,$data)
{
    s($data);
    s($field);
    s($id);
    $class='';
    $comm_name = $data['is_command_num'];
    if ($comm_name==1) { $class='command1'; $n=$_SESSION['command_name1'];}
    if ($comm_name==2) {$class='command2'; $n=$_SESSION['command_name2'];}
    $name ='<div class="f14 '.$class.'"><span class="f14 '.$class.'">'.$n.'</span></div>';

    return $name;
}
?>