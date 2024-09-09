<? if (!check_bitrix_sessid())
	return; ?>
<?
$MODULE_ID = "webprofy.pseudosection";
$MLANG = "WP_PSEUDOSECTION_";

DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$MODULE_ID."/install/admin"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

$errors = false;

if (!array_key_exists("savedata", $_REQUEST) || $_REQUEST["savedata"] != "Y"){
	$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$MODULE_ID."/install/mysql/uninstall.sql"));
}

if ($errors === false){
	echo CAdminMessage::ShowNote(GetMessage($MLANG."UNINSTALL_COMPLETE"));
	COption::RemoveOption($MODULE_ID);
	CAgent::RemoveModuleAgents($MODULE_ID);
	UnRegisterModule($MODULE_ID);
	UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $MODULE_ID, "WPPseudosection", "OnBuildGlobalMenuHandler");

	UnRegisterModuleDependences("main", "OnUserTypeBuildList", $MODULE_ID, "WPPseudosectionProperty", "GetUserTypeDescription");
	UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionAdd", $MODULE_ID, "WPPseudosectionProperty", "updateUserField");
	UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", $MODULE_ID, "WPPseudosectionProperty", "updateUserField");
}else{
	for ($i = 0; $i < count($errors); $i++)
		$alErrors .= $errors[$i]."<br>";
	echo CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage($MLANG."UNINSTALL_ERROR"), "DETAILS" => $alErrors, "HTML" => true));
}
?>

<form action="<? echo $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<? echo LANG ?>">
	<input type="submit" name="" value="<? echo GetMessage("MOD_BACK") ?>">
</form>