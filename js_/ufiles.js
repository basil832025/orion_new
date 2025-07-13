var $ = jQuery.noConflict();

$(document).ready(function() {
	// В dataTransfer помещаются изображения которые перетащили в область div
	jQuery.event.props.push('dataTransfer');
	
	// Максимальное количество загружаемых изображений за одни раз
	var maxFiles = 6;
	
	// Оповещение по умолчанию
	var errMessage = 0;
	
	// Кнопка выбора файлов
	var defaultUploadBtn = $('#uploadbtn');
	
	// Массив для всех изображений
	var dataArray = [];
	
	// Область информер о загруженных изображениях - скрыта
	$('#uploaded-files').hide();
	
	// Метод при падении файла в зону загрузки
	$('#drop-files').on('drop', function(e) {	
		// Передаем в files все полученные изображения
		var files = e.dataTransfer.files;
           maxFiles =  $('#cnt_files__').val();
           c =  $('.image').length;
       	// Проверяем на максимальное количество файлов
		if (files.length+c <= maxFiles) {
			// Передаем массив с файлами в функцию загрузки на предпросмотр
			loadInView(files);
		} else {
			alert('Вы не можете загружать больше '+maxFiles+' изображений!'); 
			files.length = 0; return;
		}
	});
	
	// При нажатии на кнопку выбора файлов
	defaultUploadBtn.on('change', function() {
   		// Заполняем массив выбранными изображениями
   		var files = $(this)[0].files;
        maxFiles =  $('#cnt_files__').val();
        c =  $('.image').length;
      	// Проверяем на максимальное количество файлов
		if (files.length+c <= maxFiles) {
			// Передаем массив с файлами в функцию загрузки на предпросмотр
			loadInView(files);
			// Очищаем инпут файл путем сброса формы
            $('#frm').each(function(){
	        	    this.reset();
			});
		} else {
			alert('Вы не можете загружать больше '+maxFiles+' изображений!'); 
			files.length = 0;
		}
	});
	
	// Функция загрузки изображений на предросмотр
	function loadInView(files) {
		// Показываем обасть предпросмотра
		$('#uploaded-holder').show();
          maxFiles =  $('#cnt_files__').val();
           if (maxFiles>1) { 
            $('#dropped-files').css({'width' : '700px','float' : 'none'})
           };
           module_id__ =  $('#module_id__').val();
           id_elem__ =  $('#id_elem__').val();
           id__ =  $('#id__').val();
           max_width__ =  $('#max_width__').val();
           max_height__ =  $('#max_height__').val();
           type_view___ =  $('#type_view___').val();
           name_field__ =  $('#name_field_').val();
		// Для каждого файла
		$.each(files, function(index, file) {
						
			// Несколько оповещений при попытке загрузить не изображение
		/*	if (!files[index].type.match('image.*')) {
				
				if(errMessage == 0) {
					$('#drop-files p').html('Эй! только изображения!');
					++errMessage
				}
				else if(errMessage == 1) {
					$('#drop-files p').html('Стоп! Загружаются только изображения!');
					++errMessage
				}
				else if(errMessage == 2) {
					$('#drop-files p').html("Не умеешь читать? Только изображения!");
					++errMessage
				}
				else if(errMessage == 3) {
					$('#drop-files p').html("Хорошо! Продолжай в том же духе");
					errMessage = 0;
				}
				return false;
			}
			*/
         
			// Проверяем количество загружаемых элементов
			if((dataArray.length+files.length) <= maxFiles) {
				// показываем область с кнопками
				$('#upload-button').css({'display' : 'block'});
			} 
			else { alert('Вы не можете загружать больше '+maxFiles+' изображений!'); return; }
			
  		// Создаем новый экземпляра FileReader
			var fileReader = new FileReader();
				// Инициируем функцию FileReader
				fileReader.onload = (function(file) {
					
					return function(e) {
					 //  alert(file.size)
					  //alert(file.type )
						// Помещаем URI изображения в массив
						dataArray.push({name : file.name, value : this.result,module_:module_id__,id_elem:id_elem__,id:id__,max_width:max_width__,max_height:max_height__,type_view_:type_view___,size:file.size,type_file_:file.type,name_field_:name_field__});
                        
						addImage((dataArray.length-1));
					}; 
						
				})(files[index]);
			// Производим чтение картинки по URI
			fileReader.readAsDataURL(file);
		});
		return false;
	}
		
	// Процедура добавления эскизов на страницу
	function addImage(ind) {
		// Если индекс отрицательный значит выводим весь массив изображений
		if (ind < 0 ) { 
		start = 0; end = dataArray.length; 
		} else {
		// иначе только определенное изображение 
		start = ind; end = ind+1; } 
		// Оповещения о загруженных файлах
		if(dataArray.length == 0) {
			// Если пустой массив скрываем кнопки и всю область
			$('#upload-button').hide();
			$('#uploaded-holder').hide();
		} else if (dataArray.length == 1) {
			$('#upload-button span').html("Был выбран 1 файл");
		} else {
			$('#upload-button span').html(dataArray.length+" файлов были выбраны");
		}
        
            maxFiles =  $('#cnt_files__').val();
           // alert(ind+'=ind '+end+'=end '+maxFiles+'=maxFiles')
          if (ind>= maxFiles) 
        //скрываем кнопку обзор
        $('#uploadbtn').hide();
           
           
      	// Цикл для каждого элемента массива
		for (i = start; i < end; i++) {
		  size_file=dataArray[i].value;
         // alert(dataArray[i].value)
         // alert(size_file.length)
			// размещаем загруженные изображения
			if($('#dropped-files > .image').length <= maxFiles) { 
			 name_f = dataArray[i].name;
			   var fileArr = name_f.split('.'); 
			 name_dop_dile = file_type(dataArray[i].type_file_,fileArr[1]);
             
			 if (name_dop_dile )
                $('#dropped-files').append('<div id="img-'+i+'" class="image image_prew" style="background: url(../img/files_type/'+name_dop_dile+'.png); background-size: cover;"><a href="#"  class="drop-button_up">Предосмотр</a></div> <a href="#" id="drop-'+i+'" class="drop-button">Удалить файл</a></div>');
		     else
				$('#dropped-files').append('<div id="img-'+i+'" class="image image_prew" style="background: url('+dataArray[i].value+'); background-size: cover;"><a href="#"  class="drop-button_up">Предосмотр</a> <a href="#" id="drop-'+i+'" class="drop-button">Удалить изображение</a></div>'); 
			}
		}
		return false;
	}
	
	// Функция удаления всех изображений
	function restartFiles() {
	if(typeof param=="undefined"){param = 0}
		// Установим бар загрузки в значение по умолчанию
		$('#loading-bar .loading-color').css({'width' : '0%'});
		$('#loading').css({'display' : 'none'});
		$('#loading-content').html(' ');
		
		// Удаляем все изображения на странице и скрываем кнопки
		$('#upload-button').hide();
		$('#dropped-files > .image').remove();
		$('#uploaded-holder').hide();
        if (param) 
	    $("#drop-files").hide();
        else   
	    $('#uploadbtn').show();   
		// Очищаем массив
		dataArray.length = 0;
		
		return false;
	}
	// Функция удаления всех изображений
	function restartFiles_nodrop() {
	if(typeof param=="undefined"){param = 0}
		// Установим бар загрузки в значение по умолчанию
		$('#loading-bar .loading-color').css({'width' : '0%'});
		$('#loading').css({'display' : 'none'});
		$('#loading-content').html(' ');
		
		// Удаляем все изображения на странице и скрываем кнопки
		$('#upload-button').hide();
	//	$('#dropped-files > .image').remove();
	//	$('#uploaded-holder').hide();
        if (param) 
	   ;// $("#drop-files").hide();
        else   
	    $('#uploadbtn').show();   
		// Очищаем массив
		dataArray.length = 0;
		
		return false;
	}	
	// Удаление только выбранного изображения 
	$("#dropped-files").on("click","a[id^='drop']", function() {
		// получаем название id
 		var elid = $(this).attr('id');
 		var elem_id = $(this).attr('elem_id');
   
        	var temp = new Array();
		// делим строку id на 2 части
		temp = elid.split('-');
		// получаем значение после тире тоесть индекс изображения в массиве
		dataArray.splice(temp[1],1);
      	// Удаляем старые эскизы
       	$('#img-'+temp[1]).remove();
     	// Обновляем эскизи в соответсвии с обновленным массивом
		//addImage(-1);	
        // отображаем кнопку обзор когда удадлили файл нужно делать проверку кога мультизагрузка
        $('#uploadbtn').show(); 
        $("#drop-files").show();
     
		// создаем массив для разделенных строк
        if(typeof elem_id=="undefined"){
	   }else{
            var dropArray = [];
             module_id__ =  $('#module_id__').val();
           id_elem__ =  $('#id_elem__').val();
           id__ =  $('#id__').val();
           max_width__ =  $('#max_width__').val();
           max_height__ =  $('#max_height__').val();
           type_view___ =  $('#type_view___').val();  
           name_field__ =  $('#name_field_').val();
         //  alert(id_elem__+'   '+id__); 
           dropArray.push({delete:1,module_:module_id__,id_elem:id_elem__,id:elem_id,max_width:max_width__,max_height:max_height__,type_view_:type_view___,name_field_:name_field__})
          $.post('func/ufiles.php', dropArray[0], function() {
		      
          })
        }	
	});
	
	// Удалить все изображения кнопка 
	$('#upload-button .delete').click(restartFiles);
	
	// Загрузка изображений на сервер
	$('#upload-button .upload').click(function() {
		//alert('tyt')
        //$('#dropped-files > .image').remove();
		// Показываем прогресс бар
	//	$("#drop-files").hide();
		$("#loading").show();
		// переменные для работы прогресс бара
		var totalPercent = 100 / dataArray.length;
		var x = 0;
		$('#dropped-files > .image_prew').remove();
		$('#loading-content').html('Загружен '+dataArray[0].name);
        //	 restartFiles(1); // удаляем предосмотр   
		// Для каждого файла  
		$.each(dataArray, function(index, file) {
    			// загружаем страницу и передаем значения, используя HTTP POST запрос 
			$.post('ufiles.html', dataArray[index], function(data) {
 
		   		var fileName = dataArray[index].name;
            		++x;        
			//	$('#'+json.name_field+'_bas', top.document).val(json.id);
				// Изменение бара загрузки
				$('#loading-bar .loading-color').css({'width' : totalPercent*(x)+'%'});
				// Если загрузка закончилась
                		// data формируется в upload.php
				var dataSplit = data.split(':');
                var fileArr = fileName.split('.'); 
        		 name_dop_dile = file_type(dataArray[index].type_file_,fileArr[1]);
     
				if(totalPercent*(x) == 100) {
					// Загрузка завершена
					$('#loading-content').html('Загрузка завершена!');
						
	                   	setTimeout(restartFiles_nodrop, 200);
				
				// если еще продолжается загрузка	
				} else if(totalPercent*(x) < 100) {
					// Какой файл   загружается
					$('#loading-content').html('Загружается '+fileName);
				}
				
				// Формируем в виде списка все загруженные изображения
               // alert(dataSplit[0])
                //alert(dataSplit[1])
               // alert(name_dop_dile)
               // Вызываем функцию удаления всех изображений после задержки 1 секунда
				
		       if(dataSplit[1] == 'uploaded') {
			 if (name_dop_dile )
                $('#dropped-files').append('<div id="img-'+index+'" class="image " style="background: url(../img/files_type/'+name_dop_dile+'.png); background-size: cover;"></div> <a href="#" id="drop-'+index+'" elem_id="'+dataSplit[2]+'" class="drop-button">Удалить файл</a></div>');
		     else
				$('#dropped-files').append('<div id="img-'+index+'" class="image"  style="background: url('+dataSplit[0]+'); background-size: cover;"> <a href="#" id="drop-'+index+'" elem_id="'+dataSplit[2]+'" class="drop-button">Удалить изображение</a></div>');
                
                $('#uploaded-files').append('<li>'+fileName+' загружен успешно</li>'); 
	           }else{
	              $('#uploaded-files').append('<li> ПРОИЗОШЛА ОШИБКА ЗАГРУЗКИ ФАЙЛА!</li>');  
	           }
               	
		
        	});
		});
        
		// Показываем список загруженных файлов
	//	$('#uploaded-files').show();
   // dataArray.length = 0;
		return false;
	});
	
	// Простые стили для области перетаскивания
	$('#drop-files').on('dragenter', function() {
		$(this).css({'box-shadow' : 'inset 0px 0px 20px rgba(0, 0, 0, 0.1)', 'border' : '4px dashed #bb2b2b'});
		return false;
	});
	
	$('#drop-files').on('drop', function() {
		$(this).css({'box-shadow' : 'none', 'border' : '4px dashed rgba(0,0,0,0.2)'});
		return false;
	});
});
// функция проверет по розширению друниг файлы не изображения и возвращет это же розширение или дефаулт картинку 100 розширений есть
function file_type(type_file,rozsh){
     //если это картинка то возвращаем false
        isImg = type_file.indexOf('image');
    if (isImg>-1) {
        return false;
    }else{
       obj_type = {AC3:1, ACE:1, ADE:1, ADP:1, AI:1, AIFF:1, AU:1, AVI:1, BAT:1, BIN:1, BMP:1, BUP:1, CAB:1, CAT:1, CHM:1, CSS:1, CUE:1, DAT:1, DCR:1, DER:1, DIC:1, DIVX:1, DIZ:1, DLL:1, DOC:1, DOCX:1, DOS:1, DVD:1, DWG:1, DWT:1, Default:1, EMF:1, EXC:1, FON:1, GIF:1, HLP:1, HTML:1, IFO:1, INF:1, INI:1, INS:1, IP:1, ISO:1, ISP:1, JAVA:1, JFIF:1, JPEG:1, JPG:1, LOG:1, M4A:1, MID:1, MMF:1, MMM:1, MOV:1, MOVIE:1, MP2:1, MP2V:1, MP3:1, MP4:1, MPE:1, MPEG:1, MPG:1, MPV2:1, NFO:1, PDD:1, PDF:1, PHP:1, PNG:1, PPT:1, PPTX:1, PSD:1, RAR:1, REG:1, RTF:1, SCP:1, THEME:1, TIF:1, TIFF:1, TLB:1, TTF:1, TXT:1, UIS:1, URL:1, VBS:1, VCR:1, VOB:1, WAV:1, WBA:1, WMA:1, WMV:1, WPL:1, WRI:1, WTX:1, XLS:1, XLSX:1, XML:1, XSL:1, ZAP:1, ZIP:1}
       if (obj_type[rozsh.toUpperCase]==1)  return rozsh;
       else return 'Default';     
    }
}
function pustushka(){
    
}
