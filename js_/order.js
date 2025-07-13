var ObjUpFile_ = '';
var dialog  =false;
var uploaded = false;                   // флаг загрузки
var min_  = 0;
var window_div_id = "window-container";
var field_name_spis = ''; // для возврата select простых справочника название поля
var start_date = null;
var start_time = null;                  // время, прошедшее в миллисекундах
var time_limit = 300;                    // тайм-лимит в секундах для опроса сервера
var r = false;                          // XMLHttpRequest-object
var secs_elapsed = 0;                   // время прошедшее с момента загрузки
 var div_id_open = "windowOpen";
 var return_bool = false;

//var $j = jQuery.noConflict();
function tabs(){
jQuery(document).ready(function() {
$('ul.tabs li').css('cursor', 'pointer');
$('ul.tabs.tabs1 li').click(function(){
    var thisClass = this.className.slice(0,2);
    $('div.t1').hide();
    $('div.t2').hide();
    $('div.t3').hide();
    $('div.t4').hide();
    $('div.' + thisClass).show();
    $('ul.tabs.tabs1 li').removeClass('tab-current');
    $(this).addClass('tab-current');
    });

});
}
function input_tooltip(){
jQuery(document).ready(function(){
$(function(){
$("input[title]").tooltip();
});
});
}

function zamens_null_value(id, obj){
   for (var k in obj){
      $('#'+k+'_0').id=k+'_'+id;
}
  $('#edit_0').id='edit_'+id;
  $('#edit_'+id).innerHTML=str_replace('(0', '('+id, getOb('edit_'+id).innerHTML);
  $('#del_0').id='del_'+id;
  $('#del_'+id).innerHTML=str_replace('(0', '('+id, getOb('del_'+id).innerHTML);
  $('#tr_0').id='tr_'+id;
}
function edit_save_dop_tov(id, obj, rs, objname, form_name, module_name, action_name, action_name_add,tr_id,table_name,action_name_del){
rs = (typeof rs == 'undefined') ? 1 : rs;
form_name =(typeof form_name == 'undefined') ? "form_dop" : form_name;
objname =(typeof objname == 'undefined') ? false : objname;
module_name =(typeof module_name == 'undefined') ? false : module_name;
action_name =(typeof action_name == 'undefined') ? false : action_name;
action_name_add =(typeof action_name_add == 'undefined') ? '' : action_name_add;
action_name_del =(typeof action_name_del == 'undefined') ? '' : action_name_del;
obj =(typeof obj == 'undefined') ? false : obj;
tr_id =(typeof tr_id == 'undefined') ? 0 : tr_id;
table_name =(typeof table_name == 'undefined') ? 'parts_table_' : table_name;
if (!objname || !module_name || !action_name || !obj){
alert('Нету нужных данных')
return  false;
}
param='&id='+id+'&tr_id='+tr_id;
for (var k in obj){
param+='&'+form_name+'['+k+']=';
param+=edit_save_obj_table(id, k,obj[k], rs,form_name);
}
if (rs==1){
$('#edit_'+id).innerHTML='<a href="#" onclick="edit_save_dop_tov('+id+','+objname+',0,\''+objname+'\',\''+form_name+'\',\''+module_name+'\',\''+ action_name+'\');">сохранить</a>';
$('#del_'+id).innerHTML='';
 } else{
 ajax_send('content_return', param, module_name, ((rs==2) ? action_name_add : action_name), false,false);
    $('#edit_'+id).innerHTML='<a href="#" onclick="edit_save_dop_tov('+id+','+objname+',1,\''+objname+'\',\''+form_name+'\',\''+module_name+'\',\''+ action_name+'\');"><img height="20px" src="img/edit.gif" border="0" ></a>';
    $('#del_'+id).innerHTML='<a href="#" onclick="return  del_tr('+id+',\''+table_name+'\',\''+module_name+'\',\''+action_name_del+'\');"><img height="20px" src="img/delete.gif" border="0" ></a>';
}
}

function del_tr(id, table_name, module_name,action_name){
     window_modal('Вы подтверждаете удаление записи?',460,'', 'del_tr_(' +id +',\''+table_name+'\',\''+module_name+'\',\''+action_name+'\');');
}

function del_tr_(id, table_name, module_name,action_name){
param='&id='+id;
tr=$('#tr_'+id);
 var num_tr=tr.sectionRowIndex
 document.getElementById(table_name).tBodies[0].deleteRow(num_tr)
 ajax_send('content_return', param, module_name,action_name, false,false);

}
function edit_save_obj_table(id, k,val, rs,form_name){

plus='<img  src="img/active.gif" border="0" >';
minus='<img  src="img/pasive.gif" border="0" >';

   new_value_inner='';
   param='';
    value_inner=$('#'+k+'_'+id).innerHTML;

    switch (val.type){
     case 'text':
     if (rs==1){
         new_value_inner='<input type="text"  id="'+form_name+'_'+k+'_'+id+'" value="'+value_inner+'">'
     }else{
        new_value_inner=$('$'+form_name+'_'+k+'_'+id).value;
        param=new_value_inner;
     }
     break;
   case 'checkbox':
        if (rs==1){
         new_value_inner='<input type="checkbox"  '+(value_inner.substr(value_inner.indexOf('active'),6)=='active' ? 'checked="checked"' : '' )+' id="'+form_name+'_'+k+'_'+id+'"  value="1">'
     }else{
     new_value_inner=(($('#'+form_name+'_'+k+'_'+id).checked) ? plus : minus)
     param=(($('#'+form_name+'_'+k+'_'+id).checked) ? 1 : 0);
     }

     break;
  case 'select':
     if (rs==1){
         new_value_inner=' <input id="p_'+k+'_'+id+'" type="hidden" value="" > <input type="text" style="width:170px;" id="p_'+k+'_'+id+'_name" name="p_'+k+'_'+id+'_name"  readonly="readonly" value="'+value_inner+'"/><span style="width:30px;cursor: pointer;background-color:grey;" id="per_v_rozdel" onclick="send_spis_table(\''+val.table_spr+'\',\''+k+'_'+id+'\');">&nbsp;...&nbsp;</span>'
           }else{
        new_value_inner=$('#p_'+k+'_'+id+'_name').value;
        param=(($('#p_'+k+'_'+id).value) ? $('#p_'+k+'_'+id).value : $('#'+k+'_'+id).getAttribute('spis_value'));}

     break;
     case 'select_local':
        if (rs==1){
          st=val.spis;
         new_value_inner='<select id="'+form_name+'_'+k+'_'+id+'">';
         for (var ka in st){
         new_value_inner+='<option '+(st[ka]==value_inner ? 'selected="selected"' : '' )+'  value="'+(ka)+'">'+st[ka];
         }
         new_value_inner+='</select>';
         }
         else{
        new_value_inner=val.spis[$('#'+form_name+'_'+k+'_'+id).value];
        param=$('#'+form_name+'_'+k+'_'+id).value+1;
        
     }
        break;


    }
    $('#'+k+'_'+id).innerHTML =new_value_inner

return param;
}
function send_spis_table(table, field,module,action,id){
if(typeof module=="undefined"){module = "settings"}
if(typeof action=="undefined"){action = "spis_values"}
if(typeof id=="undefined"){id = 0}else{
id = $('#'+id).value;
}
ajax_send('content_return', '&table_spr='+table+'&field='+field+'&spis_id='+id, module, action, false,false);
}

var d = document;
function addRow(obj, table,id_r,form_name,objname,module_name,action_name,action_name_add,table_name,action_name_del)
{
    id=0;
    // Находим нужную таблицу
    var tbody = d.getElementById(table).getElementsByTagName('TBODY')[0];
    // Создаем строку таблицы и добавляем ее
    var row = d.createElement("TR");
    tbody.appendChild(row);
    row.setAttribute('id','tr_0');
    // Создаем ячейки в вышесозданной строке
    // и добавляем тх
    for (var k in obj){
    var td1 = d.createElement("TD");
    row.appendChild(td1);
    td1.setAttribute('id',k+'_'+id);
    td1.setAttribute('align','center');
    edit_save_obj_table(0, k,obj[k], 1,form_name)
    }
    var td1 = d.createElement("TD");
    row.appendChild(td1);
    td1.setAttribute('id','edit_'+id);
    td1.setAttribute('align','center');
    //добавление
    $('#edit_'+id).innerHTML='<a href="#" onclick="edit_save_dop_tov(0,'+objname+',2,\''+objname+'\',\''+form_name+'\',\''+module_name+'\',\''+ action_name+'\',\''+action_name_add+'\','+id_r+',\''+table_name+'\',\''+action_name_del+'\');">сохранить</a>';
// удаление
   var td1 = d.createElement("TD");
    row.appendChild(td1);
    td1.setAttribute('align','center');
    td1.setAttribute('id','del_'+id);

    // Наполняем ячейки
/*    td1.innerHTML = name+' '+initials;
    td2.innerHTML = posada;
    alert('ss');*/
    return false;
}
function send_nastr(){
//alert(formElements('article_edit_form'))
      return formElements('article_edit_form');
}
function list_pages(id){
   send_ajax('',action,module,'&page_id=' +id);
}

// маска
  function mask_(){
  $('.date_mask').mask('99.99.9999');
  $('.latin_valid').keyup(function (){
//is_latin=/^[a-z]+$/i;
var is_latin = new RegExp("^[a-z]+$");
    str=$(this).val();
     pred=str.substring(0,str.length-1);
    if (!is_latin.test(str))  $(this).val(pred);
});
 $('.digital_valid').keyup(function (){
is_digital=/^[0-9]+$/i;
    str=$(this).val();
     pred=str.substring(0,str.length-1);
    if (!is_digital.test(str))  $(this).val(pred);
});
/*  $('.icq_valid').keyup(function (){
 icqTest = /^[0-9]{4,10}$/;
    str=$(this).val();
     pred=str.substring(0,str.length-1);
    if (!icqTest.test(str))  $(this).val(pred);
});
 $('.pass_valid').keyup(function (){
     passTest = new RegExp("^[0-9a-z\\.,\\-_]+$");
    str=$(this).val();
     pred=str.substring(0,str.length-1);
    if (!passTest.test(str))  $(this).val(pred);
});
  $('.url_valid').keyup(function (){
     var regex = new RegExp("^http://(.+)$");
    str=$(this).val();
    alert(str)
     pred=str.substring(0,str.length-1);
    if (!regex.test(str))  $(this).val(pred);
}); */
  }
  
  //Проверка емейла
function validemail(email){
    re_email = /^[a-z_\d]+[-a-z\d\._]*@(([\da-z]+(-[\da-z]+)*)(\.[\da-z]+(-[\da-z]+)*)*\.(com|net|org|mil|edu|gov|arpa|info|biz|inc|name|[a-z]{2})|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i;
    if (!re_email.test(email)){
        return false;
    }else{
        return true;
    }
}

//проверка по задыным аттрибутам
function verif(obj,value){
def=0;
type_def='';
prov=true;
def=obj.getAttribute('def');
type_def=obj.getAttribute('type_def');
name_field=obj.getAttribute('name_field');
min=obj.getAttribute('min');
min=min ? min : 3;
if (type_def && def){
switch(type_def){
case 'date':
    prov=validdate(value);
    if (!prov){
    window_div('Не правильно заполнен формат даты '+(name_field ? 'для поля "'+name_field+'"' : ''));
    }
break;
case 'int':
    prov=isValidInt(obj,1);
    if (!prov){
    window_div('В поле '+(name_field ? '"'+name_field+'"' : '')+' должно стоять целое число!');
    }
break;
case 'text':
 tex=trim(value);
    prov=(tex.length > min ? true : false );
    if (!prov){
    window_div('В поле '+(name_field ? '"'+name_field+'"' : '')+' должно стоять больше '+min+' символов!');
    }
break;

}
}
return prov;
}

// получить окно по тагу
//функция проверяет для разных браузеров, какой тип iframe подставлять
function getIframeDocument(iframeNode) {
  if (iframeNode.contentDocument) return iframeNode.contentDocument
  if (iframeNode.contentWindow) return iframeNode.contentWindow.document
  return iframeNode.document
}
// Функция определения типа объектов
function type_object(Obj, tyny_m_){
prov=true;
    if(Obj == null ){
    return null;}
    Obj_str_val = '';
    switch(Obj.type){
     case "radio":
        Obj_str_val = ((Obj.checked)? Obj.value : -1);  break;
        //return get_radiobox(Obj.name);  break;
    case "checkbox":
        Obj_str_val = ((Obj.checked)? 1 : 0) ;    break;
   case "textarea":
        if (tyny_m_ && (tyny_m_.indexOf(Obj.id)+1)){
           Obj_str_val = encodeURIComponent(tinyMCE.get(Obj.id).getContent());
        }else{
            Obj_str_val = encodeURIComponent(Obj.value);
        }
        break;
        case "text":
            prov=verif(Obj,Obj.value);
            Obj_str_val = encodeURIComponent(Obj.value);

        break;
      case "password":
            Obj_str_val = encodeURIComponent(Obj.value);
        break;
    default:
       Obj_str_val = encodeURIComponent(Obj.value);
     break;
    }
    if(prov){
    return ((Obj.type =='radio' && Obj_str_val==-1) ? '' : Obj.name+'='+Obj_str_val);
    }else{
    return false;
    }

}
function modalclose (dialog) {
   dialog.data.fadeOut('slow', function () {
        dialog.container.hide('slow', function () {
            dialog.overlay.fadeIn('slow', function () {
                $.modal.close();
            });
        });
    });
}

// иницилизация маски ввода
function mask(){
alert('mask')
    jQuery(function($) {
//$('#date').mask('99/99/9999');
$('#mask').mask('(999) 999-9999');
/*$("#date2").mask("99/99/9999",{placeholder:"-"});
$("#phone2").mask("(999) 999-9999",{completed:function(){alert("Этого: "+this.val()+" достаточно!");}});*/
});
}
(function($) {
		$(function() {
			$('input, select').styler({
				selectSearch: true
			});
		});
		})(jQuery);
