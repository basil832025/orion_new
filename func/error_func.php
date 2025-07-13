<?php

// тут установленна пользовательская функция обработки ошибок
error_reporting(E_ALL);
// Обработка обычных ошибок
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return;

    $levels = [
        E_ERROR => 'error',
        E_WARNING => 'warning',
        E_PARSE => 'error',
        E_NOTICE => 'notice',
        E_CORE_ERROR => 'error',
        E_CORE_WARNING => 'warning',
        E_COMPILE_ERROR => 'error',
        E_COMPILE_WARNING => 'warning',
        E_USER_ERROR => 'error',
        E_USER_WARNING => 'warning',
        E_USER_NOTICE => 'notice',
        E_STRICT => 'notice',
        E_RECOVERABLE_ERROR => 'error',
        E_DEPRECATED => 'notice',
        E_USER_DEPRECATED => 'notice',
    ];

    $level = $levels[$errno] ?? 'error';
    $msg = "$errstr";
    wLog($msg, $level, 'error');

    // Вывести в браузер (опционально при разработке)
    // echo "<b>$level:</b> $errstr in <b>$errfile</b> on line <b>$errline</b><br>";
    return true; // предотвратить стандартную обработку
});

// Обработка необработанных исключений
set_exception_handler(function ($exception) {
    $message = "Uncaught Exception: " . $exception->getMessage() . "\n"
        . $exception->getFile() . ':' . $exception->getLine() . "\n"
        . $exception->getTraceAsString();

    wLog($message, 'error', 'error');
});

// Fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = "{$error['message']} in {$error['file']} on line {$error['line']}";
        wLog($msg, 'error', 'error');
    }
});
?>
