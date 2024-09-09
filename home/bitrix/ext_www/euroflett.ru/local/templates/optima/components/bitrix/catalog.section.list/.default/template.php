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
if ($USER->IsAdmin()) var_dump ($arResult);
$arViewModeList = $arResult['VIEW_MODE_LIST'];

$emptyImg = SITE_TEMPLATE_PATH.'/img/no-img.png';

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));
?>

<?if ('Y' == $arParams['SHOW_PARENT_NAME'] && 0 < $arResult['SECTION']['ID']){
	$this->AddEditAction($arResult['SECTION']['ID'], $arResult['SECTION']['EDIT_LINK'], $strSectionEdit);
	$this->AddDeleteAction($arResult['SECTION']['ID'], $arResult['SECTION']['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
	?>

	<h1 id="<? echo $this->GetEditAreaId($arResult['SECTION']['ID']); ?>">
		<?echo (
			isset($arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]) && $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != ""
			? $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]
			: $arResult['SECTION']['NAME']
		);?>
	</h1>
<?}?>

<?if ($arResult["SECTIONS_COUNT"] > 0){?>
		<?
			switch ($arParams['VIEW_MODE'])
			{
				case 'TEXT':
					?>
					<nav class="catalog-submenu-horizontal">
						<ul>
							<?
							foreach ($arResult['SECTIONS'] as &$arSection){
								$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
								$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
								?>
								<li id="<? echo $this->GetEditAreaId($arSection['ID']); ?>">
									<a href="<? echo $arSection['SECTION_PAGE_URL']; ?>"><? echo $arSection['NAME']; ?></a>
									<?if ($arParams["COUNT_ELEMENTS"]){?> <span>(<? echo $arSection['ELEMENT_CNT']; ?>)</span><?}?>
								</li>
							<?
							}
							unset($arSection);
							?>
						</ul>
					</nav>
					<?
					break;
				case 'TILE':
					?>
					<section class="catalog-sections">
					<div class="catalog-sections-items">
					<?
					foreach ($arResult['SECTIONS'] as &$arSection){
						$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
						$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

						if (false === $arSection['PICTURE']){
							$arSection['PICTURE'] = array(
								'SRC' => $emptyImg,
								'ALT' => ($arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_ALT"] != '' ? $arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_ALT"] : $arSection["NAME"]),
								'TITLE' => ($arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_TITLE"] != '' ? $arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_TITLE"] : $arSection["NAME"])
							);
						}
						?>
						<div id="<? echo $this->GetEditAreaId($arSection['ID']); ?>" class="catalog-section-item">
							<a class="catalog-section-image" href="<? echo $arSection['SECTION_PAGE_URL']; ?>">
								<img src="<?=$arSection['PICTURE']['SRC'];?>" alt="<?=$arSection['PICTURE']['TITLE'];?>" title="<?=$arSection['PICTURE']['TITLE'];?>"/>
							</a>
							<?if ($arParams['HIDE_SECTION_NAME'] != 'Y'){?>
								<a href="<?=$arSection['SECTION_PAGE_URL'];?>" class="catalog-section-title">
									<span>
										<?=$arSection['NAME'];?>
										<?if ($arParams["COUNT_ELEMENTS"]){?>(<?=$arSection['ELEMENT_CNT'];?>)<?}?>
									</span>
								</a>
							<?}?>
						</div>
					<?
					}
					unset($arSection);
					?>
					</div>
					</section>
					<?
					break;
				case 'LIST':
					//TODO почистить код
					?>
					<nav class="catalog-submenu-horizontal">
						<ul>
							<?
							$intCurrentDepth = 1;
							$boolFirst = true;
							foreach ($arResult['SECTIONS'] as &$arSection){
								$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
								$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

								if ($intCurrentDepth < $arSection['RELATIVE_DEPTH_LEVEL']){
									if (0 < $intCurrentDepth)
										echo "\n",str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']),'<ul>';
								}elseif ($intCurrentDepth == $arSection['RELATIVE_DEPTH_LEVEL']){
									if (!$boolFirst)
										echo '</li>';
								}else{
									while ($intCurrentDepth > $arSection['RELATIVE_DEPTH_LEVEL']){
										echo '</li>',"\n",str_repeat("\t", $intCurrentDepth),'</ul>',"\n",str_repeat("\t", $intCurrentDepth-1);
										$intCurrentDepth--;
									}
									echo str_repeat("\t", $intCurrentDepth-1),'</li>';
								}

								echo (!$boolFirst ? "\n" : ''),str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']);
								?>

								<li id="<?=$this->GetEditAreaId($arSection['ID']);?>">
									<a href="<? echo $arSection["SECTION_PAGE_URL"]; ?>">
										<? echo $arSection["NAME"];?>
										<?if ($arParams["COUNT_ELEMENTS"]){?>
											<span>(<? echo $arSection["ELEMENT_CNT"]; ?>)</span>
										<?}?>
									</a>
								<?

								$intCurrentDepth = $arSection['RELATIVE_DEPTH_LEVEL'];
								$boolFirst = false;
							}
							unset($arSection);
							while ($intCurrentDepth > 1)
							{
								echo '</li>',"\n",str_repeat("\t", $intCurrentDepth),'</ul>',"\n",str_repeat("\t", $intCurrentDepth-1);
								$intCurrentDepth--;
							}
							if ($intCurrentDepth > 0)
							{
								echo '</li>',"\n";
							}
							?>
						</ul>
					</nav>
					<?
					break;
			}
}?>