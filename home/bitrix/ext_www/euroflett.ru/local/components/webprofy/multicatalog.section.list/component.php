<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock")){ 
	die();
} 
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

if ($this->StartResultCache(false)) { 
	CModule::IncludeModule("catalog");

	// Получаем список инфоблоков типа Каталог
	$db = CIBlock::GetList(array("SORT" => "ASC"), array("ACTIVE" => "Y"));
	$arIblocks = array();
	while($arIblock = $db->Fetch()){
		$isCatalog = CCatalog::GetByID($arIblock['ID']);
		if(is_array($isCatalog) && $isCatalog['OFFERS'] == "N")
			$arIblocks[$arIblock['ID']] = $arIblock;
	}

	global $USER_FIELD_MANAGER;

	$arParents = array();

	foreach ($arIblocks as $key => $arIblock) {
		$arUserFields = $USER_FIELD_MANAGER->GetUserFields("ASD_IBLOCK", $arIblock["ID"]);
		if ($arUserFields['UF_PARENT']["VALUE"]>0) {
			$rsParent = CUserFieldEnum::GetList(array(), array("ID" => $arUserFields['UF_PARENT']["VALUE"]));
			if($arParent = $rsParent->GetNext()){
				if(!is_array($arParents[$arParent['XML_ID']]))
					$arParents[$arParent['XML_ID']] = $arParent;
				
				if(!is_array($arParents[$arParent['XML_ID']]['IBLOCKS']))
					$arParents[$arParent['XML_ID']]['IBLOCKS'] = array();
				$arIblock["URL"] = str_replace(array("#SITE_DIR#/", "#IBLOCK_CODE#"), array(SITE_DIR, $arIblock['CODE']), $arIblock["LIST_PAGE_URL"]);
				$arParents[$arParent['XML_ID']]['IBLOCKS'][] = $arIblock;
			}
			
			/*additional parents*/
			foreach ($arUserFields["UF_PARENT_NEW"]["VALUE"] as $parent) {
				$rsParent_new = CUserFieldEnum::GetList(array(), array("ID" => $parent));
				if($arParent_new = $rsParent_new->GetNext()){
					$arParents[$arParent_new['XML_ID']]['IBLOCKS'][] = $arIblock;
				}
			}
		}
	}

	$arResult = array(
		"SECTIONS" => array()
	);
	foreach ($arParents as $key => $arParent) {

		$arResult["SECTIONS"][] = array(
			"NAME" => $arParent["VALUE"],
			"CODE" => $arParent["XML_ID"],
			"SORT" => $arParent["SORT"],
			"URL" => SITE_DIR.$arParent["XML_ID"]."/",
			"SECTIONS" => $arParent["IBLOCKS"]
		);
	}

	$this->IncludeComponentTemplate();
}
?>