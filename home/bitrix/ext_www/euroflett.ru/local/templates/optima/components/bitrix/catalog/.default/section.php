<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

$this->setFrameMode(true);
if (intval($arParams["IBLOCK_ID"]) <= 0) {
    define("ERROR_404", "Y");
}
if (isset($_GET['action']) && ($_GET['action'] == 'ADD_TO_COMPARE_LIST' || $_GET['action'] == 'DELETE_FROM_COMPARE_LIST')) {
    if ($_GET['action'] == 'ADD_TO_COMPARE_LIST' && count($_SESSION['CATALOG_COMPARE_LIST'][$_GET['iblock']]['ITEMS']) >= 3) {
        $APPLICATION->RestartBuffer();
        die();
    } else {
        $APPLICATION->RestartBuffer();
        $APPLICATION->IncludeComponent(
            "bitrix:catalog.compare.list",
            "compare_ajax",
            array(
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "NAME" => $arParams["COMPARE_NAME"],
                "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                "COMPARE_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                'POSITION_FIXED' => isset($arParams['COMPARE_POSITION_FIXED']) ? $arParams['COMPARE_POSITION_FIXED'] : '',
                'POSITION' => isset($arParams['COMPARE_POSITION']) ? $arParams['COMPARE_POSITION'] : ''
            ),
            $component,
            array("HIDE_ICONS" => "Y")
        );
        die();
    }
}

if (preg_match('~^404~i', CHTTP::GetLastStatus())) {
?>
<div class="columns">
    <div class="left-column">
        <?
        $APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR . "include/sections_side.php"), false); ?>
    </div>
    <div class="content-area">
        <?
        include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        return;
        }


        $SORT = htmlspecialcharsbx($_REQUEST['sort']);
        $SHOWBY = htmlspecialcharsbx($_REQUEST['showby']);
        $ORDER = htmlspecialcharsbx($_REQUEST['order']);
        $LAYOUT = htmlspecialcharsbx($_REQUEST['layout']);

        if (!isset($SORT))
            $SORT = $arParams['ELEMENT_SORT_FIELD2'];
        if (!isset($SHOWBY))
            $SHOWBY = $arParams['PAGE_ELEMENT_COUNT'];
        if (!isset($ORDER))
            $ORDER = $arParams['ELEMENT_SORT_ORDER2'];
        if (!isset($LAYOUT))
            $LAYOUT = '.default';

        if (mb_strlen($SORT) == 0) {
            $SORT = 'qty';
            $ORDER = 'desc';
        }

        $sortBy = $SORT;
        switch ($sortBy) {
            case 'price':
                $sortBy = 'CATALOG_PRICE_2';
                break;
            case 'new':
                $sortBy = 'date_active_from';
                break;
        }

        switch ($LAYOUT) {
            case 'list':
                $LAYOUT = 'list';
                break;
            case 'tiles':
            default:
                $LAYOUT = '.default';
        }

        $arSort = array(
            array(
                'TITLE' => "Наличию",
                'NAME' => 'sort',
                'VALUE' => 'qty',
                'ORDER' => 'desc',
                'DEFAULT' => true
            ),
            array(
                'TITLE' => "Цене",
                'NAME' => 'sort',
                'VALUE' => 'price',
                'ORDER' => 'asc',
                'DEFAULT' => false
            ),
            array(
                'TITLE' => "Новизне",
                'NAME' => 'sort',
                'VALUE' => 'new',
                'ORDER' => 'desc',
                'DEFAULT' => false
            ),
        );


        ?>


        <div class="columns">
            <div class="left-column">
                <?
                // Фильтр
                $arParams['USE_FILTER'] = (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] == 'Y' ? 'Y' : 'N');
                if ($arParams['USE_FILTER'] == 'Y') {
                    $arFilter = array(
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "ACTIVE" => "Y",
                        "GLOBAL_ACTIVE" => "Y",
                    );
                    if (0 < intval($arResult["VARIABLES"]["SECTION_ID"])) {
                        $arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
                    } elseif ('' != $arResult["VARIABLES"]["SECTION_CODE"]) {
                        $arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
                    }

                    $obCache = new CPHPCache();
                    if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog")) {
                        $arCurSection = $obCache->GetVars();
                    } elseif ($obCache->StartDataCache()) {
                        $arCurSection = array();
                        if (Loader::includeModule("iblock")) {
                            $dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));

                            if (defined("BX_COMP_MANAGED_CACHE")) {
                                global $CACHE_MANAGER;
                                $CACHE_MANAGER->StartTagCache("/iblock/catalog");

                                if ($arCurSection = $dbRes->Fetch()) {
                                    $CACHE_MANAGER->RegisterTag("iblock_id_" . $arParams["IBLOCK_ID"]);
                                }
                                $CACHE_MANAGER->EndTagCache();
                            } else {
                                if (!$arCurSection = $dbRes->Fetch())
                                    $arCurSection = array();
                            }
                        }
                        $obCache->EndDataCache($arCurSection);
                    }
                    if (!isset($arCurSection)) {
                        $arCurSection = array();
                    }
                    ?>

                    <?
                    if (!function_exists('getClosestNotPseudoSection')) {
                        function getClosestNotPseudoSection($sectionId, $arFilter, $arParams)
                        {
                            $resInside = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $sectionId, 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_PSEUDO_SECTION'));
                            if ($obInside = $resInside->GetNext()) {
                                $sectionValue = unserialize(htmlspecialchars_decode($obInside['UF_PSEUDO_SECTION']));
                                if (count($sectionValue) > 0) {
                                    if ($sectionValue['is_pseudosection'] == 'Y') {
                                        $obCond = new CCCatalogCondTree();
                                        $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                                        $conditions = $obCond->Parse($sectionValue['rule']);
                                        $strEval = $obCond->Generate($conditions, array());
                                        $strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
                                        eval('$arFilterInside = ' . $strEval);
                                        foreach ($arFilterInside as $fKey => $arFElem) {
                                            $arFilter[] = array('PROP_ID' => preg_replace("/\D/", "", $fKey), 'PROP_VALUE' => $arFElem);
                                        }
                                        $sectionId = $obInside['IBLOCK_SECTION_ID'];
                                        $functionReturn = getClosestNotPseudoSection($sectionId, $arFilter, $arParams);
                                        if ($functionReturn) {
                                            $sectionId = $functionReturn['SECTION_ID'];
                                            $arFilter = array_merge($arFilter, $functionReturn['FILTER']);
                                        }
                                        return array('SECTION_ID' => $sectionId, 'FILTER' => $arFilter);
                                    }
                                }
                            }
                            return false;
                        }
                    }
                    $pseudofilter = false;
                    $sectionPath = false;
                    $res = CIBlockSection::GetList(Array("left_margin" => "DESC"), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arCurSection['ID'], 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_PSEUDO_SECTION'));
                    if ($ob = $res->GetNext()) {
                        $sectionValue = unserialize(htmlspecialchars_decode($ob['UF_PSEUDO_SECTION']));
                        if (is_array($sectionValue) && count($sectionValue) > 0) {
                            if ($sectionValue['is_pseudosection'] == 'Y') {
                                $obCond = new CCCatalogCondTree();
                                $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                                $conditions = $obCond->Parse($sectionValue['rule']);
                                $strEval = $obCond->Generate($conditions, array());
                                $strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
                                eval('$arFilter2 = ' . $strEval);
                                $arCurSection['ID'] = $ob['IBLOCK_SECTION_ID'];
                                foreach ($arFilter2 as $fKey => $arFElem) {
                                    $pseudofilter[] = array('PROP_ID' => preg_replace("/\D/", "", $fKey), 'PROP_VALUE' => $arFElem);
                                }
                                $functionReturn = getClosestNotPseudoSection($ob['IBLOCK_SECTION_ID'], $pseudofilter, $arParams);
                                if ($functionReturn) {
                                    $arCurSection['ID'] = $functionReturn['SECTION_ID'];
                                    $pseudofilter = array_merge($pseudofilter, $functionReturn['FILTER']);
                                }
                                $dbParents = CIBlockSection::GetNavChain(false, intval($arCurSection['ID']));
                                while ($arParents = $dbParents->Fetch()) {
                                    $arList[] = $arParents['CODE'];
                                    $arListName[] = $arParents['NAME'];
                                }
                                $sectionPath = $arParams['SEF_FOLDER'] . implode('/', $arList) . '/';
                            }
                        }
                    }
                    ?>
                    <?
                    $nameTemplate = 'optima';
                    if ($_REQUEST['test'] == 'test') {
                        $nameTemplate = '.default';
                    } ?>


                    <?

                    if ($APPLICATION->GetCurPage() !== '/catalog/melkaya-bytovaya-tekhnika/posuda/') {
                        $APPLICATION->IncludeComponent(
                            "bitrix:catalog.smart.filter",
                            $nameTemplate,
                            array(
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "SECTION_ID" => $arCurSection['ID'],
                                "FILTER_NAME" => $arParams["FILTER_NAME"],
                                "PRICE_CODE" => $arParams["PRICE_CODE"],
                                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                "CACHE_TIME" => $arParams["CACHE_TIME"],
                                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                "SAVE_IN_SESSION" => "N",
                                "XML_EXPORT" => "Y",
                                "SECTION_TITLE" => "NAME",
                                "SECTION_DESCRIPTION" => "DESCRIPTION",
                                'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                'PSEUDOFILTER' => $pseudofilter,
                                'PSEUDOFILTER_ACTION' => $sectionPath,
                                "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                                "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                                "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                                "WP_CURRENCY_ID" => $arParams["CURRENCY_ID"],
                                "WP_CONVERT_PRICE_FROM" => $arParams["WP_CONVERT_PRICE_FROM"],
                                "POPUP_POSITION" => "right"
                            ),
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        );
                    } ?>

                    <?
                }
                ?>
                <? $APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR . "include/sections_side.php"), false); ?>

                <? /*if ($USER->IsAdmin()) {
			$rsSect = CIBlockSection::GetList(Array("SORT"=>"ASC"),Array("GLOBAL_ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"], "SECTION_ID"=>$arResult["VARIABLES"]["SECTION_ID"], "!UF_BRAND"=>false),false,Array("NAME", "SECTION_PAGE_URL"));

			if ($arSect = $rsSect->GetNext()){?>
		<nav class="catalog-menu">
			<div class="catalog-section-menu">
				<div class="catalog-section-title"><span>Разделы</span></div>
					<ul>
						<li>
							<a href="<?=$arSect["SECTION_PAGE_URL"]?>"><?=$arSect["NAME"]?></a>
						<li>
						<?while ($arSect = $rsSect->GetNext()){?>
						<li>
							<a href="<?=$arSect["SECTION_PAGE_URL"]?>"><?=$arSect["NAME"]?></a>
						<li>
						<?}?>
					</ul>
				</div>
			</div>
		</nav>
		<?}}*/ ?>

            </div>
            <div class="content-area">
                <?
                $APPLICATION->ShowViewContent('SectionH1'); ?>
                <?

                $section_tmp = explode('/', $arResult['VARIABLES']['SECTION_CODE_PATH']);

                if (count($section_tmp) > 1) {
                    $section_list_id = '';
                    $section_list_code = $section_tmp[0];
                } else {
                    $section_list_id = $arResult['VARIABLES']['SECTION_ID'];
                    $section_list_code = $arResult['VARIABLES']['SECTION_CODE'];
                }

                // Список подразделов
                if ($arParams["SECTIONS_VIEW_MODE"] == 'Y') {
                    $APPLICATION->IncludeComponent(
                        "bitrix:catalog.section.list",
                        "brands-no-brands",//"",
                        array(
                            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                            "SECTION_CURRENT" => $arCurSection['ID'],
                            "SECTION_ID" => $section_list_id,
                            "SECTION_CODE" => $section_list_code,
                            "CACHE_TYPE" => 'N',
                            "CACHE_TIME" => $arParams["CACHE_TIME"],
                            "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                            "COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
                            "TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
                            "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                            "SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
                            "HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
                            "ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : ''),
                        ),
                        $component,
                        array("HIDE_ICONS" => "Y")
                    );
                }
                ?>
                <? $APPLICATION->ShowViewContent('SubSectionsNoBrands');// from "brands-no-brands" section.list template?>
                <div id="compare_list_count">
                    <?
                    // Блок со сравнением
                    if ($arParams["USE_COMPARE"] == "Y") {
                        ?><?
                        $APPLICATION->IncludeComponent(
                            "bitrix:catalog.compare.list",
                            "",
                            array(
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "NAME" => $arParams["COMPARE_NAME"],
                                "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                                "COMPARE_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
                                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                                'POSITION_FIXED' => isset($arParams['COMPARE_POSITION_FIXED']) ? $arParams['COMPARE_POSITION_FIXED'] : '',
                                'POSITION' => isset($arParams['COMPARE_POSITION']) ? $arParams['COMPARE_POSITION'] : ''
                            ),
                            $component,
                            array("HIDE_ICONS" => "Y")
                        );
                    }
                    ?>
                </div>
                <?
                $intSectionID = 0;
                ?>

                <div class="mobile-subsections">
                    <? $APPLICATION->ShowViewContent('SubSectionsNoBrands');// from "brands-no-brands" section.list template?>
                </div>

                <div class="mobile-filter">
                    <?
                    // Фильтр
                    $arParams['USE_FILTER'] = (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] == 'Y' ? 'Y' : 'N');
                    if ($arParams['USE_FILTER'] == 'Y') {
                        $arFilter = array(
                            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                            "ACTIVE" => "Y",
                            "GLOBAL_ACTIVE" => "Y",
                        );
                        if (0 < intval($arResult["VARIABLES"]["SECTION_ID"])) {
                            $arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
                        } elseif ('' != $arResult["VARIABLES"]["SECTION_CODE"]) {
                            $arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
                        }

                        $obCache = new CPHPCache();
                        if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog")) {
                            $arCurSection = $obCache->GetVars();
                        } elseif ($obCache->StartDataCache()) {
                            $arCurSection = array();
                            if (Loader::includeModule("iblock")) {
                                $dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));

                                if (defined("BX_COMP_MANAGED_CACHE")) {
                                    global $CACHE_MANAGER;
                                    $CACHE_MANAGER->StartTagCache("/iblock/catalog");

                                    if ($arCurSection = $dbRes->Fetch()) {
                                        $CACHE_MANAGER->RegisterTag("iblock_id_" . $arParams["IBLOCK_ID"]);
                                    }
                                    $CACHE_MANAGER->EndTagCache();
                                } else {
                                    if (!$arCurSection = $dbRes->Fetch())
                                        $arCurSection = array();
                                }
                            }
                            $obCache->EndDataCache($arCurSection);
                        }
                        if (!isset($arCurSection)) {
                            $arCurSection = array();
                        }
                        ?>

                        <?
                        if (!function_exists('getClosestNotPseudoSection')) {
                            function getClosestNotPseudoSection($sectionId, $arFilter, $arParams)
                            {
                                $resInside = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $sectionId, 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_PSEUDO_SECTION'));
                                if ($obInside = $resInside->GetNext()) {
                                    $sectionValue = unserialize(htmlspecialchars_decode($obInside['UF_PSEUDO_SECTION']));
                                    if (count($sectionValue) > 0) {
                                        if ($sectionValue['is_pseudosection'] == 'Y') {
                                            $obCond = new CCCatalogCondTree();
                                            $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                                            $conditions = $obCond->Parse($sectionValue['rule']);
                                            $strEval = $obCond->Generate($conditions, array());
                                            $strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
                                            eval('$arFilterInside = ' . $strEval);
                                            foreach ($arFilterInside as $fKey => $arFElem) {
                                                $arFilter[] = array('PROP_ID' => preg_replace("/\D/", "", $fKey), 'PROP_VALUE' => $arFElem);
                                            }
                                            $sectionId = $obInside['IBLOCK_SECTION_ID'];
                                            $functionReturn = getClosestNotPseudoSection($sectionId, $arFilter, $arParams);
                                            if ($functionReturn) {
                                                $sectionId = $functionReturn['SECTION_ID'];
                                                $arFilter = array_merge($arFilter, $functionReturn['FILTER']);
                                            }
                                            return array('SECTION_ID' => $sectionId, 'FILTER' => $arFilter);
                                        }
                                    }
                                }
                                return false;
                            }
                        }
                        $pseudofilter = false;
                        $sectionPath = false;
                        $res = CIBlockSection::GetList(Array("left_margin" => "DESC"), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arCurSection['ID'], 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_PSEUDO_SECTION'));
                        if ($ob = $res->GetNext()) {
                            $sectionValue = unserialize(htmlspecialchars_decode($ob['UF_PSEUDO_SECTION']));
                            if (is_array($sectionValue) && count($sectionValue) > 0) {
                                if ($sectionValue['is_pseudosection'] == 'Y') {
                                    $obCond = new CCCatalogCondTree();
                                    $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                                    $conditions = $obCond->Parse($sectionValue['rule']);
                                    $strEval = $obCond->Generate($conditions, array());
                                    $strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
                                    eval('$arFilter2 = ' . $strEval);
                                    $arCurSection['ID'] = $ob['IBLOCK_SECTION_ID'];
                                    foreach ($arFilter2 as $fKey => $arFElem) {
                                        $pseudofilter[] = array('PROP_ID' => preg_replace("/\D/", "", $fKey), 'PROP_VALUE' => $arFElem);
                                    }
                                    $functionReturn = getClosestNotPseudoSection($ob['IBLOCK_SECTION_ID'], $pseudofilter, $arParams);
                                    if ($functionReturn) {
                                        $arCurSection['ID'] = $functionReturn['SECTION_ID'];
                                        $pseudofilter = array_merge($pseudofilter, $functionReturn['FILTER']);
                                    }
                                    $dbParents = CIBlockSection::GetNavChain(false, intval($arCurSection['ID']));
                                    while ($arParents = $dbParents->Fetch()) {
                                        $arList[] = $arParents['CODE'];
                                        $arListName[] = $arParents['NAME'];
                                    }
                                    $sectionPath = $arParams['SEF_FOLDER'] . implode('/', $arList) . '/';
                                }
                            }
                        }
                        ?>
                        <?
                        $nameTemplate = 'optima';
                        if ($_REQUEST['test'] == 'test') {
                            $nameTemplate = '.default';
                        } ?>

                        <?
                        if ($APPLICATION->GetCurPage() !== '/catalog/melkaya-bytovaya-tekhnika/posuda/') {
                            $APPLICATION->IncludeComponent(
                                "bitrix:catalog.smart.filter",
                                $nameTemplate,
                                array(
                                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                    "SECTION_ID" => $arCurSection['ID'],
                                    "FILTER_NAME" => $arParams["FILTER_NAME"],
                                    "PRICE_CODE" => $arParams["PRICE_CODE"],
                                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                    "SAVE_IN_SESSION" => "N",
                                    "XML_EXPORT" => "Y",
                                    "SECTION_TITLE" => "NAME",
                                    "SECTION_DESCRIPTION" => "DESCRIPTION",
                                    'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                    'PSEUDOFILTER' => $pseudofilter,
                                    'PSEUDOFILTER_ACTION' => $sectionPath,
                                    "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                                    "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                                    "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                                    "WP_CURRENCY_ID" => $arParams["CURRENCY_ID"],
                                    "WP_CONVERT_PRICE_FROM" => $arParams["WP_CONVERT_PRICE_FROM"],
                                    "POPUP_POSITION" => "right"
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            );
                        } ?>

                        <?
                    }
                    ?>
                    <div class="mobile-filter-btn"> Показать все параметры</div>
                </div>

                <div class="catalog-sort-and-view">
                    <div class="catalog-sort">
                        <div class="sort-by">Сортировать по:</div>
                        <ul class="sort-by-options">
                            <? foreach ($arSort as $key => $ar) {
                                if ($SORT == $ar['VALUE'] && $ORDER == 'desc') {
                                    $href = $APPLICATION->GetCurPageParam("sort=" . $ar['VALUE'] . "&order=asc", array('sort', 'order'));
                                    $class = "active desc";
                                } else if ($SORT == $ar['VALUE']) {
                                    $href = $APPLICATION->GetCurPageParam("sort=" . $ar['VALUE'] . "&order=desc", array('sort', 'order'));
                                    $class = "active asc";
                                } else {
                                    $href = $APPLICATION->GetCurPageParam("sort=" . $ar['VALUE'] . "&order=" . $ar['ORDER'], array('sort', 'order'));
                                    $class = "";
                                } ?>
                                <li class="<?= $class ?>"><a href="<?= $href ?>" rel="nofollow"><?= $ar['TITLE'] ?></a>
                                </li>
                            <? } ?>
                        </ul>
                    </div>
                    <div class="catalog-show-how">
                        <a class="pic<? if (!empty($_SESSION['view']) && ($_SESSION['view'] == "pics")): ?> active<? endif; ?>"
                           href="?view=pics#products" style="cursor:pointer;">
                            <span></span>
                        </a>
                        <a class="list<? if (empty($_SESSION['view']) || ($_SESSION['view'] == "list")): ?> active<? endif; ?>"
                           href="?view=list#products" style="cursor:pointer;">
                            <span></span>
                        </a>
                    </div>
                    <div class="catalog-show-by">
                        <div class="show-by">Показывать по:</div>
                        <select class="show-by-options" data-action="change-count-catalog-items">
                            <option value="9" <?= (intval($_REQUEST['showby']) == 9) ? 'selected' : '' ?>>9</option>
                            <option value="18" <?= (intval($_REQUEST['showby']) == 18 || !$_REQUEST['showby']) ? 'selected' : '' ?>>
                                18
                            </option>
                            <option value="21" <?= (intval($_REQUEST['showby']) == 21) ? 'selected' : '' ?>>21</option>
                            <option value="45" <?= (intval($_REQUEST['showby']) == 45) ? 'selected' : '' ?>>45</option>
                        </select>
                    </div>

                </div>
                <!-- переключение вида товаров -->
                <? if (!empty($_REQUEST) && ($_REQUEST['view'] == "list")) {
                    $_SESSION['view'] = "list";
                } elseif (!empty($_REQUEST) && ($_REQUEST['view'] == "pics")) {
                    $_SESSION['view'] = "pics";
                }

                if (!empty($_SESSION['view']) && ($_SESSION['view'] == "list")) {
                    $template = "list_view";
                    $act_pic = "active";
                } else {
                    $template = "";
                    $act_list = "active";
                }

                //если параметры фильтра заданы, убираем из выдачи товары, снятые с производства
                /*if(
                    is_array($GLOBALS[$arParams['FILTER_NAME']]['><CATALOG_PRICE_2']) &&
                    count($GLOBALS[$arParams['FILTER_NAME']]['><CATALOG_PRICE_2']) > 0
                ) {
                    $GLOBALS[$arParams['FILTER_NAME']]['!PROPERTY_OUT_OF_PRODUCTION_VALUE'] = 'Да';
                }*/
                //    unset($GLOBALS[$arParams['FILTER_NAME']]);
                //    _a($arParams,1);
                // Список товаров
                global $arFilter, $USER;
//                if ($USER->isAdmin()) {
//                    echo '<pre>';var_dump($arFilter);echo '</pre>';
//                }

                $intSectionID = $APPLICATION->IncludeComponent(
                    "kokos:catalog.section",
                    $template,//"",
                    array(
                        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                        "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                        "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                        "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
                        "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                        "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                        "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                        "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                        "SET_BROWSER_TITLE" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                        "HIDDEN_PROPERTIES" => $arParams["HIDDEN_PROPERTIES"],
                        "BASKET_URL" => $arParams["BASKET_URL"],
                        "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                        "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                        "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                        "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                        "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                        "FILTER_NAME" => $arParams["FILTER_NAME"],
                        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                        "SET_TITLE" => "N",
                        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                        "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
//                "PAGE_ELEMENT_COUNT" => 18, //$arParams["PAGE_ELEMENT_COUNT"],
                        "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                        "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                        "PRICE_CODE" => $arParams["PRICE_CODE"],
                        "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                        "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                        "SHOW_PRODUCT_QUANTITY" => $arParams["SHOW_SECTION_PRODUCT_QUANTITY"],

                        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                        "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                        "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                        "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                        "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],

                        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                        "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                        "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
                        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],

                        "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                        "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                        "OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
                        "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                        "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                        "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                        "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                        "OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

                        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                        "SECTION_USER_FIELDS" => array(
                            0 => 'UF_SEOTITLE',
                            1 => 'UF_SEOH1',
                            2 => 'UF_SEOKEYWORDS',
                            3 => 'UF_SEODESCRIPTION',
                            4 => '',
                        ),
                        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                        'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                        'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],

                        'LABEL_PROP' => $arParams['LABEL_PROP'],
                        'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                        'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],

                        'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                        'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
                        'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                        'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                        'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                        'MESS_BTN_BUY' => $arParams['MESS_BTN_BUY'],
                        'MESS_BTN_ADD_TO_BASKET' => $arParams['MESS_BTN_ADD_TO_BASKET'],
                        'MESS_BTN_SUBSCRIBE' => $arParams['MESS_BTN_SUBSCRIBE'],
                        'MESS_BTN_DETAIL' => $arParams['MESS_BTN_DETAIL'],
                        'MESS_NOT_AVAILABLE' => $arParams['MESS_NOT_AVAILABLE'],

                        "ADD_SECTIONS_CHAIN" => 'N',
                        'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                        'COMPARE_PATH' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['compare']
                    ),
                    $component
                ); ?>

                <?
                //Лидеры продаж
                if (ModuleManager::isModuleInstalled("sale") && (!isset($arParams['USE_SALE_BESTSELLERS']) || $arParams['USE_SALE_BESTSELLERS'] != 'N')) {
//                    $arRecomData = array();
//                    $recomCacheID = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
//                    $obCache = new CPHPCache();
//                    if ($obCache->InitCache(36000, serialize($recomCacheID), "/sale/bestsellers")) {
//                        $arRecomData = $obCache->GetVars();
//                    } elseif ($obCache->StartDataCache()) {
//                        if (Loader::includeModule("catalog")) {
//                            $arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
//                            $arRecomData['OFFER_IBLOCK_ID'] = (!empty($arSKU) ? $arSKU['IBLOCK_ID'] : 0);
//                        }
//                        $obCache->EndDataCache($arRecomData);
//                    }

                    $obCache = new CPHPCache();
                    $cache_id = 'sectionItemCount' . $arResult["VARIABLES"]["SECTION_ID"];
                    $cache_path = 'sectionItemCount';
                    if ($obCache->InitCache(3600, $cache_id, $cache_path)) {
                        $sectionItemCount = $obCache->GetVars();
                    } elseif ($obCache->StartDataCache()) {
                        CModule::IncludeModule("iblock");
                        $sectionItemCount = CIBlockElement::GetList(
                            Array("SORT" => "ASC"),
                            Array(
                                'IBLOCK_ID' => $arParams["IBLOCK_ID"],
                                'SECTION_ID' => $arResult["VARIABLES"]["SECTION_ID"],
                                'INCLUDE_SUBSECTIONS' => $arParams['INCLUDE_SUBSECTIONS'],
                                'ACTIVE' => 'Y',
                            ),
                            Array(),
                            false,
                            Array("ID", "IBLOCK_ID")
                        );
                        $obCache->EndDataCache($sectionItemCount);
                    }


//                    if (!empty($arRecomData)) {

//                        if ($sectionItemCount > 6) {
                            GLOBAL $filterSection;
                            $filterSection = array(
                                "!SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                                "!PROPERTY_OUT_OF_PRODUCTION_VALUE" => "Да",
                                "=PROPERTY__POPULAR_VALUE" => "Да",
//                                array(
//                                    "LOGIC" => "OR",
//                                    array("=PROPERTY_HIT_VALUE" => "Y"),
//                                    array("=PROPERTY_INDEX_VALUE" => "Да"),
//
//                                )
                            );
//                        $filterSection = array("!SECTION_ID"=>$arResult["VARIABLES"]["SECTION_ID"]);


//                        }

                        $APPLICATION->IncludeComponent(
                            "bitrix:catalog.top",
                            "top_and_hit",
                            array(
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "ELEMENT_SORT_FIELD" => "propertysort_HIT",
                                "ELEMENT_SORT_ORDER" => "desc",
                                "ELEMENT_SORT_FIELD2" => "RAND",
                                "ELEMENT_SORT_ORDER2" => "desc",
                                "FILTER_NAME" => "filterSection",
                                'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                "ELEMENT_COUNT" => 6,
                                "LINE_ELEMENT_COUNT" => "3",
                                "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                                "OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],
                                "VIEW_MODE" => "SECTION",
                                'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                                'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                                'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                                'MESS_BTN_BUY' => $arParams['MESS_BTN_BUY'],
                                'MESS_BTN_ADD_TO_BASKET' => $arParams['MESS_BTN_ADD_TO_BASKET'],
                                'MESS_BTN_SUBSCRIBE' => $arParams['MESS_BTN_SUBSCRIBE'],
                                'MESS_BTN_DETAIL' => $arParams['MESS_BTN_DETAIL'],
                                'MESS_NOT_AVAILABLE' => $arParams['MESS_NOT_AVAILABLE'],
                                "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                                "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                                "SECTION_ID_VARIABLE" => 'parentSection',
                                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                "CACHE_TIME" => $arParams["CACHE_TIME"],
                                "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                                "PRICE_CODE" => $arParams["PRICE_CODE"],
                                "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                                "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                                "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                "BASKET_URL" => $arParams["BASKET_URL"],
                                "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                                "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                                "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                                "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                                "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
                                "ADD_TO_BASKET_ACTION" => "ADD",
                                "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                                "WP_H2_TITLE" => "Популярные товары",
                                "FIRST_ELEMENTS_COUNT" => "6",
                                "WP_H2_TITLE2" => "Хиты продаж",
                                "WP_SHOW_ALL_LINK" => "N",
                                "WP_ALL_LINK_TEXT" => "",
                                "WP_ALL_LINK" => "/catalog/",
                                "WP_ITEMLIST_CATEGORY_LINE" => "_BRAND",
                                "ON_SECTION_PAGE" => "Y"
                            ),
                            $component,
                            array("HIDE_ICONS" => "Y")
                        );
                /*if (!empty($arRecomData)) {*/
//                    }
                }
                ?>
                <?

                ?>


                </section>
            </div>
        </div>
        <div class="global-hide">
            <noindex>
                <? $APPLICATION->IncludeComponent(
                    "bitrix:form.result.new",
                    ".default",
                    array(
                        "WEB_FORM_ID" => "2",
                        "IGNORE_CUSTOM_TEMPLATE" => "N",
                        "USE_EXTENDED_ERRORS" => "Y",
                        "SEF_MODE" => "N",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "3600",
                        "LIST_URL" => "",
                        "EDIT_URL" => "",
                        "SUCCESS_URL" => "",
                        "CHAIN_ITEM_TEXT" => "",
                        "CHAIN_ITEM_LINK" => "",
                        "SEF_FOLDER" => "",
                        "WEB_FORM_ID2" => "N",
                        "ITEM_TITLE_VALUE" => "",
                        "ITEM_URL_VALUE" => "",
                        "FORM_CONTAINER_CLASS" => "form-pupop", // yeah, i know. It's SEO, baby.
                        "FORM_CONTAINER_ID" => "preorderform",
                        "VARIABLE_ALIASES" => array(
                            "WEB_FORM_ID" => "PREORDER_FORM",
                            "RESULT_ID" => "RESULT_ID",
                        )
                    ),
                    false
                ); ?>
            </noindex>
        </div>