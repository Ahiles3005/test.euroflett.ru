<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

$isAjax = ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["ajax_action"]) && $_POST["ajax_action"] == "Y");

?><div class="compare-page" id="bx_catalog_compare_block"><?
if ($isAjax) {
	$APPLICATION->RestartBuffer();
}
?><div class="compare-page-tabs">
	<a class="sortbutton<? echo ($arResult["DIFFERENT"] ? ' current' : ''); ?>" href="<? echo $arResult['COMPARE_URL_TEMPLATE'].'DIFFERENT=N'; ?>" rel="nofollow"><?=GetMessage("CATALOG_ALL_CHARACTERISTICS")?></a>
	<a class="sortbutton<? echo (!$arResult["DIFFERENT"] ? ' current' : ''); ?>" href="<? echo $arResult['COMPARE_URL_TEMPLATE'].'DIFFERENT=Y'; ?>" rel="nofollow"><?=GetMessage("CATALOG_ONLY_DIFFERENT")?></a>
</div>

<? // cdump($arResult); ?>

<div class="compare-page-table">
<table class="data-table" cellspacing="0" cellpadding="0">
<?
if (!empty($arResult["SHOW_FIELDS"]))
{
	foreach ($arResult["SHOW_FIELDS"] as $code => $arProp)
	{
		$showRow = true;
		if (!isset($arResult['FIELDS_REQUIRED'][$code]) || $arResult['DIFFERENT'])
		{
			$arCompare = array();
			foreach($arResult["ITEMS"] as &$arElement)
			{
				$arPropertyValue = $arElement["FIELDS"][$code];
				if (is_array($arPropertyValue))
				{
					sort($arPropertyValue);
					$arPropertyValue = implode(" / ", $arPropertyValue);
				}
				$arCompare[] = $arPropertyValue;
			}
			unset($arElement);
			$showRow = (count(array_unique($arCompare)) > 1);
		}
		if ($showRow)
		{
			?><tr><td><span><?=GetMessage("IBLOCK_FIELD_".$code)?></span></td><?
			foreach($arResult["ITEMS"] as &$arElement)
			{
		?>


		<? // MIELE КОСТЫЛЬ
		$buyText = "Купить";
		$BRAND_MIELE = 46;
		$VACUUM_CLEANERS_SECTION_MIELE = 812;
		//cdump($arElement);
		if($arElement['PROPERTIES']['_BRAND']['VALUE'] == $BRAND_MIELE && $arElement['IBLOCK_SECTION_ID'] != $VACUUM_CLEANERS_SECTION_MIELE)
			$buyText = "Заказать";
		?>

				<td valign="top">
		<?
				switch($code)
				{
					case "NAME":
						?><div class="product-title"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement[$code]?></a></div>
						<?if($arElement["CAN_BUY"]):?>
						<div class="buttons"><noindex><a class="button button-buy" href="<?=$arElement["BUY_URL"]?>" rel="nofollow"><?=$buyText ?></a></noindex></div>
						<?elseif(!empty($arResult["PRICES"]) || is_array($arElement["PRICE_MATRIX"])):?>
						<div class="buttons not-available">Под заказ</div>
						<?endif;
						break;
					case "PREVIEW_PICTURE":
					case "DETAIL_PICTURE":
						if(is_array($arElement["FIELDS"][$code])):?>
							<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img
							border="0"
							src="<?=$arElement["FIELDS"][$code]["SRC"]?>"
							width="auto"
							height="150"
							alt="<?=$arElement["FIELDS"][$code]["ALT"]?>"
							title="<?=$arElement["FIELDS"][$code]["TITLE"]?>"
							/></a>
						<?endif;
						break;
					default:
						echo $arElement["FIELDS"][$code];
						break;
				}
			?>
				</td>
			<?
			}
			unset($arElement);
		}
	?>
	</tr>
	<?
	}
}

if (!empty($arResult["SHOW_OFFER_FIELDS"]))
{
	foreach ($arResult["SHOW_OFFER_FIELDS"] as $code => $arProp)
	{
		$showRow = true;
		if ($arResult['DIFFERENT'])
		{
			$arCompare = array();
			foreach($arResult["ITEMS"] as &$arElement)
			{
				$Value = $arElement["OFFER_FIELDS"][$code];
				if(is_array($Value))
				{
					sort($Value);
					$Value = implode(" / ", $Value);
				}
				$arCompare[] = $Value;
			}
			unset($arElement);
			$showRow = (count(array_unique($arCompare)) > 1);
		}
		if ($showRow)
		{
		?>
		<tr>
			<td><?=GetMessage("IBLOCK_OFFER_FIELD_".$code)?></td>
			<?foreach($arResult["ITEMS"] as &$arElement)
			{
			?>
			<td>
				<?=(is_array($arElement["OFFER_FIELDS"][$code])? implode(" / ", $arElement["OFFER_FIELDS"][$code]): $arElement["OFFER_FIELDS"][$code])?>
			</td>
			<?
			}
			unset($arElement);
			?>
		</tr>
		<?
		}
	}
}
?>
<tr>
	<td><?=GetMessage('CATALOG_COMPARE_PRICE');?></td>
	<?
	foreach ($arResult["ITEMS"] as &$arElement)
	{
		if (isset($arElement['MIN_PRICE']) && is_array($arElement['MIN_PRICE']))
		{
			?><td>
				<?if($arElement['NO_PRICE'] != 'Y'): ?>
					<div class="price">
					<? echo $arElement['MIN_PRICE']['PRINT_DISCOUNT_VALUE']; ?>
					<span class="<?= strtolower($arElement['MIN_PRICE']['CURRENCY']) ?>"><?= GetMessage("CURRENCY_SYMBOL_".$arElement['MIN_PRICE']['CURRENCY']) ?></span>
					</div>
				<?else: ?>
					Уточняйте у&nbsp;консультантов
				<?endif; ?>
			</div></td><?
		}
		else
		{
			?><td>&nbsp;</td><?
		}
	}
	unset($arElement);
	?>
</tr>
<?
if (!empty($arResult["SHOW_PROPERTIES"]))
{
	foreach ($arResult["SHOW_PROPERTIES"] as $code => $arProperty)
	{
		$showRow = true;
		if ($arResult['DIFFERENT'])
		{
			$arCompare = array();
			foreach($arResult["ITEMS"] as &$arElement)
			{
				$arPropertyValue = $arElement["DISPLAY_PROPERTIES"][$code]["VALUE"];
				if (is_array($arPropertyValue))
				{
					sort($arPropertyValue);
					$arPropertyValue = implode(" / ", $arPropertyValue);
				}
				$arCompare[] = $arPropertyValue;
			}
			unset($arElement);
			$showRow = (count(array_unique($arCompare)) > 1);
		}

		if ($showRow)
		{
			?>
			<tr>
				<td><?=$arProperty["NAME"]?></td>
				<?foreach($arResult["ITEMS"] as &$arElement)
				{
					?>
					<td>
						<?=(is_array($arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])? implode(" / ", $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]): $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])?>
					</td>
				<?
				}
				unset($arElement);
				?>
			</tr>
		<?
		}
	}
}

if (!empty($arResult["SHOW_OFFER_PROPERTIES"]))
{
	foreach($arResult["SHOW_OFFER_PROPERTIES"] as $code=>$arProperty)
	{
		$showRow = true;
		if ($arResult['DIFFERENT'])
		{
			$arCompare = array();
			foreach($arResult["ITEMS"] as &$arElement)
			{
				$arPropertyValue = $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["VALUE"];
				if(is_array($arPropertyValue))
				{
					sort($arPropertyValue);
					$arPropertyValue = implode(" / ", $arPropertyValue);
				}
				$arCompare[] = $arPropertyValue;
			}
			unset($arElement);
			$showRow = (count(array_unique($arCompare)) > 1);
		}
		if ($showRow)
		{
		?>
		<tr>
			<td><?=$arProperty["NAME"]?></td>
			<?foreach($arResult["ITEMS"] as &$arElement)
			{
			?>
			<td>
				<?=(is_array($arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])? implode(" / ", $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]): $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])?>
			</td>
			<?
			}
			unset($arElement);
			?>
		</tr>
		<?
		}
	}
}
	?>
	<tr>
		<td></td>
		<?foreach($arResult["ITEMS"] as &$arElement)
		{
		?>
		<td>
			<a onclick="CatalogCompareObj.MakeAjaxAction('<?=CUtil::JSEscape($arElement['~DELETE_URL'])?>');" href="javascript:void(0)" class="button button-delete"><?=GetMessage("CATALOG_REMOVE_PRODUCT")?></a>
		</td>
		<?
		}
		unset($arElement);
		?>
	</tr>
</table>
</div>
<?
if ($isAjax)
{
	die();
}

?>
<div class="catalog-item-block">
<? $APPLICATION->IncludeComponent("bitrix:main.include","",Array(
       "AREA_FILE_SHOW" => "file", 
       "PATH" => SITE_DIR.'/include/catalog_item_questions.php'
    ));?>
</div>
</div>
<script type="text/javascript">
	var CatalogCompareObj = new BX.Iblock.Catalog.CompareClass("bx_catalog_compare_block");
</script>