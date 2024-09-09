<?php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader; 

Loader::includeModule("highloadblock"); 
Loader::includeModule("catalog"); 
Loader::includeModule("sale"); 

use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;

$dataCategory = array(
	"Духовые шкафы"	=>	"Dukhovye shkafy",
	"Варочные панели"	=>	"Varochnye paneli",
	"Холодильники"	=>	"Kholodilniki",
	"Посудомоечные машины"	=>	"Posudomoechnye mashiny",
	"Стиральные машины"	=>	"Stiralnye mashiny",
	"Вытяжки"	=>	"Vytyazhki",
	"Микроволновые печи"	=>	"Mikrovolnovye pechi",
	"Пароварки"	=>	"Parovarki",
	"Кофемашины"	=>	"Kofemashiny",
	"Подогреватели"	=>	"Podogrevateli",
	"Комби-панели"	=>	"Kombi-paneli",
	"Вакууматоры"	=>	"Vacuumatori",
	"Варочные центры"	=>	"Varochnye tsentry",
	"Винные шкафы"	=>	"Vinnye shkafy",
	"Хьюмидоры"	=>	"Khyumidory",
	"Аксессуары"	=>	"Aksessuary",
	"Сушильные машины"	=>	"Sushilnye mashiny",
	"Сушильные шкафы"	=>	"Sushilnye shkafy",
	"Гладильные машины"	=>	"Gladilnye mashiny",
	"Системы по уходу за одеждой"	=>	"Sistemy po ukhodu za odezhdoy",
	"Измельчители"	=>	"Izmelchiteli",
	"Пылесосы"	=>	"Pylesosy",
	"Чайники"	=>	"Chayniki",
	"Блендеры"	=>	"Blendery",
	"Тостеры"	=>	"Tostery",
	"Миксеры"	=>	"Miksery"
);

// Собираем список инфоблоков
$iblockIds = [];
$iblockIdName = [];
$res = CIBlock::GetList(
	array(),
	array(
		'TYPE'			=> 'catalog', 
		'SITE_ID'		=> SITE_ID, 
		'ACTIVE'		=> 'Y', 
		"CNT_ACTIVE"	=> "Y",
	),
	true
);
while ($ar_res = $res->Fetch()) {
	if (isset($dataCategory[$ar_res['NAME']])) {
		$iblockIdName[$ar_res['ID']] = $dataCategory[$ar_res['NAME']];
	}
	$iblockIds[] = $ar_res['ID'];
}

$arSelect = array("ID", "SECTION_NAME", "IBLOCK_ID", "PROPERTY_GOOGLE_CATEGORY", "PROPERTY__BRAND");
$arFilter = array("IBLOCK_ID"=> $iblockIds, "CHECK_PERMISSIONS" => "N", "PROPERTY_GOOGLE_CATEGORY" => false);
$arOrder = array();
$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, array("nTopCount"=>300), $arSelect);
if (intval($rsItems->SelectedRowsCount())>0){
	while($obItem = $rsItems->GetNextElement()){
		$arItem = $obItem->GetFields();
		
		$brandItem = CIBlockElement::GetList(array(), array("IBLOCK_ID" => 9, "CHECK_PERMISSIONS" => "N", "ID" => $arItem['PROPERTY__BRAND_VALUE']), false, array(), array("NAME"));
		$brandItem = $brandItem->GetNextElement();
		
		echo $arItem['IBLOCK_ID'] . " - " . $arItem['ID']."\n";		
		if (isset($iblockIdName[$arItem['IBLOCK_ID']])) {
			$google_category = "Home > " . $iblockIdName[$arItem['IBLOCK_ID']] . " > " . $brandItem->fields['NAME'];
			CIBlockElement::SetPropertyValuesEx($arItem['ID'], $arItem['IBLOCK_ID'], [
				'GOOGLE_CATEGORY' => $google_category
			]);
		}
	}
}