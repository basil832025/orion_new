<?php
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
 private $wintype  ='';
 private $page_items  ='';

 
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
                $this->content .= '<th  class="align-middle '.(!empty($val['classAlign']) ?  ''.$val['classAlign'] : 'text-center').'"  ' .$width.'>'
                    .(!empty($val['filter']) ? '<span class="filter" '.(!empty($val['name_field']) ? ' filter_name="'.$val['name_field'].'"' : '').'></span>':'') 
                    .'<span '. (!empty($val['name_field']) ? 'class="sort_cols '.$rorate_90.'"  sort="'.$val['name_field'].'"' : '').' >'. $name
                    .'<span '.(!empty($_SESSION[$this->module]['sort']) && !empty($val['name_field']) && ($val['name_field']==$_SESSION[$this->module]['sort']) ? 
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
                    $this->content .= '<tr class="align-middle">';

                
                    // нужно 2 раза прогнать для кнопки выбор
                    foreach ($this->aColList as $val) {
                        $field = !empty($val['name_field']) ? $val['name_field'] : '';
                          if (!empty($val['return_id_val'])) $arrReturn_id_val[$val['return_id_val']]=($vData[$field]);
                
                        }

                        // пройдемся по заголовкам таблицы
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
                                $class = (!empty($val['class']) ? 'class="'.$val['class'].'"' : '') ;
                                $this->content .= '<td align="center" ><span '.$class.'>' . $this->number_line . '</span></td>';
                                break;
                            case 'edit':
                            //$postHref= !empty($val['postHref']) ? $val['postHref'] : '';
                         if ($fir) {

                         $postButton = !empty($val['postButton']) ? $val['postButton'] : '';
                             $this->postButton=  $this->postButton.$postButton;
                          $fir=0;
                         }
                                $width_default = ($_SESSION['is_mobile'] ) ? 12 : 20;

                                $this->content .= ' <td align="center"><a href="#' . $this->module .'-edit-id=' . $vData['id'] .$page_id.$this->postButton.'" class="ajax_send"><img height="'.$width_default.'px" src="img/edit.gif" border="0" ></a></td>';
                                break;
                            case 'image':
                            case 'imagefull':
                            $file_path = (!empty($vData[$field.'_imgmini']) && file_exists(DIR_IMAGES .$vData[$field.'_imgmini'])) 
                            ? DIR_IMAGES_ .$vData[$field.'_imgmini'] : ((!empty($vData[$field]) && file_exists(DIR_FILES_SITE_SMALL .
                                    $vData[$field])) ? URL_FILES_SITE_SMALL . $vData[$field] : '');
                                    
                            $file_path_full = (!empty($vData[$field.'_imgfull']) && file_exists(DIR_IMAGES .$vData[$field.'_imgfull'])) 
                            ? DIR_IMAGES_ .$vData[$field.'_imgfull'] : ((!empty($vData[$field]) && file_exists(URL_FILES_SITE .
                                    $vData[$field])) ? URL_FILES_SITE . $vData[$field] : '');
                                            
                                $this->content .= '<td align="center">
              ' . (!empty($file_path) ? '<a class="fancybox-buttons" data-fancybox-group="button" href="' .$file_path_full .
                                    '" ><img border="0" width="50px" src="' . $file_path  . '"></a>' :
                                    '') . '</td>';
                                break;
                            case 'text':
                                $cheked_str='';
                                $class = !empty($val['classAlign']) ? $val['classAlign'] : 'td_align_center';
                                if (!empty($val['check_elem']) )
                                    if ($vData[$field]>0)
                                        $cheked_str ='<img height="20px" src="css/images/icons8-done-48.png"></img>';
                                    else $cheked_str = '<img height="20px" src="css/images/icons8-uncheck-all-48.png"></img>';
                                $cheked_str = $cheked_str ? $cheked_str : '<span id="dataName--'.$field.'--' .
                                    $vData["id"] . '" '.(!empty($val['speedsearch']) ? 'speedsearch="'.$val['speedsearch'].'"' : '').'>' .
                                    (isset($val['round']) ? round($vData[$field],$val['round']) : $vData[$field]). '</span>' ;
                                $cheked_str = !empty($val['is_img']) && !empty($vData[$field]) ? '<img  src="css/images/'.$vData[$field].'.png"></img>' : $cheked_str;
                                    $this->content .=
                                    '<td   class="'.$class.(!empty($val['no_edit_table']) ? '' : ' editTd ') .' ' . (!empty($val['class']) ? $val['class'] : '') . '" id="editTdElem--'.$field.'--'.$vData['id'].'">'
                                    . (!empty($val['oper']) && $val['oper'] == 'edit' ?
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

                                // s ((!empty($field) && !empty($vData[$field]) ? $vData[$field] : $vData["name"]));
                                $this->content .= '<td align="center"><span  post_string="' . $this->
                                    postButton . '&id=' . $vData["id"] .$page_id. '" module="' . $this->module .
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
                                    $this->content .= '<td align="center">'.(empty($val['action'])?'<a href="#" <img  src="img/plus_.png" height="20px" border="0"></a>' :
  
            '<a href="#' . (!empty($val['module'])?$val['module']:$this->module) .'-'.$val['action'].'-' .(!empty($val['name_field_child']) ? $val['name_field_child'] :
                                    'id') . '=' . $vData['id'].'&'. $page_id.$this->postButton.'"  class="ajax_send">'.$svg_mobile
            .
            (!empty($val['img']) ? '<img  src="img/'.$val['img'].'.png" height="20px" border="0">' :  '' ).'</a></td>');
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
                                $val_oper = round($vData[$val['name_field1']],$val['round'])-round($vData[$val['name_field2']],$val['round']);
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
                        $cheked_str = $cheked_str ? $cheked_str : '<span 
                        
id="news_name_id_' . $vData["id"] . '">' . (isset($val['round']) ? round($vData[$field],$val['round']) : $vData[$field]). '</span>' ;
                        $cheked_str = !empty($val['is_img']) && !empty($vData[$field]) ? '<img  src="css/images/'.$vData[$field].'.png"></img>' : $cheked_str;

                        $this->content .=
                                    '<td  class="'.$class.'">'.(!empty($val['oper']) && $val['oper'] == 'edit' ?
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
                    case 'prostspr':
                                $this->content .=
                                    '<td  class="editTd text-center" ><span  id="news_name_id_' .
                                    $vData["id"] . '">' . ($vData[$field.'_name']) . '</span></td>';
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

                }

            }
       
        }
    }
    function sql_list($sql='')
    {   
       // $this->dinamyk_tree_set_sort_new();

        $where = (!empty($_SESSION[$this->module]['where']) ? $_SESSION[$this->module]['where'] :'');

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
                            $where .= ' and ' . $this->getNameAperent($key) . '=' . $this->id_aParent;

                            $this->sql .= ',' . $this->getNameAperent($key);
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
         $respon = ($_SESSION['is_mobile']) ? 'class="table-responsive"' : 'class="container-fluid"';
         $this->content = '<div '.$respon.'>

<table cellpadding="0" cellspacing="1" class="table table-sm table-striped  bordered2 table-hover table-bordered '.(!empty($this->table_class) ? $this->table_class :'').' border-light-subtle" border="0" id="parts_table_">
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