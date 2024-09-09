<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);

$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder() . '/themes/' . $arParams['TEMPLATE_THEME'] . '/style.css',
	'TEMPLATE_CLASS' => 'bx_' . $arParams['TEMPLATE_THEME']
);

$notAvailableMessage = ($arParams['MESS_NOT_AVAILABLE'] != '' ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('SB_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
//Не потерять активное тп
//dump($arItem['OFFERS'][$arItem['OFFERS_SELECTED']]);

// Дополняет элемент требуемым свойством.
function fillItemProperty(&$element, $propertyCode) {
    // Получить свойство элемента.
    $propertyRaw = CIBlockElement::GetProperty($element['IBLOCK_ID'], $element['ID'], array("sort"=>"asc"), array('CODE'=>$propertyCode));
    $property = $propertyRaw->Fetch();

    // Дополнить элемент полученным свойством.
    $element['PROPERTIES'][$propertyCode] = $property;
}

//TODO Смена торговых предложений. После реализации перенести код из catalog.section
$arSkuTemplate = array();
if (is_array($arResult['SKU_PROPS']))
{
	foreach ($arResult['SKU_PROPS'] as $iblockId => $skuProps)
	{
		$arSkuTemplate[$iblockId] = array();
		foreach ($skuProps as &$arProp)
		{
			ob_start();
			if ('TEXT' == $arProp['SHOW_MODE'])
			{
				if (5 < $arProp['VALUES_COUNT'])
				{
					$strClass = 'bx_item_detail_size full';
					$strWidth = ($arProp['VALUES_COUNT'] * 20) . '%';
					$strOneWidth = (100 / $arProp['VALUES_COUNT']) . '%';
					$strSlideStyle = '';
				}
				else
				{
					$strClass = 'bx_item_detail_size';
					$strWidth = '100%';
					$strOneWidth = '20%';
					$strSlideStyle = 'display: none;';
				}
				?>
<div class="<? echo $strClass; ?>" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_cont" xmlns="http://www.w3.org/1999/html">
<span class="bx_item_section_name_gray"><? echo htmlspecialcharsex($arProp['NAME']); ?></span>
<div class="bx_size_scroller_container">
<div class="bx_size">
	<ul id="#ITEM#_prop_<? echo $arProp['ID']; ?>_list" style="width: <? echo $strWidth; ?>;"><?
				foreach ($arProp['VALUES'] as $arOneValue)
				{
				?>
	<li data-treevalue="<? echo $arProp['ID'] . '_' . $arOneValue['ID']; ?>" data-onevalue="<? echo $arOneValue['ID']; ?>" style="width: <? echo $strOneWidth; ?>;" ><i></i><span class="cnt"><? echo htmlspecialcharsex($arOneValue['NAME']); ?></span></li>
				<?
				}
	?></ul>
</div>
<div class="bx_slide_left" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_left" data-treevalue="<? echo $arProp['ID']; ?>" style="<? echo $strSlideStyle; ?>"></div>
<div class="bx_slide_right" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_right" data-treevalue="<? echo $arProp['ID']; ?>" style="<? echo $strSlideStyle; ?>"></div>
</div>
</div><?
			}
			elseif ('PICT' == $arProp['SHOW_MODE'])
			{
				if (5 < $arProp['VALUES_COUNT'])
				{
					$strClass = 'bx_item_detail_scu full';
					$strWidth = ($arProp['VALUES_COUNT'] * 20) . '%';
					$strOneWidth = (100 / $arProp['VALUES_COUNT']) . '%';
					$strSlideStyle = '';
				}
				else
				{
					$strClass = 'bx_item_detail_scu';
					$strWidth = '100%';
					$strOneWidth = '20%';
					$strSlideStyle = 'display: none;';
				}
				?>
<div class="<? echo $strClass; ?>" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_cont">
<span class="bx_item_section_name_gray"><? echo htmlspecialcharsex($arProp['NAME']); ?></span>
<div class="bx_scu_scroller_container">
<div class="bx_scu">
	<ul id="#ITEM#_prop_<? echo $arProp['ID']; ?>_list" style="width: <? echo $strWidth; ?>;"><?
				foreach ($arProp['VALUES'] as $arOneValue)
				{
				?>
	<li data-treevalue="<? echo $arProp['ID'] . '_' . $arOneValue['ID'] ?>" data-onevalue="<? echo $arOneValue['ID']; ?>" style="width: <? echo $strOneWidth; ?>; padding-top: <? echo $strOneWidth; ?>;"><i title="<? echo htmlspecialcharsbx($arOneValue['NAME']); ?>"></i>
		<span class="cnt"><span class="cnt_item" style="background-image:url('<? echo $arOneValue['PICT']['SRC']; ?>');" title="<? echo htmlspecialcharsbx($arOneValue['NAME']); ?>"></span></span>
	</li><?
				}
	?></ul>
</div>
<div class="bx_slide_left" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_left" data-treevalue="<? echo $arProp['ID']; ?>" style="<? echo $strSlideStyle; ?>"></div>
<div class="bx_slide_right" id="#ITEM#_prop_<? echo $arProp['ID']; ?>_right" data-treevalue="<? echo $arProp['ID']; ?>" style="<? echo $strSlideStyle; ?>"></div>
</div>
</div><?
			}
			$arSkuTemplate[$iblockId][$arProp['CODE']] = ob_get_contents();
			ob_end_clean();
			unset($arProp);
		}
	}
}

?>

<?
	$title = GetMessage('SB_HREF_TITLE');
	if(isset($arParams['WP_H2_TITLE']) && $arParams['WP_H2_TITLE'] != ""){
		$title = $arParams['WP_H2_TITLE'];
	}

	$linkToAll = "";
	if(isset($arParams['WP_SHOW_ALL_LINK']) && $arParams['WP_SHOW_ALL_LINK'] == 'Y'){
		$linkToAll = '<a href="'. $arParams['WP_ALL_LINK'] .'" class="heading-note">'. $arParams['WP_ALL_LINK_TEXT'].' <b>'. $allItemsCount .'</b></a>';
	}

?>

<?
$propname="\0CBitrixComponent\0__currentCounter";
$a = (array) $this->__component;
$component_counter = $a[$propname];
//$templateData['component_counter'] = $component_counter;
?>
<?if (!empty($arResult['ITEMS'])) {?>
	<section class="catalog-top catalog-popular-items" id="catalog-top-w<?=$component_counter?>">
		<?if(!$arParams["NO_SHOW_TITLE"]):?> <p class="popular-h2"><?= $title ?><?= $linkToAll ?></p><? else: ?><br><?endif;?>
		<? if (!empty($arResult['ITEMS'])): ?>
		<div class="catalog-items">
			<?
			foreach ($arResult['ITEMS'] as $key => $arItem){
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
				$strMainID = $this->GetEditAreaId($arItem['ID']);
				$canBuy = $arItem['CAN_BUY'];

				$arItemIDs = array(
					'ID' => $strMainID,
					'PICT' => $strMainID.'_pict',
					'SECOND_PICT' => $strMainID.'_secondpict',
					'STICKER_ID' => $strMainID.'_sticker',
					'SECOND_STICKER_ID' => $strMainID.'_secondsticker',
					'QUANTITY' => $strMainID.'_quantity',
					'QUANTITY_DOWN' => $strMainID.'_quant_down',
					'QUANTITY_UP' => $strMainID.'_quant_up',
					'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
					'BUY_LINK' => $strMainID.'_buy_link',
					'BASKET_ACTIONS' => $strMainID.'_basket_actions',
					'NOT_AVAILABLE_MESS' => $strMainID.'_not_avail',
					'SUBSCRIBE_LINK' => $strMainID.'_subscribe',
					'COMPARE_LINK' => $strMainID.'_compare_link',

					'PRICE' => $strMainID.'_price',
					'DSC_PERC' => $strMainID.'_dsc_perc',
					'SECOND_DSC_PERC' => $strMainID.'_second_dsc_perc',
					'PROP_DIV' => $strMainID.'_sku_tree',
					'PROP' => $strMainID.'_prop_',
					'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
					'BASKET_PROP_DIV' => $strMainID.'_basket_prop',
				);

				$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);

				$productTitle = (
				isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
					? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
					: $arItem['NAME']
				);

				$imgTitle = (
				isset($arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
					? $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
					: $arItem['NAME']
				);

				$buyUrl = $arItem['~BUY_URL'];
				$addUrl = $arItem['~ADD_URL'];
				//dump($arParams['PRODUCT_DISPLAY_MODE']);

				?>
				<div itemscоpe itеmtypе="http://schema.org/Product">

				<div class="catalog-item" id="<?=$strMainID;?>">
					<a href="<?=$arItem['DETAIL_PAGE_URL'];?>" class="catalog-item-image">
                        <? // Получить нужные свойства товара, если их еще нет.
                        $requiredPropertiesCodes = array('_FREE_DELIVERY', '_FREE_SETUP', 'NEW', 'HIT', 'SPECIAL_OFFER');

                        foreach ($requiredPropertiesCodes as $code) {
                            if (!$arItem['PROPERTIES'][$code]) {
                                fillItemProperty($arItem, $code);
                            }
                        } ?>

                        <!-- <ul class="property-flags"> -->
                            <?/*
                            if ($arItem['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                                echo '<li class="free-delivery-flag">Бесплатная доставка*</li>';
                            }

                            if ($arItem['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                                echo '<li>Бесплатная установка</li>';
                            }

                            if ($arItem['PROPERTIES']['NEW']['VALUE'] === 'Y' || strlen($arItem['PROPERTIES']['NEW']['VALUE'])) {
                                echo '<li>Новинка</li>';
                            }

                            if ($arItem['PROPERTIES']['HIT']['VALUE'] === 'Y' || strlen($arItem['PROPERTIES']['HIT']['VALUE'])) {
                                echo '<li>Хит</li>';
                            }

                            if ($arItem['PROPERTIES']['SPECIAL_OFFER']['VALUE_XML_ID'] === 'Y') {
                                echo '<li>Спецпредложение</li>';
                            }
                            */?>
                        <!-- </ul> -->

						<img itemprоp="imаge" src="<?=$arItem['PREVIEW_PICTURE']['SRC'];?>" alt="<?=$imgTitle;?>" title="<?=$imgTitle;?>" />
						<?if ($arItem['LABEL']){?>
							<div class="label label-discount"><?=$arItem['LABEL_VALUE'];?></div>
						<?}?>
					</a>
					<div class="product-labels">
						<?
							if ($arItem['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
	                        echo '
	                            <a class="product-label-link" href="/payment-and-delivery/"><div class="product-label product-label--delivery">
	                            	<div class="product-label__tips">Бесплатная доставка</div>
	                            </div></a>';
	                        }
							if ($arItem['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
	                        echo '
	                            <div class="product-label product-label--setup">
	                            	<div class="product-label__tips">Бесплатная установка</div>
	                            </div>';
	                        }
	                        if ($arItem['PROPERTIES']['NEW']['VALUE'] === 'Y' || strlen($arItem['PROPERTIES']['NEW']['VALUE'])) {
	                        echo '
	                            <div class="product-label product-label--new">
	                            	<div class="product-label__title">Новинка</div>
	                            </div>';
	                        }
	                        if ($arItem['PROPERTIES']['HIT']['VALUE'] === 'Y' || strlen($arItem['PROPERTIES']['HIT']['VALUE'])) {
	                        echo '
	                            <div class="product-label product-label--hit">
	                            	<div class="product-label__title">Хит</div>
	                            </div>';
	                        }
	                        if ($arItem['PROPERTIES']['SPECIAL_OFFER']['VALUE_XML_ID'] === 'Y') {
	                        echo '
	                            <div class="product-label product-label--special">
	                            	<div class="product-label__title">Спецпредложение</div>
	                            </div>';
	                        }
						?>
					</div>
					<div class="catalog-item-title-and-description">
						
						<div class="catalog-item-category"><?php if ($arItem['BRAND_LINK']) {
						echo str_replace('<a', '<a itemprop="brand"', $arItem['BRAND_LINK']);
						}?></div>
						<div class="catalog-item-title"><a itemprop="nаme" href="<?=$arItem['DETAIL_PAGE_URL'];?>"><?=$productTitle;?></a></div>
					</div>
					<div class="catalog-item-price-and-buy">
						
						<?if($arItem['IS_GAGGENAU'] === true): ?>
							<div class="catalog-item-price">
								<a href="#preorder" class="in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Цену уточняйте у консультантов</a>
							</div>
						<?else: ?>
							<?if (!$arItem['NO_PRICE']): ?>
                            <span itemprоp="offеrs" itemscоpe itеmtyрe="http://schema.org/Offer">
                             <?php include($_SERVER['DOCUMENT_ROOT']."/include/meta_include.php"); ?>
								<div class="catalog-item-price">цена: <span class="price" itemprоp="price">
									<?
									//Показ цены "от" или просто цены
									if ($arParams['PRODUCT_DISPLAY_MODE'] == 'N' && isset($arItem['OFFERS']) && !empty($arItem['OFFERS'])){
										echo GetMessage('CT_BCS_TPL_MESS_PRICE_SIMPLE_MODE',
											array('#PRICE#' => $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'])
										);
									}else{
										echo $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];
									}
									?>
								</span> <span class="rub">pyб.</span>
                                </div>
                                

								<? $buyText = getBuyText($arItem); ?>

								<div class="catalog-item-buy">
									<?if ($canBuy): ?>
										<a href="<?=$buyUrl?>" class="button-buy" data-action="add-to-basket"><span><?= $buyText ?></span></a>
										<? if ($arItem['CATALOG_QUANTITY']>0) echo '<div class="is-available"><link itemprop="availability" href="http://schema.org/InStock"/>В наличии</div>';
									else echo '<div class="is-not-available"><link itemprop="availability" href="http://schema.org/InStock"/>Под заказ</div>';
									//getIsAvailableText($arItem['CATALOG_QUANTITY'] > 0)
									?>
									<? else: ?>
										<a href="#preorder" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>" class="button not-available">Под заказ</a>
									<? endif; ?>
								</div>
							<? else: ?>
								<? if($arItem['CATALOG_QUANTITY'] > 0) { ?>
									<div class="catalog-item-price">
										<a href="#preorder" class="in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Есть в наличии.<br />Цену уточняйте у консультантов</a>
									</div>
								<? } else { ?>
									<div class="catalog-item-price">
										<a href="#preorder" class="not-in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Товар под заказ.<br />Узнать цену...</a>
									</div>
								<? } ?>
							<? endif;?>
						<? endif; ?>
						</span>
					</div>
                    
                    </div>

					<div class="bx_catalog_item_container global-hide">

						<?
						$showSubscribeBtn = false;
						$compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCS_TPL_MESS_BTN_COMPARE'));


						if (!isset($arItem['OFFERS']) || empty($arItem['OFFERS'])){
							//Товар без торговых предложений
						?>
							<?if ($arItem['CAN_BUY']){?>
									<a class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow">
										<?=(GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET'));?>
									</a>

								<?if ($arParams['DISPLAY_COMPARE']){?>
										<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
								<?}?>

								<?if ($arParams['USE_PRODUCT_QUANTITY'] == 'Y' && $arParams['SHOW_PRODUCT_QUANTITY'] == 'Y'){?>
									<div class="bx_catalog_item_controls_blockone">
										<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
										<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
										<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
										<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"><? echo $arItem['CATALOG_MEASURE_NAME']; ?></span>
									</div>
								<?}?>

							<?}else{?>

								<div class="bx_notavailable">
									<?echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE'));?>
								</div>

								<?if ($arParams['DISPLAY_COMPARE']){?>
									<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
								<?}?>

								<?if ($showSubscribeBtn){?>
									<a id="<? echo $arItemIDs['SUBSCRIBE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)">
										<?echo ('' != $arParams['MESS_BTN_SUBSCRIBE'] ? $arParams['MESS_BTN_SUBSCRIBE'] : GetMessage('CT_BCS_TPL_MESS_BTN_SUBSCRIBE'));?>
									</a>
								<?}?>

							<?}?>

							<?if ($arParams['SHOW_PROPERTIES'] == 'Y' && isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES'])){?>
								<div class="bx_catalog_item_articul">
									<?
									foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp)
									{
										?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
											echo (
												is_array($arOneProp['DISPLAY_VALUE'])
												? implode('<br>', $arOneProp['DISPLAY_VALUE'])
												: $arOneProp['DISPLAY_VALUE']
											);
									}
									?>
								</div>
							<?}?>

							<?
							$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
							if ($arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y' && !$emptyProductProperties){
							?>
							<div id="<? echo $arItemIDs['BASKET_PROP_DIV']; ?>" class="global-hide">
								<?
								//TODO Разобраться как это работает
								if (!empty($arItem['PRODUCT_PROPERTIES_FILL'])){
									foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo){
									?>
										<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
									<?
										if (isset($arItem['PRODUCT_PROPERTIES'][$propID]))
											unset($arItem['PRODUCT_PROPERTIES'][$propID]);
									}
								}

								$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
								if (!$emptyProductProperties){
									foreach ($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo){
										echo $arItem['PROPERTIES'][$propID]['NAME'];
										if('L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE'] && 'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']){
											foreach($propInfo['VALUES'] as $valueID => $value){
											?>
												<label>
													<input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>>
													<? echo $value; ?>
												</label><br>
											<?
											}
										}else{
										?>
											<select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]">
											<?foreach($propInfo['VALUES'] as $valueID => $value){?>
												<option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? 'selected' : ''); ?>><? echo $value; ?></option>
											<?}?>
											</select>
										<?
										}
									}
								}
								?>
							</div>
							<?
							}

							$arJSParams = array(
								'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
								'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
								'SHOW_ADD_BASKET_BTN' => false,
								'SHOW_BUY_BTN' => true,
								'SHOW_ABSENT' => true,
								'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
								'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
								'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
								'PRODUCT' => array(
									'ID' => $arItem['ID'],
									'NAME' => $productTitle,
									'PICT' => ('Y' == $arItem['SECOND_PICT'] ? $arItem['PREVIEW_PICTURE_SECOND'] : $arItem['PREVIEW_PICTURE']),
									'CAN_BUY' => $arItem["CAN_BUY"],
									'SUBSCRIPTION' => ('Y' == $arItem['CATALOG_SUBSCRIPTION']),
									'CHECK_QUANTITY' => $arItem['CHECK_QUANTITY'],
									'MAX_QUANTITY' => $arItem['CATALOG_QUANTITY'],
									'STEP_QUANTITY' => $arItem['CATALOG_MEASURE_RATIO'],
									'QUANTITY_FLOAT' => is_double($arItem['CATALOG_MEASURE_RATIO']),
									'SUBSCRIBE_URL' => $arItem['~SUBSCRIBE_URL'],
									'BASIS_PRICE' => $arItem['MIN_BASIS_PRICE']
								),
								'BASKET' => array(
									'ADD_PROPS' => ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET']),
									'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
									'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
									'EMPTY_PROPS' => $emptyProductProperties,
									'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
									'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
								),
								'VISUAL' => array(
									'ID' => $arItemIDs['ID'],
									'PICT_ID' => ('Y' == $arItem['SECOND_PICT'] ? $arItemIDs['SECOND_PICT'] : $arItemIDs['PICT']),
									'QUANTITY_ID' => $arItemIDs['QUANTITY'],
									'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
									'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
									'PRICE_ID' => $arItemIDs['PRICE'],
									'BUY_ID' => $arItemIDs['BUY_LINK'],
									'BASKET_PROP_DIV' => $arItemIDs['BASKET_PROP_DIV'],
									'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
									'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
									'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
								),
								'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
							);

							if ($arParams['DISPLAY_COMPARE']){
								$arJSParams['COMPARE'] = array(
									'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
									'COMPARE_PATH' => $arParams['COMPARE_PATH']
								);
							}
							unset($emptyProductProperties);
							?>
							<script type="text/javascript">
							var <? echo $strObName; ?> = new JCCatalogSectionBest(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
							</script>
						<?
						}else{
							// товар с торговыми предложениями
							if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']){
								$canBuy = $arItem['JS_OFFERS'][$arItem['OFFERS_SELECTED']]['CAN_BUY'];

								//TODO проверка по параметрам
								$currentOffer = $arItem['OFFERS'][0];

								$buyUrl = $currentOffer['~BUY_URL'];
								$addUrl = $currentOffer['~ADD_URL'];
								?>

								<?if ('Y' == $arParams['USE_PRODUCT_QUANTITY'] && 'Y' == $arParams['SHOW_PRODUCT_QUANTITY']){?>
									<div class="bx_catalog_item_controls_blockone">
										<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
										<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
										<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
										<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"></span>
									</div>
								<?}?>

								<?if($canBuy){?>
									<div id="<? echo $arItemIDs['BASKET_ACTIONS']; ?>" class="bx_catalog_item_controls_blocktwo">
										<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow">
											<?if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY'){
												echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCS_TPL_MESS_BTN_BUY'));
											}else{
												echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET'));
											}?>
										</a>
									</div>
								<?}else{?>
									<div class="bx_notavailable">
										<?echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE'));?>
									</div>
								<?}?>

								<?if ($arParams['DISPLAY_COMPARE']){?>
									<div class="bx_catalog_item_controls_blocktwo">
										<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
									</div>
								<?}?>

								<?
								unset($canBuy);
							}
							?>

							<?
							$boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $arItem['OFFERS_PROPS_DISPLAY']);
							$boolShowProductProps = ($arParams['SHOW_PROPERTIES'] == 'Y' && isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
							if ($boolShowProductProps || $boolShowOfferProps){
							?>
								<div class="bx_catalog_item_articul">
								<?
								if ($boolShowProductProps){
									foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp){
									?>
										<br><strong><? echo $arOneProp['NAME']; ?></strong> <?
										echo (
											is_array($arOneProp['DISPLAY_VALUE'])
											? implode(' / ', $arOneProp['DISPLAY_VALUE'])
											: $arOneProp['DISPLAY_VALUE']
										);
									}
								}

								if ($boolShowOfferProps){
									//TODO показывать свойства торговых предложений
								}
								?>
								</div>
							<?
							}

							//TODO нормально доделать выбор торогового предложение
							if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && false){
								if (!empty($arItem['OFFERS_PROP'])){
									$arSkuProps = array();
									?>
								<div class="bx_catalog_item_scu" id="<? echo $arItemIDs['PROP_DIV']; ?>">
									<?
									foreach ($arSkuTemplate as $code => $strTemplate)
									{
										if (!isset($arItem['OFFERS_PROP'][$code]))
											continue;
										echo '<div>', str_replace('#ITEM#_prop_', $arItemIDs['PROP'], $strTemplate), '</div>';
									}
									foreach ($arResult['SKU_PROPS'] as $arOneProp)
									{
										if (!isset($arItem['OFFERS_PROP'][$arOneProp['CODE']]))
											continue;
										$arSkuProps[] = array(
											'ID' => $arOneProp['ID'],
											'SHOW_MODE' => $arOneProp['SHOW_MODE'],
											'VALUES_COUNT' => $arOneProp['VALUES_COUNT']
										);
									}
									foreach ($arItem['JS_OFFERS'] as &$arOneJs)
									{
										if (0 < $arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'])
										{
											$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
											$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
										}
									}
									unset($arOneJs);
									?></div><?
									if ($arItem['OFFERS_PROPS_DISPLAY'])
									{
										foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer)
										{
											$strProps = '';
											if (!empty($arJSOffer['DISPLAY_PROPERTIES']))
											{
												foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp)
												{
													$strProps .= '<br>'.$arOneProp['NAME'].' <strong>'.(
														is_array($arOneProp['VALUE'])
														? implode(' / ', $arOneProp['VALUE'])
														: $arOneProp['VALUE']
													).'</strong>';
												}
											}
											$arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
										}
									}
									$arJSParams = array(
										'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
										'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
										'SHOW_ADD_BASKET_BTN' => false,
										'SHOW_BUY_BTN' => true,
										'SHOW_ABSENT' => true,
										'SHOW_SKU_PROPS' => $arItem['OFFERS_PROPS_DISPLAY'],
										'SECOND_PICT' => $arItem['SECOND_PICT'],
										'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
										'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
										'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
										'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
										'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
										'DEFAULT_PICTURE' => array(
											'PICTURE' => $arItem['PRODUCT_PREVIEW'],
											'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
										),
										'VISUAL' => array(
											'ID' => $arItemIDs['ID'],
											'PICT_ID' => $arItemIDs['PICT'],
											'SECOND_PICT_ID' => $arItemIDs['SECOND_PICT'],
											'QUANTITY_ID' => $arItemIDs['QUANTITY'],
											'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
											'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
											'QUANTITY_MEASURE' => $arItemIDs['QUANTITY_MEASURE'],
											'PRICE_ID' => $arItemIDs['PRICE'],
											'TREE_ID' => $arItemIDs['PROP_DIV'],
											'TREE_ITEM_ID' => $arItemIDs['PROP'],
											'BUY_ID' => $arItemIDs['BUY_LINK'],
											'ADD_BASKET_ID' => $arItemIDs['ADD_BASKET_ID'],
											'DSC_PERC' => $arItemIDs['DSC_PERC'],
											'SECOND_DSC_PERC' => $arItemIDs['SECOND_DSC_PERC'],
											'DISPLAY_PROP_DIV' => $arItemIDs['DISPLAY_PROP_DIV'],
											'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
											'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
											'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
										),
										'BASKET' => array(
											'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
											'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
											'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
											'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
											'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
										),
										'PRODUCT' => array(
											'ID' => $arItem['ID'],
											'NAME' => $productTitle
										),
										'OFFERS' => $arItem['JS_OFFERS'],
										'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
										'TREE_PROPS' => $arSkuProps,
										'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
									);
									if ($arParams['DISPLAY_COMPARE'])
									{
										$arJSParams['COMPARE'] = array(
											'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
											'COMPARE_PATH' => $arParams['COMPARE_PATH']
										);
									}
									?>
									<script type="text/javascript">
									var <? echo $strObName; ?> = new JCCatalogSectionBest(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
									</script>
									<?
								}
							}
						}
					?>
					</div>

				</div>
			<?
			}
			?>
		</div>
	<? else: ?>
		<div class="bx-nothing"><?= GetMessage("SB_NO_PRODUCTS"); ?></div>
	<?endif ?>

	<?if(!isset($arParams["ON_SECTION_PAGE"]) || $arParams["ON_SECTION_PAGE"]!="Y"):?>
		<div class="global-hide"><noindex>
		<?$APPLICATION->IncludeComponent(
			"bitrix:form.result.new", 
			".default", 
			array(
				"WEB_FORM_ID" => "2",
				"IGNORE_CUSTOM_TEMPLATE" => "N",
				"USE_EXTENDED_ERRORS" => "Y",
				"SEF_MODE" => "N",
				"CACHE_TYPE" => "A",
				"CACHE_TIME" => "3600",
				"LIST_URL" => "",
				"EDIT_URL" => "",
				"SUCCESS_URL" => "",
				"CHAIN_ITEM_TEXT" => "",
				"CHAIN_ITEM_LINK" => "",
				"SEF_FOLDER" => "",
				"WEB_FORM_ID2" => "N",
				"ITEM_TITLE_VALUE" => "",
				"ITEM_URL_VALUE" => "",
				"FORM_CONTAINER_CLASS" => "form-pupop", // yeah, i know. It's SEO, baby.
				"FORM_CONTAINER_ID" => "preorderform", // yeah, i know. It's SEO, baby.
				"VARIABLE_ALIASES" => array(
					"WEB_FORM_ID" => "PREORDER_FORM",
					"RESULT_ID" => "RESULT_ID",
				)
			),
			false
		);?>
		</noindex></div>
	<?endif;?>
	
		<script type="text/javascript">
			BX.message({
				MESS_BTN_BUY: '<? echo ('' != $arParams['MESS_BTN_BUY'] ? CUtil::JSEscape($arParams['MESS_BTN_BUY']) : GetMessageJS('SB_TPL_MESS_BTN_BUY')); ?>',
				MESS_BTN_ADD_TO_BASKET: '<? echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? CUtil::JSEscape($arParams['MESS_BTN_ADD_TO_BASKET']) : GetMessageJS('SB_TPL_MESS_BTN_ADD_TO_BASKET')); ?>',
				MESS_BTN_DETAIL: '<? echo ('' != $arParams['MESS_BTN_DETAIL'] ? CUtil::JSEscape($arParams['MESS_BTN_DETAIL']) : GetMessageJS('SB_TPL_MESS_BTN_DETAIL')); ?>',
				MESS_NOT_AVAILABLE: '<? echo ('' != $arParams['MESS_BTN_DETAIL'] ? CUtil::JSEscape($arParams['MESS_BTN_DETAIL']) : GetMessageJS('SB_TPL_MESS_BTN_DETAIL')); ?>',
				BTN_MESSAGE_BASKET_REDIRECT: '<? echo GetMessageJS('SB_CATALOG_BTN_MESSAGE_BASKET_REDIRECT'); ?>',
				BASKET_URL: '<? echo $arParams["BASKET_URL"]; ?>',
				ADD_TO_BASKET_OK: '<? echo GetMessageJS('SB_ADD_TO_BASKET_OK'); ?>',
				TITLE_ERROR: '<? echo GetMessageJS('SB_CATALOG_TITLE_ERROR') ?>',
				TITLE_BASKET_PROPS: '<? echo GetMessageJS('SB_CATALOG_TITLE_BASKET_PROPS') ?>',
				TITLE_SUCCESSFUL: '<? echo GetMessageJS('SB_ADD_TO_BASKET_OK'); ?>',
				BASKET_UNKNOWN_ERROR: '<? echo GetMessageJS('SB_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
				BTN_MESSAGE_SEND_PROPS: '<? echo GetMessageJS('SB_CATALOG_BTN_MESSAGE_SEND_PROPS'); ?>',
				BTN_MESSAGE_CLOSE: '<? echo GetMessageJS('SB_CATALOG_BTN_MESSAGE_CLOSE') ?>'
			});
		</script>
	</section>
<?}?>