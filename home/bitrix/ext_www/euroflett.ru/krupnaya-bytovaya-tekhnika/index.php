<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каталог");

$arParams["SEF_FOLDER"] = "/krupnaya-bytovaya-tekhnika/";

$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_ID#/",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"compare" => "compare.php?action=COMPARE",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
	"action",
);


$SEF_URL_TEMPLATES = array(
			"sections" => "",
			"section" => "#SECTION_CODE_PATH#/",
			"element" => "#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
			"compare" => "compare/",
		);

$arVariables = array();

$engine = new CComponentEngine($this);
if (\Bitrix\Main\Loader::includeModule('iblock'))
{
	$engine->addGreedyPart("#SECTION_CODE_PATH#");
	$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
}
$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $SEF_URL_TEMPLATES);
$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

$componentPage = $engine->guessComponentPath(
	$arParams["SEF_FOLDER"],
	$arUrlTemplates,
	$arVariables
);

if(!$componentPage && isset($_REQUEST["q"]))
	$componentPage = "search";

$b404 = false;
if(!$componentPage)
{
	$componentPage = "sections";
	$b404 = true;
}

if($componentPage == "section")
{
	if (isset($arVariables["SECTION_ID"]))
		$b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
	else
		$b404 |= !isset($arVariables["SECTION_CODE"]);
}

if($b404 && $arParams["SET_STATUS_404"]==="Y")
{
	$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
	if ($folder404 != "/")
		$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
	if (substr($folder404, -1) == "/")
		$folder404 .= "index.php";

	if($folder404 != $APPLICATION->GetCurPage(true))
		CHTTP::SetStatus("404 Not Found");
}

CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
$arResult = array(
	"FOLDER" => $arParams["SEF_FOLDER"],
	"URL_TEMPLATES" => $arUrlTemplates,
	"VARIABLES" => $arVariables,
	"ALIASES" => $arVariableAliases
);






//dump($arResult);

$iblockCode = $arResult['VARIABLES']['SECTION_CODE_PATH'];
if(mb_strpos($iblockCode, "/")!==false){
	$arIblockCode = explode('/', $iblockCode);
	$iblockCode = $arIblockCode[0];
}

$res = CIBlock::GetList(array("SORT"=>"ASC"), array('CODE'=>$iblockCode));
if($ar_res = $res->Fetch()){
   // echo $ar_res['ID'];
    $iblockId = $ar_res['ID'];
}

?>

<?
$APPLICATION->SetPageProperty("title", "Каталог крупной бытовой техники");
?>
<?
$itemsOnPage = 21;
$sortBy = 'CATALOG_QUANTITY';
$sortByOrder = 'DESC';
$sortBy2 = 'SHOWS';
$sortByOrder2 = 'DESC';

if(intval($_REQUEST['showby'])>0){
	$itemsOnPage = intval($_REQUEST['showby']);
}

if(htmlspecialcharsbx($_REQUEST['sort'])!=''){
	switch (htmlspecialcharsbx($_REQUEST['sort'])){
		case 'qty':
			$sortBy = 'CATALOG_QUANTITY';
			$sortBy2 = 'SORT';
			$sortByOrder = 'ASC';
			$sortByOrder2 = 'ASC';
			break;
		case 'price':
			$sortBy = 'CATALOG_PRICE_2';
			$sortBy2 = 'SORT';
			$sortByOrder = 'ASC';
			$sortByOrder2 = 'ASC';
			break;
		case 'new':
			$sortBy = 'DATE_ACTIVE_FROM';
			$sortBy2 = 'SORT';
			$sortByOrder = 'ASC';
			$sortByOrder2 = 'ASC';
			break;
	}
}

if(htmlspecialcharsbx($_REQUEST['order'])!=''){
	if (htmlspecialcharsbx($_REQUEST['order'])=='desc'){
		$sortByOrder = 'DESC';
		$sortByOrder2 = 'DESC';
	}else{
		$sortByOrder = 'ASC';
		$sortByOrder2 = 'ASC';
	}
}
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog", 
	".default", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => $iblockId,
		"HIDE_NOT_AVAILABLE" => "N",
		"TEMPLATE_THEME" => "blue",
		"COMMON_SHOW_CLOSE_POPUP" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"DETAIL_SHOW_MAX_QUANTITY" => "N",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_COMPARE" => "Сравнение",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Под заказ",
		"DETAIL_USE_VOTE_RATING" => "Y",
		"DETAIL_USE_COMMENTS" => "Y",
		"DETAIL_BRAND_USE" => "N",
		"SEF_MODE" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"SET_STATUS_404" => "Y",
		"SET_TITLE" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"ADD_ELEMENT_CHAIN" => "Y",
		"USE_ELEMENT_COUNTER" => "Y",
		"USE_SALE_BESTSELLERS" => "Y",
		"USE_FILTER" => "Y",
		"FILTER_VIEW_MODE" => "VERTICAL",
		"USE_REVIEW" => "Y",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"USE_COMPARE" => "Y",
		"PRICE_CODE" => array(
			0 => "MSK",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"CONVERT_CURRENCY" => "Y",
		"BASKET_URL" => "/cart/",
		"USE_PRODUCT_QUANTITY" => "Y",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "Y",
		"PRODUCT_PROPERTIES" => array(
		),
		"HIDDEN_PROPERTIES" => array(
			"INDEX",
			"MODEL",
			"_BRAND",
			"_UNID",
			"BLOG_POST_ID",
			"BLOG_COMMENTS_CNT",
			"CANONICAL_SECTION",
			"DESCTITLE",
			"YM_COUNTRY",
			"YM_WARRANTY",
			"YM_MODEL",
			"YM_VENDORCODE",
			"YM_VENDOR",
			"YM_TYPEPREFIX",
			"RELATED",
			"COLORGROUP",
			"COMMENT",
			"_PRICE_CVT_ID",
			"FEATURES",
			"DETAILDESCRIPTION",
			"_AVAILABLE",
			"_PREORDER",
		),
		"USE_COMMON_SETTINGS_BASKET_POPUP" => "Y",
		"TOP_ADD_TO_BASKET_ACTION" => "ADD",
		"SECTION_ADD_TO_BASKET_ACTION" => "ADD",
		"DETAIL_ADD_TO_BASKET_ACTION" => "BUY",
		"SHOW_TOP_ELEMENTS" => "Y",
		"TOP_ELEMENT_COUNT" => "9",
		"TOP_LINE_ELEMENT_COUNT" => "3",
		"TOP_ELEMENT_SORT_FIELD" => "sort",
		"TOP_ELEMENT_SORT_ORDER" => "asc",
		"TOP_ELEMENT_SORT_FIELD2" => "id",
		"TOP_ELEMENT_SORT_ORDER2" => "desc",
		"TOP_PROPERTY_CODE" => array(
			0 => "",
			1 => "BRAND",
			2 => "",
		),
		"SECTION_COUNT_ELEMENTS" => "N",
		"SECTION_TOP_DEPTH" => "2",
		"SECTIONS_VIEW_MODE" => "Y",
		"SECTIONS_VIEW_TEMPLATE" => "TILE",
		"SECTIONS_SHOW_PARENT_NAME" => "Y",
		"PAGE_ELEMENT_COUNT" => $itemsOnPage,
		"LINE_ELEMENT_COUNT" => "3",
		"ELEMENT_SORT_FIELD" => $sortBy,
		"ELEMENT_SORT_ORDER" => $sortByOrder,
		"ELEMENT_SORT_FIELD2" => $sortBy2,
		"ELEMENT_SORT_ORDER2" => $sortByOrder2,
		"LIST_PROPERTY_CODE" => array(
			0 => "",
			1 => "BRAND",
			2 => "",
		),
		"INCLUDE_SUBSECTIONS" => "Y",
		"LIST_META_KEYWORDS" => "-",
		"LIST_META_DESCRIPTION" => "-",
		"LIST_BROWSER_TITLE" => "-",
		"DETAIL_PROPERTY_CODE" => array(
			0 => "",
			1 => "BRAND",
			2 => "MN_TYPE",
			3 => "MN_KIND",
		),
		"DETAIL_META_KEYWORDS" => "-",
		"DETAIL_META_DESCRIPTION" => "-",
		"DETAIL_BROWSER_TITLE" => "-",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
		"DETAIL_DISPLAY_NAME" => "Y",
		"DETAIL_DETAIL_PICTURE_MODE" => "POPUP",
		"DETAIL_ADD_DETAIL_TO_SLIDER" => "Y",
		"DETAIL_DISPLAY_PREVIEW_TEXT_MODE" => "E",
		"LINK_IBLOCK_TYPE" => "",
		"LINK_IBLOCK_ID" => "",
		"LINK_PROPERTY_SID" => "",
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
		"USE_ALSO_BUY" => "Y",
		"USE_STORE" => "N",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"TOP_VIEW_MODE" => "SECTION",
		"ADD_PICT_PROP" => "PHOTOS",
		"LABEL_PROP" => "-",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"OFFER_ADD_PICT_PROP" => "ADDITIONAL_IMAGES",
		"OFFER_TREE_PROPS" => array(
			0 => "COLOR",
			1 => "SIZE",
		),
		"OFFERS_CART_PROPERTIES" => array(
			0 => "SIZE",
			1 => "COLOR",
		),
		"TOP_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"TOP_OFFERS_PROPERTY_CODE" => array(
			0 => "SIZE",
			1 => "COLOR",
			2 => "size",
			3 => "",
		),
		"TOP_OFFERS_LIMIT" => "5",
		"LIST_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
			2 => "",
		),
		"LIST_OFFERS_PROPERTY_CODE" => array(
			0 => "SIZE",
			1 => "COLOR",
			2 => "",
		),
		"LIST_OFFERS_LIMIT" => "0",
		"DETAIL_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "undefined",
			2 => "",
		),
		"DETAIL_OFFERS_PROPERTY_CODE" => array(
			0 => "ADDITIONAL_IMAGES",
			1 => "UNUSUAL_IMG",
			2 => "SIZE",
			3 => "COLOR",
			4 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"SEF_FOLDER" => "/krupnaya-bytovaya-tekhnika/",
		"FILTER_NAME" => "",
		"FILTER_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_PRICE_CODE" => array(
			0 => "BASE",
		),
		"FILTER_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_OFFERS_PROPERTY_CODE" => array(
			0 => "SIZE",
			1 => "COLOR",
			2 => "",
		),
		"MESSAGES_PER_PAGE" => "10",
		"USE_CAPTCHA" => "Y",
		"REVIEW_AJAX_POST" => "Y",
		"PATH_TO_SMILE" => "/bitrix/images/forum/smile/",
		"FORUM_ID" => "",
		"URL_TEMPLATES_READ" => "",
		"SHOW_LINK_TO_FORUM" => "Y",
		"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
		"COMPARE_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"COMPARE_PROPERTY_CODE" => getCompareProperties($iblockId),
		"COMPARE_OFFERS_FIELD_CODE" => array(
			0 => "ID",
			1 => "",
		),
		"COMPARE_OFFERS_PROPERTY_CODE" => array(
			0 => "SIZE",
			1 => "COLOR",
			2 => "",
		),
		"COMPARE_ELEMENT_SORT_FIELD" => "sort",
		"COMPARE_ELEMENT_SORT_ORDER" => "asc",
		"DISPLAY_ELEMENT_SELECT_BOX" => "N",
		"COMPARE_POSITION_FIXED" => "Y",
		"COMPARE_POSITION" => "bottom left",
		"SECTIONS_HIDE_SECTION_NAME" => "N",
		"ALSO_BUY_ELEMENT_COUNT" => "5",
		"ALSO_BUY_MIN_BUYES" => "1",
		"AJAX_OPTION_ADDITIONAL" => "",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"COMMON_ADD_TO_BASKET_ACTION" => "ADD",
		"CURRENCY_ID" => "RUB",
		"DETAIL_VOTE_DISPLAY_AS_RATING" => "rating",
		"DETAIL_BLOG_USE" => "Y",
		"DETAIL_SHOW_BASIS_PRICE" => "Y",
		"PRODUCT_SUBSCRIPTION" => "Y",
		"DETAIL_BLOG_URL" => "catalog_comments",
		"DETAIL_BLOG_EMAIL_NOTIFY" => "Y",
		"SHOW_SECTION_PRODUCT_QUANTITY" => "Y",
		"SEF_URL_TEMPLATES" => array(
			"sections" => "",
			"section" => "#SECTION_CODE_PATH#/",
			"element" => "#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
			"compare" => $iblockCode."/compare/",
		),
		"WP_ITEMLIST_CATEGORY_LINE" => "_BRAND",
		"WP_SHOW_MENU_GROUPS" => array(),
		"WP_SPLIT_BY_MENU_GROUPS" => "N",
		"WP_CONVERT_PRICE_FROM" => "RUB",
	),
	false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>