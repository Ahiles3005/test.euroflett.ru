<? if (!check_bitrix_sessid()) return; ?>

<?
$MODULE_ID = "webprofy.pseudosection";
$MLANG = "WP_PSEUDOSECTION_";

CopyDirFiles($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$MODULE_ID."/install/admin"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);


$errors = false;
$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$MODULE_ID."/install/mysql/install.sql"));


if ($errors === false){
	echo CAdminMessage::ShowNote(GetMessage($MLANG."INSTALL_COMPLETE_OK"));
	RegisterModule($MODULE_ID);

	RegisterModuleDependences("main", "OnUserTypeBuildList", $MODULE_ID, "WPPseudosectionProperty", "GetUserTypeDescription");
	RegisterModuleDependences("iblock", "OnBeforeIBlockSectionAdd", $MODULE_ID, "WPPseudosectionProperty", "updateUserField");
	RegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", $MODULE_ID, "WPPseudosectionProperty", "updateUserField");

	RegisterModuleDependences("main", "OnBuildGlobalMenu", $MODULE_ID, "WPPseudosection", "OnBuildGlobalMenuHandler");
}
else
{
	for ($i = 0; $i < count($errors); $i++)
		$alErrors .= $errors[$i]."<br/>";

	echo CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage($MLANG."INSTALL_ERROR"), "DETAILS" => $alErrors, "HTML" => true));
}
?>

<form action="<? echo $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<? echo LANG ?>">
	<input type="submit" name="" value="<? echo GetMessage($MLANG."INSTALL_BACK") ?>">

</form>