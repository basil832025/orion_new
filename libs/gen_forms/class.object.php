<?php

// класс описующий струткру модуля, таблицы, формы, и как и что выводить
 class ObjectRT 
{  private static $instance = false; // экземляр данного объекта
    // тип модуля например tree вывод как дерева
   protected static $type_module;
   // таблица данного модуля основная
   protected static $table_module;
   // сокращенное название таблицы для сложных связей и объединений таблиц по умол "p"
   protected static $table_module_synonym='p';
   // массив полей таблицы БД 
   protected static $table_fields=array();
   // колонки для вывода в таблице
   protected static $aColList=array();
   // массив таблиц для сложных связей с главной таблицей 
   protected static $aTableUnion=array();
   // поля самой формы, которые должны выводиться
   protected static $aEditField=array();
   protected static $aSpecField=array();
   // заглавие модуля общее
   public static $nameZ='';
   public static $table_class='';
   public static $theed_tr_class='';
   // название для таблицы list
   protected static $nameZList='';
   // название в форме при редактировании   
   protected static $nameZEdit=''; 
   // подменю управляющих кнопок модуля для списка
   protected static $submenu_list=array(); 
   protected static $MainMenu=array(); 
   public static $MenuTurnirs=array();
   public static $MenuLeagues=array();
   protected static $subMenu2=array();
   // подменю укправляющих кнопок в режиме редактирования
   protected static $submenu_edit=array(); 
   // меню табс для модуля формы
   protected static $aTabs=array(); 
   // если модуль имеет родителя, то сязь идет через данный массив
   protected static $aParent=array(); 
   protected static $aFilters=array(); 
   protected static $name_list_parent_dop=''; 
   protected static $TableWidth = '100%';
   protected static $redirectUrl = [];
    public static function instance()
   {
    if (self::$instance == false) {
			self::$instance = new ObjectRT();
		}
		
		return self::$instance;
   }
   function LoadObject ()
   {global $aModulesSettings; // глобальный массив модульных настроеек
  // s('LoadObject');
    $module = SystemClass::getModule();
    $grp_module = !empty($aModulesSettings[$module]['path']) ? $aModulesSettings[$module]['path'] : '';    // запуск модуля
  
   //s(ROOT_A.'modules/'.(!empty($grp_module) ? $grp_module :$module).'/'  .'object.'.$module .'.php');
        if (file_exists(ROOT_A.'modules/'.(!empty($grp_module) ? $grp_module :$module).'/'  .'object.'.$module .'.php'))
                include_once ROOT_A.'modules/'.(!empty($grp_module) ? $grp_module :$module).'/'  .'object.'.$module .'.php';
         
         $classModule = $module.'Object'; // класс с которого нужно прочитать настройки
    if (class_exists($classModule)) {     
          $ob = new $classModule();
          $ob->init();
   }      
     ///$ob = $classModule::init()->init_();
     //s($ob);
    // $ob->init();       

   } 
   public function __construct()
   {
        
   }
   public function addFieldBD($Field='', $type='text', $tableParent='')
   {
        self::$table_fields[$Field] = array($type,$tableParent);
   }
   // добавляет описание поля колонки таблицы модуля
   public function addFTL($aField)
   {
         $aField['name']= !empty($aField['name']) ?  $aField['name'] : ''; //название колонки
         $aField['type']= !empty($aField['type']) ?  $aField['type'] : 'text'; //тип колонки или правила по каким выводить данную колонку 
         $aField['type']= $aField['type']=='field' ? 'text' : $aField['type']; //тип колонки или правила по каким выводить данную колонку 
         $aField['width']= !empty($aField['width']) ?  $aField['width'] : ''; // ширина колонки таблицы
         $aField['name_field']= !empty($aField['name_field']) ?  $aField['name_field'] : ''; // название колнки таблицы БД для запроса на отображение
         // модуль в который будем переходить при клике на данную ячейку 
         $aField['module']= !empty($aField['module']) ?  $aField['module'] : ''; 
         //количество изображений
         $aField['imgCnt']= !empty($aField['imgCnt']) ?  $aField['imgCnt'] : 1; 
         // поле табл БД куда перейдем поссылке
         $aField['name_field_child']= !empty($aField['name_field_child']) ?  $aField['name_field_child'] : '';
          $aField['bd_field_syn'] = '';
         if (!empty($aField['bd_field']) ) 
         {   
         $aBdField = explode('.',$aField['bd_field']);
         if (count($aBdField)>1){
            $aField['bd_field_syn'] = $aBdField[0];
            $aField['bd_field_short_name']=  $aBdField[1];       
         } else
             $aField['bd_field_short_name'] = $aField['bd_field'];
     
         }else
         {
              $aField['bd_field'] = $aField['name_field'];
              $aField['bd_field_short_name'] = $aField['bd_field'];
        } 
        if ($_SESSION['gt']['user_rule']>=10) {
          if (($aField['type']=='edit') or ($aField['type']=='delete')) return; 
        }
     //   s($aField);
         self::$aColList[] = $aField;
   }
   // поиск элемента массива $aColList по полю возвращается текущий подмассив
   public static function getColListPoField($field)
   {
        foreach (self::$aColList as $v){
            if ($v['name_field']==$field) return $v; // найден текущий элемент 
        }
        return false;
   }
     // поиск элемента массива $aEditField по полю возвращается текущий подмассив
   public static function getEditPoField($field)
   {
        foreach (self::$aEditField as $v){
            if ($v['name_field']==$field) return $v; // найден текущий элемент 
        }
        return false;
   } 
     public function addTabs($aField)
   {
         $aField['name']= !empty($aField['name']) ?  $aField['name'] : 'Общие'; //название колонки 
         self::$aTabs[] = $aField;
   }  
   // добавляет поле формы с его описнаием
   function addFF ($aField)
   {
   $aField['name']= !empty($aField['name']) ?  $aField['name'] : 'Назвa поля'; //название колонки
   $aField['name_field']= !empty($aField['name_field']) ?  $aField['name_field'] : ''; // поле формы
   // объязательность заполнения поля формы иначе выведется текст данной настройки
   $aField['required']= !empty($aField['required']) ?  $aField['required'] : '';
   // тип поля  взамисомости от типа, определяется как будет выводиться данное поле 
   $aField['type']= !empty($aField['type']) ?  $aField['type'] : 'text'; 
   $aField['type']= $aField['type']=='field' ? 'text' : $aField['type']; //тип колонки или правила по каким выводить данную колонку 
       
   $aField['readonly']= !empty($aField['readonly']) ?  $aField['readonly'] : ''; // не редактируемое поле
    //sort данное значение будет сортировать и делать принадлежность если меняется родитель
   $aField['sort']= !empty($aField['sort']) ?  $aField['sort'] : ''; 
   // название окна диалогового
   $aField['mess']= !empty($aField['mess']) ?  $aField['mess'] : ''; 
   $aField['length']= !empty($aField['length']) ?  $aField['length'] : ''; //лимит символов для полей текстовых
   // сколько будет колонок для textarea
   $aField['cols']= !empty($aField['cols']) ?  $aField['cols'] : 20; 
   $aField['rows']= !empty($aField['rows']) ?  $aField['rows'] : 7; 
   
 //  $aField['mess']= !empty($aField['mess']) ?  $aField['mess'] : ''; 
   // поле Бд,е сли пусто то берем сназвание поля, в основном одинаковые названия поля БД и формы
   $aField['bd_field_syn'] = 'p';
   if (!empty($aField['bd_field']) ) 
   {   
   $aBdField = explode('.',$aField['bd_field']);
   if (count($aBdField)>1){
        $aField['bd_field_syn'] = $aBdField[0];
        $aField['bd_field_short_name']=  $aBdField[1];       
   } else
        $aField['bd_field_short_name'] = $aField['bd_field'];
     
   }else
    {
       $aField['bd_field'] = $aField['name_field'];
       $aField['bd_field_short_name'] = $aField['bd_field'];
    } 
    $aField['bd_field_short_name'] =  (!empty ($aField['lang_type']) ? $aField['bd_field_short_name'].'_'.$aField['lang_type']:$aField['bd_field_short_name']);
      self::$aEditField[$aField['name_field']] = $aField;  
   }    
   public static function getTypeModule()
   {
        return self::$type_module;
   }
   public static function getTableModule()
   {
        return self::$table_module;
   }
   public static function getTablesUnion()
   {
       return self::$aTableUnion; 
   }
   public static function getTableModuleSynon()
   {
        return self::$table_module_synonym;
   }
   
    public static function getTableFields()
   {
        return self::$table_fields;
   }
    public static function getAColList()
   {
        return self::$aColList;
   }
    public static function getAEditField()
   {
        return self::$aEditField;
   }
   public static function getASpecField()
   {
        return self::$aSpecField;
   }
   
    public static function getNameZ()
   {
        return self::$nameZ;
   }
   public static function getTable_class()
   {
        return self::$table_class;
   }
    public static function getNameZList()
   {
        return self::$nameZList;
   }
     public static function setNameZ($nameZList)
     {
          self::$nameZList=$nameZList;
     }
     public static function setNameZList($nameZList)
     {
          self::$nameZList=$nameZList;
     }
    public static function getNameZEdit()
   {
        return self::$nameZEdit;
   }

     public static   function getSubmenuList()
   {
        return self::$submenu_list;
   }
     public static function setSubmenuList($submenu_list)
     {
         self::$submenu_list = $submenu_list;
     }

     public static function getMainMenu()
     {
         return self::$MainMenu;
     }
     public static function getmenuTurnirs()
   {
        return self::$MenuTurnirs;
   }
   public static function getmenuLeagues()
   {
        return self::$MenuLeagues;
   }
         public static function getSubMenu2()
   {
        return self::$subMenu2;
   } 
    function setSubMenu2($subMenu2)
  {
     $this->subMenu2 = $subMenu2;
  } 
   
     public static function getAFilters()
   {
        return self::$aFilters;
   }  
   
    public static function getSubmenuEdit()
   {
        return self::$submenu_edit;
   }  
       public static function getAParent()
   {
        return self::$aParent;
   }
   public static function getRedirectUrl()
   {
    return self::$redirectUrl;
   }
   public static function setRedirectUrl($redirectUrl)
   {
    self::$redirectUrl = $redirectUrl;
   }
   public static function getATabs()
   {
        return self::$aTabs;
   }
     public static function setTypeModule($type_module)
   {
         self::$type_module = $type_module;
   }
   public static function setTableModule($table_module,$synon='p')
   {
         self::$table_module = $table_module;
         self::$table_module_synonym = $synon;
         self::$aTableUnion[$synon] = array('table'=>$table_module, 'synon'=>$synon, 'aFealdLinks'=>'') ;
   }
    public static function setTableUnion($table_module='',$synon='t',$aFieldLinks=array())
   {    
        $arr = array('table'=>$table_module, 'synon'=>$synon, 'aFealdLinks'=>$aFieldLinks);
        self::$aTableUnion[$synon] = $arr ;
   } 
   
   public static function getTablePoSynon($synon)
   { 
    if (!empty($synon))
    // поиск названия таблицы по синониму таблицы в сложных объединениях
    return self::$aTableUnion[$synon];
    else
    return '';
   }
  public static function getTableWidth()
  {
    return self::$TableWidth;
  }   
  static function InitLeaguesMenu(){
      $league_id = poste('league_id');
      $is_team_league = 0;
      if (!empty($league_id)) {
          $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
      }

      self::$MenuLeagues = array(
          '2'=>
              array(
                  'name'=>'Інформація ',
                  'href'=>'#infoleagues-show-league_id='.poste('league_id'),
                  'module'=>'infoleagues',
              ),
          '3'=>
              array(
                  'name'=>'Турніри ліги ',
                  'href'=>'#turnirs-list-league_id='.poste('league_id'),
                  'module'=>'turnirs',
              ),
      );

      if (!$is_team_league) {
          self::$MenuLeagues['4'] = array(
              'name'=>'Топ гравців ліги',
              'href'=>'#topplayersleague-list-league_id='.poste('league_id'),
              'module'=>'topplayersleague',
          );
      } else {
          self::$MenuLeagues['4'] = array(
              'name'=>'Таблиця ліг',
              'href'=>'#teamleaguetable-list-league_id='.poste('league_id'),
              'module'=>'teamleaguetable',
          );
      }
  }
  static function InitMainMenu()
    {
     //   $name_players = $_SESSION['is_mobile']  ? 'Гравці' : 'Гравці';
        $name_players = 'Гравці';
        $league_id = (int)poste('league_id');
        $hrefLeag = $league_id > 0 ? '&league_id='.$league_id : '';
        $turnir_id = (int)poste('turnir_id');
        if ($turnir_id <= 0) {
            $turnir_id = (int)poste('id');
        }
        $date_raschet ='';
        // Проверяем, является ли лига командной
        $is_team_league = 0;
        if ($league_id > 0) {
            $is_team_league = (int)db_field('SELECT is_team_league FROM `bs_leagues` WHERE id='.$league_id, 'is_team_league');
        }
        
        // Если командная лига, меняем название и модуль
        if ($is_team_league) {
            if ($turnir_id > 0) {
                $sql = 'select date_raschet from bs_turnirs where id='.$turnir_id;
                $date_raschet = db_field($sql,'date_raschet');
            }
            $name_players = 'Команди';
            $module_players = 'turnirsteams'; // будет создан отдельный модуль для команд в турнире
        } else {
            $module_players = 'turnirsplayers';
        }
        
      if ($_SESSION['gt']['user_rule']<10) {
          self::$MenuTurnirs = array(
              '2' =>
                  array(
                      'name' => 'Результати ',
                       'href' => '#etapresult-show-turnir_id=' . $turnir_id . $hrefLeag,
                      'module' => 'etapresult',
                  ),
              '3' =>
                  array(
                      'name' => 'Ігри ',
                       'href' => '#reiting-list-turnir_id=' . $turnir_id . $hrefLeag,
                      'module' => 'reiting',
                  ),
              '4' =>
                  array(
                      'name' => $name_players,
                       'href' => '#' . $module_players . '-list-turnir_id=' . $turnir_id . $hrefLeag,
                      'module' => $module_players,
                  ),


              '5' =>
                  array(
                      'name' => 'Етапи ',
                       'href' => '#etaps-list-turnir_id=' . $turnir_id . $hrefLeag,
                      'module' => 'etaps',
                  ),
              '6' =>
                  array(
                      'name' => 'Столи ',
                       'href' => '#tables-show-turnir_id=' . $turnir_id . $hrefLeag,
                      'module' => 'tables',
                  ),
          );
      }
                      else
                      self::$MenuTurnirs = array(
                          '2'=>
                              array(
                                  'name'=>'Результати ',
                                   'href'=>'#etapresult-show-turnir_id='.$turnir_id.$hrefLeag,
                                  'module'=>'etapresult',
                              ),
                          '3'=>
                              array(
                                  'name'=>'Ігри ',
                                   'href'=>'#reiting-list-turnir_id='.$turnir_id.$hrefLeag,
                                  'module'=>'reiting',
                              ),
                          '4'=>
                        array(
                                'name'=>$name_players,
                                'href'=>'#'.$module_players.'-list-turnir_id='.$turnir_id.$hrefLeag,
                                'module'=>$module_players,
                        ),

                        

                          '5'=>
                              array(
                                  'name'=>'Столи ',
                                   'href'=>'#tables-show-turnir_id='.$turnir_id.$hrefLeag,
                                  'module'=>'tables',
                              ),
                      );

        if (!empty($date_raschet)) {
            self::$MenuTurnirs[] =  [
                'name' => 'Гравці',
                'href' => '#turnirsplayers-list-turnir_id=' . $turnir_id . $hrefLeag,
                'module' => 'turnirsplayers',
            ];
        }
    }
}

?>
