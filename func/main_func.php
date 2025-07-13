<?php
//---------------------------------------------------------------------------------
//                     Функция P() - тестовый вывод на экран
//---------------------------------------------------------------------------------
//------------------------ОПИСАНИЕ-------------------------------------------------
/* ТЕСТОВАЯ функция выводит на экран переменные или масивы, которые нужно отследить в читабельном виде!
*
*/
//wLog('main');
function wLog($message, $level = 'info', $logDir = 'logs') {
	// Преобразуем message в строку, если это массив или объект
	if (is_array($message) || is_object($message)) {
		$message = print_r($message, true);
	}

	$level = strtoupper($level);
	$timestamp = date('Y-m-d H:i:s');
	$date = date('Y-m-d');

	// Убедимся, что папка существует
	if (!is_dir($logDir)) {
		mkdir($logDir, 0777, true);
	}

	$logFile = rtrim($logDir, '/\\') . "/log_$date.log";

	// Определим, откуда вызвана функция
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
	$file = $backtrace['file'] ?? 'unknown file';
	$line = $backtrace['line'] ?? 'unknown line';

	$logEntry = "[$timestamp] [$level] ($file:$line) $message" . PHP_EOL;
	file_put_contents($logFile, $logEntry, FILE_APPEND);
}

function p($data,  $str = 0,$dop_param = 0) {
  if ($dop_param == 1){
	  print '--------------------------------<br />Инфо о переменной: <pre>';
	  print var_dump($data) .'</pre>' ;
  }
  if ($str){
	  $str = '<br />-------------------------------<br />Вывод функции p(<br /><pre>'. "\r\n";
	  $str .= print_r($data, true);
	  $str .= '</pre>' .')==============================<br />'. "\r\n";
	  return $str;
  }else{
	  print '<br />-------------------------------<br />Вывод функции p(<br /><pre>';
	  print print_r($data) .'</pre>' .')==============================<br />' ;
  }
}
//==================================================================================
//==================================================================================

// тестовая функция для записи лога
function log_write($data='no_data'){
 list($fp_log_err) = read_file(ROOT_A .'log.txt', 1);

  if (is_array($data) || is_object($data)){
	  $data=p($data,1);
	  $data.='____test';
  }
  if ($data=='') $data='no_data';
   write_file($fp_log_err, $data);

}
//---------------------------------------------------------------------------------
//                     Функция read_file() - чтение файла
//---------------------------------------------------------------------------------
//------------------------ОПИСАНИЕ-------------------------------------------------
/* функция считывания файла. моежет считывать файл в строку в масив или просто открывает файл,
 * создавать с правами веб сервера или фтп
 * также создает все каталоги если их не достает в пути, блокирует файл перед любой операцией
*/
function read_file($path_file, $regim, $block = true){
//	s('$path_file='.$path_file);
	global $Error_Ftp_Connect,$ftp_Connect_glob;
	# - $path_file:
	#     полный путь к открываемому файлу (локальный)
	# - $regim:
	#     0  -  тоько открыть для чтения r
	#     1 -  тоько открыть для дозаписи a
	#     2 -  тоько открыть для записи содержимое стирается w
	#     10 -  тоько открыть для чтения и записи содержимое стирается r+
	#     11 -  тоько открыть для чтения и дозаписи содержимое стирается a+
	#     12 -  тоько открыть для записи и чтения содержимое стирается w+
	#     3  -  читать содержимое файла в строку + закрыть файл
	#     4  -  читать содержимое файла в массив + закрыть файл

	switch ((int)$regim) {
	   case 0:
	   case 3:
	   case 4:
			$mode = 'r';
			break;
	case 1:
			$mode = 'a';
			break;
	case 2:
			$mode = 'w';
			break;
	case 10:
			$mode = 'r+';
			break;
	case 11:
			$mode = 'a+';
			break;
	case 12:
			$mode = 'w+';
			break;
	}
	if (!$path_file){
		send_error('Передаваемый файл пустой ERR_NUM=R1');
		return array(false, '');
	}
	 $path_file = stristr($path_file, ROOT) ? $path_file : ROOT .($path_file[0]=='/' ? substr($path_file, 1) : $path_file);
	// пытаемся открыть файл в заданом режиме
    
	clearstatcache();
	if(file_exists($path_file) &&  FALSE != $fp = @fopen($path_file, $mode ."b")){
//send_error('tyt main '.$path_file);
		// получилось открыть файл пока все в порядке выполняем действие указаное в режиме
		if ($block){
			$poput =0;
			if ($mode == 'r'){
				// попытка захватить файл для блокировки, если он занят другим скриптом ждем пока не освободится но не больше 3 секунд
				while (false === $f_lock = flock($fp, LOCK_SH + LOCK_NB, $lock )){
					if (function_exists('usleep')) {
						// пытаемся установиить контроль над файлом через 20 мили секунд
						usleep(2000000);
						$poput ++;
					}else{
						// если система не поддерживает usleep то прийдется более грубей по 1 с делать паузу
						sleep(1);
						$poput += 5;
					}
					// если достигле 3 секунд выходим нет смысла продолжать файл кто-то жестко занял
					if ($poput>=15) {break;}
				}
			}else{
				// попытка захватить файл для блокировки, если он занят другим скриптом ждем пока не освободится но не больше 3 секунд
				while (false === $f_lock = flock($fp, LOCK_EX  + LOCK_NB, $lock )){
					if (function_exists('usleep')) {
						// пытаемся установиить контроль над файлом через 20 мили секунд
					   $poput ++;
						usleep(200000);

					}else {
						 $poput += 5;
						// если система не поддерживает usleep то прийдется более грубей по 1 с делать паузу
						sleep(1);
						$poput += 5;
					}
					// если достигле 3 секунд выходим нет смысла продолжать файл кто-то жестко занял
					if ($poput>=15) {break;}
				}
			}
		}else{
		   $f_lock =true;
		}
		if ($f_lock) {
			// блокировка удалась выполняем полезные действия

			// выполняем результат операции только те что сичтываем все остальные прочто вернуть указатель на файл
				 switch ((int)$regim) {
					   case 3:
							// читать содержимое файла в строку + закрыть файл
							$f_r = filesize ($path_file);
							$contents = fread ($fp, ($f_r > 0 ? $f_r : 1));
							fclose($fp);
							return array(true, $contents);
							break;
					   case 4:
							// читать содержимое файла в массив + закрыть файл
							$contents = file($path_file);
							fclose($fp);
							return array(true, $contents);
							break;
					   case 10:
							$f_r = filesize ($path_file);
							 $contents = fread ($fp, ($f_r>0 ? $f_r : 1));
							return array($fp, $contents);
							break;
					   default:
							return array($fp, '');
				  }
		}
		else {
			// блокировка не удалась
	}
    return array($fp, '');
	}else{
	//	s('tyt11_$path_file='.$path_file);
//send_error('tyt main не удалось открыть '.$path_file);
// открыть файл не удалось. Возможно этого файла еще нет попытаемся записать этот файл или другая причина
		 // пробуем создать не достающие каталоги или проверить если они в наличии в пути передаваемого файла
		 clearstatcache();
		 if (!file_exists($path_file)){
			 // если файла не существует

			if ($res_cr_i = create_dir($path_file)){
				 // катлог существует, но файла еще нету
				 if(PHP_OS != "WIN32" && PHP_OS != "WINNT" && 1==0){
					 // для unix систем
					   if (false === $ftp_connect = ftp_connect_()){
					    //  p('Не удалось открыть фтп соединение  ERR_NUM=R');
                           send_error('Не удалось открыть фтп соединение  ERR_NUM=R2');
						return array(false, '');
					}
						clearstatcache();
						// для фтп создания файла нужен пустой файл и мы проверяем если он, если нету пытаемся создать
						if (!file_exists(ROOT_A .'tmp/ftp.tmp')){
							if (create_dir(ROOT_A .'tmp')){
								if (!is_writable(ROOT_A .'tmp')){
									// нету прав на запись во временый каталог
									// для начала пробуем изменить права на катлог средствами и с правами WEB-cервера
									if (!chmod(ROOT_A .'tmp', 0777)) {
										  // для ФТП убираем лишний путь от корня сервера
											$path_file_tmp = "/" .substr(ROOT_A .'tmp/', strlen(ROOT));
										 // пытаемся поменять права FTP
										 if (!ftp_site ($ftp_connect, "chmod " ."777 " .FTP_HOST_ROOT .$path_file_tmp)){
											 send_error('Невозможно поменять права на каталог. (' .FTP_HOST_ROOT .$path_file_tmp .') Нету прав, возможно, в UNIX системе  ERR_NUM=R3');
											 ftp_quit($ftp_connect);
											 return array(false, '');
										 }
									}
								}
								  if (false == $fpTmp = fopen(ROOT_A .'tmp/ftp.tmp', 'w')){
									  // ветка почти не врозможная но на всяк случай проверим
										send_error('По не понятной причине нельяз создать временный файл. (' .ROOT_A .'tmp/ftp.tmp' .') хотя все права на запись в катлог есть ');
										if (!chmod(ROOT_A .'tmp/ftp.tmp', 0777)) {
												send_error('Невозможно поменять права на временный файл. (' .ROOT_A .'tmp/ftp.tmp' .')  ERR_NUM=R4');
										}
										fclose($fpTmp);
										return array(false, '');
								  }
								  fclose($fpTmp);

							} else {
								// не возможно создать временную директорию (очень плохо)                               // send_error('не возможно создать временную директорию (очень плохо)  ERR_NUM=R5');
								return array(false, '');
							}
						}
						 // для ФТП убираем лишний путь от корня сервера
						 $path_file_ftp = "/" .substr($path_file, strlen(ROOT));

						// cдесь создаем с помощью FTP команд файл
						if (!ftp_put($ftp_connect, FTP_HOST_ROOT .$path_file_ftp, ROOT_A .'tmp/ftp.tmp',  FTP_BINARY)){
							   send_error('Невозможно создать файл с помощью FTP команд возможно у вас не правильный . (' .FTP_HOST_ROOT .$path_file_ftp .') FTP путь. Проверте константу FTP_HOST_ROOT  ERR_NUM=R6');
							   ftp_quit($ftp_connect);
							   return array(false, '');
						}
						// Изменение доступа
						if (!ftp_site ($ftp_connect, "chmod " ."666 " .FTP_HOST_ROOT.$path_file_ftp)){
							send_error('Невозможно поменять права на файл на (' .FTP_HOST_ROOT .$path_file_tmp .')  ERR_NUM=R7');
							   ftp_quit($ftp_connect);
							   return array(false, '');
						}

						}else{
							// для Win системы
						   if(false == $fp = fopen($path_file, "w" ."b")){
								send_error('Невозможно cоздать файл на (' .$path_file .') в windows системе. редкий глюк  ERR_NUM=R8');      
                                             return array(false, '');
						   }
							  fclose($fp);

						 }
						 // попытка снова открыть для режимов не закрывающих файл
							switch ((int)$regim) {
							   case 0:
							   case 1:
							   case 2:
							   case 10:
							   case 11:
							   case 12:
							   // не должно быть зацикливания --------------------- но все же)))))
									return read_file($path_file, $regim);
									break;
							  default :
									return array(true, '');
							}

				}else{
					// не возможно создать каталог к создаваемому файлу
                    send_error('не возможно создать каталог к создаваемому файлу  ERR_NUM=R9');
					return array(false, '');
				}
		 }else{
				clearstatcache();
				  // файл существует но есть проблемы
				 if(PHP_OS != "WIN32" && PHP_OS != "WINNT"){
					 if (false === $ftp_connect = ftp_connect_()){
                           send_error('Не удалось открыть фтп соединение  ERR_NUM=R10');
                        
						return array(false, '');
					}
					  // для ФТП убираем лишний путь от корня сервера
						$path_file_ftp = "/" .substr($path_file, strlen(ROOT));
					 // пытаемся поменять права FTP
                    // send_error('права для файла фтп '.FTP_HOST_ROOT .$path_file_ftp);
					 if (!ftp_site ($ftp_connect, "chmod " ."666 " .FTP_HOST_ROOT .$path_file_ftp)){
						 send_error('Невозможно поменять права на файл. (' .FTP_HOST_ROOT .$path_file_ftp .') Нету прав, возможно, в UNIX системе  ERR_NUM=R11');
						 ftp_quit($ftp_connect);
						 return array(false, '');
					 }else{
						ftp_quit($ftp_connect);
						// повторно візіваем функцию
						 return read_file($path_file, $regim);
						//return array(true, '');
					 }

			 }else{
				 // если файл существует но его нельзя открыть то ошибка для windows
                  send_error('если файл существует но его нельзя открыть то ошибка для windows ERR_NUM=R12'); 
				 return array(false, '');
			 }


		 }
	}

return;
}
//==================================================================================
//                     Функция create_dir() - создание катлогов рекурсивно
//---------------------------------------------------------------------------------
//------------------------ОПИСАНИЕ-------------------------------------------------
/*  функцмя создает недостающиеся каталоги в передаваемом пути, если в конце пути файл (с росширением), то он игнорируется    *  потому, что это задача функции read_file создать файл
*/
function create_dir($path_dir, $prava='777') {
	global $Error_Ftp_Connect,$ftp_Connect_glob;
 // убираем корневой  путь к сайту
 $path_dir = preg_replace('#' .ROOT .'#i', "", $path_dir);
 // получаем каталоги
 $dir_mas = pathinfo($path_dir);
 if (empty($dir_mas['dirname'])){
	 send_error('Не корректен путь к катлогу. (' .ROOT .$path_dir .') Проверте правильность передаваемого пути ERR_NUM=CD1');
					return 0;
 }
 // если нету розширения в пути, то добавляем последний элемент, как каталог (потому что в основном у всех файлах должно быть розширение)
 $dir_mas['dirname'] .= empty($dir_mas['extension']) ? '/' .$dir_mas['basename'] : '';
 // если существует путь с каталогами и не существет в системы такого каталога по такому пути
 if(!is_dir($dir_mas['dirname'])){
	 $dir_path = '';
	// розбиваем имена катлогов и добавляем в массив
	$dirs_m = explode("/", $dir_mas['dirname']);
	clearstatcache();
	foreach ($dirs_m as $key => $value) {
		$dir_path .=$value;
		if (!is_dir(ROOT .$dir_path)){

			if(PHP_OS != "WIN32" && PHP_OS != "WINNT"){
				// это UNIX системы
				// пробуем соединится к ФТП серверу
				if (false === $ftp_connect = ftp_connect_()){
                      send_error('Не удалось открыть фтп соединение  ERR_NUM=CD2');
					return 0;
				}
				$ftplocal = 1;
                //send_error(' (Возможно у вас не правильно  веедена константа пути ФТП  - FTP_HOST_ROOT) (' .ROOT .$dir_path .') Нету прав, возможно, в UNIX системе '.FTP_HOST_ROOT ."/" .$dir_path.'   ERR_NUM=CD3');
				if (false == $fp=ftp_mkdir($ftp_connect, FTP_HOST_ROOT ."/" .$dir_path)){
					send_error('Не возможно создать каталог. (Возможно у вас не правильно  веедена константа пути ФТП  - FTP_HOST_ROOT) (' .ROOT .$dir_path .') Нету прав, возможно, в UNIX системе   ERR_NUM=CD3');
                    ftp_quit($ftp_connect);
					return 0;
				}
				if (!ftp_site ($ftp_connect, "chmod " .$prava. " " .FTP_HOST_ROOT ."/" .$dir_path)){
					send_error('Не возможно поменять права средствами ФТП   ERR_NUM=CD4');ftp_quit($ftp_connect);
					return 0;
				}

			}else{
				// это WINDOWS системы
				if (!mkdir(ROOT .$dir_path, '0'.$prava)){
					send_error('Не возможно создать каталог. ['.$path_dir.']  (' .ROOT .$dir_path .') Нету прав, возможно, в WIN системе   ERR_NUM=CD5');
					return 0;
				}
			}
		}
		$dir_path .= '/';
	}
 }else{
	// или есть уже такой каталог
	return 2;
 }

  return 1;
}
//==================================================================================
//==================================================================================

//---------------------------------------------------------------------------------
//                     Функция ftp_connect_()
//---------------------------------------------------------------------------------
//------------------------ОПИСАНИЕ-------------------------------------------------
/* создает подключение к фтп серверу
*
*/
function ftp_connect_($ftp_host='', $ftp_user='', $ftp_pass='', $ftp_port=''){
	global $Error_Ftp_Connect,$ftp_Connect_glob;
	if (substr(php_uname(), 0, 7) == "Windows") {
		return false;
	}

	if (!$ftp_Connect_glob){
	$ftp_host   = $ftp_host ? $ftp_host : ((defined('FTP_SERVER') && FTP_SERVER) ? FTP_SERVER : $_SERVER['SERVER_NAME']);
	$ftp_port   = $ftp_port ? $ftp_port : ((defined('FTP_PORT') && FTP_PORT) ? FTP_PORT : 21);
	$ftp_time   = (defined('FTP_TIME') && FTP_TIME) ? FTP_TIME : 20;
	$ftp_connect = ftp_connect($ftp_host, $ftp_port, $ftp_time);
	if(!$ftp_connect){

		 $Error_Ftp_Connect='К сожалению, не удается установить  соединение с FTP-сервером';
		 return false;
	}
	$ftp_user   = $ftp_user ? $ftp_user :((defined('FTP_USER_NAME') && FTP_USER_NAME) ? FTP_USER_NAME : '');
	$ftp_pass   = $ftp_pass ? $ftp_pass :((defined('FTP_USER_PASS') && FTP_USER_PASS) ? FTP_USER_PASS : '');
	if($ftp_user=='' || $ftp_pass==''){
		$Error_Ftp_Connect='Не введены данные идентификации на сервере FTP';

		 return false;
	}
	if (!@ftp_login($ftp_connect, $ftp_user, $ftp_pass)){
		$Error_Ftp_Connect='Не праивльные фтп логин или фтп пароль введен, проверте пожалуйста';
		 return false;
	}
	// Текущая директория или стартовая в системе
	$ftp_Connect_glob=$ftp_connect;
	}else{
	   $ftp_connect = $ftp_Connect_glob;
	}
return $ftp_connect;
}
  //---------------------------------------------------------------------------------
  //                     Функция write_file - запись у файл
  //---------------------------------------------------------------------------------
  //------------------------ОПИСАНИЕ-------------------------------------------------
  /*  # Запись данных в файл с полной перезаписью файла
  * необязателные параметры: $close -  1-закрыть файл по умолчанию закрываем
  * $bit - Бит с которого пишем изменненные данные  (-1 - записать в конец файла) -
  */


function write_file($fp, $str, $close = 1, $bit=-1){

  if (!empty($fp)){

		if ($bit != -1){
			// Принимает указатель файла fp и усекает файл до размера $bit. Эта функция возвращает TRUE при успехе, FALSE при неудаче.
			ftruncate($fp,$bit);
			// ищет позицию указателя файла.
			fseek($fp, $bit);     // rewind($fp_file);
			fwrite($fp, $str);
		} else {
			fwrite($fp, $str);
		}
		// Немедленная запись всех изменений в файле.
		fflush($fp);

	if ($close){
		fclose($fp);
	}
  }else{
	  // Запись ошибки
	send_error("Ресурса для файла передаваемого на запись  нету ");
	return;
  }
}
  //==================================================================================
  //---------------------------------------------------------------------------------
  //                     Функция AjaxJson() отправлЯет аякс ответ
  //---------------------------------------------------------------------------------
  //------------------------ОПИСАНИЕ-------------------------------------------------
  /*
  *
  */
function Ajax($masiv){
//wLog($masiv);
   // замечен глюк при перезагрузке выскакивало сообщения со сохранением файла
   if (empty($_POST['ajax_method'])){
      return false;
   }
   $_POST['ajax_method']='';
	if (!empty($_SESSION['error_predypreg'])){
	   //$masiv= $masiv+ array('ERRN_AJAX' => $_SESSION['error_predypreg']);
		$_SESSION['error_predypreg']=false;
	}
   // замечен был глюк, что иногда проскакивала кодировка не utf-8, а старенькая WINDOWS-1251 потому проверям код на корректность utf8 если все впорядке то пропускаем, если проскочило то пітаемся преобразовать, конечно єто не ПАНАЦЕя от всех бед, но всеже единичній віход с положения, будем решать проблемі по мере поступления, продолбался с єтой проблемой 2 дня!!! с поиском подходящего решения 
 if (function_exists('iconv') && !empty($masiv['content']) && !utf8_compliant($masiv['content'])) {
       $masiv['content']= iconv('WINDOWS-1251','UTF-8',$masiv['content']);
  //s('utt8');
   }

 //send_error(p($masiv,1));
  // $myJsonData=array2json($masiv);
	//s($masiv);
	//s('tyt1111');
   $myJsonData=json_encode($masiv);
  // s(json_last_error());
 //s($myJsonData);
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' .gmdate('D, d M Y H:i:s') .'GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header("Content-type: application/json; charset=utf-8");
  
   print $myJsonData;
  exit;
}
//проверяет корректная ли кодировка utf8
function utf8_compliant($str) {   
    if ( strlen($str) == 0 ) return true;   
    return (preg_match('/^.{1}/us',$str) == 1);   
} 
function mess($mess='',$action='',$module='',$reload=0,$time_save=1,$post_href=''){
//   if (empty($_POST['redirectStatus']))
   {
	$action = $action ? $action : $_SESSION['kernel']['action'];
	$module = $module ? $module : $_SESSION['kernel']['module'];
 Ajax(array(
 'return_content_bool'=>'false',
 'module'=>$module,
 'time_save'=>$time_save,
 'close_'=>'1',
'java_script'=>'',
'reload'=>$reload, 
'action'=>$action,
'MESS_AJAX'=>$mess,
'post_return'=>$post_href));
}
//exit;
}
function redirect_Ajax($module='',$post_href='',$status='NOT'){
  if (empty($_POST['redirectStatus']))
   {
	//$action = $action ? $action : $_SESSION['kernel']['action'];
	$module = $module ? $module : $_SESSION['kernel']['module'];
 Ajax(array(
 'return_content_bool'=>'false',
 'module'=>$module,
 'close_'=>'1',
'java_script'=>'',
'reload'=>0,
'status'=>$status, 
'action'=>'anyaction',
'post_return'=>$post_href));
}
//exit;
}
function window_mess($mess=''){
  Ajax(array('content' => '',
         'message_user' => 'ERROR!',
         'close_' => 0,
         'java_script' => 'mess_modal("'.$mess.'");',
        'post_return' => '',
        )); exit;
}
  //==================================================================================
 function array2json($arr) {
  $parts = array();
  $is_list = false;
  if (is_object($arr))  $arr= (array) $arr;
  if (!is_array($arr)) return;
  if (count($arr)<1) return '{}';

  //Find out if the given array is a numerical array
  $keys = array_keys($arr);
  $max_length = count($arr)-1;
  //See if the first key is 0 and last key is length - 1
  if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {
	$is_list = true;
	for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
	  if($i != $keys[$i]) { //A key fails at position check.
		$is_list = false; //It is an associative array.
		break;
	  }
	}
  }

  foreach($arr as $key=>$value) {
	if(is_array($value)) { //Custom handling for arrays
	  if($is_list) $parts[] = array2json($value); /* :RECURSION: */
	  else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
	} else {
	  $str = '';
	  if(!$is_list) $str = '
	  "' . $key . '":';
        $value = str_replace("\\", '', $value); 
	  //Custom handling for multiple data types
	  if(is_numeric($value)) $str .= '"'.$value.'"'; //Numbers
	  elseif($value === false) $str .= 'false'; //The booleans
	  elseif($value === true) $str .= 'true';
	 else  $str .= '"' .preg_replace('|\s|u',' ', addcslashes($value,'"')) . '"'; //All other things
    // $str = str_replace("\\", "\\\\", $str);
   
	//  else $str .= '\'' .addslashes($value) . '\''; //All other things
	  // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

	  $parts[] = $str;
	}
  }
  $json = implode(',',$parts);

  if($is_list) return '[' . $json . ']';//Return numerical JSON
  return '{  '. $json . ' } ';//Return associative JSON
}
  //---------------------------------------------------------------------------------
  //                     Функция
  //---------------------------------------------------------------------------------
  //------------------------ОПИСАНИЕ-------------------------------------------------
  /*
  *
  */
function send($email, $subj, $from, $html){
global $smarty, $template;
// нужно подумать что к чему с этой библетекой или выбрать другую
include_once(ROOT_A."libs/libmail.php");
$mail = new Mail();          // Задаем кодировку при инициализации
$mail->From($from);
$mail->To($email);
$mail->Subject($subj);
$mail->Body($html);
//$mail->Cc( "copyreceiver@ukr.net"); // Копия письма
$mail->Receipt();                 // Уведомление о прочтении
//$mail->autoCheck(true);             // Проверка адресатов на валидность
//$mail->Bcc( "bcopy@asd.com");     // скрытая копия отправится по этому адресу
$mail->Priority(3) ;                // приоритет письма
$mail->ReplyTo( $from ); // адрес для ответа
//$mail->Attach( "asd.gif","", "image/gif" ) ; // прикрепленный файл
//$mail->smtp_on() - не используйте, если хотите отправить через ф-цию mail()
//$mail->smtp_on( "ssl://smtp.gmail.com", "login", "password", 465) ; // отправка через SMTP
$error = $mail->Send();    // а теперь пошла отправка. Возвращает ошибку или null";
// делаем два варианта письма - text/html и plain/text
// для каждого адреса свое письмо
	if ($error) return false;
	else return true;
}
  //==================================================================================
  //---------------------------------------------------------------------------------
  //                     служебные функции
  //---------------------------------------------------------------------------------
  //------------------------ОПИСАНИЕ-------------------------------------------------
  /*
  *
  */
function PrepateString($str){
	return trim(strip_tags($str));
}

// Берем переменные GET и POST
function get($key){
		return isset($_GET[$key])?$_GET[$key]:false;
}
//echo 'func';
function gete($key){
		return isset($_GET[$key])?escape($_GET[$key]):false;
}

function post($key){
		if (isset($_POST[$key]) && is_array($_POST[$key])){
		foreach ($_POST[$key] as $k => $value) {
			  $_POST[$key][$k] = escape($value);
		} //конец цикла fekv
	} // конец if
	else {
		$_POST[$key] = isset($_POST[$key])?escape($_POST[$key]):false;
	} // конец else
		return $_POST[$key];}
/*function post($key){
		return isset($_POST[$key])?$_POST[$key]:false;
}*/
function poste($key){
	if (isset($_POST[$key]) && is_array($_POST[$key])){
		foreach ($_POST[$key] as $k => $value) {
			if (is_array($value)){
				foreach ($value as $k2 => $v2) {
					$_POST[$key][$k][$k2] = addslashes($v2);
					//$_POST[$key][$k][$k2] = escape($v2);
				}
			}else{
			 // $_POST[$key][$k] = escape($value);
			  $_POST[$key][$k] = addslashes($value);
			}
		} //конец цикла fekv
	} // конец if
	else {
		$_POST[$key] = isset($_POST[$key])?addslashes($_POST[$key]):false;
		//$_POST[$key] = isset($_POST[$key])?escape($_POST[$key]):false;
	} // конец else
		return $_POST[$key];
}
// Удаляет HTML-теги из параметра. data - mixed
function remove_tags($data){
		$doc = $data;
		if (is_array($doc)){
				while( list($k,$v) = each ($doc))
						$doc[$k] = trim(strip_tags($v));
		}
		else $doc = trim(strip_tags($doc));
		return $doc;
}

// Экранирует кавычки. data - mixed
function escape($data){
//	if (!get_magic_quotes_gpc()){
			$doc = $data;
			if (is_array($doc)){
					while( list($k,$v) = each ($doc))
							$doc[$k] = sql_valid(clean_word($v));
			}
			else $doc = sql_valid(clean_word($doc));
			return $doc;
/*	}
	else{
		return $data;
	}*/
}
function clean_word($data){
    $data=preg_replace('#<!--\[if gte mso 9\]>.*?<!\[endif\]-->#is','',$data);
      $data=preg_replace('#<!--\[if gte mso 10\]>.*?<!\[endif\]-->#is','',$data);
    return $data; 
}
// Перенаправляет на другой скрипт
function redirect($url){
		header("Location: ".$url);
		exit;
}
  //==================================================================================
  //==================================================================================

 //---------------------------------------------------------------------------------
 //                     Функция shifr - шифрует или розшифровуют строку
 //---------------------------------------------------------------------------------
 //------------------------ОПИСАНИЕ-------------------------------------------------
 /*
 *
 */
function shifr($data,$metod, $secret_key=''){
	if (!$secret_key && defined('SECRET_KEY')){
		$secret_key = SECRET_KEY;
	}elseif (!$secret_key && !defined('SECRET_KEY')){
		$secret_key = 'nsdf8rh23sdn83';
	}

	 $td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
	/* Создать IV и определить длину keysize */
	$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
	$ks = mcrypt_enc_get_key_size ($td);
	$key = substr (md5 ($secret_key), 0, $ks);

	 /* Открыть шифр */
 if ($metod)
 {

	/* Инициализировать шифрование */
	mcrypt_generic_init ($td, $key, $iv);

	/* Шифровать данные */
	$encrypted = mcrypt_generic ($td, $data);

	/* Закрыть дескриптор шифрования */
	mcrypt_generic_deinit ($td);
	 mcrypt_module_close ($td);
	return $encrypted;
}
else
	{
	/* Инициализировать модуль шифрования для дешифрования */
	mcrypt_generic_init ($td, $key, $iv);

	/* Дешифровать шифрованную строку */
	$decrypted = mdecrypt_generic ($td, $data);

	/* Закрыть дескриптор дешифрования и закрыть модуль */
	mcrypt_generic_deinit ($td);
	mcrypt_module_close ($td);
	return $decrypted;
	}

}
 //==================================================================================
 //==================================================================================


 //-------------------Функция process_tegs() -------------------------------------
 //------------------------ОПИСАНИЕ-----------------------------------------------
 /* Функция обработки служебных тегов
 */
function process_tegs($sContents,$svg_html) {
	global $mTegsTextGlob,$admin_html_login;
	// обработка тега галвного контента
//s($mTegsTextGlob);

	//ob_clean();
	$sContents = preg_replace('#<\s*content_html\s*>.*?<\s*/\s*content_html\s*>#is', (!empty($admin_html_login) ? $admin_html_login : ''), $sContents);
	$sContents = preg_replace('#<\s*content\s*>.*?<\s*/\s*content\s*>#is', (!empty($mTegsTextGlob['content']) ? $mTegsTextGlob['content'] : ''), $sContents);
	$sContents = preg_replace('#<svg_array>.*?</svg_array>#is', $svg_html, $sContents);

	// обработка под меню тега
	$sContents = preg_replace('#<\s*submenu\s*>.*?<\s*/\s*submenu\s*>#is', (!empty($mTegsTextGlob['submenu']) ? $mTegsTextGlob['submenu'] : ''), $sContents);
	$sContents = preg_replace('#<\s*mainmenu\s*>.*?<\s*/\s*mainmenu\s*>#is', (!empty($mTegsTextGlob['mainmenu']) ? $mTegsTextGlob['mainmenu'] : '*****'), $sContents);

// обработка javascript файлов
	$sContents = preg_replace('#<\s*javascript_teg_file\s*>.*?<\s*/\s*javascript_teg_file\s*>#is', (!empty($mTegsTextGlob['javascript_teg_file']) ? $mTegsTextGlob['javascript_teg_file'] : ''), $sContents);
   // обработка javascript тега
	$sContents = preg_replace('#<\s*javascript_teg_after\s*>.*?<\s*/\s*javascript_teg_after\s*>#is', (!empty($mTegsTextGlob['javascript_teg_after']) ? '<script language="JavaScript">
<!--
   ' .$mTegsTextGlob['javascript_teg_after'] .'
//-->
</script>' : ''), $sContents);
 $sContents = preg_replace('#<\s*javascript_teg_up\s*>.*?<\s*/\s*javascript_teg_up\s*>#is', (!empty($mTegsTextGlob['javascript_teg_up']) ? '<script language="JavaScript">
<!--
   ' .$mTegsTextGlob['javascript_teg_up'] .'
//-->
</script>' : ''), $sContents);
	//s($sContents);
return $sContents;
}
 //=============  конец функции ==================================================
 //-------------------Функция ----------------------------------------------------
 //------------------------ОПИСАНИЕ-----------------------------------------------
 /*
 */
 function javascript_teg_file(){
	$str = '';
	if ($handle = opendir('js_')) {
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && strpos($file, '.js')){
		   if (!empty($str)){
			   $str .='-';
		   }
		   $str .= substr($file,0,-3);
		}
	}
	closedir($handle);
}
if (!empty($str)){
 $str = '<script type="text/javascript" language="JavaScript" src="scriptjs/' .$str .'.js"></script>' ."\r\n";
}
return $str;
}
 //=============  конец функции ==================================================

  //==================================================================================

 //---------------------------------------------------------------------------------
 //                     Функция translit_latin() - переводит русские символы в латинские
 //---------------------------------------------------------------------------------
 //------------------------ОПИСАНИЕ-------------------------------------------------
 /*
 *
 */
function translitIt($str)
{
	$tr = array(
		"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
		"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
		"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
		"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
		"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
		"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
		"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
		"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
		" "=> "_", "."=> "", "/"=> "_"
	);
	return strtr($str,$tr);
}
 function translit_latin($urlstr) {
if (preg_match('/[^A-Za-z0-9_\-\/]/u', $urlstr)) {
	$urlstr = translitIt($urlstr);
   return  preg_replace('/[^A-Za-z0-9_\-\/]/u', '', $urlstr);
}
return $urlstr;
}

 //==================================================================================
 //==================================================================================
//-------------------Функция submenu()----------------------------------------------------
//------------------------ОПИСАНИЕ-----------------------------------------------
/*  Функция создает под меню для админсайта
*
*
*/
   function menu_turnirs($menuAll,$thisActiveModule=''){
	   $s_return='';
	//   s($menuAll);

	   if (!empty($menuAll))
	   {
		   foreach ($menuAll as $key => $menu) {
			   $class = !empty($menu['class']) ? $menu['class'] : '';

			   if ($menu['module']==$thisActiveModule)
				   $s_return .= '<span class="active_menu_turnirs" ">'.$menu['name'].'</span> ';
			   else
				   $s_return .= '<a href="'.$menu['href'].'" class="ajax_send '.$class.'">'.$menu['name'].'</a> ';

		   }
			$s_return='<div class="menu_turnirs">'.$s_return.'</div>';
	   }

	   return  $s_return;
   }
   function submenu2($menuAll,$html=''){
	   $s_return ='';
if ($html)
{
	return $html;
}else {
	if (!empty($menuAll)) {
		if (!empty($menuAll['bigMenu'])) {
			$menuAll = $menuAll['bigMenu'];
			//	s($menuAll);
			$s_return .= '<!-- Example single danger button -->
<div class="submenu3_polosa">
<div >
';
			if (!empty($menuAll['Search_field']))
				$s_return .= '<div class="search_fio_games">' . $menuAll['Search_field'] . ' </div> ';

			$s_return .= $menuAll['Button_Menu'];

		}

		$menuAll_ = !empty($menuAll['Line_Menu']) ? $menuAll['Line_Menu'] : $menuAll;
		$menuLineClass = !empty($menuAll['Line_Menu']) ? 'menuLine' : 'menuLineLeft';
		//	s($menuAll_);
		$s_return .= '<div class="' . $menuLineClass . '">';
		foreach ($menuAll_ as $key => $menu) {
			$class = !empty($menu['class']) ? $menu['class'] : '';
			$s_return .= '<a href="' . $menu['href'] . '" class="ajax_send ' . $class . '">' . $menu['name'] . '</a>
				 ';

		} //конец цикла fekv
		$s_return .= '</div>';
		if (!empty($menuAll['bigMenu'])) $s_return .= '</div>';


	} // конец if`
	// send_error($s_return);
	return $s_return;
}
  }
  /*  function submenu2($menuAll){
	   $s_return ='';

   //   s($menuAll);
	   if (!empty($menuAll)) {
	   	if (!empty($menuAll['bigMenu']))
		{
			$menuAll=$menuAll['bigMenu'];
		//	s($menuAll);
			$s_return.='<!-- Example single danger button -->
<div class="submenu3_polosa">
<div >
';
			if (!empty($menuAll['Search_field']))
				$s_return.='<div class="search_fio_games">'.$menuAll['Search_field'].' </div> ';

			$s_return.=$menuAll['Button_Menu'];

		}

		   $menuAll_ = !empty($menuAll['Line_Menu']) ? $menuAll['Line_Menu'] :$menuAll;
		   $menuLineClass = !empty($menuAll['Line_Menu']) ? 'menuLine': 'menuLineLeft';
	   //	s($menuAll_);
		   $s_return.='<div class="'.$menuLineClass.'">';
			foreach ($menuAll_ as $key => $menu) {
			    $class = !empty($menu['class']) ? $menu['class'] : '';
			    $s_return .= '<a href="'.$menu['href'].'" class="ajax_send '.$class.'">'.$menu['name'].'</a>
				 ';

         		} //конец цикла fekv
			$s_return.='</div>';
		   if (!empty($menuAll['bigMenu'])) $s_return.='</div>';


	   } // конец if`
     // send_error($s_return);
	return $s_return;
  }*/
    function submenu($menu){
	   global $SubMenuName;
	   $s_return ='';
	   if (!empty($menu)) {
			foreach ($menu as $key => $value) {
				$sub_name=(!empty($value['menu_name'])?$value['menu_name']:(!empty($SubMenuName[$key])?$SubMenuName[$key]:''));
			   if (!empty($value['javascript']))
                $s_return .= '<div class="submenu_text" align="center"><a href="javascript:'.$value['javascript'].'" ><img width="35px" alt="'.$sub_name.'" title="'.$sub_name.'" src="img/slug_small/' .$key .'.png" border="0"></a></div>';
		       else  if (!empty($value['http']))
                 $s_return .= '<div class="submenu_text" align="center"><a href="'.$value['http'].'" ><img width="35px" alt="'.$sub_name.'" title="'.$sub_name.'" src="img/slug_small/' .$key .'.png" border="0"></a></div>';	  // $s_return .= '<div class="submenu_text" align="center"><a href="#'.(!empty($value['module']) ? $value['module'] : '').'|'.(!empty($value['action']) ? $value['action'] :'').'|'.(!empty($value['post']) ? $value['post'] :'').'" '.(!empty($value['del']) ? 'class="delete_val" mess="'.$value['del'].'"' :  'class="'.(!empty($value['class']) ? $value['class']:'ajax_send').'"').'   ><img width="45px" alt="'.$sub_name.'" title="'.$sub_name.'" src="img/slug_small/' .$key .'.png" border="0"><br />'.$sub_name.'</a></div>';
	    else
            $s_return .= '<div class="submenu_text" align="center"><a href="#'.(!empty($value['module']) ? $value['module'] : '').'-'.(!empty($value['action']) ? $value['action'] :'').'-'.(!empty($value['post']) ? $value['post'] :'').'" '.(!empty($value['del']) ? 'class="delete_val" mess="'.$value['del'].'"' :  'class="'.(!empty($value['class']) ? $value['class']:'ajax_send').'"').' '.(!empty($value['mess']) ? 'mess="'.$value['mess'].'"' : '').' '.(!empty($value['wintype']) ? 'wintype="'.$value['wintype'].'"':'').'  ><img width="35px" alt="'.$sub_name.'" title="'.$sub_name.'" src="img/slug_small/' .$key .'.png" border="0"></a></div>';
             
         		} //конец цикла fekv
	   } // конец if
     // send_error($s_return);
	return $s_return;
  } 
  function main_menu($menu = '',$thisActiveModule=''){
	   global $globMenuArr,$globMenuArr_avtor;
     //  echo '+++++';
     
 //    s('$thisActiveModule');


	  $text='';$str='';
	  if (!empty($_SESSION['gt']['user_name']))
	  {
		  $text = 'User: '.$_SESSION['gt']['user_name'] ;
	  }
	  if (!empty($_SESSION['gt']['id']))
		  $str='<div  class="text-center user_prof" ><a href="#profile-edit-id='.$_SESSION['gt']['id'].'" class="ajax_send">'.$text.'</a> </div>';
	//  <span class="login_in"></span>
	  if ($_SESSION['gt']['user_rule']<10)
	  {
		  $avtor = ' <div class="avtor_mob d-flex">
          <a href="#players-list-logout=1" class="ajax_send" >Вихід</a>
          </div>';
		  $avtor_mob = '<a href="#players-list-logout=1" class="ajax_send"> <svg height="24px" width="24px" class="login_in"> <use width="24px" height="24px" xlink:href="#exit"></use> </svg></a>';
	  }

	  else
	  {
		  $avtor = ' <div class="avtor_mob d-flex">
          <a href="#players-list-login=1" id="avtoris" >Вхід</a>
          </div>';
		  $avtor_mob = '<a href="#players-list-login=1" id="avtoris" > <svg height="24px" width="24px" class="login_in"> <use width="24px" height="24px" xlink:href="#vhod"></use> </svg></a>';

	  }

$str1='
  <nav class="navbar navbar-expand-lg navbar-dark  bg-dark">
  <div class="container-fluid">
  <div class="logo"><a href="#players-list" class="ajax_send"><img src="img/logo_orion.png" loading="lazy" width="115"/></a></div>
   <ul class="nav navbar-nav navbar-right hide_bigscreen">
         <li>'.$avtor_mob.'</li>
      </ul>
      <button class="navbar-toggler collapsed" id="NavBarBurger" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" 
    aria-expanded="false"> <span class="burger"></span>
      </button>
  
     
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav  me-auto mb-2 mb-md-0">';
  if ($_SESSION['gt']['user_rule']<10)
$MenuAr = !empty($menu) ? $menu : $globMenuArr_avtor;
else
$MenuAr = !empty($menu) ? $menu : $globMenuArr;

if (!empty($MenuAr)) {  

            
       //echo 'dsksdll'; 
    foreach ($MenuAr as $menu){
       $active=''; $id_dop='';
      if ($_SESSION['gt']['user_rule']>9 && $menu['module']=='settings')  continue;
      if ($_SESSION['gt']['user_rule']!=3 && $menu['module']=='visits')  continue;
      if ($_SESSION['gt']['user_rule']!=3 && $menu['module']=='sprtov')  continue;
      if ($_SESSION['gt']['user_rule']!=3 && $menu['module']=='shop')  continue;
       if (!empty($menu['module']) && ($thisActiveModule==$menu['module'])) $active='active';
       if (!empty($menu['module']) && $menu['module'] == 'turnirs'
		   && (($thisActiveModule=='etapresult') || ($thisActiveModule=='turnirsplayers') || ($thisActiveModule=='reiting') || ($thisActiveModule=='tables'))
	   ) $active='active';

      // if (!empty($menu['module']) && ('profile'==$menu['module'])) $menu['href'].='-id='.$_SESSION['gt']['id']; 
         if (!empty($menu['dop']) && ('avtoris'==$menu['dop']))
         {
            $id_dop='id="avtoris"'; 
            $ajax_send=''; 
        } else     
        $ajax_send='ajax_send"'; 
       $str1.= ' <li class="nav-item" >
  				    <a href="'.$menu['href'].'" class="nav-link '.$active.' '.$ajax_send.'" '.$id_dop.'>'.$menu['name'].'</a>
				</li>';
        
    }



	$str1.='         
        </ul>
        '.$str.'
       
     
    </div>

    '.$avtor.'

    </div>
  </nav>
';

}

return $str1;
  }    
/*Функция проверка подключеных модулей, которые нужны для работы проги  */
function check_module_php($brouz=0){
	global $Error_Ftp_Connect,$ERROR_BASE_DATA;
	$status='';
   $php_ver = phpversion();
	$status .= ((float)$php_ver <= MIN_PHP) ? 'Ваша версия PHP ('.phpversion().') устарела, минимум '.MIN_PHP.' дальнейшая работа не имеет смысла<br />' : '';
	$status .= (apache_get_version()< "1.3") ? 'Ваша версия APACHE устарела<br />' : '';
	$status .= (!extension_loaded('mysql')) ? 'Для работы программы, нужно установить модуль Mysql<br />' : '';
	$status .= (!extension_loaded('gd')) ? 'Для работы программы, нужно установить модуль GD<br />' : '';
	$status .= (!extension_loaded('iconv')) ? 'Для работы программы, нужно установить модуль iconv<br />' : '';
	//$status .= (!extension_loaded('mcrypt')) ? 'Для работы программы, нужно установить модуль mcrypt<br />' : '';
	$status .= (!extension_loaded('session')) ? 'Для работы программы, нужно установить модуль Session<br />' : '';
	$status .= (!extension_loaded('sockets')) ? 'Для работы программы, нужно установить модуль Sockets<br />' : '';
	$status .= (!extension_loaded('ftp')) ? 'Для работы программы, нужно установить модуль Ftp<br />' : '';
	$status .= (ini_get('session.auto_start')) ? 'Установите в настройках php.ini значение session.auto_start=OFF<br />' : '';
	if (!$brouz){
	$status .= (preg_match("#msie#i", $_SERVER['HTTP_USER_AGENT'])) ? 'Извините в данный момент Ваш браузер, Internet Explorer, по ряду причин не поддерживается, воспользуйтесь FireFox или Opera!' : '';
	}
	if ($status){
		return '<div style="color:red" align="center">' .$status .'<br />Дальнейшее продолжение программы не имеет смысла.<br />Исправте выше перечисленные проблемы и возвращайтесь снова!!!</div>';
	}else{
		  check_ftp_bd_error();
		return false;
	}

}

  ##################################################
//  загрузка картинки с изминением размера картинки при необходимости, при включеном GD библеотеке
/* $file - это путь к файлу, где  лежит файл изображения
*  $dest  - путь куда нужно скопировать новый маленький файлик
*  $ maxwdt - максимальная ширина изображения, котора должна быть
*  $ maxhdt - максимальная высота изображения, котора должна быть
* *  возвращает массив
*  1 параметр - если ошибка или нет true или false
*  2 пар - при ошибке те69кст ошибки, при удаче имя файла загруженого
*  3 парам - влкючена ли библиотека GD, которая нужна для изменеия размеров пропорций           картинок
*/
function upload_image_size($file,$url_file, $dest='/', $maxwdt='', $maxhgt='',$max_image_size=819200) { //800*1024
  if (!file_exists($file)) {
	  return array(false, 'Файла не существует!!!');
  }
  if (substr( $dest,-1)!='/'){$dest=$dest.'/';}
   if (!is_dir($dest)){
	create_dir($dest);
}
   // $valid_types=array("gif","jpg", "png", "jpeg");
	 $afil=pathinfo($file);
	 $dir_n = $afil["dirname"];
	 $file_n = $afil["basename"];

	  list($owdt,$ohgt,$otype)=@getimagesize($file);
     //  send_error(' передался файл при загрузке!!!');   
	  $newimg = imagecreatefromstring(file_get_contents($file));

/*  switch($otype) {
   case 1:  $newimg=imagecreatefromgif($file); break;
   case 2:  $newimg=imagecreatefromjpeg($file); break;
   case 3:  $newimg=imagecreatefrompng($file); break;
   default: $error_result='Загружаемый формат файла не поддерживается (Файл: '.$file_n.', Тип: '.$otype.')';
   return array(false, $error_result);
  }*/

  if($newimg) {
	if($owdt>1500 || $ohgt>1200){
		list($newimg,$owdt, $ohgt) = Resample($newimg, $owdt, $ohgt, 1024,768,0);
	}
	if ($maxwdt && $maxhgt && ($owdt>$maxwdt || $ohgt>$maxhgt)){
			list($newimg,$owdt, $ohgt)= Resample($newimg, $owdt, $ohgt, $maxwdt, $maxhgt);
		}

   switch($otype) {
	 case 1: imagegif($newimg,$dest .$file_n); break;
	 case 2: imagejpeg($newimg,$dest .$file_n,90); break;
	 case 3: imagepng($newimg,$dest .$file_n);  break;
   }

	   imagedestroy($newimg);

   chmod_($dest .$file_n,644);
	 return array(true, $file_n);
  }

}
// изменинеи размера картинки
function Resample($img, $owdt, $ohgt, $maxwdt, $maxhgt, $quality=1) {
  if(!$maxwdt) $divwdt=0;
   else $divwdt=Max(1,$owdt/$maxwdt);

  if(!$maxhgt) $divhgt=0;
   else $divhgt=Max(1,$ohgt/$maxhgt);

  if($divwdt>=$divhgt) {
   $newwdt=$maxwdt;
   $newhgt=round($ohgt/$divwdt);
  } else {
   $newhgt=$maxhgt;
   $newwdt=round($owdt/$divhgt);
  }

   $tn=imagecreatetruecolor($newwdt,$newhgt);
   if($quality)
	   imagecopyresampled($tn,$img,0,0,0,0,$newwdt,$newhgt,$owdt,$ohgt);
   else
	   imagecopyresized($tn,$img,0,0,0,0,$newwdt,$newhgt,$owdt,$ohgt);

   imagedestroy($img);

   $img = $tn;

   return array($img,$newwdt, $newhgt);
}
function writeToLog($text,$dir='logs')
{
	$text = 'Дата: ' .date("H:i:s d-m-Y ") ."=====================\n". $text ."\n-----------------------\n";

	$filename = ROOT_A . $dir.'/log_' .date('d-m-Y') .'.html';
	// Вначале давайте убедимся, что файл существует и доступен для записи.
	if (!chmod(ROOT_A .$dir, 0777)) {
		echo('Невозможно поменять права на временный файл. (' .ROOT_A .$dir .')  ERR_NUM=R4');
	}
	/*	if (!chmod(ROOT_A .'barcodes', 0777)) {
                                                    echo('Невозможно поменять права на временный файл. (' .ROOT_A .'logs' .')  ERR_NUM=R4');
                                            }
       */ // В нашем примере мы открываем $filename в режиме "записи в конец".
	// Таким образом, смещение установлено в конец файла и
	// наш $text допишется в конец при использовании fwrite().
	if (!$fp = fopen($filename, 'a')) {
		echo "Не могу открыть файл ($filename)";
		exit;
	}

	// Записываем $somecontent в наш открытый файл.
	if (fwrite($fp, $text) === FALSE) {
		echo "Не могу произвести запись в файл ($filename)";
		exit;
	}

	//echo "Ура! Записали ($text) в файл ($filename)";

	fclose($fp);


}
function sCron($data)
{
	writeToLog(p($data,1),'log_cron');
}
//***********************************************
//-------------------Функция ----------------------------------------------------
//------------------------ОПИСАНИЕ-----------------------------------------------
/*
*/
   function send_error($str) {
	   if (!empty($str)){
		 //  $_SESSION['error_predypreg'] = addslashes($str);
        // s($_POST);
		   user_error(addslashes($str), E_USER_ERROR);
	   }
	return;
  }
//=============  конец функции ==================================================

// загружает любой файл и возвращает уникальное имя файла
function upload_file($name_polya,$path, $type_file=1, $max_file=0){
		if(isset($_FILES[$name_polya]))  {
			$err=$_FILES[$name_polya]["error"];
	  // Если ошибок не было
		if($err == 0){
			 //Новый файл передан?
	if(is_file($_FILES[$name_polya]["tmp_name"])){
	   // send_error('старт 33 '.date("H:i:s"));
		if($_FILES[$name_polya]["size"]==0) {
		   // send_error('Новый файл не передан');
			return array(false, 0, 0, 'Новый файл не передан');}
			//Файл  получен
			$ext=substr($_FILES[$name_polya]["name"],strrpos($_FILES[$name_polya]["name"],"."));
			$name=preg_replace('/(.+)\..+$/', "\\1", $_FILES[$name_polya]["name"]);
			$fn=translit_latin($name).time().$ext;
			  //файл не удалось скопировать?
			   $size_f = $_FILES[$name_polya]["size"];
			   $type_f=$_FILES[$name_polya]["type"];
			   // определяем варианты загрузки файла картинка или что-то другое
			   switch ($type_file) {
				  case 2: // только изображения
						 if(strpos($type_f,"image/")!==0) {
							 return array(false, 0, 0, 'Файл, который Вы передали не изображение');
						 }
					break;
				 case 3: // все, кроме изображения
						 if(strpos($type_f,"image/")===0) {
							 return array(false, 0, 0, 'Файл, который Вы передали изображение');
						 }
					break;
				  default: // по-умолчанию все фйалы

			   }
			   // проверяем максимальный размер файла
			   if ($max_file && $size_f > $max_file){
					return array(false, 0, 0, 'Размер загружаемого файла привышает, указаного в настройках модуля на ' .($size_f-$max_file) .' байт.');
			   }
				 //Скопируем новий файл
			if(!move_uploaded_file($_FILES[$name_polya]["tmp_name"], $path.$fn)) {
			   // send_error('файл не удалось скопировать '.$path.$fn);
					return array(false, 0, 0, 'Файл не удалось скопировать');}
			  chmod($path.$fn, 0644);
			return  array($fn, $size_f, $type_f,'');
		} else{
		   // send_error('файл не передался');
			return array(false, 0, 0, 'Файл не передался');
		}
		}else{
			switch ($err) {
			   case 1:
			  // send_error('Размер загруженного файла превышает размер установленный параметром upload_max_filesize в php.ini               ');
			   return array(false, 0, 0, 'Размер загруженного файла превышает размер установленный параметром upload_max_filesize в php.ini');
							   break;
			   case 2:
				//    send_error('размер загруженного файла превышает размер установленный параметром MAX_FILE_SIZE в HTML форме');
				   return array(false, 0, 0, 'Размер загруженного файла превышает размер установленный параметром MAX_FILE_SIZE в HTML форме');
			   break;
			   case 3:
			   //     send_error('загружена только часть файла');
					return array(false, 0, 0, 'Загружена только часть файла');
			   case 4:
				//    send_error('Файл не был загружен (Пользователь в форме указал неверный путь к файлу)');
					return array(false, 0, 0, 'Файл не был загружен (Пользователь в форме указал неверный путь к файлу)');
				 break;
			   default:
			} // конец switch
		}
	 }
	 return array(false, 0, 0, 'Нету такого названия поля файла '.$name_polya);
}
//=============  конец функции ==================================================
// загружает любой файл и возвращает уникальное имя файла новая функция загрузка файлов с потока по моднявому поддерживается с php 5.1
function upload_file_new($data_file,$name_file,$size,$type,$path, $type_file=1, $max_file=0){
  //  s($name_file);
 //   s($size);
  //  s($type);
  //  s($data_file);
    
		if(empty($data_file))  {
			$err=1;
            	return array(false, 'Новый файл не передан');
             }
	  // Если ошибок не было
	// Выделим данные
//$data = explode(',', $data_file);

// Декодируем данные, закодированные алгоритмом MIME base64
//$encodedData = str_replace(' ','+',$data[1]);
//$data_file = base64_decode($encodedData);
			//Файл  получен
			$ext=substr($name_file,strrpos($name_file,"."));
			$name=preg_replace('/(.+)\..+$/', "\\1", $name_file);
			$fn=translit_latin($name).time().$ext;
			  //файл не удалось скопировать?
			   // определяем варианты загрузки файла картинка или что-то другое
			   switch ($type_file) {
				  case 2: // только изображения
						 if(strpos($type,"image/")!==0) {
							 return array(false, 'Файл, который Вы передали не изображение');
						 }
					break;
				 case 3: // все, кроме изображения
						 if(strpos($type,"image/")===0) {
							 return array(false, 'Файл, который Вы передали изображение');
						 }
					break;
				  default: // по-умолчанию все фйалы

			   }
			   // проверяем максимальный размер файла
			   if ($max_file && $size > $max_file){
					return array(false, 'Размер загружаемого файла привышает, указаного в настройках модуля на ' .($size-$max_file) .' байт.');
			   }
				 //Скопируем новий файл
     if(!move_uploaded_file($data_file, $path.$fn))

     //          if(!file_put_contents($path.$fn, $data_file))
     	{
			   // send_error('файл не удалось скопировать '.$path.$fn);
					return array(false, 'Файл не удалось скопировать');
        }
			  chmod($path.$fn, 0644);
			return  array($fn, '');
	
	
	
	 return array(false, 'Нету такого названия поля файла '.$name_file);
}
//=============  конец функции ==================================================

/**
 * Ищет загружаемый файл во временной папке.
 * Возвращает имя временного файла в случае успеха.
 *
 * @param   string      $tmp_dir        имя временной директории, в которой ищется файл
 * @param   string      $pattern        шаблон, по которому ищутся файлы
 *
 * @return  boolean
 */
function findTemporaryFile($tmp_dir, $pattern)
{
	$found = false;

	if (is_dir($tmp_dir))
	{
		$phptempfiles = glob($tmp_dir.$pattern);

		if (count($phptempfiles)==1)
		{
			$found = $phptempfiles[0];
		}
	}

	return $found;
}

/**
 * Форматирует и возвращает представление размера файла в более удобочитаемое.
 *
 * @param   integer     $size       размер файла
 *
 * @return  string
 */
function humanFileSize($size)
{
	$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}
//-------------------Функция ----------------------------------------------------
//------------------------ОПИСАНИЕ-----------------------------------------------
/*
	удаление файла
*/
function delete_file($path) {
	global $Error_Ftp_Connect,$ftp_Connect_glob;
   if (is_file($path)){
	   if (!@unlink($path)) {
         # "Проверка наличия доступа для удаления файла";
                if(PHP_OS != "WIN32" && PHP_OS != "WINNT"){
       	   // пробуем соединится к ФТП серверу
				if (false === $ftp_connect = ftp_connect_()){
					send_error('Не удалось удалить файл '.$path);
					return false;
				}
			// для ФТП убираем лишний путь от корня сервера
						 $path = FTP_HOST_ROOT.'/' .substr($path, strlen(ROOT));
		   if (!ftp_delete ($ftp_connect, $path)){
					send_error('Не удалось удалить файл средставми FTP '.$path);
					return false;
		   }
         }
	   } // конец if 
      // else send_error('удалалили файл средствами PHP');
   } 
	return true;
}
//=============  конец функции ==================================================



function validateEmail($email){
	if (preg_match('/^[a-z_\d]+[-a-z\d\._]*@(([\da-z]+(-[\da-z]+)*)(\.[\da-z]+(-[\da-z]+)*)*\.(com|net|org|mil|edu|gov|arpa|info|biz|inc|name|[a-z]{2})|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $email)){
		return true;
	}else{
		return false;
	}
}
//запись в константы УСТАРЕВШАЯ ФУНКЦИЯ СЕЙЧАС ДРУГУЮ ИСПОЛЬЗУЮ
/*function write_define($const, $value, $chmod=1, $path_file=''){
	if (!$path_file){
			$path_file = ROOT_A .'config/access.bas';


		list($fp, $content) = read_file($path_file, 10);
		 $content = preg_replace('#define\s*\(\s*[\'"]{1}'.$const.'[\'"]{1}\s*,\s*[\'"]{1}([^\'"]*)[\'"]{1}\s*\)\s*;#i',"define('".$const."','".$value."');",$content  );
		write_file($fp, $content,1,0);
		if ($chmod){
			chmod_($path_file, 444);
		}
	}
	return false;
}*/
//установка ftp прав
function chmod_($file, $prava=777){
	global $Error_Ftp_Connect,$ftp_Connect_glob;
if(PHP_OS != "WIN32" && PHP_OS != "WINNT"){
// для unix систем
if (!$ftp_Connect_glob){
if (false === $ftp_connect = ftp_connect_()){
return false;
}
}else{
	$ftp_connect=$ftp_Connect_glob;
}
//clearstatcache();
	// нету прав на запись
	// для начала пробуем изменить права на катлог средствами и с правами WEB-cервера
	if (!@chmod($file, octdec($prava))) {
		  // для ФТП убираем лишний путь от корня сервера
			$path_file_tmp = "/" .substr($file, strlen(ROOT));
		 // пытаемся поменять права FTP
		   if (!@ftp_site ($ftp_connect, "chmod " .$prava." " .FTP_HOST_ROOT .$path_file_tmp)){
				@ftp_quit($ftp_connect);
			 return false;
		 }
		 ftp_quit($ftp_connect);
	}

 return true;
}
}

function check_ftp_bd_error(){
   global $Error_Ftp_Connect,$ERROR_BASE_DATA,$ftp_Connect_glob;

  ftp_connect_();
// s($ERROR_BASE_DATA);
	 if (!empty($ERROR_BASE_DATA) || !empty($Error_Ftp_Connect) || (!empty($_POST['module'])&& $_POST['module']=='error_bd_ftp')){
	  // send_error($_SESSION['kernel']['module']);
	if (!empty($_POST['module']) && $_POST['module']=='error_bd_ftp'){
		$form=poste('form');
	   if (!empty($ERROR_BASE_DATA)){
			$ERROR_BASE_DATA = '';
   if (false===$dsn=@mysql_connect($form['server_name_bd'],$form['user_bd'],$form['pass_bd'])){
	   $ERROR_BASE_DATA = 'Ваши введенные данные были проверены, но они не решили проблему, повторите позже, когда будет доступен серевер БД или повторите ввод';
   }
if (!$ERROR_BASE_DATA && !@mysql_select_db($form['name_bd'], $dsn)){
	 $ERROR_BASE_DATA .= 'Ваши введенные данные были проверены, но они не решили проблему, повторите позже, когда будет доступен серевер БД или повторите ввод';
}
}
if (!empty($Error_Ftp_Connect)){
	$Error_Ftp_Connect='';
	$ftp_Connect_glob = ftp_connect_($form['server_ftp'],$form['user_ftp'],$form['pass_ftp'],$form['port_ftp']);
}
if ($ERROR_BASE_DATA || $Error_Ftp_Connect){
	   mess($Error_Ftp_Connect.'<br />'.$ERROR_BASE_DATA,'error_bd_ftp','error_bd_ftp');
}
	if (!empty($form['server_ftp'])){
	write_define('FTP_SERVER',$form['server_ftp'], 0);
	write_define('FTP_USER_NAME',$form['user_ftp'], 0);
	write_define('FTP_USER_PASS',$form['pass_ftp'], 0);
	write_define('FTP_HOST_ROOT',$form['root_ftp'],0);
	write_define('FTP_PORT',$form['port_ftp'], 0);
	$path_file = ROOT_A .'config/access.bas';
	//chmod_($path_file, 444);
	}
	  //запишем переменніе БД
	if (!empty($form['server_name_bd'])){
	write_define('DB_HOST',$form['server_name_bd']);
	write_define('DB_USER',$form['user_bd']);
	write_define('DB_PASS',$form['pass_bd']);
	write_define('DB_NAME',$form['name_bd']);
	}
	//  mess('Соединение прошло успешно, можете продолжать работу!','redirect','parts');
	 Ajax(array('return_content_bool'=>'false','content'=>'*', 'module'=>'home','close_'=>'1','java_script'=>'','action'=>'redirect','MESS_AJAX'=>'Соединение прошло успешно, можете продолжать работу!'));

		exit;
	}

	$mTegsTextGlob['submenu'] = array(
	'save' => array('module' => 'error_bd_ftp', 'action' => 'error_bd_ftp', 'post' => '', 'js_func' => 'send_error_bd_ftp()'),
	);
	$mTegsTextGlob['submenu'] = submenu($mTegsTextGlob['submenu']);
//	include_once 'html/error_bd_ftp.html';
	if (!empty($_POST['ajax_method'])){
		$admin_html_login = ob_get_contents();
		ob_clean();
	Ajax(array('content' => '*',
					'submenu' => $mTegsTextGlob['submenu'],
					'module' => 'error_bd_ftp',
					'action' => 'error_bd_ftp',
					'content_body' => $admin_html_login,
					'close_' => '1',
					'java_script' => '',
					'return_content_bool' => '1'
					));
	}
	exit;
}

}
function curr_value($curr) {
   switch ($curr) {
	  case 1:
		   return 'грн';
		break;
	  case 2:
		   return 'usd';
		break;
		case 3:
			return 'eur';
		break;
   }
	return;
}

function get_values_modul_spis($modul_id,$tabl_ind) {
	global $language;
   if ($modul_id && $tabl_ind){
	   $sql='select * from `'.T_MOD_FIELS_SPIS.'` where module='.$modul_id.' and  table_module="'.$tabl_ind.'"';
	   $aval=db_list($sql);
	   $arez=array();
	   foreach ($aval as $key => $value) {
		   if (!empty($value['table_spis'])){
			   $sql = 'SELECT name_'.$language.' as name, id   FROM `bs_spr-'.$value['table_spis'].'_clz` where active=1 order by name_'.$language;
				$arez[$value['field']] = db_list($sql);
		   }elseif(!empty($value['table_vnesh'])){
			   $lang=!empty($value['lang']) ? '_'.$language :'';
			   $sql = 'SELECT '.$value['field_vnesh'].$lang.' as name, id   FROM `'.PREF.$value['table_vnesh'].'_clz` where active=1 order by '.$value['field_vnesh'].$lang;
				$arez[$value['field']] = db_list($sql);

		   }
	   }
	   return $arez;
   }
   return false;

}
function date_for_firebird_format($date) {
	//  s($date);
	//	return $date;
// c такой 2019-01-26 вот  так выводит 26.01.2019
	return substr($date,8,2).'.' .substr($date,5,2).'.'.substr($date,0,4);
}
function date_for_sql_format($date) {
  //  s($date);
  //	return $date;
// вот  так выводит 2019-01-26
	return substr($date,6,4).'-' .substr($date,3,2).'-' .substr($date,0,2);
}
// роздать все права указной папке и вложеным папкам и файлам рекурсивно
function chmod_R($path, $perm) {

  $handle = opendir($path);
  while ( false !== ($file = readdir($handle)) ) {
	if ( ($file !== "..") ) {
	  @chmod($path . "/" . $file, $perm);
	  if ( !is_file($path."/".$file) && ($file !== ".") )
		chmod_R($path . "/" . $file, $perm);
	}
  }
  closedir($handle);

}
// пример использования данной функции
/*$path = $_SERVER["QUERY_STRING"];

if ( $path{0} != "/" )
  $path = $_SERVER["DOCUMENT_ROOT"] . "/" . $path;

chmod_R($path, 0777);
echo $path;  */
function arr_html_form($name_arr,$aform,$cnt=2,$razd='-') {
	$str='('.$name_arr.')';
	for($i=1;$i<=$cnt;$i++){
	   $str.=$razd.'([^'.$razd.'\n]+)';
	   //$str.=$razd.'(.+?)';
	}
	$atemp=array();
	//send_error($str);
	//$str='(tovs)-([^-\n]+)-([^-\n]+)';
	 $str_rez='';
   if (!empty($aform)){
	foreach ($aform as $key => $value) {
	  if (preg_match('#'.$str.'#is',$key, $atmp)){
		  $st='$'.$atmp[1];
		   for($i=2;$i<=$cnt+1;$i++){
				$st.='[\''.$atmp[$i].'\']';
			}
		   $str_rez.=$st.'="'.$value.'"'.";\n";
	  }
   }
   }
   $str_rez .=' $atemp= (!empty($'.$name_arr.')) ? $'.$name_arr.' : "";';
   eval($str_rez);
   //send_error(p($atemp,1).$str_rez );

	return $atemp;
}
/*
РОзписуем права:
 1 - общий доступ к модулю, просмотр списка list
 2 - добавление нового элемента             add
 3 - редактирование элемента                edit
 4 - сохранение данных                      save
 5 - удаление элемента                      del
 6 - настройка модуля                       nastr
 7 - зарезервировано
 8 - зарезервировано
 9 - зарезервировано
 10 - зарезервировано
*/
function get_access_admin($action='list',$dop_module=false,$module=false,$id_user=1) {
	global $user_admin;
	if (!empty($user_admin) && $user_admin['user_rule']<2){
		return true;
	}
   $dostup=false;
   $module=($module ? $module : ($_SESSION['kernel']['module']?$_SESSION['kernel']['module']:false));
   $id_user=($id_user ? $id_user : ($_SESSION['kernel']['module']?$_SESSION['kernel']['module']:false));
	  $sql = 'SELECT access FROM `' .T_ACCESS_MODUL .'` where mname="'.$module.'" and sotr_id='.$user_admin['user_id'] .' and action=' .($dop_module ? ' "'.$dop_module.'"' : '""').' limit 1  ';
 $mod_access = db_field($sql,'access');
 //send_error($sql.'**');
 if ($mod_access){
  switch ($action) {
	 case 'list':
		 $dostup = ($mod_access[0] ? 1 : 0);
	   break;
	 case 'add':
		  $dostup = ($mod_access[1] ? 1 : 0);
	   break;
	 case 'edit':
		   $dostup = ($mod_access[2] ? 1 : 0);
	   break;
	  case 'save':
			$dostup = ($mod_access[3] ? 1 : 0);
		   break;
	  case 'del':
		   $dostup = ($mod_access[4] ? 1 : 0);
		   break;
	  case 'nastr':
			$dostup = ($mod_access[5] ? 1 : 0);
	   break;
	   default:
		  $dostup = 0;
  }
}
  if (!$dostup){
	  mess(MESS_NO_ACCESS,$_SESSION['kernel']['action_prev'],$_SESSION['kernel']['module_prev']);
	/*AjaxXML(array('return_content_bool'=>'false','module'=>$_SESSION['kernel']['module_prev'],'close_'=>'1','java_script'=>'','action'=>$_SESSION['kernel']['action_prev'],'MESS_AJAX'=>MESS_NO_ACCESS)); */
}
	return $dostup;
}
// по спискам функция
//  $var_vuv  1- выводим все простые справочники список, 2 - выводим по номеру $num название списка, 3 выводим список значений списка в массив, 4 - выводим с внешних модулей все формы
function get_spis_list($num=0, $var_vuv=1, $list=0) {
	global $language;
	switch ($var_vuv) {
	   case 1:
		   $sql = 'select name_'.$language.' as name,default_values,id from `'.T_SPRLIST.'` where module_id=0 and active=1' ;
		   $form=db_list($sql);
		   return $form;
		 break;
	   case 2:
			$sql = 'select name_'.$language.' as name,default_values from `'.T_SPRLIST.'` where id='.$num. ' limit 1' ;
			$form=db_row($sql);
		   return array($form['name'],$form['default_values']);
		 break;
	   case 3:

		 break;
	  case 4:
			$sql = 'select id, name_'.$language.' as name, (select name_'.$language.' as name from '.T_MODULES.' m where f.module=m.id) as mname  from `' .T_FORMMASTER .'` f  order by module, mname';
			send_error($sql);
		   $form=db_list($sql);
		   $mn='';
		   foreach ($form as $key => $value) {
			  if ($mn!=$value['mname']){
				  $form[$key]['level']=0;
			  }else{
			  $form[$key]['level']=1;
			  }
			  $mn=$value['mname'];
		   }
				 return $form;
		 break;
	}

	return;
}
/*
 функции возвращют дерево массив
 -----------------------------------------------------
*/
  // функция выводит в один уровень отсортированое дерево   (упрощенный вариант для вывода)
  //  $skip_elem - пропускаем какой-то элемент например в принадлежности сам себя и потомков исключить
	function get_tree_level($tree_arr, $level=1, $id_parent=0,$skip_elem=0) {
	   $tree_vuv=array();

	   foreach ($tree_arr as $key => $value) {
		  if (!$skip_elem || $value['id']!=$skip_elem){
		      if (!empty($value['level']))
			$tree_vuv[$value['level']][$value['id']]=$value;
            else
			$tree_vuv[1][$value['id']]=$value;
		  }
	   }
	 return  get_tree_level_($tree_vuv,$level, $id_parent);

  }
 function get_tree_level_($tree_arr, $level=1, $id_parent=0) {
	   $result_a=array();
	  if (!empty($tree_arr[$level])){
		   foreach ($tree_arr[$level] as $key => $value) {
			  if (!$id_parent || $id_parent==$value['pid']){
			  $result_a[$key]=$value;

			 if (array_key_exists($level+1, $tree_arr)){
			   $result_a= $result_a+ get_tree_level_($tree_arr, $level+1, $value['id']);

			 }
		  }
		  }
	  }
	  return $result_a;

  }

  //==============================================================
  /*
	$parts_parent_id - новый id родителя
	$parent_id - старый id родителя
	$id - id выбранного элемена
	$level - уровень выбранного элемента
	$table - таблица в которой живет выбранный элемент
  */
 function prenad_razdel($parts_parent_id=0,$parent_id=0,$id=0,$level=1,$table=''){
  if ($parts_parent_id!=$parent_id){
	// ищем  в потомках и находим последний элемент
	$sql = 'SELECT sort,level FROM `' .$table .'` WHERE pid=' .$parts_parent_id  .' ORDER by sort desc LIMIT 1';
	$sort_tree_ = db_row($sql);

		$sort_tree_id=$sort_tree_['sort'];
		$level_=$sort_tree_['level'];
  // есть дети в родителя
  if ($sort_tree_id){
	$sort_tree_id++ ;
}else{
	// если потомков нет, то ищем уровень нового родителя и увеличиваем на 1
	$level_=db_field('select level from `'.$table .'` where id='.$parts_parent_id,'level')+1;
	$sort_tree_id = 1;
}
$lev_diff=$level_-$level;
// возвращаем всех потомков выбранного элемента
$list_child=get_tree_level(db_list('select * from `'.$table.'` order by sort'),$level+1,$id);
if (!empty($list_child)){
	// меняем всем потомкам уровень
foreach ($list_child as $key => $value) {
	 db_query('update `'.$table.'` set
			   level=level+'.$lev_diff.'
			   where id='.$value['id'].'
		');
}
}
// меняем уровень и порядок (последний) выбранного элемента
db_query('UPDATE `'.$table.'` set
		sort = '.$sort_tree_id.',
		level='.$level_.'
WHERE id='.$id);
}
}

// возвращает имя таблицы с модулей таблицы
function get_table_name($module_name) {
	global $language;
	 $table_name='';
	 $tmp= db_row('select table_name,lang_type from `'.T_MODULES.'` where mname="'.$module_name.'"');
	 if (!empty($tmp)){
	 switch ($tmp['lang_type']) {
		case 1:
			// $table_name = PREF.$tmp['table_name'].'_lz_'.$language;
			 $table_name = PREF.$tmp['table_name'];
		  break;
		case 2:
			  $table_name = PREF.$tmp['table_name'].'_clz';
		  break;
		case 3:
			 $table_name = PREF.$tmp['table_name'].'_s';
		  break;
	  case 4:
			$table_name = PREF.$tmp['table_name'].'_lbz';
		  break;
	 }
	 }
	return $table_name;
}
//=============================================================
//--------------- функкция копирования файлов с разными проверками и фтп на локальном серваке
function cp($file_in,$file_out) {
      global $Error_Ftp_Connect,$ftp_Connect_glob;
 // убираем корневой  путь к сайту
 if (!is_file($file_in)){
    if (is_dir($file_in)){
        if (!create_dir($file_in)){
            send_error('Нельзя создать каталог '.$file_in.' err_num=C1');
         return 'Нельзя создать каталог '.$file_in.' err_num=C1';
      } 
    }
    send_error('Файл '.$file_in. ' есть не корректным файлом или не существует. смысла копировать его нет  err_num=C0');
    return 'Файл '.$file_in. ' есть не корректным файлом или не существует. смысла копировать его нет err_num=C0' ;
 }
 //$file_out = preg_replace('#' .ROOT .'#i', "", $file_out);
 // получаем каталоги
 $dir_mas = pathinfo($file_out);
 if (empty($dir_mas['dirname'])){
    send_error('Не корректен путь к катлогу. (' .$file_out .') Проверте правильность передаваемого пути err_num=C2');
               return 'Не корректен путь к катлогу. (' .$file_out .') Проверте правильность передаваемого пути err_num=C2';
 }
 // если нету розширения в пути, то добавляем последний элемент, как каталог (потому что в основном у всех файлах должно быть розширение)
 if (empty($dir_mas['extension'])){
    $file_in_name = basename($file_in);
    $file_out .=(substr($file_out,-1)=='/' ? '' : '/').  $file_in_name;
 }
 if (!is_dir($dir_mas['dirname'])){
      if (!create_dir($dir_mas['dirname'])){
              send_error('Нельзя создать каталог '.$dir_mas['dirname'].' err_num=C3');
          return 'Нельзя создать каталог '.$dir_mas['dirname'].' err_num=C3';
      } 
 }
 if (is_writable($dir_mas['dirname'])){
 if (!copy($file_in,$file_out)){
    send_error('ошибка копирования файла "'.$file_in.'" в "'.$file_out.'" err_num=C4');
    return 'ошибка копирования файла "'.$file_in.'" в "'.$file_out.'" err_num=C4';
 }
 }else {
     send_error('ошибка копирования файла "'.$file_in.'" в "'.$file_out.'" НЕТ ПРАВ НА ЗАПИСЬ err_num=C5');
    
  return 'ошибка копирования файла "'.$file_in.'" в "'.$file_out.'" НЕТ ПРАВ НА ЗАПИСЬ err_num=C5';
 }
    return false;
 
}
//======================================================
#  Функция записи пременных в текстовый файл
// $mlStrFile - путь к файлу в котором находится константа
// $Const_name - имя константы 
// $mlValue - Переменная
// Тип переменной $TypeVir:
// $ini_block это переменая нужна когда тип 4 запись в ini файл это запись нужного блока потому что бываю тпеременные в разных блокахс одинаковыми именами например блок это [Beta_ver_Gr_compani] 

function write_const($mlStrFile,$Const_name,$mlValue,$text=''){
   $_str='';
   if ((file_exists($mlStrFile) && is_file($mlStrFile)) || $text){
 $_str=$text ? $text : file_get_contents($mlStrFile);
     // изминеям константу версии админки
   //send_error($_str);
 
 if (preg_match('#define *\( *[\'"]' .$Const_name .'[\'"][^;]+#i',$_str)){
            // Переменная типа define("IDVERSIA", 24122004);
              $_str = preg_replace ('#define *\( *[\'"]' .$Const_name .'[\'"][^;]+#i',
                  'define(\'' .$Const_name .'\', \'' .$mlValue .'\')',
                  $_str);
 }else{
   preg_match('#^.*(<\?.*)\?>.*#is',$_str,$aIni);
   $_str=(!empty($aIni[1]) ? $aIni[1]."\r\ndefine('" .$Const_name ."', '" .$mlValue ."');\r\n?>": $_str);
 }
 if (!$text){
    list($fp)= read_file($mlStrFile,2);
    if ($fp!==false){
        write_file($fp, $_str);
     }
 }
   }
     return $_str;
}
// функция отправляет через сокет по 80 порту пост запрос а также файл и принимает ответ
// возвразает ответ от сервера
function send_post_socket($aVar,$host='',$post_script='',$file_path='',$name_file='file_send') {
    $head=$buf = '';
    $host=(!empty($host)) ? $host: 'basilcompany.org.ua';
   $socket = fsockopen($host, 80, $errno, $errstr, 30);
//если fsockopen вернула false, то завершаем работу 
//скрипта и выводим текст 
//и номер ошибки

if(!$socket){ send_error("Сокет ошибка подключения $errstr($errno)");
   $answer = 'Ошибка подключения к серверу';
}else{
   //разделитель
$boundary = md5(uniqid(time()));
/*собираем часть с файлом:
сначала разделитель\r\n
со следующей строки заголовки\r\n
потом пустая строка\r\n
после чего сам файл\r\n
*/
$file=$vars='';
if ($file_path && file_exists($file_path) && is_file($file_path)){

$file = "--$boundary\r\n".
"Content-Disposition: form-data; name=\"$name_file\";".
" filename=\"$file_path\"\r\n".
"Content-Type: ".get_mimetype($file_path)."\r\n".
"Content-Transfer-Encoding: binary\r\n\r\n";
$file.= file_get_contents($file_path);
$file.="\r\n";
}

if (!empty($aVar)){
   foreach ($aVar as $key => $value) {
    $vars .=  "--$boundary\r\nContent-Disposition: '.
'form-data; name=\"$key\"\r\n\r\n".
urlencode($value)."\r\n";
   }
}
   $head = "POST /$post_script HTTP/1.1\r\n";   
   $head .= "Host: $host\r\n";  
   $head .= "User-agent:Opera 1000.00\r\n";  
   $head .= "Connection: close\r\n";  
   //$head .= "Content-Type: application/x-www-form-urlencoded\r\n";  
   $head .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";  
   $head .= "Content-length: ".(strlen($file)+strlen($vars))."\r\n";  
      //типы принимаемых данных. */* 
//означает, что принимаем все типы данных

   $head .= "Accept:*/*\r\n\r\n";  


//fwrite($socket,"--$boundary--\r\n"); 
//send_error($head.$file.$vars."--$boundary--\r\n\r\n");
   // отправляем
   fwrite($socket, $head.$file.$vars."--$boundary--\r\n\r\n");
   //теперь читаем и выводим ответ
   $head='';
   // если наступит, то со след. байта начнется документ
   while(!feof($socket)){
      $h=fgets($socket,2048);
      if ($h!="\r\n"){
         $head.=$h;
      }else break;
   }
$buf='';
   // читаем документ в переменную
   while(!feof($socket)) $buf.=fread($socket,2048);

fclose($socket);
 
}   
     return array($head,$buf);
 }
 // функция обрабатывает ответ от сокетов и возвращает 2 масива
 //один постов значение=переменная и массив с одного элемента содержимое файла
   function answer_socket($head,$content){
     $aPost = array();
     $aFile='';
         if (preg_match('#200 OK#i',$head)){
            if (preg_match('#boundary=([^\s]+)#is',$head,$aHead)){
               $boundary=!empty($aHead[1]) ? $aHead[1] : '';
            }else{
               send_error('Нету разделителя boundary');
               return array(false,false);
            }
            
         if ($content){
              // определим все переменные пост
              $aFile=preg_replace('#^(?:.*?)Content-Disposition:\s*form-data;\s*filename="(.*?)"\r\n\r\n(.*)\r\n--'.$boundary.'--\r\n\r\n.*$#is',"\\2",$content);
              preg_match_all('#\s+Content-Disposition:\s*form-data\s*;\s*name="([^"]+)"\r\n\r\n(.*?)\s+--'.$boundary.'#is',$content,$aContens);
            if (!empty($aContens[1])){
               foreach ($aContens[1] as $key => $value) {
                  //if ($aContens[1][$key]=='name'){
                     $aPost[$value]=urldecode($aContens[2][$key]);
                  //}/*else{
                   //  $aFile=$aContens[3][$key];
                  //}*/
               }
               unset($aContens);
               return array($aPost,$aFile);
            }else{
               return array(false,false);
            }
          /*list($fp)= read_file(ROOT_A.'temp_bas/'.basename($value),2);
    if ($fp!==false){
        write_file($fp, $content);
     }*/
     }
      }else{
         send_error('Передача завершилась не удачой');
         return array(false,false);
      } 
  }
  // отправка ответа серверу который запросил данные 
  // первый парметр массив переменных типа пост
  // 2 параметр путь к файлу полный на этом сервере для отправки
   function send_answer_socket($aPost,$file_path){  
    $vars='';
 //разделитель    
$boundary = md5(uniqid(time()));
header("Content-Type: multipart/form-data; boundary=$boundary");  
   
 //header("Content-Disposition: attachment; filename=".basename($file_path));
foreach ($aPost as $key => $value) {
  $vars .=  "--$boundary\r\n".
      "Content-Disposition: form-data; name=\"$key\"\r\n\r\n".
urlencode($value)."\r\n";
}
if ($file_path && file_exists($file_path) && is_file($file_path)){
$path_otn = str_replace(ROOT,'',$file_path);
$file = "--$boundary\r\n".
"Content-Disposition: form-data; filename=\"".$path_otn."\"\r\n\r\n";
$file.= (file_get_contents($file_path));
$file.="\r\n";
}
echo $vars.$file."--$boundary--\r\n\r\n";

    exit;
 } 
  function get_mimetype($file='') {
   $file = str_replace(ROOT,'',$file);
   
        $ct['htm'] = 'text/html';
        $ct['html'] = 'text/html';
        $ct['txt'] = 'text/plain';
        $ct['asc'] = 'text/plain';
        $ct['bmp'] = 'image/bmp';
        $ct['gif'] = 'image/gif';
        $ct['jpeg'] = 'image/jpeg';
        $ct['jpg'] = 'image/jpeg';
        $ct['jpe'] = 'image/jpeg';
        $ct['png'] = 'image/png';
        $ct['ico'] = 'image/vnd.microsoft.icon';
        $ct['mpeg'] = 'video/mpeg';
        $ct['mpg'] = 'video/mpeg';
        $ct['mpe'] = 'video/mpeg';
        $ct['qt'] = 'video/quicktime';
        $ct['mov'] = 'video/quicktime';
        $ct['avi']  = 'video/x-msvideo';
        $ct['wmv'] = 'video/x-ms-wmv';
        $ct['mp2'] = 'audio/mpeg';
        $ct['mp3'] = 'audio/mpeg';
        $ct['rm'] = 'audio/x-pn-realaudio';
        $ct['ram'] = 'audio/x-pn-realaudio';
        $ct['rpm'] = 'audio/x-pn-realaudio-plugin';
        $ct['ra'] = 'audio/x-realaudio';
        $ct['wav'] = 'audio/x-wav';
        $ct['css'] = 'text/css';
        $ct['zip'] = 'application/zip';
        $ct['pdf'] = 'application/pdf';
        $ct['doc'] = 'application/msword';
        $ct['bin'] = 'application/octet-stream';
        $ct['exe'] = 'application/octet-stream';
        $ct['class']= 'application/octet-stream';
        $ct['dll'] = 'application/octet-stream';
        $ct['xls'] = 'application/vnd.ms-excel';
        $ct['ppt'] = 'application/vnd.ms-powerpoint';
        $ct['wbxml']= 'application/vnd.wap.wbxml';
        $ct['wmlc'] = 'application/vnd.wap.wmlc';
        $ct['wmlsc']= 'application/vnd.wap.wmlscriptc';
        $ct['dvi'] = 'application/x-dvi';
        $ct['spl'] = 'application/x-futuresplash';
        $ct['gtar'] = 'application/x-gtar';
        $ct['gzip'] = 'application/x-gzip';
        $ct['js'] = 'application/x-javascript';
        $ct['swf'] = 'application/x-shockwave-flash';
        $ct['tar'] = 'application/x-tar';
        $ct['xhtml']= 'application/xhtml+xml';
        $ct['au'] = 'audio/basic';
        $ct['snd'] = 'audio/basic';
        $ct['midi'] = 'audio/midi';
        $ct['mid'] = 'audio/midi';
        $ct['m3u'] = 'audio/x-mpegurl';
        $ct['tiff'] = 'image/tiff';
        $ct['tif'] = 'image/tiff';
        $ct['rtf'] = 'text/rtf';
        $ct['wml'] = 'text/vnd.wap.wml';
        $ct['wmls'] = 'text/vnd.wap.wmlscript';
        $ct['xsl'] = 'text/xml';
        $ct['xml'] = 'text/xml';
        list($name,$extension)=explode('.',$file);
           $type = 'text/html';
         //  echo $extension;
        if (!empty($extension)){
         
        if (!empty($ct[strtolower($extension)])) {
           $type = $ct[strtolower($extension)];
        }
        }
        return $type;
    }
 function get_mimetype_file($file='',$mime='') {
   $file = str_replace(ROOT,'',$file);
   
        $ct['htm'] = 'text/html';
        $ct['html'] = 'text/html';
        $ct['txt'] = 'text/plain';
        $ct['asc'] = 'text/plain';
        $ct['bmp'] = 'image/bmp';
        $ct['gif'] = 'image/gif';
        $ct['jpeg'] = 'image/jpeg';
        $ct['jpg'] = 'image/jpeg';
        $ct['jpe'] = 'image/jpeg';
        $ct['png'] = 'image/png';
        $ct['ico'] = 'image/vnd.microsoft.icon';
        $ct['mpeg'] = 'video/mpeg';
        $ct['mpg'] = 'video/mpeg';
        $ct['mpe'] = 'video/mpeg';
        $ct['qt'] = 'video/quicktime';
        $ct['mov'] = 'video/quicktime';
        $ct['avi']  = 'video/x-msvideo';
        $ct['wmv'] = 'video/x-ms-wmv';
        $ct['mp2'] = 'audio/mpeg';
        $ct['mp3'] = 'audio/mpeg';
        $ct['rm'] = 'audio/x-pn-realaudio';
        $ct['ram'] = 'audio/x-pn-realaudio';
        $ct['rpm'] = 'audio/x-pn-realaudio-plugin';
        $ct['ra'] = 'audio/x-realaudio';
        $ct['wav'] = 'audio/x-wav';
        $ct['css'] = 'text/css';
        $ct['zip'] = 'application/zip';
        $ct['pdf'] = 'application/pdf';
        $ct['doc'] = 'application/msword';
        $ct['bin'] = 'application/octet-stream';
        $ct['exe'] = 'application/octet-stream';
        $ct['class']= 'application/octet-stream';
        $ct['dll'] = 'application/octet-stream';
        $ct['xls'] = 'application/vnd.ms-excel';
        $ct['ppt'] = 'application/vnd.ms-powerpoint';
        $ct['wbxml']= 'application/vnd.wap.wbxml';
        $ct['wmlc'] = 'application/vnd.wap.wmlc';
        $ct['wmlsc']= 'application/vnd.wap.wmlscriptc';
        $ct['dvi'] = 'application/x-dvi';
        $ct['spl'] = 'application/x-futuresplash';
        $ct['gtar'] = 'application/x-gtar';
        $ct['gzip'] = 'application/x-gzip';
        $ct['js'] = 'application/x-javascript';
        $ct['swf'] = 'application/x-shockwave-flash';
        $ct['tar'] = 'application/x-tar';
        $ct['xhtml']= 'application/xhtml+xml';
        $ct['au'] = 'audio/basic';
        $ct['snd'] = 'audio/basic';
        $ct['midi'] = 'audio/midi';
        $ct['mid'] = 'audio/midi';
        $ct['m3u'] = 'audio/x-mpegurl';
        $ct['tiff'] = 'image/tiff';
        $ct['tif'] = 'image/tiff';
        $ct['rtf'] = 'text/rtf';
        $ct['wml'] = 'text/vnd.wap.wml';
        $ct['wmls'] = 'text/vnd.wap.wmlscript';
        $ct['xsl'] = 'text/xml';
        $ct['xml'] = 'text/xml';
        list($name,$extension)=explode('.',$file);
           $type = 'text/html';
         //  echo $extension;
        if (!empty($extension)){
         
        if (!empty($ct[strtolower($extension)])) {
           $type = $ct[strtolower($extension)];
        }
        }
        return $type;
    }
//добавляет и проверяет колонку в таблицу
function set_feald_new($table,$field,$type='VARCHAR(255)',$default=null)
    {
        $isFeald = db_row("show columns FROM `".$table."` where `Field` = '".$field."'");
        if (empty($isFeald)) { //ЕСЛИ ПОЛЯ ЕЩЕ НЕТ ДОБАВЛЯЕМ ЕГО В СТРУКТУРУ ТАБЛИЦЫ
            db_query('ALTER TABLE `' . $table .
                '` ADD COLUMN `'.$field.'` '.$type.' NULL '.($default<>null ?  ' DEFAULT '.$default : ''));
        }

    } 
    
// замена или алтенатива mysql_real_escape_string
 function sql_valid($data) { 
  $data = str_replace("\\", "\\\\", $data); 
  $data = str_replace("'", "\'", $data); 
  $data = str_replace('"', '\"', $data); 
  $data = str_replace("\x00", "\\x00", $data); 
  $data = str_replace("\x1a", "\\x1a", $data); 
  $data = str_replace("\r", "\\r", $data); 
  $data = str_replace("\n", "\\n", $data); 
  return($data);  
 }       
function myEach($array) {
    foreach ($array as $key => $value) {
        return [$key, $value];
    }  
 }
function s($message, $level = 'info', $logDir = 'error') {
	// Преобразуем message в строку, если это массив или объект
	if (is_array($message) || is_object($message)) {
		$message = print_r($message, true);
	}

	$level = strtoupper($level);
	$timestamp = date('Y-m-d H:i:s');
	$date = date('Y-m-d');

	// Убедимся, что папка существует
	if (!is_dir($logDir)) {
		mkdir($logDir, 0777, true);
	}

	$logFile = rtrim($logDir, '/\\') . "/log_$date.log";

	// Определим, откуда вызвана функция
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
	$file = $backtrace['file'] ?? 'unknown file';
	$line = $backtrace['line'] ?? 'unknown line';

	$logEntry = "[$timestamp] [$level] ($file:$line) $message" . PHP_EOL;
	file_put_contents($logFile, $logEntry, FILE_APPEND);
}
// функция для прогресс бара
// prc - процент отправляемый аяксом
function progressBar($prc=0,$text_title='',$module='',$action='',$content=' '){
	// s('tyt=31==act='.$_SESSION['kernel']['action']);
	Ajax(array('content' =>$content,
		'module' => ($module ? $module : (!empty($_SESSION['kernel']['module']) ? $_SESSION['kernel']['module'] : 'home')),
		'message_user' => '',
		'action' => ($action ? $action : (!empty($_SESSION['kernel']['action']) ? $_SESSION['kernel']['action'] : 'home')),
		'close_' => (($prc>=100) ? 1 : 0),
		'java_script' => 'progresbar('.$prc.',"'.$text_title.'")',
		'return_content_bool' => 'false'
	));
}
// получеам список для фильтра
function get_select($id_spis,$name_vibor,$id,$name_field,$data_id,$name_all)
{
	$sql = 'SELECT *, value as name FROM `' . T_SPRLIST_VALUES .
		'` where id_spis='.$id_spis.' and active=1   ORDER by name';
	$aProstSpr = db_list($sql);
	// $aProstSpr[0]=['id'=>0,'id_spis'=>$id_spis,'name'=>$name_vibor,'active'=>0];
	// $aProstSpr= array_merge($aProstSpr,$aProstSpr_);
	$sSpis = '<div class="col_flo_left"><select   class="form-select w-auto"    tabindex="5"  name=form['.$name_field.']" id="'.$id.'">
    <option value="0">'.$name_all.'</option>
    ';$selected='';
	foreach ($aProstSpr as $elem)
	{
		if (!empty($data_id))
			$selected= $elem['id']==$data_id ? 'selected="selected"' : '';
		$sSpis.='<option '.$selected.' value="'.$elem['id'].'" >'.$elem['name'].'</option>';

	}
	$sSpis.=  '</select></div>';
	return $sSpis;
}
// cсокращает полное ФИО до Литвин Стефан Петрович до Литвин С.П.
function full_name_to_short ($full_name, $format="A b. c.") {
	$words = explode(" ", $full_name);
	$format_keys = array("A", "B", "C");
	$short_name = $format;
	foreach ($format_keys as $index => $word) {
		if (!empty($words[$index])){
			$short_name = str_replace($word, $words[$index], $short_name);
			$short_name = str_replace(mb_strtolower($word), mb_substr($words[$index], 0, 1, 'UTF-8'), $short_name);

		}
	}

	return $short_name;
}
?>
