<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["CATEGORIES"]))
	return;
?>
<? cdump($arResult) ?>
<div class="search-results">
<?foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
	<?foreach($arCategory["ITEMS"] as $i => $arItem):?>
		<?//echo $arCategory["TITLE"]?>
		<?if($category_id === "all"):?>
			<a class="search-result-item all-result" href="<?echo $arItem["URL"]?>">
				<div class="image"></div>
				<div class="element">
					<span class="all-result-title"><?echo $arItem["NAME"]?></span>
				</div>
			</a>
		<?elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):
			$arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];?>
			<a class="search-result-item" href="<?echo $arItem["URL"]?>">
				<?if (is_array($arElement["PICTURE"])):?>
				<div class="image">
					<div class="bx-image" style="background-image: url('<?echo $arElement["PICTURE"]["src"]?>')"></div>
				</div>
				<?endif;?>
				<div class="element">
					<span class="element-title"><?echo $arItem["NAME"]?></span>
					<?
					foreach($arElement["PRICES"] as $code=>$arPrice)
					{
						if ($arPrice["MIN_PRICE"] != "Y")
							continue;

						if($arPrice["CAN_ACCESS"])
						{
							if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<div class="price">
									<?=$arPrice["PRINT_DISCOUNT_VALUE"]?>
									<span class="<?= strtolower($arPrice['CURRENCY'])?>"><?= GetMessage("WP_CURRENCY_SYMBOL_".$arPrice['CURRENCY']) ?></span>
									<span class="old"><?=$arPrice["PRINT_VALUE"]?></span>
								</div>
							<?else:?>
								<div class="price"><?=$arPrice["PRINT_VALUE"]?> <span class="<?= strtolower($arPrice['CURRENCY'])?>"><?= GetMessage("WP_CURRENCY_SYMBOL_".$arPrice['CURRENCY']) ?></span></div>
							<?endif;
						}
						if ($arPrice["MIN_PRICE"] == "Y")
							break;
					}
					?>
				</div>
			</a>
		<?elseif(isset($arItem["ITEM_ID"])):?>
			<a class="search-result-item others-result" href="<?echo $arItem["URL"]?>">
				<div class="image"></div>
				<div class="element">
					<?echo $arItem["NAME"]?>
				</div>
			</a>
		<?endif;?>
	<?endforeach;?>
<?endforeach;?>
</div>