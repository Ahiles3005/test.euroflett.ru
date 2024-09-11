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

$currentUrl = $APPLICATION->GetCurPage(false);
$currentSectionCode = end(array_filter(explode('/', $currentUrl)));
$arSectionsCodes = array_column($arResult['SECTIONS'], 'CODE');

if (empty($_REQUEST['set_filter']) && empty($_REQUEST['sort'])) {
	
//if ($USER->IsAdmin()) var_dump ($arResult);
    $arViewModeList = $arResult['VIEW_MODE_LIST'];

    $emptyImg = SITE_TEMPLATE_PATH . '/img/no-img.png';

    $strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
    $strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
    $arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));

    ?>

    <? if ('Y' == $arParams['SHOW_PARENT_NAME'] && 0 < $arResult['SECTION']['ID']) {
        $this->AddEditAction($arResult['SECTION']['ID'], $arResult['SECTION']['EDIT_LINK'], $strSectionEdit);
        $this->AddDeleteAction($arResult['SECTION']['ID'], $arResult['SECTION']['DELETE_LINK'], $strSectionDelete,
            $arSectionDeleteParams);
        ?>

        <?


        /* <h1 id="<? echo $this->GetEditAreaId($arResult['SECTION']['ID']); ?>">
           <?echo (
               isset($arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]) && $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != ""
               ? $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]
               : $arResult['SECTION']['NAME']
           );?>
       </h1> */ ?>
    <?
    }
//echo "<pre>"; print_r($arResult["SECTIONS"]); echo "</pre>";
    $BUFF = $arResult['SECTIONS'];
    foreach ($BUFF as $k => $S) {
        $SECTION_ID = $S['ID'];
        $activeElements = CIBlockSection::GetSectionElementsCount($SECTION_ID, Array("CNT_ACTIVE" => "Y"));
        if ($activeElements == 0) {
            unset($arResult['SECTIONS'][$k]);
        }
    }
    foreach ($arResult['SECTIONS_NO_BRANDS'] as $k => $S) {
        $SECTION_ID = $S['ID'];
        $activeElements = CIBlockSection::GetSectionElementsCount($SECTION_ID, Array("CNT_ACTIVE" => "Y"));
        if ($activeElements == 0) {
            unset($arResult['SECTIONS_NO_BRANDS'][$k]);
        }
    }
	
    ?>

    <? if (count($arResult["SECTIONS"]) > 0) { ?>
        <?
        switch ($arParams['VIEW_MODE']) {
            case 'TEXT':
                ?>
                <nav class="catalog-submenu-horizontal 111">
                    <ul>
                        <?
                        foreach ($arResult['SECTIONS'] as &$arSection) {
                            $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
                            $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete,
                                $arSectionDeleteParams);
                            ?>
                            <li id="<? echo $this->GetEditAreaId($arSection['ID']); ?>">
                                <a href="<? echo $arSection['SECTION_PAGE_URL']; ?>"><? echo $arSection['NAME']; ?></a>
                                <?
                                if ($arParams["COUNT_ELEMENTS"]) {
                                    ?> <span>(<? echo $arSection['ELEMENT_CNT']; ?>)</span><?
                                } ?>
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
                        foreach ($arResult['SECTIONS'] as &$arSection) {
                            $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
                            $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete,
                                $arSectionDeleteParams);

                            if (false === $arSection['PICTURE']) {
                                $arSection['PICTURE'] = array(
                                    'SRC' => $emptyImg,
                                    'ALT' => ($arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_ALT"] != '' ? $arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_ALT"] : $arSection["NAME"]),
                                    'TITLE' => ($arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_TITLE"] != '' ? $arSection["IPROPERTY_VALUES"]["SECTION_PICTURE_FILE_TITLE"] : $arSection["NAME"])
                                );
                            }
                            ?>
                            <?


                            $nont2 = '';
                            $nonkatbrend2 = array(
                                '999',
                                '1000',
                                '1001',
                                '1002',
                                '1003',
                                '1004',
                                '1005',
                                '1006',
                                '1007',
                                '1008',
                                '1009',
                                '1010',
                                '1011',
                                '1010',
                                '1013',
                                '1014'
                            ); // Список id Разделов которые скрываем в списке брендов
                            if (in_array($arSection['ID'], $nonkatbrend2)) {
                                $nont2 = 'style="display:none;"';
                            }

                            ?>
                            <div <?= $nont2 ?> id="<? echo $this->GetEditAreaId($arSection['ID']); ?>"
                                               class="catalog-section-item">
                                <a class="catalog-section-image" href="<? echo $arSection['SECTION_PAGE_URL']; ?>">
                                    <img src="<?= $arSection['PICTURE']['SRC']; ?>"
                                         alt="<?= $arSection['PICTURE']['TITLE']; ?>"
                                         title="<?= $arSection['PICTURE']['TITLE']; ?>"/>
                                </a>
                                <?
                                if ($arParams['HIDE_SECTION_NAME'] != 'Y') {
                                    ?>
                                    <a href="<?= $arSection['SECTION_PAGE_URL']; ?>" class="catalog-section-title">
									<span>
										<?= $arSection['NAME']; ?>
                                        <?
                                        if ($arParams["COUNT_ELEMENTS"]) {
                                            ?>(<?= $arSection['ELEMENT_CNT']; ?>)<?
                                        } ?>
									</span>
                                    </a>
                                <?
                                } ?>
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
                <nav class=" catalog-submenu-horizontal catalog-submenu-horizontal-brands">
					<a href="#" class="brand-all-show" style="display: none;">Показать всё</a>
                    <ul>
                        <?
						if ($arResult["INT_CURRENT_DEPTH"] == 2) {
							foreach ($arResult["SECTIONS"] as $sectionId => $arSection) {
								if ($arSection['IBLOCK_SECTION_ID'] == $arResult["CURRENT_SECTION"]) {
									$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
									$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
									echo "<li id=\"" . $this->GetEditAreaId($arSection['ID']) . "\"><a href=\"" . $arSection["SECTION_PAGE_URL"] . "\">" . $arSection["NAME"] . "</a>";
								}
							}
						} else {
						
							$intCurrentDepth = 1;

							$boolFirst = true;
							usort($arResult['SECTIONS'], function ($a, $b)
                            {
                                if ($a['SORT'] == $b['SORT']) {
                                    return 0;
                                }
                                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
                            });
//							global $USER;
//							if ($USER->isAdmin()) {
//							    echo '<pre>'; print_r($arResult['SECTIONS']); echo '</pre>';
//                            }
							foreach ($arResult['SECTIONS'] as &$arSection){
							$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
							$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete,
								$arSectionDeleteParams);
							//заглушка
							$arSection['RELATIVE_DEPTH_LEVEL'] = 1;

							if ($intCurrentDepth < $arSection['RELATIVE_DEPTH_LEVEL']) {
								if (0 < $intCurrentDepth) {
									echo "\n", str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']), '<ul>';
								}
							} elseif ($intCurrentDepth == $arSection['RELATIVE_DEPTH_LEVEL']) {
								if (!$boolFirst) {
									echo '</li>';
								}
							} else {
								while ($intCurrentDepth > $arSection['RELATIVE_DEPTH_LEVEL']) {
									echo '</li>', "\n", str_repeat("\t",
										$intCurrentDepth), '</ul>', "\n", str_repeat("\t", $intCurrentDepth - 1);
									$intCurrentDepth--;
								}
								echo str_repeat("\t", $intCurrentDepth - 1), '</li>';
							}

							echo(!$boolFirst ? "\n" : ''), str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']);
							?>

							<?

							$nont = '';
							$nonkatbrend = array(
								'999',
								'1000',
								'1001',
								'1002',
								'1003',
								'1004',
								'1005',
								'1006',
								'1007',
								'1008',
								'1009',
								'1010',
								'1011',
								'1010',
								'1013',
								'1014'
							); // Список id Разделов которые скрываем в списке брендов
							if (in_array($arSection['ID'], $nonkatbrend) || $arSection['DEPTH_LEVEL'] >= 3) {
								$nont = 'style="display:none;"';
							}
							?>
							<li <?= $nont ?> id="<?= $this->GetEditAreaId($arSection['ID']); ?>">
								<?

								if (!in_array($arSection['ID'], $nonkatbrend)) {
									?>
									<a href="<? echo $arSection["SECTION_PAGE_URL"]; ?>">
										<? echo $arSection["NAME"]; ?>
										<?
										if ($arParams["COUNT_ELEMENTS"]) {
											?>
											<span>(<? echo $arSection["ELEMENT_CNT"]; ?>)</span>
											<?
										} ?>
									</a>
									<?
								} ?>
								<?

								$intCurrentDepth = $arSection['RELATIVE_DEPTH_LEVEL'];
								$boolFirst = false;
								}
								unset($arSection);
								while ($intCurrentDepth > 1) {
									echo '</li>', "\n", str_repeat("\t",
										$intCurrentDepth), '</ul>', "\n", str_repeat("\t", $intCurrentDepth - 1);
									$intCurrentDepth--;
								}
								if ($intCurrentDepth > 0) {
									echo '</li>', "\n";
								}
								?>
						<?
							}
						?>
                    </ul>
                </nav>
                <?
                break;
        }
    } ?>
    <? if (count($arResult["SECTIONS_NO_BRANDS"]) > 0 && $arResult["HIDE_CATEGORIES"] != 1) {
        $this->SetViewTarget("SubSectionsNoBrands"); ?>
        <div class="catalog-submenu-horizontal catalog-submenu-sections">

            <ul>
                <?
                foreach ($arResult["SECTIONS_NO_BRANDS"] as $arSection):
					$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
					$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
				?>
                <li id="<?= $this->GetEditAreaId($arSection['ID']); ?>">
                    <a href="<?= $arSection["SECTION_PAGE_URL"] ?>"><?= $arSection["NAME"] ?></a>
                <li>
                    <?
                    endforeach; ?>
            </ul>

        </div>
        <?
        $this->EndViewTarget();
    }
}?>