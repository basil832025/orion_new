<?php
// класс возвращает и обрабатывает для вывода поля формы
class formField 
{
 public $aEditField = array(); // массив полей 
 protected $isAdd = 0; // если это не добавление 
 public $shablon_text = ''; // вывод полей накапливается в эту переменную
 protected $javaScrArr = []; // массив javascript  функция для иницилизации
 protected $thVdata = array(); // массив настроек для одного поля
 protected $aData = array(); // массив данных запроса
 protected $module = ''; // массив данных запроса
 protected $id = 0; // массив данных запроса
 protected $Tabs_ = 0; // массив данных запроса
 
 public function __construct($aEditField = array(),$aData = array(),$module = '',$id=0) // конструктор
  {
    $this->aEditField = $aEditField; 
    $this->aData = $aData;
    $this->module = $module;
    $this->id = $id;
   } 
 public function init()
 {  
    //   $objAdmin_gen_form = new admin_gen_form(); 
    /*$this->aEditField =  $this->getaEditField();
    //s($this->aEditField);
    $this->aData =  $this->getAdata();
    $this->module =  $this->getModule();
    $this->isAdd =  $this->getIsAdd();
    $this->id =  $this->getId();*/
 }
 public function genFormFields()
 {
         foreach ($this->aEditField as $kData => $vData) {
                $this->thVdata = $vData;
               // s($vData);
                $type_f = !empty($vData['type']) ? $vData['type'] : 'text';
                if ($this->isAdd && $type_f == 'parent')
                    continue;
               if ($type_f=='TextNoSQL') $type_f='Text';     
              // если сущесвует метод действие, то вызываем его
                if (method_exists($this, 'getField' . $type_f)) {
                    call_user_func(array($this, 'getField' . $type_f));
                }
            
            }
            $this->shablon_text .= '</table>
   <link rel="stylesheet" href="plugins/bootstrap-icons/font/bootstrap-icons.css" crossorigin="anonymous">
      <link href="plugins/fileinput/css/fileinput.css" media="all" rel="stylesheet" type="text/css"/>
     <script src="plugins/fileinput/js/buffer.min.js" type="text/javascript"></script>
   <script src="plugins/fileinput/js/filetype.min.js" type="text/javascript"></script>
     <script src="plugins/fileinput/js/fileinput.js" type="text/javascript"></script>
 ' ;
           // s($this->shablon_text);
        return $this->getShablon();    
 }  

 function getFieldText()
    { //s($this->aData);
      //  if (!in_array('validateForm()', $this->javaScrArr))
         //   $this->javaScrArr[] = 'validateForm()';
        $width_default = ($_SESSION['is_mobile'] ) ? 80 : 280;
        $w_75 = ($_SESSION['is_mobile'] ) ? '' : 'w-75';
        $w_input = ($_SESSION['is_mobile'] ) ? '95' : '80';
        $field_show = !empty($this->thVdata['field_show']) && $this->thVdata['field_show']=='hide' ? 'style="display: none;"' : '';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'" '.$field_show.'>
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '" class="mob_width_edit" 
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                $width_default) . 'px">' . $this->thVdata['name'] . '</td>
        <td class="mob_edit_field"><div class="position-relative"> <input autocomplete="off" type="text" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '"  '.(!empty($this->thVdata['size']) ? 'size="'.$this->thVdata['size'].'"':'style="width:'.$w_input.'%;"').'  
             '.(!empty($this->thVdata['maxlength']) ? 'maxlength="'.$this->thVdata['maxlength'].'"':'').'
         '.(!empty($this->thVdata['speedsearch']['min_letter']) ? 'speedsearch="'.$this->thVdata['speedsearch']['min_letter'].'" ' : '').
        (!empty($this->thVdata['speedsearch']['result_fields_dop']) ? 'result_fields_dop="'. base64_encode(json_encode($this->thVdata['speedsearch']['result_fields_dop'])).'" ' : '').
        (!empty($this->thVdata['speedsearch']['where']) ? 'where="'.implode(',',base64_encode($this->thVdata['speedsearch']['where'])).'" ' : '').
         (!empty($this->thVdata['required']) ?
             (!empty($this->thVdata['pattern']) ? 'pattern="'.$this->thVdata['pattern'].'"' : '').'    required=""  class="'.$w_75.' form-control input-sm  text-input"' : 'class="form-control  input-sm"') . ' 
        ' . (!empty($this->thVdata['readonly']) && $this->thVdata['readonly'] ==
            1 ? 'readonly="readonly"' : '') . '
             value="' . (isset($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '"  />   ' . (!empty($this->thVdata['required']) ? ' <div  class="invalid-feedback" data-bs-toggle="tooltip" data-bs-placement="right">'.$this->thVdata['required'].'</div>':''). '</div></td></tr>';
    }
    function getFieldProstSprEdit()
    { //s($this->aData);
      //  if (!in_array('validateForm()', $this->javaScrArr))
         //   $this->javaScrArr[] = 'validateForm()';
        $width_default2 = ($_SESSION['is_mobile'] ) ? 95 : 30;

        $this->javaScrArr[]='chosen_vibor("'.$width_default2.'%");';
        // если это не стандарный справочник то указываем таблицу и берем значения с неее
        if (!empty($this->thVdata['table'])){
            $where = !empty($this->thVdata['where']) ? ' where '.$this->thVdata['where'] : '';
            $sql = 'SELECT *, '.$this->thVdata['out_result_field'].' as name FROM `' . $this->thVdata['table'] .
                '` '.$where.'  ORDER by id';

        }
        else
            $sql = 'SELECT *, value as name FROM `' . T_SPRLIST_VALUES .
                '` where id_spis='.$this->thVdata['id_spis'].' and active=1   ORDER by name';
        $aProstSpr_ = db_list($sql);
        $aProstSpr[0]=['id'=>0,'id_spis'=>$this->thVdata['id_spis'],'name'=>'Виберіть значення','active'=>0];
        $aProstSpr= array_merge($aProstSpr,$aProstSpr_);
        if (!empty($this->thVdata['isFirstElem'])) $aProstSpr=array_merge($this->thVdata['isFirstElem'],$aProstSpr);
        $disabled = !empty($this->thVdata['disabled']) ? 'disabled' : '';
        $selected='';$data_id='';
        if (!empty($this->aData[$this->thVdata['name_field']])) $data_id=$this->aData[$this->thVdata['name_field']];
        $data_id = $data_id=='' && !empty($this->thVdata['def']) ? $this->thVdata['def'] : $data_id;
        //  $val_select = $data_id ? 'value="'.$data_id.'"'  : '';
        $name_field =  !empty($this->thVdata['name_field']) ? $this->thVdata['name_field'] :$this->thVdata['name_field_virt'] ;
        $sSpis = '<select class="chosen-select " '.$disabled.' tabindex="5"   id="Prostid'.$name_field.'">';
        foreach ($aProstSpr as $elem)
        {
            if (!empty($data_id))
                $selected= $elem['id']==$data_id ? 'selected="selected"' : '';
            $sSpis.='<option '.$selected.' value="'.$elem['id'].'" >'.$elem['name'].'</option>';

        }
        $sSpis.=  '</select>';


        $width_default = ($_SESSION['is_mobile'] ) ? 80 : 100;
        $w_75 = ($_SESSION['is_mobile'] ) ? '' : '';
        $w_input = ($_SESSION['is_mobile'] ) ? '95' : '50';
        $field_show = !empty($this->thVdata['field_show']) && $this->thVdata['field_show']=='hide' ? 'style="display: none;"' : '';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'" '.$field_show.'>
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '" class="mob_width_edit" 
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                $width_default) . 'px">' . $this->thVdata['name'] . '</td>
        <td class="mob_edit_field"><div class=" input-group"> 
        <div class="input-group-prepend w-50"><input autocomplete="off" type="text" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '"  '.(!empty($this->thVdata['size']) ? 'size="'.$this->thVdata['size'].'"':'style="width:'.$w_input.'%;"').'  
             '.(!empty($this->thVdata['maxlength']) ? 'maxlength="'.$this->thVdata['maxlength'].'"':'').'
         '.(!empty($this->thVdata['speedsearch']['min_letter']) ? 'speedsearch="'.$this->thVdata['speedsearch']['min_letter'].'" ' : '').
        (!empty($this->thVdata['speedsearch']['result_fields_dop']) ? 'result_fields_dop="'. base64_encode(json_encode($this->thVdata['speedsearch']['result_fields_dop'])).'" ' : '').
        (!empty($this->thVdata['speedsearch']['where']) ? 'where="'.implode(',',base64_encode($this->thVdata['speedsearch']['where'])).'" ' : '').
         (!empty($this->thVdata['required']) ?
             (!empty($this->thVdata['pattern']) ? 'pattern="'.$this->thVdata['pattern'].'"' : '').'    required=""  class="'.$w_75.' form-control input-sm  text-input"' : 'class="  input-sm"') . ' 
        ' . (!empty($this->thVdata['readonly']) && $this->thVdata['readonly'] ==
            1 ? 'readonly="readonly"' : '') . '
             value="' . (isset($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '"  />   ' . (!empty($this->thVdata['required']) ? ' <div  class="invalid-feedback" data-bs-toggle="tooltip" data-bs-placement="right">'.$this->thVdata['required'].'</div>':''). '
             </div>'.$sSpis.'
             
            
             </div>
             </td></tr>';
    }
    
 function getFieldTextOutKey()
    { //s($this->thVdata);
    //s('tyt');
        //if (!in_array('validateForm()', $this->javaScrArr))
          //  $this->javaScrArr[] = 'validateForm()';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'">
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td ><input autocomplete="off" type="text" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '"  '.(!empty($this->thVdata['size']) ? 'size="'.$this->thVdata['size'].'"':'style="width:80%;"').'  
             '.(!empty($this->thVdata['maxlength']) ? 'maxlength="'.$this->thVdata['maxlength'].'"':'').'
         '.(!empty($this->thVdata['speedsearch']['min_letter']) ? 'speedsearch="'.$this->thVdata['speedsearch']['min_letter'].'" ' : '').
        (!empty($this->thVdata['speedsearch']['result_fields_dop']) ? 'result_fields_dop="'.base64_encode(json_encode($this->thVdata['speedsearch']['result_fields_dop'])).'" ' : '').
         (!empty($this->thVdata['required']) ?
            'class="validate[required '.(!empty($this->thVdata['required_custom']) ?',custom['.$this->thVdata['required_custom'].']':'').'] text-input"' : '') . ' 
        ' . (!empty($this->thVdata['readonly']) && $this->thVdata['readonly'] ==
            1 ? 'readonly="readonly"' : '') . '
             value="' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '"  /></td></tr>';
    }

    function getFieldPass()
    {
     /*   if (!in_array('validateForm()', $this->javaScrArr))
            $this->javaScrArr[] = 'validateForm()';*/
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'">
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td ><input type="password" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '" 
        style="width:80%;" ' . (!empty($this->thVdata['required']) ?
            'class="validate[required] text-input"' : '') . ' 
        ' . (!empty($this->thVdata['readonly']) && $this->thVdata['readonly'] ==
            1 ? 'readonly="readonly"' : '') . '
             value="' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '" /></td></tr>';
    }
    function getFieldFunc(){
        $content ='';
                    $class = !empty($this->thVdata['classAlign']) ? $this->thVdata['classAlign'] : 'td_align_center';

                    $function = !empty($this->thVdata['function']) ? $this->thVdata['function'] : '';
         //   s('function='.$function);
                            if (!empty($function) && function_exists($function)){
                                  $id = $this->aData['id'] ?? 0;
                                $content .=    call_user_func_array($function,array($this->thVdata['name_field'],$id,$this->aData));
                            }

        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'">
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td >'.$content.'</td></tr>';

    }
    function getFieldDate()
    {
        $w_25 = ($_SESSION['is_mobile'] ) ? '' : 'w-25';
       if (!in_array('date_input()', $this->javaScrArr))
            $this->javaScrArr[] = 'date_input()';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'">
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td ><div class="ui-widget"><div class="position-relative"><input type="text" '.   (!empty($this->thVdata['required']) ?
                (!empty($this->thVdata['pattern']) ? 'pattern="'.$this->thVdata['pattern'].'"' : '').'    required=""  ' : '')
    .' name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '" 
        class="datepicker form-control '.$w_25.'  date_input" ' . (!empty($this->thVdata['readonly']) &&
            $this->thVdata['readonly'] == 1 ? 'readonly="readonly"' : '') . '
             value="' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '" />'. (!empty($this->thVdata['required']) ? ' <div  class="invalid-feedback">'.$this->thVdata['required'].'</div>':''). '</div></div></td></tr>';
    }
    function getFieldImg()
    {
        $w_input = ($_SESSION['is_mobile'] ) ? '95' : '80';

        // найдем нужную картинку  в базе
        // Используем ObjectRT::getTableModule() если доступно, иначе get_table_name для обратной совместимости
        $table_name = ObjectRT::getTableModule();
        if (empty($table_name)) {
            $table_name = get_table_name($this->module);
        }
        // Проверяем, что id не пустой (для новой записи не ищем картинку)
        if (empty($this->id) || $this->id == 0) {
            $this->javaScrArr[] = 'fileinput_init("","'.$this->thVdata['name_field'].'")';
            $this->shablon_text .= '<tr  >
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td valign="top" width="'.$w_input.'%"> ' . upload_img((!empty($this->aData[$this->
            thVdata['name_field']]) ? $this->aData[$this->thVdata['name_field']] : ''), $this->
            id, $this->thVdata['name_field'], 2, 180, 180,$this->module) . '
        </td>
        </tr>';
            return;
        }
        $sql = 'select f.name,f.id from `'.$table_name.'` m,'.T_FILES.' f where 
          f.id=m.'.$this->thVdata['name_field'].' and   m.id='.$this->id;
       $aFile=db_row($sql);
       if (!empty($aFile))
       {
           $name_file=$aFile['name'];
           $id_file=$aFile['id'];
           $name_file_full='uploads/files_site/'.$name_file;
           $this->javaScrArr[] = 'fileinput_init("'.$name_file_full.'","'.$this->thVdata['name_field'].'","'.$name_file.'",'.$id_file.')';

       }else
           $this->javaScrArr[] = 'fileinput_init("","'.$this->thVdata['name_field'].'")';

        $this->shablon_text .= '<tr  >
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td valign="top" width="'.$w_input.'%"> ' . upload_img((!empty($this->aData[$this->
            thVdata['name_field']]) ? $this->aData[$this->thVdata['name_field']] : ''), $this->
            id, $this->thVdata['name_field'], 2, 180, 180,$this->module) . '
        </td>
        </tr>';
    }
    
        function getFieldImgMany()
    {
            
        $this->shablon_text .= '<tr  >
        <td colspan="2" valign="top" align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">
       ' . upload_iframe_many((!empty($this->aData[$this->
            thVdata['name_field']]) ? $this->aData[$this->thVdata['name_field']] : ''), $this->
            id, $this->thVdata['name_field'], 4, 180, 180,$this->module,$this->thVdata['imgCnt']) . '
        </td>
        </tr>';
    }
    function getFieldFile()
    {
        $this->shablon_text .= '<tr  >
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td valign="top"> ' . upload_iframe_((!empty($this->aData[$this->
            thVdata['name_field']]) ? $this->aData[$this->thVdata['name_field']] : ''), $this->
            id, $this->thVdata['name_field'], 1, 180, 180,$this->module) . '
        </td>
        </tr>';
    }
    function getFieldRadioOutKey()
    {
        $w_input = ($_SESSION['is_mobile'] ) ? '95' : '80';

        $field_show = !empty($this->thVdata['field_show']) && $this->thVdata['field_show']=='hide' ? 'style="display: none;"' : '';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'" '.$field_show.'>
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
         <td align="left" width="'.$w_input.'">
         <div class="radio">';
        if (!empty($this->thVdata['valRadio']))
        {
            foreach ($this->thVdata['valRadio']  as $key=> $elemRadio)
            {
                $cheked = (!empty($this->aData[$this->thVdata['name_field']])
                && $this->aData[$this->thVdata['name_field']]==$elemRadio['val'] ?
                    ' checked' : '');
                $this->shablon_text .= '<input label="'.$elemRadio['name'].'" type="radio" id="' .$this->thVdata['name_field'].$key.'" name="form[' . $this->thVdata['name_field'] .
                    ']" 
                value="'.$elemRadio['val'].'" '.$cheked.'>';
            }
        }


        $this->shablon_text .= '</div></td>
          </tr>';
    }
    function getFieldRadioBox()
    {
        $w_input = ($_SESSION['is_mobile'] ) ? '95' : '80';

        $field_show = !empty($this->thVdata['field_show']) && $this->thVdata['field_show']=='hide' ? 'style="display: none;"' : '';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'" '.$field_show.'>
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
         <td align="left" width="'.$w_input.'%">
         <div class="radio">';
        if (!empty($this->thVdata['valRadio']))
        {
            foreach ($this->thVdata['valRadio']  as $key=> $elemRadio)
            {
                $cheked = (!empty($this->aData[$this->thVdata['name_field']])
                && $this->aData[$this->thVdata['name_field']]==$elemRadio['val'] ?
                    ' checked' : '');
                $this->shablon_text .= '<input label="'.$elemRadio['name'].'" type="radio" id="' .$this->thVdata['name_field'].$key.'" name="form[' . $this->thVdata['name_field'] .
                    ']" 
                value="'.$elemRadio['val'].'" '.$cheked.'>';
            }
        }


$this->shablon_text .= '</div></td>
          </tr>';
    }
    function getFieldCheckbox()
    {
        $field_show = !empty($this->thVdata['field_show']) && $this->thVdata['field_show']=='hide' ? 'style="display: none;"' : '';
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'" '.$field_show.'>
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
         <td align="left" >
         <div class="form-check form-switch f14">
         <input class="form-check-input" role="switch" type="checkbox" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '"  value="1" 
         ' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            'checked="checked"' : '') . '></div></td>
          </tr>';
    }
        function getFieldCheckboxOut()
    {
        $this->shablon_text .= '<tr  id="trId_'.$this->thVdata['name_field'].'">
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
         <td align="left"><input type="checkbox" name="form[' . $this->thVdata['name_field'] .
            ']" id="' . $this->thVdata['name_field'] . '"  value="1" 
         ' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            'checked="checked"' : '') . '></td>
          </tr>';
    }
    function getFieldRedaktor_mini()
    {

        $this->javaScrArr[] = '_redaktor(\'\',\'\',\'\',\'\',\'simple\',\'' . $this->
            thVdata['name_field'] . '_\')';
        $this->shablon_text .= ' <tr >
        <td colspan="2" align="center" >' . $this->thVdata['name'] . '</td>
     </tr>   
     <tr> 
     <td colspan="2"><textarea id="' . $this->thVdata['name_field'] .
            '_" name="form[' . $this->thVdata['name_field'] . ']" rows="' . (!empty($this->
            thVdata['rows']) ? $this->thVdata['rows'] : '15') . '" cols="' . (!empty($this->
            thVdata['cols']) ? $this->thVdata['cols'] : '80') . '" style="width: 100%" >' . (!
            empty($this->aData[$this->thVdata['name_field']]) ? htmlspecialchars($this->
            aData[$this->thVdata['name_field']]) : '') . '</textarea></td></tr>';
    }
    function getFieldRedaktor()
    {
        list($panel1, $panel2, $panel3, $plagins_) = get_redaktor();

        $this->javaScrArr[] = '_redaktor([' . implode(',', $plagins_) . '],[' . $panel1 .
            '],[' . $panel2 . '],[' . $panel3 . '],\'advanced\',\'' . $this->thVdata['name_field'] .
            '_\')';
        $this->shablon_text .= ' <tr >
        <td colspan="2" align="center" >' . $this->thVdata['name'] . '</td>
     </tr>   
     <tr> 
     <td colspan="2"><textarea id="' . $this->thVdata['name_field'] .
            '_" name="form[' . $this->thVdata['name_field'] . ']" rows="' . (!empty($this->
            thVdata['rows']) ? $this->thVdata['rows'] : '15') . '" cols="' . (!empty($this->
            thVdata['cols']) ? $this->thVdata['cols'] : '80') . '" style="width: 100%" >' . (!
            empty($this->aData[$this->thVdata['name_field']]) ? htmlspecialchars($this->
            aData[$this->thVdata['name_field']]) : '') . '</textarea></td></tr>';
    }
    function getFieldTextarea()
    {
        $this->shablon_text .= ' <tr >
        <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
        <td ><textarea id="' . $this->thVdata['name_field'] . '_" name="form[' .
            $this->thVdata['name_field'] . ']"
        ' . (!empty($this->thVdata['length']) ? 'onkeyup="if(this.value.length>' .
            $this->thVdata['length'] . ') this.value=this.value.substr(0,' . $this->thVdata['length'] .
            ');"' : '') . '
         rows="' . (!empty($this->thVdata['rows']) ? $this->thVdata['rows'] :
            '15') . '" cols="' . (!empty($this->thVdata['cols']) ? $this->thVdata['cols'] :
            '80') . '" >' . (!empty($this->aData[$this->thVdata['name_field']]) ?
            htmlspecialchars($this->aData[$this->thVdata['name_field']]) : '') .
            '</textarea></td></tr>';
    }
    function getFieldHidden()
    {
        // Получаем значение из aData, post_field, или из POST
        $field_value = '';
        if (!empty($this->thVdata['post_field']) && array_key_exists($this->thVdata['post_field'], $this->aData)) {
            $field_value = $this->aData[$this->thVdata['post_field']];
        } elseif (array_key_exists($this->thVdata['name_field'], $this->aData)) {
            $field_value = $this->aData[$this->thVdata['name_field']];
        } else {
            // Если значения нет в aData, пытаемся получить из POST (для скрытых полей turnir_id, league_id и т.д.)
            $field_value = poste($this->thVdata['name_field']);
            // Если нет в POST, пытаемся получить из сессии (для teamplayers)
            if (empty($field_value) && $this->module == 'teamplayers') {
                if ($this->thVdata['name_field'] == 'turnir_id' && !empty($_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'])) {
                    $field_value = $_SESSION['TEAMPLAYERS_SAVE_TURNIR_ID'];
                } elseif ($this->thVdata['name_field'] == 'league_id' && !empty($_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'])) {
                    $field_value = $_SESSION['TEAMPLAYERS_SAVE_LEAGUE_ID'];
                }
            }
        }

        if (($field_value === false || $field_value === '') && isset($this->thVdata['def'])) {
            $field_value = $this->thVdata['def'];
        }

        if ($field_value === false) {
            $field_value = '';
        }
        
        $this->shablon_text .= '<input type="hidden" name="' . $this->thVdata['name_field'] .
            '" value="' . htmlspecialchars($field_value) . '"/>';
    }
      function getFieldSelect()
    {

        $this->shablon_text .= '<select name="user_profile_color_1">
  <option value="1">Синий</option>
  
</select>';
    }
   function getFieldParent()
    {
        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
           <input id="p_' . $this->thVdata['name_field'] . '_id" name="form[' .
            $this->thVdata['name_field'] . ']" type="hidden" 
           value="' . (!empty($this->aData[$this->thVdata['name_field']]) ? ($this->
            aData[$this->thVdata['name_field']]) : '') . '" >
             <input type="text" id="p_' . $this->thVdata['name_field'] .
            '_name" name="p_parts_parent_name" style="width:30%;" readonly="readonly" 
             value="' . (!empty($this->aData[$this->thVdata['name_field'] .
            '_name']) ? $this->aData[$this->thVdata['name_field'] . '_name'] : "Корень") .
            '"/><span style="width:20px;cursor: pointer;background-color:grey;" 
              id="per_v_rozdel" module="' . $this->module .
            '" action="tree_window" 
              post_string="&id_spis_=' . $this->thVdata['name_field'] . '&table=' . (!empty($this->thVdata['table']) ?
            $this->thVdata['table'] : '') .
            '" return_content_bool="" blok="" class="ajax_send" >&nbsp;...&nbsp;</span>
           </td>
         </tr>';
         //&id=' . (!empty($this->id) ? $this->id : 0) . '
    }
    function getFieldout_keynosql()
    {
        //  s($this->thVdata);
        if (!empty($this->thVdata['module']) && $this->thVdata['module']=='no_module'){

            $_SESSION['wintype']['no_module']['table'] = $this->thVdata['table'];
        }
        if (!empty($this->thVdata['descr_table'])){
            // s($this->thVdata['descr_table']);
            $mod = (!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module);
            $_SESSION['wintype'][$mod][$this->thVdata['name_field']]['descr_table'] = $this->thVdata['descr_table'];

        }          $mod = (!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module);
        if (!empty($this->thVdata['where'])){
            // s($this->thVdata['descr_table']);

            $_SESSION['wintype'][$mod][$this->thVdata['name_field']]['where'] = $this->thVdata['where'];

        }else{
            $_SESSION['wintype'][$mod][$this->thVdata['name_field']]['where'] ='';

        }
        //s($this->aData);
        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
           <input id="p_' . $this->thVdata['name_field'] . '_id" name="form[' .
            $this->thVdata['name_field'] . ']" type="hidden" 
           value="' . (!empty($this->aData[$this->thVdata['name_field']]) ? ($this->
            aData[$this->thVdata['name_field']]) : '') . '" >
            <div class="ui-widget">
             <input type="text" id="p_' . $this->thVdata['name_field'] .
            '_name"  style="width:30%;"'.(!empty($this->thVdata['speedsearch']['min_letter']) ? 'speedsearch="'.$this->thVdata['speedsearch']['min_letter'].'" ' : '').
            (!empty($this->thVdata['speedsearch']['result_fields_dop']) ? 'result_fields_dop="'.base64_encode(json_encode($this->thVdata['speedsearch']['result_fields_dop'])).'" ' : '').
            (!empty($this->thVdata['speedsearch']['where']) ? ' where="'.base64_encode($this->thVdata['speedsearch']['where']).'" ' : '').
            'name2="' . $this->thVdata['out_result_field'] . '" id2="' . $this->thVdata['name_field'] . '"
              '.(!empty($this->thVdata['speedsearch']['table']) ? 'table='.$this->thVdata['speedsearch']['table'] : '').' 
              value="' .    (!empty($this->aData[$this->thVdata['name_field'] .
            '_name']) ? $this->aData[$this->thVdata['name_field'] . '_name'] :  (isset($this->thVdata['no_vubor']) ? $this->thVdata['no_vubor'] : 'Не выбран')) .
            '"/><span style="width:20px;cursor: pointer;background-color:grey;" 
              id="per_v_rozdel" module="' .(!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module) .
            '" action="' .(!empty($this->thVdata['action']) ?$this->thVdata['action'] : 'list') .'" 
            post_string="'.(!empty($this->thVdata['post_string']) ?$this->thVdata['post_string'] : '') .'"
              return_content_bool="" blok="" class="ajax_send" field_result="' . $this->thVdata['name_field']  .'" field_result_name="' . $this->thVdata['out_result_field']  .'" wintype="1" '.(!empty($this->thVdata['width'] ) ? 'width_="'.$this->thVdata['width'].'"' : '').' >&nbsp;...&nbsp;</span>
          </div>
           </td>
         </tr>';
        //&id=' . (!empty($this->id) ? $this->id : 0) . '
    }
    function getFieldOut_key()
    {
      //  s($this->thVdata);
        if (!empty($this->thVdata['module']) && $this->thVdata['module']=='no_module'){
            
            $_SESSION['wintype']['no_module']['table'] = $this->thVdata['table'];
            }
        if (!empty($this->thVdata['descr_table'])){
           // s($this->thVdata['descr_table']);
            $mod = (!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module);
$_SESSION['wintype'][$mod][$this->thVdata['name_field']]['descr_table'] = $this->thVdata['descr_table'];

}          $mod = (!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module);
     if (!empty($this->thVdata['where'])){
           // s($this->thVdata['descr_table']);
  
$_SESSION['wintype'][$mod][$this->thVdata['name_field']]['where'] = $this->thVdata['where'];

}else{
$_SESSION['wintype'][$mod][$this->thVdata['name_field']]['where'] ='';
    
}             
 //s($this->aData);
        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
           <input id="p_' . $this->thVdata['name_field'] . '_id" name="form[' .
            $this->thVdata['name_field'] . ']" type="hidden" 
           value="' . (!empty($this->aData[$this->thVdata['name_field']]) ? ($this->
            aData[$this->thVdata['name_field']]) : '') . '" >
            <div class="ui-widget">
             <input type="text" id="p_' . $this->thVdata['name_field'] .
            '_name"  style="width:30%;"'.(!empty($this->thVdata['speedsearch']['min_letter']) ? 'speedsearch="'.$this->thVdata['speedsearch']['min_letter'].'" ' : '').
        (!empty($this->thVdata['speedsearch']['result_fields_dop']) ? 'result_fields_dop="'.base64_encode(json_encode($this->thVdata['speedsearch']['result_fields_dop'])).'" ' : '').
        (!empty($this->thVdata['speedsearch']['where']) ? ' where="'.base64_encode($this->thVdata['speedsearch']['where']).'" ' : '').
        'name2="' . $this->thVdata['out_result_field'] . '" id2="' . $this->thVdata['name_field'] . '"
              '.(!empty($this->thVdata['speedsearch']['table']) ? 'table='.$this->thVdata['speedsearch']['table'] : '').' 
              value="' .    (!empty($this->aData[$this->thVdata['name_field'] .
            '_name']) ? $this->aData[$this->thVdata['name_field'] . '_name'] :  (isset($this->thVdata['no_vubor']) ? $this->thVdata['no_vubor'] : 'Не выбран')) .
            '"/><span style="width:20px;cursor: pointer;background-color:grey;" 
            
            id="per_v_rozdel" module="' .(!empty($this->thVdata['module']) ?$this->thVdata['module'] : $this->module) .
            '" action="' .(!empty($this->thVdata['action']) ?$this->thVdata['action'] : 'list') .'" 
            post_string="'.(!empty($this->thVdata['post_string']) ?$this->thVdata['post_string'] : '') .'"
              return_content_bool="" blok="" class="ajax_send" post_string="wint=1" field_result="' . $this->thVdata['name_field']  .'" field_result_name="' . $this->thVdata['out_result_field']  .'" wintype="1" '.(!empty($this->thVdata['width'] ) ? 'width_="'.$this->thVdata['width'].'"' : '').' >&nbsp;...&nbsp;</span>
          </div>
           </td>
         </tr>';
         //&id=' . (!empty($this->id) ? $this->id : 0) . '
    } 
    function getFieldProstSpr()
    {
        $width_default = ($_SESSION['is_mobile'] ) ? 95 : 30;

        $this->javaScrArr[]='chosen_vibor("'.$width_default.'%");';
        // если это не стандарный справочник то указываем таблицу и берем значения с неее
        if (!empty($this->thVdata['table'])){
            $where = !empty($this->thVdata['where']) ? ' where '.$this->thVdata['where'] : '';
            $sql = 'SELECT *, '.$this->thVdata['out_result_field'].' as name FROM `' . $this->thVdata['table'] .
                '` '.$where.'  ORDER by id';

        }
            else
        $sql = 'SELECT *, value as name FROM `' . T_SPRLIST_VALUES .
            '` where id_spis='.$this->thVdata['id_spis'].' and active=1   ORDER by name';
        $aProstSpr_ = db_list($sql);
        $this->thVdata['id_spis'] = !empty($this->thVdata['id_spis']) ? $this->thVdata['id_spis'] :0;
        $aProstSpr[0]=['id'=>0,'id_spis'=>$this->thVdata['id_spis'],'name'=>'Виберіть значення','active'=>0];
        $aProstSpr= array_merge($aProstSpr,$aProstSpr_);
        if (!empty($this->thVdata['isFirstElem'])) $aProstSpr=array_merge($this->thVdata['isFirstElem'],$aProstSpr);
        $disabled = !empty($this->thVdata['disabled']) ? 'disabled' : '';
        $selected='';$data_id='';
        if (!empty($this->aData[$this->thVdata['name_field']])) $data_id=$this->aData[$this->thVdata['name_field']];
        $data_id = $data_id=='' && !empty($this->thVdata['def']) ? $this->thVdata['def'] : $data_id;
      //  $val_select = $data_id ? 'value="'.$data_id.'"'  : '';
        $name_field =  !empty($this->thVdata['name_field']) ? $this->thVdata['name_field'] :$this->thVdata['name_field_virt'] ;
        $dis_input = $data_id && $disabled ? '<input type="hidden" name=form['.$name_field.']" value="'.$data_id.'" />' : '';
        $sSpis = $dis_input.'<select class="chosen-select " '.$disabled.' tabindex="5"  name=form['.$name_field.']" id="Prostid'.$name_field.'">';
        foreach ($aProstSpr as $elem)
        {
            if (!empty($data_id))
            $selected= $elem['id']==$data_id ? 'selected="selected"' : '';
            $sSpis.='<option '.$selected.' value="'.$elem['id'].'" >'.$elem['name'].'</option>';

        }
        $sSpis.=  '</select>';


        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
            '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
                '.$sSpis.'
           </td>
         </tr>';
    }
    function getFieldout_key_prostspr()
    {
        $width_default = ($_SESSION['is_mobile'] ) ? 95 : 30;
        $this->javaScrArr[]='chosen_vibor("'.$width_default.'%");';
        if (!empty($this->thVdata['table'])){
            $where = !empty($this->thVdata['where']) ? ' where '.$this->thVdata['where'] : '';
            $sql = 'SELECT *, '.$this->thVdata['out_result_field'].' as name FROM `' . $this->thVdata['table'] .
                '` '.$where.'  ORDER by id';

        }
        else
        $sql = 'SELECT *, value as name FROM `' . T_SPRLIST_VALUES .
            '` where id_spis='.$this->thVdata['id_spis'].' and active=1   ORDER by name';
        $aProstSpr = db_list($sql);
        if (!empty($this->thVdata['isFirstElem'])) $aProstSpr=array_merge($this->thVdata['isFirstElem'],$aProstSpr);

        $sSpis = '<select class="chosen-select " tabindex="5" name=form['.$this->thVdata['name_field'].']" id="Prostid'.$this->thVdata['name_field'].'">';
        foreach ($aProstSpr as $elem)
        {
            $attr = !empty($this->thVdata['attr_elem']) && !empty($elem[$this->thVdata['attr_elem']]) ?
                $this->thVdata['attr_elem'].'="'.$elem[$this->thVdata['attr_elem']] .'"': '';
            $selected= $elem['id']==$this->aData[$this->thVdata['name_field']] ? 'selected="selected"' : '';
            $sSpis.='<option '.$attr.' '.$selected.' value="'.$elem['id'].'" >'.$elem['name'].'</option>';

        }
        $sSpis.=  '</select>';


        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
                '.$sSpis.'
           </td>
         </tr>';
    }

    function getFieldProstSprOld()
    {
        $this->shablon_text .= ' <tr  id="trId_'.$this->thVdata['name_field'].'">
                    <td align="' . (!empty($this->thVdata['align_left_col']) ? $this->
            thVdata['align_left_col'] : 'right') . '"
         width="' . (!empty($this->thVdata['width_left_col']) ? $this->thVdata['width_left_col'] :
                '280') . 'px">' . $this->thVdata['name'] . '</td>
           <td align="left">
           <input id="p_' . $this->thVdata['name_field'] . '_id" name="form[' .
            $this->thVdata['name_field'] . ']" type="hidden" 
           value="' . (!empty($this->aData[$this->thVdata['name_field']]) ? ($this->
            aData[$this->thVdata['name_field']]) : '') . '" >
             <input type="text" id="p_' . $this->thVdata['name_field'] .
            '_name" name="p_parts_parent_name" style="width:30%;" readonly="readonly" 
             value="' . (!empty($this->aData[$this->thVdata['name_field'] .
            '_name']) ? $this->aData[$this->thVdata['name_field'] . '_name'] : "Корень") .
            '"/><span style="width:20px;cursor: pointer;background-color:grey;" 
              id="per_v_rozdel" module="' . $this->module .
            '" action="Prost_Spr" 
              post_string="&id_value='.$this->thVdata['id_spis'] .'&id_spis=' . $this->thVdata['name_field'] . '&id=' . (!
            empty($this->id) ? $this->id : 0) . '&table=' . (!empty($this->thVdata['table']) ?
                $this->thVdata['table'] : '') .'&mess='.(!empty($this->thVdata['mess']) ? $this->thVdata['mess'] :'').
            '" return_content_bool="" blok="0" class="ajax_send" >&nbsp;...&nbsp;</span>
           </td>
         </tr>';
    }
function  getFieldTab()
{   
      $this->shablon_text .= '</table> <table width="100%" cellpadding="0" cellspacing="0"  class="parts_table_edit f12 tab">';
    
    $this->Tabs_=1;
}
 public function getShablon()
    {
        return $this->shablon_text;
    }
 public function getJavaScript()
    {
        return $this->javaScrArr;
    }    
 public function ShowShablon()
    {
        echo $this->shablon_text;
    }
 public function setAEditField ($aEditField, $aData)
 {
   $this->aEditField = $aEditField;
   $this->aData = $aData; 
 }
 public function setId($id)
 {
    $this->id = $id;
 } 
 public function setModule($Module)
 {
    $this->Module = $Module;
 }  
}

?>
