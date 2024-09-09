<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){
		die();
	}

if(is_array($arResult['ORDER']) && count($arResult['ORDER']) > 0) {
    $APPLICATION->SetTitle('Спасибо за заказ');
    
	$APPLICATION->IncludeComponent(
		"bitrix:sale.personal.order.detail",
		"complete",
		Array(
	        "PATH_TO_LIST" => "order_list.php",
	        "PATH_TO_CANCEL" => "order_cancel.php",
	        "PATH_TO_PAYMENT" => "payment.php",
	        "ID" => $_REQUEST['ORDER_ID'],
	        "CACHE_TYPE" => "A",
	        "CACHE_TIME" => "3600",
	        "CACHE_GROUPS" => "Y",
	        "SET_TITLE" => "N",
	        "ACTIVE_DATE_FORMAT" => "d.m.Y",
	        "PREVIEW_PICTURE_WIDTH" => "110",
	        "PREVIEW_PICTURE_HEIGHT" => "110",
	        "RESAMPLE_TYPE" => "1",
	        "CUSTOM_SELECT_PROPS" => array(),
	        "PROP_1" => Array(),
	        "PROP_2" => Array(),
            "PAYMENT" => $arResult["PAYMENT"],
            "PAY_SYSTEM_LIST" => $arResult["PAY_SYSTEM_LIST"],
            "ORDER" => $arResult["ORDER"]
	    )
	);
} else {
    $APPLICATION->SetTitle('Заказ не найден');
?>
<section class="order-success">
	<div class="content-center">
		<div class="personal-data-plate">
			<div class="columns">
				<div class="order-info">
					<p>Извините, мы не смогли найти ваш заказ. Позвоните нам по номеру 8 (800) 500-75-66 или напишите на email: shop@euroflett.ru</p>
				</div>
			</div>
		</div>
	</div>
</section>
<?
}
?>