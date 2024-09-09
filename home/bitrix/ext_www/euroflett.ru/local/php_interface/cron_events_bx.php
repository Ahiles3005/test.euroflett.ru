<?
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
 
AgentCheckFeedXB();
sleep(3*60);
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_dietrich.php')) {
	
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_BC.php';
	AgentCheckFeedBC();
}

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");
?>