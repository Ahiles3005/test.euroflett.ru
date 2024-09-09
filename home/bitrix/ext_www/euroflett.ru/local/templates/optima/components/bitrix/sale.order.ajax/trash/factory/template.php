<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){
		die();
	}

	include __DIR__.'/classes/Order.php';

	$order = new Order($arResult, $arParams, $templateFolder);
	$order
		->createFunctions()
		->makeInitialRedirect()
		->initJsCss()
	;

	getColumnName();
?>

<div id="order_form_div" class="order-checkout">
	<div class="bx_order_make">
		<? include __DIR__.'/template_content.php' ?>
	</div>
</div>

<?
	if(CSaleLocation::isLocationProEnabled()){
		?>
			<div class="global-hide">
				<?// we need to have all styles for sale.location.selector.steps, but RestartBuffer() cuts off document head with styles in it?>
				<?$APPLICATION->IncludeComponent(
					"bitrix:sale.location.selector.steps", 
					".default", 
					array(
					),
					false
				);?>
				<?$APPLICATION->IncludeComponent(
					"bitrix:sale.location.selector.search", 
					".default", 
					array(
					),
					false
				);?>
			</div>

		<?
	}
?>