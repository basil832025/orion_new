$(document).ready( function(){

});
var TinyConfig_plugins = [] ;
var TinyConfig_button = [] ;

TinyConfig_plugins[1] = 'pagebreak'; // вставляет разделитель страницы
TinyConfig_plugins[2] = 'style';   // стили  блока текст очень мнго фичей
TinyConfig_plugins[3] = 'table';  // таблица
TinyConfig_plugins[4] = 'advimage'; // дополнительно по изображению в окне изображения простого
TinyConfig_plugins[5] = 'advhr';  // вставляет горизонтальную линию
TinyConfig_plugins[6] = 'advlink'; // дополнительно по ссылке в окне создания ссылки
TinyConfig_plugins[7] = 'emotions'; // вставляет смайлики
TinyConfig_plugins[8] = 'iespell';
TinyConfig_plugins[9] = 'inlinepopups';
TinyConfig_plugins[10] = 'insertdatetime'; // вставка времени
TinyConfig_plugins[11] = 'preview';  // Предвариельный просмотр
TinyConfig_plugins[12] = 'media'; // вставляет flash win media ...
TinyConfig_plugins[13] = 'searchreplace'; // поиск и замена
TinyConfig_plugins[14] = 'print';   // печать
TinyConfig_plugins[15] = 'contextmenu'; // по правой кнопке мыше контексное меню
TinyConfig_plugins[16] = 'paste'; // вставить только текст и только word
TinyConfig_plugins[17] = 'safari';
TinyConfig_plugins[18] = 'directionality'; // кнопки навправления справа налево и слево направо
TinyConfig_plugins[19] = 'fullscreen'; // полноекранный режим
TinyConfig_plugins[20] = 'noneditable'; // пока неясен
TinyConfig_plugins[21] = 'visualchars'; // символы визуального конроля вкл/выкл
TinyConfig_plugins[22] = 'nonbreaking'; // вставить не разрывный пробел
TinyConfig_plugins[23] = 'xhtmlxtras'; // редактирование атрибуов текста 6 кнопок
TinyConfig_plugins[24] = 'layer'; // доролнительнԐ٠слой новая div возможно с абсолютным позиционированием
TinyConfig_plugins[25] = 'template'; // вставка шаблонов
TinyConfig_plugins[26] = 'images'; // плагин вставки картинок с загрузкой и созданием папок

TinyConfig_button[1] = 'bold'; //жирный текст
TinyConfig_button[2] = 'italic'; // курсив
TinyConfig_button[3] = 'underline'; // подчеркивание
TinyConfig_button[4] = 'strikethrough'; // зачеркивание
TinyConfig_button[5] = 'justifyleft'; // выравнивание влево текста
TinyConfig_button[6] = 'justifycenter'; // по центру
TinyConfig_button[7] = 'justifyright'; // вправо
TinyConfig_button[8] = 'justifyfull'; // по ширине
TinyConfig_button[9] = 'styleselect';// какие=то стили не понятные                             ********
TinyConfig_button[10] = 'formatselect'; // рпзыные форматы теккста и заглавие и абзаци и так далее
TinyConfig_button[11] = 'fontselect'; // выбор шрифта названия
TinyConfig_button[12] = 'fontsizeselect'; // размер шрифта
TinyConfig_button[13] = 'cut'; // вырезать текст выделеный (не всеми браузерами поддержуется) *******
TinyConfig_button[14] = 'copy'; // копировать ie                                              *******
TinyConfig_button[15] = 'paste'; // вставить ie                                               *******
TinyConfig_button[16] = 'bullist';// маркированый список
TinyConfig_button[17] = 'numlist';// нумерованый список
TinyConfig_button[18] = 'outdent';// отступ влево
TinyConfig_button[19] = 'indent';// отступ вправо
TinyConfig_button[20] = 'blockquote';// цитата всего абзаца
TinyConfig_button[21] = 'undo';// Отмена
TinyConfig_button[22] = 'redo';// Повторить
TinyConfig_button[23] = 'link';// Ссылка
TinyConfig_button[24] = 'unlink';// удалить ссылку
TinyConfig_button[25] = 'anchor';// якорь
TinyConfig_button[26] = 'image';// вставка изображения простого написанием ссылки
TinyConfig_button[27] = 'cleanup';// очисщает теги каие-то плохие
TinyConfig_button[28] = 'code';// просмотр HTML кода
TinyConfig_button[29] = 'forecolor';// выбор цвета шрифта
TinyConfig_button[30] = 'backcolor';// выбор фона шрифта
TinyConfig_button[31] = 'hr';// горизонтальная линия
TinyConfig_button[32] = 'removeformat';// убирает форматирование
TinyConfig_button[33] = 'visualaid';// вкл/выкл нивидимые элементы направляющие,(пример: якоря, таблица без границ)
TinyConfig_button[34] = 'sub';// нижний индекс
TinyConfig_button[35] = 'sup';// верхний индекс
TinyConfig_button[36] = 'charmap';// вставка и выбор спец символов из таблицы
TinyConfig_button[37] = 'emotions';// смайлики
TinyConfig_button[38] = 'iespell';// пока не известно

TinyConfig_button[39] = 'pagebreak';// вставляет символ разделитель страници <!-- pagebreak -->
TinyConfig_button[40] = 'styleprops';// стил оформления текста, много фичей и цвета и шрифт, позиции для профи
TinyConfig_button[41] = 'tablecontrols';// серия кнопок для работы с таблицей
TinyConfig_button[42] = 'advhr';// линия
TinyConfig_button[43] = 'insertdate';// Вставляет текущую дату (плагин)
TinyConfig_button[44] = 'inserttime';// Вставляет текущие время
TinyConfig_button[45] = 'preview';// Предпросмотр
TinyConfig_button[46] = 'media';// вставка флеша и медии
TinyConfig_button[47] = 'search'; // поиск в тексте (плагин)
TinyConfig_button[48] = 'replace';// замена в тексте (плагин)
TinyConfig_button[49] = 'print';// пока не известно
TinyConfig_button[50] = 'pastetext'; // вставляет простой текст в окне, читит теги (плагин)   ////
TinyConfig_button[51] = 'images';// цитата всего абзаца

TinyConfig_button[52] = 'pasteword';// вставляет word текст в окне, читит теги (плагин)        /////
TinyConfig_button[53] = 'ltr';// направление слева на право
TinyConfig_button[54] = 'rtl';// направления справа налево
TinyConfig_button[55] = 'fullscreen';// полноэкранный редактор для удобства ввода
TinyConfig_button[56] = 'visualchars';// символы визуального контроля (пока н нужен)
TinyConfig_button[57] = 'nonbreaking';// вставляет не разрывный пробел html &nbsp
TinyConfig_button[58] = 'cite';// вставляет цитату
TinyConfig_button[59] = 'abbr';// вставляет абривиатуру
TinyConfig_button[60] = 'acronym';// всавляет акроним
TinyConfig_button[61] = 'del';// вставляет удаленный текст, красныйзачеркнутый
TinyConfig_button[62] = 'ins';// вставляе добавленый текст
TinyConfig_button[63] = 'attribs';// редактирование атрибутов для профи в принципе не нужен
TinyConfig_button[64] = 'insertlayer';// вставка нового слоя это дивка по умолчанию с абсолютным позиционированием для профи нужно
TinyConfig_button[65] = 'moveforward';// слой на передний план
TinyConfig_button[66] = 'movebackward';// слой на задний план
TinyConfig_button[67] = 'absolute';// вкл/выкл абсолютное позицианирование
TinyConfig_button[68] = 'template';// выбор и вставка готового шаблона, пока не тестил




<!-- Load TinyMCE -->
function _redaktor( plugins_java, panel1, panel2,panel3,theme_, id){
if(typeof id=="undefined"){id = 'content_'}
if(typeof theme_=="undefined"){theme_ = "advanced"}
if(typeof plugins_java=="undefined"){plugins_java = ""}
if(typeof panel1=="undefined"){panel1 = ""}
if(typeof panel2=="undefined"){panel2 = ""}
if(typeof panel3=="undefined"){panel3 = ""}

    panel1_='';
    panel2_='';
    panel3_='';
    plugins_='';
    for (i=0;i<panel1.length;i++){
        panel1_ += TinyConfig_button[panel1[i]]+',';
    }
    for (i=0;i<panel2.length;i++){
        panel2_ += TinyConfig_button[panel2[i]]+',';
    }
    for (i=0;i<panel3.length;i++){
        panel3_ += TinyConfig_button[panel3[i]]+',';
    }
    for (i=0;i<plugins_java.length;i++){
        plugins_ += TinyConfig_plugins[plugins_java[i]]+',';
    }
tinyMCE.init({
        // General options
        mode : "exact",
        elements : id,
        theme : theme_,
        skin : "o2k7",

        language:"ru",
        plugins : plugins_,

        theme_advanced_buttons1 :  panel1_,
        theme_advanced_buttons2 : panel2_,

        theme_advanced_buttons3 : panel3_,
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
         file_browser_callback : "tinyBrowser", // подключаем файловый редактор
    });


}
<!-- /TinyMCE -->
