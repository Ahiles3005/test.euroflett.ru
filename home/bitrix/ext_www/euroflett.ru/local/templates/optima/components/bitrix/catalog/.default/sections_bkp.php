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
	<div class="columns">
	<div class="left-column">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/sections_side.php"), false);?>
	</div>
	<div class="content-area">
		<section class="catalog-sections">
			<div class="catalog-sections-items">
				<?

				
				$arCurrentParentSection = explode('/', $arParams['SEF_FOLDER']);
				end($arCurrentParentSection);
				$currentParentSection = prev($arCurrentParentSection);
				global $USER_FIELD_MANAGER; 
				$rsIblocks = CIBlock::GetList(array('SORT'=>'ASC'), array('TYPE'=>'catalog', 'ACTIVE' => 'Y'), false, array('ID', 'NAME'));
				while($arIblocks = $rsIblocks->GetNext()){
	    			$arUserFields = $USER_FIELD_MANAGER->GetUserFields("ASD_IBLOCK", $arIblocks["ID"]);
	    			if ($arUserFields['UF_PARENT']["VALUE"]>0) {
		    			$rsParent = CUserFieldEnum::GetList(array(), array("ID" => $arUserFields['UF_PARENT']["VALUE"]));
				        if($arParent = $rsParent->GetNext()){
					        if($currentParentSection==$arParent['XML_ID']){
					        ?>
								<?$APPLICATION->IncludeComponent(
									"bitrix:catalog.section.list",
									"nowrap",
									array(
										"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
										"IBLOCK_ID" => $arIblocks["ID"],
										"CACHE_TYPE" => $arParams["CACHE_TYPE"],
										"CACHE_TIME" => $arParams["CACHE_TIME"],
										"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
										"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
										"TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
										"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
										"VIEW_MODE" => $arParams["SECTIONS_VIEW_TEMPLATE"],
										"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
										"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
										"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : '')
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
								?>
							<?
					        }
		    			}
					}
				}?>
			</div>
		</section>

		<?
		include($_SERVER['DOCUMENT_ROOT']."/local/seo_mod.php");
		if ($seo_text){?>
			<div class="catalog-item-block"><div class="catalog-item-description">
			<?=$seo_text;?>
			</div></div>
		<?}?>


		<?
		if($arParams["USE_COMPARE"]=="Y")
		{
			?><?$APPLICATION->IncludeComponent(
			"bitrix:catalog.compare.list",
			"",
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"NAME" => $arParams["COMPARE_NAME"],
				"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
				"COMPARE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["compare"],
				"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				'POSITION_FIXED' => isset($arParams['COMPARE_POSITION_FIXED']) ? $arParams['COMPARE_POSITION_FIXED'] : '',
				'POSITION' => isset($arParams['COMPARE_POSITION']) ? $arParams['COMPARE_POSITION'] : ''
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);?><?
		}
		?>


	</div>
</div>