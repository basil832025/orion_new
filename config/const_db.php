<?php
/* Описание таблиц БД 
*  пример bs_parts_lz_ru
*  bs - префикс всех таблиц по умолчанию bs
*  parts - название таблицы
*  lz - служебные символы могут быть разные и описание их ниже:
*     lz - означает, что языковая версия таблица, может быть на разных языках и таблица зеркальная 
*     lbz - означает, что языковая и не зеркальная
*     clz - колонки текстовые будут зеркально добавляться при добавлении нового языка (для маленьких таблиц, где несколько текстовых полей, например фотогалерия с описанием)
*     s - служебная таблица
*       пока эти 4 значения 
*  ru - язык таблицы
*  в названии таблицы не должно быть _
*/
define('PREF','bs_');
define('T_PARTS', PREF .'parts_lz_'.$language);
define('T_ARTICLES', PREF .'articles_lz_' .$language);
//define('T_ADMIN_CONFIG', PREF .'admin_config');
//define('T_ADMIN_LANG', PREF .'admin_lang');
define('T_NEWS', PREF .'news_lz_'.$language);
define('T_USERS', PREF .'users');
define('T_MODULES', PREF .'modules_clz');
define('T_SPRLIST', PREF .'spr-spis');
define('T_SPRLIST_VALUES', PREF .'spr-spis-values');
define('T_BRANDS', PREF .'spr-brands_clz');
define('T_MOD_FIELS_SPIS', PREF .'modules-fields-spis_s');
define('T_ACCESS_MODUL', PREF .'access-modul_s');
define('T_FILES', PREF .'files_s');
define('T_USER_SITE', PREF .'users_s');
define('T_OTZIVI', PREF .'otzivi_lbz');
define('T_FORMMASTER', PREF .'formmaster_clz');
define('T_FORMFIELDS', PREF .'form-fields_clz');
define('T_SPR_GROUP', PREF .'spr-group_lz_'.$language);
define('T_SPR_TOV', PREF .'spr-tov_lz_'.$language);
define('T_SPR_TOV_DOP', PREF .'tov_dop_clz');
define('T_GALER', PREF .'galer_lz_'.$language);
define('T_GALER_GRP', PREF .'galer-group_lz_'.$language);
define('T_CONTACTS', PREF .'contacts_clz');
define('T_CONTACTS_FORM', PREF .'contacts_form');
define('T_REGISTR_ART', PREF .'registr-articles_clz');
define('T_HD_TOV_OUT', PREF .'hdtovout');
define('T_RC_TOV_OUT', PREF .'rctovout_lbz');

define('T_SPRTOV', PREF .'spr_tov');
define('T_SHOPS', PREF .'shops');
define('T_VISITS', PREF .'visits');
define('T_TURNIRS', PREF .'turnirs');
define('T_REITING', PREF .'reiting');
define('T_PLAYERS', PREF .'players');
define('T_TURNIR_PLAYERS', PREF .'turnirplayers');
define('T_TURNIR_VARIANTS', PREF .'turnirs_variants');
define('T_GROUP_PORYADOK', PREF .'group_poryadok');
define('T_ETAPS', PREF .'etaps_work');
define('T_ETAPS_NAME', PREF .'etaps_name');
define('T_ETAPS_PLAYER_MESTA', PREF .'etaps_players_mesta');

$tables_update_main=array('parts'=>'lz','articles'=>'lz','news'=>'lz','users'=>'s','modules'=>'clz','spr-spis'=>'clz','spr-spis-values'=>'clz','spr-brands'=>'clz','modules-fields-spis'=>'s','access-modul'=>'s','files'=>'s','user-site'=>'lbz','formmaster'=>'clz','form-fields'=>'clz','spr-group'=>'lz','spr-tov'=>'lz','tov_dop'=>'clz','galer'=>'clz','hdtovout'=>'lbz','rctovout'=>'lbz');

$aTableCreate=array(
    'parts'=>array("CREATE TABLE IF NOT EXISTS  `table_zamena` (
    `id` tinyint(6) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'принадлежность к родителю',
  `sort` int(8) unsigned NOT NULL DEFAULT '1' ,
  `level` int(11) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `parts_modules_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Модуль с которым связаный данный раздел',
  `active` tinyint(1) unsigned DEFAULT '0',
  `parts_menu` tinyint(1) unsigned DEFAULT '1',
  `parts_delete` tinyint(1) unsigned DEFAULT '1' COMMENT 'метка удаления; 0 -  нельзя удалять; 2 - в корзине; 1 -можно',
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `parts_link` int(11) unsigned DEFAULT '0' COMMENT 'переход на внутрений раздел сайта',
  `comment` varchar(255) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `href` varchar(200) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `date_last_modif` datetime DEFAULT NULL,
  `user_last_modif` int(6) DEFAULT NULL,
  `file1` varchar(60) DEFAULT NULL,
  `type_file1` varchar(50) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT '0',
  `parts_type` tinyint(3) DEFAULT '1' COMMENT '1 главное меню, 2 - служеб, 3 - доп меню сайта',
  `is_home` tinyint(1) DEFAULT '0',
  `operaciya` varchar(20) DEFAULT NULL COMMENT 'в модулях связана с полем соответст.',
  `maket` varchar(50) NOT NULL DEFAULT 'maket',
  `shablon` varchar(255) NOT NULL DEFAULT 'articles',
  'sort_new'  varchar(255) NOT NULL DEFAULT,
  PRIMARY KEY (`id`),
  KEY `parts_modules_id` (`parts_modules_id`),
  KEY `part_perent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
","INSERT INTO `table_zamena` VALUES (1,'00001',0,'Главная страница',5,NULL,0,0,1,1,'','','',1,'','home.html',NULL,NULL,NULL,'10.04.2010 17:53:20',1,NULL,NULL,0,2,0,NULL);
"),
'articles' =>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parts_id` int(11) unsigned DEFAULT '0' COMMENT 'какому разделу принадлежит статья',
  `teg` varchar(255) NOT NULL DEFAULT '' COMMENT 'teg dlya vuvoda na sayte',
  `name` varchar(255) DEFAULT NULL COMMENT 'наиминование статьи',
  `categ` int(11) NOT NULL DEFAULT '0',
  `source` varchar(255) DEFAULT NULL COMMENT 'источник статьи',
  `author` varchar(255) DEFAULT NULL COMMENT 'автор статьи',
  `notice` text COMMENT 'анонс статьи в редких случаях',
  `content` longtext COMMENT 'полный текст статьи',
  `date` datetime DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL COMMENT 'картинка по надобности может для анонса',
  `start` tinyint(1) DEFAULT '0' COMMENT 'отображать стаью на стартовой странице',
  `print` varchar(255) DEFAULT NULL COMMENT 'статья на печать выводиться',
  `date_create` datetime NOT NULL COMMENT 'дата создания статьи',
  `date_last_modif` datetime DEFAULT NULL COMMENT 'дата последней модификации',
  `user_last_modif` int(4) DEFAULT '0' COMMENT 'пользователь который последний изменил статью',
  `priority` int(11) unsigned DEFAULT NULL COMMENT 'порядок отображения',
  `active` tinyint(1) unsigned DEFAULT '0' COMMENT 'общая активность статьи',
  `file1` varchar(100) DEFAULT NULL COMMENT 'доп файл для статьи',
  `type_file1` varchar(60) DEFAULT NULL,
  `delete_flag` tinyint(1) DEFAULT '0' COMMENT 'пометка что статья удалена, но она еще будет видна с гугла',
  `title` varchar(255) DEFAULT NULL COMMENT 'при надобности заглавие для браузера',
  `description` varchar(255) DEFAULT NULL COMMENT 'description для статьи',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'ключевые слова для статьи',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
'news'=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'наиминование статьи',
  `source` varchar(255) DEFAULT NULL COMMENT 'источник статьи',
  `author` varchar(255) DEFAULT NULL COMMENT 'автор статьи',
  `notice` text COMMENT 'анонс статьи ',
  `content` longtext COMMENT 'полный текст статьи',
  `date` datetime DEFAULT NULL,
  `img1` varchar(50) DEFAULT NULL COMMENT 'картинка по надобности может для анонса',
  `type_img1` varchar(60) DEFAULT NULL,
  `start` tinyint(1) DEFAULT '0' COMMENT 'отображать стаью на стартовой странице',
  `print` varchar(255) DEFAULT NULL COMMENT 'статья на печать выводиться',
  `date_create` datetime NOT NULL COMMENT 'дата создания статьи',
  `date_last_modif` datetime DEFAULT NULL COMMENT 'дата последней модификации',
  `user_last_modif` int(4) DEFAULT '0' COMMENT 'пользователь который последний изменил статью',
  `priority` int(11) unsigned DEFAULT NULL COMMENT 'порядок отображения',
  `active` tinyint(1) unsigned DEFAULT '0' COMMENT 'общая активность статьи',
  `delete_flag` tinyint(1) DEFAULT '0' COMMENT 'пометка что статья удалена, но она еще будет видна с гугла',
  `title` varchar(255) DEFAULT NULL COMMENT 'при надобности заглавие для браузера',
  `description` varchar(255) DEFAULT NULL COMMENT 'description для статьи',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'ключевые слова для статьи',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
'contacts'=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name_ru` varchar(100) default NULL,
  `name_en` varchar(100) default NULL,
  `name_ua` varchar(100) default NULL,
  `text_ru` text,
  `text_en` text,
  `text_ua` text,
  `email` varchar(60) default NULL,
  `date_create` datetime default NULL,
  `date_last_modif` datetime default NULL,
  `user_last_modif` int(8) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;",
'contacts_form'=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `fio` varchar(120) default NULL,
  `phone` varchar(80) default NULL,
  `email` varchar(60) default NULL,
  `text_message` text,
  `date_create` datetime default NULL,
  `date_last_modif` datetime default NULL,
  `user_last_modif` int(6) default NULL,
  `active` tinyint(1) default '1',
  `new` tinyint(1) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;",
'galer'=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `parts_id` int(11) NOT NULL DEFAULT '0',
  `categ` int(11) NOT NULL DEFAULT '0',
  `name_ru` varchar(255) NOT NULL DEFAULT '',
  `img` int(11) NOT NULL DEFAULT '0',
  `text_ru` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `start` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
",
'brands'=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
     `id` tinyint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name_ru` varchar(255) NOT NULL,
  `default_znach` tinyint(1) unsigned DEFAULT '0',
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
'modules'=>array("CREATE TABLE IF NOT EXISTS  `table_zamena` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `type_module` tinyint(1) DEFAULT '1' COMMENT 'Тип вывода, можно ли делать пренадлежность для разделов,',
  `module_use` tinyint(1) DEFAULT '0',
  `slug_module` tinyint(1) DEFAULT '0' COMMENT 'Например баннеры даный модуль не будет выводиться  в меню',
  `mname` varchar(60) DEFAULT NULL,
  `operaciya` text COMMENT 'операции разный принадлежащие к одному разделу',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;",
"INSERT INTO `table_zamena` VALUES (1, 'Статья', 1, 0, 1, 0, 'articles',NULL),
(2, 'Новости', 1, 1, 0, 0, 'news',NULL),
(3, 'Список статей', 1, 0, 0, 0, 'articles_list',NULL),
(4, 'Статьи по категориям', 1, 0, 0, 0, 'articles_categ',NULL),
(5, 'Главная страница', 1, 1, 1, 0, 'home',NULL),
(6, 'Контакты', 1, 1, 1, 0, 'contacts',NULL),
(7, 'Глосарий', 1, 1, 0, 0, 'glosar',NULL),
(8, 'Банеры', 1, 1, 0, 1, 'baners',NULL),
(9, 'Галерея', 1, 1, 1, 0, 'galer',NULL),
(10, 'Каталог товаров', 1, 1, 1, 0, 'catalog',NULL),
(11, 'Карта сайта', 1, 1, 0, 1, 'site_map',NULL),
(12, 'Голосование', 1, 1, 0, 0, 'golosov',NULL),
(13, 'Каталог для интернет магазинов', 1, 1, 0, 0, 'catalog_for_inernet',NULL),
(14, 'Создание форм', 1, 1, 0, 1, 'create_form',NULL),
(100,'Общий',1,1,0,0,NULL,NULL),
(15,'Пользователи сайта',1,0,1,0,'avtoriz','a:4:{s:8:\"remember\";a:2:{s:2:\"id\";s:8:\"remember\";s:4:\"name\";s:16:\"Вспомнить пароль\";}s:7:\"registr\";a:2:{s:2:\"id\";s:7:\"registr\";s:4:\"name\";s:24:\"Регистрация пользователя\";}s:10:\"registr_ok\";a:2:{s:2:\"id\";s:10:\"registr_ok\";s:4:\"name\";s:19:\"Регистрация успешна\";}s:7:\"avtoriz\";a:2:{s:2:\"id\";s:7:\"avtoriz\";s:4:\"name\";s:11:\"Авторизация\";}}');
"
),
"spr_group"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
  `id` tinyint(6) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL COMMENT 'наиминование раздела',
  `level` tinyint(3) NOT NULL DEFAULT '1',
  `active` tinyint(1) unsigned DEFAULT '0' COMMENT 'активность разделов',
  `delete` tinyint(1) unsigned DEFAULT '1' COMMENT 'метка удаления; 0 -  нельзя удалять; 2 - в корзине; 1 -можно',
  `title` varchar(255) DEFAULT NULL COMMENT 'заглавие для браузера',
  `description` varchar(255) DEFAULT NULL COMMENT 'описание для роботов',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'ключевые слова для роботов',
  `link` int(11) unsigned DEFAULT '0' COMMENT 'переход на внутрений раздел сайта',
  `comment` varchar(255) DEFAULT NULL COMMENT 'коментарий для данного раздела ',
  `url` varchar(200) NOT NULL COMMENT 'название раздела в адреснной строке',
  `user_last_modif` int(6) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `date_last_modif` datetime DEFAULT NULL,
  `notice` text COMMENT 'Описание группы анонс',
  `text` text COMMENT 'описание полный текст',
  `opic_site` tinyint(1) DEFAULT '1' COMMENT 'выводить ли описнаие на сайт',
  `img` varchar(50) DEFAULT NULL,
  `count_tov` int(11) DEFAULT NULL COMMENT 'количество товаров для вывода',
  `type_v` tinyint(3) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
'spr_tov_dop'=>array("CREATE TABLE IF NOT EXISTS  `table_zamena` (
     `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `tov` int(11) unsigned NOT NULL DEFAULT '0',
  `type_dux` int(6) NOT NULL DEFAULT '0',
  `sex` tinyint(1) DEFAULT '1',
  `weight` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_exists` tinyint(1) unsigned zerofill NOT NULL DEFAULT '1',
  `active` tinyint(1) unsigned zerofill NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
"),
'users'=>array("CREATE TABLE IF NOT EXISTS  `table_zamena` (
    `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(30) DEFAULT NULL,
  `user_job` varchar(255) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `user_login` varchar(255) NOT NULL DEFAULT '',
  `user_pass` varchar(255) DEFAULT NULL,
  `user_rule` varchar(100) NOT NULL DEFAULT '1',
  `user_parts` varchar(20) DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT '0',
  `date_create` datetime DEFAULT NULL,
  `date_last_modif` datetime DEFAULT NULL,
  `user_last_modif` int(10) DEFAULT '1',
  `users_comments` text,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`user_login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
",
"INSERT INTO `bs_users` (`user_id`, `user_name`, `user_job`, `email`, `phone`, `user_login`, `user_pass`, `user_rule`, `user_parts`, `active`, `date_create`, `date_last_modif`, `user_last_modif`, `users_comments`) VALUES (1,'','Администартор сайта','','','admin','0f877caf796b0dda304a9ce24cd9b3ce','1',NULL,1,NULL,'2010-01-10 18:04:44',1,'Пароль по-умолчанию adminroot');"),
"sprlist"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name_ru` varchar(70) NOT NULL DEFAULT '' COMMENT 'name cpravochnika',
  `active` tinyint(1) DEFAULT '1',
  `module_id` int(6) unsigned NOT NULL DEFAULT '0',
  `form_teg_vnesh` varchar(40) NOT NULL DEFAULT '' COMMENT 'clugeb imya formu',
  `field_name_vnesh` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `col_lang` tinyint(1) DEFAULT '0' COMMENT 'dlya kolonok c yazikami',
  `default_values` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;",
"sprlist_s"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_spis` int(11) NOT NULL DEFAULT '0',
  `value_ru` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
",
"formmaster_s"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
    `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `module` int(8) DEFAULT '0',
  `form_teg` varchar(30) NOT NULL DEFAULT '' COMMENT 'teg dly razdelen form i vuvoda v shablone na cayte i id form',
  `name_ru` varchar(100) DEFAULT NULL ,
  `text_ru` ',
  `type_form` tinyint(1) DEFAULT '1' COMMENT 'Тип формы: 1-обычная форма со сохранием в бд',
  `name_for_site_ru` varchar(250) DEFAULT NULL ,
  `active` tinyint(1) DEFAULT '1' COMMENT 'Активность на сайте',
  `delete_` tinyint(1) NOT NULL DEFAULT '1' ,
  `delete_view` tinyint(1) DEFAULT '1' COMMENT 'кнопка удаление отображается или нет в списке',
  `active_view` tinyint(1) DEFAULT '1' COMMENT 'отображения активности колонки в таблице',
  `action_js_edit` varchar(50) DEFAULT 'class_form_edit' COMMENT 'для JS со списка функция редактирования',
  `action_js_delete` varchar(50) DEFAULT 'class_form_delete',
  `name_for_list_ru` varchar(250) DEFAULT NULL,
  `name_for_edit_ru` varchar(200) DEFAULT NULL,
  `name_for_add_ru` varchar(250) DEFAULT NULL,
  `is_email` tinyint(3) DEFAULT '0',
  `is_email_admin` tinyint(1) DEFAULT '0',
  `view_text` tinyint(3) DEFAULT '1',
  `shablon_up` text,
  `shablon_down` text,
  `shablon_row` text,
  `main_form` tinyint(1) DEFAULT '1' COMMENT 'glavnaya li forma dlya modulya',
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_teg` (`form_teg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
",
"form-fields_s"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(10) NOT NULL DEFAULT '0' COMMENT 'связь с родител формой',
  `name_ru` varchar(250) NOT NULL DEFAULT '' COMMENT 'описательное название поля русс',
  `type` tinyint(3) DEFAULT '1' COMMENT 'С‚РёРї 1-text,2-checkb,3-radio,4-selec,5-textar,6-pass,7-hid',
  `align` tinyint(1) DEFAULT '1',
  `name_field` varchar(50) NOT NULL DEFAULT '' COMMENT 'название поля лат в форме',
  `name_bd` varchar(50) NOT NULL DEFAULT '' COMMENT 'название для Бд по умолчан такое как field',
  `value_default` varchar(255) NOT NULL DEFAULT '' COMMENT 'значение по умолчанию',
  `max_value_bd_field` int(11) DEFAULT '0' COMMENT 'максимальное значения для Бд и поля например 256 символов',
  `name_short_ru` varchar(40) NOT NULL DEFAULT '' COMMENT 'краткое для спискового названия',
  `name_site_ru` varchar(250) NOT NULL DEFAULT '' COMMENT 'поумолч вывод. с name для сайта, иначе с этого',
  `table_spis` int(8) NOT NULL DEFAULT '0' COMMENT 'таблица с табл. bs_spr-spis_s для selec,radio',
  `table_spis_type` tinyint(1) NOT NULL DEFAULT '1',
  `width_name_col` varchar(8) DEFAULT '280px' COMMENT 'ширина для первой колонки названия',
  `width_field_col` varchar(8) DEFAULT '200px' COMMENT 'ширина для поля',
  `width_list` varchar(6) NOT NULL DEFAULT '' COMMENT 'ширина для колонки списка',
  `view_col` tinyint(1) DEFAULT '1' COMMENT '1 вывести 2 колонки,2 название вверху, 3-внизу,4 -справ',
  `value_obyaz` tinyint(1) DEFAULT '0' COMMENT '1- обязательное поле для заполнения',
  `vuvod_site_admin` tinyint(1) DEFAULT '0' COMMENT '0-вывод и на сайте и в админке,1-на сайте,2-админка',
  `list_view_admin` tinyint(1) DEFAULT '0' COMMENT 'Выводить в админ. в спис это поле по сокращен. полю название',
  `sort` int(8) DEFAULT '1' COMMENT 'порядок отображения',
  `active` tinyint(1) DEFAULT '1' COMMENT 'активность поля',
  `pass_admin_view_text` tinyint(1) DEFAULT '1' COMMENT 'по умол выводить как текст в админке, 0 как на сайте под зве',
  `comment_ru` varchar(255) NOT NULL DEFAULT '' COMMENT 'komentariy k polyu',
  `comment` varchar(255) NOT NULL DEFAULT '' COMMENT 'komentariy k polyu',
  `filter1` tinyint(1) DEFAULT '0',
  `filter2` tinyint(1) DEFAULT '0',
  `filter3` tinyint(1) DEFAULT '0',
  `min_char` varchar(2) DEFAULT '3',
  `type_char` tinyint(1) DEFAULT '1',
  `cpez_char` varchar(100) NOT NULL DEFAULT '',
  `type_filter` tinyint(3) NOT NULL DEFAULT '0',
  `regular` varchar(255) NOT NULL DEFAULT '',
  `mask` varchar(255) NOT NULL DEFAULT '',
  `spis_filter_bd` varchar(255) NOT NULL DEFAULT '' COMMENT 'Dop zapros dlya filtra',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
",
"files_s"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `size` varchar(20) DEFAULT NULL,
  `delete` tinyint(3) NOT NULL DEFAULT '1',
  `is_img` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
",
"sprtov"=>"CREATE TABLE IF NOT EXISTS  `table_zamena` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parts_id` int(11) unsigned DEFAULT '0' COMMENT 'какому разделу принадлежит статья',
  `brand` int(6) DEFAULT '0' COMMENT 'Производитель со справочника производителей',
  `name` varchar(255) DEFAULT NULL COMMENT 'наиминование статьи',
  `articul` varchar(60) DEFAULT NULL,
  `notice` text COMMENT 'анонс статьи в редких случаях',
  `content` longtext COMMENT 'полный текст статьи',
  `price` double(10,2) NOT NULL DEFAULT '0.00',
  `curr` tinyint(1) DEFAULT '1',
  `date` datetime DEFAULT NULL,
  `start` tinyint(1) DEFAULT '0' COMMENT 'отображать стаью на стартовой странице',
  `print` varchar(255) DEFAULT NULL COMMENT 'статья на печать выводиться',
  `date_create` datetime NOT NULL COMMENT 'дата создания статьи',
  `date_last_modif` datetime DEFAULT NULL COMMENT 'дата последней модификации',
  `user_last_modif` int(4) DEFAULT '0' COMMENT 'пользователь который последний изменил статью',
  `priority` int(11) unsigned DEFAULT '0' COMMENT 'порядок отображения',
  `active` tinyint(1) unsigned DEFAULT '0' COMMENT 'общая активность товара',
  `file` varchar(100) NOT NULL DEFAULT '' ,
  `delete_flag` tinyint(1) DEFAULT '0' COMMENT 'пометка что товар удален, но она еще будет видна с гугла',
  `title` varchar(255) DEFAULT NULL COMMENT 'при надобности заглавие для браузера',
  `description` varchar(255) DEFAULT NULL COMMENT 'description для товара',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'ключевые слова для товара',
  `old_price` float(10,2) DEFAULT '0.00',
  `sale` float(4,2) NOT NULL DEFAULT '0.00' COMMENT 'скидка',
  `weight` varchar(20) DEFAULT NULL COMMENT 'вес например мл',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'пол 1 - женщины, 2 - мужчины',
  `filter1` int(8) NOT NULL DEFAULT '0' COMMENT 'Доп фильтр с простого справочника',
  `is_exist` tinyint(1) unsigned zerofill NOT NULL DEFAULT '1',
  `is_new` tinyint(1) DEFAULT '0',
  `is_home` tinyint(1) DEFAULT '0',
  `count_tov` double(15,2) DEFAULT NULL COMMENT 'количество в остатке товара',
  `img` varchar(100) NOT NULL DEFAULT '',
  `img2` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",

);
?>