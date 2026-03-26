<?php
// класс описующий струткру модуля, таблицы, формы, и как и что выводить
class EtapsObject extends ObjectRT 
{   
  //$this-> = 'tree'; 
  function init ()
  {
    $etap_id= poste('id');
      $turnir_id= poste('turnir_id');
      $league_id = poste('league_id');
    $type_etap=0;
      $ist_type_etap=2;
      $sql = 'select * from '.T_TURNIRS.' t where t.id='.$turnir_id;
      $aTurnir= db_row($sql);
      
      // Определяем, является ли это командной лигой (по новому ТЗ)
      $is_team_league = 0;
      if (!empty($league_id)) {
          $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
      }
      
      // Старая логика для обратной совместимости
      $is_command = $aTurnir['is_command'];
      $command_name1= $_SESSION['command_name1'] = $aTurnir['command_name1'];
      $command_name2= $_SESSION['command_name2'] = $aTurnir['command_name2'];
    // если нет хоть одного названия то командный не командный
      $is_command = (!empty($is_command) && !empty($command_name1) && !empty($command_name2)) ? 1 : 0;
      
   if (!empty($etap_id)) {
$sql = 'select type_etap, (SELECT type_etap FROM bs_etaps_work e WHERE e.id=w.istochnik_posev) AS ist_type_etap from '.T_ETAPS. ' w where id = '.$etap_id;
$aEtap = db_row($sql);
$type_etap = $aEtap['type_etap'];
$ist_type_etap = !empty($aEtap['ist_type_etap']) ?  $aEtap['ist_type_etap'] : 0;
    } else {
    // При создании нового этапа проверяем, есть ли уже этапы с type_etap=66 в этом турнире
    // Если есть, то это турнир "команда против команды"
    if ($is_command) {
        $sql = 'SELECT COUNT(*) as cnt FROM '.T_ETAPS.' WHERE turnir_id='.$turnir_id.' AND type_etap=66';
        $cnt_team_vs_team = db_field($sql, 'cnt');
        if ($cnt_team_vs_team > 0) {
            $type_etap = 66; // Предполагаем, что новый этап тоже будет type_etap=66
        }
        // Для командных турниров по умолчанию используем формат "команда против команды"
        $type_etap = 66;
    }
 }
// описание полей таблицы модуля    
$this->addFTL(array('name'=>'№','type'=>'number','width'=>'5')); 
$this->addFTL(array('name'=>'Ред.','type'=>'edit','width'=>'5'));
      if ($is_command)
      $this->addFTL(array('name' => 'Тип етапу', 'name_field' => 'type_etap', 'bd_field' => 'type_etap', 'width' => '80', 'width_mob' => '44', 'type' => 'prostspr'));
    else
        $this->addFTL(array('name'=>'Тип етапу','type'=>'out_key',
    'table'=>T_ETAPS_NAME, 'parent_field'=>'type_etap','out_result_field'=>'name',
    'width'=>'200','name_field'=>'type_etap'));

      $this->addFTL(array('name'=>'Назва етапу','type'=>'goto_modact','width'=>'200','name_field'=>'name_etap',
          'name_field_child'=>'etap_id',
          'action'=>'sort_etap','module'=>'etaps'));
$this->addFTL(array('name'=>'Кількість гравців','type'=>'field','width'=>'9','name_field'=>'cnt_people'));
$this->addFTL(array('name'=>'Гравці етапу','type'=>'anyaction',  'width'=>'60','img'=>'reports','name_field_child'=>'etap_id','action'=>'list','module'=>'etapplayers'));
                 
$this->addFTL(array('name'=>'Місця з','type'=>'field','width'=>'9','name_field'=>'mesto_from'));
$this->addFTL(array('name'=>'Місця по','type'=>'field','width'=>'9','name_field'=>'mesto_to'));
  $this->addFTL(array('name'=>'Джерело гравців етапу','type'=>'out_key',
    'table'=>T_ETAPS, 'parent_field'=>'istochnik_posev','out_result_field'=>'name_etap',
    'width'=>'200','name_field'=>'istochnik_posev'));
  $this->addFTL(array('name'=>'К-сть ігор','type'=>'get_func',
          'function'=>'get_games',    'width'=>'100','name_field'=>'id','no_sql' => 1));
$this->addFTL(array('name'=>'Видалити','type'=>'delete','width'=>'40','name_field'=>'name_etap'));
//================================================================================================
// описание полей формы модуля при редактировании или добавления

 // $this->addFF(array('name'=>'Назва етапу','required'=>'Поле обов"язкове','name_field'=>'name_etap','size'=>'50'));
  $this->addFF(array('name'=>'Назва етапу','required'=>'Поле обов"язкове','name_field'=>'name_etap','size'=>'20',
      'type'=>'ProstSprEdit','id_spis'=>'6'));
    //  $this->addFF(array('name'=>'Список етапів','name_field_virt'=>'spisetap','type'=>'ProstSpr', 'id_spis'=>'6'));
    // Поле "Варіанти для команд" показываем для командных турниров
    if ($is_command)
        $this->addFF(array('name'=>'Варіанти для команд','name_field'=>'type_etap','type'=>'ProstSpr', 'id_spis'=>'5', 'bd_field'=>'type_etap'));

   else
      $this->addFF(array('name'=>'Варіанти','name_field'=>'type_etap','type'=>'ProstSpr','out_result_field'=>'name',
          'bd_field'=>'type_etap','table'=>T_ETAPS_NAME));


      $s_etap = !empty($etap_id) ? ' and id<>'.$etap_id : '';
      $aFirst = array('-1'=> array('id'=>0, 'name'=>'Гравці турніру'));
      $this->addFF(array('name'=>'Джерело гравців','name_field'=>'istochnik_posev','type'=>'out_key_prostspr',
          'out_result_field'=>'name_etap',
          'isFirstElem'=>$aFirst,
          'attr_elem'=>'type_etap',
          'where'=>'turnir_id='.poste('turnir_id').$s_etap ,
          'bd_field'=>'istochnik_posev','table'=>T_ETAPS));
  /*$this->addFF(array('name'=>'Варіанти ','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'type_etap',
                    'out_result_field'=>'name',
                    'bd_field'=>'type_etap',
                    'mess'=>'Вибиріть варіанти етапу',
                    'table'=>T_ETAPS_NAME,
                     'no_vubor' => '',
                    'width'=> '500', // ширина окна
                  //  'action'=>'groupsvariants',
                  //  'required'=>'Игрок  объязательно',
                 //   'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'table'=>T_PLAYERS ),
                    'module'=>'no_module',
                'descr_table'=>array(
                        array('name'=>'Тип етапу','name_field'=>'name','width'=>'250','filter'=>'1'),
                           
                    )
                    ));
*/
  $this->addFF(array('name'=>'Кількість спорстменів','required'=>'Поле обов"язкове','name_field'=>'cnt_people','size'=>'2','required_custom'=>'onlyNumber','maxlength'=>2));
      $field_show='show';
  if ($type_etap>1){
    $field_show='hide';
}

  //  s($type_etap);
 /*  $this->addFF(array('name'=>'Варінти груп','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'group_id',
                    'out_result_field'=>'itogo',
                    'bd_field'=>'group_id',
                    'mess'=>'Виберіть варіанти групи',
                    'table'=>T_TURNIR_VARIANTS,
                     'no_vubor' => '',
                      'post_string' => 'turnir_id='.poste('turnir_id'),
                    'width'=> '500', // ширина окна
                    'action'=>'groupsvariants',
                  //  'required'=>'Игрок  объязательно',
                 //   'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'table'=>T_PLAYERS ),
                    'module'=>'etaps',
                    'descr_table'=>array(
                        array('name'=>'Кол-во групп 1','name_field'=>'groups1','width'=>'50','filter'=>'0'),
                        array('name'=>'Кол-во человек 1','name_field'=>'people1','width'=>'20','filter'=>'0'),
                        array('name'=>'Кол-во групп 2','name_field'=>'groups2','width'=>'50','filter'=>'0'),
                        array('name'=>'Кол-во человек 2','name_field'=>'people2','width'=>'50','filter'=>'0'),
                        array('name'=>'Кол-во игр','name_field'=>'cntGames','width'=>'50','filter'=>'0'),
                        array('name'=>'Кол-во ГРУПП','name_field'=>'cntGroups','width'=>'50','filter'=>'0'),
                     
                    )
                    )); */
    $this->addFF(array('name'=>'Кількість груп',  'name_field'=>'cnt_grp','size'=>'2','required_custom'=>'onlyNumber','maxlength'=>3,'field_show'=>$field_show,'def'=>0));

  $this->addFF(array('name'=>'Місця з', 'required'=>'Поле обов"язкове', 'name_field'=>'mesto_from','size'=>'2','required_custom'=>'onlyNumber','maxlength'=>3));
 // $this->addFF(array('name'=>'Места по','required'=>'Поле объязательно','name_field'=>'mesto_to','size'=>'2','required_custom'=>'onlyNumber','maxlength'=>3));

    /*  $this->addFTL(array('name'=>'Група','type'=>'out_key_prostspr',
          'table'=>T_PLAYERS, 'parent_field'=>'player_id','out_result_field'=>'grp',
          'width'=>'20','name_field'=>'grp')); */
    /*  $this->addFF(array('name'=>'Джерело гравців','width'=>'250',
                    'type'=>'out_key',
                    'name_field'=>'istochnik_posev',
                    'out_result_field'=>'name_etap',
                    'bd_field'=>'istochnik_posev',
                    'mess'=>'Виберіть Джерело для цього етапу',
                    'table'=>T_ETAPS,
                     'no_vubor' => '',
                     'post_string' => 'turnir_id='.poste('turnir_id'),
                    'width'=> '500', // ширина окна
                    'action'=>'istochnikvariants',
                  //  'required'=>'Игрок  объязательно',
                 //   'speedsearch'=>array('min_letter'=>3,'result_fields_dop'=>array('id'),'table'=>T_PLAYERS ),
                    'module'=>'etaps',
                    'descr_table'=>array(
                        array('name'=>'Джерело','name_field'=>'name_etap','width'=>'50','filter'=>'0'),
                           
                    )
                    ));*/
      $field_show='show';
      if ($type_etap>1 || $ist_type_etap>1){
          $field_show='hide';
      }
   //   s('$field_show='.$field_show.' $ist_type_etap='.$ist_type_etap.' $type_etap='.$type_etap);
 $this->addFF(array('name'=>'Перенос ігор','type'=>'Checkbox','name_field'=>'is_perenos','bd_field'=>'is_perenos','field_show'=>$field_show));

// $this->addFF(array('name'=>'Сеять доп. игроков змейка или по рейтингу (умолч. по рейтингу)','type'=>'Checkbox','name_field'=>'is_reiting_zmeyka','bd_field'=>'is_reiting_zmeyka'));
                                
  $this->setTableModule(T_ETAPS);
  //$this->setTypeModule('tree');
  
  $_SESSION['etaps']['sort']='id';
  $_SESSION['etaps']['sort_type']='asc';
      $turnir_id = poste('turnir_id');
      $name_turnir =db_row('select name,dat  from `' . T_TURNIRS .
          '` where id=' . $turnir_id);
      $turnir_name = htmlspecialchars(stripslashes((string)$name_turnir['name']), ENT_QUOTES, 'UTF-8');
      $date = new DateTimeImmutable($name_turnir['dat']);
      $tdat = $date->format('d.m.Y');
  self::$nameZ='';
 self::$nameZList='Етапи турніру "'.$turnir_name.'" ('.$tdat.')';
 self::$nameZEdit='Редагування етапу';
 if ($_SESSION['gt']['user_rule']<=10)
 self::$submenu_list =array( 
   //filter' => array('module' => 'tovs'),
    'back' => array('module' => 'turnirs', 'action' => 'list'),
 //   'truck' => array('menu_name'=>'Отправить результаты','module' => 'reiting', 'action' => 'put_results', 'post' => 'id='.poste('turnir_id')),
 
  //  'filter' => array('menu_name'=>'Экспорт в Excel результатов', 'http' => 'modules/reiting/action/toexcel.bas?id='.poste('turnir_id')),
 
    );
 self::$aFilters=array(
    'name'=>'По имени',
    'articul'=>'По артикулам',
 );
 
self::InitMainMenu();
  self::$aParent[0]= array('name_field'=>'turnir_id',  'table'=>T_TURNIRS,  'type'=>'Hidden'    );
  self::$aParent[1]= ['name_field'=>'league_id', 'type'=>'Hidden'];
  }
    
}
?>
