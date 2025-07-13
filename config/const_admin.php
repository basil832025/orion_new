<?php
/*============================!!!!!!ВНИМАНИЕ!!!!!!=======================================
    сдесь собраны все константы системы, которыми вы можете управлять, но изменяйте их осторожно некоторые могут
    кординально изменить систему или привести ее к краху. Хорошо подумайте перед тем, как менять константу.
    Внимательно  прочитайте коментарий к констане
===========================!!!!!!ВНИМАНИЕ!!!!!!=========================================*/

   // настройки кнопок для редактора
   $TinyConfig_button = array('bold'=>array(1, 0), 'italic'=>array(2, 0), 'underline'=>array(3, 0), 'strikethrough'=>array(4, 0), 'justifyleft'=>array(5, 0), 'justifycenter'=>array(6, 0), 'justifyright'=>array(7, 0), 'justifyfull'=>array(8, 0), 'styleselect'=>array(9, 0), 'formatselect'=>array(10, 0), 'fontselect'=>array(11, 0), 'fontsizeselect'=>array(12, 0), 'cut'=>array(13, 0), 'copy'=>array(14, 0), 'paste'=>array(15, 0), 'bullist'=>array(16, 0), 'numlist'=>array(17, 0),'outdent'=>array(18, 0), 'indent'=>array(19, 0), 'blockquote'=>array(20, 0), 'undo'=>array(21, 0), 'redo'=>array(22, 0), 'link'=>array(23, 0), 'unlink'=>array(24, 0), 'anchor'=>array(25, 0), 'image'=>array(26, 0), 'cleanup'=>array(27, 0), 'code'=>array(28, 0), 'forecolor'=>array(29, 0), 'backcolor'=>array(30, 0), 'hr'=>array(31, 0),'removeformat'=>array(32, 0),'visualaid'=>array(33, 0),'sub'=>array(34, 0),'sup'=>array(35, 0),'charmap'=>array(36, 0),'emotions'=>array(37, 7),'iespell'=>array(38, 0),'pagebreak'=>array(39, 1),'styleprops'=>array(40, 2),'tablecontrols'=>array(41, 3),'advhr'=>array(42, 5),'insertdate'=>array(43, 10),'inserttime'=>array(44, 10),'preview'=>array(45, 11),'media'=>array(46, 12),'search'=>array(47, 13),'replace'=>array(48, 13),'print'=>array(49, 14),'pastetext'=>array(50, 16),'images'=>array(51, 26),'pasteword'=>array(52, 16),'ltr'=>array(53, 18),'rtl'=>array(54, 18),'fullscreen'=>array(55, 19),'visualchars'=>array(56, 0),'nonbreaking'=>array(57, 22),'cite'=>array(58, 23),'abbr'=>array(59, 23),'acronym'=>array(60, 23),'del'=>array(61, 23),'ins'=>array(62, 23),'attribs'=>array(63, 23),'insertlayer'=>array(64, 24),'moveforward'=>array(65, 0),'movebackward'=>array(66, 0),'absolute'=>array(67, 0),'template'=>array(68, 25));

   $TinyConfig_button_article_default=array('bold'=> '2,1,1', 'italic'=> '2,1,1', 'underline'=> '3,1,1', 'strikethrough'=> '4,1,1', 'justifyleft'=> '5,1,1', 'justifycenter'=> '6,1,1', 'justifyright'=> '7,1,1', 'justifyfull'=> '8,1,1', 'formatselect'=> '10,1,1', 'fontselect'=> '11,1,1', 'fontsizeselect'=> '12,1,1', 'bullist'=> '16,1,1', 'numlist'=> '17,1,1','outdent'=> '18,1,1', 'indent'=> '19,1,1', 'blockquote'=> '20,0,2', 'undo'=> '21,1,1', 'redo'=> '22,1,1','tablecontrols'=> '41,1,2', 'link'=> '23,1,2', 'unlink'=> '24,1,2', 'anchor'=> '25,1,2', 'image'=> '26,1,2', 'cleanup'=> '27,1,2', 'code'=> '28,1,2', 'forecolor'=> '29,1,1', 'backcolor'=> '30,1,1', 'hr'=> '31,0,2','sub'=> '34,1,2','sup'=> '35,1,2','charmap'=> '36,1,2','emotions'=> '37,0,2','advhr'=> '42,0,2','insertdate'=> '43,0,2','inserttime'=> '44,0,2','preview'=> '45,1,2','media'=> '46,0,2','search'=> '47,0,2','replace'=> '48,0,2','print'=> '49,0,2','pastetext'=> '50,1,1','images'=> '51,1,2','pasteword'=> '52,1,1','ltr'=> '53,0,2','rtl'=> '54,0,2','fullscreen'=> '55,1,2','nonbreaking'=> '57,1,2','cite'=> '58,0,2','abbr'=> '59,0,2','del'=> '61,0,2','ins'=> '62,0,2');

   $Error_Ftp_Connect='';
   $ftp_Connect_glob='';
   // плагины для редактора
   $TinyConfig_plugins= array('','pagebreak',  'style', 'table', 'advimage', 'advhr', 'advlink','emotions', 'iespell', 'inlinepopups','insertdatetime','preview','media','searchreplace','print', 'contextmenu','paste', 'safari','directionality','fullscreen', 'noneditable','visualchars','nonbreaking','xhtmlxtras','layer','template','images');
   // плагины и кнопки
   //s($_SESSION);
 /*  '4'=>
                        array(
                                'name'=>'Профиль',
                                'href'=>'#profile-edit',
                                'module'=>'profile',
                        ),*/
   $globMenuArr_avtor = array( '1'=>
                               array(
                                   'name'=>'ГРАВЦІ',
                                   'href'=>'#players-list',
                                   'module'=>'players',
                               ),
                               '2'=>
                                   array(
                                       'name'=>'ТУРНІРИ',
                                       'href'=>'#turnirs-list',
                                       'module'=>'turnirs',
                                   ),

                               '3'=>
                        array(
                                'name'=>'ПАРИ',
                                'href'=>'#pair-list',
                                'module'=>'pair',
                        ),

       '4'=>
           array(
               'name'=>'ЛІГА ЧЕМПІОНІВ',
               'href'=>'#leagues-list',
               'module'=>'pair',
           ),

       '6'=>
                        array(
                                'name'=>'Продажі',
                                'href'=>'#shop-list',
                                'module'=>'shop',
                        ),
                        '7'=>
                        array(
                                'name'=>'Візити',
                                'href'=>'#visits-list',
                                'module'=>'visits',
                        ),
                        '8'=>
                        array(
                                'name'=>'ДОВІДНИКИ',
                                'href'=>'#sprtov-list',
                                'module'=>'sprtov',
                        ),
                       '9'=>
                           array(
                               'name'=>'НОМІНАЦІЇ',
                               'href'=>'#nomination-show',
                               'module'=>'nomination',
                           ),
       '10'=>
           array(
               'name'=>'НАЛАШТУВАННЯ',
               'href'=>'#settings-show',
               'module'=>'settings',
           ),
                      /*  '11'=>
                        array(
                                'name'=>'Вихід',
                                'href'=>'#players-list-logout=1',
                                'module'=>'',
                        )*/
                      );

   $globMenuArr = array(
                  '1'=>
                       array(
                           'name'=>'ГРАВЦІ',
                           'href'=>'#players-list',
                           'module'=>'players',
                       ),
                    '2'=>
                        array(
                                'name'=>'ТУРНІРИ',
                                'href'=>'#turnirs-list',
                                'module'=>'turnirs',
                        ),

                          '3'=>
                        array(
                                'name'=>'ПАРИ',
                                'href'=>'#pair-list',
                                'module'=>'pair',
                        ),
       '4'=>
           array(
               'name'=>'ЛІГА ЧЕМПІОНІВ',
               'href'=>'#leagues-list',
               'module'=>'pair',
           ),
                       '5'=>
                           array(
                               'name'=>'НОМІНАЦІЇ',
                               'href'=>'#nomination-show',
                               'module'=>'nomination',
                           ),
                  /*      '5'=>
                        array(
                                'name'=>'Екран',
                                'href'=>'#test-show',
                                'module'=>'test',
                        )*/
                      );
   //кнопки для подменю имена поумолчанию
   $SubMenuName = array('add'=>'Додати', 'back'=>'Назад','save'=>'Зберегти', 'nastr'=>'Настройка','help'=>'Допомога','delete'=>'Видалити','zakaz'=>'Закази','user_add'=>'Добавить<br />клієнта','prodol'=>'Продовжити<br />вібір','prava_user'=>'Права<br />користувача','report_ok'=>'Сформувати','filter'=>'Фільтри','print1'=>'Печать');
     // максимальное количество записей на странице, если не указано другое число, это по умолчанию
  define('PAGE_ITEMS',        100);
  // количество групп по-умолчанию для страниц
  define('PAGE_GROUPS',        5);
  define('PAGE_GROUPS_MOB',        3);
   // время в минутах сколько в админке будет доступна сесия
  define('TIME_SESSION_LOGIN',        120);
  // показывать кнопку заказы на главной панеле
  define('ZAKAZ_VIEW',  true);
  // показывать кнопку перевод сайта на главной панеле
  define('TRANSL_VIEW',  FALSE);
  // показывать кнопку модули на главной панеле
  define('MODULE_VIEW',  true);
  // показывать кнопку переход на главной панеле
  define('PEREXOD_VIEW',  true);
  // показать кнопку отчеты
  define('RAPORTS_VIEW',  false);
  
  define('STAT_VIEW',  false);
  define('ERROR_VIEW',  true);
  define('LOSE_KOEF',  30);
  define('LOSE_KOEF_NEW',  10);
  define('WIN_KOEF',  10);

// показывать кнопку выход на главной панеле
  define('EXIT_VIEW',  true);
  define('MESS_NO_ACCESS',  'Ви не маєте прав на данну операцію! <br />Зверніться до вашого адмінистратора.');
  // разные настройки, пока только не стандартные настройки модулей
  $aModulesSettings = 
  array(

     'turnirs' =>array('path'=>'grp_turnirs/turnirs'),
     'etapresult' =>array('path'=>'grp_turnirs/etapresult'),
     'turnirsplayers' =>array('path'=>'grp_turnirs/turnirsplayers'),
     'reiting' =>array('path'=>'grp_turnirs/reiting'),
     'etapplayers' =>array('path'=>'grp_turnirs/etapplayers'),
     'etaps' =>array('path'=>'grp_turnirs/etaps'),
     'tables' =>array('path'=>'grp_turnirs/tables'),
     'leagues' =>array('path'=>'grp_leagues/leagues'),
     'topplayersLeague' =>array('path'=>'grp_leagues/topplayersLeague'),
    // 'sprvalues' =>array('path'=>'settings/sprlist'),
    // 'users' =>array('path'=>'settings/users'), 
  );
$aVariantsOlimp_8 = array(
    '1' => array('player1'=>'1','player2'=>'8','lost'=>'8.1','win'=>'5.1','etap'=>'1/4'),
    '2' => array('player1'=>'5','player2'=>'4','lost'=>'8.2','win'=>'5.2','etap'=>'1/4'),
    '3' => array('player1'=>'3','player2'=>'6','lost'=>'9.1','win'=>'6.1','etap'=>'1/4'),
    '4' => array('player1'=>'7','player2'=>'2','lost'=>'9.2','win'=>'6.2','etap'=>'1/4'),

    '5' => array('player1'=>'5.1','player2'=>'5.2','lost'=>'11.1','win'=>'7.1','etap'=>'1/2','cnt'=>'4'),
    '6' => array('player1'=>'6.1','player2'=>'6.2','lost'=>'11.2','win'=>'7.2','etap'=>'1/2','cnt'=>'3'),
    // за 5-8
    '8' => array('player1'=>'8.1','player2'=>'8.2','lost'=>'12.1','win'=>'10.1','etap'=>'за 5 місце 1/2','cnt'=>'6','isGame1'=>'1','isGame2'=>'2'),
    '9' => array('player1'=>'9.1','player2'=>'9.2','lost'=>'12.2','win'=>'10.2','etap'=>'за 5 місце 1/2','cnt'=>'6','isGame1'=>'3','isGame2'=>'4'),
     // за 5 місце
    '10' => array('player1'=>'10.1','player2'=>'10.2','lost'=>'','win'=>'','etap'=>'за 5 місце','cnt'=>'5','isGame1'=>'8','isGame2'=>'9','isArray'=>'1,2,3,4','isCnt'=>2),
    // финал
    '7' => array('player1'=>'7.1','player2'=>'7.2','lost'=>'','win'=>'','etap'=>'Фінал'),
    // за 3 место
    '11' => array('player1'=>'11.1','player2'=>'11.2','lost'=>'','win'=>'','etap'=>'за 3 місце','cnt'=>'4'),
    // за 7 місце
    '12' => array('player1'=>'12.1','player2'=>'12.2','lost'=>'','win'=>'','etap'=>'за 7 місце','cnt'=>'8'),

);
$aVariantsOlimp_16 = array(
    //верхняя сетка 1/8
    '1' => array('player1'=>'1','player2'=>'16','lost'=>'16.1','win'=>'9.1','etap'=>'1/8','cnt'=>'16'),
    '2' => array('player1'=>'9','player2'=>'8','lost'=>'16.2','win'=>'9.2','etap'=>'1/8','cnt'=>'9'),
    '3' => array('player1'=>'5','player2'=>'12','lost'=>'17.1','win'=>'10.1','etap'=>'1/8','cnt'=>'12'),
    '4' => array('player1'=>'13','player2'=>'4','lost'=>'17.2','win'=>'10.2','etap'=>'1/8','cnt'=>'13'),
    '5' => array('player1'=>'3','player2'=>'14','lost'=>'18.1','win'=>'11.1','etap'=>'1/8','cnt'=>'14'),
    '6' => array('player1'=>'11','player2'=>'6','lost'=>'18.2','win'=>'11.2','etap'=>'1/8','cnt'=>'11'),
    '7' => array('player1'=>'7','player2'=>'10','lost'=>'19.1','win'=>'12.1','etap'=>'1/8','cnt'=>'10'),
    '8' => array('player1'=>'15','player2'=>'2','lost'=>'19.2','win'=>'12.2','etap'=>'1/8','cnt'=>'15'),
     // верхняя сетка 1/4
    '9' => array('player1'=>'9.1','player2'=>'9.2','lost'=>'23.1','win'=>'13.1','etap'=>'1/4'),
    '10' => array('player1'=>'10.1','player2'=>'10.2','lost'=>'23.2','win'=>'13.2','etap'=>'1/4'),
    '11' => array('player1'=>'11.1','player2'=>'11.2','lost'=>'24.1','win'=>'14.1','etap'=>'1/4'),
    '12' => array('player1'=>'12.1','player2'=>'12.2','lost'=>'24.2','win'=>'14.2','etap'=>'1/4'),
    // за верхня сітка 1/2
    '13' => array('player1'=>'13.1','player2'=>'13.2','lost'=>'26.1','win'=>'15.1','etap'=>'1/2'),
    '14' => array('player1'=>'14.1','player2'=>'14.2','lost'=>'26.2','win'=>'15.2','etap'=>'1/2'),
    //фінал
    '15' => array('player1'=>'15.1','player2'=>'15.2','lost'=>'','win'=>'','etap'=>'Фінал'),
    // игра за 9-16
    '16' => array('player1'=>'16.1','player2'=>'16.2','lost'=>'27.1','win'=>'20.1','etap'=>'за 9 місце 1/4','cnt'=>'13','isGame1'=>'1','isGame2'=>'2'),
    '17' => array('player1'=>'17.1','player2'=>'17.2','lost'=>'27.2','win'=>'20.2','etap'=>'за 9 місце 1/4','cnt'=>'13','isGame1'=>'3','isGame2'=>'4'),
    '18' => array('player1'=>'18.1','player2'=>'18.2','lost'=>'28.1','win'=>'21.1','etap'=>'за 9 місце 1/4','cnt'=>'13','isGame1'=>'5','isGame2'=>'6'),
    '19' => array('player1'=>'19.1','player2'=>'19.2','lost'=>'28.2','win'=>'21.2','etap'=>'за 9 місце 1/4','cnt'=>'13','isGame1'=>'7','isGame2'=>'8'),
    // игра за 9-12
    '20' => array('player1'=>'20.1','player2'=>'20.2','lost'=>'30.1','win'=>'22.1','etap'=>'за 9 місце 1/2','cnt'=>'9','isGame1'=>'16','isGame2'=>'17','isArray'=>'1,2,3,4','isCnt'=>2),
    '21' => array('player1'=>'21.1','player2'=>'21.2','lost'=>'30.2','win'=>'22.2','etap'=>'за 9 місце 1/2','cnt'=>'9','isGame1'=>'18','isGame2'=>'19','isArray'=>'5,6,7,8','isCnt'=>2),
    // игра за 9 место
    '22' => array('player1'=>'22.1','player2'=>'22.2','lost'=>'','win'=>'','etap'=>'за 9 місце','cnt'=>'10'),
    // за 5-8
    '23' => array('player1'=>'23.1','player2'=>'23.2','lost'=>'31.1','win'=>'25.1','etap'=>'за 5 місце 1/2','cnt'=>'9'),
    '24' => array('player1'=>'24.1','player2'=>'24.2','lost'=>'31.2','win'=>'25.2','etap'=>'за 3 місце 1/2','cnt'=>'9'),
    // за 5 місце
    '25' => array('player1'=>'25.1','player2'=>'25.2','lost'=>'','win'=>'','etap'=>'за 5 місце'),
    // за 3 место
    '26' => array('player1'=>'26.1','player2'=>'26.2','lost'=>'','win'=>'','etap'=>'за 3 місце'),
    // за 13-16
    '27' => array('player1'=>'27.1','player2'=>'27.2','lost'=>'32.1','win'=>'29.1','etap'=>'за 13 місце 1/2','isGame1'=>'16','isGame2'=>'17'),
    '28' => array('player1'=>'28.1','player2'=>'28.2','lost'=>'32.2','win'=>'29.2','etap'=>'за 13 місце 1/2','isGame1'=>'18','isGame2'=>'19'),
    // за 13 место
    '29' => array('player1'=>'29.1','player2'=>'29.2','lost'=>'','win'=>'','etap'=>'за 13 місце','cnt'=>'14'),
    // за 11 місце
    '30' => array('player1'=>'30.1','player2'=>'30.2','lost'=>'','win'=>'','etap'=>'за 11 місце','isGame1'=>'20','isGame2'=>'21'),
    // за 7 место
    '31' => array('player1'=>'31.1','player2'=>'31.2','lost'=>'','win'=>'','etap'=>'за 7 місце'),
    // за 15 місце
    '32' => array('player1'=>'32.1','player2'=>'32.2','lost'=>'','win'=>'','etap'=>'за 15 місце','isGame1'=>'27','isGame2'=>'28'),

);
$aVariants2minuska_16 = array(
'1' => array('player1'=>'1','player2'=>'16','lost'=>'16.1','win'=>'9.1','etap'=>'1/8','cnt'=>'16'),
'2' => array('player1'=>'9','player2'=>'8','lost'=>'16.2','win'=>'9.2','etap'=>'1/8','cnt'=>'9'),
'3' => array('player1'=>'5','player2'=>'12','lost'=>'17.1','win'=>'10.1','etap'=>'1/8','cnt'=>'12'),
'4' => array('player1'=>'13','player2'=>'4','lost'=>'17.2','win'=>'10.2','etap'=>'1/8','cnt'=>'13'),
'5' => array('player1'=>'3','player2'=>'14','lost'=>'18.1','win'=>'11.1','etap'=>'1/8','cnt'=>'14'),
'6' => array('player1'=>'11','player2'=>'6','lost'=>'18.2','win'=>'11.2','etap'=>'1/8','cnt'=>'11'),
'7' => array('player1'=>'7','player2'=>'10','lost'=>'19.1','win'=>'12.1','etap'=>'1/8','cnt'=>'10'),
'8' => array('player1'=>'15','player2'=>'2','lost'=>'19.2','win'=>'12.2','etap'=>'1/8','cnt'=>'15'),

'9' => array('player1'=>'9.1','player2'=>'9.2','lost'=>'23.1','win'=>'13.1','etap'=>'1/4'),
'10' => array('player1'=>'10.1','player2'=>'10.2','lost'=>'22.1','win'=>'13.2','etap'=>'1/4'),
'11' => array('player1'=>'11.1','player2'=>'11.2','lost'=>'21.1','win'=>'14.1','etap'=>'1/4'),
'12' => array('player1'=>'12.1','player2'=>'12.2','lost'=>'20.1','win'=>'14.2','etap'=>'1/4'),

'16' => array('player1'=>'16.1','player2'=>'16.2','lost'=>'29.1','win'=>'20.2','etap'=>'за 3 місце 1/16','cnt'=>'13','isGame1'=>'1','isGame2'=>'2'),
'17' => array('player1'=>'17.1','player2'=>'17.2','lost'=>'29.2','win'=>'21.2','etap'=>'за 3 місце 1/16','cnt'=>'13','isGame1'=>'3','isGame2'=>'4'),
'18' => array('player1'=>'18.1','player2'=>'18.2','lost'=>'30.1','win'=>'22.2','etap'=>'за 3 місце 1/16','cnt'=>'13','isGame1'=>'5','isGame2'=>'6'),
'19' => array('player1'=>'19.1','player2'=>'19.2','lost'=>'30.2','win'=>'23.2','etap'=>'за 3 місце 1/16','cnt'=>'13','isGame1'=>'7','isGame2'=>'8'),

'20' => array('player1'=>'20.1','player2'=>'20.2','lost'=>'32.1','win'=>'24.1','etap'=>'за 3 місце 1/8','cnt'=>'9','isGame1'=>'2','isGame11'=>'1'),
'21' => array('player1'=>'21.1','player2'=>'21.2','lost'=>'32.2','win'=>'24.2','etap'=>'за 3 місце 1/8','cnt'=>'9','isGame1'=>'3','isGame11'=>'4'),
'22' => array('player1'=>'22.1','player2'=>'22.2','lost'=>'33.1','win'=>'25.1','etap'=>'за 3 місце 1/8','cnt'=>'9','isGame1'=>'6','isGame11'=>'5'),
'23' => array('player1'=>'23.1','player2'=>'23.2','lost'=>'33.2','win'=>'25.2','etap'=>'за 3 місце 1/8','cnt'=>'9','isGame1'=>'7','isGame11'=>'8'),

'29' => array('player1'=>'29.1','player2'=>'29.2','lost'=>'37.1','win'=>'31.1','etap'=>'за 13-16', 'isGame1'=>'16','isGame2'=>'17','cnt'=>'15'),
'30' => array('player1'=>'30.1','player2'=>'30.2','lost'=>'37.2','win'=>'31.2','etap'=>'за 13-16','isGame1'=>'18','isGame2'=>'19','cnt'=>'15'),

'32' => array('player1'=>'32.1','player2'=>'32.2','lost'=>'38.1','win'=>'34.1','etap'=>'за 9-12','cnt'=>'11','isGame1'=>'20','isGame2'=>'21'),
'33' => array('player1'=>'33.1','player2'=>'33.2','lost'=>'38.2','win'=>'34.2','etap'=>'за 9-12','cnt'=>'11','isGame1'=>'22','isGame2'=>'23'),

'24' => array('player1'=>'24.1','player2'=>'24.2','lost'=>'35.1','win'=>'26.2','etap'=>'за 3 місце 1/4'),
'25' => array('player1'=>'25.1','player2'=>'25.2','lost'=>'35.2','win'=>'27.2','etap'=>'за 3 місце 1/4'),

'13' => array('player1'=>'13.1','player2'=>'13.2','lost'=>'26.1','win'=>'15.1','etap'=>'1/2'),
'14' => array('player1'=>'14.1','player2'=>'14.2','lost'=>'27.1','win'=>'15.2','etap'=>'1/2'),

'31' => array('player1'=>'31.1','player2'=>'31.2','lost'=>'','win'=>'','etap'=>'за 13 місце','cnt'=>'14'),
'37' => array('player1'=>'37.1','player2'=>'37.2','lost'=>'','win'=>'','etap'=>'за 15 місце','cnt'=>'16'),
'34' => array('player1'=>'34.1','player2'=>'34.2','lost'=>'','win'=>'','etap'=>'за 9 місце','cnt'=>'10'),
'38' => array('player1'=>'38.1','player2'=>'38.2','lost'=>'','win'=>'','etap'=>'за 11 місце','cnt'=>'12'),

'26' => array('player1'=>'26.1','player2'=>'26.2','lost'=>'36.1','win'=>'28.1','etap'=>'за 3 місце 1/2'),
'27' => array('player1'=>'27.1','player2'=>'27.2','lost'=>'36.2','win'=>'28.2','etap'=>'за 3 місце 1/2'),

'35' => array('player1'=>'35.1','player2'=>'35.2','lost'=>'','win'=>'','etap'=>'за 7 місце'),
'15' => array('player1'=>'15.1','player2'=>'15.2','lost'=>'','win'=>'','etap'=>'Фінал'),
'28' => array('player1'=>'28.1','player2'=>'28.2','lost'=>'','win'=>'','etap'=>'за 3 місце'),
'36' => array('player1'=>'36.1','player2'=>'36.2','lost'=>'','win'=>'','etap'=>'за 5 місце'),

); 
$aVariants2minuska_8 = array(
'1' => array('player1'=>'1','player2'=>'8','lost'=>'8.1','win'=>'5.1','etap'=>'1/4','cnt'=>'8'),
'2' => array('player1'=>'5','player2'=>'4','lost'=>'8.2','win'=>'5.2','etap'=>'1/4','cnt'=>'5'),
'3' => array('player1'=>'3','player2'=>'6','lost'=>'9.1','win'=>'6.1','etap'=>'1/4','cnt'=>'6'),
'4' => array('player1'=>'7','player2'=>'2','lost'=>'9.2','win'=>'6.2','etap'=>'1/4','cnt'=>'7'),

'5' => array('player1'=>'5.1','player2'=>'5.2','lost'=>'11.1','win'=>'7.1','etap'=>'1/2','cnt'=>'4'),
'6' => array('player1'=>'6.1','player2'=>'6.2','lost'=>'10.1','win'=>'7.2','etap'=>'1/2','cnt'=>'3'),

'8' => array('player1'=>'8.1','player2'=>'8.2','lost'=>'13.1','win'=>'10.2','etap'=>'за 3 місце 1/4','cnt'=>'6','isGame1'=>'1','isGame2'=>'2'),
'9' => array('player1'=>'9.1','player2'=>'9.2','lost'=>'13.2','win'=>'11.2','etap'=>'за 3 місце 1/4','cnt'=>'6','isGame1'=>'3','isGame2'=>'4'),

'10' => array('player1'=>'10.1','player2'=>'10.2','lost'=>'14.1','win'=>'12.1','etap'=>'за 3 місце 1/2','cnt'=>'5','isGame1'=>'2'),
'11' => array('player1'=>'11.1','player2'=>'11.2','lost'=>'14.2','win'=>'12.2','etap'=>'за 3 місце 1/2','cnt'=>'5','isGame1'=>'3'),

'13' => array('player1'=>'13.1','player2'=>'13.2','lost'=>'','win'=>'','etap'=>'за 7 місце','cnt'=>'8'),
'7' => array('player1'=>'7.1','player2'=>'7.2','lost'=>'','win'=>'','etap'=>'Фінал'),
'12' => array('player1'=>'12.1','player2'=>'12.2','lost'=>'','win'=>'','etap'=>'за 3 місце','cnt'=>'4'),
'14' => array('player1'=>'14.1','player2'=>'14.2','lost'=>'','win'=>'','etap'=>'за 5 місце','cnt'=>'6'),

); 
  
  
    // Выполниь даже если пользователь закрыл брпаузер   test
   ignore_user_abort("1");
 //  session_name('site_basil_adminsite');
 //  session_start();
$tmp = tempnam(sys_get_temp_dir(), 'app_');
//phpinfo();exit();
?>