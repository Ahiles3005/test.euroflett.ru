<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;
$arTypesEx = CIBlockParameters::GetIBlockTypes(array("-"=>" "));

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "catalog",
		),
		"SECTION_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("WPP_MULTICATALOG_SECTION_LIST_BYNAME_SECTION_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	)
);
?>