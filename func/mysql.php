<?php
//slog('tyty21');
$ERROR_BASE_DATA = '';
$db_host= DB_HOST;
$db_user= DB_USER;
$db_pass=DB_PASS;
$db_name=DB_NAME;
if (false===$dsn=@mysqli_connect($db_host,$db_user,$db_pass)){
    if (!$dsn){

        $ERROR_BASE_DATA .= 'Cервер базы данных "'.$db_host.'" недоступен, или некорректные логин, или пароль!<br />';
     //   slog('tytyERROR '.$ERROR_BASE_DATA);
        //send_error('Cервер базы данных "'.DB_HOST.'" недоступен, или некорректные логин, или пароль!<br />', E_USER_ERROR);
      //  s($ERROR_BASE_DATA);
    }

}
//$charset = mysql_character_set_name($dsn);
//printf ("current character set is %s\n", $charset);
//slog('tyty22');
if (!$ERROR_BASE_DATA && !mysqli_select_db($dsn,$db_name)){
    $ERROR_BASE_DATA .= 'База данных "'.$db_name.'" недоступная!';
    // send_error('База данных "'.DB_NAME.'" недоступная!', E_USER_ERROR);
}
//echo $ERROR_BASE_DATA;exit;
function db_query($sql_query){
    global $dsn;
    if(is_array($sql_query)){    //Выполним запросы
        mysqli_query($dsn,"BEGIN");    //Начнём транзакцию
        for($i=0;$i<count($sql_query);$i++)    //Цикл по всем запросам
        {   $ret=mysqli_query($dsn,$sql_query[$i]);
            if(!$ret)    //Произошла ошибка -
            {    mysqli_query($dsn,"ROLLBACK");        //откат транзакции
                if (ERROR_DB){

                    user_error('Ошибочный запрос: <b> '.$sql_query.'</b> <br />', E_USER_ERROR);

                }

                return false;
            }
        }
        mysqli_query($dsn,"COMMIT");    //Подтверждение транзакции
        return true;
    }
    if(is_string($sql_query)){

        $_temp_mysql=mysqli_query($dsn,$sql_query);
        if ($_temp_mysql){
            return $_temp_mysql;
        }
        // send_error($_temp_mysql);
        if (ERROR_DB){
            wLog('Ошибочный запрос: <b> '.$sql_query.'</b> <br />Код: '.type_mysql_error(mysqli_errno ($dsn)).' Подробно: '.mysqli_error($dsn), 'error','error');
            $err=type_mysql_error(mysqli_errno ($dsn));

        }
        return false;
    }
    return false;
}

function db_list($sql_query){
    global $dsn;

    //user_error('11Ошибочный запрос: '.dirname(__FILE__), E_USER_ERROR);
    $result=db_query($sql_query);
    if ($result)
    {       $masiv= array();
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $masiv[]=$row;
        }

        // $_SESSION['page']=0;
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
        if (empty($row[$field]))
        {
            return false;
        } else
            return $row[$field];
    }

    else
        return false;
}

function db_affected_rows()
{        return mysqli_affected_rows();
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
