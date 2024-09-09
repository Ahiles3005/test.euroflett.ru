<?
// php /home/bitrix/ext_www/euroflett.ru/local/php_interface/cron_events_free_spase.php

// 1 9 * * * /usr/bin/php /home/bitrix/ext_www/euroflett.ru/local/php_interface/cron_events_free_spase.php


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
 
 
$s = shell_exec('df -h');
 

preg_match ( '~\/dev\/md125\s*[1-90.]{1,3}[GM]{1}\s*[1-90.]{1,3}[GM]{1}\s*[1-90.]{1,3}[GM]{1}\s*([1-90.]{1,3})~m' , $s , $matches);
 
if(!empty($matches[1])){
	
	$free_spase = (int)$matches[1]; //(int)$matches[1];
	if($free_spase >= 70){
	
		$arFields = [
		        'REPORT' => 'На сайте https://www.euroflett.ru/ занято ' . $free_spase . '%',
			// 'EMAILS' => 'smirnov.d@hardkod.ru'
		        'EMAILS' => 'svp@euroflett.ru, 89154202923@mail.ru, egorov.anton@hardkod.ru'
		    ];
	 	CEvent::Send("CHECK_FREE_SPASE", 's1', $arFields);
	}
}



require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");
?>