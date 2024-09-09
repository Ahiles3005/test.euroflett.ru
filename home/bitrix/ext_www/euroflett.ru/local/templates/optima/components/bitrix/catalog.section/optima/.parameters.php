<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arTemplateParameters['WP_H2_TITLE'] = array(
	'PARENT' => 'TEMPLATE_SETTINGS',
	'NAME' => GetMessage("WP_H2_TITLE"),
	'TYPE' => 'STRING'
);

$arTemplateParameters['WP_SHOW_ALL_LINK'] = array(
	'PARENT' => 'TEMPLATE_SETTINGS',
	'NAME' => GetMessage("WP_SHOW_ALL_LINK"),
	'TYPE' => 'CHECKBOX'
);

$arTemplateParameters['WP_ALL_LINK_TEXT'] = array(
	'PARENT' => 'TEMPLATE_SETTINGS',
	'NAME' => GetMessage("WP_ALL_LINK_TEXT"),
	'TYPE' => 'STRING'
);

$arTemplateParameters['WP_ALL_LINK'] = array(
	'PARENT' => 'TEMPLATE_SETTINGS',
	'NAME' => GetMessage("WP_ALL_LINK"),
	'TYPE' => 'STRING',
	'DEFAULT' => '/catalog/'
);


if (isset($arCurrentValues['IBLOCK_ID']) && 0 < intval($arCurrentValues['IBLOCK_ID']))
{
	$arAllPropList = array();
	$arFilePropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$arListPropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$arHighloadPropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$rsProps = CIBlockProperty::GetList(
		array('SORT' => 'ASC', 'ID' => 'ASC'),
		array('IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], 'ACTIVE' => 'Y')
	);
	while ($arProp = $rsProps->Fetch())
	{
		$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];
		if ('' == $arProp['CODE'])
			$arProp['CODE'] = $arProp['ID'];
		$arAllPropList[$arProp['CODE']] = $strPropName;
		if ('F' == $arProp['PROPERTY_TYPE'])
			$arFilePropList[$arProp['CODE']] = $strPropName;
		if ('L' == $arProp['PROPERTY_TYPE'])
			$arListPropList[$arProp['CODE']] = $strPropName;
		if ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && CIBlockPriceTools::checkPropDirectory($arProp))
			$arHighloadPropList[$arProp['CODE']] = $strPropName;
	}
}

$arTemplateParameters["WP_ITEMLIST_CATEGORY_LINE"] = array(
	'PARENT' => 'WEBPROFY_OPTIMA_SETTINGS',
	'NAME' => GetMessage('WP_ITEMLIST_CATEGORY_LINE'),
	'TYPE' => 'LIST',
	'VALUES' => $arAllPropList
);

?>
