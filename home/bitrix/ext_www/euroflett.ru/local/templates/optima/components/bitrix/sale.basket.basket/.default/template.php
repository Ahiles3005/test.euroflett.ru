<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arUrls = Array(
	"delete" => $APPLICATION->GetCurPage()."?".$arParams["ACTION_VARIABLE"]."=delete&id=#ID#",
	"delay" => $APPLICATION->GetCurPage()."?".$arParams["ACTION_VARIABLE"]."=delay&id=#ID#",
	"add" => $APPLICATION->GetCurPage()."?".$arParams["ACTION_VARIABLE"]."=add&id=#ID#",
);

$arBasketJSParams = array(
	'SALE_DELETE' => GetMessage("SALE_DELETE"),
	'SALE_DELAY' => GetMessage("SALE_DELAY"),
	'SALE_TYPE' => GetMessage("SALE_TYPE"),
	'TEMPLATE_FOLDER' => $templateFolder,
	'DELETE_URL' => $arUrls["delete"],
	'DELAY_URL' => $arUrls["delay"],
	'ADD_URL' => $arUrls["add"]
);
?>
<script type="text/javascript">
	var basketJSParams = <?=CUtil::PhpToJSObject($arBasketJSParams);?>
</script>
<?
$APPLICATION->AddHeadScript($templateFolder."/script.js");

if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
{
	?>
	<div id="warning_message">
		<?
		if (is_array($arResult["WARNING_MESSAGE"]) && !empty($arResult["WARNING_MESSAGE"]))
		{
			foreach ($arResult["WARNING_MESSAGE"] as $v)
				echo ShowError($v);
		}
		?>
	</div>
	
	<?

	$normalCount = count($arResult["ITEMS"]["AnDelCanBuy"])+count($arResult["PREORDER_ITEMS"]["AnDelCanBuy"]);
	$normalCount = 0;
	foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $item)
		$normalCount += $item["QUANTITY"];
	foreach ($arResult["PREORDER_ITEMS"]["AnDelCanBuy"] as $item)
		$normalCount += $item["QUANTITY"];
	$normalHidden = ($normalCount == 0) ? "style=\"display:none\"" : "";

	$delayCount = count($arResult["ITEMS"]["DelDelCanBuy"]);
	$delayHidden = ($delayCount == 0) ? "style=\"display:none\"" : "";

	$subscribeCount = count($arResult["ITEMS"]["ProdSubscribe"]);
	$subscribeHidden = ($subscribeCount == 0) ? "style=\"display:none\"" : "";

	$naCount = count($arResult["ITEMS"]["nAnCanBuy"]);
	$naHidden = ($naCount == 0) ? "style=\"display:none\"" : "";

	?>
		<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form" id="basket_form">
			<div id="basket_form_container">
				<div class="bx_ordercart">
					<div class="bx_sort_container">
						<span><?=GetMessage("SALE_ITEMS")?></span>
						<a href="#" data-list="AnDelCanBuy" class="e_change_basket_list current" <?=$normalHidden?>><?=GetMessage("SALE_BASKET_ITEMS")?><span>&nbsp;(<?=$normalCount?>)</span></a>
						<a href="#" data-list="DelDelCanBuy" class="e_change_basket_list" <?=$delayHidden?>><?=GetMessage("SALE_BASKET_ITEMS_DELAYED")?><span>&nbsp;(<?=$delayCount?>)</span></a>
						<a href="#" data-list="ProdSubscribe" class="e_change_basket_list" <?=$subscribeHidden?>><?=GetMessage("SALE_BASKET_ITEMS_SUBSCRIBED")?><span>&nbsp;(<?=$subscribeCount?>)</span></a>
						<a href="#" data-list="nAnCanBuy" class="e_change_basket_list" <?=$naHidden?>><?=GetMessage("SALE_BASKET_ITEMS_NOT_AVAILABLE")?><span>&nbsp;(<?=$naCount?>)</span></a>
					</div>
					<?
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_new.php");
					?>
				</div>
			</div>
			<input type="hidden" name="BasketOrder" value="BasketOrder" />
			<!-- <input type="hidden" name="ajax_post" id="ajax_post" value="Y"> -->
		</form>
	<?
	// Ya ecommerce
	$ids = array();
	foreach ($arResult['ITEMS']["AnDelCanBuy"] as $item) {
		$ids[] = $item['PRODUCT_ID'];
	}
	$section_names = getElementsSections($ids); // /local/php_interface/init.php
	?>

	<script>
		var del_products = {
			<?foreach ($arResult['ITEMS']["AnDelCanBuy"] as $item):?>
			"<?=$item['PRODUCT_ID']?>" : {
				"id": "<?=$item['PRODUCT_ID']?>", //обязательный параметр id или name
				"name" : "<?=$item['NAME']?>", //обязательный параметр id или name
				<?if (!empty($section_names[$item['PRODUCT_ID']]['section'])):?>
				"category": "<?=$section_names[$item['PRODUCT_ID']]['section']?>",
				<?endif;?>
				<?if (!empty($section_names[$item['PRODUCT_ID']]['brand'])):?>
				"brand": "<?=$section_names[$item['PRODUCT_ID']]['brand']?>",
				<?endif;?>
				"price": "<?=$item['PRICE']?>" //стоимость единицы товара
			}<?if ($item !== end($arResult['ITEMS']["AnDelCanBuy"])):?>,<?endif;?>
			<?endforeach;?>
		};
	</script>
<?
}
else
{
	ShowError($arResult["ERROR_MESSAGE"]);
}
?>