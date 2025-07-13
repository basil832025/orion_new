<?php
//phpinfo();
date_default_timezone_set('Europe/Kyiv');
require_once __DIR__ . '/config/access.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/config/const.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/config/const_admin.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/main_func.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/mysql.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/mysql.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/error_func.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/error_func.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/func/dop_func.php';  // íàëàøòóâàííÿ
require_once __DIR__ . '/config/const_db.php';  // íàëàøòóâàííÿ

require_once __DIR__ . '/modules/turnirs/action/raschet_shtraph.php';  // êëàññ øòğàôîâ


//echo ROOT;
set_time_limit(0); // íå îáìåæóºìî ÷àñ âèêîíàííÿ
ini_set('memory_limit', '512M'); // àáî '1024M' ÿêùî ïîòğ³áíî á³ëüøå

$startTime = microtime(true);


//sCron("?? Ïî÷àòîê âèêîíàííÿ âåëèêî¿ çàäà÷³...");
//s('cron');
try {
    $obAct = new Raschet_shtraphAction();
    $obAct->init(1);

    $duration = round(microtime(true) - $startTime, 2);
    //  sCron("? Çàâåğøåíî! ×àñ âèêîíàííÿ: {$duration} ñåê.");
} catch (Exception $e) {
    sCron("? Ïîìèëêà: " . $e->getMessage());
}