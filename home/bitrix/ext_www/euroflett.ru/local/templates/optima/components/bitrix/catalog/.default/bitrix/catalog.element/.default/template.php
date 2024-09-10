<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

// Дополняет элемент требуемым свойством.
if (!function_exists("fillItemProperty"))
{
	function fillItemProperty(&$element, $propertyCode) {
		// Получить свойство элемента.
		$propertyRaw = CIBlockElement::GetProperty($element['IBLOCK_ID'], $element['ID'], array("sort"=>"asc"), array('CODE'=>$propertyCode));
		$property = $propertyRaw->Fetch();

		// Дополнить элемент полученным свойством.
		$element['PROPERTIES'][$propertyCode] = $property;
	}
}
$templateLibrary = array('popup');
$currencyList = '';
if (!empty($arResult['CURRENCIES'])){
	$templateLibrary[] = 'currency';
	$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}
$templateData['TEMPLATE_LIBRARY'] = $templateLibrary;
$templateData['CURRENCIES'] = $currencyList;
$templateData['CATALOG_PRICE_2'] = $arResult['CATALOG_PRICE_2'];
$templateData['ELEMENT_ID'] = $arResult['ID'];
unset($currencyList, $templateLibrary);

$strMainID = $this->GetEditAreaId($arResult['ID']);
$arItemIDs = array(
	'ID' => $strMainID,
	'PICT' => $strMainID.'_pict',
	'DISCOUNT_PICT_ID' => $strMainID.'_dsc_pict',
	'STICKER_ID' => $strMainID.'_sticker',
	'BIG_SLIDER_ID' => $strMainID.'_big_slider',
	'BIG_IMG_CONT_ID' => $strMainID.'_bigimg_cont',
	'SLIDER_CONT_ID' => $strMainID.'_slider_cont',
	'SLIDER_LIST' => $strMainID.'_slider_list',
	'SLIDER_LEFT' => $strMainID.'_slider_left',
	'SLIDER_RIGHT' => $strMainID.'_slider_right',
	'OLD_PRICE' => $strMainID.'_old_price',
	'PRICE' => $strMainID.'_price',
	'DISCOUNT_PRICE' => $strMainID.'_price_discount',
	'SLIDER_CONT_OF_ID' => $strMainID.'_slider_cont_',
	'SLIDER_LIST_OF_ID' => $strMainID.'_slider_list_',
	'SLIDER_LEFT_OF_ID' => $strMainID.'_slider_left_',
	'SLIDER_RIGHT_OF_ID' => $strMainID.'_slider_right_',
	'QUANTITY' => $strMainID.'_quantity',
	'QUANTITY_DOWN' => $strMainID.'_quant_down',
	'QUANTITY_UP' => $strMainID.'_quant_up',
	'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
	'QUANTITY_LIMIT' => $strMainID.'_quant_limit',
	'BASIS_PRICE' => $strMainID.'_basis_price',
	'BUY_LINK' => $strMainID.'_buy_link',
	'ADD_BASKET_LINK' => $strMainID.'_add_basket_link',
	'BASKET_ACTIONS' => $strMainID.'_basket_actions',
	'NOT_AVAILABLE_MESS' => $strMainID.'_not_avail',
	'COMPARE_LINK' => $strMainID.'_compare_link',
	'PROP' => $strMainID.'_prop_',
	'PROP_DIV' => $strMainID.'_skudiv',
	'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
	'OFFER_GROUP' => $strMainID.'_set_group_',
	'BASKET_PROP_DIV' => $strMainID.'_basket_prop',
);
$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);
$templateData['JS_OBJ'] = $strObName;

$strImgTitle = (
	isset($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]) && $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"] != ''
	? $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]
	: $arResult['NAME']
);
$strImgAlt = (
	isset($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]) && $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"] != ''
	? $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]
	: $arResult['NAME']
);
/*
$strTitle = (
isset($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]) && $arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != ''
	? $arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]
	: $arResult["NAME"]
);
*/
reset($arResult['MORE_PHOTO']);
$arFirstPhoto = current($arResult['MORE_PHOTO']);
?><?
$hasOffers = (isset($arResult['OFFERS']) && !empty($arResult['OFFERS']));
?>

<? $arPhotos = array();
foreach ($arResult['MORE_PHOTO'] as $photo) {
	$arPhotos[] = array(
		"SRC" => resizeImageGetSrc($photo['ID'], 'CATALOG_ITEM_PREVIEW'),
		"THUMBNAIL_SRC" => resizeImageGetSrc($photo['ID'], 'CATALOG_ITEM_THUMBNAIL'),
		"FULL_SRC" => resizeImageGetSrc($photo['ID'], 'CATALOG_ITEM_FULL')
	);
}

$filesizes = array();
foreach ($arPhotos as $phkey => $photo) {
	$filesize = filesize($_SERVER['DOCUMENT_ROOT'].$photo['FULL_SRC']);
	if(in_array($filesize, $filesizes)){
		unset($arPhotos[$phkey]);
	}else{
		$filesizes[] = $filesize;
	}
}
?>

<? $templateData['NAME'] = $arResult["NAME"]; ?>

<?
if(file_exists($_SERVER["DOCUMENT_ROOT"].'/include/seo_mod.php')) {
    include($_SERVER["DOCUMENT_ROOT"].'/include/seo_mod.php');
}

?>

<section class="catalog-item-block" id="<? echo $arItemIDs['ID']; ?>">
<div itemscope itemtype="http://schema.org/Product" id="props_block">    
    <h1 class="header-line" itemprop="name"><?php if (isset($seo_h1) && $seo_h1) echo $seo_h1; else echo $arResult["NAME"];?></h1>
    <?if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']!='') {?>
		<div class="articul_top"><strong>Артикул:</strong> <span class="catalog-articul" itemprop="mpn"><?=$arResult['PROPERTIES']['ARTNUMBER']['VALUE']?></span></div>
    <?}?>
    <div class="image-gallery-slider-with-thumbnails">
		<div class="image">
			<div class="image-wrapper">
                <!-- <ul class="property-flags"> -->
                    <?/*
                    if ($arResult['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                         echo '<li class="free-delivery-flag"></li>';
                    }

                    if ($arResult['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                        echo '<li class="free-setup"></li>';
                    }

                    if ($arResult['PROPERTIES']['NEW']['VALUE'] === 'Y') {
                        echo '<li>Новинка</li>';
                    }

                    if ($arResult['PROPERTIES']['HIT']['VALUE'] === 'Y') {
                        echo '<li>Хит</li>';
                    }

                    if ($arResult['PROPERTIES']['SPECIAL_OFFER']['VALUE_XML_ID'] === 'Y') {
                        echo '<li>Спецпредложение</li>';
                    }
                    */?>
                <!-- </ul> -->
                <div class="product-labels">
                    <?
                        if ($arResult['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                        echo '
                            <a class="product-label-link" href="/payment-and-delivery/"><div class="product-label product-label--delivery">
                                <div class="product-label__tips">Бесплатная доставка</div>
                            </div></a>';
                        }
                        if ($arResult['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                        echo '
                            <div class="product-label product-label--setup">
                                <div class="product-label__tips">Бесплатная установка</div>
                            </div>';
                        }
                        if ($arResult['PROPERTIES']['NEW']['VALUE'] === 'Y' || strlen($arResult['PROPERTIES']['NEW']['VALUE'])) {
                        echo '
                            <div class="product-label product-label--new">
                                <div class="product-label__title">Новинка</div>
                            </div>';
                        }
                        if ($arResult['PROPERTIES']['HIT']['VALUE'] === 'Y' || strlen($arResult['PROPERTIES']['HIT']['VALUE'])) {
                        echo '
                            <div class="product-label product-label--hit">
                                <div class="product-label__title">Хит</div>
                            </div>';
                        }
                        if ($arResult['PROPERTIES']['SPECIAL_OFFER']['VALUE_XML_ID'] === 'Y') {
                        echo '
                            <div class="product-label product-label--special">
                                <div class="product-label__title">Спецпредложение</div>
                            </div>';
                        }
						if ($arResult['PROPERTIES']['_FREE_STORAGE']['VALUE_XML_ID'] === 'Y') {
						echo '
							<div class="product-label product-label--guard">
							   <div class="product-label__tips">Бесплатное хранение</div>
							</div>';
						}
                    ?>
                </div>
				<img itemprop="image" id="<? echo $arItemIDs['PICT']; ?>" src="<?= $arPhotos[0]['SRC']; ?>" data-fullimage="<?= $arPhotos[0]['FULL_SRC']; ?>" alt="<?= $strImgAlt; ?>" />
			</div>
		</div>

		<? if(count($arPhotos) > 1) { ?>
			<ul class="more-images"<? 
			if(count($arPhotos) < 4) { ?> style="overflow-y: hidden;"<? 
			} ?>> <div style="float: left; margin-left: 25px;">Фотографии:</div>
			<? foreach ($arPhotos as $k => $arPhoto) { ?>
				<li <?= ($k == 0 ? 'class="active"' : '' )?>><img src="<?= $arPhoto['THUMBNAIL_SRC'] ?>" data-image="<?= $arPhoto['SRC'] ?>" data-fullimage="<?= $arPhoto['FULL_SRC'] ?>" /></li>
			<? } ?>
			</ul>
		<? } ?>
	</div>
   <div>
   <br />
   <strong>Бренд:</strong> <span class="catalog-item-block-brand" itemprop="brand"><?if ($arResult['BRAND_LINK']) { echo strip_tags($arResult['BRAND_LINK']); }?></span></div>
  
  <?if ($arResult['PROPERTIES']['MODEL']['VALUE']!='') {?>
   <div><strong>Модель:</strong> <span class="catalog-articul" itemprop="mpn"><?=$arResult['PROPERTIES']['MODEL']['VALUE']?></span></div>
    <?}?>
  <?if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']!='') {?>
   <div><strong>Артикул:</strong> <span class="catalog-articul" itemprop="mpn"><?=$arResult['PROPERTIES']['ARTNUMBER']['VALUE']?></span></div>
    <?}?>

<? /*
    <div class="catalog-item-gallery">
        <div class="catalog-item-main-image">
            <img src="<?=$arFirstPhoto['SRC'];?>" alt="<?=$strImgAlt;?>" title="<? echo $strImgTitle; ?>" data-action="big-slider-image" />
        </div>
        <div class="catalog-item-pics">
        	<?foreach ($arResult['MORE_PHOTO'] as $key => $value) {?>
        		<a href="#"<?=($key==0)?' class="active"':''?> data-action="change-detail-pic"><img src="<?=$value['SRC']?>" alt="<?=$strImgAlt;?>" /></a>
        	<?}?>
        </div>
    </div>
*/ ?>

    <div class="space"></div>

	<?
	$canBuy = $arResult['CAN_BUY'];

	$buyBtnMessage = ($arParams['MESS_BTN_BUY'] != '' ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCE_CATALOG_BUY'));
	$addToBasketBtnMessage = ($arParams['MESS_BTN_ADD_TO_BASKET'] != '' ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCE_CATALOG_ADD'));
	$notAvailableMessage = ($arParams['MESS_NOT_AVAILABLE'] != '' ? $arParams['MESS_NOT_AVAILABLE'] : GetMessageJS('CT_BCE_CATALOG_NOT_AVAILABLE'));
	$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
	$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
	if ($hasOffers){
		$buyUrl = $currentOffer['~BUY_URL'];
		$addUrl = $currentOffer['~ADD_URL'];
		$compareUrl = $currentOffer['COMPARE_URL'];
	}else{
		$buyUrl = $arResult['~BUY_URL'];
		$addUrl = $arResult['~ADD_URL'];
		$compareUrl = $arResult['COMPARE_URL'];
	}

	$showSubscribeBtn = false;
	$compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCE_CATALOG_COMPARE'));
	?>

    <? ob_start(); // Этот блок встречается дважды, соберём его один раз ?>

    <div class="catalog-price-block">
		<? if ($arResult['PROPERTIES']['OUT_OF_PRODUCTION']['VALUE_XML_ID'] !== 'Y') { ?>
            <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                <?if(!$arResult['NO_PRICE']): ?>
                    <div class="catalog-price">
                      <?php include_once($_SERVER['DOCUMENT_ROOT']."/include/meta_include.php"); ?>
                      <?=(!empty($arResult['DISPLAY_PROPERTIES']['OLDPRICE']['DISPLAY_VALUE']) ? '<span class="oldprice_name">старая цена:</span><span class="old_price">'.number_format($arResult['DISPLAY_PROPERTIES']['OLDPRICE']['DISPLAY_VALUE'], 0, " ", " ").' руб.</span>' : '');?>
                      <span>цена:</span><strong><span itemprop="price" content="<?=str_replace(" ", "", $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>"><?=$arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?></span> <span>руб.</span></strong>
                         <? if ($arResult['CATALOG_QUANTITY']>0) echo '<div class="is-available"><link itemprop="availability" href="http://schema.org/InStock"/><span class="green_yes">В наличии</span></a> <br />*цена не является фиксированной или минимальной</div>';
                                        else echo '<link itemprop="availability" href="http://schema.org/InStock"/><div class="is-not-available" data-availability="not-availability"></div><br />*цена не является фиксированной или минимальной';
                                        //getIsAvailableText($arItem['CATALOG_QUANTITY'] > 0)
                                        ?>
                    </div>

                    <? $buyText = getBuyText($arResult); ?>

                    
					
					<?if(  $arResult['CATALOG_QUANTITY'] < 1 ) {?>
					
						<div class="catalog-item-one-click" style='margin-bottom: 34px;'>
						<a href="#learn-about-admission" class="button-buy dark-blue learn-about-admission-button" ><span>Узнать о поступлении</span></a>
						</div>
					<?}else{?>
					
						<div class="catalog-item-buy">
						<?if($canBuy){?>
							<a href="<?=$addUrl?>" data-action="add-to-basket" class="button-buy"><span><?= $buyText ?></span></a>
							<!--<? if($arResult['CATALOG_QUANTITY'] <= 0) { ?>
								<span class="not-available">Под заказ</span>
							<? } ?> -->
						<?}else{?>
							<a href="#preorder" data-action="preorder" data-item-title="<?= $arResult['NAME'] ?>" data-item-url="<?= $arResult['DETAIL_PAGE_URL'] ?>" class="button not-available">Под заказ</a>
						<?}?>
						</div>
						
						
						<div class="catalog-item-one-click">
						<? if($canBuy): ?>
							<a href="#one-click" class="button-buy dark-blue"><span>Быстрый заказ</span></a>
						<? endif; ?>
						</div>
					
					
					<?}?>
					
					
                    <div class="labels-before">
                        <?php
                            if ($arResult['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                                ?>
                                <a class="link" href="/payment-and-delivery/">Бесплатная доставка</a>
                                <?php
                            }
                            if ($arResult['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                                ?>
                                <span class="link">Бесплатная установка</span>
                                <?php
                            }
                        ?>
                    </div>
                    <div class="space"></div>
                <? else: ?>
                    <? if($arResult['CATALOG_QUANTITY'] > 0) { ?>
                        <div class="catalog-item-no-price">
                            <a href="#preorder" class="in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $arResult['NAME'] ?>" data-item-url="<?=$arResult['DETAIL_PAGE_URL'];?>" data-form-name="Уточнить цену" data-form-submit="Запросить" id="preorder-stock">Есть в наличии. Цену уточняйте у консультантов</a>
                        </div>
                    <? } else { ?>
                        <?/* $brmd = strip_tags($arResult['BRAND_LINK']);
                         if ($brmd!='Gaggenau') {}  else  echo' <style> .catalog-item-block .catalog-price-block {background:#fff;margin:0;border:0;}</style>'; */?>
                        <div class="catalog-item-no-price">
                            <a href="#preorder" class="not-in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $arResult['NAME'] ?>" data-item-url="<?=$arResult['DETAIL_PAGE_URL'];?>">Товар под заказ. Цену уточняйте у консультантов</a>
                        </div>
                    <? } ?>
                    <div class="labels-before no-price">
                        <?php
                        if ($arResult['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                            ?>
                            <a class="link" href="/payment-and-delivery/">Бесплатная доставка</a>
                            <?php
                        }
                        if ($arResult['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                            ?>
                            <span class="link">Бесплатная установка</span>
                            <?php
                        }
                        ?>
                    </div>
                <? endif; ?>
            </span>
        <? } else { ?>
			<div class="out-of-production-item-card">Снят с производства</div>
        <? } ?>
    </div>

    <? $priceAndBuyBlock = ob_get_clean(); ?>
	<?= $priceAndBuyBlock ?>

	<?/*if ($arParams['DISPLAY_COMPARE']){?>
	    <div class="catalog-compare-block">
	        <span class="icon icon-compare"></span>
	        <a href="<?=$compareUrl?>" class="compare" data-action="add-to-compare">Сравнить</a>
	    </div>
	<?}*/?>
	<?//COMPARE				
	if ($arParams['DISPLAY_COMPARE']){?>
			<div class="compare_check_box"><div><input data-attr-iblock="<?=$arResult['IBLOCK_ID']?>" data-attr-id="<?=$arResult['ID']?>" type="checkbox" id="compare_<?=$arResult['ID']?>" ><span>Сравнить</span></div></div>
	<?
	}								
	?>


<?if (!empty($arResult['DISPLAY_PROPERTIES']) && false){?>
<div class="item_info_section">
	<?if (!empty($arResult['DISPLAY_PROPERTIES'])){?>
		<?foreach ($arResult['DISPLAY_PROPERTIES'] as $arOneProp){?>
		<div>
			<?=$arOneProp['NAME'];?>:
			<?=(is_array($arOneProp['DISPLAY_VALUE']) ? implode(' / ', $arOneProp['DISPLAY_VALUE']) : $arOneProp['DISPLAY_VALUE']); ?>
		</div>
		<?}?>
	<?}?>
</div>
<?}?>
    <div class="catalog-tabs-block">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Характеристики</a></li>
                <?

                $propertyDetailText = $arResult['PROPERTIES']["DETAILDESCRIPTION"]['VALUE']['TEXT'] ?? '';
                if(!empty($propertyDetailText)): ?>
                	<li><a href="#tabs-2">Особенности</a></li>
                <? endif; ?>
                <!--<li><a href="#tabs-3">Условия покупки</a></li>-->
                <li><a href="#tabs-5">Доставка</a></li>
                <li><a href="#tabs-4">Отзывы покупателей</a></li>
            </ul>
            <div id="tabs-1">
                <div class="features-wrap">
                    <div class="features-buttons">
                        <a href="#" title="Кратко" class="active hide-toggle-block">Кратко</a>
                        <a href="#" title="Полностью" class="show-toggle-block">Полностью</a>
                    </div>
                    <script>
                        console.log(<?=json_encode($arResult)?>);
                    </script>
                    <div class="features-body toggle-block">
                        <div class="table_wrap" itemprop="description">
                            <table cellspacing="0">
                                <tbody>
                                    <? $excludedPropertiesCodes = array('_FREE_DELIVERY', '_FREE_SETUP', 'NEW', 'HIT', 'SPECIAL_OFFER', 'NO_PRICE');

                                    foreach ($arResult['DISPLAY_PROPERTIES'] as $key => $arOneProp) {

                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_PRIORITY') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_PRIORITY2') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_PRIORITY3') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_CHANGEFREQ') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_CHANGEFREQ2') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_CHANGEFREQ3') continue;
                                        if ($arOneProp['CODE'] == 'SMARTSITEMAP_CHANGEFREQ3') continue;
                                        if ($arOneProp['CODE'] == 'DATA_DOSTAVKI_DAY_BX') continue;
                                        if ($arOneProp['CODE'] == 'DATA_DOSTAVKI_BX') $arOneProp['NAME'] = 'Дата доставки' ;
             

                                        if(($arOneProp['CODE']!='MARKET_DESCRIPTION')and($arOneProp['CODE']!='PROP_SHOW_ON_MAIN')and($arOneProp['CODE']!='ARTNUMBER')and($arOneProp['CODE'] != 'SMARTSITEMAP_PRIORITY')and($arOneProp['CODE'] != 'SMARTSITEMAP_CHANGEFREQ') && $arOneProp['CODE'] != 'DESCRIPTION' && $arOneProp['CODE'] != 'ART_FILE'){
											if (!in_array($arOneProp['CODE'], $excludedPropertiesCodes)) {
												if (!is_array($arOneProp['DISPLAY_VALUE'])) {
													if (trim($arOneProp['DISPLAY_VALUE']) == 'Y') {
														$arOneProp['DISPLAY_VALUE'] = "Есть";
													}
												} ?>
												<?
												if($arOneProp['CODE']=='MARKET_DESCRIPTION'){
													$arOneProp['DISPLAY_VALUE'] = str_replace(array("<![CDATA[","]]>","<h3>","</h3>"),"",htmlspecialchars_decode($arOneProp['DISPLAY_VALUE']));;
												}
												?>
                                                <? if($arOneProp['CODE']=='EXPORT_DESCRIPTION') {?>
                                                    <td style="border-bottom: none;" class="feature-value" colspan='2'>
                                                        <div class="additional-items" >
                                                            <div class="additional-items" id="additional-items">
                                                                <?=(is_array($arOneProp['DISPLAY_VALUE']) ? implode(' / ', $arOneProp['DISPLAY_VALUE']) : $arOneProp['DISPLAY_VALUE']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?} elseif($arOneProp['CODE']!='YML_DELIVERY') {?>
                                                
												<tr>
													<td class="feature-name 1"><?=$arOneProp['NAME'];?></td>
													<td class="feature-value"><?=(is_array($arOneProp['DISPLAY_VALUE']) ? implode(' / ', $arOneProp['DISPLAY_VALUE']) : $arOneProp['DISPLAY_VALUE']); ?></td>
												</tr>
                                                <?}?>
											<? }
										}
                                    } ?>
                                </tbody>
                            </table>

                            <?=$arResult['DISPLAY_PROPERTIES']['DESCRIPTION']['DISPLAY_VALUE'];?>
                        </div>
                    </div>
                </div>

            </div>
            <div id="tabs-2">
                <?
                $propertyDetailTextType =  $arResult['PROPERTIES']["DETAILDESCRIPTION"]['~VALUE']['TYPE'] ?? '';

                if($propertyDetailTextType == 'text' && $propertyDetailText):?>
                    <p><?=$propertyDetailText?></p>
                <?endif;?>
            </div>
            <!--<div id="tabs-3">
               <?/* $APPLICATION->IncludeComponent("bitrix:main.include","",Array(
					"AREA_FILE_SHOW" => "file", 
					"PATH" => SITE_DIR.'/include/usloviya_pokupki.php'
				));	*/?>
            </div>-->
<div id="tabs-4">
<?php
$APPLICATION->IncludeComponent(
	"bitrix:catalog.comments",
	"",
	array(
		"ELEMENT_ID" => $arResult['ID'],
		"ELEMENT_CODE" => "",
		"IBLOCK_ID" => $arParams['IBLOCK_ID'],
		"URL_TO_COMMENT" => "",
		"WIDTH" => "",
		"COMMENTS_COUNT" => "10",
		"BLOG_USE" => 'Y',
		"FB_USE" => 'N',
		"FB_APP_ID" => '',
		"VK_USE" => 'N',
		"VK_API_ID" => '',
		"CACHE_TYPE" => $arParams['CACHE_TYPE'],
		"CACHE_TIME" => $arParams['CACHE_TIME'],
		'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
		"BLOG_TITLE" => "",
		"BLOG_URL" => $arParams['BLOG_URL'],
		"PATH_TO_SMILE" => "",
		"EMAIL_NOTIFY" => 'Y',
		"AJAX_POST" => "Y",
		"SHOW_SPAM" => "Y",
		"SHOW_RATING" => "N",
		"FB_TITLE" => "",
		"FB_USER_ADMIN_ID" => "",
		"FB_COLORSCHEME" => "light",
		"FB_ORDER_BY" => "reverse_time",
		"VK_TITLE" => "",
		"TEMPLATE_THEME" => $arParams['~TEMPLATE_THEME']
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>
<div id="tabs-5">
<p><b>Техника с аксессуарами:</b> в пределах МКАД — бесплатно, за пределы МКАД — 40 руб./км.<br />
	<b>Аксессуары отдельно:</b> в пределах МКАД — 500 руб., за пределы МКАД — +50 руб./км.</p>
	<p>Техника Miele доставляется и подключается компанией Miele <a href="/upload/miele_delivery.pdf" target="_blank">по официальным тарифам</a>.</p>
<p>Мы доставляем заказ по указанному адресу в указанное время. Простой машины и повторная доставка оплачиваются отдельно.<br />
Платный въезд на территорию — за ваш счет.</p>
<p><em><font color="red"><b>!</b></font> Технику сопровождает экспедитор. Он не может проконсультировать вас по ее функционалу и помочь с подключением.</em></p>
<p><b>Подъем техники на этаж:</b><br />
Мелкая техника (вес до 20 кг: СВЧ-печи, вытяжки до 60 см в ширину, аксессуары) — 50 руб./этаж.<br />
Крупная техника (вес от 20 кг: стиральные, сушильные и посудомоечные машины, вытяжки, духовые шкафы) — 100 руб./этаж<br />
Холодильники тяжелее 100 кг или шире 60 см — 150 руб./этаж.<br />
Холодильники Side-by-side — 250 руб./этаж.<br />
Подъем грузовым лифтом — бесплатно, кроме холодильников Side-by-side.</p>
<p><b>Перед тем, как подписать документы о приеме товара, убедитесь:</b></p>
<ul>
	<li>в отсутствии внешних дефектов: сколов, царапин, вмятин;</li>
	<li>в полной комплектации: наличии деталей, фурнитуры, дополнительных аксессуаров.</li>
</ul>
<p><em><font color="red"><b>!</b></font> Если на улице холодно, перед подключением техника должна несколько часов постоять в теплом помещении.</em></p>
</div>

        </div>
    </div>
	<div style="color: gray;margin:7px 0;">
	*Все сведения, указанные на сайте, носят информационный характер и не являются публичной офертой. Производитель на свое усмотрение и без дополнительных уведомлений может менять комплектацию, внешний вид, страну производства и технические характеристики модели. Уточняйте подробную информацию о товаре у консультантов.
	</div>
    <? if(is_array($arResult['PROPERTIES']['DOCUMENTATION']['VALUE']) && count($arResult['PROPERTIES']['DOCUMENTATION']['VALUE']) > 0): ?>

    <div class="catalog-item-documentation">
    	<ul>
    		<? foreach ($arResult['PROPERTIES']['DOCUMENTATION']['VALUE'] as $key => $file_id): ?>
    			<? $arFile = CFile::getByID($file_id);
    			$arFile = $arFile->Fetch();
    			//dump($arFile);
    			$docTitle = $arResult['PROPERTIES']['DOCUMENTATION']['DESCRIPTION'][$key];
    			if($docTitle == ""){
    				$docTitle = $arFile['ORIGINAL_NAME'];
    			}
    			$contentType = substr($arFile['CONTENT_TYPE'], strpos($arFile['CONTENT_TYPE'], "/") + 1);
    			$filePath = CFile::GetPath($file_id);

    			?>
    			<li class="<?= $contentType ?>" ><a href="<?= $filePath ?>"><?= $docTitle ?></a> <span class="file-size">(<?= round($arFile['FILE_SIZE'] / 1000) ?> КБ)</span></li>
    		<? endforeach; ?>
    	</ul>
    </div>
	<? endif; ?>


	<?if ('' != $arResult['DETAIL_TEXT']){?>
	    <div class="catalog-item-description">
	        <h3>Описание</h3>
	        <p>
					<?if ('html' == $arResult['DETAIL_TEXT_TYPE']){
						echo $arResult['DETAIL_TEXT'];
					}else{
						echo '<p>'.$arResult['DETAIL_TEXT'].'</p>';
					}?>
	        </p>
	    </div>
	<?}?>
    
	<? $APPLICATION->IncludeComponent("bitrix:main.include","",Array(
       "AREA_FILE_SHOW" => "file", 
       "PATH" => SITE_DIR.'/include/catalog_item_questions.php'
    ));?>

	<?//= $priceAndBuyBlock ?>

	<?//= $priceAndBuyBlock ?>

<??>
</div>
</section>

<!--МОДАЛЬНОЕ ОКНО ДЛЯ БЫСТРОГО ЗАКАЗА-->
<div class="form-pupop" id="one-click" style="display:none;">

    <form name="quick_buy" action="/quick_buy.php" method="POST">

        <input type="hidden" name="product_id" value="<?= $arResult['ID'] ?>">

        <div class="form-title">Быстрый заказ</div>

        <div class="current-position">
            <img src="<?= $arPhotos[0]['SRC']; ?>" alt="<?= $arResult['NAME'] ?>">
            <h4><?= $arResult['NAME'] ?></h4>
            <strong>
                <span itemprop="price" content="<?=str_replace(" ", "", $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>">
                    <?=$arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?>
                </span>
                <span>руб.</span>
            </strong>
        </div>

        <div class="form-questions">

            <div class="field">
                <div class="right">
                    <input type="text" required placeholder="Ваше имя" name="user_name">
                </div>
            </div>

            <div class="field">
                <div class="right">
                    <input type="text" required id="user_phone" placeholder="Телефон" name="user_phone">
                </div>
            </div>

            <div class="field">
                <div class="right">
                    <input type="checkbox" required id="checkbox" name="agreement" value="true">
                    <label for="checkbox">
                        Я подтверждаю, что ознакомлен и согласен с <a href="/usloviia-soglasheniia/" target="_blank" title="Своей волей и в своем интересе даю согласие администраторам сайта на обработку следующих персональных данных с использованием и без использования средств автоматизации, в соответствии с Федеральным законом от 27.07.2006 № 152-ФЗ «О персональных данных». Я подтверждаю, что ознакомлен(а), что обработка персональных данных может осуществляться путем сбора, систематизации, накопления, хранения, уточнения (обновление, изменение), использования, передачи, обезличивания, блокирования, уничтожения. Согласие на обработку предоставленных мною персональных данных действует в течение 1 года с даты заполнения анкеты. Я уведомлен(а) о своем праве отозвать согласие на обработку персональных данных путем подачи администратору сайта письменного заявления. Подтверждаю, что ознакомлен(а) с положениями Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных», права и обязанности в области защиты персональных данных мне разъяснены.">условиями</a> предоставления данных.
                    </label>
                </div>
            </div>

            <div class="field buttons">
                <div class="catalog_top_admission">
						<input type="hidden" name="recaptcha_response" >
                </div>
                <div class="right">
                    <input type="submit" name="quick_buy" value="Заказать" class="button-primary button-popup" style="opacity: 0.5;">
                </div>
				
            </div>
        </div>

    </form>

    <script>
        $(document).ready(function() {
            $(".button-buy.dark-blue").fancybox();
            $('#user_phone').mask("+7 (999) 999-9999");
        });
    </script>
</div>

<!--МОДАЛЬНОЕ ОКНО ДЛЯ УЗНАТЬ О ПОСТУПЛЕНИИ-->

<?if(  $arResult['CATALOG_QUANTITY'] < 1 ) {?>
<div class="form-pupop" id="learn-about-admission" style="display:none;">

    <form name="quick_buy" action="/learn_about_admission.php" method="POST">

        <input type="hidden" name="product_id" value="<?= $arResult['ID'] ?>">
        <input type="hidden" name="product_name" value="<?= $arResult['NAME'] ?>">
        <input type="hidden" name="product_url" value="<?= $arResult['DETAIL_PAGE_URL'] ?>">
		<?
			/* echo '<pre style="display:none">';
			print_r($arResult);
			echo '</pre>';*/
		
		?>
        <div class="form-title">Узнать о поступлении</div>

        <div class="current-position">
            <img src="<?= $arPhotos[0]['SRC']; ?>" alt="<?= $arResult['NAME'] ?>">
            <h4><?= $arResult['NAME'] ?></h4>
            <strong>
                <span itemprop="price" content="<?=str_replace(" ", "", $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>">
                    <?=$arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?>
                </span>
                <span>руб.</span>
            </strong>
        </div>

        <div class="form-questions">

            <div class="field">
                <div class="right">
                    <input type="text" required placeholder="Ваше имя" name="user_name">
                </div>
            </div>

            <div class="field">
                <div class="right">
                    <input type="text" required id="phone" placeholder="Телефон" name="phone">
                </div>
            </div>
			<div class="field">
                <div class="right">
                    <input type="text" required id="email" placeholder="E-mail" name="email">
                </div>
            </div>

            <div class="field">
                <div class="right">
                    <input type="checkbox" required id="checkbox1" name="agreement" value="true">
                    <label for="checkbox1">
                        Я подтверждаю, что ознакомлен и согласен с <a href="/usloviia-soglasheniia/" target="_blank" title="Своей волей и в своем интересе даю согласие администраторам сайта на обработку следующих персональных данных с использованием и без использования средств автоматизации, в соответствии с Федеральным законом от 27.07.2006 № 152-ФЗ «О персональных данных». Я подтверждаю, что ознакомлен(а), что обработка персональных данных может осуществляться путем сбора, систематизации, накопления, хранения, уточнения (обновление, изменение), использования, передачи, обезличивания, блокирования, уничтожения. Согласие на обработку предоставленных мною персональных данных действует в течение 1 года с даты заполнения анкеты. Я уведомлен(а) о своем праве отозвать согласие на обработку персональных данных путем подачи администратору сайта письменного заявления. Подтверждаю, что ознакомлен(а) с положениями Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных», права и обязанности в области защиты персональных данных мне разъяснены.">условиями</a> предоставления данных.
                    </label>
                </div>
            </div>

            <div class="field buttons">
                <div class="right">
					<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                    <input type="submit" name="quick_buy" value="Заказать" class="button-primary button-popup" style="opacity: 0.5;">
                </div>

            </div>
        </div>

    </form>

    <script>
        $(document).ready(function() { 
            $('#phone').mask("+7 (999) 999-9999");
        });
    </script>
</div>

					
					
					
<?}?>
					


<div>
<?
	echo '<script type="text/javascript"> updateViewList("'.SITE_ID.'", '.$arResult['ID'].', '.$arResult['ID'].');</script>';
	$emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
	if ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET'] && !$emptyProductProperties)
	{
		?>
		<div id="<? echo $arItemIDs['BASKET_PROP_DIV']; ?>" class="global-hide">
		<?
				if (!empty($arResult['PRODUCT_PROPERTIES_FILL']))
				{
					foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo)
					{
		?>
			<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
		<?
						if (isset($arResult['PRODUCT_PROPERTIES'][$propID]))
							unset($arResult['PRODUCT_PROPERTIES'][$propID]);
					}
				}
				$emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
				if (!$emptyProductProperties)
				{
		?>
			<table>
		<?
					foreach ($arResult['PRODUCT_PROPERTIES'] as $propID => $propInfo)
					{
		?>
			<tr><td><? echo $arResult['PROPERTIES'][$propID]['NAME']; ?></td>
			<td>
		<?
						if(
							'L' == $arResult['PROPERTIES'][$propID]['PROPERTY_TYPE']
							&& 'C' == $arResult['PROPERTIES'][$propID]['LIST_TYPE']
						)
						{
							foreach($propInfo['VALUES'] as $valueID => $value)
							{
								?><label><input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?></label><br><?
							}
						}
						else
						{
							?><select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]"><?
							foreach($propInfo['VALUES'] as $valueID => $value)
							{
								?><option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option><?
							}
							?></select><?
						}
		?>
			</td></tr>
		<?
					}
		?>
			</table>
		<?
				}
		?>
		</div>
		<?
	}
	if ($arResult['MIN_PRICE']['DISCOUNT_VALUE'] != $arResult['MIN_PRICE']['VALUE']){
		$arResult['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] = -$arResult['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'];
		$arResult['MIN_BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'] = -$arResult['MIN_BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'];
	}
	$arJSParams = array(
		'CONFIG' => array(
			'USE_CATALOG' => $arResult['CATALOG'],
			'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
			'SHOW_PRICE' => (isset($arResult['MIN_PRICE']) && !empty($arResult['MIN_PRICE']) && is_array($arResult['MIN_PRICE'])),
			'SHOW_DISCOUNT_PERCENT' => ($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y'),
			'SHOW_OLD_PRICE' => ($arParams['SHOW_OLD_PRICE'] == 'Y'),
			'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
			'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
			'SHOW_BASIS_PRICE' => ($arParams['SHOW_BASIS_PRICE'] == 'Y'),
			'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
			'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y')
		),
		'VISUAL' => array(
			'ID' => $arItemIDs['ID'],
		),
		'PRODUCT_TYPE' => $arResult['CATALOG_TYPE'],
		'PRODUCT' => array(
			'ID' => $arResult['ID'],
			'PICT' => $arFirstPhoto,
			'NAME' => $arResult['~NAME'],
			'SUBSCRIPTION' => true,
			'PRICE' => $arResult['MIN_PRICE'],
			'BASIS_PRICE' => $arResult['MIN_BASIS_PRICE'],
			'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
			'SLIDER' => $arResult['MORE_PHOTO'],
			'CAN_BUY' => $arResult['CAN_BUY'],
			'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
			'QUANTITY_FLOAT' => is_double($arResult['CATALOG_MEASURE_RATIO']),
			'MAX_QUANTITY' => $arResult['CATALOG_QUANTITY'],
			'STEP_QUANTITY' => $arResult['CATALOG_MEASURE_RATIO'],
		),
		'BASKET' => array(
			'ADD_PROPS' => ($arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y'),
			'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
			'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
			'EMPTY_PROPS' => $emptyProductProperties,
			'BASKET_URL' => $arParams['BASKET_URL'],
			'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
			'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
		)
	);
	if ($arParams['DISPLAY_COMPARE'])
	{
		$arJSParams['COMPARE'] = array(
			'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
			'COMPARE_PATH' => $arParams['COMPARE_PATH']
		);
	}
	unset($emptyProductProperties);
?>

<script type="text/javascript">
var <? echo $strObName; ?> = new JCCatalogElement(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
BX.message({
	ECONOMY_INFO_MESSAGE: '<? echo GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO'); ?>',
	BASIS_PRICE_MESSAGE: '<? echo GetMessageJS('CT_BCE_CATALOG_MESS_BASIS_PRICE') ?>',
	TITLE_ERROR: '<? echo GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR') ?>',
	TITLE_BASKET_PROPS: '<? echo GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS') ?>',
	BASKET_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
	BTN_SEND_PROPS: '<? echo GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS'); ?>',
	BTN_MESSAGE_BASKET_REDIRECT: '<? echo GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT') ?>',
	BTN_MESSAGE_CLOSE: '<? echo GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE'); ?>',
	BTN_MESSAGE_CLOSE_POPUP: '<? echo GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP'); ?>',
	TITLE_SUCCESSFUL: '<? echo GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK'); ?>',
	COMPARE_MESSAGE_OK: '<? echo GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK') ?>',
	COMPARE_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR') ?>',
	COMPARE_TITLE: '<? echo GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE') ?>',
	BTN_MESSAGE_COMPARE_REDIRECT: '<? echo GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT') ?>',
	SITE_ID: '<? echo SITE_ID; ?>'
});

var _tmr = _tmr || [];
_tmr.push({type: "itemView", productid: "<?=$arResult['ID']?>", pagetype: "product", totalvalue: "<?=str_replace(" ", "", $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>", list: 1});

var dataLayer = window.dataLayer || [];
dataLayer.push({
	'google_tag_params': {
		'ecomm_pagetype': 'product',
		'ecomm_prodid': '<?=$arResult['ID']?>', // Идентификатор товара
		'ecomm_totalvalue': '<?=str_replace(" ", "", $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>' // Стоимость товара
	}
});
</script>
    <?php
$titleEnding = ' | Характеристики, описание и отзывы в интернет-магазине Euroflett';

/*
 * %1$s - заголовок H1
 * %2$s - цена товара с валютой, в рублях
 * %3$s - статус товара
 *
 * 1 статус товара «В НАЛИЧИИ» и указана его стоимость
 * 2 статус товара «В НАЛИЧИИ» но нет стоимости
 * 3 статус «Товар под заказ»
 * 4 статус товара «ОЖИДАЕТСЯ ПОСТАВКА»
 * 5 статус товара «СНЯТ С ПРОИЗВОДСТВА»
 */

$newMeta = $newMeta2 = 0;
$printPtice = false;
$status = false;

$arNewMeta = [
    'TITLE' => [
        1 => '%1$s по цене %2$s купить с бесплатной доставкой в Москве',
        2 => '%1$s купить недорого с бесплатной доставкой в Москве',
        3 => '%1$s купить недорого с бесплатной доставкой в Москве',
        4 => '%1$s по цене %2$s купить с бесплатной доставкой в Москве',
        5 => '%1$s',
    ],
    'DESCR' => [
        1 => '%1$s в официальном магазине Euroflett по цене %2$s - %3$s. Быстрая доставка в регионы, по Москве бесплатно. На странице описание модели, технические характеристики и отзывы покупателей. %1$s купить с гарантией производителя.',
        2 => '%1$s в официальном магазине Euroflett, стоимость товара уточняйте у менеджеров. Быстрая доставка в регионы, по Москве бесплатно. На странице описание модели, технические характеристики и отзывы покупателей. %1$s купить с гарантией производителя.',
        3 => '%1$s в официальном магазине Euroflett, стоимость товара уточняйте у менеджеров. Быстрая доставка в регионы, по Москве бесплатно. На странице описание модели, технические характеристики и отзывы покупателей. %1$s купить с гарантией производителя.',
        4 => '%1$s в официальном магазине Euroflett по цене %2$s. Быстрая доставка в регионы, по Москве бесплатно. На странице описание модели, технические характеристики и отзывы покупателей. %1$s купить с гарантией производителя.',
        5 => '%1$s в официальном магазине Euroflett. На странице описание модели, технические характеристики и отзывы покупателей.',
    ]
];

if ($arResult['PROPERTIES']['OUT_OF_PRODUCTION']['VALUE_XML_ID'] !== 'Y') {
    if (!$arResult['NO_PRICE']) {
        $printPtice = $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] . ' руб';
        if ($arResult['CATALOG_QUANTITY'] > 0) {
            $newMeta = 1;
            $status = 'в наличии';
        } else {
            $newMeta = 4;
        }
    } else {
        if($arResult['CATALOG_QUANTITY'] > 0) {
            $newMeta = 2;
        } else {
            $newMeta = 3;
        }
    }
} else {
    $newMeta = 5;
}
$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
    $parent_id = $ar_res['IBLOCK_SECTION_ID'];

if ($newMeta > 0) {
    $title = sprintf($arNewMeta['TITLE'][$newMeta] . $titleEnding, $arResult['NAME'], $printPtice, $status);
    $descr = sprintf($arNewMeta['DESCR'][$newMeta], $arResult['NAME'], $printPtice, $status);

    $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE'] = $title;
    $arResult['IPROPERTY_VALUES']['SECTION_META_TITLE'] = $title;
    $arResult['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION'] = $descr;
    $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'] = $descr;

    $seo_title = $title;
    $seo_description = $descr;
}

if ($parent_id==657) {
    $newMeta2 = 1;
    $arNewMeta2 = [
        'TITLE' => [
            1 => '%1$s духовой шкаф %2$s %3$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            2 => '%1$s духовой шкаф %2$s %3$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            3 => '%1$s духовой шкаф %2$s %3$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            4 => '%1$s духовой шкаф %2$s %3$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            5 => '%1$s духовой шкаф %2$s %3$s | Характеристики, описание и отзывы в интернет-магазине Euroflett',
        ],
        'DESCR' => [
            1 => 'Купить %1$s духовой шкаф %2$s %3$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            2 => 'Купить %1$s духовой шкаф %2$s %3$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            3 => 'Купить %1$s духовой шкаф %2$s %3$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            4 => 'Купить %1$s духовой шкаф %2$s %3$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            5 => 'Купить %1$s духовой шкаф %2$s %3$s в официальном магазине Euroflett. На странице описание модели, технические характеристики и отзывы покупателей.',
        ]
    ];
    $type = mb_strtoupper(mb_substr($arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], 0, 1)) . mb_substr($arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], 1);
    $title = sprintf($arNewMeta2['TITLE'][$newMeta], $type, $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE'], $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']);
    $descr = sprintf($arNewMeta2['DESCR'][$newMeta], $arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE']);
}
if ($parent_id==838) {
    $newMeta2 = 1;
    $arNewMeta2 = [
        'TITLE' => [
            1 => '%1$s варочная панель  %2$s %3$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            2 => '%1$s варочная панель  %2$s %3$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            3 => '%1$s варочная панель  %2$s %3$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            4 => '%1$s варочная панель  %2$s %3$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            5 => '%1$s варочная панель  %2$s %3$s | Характеристики, описание и отзывы в интернет-магазине Euroflett',
        ],
        'DESCR' => [
            1 => 'Купить %1$s варочная панель %2$s %3$s (%4$s) у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            2 => 'Купить %1$s варочная панель %2$s %3$s (%4$s) у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            3 => 'Купить %1$s варочная панель %2$s %3$s (%4$s) у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            4 => 'Купить %1$s варочная панель %2$s %3$s (%4$s) у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s по привлекательной цене в интернет-магазине Еврофлетт.',
            5 => 'Купить %1$s варочная панель %2$s %3$s (%4$s) в официальном магазине Euroflett. На странице описание модели, технические характеристики и отзывы покупателей.',
        ]
    ];
    $type = mb_strtoupper(mb_substr($arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], 0, 1)) . mb_substr($arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], 1);
    $title = sprintf($arNewMeta2['TITLE'][$newMeta], $type, $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE'], $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE']);
    $descr = sprintf($arNewMeta2['DESCR'][$newMeta], $arResult['PROPERTIES']['MN_CONNECTTYPE']['VALUE'], $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE'], $arResult['PROPERTIES']['G_FN']['VALUE'][0]);
}
if ($parent_id==682) {
    $newMeta2 = 1;
    $arNewMeta2 = [
        'TITLE' => [
            1 => '%1$s %2$s %3$s %5$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            2 => '%1$s %2$s %3$s %5$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            3 => '%1$s %2$s %3$s %5$s по доступной цене купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            4 => '%1$s %2$s %3$s %5$s по цене %4$s купить в Москве | Характеристики и описание %2$s %3$s в интернет-магазине Euroflett',
            5 => '%1$s %2$s %3$s %5$s | Характеристики, описание и отзывы в интернет-магазине Euroflett',
        ],
        'DESCR' => [
            1 => 'Купить %1$s %2$s %3$s %4$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s (%5$s) по привлекательной цене в интернет-магазине Еврофлетт.',
            2 => 'Купить %1$s %2$s %3$s %4$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s (%5$s) по привлекательной цене в интернет-магазине Еврофлетт.',
            3 => 'Купить %1$s %2$s %3$s %4$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s (%5$s) по привлекательной цене в интернет-магазине Еврофлетт.',
            4 => 'Купить %1$s %2$s %3$s %4$s у официального дилера. Осуществляем доставку в регионы, по Москве бесплатно. Действует гарантия производителя.  %2$s %3$s (%5$s) по привлекательной цене в интернет-магазине Еврофлетт.',
            5 => 'Купить %1$s %2$s %3$s %4$s в официальном магазине Euroflett. На странице описание модели, технические характеристики и отзывы покупателей.',
        ]
    ];
    $type = mb_strtoupper(mb_substr($arResult['PROPERTIES']['MN_KIND']['VALUE'], 0, 1)) . mb_substr($arResult['PROPERTIES']['MN_KIND']['VALUE'], 1);
    $title = sprintf($arNewMeta2['TITLE'][$newMeta], $type, $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE'], $arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'],$arResult['PROPERTIES']['MN_TYPE']['VALUE']);
    $descr = sprintf($arNewMeta2['DESCR'][$newMeta], $arResult['PROPERTIES']['MN_KIND']['VALUE'], $arResult['SECTION']['NAME'], $arResult['PROPERTIES']['MODEL']['VALUE'],$arResult['PROPERTIES']['DS_COLOR_S1']['VALUE'],$arResult['PROPERTIES']['MN_TYPE']['VALUE']);
}
// _a($arResult);

if ($newMeta2>0) {
    $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE'] = $seo_title = $title;
    $arResult['IPROPERTY_VALUES']['SECTION_META_TITLE'] = $title;
    $arResult['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION'] = $seo_description = $descr;
    $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'] = $descr;
}
$APPLICATION->SetPageProperty("title", $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE']);
$APPLICATION->SetPageProperty("description",  $arResult['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION']);

// pre($arResult['IPROPERTY_VALUES']);
// pre($newMeta);

if(($arResult['DETAIL_TEXT'] == '') && ($newMeta <= 0) && $newMeta2 <= 0) {
    if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/include/createCatalogMeta.php')) {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/include/createCatalogMeta.php';

        if(class_exists('createCatalogMeta')) {
            $meta_creator = new createCatalogMeta();
            $meta_creator -> set_show_price(!$arResult['NO_PRICE']);
            $element_meta = $meta_creator -> get_element_meta($arParams['IBLOCK_ID'], $arResult['ID']);

            //pre($element_meta);

            if($element_meta['title'] != '') {
                $arResult['DETAIL_TEXT'] = $element_meta['text'];
            }
        }
    }
}