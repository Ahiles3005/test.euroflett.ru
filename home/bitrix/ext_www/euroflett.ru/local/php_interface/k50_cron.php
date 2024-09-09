<? // php /home/bitrix/ext_www/euroflett.ru/local/php_interface/k50_cron.php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
ini_set('memory_limit', '2256M');
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true); 
define('CHK_EVENT', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);
define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);
 
CModule::IncludeModule("iblock");
CModule::IncludeModule("main");
ExportToFileAdProXML();


require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");

 