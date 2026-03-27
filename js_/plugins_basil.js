//мини плагин изминения поля таблицы на лету  
 (function( $ ){
//  UnikTd  - id поля таблицы td или другого элемента по умолчанию = editTdElem , а полное имя должно быть уникально например так  editTdElem:Namefield:UnikId
  $.fn.TableFieldUpdate = function( options ) {  
     // Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
    var settings = $.extend( {
      'StartUnikTd'         : 'editTdElem', // повторяющее название элемента которое редактировать будем
    }, options);    
    return this.each(function() {

      var $this = $(this);
       t_id = $(this).attr("id");
       var re = new RegExp(settings.StartUnikTd, 'g'); // использования переменных в регулярном выражении
       t_id_unik=t_id.replace(re, "" );
       input_id = $(this).find('#input'+t_id_unik).attr("id");
       if (typeof input_id=="undefined"){ //делаем защиту от повторных нажатий по полю input
        dblclick = $(this).html();
       aElm = t_id_unik.split('--');
       //  this_elem = $(this);
        width= $(this).width();
        elemHtml = $('#dataName'+t_id_unik).html();
          //alert('tyt '+elemHtml)
          if (typeof elemHtml!="undefined"){ //делаем защиту от редактирования ссылок
         $(this).html('<input style="width:99%" id="input'+t_id_unik+'" value="'+elemHtml+'" type="text" name="form['+t_id_unik+']">');
         id_inp = $('#input'+t_id_unik);
         $('#input'+t_id_unik).focus();          
         $('#input'+t_id_unik).setCursorPosition(id_inp.val().length);
         }
         }
         // если теряем фокус то выполняем действия возвращаем все назад без сохранения
         $('#input'+t_id_unik).blur(function () {
              $($this).html(dblclick);
              //$('#dataName'+t_id_unik).html(id_inp.val())
          })
          // обрабатываем нажатия enter и если поменялся текст то отправляем на сервер для сохранения в БД
            $('#'+t_id+'> input').keyup(function(event){
                if(event.keyCode==13){// если это enter
                new_val=$(this).val();
                 if (elemHtml!=new_val)// если есть перемены отправляем аяксом 
                {
                      post_string ='&id='+aElm[2]+'&nameField='+aElm[1]+'&NewvalField='+new_val;  
                      mess =  ajax_content('saveElementInput','',post_string) 
                      if (mess=='OK') {
                          $($this).html('<span id="dataName--'+aElm[1]+'--'+aElm[2]+'">'+new_val+'</span>');
                          $this.css('border','1px solid green') 
                         // $this.animate({'border':'1px solid #ccc'},3000) 
                          $this.animate({'border':'1px solid red'},3000, "linear", function(){
                            $this.css('border','0','border-left: 1px solid #ccc; border-top: 1px solid #ccc;')}) 
                                                 
                      } else {
                        $($this).html(dblclick);
                        $this.css('border','1px solid red') 
                         // $this.animate({'border':'1px solid #ccc'},3000) 
                          $this.animate({'border':'1px solid red'},3000, "linear", function(){
                            $this.css('border','0','border-left: 1px solid #ccc; border-top: 1px solid #ccc;')}) 
                 
                      }
                      //setTimeout(function () { }, 3000);    
                }else{// если нет вернуть как было
                    $($this).html(dblclick);
                }
                //alert(filter_s)
               // filter_field=$(this).find('input[name="form[filter_field]"]').val();
                //  filter_field_bd=$(this).find('input[name="form[bd_field]"]').val();
                // send_ajax('','','','&form[filter_s]='+filter_s+'&form[filter_field]='+filter_field+'&form[bd_field]='+filter_field_bd);
        return false;
     }   
    }) 
   

    });

  };
})( jQuery );
// плагин поиск по первых буквах в играх по игрокамkeydown(function(e){
$('#search_field_players').focus();
$(document).on('keyup', '#search_field_players', function(e) {

    // if (e.keyCode == 13) { //если нажали Enter, то true

    var $this = $(this);
    speedsearch = $this.attr('speeds')
    val = $this.val();
    valLen = val.length
    if (valLen >= speedsearch) {
        //    console.log(val)
        post_string_ = '&fio_search=' + val;
        //  console.log(post_string_)
        // alert(post_string_)
        $content =   ajax_content('','',post_string_);
        $("#data_adminsite").html($content);
        //  console.log($content)
        //  send_ajax('', '', '', post_string_,1,0);


    }
    //}
});
// плагин поиск по первых буквах в играх по игрокамkeydown(function(e){

$(document).on('keyup', '#search_field_games', function(e) {

  //  if (e.keyCode == 13) { //если нажали Enter, то true

        var $this = $(this);
        speedsearch = $this.attr('speeds')
        val = $this.val();
        valLen = val.length
        if (valLen >= speedsearch) {
       //     console.log(val)
            href = document.location.hash.replace('#', '');
            aCurrentUrl = href.split('-');
            if (aCurrentUrl.length >= 2) {
                module = aCurrentUrl[0];
                action  = aCurrentUrl[1];
                post_string_ = aCurrentUrl[2];
     }

            post_string_ = post_string_+'&fio_search=' + val;
        //   console.log(post_string_)
            // alert(post_string_)
         //   send_ajax('', '', '', post_string_);
            $content =   ajax_content('','',post_string_);
            $("#data_adminsite").html($content);


        }
//    }
});

$(document).on('change', '#search_field_games_select', function() {
    var $this = $(this);
    var val = $this.val();
    var href = document.location.hash.replace('#', '');
    var aCurrentUrl = href.split('-');
    var post_string_ = '';
    if (aCurrentUrl.length >= 2) {
        post_string_ = aCurrentUrl[2];
    }

    if (val && val.length > 0) {
        post_string_ = post_string_ + '&fio_search=' + encodeURIComponent(val);
    }
    $content = ajax_content('', '', post_string_);
    $("#data_adminsite").html($content);
});


    var nameIdGL = '';
 //-------------------------------------------------------------------------------------------
// плагин поиск по первых буквах  и выбор элемента
$(function() {

    $(document).on('keyup', 'input[speedsearch]', function(){

      var  $this = $(this);
      //  console.log(item.val());
        $this.autocomplete({
                minLength:3,
                delay: 100, //ждем 1 сек если ничего неввели то отправим запрос
                source:  function(request,response) {
                    var val = $this.val();
                    t_id = $this.attr("speedsearch");
                    table = $this.attr("table");
                    where = $this.attr("where");
                    // alert(where)
                    $nameId = $this.attr("id");
                    post_string ='&nameField='+$this.attr("name2")+'&NewvalField='+$this.val()+'&where='+where+'&result_fields_dop='+$this.attr("result_fields_dop")+'&table='+table;
                    //  alert (post_string)
                    //console.log(post_string)
                    data_json =  ajax_content('searchFirstLetter','',post_string)
                    if (data_json!='NO') {
                        data_json = atob(data_json);
                      //  console.log(data_json)
                       data = JSON.parse(data_json);
                        response($.map( data, function( item ) {
                             return {
                                label: item.label,
                                value: item.label,
                                id: item.value,
                                jsonReturn: item.jsonReturn
                            }
                        }));

                    }

                },
                select : function (event, ui)
                {
                    jsonreturn = ui.item.jsonReturn;
                    // console.log('jsonreturn'+jsonreturn)

                    if (typeof jsonreturn!='undefined' && jsonreturn!=''){
                        jsonReturn_decode = atob(jsonreturn);
                        //  console.log('jsonreturn'+jsonreturn)
                    //         console.log('jsonReturn_decode'+jsonReturn_decode)
                        const obj_json = JSON.parse(jsonReturn_decode);
                     //    console.log('obj_json'+obj_json)
                        for (var key in obj_json) {
                            // этот код будет вызван для каждого свойства объекта
                            // ..и выведет имя свойства и его значение
//console.log( "Ключ: " + key + " значение: " + obj_json[key] );
// пройдемся по json и всем полям присвоим значение по умолчанию
                            if (key=='sex') {
                                if (obj_json[key]=='f') {
                                    $('#'+key+'1').prop('checked', true);
                                    $('#'+key+'0').prop('checked', false);
                                }
                            }
                            $('#'+key).val(obj_json[key])

                        }
                    }
                    name_hidden='p_'+$this.attr("id2")+'_id';
                  //  console.log(name_hidden)
                    $('#'+name_hidden).val((ui.item.id));


                }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li data-id='"+item.id+"' jsonReturn='"+item.jsonReturn+"'>")
                .append("<div>"+item.label+"</div>")
                .appendTo(ul);
        };


    });
});


//-------------------------------------------------------------------------------------------  
// плагин устанавливает курсор в конец input элемента
$.fn.setCursorPosition = function(pos) {
    this.each(function(index, elem) {
    if (elem.setSelectionRange) {
        elem.setSelectionRange(pos, pos);
    } else if (elem.createTextRange) {
        var range = elem.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
    });
    return this;
};
//----------------------------------------------------------------------------------------
// плагин изминение высоты таблицы
$(document).ready(function() {
    $(".resizable").resizable();
});
(function($) {

	var defaults = {
		color: 'red'
	};
	
	var f = {
	
		init: function(options) {
			var options = $.extend({}, defaults, options);
			
			var c = this;
            var qe = $("<div class='res'><div class='resizers'></div><div class='resizers'></div></div>");
            $(c).append(qe);
			$(".res").css({
				"height":		'7px',
				// "background":	options.color,
				"cursor":		's-resize'
			});
			
			return this.each(function() {
				var me = $(this);
			
				qe.bind('mousedown', function(e) {
					var h = me.height();
					var y = e.clientY;
					var moveHandler = function(e) {
						var s = Math.max(20, e.clientY + h - y);
						s1=s-30;
						me.height(s);
						$('#thetable').tableScroll({height:s1,width:"99%"});
						return false;
					};
					
					var upHandler = function(e) {
						$('html').unbind('mousemove', moveHandler).unbind('mouseup', upHandler);
					};
					
					$('html').bind('mousemove', moveHandler).bind('mouseup', upHandler);
				});
			});
		},
	};
	
	
	$.fn.resizable = function(method) {
		if(f[method]) {
			return f[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if(typeof method === 'object' || ! method) {
			return f.init.apply(this, arguments);
		} else {
			$.error("ћетод с именем " + method + " не существует");
		}
		
		
	};

}) (jQuery);
function select2_vibor(width)
{
 //   console.log('select2_vibor')
     href = document.location.hash.replace('#', '');
 //   console.log('href_reload='+href)


if (href) {

    last_href = href;
    aCurrentUrl = href.split('-');
    if (aCurrentUrl.length >= 2) {
        //alert('tyt')

        module = aCurrentUrl[0];
        action  = aCurrentUrl[1];
        post_string = aCurrentUrl[2];

    }
}

  //  inputValue = "ajax_method=1&module=" + module + "&action=" + action + "&return_content_bool="+return_content_bool +"&" + inputValue;
inputValue = "&ajax_method=2&module=" + module + "&action=ajaxspisplayers&" + post_string;
//console.log(inputValue)
//inputValue= post_string+inputValue;

    width = (typeof width == 'undefined' ? '100%' : width);
   $(".js-example-data-ajax").select2({
        ajax: {
            url: function (){
                return '';
            },
            dataType: 'json',
            delay: 2,
            data: function (params) {
            //    console.log(params)
                return {
                    q: params.term+inputValue, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search for a repository',
        minimumInputLength: 0,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

}
///блок для выбора столов множественный ********************************
let Select2MultiCheckBoxObj = [];
const id_selectElement = 'tableList';
const staticWordInID = 'state_';

function AddItemInSelect2MultiCheckBoxObj(id, IsChecked) {
    let index = Select2MultiCheckBoxObj.findIndex(x => x.id == id);
    if (index > -1) {
        Select2MultiCheckBoxObj[index].IsChecked = IsChecked;
    } else {
        Select2MultiCheckBoxObj.push({ id: id, IsChecked: IsChecked });
    }
}

function generateTables(count, preselectedTables) {
    const $select = $('#' + id_selectElement);
    $select.empty();

    if (!count || count <= 0) {
        $select.append(`<option disabled>Заповніть кількість столів</option>`);
        $select.trigger("change");
        return;
    }

    const allShouldBeSelected = preselectedTables.length === 0;
    const selectedValues = [];

    for (let i = 1; i <= count; i++) {
        const value = i.toString();
        const selected = allShouldBeSelected || preselectedTables.includes(value);
        $select.append(new Option(`Стіл ${i}`, value, selected, selected));
        AddItemInSelect2MultiCheckBoxObj(i, selected);
        if (selected) selectedValues.push(value);
    }

    // 💥 Важно: явно установить выбранные значения
    $select.val(selectedValues).trigger('change.select2');
}

function formatResult(state) {
    if (!state.id) return state.text;
    const isChecked = Select2MultiCheckBoxObj.find(x => x.id == state.id)?.IsChecked || false;
    return $(
        `<div class="checkbox">
        <input type="checkbox" id="${staticWordInID + state.id}" ${isChecked ? 'checked' : ''} />
        <label for="${staticWordInID + state.id}">${state.text}</label>
      </div>`
    );
}
function select2_vibor_tables(preselectedTables = []) {
    const count = parseInt($('#tables').val(), 10) || 0;
    const $select = $('#' + id_selectElement);

    // 1. Сначала генерируем опции без selected
    generateTables(count, preselectedTables);

    // 2. Устанавливаем selected значения вручную
    const stringValues = preselectedTables.map(x => x.toString());
    $select.val(stringValues);

    // 3. Инициализация select2 только после val()
    $select.select2({
        templateResult: formatResult,
        closeOnSelect: false,
        width: '100%'
    });
    // 🔁 Установим значения ещё раз после init
    $select.val(preselectedTables.length ? preselectedTables : [...Array(count).keys()].map(i => (i + 1).toString()));
    $select.trigger('change.select2');


    // 5. Перегенерация при изменении количества столов
    $('#tables').on('input', function () {
        const newCount = parseInt(this.value, 10) || 0;
        generateTables(newCount, preselectedTables);
     //   $select.val(stringValues).trigger('change.select2');
    });

    // 6. Обработка select/unselect
    $select.on("select2:select", function (e) {
        $(`#${staticWordInID + e.params.data.id}`).prop("checked", true);
        AddItemInSelect2MultiCheckBoxObj(e.params.data.id, true);
    });

    $select.on("select2:unselect", function (e) {
        $(`#${staticWordInID + e.params.data.id}`).prop("checked", false);
        AddItemInSelect2MultiCheckBoxObj(e.params.data.id, false);
    });

    // 7. Обработка кликов вручную
    $(document).on("mousedown", ".select2-results__option", function (e) {
        e.preventDefault();
        const checkbox = $(this).find('input[type="checkbox"]');
        const optionId = this.id.split('-').pop();
        const $option = $select.find(`option[value="${optionId}"]`);
        const isSelected = $option.prop('selected');

        $option.prop('selected', !isSelected);
        checkbox.prop('checked', !isSelected);
        AddItemInSelect2MultiCheckBoxObj(optionId, !isSelected);

        $select.trigger('change.select2');
    });
}

/// конец блок для выбора столов множественный ********************************


function chosen_vibor_filter_turnir(width='100%') {
   // Проверяем наличие league_id в hash URL - если есть реальное значение, скрываем фильтры города и клуба
   var currentHash = window.location.hash || '';
   // Проверяем, что после league_id= есть непустое значение (до & или до конца строки)
   var leagueIdMatch = currentHash.match(/league_id=([^&]*)/);
   var hasLeagueId = leagueIdMatch && leagueIdMatch[1] && leagueIdMatch[1].trim() !== '';
   if (hasLeagueId) {
       // Есть league_id - скрываем фильтры города и клуба, но продолжаем инициализацию etap-chosen-select
       $('#city-chosen-select').hide();
       $('#club-chosen-select').hide();
       // Скрываем только контейнеры для city и club, но не для etap
       $('#city-chosen-select').closest('.chosen-container').hide();
       $('#club-chosen-select').closest('.chosen-container').hide();
       $('#slugeb_info').html('');
       // НЕ делаем return - продолжаем выполнение для инициализации etap-chosen-select
   }
   
   // Проверяем наличие всех элементов перед инициализацией
    var citySelect = $('#city-chosen-select');
    var prostidcity = $('#Prostidcity');
    var prostidclub = $('#Prostidclub');
    var clubSelect = $('#club-chosen-select');
    var etapSelect = $('#etap-chosen-select');
    var searchSelect = $('#search_field_games_select');
   
   if (citySelect.length > 0) {
       $('#city-chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть місто.'
    });
       $("#city-chosen-select").chosen().change(function(e){
            ElemVar= $(this).val();
            post_string_ ='&city='+ElemVar;
            send_ajax('','','',post_string_);
        });
   }
   if (prostidcity.length > 0) {
       $('#Prostidcity').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть місто.'
    });
   }
   if (prostidclub.length > 0) {
       $('#Prostidclub').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть клуб.'
    });
   }

    // фикс: переключаем выбранность вручную при клике по опции
    $(document).on('mouseup', '.select2-results__option', function (e) {
        const value = $(this).data('select2-id');
        const select = $('#tableList');
        const current = select.val() || [];

        if (current.includes(value)) {
            select.find(`option[value="${value}"]`).prop('selected', false);
        } else {
            select.find(`option[value="${value}"]`).prop('selected', true);
        }

        select.trigger('change.select2');
        e.stopPropagation(); // предотвратим закрытие выпадающего списка
    });

   if (clubSelect.length > 0) {
       $('#club-chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть клуб'
    });
       $("#club-chosen-select").chosen().change(function(e){
            ElemVar= $(this).val();
            post_string_ ='&club='+ElemVar;
            send_ajax('','','',post_string_);
        });
   }

    $('#month_nomination').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть місяць'
    });
    $("#month_nomination").chosen().change(function(e){
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&club='+ElemVar;
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });

    $('#year_nomination').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть рік'
    });
    $("#year_nomination").chosen().change(function(e){
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&club='+ElemVar;
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });


   if (etapSelect.length > 0) {
       $('#etap-chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть етап'
    });
       $("#etap-chosen-select").chosen().change(function(e){
            ElemVar= $(this).val();
            //   console.log(ElemVar)
            post_string_ ='&etap_id='+ElemVar;
            // alert(post_string_)
            send_ajax('','','',post_string_);

        });
   }
   if (searchSelect.length > 0) {
       $('#search_field_games_select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: $('#search_field_games_select').attr('data-placeholder')
    });
   }
}
function formatRepoSelection (repo) {
    return repo.name || repo.text;
}
    function chosen_vibor(width,txt_def='')

{
  //  console.log('chosen_vibor')
    txt_def = txt_def == '' ? 'Виберіть гравця' : txt_def;
    width = (typeof width == 'undefined' ? '100%' : width);

    $('.chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: txt_def,
        template: function (text, value, templateData) {
            console.log('templa');
            return [
                'test1'  + text + "</i> value: " + value
            ].join("");
        }

    });
    $(".chosen-select").chosen().change(function(e){

       // console.log($(this));
      //   parent_id = $(this).parent().attr("id");
      //  console.log('parent_id='+parent_id);
        elem_id= $(this).attr("id")
      //  console.log(elem_id)
       // const re = /Playerid\s(?[0-9]).+?(jumps)/dgi;
    //    const reg =/Playerid_(\d+)_(\d+)_(\d+)/gi;
    if (elem_id=='Prostidtype_etap') {
    After_element_vibor($(this).val());
    typeElem= $('#Prostidistochnik_posev option:selected').attr('type_etap')
    typeElemVar= $(this).val()
    if (typeElem==1 && typeElemVar==1)
    {
        $("#trId_is_perenos").show(1000);

    }  else
    {
        $("#trId_is_perenos").hide(1000);
    }
}

        if (elem_id=='Prostidname_etap') {
              //  ElemVar= $(this).val()
            const ElemVar = $(':checked', this).text();
               // console.log(ElemVar)
                //   console.log(ElemVar)
                $('#name_etap').val(ElemVar);
      }
        if (elem_id=='ComparePlayer') {
            idPlayer= $(this).val()
            post_string_ ='&compare_id='+idPlayer;
            send_ajax('','','',post_string_);

        }


if (elem_id=='Prostidistochnik_posev') {
  //  console.log('Prostidistochnik_posev')
    typeElemVar= $('#Prostidtype_etap option:selected').val()
    typeElem= $('#Prostidistochnik_posev option:selected').attr('type_etap')
    if (typeElem==1 && typeElemVar==1)
    {
        $("#trId_is_perenos").show(1000);

    }  else
    {
        $("#trId_is_perenos").hide(1000);
    }
}
        $val=$(this).val();
        var newPlayer = parseInt($val, 10);
        if (isNaN(newPlayer) || newPlayer < 0) {
            newPlayer = 0;
        }
        const   reg1 =/PlayeridGrp/g;
        mas_t_id_grp =elem_id.match(reg1)
      //  t_id=elem_id.replace(/Playerid/g, "" );
      //  console.log('mas_t_id='+ mas_t_id)
        if (mas_t_id_grp!=null)
        {
            var row_id = parseInt($(this).attr('data-row-id'), 10);
            oldPlayer = parseInt($(this).attr('data-old-player'), 10);
            grp = parseInt($(this).attr('data-grp'), 10);
            grpnum = parseInt($(this).attr('data-grpnum'), 10);
            if (isNaN(oldPlayer) || isNaN(grp) || isNaN(grpnum)) {
                var partsGrp = elem_id.split('_');
                oldPlayer = parseInt(partsGrp[1], 10);
                grp = parseInt(partsGrp[2], 10);
                grpnum = parseInt(partsGrp[3], 10);
            }
            if (!isNaN(oldPlayer) && !isNaN(grp) && !isNaN(grpnum)) {
                post_string_ ='&grp='+grp+'&grpnum='+grpnum+'&oldPlayer=' +oldPlayer+'&newPlayer='+newPlayer;
                if (!isNaN(row_id) && row_id > 0) {
                    post_string_ += '&row_id='+row_id;
                }
            } else {
                return;
            }
            // alert(post_string_)
             send_ajax('','','',post_string_);
            //console.log('post_string_=' + post_string_)
        }
        const   reg2 =/PlayeridSetka_/g;
        mas_t_id_setka =elem_id.match(reg2)
        //  t_id=elem_id.replace(/Playerid/g, "" );
        //  console.log('mas_t_id='+ mas_t_id)
        if (mas_t_id_setka!=null)
        {
            var row_id = parseInt($(this).attr('data-row-id'), 10);
            oldPlayer = parseInt($(this).attr('data-old-player'), 10);
            mesto = parseInt($(this).attr('data-mesto'), 10);
            if (isNaN(oldPlayer) || isNaN(mesto)) {
                var partsSetka = elem_id.split('_');
                oldPlayer = parseInt(partsSetka[1], 10);
                mesto = parseInt(partsSetka[2], 10);
            }
            if (!isNaN(oldPlayer) && !isNaN(mesto)) {
                post_string_ ='&mesto='+mesto+'&oldPlayer=' +oldPlayer+'&newPlayer='+newPlayer;
                if (!isNaN(row_id) && row_id > 0) {
                    post_string_ += '&row_id='+row_id;
                }
            } else {
                return;
            }
            // alert(post_string_)
                 send_ajax('','','',post_string_);
            //console.log('post_string_=' + post_string_)
        }

       // console.log('$val='+$val)
    //    $id=$(this).id();
       // grp = $("#opt_"+$val).attr('grp')

    //    console.log($id);


    });
}
function fileinput_init(url='', name_field='',name_img='',id=0)
{
if (url)
    $("#"+name_field).fileinput({
        showUpload: false,
        showRemove: true,
        initialPreviewShowDelete: true,
        allowedFileExtensions: ["jpg", "png", "gif"],
        initialPreviewAsData: true,
        initialPreview: [url], //наш файл
        initialPreviewConfig: [
            // {downloadUrl: url},
            {caption: name_img,  url: "?isdelete=1", key: id},

        ],
    });
else
    $("#"+name_field).fileinput({
        showUpload: false,
        showRemove: true,
        initialPreviewShowDelete: true,
        allowedFileExtensions: ["jpg", "png", "gif"],

    });
  //  console.log('ssaas='+name_field)
}
function vubor_mes_year(width=200)
{
    $('#month_nomination').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть місяць'
    });
    $("#month_nomination").chosen().change(function(e){
        $year=$('#year_nomination').val();
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&month='+$(this).val()+'&year='+$year;
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });

    $('#year_nomination').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть рік'
    });
    $("#year_nomination").chosen().change(function(e){
        $year=$('#year_nomination').val();
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&year_v='+$(this).val();
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });
    $('#city-chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть місто.'
    });
    $("#city-chosen-select").chosen().change(function(e){
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&city='+ElemVar;
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });


    $('#club-chosen-select').chosen({
        width: width,
        no_results_text: 'Співпадінь не знайдено',
        placeholder_text_single: 'Виберіть клуб'
    });
    $("#club-chosen-select").chosen().change(function(e){
        ElemVar= $(this).val();
        //   console.log(ElemVar)
        post_string_ ='&club='+ElemVar;
        // alert(post_string_)
        send_ajax('','','',post_string_);

    });



}
function player_graphik()
{
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    data_json = jsonData;
    function drawChart() {
     //   console.log(jsonData)

        var data = google.visualization.arrayToDataTable(jsonData);

        var options = {
            title: '', //Приріст рейтингу гравця
            titleTextStyle: {
                           fontSize: 8, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
            },
            curveType: 'function',
            pointSize: 5,
             fontName: 'Lato',
            chartArea: {width: '85%', height: '80%'},
            legend: {position: 'none'},
            series: {0: {color: '#FF6F6F'}}, // – кольору стовпців
            titlePosition: 'out', axisTitlesPosition: 'out',
            hAxis: {textPosition: 'in'}, vAxis: {textPosition: 'out'}
          //  chartArea:{left:-20,top:10,width:'90%',height:'80%'},
          //  legend: { position: 'bottom' }

        };

        var chart = new google.visualization.LineChart(document.getElementById('player_chart'));

        chart.draw(data, options);
    }

}
function buregrMenu(){

    $('#NavBarBurger').on('click', function () {

        aria_expanded = $(this).attr("aria-expanded");
        if (aria_expanded=='true') {
            $('#NavBarBurger span').attr("class", "burger_close");
        } else
        {
            $('#NavBarBurger span').attr("class", "burger");

        }
      //  $(this).toggleClass("active");
    });
}
function show_zag_left(href='')
{
  //  console.log('tyt')
    $('#zag_left').removeClass('hide');
    $('#zag_left').addClass('ajax_send');
    $('#zag_left').attr('href', href);


}
function show_zag_center()
{
    $('#zagl_module').removeClass('hide_bigscreen');
}function show_zag_left_big(href='')
{
    $('#zagl_left_main').removeClass('hide_bigscreen');
    $('#zag_left').removeClass('hide');
    $('#zag_left').addClass('ajax_send');
    $('#zag_left').attr('href', href);
    $('#mobx1').removeClass('col');
    $('#mobx1').addClass('col-2');
}
