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
?>
<div class="news-list">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

	$withImage = $arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"]);
	?>
	<div class="news-item<?= ($withImage ? ' with-image' : '') ?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<div class="image">
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
					<img src="<?= resizeImageGetSrc($arItem["PREVIEW_PICTURE"]["ID"],"NEWS_LIST") ?>" alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
						title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>" />
				</a>
			<?else:?>
				<img src="<?= resizeImageGetSrc($arItem["PREVIEW_PICTURE"]["ID"],"NEWS_LIST") ?>" alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
						title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>" />
			<?endif;?>
			</div>
		<?endif?>
		<div class="text">
			<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
				<div class="date"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></div>
			<?endif?>
			<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
				<div class="title">
				<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
					<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><?echo $arItem["NAME"]?></a>
				<?else:?>
					<?echo $arItem["NAME"]?>
				<?endif;?>
				</div>
			<?endif;?>

			<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
				<p class="announce"><?echo strip_tags($arItem["PREVIEW_TEXT"],'<a><span><b>');?></p>
			<?endif;?>
			
			<?if(count($arItem["FIELDS"]) > 0 || count($arItem["DISPLAY_PROPERTIES"] > 0)):?>
				<div class="properties">
				<?foreach($arItem["FIELDS"] as $code=>$value):?>
					<div class="property property-<?= strtolower($code) ?>">
					<?=GetMessage("IBLOCK_FIELD_".$code)?>:&nbsp;<?=$value;?>
					</div>
				<?endforeach;?>
				<?foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
					<div class="property property-<?= $pid ?>">
						<?=$arProperty["NAME"]?>:&nbsp;
						<?if(is_array($arProperty["DISPLAY_VALUE"])):?>
							<?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
						<?else:?>
							<?=$arProperty["DISPLAY_VALUE"];?>
						<?endif?>
					</div>
				<?endforeach;?>
				</div>
			<?endif;?>
		</div>
	</div>
<?endforeach;?>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
