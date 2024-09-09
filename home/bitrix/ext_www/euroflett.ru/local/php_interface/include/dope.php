<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler("main", "OnAfterUserLogin", Array("ExtendedLogs", "OnAfterUserLoginHandler"));
$eventManager->addEventHandler("main", "OnAfterUserLogout", Array("ExtendedLogs", "OnAfterUserLogoutHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionAdd", Array("ExtendedLogs", "OnAfterIBlockSectionAddHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionUpdate", Array("ExtendedLogs", "OnAfterIBlockSectionUpdateHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionDelete", Array("ExtendedLogs", "OnAfterIBlockSectionDeleteHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementAdd", Array("ExtendedLogs", "OnAfterIBlockElementAddHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("ExtendedLogs", "OnAfterIBlockElementUpdateHandler"));
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementDelete", Array("ExtendedLogs", "OnAfterIBlockElementDeleteHandler"));

class ExtendedLogs
{
	const PATH = '/upload/logs/users/';

	public static function OnAfterUserLoginHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "1",
			"AUDIT_TYPE_ID" => "USER_LOGIN",
			"MODULE_ID" => "main",
			"ITEM_ID" => $arParams["USER_ID"]
		);
		if(COption::GetOptionString("main", "event_log_login", "N") !== "Y")
			CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterUserLogoutHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "1",
			"AUDIT_TYPE_ID" => "USER_LOGOUT",
			"MODULE_ID" => "main",
			"ITEM_ID" => $arParams["USER_ID"]
		);
		if(COption::GetOptionString("main", "event_log_logout", "N") !== "Y")
			CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockSectionAddHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_SECTION_ADD",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockSectionUpdateHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_SECTION_EDIT",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockSectionDeleteHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_SECTION_DELETE",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockElementAddHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_ELEMENT_ADD",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockElementUpdateHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_ELEMENT_EDIT",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	public static function OnAfterIBlockElementDeleteHandler($arParams)
	{
		$arFields = array(
			"SEVERITY" => "4",
			"AUDIT_TYPE_ID" => "IBLOCK_ELEMENT_DELETE",
			"MODULE_ID" => "iblock",
			"ITEM_ID" => $arParams["ID"],
			"DESCRIPTION" => serialize($arParams)
		);
		CEventLog::Add($arFields);
		self::writeToFile($arFields);
	}

	private static function writeToFile($arFields)
	{
		global $USER;
		$url = preg_replace("/(&?sessid=[0-9a-z]+)/", "", $_SERVER["REQUEST_URI"]);
		$SITE_ID = defined("ADMIN_SECTION") && ADMIN_SECTION==true ? false : SITE_ID;
		$time = new \Bitrix\Main\Type\DateTime();

		$arFields = array(
			"TIME" => $time->format("H:i:s d-m-Y"),
			"SEVERITY" => $arFields["SEVERITY"] ? $arFields["SEVERITY"]: "UNKNOWN",
			"AUDIT_TYPE_ID" => strlen($arFields["AUDIT_TYPE_ID"]) <= 0? "UNKNOWN": $arFields["AUDIT_TYPE_ID"],
			"MODULE_ID" => strlen($arFields["MODULE_ID"]) <= 0? "UNKNOWN": $arFields["MODULE_ID"],
			"ITEM_ID" => strlen($arFields["ITEM_ID"]) <= 0? "UNKNOWN": $arFields["ITEM_ID"],
			"REMOTE_ADDR" => $_SERVER["REMOTE_ADDR"],
			"USER_AGENT" => $_SERVER["HTTP_USER_AGENT"],
			"REQUEST_URI" => $url,
			"SITE_ID" => strlen($arFields["SITE_ID"]) <= 0 ? $SITE_ID : $arFields["SITE_ID"],
			"USER_ID" => is_object($USER) && ($USER->GetID() > 0)? $USER->GetID(): false,
			"GUEST_ID" => (isset($_SESSION) && array_key_exists("SESS_GUEST_ID", $_SESSION) && $_SESSION["SESS_GUEST_ID"] > 0? $_SESSION["SESS_GUEST_ID"]: false),
			"DESCRIPTION" => $arFields["DESCRIPTION"]
		);
		if ($arFields["AUDIT_TYPE_ID"] == "USER_LOGOUT" && !$arFields["USER_ID"]) $arFields["USER_ID"] = $arFields["ITEM_ID"];
		$path = \Bitrix\Main\Application::getDocumentRoot() . self::PATH;
		if (!is_dir($path)) mkdir($path, 0755, true);
		$path .= $arFields["USER_ID"] . '_' . $time->format('mY') . '.csv';
		if (file_exists($path)){
			$fp = fopen($path, "a+");
		} else {
			$fp = fopen($path, "w+");
			fputcsv($fp, array_keys($arFields), chr(9));
		}
		fputcsv($fp, $arFields, chr(9));
	}

	public static function DeleteOldFilesAgent($days = 61)
	{
		$deltime = time() - $days*24*60*60;
		$dir = \Bitrix\Main\Application::getDocumentRoot() . self::PATH;
		self::DeleteOldFilesDir($dir, $deltime);

		return __CLASS__ . '::DeleteOldFilesAgent('.$days.');';
	}

	private static function DeleteOldFilesDir($dir, $deltime)
	{
		$count = 0;
		$dir = new \Bitrix\Main\IO\Directory($dir);
		if ($dir->isExists()){
			foreach ($dir->getChildren() as $child) {
				if (is_a($child, 'Bitrix\Main\IO\Directory')) {
					if (self::DeleteOldFilesDir($child->getPath(), $deltime) > 0)
						$count++;
				} elseif (is_a($child, 'Bitrix\Main\IO\File')) {
					if ($child->getModificationTime() < $deltime) {
						$child->delete();
					} else $count++;
				}
			}
			if ($count==0) {
				$dir->delete();
			}
		}
		return $count;
	}
}

$arTime = localtime();
if ($arTime[2] == 0 && $arTime[1] <= 10 ) {
	ExtendedLogs::DeleteOldFilesAgent(0);
}