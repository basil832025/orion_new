<?php
  // считывание или создание конфига

function get_write_config($module, $admin_module_default=array(),$TinyConfig_button_article_default=array(), $type=0, $site_array_default=array()){
    global $TinyConfig_button;
//запись
$str_conf='';
if ($type==1){
       $str_conf .= '[redaktor]' ."\r\n";
    if (!empty($TinyConfig_button_article_default)){
    foreach ($TinyConfig_button_article_default as $key => $value) {
        if (substr($key, -1)!='_'){
            $str_conf .= $key .'="' .($TinyConfig_button[$key][0]) .',' .$value .',';
        }else{
            $str_conf .= $value .'"'."\r\n";
        }
    } //конец цикла fekv
}
 $str_conf .= '[admin]' ."\r\n";
    foreach ($admin_module_default as $key => $value) {
            $str_conf .= $key .'="' .$value .'"'."\r\n";;
    } //конец цикла fekv
 if (!empty($site_array_default)){

 $str_conf .= '[site]' ."\r\n";
    foreach ($site_array_default as $key => $value) {
            $str_conf .= $key .'="' .$value .'"'."\r\n";;
    } //конец цикла fekv
 }
    list($fp_art) = read_file(ROOT_A .'/modules/'.$module.'/'.$module.'.ini', 2);
    write_file($fp_art, $str_conf);
}elseif ($type==2){ // чтение для сайта
      $site = array();
$form_ = parse_ini_file(ROOT_A .'/modules/'.$module.'/'.$module.'.ini', true);
if (!empty($form_['site'])){
  foreach ($form_['site'] as $key => $value) {
      $site[$key] = $value;
  }
}
return array($site);
}else{ // чтение
      // если нет еще файла конфига
if (!file_exists(ROOT_A .'/modules/'.$module.'/'.$module.'.ini')){
    if (!empty($TinyConfig_button_article_default)){
        $str_conf = '[redaktor]' ."\r\n";
        foreach ($TinyConfig_button_article_default as $key => $value) {
            $str_conf .= $key .'=' .'"' .$value .'"'."\r\n";
        } //конец цикла fekv
    }
     $str_conf .= '[admin]' ."\r\n";
    foreach ($admin_module_default as $key => $value) {
        $str_conf .= $key .'=' .'"' .$value .'"'."\r\n";
    } //конец
    if (!empty($site_array_default)){
        $str_conf .= '[site]' ."\r\n";
        foreach ($site_array_default as $key => $value) {
            $str_conf .= $key .'=' .'"' .$value .'"'."\r\n";
        } //конец цикла fekv
    }

    list($fp_art) = read_file(ROOT_A .'/modules/'.$module.'/'.$module.'.ini', 2);
    write_file($fp_art, $str_conf);
}
  $form_ = parse_ini_file(ROOT_A .'/modules/'.$module.'/'.$module.'.ini', true);
  $redaktor_ =  array();
  $redaktor = array();
  $panel1='';
  $panel2='';
  $panel3='';
  $plagins_=array(15);
  if (!empty($form_['redaktor'])){
  foreach ($form_['redaktor'] as $key => $value) {
      $val_t = explode(',', $value);
      $redaktor_[$key] = $val_t[1];
      $redaktor[$key.'_'] = $val_t[2];
  if (!empty($val_t[1])){
      if (!empty($val_t[2])){
          switch ($val_t[2]) {
             case 1:
                    $panel1 .= $val_t[0] .',';
               break;
             case 2:
                    $panel2 .= $val_t[0] .',';
               break;
             case 3:
                   $panel3 .= $val_t[0] .',';
             break;

          }
      }
      if (!empty($TinyConfig_button[$key][1]) && $TinyConfig_button[$key][1]>0 && !in_array($TinyConfig_button[$key][1], $plagins_)){
        $plagins_[] = $TinyConfig_button[$key][1];
      }
  }
  }
  }
   $admin = array();
if (!empty($form_['admin'])){
  foreach ($form_['admin'] as $key => $value) {
      $admin[$key] = $value;
  }
}
return array($admin, $redaktor_, $redaktor, $panel1,$panel2,$panel3,$plagins_);
}
}
// для новой версии настрйоки в конфиге 
function get_redaktor(){
   global $TinyConfig_button_article_default,$TinyConfig_button; 
     $redaktor_ =  array();
  $redaktor = array();
  $panel1='';
  $panel2='';
  $panel3='';
  $plagins_=array(15);
  if (!empty($TinyConfig_button_article_default)){
  foreach ($TinyConfig_button_article_default as $key => $value) {
     $val_t = explode(',', $value);
     $redaktor_[$key] = $val_t[1];
     $redaktor[$key.'_'] = $val_t[2];
  if (!empty($val_t[1])){
     if (!empty($val_t[2])){
        switch ($val_t[2]) {
          case 1:
               $panel1 .= $val_t[0] .',';
            break;
          case 2:
               $panel2 .= $val_t[0] .',';
            break;
          case 3:
               $panel3 .= $val_t[0] .',';
          break;

        }
     }
     if (!empty($TinyConfig_button[$key][1]) && $TinyConfig_button[$key][1]>0 && !in_array($TinyConfig_button[$key][1], $plagins_)){
      $plagins_[] = $TinyConfig_button[$key][1];
     }
  }
  }
  }
  return array($panel1,$panel2,$panel3,$plagins_);

}
//==================================================================================
//==================================================================================
 //---------------------------------------------------------------------------------
 //                     Функция window_info_var() - выводит в сплывающем окне переменныее PHP
 //---------------------------------------------------------------------------------
 //------------------------ОПИСАНИЕ-------------------------------------------------
 /* функция выводит в сплывающем окне переменныее PHP
 * описание режимов
 * 0 - вывести всю инфу
 * 1 - вывести только переменные пользователя
 * 2 - вывести перемнные сереера и окружения
 * 3 - вывести выводит перемнные post get session cookie files
 *
 *
 */

function window_info_var($regim = 1) {
global $vars_all_mass_test__;
        $str = '';
        if (empty($vars_all_mass_test__)){
            send_error('Нету глобальной переменной $vars_all_mass_test__ ');
         return false;
        }
        unset($vars_all_mass_test__['GLOBALS']);
        unset($vars_all_mass_test__['vars_all_mass_test__']);
        if (isset($vars_all_mass_test__['HTTP_ENV_VARS'])){
                    unset($vars_all_mass_test__['HTTP_ENV_VARS']);
        }
         if (isset($vars_all_mass_test__['HTTP_POST_VARS'])){
                    unset($vars_all_mass_test__['HTTP_POST_VARS']);
         }
        if (isset($vars_all_mass_test__['HTTP_GET_VARS'])){
                    unset($vars_all_mass_test__['HTTP_GET_VARS']);
         }
        if (isset($vars_all_mass_test__['HTTP_COOKIE_VARS'])){
                    unset($vars_all_mass_test__['HTTP_COOKIE_VARS']);
         }
        if (isset($vars_all_mass_test__['HTTP_POST_FILES'])){
                    unset($vars_all_mass_test__['HTTP_POST_FILES']);
         }
        if (isset($vars_all_mass_test__['HTTP_SERVER_VARS'])){
                    unset($vars_all_mass_test__['HTTP_SERVER_VARS']);
         }
         if (isset($vars_all_mass_test__['HTTP_SESSION_VARS'])){
                    unset($vars_all_mass_test__['HTTP_SESSION_VARS']);
         }

        switch ($regim) {
           case 0:
                // вывести всю инфу
               $str ='<pre>' .print_r($vars_all_mass_test__, true) .'</pre>';
             break;
           case 1:
               // вывести только переменные пользователя
                if (isset($vars_all_mass_test__['_ENV'])){
                    unset($vars_all_mass_test__['_ENV']);
                }
                if (isset($vars_all_mass_test__['_SERVER'])){
                    unset($vars_all_mass_test__['_SERVER']);
                }
               if (isset($vars_all_mass_test__['_REQUEST'])){
                    unset($vars_all_mass_test__['_REQUEST']);
                }
               if (isset($vars_all_mass_test__['_FILES'])){
                    unset($vars_all_mass_test__['_FILES']);
                }
               if (isset($vars_all_mass_test__['_GET'])){
                    unset($vars_all_mass_test__['_GET']);
                }
               if (isset($vars_all_mass_test__['_POST'])){
                    unset($vars_all_mass_test__['_POST']);
                }
               if (isset($vars_all_mass_test__['_SERVER'])){
                    unset($vars_all_mass_test__['_SERVER']);
                }
               if (isset($vars_all_mass_test__['_COOKIE'])){
                    unset($vars_all_mass_test__['_COOKIE']);
                }
                if (isset($vars_all_mass_test__['_SESSION'])){
                    unset($vars_all_mass_test__['_SESSION']);
                }

                $str ='<pre>' .print_r($vars_all_mass_test__, true) .'</pre>';
             break;
           case 2:
                // только перемнные сереера и окружения
                 $str .='<pre> Сервер переменные <br />' .print_r($vars_all_mass_test__['_SERVER'], true) .'</pre>';
                 $str .='<pre> Переменные окружения<br />' .print_r($vars_all_mass_test__['_ENV'], true) .'</pre>';
             break;
             case 3:
                // только перемнные post get session cookie files
                 $str .='<pre> _POST переменные <br />' .print_r($vars_all_mass_test__['_POST'], true) .'</pre>';
                 $str .='<pre> Переменные _GET<br />' .print_r($vars_all_mass_test__['_GET'], true) .'</pre>';
                 $str .='<pre> Переменные _SESSION<br />' .print_r((isset($vars_all_mass_test__['_SESSION'])?$vars_all_mass_test__['_SESSION']:''), true) .'</pre>';
                 $str .='<pre> Переменные _COOKIE<br />' .print_r($vars_all_mass_test__['_COOKIE'], true) .'</pre>';
                 $str .='<pre> Переменные _FILES<br />' .print_r((isset($vars_all_mass_test__['_FILES'])? $vars_all_mass_test__['_FILES'] : ''), true) .'</pre>';
             break;
        }

        $str = preg_replace('#(\r\n|\n)#i', '<br />', $str);
        print '<script language="JavaScript">
                <!--
                msgWindowvar_=window.open("","displayWindow", "resizable = yes, width = 700px, height = 700px, scrollbars = yes");
                msgWindowvar_.document.write("<HEAD><TITLE>Окно Переменных</TITLE></HEAD>");
                msgWindowvar_.document.writeln(\'' .addslashes($str) .'\');
                //-->
            </script>';
            unset($vars_all_mass_test__);
     return;
   }
 //==================================================================================
 //==================================================================================
// поиск элемента в многомерном массиве
function in_multiarray($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        while($bottom <= $top)
        {
            if($array[$bottom] == $elem)
                return true;
            else
                if(is_array($array[$bottom]))
                    if(in_multiarray($elem, ($array[$bottom])))
                        return true;

            $bottom++;
        }
        return false;
    }
// поиск ключа в многомерном массиве
function in_multiarray_key($key_, $array){
      foreach ($array as $key => $value) {
            if(array_key_exists($key_, $array))
                return true;
            else
                if(is_array($value))
                    if(in_multiarray($key_, ($value)))
                        return true;

        }
        return false;
    }
    function getListAdmin($id,$field,$name_val,$module,$action) {
    global $form;
       return '<input id="p_'.$id.'_id" name="form['.$id.']" type="hidden" value="'.(!empty($form[$field]) ? $form[$field] : 0).'" >
             <input type="text" id="p_'.$id.'_name" name="p_'.$id.'_name" style="width:80%;" readonly="readonly" value="'.(!empty($name_val) ? $name_val : '').'"/><span style="width:20px;cursor: pointer;background-color:grey;" module="'.$module.'" action="'.$action.'" post_string="&id_spis='.$id.'" return_content_bool="0" blok="0" class="ajax_send">&nbsp;...&nbsp;</span>';

    }
      /*==============================================================
  // iframe для файлов
  $type_view - 1 все файлі, 2 только изображения, 3 - все кроме изображения, 4- много изображений
  //==============================================================*/
/*function upload_iframe($id='',$id_elem=0,$name_field='file',$type_view=1,$max_w,$max_h,$module='',$cnt_files=1){
    $module = $module ? $module : $_SESSION['kernel']['module'];
   //send_error($module);
print '
<input type="hidden" name="form['.$name_field.']" id="'.$name_field.'_bas" value="'.$id.'" />
<IFRAME  marginheight="0" marginwidth=0 height="120px" class="ifram_files" frameborder="0"  src="ufiles.html?module_='.$module.'&max_width='.$max_w.'&max_height='.$max_h.'&id='.$id.'&name_field_='.$name_field.'&type_view_='.$type_view.'&id_elem='.$id_elem.'"  scrolling="no"></IFRAME>

';   
}*/
function upload_img($id='',$id_elem=0,$name_field='file',$type_view=1,$max_w,$max_h,$module='',$cnt_files=1)
{


    return '
        <input   name="'.$name_field.'[type_view_]" type="hidden" value="'.$type_view.'">
        <input   name="'.$name_field.'[max_w]" type="hidden" value="'.$max_w.'">
        <input   name="'.$name_field.'[max_h]" type="hidden" value="'.$max_h.'">
        <input id="'.$name_field.'" required name="'.$name_field.'" type="file" accept="image/*" data-browse-on-zone-click="true">
 
 
';
}
function upload_iframe_($id='',$id_elem=0,$name_field='file',$type_view=1,$max_w,$max_h,$module='',$cnt_files=1){
    //$module = $module ? $module : $_SESSION['kernel']['module'];
    $module = $module ? $module : '';
   //send_error($module);
return '
<input type="hidden" name="form['.$name_field.']" id="'.$name_field.'_bas" value="'.$id.'" />
<IFRAME  marginheight="0" marginwidth=0 width="100%"; height="380px" border="0";  class="ifram_files" frameborder="0"  src="func/ufiles.php?module_='.$module.'&max_width='.$max_w.'&max_height='.$max_h.'&id='.$id.'&name_field_='.$name_field.'&type_view_='.$type_view.'&id_elem='.$id_elem.'&cnt_files='.$cnt_files.'"  scrolling="no"></IFRAME>

';   
}
function upload_iframe_many($id='',$id_elem=0,$name_field='file',$type_view=1,$max_w,$max_h,$module='',$cnt_files=1){
    //$module = $module ? $module : $_SESSION['kernel']['module'];
    $module = $module ? $module : '';
   //send_error($module);
return '
<input type="hidden" name="form['.$name_field.']" id="'.$name_field.'_bas" value="'.$id.'" />
<IFRAME  marginheight="0" marginwidth=0 width="100%"; height="870px" border="0";  class="ifram_files" frameborder="0"  src="ufiles.html?module_='.$module.'&max_width='.$max_w.'&max_height='.$max_h.'&id='.$id.'&name_field_='.$name_field.'&type_view_='.$type_view.'&id_elem='.$id_elem.'&cnt_files='.$cnt_files.'"  scrolling="no"></IFRAME>

';   
}

?>
