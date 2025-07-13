//var s_list = new Object();
//var s_list = {}; // Создаём ассоциативный массив
 
//s_list[0] = {'field' : 'name', 'bdfield':'bdname'};
//s_list[1] = {'field' : 'name2', 'bdfield':'bdname4'};
//s_list[2] = {'field' : 'name3', 'bdfield':'bdname5'};
//s_list["fat"] = "Толстый";
//s_list["small"] = "Маленький";
//s_list["name"] = "Гомер";
/* 
for (var x in s_list) //выведем на экран все элементы
{
   alert('key='+x + ' val='+ s_list[x]['field']);
}*/
width_body=document.body.clientWidth; // ширина
is_mobile =  width_body<768 ? 1 : 0;

// присваивает глобальную переменную
function setVar(variable, val) {
   // alert('setVar');
window[variable] = val;
}
   
reload_page = (typeof reload_page=="undefined")? true :reload_page;
//alert('reload_page3='+reload_page)
var data ='';
filter_name='';
down_left = 0;
is_window_open = false;
 function window_modal(text,width, height, command_ok_but, name_win,type_win, div_id,name2) {
  ///  alert('div_id')
  //  alert(div_id)
     parent.jQuery.fancybox.close();
      if(typeof text=="undefined"){text = "&nbsp;"}
    if(typeof width=="undefined" || width == ''){width = 400}
    if(typeof height=="undefined" || height == ''){height = 100}
    if(typeof div_id=="undefined" || div_id == ''){div_id = "window-new"}
   // if(typeof div_id_open_=="undefined" || div_id_open_ == ''){div_id_open_ = "windowOpen"}
   //alert(div_id)
    if(typeof name_win=="undefined"){name_win = "Увага!!!"}
    if(typeof name2=="undefined"){name2 = ""}
    if(typeof type_win=="undefined" || type_win==''){type_win = 100}
    if(typeof command_ok_but=="undefined"){command_ok_but = ""}
       var browserWindow = $(window);
var width_ = browserWindow.width();
var height_ = browserWindow.height();
    win_w = screen.width;
//    win_h = document.body.clientHeight;
    win_h = screen.height;
    height_c = (height_-height<0)?  (height-100) : height;
    width_c = (width_-width<0)?  (width-50) : width;
    is_window_open = 1;
switch(type_win){
case 1: // без заголовка модальное
data = '<div id="'+div_id+'" > <table id="zoom_table" border="0" class="window-content" style="border-collapse:collapse; width:100%; height:100%;"><tbody><tr><td class="tl" style="background:url(\'img/win/tl.png\') 0 0 no-repeat; width:20px; height:20px; overflow:hidden;" /><td class="tm" style="background:url(\'img/win/tm.png\') 0 0 repeat-x; height:20px; overflow:hidden;" /><td class="tr" style="background:url(\'img/win/tr.png\') 100% 0 no-repeat; width:20px; height:20px; overflow:hidden;" />     </tr><tr><td class="ml" style="background:url(\'img/win/ml.png\') 0 0 repeat-y; width:20px; overflow:hidden;" /><td class="mm" style="background:#fff;vertical-align:top; padding:5 10 10 10;"><table border="0" width="100%" height="100%"><tr><td valign="middle"><div id="zoom_content" style="height:' + (height_c) + 'px;width:' +width_c +'px;">' + text + '</div><div align="right"></div></td></tr><tr ><td valign="bottom" height="30px"><div align="right" style="float:right;">'+ (command_ok_but ? button__(command_ok_but, 'Ok', div_id) : '' ) +button__('', 'Відміна', div_id, 'Натисныть, щоб закрити') +'</div></td></tr></table></td><td class="mr" style="background:url(\'img/win/mr.png\') 100% 0 repeat-y;  width:20px; overflow:hidden;" /></tr><tr><td class="bl" style="background:url(\'img/win/bl.png\') 0 100% no-repeat; width:20px; height:20px; overflow:hidden;" />         <td class="bm" style="background:url(\'img/win/bm.png\') 0 100% repeat-x; height:20px; overflow:hidden;" />                     <td class="br" style="background:url(\'img/win/br.png\') 100% 100% no-repeat; width:20px; height:20px; overflow:hidden;" /></tr></tbody></table><a href="javascript:parent.jQuery.fancybox.close();" title="Нажмите, чтобы закрыть" id="zoom_close" style="position:absolute; top:0; left:0;" ><img src="img/win/closebox.png" alt="Close" style="border:none; margin:0; padding:0;" /></a></div>';
 
break;
case 2: // загрузка картинки ожидания
data = '<div id="'+div_id+'" ><img src="img/kompnew.gif"></div>';
    width= 85;
    height =85;
break;
case 3: // загрузка c прогрессбаром
    data = '<div id="'+div_id+'" > <table id="zoom_table" border="0" class="window-content" style="border-collapse:collapse; width:100%; height:100%;"><tbody><tr><td class="tl" style="background:url(\'img/win/tl.png\') 0 0 no-repeat; width:20px; height:20px; overflow:hidden;" /><td class="tm" style="background:url(\'img/win/tm.png\') 0 0 repeat-x; height:20px; overflow:hidden;" /><td class="tr" style="background:url(\'img/win/tr.png\') 100% 0 no-repeat; width:20px; height:20px; overflow:hidden;" />     </tr><tr><td class="ml" style="background:url(\'img/win/ml.png\') 0 0 repeat-y; width:20px; overflow:hidden;" /><td class="mm" style="background:#fff;vertical-align:top; padding:5 10 10 10;"><table border="0" width="100%" height="100%"><tr><td valign="middle"><div id="windowTop"><div id="windowTopContent" class="window-title">'+name_win+'<span id="progress_text_title_"></span></div></div><div id="zoom_content" style="height:' + (height_c) + 'px;width:' +width_c +'px;"><div class="progressbar" id="pb" ><div></div></div></div><div align="right"></div></td></tr><tr ><td valign="bottom" height="30px"><div align="right" style="float:right;"></div></td></tr></table></td><td class="mr" style="background:url(\'img/win/mr.png\') 100% 0 repeat-y;  width:20px; overflow:hidden;" /></tr><tr><td class="bl" style="background:url(\'img/win/bl.png\') 0 100% no-repeat; width:20px; height:20px; overflow:hidden;" />         <td class="bm" style="background:url(\'img/win/bm.png\') 0 100% repeat-x; height:20px; overflow:hidden;" />                     <td class="br" style="background:url(\'img/win/br.png\') 100% 100% no-repeat; width:20px; height:20px; overflow:hidden;" /></tr></tbody></table><a href="javascript:parent.jQuery.fancybox.close();" title="Нажмите, чтобы закрыть" id="zoom_close" style="position:absolute; top:0; left:0;" ><img src="img/win/closebox.png" alt="Close" style="border:none; margin:0; padding:0;" /></a></div>'
break;
    case 4: // модальное и с заголовком
        data = '<div id="'+div_id+'" > <table id="zoom_table" border="0" class="window-content" style="border-collapse:collapse; width:100%; height:100%;"><tbody><tr><td class="tl" style="background:url(\'img/win/tl.png\') 0 0 no-repeat; width:20px; height:20px; overflow:hidden;" /><td class="tm" style="background:url(\'img/win/tm.png\') 0 0 repeat-x; height:20px; overflow:hidden;" /><td class="tr" style="background:url(\'img/win/tr.png\') 100% 0 no-repeat; width:20px; height:20px; overflow:hidden;" />     </tr><tr><td class="ml" style="background:url(\'img/win/ml.png\') 0 0 repeat-y; width:20px; overflow:hidden;" /><td class="mm" style="background:#fff;vertical-align:top; padding:5 10 10 10;"><table border="0" width="100%" height="100%"><tr><td valign="middle"><div id="windowTop"><div id="windowTopContent" class="window-title">'+name_win+'<span id="progress_text_title_"></span></div></div><div id="zoom_content" style="height:' + (height_c) + 'px;width:' +width_c +'px;"><div class="progressbar" id="pb" ><div></div></div></div><div align="right"></div></td></tr><tr ><td valign="bottom" height="30px"><div align="right" style="float:right;"></div></td></tr></table></td><td class="mr" style="background:url(\'img/win/mr.png\') 100% 0 repeat-y;  width:20px; overflow:hidden;" /></tr><tr><td class="bl" style="background:url(\'img/win/bl.png\') 0 100% no-repeat; width:20px; height:20px; overflow:hidden;" />         <td class="bm" style="background:url(\'img/win/bm.png\') 0 100% repeat-x; height:20px; overflow:hidden;" />                     <td class="br" style="background:url(\'img/win/br.png\') 100% 100% no-repeat; width:20px; height:20px; overflow:hidden;" /></tr></tbody></table><a href="javascript:parent.jQuery.fancybox.close();" title="Нажмите, чтобы закрыть" id="zoom_close" style="position:absolute; top:0; left:0;" ><img src="img/win/closebox.png" alt="Close" style="border:none; margin:0; padding:0;" /></a></div>'
        break;
     default: // по умолчанию модальное и с заголовком
     { // alert(text)
     if (name2!='') name2='<h5 class="modal-title2" >'+name2+'</h5>';
 data = '<div class="modal fade"  id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">'
         +'<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="min-width: '+width+'px;width: '+width+'px;min-height: '+height_c+'px">'
         +'<div class="modal-content">'
         +'<div class="modal-header">'
     +'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>'+name2+'<h5 class="modal-title" id="staticBackdropLabel">'+name_win+'</h5></div>'

     +'<div class="modal-body"><div class="container-fluid">'+text+'</div></div>'
     +'<div class="modal-footer">'
     + (command_ok_but ? button__(command_ok_but, 'Ok', div_id) : '<button type="button" class="btn-myClose" data-bs-dismiss="modal">Скасувати зміни</button>' )
     +''
     +'</div></div></div></div>';
         $('#modal_new_window').html(data);
          $('#staticBackdrop').modal({backdrop:'static', keyboard:true});
         $('#staticBackdrop').modal('show');
       //  myModal.show();


 }
 }
if (type_win<100) {
    // $(document).ready(function() {
    $.fancybox.open({
        href : '#',
        type : 'image',
        minWidth : width,
        minHeight : height,
        autosize : false,
        scrolling:'no',
      //  autoSize:false,
        //autoResize  : false,
        modal :true,
        closeBtn:false,
        content : data,
        onComplete: function() {
          //  $("#fancybox-overlay").css({'left':'20px', 'right':'auto'});
        },
        afterClose    : function() {wintype='';field_result='';is_window_open=false;}
    });
}


//$.fancybox({ onClosed : function(){ alert('close') } })
//	});	

}
function button__(onClickFunc, text, div_id, title,  close, id, class_){
    if(typeof onClickFunc!="undefined" && onClickFunc!=''){onClickFunc = 'bool=true'}  else {onClickFunc = ''} ;
    if(typeof text=="undefined"){text = "Ok"}
    if(typeof title=="undefined"){title = 'Нажмите, чтобы выполнить действие.'}
    if(typeof id=="undefined"){id = null}
    if(typeof close=="undefined"){close = 1}
    if(typeof class_=="undefined"){class_ = 'sbutton_'}
    if(typeof div_id=="undefined"){div_id = null}
return '' +
    '' +
    '' +
    '   <div class="row justify-content-center mt-4">' +
    '                <div class="col"><input data-bs-dismiss="modal" type="button"' +
    '        class="buttonOK" value="'+text+'" ' +onClickFunc +' id="' +id +'" ' +
    '          ></div>' +
    '                <div class="col">' +
    '                 <button type="button" class="btn-myClose" data-bs-dismiss="modal">Відміна</button>' +
    '            </div>' +
    '        </div>';
}


 //div окно функции с прокруткой
// для Jquery


function adminEnter(befor){
 objuser = $("#username");
 objpass = $("#password");
//console.log(objuser)
//console.log(objpass)
    var myModalEl = document.getElementById('staticBackdrop')
    var myModal = bootstrap.Modal.getInstance(myModalEl)
      myModal.hide();
  //    myModal.dispose();
  if (trim(objuser.val()).length<3 || trim(objpass.val()).length<3){
     window_modal('Ви не ввели Логін або Пароль');
    return false;
 }
  post_string_ ='&username=' +objuser.val()+'&password='+objpass.val();
 // console.log(post_string_)
 // alert(post_string_)
  if(befor!=undefined){
    send_ajax('','','players',post_string_);
   }
 return true;
 //ajax_send('content_return', '&username=' +objuser.val()+'&password='+objpass.val(), 'parts', '');
}
function trim(string)
{
 if (string.length>0){
  return string.replace(/(^\s+)-(\s+$)/g, "");
 }else{
 return string;
 }

}
function send_error_bd_ftp(){
str='';
if ($("#user_bd")!=null){
 objuser = $("#user_bd");
 objsever = $("#server_name_bd");   
 objname = $("#name_bd");
 if (trim(objsever.value).length<1){
    str +='Введите имя сервера БД<br />';
 }
 if (trim(objuser.value).length<1){
    str +='Введите пользователя БД<br />';
 }
 if (trim(objname.value).length<1){
    str +='Введите название БД';
 }

}
if ($("#server_ftp")!=null){
 objserver_ftp = $("#server_ftp");
 objuser_ftp = $("#user_ftp");
 objpass_ftp = $("#pass_ftp");
 objroot_ftp = $("#root_ftp");
 objport_ftp = $("#port_ftp");
  if (trim(objserver_ftp.value).length<1){
    str +='Введите имя сервера FTP<br />';
 }
 if (trim(objuser_ftp.value).length<1){
    str +='Введите пользователя FTP<br />';
 }
 if (trim(objpass_ftp.value).length<1){
    str +='Введите пароль FTP';
 }
 if (trim(objroot_ftp.value).length<1){
    str +='Введите путь к корневой папке FTP';
 }
 if (trim(objport_ftp.value).length<1){
    str +='Введите порт FTP';
 }

}
 if (str){
   window_modal(str);
    return false;
 }
 return formElements('form_edit_form');

}
function redirect_url(url){
  //  alert(url)
    document.location.href = url;
      window.location.reload(true);
    }
function redirect_(){
    //alert(globalServerAdress)
    window.location.reload(true);
}


// функция замены строки
function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}

function get_post_href()
{
    href = document.location.hash.replace('#', '');
    if (href) {
    last_href = href;
   aCurrentUrl = href.split('-');
   if (aCurrentUrl.length >= 2) {
        //alert('tyt')
       
     //    module = aCurrentUrl[0];
     //    action  = aCurrentUrl[1];
         post_string = aCurrentUrl[2];
      }
   }
   return post_string;
}

/*
25,11,2020 обработка количество человек на єтапе
*/
$(document).on('keypress','#cnt_people',function(event){
  event = event || window.event;
  if (event.charCode && event.charCode!=0 && event.charCode!=46 && (event.charCode < 48 || event.charCode > 57) )
    return false;

});
/*
14,04,2021 обработка отказы от игры 1 игрока
*/
$(document).on('click','#break_1',function(event){
   // alert()
 $is_check_1 = $(this).is(':checked');  
 $is_check_2 = $('#break_2').is(':checked');  
 if ($is_check_1==1 && $is_check_2==0)
 {
    $('#set_1').val('L');
    $('#set_2').val('W'); 
 }else
 if ($is_check_1==1 && $is_check_2==1)
 {
    $('#set_1').val('L');
    $('#set_2').val('L'); 
 } else
 if ($is_check_1==0 && $is_check_2==1)
 {
    $('#set_1').val('W');
    $('#set_2').val('L'); 
 }else
 if ($is_check_1==0 && $is_check_2==0)
 {
    $('#set_1').val('0');
    $('#set_2').val('0'); 
 }   
 

});
/*
14,04,2021 обработка отказы от игры 2 игрока
*/
$(document).on('click','#break_2',function(event){
   // alert()
 $is_check_2 = $(this).is(':checked');  
 $is_check_1 = $('#break_1').is(':checked');  
 if ($is_check_1==1 && $is_check_2==0)
 {
    $('#set_1').val('L');
    $('#set_2').val('W'); 
 }else
 if ($is_check_1==1 && $is_check_2==1)
 {
    $('#set_1').val('L');
    $('#set_2').val('L'); 
 } else
 if ($is_check_1==0 && $is_check_2==1)
 {
    $('#set_1').val('W');
    $('#set_2').val('L'); 
 }else
 if ($is_check_1==0 && $is_check_2==0)
 {
    $('#set_1').val('0');
    $('#set_2').val('0'); 
 }   
 

});
$(document).on('keyup','#cnt_people',function(event){

  //   var key = event.keyCode || event.which;
 //   key = String.fromCharCode( key );
    val = $(this).val();
   // val = old_val||key;
   if (val=='') val='0';
  // alert(val_old)
   if (typeof  val_old == 'undefined') {
    val_old=555
    }
   //lert(get_post_href())
   if (val_old!=val)  content =  ajax_content('cntpeople','etaps',get_post_href()+'&cnt='+val)
   if (content!='777') $(this).val(content);
  val_old = val;
  //  alert(val);
});
intervalId = setInterval(timerDecrement, 5000);

function timerDecrement() {
    aJson = [];
    $('.mainTable').each(function (i, mainTable) {

        tableID = $(mainTable).attr('tableBig');
        tableBig = $('#tableBig_'+tableID);
        newgame = $(tableBig).attr('newgame');
     //   console.log('table_id=' + tableID)
      //  console.log('newgame=' + newgame)
      //  console.log('i=' + i)
        aJson[i] = newgame;
    //    post_string = $(tableBig).attr('post_string');
     });
    let jsonRes = JSON.stringify(aJson);
 //   console.log(aJson.length)
    if (aJson.length>0)
    {
      //  console.log(aJson.length)

        // console.log(jsonRes);
        href = document.location.hash.replace('#', '');
        aCurrentUrl = href.split('-');
        if (aCurrentUrl.length >= 2) {
            module = aCurrentUrl[0];
            action  = aCurrentUrl[1];
            post_string_ = aCurrentUrl[2];
        }
     //   console.log(jsonRes)
        post_string = post_string_+'&jsonGame='+jsonRes;
        content =  ajax_content('getstatustables','tables',post_string);
        jsonParse = jQuery.parseJSON(content);

        for (i in jsonParse) {
            if (jsonParse[i].edit==1)
            {
                $('#mainTable_'+i).html(jsonParse[i].content);
                elem= $('#Table_'+i);
                if (jsonParse[i].newgame>0)       StartTimer(jsonParse[i].diff,elem)

            }
            //     console.log(jsonParse[i]);
            //   console.log(jsonParse[i].edit);
            //  console.log(jsonParse[i].content);

        }

    }

      //      console.log(jsonParse)
  //  console.log('post_string=' + post_string)


}
// выбор игры произведен



$(document).on('click','.tableBig',function(event){

  newgame=$(this).attr('newgame');
  post_string=$(this).attr('post_string');
  
  
  obj = this;
    //tableID = $(obj).attr('tableBig');
    name_table = $(obj).find(".numTable").html();

  //alert(post_string)
  if (newgame==0){
      if (is_mobile) width = 380; else width=1184;
      if (width_body<359) width = 318;
   // alert('setgametotable==')
  content =  ajax_content('setgametotable','tables',post_string);
   if (content)
  window_modal(content,width,'630', '', 'Виберіть гру','','',name_table,'text-align: left;');
 
  }else
  {

      if (is_mobile) width = 380; else width=740;
      if (width_body<359) width = 318;
      post_string = post_string + '&newgame='+newgame+'&width_body='+width_body;
   // alert('setresultwin=='+post_string)
     content =  ajax_content('setresultwin','tables',post_string);
   if (content)
  window_modal(content,width,'330', '', 'Внесіть результат гри','','',name_table,'text-align: left;');
 
  }

  $(obj).off('click'); // удаляем повтоное срабатывание

});
// окно аворизации пользователя
$(document).on('click','#avtoris',function(event){
post_string='';
   // alert('setgametotable==')
  content =  ajax_content('avtoris','avtoris',post_string);
  width = is_mobile ? '360' : 500 ;
   if (content)
  window_modal(content,width,'500', '', 'Авторизуйтесь','','','','text-align: left;');
 

  $(obj).off('click'); // удаляем повтоное срабатывание

});
 // старт новой игры

  $(document).on('click','.setgemtotable',function(event){
    post_string=$(this).attr('post_string');
  //    alert('setgemtotable='+post_string)
  content =  ajax_content('settablegame','tables',post_string); 
  
  jsonParse = jQuery.parseJSON(content);
 
   $(obj).find(".startTime").html('Старт <span>'+jsonParse.start_game+'</span>');
   $(obj).find(".workTimeName").html('Йде матч:');
   $(obj).find(".tableEtapPrim").html(jsonParse.name_etap+'::'+jsonParse.etap_prim);
   $(obj).find(".player1").html(jsonParse.name1);
   $(obj).find(".player2").html(jsonParse.name2);
   elem= $(obj).find('.watchTable');
   StartTimer(jsonParse.diff,elem)
   $(obj).find(".table_table_").toggleClass("bor_blue", false);
   $(obj).find(".table_table_mini").toggleClass("bor_blue", false);
   $(obj).find(".table_table_").toggleClass("bor_red", true);
   $(obj).find(".table_table_mini").toggleClass("bor_red", true);
 //  alert(jsonParse.newgame);
   $(obj).attr('newgame',jsonParse.newgame);
   $(this).off('click'); // удаляем повтоное срабатывание
  });
  
  $(document).on('click','#cancelGame',function(event){
    post_string=$(this).attr('post_string');
    table_id=$(this).attr('table_id');
    gameid=$(this).attr('gameid');
    //  alert('cancelgame11='+post_string)
  content =  ajax_content('cancelgame','tables',post_string); 
  
 
 if (content=='OK') {
    if (table_id == '')
      {
        
        cancelResultGame(gameid,post_string);
      } else
      {
        obj=$('#tableBig_'+table_id);
        cleanTable(obj);
      } 
    

 }
$(this).off('click'); // удаляем повтоное срабатывание
   
  })
  function cancelResultGame(gameid,post_string)
{
        $('#dataName--set_1--'+gameid).html('');
        $('#dataName--set_2--'+gameid).html('');
        $('#dataName--start_game--'+gameid).html('<span id="dataName--start_game--'+gameid+'"><span class="blue tableBig" post_string="'+post_string+'" newgame="'+gameid+'">Розпочати гру</span></span>');
    
} 
function cleanTable(obj)
{
        $(obj).find(".startTime").html('&nbsp;');
   $(obj).find(".workTimeName").html('&nbsp;');
   $(obj).find(".tableEtapPrim").html('&nbsp;');
   $(obj).find(".player1").html('&nbsp;');
   $(obj).find(".player2").html('&nbsp;');
   $(obj).find('.watchTable').html('&nbsp;');
   $(obj).find('.watchTable').attr('start_timer','-1');
   
   
   $(obj).find(".table_table_").toggleClass("bor_blue", true);
   $(obj).find(".table_table_mini").toggleClass("bor_blue", true);
   $(obj).find(".table_table_").toggleClass("bor_red", false);
   $(obj).find(".table_table_mini").toggleClass("bor_red", false);
 //  alert(jsonParse.newgame);
   $(obj).attr('newgame',0);   
}


// удаление спредупреждением
$(document).on('click','.ajax_vibor',function(){
obj_=this;
 parent.jQuery.fancybox.close();
//alert($(obj_).attr('post_string'));
send_ajax(obj_);

})
$(document).on('click','.mess_shtraph',function(){
    mess=$(this).attr('mess');
    obj_=this;
    window_modal(mess,460,'', true);
    $(document).on('click','.buttonOK[bool="true"]',function(){

        send_ajax(obj_);
    });
});



$(document).on('click','.delete_val',function(){
     mess=$(this).attr('mess');
     obj_=this;
 
     window_modal('Ви підтверджуєте видалення "'+mess+'"?',460,'', true);
 $(document).on('click','.buttonOK[bool="true"]',function(){
  
     send_ajax(obj_);
});

});
// удаление спредупреждением тоже самое но выполнено через функцию, нужно напрмимер в удалениях внутри редактированого элемента
function delete_val(mess){
     window_modal('Ви підтверджуєте видалення "'+mess+'"?',460,'', true);
     $('.sbutton_ a[bool="true"]').on({
  click: function() {
     return true;
     //send_ajax(obj_);
    }
});
};
function sleep(milliseconds) {
    const date = Date.now();
    let currentDate = null;
    do {
        currentDate = Date.now();
    } while (currentDate - date < milliseconds);
}
function mess_modal(mess,code){
   // alert('1')

    code = (typeof code == 'undefined' ? 0 : 1);
   // alert(code);
    if (code)  mess = atob(mess);
     window_modal(mess,460,'', false);
  //   sleep(2000);
 //   setTimeout("tempfun", 15000);
  //  alert('11')

};
// иницилизация даты
function date_input(){

      $(".datepicker").datepicker({
      inline: true ,
             firstDay :1,
             dateFormat:"dd.mm.yy",
          showButtonPanel: true,
          changeMonth: true,
          changeYear: true,
          showOtherMonths: true,
          selectOtherMonths: true,
          yearRange: "-80:+00"
      });
//});
//});
}

var sort_arr='';
function sort_(){
  //  alert('sort')
    jQuery(function($) {
    $("#list_ ul").sortable({ opacity: 0.8, cursor: 'move', update: function() {
            sort_arr = $(this).sortable("serialize");
      //      alert(sort_arr);
        }
        });
    });

}
// сортировка окно
 function spis_sort(name,action_,module_,post_string_){
// 
   action_ = ( action_ == undefined ? '' : action_);
     module_ = ( module_ == undefined ? '' : module_);
     post_string_ = ( post_string_ == undefined ? '' : post_string_);
     if (is_mobile)  width=370; else width=455;
 window_modal(content,width,'430', true, name,'','','','text-align: left;');
   $('.sbutton_ a[bool="true"]').on({
  click: function() {
    sort_();
   // alert('tyt ' +post_string_+'    '+sort_arr);

     post_string_='&save=1&'+post_string_+'&'+sort_arr;
     send_ajax('',action_,module_,post_string_);
      }
});
return false;
}
reload_page_();
function reload_page_(){
if(typeof reload_page=="undefined"){reload_page = ""}
if (reload_page){
$(document).on('click','#reload_page_ajax',function(){  
send_ajax('');
    });

  $('#reload_page_ajax').trigger('click');
        //ajax_send('content_return', '&reload_page=1', '', '');
}
}

    function spis_select(name,field_name_spis){
window_modal(content,'455','430', '', name,'','','','text-align: left;');
//alert(name+' '  +field_name_spis)
  //  $(document).ready( function(){
    $(".pane-list li").click(function(){
        t_id = $(this).find("span").attr("id");
         t_id=t_id.replace(/spr_name_id_/g, "" );
        t_html = $(this).find("span").html();
        $('#p_'+field_name_spis+'_name').val(t_html);
         $('#p_'+field_name_spis+'_id').val(t_id);
        //modalclose(dialog);
        parent.jQuery.fancybox.close();
    });
//});
return false;
}
// прогресс бар
prev_prc=0;
function progresbar(prc,text_,action='',module=''){
    text_ = (typeof text_=="undefined" ? '' : text_)
    $(document).on('click','#progress_text_title_',function(){
        $('#progress_text_title_').html(text_);
    });
    $(document).on('click','#pb div',function(){

        // меняем состояние прогрессбара
        $('#pb div').stop(true).animate({width: prc + '%'},
            {
                // пошагово ищменяя текст состояния
                step: function(now)
                {
                    // записываем в текст
                    $(this).text(Math.round(now) + '%');
                },
                duration: 2000
            });
    });
    $('#pb div').trigger('click');
    $('#progress_text_title_').trigger('click');
 //   console.log(prc)
  //  console.log(text_)
    if (prc==0) send_ajax('',action,module,'','false',0,'window_progress_return',535,28);
    if (prc<100){
        send_ajax('',action,module,'','false',0);
    }
  /*  if ( prev_prc!=prc){
        if (prc>=100) {
            prev_prc=prc;
            send_ajax('',action,module,'','true');

        }
    }*/
    // когда сайт загружен для прогресс бара
    $(document.body).ready(function()
    {
// заставляем фоновую картинку анимировать
        x = 0;
        setInterval(function()
        {
            $('#pb div').css('background-position', (x++) + '0px 0px' );
        }, 50);
    });
}

function filters(){
//$(document).ready(function(){
  	$(".filter_trigger_"+filter_name).click(function(){
		$(".filter_panel").toggle("fast");
		$(this).toggleClass("active");
		return false;
	});
    $(".close_filter").click(function(){
    $(".filter_panel_"+filter_name).hide();
		return false;
})
//});
}
function validateForm(){
   // alert('nameForm')
 //  $(document).ready(function(){
 /*  	$("[class^=validate]").validationEngine({
		success :  false,
		failure : function() {}
	})*/
//});
return false;
}
function file_type(type_file,rozsh){
    //если это картинка то возвращаем false
    if (type_file.indexof('image')>0) {
        return false;
    }else{
       obj_type = {AC3:1, ACE:1, ADE:1, ADP:1, AI:1, AIFF:1, AU:1, AVI:1, BAT:1, BIN:1, BMP:1, BUP:1, CAB:1, CAT:1, CHM:1, CSS:1, CUE:1, DAT:1, DCR:1, DER:1, DIC:1, DIVX:1, DIZ:1, DLL:1, DOC:1, DOCX:1, DOS:1, DVD:1, DWG:1, DWT:1, Default:1, EMF:1, EXC:1, FON:1, GIF:1, HLP:1, HTML:1, IFO:1, INF:1, INI:1, INS:1, IP:1, ISO:1, ISP:1, JAVA:1, JFIF:1, JPEG:1, JPG:1, LOG:1, M4A:1, MID:1, MMF:1, MMM:1, MOV:1, MOVIE:1, MP2:1, MP2V:1, MP3:1, MP4:1, MPE:1, MPEG:1, MPG:1, MPV2:1, NFO:1, PDD:1, PDF:1, PHP:1, PNG:1, PPT:1, PPTX:1, PSD:1, RAR:1, REG:1, RTF:1, SCP:1, THEME:1, TIF:1, TIFF:1, TLB:1, TTF:1, TXT:1, UIS:1, URL:1, VBS:1, VCR:1, VOB:1, WAV:1, WBA:1, WMA:1, WMV:1, WPL:1, WRI:1, WTX:1, XLS:1, XLSX:1, XML:1, XSL:1, ZAP:1, ZIP:1

}
       if (obj_type[rozsh.toUpperCase]=1)  return rozsh;
       else return 'Default';     
    }
}
//показать меню
menu_show();
function menu_show(){
//jQuery(document).ready(function(){
   
                jQuery("#nav li").hover(function(){
                    jQuery(this).find('ul:first').css({visibility: "visible",display: "none"}).slideDown(400);
                },function(){
                    jQuery(this).find('ul:first').css({visibility: "hidden"});
                });
          //  });
   }
function tabs_work()
{   
  //  jQuery(document).ready(function(){
        $(document).on('click','li',function(){
        var number = $(this).index();
        $('table.tab').slideUp(0).eq(number).slideDown(0);
        $('li').removeClass('inactive').addClass('active');
        $('li').not(this).removeClass('active').addClass('inactive');
    });

    $('table.tab').not(':first').hide();
//});
}
function fancyImageShow()
{
     //   jQuery(document).ready(function(){
    		$('.fancybox-buttons').fancybox({
				openEffect  : 'none',
				closeEffect : 'none',
    			prevEffect : 'none',
				nextEffect : 'none',
    			closeBtn  : false,
    			helpers : {
					title : {
					type : 'inside'
					},
					buttons	: {}
				},
    			afterLoad : function() {
					this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
				}
			});
//});
}
filter_ok =false;

//$(document).ready( function(){
  //  alert('tyt1');
  // фильтр по enter
  $(document).on('keyup','.filternameSS',function(event){
     if(event.keyCode==13){
        filter_s=$(this).find('input[name="form[filter_s]"]').val();
        //alert(filter_s)
        filter_field=$(this).find('input[name="form[filter_field]"]').val();
        filter_field_bd=$(this).find('input[name="form[bd_field]"]').val();
       // wintype=$(this).find('input[name="form[wintype]"]').val();
      //  field_result=$(this).find('input[name="form[field_result]"]').val();
      //  module=$(this).find('input[name="module"]').val();
      //  action=$(this).find('input[name="action"]').val();
       // alert(module);
      // if (wintype!='')
    //       send_ajax('',action,module,'&form[wintype]='+wintype+'&form[field_result]='+field_result+'&form[filter_s]='+filter_s+'&form[filter_field]='+filter_field+'&form[bd_field]='+filter_field_bd,true,2,'window_return');
    //    else   
           send_ajax('','','','&form[filter_s]='+filter_s+'&form[filter_field]='+filter_field+'&form[bd_field]='+filter_field_bd);
        return false;
     }   
    })
 /*$(document).on('click','.addplayertogrp',function(){ 
    
     post_string_='etap_id=62&turnir_id=117';
   //  alert(post_string_)
     send_ajax('','addplayertogrp','etapplayers',post_string_,true);
 });*/  
  // окно фильтра 
$(document).on('click','.filter',function(){
    filter_ok=true;
    filter_name=$(this).attr('filter_name');
        //alert(filter_name);
    $(".filter_panel").hide();
	$(".filter_panel_"+filter_name).toggle("fast");
    $(".filter_panel_"+filter_name).find('input[type=text]').focus();
		$(this).toggleClass("active");
		return false;

   });

    // сортировка колонок таблицы
    $(document).on('click','.sort_cols',function(){
  //  $('.sort_cols').on('click',function(){
        field=$(this).attr('sort');
        action=$(this).attr('action');
        module=$(this).attr('module');
       // alert('tyt');
        if (field && (down_left==0) && filter_ok==false){ 
          send_ajax('',action,module,'sort_cols='+field);
          } 
          if (down_left==2) down_left =0; 
          filter_ok =false;
   }); 

     $(".close_filter").click(function(){
    $(".filter_panel_"+filter_name).hide();
		return false;
})   
// выбор элемента со всплывающего окна внешнего модуля
$(document).on('click','.element_vibor',function(){
        t_id = $(this).attr("id");
        
        jsonreturn = $(this).attr("jsonreturn");
        if (typeof jsonreturn!='undefined'){
            jsonReturn_decode = atob(jsonreturn);
          //       console.log('jsonreturn'+jsonreturn)
            //     console.log('jsonReturn_decode'+jsonReturn_decode)
        const obj_json = JSON.parse(jsonReturn_decode);
        // console.log('obj_json'+obj_json)
        for (var key in obj_json) {
  // этот код будет вызван для каждого свойства объекта
  // ..и выведет имя свойства и его значение
//console.log( "Ключ: " + key + " значение: " + obj_json[key]
// пройдемся по json и всем полям присвоим значение по умолчанию
// для пола делаем исключение нужно для радиобоксов подумать более красивый вариант, но так побыстрому так
 if (key=='sex') {
   if (obj_json[key]=='f') {
       $('#'+key+'1').prop('checked', true);
       $('#'+key+'0').prop('checked', false);
   }
 }

$('#'+key).val(obj_json[key])
  
}
        }
   
        
        
        result_field = $(this).attr("result");
        
        field = $(this).attr("field");
         t_id=t_id.replace(/element_vibor_id_/g, "" );
          $('#p_'+field+'_name').val(result_field);
         $('#p_'+field+'_id').val(t_id);
        //modalclose(dialog);
      //  alert('tyt');
  //      parent.jQuery.fancybox.close();
  //      After_element_vibor();
     //   alert('tyt2')
     
    });
    // функция котрая выполянет разные фичи после выбора пользователя
 function After_element_vibor(val)
 {
   //val= $("#Prostidtype_etap" ).val();
   //alert(val);
  //   console.log('after='+ val)
   if (val==1) 
   {
    //   console.log('tyty11')
   $("#trId_group_id").show(1000);  
   $("#trId_cnt_grp").show(1000); 
  }  else
 {
   //  console.log('tyty22')
     $("#trId_group_id").hide(1000);
     $("#trId_cnt_grp").hide(1000);
  }
  // alert(val)
 }
//переход по страницам
 $(document).on('click','.page_num',function(){
        field=$(this).attr('num');
        action=$(this).attr('action');
        module=$(this).attr('module');
     post_string=$(this).attr('post_string');
        if (field){
          send_ajax('',action,module,'page_number='+field+'&'+post_string);
          }
   });
$(document).on('click','.page_grp',function(){

    field=$(this).attr('num');
    console.log(field)
    action=$(this).attr('action');
    module=$(this).attr('module');
    post_string=$(this).attr('post_string');
    if (field){
        send_ajax('',action,module,'page_items='+field+'&'+post_string);
    }
});
 //редактирования элемента на лету
 $(document).on('dblclick','.bordered td.editTd',function(){
            $(this).TableFieldUpdate();   
    });
 //поиск по первых буквах
//$(document).SpeedSearchElemens();
// $(document).on('keyup','input[speedsearch]',function(){$(this).SpeedSearchElemens();});

 
  function start_time(cntSec)
       {
         time_beg = new Date('2022-02-02T00:00:00');
         time_beg.setSeconds(cntSec);
         return time_beg;
       }

$(document).on('click','#sendFinishGame',function(){
     post_string=$(this).attr('post_string')+'&' +$("#form_edit_form"). serialize();
    
      $(".btn_finish_game").css("display", "none"); // Для показа
    // gameid=$(this).attr('gameid');
    // post_string = post_string + '&id='+gameid;
   //  alert('sendFinishGame==='+post_string);
     module=$(this).attr('module');
     action=$(this).attr('action');
     table_id=$(this).attr('table_id');
     gameid=$(this).attr('gameid');
  //   gameid=$(this).attr('gameid');
   
    // obj_=this; 
   $res1 = $('#res1').val();
   $res2 = $('#res2').val();
  //  alert($set1)
   // alert(post_string)
     parent.jQuery.fancybox.close();
    
   // if (table_id == '') alert('tyt'); else alert('222');
 //   alert(module)
    
    content =  ajax_content(action,module,post_string);
 //   alert(content)
     if (content=='OK') {
      if (table_id == '')
      {
        
        setResultGame(gameid,$res1,$res2);
      } else
      {
        obj=$('#tableBig_'+table_id);
        cleanTable(obj);
      } 
      
 }  
 // send_ajax('',action,module,post_string_,false);
});
function setResultGame(gameid,$res1,$res2)
{
   // alert(gameid)
  //  alert($res1)
  //  alert('javaFinishTime='+$javaFinishTime)
  if ($javaStartTime=='') startT = ''; else startT = 'Старт='+$javaStartTime+' ';
        $('#dataName--set_1--'+gameid).html($res1);
        $('#dataName--set_2--'+gameid).html($res2);
        $('#dataName--start_game--'+gameid).html(startT+'Фініш='+$javaFinishTime);
    
}     
function setResultWin(){   
  

   /* только цифри можно вводить*/
   $('body').on('input', '.text-field__input', function(){
	value = this.value.replace(/[^0-9]/g, '');
    if (value < $(this).data('min')) {
		this.value = $(this).data('min');
	} else if (value > $(this).data('max')) {
		this.value = $(this).data('max');
	} else {
		this.value = value;
	}
  //  alert('field__input11')
}); 
 
 $(".text-field__input").click(function() { $(this).select(); }); 
 
    /*при нажатии на input*/
//$(document).on('keyup','.text-field__input',function(event){    
 $('body').on('input', '#res1', function(){
     //   alert('res1')
   ValRes1 = $('#res1').val();
    ValRes2 = $('#res2').val(); 
    if (ValRes2=='') {ValRes2 = 0; $('#res2').val('0'); }
  
 ///   alert('ValRes1='+ValRes1+ ' ValRes2='+ValRes2)
    if (ValRes1>ValRes2) {
    $(".btn_finish_game").css("display", "block"); // Для показа
    $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false); 
    }
     if (ValRes1<ValRes2) {
             $(".btn_finish_game").css("display", "block"); // Для показа
     $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);      }
       if (ValRes1==ValRes2) {
         $('#res1').val(0);
        }
  if (ValRes1==ValRes2 && ValRes1==0) {
     $(".btn_finish_game").css("display", "none"); // Для показа
  }  
});
    /*при нажатии на input*/
  //  $('#res2').keyup(function(){
  $('body').on('input', '#res2', function(){   
     //  alert('res2')
   ValRes1 = $('#res1').val();
    ValRes2 = $('#res2').val(); 
    if (ValRes1=='') {ValRes1 = 0; $('#res1').val('0'); }
  
  //  alert('ValRes1='+ValRes1+ ' ValRes2='+ValRes2)
    if (ValRes1>ValRes2) {
             $(".btn_finish_game").css("display", "block"); // Для показа
           $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false); 
    }
     if (ValRes1<ValRes2) {
             $(".btn_finish_game").css("display", "block"); // Для показа
     $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);      }
       if (ValRes1==ValRes2) {
         $('#res2').val(0);
        }
     if (ValRes1==ValRes2 && ValRes1==0) {
     $(".btn_finish_game").css("display", "none"); // Для показа
  }  
});
   $(document).on('click','.vibres',function(){
      rahun=$(this).attr('rahun');
      $(".btn_finish_game").css("display", "block"); // Для показа
       if (rahun=='20')
       {
           //  console.log('30')
           $('#res1').val('2');
           $('#res2').val('0');
           $('#player1').toggleClass("losePlayer", false);
           $('#player1').toggleClass("winPlayer", true);
           $('#player2').toggleClass("losePlayer", true);
           $('#player2').toggleClass("winPlayer", false);
       }
       if (rahun=='21')
       {
           //  console.log('30')
           $('#res1').val('2');
           $('#res2').val('1');
           $('#player1').toggleClass("losePlayer", false);
           $('#player1').toggleClass("winPlayer", true);
           $('#player2').toggleClass("losePlayer", true);
           $('#player2').toggleClass("winPlayer", false);
       }



       if (rahun=='30')
    {
      //  console.log('30')
       $('#res1').val('3');
      $('#res2').val('0');      
        $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false);  
    } 
     if (rahun=='31')
    {
       $('#res1').val('3');
      $('#res2').val('1');   
        $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false);     
    }  
   if (rahun=='32')
    {
       $('#res1').val('3');
      $('#res2').val('2');
       $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false);      
    }
       if (rahun=='12')
       {
           $('#res1').val('1');
           $('#res2').val('2');
           $('#player1').toggleClass("losePlayer", true);
           $('#player1').toggleClass("winPlayer", false);
           $('#player2').toggleClass("losePlayer", false);
           $('#player2').toggleClass("winPlayer", true);
       }
       if (rahun=='02')
       {
           $('#res1').val('0');
           $('#res2').val('2');
           $('#player1').toggleClass("losePlayer", true);
           $('#player1').toggleClass("winPlayer", false);
           $('#player2').toggleClass("losePlayer", false);
           $('#player2').toggleClass("winPlayer", true);
       }
       if (rahun=='23')
    {
       $('#res1').val('2');
      $('#res2').val('3');
       $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);
    }
     if (rahun=='13')
    {
       $('#res1').val('1');
      $('#res2').val('3');  
       $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);    
    } 
     if (rahun=='03')
    {
       $('#res1').val('0');
      $('#res2').val('3'); 
      $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);
 
    
    }  
        if (rahun=='LW')
    {
       $('#res1').val('L');
      $('#res2').val('W');  
      $('#break_1').val('1'); 
      $('#player1').toggleClass("losePlayer", true);
      $('#player1').toggleClass("winPlayer", false);
      $('#player2').toggleClass("losePlayer", false);
      $('#player2').toggleClass("winPlayer", true);
    } 
    if (rahun=='WL')
    {
       $('#res1').val('W');
      $('#res2').val('L'); 
      $('#break_2').val('1'); 
      $('#player1').toggleClass("losePlayer", false);
      $('#player1').toggleClass("winPlayer", true);
      $('#player2').toggleClass("losePlayer", true);
      $('#player2').toggleClass("winPlayer", false);
    } 
    });
}
function getTables(){        // alert('tttt')
         //   test  = $('.watchTable').attr('start_timer');
           // alert(test)
                $('.watchTable').each(function(i,elem) {
//	alert('ssss');
	      stT = $(elem).attr('start_timer');
          if (stT!='-1')	StartTimer(stT,elem);

              });          
                
 }                
function StartTimer(stT,elem)
{  // alert(stT)
        startTime = start_time(stT);
      //        alert(startTime);
                $(elem).epiclock({mode: $.epiclock.modes.explicit, format: 'H:i:s', time: startTime});
  
}

 $(document).on('click','.slct',function(){
		/* Заносим выпадающий список в переменную */
		var dropBlock = $(this).parent().find('.drop');
		var val_act = $(this).attr('val');
        val_ = 0;
		/* Делаем проверку: Если выпадающий блок скрыт то делаем его видимым*/
		if( dropBlock.is(':hidden') ) {
			dropBlock.slideDown();
			
			/* Выделяем ссылку открывающую select */
			$(this).addClass('active');
			
			/* Работаем с событием клика по элементам выпадающего списка */
//			$('.drop').find('li').click(function(){

			
		/* Продолжаем проверку: Если выпадающий блок не скрыт то скрываем его */
		} else {
			$(this).removeClass('active');
			dropBlock.slideUp();
		}
       
		/* Предотвращаем обычное поведение ссылки при клике */
	//	return false;
	});	
//}); 
// печать конктетного элемеента
function print_page(elem)
{//alert('tyt')
//alert($('.'+elem).html());
    w=window.open();
w.document.write($('.'+elem).html());
w.print();
w.close();
}



// функция продолжает запускать скрипт до того как не вернется успех об окончании скрипта          