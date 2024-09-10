<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

if (!isset($arParams['LINE_ELEMENT_COUNT']))
	$arParams['LINE_ELEMENT_COUNT'] = 3;
$arParams['LINE_ELEMENT_COUNT'] = (int)$arParams['LINE_ELEMENT_COUNT'];
if (2 > $arParams['LINE_ELEMENT_COUNT'] || 5 < $arParams['LINE_ELEMENT_COUNT'])
	$arParams['LINE_ELEMENT_COUNT'] = 3;

$arParams['TEMPLATE_THEME'] = (string)($arParams['TEMPLATE_THEME']);
if ('' != $arParams['TEMPLATE_THEME'])
{
	$arParams['TEMPLATE_THEME'] = preg_replace('/[^a-zA-Z0-9_\-\(\)\!]/', '', $arParams['TEMPLATE_THEME']);
	if ('site' == $arParams['TEMPLATE_THEME'])
	{
		$arParams['TEMPLATE_THEME'] = COption::GetOptionString('main', 'wizard_eshop_adapt_theme_id', 'blue', SITE_ID);
	}
	if ('' != $arParams['TEMPLATE_THEME'])
	{
		if (!is_file($_SERVER['DOCUMENT_ROOT'].$this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css'))
			$arParams['TEMPLATE_THEME'] = '';
	}
}
if ('' == $arParams['TEMPLATE_THEME'])
	$arParams['TEMPLATE_THEME'] = 'blue';

if (!empty($arResult['ITEMS']))
{
	$arEmptyPreview = false;
	$strEmptyPreview = $this->GetFolder() . '/images/no_photo.png';
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . $strEmptyPreview))
	{
		$arSizes = getimagesize($_SERVER['DOCUMENT_ROOT'] . $strEmptyPreview);
		if (!empty($arSizes))
		{
			$arEmptyPreview = array(
				'SRC' => $strEmptyPreview,
				'WIDTH' => (int)$arSizes[0],
				'HEIGHT' => (int)$arSizes[1]
			);
		}
		unset($arSizes);
	}
	unset($strEmptyPreview);

	$arSKUPropList = array();
	$arSKUPropIDs = array();
	$arSKUPropKeys = array();
	$boolSKU = false;
	$strBaseCurrency = '';
	$boolConvert = isset($arResult['CONVERT_CURRENCY']['CURRENCY_ID']);

	//
	$skuPropList = array(); // array("id_catalog" => array(...))
	$skuPropIds = array(); // array("id_catalog" => array(...))
	$skuPropKeys = array(); // array("id_catalog" => array(...))

	if (!$boolConvert)
		$strBaseCurrency = CCurrency::GetBaseCurrency();

	$catalogs = array();
	foreach($arResult['CATALOGS'] as $catalog)
	{
		$offersCatalogId = (int)$catalog['OFFERS_IBLOCK_ID'];
		$offersPropId = (int)$catalog['OFFERS_PROPERTY_ID'];
		$catalogId = (int)$catalog['IBLOCK_ID'];
		$sku = false;
		if($offersCatalogId > 0 && $offersPropId > 0)
			$sku = array("IBLOCK_ID" => $offersCatalogId, "SKU_PROPERTY_ID" => $offersPropId, "PRODUCT_IBLOCK_ID" => $catalogId);


		if (!empty($sku) && is_array($sku))
		{
			$skuPropList[$catalogId] = CIBlockPriceTools::getTreeProperties(
				$sku,
				$arParams['OFFER_TREE_PROPS'][$offersCatalogId],
				array(
					'PICT' => $arEmptyPreview,
					'NAME' => '-'
				)
			);

			$needValues = array();
			CIBlockPriceTools::getTreePropertyValues($skuPropList[$catalogId], $needValues);

			$skuPropIds[$catalogId] = array_keys($skuPropList[$catalogId]);
			if (!empty($skuPropIds[$catalogId]))
				$skuPropKeys[$catalogId] = array_fill_keys($skuPropIds[$catalogId], false);
		}
	}

	$arNewItemsList = array();

	foreach ($arResult['ITEMS'] as $key => $arItem)
	{
		$arItem['CATALOG_QUANTITY'] = (
		0 < $arItem['CATALOG_QUANTITY'] && is_float($arItem['CATALOG_MEASURE_RATIO'])
			? floatval($arItem['CATALOG_QUANTITY'])
			: intval($arItem['CATALOG_QUANTITY'])
		);
		$arItem['CATALOG'] = false;
		$arItem['LABEL'] = false;
		if (!isset($arItem['CATALOG_SUBSCRIPTION']) || 'Y' != $arItem['CATALOG_SUBSCRIPTION'])
			$arItem['CATALOG_SUBSCRIPTION'] = 'N';

		// Item Label Properties
		$itemIblockId = $arItem['IBLOCK_ID'];
		$propertyName = isset($arParams['LABEL_PROP'][$itemIblockId]) ? $arParams['LABEL_PROP'][$itemIblockId] : false;

		if ($propertyName && isset($arItem['PROPERTIES'][$propertyName]))
		{
			$property = $arItem['PROPERTIES'][$propertyName];

			if (!empty($property['VALUE']))
			{
				if ('N' == $property['MULTIPLE'] && 'L' == $property['PROPERTY_TYPE'] && 'C' == $property['LIST_TYPE'])
				{
					$arItem['LABEL_VALUE'] = $property['NAME'];
				}
				else
				{
					$arItem['LABEL_VALUE'] = (is_array($property['VALUE'])
						? implode(' / ', $property['VALUE'])
						: $property['VALUE']
					);
				}
				$arItem['LABEL'] = true;

				if (isset($arItem['DISPLAY_PROPERTIES'][$propertyName]))
					unset($arItem['DISPLAY_PROPERTIES'][$propertyName]);
			}
			unset($property);
		}
		// !Item Label Properties

		// item double images
		$productPictures = array(
			"PICT" => false,
			"SECOND_PICT" => false
		);

		if (isset($arParams['ADDITIONAL_PICT_PROP'][$itemIblockId]))
		{
			$productPictures = CIBlockPriceTools::getDoublePicturesForItem($arItem, $arParams['ADDITIONAL_PICT_PROP'][$itemIblockId]);
		}
		else
		{
			$productPictures = CIBlockPriceTools::getDoublePicturesForItem($arItem, false);
		}
		if (empty($productPictures['PICT']))
			$productPictures['PICT'] = $arEmptyPreview;
		if (empty($productPictures['SECOND_PICT']))
			$productPictures['SECOND_PICT'] = $productPictures['PICT'];
		$arItem['PREVIEW_PICTURE'] = $productPictures['PICT'];
		$arItem['PREVIEW_PICTURE_SECOND'] = $productPictures['SECOND_PICT'];
		$arItem['SECOND_PICT'] = true;
		$arItem['PRODUCT_PREVIEW'] = $productPictures['PICT'];
		$arItem['PRODUCT_PREVIEW_SECOND'] = $productPictures['SECOND_PICT'];
		// !item double images

		$arItem['CATALOG'] = true;
		if (!isset($arItem['CATALOG_TYPE']))
			$arItem['CATALOG_TYPE'] = CCatalogProduct::TYPE_PRODUCT;
		if (
			(CCatalogProduct::TYPE_PRODUCT == $arItem['CATALOG_TYPE'] || CCatalogProduct::TYPE_SKU == $arItem['CATALOG_TYPE'])
			&& !empty($arItem['OFFERS'])
		)
		{
			$arItem['CATALOG_TYPE'] = CCatalogProduct::TYPE_SKU;
		}
		switch ($arItem['CATALOG_TYPE'])
		{
			case CCatalogProduct::TYPE_SET:
				$arItem['OFFERS'] = array();
				$arItem['CATALOG_MEASURE_RATIO'] = 1;
				$arItem['CATALOG_QUANTITY'] = 0;
				$arItem['CHECK_QUANTITY'] = false;
				break;
			case CCatalogProduct::TYPE_SKU:
				break;
			case CCatalogProduct::TYPE_PRODUCT:
			default:
				$arItem['CHECK_QUANTITY'] = ('Y' == $arItem['CATALOG_QUANTITY_TRACE'] && 'N' == $arItem['CATALOG_CAN_BUY_ZERO']);
				break;
		}

		// Offers
		if ($arItem['CATALOG'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
		{
			$arSKUPropIDs = isset($skuPropIds[$arItem['IBLOCK_ID']]) ? $skuPropIds[$arItem['IBLOCK_ID']] : array();
			$arSKUPropList = isset($skuPropList[$arItem['IBLOCK_ID']]) ? $skuPropList[$arItem['IBLOCK_ID']] : array();
			$arSKUPropKeys = isset($skuPropKeys[$arItem['IBLOCK_ID']]) ? $skuPropKeys[$arItem['IBLOCK_ID']] : array();

			$arMatrixFields = $arSKUPropKeys;
			$arMatrix = array();

			$arNewOffers = array();
			$boolSKUDisplayProperties = false;
			$arItem['OFFERS_PROP'] = false;

			foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
			{
				$arRow = array();
				foreach ($arSKUPropIDs as $propkey => $strOneCode)
				{
					$arCell = array(
						'VALUE' => 0,
						'SORT' => PHP_INT_MAX,
						'NA' => true
					);

					if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]))
					{
						$arMatrixFields[$strOneCode] = true;
						$arCell['NA'] = false;
						if ('directory' == $arSKUPropList[$strOneCode]['USER_TYPE'])
						{
							$intValue = $arSKUPropList[$strOneCode]['XML_MAP'][$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']];
							$arCell['VALUE'] = $intValue;
						}
						elseif ('L' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
						{
							$arCell['VALUE'] = intval($arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE_ENUM_ID']);
						}
						elseif ('E' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
						{
							$arCell['VALUE'] = intval($arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']);
						}
						$arCell['SORT'] = $arSKUPropList[$strOneCode]['VALUES'][$arCell['VALUE']]['SORT'];
					}
					$arRow[$strOneCode] = $arCell;
				}
				$arMatrix[$keyOffer] = $arRow;


				$newOfferProps = array();
				if(!empty($arParams['PROPERTY_CODE'][$arOffer['IBLOCK_ID']]))
				{
					foreach($arParams['PROPERTY_CODE'][$arOffer['IBLOCK_ID']] as $propName)
						$newOfferProps[$propName] = $arOffer['DISPLAY_PROPERTIES'][$propName];
				}
				$arOffer['DISPLAY_PROPERTIES'] = $newOfferProps;

				$arOffer['CHECK_QUANTITY'] = ('Y' == $arOffer['CATALOG_QUANTITY_TRACE'] && 'N' == $arOffer['CATALOG_CAN_BUY_ZERO']);
				if (!isset($arOffer['CATALOG_MEASURE_RATIO']))
					$arOffer['CATALOG_MEASURE_RATIO'] = 1;
				if (!isset($arOffer['CATALOG_QUANTITY']))
					$arOffer['CATALOG_QUANTITY'] = 0;
				$arOffer['CATALOG_QUANTITY'] = (
				0 < $arOffer['CATALOG_QUANTITY'] && is_float($arOffer['CATALOG_MEASURE_RATIO'])
					? floatval($arOffer['CATALOG_QUANTITY'])
					: intval($arOffer['CATALOG_QUANTITY'])
				);
				$arOffer['CATALOG_TYPE'] = CCatalogProduct::TYPE_OFFER;
				CIBlockPriceTools::setRatioMinPrice($arOffer);

				$offerPictures = CIBlockPriceTools::getDoublePicturesForItem($arOffer, $arParams['ADDITIONAL_PICT_PROP'][$arOffer['IBLOCK_ID']]);
				$arOffer['OWNER_PICT'] = empty($offerPictures['PICT']);
				$arOffer['PREVIEW_PICTURE'] = false;
				$arOffer['PREVIEW_PICTURE_SECOND'] = false;
				$arOffer['SECOND_PICT'] = true;
				if (!$arOffer['OWNER_PICT'])
				{
					if (empty($offerPictures['SECOND_PICT']))
						$offerPictures['SECOND_PICT'] = $offerPictures['PICT'];
					$arOffer['PREVIEW_PICTURE'] = $offerPictures['PICT'];
					$arOffer['PREVIEW_PICTURE_SECOND'] = $offerPictures['SECOND_PICT'];
				}
				if ('' != $arParams['OFFER_ADD_PICT_PROP'] && isset($arOffer['DISPLAY_PROPERTIES'][$arParams['OFFER_ADD_PICT_PROP']]))
					unset($arOffer['DISPLAY_PROPERTIES'][$arParams['OFFER_ADD_PICT_PROP']]);
				$arNewOffers[$keyOffer] = $arOffer;
			}
			$arItem['OFFERS'] = $arNewOffers;

			$arUsedFields = array();
			$arSortFields = array();

			foreach ($arSKUPropIDs as $propkey => $strOneCode)
			{
				$boolExist = $arMatrixFields[$strOneCode];
				foreach ($arMatrix as $keyOffer => $arRow)
				{
					if ($boolExist)
					{
						if (!isset($arItem['OFFERS'][$keyOffer]['TREE']))
							$arItem['OFFERS'][$keyOffer]['TREE'] = array();
						$arItem['OFFERS'][$keyOffer]['TREE']['PROP_' . $arSKUPropList[$strOneCode]['ID']] = $arMatrix[$keyOffer][$strOneCode]['VALUE'];
						$arItem['OFFERS'][$keyOffer]['SKU_SORT_' . $strOneCode] = $arMatrix[$keyOffer][$strOneCode]['SORT'];
						$arUsedFields[$strOneCode] = true;
						$arSortFields['SKU_SORT_' . $strOneCode] = SORT_NUMERIC;
					}
					else
					{
						unset($arMatrix[$keyOffer][$strOneCode]);
					}
				}
			}
			$arItem['OFFERS_PROP'] = $arUsedFields;

			\Bitrix\Main\Type\Collection::sortByColumn($arItem['OFFERS'], $arSortFields);

			// Find Selected offer
			foreach($arItem['OFFERS']  as $ind => $offer)
				if($offer['SELECTED'])
				{
					$arItem['OFFERS_SELECTED'] = $ind;
					break;
				}

			$arMatrix = array();
			$intSelected = -1;
			$arItem['MIN_PRICE'] = false;
			foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
			{
				if (empty($arItem['MIN_PRICE']) && $arOffer['CAN_BUY'])
				{
					$intSelected = $keyOffer;
					$arItem['MIN_PRICE'] = (isset($arOffer['RATIO_PRICE']) ? $arOffer['RATIO_PRICE'] : $arOffer['MIN_PRICE']);
				}
				$arSKUProps = false;
				if (!empty($arOffer['DISPLAY_PROPERTIES']))
				{
					$boolSKUDisplayProperties = true;
					$arSKUProps = array();
					foreach ($arOffer['DISPLAY_PROPERTIES'] as &$arOneProp)
					{
						if ('F' == $arOneProp['PROPERTY_TYPE'])
							continue;
						$arSKUProps[] = array(
							'NAME' => $arOneProp['NAME'],
							'VALUE' => $arOneProp['DISPLAY_VALUE']
						);
					}
					unset($arOneProp);
				}

				$arOneRow = array(
					'ID' => $arOffer['ID'],
					'NAME' => $arOffer['~NAME'],
					'TREE' => $arOffer['TREE'],
					'DISPLAY_PROPERTIES' => $arSKUProps,
					'PRICE' => (isset($arOffer['RATIO_PRICE']) ? $arOffer['RATIO_PRICE'] : $arOffer['MIN_PRICE']),
					'SECOND_PICT' => $arOffer['SECOND_PICT'],
					'OWNER_PICT' => $arOffer['OWNER_PICT'],
					'PREVIEW_PICTURE' => $arOffer['PREVIEW_PICTURE'],
					'PREVIEW_PICTURE_SECOND' => $arOffer['PREVIEW_PICTURE_SECOND'],
					'CHECK_QUANTITY' => $arOffer['CHECK_QUANTITY'],
					'MAX_QUANTITY' => $arOffer['CATALOG_QUANTITY'],
					'STEP_QUANTITY' => $arOffer['CATALOG_MEASURE_RATIO'],
					'QUANTITY_FLOAT' => is_double($arOffer['CATALOG_MEASURE_RATIO']),
					'MEASURE' => $arOffer['~CATALOG_MEASURE_NAME'],
					'CAN_BUY' => $arOffer['CAN_BUY'],
					'BUY_URL' => $arOffer['~BUY_URL'],
					'ADD_URL' => $arOffer['~ADD_URL'],
				);
				$arMatrix[$keyOffer] = $arOneRow;
			}

			if (-1 == $intSelected)
				$intSelected = 0;
			if (!$arMatrix[$intSelected]['OWNER_PICT'])
			{
				$arItem['PREVIEW_PICTURE'] = $arMatrix[$intSelected]['PREVIEW_PICTURE'];
				$arItem['PREVIEW_PICTURE_SECOND'] = $arMatrix[$intSelected]['PREVIEW_PICTURE_SECOND'];
			}
			$arItem['JS_OFFERS'] = $arMatrix;
			//$arItem['OFFERS_SELECTED'] = $intSelected;
			$arItem['OFFERS_PROPS_DISPLAY'] = $boolSKUDisplayProperties;
		}

		if ($arItem['CATALOG'] && CCatalogProduct::TYPE_PRODUCT == $arItem['CATALOG_TYPE'])
		{
			CIBlockPriceTools::setRatioMinPrice($arItem, true);
		}

		if (!empty($arItem['DISPLAY_PROPERTIES']))
		{
			foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp)
			{
				if ('F' == $arDispProp['PROPERTY_TYPE'])
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
			}
		}
		$arItem['LAST_ELEMENT'] = 'N';
		$arNewItemsList[$key] = $arItem;
	}

	$arNewItemsList[$key]['LAST_ELEMENT'] = 'Y';
	$arResult['ITEMS'] = $arNewItemsList;
	$arResult['ITEMS'] = getCanonicalLink($arResult['ITEMS']);
	$arResult['SKU_PROPS'] = $skuPropList;
	$arResult['DEFAULT_PICTURE'] = $arEmptyPreview;
}

// Надпись над заголовком товара
// По-умолчанию сюда идёт название текущей категории, если не определено другое в настройках шаблона
// Содержимое надписи фильтруется из начала заголовка, чтобы не было задваивания
foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arResult['ITEMS'][$key]['WP_CATEGORY_LINE'] = $arResult['NAME']; // В Каталог ТОПе это не работает

	if($arParams['WP_ITEMLIST_CATEGORY_LINE'] != '' && isset($arItem['DISPLAY_PROPERTIES'][$arParams['WP_ITEMLIST_CATEGORY_LINE']])){
		$arResult['ITEMS'][$key]['WP_CATEGORY_LINE'] = $arItem['DISPLAY_PROPERTIES'][$arParams['WP_ITEMLIST_CATEGORY_LINE']]['DISPLAY_VALUE'];
	}
	if(stripos($arItem['NAME'], $arItem['WP_CATEGORY_LINE']) == 0){
		$arResult['ITEMS'][$key]['NAME'] = trim(substr($arItem['NAME'], strlen(strip_tags($arItem['WP_CATEGORY_LINE']))));
	}
}

// Disable price by flag

// Getting all used top groups
$arIds = array();
foreach ($arResult['ITEMS'] as $key => $arItem)
	$arIds[] = $arItem['ID'];

$db_groups = CIBlockElement::GetElementGroups($arIds, false, array("ID", "NAME", "IBLOCK_ID"));
$arSections = array();
while($ar_group = $db_groups->Fetch()){
	$arSections[$ar_group['ID']] = $ar_group;
}

global $USER_FIELD_MANAGER;

// Getting NavChains for each groups
$arUfs = array(); // caching;

foreach ($arSections as $key => $arSection) {
	$ob = CIBlockSection::GetNavChain(intval($arSection['IBLOCK_ID']), intval($arSection['ID']), array("ID", "NAME", "IBLOCK_ID"));
	$arSections[$key]['CHAIN'] = array();
	while($res = $ob->GetNext()){
		if(isset($arUfs[$res['ID']])){
			$arFields = $arUfs[$res['ID']];
		} else {
			$arFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_'.$arSection['IBLOCK_ID'].'_SECTION', $res['ID']);
			$arUfs[$res['ID']] = $arFields;
		}	
		$res['NO_PRICE'] = (isset($arFields['UF_NOPRICE']) && $arFields['UF_NOPRICE']['VALUE'] == "1");
		$arSections[$key]['CHAIN'][] = $res;
	}
}


foreach ($arResult['ITEMS'] as $key => $arItem) {

	$db_groups = CIBlockElement::GetElementGroups($arItem['ID'], false, array("ID", "NAME", "IBLOCK_ID"));
	$arItemSections = array();
	while($ar_group = $db_groups->Fetch()){
		$arItemSections[$ar_group['ID']] = $ar_group;
	}

	$noPrice = false;
	foreach ($arItemSections as $k => $arSection) {
		$chain = $arSections[$arSection['ID']]['CHAIN'];
		foreach ($chain as $c => $value) {
			$noPrice |= $value['NO_PRICE'];
		}
	}

	$noPrice |= $arItem['PROPERTIES']['NO_PRICE']['VALUE'];

	$arResult['ITEMS'][$key]['NO_PRICE'] = (bool) $noPrice;
	
	if(!($arItem['MIN_PRICE']['VALUE'] > 0)){
		$arResult['ITEMS'][$key]['NO_PRICE'] = true;
	}
	if($arResult['ITEMS'][$key]['NO_PRICE']){
		$arResult['ITEMS'][$key]['CAN_BUY'] = false;
	}

	if(!isset($arItem['PROPERTIES']['_BRAND'])){
		// Multicatalog top. Need to get additional properties
		$ob = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem["ID"], array(), array("CODE" => "_BRAND"));
		$brand = $ob->Fetch();
		$arItem['PROPERTIES']['_BRAND'] = $brand;
		$arResult['ITEMS'][$key]['PROPERTIES']['_BRAND'] = $brand;
	}

	// --------- GAGGENAU & IP -----------
	$arResult['ITEMS'][$key] = setNoPriceSpecific($arResult['ITEMS'][$key]);

}

?>

<?
 foreach($arResult["ITEMS"] as $key=>$arItem)
  {
   $renderImage = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']['ID'], Array("width" =>"240", "height" =>"201",BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 60 ));
    $arResult["ITEMS"][$key]['MIN_IMG']=$renderImage['src'];
  }
?>