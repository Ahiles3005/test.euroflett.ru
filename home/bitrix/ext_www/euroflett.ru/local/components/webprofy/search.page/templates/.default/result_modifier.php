<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["TAGS_CHAIN"] = array();
if($arResult["REQUEST"]["~TAGS"])
{
	$res = array_unique(explode(",", $arResult["REQUEST"]["~TAGS"]));
	$url = array();
	foreach ($res as $key => $tags)
	{
		$tags = trim($tags);
		if(!empty($tags))
		{
			$url_without = $res;
			unset($url_without[$key]);
			$url[$tags] = $tags;
			$result = array(
				"TAG_NAME" => htmlspecialcharsex($tags),
				"TAG_PATH" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url)), array("tags")),
				"TAG_WITHOUT" => $APPLICATION->GetCurPageParam((count($url_without) > 0 ? "tags=".urlencode(implode(",", $url_without)) : ""), array("tags")),
			);
			$arResult["TAGS_CHAIN"][] = $result;
		}
	}
}

// Получаем связанные элементы

// Для начала берём весь список инфоблоков
$arIblocks = array();
foreach ($arResult['SEARCH'] as $key => $value) {
	if($value['MODULE_ID'] == 'iblock'){
		$arIblocks[$value['PARAM2']] = true;
	}
}
$arIblocks = array_keys($arIblocks);

// Инфоблоки типа "Каталог"
$arIblocksCatalog = array();
CModule::IncludeModule("catalog");
foreach ($arIblocks as $id) {
	$isCatalog = CCatalog::GetByID($id);
	if(is_array($isCatalog) && $isCatalog['OFFERS'] == "N") {
		$arIblocksCatalog[$id] = array(
			"SECTIONS" => array(),
			"ELEMENTS" => array()
		);
		foreach ($arResult['SEARCH'] as $key => $value) {
			if($value['MODULE_ID'] == 'iblock' && $value['PARAM2'] == $id){
				if(substr($value['ITEM_ID'],0,1) == 'S'){
					$arIblocksCatalog[$id]['SECTIONS'][] = intval(substr($value['ITEM_ID'],1));
				} else {
					$arIblocksCatalog[$id]['ELEMENTS'][] = intval($value['ITEM_ID']);
				}
			}
		}
	}
}

$arResult['CATALOG'] = $arIblocksCatalog;

?>