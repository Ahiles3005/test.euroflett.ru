<?
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true); 
define('CHK_EVENT', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);
define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_brandt.php')) {
	
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_brandt.php';
	AgentCheckFeedBrandt();
}
 

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");
?>