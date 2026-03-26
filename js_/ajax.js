// переменная для хранения адресса удаленного сервера
var globalServerAdress = 'https://'+location.hostname+'/#';
//alert(location.hostname)
//console.log(location.hostname);
var globalFormStatus=1;
// если установлено значение true, выводиться подробное сообщение об ошибках
var showErrors = false;
var funkc_return_ = 'content_return';
var post_string_ ='';
action = '';
content = '';
dop_info = '';
java_script = '';
module= '';
serverAddress ='';
host_server ='';
close=0;
time_close=1;
reload=0;
time_close_save=2;
last_href='';
content_ajax='';
wintype='';
field_result='' // пока работаем со открытым окном, то помним вызвашое поле 
field_result_name='';
module_win = '';
action_win ='';



function t(){
//$.modal.close();
    parent.jQuery.fancybox.close();
    $('#slugeb_info').fadeOut('normal');
}
// АСИНХРОННАЯ функция аякс главная функция админки
//  запуск происходит по указанию класа в любом теге html "ajax_send"
// или вызовом данной функции с указанием объекта
//пример вызова с html <a href="#" module="catalog" action="zakaz_tov_list"  class="ajax_send">test</a>
// пример вызова с объекта
/* описание параметров:
   obj -  объект в котром есть нужные атрибуты
   action_ - действие которое нужно выолнить на сервере
   module_ - модуль который нужно выполнить для обработки
   post_string_ - передаем параметр допольнительные php скрипту по логике модуля
   return_content_bool_ -  возвращать ли контент или просто тихо выполнить передачу и все
   blok_ - блокировать ли экран когда ждем действие от сервака
*/
is_mobile = 0;
function send_ajax(obj,action_,module_,post_string_,return_content_bool_,blok_,funkc_return,width_,height_){
    redirectStatus='0';wintype_='';wintype='';
    $('#zag_left').addClass('hide');
    $('#zagl_module').addClass('hide_bigscreen');
    $('#zagl_left_main').addClass('hide_bigscreen');
    if ( obj == undefined || obj=='' ){
        if (obj=='') {redirectStatus='1'}
        action = (typeof action_ == 'undefined' ? '' : action_);
        module = (typeof module_ == 'undefined' ? '' : module_);
        funkc_return = ( funkc_return === undefined ? false : funkc_return);
        blok = (typeof blok_ == 'undefined' ? 2 : blok_);
        return_content_bool = (typeof return_content_bool_ == 'undefined' ? true : return_content_bool_);
        form_name = (typeof form_name == 'undefined' ? 'form_edit_form' : form_name);
        if (module=='' || action=='')
            href = document.location.hash.replace('#', '');
        else
            href='';
        //console.log('href0='+href)
    }else{
        href=$(obj).attr('href');
        module=$(obj).attr('module');
        action=$(obj).attr('action');
        post_string=$(obj).attr('post_string');
        form_name=$(obj).attr('form_name');
        funkc_return=$(obj).attr('funkc_return');
        wintype_=$(obj).attr('wintype'); // модальное окно fansybox
        wintype_divId=$(obj).attr('win_div_id'); // модальное окно fansybox div_id
        name_window=$(obj).attr('name_window'); // модальное окно fansybox div_id

//alert(action)
        //alert(name_window)
        field_result_=$(obj).attr('field_result'); // модальное окно fansybox
        field_result_name_=$(obj).attr('field_result_name'); // модальное окно fansybox
        serverAddress=$(obj).attr('serverAddress');
        befor=$(obj).attr('befor');
        blok=$(obj).attr('blok');
        width_=$(obj).attr('width_');
        host_server=$(obj).attr('host_server');

        return_content_bool=$(obj).attr('return_content_bool');

    }
//    var uploadfile = $("#main_slider").val();
    //
    width_body=document.body.clientWidth; // ширина
    is_mobile =  width_body<768 ? 1 : 0;

    height_body=document.body.clientHeight; // высота
    wintype = (typeof wintype_ == 'undefined' || wintype_=='' ? wintype : wintype_);
//      (wintype)
    if (typeof wintype_ != 'undefined' && wintype_!='') {
        module_win = module
        wintype_='';
    }
    field_result = (typeof field_result_ == 'undefined' ? field_result : field_result_);
    field_result_name = (typeof field_result_name_ == 'undefined' ? field_result_name : field_result_name_);
    post_wind ='';

    //console.log('href='+href)
    href = (typeof href == 'undefined' ? false : href.replace('#', ''));
    //  console.log('href2='+href)
    // alert(action + href +' reload_page='+reload_page)
    if (reload_page ){
        //  alert('rel' + reload_page)
        href = document.location.hash.replace('#', '');
        //console.log('href_reload='+href)

    }
    if (href) {

        last_href = href;
        // Парсим hash URL формата: module-action-&param1=value1&param2=value2
        // Или: module-action-param1=value1&param2=value2
        // Находим первое вхождение параметров (начинается с & или без него)
        var firstDashIndex = href.indexOf('-');
        if (firstDashIndex !== -1) {
            var secondDashIndex = href.indexOf('-', firstDashIndex + 1);
            if (secondDashIndex !== -1) {
                module = href.substring(0, firstDashIndex);
                action = href.substring(firstDashIndex + 1, secondDashIndex);
                // Все что после второго дефиса - это параметры
                // Например: turnirs-list-league_id=3 или turnirs-list-&league_id=3
                post_string = href.substring(secondDashIndex + 1);
                // Если post_string не пустой и содержит параметры (есть =)
                if (post_string && post_string.trim() !== '' && post_string.indexOf('=') !== -1) {
                    // Если post_string не начинается с &, добавляем его
                    if (post_string.indexOf('&') !== 0) {
                        post_string = '&' + post_string;
                    }
                    // console.log('Parsed post_string from hash:', post_string);
                } else {
                    post_string = '';
                }
            } else {
                // Только один дефис: module-action
                aCurrentUrl = href.split('-');
                module = aCurrentUrl[0];
                action = aCurrentUrl[1];
                post_string = '';
            }
        }
    }
    //  console.log('post_string='+post_string)
    return_content_bool = (typeof return_content_bool == 'undefined' ? true : return_content_bool);
    blok = (typeof blok == 'undefined'  ? 2 : blok);
    //blok = ( blok == undefined ? true : false);
    module = (typeof module == 'undefined' ? 'players' : module);
    form_name = (typeof form_name == 'undefined' ? 'form_edit_form' : form_name);
    // возвращаем модуль и дейтсвие переданное первоначально для окна
    if (wintype && field_result){
        post_wind = '&wintype='+wintype+'&field_result='+field_result+'&field_result_name='+field_result_name;
        //  module = module_win
        //  action = action_win
        //   console.log('post_wind='+post_wind)
    }


    // обработка textarea

    $('#'+form_name+' textarea').each(function(n,element){
        id_=$(element).attr('id');
        tyny_temp=tinyMCE.get(id_);
        if (typeof id_!="undefined" && typeof tyny_temp !="undefined"){
         //   alert('=='+id_+'--');
       //     alert(tinyMCE.get(id_).getContent())
            $('#'+id_).val(tinyMCE.get(id_).getContent());

        }
    });
    action = (typeof action == 'undefined' ? 'parts_list' : action);
    befor = (typeof befor == 'undefined' ? '' : befor);
    post_string = (typeof post_string == 'undefined' ? '' : post_string);
    seril ='';
    //post_string = (typeof window.post_string_!= 'undefined' && window.post_string_!='' ? window.post_string_  :post_string );
    post_string_ = (typeof post_string_ == 'undefined' ? '' : post_string_);
    // console.log('post_string_='+post_string_)
    // console.log('post_wind='+post_wind)
    if (post_string_!='' || post_wind !='')
        post_string = post_string +'&'+ post_string_;
    else
    {
        seril =$("#"+form_name). serialize();
       // alert(seril)
        //   console.log('form_name='+form_name+' seril='+seril);
        if (seril!='')
        {
            form_data_ =  document.getElementById(form_name)
            //  console.log(formData);
            //  console.log('form_name='+form_name);
            formData = new FormData(form_data_); // создаем новый экземпляр объекта и передаем ему нашу форму (*)

            // post_string = post_string +'&' +$("#"+form_name);
            post_string = post_string+'&id_elem=455&new_val=766';
            // console.log('ser='+seril);
            //  console.log('post_string='+post_string);
            const words = post_string.split('&');
            words.forEach(function(item, i, arr) {
                if (item)
                {
                    valElem = item.split('=');
                    if (valElem)
                    {
                        formData.append(valElem[0],valElem[1]);
                        //console.log('valElem='+valElem[0])
                        //console.log('Elem='+valElem[1])

                    }
                    //  formData.append('redirectStatus',redirectStatus);

                }
                // console.log('i='+i+' item='+item)
            });
            //console.log(words[1]);

            //   form_data_ =  $("#"+form_name)
            //   var formData = new FormData($('#'+form_name));
            //  var formData = $("#"+form_name[0]).serializefiles();
            // return ;
            //  console.log(form_Data)
        }


    }
    //  console.log(new FormData(form_data))

    //alert(post_string)
//console.log('redirectStatus='+redirectStatus)
    if (seril!='')
    {
        formData.append('redirectStatus',redirectStatus);

    }
    else {
        // Формируем inputValue с учетом post_string и post_wind
        inputValue = '';
        if (post_string && post_string.trim() !== '') {
            inputValue = post_string;
            // Убираем начальный & если есть, чтобы не было двойного &&
            if (inputValue.indexOf('&') === 0) {
                inputValue = inputValue.substring(1);
            }
        }
        if (post_wind && post_wind.trim() !== '') {
            inputValue += (inputValue ? '&' : '') + post_wind.substring(1); // Убираем начальный & из post_wind
        }
        inputValue += (inputValue ? '&' : '') + 'redirectStatus='+redirectStatus+'&width_body='+width_body+'&height_body='+height_body;
    }
    // console.log(inputValue)
    // Сохраняем post_string перед очисткой, так как он нужен для формирования inputValue ниже
    var saved_post_string = post_string || '';
    post_string='';
    post_string_='';
    // funkc_return = (typeof funkc_return == "undefined" ? funkc_return_ : funkc_return);

    funkc_return__ = ( (typeof wintype == "undefined")  || (wintype=='0') || (wintype=='')? funkc_return_ : 'window_return');
    funkc_return = (typeof funkc_return == "undefined" || funkc_return == false ? funkc_return__ : funkc_return);

    //alert(funkc_return__)
    if (inputValue===false){
        return false;
    }
    host_server = (typeof host_server == 'undefined'  ? location.hostname : host_server) ;
    // //console.log('host_server='+host_server)
    serverAddress = (typeof serverAddress == 'undefined' ?  '' : serverAddress);
//
    if (typeof serverAddress != 'undefined' && serverAddress.indexOf('https://')<0 && serverAddress.indexOf('http://')<0) {
        // Используем тот же протокол, что и текущая страница
        var protocol = window.location.protocol; // 'http:' или 'https:'
        serverAddress = (serverAddress=='') ? globalServerAdress : protocol+'//'+host_server+serverAddress;
        //   //console.log('serverAddress2='+serverAddress)

    }
    // Убираем хеш из serverAddress, если он есть
    if (typeof serverAddress != 'undefined' && serverAddress.indexOf('#') !== -1) {
        serverAddress = serverAddress.split('#')[0];
    }
    $contentType ='application/x-www-form-urlencoded; charset=UTF-8';
    $processData = true;
    if (funkc_return){
        if (seril!='')
        {
            formData.append('ajax_method',1);
            formData.append('module',module);
            formData.append('action',action);
            formData.append('return_content_bool',return_content_bool);
            formData.append('width_body',width_body);
            formData.append('height_body',height_body);
            inputValue = formData ;
            $contentType = false;
            $processData = false;
            //  console.log('inputValue')
            //  console.log(inputValue)
        }
        else
        {
            // Используем сохраненный post_string, так как он был очищен выше
            // Формируем inputValue с параметрами из hash URL (включая league_id)
            inputValue = "ajax_method=1&module=" + module + "&action=" + action + "&return_content_bool="+return_content_bool;
            
            // Добавляем сохраненный post_string (может содержать league_id=3)
            if (saved_post_string && saved_post_string.trim() !== '') {
                // Если post_string начинается с &, убираем его, так как мы уже добавим &
                var postStringClean = (saved_post_string.indexOf('&') === 0) ? saved_post_string.substring(1) : saved_post_string;
                inputValue += "&" + postStringClean;
            }
            
            // Добавляем post_wind, если есть
            if (post_wind && post_wind.trim() !== '') {
                var postWindClean = (post_wind.indexOf('&') === 0) ? post_wind.substring(1) : post_wind;
                inputValue += "&" + postWindClean;
            }
            
            // Добавляем остальные обязательные параметры
            inputValue += "&redirectStatus="+redirectStatus+"&width_body="+width_body+"&height_body="+height_body;
            
            //  console.log('inputValue22')
            //  console.log('saved_post_string='+saved_post_string)
            //  console.log('inputValue='+inputValue)
        }
    }else{
        // return false;
    }
    $('#slugeb_info').html('');
    if (reload=0) {
        reload=1;
        redirect_();
    }
    //    alert(inputValue + '   '+funkc_return +' tyt2')

// alert(globalFormStatus)
    //if (action!='modules_edit_ok'){
    //   console.log('inputValue')
 //  console.log(inputValue)
  //  alert(inputValue);
    $.ajax({
        url: serverAddress,             // указываем URL и
        type: "POST",
        dataType : "json",      // тип загружаемых данных
        beforeSend: function(){
            bool=true;
            if (befor!=''){
                eval('bool='+befor+'()')
                if (bool==false)  {
                    //$.modal.close();
                    parent.jQuery.fancybox.close();
                    return false;
                }
            }
            // включить блокирующе окно загрузки
            if (blok>0){
                $('#slugeb_info').html('<span>Йде завантаження...</span>');
                $("#slugeb_info").fadeIn(600, function () {
                    $("#slugeb_info").fadeOut(600);
                });
                window_modal('',85,85, '', 'Йде заватаження...',blok,'load_wind');
            }
            return true;
        },
        data: inputValue,
        // cache: false,
        // contentType: false,
        contentType: $contentType,
        processData: $processData,
        success: function (json, textStatus) { // вешаем свой обработчик на функцию success
            window.Funkc = funkc_return;
            window.json = json;
            window.width_ = (typeof width_ == "undefined"  ? 900 : width_);
            window.height_ = (typeof height_ == "undefined"  ? 500 : height_);
            eval(window.Funkc+'()')
        },
        error: function(){
            error_fun();
        }
    });
//}
}
//отправка
//function ajax_send(funkc_return, inputValue, module, action,form_name, return_content_bool ,blok,  serverAddress) {
// иницилизировать кэш запросов


// jQuery(document).ready(function(){

$(document).on('click','.ajax_send',function(){
    send_ajax(this);
});
$(document).on('dblclick','.ajax_send_dbl',function(){
    send_ajax(this);
});
$(document).on('click','.ajax_back',function(){
    send_ajax(this);
});
// });

function test(){
//alert('dd')

}
function error_fun(){

    $('#slugeb_info').html('Виникли проблеми при передачі в мережі. Попробуйте ще раз! ');
    //$.modal.close();
    parent.jQuery.fancybox.close();
}
function content_return(){
    json =window.json;
    //alert(json.content)
    
    // Проверяем наличие league_id в hash URL и скрываем фильтры, если они уже отображены
    var currentHash = window.location.hash || '';
    var hasLeagueId = currentHash.indexOf('league_id=') !== -1;
    if (hasLeagueId) {
        // Скрываем фильтры (city-chosen-select, club-chosen-select)
        $('#city-chosen-select').closest('.ms-5').hide();
        $('#club-chosen-select').closest('.ms-5').hide();
        $('.chosen-container').hide();
        // Очищаем содержимое slugeb_info, если там фильтры
        var slugebContent = $('#slugeb_info').html() || '';
        if (slugebContent.indexOf('city-chosen-select') !== -1 || slugebContent.indexOf('club-chosen-select') !== -1) {
            $('#slugeb_info').html('');
        }
    }
    
    obj = $("#data_adminsite");
    obj_menu = $("#submenu");
    obj_mainmenu = $("#meinmenu");
    obj_submenu2 = $("#submenu2");
    menuTurinirs = $("#menuTurinirs");
    obj_zagl_module = $("#zagl_module");
//obj_profile = $("#profile");
    action = (typeof action!='undefined' && action=='redirect_' ? action :(typeof json.action == 'undefined' ? 'parts_list' : json.action));

    reload = json.reload;
    close_ = json.close_;
    post_return = (typeof json.post_return == 'undefined' ? '' : json.post_return);
    //  console.log('content_return='+post_return);
    status_ajax = (typeof json.status == 'undefined' ? '' : json.status);
//  alert(reload)
    //  alert('json.status='+json.status)
    // alert('post_return='+post_return)
    // module = (typeof json.module == 'undefined' ? 'parts' : json.module);
    reload = (typeof reload != 'undefined' ? reload : 0);
    content = json.content
  //  console.log(content)
    // return_content_bool = json.return_content_bool;
    return_content_bool = (typeof return_content_bool == 'undefined' ? 'false' : return_content_bool);
    reload_page = false;
    // alert('posle ' + return_content_bool + ' act='+action+' close_='+close_)
    // alert('posle_content ' + content )

//Walert(action)

    switch (action) {
        case 'login_exit':    // если отлогинились, то нужно сбросить адрессную строку
            document.location.hash = '#';
            break;

        case 'edit_ok' :
            document.location.hash = '#'+post_return;
            last_href = document.location.hash;
            // Вызываем redirect_() для немедленного обновления страницы
            if (post_return != '') {
                redirect_();
            }
            break;
        case 'show' :
            //  console.log('show')
            //    console.log('module='+module)
            if (module=='nomination')
            {
                document.location.hash = '#'+post_return;
                last_href = document.location.hash;
            }

            break;
        case 'anyaction' :
            //  alert('anyaction');
            //  alert(post_return);
            document.location.hash = '#'+post_return;
            last_href = document.location.hash;
            redirect_();
            break;
        case 'anyactionNORedirect' :
            //  alert('anyaction');
            //  alert(post_return);
            document.location.hash = '#'+post_return;
            last_href = document.location.hash;
            //redirect_();
            break;
        case 'edit' :

            break;
        case 'add' :

            break;
        default:
//          console.log('post='+post_return);
            if (post_return!='')
            {
                // console.log(post_return);
                document.location.hash = '#'+post_return;
                last_href = document.location.hash;
                
                // Если java_script содержит send_ajax(""), вызываем его после установки hash
                if (java_script && java_script.indexOf('send_ajax("")') !== -1) {
                    // Заменяем send_ajax("") на вызов с небольшой задержкой, чтобы hash успел установиться
                    java_script = java_script.replace('send_ajax("")', 'setTimeout(function(){ send_ajax(""); }, 50)');
                }
            }
            break;
        //  if (last_href && !href) { document.location.hash = '#'+ last_href;  }

    }
    //alert('reload_page='+reload_page)

    if (typeof json.content != 'undefined'){ //1

        reload_page = true;
        content_body = json.content_body;

        if (content_body){ //3
            // alert('tyt')
            $("#content_html").html(content_body);
            //   $(document).ready(function () { //4
            json.content_body=content_body='';
            if (return_content_bool){//6
                obj.html(content);
                obj_menu.html(json.submenu);
                obj_mainmenu.html(json.mainmenu);
                obj_submenu2.html(json.submenu2);
                menuTurinirs.html(json.menuTurinirs);
                obj_zagl_module.html(json.zagl_module);
                //  alert(json.profile)
                //   obj_profile.html(json.profile);
            } //6-
            if (close_!='0'){//7
                $(document).ready(function () {
                    setTimeout("t()", time_close);
                });
                setTimeout("t()", time_close);
            }else{
                close=1;
                // Если close_='0', не закрываем окно автоматически - сообщение должно оставаться видимым
            }
            if (action=='redirect_'){ //2
                setTimeout("t()", time_close);
                // alert(action);
                //action='';
                redirect_();
                return;

            }// конец редирект  //2-

            //  redirect_();
            //send_ajax('','redirect','parts');
            if (trim(json.content) != '')     redirect_();
//});//4-
            return;
        } //3-



        // если была ошибка php
        if (json.ERRN_AJAX != undefined && json.ERRN_AJAX != '' ){//5
            // alert(json.ERRN_AJAX+' test')
            $('#slugeb_info').html('<span style="font-weight: bold;">Повідомлення!</span> <br />' + '<span style="color:red">'+json.ERRN_AJAX+'</span>');

        }//5-
        java_script = json.java_script;
        //  alert(java_script)
        message_user = json.message_user;
         //   alert(message_user)

        if (message_user!='ERROR!' && message_user!='' && typeof message_user != 'undefined') {
            // Проверяем, нужно ли показывать модальное окно
            // Модальные окна показываем для важных сообщений (предупреждения, успешные операции, ошибки валидации)
            var showModal = false;
            var modalMessages = [
                'Гру розпочато!',
                'Створено ігор:',
                'Данная игра создана автоматически',
                'Данная игра создана автоматически. Удалять нельзя!',
                'Склад команди збережено!',
                'Пари гравців збережено!',
                'Автоматично активовано',
                'Автоматично створено',
                'В цьому турнірі є етапи',
                'В цій лізі є турніри',
                'Видалять спочатку',
                'Удалять нельзя',
                'нельзя'
            ];
            
            // Ключевые слова для ошибок валидации - показываем в модальном окне
            var validationErrorKeywords = [
                'Не знайдено',
                'не свіпадає',
                'Не рівна',
                'повино приймати',
                'Заповніть поле',
                'Груп максимум',
                'Мінімальна кількість',
                'Максимальна кількість',
                'Ви виходите за ліміт',
                'Змінювати параметри',
                'Меняйте признак',
                'Помилка при підрахунку'
            ];
            
            // Проверяем стандартные модальные сообщения
            for (var i = 0; i < modalMessages.length; i++) {
                if (message_user.indexOf(modalMessages[i]) !== -1) {
                    showModal = true;
                    break;
                }
            }
            
            // Если не нашли в стандартных, проверяем ошибки валидации
            if (!showModal) {
                for (var j = 0; j < validationErrorKeywords.length; j++) {
                    if (message_user.indexOf(validationErrorKeywords[j]) !== -1) {
                        showModal = true;
                        break;
                    }
                }
            }
            
            // Если close_='0', показываем в модальном окне, чтобы сообщение не закрывалось автоматически
            if (!showModal && close_ === '0') {
                showModal = true;
            }
            
            if (showModal) {
                // Закрываем окно загрузки перед показом модального окна с сообщением
                var loadingModal = bootstrap.Modal.getInstance(document.getElementById('staticBackdrop'));
                if (loadingModal) {
                    loadingModal.hide();
                }
                // Также закрываем через jQuery, если Bootstrap modal не найден
                $('#staticBackdrop').modal('hide');
                // Закрываем fancybox, если открыт
                if (typeof parent.jQuery !== 'undefined' && typeof parent.jQuery.fancybox !== 'undefined') {
                    try {
                        parent.jQuery.fancybox.close();
                    } catch(e) {}
                }
                // Удаляем элемент загрузки, если есть
                $('#load_wind').remove();
                
                // Сохраняем post_return и JavaScript для обработки после закрытия модального окна
                var savedPostReturn = post_return;
                var savedJavaScript = java_script;
                // Очищаем java_script, чтобы он не выполнился сразу (строка 732)
                java_script = '';
                
                // Показываем модальное окно с небольшой задержкой, чтобы окно загрузки успело закрыться
                setTimeout(function() {
                    // Полностью закрываем и удаляем существующее модальное окно, если есть
                    var existingModalElement = document.getElementById('infoModal');
                    if (existingModalElement) {
                        // Получаем существующий экземпляр Bootstrap Modal
                        var existingModal = bootstrap.Modal.getInstance(existingModalElement);
                        if (existingModal) {
                            // Закрываем модальное окно
                            existingModal.hide();
                            // Удаляем экземпляр и все обработчики
                            existingModal.dispose();
                        }
                        // Удаляем DOM элемент
                        $(existingModalElement).remove();
                    }
                    
                    var width = 500;
                    var height = 200;
                    var formatted_message = '<div style="text-align: center; font-size: 20px; font-weight: 500; padding: 30px 20px; line-height: 1.5;">' + message_user + '</div>';
                    var modal_html = '<div class="modal fade" id="infoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">'
                        + '<div class="modal-dialog modal-dialog-centered" style="min-width: ' + width + 'px; width: ' + width + 'px;">'
                        + '<div class="modal-content">'
                        + '<div class="modal-header" style="border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; position: relative;">'
                        + '<h5 class="modal-title" id="infoModalLabel" style="margin: 0; text-align: center; flex: 1;">Інформація</h5>'
                        + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);"></button>'
                        + '</div>'
                        + '<div class="modal-body" style="padding: 0;">' + formatted_message + '</div>'
                        + '<div class="modal-footer" style="justify-content: center; border-top: 1px solid #dee2e6; padding: 15px;">'
                        + '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="min-width: 100px;">ОК</button>'
                        + '</div>'
                        + '</div></div></div>';
                    
                    // Добавляем модальное окно напрямую в body, не используя контейнер modal_new_window,
                    // чтобы не затронуть содержимое страницы (фильтры и т.д.)
                    $('body').append(modal_html);
                    
                    // Показываем модальное окно
                    var modalElement = document.getElementById('infoModal');
                    var modal = new bootstrap.Modal(modalElement);
                    
                    // Функция для выполнения JavaScript после закрытия модального окна
                    var executeSavedJavaScript = function() {
                        // Удаляем экземпляр и DOM элемент после закрытия
                        setTimeout(function() {
                            if (modal) {
                                modal.dispose();
                            }
                            $(modalElement).remove();
                        }, 100);
                        
                        // Обрабатываем post_return только после закрытия модального окна
                        if (savedPostReturn && savedPostReturn.trim() !== '') {
                            document.location.hash = '#' + savedPostReturn;
                        }
                        
                        // Для определенных сообщений всегда перезагружаем страницу
                        if (message_user && (message_user.indexOf('Гру розпочато') !== -1 || message_user.indexOf('Створено ігор') !== -1)) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 200);
                            return;
                        }
                        
                        // Выполняем сохраненный JavaScript код после закрытия модального окна
                        if (savedJavaScript && savedJavaScript.trim() !== '') {
                            setTimeout(function() {
                                try {
                                    if (savedJavaScript.indexOf('send_ajax') !== -1) {
                                        send_ajax("");
                                    } else {
                                        // Выполняем любой сохраненный JavaScript код (например, window.location.reload())
                                        eval(savedJavaScript);
                                    }
                                } catch(e) {
                                    console.error('Error executing saved JavaScript:', e);
                                }
                            }, 200);
                        }
                    };
                    
                    // Обрабатываем закрытие модального окна (срабатывает при нажатии OK, закрытии через X, клике вне модального окна)
                    modalElement.addEventListener('hidden.bs.modal', executeSavedJavaScript, { once: true });
                    
                    modal.show();
                }, 100);
                
                // НЕ выводим в обычные места - только модальное окно
                // Очищаем старые сообщения, если есть
                if (is_mobile)
                    $('#message_user_mobile').html('');
                else
                    $('#message_user').html('');
            } else {
                // Для остальных сообщений используем обычный вывод
                if (is_mobile)
                    $('#message_user_mobile').html(message_user);
                else
                    $('#message_user').html(message_user);
            }
        } else {
            if (is_mobile)
                $('#message_user_mobile').html('');
            else
                $('#message_user').html('');
        }
        // console.log('message_user='+message_user)
        // вывести сообщение
//alert('return_content_bool='+return_content_bool)
        if (return_content_bool && message_user!='ERROR!'){//6
            obj.html(content);
            obj_menu.html(json.submenu);
            obj_submenu2.html(json.submenu2);
            //console.log(json.menuTurinirs)
// console.log(json.submenu2)
            menuTurinirs.html(json.menuTurinirs);
            obj_mainmenu.html(json.mainmenu);
            obj_zagl_module.html(json.zagl_module);
            //  alert(json.profile)
            //  alert(obj_profile.html())
            //obj_profile.html(json.profile);
        } //6-
        if (close_!='0'){//7
            //  alert('uuuuu');
            $(document).ready(function () {
                setTimeout("t()", time_close);
            });
        }else{
            // alert('111');
            close=1;
        }  //7-
        reload_page = false;

        tabs_work(); //отобразить вкладки если есть

        // функция существует, ее можно вызывать
        if (java_script!=''){    eval(java_script); }
        
        // После выполнения JavaScript привязываем обработчики для командных игр через делегирование событий
        // Используем задержку, чтобы плагины успели инициализироваться
        setTimeout(function() {
            // Находим все элементы с onclick, содержащим showTeamMatchDetails, и заменяем на jQuery обработчики
            $(document).find('[onclick*="showTeamMatchDetails"]').each(function() {
                var $el = $(this);
                var onclickAttr = $el.attr('onclick');
                if (onclickAttr && onclickAttr.indexOf('showTeamMatchDetails') !== -1) {
                    // Удаляем inline onclick и привязываем обработчик напрямую через jQuery
                    $el.removeAttr('onclick');
                    $el.css('cursor', 'pointer'); // Добавляем курсор вручную
                    $el.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof showTeamMatchDetails === 'function') {
                            showTeamMatchDetails(this);
                        } else {
                            console.error('showTeamMatchDetails не определена');
                        }
                    });
                }
            });
        }, 100); // Задержка для инициализации плагинов
        
        if (is_mobile){    eval(buregrMenu()); }

    }//1
    else if (json.MESS_AJAX != undefined){ //1a
//MESS_AJAX = xmlDoc.getElementsByTagName("MESS_AJAX")[0].firstChild.data;
//if (json.java_script != undefined){
        //java_script = xmlDoc.getElementsByTagName("java_script")[0].firstChild.data;
        //alert('reload_page='+reload_page)
        tempCon=$("#data_adminsite").html().trim(); //  проверяем если была перегрузка страниы ничего не выводим
//alert('tempCon='+tempCon+'++')
//}
//if (tempCon=='') 
        // Проверяем, нужно ли показывать модальное окно для MESS_AJAX
        var messText = json.MESS_AJAX;
        
        // Проверяем наличие league_id в hash URL - если есть, фильтры не показываем
        var currentHash = window.location.hash || '';
        var hasLeagueId = currentHash.indexOf('league_id=') !== -1;
        
        // Если есть league_id в hash и это фильтры (city-chosen-select или club-chosen-select), не показываем их
        if (hasLeagueId && messText && (messText.indexOf('city-chosen-select') !== -1 || messText.indexOf('club-chosen-select') !== -1 || messText.indexOf('chosen-container') !== -1)) {
            // Это фильтры, но есть league_id - не показываем их
            $('#slugeb_info').html('');
            // Очищаем содержимое фильтров, если они уже были добавлены
            $('.ms-5.w-100').parent().html('');
            return; // Прерываем выполнение, не показываем фильтры
        }
        var showModal = false;
        var modalMessages = [
            'Гру розпочато!',
            'Створено ігор:',
            'Данная игра создана автоматически',
            'Данная игра создана автоматически. Удалять нельзя!',
            'Склад команди збережено!',
            'Пари гравців збережено!',
            'Автоматично активовано',
            'Автоматично створено',
            'В цьому турнірі є етапи',
            'В цій лізі є турніри',
            'Видалять спочатку',
            'Удалять нельзя',
            'нельзя'
        ];
        
        // Ключевые слова для ошибок валидации - показываем в модальном окне
        var validationErrorKeywords = [
            'Не знайдено',
            'не свіпадає',
            'Не рівна',
            'повино приймати',
            'Заповніть поле',
            'Груп максимум',
            'Мінімальна кількість',
            'Максимальна кількість',
            'Ви виходите за ліміт',
            'Змінювати параметри',
            'Меняйте признак',
            'Помилка при підрахунку'
        ];
        
        // Проверяем стандартные модальные сообщения
        for (var i = 0; i < modalMessages.length; i++) {
            if (messText.indexOf(modalMessages[i]) !== -1) {
                showModal = true;
                break;
            }
        }
        
        // Если не нашли в стандартных, проверяем ошибки валидации
        if (!showModal) {
            for (var j = 0; j < validationErrorKeywords.length; j++) {
                if (messText.indexOf(validationErrorKeywords[j]) !== -1) {
                    showModal = true;
                    break;
                }
            }
        }
        
        // Если close_='0', показываем в модальном окне, чтобы сообщение не закрывалось автоматически
        if (!showModal && close_ === '0') {
            showModal = true;
        }
        
        if (showModal) {
            // Закрываем окно загрузки перед показом модального окна с сообщением
            var loadingModal = bootstrap.Modal.getInstance(document.getElementById('staticBackdrop'));
            if (loadingModal) {
                loadingModal.hide();
            }
            // Также закрываем через jQuery, если Bootstrap modal не найден
            $('#staticBackdrop').modal('hide');
            // Закрываем fancybox, если открыт
            if (typeof parent.jQuery !== 'undefined' && typeof parent.jQuery.fancybox !== 'undefined') {
                try {
                    parent.jQuery.fancybox.close();
                } catch(e) {}
            }
            // Удаляем элемент загрузки, если есть
            $('#load_wind').remove();
            
            // Сохраняем post_return для обработки после закрытия модального окна
            var savedPostReturn = json.post_return;
            var savedJavaScript = json.java_script;
            
            // Показываем модальное окно с небольшой задержкой, чтобы окно загрузки успело закрыться
            setTimeout(function() {
                // Полностью закрываем и удаляем существующее модальное окно, если есть
                var existingModalElement = document.getElementById('infoModal');
                if (existingModalElement) {
                    // Получаем существующий экземпляр Bootstrap Modal
                    var existingModal = bootstrap.Modal.getInstance(existingModalElement);
                    if (existingModal) {
                        // Закрываем модальное окно
                        existingModal.hide();
                        // Удаляем экземпляр и все обработчики
                        existingModal.dispose();
                    }
                    // Удаляем DOM элемент
                    $(existingModalElement).remove();
                }
                
                var width = 500;
                var height = 200;
                var formatted_message = '<div style="text-align: center; font-size: 20px; font-weight: 500; padding: 30px 20px; line-height: 1.5;">' + messText + '</div>';
                var modal_html = '<div class="modal fade" id="infoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">'
                    + '<div class="modal-dialog modal-dialog-centered" style="min-width: ' + width + 'px; width: ' + width + 'px;">'
                    + '<div class="modal-content">'
                    + '<div class="modal-header" style="border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; position: relative;">'
                    + '<h5 class="modal-title" id="infoModalLabel" style="margin: 0; text-align: center; flex: 1;">Інформація</h5>'
                    + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);"></button>'
                    + '</div>'
                    + '<div class="modal-body" style="padding: 0;">' + formatted_message + '</div>'
                    + '<div class="modal-footer" style="justify-content: center; border-top: 1px solid #dee2e6; padding: 15px;">'
                    + '<button type="button" class="btn btn-primary infoModalOkBtn" data-bs-dismiss="modal" style="min-width: 100px;">ОК</button>'
                    + '</div>'
                    + '</div></div></div>';
                
                // Добавляем модальное окно напрямую в body, не используя контейнер modal_new_window,
                // чтобы не затронуть содержимое страницы (фильтры и т.д.)
                $('body').append(modal_html);
                
                // Показываем модальное окно
                var modalElement = document.getElementById('infoModal');
                var modal = new bootstrap.Modal(modalElement);
                
                // Функция для выполнения JavaScript после закрытия модального окна
                var executeSavedJavaScript = function() {
                    // Удаляем экземпляр и DOM элемент после закрытия
                    setTimeout(function() {
                        if (modal) {
                            modal.dispose();
                        }
                        $(modalElement).remove();
                    }, 100);
                    
                    // Обрабатываем post_return только после закрытия модального окна
                    if (savedPostReturn && savedPostReturn.trim() !== '') {
                        document.location.hash = '#' + savedPostReturn;
                    }
                    
                    // Для определенных сообщений всегда перезагружаем страницу
                    if (messText && (messText.indexOf('Гру розпочато') !== -1 || messText.indexOf('Створено ігор') !== -1)) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 200);
                        return;
                    }
                    
                    // Выполняем сохраненный JavaScript код после закрытия модального окна
                    if (savedJavaScript && savedJavaScript.trim() !== '') {
                        setTimeout(function() {
                            try {
                                if (savedJavaScript.indexOf('send_ajax') !== -1) {
                                    send_ajax("");
                                } else {
                                    // Выполняем любой сохраненный JavaScript код (например, window.location.reload())
                                    eval(savedJavaScript);
                                }
                            } catch(e) {
                                console.error('Error executing saved JavaScript:', e);
                            }
                        }, 200);
                    }
                };
                
                // Обрабатываем закрытие модального окна
                modalElement.addEventListener('hidden.bs.modal', executeSavedJavaScript, { once: true });
                
                // Также обрабатываем нажатие на кнопку OK
                $(modalElement).on('click', '.infoModalOkBtn', function() {
                    executeSavedJavaScript();
                });
                
                modal.show();
            }, 100);
        } else if (json.time_save != undefined && json.time_save>0 && tempCon!=''){ //10
//alert(json.MESS_AJAX);
            $('#slugeb_info').html('<span style="color:red">'+json.MESS_AJAX+'</span>');
            time_close_save_= (json.time_save>0) ? json.time_save*1000 : time_close_save;
            $("#slugeb_info").fadeIn(600, function () {
                $("#slugeb_info").fadeOut(time_close_save_);
            });
            //  alert(time_close_save_)
            //  setTimeout("time_save_()", time_close_save_);

            redirect_();
        }else{
            // Проверяем наличие league_id в hash URL - если есть, фильтры не показываем
            var currentHash = window.location.hash || '';
            var hasLeagueId = currentHash.indexOf('league_id=') !== -1;
            
            // Если есть league_id в hash и это фильтры, не показываем их
            if (hasLeagueId && messText && (messText.indexOf('city-chosen-select') !== -1 || messText.indexOf('club-chosen-select') !== -1 || messText.indexOf('chosen-container') !== -1)) {
                // Это фильтры, но есть league_id - не показываем их
                $('#slugeb_info').html('');
                // Очищаем содержимое фильтров, если они уже были добавлены
                $('.ms-5.w-100').parent().html('');
            } else {
                // Если close_='0', показываем в модальном окне вместо slugeb_info
                if (close_ === '0' && json.MESS_AJAX) {
                    // Показываем модальное окно для важных сообщений
                    var formatted_message = '<div style="text-align: center; font-size: 18px; font-weight: 500; padding: 30px 20px; line-height: 1.5; color: red;">' + json.MESS_AJAX + '</div>';
                    var modal_html = '<div class="modal fade" id="infoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">'
                        + '<div class="modal-dialog modal-dialog-centered" style="min-width: 500px; width: 500px;">'
                        + '<div class="modal-content">'
                        + '<div class="modal-header" style="border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; position: relative;">'
                        + '<h5 class="modal-title" id="infoModalLabel" style="margin: 0; text-align: center; flex: 1;">Помилка</h5>'
                        + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);"></button>'
                        + '</div>'
                        + '<div class="modal-body" style="padding: 0;">' + formatted_message + '</div>'
                        + '<div class="modal-footer" style="justify-content: center; border-top: 1px solid #dee2e6; padding: 15px;">'
                        + '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="min-width: 100px;">ОК</button>'
                        + '</div>'
                        + '</div></div></div>';
                    
                    // Удаляем существующее модальное окно, если есть
                    var existingModalElement = document.getElementById('infoModal');
                    if (existingModalElement) {
                        var existingModal = bootstrap.Modal.getInstance(existingModalElement);
                        if (existingModal) {
                            existingModal.hide();
                            existingModal.dispose();
                        }
                        $(existingModalElement).remove();
                    }
                    
                    $('body').append(modal_html);
                    var modalElement = document.getElementById('infoModal');
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    $('#slugeb_info').html('<table border="0"><tr><td><img  src="img/stop.png" border="0" /></td><td><span style="font-weight: bold;"> Увага!</span> <br />' + '<span style="color:red">'+json.MESS_AJAX+'</span></td></tr></table>');
                    if (close_!='0') {
                        $(document).ready(function () {
                            setTimeout("t()", time_close);
                        });
                    }
                }
            }
        }//10-
    }//1a-
    else if (json.ERRN_AJAX != undefined ){//2a
        $('#slugeb_info').html('<span style="font-weight: bold;">Помилка!</span> Вибачьте за незручності! <br />Причина: ' + '<span style="color:red">'+json.ERRN_AJAX+'</span>');
        $(document).ready(function () {
            setTimeout("t()", time_close);
        });

    }

    else{
        if (json.status != undefined ){//2a
            // alert('ffff')
        } else{
            obj.html("<div align='center' style='color:red;'>Невідома помилка. Зверніться до розробника 1245640@gmail.com</div>");
            $(document).ready(function () {
                setTimeout("t()", time_close);
            });
        }
    }//2a-
}
function window_return(){
    json =window.json;
    java_script = json.java_script;
    wintype_divId = (typeof wintype_divId == 'undefined' ? '' : wintype_divId);
    name_window = (typeof name_window == 'undefined' ? '' : name_window);
    //alert('wintype_divId='+wintype_divId)
    //alert('json.content='+json.content)
    //alert('is_window_open='+is_window_open)
    if (json.content != undefined){ //1
        if (!is_window_open || wintype_divId!='')
            window_modal(json.content,window.width_,window.height_,'',name_window,100,wintype_divId);
        else {
            zoom_content = $("#zoom_content");
            zoom_content.html(json.content);
        }



    }//1
// alert(java_script)
    if (java_script!=''){    eval(java_script); }

}

function window_progress_return(){
    json =window.json;
    java_script = json.java_script;
    if (json.content != undefined){ //1

        window_modal(json.content,window.width_,window.height_,'',' Чекайте...',3);

    }//1
    if (java_script!=''){    eval(java_script); }

}


function time_save_(){
    $('#slugeb_info').html('');
    if (reload==1){
        send_ajax('',action,module,post_return);
        return;
    }
    setTimeout("t()", time_close);
}
var status_ajax_func = '';
// новая функция аякс СИНХРОННАЯ которая не заметно выполняет действия аякс и возвращает текст какой-то плюс может выполнить 
// по возвращению любые javascript функциии
function ajax_content(action_,module_,post_string_){
    //  alert('ajax_content');
    href = document.location.hash.replace('#', '');
    module = '';
    action = '';
    post_string = '';
    width_body=document.body.clientWidth; // ширина
    is_mobile =  width_body<768 ? 1 : 0;
    if (href) {
        aCurrentUrl = href.split('-');
        //    //console.log(aCurrentUrl)
        if (aCurrentUrl.length >= 2) {
            module = aCurrentUrl[0];
            action  = aCurrentUrl[1];
            post_string = aCurrentUrl[2];
        }
    }
    action_ = (typeof action_ == 'undefined' ? '' : action_);
    action = (action_ == '' ? action : action_);

    module_ = (typeof module_ == 'undefined' ? '' : module_);
    module = (module_ == '' ? module : module_);
    post_string_ = (typeof post_string_ == 'undefined' ? '' : post_string_);
    post_string = ( post_string_ == '' ? post_string : post_string_);



    inputValue = "ajax_method=2&module=" + module + "&action=" + action + "&width_body=" + width_body + "&" + post_string;
    //  alert(inputValue)
    //  console.log(inputValue)
    content_ajax='hello';
    $.ajax({
        url: globalServerAdress,             // указываем URL и"
        type: "POST",
        async: false, // выполняем синхронно, по умолчанию true асинхронно
        dataType : "json",      // тип загружаемых данных
        data: inputValue,
        success: function (json, textStatus) { // вешаем свой обработчик на функцию success

            post_return = (typeof json.post_return == 'undefined' ? '' : json.post_return);
            content_ajax = (typeof json.content == 'undefined' ? '' : json.content);

            message_user = (typeof json.message_user == 'undefined' ? '' : json.message_user);
            status_ajax_func = (typeof json.status == 'undefined' ? '' : json.status);
            java_script = (typeof json.java_script == 'undefined' ? '' : json.java_script);
            // alert(java_script);
            if (message_user && message_user!='ERROR!' && message_user!='') {
                // Проверяем, нужно ли показывать модальное окно (только для определенных сообщений)
                var showModal = false;
                var modalMessages = [
                    'Гру розпочато!',
                    'Створено ігор:',
                    'Данная игра создана автоматически',
                    'Склад команди збережено!',
                    'Пари гравців збережено!',
                    'Автоматично активовано',
                    'Автоматично створено'
                ];
                
                for (var i = 0; i < modalMessages.length; i++) {
                    if (message_user.indexOf(modalMessages[i]) !== -1) {
                        showModal = true;
                        break;
                    }
                }
                
                if (showModal) {
                    // Создаем специальное информационное модальное окно с центрированным крупным текстом и одной кнопкой "ОК"
                    var width = 500;
                    var height = 200;
                    var formatted_message = '<div style="text-align: center; font-size: 20px; font-weight: 500; padding: 30px 20px; line-height: 1.5;">' + message_user + '</div>';
                    var modal_html = '<div class="modal fade" id="infoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">'
                        + '<div class="modal-dialog modal-dialog-centered" style="min-width: ' + width + 'px; width: ' + width + 'px;">'
                        + '<div class="modal-content">'
                        + '<div class="modal-header" style="border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; position: relative;">'
                        + '<h5 class="modal-title" id="infoModalLabel" style="margin: 0; text-align: center; flex: 1;">Інформація</h5>'
                        + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);"></button>'
                        + '</div>'
                        + '<div class="modal-body" style="padding: 0;">' + formatted_message + '</div>'
                        + '<div class="modal-footer" style="justify-content: center; border-top: 1px solid #dee2e6; padding: 15px;">'
                        + '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="min-width: 100px;">ОК</button>'
                        + '</div>'
                        + '</div></div></div>';
                    
                    // Полностью закрываем и удаляем существующее модальное окно, если есть
                    var existingModalElement = document.getElementById('infoModal');
                    if (existingModalElement) {
                        var existingModal = bootstrap.Modal.getInstance(existingModalElement);
                        if (existingModal) {
                            existingModal.hide();
                            existingModal.dispose();
                        }
                        $(existingModalElement).remove();
                    }
                    
                    // Добавляем модальное окно напрямую в body, не используя контейнер modal_new_window,
                    // чтобы не затронуть содержимое страницы (фильтры и т.д.)
                    $('body').append(modal_html);
                    
                    // Показываем модальное окно
                    var modalElement = document.getElementById('infoModal');
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    
                    // НЕ выводим в обычное место - только модальное окно
                    // Очищаем старое сообщение, если есть
                    $('#message_user').html('');
                } else {
                    // Для остальных сообщений используем обычный вывод
                    $('#message_user').html(message_user);
                }
            } else {
                $('#message_user').html('');
            }
            if (java_script!=''){    eval(java_script); }

        },
        error: function(){
            error_fun();
        }
    });
    return content_ajax;
}

(function($) {
    $.fn.serializefiles = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            //  console.log('val.name='+val.name)
            //  console.log('val.value='+val.value)
            formData.append(val.name, val.value);
        });
        return formData;
    };
})(jQuery);
