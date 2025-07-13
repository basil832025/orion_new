<?php
// масcив для существуещих подмодулей для этого модуля все остальные игнорируются
//$mModuleTest = array('modules_edit', 'modules_edit_ok', 'modules_help', 'modules_nastr','modules_nastr_ok', 'modules_list', '','modules_delete');
// сдесь проверяем действие для даного модуля
$_SESSION['kernel']['action'] = (!empty($_SESSION['kernel']['action']) ? $_SESSION['kernel']['action'] :
    'modules_list');
// 1 цифра в массиве - в джава код кнопки, 2 - активность кнопки, 3 - в какой панели отображается (пока 3 панели придусмотрено)
/*$mTegsTextGlob['submenu'] = array(
'view' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => ''),
'add' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => ''),
'nastr' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => ''),
'back' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => ''),
'save' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => ''),
'help' => array('module' => 'articles', 'action' => 'articles_edit', 'post' => '')
);*/
// выполняем переданое действие для модуля структуры
// общая таблица для всего модуля
// по умолчанию какой делать action когда юзер нажимает F5
$part_id = poste('id');
$parent_id = poste('pid');
  $add_=0;
switch ($_SESSION['kernel']['action']) {
        /**********************************************************************/
    case 'modules_list':
        //get_access_admin('list');
        //---------------------------------------------------------------------
        //send_error('****');
        modules_list();
        break;
    case 'modules_form_list':
        //get_access_admin('list');
        //---------------------------------------------------------------------
        //send_error('****');
        modules_form_list();
        break;
    case 'modules_fields_list':
        //get_access_admin('list');
        //---------------------------------------------------------------------
        //send_error('****');
        modules_fields_list();
        break;
        /**********************************************************************/
    case 'modules_edit':
        get_access_admin('edit');
        //---------------------------------------------------------------------
        $part_id = !empty($part_id) ? $part_id : 0;
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_list',
                'class' => 'ajax_back',
                'post' => ''),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_edit_ok',
                'post' => '',
                'js_func' => ''),
            );
        $_SESSION['kernel']['java_script'] = 'validateForm();_redaktor(\'\',\'\',\'\',\'\',\'simple\',\'text_\');';
        $sql = 'SELECT *, name_' . $language . ' as name FROM `' . T_MODULES .
            '` WHERE id=' . $part_id;
        $form = db_row($sql);
        //    $_SESSION['kernel']['default_action'] = 'modules_edit';
        $_SESSION['kernel']['action'] = 'modules_edit';
      
        //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
        include_once 'html/modules_edit.html';
        break;
         /**********************************************************************/
    case 'modules_field_edit':
        //get_access_admin('edit');
        //---------------------------------------------------------------------
        $part_id = !empty($part_id) ? $part_id : 0;
        $form_id = poste('form_id');
        $field_id = poste('field_id');
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_fields_list',
                'post' => '&id=' . $part_id . '&form_id=' . $form_id),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_field_edit_ok',
                'post' => '',
                'js_func' => ''),
            );
        $_SESSION['kernel']['java_script'] = '';
        $sql = 'SELECT *, name_' . $language . ' as name, name_short_' . $language .
            ' as name_short, name_site_' . $language . ' as name_site FROM `' . T_FORMFIELDS .
            '` where id="' . $field_id . '"';
        $form = db_row($sql);
        if ($form['table_spis']) {
            list($spis_name) = get_spis_list($form['table_spis'], 2);
        }
        //send_error($spis_name);
        //    $_SESSION['kernel']['default_action'] = 'modules_edit';
        $_SESSION['kernel']['action'] = 'modules_filed_edit';
        //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
        include_once 'html/modules_field_edit.html';
        break;
        /**********************************************************************/
         /****************/
         /**********************************************************************/
    case 'modules_feald_add':
        //get_access_admin('edit');
        //---------------------------------------------------------------------
        //$part_id = !empty($part_id) ? $part_id : 0;
        $form_id = poste('form_id');
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_fields_list',
                'post' => '&id=' . $part_id . '&form_id=' . $form_id),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_field_add_ok',
                'post' => '',
                'js_func' => ''),
            );

        //send_error($spis_name);
        //    $_SESSION['kernel']['default_action'] = 'modules_edit';
        $_SESSION['kernel']['action'] = 'modules_filed_list';
        //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
        include_once 'html/modules_field_add.html';
        break;
        /**********************************************************************/
         /**********************************************************************/
    case 'modules_form_edit':
        //get_access_admin('edit');
        //---------------------------------------------------------------------
        $part_id = !empty($part_id) ? $part_id : 0;
        $form_id = poste('form_id');
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_form_list',
                'post' => '&id=' . $part_id),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_form_edit_ok',
                'post' => '',
                'js_func' => 'send_edit_modules()'),
            'nastr' => array(
                'module' => 'modules',
                'action' => 'modules_nastr',
                'post' => ''),
            );
        $_SESSION['kernel']['java_script'] = '_redaktor(\'\',\'\',\'\',\'\',\'simple\',\'text_\');tabs();';
        $sql = 'SELECT *, name_' . $language . ' as name, text_' . $language .
            ' as text,name_for_site_' . $language . ' as name_for_site, name_for_list_' . $language .
            ' as name_for_list, name_for_edit_' . $language .
            ' as name_for_edit, name_for_add_' . $language . ' as name_for_add FROM `' .
            T_FORMMASTER . '` where id="' . $form_id . '"';
        $form = db_row($sql);
        //   $form['table_order']='select '
        //    $_SESSION['kernel']['default_action'] = 'modules_edit';
        $_SESSION['kernel']['action'] = 'modules_form_edit';
        //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
        include_once 'html/modules_form_edit.html';
        break;
        /**********************************************************************/
    case 'modules_field_edit_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');

        // блок определяет поле сортировки и принадлежности детей к родителям
        $part_id = !empty($part_id) ? $part_id : 0;

        $sql = "update `" . T_FORMFIELDS . "`  SET 
             " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .
            '",' : '') . "
            " . (isset($form['type']) ? 'type="' . $form['type'] . '",' : '') .
            "
            " . (isset($form['comment']) ? 'comment_' . $language . '="' . $form['comment'] .
            '",' : '') . "
            " . (isset($form['name_field']) ? 'name_field="' . $form['name_field'] .
            '",' : '') . "
            " . (isset($form['name_bd']) ? 'name_bd="' . $form['name_bd'] . '",' :
            '') . "
            " . (isset($form['spis_id']) ? 'table_spis="' . $form['spis_id'] .
            '",' : '') . "
            " . (isset($form['value_default']) ? 'value_default="' . $form['value_default'] .
            '",' : '') . "
            " . (isset($form['vuvod_site_admin']) ? 'vuvod_site_admin="' . $form['vuvod_site_admin'] .
            '",' : '') . "
            " . (isset($form['sort']) ? 'sort="' . $form['sort'] . '",' : '') .
            "
            " . (isset($form['name_short']) ? 'name_short_' . $language . '="' .
            $form['name_short'] . '",' : '') . "
            " . (isset($form['name_site']) ? 'name_site_' . $language . '="' . $form['name_site'] .
            '",' : '') . "
            
            " . (isset($form['max_value_bd_field']) ? 'max_value_bd_field="' . $form['max_value_bd_field'] .
            '",' : '') . "
            " . (isset($form['type_filter']) ? 'type_filter="' . $form['type_filter'] .
            '",' : '') . "
            " . (isset($form['type_char']) ? 'type_char="' . $form['type_char'] .
            '",' : '') . "
            " . (isset($form['cpez_char']) ? 'cpez_char="' . $form['cpez_char'] .
            '",' : '') . "
            " . (isset($form['min_char']) ? 'min_char="' . $form['min_char'] .
            '",' : '') . "
            " . (isset($form['regular']) ? 'regular="' . $form['regular'] . '",' :
            '') . "
            " . (isset($form['mask']) ? 'mask="' . $form['mask'] . '",' : '') .
            "
            " . (isset($form['form_id']) ? 'mask="' . $form['mask'] . '",' : '') .
            "
              value_obyaz=" . (!empty($form['value_obyaz']) ? 1 : 0) . ",
              filter1=" . (!empty($form['filter1']) ? 1 : 0) . ",
              filter2=" . (!empty($form['filter2']) ? 1 : 0) . ",
              filter3=" . (!empty($form['filter3']) ? 1 : 0) . ",
              active=" . (!empty($form['active']) ? 1 : 0) . "
             where id=" . $form['id'];

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_fields_list();
        break;
        /**********************************************************************/
    case 'modules_field_add_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');
        $form_id = poste('form_id');
        //send_error(p($_POST,1));
        // блок определяет поле сортировки и принадлежности детей к родителям
        //$part_id = !empty($part_id) ? $part_id : 0;

        $sql = "insert into `" . T_FORMFIELDS . "`  SET 
             " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .
            '",' : '') . "
            " . (isset($form['type']) ? 'type="' . $form['type'] . '",' : '') .
            "
            " . (isset($form['comment']) ? 'comment_' . $language . '="' . $form['comment'] .
            '",' : '') . "
            " . (isset($form['name_field']) ? 'name_field="' . $form['name_field'] .
            '",' : '') . "
            " . (isset($form['name_bd']) ? 'name_bd="' . $form['name_bd'] . '",' :
            '') . "
              " . (isset($form['value_default']) ? 'value_default="' . $form['value_default'] .
            '",' : '') . "
            " . (isset($form['vuvod_site_admin']) ? 'vuvod_site_admin="' . $form['vuvod_site_admin'] .
            '",' : '') . "
            " . (isset($form['sort']) ? 'sort="' . $form['sort'] . '",' : '') .
            "
            " . (isset($form['name_short']) ? 'name_short_' . $language . '="' .
            $form['name_short'] . '",' : '') . "
            " . (isset($form['name_site']) ? 'name_site_' . $language . '="' . $form['name_site'] .
            '",' : '') . "
              value_obyaz=" . (!empty($form['value_obyaz']) ? 1 : 0) . ",
              active=" . (!empty($form['active']) ? 1 : 0) . ",
              form_id=" . $form_id . '
              ';

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_fields_list();
        break;
    case 'spis_light':
        $id_spis = poste('id_spis');
        $form = get_spis_list();
        $_SESSION['kernel']['close_'] = '0';
        $first_name = '';
        $_SESSION['kernel']['java_script'] = 'spis_select("Выбирете список", "' . $id_spis .
            '")';
        include_once ROOT . 'adminsite/html/select_spis.html';
        break;
        /**********************************************************************/
    case 'spis_module':
        $id_spis = poste('id_spis');
        $form = get_spis_list(0, 4);
        $_SESSION['kernel']['close_'] = '0';
        $first_name = '';
        $_SESSION['kernel']['java_script'] = 'spis_select("Выбирете список", "' . $id_spis .
            '")';
        include_once ROOT . 'adminsite/html/select_spis.html';
        break;
        /**********************************************************************/
    case 'modules_form_edit_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');
        send_error(p($form, 1));
        // блок определяет поле сортировки и принадлежности детей к родителям
        $part_id = !empty($part_id) ? $part_id : 0;
        $form_id = poste('form_id');
        $sql = "update `" . T_FORMMASTER . "`  SET 
             " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .
            '",' : '') . "
            " . (isset($form['text']) ? 'text_' . $language . '="' . $form['text'] .
            '",' : '') . "
            " . (isset($form['form_teg']) ? 'form_teg="' . $form['form_teg'] .
            '",' : '') . "
          
            " . (isset($form['type_form']) ? 'type_form="' . $form['type_form'] .
            '",' : '') . "
            " . (isset($form['name_for_site']) ? 'name_for_site_' . $language .
            '="' . $form['name_for_site'] . '",' : '') . "
            " . (isset($form['name_for_list']) ? 'name_for_list_' . $language .
            '="' . $form['name_for_list'] . '",' : '') . "
            " . (isset($form['name_for_edit']) ? 'name_for_edit_' . $language .
            '="' . $form['name_for_edit'] . '",' : '') . "
            " . (isset($form['name_for_add']) ? 'name_for_add_' . $language .
            '="' . $form['name_for_add'] . '",' : '') . "
            " . (isset($form['view_text']) ? 'view_text="' . $form['view_text'] .
            '",' : '') . "
            " . (isset($form['shablon_up']) ? 'shablon_up="' . $form['shablon_up'] .
            '",' : '') . "
            " . (isset($form['shablon_down']) ? 'shablon_down="' . $form['shablon_down'] .
            '",' : '') . "
            " . (isset($form['shablon_row']) ? 'shablon_row="' . $form['shablon_row'] .
            '",' : '') . "
            " . (isset($form['table_order']) ? 'table_order="' . $form['table_order'] .
            '",' : '') . "
            " . (isset($form['parent_form']) ? 'parent_form="' . $form['parent_form'] .
            '",' : '') . "
            " . (isset($form['field_key']) ? 'field_key="' . $form['field_key'] .
            '",' : '') . "
              
              is_email=" . (!empty($form['is_email']) ? 1 : 0) . ",
              is_email_admin=" . (!empty($form['is_email_admin']) ? 1 : 0) . ",
              delete_=" . (!empty($form['delete_']) ? 1 : 0) . ",
              delete_view=" . (!empty($form['delete_view']) ? 1 : 0) . ",
              active_view=" . (!empty($form['active_view']) ? 1 : 0) . ",
              active=" . (!empty($form['active']) ? 1 : 0) . "
             where id=" . $form['id'];

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_form_list();
        break;
    case 'modules_add':
        get_access_admin('add');
        $level = poste('level');
        //---------------------------------------------------------------------
        $_SESSION['kernel']['action'] = 'modules_list';
        $_SESSION['kernel']['default_action'] = 'modules_list';
        // сдесь формируем код Javascript для редактора
        $_SESSION['kernel']['java_script'] = 'mask_();_redaktor(\'\',\'\',\'\',\'\',\'simple\',\'text_\');';
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_list',
                'post' => ''),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_add_ok',
                'post' => '',
                'js_func' => ''),
            );
        $add_ = 1;
        $form['type']=1;
        include_once 'html/modules_edit.html';
        break;
    case 'modules_form_add':
        get_access_admin('add');
        $level = poste('level');
        //---------------------------------------------------------------------
        $_SESSION['kernel']['action'] = 'modules_form_list';
        $_SESSION['kernel']['default_action'] = 'modules_form_list';
        // сдесь формируем код Javascript для редактора
        // $_SESSION['kernel']['java_script'] ='mask_();_redaktor(\'\',\'\',\'\',\'\',\'simple\',\'text_\');';
        $mTegsTextGlob['submenu'] = array(
            'back' => array(
                'module' => 'modules',
                'action' => 'modules_form_list',
                'post' => ''),
            'save' => array(
                'module' => 'modules',
                'action' => 'modules_form_add_ok',
                'post' => '',
                'js_func' => ''),
            );
        $add_ = 1;
        include_once 'html/modules_form_edit.html';
        break;
    case 'modules_form_add_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');

        // блок определяет поле сортировки и принадлежности детей к родителям
        $part_id = !empty($part_id) ? $part_id : 0;
        $form_id = poste('form_id');
        $sql = "insert into `" . T_FORMMASTER . "`  SET 
             " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .
            '",' : '') . "
            " . (isset($form['text']) ? 'text_' . $language . '="' . $form['text'] .
            '",' : '') . "
            " . (isset($form['form_teg']) ? 'form_teg="' . $form['form_teg'] .
            '",' : '') . "
          
            " . (isset($form['type_form']) ? 'type_form="' . $form['type_form'] .
            '",' : '') . "
            " . (isset($form['name_for_site']) ? 'name_for_site_' . $language .
            '="' . $form['name_for_site'] . '",' : '') . "
            " . (isset($form['name_for_list']) ? 'name_for_list_' . $language .
            '="' . $form['name_for_list'] . '",' : '') . "
            " . (isset($form['name_for_edit']) ? 'name_for_edit_' . $language .
            '="' . $form['name_for_edit'] . '",' : '') . "
            " . (isset($form['name_for_add']) ? 'name_for_add_' . $language .
            '="' . $form['name_for_add'] . '",' : '') . "
            " . (isset($form['view_text']) ? 'view_text="' . $form['view_text'] .
            '",' : '') . "
            " . (isset($form['shablon_up']) ? 'shablon_up="' . $form['shablon_up'] .
            '",' : '') . "
            " . (isset($form['shablon_down']) ? 'shablon_down="' . $form['shablon_down'] .
            '",' : '') . "
            " . (isset($form['shablon_row']) ? 'shablon_row="' . $form['shablon_row'] .
            '",' : '') . "
            " . (isset($form['table_order']) ? 'table_order="' . $form['table_order'] .
            '",' : '') . "
            " . (isset($form['parent_form']) ? 'parent_form="' . $form['parent_form'] .
            '",' : '') . "
            " . (isset($form['field_key']) ? 'field_key="' . $form['м'] . '",' :
            '') . "
              is_email=" . (!empty($form['is_email']) ? 1 : 0) . ",
              is_email_admin=" . (!empty($form['is_email_admin']) ? 1 : 0) . ",
              delete_=" . (!empty($form['delete_']) ? 1 : 0) . ",
              delete_view=" . (!empty($form['delete_view']) ? 1 : 0) . ",
              active_view=" . (!empty($form['active_view']) ? 1 : 0) . ",
              active=" . (!empty($form['active']) ? 1 : 0);

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_form_list();
        break;
    case 'modules_edit_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');
        // send_error(p($form,1));
        // блок определяет поле сортировки и принадлежности детей к родителям
        $part_id = !empty($part_id) ? $part_id : 0;

        $sql = "update " . T_MODULES . "  SET 
             " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .'",' : '') . "
             " . (isset($form['mname']) ? 'mname="' . $form['mname'] .'",' : '') . "
            " . (isset($form['text']) ? 'text_' . $language . '="' . $form['text'] . '",' : '') . "
            " . (isset($form['table_name']) ? 'table_name="' . $form['table_name'] . '",' : '') . "
            " . (isset($form['type']) ? 'type="' . $form['type'] . '",' : '') . "
            " . (isset($form['lang_type']) ? 'lang_type="' . $form['lang_type'] . '",' : '') . "
               slug_module=" . (!empty($form['slug_module']) ? 1 : 0) . ",
              active=" . (!empty($form['active']) ? 1 : 0) . "
             where id=" . $form['id'];

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_list();
        break;

        /**********************************************************************/
    case 'modules_add_ok':
        //---------------------------------------------------------------------
        get_access_admin('save');
        $form = poste('form');
        $level = poste('level');
        // send_error(p($form,1));
        $sql = 'SELECT sort FROM `' . T_MODULES . '` WHERE pid=' . $parent_id .
            ' ORDER by sort desc LIMIT 1';
        $sort_tree_id = db_field($sql, 'sort');

        if ($sort_tree_id) {
            $sort_tree_id++;
        } else {
            $sort_tree_id = 1;
        }
        // блок определяет поле сортировки и принадлежности детей к родителям
        $part_id = !empty($part_id) ? $part_id : 0;

        $sql = "insert into " . T_MODULES . "  SET 
            " . (isset($form['name']) ? 'name_' . $language . '="' . $form['name'] .'",' : '') . "
            " . (isset($form['mname']) ? 'mname="' . $form['mname'] .'",' : '') . "
            " . (isset($form['text']) ? 'text_' . $language . '="' . $form['text'] . '",' : '') . "
            " . (isset($form['table_name']) ? 'table_name="' . $form['table_name'] . '",' : '') . "
            " . (isset($form['type']) ? 'type="' . $form['type'] . '",' : '') . "
            " . (isset($form['lang_type']) ? 'lang_type="' . $form['lang_type'] . '",' : '') . "
                   slug_module=" . (!empty($form['slug_module']) ? 1 : 0) . ",
              module_use=1,
              level=" . ($level + 1) . ",
               sort='" . $sort_tree_id . "',
               pid='" . $parent_id . "',
              active= 1";

        //user_error('не зашли <br />'. $sql , E_USER_ERROR);
        db_query($sql);
        modules_list();
        break;

        /**********************************************************************/
        /**********************************************************************/
    case 'modules_delete':
        get_access_admin('del');
        //---------------------------------------------------------------------
        $sql = 'delete from `' . T_MODULES . '`    WHERE id=' . $part_id;
        db_query($sql);
        // вывод html страници структуры дерва
        modules_list();
        break;
        /**********************************************************************/
    default:
} // конец switch

function modules_list()
{
    global $mTegsTextGlob, $form, $TinyConfig_button, $TinyConfig_button_article_default,
        $part_id, $admin_article_default, $pagging_html, $language;
    // $part_id = poste('part_id');
    $mTegsTextGlob['submenu'] = array('add' => array(
            'module' => 'modules',
            'action' => 'modules_add',
            'post' => ''), );
    // test_create_table(T_NEWS,'news');
    //$page = (!empty($_SESSION[$_SESSION['kernel']['module']]['page']) ? $_SESSION[$_SESSION['kernel']['module']]['page'] : 0);
    // еще нету записи в БД, то делаем ее
    $form = get_tree_level(db_list('select *, name_' . $language . ' as name, text_' .
        $language . ' as text from `' . T_MODULES .
        '` where  active=1 order by sort,id '));//slug_module=0 and
    // send_error(p($form,1));
    //user_error('Ошибочный запрос: '.$pagging_html, E_USER_ERROR);
    $_SESSION['kernel']['default_action'] = 'modules_list';
    //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
    include_once 'html/modules_list.html';
}
function modules_form_list()
{
    global $mTegsTextGlob, $form, $TinyConfig_button, $TinyConfig_button_article_default,
        $part_id, $admin_article_default, $pagging_html, $language;
    // $part_id = poste('part_id');
    $part_id = !empty($part_id) ? $part_id : 0;
    $mTegsTextGlob['submenu'] = array(
        'back' => array(
            'module' => 'modules',
            'action' => 'modules_list',
            'post' => ''),
        'add' => array(
            'module' => 'modules',
            'action' => 'modules_form_add',
            'post' => ''),
        );
    // test_create_table(T_NEWS,'news');
    //$page = (!empty($_SESSION[$_SESSION['kernel']['module']]['page']) ? $_SESSION[$_SESSION['kernel']['module']]['page'] : 0);
    // еще нету записи в БД, то делаем ее
    $sql = 'select *, name_' . $language . ' as name, text_' . $language .
        ' as text,name_for_site_' . $language . ' as name_for_site, name_for_list_' . $language .
        ' as name_for_list, name_for_edit_' . $language .
        ' as name_for_edit, name_for_add_' . $language . ' as name_for_add from `' .
        T_FORMMASTER . '` where module="' . $part_id . '"';
    //  send_error($sql);
    //send_error($sql);
    $form = db_list($sql);
    //send_error(p($form),1);
    //user_error('Ошибочный запрос: '.$pagging_html, E_USER_ERROR);
    $_SESSION['kernel']['default_action'] = 'modules_form_list';
    //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
    include_once 'html/modules_form_list.html';
}
function modules_fields_list()
{
    global $mTegsTextGlob, $form, $part_id, $pagging_html, $language, $parent_id;
    $form_id = poste('form_id');
    $part_id = !empty($part_id) ? $part_id : 0;
    $mTegsTextGlob['submenu'] = array(
        'back' => array(
            'module' => 'modules',
            'action' => 'modules_form_list',
            'post' => '&id=' . $part_id),
        'add' => array(
            'module' => 'modules',
            'action' => 'modules_feald_add',
            'post' => '&pid=' . $parent_id . '&form_id=' . $form_id),
        );
    // test_create_table(T_NEWS,'news');
    //$page = (!empty($_SESSION[$_SESSION['kernel']['module']]['page']) ? $_SESSION[$_SESSION['kernel']['module']]['page'] : 0);
    // еще нету записи в БД, то делаем ее
    $sql = 'select *, name_' . $language . ' as name  from `' . T_FORMFIELDS .
        '` where form_id="' . $form_id . '" order by sort,id';
    //send_error($sql);
    $form = db_list($sql);
    foreach ($form as $key => $value) {
        switch ($value['type']) {
            case 1:
                $aFieldName = 'Строковое поле';
                break;
            case 2:
                $aFieldName = 'Признак (checkbox)';
                break;
            case 3:
                $aFieldName = 'Малый список (radio)';
                break;
            case 4:
                $aFieldName = 'Список упрощенный (select обычный)';
                break;
            case 5:
                $aFieldName = 'Многостроковое поле (textarea без редактора)';
                break;
            case 6:
                $aFieldName = 'Поле для пароля';
                break;
            case 7:
                $aFieldName = 'Скрытое поле';
                break;
            case 8:
                $aFieldName = 'Поле для даты (календарь)';
                break;
            case 9:
                $aFieldName = 'Список в модальном окне (продвин.)';
                break;
            case 10:
                $aFieldName = 'Список с удобной прокруткой (продвин.)';
                break;
            case 11:
                $aFieldName = 'Текстовый редактор (сокращенный)';
                break;
            case 12:
                $aFieldName = 'Текстовый редактор (полный)';
                break;
            case 13:
                $aFieldName = 'Загрузка изображений';
                break;
            case 14:
                $aFieldName = 'Загрузка файлов';
                break;
            case 15:
                $aFieldName = 'Кнопка';
                break;
            case 20:
                $aFieldName = 'Связь с родителем';
                break;

        }
        $form[$key]['type_field_name'] = $aFieldName;
    }
    $sql = 'select name_' . $language . ' as name from `' . T_FORMMASTER .
        '` where id="' . $form_id . '"';
    //send_error($sql);
    $name_form = db_field($sql, 'name');
    //user_error('Ошибочный запрос: '.$pagging_html, E_USER_ERROR);
    $_SESSION['kernel']['default_action'] = 'modules_form_list';
    //user_error('не зашли '.$part_id.'<br /> '.p($form,0,1) , E_USER_ERROR);
    include_once 'html/modules_fields_list.html';
}
?>