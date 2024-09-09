<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION 6 */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>

<? $notAvailableMessage = ($arParams['MESS_NOT_AVAILABLE'] != '' ? $arParams['MESS_NOT_AVAILABLE'] : GetMessageJS('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE')); ?> 

<?
$this->setFrameMode(false);

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
if (!empty($arResult['ITEMS'])){
	$templateLibrary = array('popup');
	$currencyList = '';
	if (!empty($arResult['CURRENCIES'])){
		$templateLibrary[] = 'currency';
		$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
	}
	$templateData = array(
		'TEMPLATE_CLASS' => 'bx_theme',
		'TEMPLATE_LIBRARY' => $templateLibrary,
		'CURRENCIES' => $currencyList
	);
	unset($currencyList, $templateLibrary);

	$arSkuTemplate = array();
	//Шаблон для вывода выбора торговых предложений
	if (!empty($arResult['SKU_PROPS']))
	{
		foreach ($arResult['SKU_PROPS'] as &$arProp)
		{
			$templateRow = '';
			if ('TEXT' == $arProp['SHOW_MODE']){
				$templateRow .= '<div class="bx_item_detail_size full" id="#ITEM#_prop_'.$arProp['ID'].'_cont">'.
				'<span class="bx_item_section_name_gray">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
				'<div class="bx_scu"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list">';
				foreach ($arProp['VALUES'] as $arOneValue)
				{
					$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
					$templateRow .= '<li data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-onevalue="'.$arOneValue['ID'].'" title="'.$arOneValue['NAME'].'"><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
				}
				$templateRow .= '</ul></div></div>';
			}elseif ('PICT' == $arProp['SHOW_MODE']){
				$templateRow .= '<div class="bx_item_detail_scu" id="#ITEM#_prop_'.$arProp['ID'].'_cont">'.
				'<span class="bx_item_section_name_gray">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
				'<div class="bx_scu"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" style="width: '.$strWidth.';">';
				foreach ($arProp['VALUES'] as $arOneValue){
					$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
					$templateRow .= '<li data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-onevalue="'.$arOneValue['ID'].'">'.
					'<span class="cnt"><span class="cnt_item" style="background-image:url(\''.$arOneValue['PICT']['SRC'].'\');" title="'.$arOneValue['NAME'].'"></span></span></li>';
				}
				$templateRow .= '</ul></div></div>';
			}
			$arSkuTemplate[$arProp['CODE']] = $templateRow;
		}
		unset($templateRow, $arProp);
	}


	$strElementEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
	$strElementDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
	$arElementDeleteParams = array("CONFIRM" => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));
	?>


	<div class="catalog-items section-catalog-items">
		
		<?if($arParams["MULTIPLY_ITEMS_LIST"]=="Y"):?>
		<p class="popular-h2">Хиты продаж</p>
		<?endif;?>

		<?
		foreach ($arResult['ITEMS'] as $key => $arItem){
			$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
			$strMainID = $this->GetEditAreaId($arItem['ID']);

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

			$canBuy = $arItem['CAN_BUY'];

			$buyUrl = $arItem['~BUY_URL'];
			$addUrl = $arItem['~ADD_URL'];

			?>

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
					var <? echo $strObName; ?> = new JCCatalogSection(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
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
			var <? echo $strObName; ?> = new JCCatalogSection(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
			</script>
							<?
						}
					}
				}
		?>
			</div>
			<div itеmscoрe itemtype="http://schema.org/Product">

			<div class="catalog-item ." id="<?=$strMainID;?>">
				<a href="<?=$arItem['DETAIL_PAGE_URL'];?>" class="catalog-item-image">
                    <!-- <ul class="property-flags"> -->
                        <?/*
                        if ($arItem['PROPERTIES']['_FREE_DELIVERY']['VALUE_XML_ID'] === 'Y') {
                            echo '<li class="free-delivery-flag">Бесплатная доставка*</li>';
                        }

                        if ($arItem['PROPERTIES']['_FREE_SETUP']['VALUE_XML_ID'] === 'Y') {
                            echo '<li>Бесплатная установка</li>';
                        }

                        if ($arItem['PROPERTIES']['NEW']['VALUE'] === 'Y') {
                            echo '<li>Новинка</li>';
                        }

                        if ($arItem['PROPERTIES']['HIT']['VALUE'] === 'Y') {
                            echo '<li>Хит</li>';
                        }

                        if ($arItem['PROPERTIES']['SPECIAL_OFFER']['VALUE_XML_ID'] === 'Y') {
                            echo '<li>Спецпредложение</li>';
                        }
                        */?>
                    <!-- </ul> -->
					<img itеmproр="image" src="<?=$arItem['PREVIEW_PICTURE']['SRC'];?>" alt="<?=$imgTitle;?>"/>

					<? if ($arItem['LABEL']) { ?>
						<div class="label label-discount"><?=$arItem['LABEL_VALUE'];?></div>
					<? } ?>
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
					<div class="catalog-item-category"><?
                   /* if ($arItem['BRAND_LINK']) {
						echo str_replace('<a', '<a itеmproр="brand"', $arItem['BRAND_LINK']);
						}*/?></div>
					<div class="catalog-item-title"><a itеmproр="name" href="<?=$arItem['DETAIL_PAGE_URL'];?>"><?=$productTitle;?></a></div>
				</div>
				<div class="catalog-item-price-and-buy">
					<? // TODO Показ старой цены
					/*if ($arParams['SHOW_OLD_PRICE'] == 'Y' && $arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE']){
						echo $arItem['MIN_PRICE']['PRINT_VALUE'];
					}*/ ?>

					<? if ($arItem['IS_GAGGENAU'] === true): ?>
						<div class="catalog-item-price">
							<a href="#preorder" class="in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Цену уточняйте у консультантов</a>
						</div>
                    <? elseif ($arItem['PROPERTIES']['OUT_OF_PRODUCTION']['VALUE_XML_ID'] === 'Y'): ?>
                        <div class="catalog-item-price">
							<div href="#" class="out-of-production">Снят с производства</div>
                        </div>
					<? else: ?>
						<?if(!$arItem['NO_PRICE']): ?>
							<span itеmproр="offers" itеmscoрe itemtype="http://schema.org/Offer">
							<?php include($_SERVER['DOCUMENT_ROOT']."/include/meta_include.php"); ?>
							<?php
								if (!empty($arItem['PROPERTIES']['OLDPRICE']['VALUE']))
								{
							?>
									<div class="old_price">старая цена:&nbsp;&nbsp;<div><?=number_format($arItem['PROPERTIES']['OLDPRICE']['VALUE'], 0, " ", " ");?> рyб.</div></div>
							<?php
								}
							?>
							<div class="catalog-item-price">цена:<span class="price" itеmproр="price" content="<?=str_replace(" ", "", $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE']); ?>">
								<? //Показ цены "от" или просто цены
								if ($arParams['PRODUCT_DISPLAY_MODE'] == 'N' && isset($arItem['OFFERS']) && !empty($arItem['OFFERS'])){
									echo GetMessage('CT_BCS_TPL_MESS_PRICE_SIMPLE_MODE',
										array('#PRICE#' => $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'])
									);
								} else {
									echo $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];
								} ?>
							    </span><span class="rub">рyб.</span>
                             </div>

							<?
							$buyText = getBuyText($arItem);
							$new_buy_arr = explode("?",$buyUrl);
							if ($new_buy_arr[1]) $newbuyUrl = $arItem['DETAIL_PAGE_URL']."?".$new_buy_arr[1]; else $newbuyUrl = $buyUrl;
							?>
							<div class="catalog-item-buy with-compare">
								<? if ($canBuy): ?>
									<a href="<?=$newbuyUrl?>" class="button-buy" data-action="add-to-basket"><span><?= $buyText ?></span></a>
									<? if ($arItem['CATALOG_QUANTITY']>0) echo '<div class="is-available"><link itеmproр="availability" href="http://schema.org/InStock"/>'.(!empty($arItem['PROPERTIES']['STATUS_NALICHIJA']['VALUE']) ? $arItem['PROPERTIES']['STATUS_NALICHIJA']['VALUE'] : 'В наличии').'</div>';
									else echo '<link itеmproр="availability" href="http://schema.org/InStock"/><div class="is-not-available"'.(!empty($arItem['PROPERTIES']['STATUS_NALICHIJA']['VALUE']) ? '' : ' data-availability="not-availability"').'>'.(!empty($arItem['PROPERTIES']['STATUS_NALICHIJA']['VALUE']) ? $arItem['PROPERTIES']['STATUS_NALICHIJA']['VALUE'] : '').'</div>';
									// getIsAvailableText($arItem['CATALOG_QUANTITY'] > 0); ?>
								<? else: ?>
									<a href="#preorder" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>" class="button not-available"><?= GetMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE'); ?></a>
								<? endif ?>
								<?//COMPARE				
									if ($arParams['DISPLAY_COMPARE']){?>
											<?
											//$checked = (isset($_SESSION["CATALOG_COMPARE_LIST"][$arResult['IBLOCK_ID']]["ITEMS"][$arItem['ID']])) ? 'checked' : '';
											?>
											<div class="compare_check_box"><div><input data-attr-iblock="<?=$arItem['IBLOCK_ID']?>" data-attr-id="<?=$arItem['ID']?>" type="checkbox" id="compare_<?=$arItem['ID']?>" ><span>Сравнить</span></div></div>
									<?
									}								
									?>
							</div>
						<? else:?>
							<? if ($arItem['CATALOG_QUANTITY'] > 0) { ?>
								<div class="catalog-item-price">
									<a href="#preorder" class="in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Есть в наличии.<br />Цену уточняйте у консультантов</a>
								</div>
							<? } else {
								?>
								<div class="catalog-item-price">
									<a href="#preorder" class="not-in-stock-ask-for-price" data-action="preorder" data-item-title="<?= $productTitle ?>" data-item-url="<?=$arItem['DETAIL_PAGE_URL'];?>">Товар под заказ.<br />Узнать цену...</a>
								</div>
							<? } ?>
							<?if ($arParams['DISPLAY_COMPARE']){?>
								<div class="with-compare"><div class="compare_check_box"><div><input data-attr-iblock="<?=$arItem['IBLOCK_ID']?>" data-attr-id="<?=$arItem['ID']?>" type="checkbox" id="compare_<?=$arItem['ID']?>" ><span>Сравнить</span></div></div></div>
							<?}?>
						<? endif ?>
					<? endif ?>
                    </span>
				</div>
                </div>
			</div>
		<?
		}
		?>
	</div>
	<script type="text/javascript">
	BX.message({
		BTN_MESSAGE_BASKET_REDIRECT: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_BASKET_REDIRECT'); ?>',
		BASKET_URL: '<? echo $arParams["BASKET_URL"]; ?>',
		ADD_TO_BASKET_OK: '<? echo GetMessageJS('ADD_TO_BASKET_OK'); ?>',
		TITLE_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_TITLE_ERROR') ?>',
		TITLE_BASKET_PROPS: '<? echo GetMessageJS('CT_BCS_CATALOG_TITLE_BASKET_PROPS') ?>',
		TITLE_SUCCESSFUL: '<? echo GetMessageJS('ADD_TO_BASKET_OK'); ?>',
		BASKET_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
		BTN_MESSAGE_SEND_PROPS: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_SEND_PROPS'); ?>',
		BTN_MESSAGE_CLOSE: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE') ?>',
		BTN_MESSAGE_CLOSE_POPUP: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE_POPUP'); ?>',
		COMPARE_MESSAGE_OK: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_OK') ?>',
		COMPARE_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_UNKNOWN_ERROR') ?>',
		COMPARE_TITLE: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_TITLE') ?>',
		BTN_MESSAGE_COMPARE_REDIRECT: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT') ?>',
		SITE_ID: '<? echo SITE_ID; ?>'
	});
	</script>
	<?
	echo $arResult["NAV_STRING"];
}
?>


<?
//Если компонент показывает не блок с размноженными элементами
if($arParams["MULTIPLY_ITEMS_LIST"] != "Y"):?>

<?
if(!($_GET["PAGEN_1"] > 1) && $arResult["DESCRIPTION"]){
	?>
	<div class="catalog-item-block"><div class="catalog-item-description">
		<?echo $arResult["DESCRIPTION"];?>
	</div></div>
	<?
}
?>



<?
//Если заполнено свойство "UF_SEOTITLE" в разделе, то берём именно его.
if (mb_strlen($arResult["UF_SEOTITLE"])>0) {
	$title = $arResult["UF_SEOTITLE"];
}else{
	global $USER_FIELD_MANAGER;
	$arUserFields = $USER_FIELD_MANAGER->GetUserFields("ASD_IBLOCK", $arResult["IBLOCK_ID"]);
	if (!$arResult["IBLOCK_SECTION_ID"]){

		//Если это корневой раздел, то генерируем его по одному шаблону
		//Купить [тип товара в соответствующем падеже во множественном числе] [название бренда на английском языке] в Москве, [тип товара единственном числе] [название бренда на русском языке] цены и продажа в интренет-магазине Еврофлэтт  
		//Пример реализации для страниц типа http://www.euroflett.ru/vstraivaemaya-bytovaya-tekhnika/dukhovye-shkafy/ 
		//Духовые шкафы – цены и продажа в Москве, купить духовой шкаф с доставкой в интернет-магазине духовых шкафов Еврофлэтт

		//С большой буквы
		$sectionName = mb_strtoupper(mb_substr($arResult["NAME"], 0, 1)).mb_strtolower(mb_substr($arResult["NAME"], 1));
		$sectionName_IP_ED = mb_strtolower($arResult["NAME"]);
		if (mb_strlen($arUserFields['UF_IP_ED_NAME']['VALUE'])>0) {
			$sectionName_IP_ED = mb_strtolower($arUserFields['UF_IP_ED_NAME']['VALUE']);
		}
		$sectionName_VP_ED = $sectionName_IP_ED;
		if (mb_strlen($arUserFields['UF_VP_ED_NAME']['VALUE'])>0) {
			$sectionName_VP_ED = mb_strtolower($arUserFields['UF_VP_ED_NAME']['VALUE']);
		}
		if (mb_strlen($arUserFields['UF_RP_MN_NAME']['VALUE'])>0) {
			$sectionName_RP_MN = mb_strtolower($arUserFields['UF_RP_MN_NAME']['VALUE']);
		}

		$title = $sectionName.' – цены и продажа в Москве, купить '.$sectionName_VP_ED.' с доставкой в интернет-магазине '.$sectionName_RP_MN.' Еврофлэтт';
		$description = $arResult["NAME"]." премиум брендов, подбор техники в шоуруме в Москве";
		$h1 = $arResult["NAME"];
	}else{

		//Иначе это подраздел с брендом и генерируем его по другому шаблону
		//[тип товара во множественном числе] – цены и продажа в Москве, купить [тип товара в единственном числе] с доставкой в интернет-магазине [тип товара в соответствующем падеже во множественном числе] Еврофлэтт
		//Пример реализации для страницы http://www.euroflett.ru/vstraivaemaya-bytovaya-tekhnika/parovarki/de-dietrich/  
		//Купить пароварки De Dietrich в Москве, пароварка Де Дитрих цены и продажа в интернет-магазине Еврофлэтт

		$dbParentSection = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
		$arParentSection=$dbParentSection->GetNext();

		$ruBrandName = $enBrandName = $arResult["NAME"];

		$dbBrand = CIBlockElement::GetList(Array(), $arFilter = Array("NAME" => $enBrandName, "IBLOCK_ID" => BRANDS_IBLOCK_ID), false, false, array('PROPERTY_NAME_RU'));
		if($arBrand = $dbBrand->GetNext()){
			if ($arBrand['PROPERTY_NAME_RU_VALUE']) {
				$ruBrandName = $arBrand['PROPERTY_NAME_RU_VALUE'];
			}			
		}

		$sectionName_IP_ED = mb_strtolower($arResult["NAME"]);
		if (mb_strlen($arUserFields['UF_IP_ED_NAME']['VALUE'])>0) {
			$sectionName_IP_ED = mb_strtolower($arUserFields['UF_IP_ED_NAME']['VALUE']);
		}

		//Данный костыль добавлен по той причине, что в структуре сайта не предусмотрен вывод заголовков не по правилу. Если присутвуют разделы уровня выше
		if ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/stiralnye-mashiny/s-vertikalnoi-zagruzkoi/asko/') {
						$h1 ='Стиральные машины с вертикальной загрузкой Asko';
						$title = 'Купить стиральные машины с вертикальной загрузкой Asko в Москве, стиральные машины с вертикальной загрузкой Asko цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить стиральные машины с вертикальной загрузкой Asko, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/stiralnye-mashiny/s-vertikalnoi-zagruzkoi/miele/') {
						$h1 ='Стиральные машины с вертикальной загрузкой Miele';
						$title = 'Купить стиральные машины с вертикальной загрузкой Miele в Москве, стиральные машины с вертикальной загрузкой Miele цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить стиральные машины с вертикальной загрузкой Miele, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/stiralnye-mashiny/s-vertikalnoi-zagruzkoi/') {
						$h1 ='Стиральные машины с вертикальной загрузкой';
						$title = 'Купить стиральные машины с вертикальной загрузкой в Москве, стиральные машины с вертикальной загрузкой цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить стиральные машины с вертикальной загрузкой, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
			elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/stiralnye-mashiny/s-sushkoy/') {
						$h1 ='Стиральные машины с сушкой';
						$title = 'Купить стиральные машины с сушкой в Москве, стиральные машины с сушкой цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить стиральные машины с сушкой, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/stiralnye-mashiny/s-sushkoy/miele/') {
						$h1 ='Стиральные машины с сушкой  Miele';
						$title = 'Купить стиральные машины с сушкой Miele в Москве, стиральные машины с сушкой Miele цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить стиральные машины с сушкой Miele, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/sushilnye-mashiny/shkaf/asko/') {
						$h1 ='Сушильные шкафы ASKO';
						$title = 'Купить сушильные шкафы ASKO в Москве, сушильные шкафы ASKO цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить сушильные шкафы ASKO, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/stirka-i-sushka/sushilnye-mashiny/shkaf/') {
						$h1 ='Сушильные шкафы';
						$title = 'Купить сушильные шкафы в Москве, сушильные шкафы цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить сушильные шкафы, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/dukhovye-shkafy/gazovye/smeg/') {
						$h1 ='Духовые газовые шкафы Smeg';
						$title = 'Купить духовые газовые шкафы Smeg в Москве, духовые газовые шкафы Smeg цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить духовые газовые шкафы Smeg, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
			elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/dukhovye-shkafy/gazovye/') {
						$h1 ='Духовые газовые шкафы';
						$title = 'Купить духовые газовые шкафы в Москве, духовые газовые шкафы цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить духовые газовые шкафы, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 

		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/varochnye-paneli/gazovye/miele/') {
						$h1 ='Газовые варочные панели Miele';
						$title = 'Купить газовые варочные панели Miele в Москве, газовые варочные панели Miele цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить газовые варочные панели Miele, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 

		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/varochnye-paneli/gazovye/smeg/') {
						$h1 ='Газовые варочные панели Smeg';
						$title = 'Купить газовые варочные панели Smeg в Москве, газовые варочные панели Smeg цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить газовые варочные панели Smeg, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/varochnye-paneli/gazovye/') {
						$h1 ='Газовые варочные панели';
						$title = 'Купить газовые варочные панели в Москве, газовые варочные панели цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить газовые варочные панели, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/varochnye-paneli/indukcionnye/') {
						$h1 ='Индукционные варочные панели';
						$title = 'Купить индукционные варочные панели в Москве, индукционные варочные панели цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить индукционные варочные панели, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/varochnye-paneli/kombinirovannye/') {
						$h1 ='Комбинированные варочные панели';
						$title = 'Купить комбинированные варочные панели в Москве, комбинированные варочные панели цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить комбинированные варочные панели, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		elseif ($_SERVER['REQUEST_URI']=='/catalog/vstraivaemaya-bytovaya-tekhnika/vytyazhki/ostrovnye/') {
						$h1 ='Комбинированные варочные панели';
						$title = 'Купить комбинированные варочные панели в Москве, комбинированные варочные панели цены и продажа в интернет-магазине Еврофлэтт';
						$description = "Купить комбинированные варочные панели, подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		} 
		
					else 
		{
		$title = $arParentSection["NAME"].' '.$enBrandName.' купить в Москве, '.$sectionName_IP_ED.' '.$ruBrandName.' цены, '.mb_strtolower($arParentSection["NAME"]).' '.$enBrandName.' - продажа в интернет-магазине Еврофлэтт';
		$description = "Купить ". mb_strtolower($arParentSection["NAME"]). " ".$arResult["NAME"].", подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение";
		$h1 = $arParentSection["NAME"].' '.$enBrandName;
		}
	}
}

if (!empty($arResult["UF_SEOH1"])) 
	$h1 = $arResult["UF_SEOH1"];
if (!empty($arResult["UF_SEOKEYWORDS"])) 
	$APPLICATION->SetPageProperty("keywords", $arResult["UF_SEOKEYWORDS"]);
if (!empty($arResult["UF_SEODESCRIPTION"])) 
	$description = $arResult["UF_SEODESCRIPTION"];


$iPage = $_GET["PAGEN_1"];
$additionalPageNum = (($iPage && $iPage>1) ? " - Страница №".$iPage : "");

if (intval($iPage)>1) $title = $h1." - страница ".$iPage." продажа в Москве в интернет-магазине Еврофлэтт";

$description .= $additionalPageNum;

$templateData['TITLE'] = $title;
$templateData['DESCRIPTION'] = $description;

?>
<?$this->SetViewTarget('SectionH1');?>

   <h1><?=$h1?></h1>
<?$this->EndViewTarget();?> 
<?endif;?>

<?
//if (!$ar_res["IBLOCK_SECTION_ID"])
//{
//
//	$strPage = ($iPage ? " - Страница №".$iPage : "");
//
//	$APPLICATION->SetPageProperty("title", $arSection['IPROPERTY_VALUES']['SECTION_META_TITLE'] . $strPage);
//	$APPLICATION->SetPageProperty("description", $ar_res["NAME"]." подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение" . $strPage);
//
//	
//}
//else
//{
//
//	$res1 = CIBlockSection::GetByID($ar_res["IBLOCK_SECTION_ID"]);
//	$ar_res1=$res1->GetNext();
//
//	$strPage = ($iPage ? " - Страница №".$iPage : "");
//
//	// $APPLICATION->SetPageProperty("title", $ar_res1["NAME"]." ".$ar_res["NAME"]." в шоуруме в Москве");
//	// $APPLICATION->SetPageProperty("description", $ar_res1["NAME"]." бренда ".$ar_res["NAME"].", подбор техники в шоуруме в Москве, расширенная гарантия магазина");
//
//	//Купить пароварки De Dietrich в Москве, пароварка Де Дитрих цены и продажа в интернет-магазине Еврофлэтт ирдвтп
//	if($ar_res["ID"] == 766)
//		$ar_res1["NAME"] = "Встраиваемые ".$ar_res1["NAME"];
//
//	$APPLICATION->SetPageProperty("title", $ar_res1["NAME"]." ".$ar_res["NAME"]." в шоуруме в Москве" . $strPage);
//	$APPLICATION->SetPageProperty("description", "Купить ". $ar_res1["NAME"]. " ".$ar_res["NAME"].", подбор бытовой техники в шоуруме в Москве. Бесплатная доставка и подключение".$strPage);
//
//	if($ar_res["ID"] == 766)
//		$APPLICATION->SetPageProperty("description", $ar_res1["NAME"]. " бренда ".$ar_res["NAME"].", подбор техники в шоуруме в Москве" . $strPage);
//}
?>
<?php
// Ya ecommerce
$ids = array();
foreach ($arResult['ITEMS'] as $item) {
	$ids[] = $item['ID'];
}
$section_names = getElementsSections($ids); // /local/php_interface/init.php
?>
<script>
	var products = {
		<?foreach ($arResult['ITEMS'] as $item):?>
			"<?=$item['ID']?>" : {
				"id": "<?=$item['ID']?>", //обязательный параметр id или name
				"name" : "<?=$item['NAME']?>", //обязательный параметр id или name
				<?if (!empty($section_names[$item['ID']]['section'])):?>
				"category": "<?=$section_names[$item['ID']]['section']?>",
				<?endif;?>
				<?if (!empty($section_names[$item['ID']]['brand'])):?>
				"brand": "<?=$section_names[$item['ID']]['brand']?>",
				<?endif;?>
				"price": "<?=$item['MIN_PRICE']["VALUE"]?>" //стоимость единицы товара
			}<?if ($item !== end($arResult['ITEMS'])):?>,<?endif;?>
		<?endforeach;?>
	};
</script>
