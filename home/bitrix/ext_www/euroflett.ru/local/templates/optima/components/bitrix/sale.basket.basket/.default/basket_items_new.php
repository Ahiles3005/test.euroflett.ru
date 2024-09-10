<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
echo ShowError($arResult["ERROR_MESSAGE"]);

$bDelayColumn  = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bPropsColumn  = false;
$bPriceType    = false;
foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader){
	$arHeaders[] = $arHeader["id"];
}
if ($normalCount > 0):
	//krumo($arResult);
?>
	<div class="personal-cart all_basket_items">
		<?php $itemIds = array(); ?>
		<?foreach($arResult['ITEMS'] as $listName => $arList){?>
			<?if($listName == 'AnDelCanBuy'){
				$listVisiblity = 'display:block;';
			}else{
				$listVisiblity = 'display:none;';
			}?>
			<?if (count($arList)>0) {?>	
				<div class="basket_items <?=$listName?>" style="<?=$listVisiblity?>">
					<table class="cart-items" cellspacing="0" cellpadding="0">
						<tbody>
						<?foreach($arList as $arItem){
							if (strlen($arItem["PREVIEW_PICTURE_SRC"]) > 0){
								$img = $arItem["PREVIEW_PICTURE_SRC"];
							}elseif (strlen($arItem["DETAIL_PICTURE_SRC"]) > 0){
								$img = $arItem["DETAIL_PICTURE_SRC"];
							}else{
								$img = $templateFolder."/images/no_photo.png";
							}
							$hasUrl = false;
							if (strlen($arItem["DETAIL_PAGE_URL"]) > 0){$hasUrl = true;}
							$itemIds[] = $arItem["PRODUCT_ID"];
							?>
							<tr class="cart-item" data-id="<?=$arItem["ID"]?>">
								<td class="image" data-selector="image">
									<img src="<?= $img ?>">
								</td>
								<td class="title" data-selector="image">
									<? //<div class="section-title">Семга</div> ?>
									<div class="item-title" data-selector="title">
										<?if ($hasUrl){?><a href="<?=$arItem["DETAIL_PAGE_URL"] ?>"><?}?>
										<?=$arItem["NAME"]?>
										<?if ($hasUrl){?></a><?}?>
										<?
											if ($arItem['CATALOG_QUANTITY']>0){
												echo '<div class="is-available">'.(!empty($arItem['STATUS_NALICHIJA']) ? $arItem['STATUS_NALICHIJA']: 'В наличии').'</div>';
											}else{
												echo '<div class="is-not-available">'.(!empty($arItem['STATUS_NALICHIJA']) ? $arItem['STATUS_NALICHIJA'] : 'Нет в наличии').'</div>';?>
												<div class="info_block">
												*на данный товар возможно потребуется предоплата,<br>подробности уточняйте у консультантов
												</div>
												<?
											} 
										?>
									</div>
									<div class="article" data-selector="props">
										<?foreach ($arItem["PROPS"] as $val){?>
											<?=$val["NAME"]?>:&nbsp;<span><?=$val["VALUE"]?><span><br>
										<?}?>
									</div>
									<? //<div class="description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet.</div> ?>
								</td>
								<td class="price">
									<div data-selector="price"><?=$arItem["PRICE_FORMATED"]?> <span class="rub">руб.</span></div>
								</td>
								<td class="quantity">
									<?if($listName == 'AnDelCanBuy'){?>
										<a href="#" data-action="quantity-down">-</a>
										<input type="text" value="<?=$arItem['QUANTITY']?>" data-action="quantity">
										<a href="#" data-action="quantity-up">+</a>
										<input type="hidden" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem["QUANTITY"]?>" />
									<?}else{?>
										<?=$arItem['QUANTITY']?>
									<?}?>
								</td>
								<td class="total">
									<div data-selector="sum"><?=$arItem["SUM"];?> <span class="rub">руб.</span></div>
								</td>
								<td class="buttons">
									<?if($listName == 'AnDelCanBuy' && false){?>
										<a data-action="delay" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["delay"])?>"><?=GetMessage("SALE_DELAY")?></a>
										<input type="hidden" name="DELAY_<?=$arItem['ID']?>" value="N" data-selector="delay-input" />
									<?}?>
									<?if($listName == 'DelDelCanBuy'){?>
										<a data-action="return" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["add"])?>"><?=GetMessage("SALE_ADD_TO_BASKET")?></a>
										<input type="hidden" name="RETURN_<?=$arItem['ID']?>" value="N" data-selector="return-input" />
									<?}?>
									<a class="delete-item" data-action="delete" product-id="<?=$arItem["PRODUCT_ID"]?>" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["delete"])?>"></a>
									<input type="hidden" name="DELETE_<?=$arItem['ID']?>" value="N" data-selector="delete-input" />
								</td>
							</tr>
						<?}?>
						<?$quanity = 0;
						foreach ($arResult['ITEMS']['AnDelCanBuy'] as $itm)
							$quanity += $itm["QUANTITY"];?>
							<tr class="cart-total">
								<td class="image"> </td>
								<td class="title" colspan="2">Итого: <span data-selector="total-text"><?=$quanity?> <?=plural($quanity, array('товар', 'товара', 'товаров'))?></span></td>
								<td class="total" colspan="2">
									<div data-selector="total-sum"><?=$arResult["allSum_FORMATED"]?> <span class="rub">руб.</span></div>
								</td>
								<td class="buttons"> </td>
							</tr>
						</tbody>
					</table>
					<div class="cart-buttons">
					<a class="button button-return" href="/catalog/">Вернуться к покупкам</a>
					<? /* <a class="button button-order-refresh" href="#" data-action="cart-recalculate">Пересчитать</a> */?>
					<a class="button-primary button-order-submit" href="#" onclick="checkOut();">Оформить заказ</a>
					</div>
				</div>
			<?}?>
		<?}?>
		<script>
			var _tmr = _tmr || [];
			_tmr.push({type: "itemView", productid: ['<?=implode("','", $itemIds)?>'], pagetype: "cart", totalvalue: "<?=str_replace(" ", "", $arResult['allSum_FORMATED']); ?>", list: 1});
			var dataLayer = window.dataLayer || [];
				dataLayer.push({
				'google_tag_params': {
					'ecomm_pagetype': 'cart',
					'ecomm_prodid': ['<?=implode("','", $itemIds)?>'], // Идентификатор товара
					'ecomm_totalvalue': '<?=str_replace(" ", "", $arResult['allSum_FORMATED']); ?>' // Общая стоимость товара/ов
				}
			});
		</script>
	<?if (count($arResult['PREORDER_ITEMS'])>0) {?>	
	<?foreach($arResult['PREORDER_ITEMS'] as $listName => $arList){?>
		<?if($listName == 'AnDelCanBuy'){
			$listVisiblity = 'display:block;';
		}else{
			$listVisiblity = 'display:none;';
		}?>
		<h2>Заявка на товары Miele</h2>
		<div class="basket_items <?=$listName?>" style="<?=$listVisiblity?>">
			<table class="cart-items" cellspacing="0" cellpadding="0">
				<tbody>
				<?foreach($arList as $arItem){
					if (strlen($arItem["PREVIEW_PICTURE_SRC"]) > 0){
						$img = $arItem["PREVIEW_PICTURE_SRC"];
					}elseif (strlen($arItem["DETAIL_PICTURE_SRC"]) > 0){
						$img = $arItem["DETAIL_PICTURE_SRC"];
					}else{
						$img = $templateFolder."/images/no_photo.png";
					}
					$hasUrl = false;
					if (strlen($arItem["DETAIL_PAGE_URL"]) > 0){$hasUrl = true;}
					?>
					<tr class="cart-item" data-id="<?=$arItem["ID"]?>">
						<td class="image" data-selector="image">
							<img src="<?= $img ?>">
						</td>
						<td class="title" data-selector="image">
							<div class="item-title" data-selector="title">
								<?if ($hasUrl){?><a href="<?=$arItem["DETAIL_PAGE_URL"] ?>"><?}?>
								<?=$arItem["NAME"]?>
								<?if ($hasUrl){?></a><?}?>
							</div>
							<div class="article" data-selector="props">
								<?foreach ($arItem["PROPS"] as $val){?>
									<?=$val["NAME"]?>:&nbsp;<span><?=$val["VALUE"]?><span><br>
								<?}?>
							</div>
							<? //<div class="description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet.</div> ?>
						</td>
						<td class="price">
							<div data-selector="price"><?=$arItem["PRICE_FORMATED"]?> <span class="rub">руб.</span></div>
						</td>
						<td class="quantity">
							<?if($listName == 'AnDelCanBuy'){?>
								<a href="#" data-action="quantity-down">-</a>
								<input type="text" value="<?=$arItem['QUANTITY']?>" data-action="quantity">
								<a href="#" data-action="quantity-up">+</a>
								<input type="hidden" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem["QUANTITY"]?>" />
							<?}else{?>
								<?=$arItem['QUANTITY']?>
							<?}?>
						</td>
						<td class="total">
							<div data-selector="sum"><?=$arItem["SUM"];?> <span class="rub">руб.</span></div>
						</td>
						<td class="buttons">
							<?if($listName == 'AnDelCanBuy' && false){?>
								<a data-action="delay" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["delay"])?>"><?=GetMessage("SALE_DELAY")?></a>
								<input type="hidden" name="DELAY_<?=$arItem['ID']?>" value="N" data-selector="delay-input" />
							<?}?>
							<?if($listName == 'DelDelCanBuy'){?>
								<a data-action="return" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["add"])?>"><?=GetMessage("SALE_ADD_TO_BASKET")?></a>
								<input type="hidden" name="RETURN_<?=$arItem['ID']?>" value="N" data-selector="return-input" />
							<?}?>
							<a class="delete-item" data-action="delete" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["delete"])?>"></a>
							<input type="hidden" name="DELETE_<?=$arItem['ID']?>" value="N" data-selector="delete-input" />
						</td>
					</tr>
				<?}?>
				<?foreach ($arResult['PREORDER_ITEMS']['AnDelCanBuy'] as $itm)
					$quanity += $itm["QUANTITY"];
					?>
					<tr class="cart-total">
						<td class="image"> </td>
						<td class="title" colspan="2">Итого: <span data-selector="total-text-preorder"><?=$quanity?> <?=plural($quanity, array('товар', 'товара', 'товаров'))?></span></td>
						<td class="total" colspan="2">
							<div data-selector="total-sum-preorder"><?=$arResult["allPreorderSum_FORMATED"]?> <span class="rub">руб.</span></div>
						</td>
						<td class="buttons"> </td>
					</tr>
				</tbody>
			</table>
			<div class="cart-buttons">
			<a class="button button-return" href="/catalog/">Вернуться к покупкам</a>
			<? /* <a class="button button-order-refresh" href="#" data-action="cart-recalculate">Пересчитать</a> */?>
			<?if (count($arResult["ITEMS"]["AnDelCanBuy"])<1){?>
			<a class="button-primary button-order-submit" href="#" onclick="checkOut();">Оформить заказ</a>
			<?}?>
			</div>
		</div>
	<?}?>
	<?}?>

		<input type="hidden" id="column_headers" value="<?=CUtil::JSEscape(implode($arHeaders, ","))?>" />
		<input type="hidden" id="offers_props" value="<?=CUtil::JSEscape(implode($arParams["OFFERS_PROPS"], ","))?>" />
		<input type="hidden" id="action_var" value="<?=CUtil::JSEscape($arParams["ACTION_VARIABLE"])?>" />
		<input type="hidden" id="quantity_float" value="<?=$arParams["QUANTITY_FLOAT"]?>" />
		<input type="hidden" id="count_discount_4_all_quantity" value="<?=($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y") ? "Y" : "N"?>" />
		<input type="hidden" id="price_vat_show_value" value="<?=($arParams["PRICE_VAT_SHOW_VALUE"] == "Y") ? "Y" : "N"?>" />
		<input type="hidden" id="hide_coupon" value="<?=($arParams["HIDE_COUPON"] == "Y") ? "Y" : "N"?>" />
		<input type="hidden" id="coupon_approved" value="N" />
		<input type="hidden" id="use_prepayment" value="<?=($arParams["USE_PREPAYMENT"] == "Y") ? "Y" : "N"?>" />
		<?
		if ($arParams["HIDE_COUPON"] != "Y"){
			$couponClass = "";
			if (array_key_exists('VALID_COUPON', $arResult)){
				$couponClass = ($arResult["VALID_COUPON"] === true) ? "good" : "bad";
			}elseif (array_key_exists('COUPON', $arResult) && !empty($arResult["COUPON"])){
				$couponClass = "good";
			}
			?>
			<div class="coupon">
				<span><?=GetMessage("STB_COUPON_PROMT")?></span>
				<input type="text" id="coupon" name="COUPON" value="<?=$arResult["COUPON"]?>" size="21" class="<?=$couponClass?>">
			</div>
		<?}?>
	</div>

<div id="basket_items_list">

	<div class="bx_ordercart_order_pay">

		<div class="bx_ordercart_order_pay_left">
			<div class="bx_ordercart_coupon">

			</div>
		</div>
	</div>
</div>
<?
else:
?>
<div id="basket_items_list">
	<table>
		<tbody>
			<tr>
				<td colspan="<?=$numCells?>" style="text-align:center">
					<div class=""><?=GetMessage("SALE_NO_ITEMS");?></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?
endif;
if(!empty($arResult["GRID"]["ROWS"])){
$this->SetViewTarget("ecommerce");
$ecom_price = array();
$ecom_id = array();
foreach($arResult["GRID"]["ROWS"] as $product){
	$ecom_id[] = "'".$product["PRODUCT_ID"]."'";
	$ecom_price[] = "'".$product["PRICE"]."'";
}
$ecom_id_js = implode(', ',$ecom_id);
$ecom_price_js = implode(', ',$ecom_price);
$str_ecom = "<script>
  var google_tag_params = { 

  ecomm_pagetype: 'cart',

  ecomm_prodid: [".$ecom_id_js."],

  ecomm_totalvalue: [".$ecom_price_js."]

};
</script>";
$APPLICATION->SetPageProperty("ecommerce_param", "Y");
print_r($str_ecom);
$this->EndViewTarget();
}
?>