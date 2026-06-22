<?php
require_once dirname(__DIR__, 2) . '/modules/teamplayers/func/func.teamplayers.php';
// класс возвращает и обрабатывает для вывода поля формы
class ListTable extends ActionModule
{
 public $aColList = array(); // массив полей таблицы модуля
 public $page_id = array(); // массив полей таблицы модуля
 private $sql='';
 protected $thVdata = array(); // массив настроек для одного поля
 private $postButton = '';
 private $cntElemsTables = 0;
 private $page_link = 5;
 private $page_number = 0;
 private $page_count = 0;
 private $page_groups = 0;
 private $TableWidth = '100%';
 private $number_line =0;
 private $html ='';
 private $field_result  ='';
 private $aFilters  ='';
 private $nameZList  ='';
 private $theed_tr_class  ='';
 private $table_class  ='';
 public $id_aParent =0;
 private $field_result_name  ='';
 private $nameZ  ='';
 private $name_list_parent = '';
 private $name_list_parent_dop = '';
 private $wintype  ='';
 private $page_items  ='';
 private $filter = '';

 
 public function __construct() // конструктор
  {
   //   wLog('тест лог');
    $this->aColList = ObjectRT::getAColList(); 
    $this->subMenu=ObjectRT::getSubmenuList();
    $this->mainMenu=ObjectRT::getMainMenu();
    $this->module= SystemClass::getModule();
    $this->id = poste('id');
    $this->aParent = ObjectRT::getAParent(); 
    $this->table_module= ObjectRT::getTableModule(); 
    $this->nameZ=ObjectRT::getNameZ();
    $this->table_class=ObjectRT::getTable_class();
    $this->theed_tr_class=ObjectRT::$theed_tr_class;
    $this->nameZList=ObjectRT::getNameZList();
    $this->type_module = ObjectRT::getTypeModule();
    $this->aFilters = ObjectRT::getAFilters();
    $this->TableWidth = ObjectRT::getTableWidth();
 //   $id_parent = !empty($this->aParent[0]['name_field']) ? poste($this->aParent[0]['name_field']) :'';
    $this->postButton .= SystemClass::getPost_return_noMA();

    $sort = SystemClass::getAPost('sort_cols');
    $this->wintype = SystemClass::getAPost('wintype') ? SystemClass::getAPost('wintype') : SystemClass::getAFormPost('wintype'); // если это окно
    $this->field_result = SystemClass::getAPost('field_result') ? SystemClass::getAPost('field_result') :  SystemClass::getAFormPost('field_result'); // если это окно? то поле от которого пришел запрос
    $this->field_result_name = SystemClass::getAPost('field_result_name') ? SystemClass::getAPost('field_result_name') :  SystemClass::getAFormPost('field_result_name'); // если это окно? то поле от которого пришел запрос
    
    $filter = SystemClass::getAFormPost('filter_s');
    $clear_filter = SystemClass::getAPost('clear_filter');
    $filter_field = SystemClass::getAFormPost('filter_field');
    $filter_field_bd = SystemClass::getAFormPost('bd_field');
     $is_first_filter = SystemClass::getAFormPost('is_first');
    $page_items = SystemClass::getAPost('page_items');
    $page_number= SystemClass::getAPost('page_number');
    if ($sort){
       $_SESSION[$this->module]['sort_type']=(!empty($_SESSION[$this->module]['sort_type']) && $_SESSION[$this->module]['sort_type']=='asc' && $_SESSION[$this->module]['sort']==$sort) ? 'desc' : 'asc';
       $_SESSION[$this->module]['sort']=$sort ? $sort : (!empty($_SESSION[$this->module]['sort']) ? $_SESSION[$this->module]['sort'] : '');
        $_SESSION[$this->module]['sort_default'] = !empty($_SESSION[$this->module]['sort_default']) ? $_SESSION[$this->module]['sort_default'] : '';

        $_SESSION[$this->module]['page_number'] = 1;
     }else{
        $_SESSION[$this->module]['sort'] = !empty($_SESSION[$this->module]['sort']) ? $_SESSION[$this->module]['sort'] : '';
        $_SESSION[$this->module]['sort_type'] = !empty($_SESSION[$this->module]['sort_type']) ? $_SESSION[$this->module]['sort_type'] : 'asc';
        $_SESSION[$this->module]['sort_default'] = !empty($_SESSION[$this->module]['sort_default']) ? $_SESSION[$this->module]['sort_default'] : '';


     }
     $_SESSION[$this->module]['filters']['filter_s'] = ($filter || $filter_field ) ? $filter : (!empty($_SESSION[$this->module]['filters']['filter_s']) ? $_SESSION[$this->module]['filters']['filter_s'] :'');
     $_SESSION[$this->module]['filters']['filter_s'] = ($clear_filter  ) ? '' : $_SESSION[$this->module]['filters']['filter_s'];
     $_SESSION[$this->module]['filters']['filter_field'] = $filter_field ? $filter_field : (!empty($_SESSION[$this->module]['filters']['filter_field']) ? $_SESSION[$this->module]['filters']['filter_field'] :'');    
     $_SESSION[$this->module]['filters']['filter_field_bd'] = $filter_field_bd ? $filter_field_bd : (!empty($_SESSION[$this->module]['filters']['filter_field_bd']) ? $_SESSION[$this->module]['filters']['filter_field_bd'] :'');
     $_SESSION[$this->module]['filters']['is_first_filter'] = $is_first_filter || $filter_field ? $is_first_filter : (!empty($_SESSION[$this->module]['filters']['is_first_filter']) ? $_SESSION[$this->module]['filters']['is_first_filter'] :'');    
     
    $_SESSION[$this->module]['page_items']= $page_items ? $page_items : (!empty($_SESSION[$this->module]['page_items']) ? $_SESSION[$this->module]['page_items'] : PAGE_ITEMS);
    //$_SESSION[$this->module]['page_items']= PAGE_ITEMS;
    $_SESSION[$this->module]['page_number']= $page_number ? $page_number : (!empty($_SESSION[$this->module]['page_number']) ? $_SESSION[$this->module]['page_number'] : 1);
   if ($_SESSION['is_mobile'])

       $this->page_link = PAGE_GROUPS_MOB;
   else
    $this->page_link = PAGE_GROUPS;
    $post_return = $this->module.'-list-'.$this->postButton;
    SystemClass::setPost_return($post_return);
    //  если это окно, то выводим те поля которые мы указали ранее в сессии

    if (!empty($this->wintype) && !empty($this->field_result) && !empty($_SESSION['wintype'][$this->module][$this->field_result])){
    // $this->aColList =  ;
    // $this->aColList[] =  ;
    $this->aColList =  array_merge(array(0 => array('name'=>'Виберіть','type'=>'vibor','width'=>'50')),$_SESSION['wintype'][$this->module][$this->field_result]['descr_table']);

    }
    // по деймствию
    $actionmany=poste('actionmany');

    if (!empty($this->wintype) && !empty($_SESSION['wintype'][$this->module][$actionmany])){

     $this->aColList = array_merge(array(0 => array('name'=>'Виберіть','type'=>'vibor','width'=>'50')), $_SESSION['wintype'][$this->module][$actionmany]);

    }
  //  s($_SESSION);
  //  $this->aSpecField = Object::gets
   // $this->aData = $aData;

 // wLog($_SESSION);
  } 
 public function init()
 {  
 }
 function list_show($sql='')
 {

    $this->page_id = post('page_id') ? post('page_id') : 1;
    // если описание полей таблицы присутсвет то будем обрабатывать их в классе
            if ($this->aColList) {
            $this->sql_list($sql);
            $this->shablon_list_header();
            $this->list_header();
           // $this->list_header_filter();

            $this->data_list();
            $this->Java_script.=' fancyImageShow();';

            $this->content .= '</tr></table></div>';
            $this->content .= $this->getHtmlPagging();
//wLog($this->content);
            if (!empty($this->subMenu)) {

                if (empty($this->subMenu['add']))
                if ($_SESSION['gt']['user_rule']<10) {
                    $this->subMenu['add'] = array(
                        'module' => $this->module,
                        'action' => 'add',
                        'post' => '');
                        }
              /*  if (!empty($this->subMenu['filter'])) {
                   
                    $this->subMenu['filter']['class'] = 'filter_trigger';
                    $this->getFilter((!empty($this->subMenu['filter']['module']) ? $this->
                        subMenu['filter']['module'] : $this->module)); // получить строку с формой поиска
                }*/
                if ($_SESSION['gt']['user_rule']<10) 
            $this->subMenu['add']['post'].=$this->postButton;
            }

          //  $this->submenu = $this->subMenu;


        }   
 }
   // получить строку с формой поиска
  /*  function getFilter($module)
    {
        if (!empty($this->aFilters)) {
            $this->filter = '<div class="filter_panel"><form id="form_filter" action="?" method="post" enctype="multipart/form-data" >
       <div align="right"><a href="#" class="close_filter"><img height="20px" border="0" src="img/minus.png"></a></div>
       <div style="color:white;
       :14px;">Форма поиска: </div>
       <div>
       <input type="text" name="form[filter_s]" value="' . (!empty($_SESSION[$module]['filters']['filter_s']) ?
                $_SESSION[$module]['filters']['filter_s'] : '') .
                '" ><input type="button" value="Найти" form_name="form_filter" module="' . (!
                empty($this->subMenu['filter']['module']) ? $this->subMenu['filter']['module'] :
                $this->module) . '" action="' . (!empty($this->subMenu['filter']['action']) ?
                $this->subMenu['filter']['action'] : 'list') . '"  class="ajax_send">
       <input type="button" value="Отменить фильтр" class="ajax_send" module="' .
                $this->module . '" action="clear_filter"> 
       </div><div style="color:white">
       ' . $this->filter .= 'Выбирите по каким полям будет идти поиск:<br />';
            $fields = '';
            foreach ($this->aFilters as $k => $v) {
                $fields .= $k . ';';
                $this->filter .= '<input type="checkbox" name="form[' . $k .
                    ']" checked="checked"> <span>' . $v . '</span><br />';
            }
            $this->filter .= '<input type="hidden" name="form[fealds]" value="' . $fields .
                '">';
            $this->filter .= '<input type="hidden" name="form[module_parent]" value="' . $module .
                '">';
            $this->filter .= '</div></form></div>';

            $this->content .= $this->filter;

            $this->Java_script .= 'filters();';
        }
    }
    */
        function getFilter($module,$field,$name,$bd_field='')
    {
            $this->filter = '<div class="hide_elem filter_panel filter_panel_'.$field.'">
            <form id="form_filter_'.$field.'" class="filternameSS" onsubmit="return false;" action="#'.$this->module.'-list" method="post" enctype="multipart/form-data" >
       <div align="right"><a href="#" class="close_filter"><img height="18px" border="0" src="img/minus.png"></a></div>
       <div style="color:white;font-size:10px;">Фільтр по "'.$name.'": </div>
       <div>
       <input type="hidden" name="form[filter_field]" value="'.$field.'">
       <input type="hidden" name="form[bd_field]" value="'.$bd_field.'">
         <input type="text" class="filterNameS" name="form[filter_s]" value="' . (!empty($_SESSION[$this->module]['filters']['filter_s']) ?
                $_SESSION[$this->module]['filters']['filter_s'] : '') .
                '" ><input type="button" value="Знайти" form_name="form_filter_'.$field.'" module="' . (!
                empty($this->subMenu['filter']['module']) ? $this->subMenu['filter']['module'] :
                $module) . '" action="' . (!empty($this->subMenu['filter']['action']) ?
                $this->subMenu['filter']['action'] : 'list') . '"   class="ajax_send">
       <input type="button" value="Відмінити фільтр" class="ajax_send" module="' .
                $this->module . '" action="list" post_string="clear_filter=1"> 
       </div><div style="color:white">
       ' ;
            $fields = '';

                $this->filter .= '<input type="checkbox" name="form[is_first]" '.($_SESSION[$this->module]['filters']['is_first_filter'] ? 'checked="checked"': '').' > <span>Шукати з 1 символу?</span><br />';
     $this->filter .= '<input type="hidden" name="form[module_parent]" value="' . $this->module .
                '">';
            $this->filter .= '</div></form></div>';
            $this->Java_script .= 'filters();';
            return  $this->filter;

    }

    function list_header()
    { // пройдемся по заголовкам таблицы
        $desctop =  ($_SESSION['width_body']<768) ? 0 : 1;

        foreach ($this->aColList  as $key => $val) {
            $name = $width_field = $field = '';
            $type_field = 'field';
            if (empty($val['name_mob']) && empty($val['name'])) continue;

            $width =  ((!empty($val['width']) && $desctop) ? 'style="width:' . $val['width'] .
                'px"' : ((!empty($val['width_mob']) && !$desctop) ? 'style="width:' . $val['width_mob'] .
                'px"' : ''));
            //
            $rorate_90 = !empty($val['rorate_90']) ? 'rotate-sm-90' : '';
            $name = !empty($val['name_mob']) && !$desctop ? $val['name_mob'] : (!empty($val['name']) ? $val['name'] : '');
            $allow_sort = (!empty($val['name_field']) && empty($val['no_sort']) && empty($val['no_sql']));
            $this->content .= '<th  class="align-middle '.(!empty($val['classAlign']) ?  ''.$val['classAlign'] : 'text-center').'"  ' .$width.'>'
                    .(!empty($val['filter']) ? '<span class="filter" '.(!empty($val['name_field']) ? ' filter_name="'.$val['name_field'].'"' : '').'></span>':'') 
                    .'<span '. ($allow_sort ? 'class="sort_cols '.$rorate_90.'"  sort="'.$val['name_field'].'"' : '').' >'. $name
                    .'<span '.(!empty($_SESSION[$this->module]['sort']) && $allow_sort && ($val['name_field']==$_SESSION[$this->module]['sort']) ? 
                    'class="'.$_SESSION[$this->module]['sort_type'].'" ': '').'></span></span>'.(!empty($val['filter']) ? $this->getFilter( $this->module,$val['name_field'],$val['name'],(!empty($val['bd_field'])? $val['bd_field'] :'')):'').'</th>';

        }
        $this->content.='
   </thead>';
    }
    function data_list()
    {
        //  для оста плюсов минусов пока если есть родитель
   $page_id = poste('page_id');
    $page_id= !empty($page_id) ? '&page_id='.$page_id : '';
        if (!empty($this->aData)) {
            $num = 0;

           $fir =1;
           $arrReturn_id_val=[];
            foreach ($this->aData as $kData => $vData) {

                    if ($_SESSION[$this->module]['page_items']>1) {
                    $num++;
                   // $this->number_line = $num + ($_SESSION[$this->module]['page_number'] - 1) * $_SESSION[$this->module]['page_items'];
                    $this->number_line = $num + ($this->page_number - 1) * $_SESSION[$this->module]['page_items'];
                } else
                    $this->number_line=$this->number_line+1;
                if ($this->aColList) {
                    // Для модуля reiting: определяем, является ли игра игрой команды или игрой игроков
                    $is_player_game = false;
                    $match_id = '';
                    $row_classes = 'align-middle';
                    $row_style = '';
                    
                    if ($this->module == 'reiting') {
                        // Проверяем наличие match_id и pair_number
                        $match_id = !empty($vData['match_id']) ? $vData['match_id'] : '';
                        $pair_number = !empty($vData['pair_number']) ? (int)$vData['pair_number'] : 0;
                        
                        // Если есть match_id и pair_number > 0, это игра игроков (вложенная)
                        if (!empty($match_id) && $pair_number > 0) {
                            $is_player_game = true;
                            $row_classes .= ' player-game-row';
                            // Игры игроков скрыты по умолчанию и будут показаны при клике на кнопку раскрытия
                            $row_style = ' style="display:none; background-color:#f0f8ff !important; border-left:4px solid #007bff; padding-left:30px;"';
                            $row_style .= ' data-match-id="'.htmlspecialchars($match_id).'" data-pair-number="'.$pair_number.'"';
                        } else {
                            // Это игра команды (верхний уровень)
                            $row_classes .= ' team-match-row';
                            // Сохраняем match_id в атрибуте для связи с играми игроков
                            if (!empty($match_id)) {
                                $row_style = ' data-match-id="'.htmlspecialchars($match_id).'"';
                            } else {
                                // Если match_id пустой, пытаемся сформировать его для связи
                                $etap_id = !empty($vData['etap_id']) ? (int)$vData['etap_id'] : 0;
                                $pl_id_1 = !empty($vData['pl_id_1']) ? (int)$vData['pl_id_1'] : 0;
                                $pl_id_2 = !empty($vData['pl_id_2']) ? (int)$vData['pl_id_2'] : 0;
                                if ($etap_id > 0 && $pl_id_1 > 0 && $pl_id_2 > 0) {
                                    $min_team = min($pl_id_1, $pl_id_2);
                                    $max_team = max($pl_id_1, $pl_id_2);
                                    $generated_match_id = 'match_'.$etap_id.'_'.$min_team.'_'.$max_team;
                                    $row_style = ' data-match-id="'.htmlspecialchars($generated_match_id).'"';
                                } else {
                                    $row_style = '';
                                }
                            }
                            // Управляем фоном строк команд вручную для правильного чередования
                            $is_odd_row = ($this->number_line % 2 == 1);
                            $bg_style = $is_odd_row ? 'background-color:rgba(0,0,0,.05) !important;' : 'background-color:transparent !important;';
                            if (!empty($row_style) && strpos($row_style, 'style=') === false) {
                                $row_style .= ' style="'.$bg_style.'"';
                            } elseif (empty($row_style)) {
                                $row_style = ' style="'.$bg_style.'"';
                            }
                        }
                    } else {
                        // Для других модулей (turnirsteams и т.д.) - стандартная логика
                        $team_id_attr = ($this->module == 'turnirsteams' && !empty($vData['player_id'])) ? $vData['player_id'] : $vData['id'];
                        $row_classes .= ' team-row';
                        $row_style = ' data-team-id="'.$team_id_attr.'"';
                        // Управляем фоном строк команд вручную для правильного чередования (исключая строки игроков)
                        $is_odd_row = ($this->number_line % 2 == 1);
                        $row_style .= $is_odd_row ? ' style="background-color:rgba(0,0,0,.05) !important;"' : ' style="background-color:transparent !important;"';
                    }
                    
                    $this->content .= '<tr class="'.$row_classes.'"'.$row_style.'>';

                
                    // ВАЖНО: Инициализируем поля с no_sql=1 и default_value перед их использованием
                    // Это предотвращает "Undefined index" ошибки для вычисляемых полей (например, cnt_players)
                    // НО: Не перезаписываем значения, которые уже установлены триггером (например, cnt_players из after.list.php)
                    foreach ($this->aColList as $val) {
                        if (!empty($val['no_sql']) && !empty($val['default_value']) && !empty($val['name_field'])) {
                            $field_name = $val['name_field'];
                            // ВАЖНО: Используем array_key_exists вместо isset, чтобы не перезаписывать значение 0
                            // isset() вернет false для значения 0, но array_key_exists() вернет true, если ключ существует
                            if (!array_key_exists($field_name, $vData)) {
                                $vData[$field_name] = $val['default_value'];
                            }
                        }
                    }
                    
                    // нужно 2 раза прогнать для кнопки выбор
                    foreach ($this->aColList as $val) {
                        $field = !empty($val['name_field']) ? $val['name_field'] : '';
                          if (!empty($val['return_id_val'])) {
                              // ВАЖНО: Проверяем наличие поля перед обращением, чтобы избежать "Undefined index" ошибок
                              $arrReturn_id_val[$val['return_id_val']] = isset($vData[$field]) ? $vData[$field] : '';
                          }
                
                        }

                        // Пройдемся по заголовкам таблицы
                    foreach ($this->aColList as $val) {

                        $name = $width_field = $field = '';
                        $type_field = !empty($val['type']) ? strtolower($val['type']) : 'text';
                        $type_field = $type_field!='field' ? $type_field : 'text';
                        $field = !empty($val['name_field']) ? $val['name_field'] : '';
                         if ($type_field=='onlybd') continue;
                         if ($type_field=='onlybd_ProstSpr') continue;
                         if ($type_field=='onlybd_out_key') continue;

                        switch ($type_field) {
                            case 'number':
                                // Для модуля reiting: если это игра игроков, показываем пустую ячейку или символ отступа
                                if ($this->module == 'reiting' && $is_player_game) {
                                    // Для игр игроков показываем пустую ячейку, чтобы сохранить выравнивание колонок
                                    $this->content .= '<td align="center"></td>';
                                } else {
                                    $class = (!empty($val['class']) ? 'class="'.$val['class'].'"' : '') ;
                                    $this->content .= '<td align="center" ><span '.$class.'>' . $this->number_line . '</span></td>';
                                }
                                break;
                            case 'expand_match':
                                // Кнопка раскрытия/сворачивания для игр команд (модуль reiting)
                                // Всегда создаем ячейку для сохранения выравнивания колонок
                                if ($this->module == 'reiting' && !$is_player_game) {
                                    // Для игр команд: проверяем наличие match_id или определяем его из данных
                                    $current_match_id = $match_id;
                                    $etap_id = !empty($vData['etap_id']) ? (int)$vData['etap_id'] : 0;
                                    
                                    // Если match_id пустой, но есть etap_id и команды, формируем match_id
                                    if (empty($current_match_id) && $etap_id > 0) {
                                        // Пытаемся получить team_a_id и team_b_id из разных источников
                                        $team_a_id = 0;
                                        $team_b_id = 0;
                                        
                                        if (!empty($vData['team_a_id'])) {
                                            $team_a_id = (int)$vData['team_a_id'];
                                        } elseif (!empty($vData['pl_id_1'])) {
                                            // Проверяем, является ли pl_id_1 командой
                                            $is_team_1 = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$vData['pl_id_1'], 'is_team');
                                            if (!empty($is_team_1)) {
                                                $team_a_id = (int)$vData['pl_id_1'];
                                            }
                                        }
                                        
                                        if (!empty($vData['team_b_id'])) {
                                            $team_b_id = (int)$vData['team_b_id'];
                                        } elseif (!empty($vData['pl_id_2'])) {
                                            // Проверяем, является ли pl_id_2 командой
                                            $is_team_2 = db_field('SELECT is_team FROM `'.T_PLAYERS.'` WHERE id='.(int)$vData['pl_id_2'], 'is_team');
                                            if (!empty($is_team_2)) {
                                                $team_b_id = (int)$vData['pl_id_2'];
                                            }
                                        }
                                        
                                        if ($team_a_id > 0 && $team_b_id > 0) {
                                            // Формируем match_id в том же формате, что и в start_team_game.php
                                            $min_team = min($team_a_id, $team_b_id);
                                            $max_team = max($team_a_id, $team_b_id);
                                            $current_match_id = 'match_'.$etap_id.'_'.$min_team.'_'.$max_team;
                                        }
                                    }
                                    
                                    // Ищем игры игроков по match_id или по etap_id и командам
                                    $cnt_player_games = 0;
                                    if (!empty($current_match_id)) {
                                        // Ищем по match_id
                                        $cnt_player_games = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` WHERE match_id="'.addslashes($current_match_id).'" AND pair_number > 0', 'cnt');
                                        $cnt_player_games = !empty($cnt_player_games) ? (int)$cnt_player_games : 0;
                                    } elseif ($etap_id > 0) {
                                        // Если match_id не определен, ищем по etap_id и командам
                                        $pl_id_1 = !empty($vData['pl_id_1']) ? (int)$vData['pl_id_1'] : 0;
                                        $pl_id_2 = !empty($vData['pl_id_2']) ? (int)$vData['pl_id_2'] : 0;
                                        if ($pl_id_1 > 0 && $pl_id_2 > 0) {
                                            // Ищем игры игроков, где match_id содержит этап и одну из команд
                                            $cnt_player_games = db_field('SELECT COUNT(*) as cnt FROM `'.T_REITING.'` WHERE etap_id='.$etap_id.' AND pair_number > 0 AND (match_id LIKE "match_'.$etap_id.'_'.$pl_id_1.'_%" OR match_id LIKE "match_'.$etap_id.'_%_'.$pl_id_1.'" OR match_id LIKE "match_'.$etap_id.'_'.$pl_id_2.'_%" OR match_id LIKE "match_'.$etap_id.'_%_'.$pl_id_2.'")', 'cnt');
                                            $cnt_player_games = !empty($cnt_player_games) ? (int)$cnt_player_games : 0;
                                            
                                            // Если нашли игры, формируем match_id для использования в JavaScript
                                            if ($cnt_player_games > 0 && empty($current_match_id)) {
                                                $min_team = min($pl_id_1, $pl_id_2);
                                                $max_team = max($pl_id_1, $pl_id_2);
                                                $current_match_id = 'match_'.$etap_id.'_'.$min_team.'_'.$max_team;
                                            }
                                        }
                                    }
                                    
                                    if ($cnt_player_games > 0 && !empty($current_match_id)) {
                                        $match_id_escaped = addslashes($current_match_id);
                                        $btn_class = 'match-expand-btn';
                                        $btn_style = 'cursor:pointer; font-size:16px; font-weight:bold; color:#007bff;';
                                        $btn_text = '▶';
                                        $btn_title = 'Показати ігри гравців ('.$cnt_player_games.')';
                                        
                                        $this->content .= '<td align="center" title="'.$btn_title.'">';
                                        // JavaScript код находит строки player-game-row сразу после командной игры
                                        // Используем nextUntil для поиска только строк между текущей командной игрой и следующей командной игрой или концом таблицы
                                        $this->content .= '<span class="'.$btn_class.'" data-match-id="'.$current_match_id.'" style="'.$btn_style.'" onclick="(function(){var matchId=\''.$match_id_escaped.'\'; var $btn=$(this); var $teamRow=$btn.closest(\'tr\'); var $allPlayerRows=$(\'tr.player-game-row\').filter(function(){return $.trim($(this).attr(\'data-match-id\')||\'\')===matchId;}); if($allPlayerRows.length>0){$allPlayerRows.detach(); $teamRow.after($allPlayerRows); $allPlayerRows=$(\'tr.player-game-row\').filter(function(){return $.trim($(this).attr(\'data-match-id\')||\'\')===matchId;}); var visibleCount=$allPlayerRows.filter(\':visible\').length; if(visibleCount>0){$allPlayerRows.slideUp(200,function(){$btn.html(\'▶\');});}else{$allPlayerRows.slideDown(200,function(){$btn.html(\'▼\');});}}}).call(this);">▶</span>';
                                        $this->content .= '</td>';
                                    } else {
                                        // Нет игр игроков - пустая ячейка
                                        $this->content .= '<td align="center"></td>';
                                    }
                                } else {
                                    // Для игр игроков или других случаев - пустая ячейка (чтобы сохранить выравнивание колонок)
                                    $this->content .= '<td align="center"></td>';
                                }
                                break;
                            case 'expand_collapse':
                                // Кнопка раскрытия/сворачивания для команд с игроками
                                // Для turnirsteams player_id это ID команды, для teams - id команды
                                if ($this->module == 'turnirsteams') {
                                    // Для turnirsteams нужно получить player_id из таблицы T_TURNIR_PLAYERS
                                    // так как в $vData['player_id'] может быть название команды после обработки out_key
                                    if (!empty($vData['id'])) {
                                        // Получаем player_id напрямую из базы по id записи в T_TURNIR_PLAYERS
                                        $team_id = db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.$vData['id'], 'player_id');
                                        // Если не получилось, пробуем взять из $vData, но проверяем, что это число
                                        if (empty($team_id) || !is_numeric($team_id)) {
                                            // Если $vData['player_id'] - число, используем его
                                            if (!empty($vData['player_id']) && is_numeric($vData['player_id'])) {
                                                $team_id = $vData['player_id'];
                                            } else {
                                                // Если это не число (название команды), получаем ID по названию
                                                $team_name = !empty($vData['player_id']) ? addslashes($vData['player_id']) : '';
                                                if (!empty($team_name)) {
                                                    $team_id = db_field('SELECT id FROM `'.T_PLAYERS.'` WHERE name="'.$team_name.'" AND is_team=1 LIMIT 1', 'id');
                                                }
                                            }
                                        }
                                    } else {
                                        $team_id = !empty($vData['player_id']) && is_numeric($vData['player_id']) ? $vData['player_id'] : '';
                                    }
                                } else {
                                    $team_id = $vData['id'];
                                }
                                // Проверяем, есть ли игроки в команде (только если team_id валидный)
                                $cnt_players = 0;
                                if (!empty($team_id) && is_numeric($team_id)) {
                                    $league_id_context = 0;
                                    if ($this->module == 'turnirsteams') {
                                        $turnir_id_context = (int)poste('turnir_id');
                                        if ($turnir_id_context <= 0 && !empty($vData['turnir_id'])) {
                                            $turnir_id_context = (int)$vData['turnir_id'];
                                        }
                                        $league_id_context = teamplayers_resolve_league_id(poste('league_id'), $turnir_id_context);
                                    }
                                    $cnt_players = teamplayers_count($team_id, $league_id_context);
                                }
                                $cnt_players = !empty($cnt_players) ? (int)$cnt_players : 0;
                                
                                // Всегда показываем кнопку, но она активна только если есть игроки
                                // При загрузке страницы все строки игроков скрыты (display:none), поэтому стрелка должна быть ▶
                                $btn_class = $cnt_players > 0 ? 'team-expand-btn' : 'team-expand-btn-disabled';
                                $btn_style = $cnt_players > 0 ? 'cursor:pointer; font-size:16px; font-weight:bold; color:#007bff;' : 'cursor:not-allowed; font-size:16px; color:#ccc;';
                                // Начальное состояние всегда ▶, так как при загрузке строки игроков скрыты (display:none)
                                // После первого клика состояние будет переключаться через JavaScript
                                $btn_text = '▶';
                                $btn_title = $cnt_players > 0 ? 'Показати гравців ('.$cnt_players.')' : 'Немає гравців';
                                
                                $this->content .= '<td align="center" title="'.$btn_title.'">';
                                if ($cnt_players > 0 && !empty($team_id) && is_numeric($team_id)) {
                                    // Добавляем onclick для немедленной работы без зависимости от глобальных обработчиков
                                    // Используем экранирование для правильной работы в JavaScript
                                    $team_id_escaped = addslashes($team_id);
                                    $this->content .= '<span class="'.$btn_class.'" data-team-id="'.$team_id.'" style="'.$btn_style.'" onclick="(function(){var teamId=\''.$team_id_escaped.'\'; var $rows=$(\'.team-player-row[data-team-id=\\\''.$team_id_escaped.'\\\']\'); var $btn=$(this); if($rows.is(\':visible\')){$rows.slideUp(200,function(){$btn.html(\'▶\');});}else{$rows.slideDown(200,function(){$btn.html(\'▼\');});}}).call(this);">▶</span>';
                                } else {
                                    // Добавляем кнопку перехода к списку игроков только если пользователь авторизован (user_rule < 10) и team_id валидный
                                    if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10 && !empty($team_id) && is_numeric($team_id)) {
                                        // Переходим на список игроков команды (list), а не на форму добавления (add)
                                        $turnir_id_context = (int)poste('turnir_id');
                                        $league_id_context = teamplayers_resolve_league_id(poste('league_id'), $turnir_id_context);
                                        $return_params = '';
                                        if (!empty($turnir_id_context)) {
                                            $return_params .= '&turnir_id='.$turnir_id_context;
                                        }
                                        if (!empty($league_id_context)) {
                                            $return_params .= '&league_id='.$league_id_context;
                                        }
                                        $this->content .= '<a href="#teamplayers-list-team_id='.$team_id.$return_params.'" class="ajax_send" title="Перейти до списку гравців команди" style="font-size:14px; color:#28a745;">+</a>';
                                    } else {
                                        $this->content .= '<span style="font-size:14px; color:#ccc;">-</span>';
                                    }
                                }
                                $this->content .= '</td>';
                                break;
                            case 'team_league_select':
                                if ($this->module != 'turnirsteams') {
                                    $this->content .= '<td align="center">-</td>';
                                    break;
                                }

                                // Получаем ID команды по записи турнира
                                $team_id = 0;
                                if (!empty($vData['id'])) {
                                    $team_id = db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.(int)$vData['id'], 'player_id');
                                    if (empty($team_id) || !is_numeric($team_id)) {
                                        if (!empty($vData['player_id']) && is_numeric($vData['player_id'])) {
                                            $team_id = $vData['player_id'];
                                        } else {
                                            $team_name = !empty($vData['player_id']) ? addslashes($vData['player_id']) : '';
                                            if (!empty($team_name)) {
                                                $team_id = db_field('SELECT id FROM `'.T_PLAYERS.'` WHERE name="'.$team_name.'" AND is_team=1 LIMIT 1', 'id');
                                            }
                                        }
                                    }
                                }

                                $turnir_id = (int)poste('turnir_id');
                                if ($turnir_id <= 0 && !empty($vData['turnir_id'])) {
                                    $turnir_id = (int)$vData['turnir_id'];
                                }
                                $league_id = (int)poste('league_id');
                                if ($league_id <= 0) {
                                    $league_id = (int)get('league_id');
                                }
                                if ($league_id <= 0 && $turnir_id > 0) {
                                    $league_id = (int)db_field('SELECT league_id FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'league_id');
                                }

                                static $leagues_count_cache = array();
                                $leagues_count = 0;
                                if ($turnir_id > 0) {
                                    if (!array_key_exists($turnir_id, $leagues_count_cache)) {
                                        $leagues_count_cache[$turnir_id] = (int)db_field('SELECT team_leagues_count FROM `'.T_TURNIRS.'` WHERE id='.$turnir_id, 'team_leagues_count');
                                    }
                                    $leagues_count = (int)$leagues_count_cache[$turnir_id];
                                }

                                if ($leagues_count <= 0 && $league_id > 0) {
                                    $leagues_count = (int)db_field('SELECT MAX(group_num) AS max_group FROM `bs_league_team_groups` WHERE league_id='.$league_id, 'max_group');
                                }

                                if ($leagues_count <= 0 || $team_id <= 0 || $league_id <= 0) {
                                    $this->content .= '<td align="center">-</td>';
                                    break;
                                }

                                $group_num = (int)db_field('SELECT group_num FROM `bs_league_team_groups` WHERE league_id='.$league_id.' AND team_id='.$team_id.' LIMIT 1', 'group_num');

                                $select_html = '<select class="team-league-select" data-team-id="'.$team_id.'" data-turnir-id="'.$turnir_id.'" data-league-id="'.$league_id.'" data-prev="'.$group_num.'">';
                                if ($group_num <= 0) {
                                    $select_html .= '<option value="" selected disabled>-</option>';
                                }
                                for ($i = 1; $i <= $leagues_count; $i++) {
                                    $selected = ($group_num === $i) ? ' selected' : '';
                                    $select_html .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                                }
                                $select_html .= '</select>';

                                $this->content .= '<td align="center">'.$select_html.'</td>';
                                break;
                            case 'add_players':
                                // Кнопка перехода к списку игроков команды
                                // Для turnirsteams player_id это ID команды, для teams - id команды
                                if ($this->module == 'turnirsteams') {
                                    // Для turnirsteams нужно получить player_id из таблицы T_TURNIR_PLAYERS
                                    // так как в $vData['player_id'] может быть название команды после обработки out_key
                                    if (!empty($vData['id'])) {
                                        // Получаем player_id напрямую из базы по id записи в T_TURNIR_PLAYERS
                                        $team_id = db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.$vData['id'], 'player_id');
                                        // Если не получилось, пробуем взять из $vData, но проверяем, что это число
                                        if (empty($team_id) || !is_numeric($team_id)) {
                                            // Если $vData['player_id'] - число, используем его
                                            if (!empty($vData['player_id']) && is_numeric($vData['player_id'])) {
                                                $team_id = $vData['player_id'];
                                            } else {
                                                // Если это не число (название команды), получаем ID по названию
                                                $team_name = !empty($vData['player_id']) ? addslashes($vData['player_id']) : '';
                                                if (!empty($team_name)) {
                                                    $team_id = db_field('SELECT id FROM `'.T_PLAYERS.'` WHERE name="'.$team_name.'" AND is_team=1 LIMIT 1', 'id');
                                                }
                                            }
                                        }
                                    } else {
                                        $team_id = !empty($vData['player_id']) && is_numeric($vData['player_id']) ? $vData['player_id'] : '';
                                    }
                                    
                                    // Получаем turnir_id и league_id из POST или из postButton для возврата назад
                                    $turnir_id = poste('turnir_id');
                                    $league_id = poste('league_id');
                                    
                                    // Формируем параметры для возврата
                                    $return_params = '';
                                    if (!empty($turnir_id)) {
                                        $return_params .= '&turnir_id='.$turnir_id;
                                    }
                                    if (!empty($league_id)) {
                                        $return_params .= '&league_id='.$league_id;
                                    }
                                } else {
                                    $team_id = $vData['id'];
                                    $return_params = '';
                                    $league_id = poste('league_id');
                                    if (!empty($league_id)) {
                                        $return_params .= '&league_id='.(int)$league_id;
                                    }
                                }
                                $this->content .= '<td align="center">';
                                // Показываем кнопку только если пользователь авторизован (user_rule < 10) и team_id валидный
                                if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10 && !empty($team_id) && is_numeric($team_id)) {
                                    // Переходим на список игроков команды (list), а не на форму добавления (add)
                                    // Добавляем параметры turnir_id и league_id для корректного возврата назад
                                    $this->content .= '<a href="#teamplayers-list-team_id='.$team_id.$return_params.'" class="ajax_send" title="Перейти до списку гравців команди" style="font-size:14px; color:#28a745; font-weight:bold;">+ Гравці</a>';
                                } else {
                                    $this->content .= '-';
                                }
                                $this->content .= '</td>';
                                break;
                            case 'edit':
                            //$postHref= !empty($val['postHref']) ? $val['postHref'] : '';
                         if ($fir) {

                         $postButton = !empty($val['postButton']) ? $val['postButton'] : '';
                             $this->postButton=  $this->postButton.$postButton;
                          $fir=0;
                         }
                                $width_default = ($_SESSION['is_mobile'] ) ? 12 : 20;
                                $img_style = '';
                                $td_style = '';
                                if ($this->module == 'reiting') {
                                    $img_style = ' style="width:25px; height:20px;"';
                                    $td_style = ' style="width:25px; min-width:25px;"';
                                }

                                $this->content .= ' <td align="center"'.$td_style.'><a href="#' . $this->module .'-edit-id=' . $vData['id'] .$page_id.$this->postButton.'" class="ajax_send"><img height="'.$width_default.'px" src="img/edit.gif" border="0"'.$img_style.'></a></td>';
                                break;
                            case 'image':
                            case 'imagefull':
                            // Для модуля turnirsteams и поля logo_team получаем логотип команды по player_id
                            if ($this->module == 'turnirsteams' && $field == 'logo_team') {
                                // Получаем player_id из таблицы T_TURNIR_PLAYERS по id записи
                                $team_player_id = db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.$vData['id'], 'player_id');
                                if (!empty($team_player_id) && is_numeric($team_player_id)) {
                                    // Получаем ID файла логотипа команды из T_PLAYERS по player_id
                                    $logo_file_id = db_field('SELECT logo FROM `'.T_PLAYERS.'` WHERE id='.$team_player_id.' AND is_team=1', 'logo');
                                    if (!empty($logo_file_id) && is_numeric($logo_file_id)) {
                                        // Получаем имя файла из таблицы bs_files_s по ID
                                        $logo_file_name = db_field('SELECT name FROM `'.T_FILES.'` WHERE id='.$logo_file_id, 'name');
                                        if (!empty($logo_file_name)) {
                                            // Проверяем существование файла и формируем пути
                                            if (file_exists(DIR_FILES_SITE_SMALL . $logo_file_name)) {
                                                $file_path = URL_FILES_SITE_SMALL . $logo_file_name;
                                            } elseif (file_exists(DIR_FILES_SITE . $logo_file_name)) {
                                                $file_path = URL_FILES_SITE . $logo_file_name;
                                            } else {
                                                $file_path = '';
                                            }
                                            
                                            if (file_exists(DIR_FILES_SITE . $logo_file_name)) {
                                                $file_path_full = URL_FILES_SITE . $logo_file_name;
                                            } elseif (!empty($file_path)) {
                                                $file_path_full = $file_path;
                                            } else {
                                                $file_path_full = '';
                                            }
                                        } else {
                                            $file_path = '';
                                            $file_path_full = '';
                                        }
                                    } else {
                                        $file_path = '';
                                        $file_path_full = '';
                                    }
                                } else {
                                    $file_path = '';
                                    $file_path_full = '';
                                }
                            } else {
                                // Стандартная обработка для других модулей
                                $file_path = (!empty($vData[$field.'_imgmini']) && file_exists(DIR_IMAGES .$vData[$field.'_imgmini'])) 
                                ? DIR_IMAGES_ .$vData[$field.'_imgmini'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE_SMALL .
                                        $vData[$field])) ? URL_FILES_SITE_SMALL . $vData[$field] : '');
                                        
                                $file_path_full = (!empty($vData[$field.'_imgfull']) && file_exists(DIR_IMAGES .$vData[$field.'_imgfull'])) 
                                ? DIR_IMAGES_ .$vData[$field.'_imgfull'] : ((!empty($vData[$field]) && file_exists(URL_FILES_SITE .
                                        $vData[$field])) ? URL_FILES_SITE . $vData[$field] : '');
                            }
                            
                            // Для модулей teams и turnirsteams делаем логотипы круглыми
                            $img_style = '';
                            if (($this->module == 'teams' && $field == 'logo') || ($this->module == 'turnirsteams' && $field == 'logo_team')) {
                                $img_style = 'border-radius: 50%; object-fit: cover; width: 50px; height: 50px;';
                            }
                                            
                                $this->content .= '<td align="center">
              ' . (!empty($file_path) ? '<a class="fancybox-buttons" data-fancybox-group="button" href="' .$file_path_full .
                                    '" ><img border="0" width="50px" height="50px" style="'.$img_style.'" src="' . $file_path  . '"></a>' :
                                    '') . '</td>';
                                break;
                            case 'text':
                                $cheked_str='';
                                $class = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';
                                if (!empty($val['check_elem']) )
                                    if ($vData[$field]>0)
                                        $cheked_str ='<img height="20px" src="css/images/icons8-done-48.png"></img>';
                                    else $cheked_str = '<img height="20px" src="css/images/icons8-uncheck-all-48.png"></img>';
                                $rounded_value = $vData[$field];
                                if (isset($val['round']) && isset($vData[$field]) && $vData[$field] !== '' && is_numeric($vData[$field])) {
                                    $rounded_value = round((float)$vData[$field],$val['round']);
                                }
                                $cheked_str = $cheked_str ? $cheked_str : '<span id="dataName--'.$field.'--' .
                                    $vData["id"] . '" '.(!empty($val['speedsearch']) ? 'speedsearch="'.$val['speedsearch'].'"' : '').'>' .
                                    $rounded_value. '</span>' ;
                                $cheked_str = !empty($val['is_img']) && !empty($vData[$field]) ? '<img  src="css/images/'.$vData[$field].'.png"></img>' : $cheked_str;
                                    $this->content .=
                                    '<td   class="'.$class.(!empty($val['no_edit_table']) ? '' : ' editTd ') .' ' . (!empty($val['class']) ? $val['class'] : '') . '" id="editTdElem--'.$field.'--'.$vData['id'].'">'
                                    . (!empty($val['oper']) && $val['oper'] == 'edit' && !((($this->module == 'teams') || ($this->module == 'turnirsteams')) && ($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login']))) ?
                                    '<a href="#' .(!empty($val['module']) ? $val['module'] : $this->module)  .'-'.(!empty($val['action']) ? $val['action'] : 'edit').'-id=' . $vData['id'] .$page_id.'&'.$page_id.$this->postButton.'"  '.(!empty($val['target']) ? 'target="_blank"' : 'class="ajax_send"').' >'
                                    . ($vData[$field]) . '</a>' : $cheked_str). '</td>';
                                    break;
                            case 'date':
                                $tdat='';
                                if (!empty($vData[$field]) && $vData[$field]!='0000-00-00'){
                                    $date = new DateTimeImmutable($vData[$field]);
                                    $tdat = $date->format('d.m.Y');
                                    if (!empty($val['onlyYear'])) $tdat =   $date->format('Y');
                                }
                               $this->content .=
                                    '<td   class="text-center editTd ' . (!empty($val['class']) ? $val['class'] : '') . '" id="editTdElem--'.$field.'--'.$vData['id'].'">'
                                    . (!empty($val['oper']) && $val['oper'] == 'edit' ?
                                    '<a href="#' . $this->module .'-edit-id=' . $vData['id'] .$page_id.'&'.$page_id.$this->postButton.'" class="ajax_send" >' 
                                    .$tdat . '</a>' : $tdat  ).'</td>';
                      
                                       break;



                                       break;
                            case 'delete':
                         //  s ('$vData[$field]');
                                $width_default = ($_SESSION['is_mobile'] ) ? 8 : 21;

                                // Для модуля teamplayers добавляем team_id, turnir_id и league_id в post_string для правильного редиректа
                                $post_string_delete = $this->postButton . '&id=' . $vData["id"] .$page_id;
                                if ($this->module == 'teamplayers') {
                                    // Извлекаем team_id из postButton
                                    $team_id_for_delete = '';
                                    if (preg_match('/&team_id=(\d+)/', $this->postButton, $matches)) {
                                        $team_id_for_delete = $matches[1];
                                    } else {
                                        // Если нет в postButton, пытаемся получить из POST
                                        $team_id_for_delete = poste('team_id');
                                        // Если нет в POST, пытаемся получить из GET
                                        if (empty($team_id_for_delete)) {
                                            $team_id_for_delete = !empty($_GET['team_id']) ? $_GET['team_id'] : '';
                                        }
                                        // Если все еще нет, пытаемся получить из сессии фильтра
                                        if (empty($team_id_for_delete) && !empty($_SESSION['teamplayers']['where'])) {
                                            if (preg_match('/team_id=(\d+)/', $_SESSION['teamplayers']['where'], $session_matches)) {
                                                $team_id_for_delete = $session_matches[1];
                                            }
                                        }
                                    }
                                    
                                    // Получаем turnir_id и league_id из postButton или POST
                                    $turnir_id_for_delete = '';
                                    $league_id_for_delete = '';
                                    
                                    // Извлекаем turnir_id
                                    if (preg_match('/&turnir_id=(\d+)/', $this->postButton, $matches)) {
                                        $turnir_id_for_delete = $matches[1];
                                    } else {
                                        $turnir_id_for_delete = poste('turnir_id');
                                        if (empty($turnir_id_for_delete)) {
                                            $turnir_id_for_delete = !empty($_GET['turnir_id']) ? $_GET['turnir_id'] : '';
                                        }
                                    }
                                    
                                    // Извлекаем league_id
                                    if (preg_match('/&league_id=(\d+)/', $this->postButton, $matches)) {
                                        $league_id_for_delete = $matches[1];
                                    } else {
                                        $league_id_for_delete = poste('league_id');
                                        if (empty($league_id_for_delete)) {
                                            $league_id_for_delete = !empty($_GET['league_id']) ? $_GET['league_id'] : '';
                                        }
                                    }
                                    
                                    if (!empty($team_id_for_delete)) {
                                        $post_string_delete = '&team_id='.$team_id_for_delete.'&id='.$vData["id"];
                                        // Добавляем параметры турнира, если они есть
                                        if (!empty($turnir_id_for_delete)) {
                                            $post_string_delete .= '&turnir_id='.$turnir_id_for_delete;
                                        }
                                        if (!empty($league_id_for_delete)) {
                                            $post_string_delete .= '&league_id='.$league_id_for_delete;
                                        }
                                    }
                                }

                                // s ((!empty($field) && !empty($vData[$field]) ? $vData[$field] : $vData["name"]));
                                $this->content .= '<td align="center"><span  post_string="' . $post_string_delete . '" module="' . $this->module .
                                    '" action="delete"  class="delete_val" mess="' .(!empty($field) && !empty($vData[$field]) ? $vData[$field] : $vData["name"]) .
                                    '" ><img height="'.$width_default.'px" src="img/delete.gif" border="0" ></span></td>';
                                break;
                             case 'win_users':
                             if (!empty($val['func_user']))  eval('$name ='.$val['func_user'].'('.$vData["id"].');'); else 
                             $name=($vData[$field]) ;
                                $this->content .= '<td align="center"><span  post_string="' . $this->
                                    postButton . '&id=' . $vData["id"] .$page_id. '" module="' . $this->module .
                                    '" action="'.$val['action'].'"  return_content_bool="" blok="0" wintype=1  ><span id="dataName--'.$field.'--' .
                                    $vData["id"] . '">'. $name. '</span></td>';
                                break;
                            case 'plus_minus':
                                $this->content .= '<td align="center"><span  post_string="' . $this->
                                    postButton . '&field=' . $field . '&id=' . $vData["id"] . '" module="' . $this->
                                    module . '" action="plus_minus" blok="0" class="ajax_send_dbl"><img  height="20px" src="img/' . ($vData[$field] ?
                                    'active' : 'pasive') . '.png" border="0" ></span></td>';
                                break;
                            case 'addsub':
                                $this->content .= '<td align="center">
            <a href="#' . $this->module .'-add-' . $this->postButton . '&id=' . $vData['id'] .
                                    '&level=' . $vData['level'] .$page_id. '"  class="ajax_send"><img  src="img/plus_.png" height="20px" border="0"></a>
      </td>';
                                break;
                                
                                case 'anyaction':
                                    $svg_mobile='';
                                    $desctop =  ($_SESSION['width_body']<768) ? 0 : 1;
                                   if (!empty($val['svg_mobile']) && !$desctop)
                                   {
                                       $aSvg = explode('|',$val['svg_mobile']);
                                       $svg_mobile=  '<svg width="'.$aSvg[1].'px" height="'.$aSvg[2].'px"> <use xlink:href="#'.$aSvg[0].'" ></use></svg>' ;
                                   }else{
                                       if (!empty($val['svg_desctop']) && $desctop)
                                       {
                                           $aSvg = explode('|',$val['svg_desctop']);
                                           $svg_mobile=  '<svg width="'.$aSvg[1].'px" height="'.$aSvg[2].'px"> <use xlink:href="#'.$aSvg[0].'" ></use></svg>' ;
                                       }
                                   }
                                    // Для модуля teamplayers и действия remove_from_team добавляем team_id из postButton
                                    $post_params = '&'.$page_id.$this->postButton;
                                    if ($this->module == 'teamplayers' && !empty($val['action']) && $val['action'] == 'remove_from_team') {
                                        // Извлекаем team_id из postButton (он должен быть там в формате &team_id=XXX)
                                        if (preg_match('/&team_id=(\d+)/', $this->postButton, $matches)) {
                                            $post_params = '&team_id='.$matches[1].'&id='.$vData['id'];
                                        } else {
                                            // Если нет в postButton, пытаемся получить из POST
                                            $team_id_post = poste('team_id');
                                            if (!empty($team_id_post)) {
                                                $post_params = '&team_id='.$team_id_post.'&id='.$vData['id'];
                                            } else {
                                                // Если нет в POST, используем стандартный формат
                                                $post_params = '&'.$page_id.$this->postButton.'&id='.$vData['id'];
                                            }
                                        }
                                    }
                                    // Определяем иконку: если задана в параметрах, используем её, иначе для remove_from_team используем delete.gif
                                    $img_html = '';
                                    if (!empty($val['img'])) {
                                        $img_html = '<img  src="img/'.$val['img'].'.png" height="20px" border="0">';
                                    } elseif (!empty($val['action']) && $val['action'] == 'remove_from_team') {
                                        $img_html = '<img height="21px" src="img/delete.gif" border="0">';
                                    }
                                    $this->content .= '<td align="center">'.(empty($val['action'])?'<a href="#" <img  src="img/plus_.png" height="20px" border="0"></a>' :
  
            '<a href="#' . (!empty($val['module'])?$val['module']:$this->module) .'-'.$val['action'].'-' .(!empty($val['name_field_child']) ? $val['name_field_child'] :
                                    'id') . '=' . $vData['id'].$post_params.'"  class="ajax_send">'.$svg_mobile.$img_html.'</a></td>');
                                break;
                            case 'goto_modact':

                                $this->content .= '<td align="center">
                                        <a href="#' . (!empty($val['module'])?$val['module']:$this->module) .'-'.$val['action'].'-' .(!empty($val['name_field_child']) ? $val['name_field_child'] :
                                            'id') . '=' . $vData['id'].'&'. $page_id.$this->postButton.'"  class="ajax_send">'.
                                        (!empty($val['img']) ? '<img  src="img/'.$val['img'].'.png" height="20px" border="0">' :  ($vData[$field]) ).'</a></td>';
                                break;
                            case 'sort':
                            
                                $this->content .= '<td align="center">
        <span  module="' . $this->module .
                                    '" action="sort" post_string="&pid='.$field.'&'.$field.'=' . (!empty($vData[$field]) ? $vData[$field] :
                                    0) .$page_id.$this->postButton. '" return_content_bool="" blok="0" class="ajax_send"><img height="30px" src="img/sort.png" border="0" ></span>
     </td>';
                                break;

                            case 'tree':
                                $font_size = 16;
                                $module = ($val['module']=='_all_' ? $vData['module'] : $val['module']);
                                $level = !empty($vData['level']) ? $vData['level'] - 1 : 0;
                                $this->content .= '<td  style="padding-left:' . ($level * 30 + 5) . 'px;">
            <span style="font-size:' . ($font_size - $level * 2) .
                                    'px;" id="catalog_name_id_' . $vData['id'] . '">
            <a href="#' . $module.'-'.(!empty($val['action'])? $val['action'] : 'list').'-' . (!empty($val['name_field_child']) ? $val['name_field_child'] :
                                    'id') . '=' . $vData['id'] .$page_id. '" class="ajax_send" >' . $vData[$field] . '</a></span> 
      </td>';

                                break;
                            case 'parent':
                                $this->content .=
                                    '<td  style="padding-left:5px;"><span style="font-size:10px;" id="news_name_id_' .
                                    $vData["id"] . '"><a href="#'. $val['module'] .'-list-' . (!empty($val['name_field_child']) ? $val['name_field_child'] :
                                    'id') . '=' . $vData['id'].'" class="ajax_send">' . ($vData[$field]) . '</a></span></td>';
                                break;
                    case 'out_key_prostspr':

                        $this->content .=
                            '<td  style="padding-left:5px;"><span id="news_name_id_' .
                            $vData["id"] . '">' . ($vData[$field.'_name']) . '</span></td>';
                        break;
                            case 'math_oper' :
                                $class = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';
                                $mf1 = (isset($vData[$val['name_field1']]) && $vData[$val['name_field1']] !== '' && is_numeric($vData[$val['name_field1']])) ? (float)$vData[$val['name_field1']] : 0;
                                $mf2 = (isset($vData[$val['name_field2']]) && $vData[$val['name_field2']] !== '' && is_numeric($vData[$val['name_field2']])) ? (float)$vData[$val['name_field2']] : 0;
                                $val_oper = round($mf1,$val['round'])-round($mf2,$val['round']);
                                $cheked_str = (isset($val['round']) ? round($val_oper,$val['round']) : $val_oper);
                                $this->content .=
                                    '<td  class="'.$class.'">'.$cheked_str.'</td>';
                                break;
                                break;
                    case 'out_key':
                        $cheked_str='';
                        $class = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';
                        if (!empty($val['check_elem']) )
                            if ($vData[$field]>0)
                            $cheked_str ='<img height="20px" src="css/images/icons8-done-48.png"></img>';
                            else $cheked_str = '<img height="20px" src="css/images/icons8-uncheck-all-48.png"></img>';
                        $rounded_value = $vData[$field];
                        if (isset($val['round']) && isset($vData[$field]) && $vData[$field] !== '' && is_numeric($vData[$field])) {
                            $rounded_value = round((float)$vData[$field],$val['round']);
                        }
                        $cheked_str = $cheked_str ? $cheked_str : '<span 
                        
id="news_name_id_' . $vData["id"] . '">' . $rounded_value. '</span>' ;
                        $cheked_str = !empty($val['is_img']) && !empty($vData[$field]) ? '<img  src="css/images/'.$vData[$field].'.png"></img>' : $cheked_str;

                        $this->content .=
                                    '<td  class="'.$class.'">'.(!empty($val['oper']) && $val['oper'] == 'edit' && !((($this->module == 'teams') || ($this->module == 'turnirsteams')) && ($_SESSION['gt']['user_rule']>=10 || empty($_SESSION['gt']['user_login']))) ?
                                        '<a '.(!empty($val['target']) ? 'target="_blank"' : 'class="ajax_send"').'  href="#' . (!empty($val['module']) ? $val['module'] : $this->module) .'-'.(!empty($val['action']) ? $val['action'] : 'edit').'-id=' . (!empty($val['out_module_result']) ? $vData[$val['out_module_result']] : $vData['id']) .$page_id.'&'.$page_id.$this->postButton.'"  >'
                                        . ($vData[$field]) . '</a>' : $cheked_str).'</td>';
                                break;
                case 'get_func':
                $content ='';
                    $class = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';

                    $function = !empty($val['function']) ? $val['function'] : '';
                           
                            if (!empty($function) && function_exists($function)){ 

                              $content =   call_user_func_array($function,array($field,$vData['id'],$vData));
                              }

                      $this->content .=
                                    '<td class="'.$class.'"><span style="font-size:10px;" id="news_name_id_' .
                                    $vData["id"] . '">' . (!empty( $val['no_slash']) ? $content : ($content))  . '</span></td>';
                                break;
                case 'field':
                    // Обработка полей типа field (например, cnt_players)
                    // ВАЖНО: Для полей с no_sql=1 инициализируем значение по умолчанию, если оно не установлено
                    // Это предотвращает "Undefined index" ошибки для вычисляемых полей
                    if (!isset($vData[$field])) {
                        // Если указано default_value в определении поля, используем его
                        $default_val = !empty($val['default_value']) ? $val['default_value'] : '';
                        $vData[$field] = $default_val;
                    }
                    // ВАЖНО: Получаем значение поля, даже если оно равно 0
                    // Используем array_key_exists() вместо isset(), чтобы правильно обработать значение 0
                    // isset() вернет false для значения 0, но array_key_exists() вернет true, если ключ существует
                    if (array_key_exists($field, $vData)) {
                        // Значение существует (включая 0), преобразуем в строку
                        $field_value = (string)$vData[$field];
                    } else {
                        // Значение не установлено, используем пустую строку
                        $field_value = '';
                    }
                    $class_field = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';
                    $this->content .=
                                    '<td class="'.$class_field.'"><span id="field_'.$field.'_'.$vData["id"].'">' . htmlspecialchars($field_value) . '</span></td>';
                                break;
                    case 'prostspr':
                                // Проверяем наличие поля _name, если нет - пытаемся получить название из справочника
                                $city_name_display = '';
                                if (!empty($vData[$field.'_name'])) {
                                    $city_name_display = $vData[$field.'_name'];
                                } elseif (!empty($vData[$field]) && is_numeric($vData[$field])) {
                                    // Если есть только ID, получаем название из справочника
                                    $city_name_display = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$vData[$field].' AND id_spis='.(!empty($val['id_spis']) ? $val['id_spis'] : 4), 'name');
                                    $city_name_display = !empty($city_name_display) ? $city_name_display : '';
                                } else {
                                    // Если это уже название, используем его
                                    $city_name_display = !empty($vData[$field]) ? $vData[$field] : '';
                                }
                                $this->content .=
                                    '<td  class="editTd text-center" ><span  id="news_name_id_' .
                                    $vData["id"] . '">' . $city_name_display . '</span></td>';
                                break;
                   case 'vibor':
                   $jsonReturn='';

                      if (!empty($arrReturn_id_val)) $jsonReturn = ' jsonReturn='.base64_encode(json_encode($arrReturn_id_val));
                                $this->content .= '<td align="center"><a href="#" data-bs-dismiss="modal" class="element_vibor" '.$jsonReturn.' field="'.$this->field_result.'" result="'.$vData[$this->field_result_name].'" id="element_vibor_id_'.$vData['id'].'">Виберіть</a></td>';
                             $arrReturn_id_val=[];
                                break;
                        

                        }
                    }
                    $this->content .= '</tr>';
                    
                    // Если это модуль teams или turnirsteams и это команда, добавляем строки с игроками
                    // Для teams все записи являются командами, так как фильтр уже установлен (is_team=1)
                    $is_team_module = ($this->module == 'teams');
                    $is_turnirsteams_module = ($this->module == 'turnirsteams');
                    
                    if ($is_team_module || $is_turnirsteams_module) {
                        // Для turnirsteams нужно получить player_id из таблицы T_TURNIR_PLAYERS
                        // так как в $vData['player_id'] может быть название команды после обработки out_key
                        if ($is_turnirsteams_module) {
                            // Получаем player_id напрямую из базы по id записи в T_TURNIR_PLAYERS
                            if (!empty($vData['id'])) {
                                $team_id = db_field('SELECT player_id FROM `'.T_TURNIR_PLAYERS.'` WHERE id='.$vData['id'], 'player_id');
                                // Если не получилось, пробуем взять из $vData, но проверяем, что это число
                                if (empty($team_id) || !is_numeric($team_id)) {
                                    // Если $vData['player_id'] - число, используем его
                                    if (!empty($vData['player_id']) && is_numeric($vData['player_id'])) {
                                        $team_id = $vData['player_id'];
                                    } else {
                                        // Если это не число (название команды), получаем ID по названию
                                        $team_name = !empty($vData['player_id']) ? addslashes($vData['player_id']) : '';
                                        if (!empty($team_name)) {
                                            $team_id = db_field('SELECT id FROM `'.T_PLAYERS.'` WHERE name="'.$team_name.'" AND is_team=1 LIMIT 1', 'id');
                                        }
                                    }
                                }
                            } else {
                                $team_id = !empty($vData['player_id']) && is_numeric($vData['player_id']) ? $vData['player_id'] : '';
                            }
                        } else {
                            // Для teams это просто id
                            $team_id = $vData['id'];
                        }
                        
                        // Рендерим строки игроков только если team_id валидный
                        if (!empty($team_id) && is_numeric($team_id)) {
                        $league_id_context = 0;
                        if ($is_turnirsteams_module) {
                            $turnir_id_context = (int)poste('turnir_id');
                            if ($turnir_id_context <= 0 && !empty($vData['turnir_id'])) {
                                $turnir_id_context = (int)$vData['turnir_id'];
                            }
                            $league_id_context = teamplayers_resolve_league_id(poste('league_id'), $turnir_id_context);
                        }
                        $players = teamplayers_list($team_id, $league_id_context);
                        
                        if (!empty($players)) {
                            $col_count = count($this->aColList);
                            foreach ($players as $player) {
                                $city_name = '';
                                if (!empty($player['city']) && is_numeric($player['city'])) {
                                    // Используем правильную таблицу bs_spr-spis-values с полем value
                                    $city_name = db_field('SELECT value as name FROM `bs_spr-spis-values` WHERE id='.$player['city'].' AND id_spis=4', 'name');
                                } elseif (!empty($player['city'])) {
                                    // Если city уже содержит название (не ID), используем его
                                    $city_name = $player['city'];
                                }
                                
                                // Добавляем класс, чтобы исключить из striped паттерна
                                $this->content .= '<tr class="team-player-row" data-team-id="'.$team_id.'" style="display:none; background-color:#ffffff !important; border-left:4px solid #007bff;">';
                                
                                // Проходим по всем колонкам и выводим данные игрока
                                foreach ($this->aColList as $val) {
                                    $type_field = !empty($val['type']) ? strtolower($val['type']) : 'text';
                                    $field = !empty($val['name_field']) ? $val['name_field'] : '';
                                    
                                    if ($type_field == 'number' || $type_field == 'edit' || $type_field == 'add_players') {
                                        $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                    } elseif ($type_field == 'expand_collapse') {
                                        $id_reiting_value = '';
                                        $can_show_id_ligas = !empty($_SESSION['gt']['user_rule']) && (int)$_SESSION['gt']['user_rule'] < 10;
                                        if ($can_show_id_ligas && isset($player['id_reiting']) && trim((string)$player['id_reiting']) !== '') {
                                            $id_reiting_value = htmlspecialchars((string)$player['id_reiting']);
                                        }
                                        $this->content .= '<td class="text-center" style="background-color:#ffffff; vertical-align:middle;"><span style="font-size:0.9em; color:#6c757d; font-weight:600;">'.$id_reiting_value.'</span></td>';
                                    } elseif ($type_field == 'delete') {
                                        // Для модуля turnirsteams не показываем кнопки удаления для игроков в раскрывающемся списке
                                        // Удаление игроков доступно только в отдельном модуле teamplayers
                                        if ($this->module == 'turnirsteams') {
                                            $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                        } elseif (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10) {
                                            // Для модуля teams показываем кнопку удаления (если нужно)
                                            $this->content .= '<td align="center" style="background-color:#ffffff; vertical-align:middle;"><span post_string="&id='.$player['id'].'&team_id='.$team_id.'" module="teamplayers" action="remove_from_team" class="delete_val" mess="Видалити гравця '.$player['name'].' з команди?" ><img height="18px" src="img/delete.gif" border="0" ></span></td>';
                                        } else {
                                            $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                        }
                                    } elseif ($type_field == 'image' || $type_field == 'imagefull') {
                                        $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                    } elseif ($field == 'name' || ($type_field == 'out_key' && $field == 'player_id')) {
                                        $this->content .= '<td class="text-start" style="padding-left:50px; background-color:#ffffff; vertical-align:middle;"><span style="font-size:0.95em; color:#212529; font-weight:500;">'.htmlspecialchars((string)$player['name']).'</span></td>';
                                    } elseif ($field == 'phone') {
                                        $this->content .= '<td class="text-center" style="background-color:#ffffff; vertical-align:middle;"><span style="font-size:0.95em; color:#495057;">'.(!empty($player['phone']) ? $player['phone'] : '-').'</span></td>';
                                    } elseif ($field == 'city' || ($type_field == 'out_key_prostspr' && $field == 'city')) {
                                        $this->content .= '<td class="text-center" style="background-color:#ffffff; vertical-align:middle;"><span style="font-size:0.95em; color:#495057;">'.(!empty($city_name) ? $city_name : '-').'</span></td>';
                                    } elseif ($field == 'reiting_ukraine') {
                                        $rating_value = ($player['reiting_ukraine'] === null || $player['reiting_ukraine'] === '') ? '-' : $player['reiting_ukraine'];
                                        $this->content .= '<td class="text-center" style="background-color:#ffffff; vertical-align:middle;"><span style="font-size:0.95em; color:#495057;">'.$rating_value.'</span></td>';
                                    } elseif ($field == 'is_opl_reiting') {
                                        if (!empty($_SESSION['gt']['user_rule']) && $_SESSION['gt']['user_rule'] < 10) {
                                            $icon = !empty($player['is_opl_reiting']) ? 'icons8-done-48.png' : 'icons8-uncheck-all-48.png';
                                            $this->content .= '<td class="text-center" style="background-color:#ffffff; vertical-align:middle;"><img height="20px" src="css/images/'.$icon.'"></td>';
                                        } else {
                                            $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                        }
                                    } elseif ($field == 'cnt_players' || $field == 'cnt_games' || $field == 'cnt_wins' || $field == 'cnt_lose' || $field == 'mesto' || $field == 'points') {
                                        $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                    } else {
                                        $this->content .= '<td style="background-color:#ffffff; vertical-align:middle;"></td>';
                                    }
                                }
                                
                                $this->content .= '</tr>';
                            }
                        }
                        } // Закрываем if (!empty($team_id) && is_numeric($team_id))
                    } // Закрываем if ($is_team_module || $is_turnirsteams_module)

                }

            }
       
        }
    }
    function sql_list($sql='')
    {   
       // $this->dinamyk_tree_set_sort_new();

        $where = (!empty($_SESSION[$this->module]['where']) ? $_SESSION[$this->module]['where'] :'');

        // Защита от «протекания» фильтров игроков в модуль игр турнира.
        // В bs_reiting нет полей ispara/is_team, поэтому такие условия ломают SQL.
        if ($this->module == 'reiting' && $this->table_module == T_REITING && !empty($where)) {
            $where = preg_replace('/\s+and\s+ispara\s*=\s*0\s*/i', ' ', $where);
            $where = preg_replace('/\s+and\s*\(\s*is_team\s+IS\s+NULL\s+OR\s*is_team\s*=\s*0\s*\)\s*/i', ' ', $where);
            $where = trim((string)$where);
            if ($where !== '' && strpos($where, 'and') !== 0) {
                $where = ' and '.$where;
            }
        }

        if (!empty($this->wintype) && !empty($this->field_result) && 
        !empty($_SESSION['wintype'][$this->module][$this->field_result]['where'])){
     $where =  $_SESSION['wintype'][$this->module][$this->field_result]['where'];
    // unset($_SESSION);

    }
          // получаем название родительского раздела

          if (!empty($this->aParent))
          { foreach ($this->aParent as $key =>$vParent){
            
                $this->id_aParent = $this->getPostReturnId($key);

               //$this->getNameATable($key));
                if (!empty($this->id_aParent)) {
                    $lang = $this->getNameALang($key);
                            if ($this->getNameATable($key)) {
                                 /*   $this->name_list_parent = db_field('select '.($lang ? 'name_'.$lang .' as name' : 'name').'  from `' . $this->getNameATable($key) .
                                    '` where id=' . $this->id_aParent, 'name');*/
                            $parent_name = $this->getNameAperent($key);
                            $skip_teamplayers_team_parent = (
                                $this->module == 'teamplayers'
                                && $parent_name == 'team_id'
                                && (!empty(poste('league_id')) || !empty($_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID']))
                            );
                            if (!$skip_teamplayers_team_parent) {
                                $where .= ' and ' . $parent_name . '=' . $this->id_aParent;
                            }

                            $this->sql .= ',' . $parent_name;
                            }
                        } 
                }        
             }  
             

                $oSelect = new SqlSelect($_SESSION[$this->module]['page_number'],$sql);
                if (!empty($_SESSION['wintype']['no_module']['table']) && $this->module=='no_module')
                $oSelect->setTables($_SESSION['wintype']['no_module']['table']);
                $oSelect->workFields($this->aColList);

             // $oSelect->s
                $where .= (!empty($_SESSION[$this->module]['filters']['filter_s']) ? ' and '.(!empty($_SESSION[$this->module]['filters']['filter_field_bd']) ? $_SESSION[$this->module]['filters']['filter_field_bd'] :  $_SESSION[$this->module]['filters']['filter_field']).' LIKE "'.($_SESSION[$this->module]['filters']['is_first_filter']? '':'%').$_SESSION[$this->module]['filters']['filter_s'].'%"' : '');

                $oSelect->setWhere($where);
                //  s($_SESSION[$this->module]);
                $oSelect->setOrder($_SESSION[$this->module]['sort'],$_SESSION[$this->module]['sort_type'],  $_SESSION[$this->module]['sort_default'] );

              
               $this->aData =  $oSelect->resultList();
           //     wLog($this->aData);
               $this->cntElemsTables =  $oSelect->getCntElem();
               $this->page_number =  $oSelect->getPageNumber();

         
    }
// функция добавляет колонку sort_new для более правильной сортировки и плюс с уровнями, очень потом удобно менять всем уровням на +1
    function set_feald_sort_new($upd_fealds = 0)
    {
        $isFeald = db_row("show columns FROM `" . $this->table_module . "`where `Field` = 'sort_new'");
        if (empty($isFeald)) { //ЕСЛИ ПОЛЯ ЕЩЕ НЕТ ДОБАВЛЯЕМ ЕГО В СТРУКТУРУ ТАБЛИЦЫ
            db_query('ALTER TABLE `' . $this->table_module  .
                '` ADD COLUMN `sort_new` VARCHAR(255) NULL ');
            $upd_fealds = 1;
        }
        if ($upd_fealds) {
            $this->dinamyk_tree_set_sort_new();
        }
    } 
      //функция рекурсивно проходит по всех записях таблицы и присваваиеивает уровни с сортировкой
    function dinamyk_tree_set_sort_new($level = 1, $parent = 0, $sort_new = '')
    {
        $Aelem_levels = db_list('select id, pid, level from `' . $this->table_module .
            '` where pid = ' . $parent . ' order by sort');
        if (!empty($Aelem_levels)) {
            $sort = 1;
            $sort_new_ = $sort_new;
            foreach ($Aelem_levels as $val) {
                $sort_new = $sort_new_.($parent>0 ? ',' : '') . $val['id'];
                db_query('update `' . $this->table_module . '` set level=' . $level . ', sort=' .
                    $sort . ', sort_new="' . $sort_new . '" where id=' . $val['id']);
                $this->dinamyk_tree_set_sort_new($level + 1, $val['id'], $sort_new); // запускаем функцию рекурсивно
                $sort++;
            }
        } else
            return false;

    }
     function shablon_list_header()
    {
         SystemClass::setZaglModule(
         '<div align="center" class="zagl">' . $this->nameZ . $this->
            nameZList . (!empty($_SESSION['filters']['module_parent']) && !empty($_SESSION[$_SESSION['filters']['module_parent']]['filters']) ?
            ' найденных по шаблону "' . $_SESSION[$_SESSION['filters']['module_parent']]['filters']['filter_s'] .
            '"' : (!empty($this->name_list_parent) ? ' ' .(!empty( $this->name_list_parent_dop) ? $this->name_list_parent_dop : '').
            ' "' . $this->name_list_parent . '"' : '')) . '</div>');
//<table cellpadding="0" cellspacing="1" class="bordered" width='.(!empty($this->TableWidth) ? $this->TableWidth : '"100%"').' border="0" id="parts_table_">
   //      width='.(!empty($this->TableWidth) ? $this->TableWidth : '"100%"').'
         $respon = ($_SESSION['is_mobile']) ? 'class="table-responsive table-responsive-'.$this->module.'"' : 'class="container-fluid"';
         $striped_class = ($this->module == 'teams') ? '' : ' table-striped';
         $this->content = '<div '.$respon.'>

<table cellpadding="0" cellspacing="1" class="table table-sm'.$striped_class.'  bordered2 table-hover table-bordered '.(!empty($this->table_class) ? $this->table_class :'').' border-light-subtle" border="0" id="parts_table_">
 <thead class="th_color_rose">
   <tr '.(!empty($this->theed_tr_class) ? 'class="'.$this->theed_tr_class.'""' :'').'>
    ';

    }

function getHtmlPagging(){
  $this->page_items = !empty($_SESSION[$this->module]['page_items']) ? $_SESSION[$this->module]['page_items'] : 1;
//  $this->page_number =  $_SESSION[$this->module]['page_number'];

  // Рассчитываем количество страниц 
      if(intval($this->cntElemsTables/$this->page_items) == $this->cntElemsTables/$this->page_items)
        $this->page_count = $this->cntElemsTables/$this->page_items;
      else 
        $this->page_count = intval($this->cntElemsTables/$this->page_items)+1;
      // Рассчитываем кол-во групп страниц 
      $this->page_groups = $this->page_count/$this->page_link;
      $this->page_groups = intval($this->page_groups)==$this->page_groups?$this->page_groups:
      1+intval($this->page_groups); 
      
      // Если страниц меньше 2 разбивки нет
      if ($this->cntElemsTables<10) return "";  

$post_b = !empty($_SESSION['POST_RETURN']) ? $_SESSION['POST_RETURN'] :'';
  $apageGrpActiv = [];
  $apageGrpActiv[0]= !empty($_SESSION[$this->module]['page_items']) && $_SESSION[$this->module]['page_items'] == 10 ? 'active_grp' : '';
  $apageGrpActiv[1]= !empty($_SESSION[$this->module]['page_items']) && $_SESSION[$this->module]['page_items'] == 20 ? 'active_grp' : '';
  $apageGrpActiv[2]= !empty($_SESSION[$this->module]['page_items']) && $_SESSION[$this->module]['page_items'] == 50 ? 'active_grp' : '';
  $apageGrpActiv[3]= !empty($_SESSION[$this->module]['page_items']) && $_SESSION[$this->module]['page_items'] == 100 ? 'active_grp' : '';
/*<div class="select page_block">
			<a  val="'.$_SESSION[$this->module]['page_items'].'" module="'.$this->module.'" action="list" post_string="'.$this->postButton.'" class="slct">'.$_SESSION[$this->module]['page_items'].' рядків</a>
		<ul class="drop">
				<li val="10" module="'.$this->module.'" action="list" post_string="'.$this->postButton.'">10 рядків</li>
				<li val="20" module="'.$this->module.'" action="list" post_string="'.$this->postButton.'">20 рядків</li>
				<li val="50" module="'.$this->module.'" action="list" post_string="'.$this->postButton.'">50 рядків</li>
				<li val="100" module="'.$this->module.'" action="list" post_string="'.$this->postButton.'">100 рядків</li>
			</ul>
	</div>*/
if ($_SESSION['is_mobile']){
    $text = '<div class="padding_main_mob ">
<div class="paging_num_mob">
 <a class="nav-link_pag dropdown-toggle" data-bs-toggle="dropdown" val="'.$_SESSION[$this->module]['page_items'].'" module="'.$this->module.'" action="list" post_string="'.$this->postButton.$post_b.'" class="slct" role="button" aria-expanded="false">'.$_SESSION[$this->module]['page_items'].'</a>
    <ul class="dropdown-menu">
      <li ><a class="dropdown-item page_grp" num="100" module="'.$this->module.'" action="list" post_string="'.$this->postButton.$post_b.'">100</a></li>
      <li ><a class="dropdown-item page_grp" num="50" module="'.$this->module.'" action="list" post_string="'.$this->postButton.$post_b.'">50</a></li>
      <li <a class="dropdown-item page_grp" num="20" module="'.$this->module.'" action="list" post_string="'.$this->postButton.$post_b.'" >20</a></li>
      <li <a class="dropdown-item page_grp"  num="10" module="'.$this->module.'" action="list" post_string="'.$this->postButton.$post_b.'">10</a></li>
      
    </ul></div>';
    $text.= '<div class="paging paging_block_center">';

    // Ищем группу в которую входит страница
    $group = intval($this->page_number / $this->page_link) === ($this->page_number / $this->page_link) ? $this->page_number / $this->page_link : intval($this->page_number / $this->page_link) + 1;

    // Стороим код выбора страницы
    if ($group > 1)
        $text .= '
<a class="previous page_num" num="' . (($group - 1) * $this->page_link) . '"><svg class="pag_left"> <use width="16px" height="16px" xlink:href="#pad_left"></use> </svg></a>';

    for ($i = 1; $i <= $this->page_link && $i + ($group - 1) * $this->page_link <= $this->page_count; $i++) {

        if (($i + ($group - 1) * $this->page_link) == $this->page_number) $text .= '<a class="active page_num" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";
        else $text .= '<a class="page_num" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";

    }

    $this->html .= ' ';

    if ($group < $this->page_groups) {
        $text .= '<a class="next page_num" module="' . $this->module . '" action="list" title="Ctrl + →" num="' . ($group * $this->page_link + 1) . '"><svg class="pag_left"> <use width="16px" height="16px" xlink:href="#pad_right"></use> </svg></a>
';
    }
    $text.='</div>';
}else {

    $text = '<div class="padding_main ">

<div class="paging paging_block_center">';

    // Ищем группу в которую входит страница
    $group = intval($this->page_number / $this->page_link) === ($this->page_number / $this->page_link) ? $this->page_number / $this->page_link : intval($this->page_number / $this->page_link) + 1;

    // Стороим код выбора страницы
    if ($group > 1)
        $text .= '<a class="begin_page page_num" num="1" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '">В початок</a>
<a class="previous page_num" num="' . (($group - 1) * $this->page_link) . '">-' . PAGE_GROUPS . '</a>';

    for ($i = 1; $i <= $this->page_link && $i + ($group - 1) * $this->page_link <= $this->page_count; $i++) {

        if (($i + ($group - 1) * $this->page_link) == $this->page_number) $text .= '<a class="active page_num" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";
        else $text .= '<a class="page_num" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="' . ($i + ($group - 1) * $this->page_link) . '">' . ($i + ($group - 1) * $this->page_link) . "</a>";

    }

    $this->html .= ' ';

    if ($group < $this->page_groups) {
        $text .= '<a class="next page_num" module="' . $this->module . '" action="list" title="Ctrl + →" num="' . ($group * $this->page_link + 1) . '">+' . PAGE_GROUPS . '</a>
<a class="end_page page_num" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="' . $this->page_count . '">В кінець ' . $this->page_count . '</a>';
    }
    $text .= '</div>
<div class="paging_block_left">
<div class="pad_text"> К-ть рядків на сторінці:</div>
<div class="paging paging_num">
<a class="page_grp ' . $apageGrpActiv[0] . '" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="10">10</a>
<a class="page_grp ' . $apageGrpActiv[1] . '" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="20">20</a>
<a class="page_grp ' . $apageGrpActiv[2] . '" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="50">50</a>
<a class="page_grp ' . $apageGrpActiv[3] . '" module="' . $this->module . '" action="list" post_string="' . $this->postButton .$post_b. '" num="100">100</a>
</div>
</div>
</div>';
}
      
        return $text;
        
        //      return (!empty($_SESSION['pagging_html']) ? $_SESSION['pagging_html'] : '');
}
  
}

?>
