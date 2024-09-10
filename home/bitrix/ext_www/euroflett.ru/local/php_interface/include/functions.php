<?php

// Функция трассировки
function dump($var, $vardump = false, $return = false)
{
	global $USER;
	if($USER->IsAdmin() && $_GET['test']=='test') {
		static $dumpCnt;

		if (is_null($dumpCnt)) {
			$dumpCnt = 0;
		}
		ob_start();

		echo '<b>DUMP #' . $dumpCnt . ':</b> ';
		echo '<p>';
		$style = "
				border: 1px solid #696969;
				background: #eee;
				border-radius: 3px;
				font-size: 14px;
				font-family: calibri, arial, sans-serif;
				padding: 20px;
				";
		echo '<pre style="'.$style.'">';
		if ($vardump) {
			var_dump($var);
		} else {
			print_r($var);
		}
		echo '</pre>';
		echo '</p>';

		$cnt = ob_get_contents();
		ob_end_clean();
		$dumpCnt++;
		if ($return) {
			return $cnt;
		} else {
			echo $cnt;
		}
	}
}

function cdump($var){
	echo "<script>";
	echo "console.log(" . json_encode($var) . ");";
	echo "</script>";
}

// Показывает время выполнения до текущего момента
define("START_SCRIPT_TIME", microtime(true));
function dumptime($text=''){
	$time = microtime(true) - START_SCRIPT_TIME;
	dump(sprintf($text.' %.4F сек.', $time));
}

function plural($n, $forms) {
	return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
}


// Возвращает отресайзенную картинку. Размеры картинок задаются в settings.php
// Пример: $arImage = resizeImageGet($arProperties['IMAGE'], 'CATALOG_ITEM_LIST');
// В ответе массив из 'SRC', 'WIDTH', 'HEIGHT';

function resizeImageGet($file, $sizeName){
	global $arImageSizes;
	if(isset($arImageSizes[$sizeName])){
		return CFile::ResizeImageGet(
            $file,
            array("width" => $arImageSizes[$sizeName]['WIDTH'], "height" => $arImageSizes[$sizeName]['HEIGHT']),
            $arImageSizes[$sizeName]['RESIZE'],
            true
        );
	}
}

function resizeImageGetSrc($file, $sizeName){
	$file = resizeImageGet($file, $sizeName);
	return $file['src'];
}



// Каноничный URL для ссылки.
// $element может принимать на вход ID элемента, массив элемента($arItem), массив элементов ($arResult['ITEMS'])
function getCanonicalLink($element, $detailPageTemplate=false){

	//Узнаём, что же у нас на входе
	if(is_int($element) || !is_array($element)){ // просто айди
		if(intval($element)>0){
			$elementId = $element;
			$handler = 'id';
		}else{
			return $element;
		}
	}elseif (is_array($element)) { // массив одного элемента
        if (array_key_exists('ID', $element)) {
            $elementId = intval($element['ID']);
            if ($elementId > 0) {
                $handler = 'item';
            } else {
                return $element;
            }
        } elseif (is_array($element[key($element)]) && array_key_exists('ID', $element[key($element)])) { // массив $arResult['ITEMS']
            $handler = 'items';
            foreach ($element as $key => $value) {
                $elementId[] = $value['ID'];
                $elementsTable[$value['ID']] = $key;
            }
        } else {
            return $element;
        }
	}

	//Массив для кэша URL инфоблока
	$cachedIblocks = array();
	$paramDetailPageTemplate = $detailPageTemplate;

	$dbRes = CIBlockElement::GetList(array("SORT"=>"ASC"), $arFilter=array("ID"=>$elementId), false, false, array("CODE", "DETAIL_PAGE_URL", "PROPERTY_CANONICAL_SECTION"));

	// Если указан каноничный раздел, то строим ссылку на основе него
	while($arRes = $dbRes->GetNext()){
		$detailPageUrl = $arRes['DETAIL_PAGE_URL'];

		if(!$arRes["PROPERTY_CANONICAL_SECTION_VALUE"]){
			$arList = array();
			// Ищем самый глубокий из непсевдоразделов элемента и привязываем его
			$db_old_groups = CIBlockElement::GetElementGroups(intval($arRes['ID']), false, array("ID", "IBLOCK_SECTION_ID", "IBLOCK_ID", "NAME", "DEPTH_LEVEL"));
			while($ar_group = $db_old_groups->Fetch()){
				$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".intval($ar_group["IBLOCK_ID"])."_SECTION", $ar_group["ID"]);
				$UF_PS = $arUF["UF_PSEUDO_SECTION"]["VALUE"];
				if(strlen($UF_PS)>0){
					$arUF_PS = unserialize($UF_PS);
					if($arUF_PS["is_pseudosection"]=="Y"){
						continue;
					}
				}
				$arList[$ar_group["ID"]] = array('ID' => $ar_group["ID"], 'PARENT' => intval($ar_group["IBLOCK_SECTION_ID"]), 'NAME' => $ar_group["NAME"], 'DEPTH_LEVEL' => $ar_group["DEPTH_LEVEL"]);
			}
			$sort_arr = array();
			foreach($arList as $uniqid => $row){
			    foreach($row as $key=>$value){
			         $sort_arr[$key][$uniqid] = $value;
			    }
			}
			array_multisort($sort_arr["DEPTH_LEVEL"], SORT_DESC, $arList);
			reset($arList);
			$arNewCanonical = $arList[key($arList)];
			CIBlockElement::SetPropertyValuesEx($arRes['ID'], $arRes['IBLOCK_ID'], array("CANONICAL_SECTION" => $arNewCanonical['ID']));
			$arRes["PROPERTY_CANONICAL_SECTION_VALUE"] = $arNewCanonical['ID'];
		}

		if($arRes["PROPERTY_CANONICAL_SECTION_VALUE"]){
			$arList = array();
			$dbParents = CIBlockSection::GetNavChain(false, intval($arRes["PROPERTY_CANONICAL_SECTION_VALUE"]));
			while($arParents = $dbParents->Fetch()){
				$arList[] = $arParents['CODE'];
			}
			$sectionPath = implode('/', $arList);

			$detailPageTemplate = $paramDetailPageTemplate;

			// Если не передан параметр шаблона DETAIL_PAGE_URL, то берём из настроек инфоблока
			if(!$detailPageTemplate){
				if(array_key_exists($arRes['IBLOCK_ID'], $cachedIblocks)){
					$detailPageTemplate = $cachedIblocks[$arRes['IBLOCK_ID']]['DETAIL_PAGE_URL'];
				}else{
					$rsIblocks = CIBlock::GetList(array('ID'=>'ASC'), array('=ID'=>$arRes['IBLOCK_ID']));
					if($arIblocks = $rsIblocks->GetNext()){
						$detailPageTemplate = $arIblocks['DETAIL_PAGE_URL'];
						$cachedIblocks[$arRes['IBLOCK_ID']] = array('DETAIL_PAGE_URL'=>$detailPageTemplate);
					}
				}
			}

			//Строим DETAIL_PAGE_URL с нуля
			$detailPageUrl = $detailPageTemplate;
			$detailPageUrl = str_replace('#SECTION_CODE_PATH#', $sectionPath, $detailPageUrl);//Меняем код пути до нужной секции
			$detailPageUrl = str_replace('#ELEMENT_CODE#', $arRes['CODE'], $detailPageUrl);//Меняем код товара
			$detailPageUrl = str_replace('#CODE#', $arRes['CODE'], $detailPageUrl);//Меняем код товара
			$detailPageUrl = str_replace('#ELEMENT_ID#', $arRes['ID'], $detailPageUrl);//Меняем код товара
			$detailPageUrl = str_replace('#SITE_DIR#', SITE_DIR, $detailPageUrl);//Меняем код товара
			$detailPageUrl = str_replace('//', '/', $detailPageUrl);//Меняем код товара
			//$detailPageUrl .= 'test';
		}
		switch ($handler) {
			case 'id':
				$return = $detailPageUrl;
				break;

			case 'item':
				$element['DETAIL_PAGE_URL'] = $detailPageUrl;
				$return = $element;
				break;

			case 'items':
				$key = $elementsTable[$arRes['ID']];
				$element[$key]['DETAIL_PAGE_URL'] = $detailPageUrl;
				$return = $element;
				break;
		}

	}
	return $return;
}

function getCanonicalSectionLink($arRes, $detailPageTemplate=false){
	$arList = array();
	$dbParents = CIBlockSection::GetNavChain(false, intval($arRes["ID"]));
	while($arParents = $dbParents->Fetch()){
		$arList[] = $arParents['CODE'];
	}
	$sectionPath = implode('/', $arList);
			
	if(!$detailPageTemplate){
		if(array_key_exists($arRes['IBLOCK_ID'], $cachedIblocks)){
			$detailPageTemplate = $cachedIblocks[$arRes['IBLOCK_ID']]['SECTION_PAGE_URL'];
		}else{
			$rsIblocks = CIBlock::GetList(array('ID'=>'ASC'), array('=ID'=>$arRes['IBLOCK_ID']));
			if($arIblocks = $rsIblocks->GetNext()){
				$detailPageTemplate = $arIblocks['SECTION_PAGE_URL'];
				$cachedIblocks[$arRes['IBLOCK_ID']] = array('SECTION_PAGE_URL'=>$detailPageTemplate);
			}
		}
	}
	//Строим DETAIL_PAGE_URL с нуля
	$detailPageUrl = $detailPageTemplate;
	$detailPageUrl = str_replace('#SECTION_CODE_PATH#', $sectionPath, $detailPageUrl);//Меняем код пути до нужной секции
	$detailPageUrl = str_replace('#ELEMENT_CODE#', $arRes['CODE'], $detailPageUrl);//Меняем код товара
	$detailPageUrl = str_replace('#CODE#', $arRes['CODE'], $detailPageUrl);//Меняем код товара
	$detailPageUrl = str_replace('#ELEMENT_ID#', $arRes['ID'], $detailPageUrl);//Меняем код товара
	$detailPageUrl = str_replace('#SITE_DIR#', SITE_DIR, $detailPageUrl);//Меняем код товара
	$detailPageUrl = str_replace('//', '/', $detailPageUrl);//Меняем код товара

	return $detailPageUrl;
}


// И эта функция не работает =)
// Работает как GetMessage(), только принимает на вход количество товара
// Необходимы строки в файле языка PLURAL_СВОЙСТВО_ONE, PLURAL_СВОЙСТВО_TWO, PLURAL_СВОЙСТВО_FIVE
// Например:
// $MESS['PLURAL_ITEM_ONE'] = 'товар';
// $MESS['PLURAL_ITEM_TWO'] = 'товара';
// $MESS['PLURAL_ITEM_MANY'] = 'товаров';
function GetMessagePlural($cnt, $name, $aReplace=null)
{
    global $MESS;
    if(isset($MESS['PLURAL_'.$name.'_ONE']) && isset($MESS['PLURAL_'.$name.'_TWO']) && isset($MESS['PLURAL_'.$name.'_MANY']) ){
    	$forms = array($MESS['PLURAL_'.$name.'_ONE'], $MESS['PLURAL_'.$name.'_TWO'], $MESS['PLURAL_'.$name.'_MANY']);
    	$s = plural($cnt, $forms);
    	if($aReplace!==null && is_array($aReplace))
            foreach($aReplace as $search=>$replace)
                $s = str_replace($search, $replace, $s);
        return $s;
    }
    
    return \Bitrix\Main\Localization\Loc::getMessage($name, $aReplace);
}

function webformAntispamBeforeResultAdd($WEB_FORM_ID, $arFields, $arrVALUES)
{
  global $APPLICATION;
  if($_REQUEST['confirm'] == '1')
  	$APPLICATION->ThrowException('Отправка формы автоматическими средствами запрещена');
}
AddEventHandler('form', 'onBeforeResultAdd', 'webformAntispamBeforeResultAdd');


function isPreorderById($itemId){
	$preorder = false;

	$BRAND_MIELE = 46;
	$VACUUM_CLEANERS_SECTION_MIELE = 812;
	$BRAND_RESTART= 49;

	$res = CIBlockElement::GetByID($itemId);
	if($ob = $res->GetNextElement()){ 
		$arItem = $ob->GetFields();  
		$arItem['PROPERTIES'] = $ob->GetProperties();
		if ($arItem['PROPERTIES']['_BRAND']['VALUE'] == $BRAND_MIELE && $arItem['IBLOCK_SECTION_ID'] != $VACUUM_CLEANERS_SECTION_MIELE) {
			$preorder = true;
		}
	}
	return $preorder;
}
function getBuyText($arItem){
	$buyText = "Купить";

	$BRAND_MIELE = 46;
	$VACUUM_CLEANERS_SECTION_MIELE = 812;
	$BRAND_RESTART= 49;

	if($arItem['PROPERTIES']['_BRAND']['VALUE'] == $BRAND_MIELE && $arItem['IBLOCK_SECTION_ID'] != $VACUUM_CLEANERS_SECTION_MIELE)
		$buyText = "Заказать";

	return $buyText;
}

/**
 * Выставляет значения для значений NO_PRICE и CAN_BUY
 * @param array $arItem  обязательно должны быть IBLOCK_ID, IBLOCK_SECTION_ID и PROPERTIES.NO_PRICE
 * @param array $arPrice MIN_PRICE
 */
function preparePriceFields($arItem, $priceValue) {
    static $arChain;

    $iblockId = intval($arItem['IBLOCK_ID']);
    $sectionId = intval($arItem['IBLOCK_SECTION_ID']);
    if (empty($arChain) || empty($arChain[$iblockId]) || empty($arChain[$iblockId][$sectionId]))
    {
        global $USER_FIELD_MANAGER;
        $ob = CIBlockSection::GetNavChain($iblockId, $sectionId, array("ID", "NAME", "IBLOCK_ID"));
        $arChain = array();
        while ($res = $ob->GetNext())
        {
            $arFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_' . $iblockId . '_SECTION', $res['ID']);
            $res['NO_PRICE'] = (isset($arFields['UF_NOPRICE']) && $arFields['UF_NOPRICE']['VALUE'] == "1");
            $arChain[] = $res;
        }
    }

    $noPrice = false;
    if (!empty($arChain[$iblockId][$sectionId])) {
        foreach ($arChain[$iblockId][$sectionId] as $key => $value) {
            $noPrice |= $value['NO_PRICE'];
        }
    }

    $noPriceItem = false;
    if(isset($arItem['PROPERTIES']['NO_PRICE']) && $arItem['PROPERTIES']['NO_PRICE']['VALUE'] == 'Y')
        $noPriceItem = true;

    $arItem['NO_PRICE'] = (bool) ($noPrice | $noPriceItem);
    if(!($priceValue > 0)){
        $arItem['NO_PRICE'] = true;
    }
    if($arItem['NO_PRICE']){
        $arItem['CAN_BUY'] = false;
    } elseif (!isset($arItem['CAN_BUY'])) {
        $arItem['CAN_BUY'] = $arItem['CATALOG_AVAILABLE'];
    }

    return $arItem;
}

function setNoPriceSpecific($arItem){
	$GAGGENAU_ID = 44;
	$IP_ID = 61;

//	if($arItem['PROPERTIES']['_BRAND']['VALUE'] == $GAGGENAU_ID) {
//		$arItem['NO_PRICE'] = true; // Force
//		$arItem['CAN_BUY'] = false;
//		$arItem['IS_GAGGENAU'] = true;
//	}

	// old
    // Не показывать цены в бренде IP.
	/*if($arItem['PROPERTIES']['_BRAND']['VALUE'] == $IP_ID) {
		$arItem['NO_PRICE'] = true; // Force
		$arItem['CAN_BUY'] = false;
	}*/
	// old

    // new
    // Показывать цены и кнопку купить для бренда IP.
	if($arItem['PROPERTIES']['_BRAND']['VALUE'] == $IP_ID) {
		$arItem['NO_PRICE'] = false;
		$arItem['CAN_BUY'] = true;
	}
    // new

    	if($arItem['PROPERTIES']['_BRAND']['VALUE'] == $GAGGENAU_ID) {
            $arItem['NO_PRICE'] = false;
            $arItem['CAN_BUY'] = true;
	}

	return $arItem;
}

function getIsAvailableText($isAvailable) {
	$cssClass = $isAvailable ? "is-available" : "is-not-available";
	$text = $isAvailable ? "В наличии" : "Под заказ";
	return "<div class={$cssClass}>{$text}</div>";
}

function ExportToFileAdProXML()
{
	ExportToFileAdProXML_old();
	ExportToFileAdProXML_new();
	ExportToFileGMerchant();
	
	return "ExportToFileAdProXML();";
}

function ExportToFileAdProXML_new()
{
    CModule::IncludeModule("catalog");
	ini_set("memory_limit", "2512M");
    global $USER;
    if (!$file = fopen($_SERVER['DOCUMENT_ROOT'].'/euroflett_k50.xml', 'w'))
        return "ExportToFileAdProXML_new();";
    if (!CModule::IncludeModule("iblock"))
        return "ExportToFileAdProXML_new();";

    $arrBlocks = array();
    $res = CIBlock::GetList(Array(), Array('TYPE'=>'catalog', 'ACTIVE'=>'Y'), false);
    while($ar_res = $res->Fetch())
        $arrBlocks[] = $ar_res;

    ob_start();

    echo '<?xml version="1.0"?>
	<catalog date="'.date("Y-m-d H:i:s").'">
	<categories>'.PHP_EOL;
    foreach ($arrBlocks as $category){
        echo ('	<category id="'.$category["ID"].'">'.$category["NAME"].'</category>'.PHP_EOL);
    }
    echo '</categories>
	<offers>'.PHP_EOL;
    foreach ($arrBlocks as $category){
        $res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$category['ID'], "ACTIVE"=>"Y", "!PROPERTY_VITRINA_VALUE" => "Да","!PROPERTY_OUT_OF_PRODUCTION_VALUE" => "Да"), false, false, array('ID', 'NAME', 'DETAIL_PAGE_URL', 'CATALOG_QUANTITY', 'PROPERTY__AVAILABLE', 'PROPERTY_MODEL', 'PROPERTY__BRAND.NAME'));
        /** @var _CIBElement $obProduct */
        while ($obProduct = $res->GetNextElement()){
            $product = $obProduct->GetFields();
            if (empty($product["PROPERTY__BRAND_NAME"]) || empty($product["PROPERTY_MODEL_VALUE"]) || !(strlen($product["PROPERTY_MODEL_VALUE"]) < 255)) {
                continue;
            }

            $product['PROPERTIES'] = $obProduct->GetProperties();
            $arPrice = CCatalogProduct::GetOptimalPrice($product['ID'], 1, array(), "N");
            if($arPrice['RESULT_PRICE']['BASE_PRICE'] > $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'])
                $actual_price = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
            else
                $actual_price = $arPrice['RESULT_PRICE']['BASE_PRICE'];

            $product = preparePriceFields($product, $actual_price);
            $product = setNoPriceSpecific($product); // GAGGENAU & IP

            $av = filter_var($product["PROPERTY__AVAILABLE_VALUE"], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

            if ( $product['NO_PRICE'] || $product['PROPERTIES']['NO_PRICE']['VALUE'] ||
                $product['IS_GAGGENAU'] === true
            ) {
                $tagAvailability = 'preorder';
            } elseif ($product['CATALOG_QUANTITY'] > 0 && !$product['NO_PRICE']) {
                $tagAvailability = 'in_stock';
            } else {
                $tagAvailability = 'out_of_stock';
            }

            echo '<offer id="'.$product["ID"].'" available="'.$av.'">
                <vendor>'.htmlspecialchars($product["PROPERTY__BRAND_NAME"]).'</vendor>
                <product>'.htmlspecialchars($product["PROPERTY_MODEL_VALUE"]).'</product>
                <category>'.$category["ID"].'</category>
                <url>https://www.euroflett.ru'.$product["DETAIL_PAGE_URL"].'</url>
                <price>'.$actual_price.'</price>
                <availability>'.$tagAvailability.'</availability>
            </offer>'.PHP_EOL;
        }
    }
    echo '</offers>
	</catalog>';

    $output = ob_get_clean();
    fwrite($file, $output);
    fclose($file);
    return "ExportToFileAdProXML_new();";
}

function ExportToFileAdProXML_old()
{
	if (!$file = fopen($_SERVER['DOCUMENT_ROOT'].'/euroflett_adpro.xml', 'w'))
		return "ExportToFileAdProXML();";
	if (!CModule::IncludeModule("iblock"))
		return "ExportToFileAdProXML();";

	$arrBlocks = array();
	$res = CIBlock::GetList(Array(), Array('TYPE'=>'catalog',  'SITE_ID'=>SITE_ID, 'ACTIVE'=>'Y'), false);
	while($ar_res = $res->Fetch())
		$arrBlocks[] = $ar_res;

	ob_start();

	echo '<?xml version="1.0"?>
	<catalog date="'.date("Y-m-d").'" xmlns="http://arwm.ru/market/schema.xsd">
	<regions>
		<region id="1" alt="msk" alt2="Москва">Russia/Central/Moscow and Moscow region</region>
	</regions>
	<categories>'.PHP_EOL;
	foreach ($arrBlocks as $category){
		echo ('	<category id="'.$category["ID"].'">'.$category["NAME"].'</category>'.PHP_EOL);
	}
	echo '</categories>
	<offers>'.PHP_EOL;
	foreach ($arrBlocks as $category){
		$res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$category['ID'], "ACTIVE"=>"Y", "!PROPERTY_VITRINA_VALUE" => "Да"), false, false, array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PROPERTY__AVAILABLE', 'PROPERTY_MODEL', 'PROPERTY__BRAND.NAME', 'CATALOG_GROUP_2'));
		while ($product = $res->GetNext()){
			if (!empty($product["PROPERTY__BRAND_NAME"]) && !empty($product["PROPERTY_MODEL_VALUE"]) && strlen($product["PROPERTY_MODEL_VALUE"]) < 255) {
				$av = filter_var($product["PROPERTY__AVAILABLE_VALUE"], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
				echo '<offer id="'.$product["ID"].'">
					<vendor>'.htmlspecialchars($product["PROPERTY__BRAND_NAME"]).'</vendor>
					<product>'.htmlspecialchars($product["PROPERTY_MODEL_VALUE"]).'</product>
					<category>'.$category["ID"].'</category>
					<url>http://'.SITE_SERVER_NAME.$product["DETAIL_PAGE_URL"].'</url>
					<regions>
						<region id="1" price="'.number_format(CCurrencyRates::ConvertCurrency($product["CATALOG_PRICE_2"], $product["CATALOG_CURRENCY_2"], "RUB"),0,"","").'" deliveryCost="0" available="'.$av.'" />
					</regions>
				</offer>'.PHP_EOL;
			}
		}
	}
	echo '</offers>
	</catalog>';

	$output = ob_get_clean();
	fwrite($file, $output);
	fclose($file);
	return "ExportToFileAdProXML();";
}

function ExportToFileGMerchant()
{
	$step = COption::GetOptionString('gmerchant', 'gmerchant_step');
if(empty($step)){
$step=0;
}
if($step==0){
if (!$file = fopen($_SERVER['DOCUMENT_ROOT'].'/euroflett_gmerchant.xml.tmp', 'w'))
		return "ExportToFileGMerchant();";
}else{
if (!$file = fopen($_SERVER['DOCUMENT_ROOT'].'/euroflett_gmerchant.xml.tmp', 'a'))
		return "ExportToFileGMerchant();";
}

	if (!CModule::IncludeModule("iblock"))
		die();

	$arrBlocks = array();
	$arrSection = array();
	
	$res = CIBlock::GetList(Array(), Array('TYPE'=>'catalog',  'SITE_ID'=>SITE_ID, 'ACTIVE'=>'Y'), false);
	while($ar_res = $res->Fetch()){
	$arrBlocks[] = $ar_res;
	
	/*Получаем дерево раздела, для текущего инфоблока*/
	$tree_category = CIBlockSection::GetTreeList(
		$arFilter=Array('IBLOCK_ID' => $ar_res['ID']),
		$arSelect=Array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME')
	);
	while($section = $tree_category->GetNext()) {
		$nav = CIBlockSection::GetNavChain($ar_res['ID'], $section['ID']);
			$product_type = 'Главная &gt; ';
			while($ar_result = $nav->GetNext()){
				if($section['ID'] == $ar_result['ID']){
					$product_type .= $ar_result['NAME'];
				}else{
					$product_type .= $ar_result['NAME'].' &gt; ';
				}
			}
		$arrSection[$section['ID']] = $product_type;
	}
	}
	
	$n=count($arrBlocks)-1;
	
		echo $step.' '.$n;
	if($step==0){
		$output = '<?xml version="1.0" encoding="utf-8"?>
		<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
			<title>euroflett.ru</title>
			<link>http://www.euroflett.ru</link>
			<description>euroflett.ru - Интернет магазин бытовой техники и шоурум в Москве</description>
			<channel>
				';
		}
	//foreach ($arrBlocks as $category){
	$category=$arrBlocks[$step];
		$res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$category['ID'], "ACTIVE"=>"Y", "!PROPERTY_VITRINA_VALUE" => "Да"), false, false, array('ID', 'IBLOCK_SECTION_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_MODEL', 'PROPERTY__BRAND.NAME', 'CATALOG_GROUP_2','PROPERTY_VITRINA'));
		while ($product = $res->GetNext()){
			
			if (!empty($product["PROPERTY__BRAND_NAME"]) && !empty($product["PROPERTY_MODEL_VALUE"]) && strlen($product["PROPERTY_MODEL_VALUE"]) < 255) {
			$product_type=$arrSection[$product['IBLOCK_SECTION_ID']];
			if($product["DETAIL_PICTURE"]>0){
				$img = CFile::GetPath($product["DETAIL_PICTURE"]);
				$img_href = ($img) ? 'http://'.SITE_SERVER_NAME.$img : '';
			}else{
				$img_href = '';
			}
				//$av = filter_var($product["PROPERTY__AVAILABLE_VALUE"], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
				$output .= '<item>';
					$output .= '<g:id>'.$product["ID"].'</g:id>';
					$output .= '<g:title>'.htmlspecialchars($product["NAME"]).'</g:title>';
					$output .= '<g:description>'.htmlspecialchars($product["NAME"]).' в наличии - удобное Вам время доставки, подбор техники в шоуруме в Москве, бесплатная установка, гарантия от производителя</g:description>';
					$output .= '<g:link>http://'.SITE_SERVER_NAME.$product["DETAIL_PAGE_URL"].'</g:link>';
					$output .= '<g:image_link>'.$img_href.'</g:image_link>';
					$output .= '<g:condition>new</g:condition>';
					$output .= '<g:availability>in stock</g:availability>';
					$output .= '<g:product_type>'.$product_type.'</g:product_type>';
					$output .= '<g:price>'.number_format(CCurrencyRates::ConvertCurrency($product["CATALOG_PRICE_2"], $product["CATALOG_CURRENCY_2"], "RUB"),0,"","").'</g:price>';
					$output .= '<g:brand>'.htmlspecialchars($product["PROPERTY__BRAND_NAME"]).'</g:brand>';
				$output .= '</item>';
			}
		}
		
		
		
	//}
	if($step==$n){
		$output .= '</channel>
		</rss>';
	}	
	$step++;
	if($step>$n){
	COption::SetOptionString('gmerchant', 'gmerchant_step', 0);
	rename($_SERVER['DOCUMENT_ROOT'].'/euroflett_gmerchant.xml.tmp', $_SERVER['DOCUMENT_ROOT'].'/euroflett_gmerchant.xml');
	}else{
	COption::SetOptionString('gmerchant', 'gmerchant_step', $step);
	}
	fwrite($file, $output);
	fclose($file);
	return "ExportToFileGMerchant();";
}

class RedirectFromHttpToHttps
{
	static function redirect_from_http_to_https()
	{
		if ($_SERVER['HTTP_X_FORWARDED_PROTO'] != "https" && empty($_POST) && preg_match("~/cart/~", $_SERVER['REQUEST_URI']))
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			die();
		}
//		elseif ($_SERVER['HTTP_X_FORWARDED_PROTO'] == "https" && empty($_POST) && !preg_match("~/cart/~", $_SERVER['REQUEST_URI']))
//		{
//			header("HTTP/1.1 301 Moved Permanently");
//			header("Location: http://".$_SERVER['HTTP_HOST'].":80".$_SERVER['REQUEST_URI']);
//			die();
//		}
	}
}
