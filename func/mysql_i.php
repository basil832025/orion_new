<?php

   
 $ERROR_BASE_DATA = '';
 $db_host=(!empty($_SESSION['url_site']) && !empty($bd_config_site[$_SESSION['url_site']]))? $bd_config_site[$_SESSION['url_site']]['DB_HOST'] : DB_HOST;
 $db_user=(!empty($_SESSION['url_site']) && !empty($bd_config_site[$_SESSION['url_site']]))? $bd_config_site[$_SESSION['url_site']]['DB_USER'] : DB_USER;
 $db_pass=(!empty($_SESSION['url_site']) && !empty($bd_config_site[$_SESSION['url_site']]))? $bd_config_site[$_SESSION['url_site']]['DB_PASS'] : DB_PASS;
 $db_name=(!empty($_SESSION['url_site']) && !empty($bd_config_site[$_SESSION['url_site']]))? $bd_config_site[$_SESSION['url_site']]['DB_NAME'] : DB_NAME;

    
  
if (false===$dsn=mysqli_connect($db_host,$db_user,$db_pass,$db_name))
{
    
        if (!$dsn){   
    $ERROR_BASE_DATA .= 'Cервер базы данных "'.$db_host.'" недоступен, или некорректные логин, или пароль!<br />';
    //echo('Cервер базы данных "'.DB_HOST.'" недоступен, или некорректные логин, или пароль!<br />');
}
   
}
if (!$ERROR_BASE_DATA && !mysqli_select_db($dsn,$db_name)){
     $ERROR_BASE_DATA .= 'База данных "'.$db_name.'" недоступная!';
  // send_error('База данных "'.DB_NAME.'" недоступная!', E_USER_ERROR); 
}
 //phpinfo();exit;
  
//==================================================================================
//==================================================================================
/*function escape($data){
	if (!get_magic_quotes_gpc()){
			$doc = $data;
			if (is_array($doc)){
					while( list($k,$v) = each ($doc))
							$doc[$k] = mysqli_escape_string(($v));
			}
			else $doc = mysqli_escape_string(($doc));
			return $doc;
	}
	else{
		return $data;
	}
}
*/
function db_query($sql_query)
{
 global $dsn;
if(is_array($sql_query)){    //Выполним запросы
        mysqli_query($dsn,"BEGIN");    //Начнём транзакцию
        for($i=0;$i<count($sql_query);$i++)    //Цикл по всем запросам
        {   $ret=mysqli_query($dsn,$sql_query[$i]);
            if(!$ret)    //Произошла ошибка -
            {    mysqli_query($dsn,"ROLLBACK");        //откат транзакции
               
                return false;
            }
        }
        mysqli_query($dsn,"COMMIT");    //Подтверждение транзакции
        return true;
    }
    if(is_string($sql_query)){ 
    //echo $sql_query;
    $_temp_mysql=mysqli_query($dsn,$sql_query);
    if ($_temp_mysql){
        return $_temp_mysql;
    }
   // send_error($_temp_mysql);
       return false;   
    }
    return false;   
}

function db_list($sql_query)
{
   global $dsn; 
  
   //user_error('11Ошибочный запрос: '.dirname(__FILE__), E_USER_ERROR);      
    $result=db_query($sql_query);
    if ($result)
           {       $masiv= array();
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) 
                {
                          $masiv[]=$row;   
                }
                    
          
        return  $masiv;
           } 
    else
        return false;        
    
}

 function db_row($sql_query)
{   global $dsn; 
    $ath=db_query($sql_query);
    if ($ath)
        return mysqli_fetch_assoc($ath);
    else
        return false;        
    
}
function db_field($sql_query, $field)
{
    global $dsn; 
    
    $ath=db_query($sql_query);
    if ($ath)
        {
            $row=mysqli_fetch_array($ath, MYSQLI_ASSOC);
             return $row[$field];  
        }   
        
    else
        return false;      
}
    
 function db_affected_rows()
{       
    return mysqli_affected_rows();
}   
function db_insert_id()
{   global $dsn;
    return mysqli_insert_id($dsn);
}
function type_mysql_error($kod){
    switch ($kod) {
       case 0:
         return false;
         break;
       case 1146:
            return 'Не существует таблицы! Проверте правильность имени или она удалена! Сообщите данную информацию разработчику!';
         break;
       case 1054:
            return 'Не существует колонки в таблице! Сообщите данную информацию разработчику!';
         break;
       case 1451:
            return 'Невозможно удалить данные, возможно, нарушаются связи с другими таблицами!';
         break;
         default: 
          return false;
         
    }
}
mysqli_query($dsn,"SET NAMES utf8;");

?>
