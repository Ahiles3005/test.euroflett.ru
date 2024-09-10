<?php

use Bitrix\Highloadblock as HL;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Context;

define("BX_DISABLE_INDEX_PAGE", true);
define("RECAPTCHA_V3_SITE_KEY", '6Ld10KcpAAAAAEg75h70CCloxy16LA5nz-CMtkvt');
define("RECAPTCHA_V3_SECRET_SITE_KEY", '6Ld10KcpAAAAAE97u36V7vqQI8ntz5PStOTPgx1D');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if (!$request->isAjaxRequest()) {
    if (($_GET['action'] == 'BUY') && ($_GET['id'] > 0)) {
        unset($_REQUEST['action']);
    }
}


// Письмо пользователю при оформлении нового заказа
AddEventHandler("sale", "OnOrderAdd", Array("mail_new", "OnOrderAdd_mail"));

class mail_new
{
    static function OnOrderAdd_mail($ID, $val)
    {

        if ($val['PAY_SYSTEM_ID'] == '11') {

            /*global $USER;
            if ($USER->IsAdmin()) {
                echo '<pre>';
                echo $ID;
                print_r($val);
                exit;
           }*/

            //var_dump($val); var_dump($orderFields); var_dump($isNew); die();

            // Получаем имя и мэйл пользователя
            $rsUser = CUser::GetByID($val["USER_ID"]);
            $arUser = $rsUser->Fetch();
            $arUser_name = $arUser["LAST_NAME"] . " " . $arUser["NAME"];
            $arUser = $val["USER_EMAIL"];


            // Получаем Содержимое заказа
            $dbBasketItems = CSaleBasket::GetList(
                array(
                    "NAME" => "ASC",
                    "ID" => "ASC"
                ),
                array(
                    "FUSER_ID" => CSaleBasket::GetBasketUserID(),
                    "LID" => SITE_ID,
                    "DELAY" => "N",
                    "CAN_BUY" => "Y",
                    "ORDER_ID" => "NULL"
                ),
                false,
                false,
                array());

            $order_list = array();

            while ($arItem = $dbBasketItems->Fetch()) {
                $st = (int)$arItem["QUANTITY"] * $arItem["PRICE"];

                $order_list[] = $arItem["NAME"] . ' - ' . $arItem["QUANTITY"] . ' : ' . $st;
            }

            $arEventFields = array(
                "ORDER_DATE" => $val['DATE'],
                "ORDER_LIST" => implode(PHP_EOL, $order_list),
                //"ORDER_ID"     => $ID,
                "ORDER_ID" => $val['ACCOUNT_NUMBER'],
                "ORDER_USER" => $arUser_name,
                "EMAIL" => $arUser,
                "PRICE" => (int)$val["PRICE"] . " руб",
            );
            CEvent::Send("SALE_NEW_ORDER", 's1', $arEventFields, "N", 22);

            $arEventFields = array(
                "ORDER_DATE" => $val['DATE'],
                "ORDER_LIST" => implode(PHP_EOL, $order_list),
                //"ORDER_ID"     => $ID,
                "ORDER_ID" => $val['ACCOUNT_NUMBER'],
                "ORDER_USER" => $arUser_name,
                "EMAIL" => $arUser,
                "PRICE" => (int)$val["PRICE"] . " руб",
            );


            CEvent::Send("SALE_NEW_ORDER", 's1', $arEventFields, "N", 53);
        }

    }

}

//start megaplan1028158
function ShowSaticCanonical()
{
    global $APPLICATION;

    if ($APPLICATION->GetPageProperty("canonical")) {
        return "<link rel='canonical' href='" . $APPLICATION->GetPageProperty("canonical") . "'/>";
    } else {
        $curPage = $APPLICATION->GetCurPage(true);
        $canonical = mb_strtolower(SITE_SERVER_NAME . str_replace('index.php', '', $curPage));
        $APPLICATION->AddHeadString("<link rel='canonical' href='https://" . $canonical . "'/>");
        return "";
    }
}

AddEventHandler("main", "OnEpilog", "ShowSaticCanonical");
//end megaplan1028158

//include_once(__DIR__.'/include/seo_redirs.php');
include_once(__DIR__ . '/include/krumo/class.krumo.php');
include_once(__DIR__ . '/include/constants.php');
include_once(__DIR__ . '/include/functions.php');
include_once(__DIR__ . '/include/events.php');
include_once(__DIR__ . '/include/settings.php');
AddEventHandler("iblock", "OnAfterIBlockSectionAdd", "updateUserFieldSectionUpdate");
AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", "updateUserFieldSectionUpdate");

// Создание словаря при индексации сайта, а также подгрузка классов для поиска через sphinx
if (COption::GetOptionString("search", "full_text_engine") === "sphinx") {
    include_once(__DIR__ . '/include/search.sphinx.php');
    AddEventHandler("search", "BeforeIndex", array("HkCSearchSphinx", "onBeforeIndex"));
}

function updateUserFieldSectionUpdate(&$arFields)
{
    $res = CIBlockSection::GetList(Array("left_margin" => "DESC"), array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'ID' => $arFields['ID'], 'ACTIVE' => 'Y', '!UF_FILTER' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_FILTER'));
    if ($ob = $res->GetNext()) {
        $sectionValue = unserialize(htmlspecialchars_decode($ob['UF_FILTER']));
        if (count($sectionValue) > 0) {
            if ($sectionValue['is_filter'] == 'Y') {
                $obCond = new CCCatalogCondTreeNew();
                $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                $conditions = $obCond->Parse($sectionValue['rule']);
                $strEvalN = $obCond->Generate($conditions, array());

                $strEvalN = preg_replace('/([\"\'])\\1+/', '$1', $strEvalN);
                $strEvalN = preg_replace('/([\"\'])%([\"\'])(.*)([\"\'])%([\"\'])/', '$1%$3%$5', $strEvalN);

                if($strEvalN){
                  eval('$arFilter2 = ' . $strEvalN);
                }

                $arSelect_el = Array("ID");
                $old = array();
                $res_elm = CIBlockElement::GetList(Array(), Array('SECTION_ID' => $arFields['ID']), false, Array("nPageSize" => 500000), $arSelect_el);
                while ($ob_elm = $res_elm->GetNext()) {
                    $old[] = $ob_elm["ID"];
                }

                $arSelect_el = Array("ID");
                $arFilter2["IBLOCK_ID"] = $arFields['IBLOCK_ID'];

                $res_el = CIBlockElement::GetList(Array(), $arFilter2, false, Array("nPageSize" => 500000), $arSelect_el);
                while ($ob_el = $res_el->GetNext()) {
                    if (in_array($ob_el["ID"], $old)) {
                        unset($old[array_search($ob_el["ID"], $array)]);
                    }
                    $db_old_groups = CIBlockElement::GetElementGroups($ob_el["ID"], true);
                    $ar_new_groups = Array($arFields['ID']);
                    while ($ar_group = $db_old_groups->Fetch())
                        $ar_new_groups[] = $ar_group["ID"];

                    CIBlockElement::SetElementSection($ob_el["ID"], $ar_new_groups);
                    \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($arFields['IBLOCK_ID'], $ob_el["ID"]);
                }

                foreach ($old as $o) {
                    $db_old_groups = CIBlockElement::GetElementGroups($o, true);
                    $ar_new_groups = Array();
                    while ($ar_group = $db_old_groups->Fetch())
                        if ($ar_group["ID"] != $arFields['ID']) $ar_new_groups[] = $ar_group["ID"];
                    CIBlockElement::SetElementSection($o, $ar_new_groups);
                    \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($arFields['IBLOCK_ID'], $o);
                }
            }
        }
    }
}

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementUpdateHandler");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "OnAfterIBlockElementUpdateHandler");

function OnAfterIBlockElementUpdateHandler(&$arFields)
{
    //добавил новую переменную в сессию, чтобы этот кусок манки кода не вызывался при апдейте из импорта в админке

    if ($_SESSION['IMPORT_UPDATE_HANDLER'] != 'Y') {

        $res = CIBlockSection::GetList(Array("left_margin" => "DESC"), array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'ACTIVE' => 'Y', '!UF_FILTER' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_FILTER'));
        while ($ob = $res->GetNext()) {
            $sectionValue = unserialize(htmlspecialchars_decode($ob['UF_FILTER']));
            if (count($sectionValue) > 0) {
                if ($sectionValue['is_filter'] == 'Y') {
                    $obCond = new CCCatalogCondTreeNew();
                    $boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
                    $conditions = $obCond->Parse($sectionValue['rule']);
                    $strEval = $obCond->Generate($conditions, array());
                    $strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
                    eval('$arFilter2 = ' . $strEval);

                    $arSelect_el = Array("ID", "NAME");
                    $arFilter2["IBLOCK_ID"] = $arFields['IBLOCK_ID'];
                    $arFilter2["ID"] = $arFields['ID'];

                    $res_el = CIBlockElement::GetList(Array(), $arFilter2, false, Array("nPageSize" => 500000), $arSelect_el);
                    while ($ob_el = $res_el->GetNext()) {
                        $db_old_groups = CIBlockElement::GetElementGroups($ob_el["ID"], true);
                        $ar_new_groups = Array($ob['ID']);
                        while ($ar_group = $db_old_groups->Fetch())
                            $ar_new_groups[] = $ar_group["ID"];
                        CIBlockElement::SetElementSection($ob_el["ID"], $ar_new_groups);
                        \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($arFields['IBLOCK_ID'], $ob_el["ID"]);
                    }

                }
            }
        }
    } 

    unset($_SESSION['IMPORT_UPDATE_HANDLER']);
}





AddEventHandler("catalog", "OnBeforeProductUpdate", Array("ProductQuantityClass", "OnBeforeProductUpdateQuan"));

class ProductQuantityClass  
{
 
  static function OnBeforeProductUpdateQuan($ID, &$arFields)
  {
	   
    if($arFields["QUANTITY"] > 0) {
		
			$learn_about_admission = 4;
			$entity_learn_about_admission = GetEntityDataClass__init($learn_about_admission);
			
			 
			
			$rsData = $entity_learn_about_admission::getList(array(
							'order' => array(),
							'select' => array('*'),
							'filter' => array( 'UF_IS_SUBMIT' =>  'N' ,'UF_ID_PRODUCT' => $arFields['ID'])
			));
			
			
			$arr_elements = array();
			
			while($el = $rsData->fetch()){
				
			 
				$entity_learn_about_admission::update($el['ID'], array(
			 
					 'UF_IS_SUBMIT' => 'Y'
					
				));
				$arr_elements[] = $el;
				 
				
			}
			
			if(!empty($arr_elements)){
				
				
				 $rsEl = CIBlockElement::GetList([], ['ID' => $arFields['ID']], false, false, ['*']);
				 $ar_data = $rsEl->Fetch();
				
				foreach($arr_elements as $elemen){
					
					
					if (mail($elemen['UF_EMAIL'], "Хорошая новость! Выбранный Вами снова в продаже", 
					"Хорошая новость! Выбранный Вами снова в продаже. " . $ar_data['NAME'] . " ссылка на товар на нашем сайте https://www.euroflett.ru" . $elemen['UF_PRODUCT_URL'] . "<br><br>С уважением,<br>
Салон элитной бытовой техники<br><br>
<img src='https://www.euroflett.ru/images/Clipboard1663052153703-25.jpeg' />
<br><br>
г. Москва, Новый Арбат, д. 36 стр. 3<br>
ТЦ «Сфера». -1 этаж<br>
Сайт:www.euroflett.ru<br>
Тел.: +7 (495) 150-09-66<br>
Тел.: 8 800 500-75-66",

	"From: robot@euroflett.ru\r\n"
    ."Content-type: text/html; charset=utf-8\r\n"
    ."X-Mailer: PHP mail script")) {
						
						echo 'Отправлено';
					}
					else {
						
						echo 'Не отправлено';
					}
					
					
				}
				
				
			}
		
		
    }
  }
}




spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../classes/' . strtr($class, array('\\' => '/')) . '.php';
    if (!file_exists($path)) {
        return;
    }
    require_once($path);
});

$arCompareExcludeProperties = array(
    0 => "INDEX",
    1 => "MODEL",
    2 => "_BRAND",
    3 => "_UNID",
    4 => "BLOG_POST_ID",
    5 => "BLOG_COMMENTS_CNT",
    6 => "CANONICAL_SECTION",
    7 => "DESCTITLE",
    8 => "YM_COUNTRY",
    9 => "YM_WARRANTY",
    10 => "YM_MODEL",
    11 => "YM_VENDORCODE",
    12 => "YM_VENDOR",
    13 => "YM_TYPEPREFIX",
    14 => "RELATED",
    15 => "COLORGROUP",
    16 => "COMMENT",
    17 => "DOCUMENTATION",
    18 => "PHOTOS",
    19 => "HIT",
    20 => "DESCTITLE",
    21 => "NEW",
    22 => "ANALOG",
    23 => "OLDPRICE",
    24 => "NO_PRICE",
    25 => "MEGAVISOR",
    26 => "SOON",
    27 => "LABELS",
    28 => "ACTION_SALE35",
    29 => "_PRICE_CVT_ID",
    30 => "_FREE_SETUP",
    31 => "_FREE_DELIVERY"

);

$arPopularFilter = array('IBLOCK_TYPE' => 'catalog', 'PROPERTY__POPULAR_VALUE' => 'Да');

function getCompareProperties($iblock_id)
{
    CModule::IncludeModule("iblock");
    global $arCompareExcludeProperties;
    $props = array();
    $res = CIBlock::GetProperties($iblock_id);
    while ($r = $res->Fetch()) {
        if (array_search($r["CODE"], $arCompareExcludeProperties) === false) {
            $props[] = $r["CODE"];
        }
    }
    return $props;
}

AddEventHandler("main", "OnBuildGlobalMenu", "ImportMenu");

function ImportMenu(&$aGlobalMenu, &$aModuleMenu)
{
    $haveSection = false;
    $arMenu = array(
        "text" => "Импорт",
        "url" => "webprofy_import.php?lang=" . LANGUAGE_ID,
        "more_url" => array(),
        "icon" => "fav_menu_icon_yellow",
        "page_icon" => "fav_page_icon_yellow",
        "title" => "Синхронизация каталога"
    );
    $arMenu2 = array(
        "text" => "Журнал импорта",
        "url" => "webprofy_import_log.php?lang=" . LANGUAGE_ID,
        "more_url" => array(),
        "icon" => "fav_menu_icon_yellow", // малая иконка
        "page_icon" => "fav_page_icon_yellow", // большая иконка
        "title" => "Синхронизация каталога"
    );
    $arMenu3 = array(
        "text" => "Экспорт каталога",
        "url" => "webprofy_export.php?lang=" . LANGUAGE_ID,
        "more_url" => array(),
        "icon" => "fav_menu_icon_yellow", // малая иконка
        "page_icon" => "fav_page_icon_yellow", // большая иконка
        "title" => "Импорт/Экспорт каталога"
    );

    foreach ($aModuleMenu as $k => $v) {
        if ($v["parent_menu"] == "global_menu_services" && $v["items_id"] == "menu_webprofy") {
            $haveSection = true;
            $aModuleMenu[$k]["items"][] = $arMenu;
            $aModuleMenu[$k]["items"][] = $arMenu2;
            $aModuleMenu[$k]["items"][] = $arMenu3;
        }
    }
    if (!$haveSection) {
        $customMenu = array(
            "parent_menu" => "global_menu_services", // поместим в раздел "Сервис"
            "sort" => 1000,                    // вес пункта меню
            "text" => "Webprofy",       // текст пункта меню
            "title" => "Меню Webprofy", // текст всплывающей подсказки
            "icon" => "fav_menu_icon_yellow", // малая иконка
            "page_icon" => "fav_page_icon_yellow", // большая иконка
            "items_id" => "menu_webprofy",  // идентификатор ветви
            "items" => array($arMenu, $arMenu2, $arMenu3),          // остальные уровни меню сформируем ниже.
        );
        $aModuleMenu[] = $customMenu;
    }
    return true;
}


function generatePassword($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyz-';
    $numChars = strlen($chars);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= substr($chars, rand(1, $numChars) - 1, 1);
    }
    return $string;
}


// Фикс служб доставки
AddEventHandler("sale", "OnSaleComponentOrderOneStepOrderProps", "fixOrderDefaultLocation");

function fixOrderDefaultLocation(&$arResult, &$arUserResult, &$arParams)
{
    foreach ($arResult['ORDER_PROP']['USER_PROPS_Y'] as $key => &$arProp) {
        if ($arProp['TYPE'] == 'LOCATION') {
            if (!$arProp['VALUE']) {
                $arProp['VALUE'] = $arProp['DEFAULT_VALUE'];
                if (!$arUserResult["DELIVERY_LOCATION"]) {
                    $arUserResult["DELIVERY_LOCATION"] = $arProp['DEFAULT_VALUE'];
                }
            }
        }
    }
    unset($arProp);
}

function p($obj)
{
    global $USER;
    if ($USER->IsAdmin()) {
        echo "-<xmp>";
        print_r($obj);
        echo "</xmp>-";
    }
}

function pe($obj)
{
    global $USER;
    if ($USER->IsAdmin()) {
        echo "<xmp>";
        print_r($obj);
        echo "</xmp>";
        die();
    }
}

function pa($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}


AddEventHandler("main", "OnEpilog", "SaveAgents");

function SaveAgents()
{

    if ($_SERVER["HTTP_USER_AGENT"] && empty($_COOKIE["AGENT_SAVE"])) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");

        $myfile_name = 'a_' . date('Y-m-d') . '.csv';
        $uploader_path_file_full = $_SERVER["DOCUMENT_ROOT"] . '/upload/logs/agents/' . $myfile_name;
        $csvFile = new CCSVData('R', false);
        $csvFile->SetDelimiter(';');
        $arField = array(
            $_SERVER["HTTP_USER_AGENT"],
            date('Y-m-d H:i:s')
        );
        //pre($arField);

        $csvFile->SaveFile($uploader_path_file_full, $arField);
        setcookie("AGENT_SAVE", "Y", mktime(0, 0, 0, date("m"), date("d") + 3, date("Y")), "/");
    }
}

AddEventHandler("main", "OnEpilog", "metaChange",9999);
function metaChange()
{
    $h1='';
    $title='';
    $description='';
    if ($_SERVER['REQUEST_URI'] == '/catalog/vstraivaemaya-bytovaya-tekhnika/') {
        $h1 = "Встраиваемая бытовая техника";
        $title = "Встраиваемая бытовая техника для кухни премиум класса, купить встраиваемую бытовую технику в Москве по ценам дилера в интернет-магазине Еврофлэтт ";

        $description = "Широкий ассортимент встраиваемой бытовой техники для кухни премиум-класса. В наличии большое количество различной техники от именитых брендов. Выгодные цены и честная гарантия. Доставка, установка и подключение техники в Москве. ";

    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/dukhovye-shkafy/') {


        $h1 = "Встраиваемые духовые шкафы";
        $title = "Встраиваемые духовые шкафы, купить встраиваемую духовку по цене официального дилера в интернет-магазине Еврофлэтт";

        $description = "Встраиваемые духовые шкафы от именитых брендов. В наличии большой ассортимент. У нас вы купите встраиваемый духовой шкаф премиум класса по привлекательной цене. Гарантия производителя на все модели. Доставка по Москве и в любой регион России. Установка и подключение по желанию.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/krupnaya-bytovaya-tekhnika/') {


        $h1 = "Крупная бытовая техника";
        $title = "Крупная бытовая техника для кухни в Москве, большой каталог, доступные цены, купить крупную технику в интернет-магазине Еврофлэтт";

        $description = "Широкий ассортимент крупной бытовой техники премиум-класса для кухни. В наличии большое количество разной техники. Крупная бытовая техника от именитых брендов. Выгодные цены и честная гарантия. Доставка, установка и подключение техники в Москве.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/') {


        $h1 = "Холодильники";
        $title = "Холодильники в ассортименте, купить холодильник премиум класса по ценам официального дилера в интернет-магазине Еврофлэтт";

        $description = "Холодильники премиум класса от именитых брендов. В наличии большой каталог моделей. У нас вы купите холодильник с гарантией и по привлекательной цене. Доставка по Москве и в любой регион России. Установка и подключение по желанию.";

    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/liebherr/') {


        $h1 = "Холодильники Liebherr";
        $title = "Холодильники Liebherr премиум класса, купить холодильник Либхер с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Liebherr для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Liebherr по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/hitachi/') {


        $h1 = "Холодильники Hitachi";
        $title = "Холодильники Hitachi премиум класса, купить холодильник Хитачи с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Hitachi для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Hitachi по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/vestfrost/') {


        $h1 = "Холодильники Vestfrost";
        $title = "Холодильники Vestfrost премиум класса, купить холодильник Вестфрост с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Vestfrost для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Vestfrost по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/mitsubishi-electric/') {


        $h1 = "Холодильники Mitsubishi";
        $title = "Холодильники Mitsubishi Electric премиум класса, купить холодильник Мицубиси с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Mitsubishi для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Mitsubishi по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/kuppersberg/') {


        $h1 = "Холодильники Kuppersberg";
        $title = "Холодильники Kuppersberg премиум класса, купить холодильник Куперсберг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Kuppersberg для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Kuppersberg по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/kholodilniki/korting/') {


        $h1 = "Холодильники Korting";
        $title = "Холодильники Korting премиум класса, купить холодильник Кертинг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Холодильники Korting для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите холодильник Korting по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/') {


        $h1 = "Посудомоечные машины";
        $title = "Посудомоечные машины в ассортименте, купить посудомоечную машину премиум класса по ценам официального дилера в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины премиум класса от именитых брендов. В наличии большой каталог посудомоек. У нас вы купите посудомоечную машину с гарантией и по привлекательной цене. Доставка по Москве и в любой регион России. Установка и подключение по желанию.";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/korting/') {


        $h1 = "Посудомоечные машины Korting";
        $title = "Посудомоечные машины Korting премиум класса, купить посудомоечную машину Кертинг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины Korting для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите посудомоечную машину Korting по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/asko/') {


        $h1 = "Посудомоечные машины Asko";
        $title = "Посудомоечные машины Asko премиум класса, купить посудомоечную машину Аско с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины Asko для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите посудомоечную машину Asko по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/kuppersberg/') {


        $h1 = "Посудомоечные машины Kuppersberg";
        $title = "Посудомоечные машины Kuppersberg премиум класса, купить посудомоечную машину Куперсберг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины Kuppersberg для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите посудомоечную машину Kuppersberg по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/kaiser/') {


        $h1 = "Посудомоечные машины Kaiser";
        $title = "Посудомоечные машины Kaiser премиум класса, купить посудомоечную машину Кайзер с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины Kaiser премиум-класса в интернет-магазине бытовой техники «Еврофлэтт». В наличии большой ассортимент моделей. Купите посудомоечную машину Kaiser по ценам официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/posudomoechnye-mashiny/kitchen-aid/') {


        $h1 = "Посудомоечные машины Kitchen Aid";
        $title = "Посудомоечные машины Kitchen Aid премиум класса, купить посудомоечную машину Китчен Эйд с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Посудомоечные машины Kitchen Aid премиум-класса в интернет-магазине бытовой техники «Еврофлэтт». В наличии большой ассортимент моделей. Купите посудомоечную машину Kitchen Aid по ценам официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/vinnye-shkafy/') {


        $h1 = "Винные шкафы";
        $title = "Винные шкафы в ассортименте, купить винный шкаф премиум класса по ценам официального дилера в интернет-магазине Еврофлэтт";

        $description = "Винные шкафы премиум класса от именитых брендов. В наличии большой каталог моделей. У нас вы купите винный шкаф с гарантией и по привлекательной цене. Доставка по Москве и в любой регион России. Установка и подключение по желанию.";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/vinnye-shkafy/liebherr/') {


        $h1 = "Винные шкафы Liebherr";
        $title = "Винные шкафы Liebherr премиум класса, купить винный шкаф Либхер с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Винные шкафы Liebherr для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите винный шкаф Liebherr по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/vinnye-shkafy/cold-vine/') {


        $h1 = "Винные шкафы Cold Vine";
        $title = "Винные шкафы Cold Vine премиум класса, купить винный шкаф Колд Вайн с доставкой по Москве и России в интернет-магазине Еврофлэтт";
        $description = "Винные шкафы Cold Vine для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите винный шкаф Cold Vine по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/vinnye-shkafy/vestfrost/') {


        $h1 = "Винные шкафы Vestfrost";
        $title = "Винные шкафы Vestfrost премиум класса, купить винный шкаф Вестфрост с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Винные шкафы Vestfrost для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите винный шкаф Vestfrost по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/vinnye-shkafy/ip/') {


        $h1 = "Винные шкафы IP";
        $title = "Винные шкафы IP премиум класса, купить винный шкаф IP с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Винные шкафы IP для дома в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите винный шкаф IP по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/khyumidory/') {


        $h1 = "Хьюмидоры для сигар";
        $title = "Хьюмидоры для хранения сигар, купить хьюмидор премиум класса по ценам официального дилера в интернет-магазине Еврофлэтт";

        $description = "Хьюмидоры премиум класса от именитых брендов. В наличии большой каталог моделей. У нас вы купите хьюмидор с гарантией и по привлекательной цене. Доставка по Москве и в любой регион России. Установка и подключение по желанию.";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/') {


        $h1 = "Стиральные машины";
        $title = "Стиральные машины премиум-класса, купить стиральную машинку у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите стиральную машину с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России. Можно заказать установку и подключение.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/asko/') {


        $h1 = "Стиральные машины Asko";
        $title = "Стиральные машины Asko премиум класса, купить стиральную машинку Аско с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Asko в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Аско по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/korting/') {


        $h1 = "Стиральные машины Korting";
        $title = "Стиральные машины Korting премиум класса, купить стиральную машинку Кертинг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Korting в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Кортинг по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/neff/') {


        $h1 = "Стиральные машины Neff";
        $title = "Стиральные машины Neff премиум класса, купить стиральную машинку Нефф с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Neff в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Нефф по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/vestfrost/') {


        $h1 = "Стиральные машины Vestfrost";
        $title = "Стиральные машины Vestfrost премиум класса, купить стиральную машинку Вестфрост с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Vestfrost в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Вестфрост по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/kuppersberg/') {


        $h1 = "Стиральные машины Kuppersberg";
        $title = "Стиральные машины Kuppersberg премиум класса, купить стиральную машинку Куперсберг с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Kuppersberg в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Куперсберг по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/smeg/') {


        $h1 = "Стиральные машины Smeg";
        $title = "Стиральные машины Smeg премиум класса, купить стиральную машинку Смег с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Smeg в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Смег по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/kuppers/') {


        $h1 = "Стиральные машины Kuppersbusch";
        $title = "Стиральные машины Kuppersbusch премиум класса, купить стиральную машинку Куперсбуш с доставкой по Москве и России в интернет-магазине Еврофлэтт";


        $description = "Стиральные машины Kuppersbusch в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Куперсбуш по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/v-zug/') {


        $h1 = "Стиральные машины V-ZUG";
        $title = "Стиральные машины V-ZUG премиум класса, купить стиральную машинку V-ZUG с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины V-ZUG в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку V-ZUG по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/gaggenau/') {


        $h1 = "Стиральные машины Gaggenau";
        $title = "Стиральные машины Gaggenau премиум класса, купить стиральную машинку Гагенау с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Gaggenau в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Гагенау по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/maunfeld/') {


        $h1 = "Стиральные машины Maunfeld";
        $title = "Стиральные машины Maunfeld премиум класса, купить стиральную машинку Маунфилд с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Maunfeld в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Маунфилд по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/stiralnye-mashiny/teka/') {


        $h1 = "Стиральные машины Teka";
        $title = "Стиральные машины Teka премиум класса, купить стиральную машинку Тека с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Стиральные машины Teka в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите стиральную машинку Тека по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-mashiny/') {


        $h1 = "Сушильные машины";
        $title = "Сушильные машины премиум-класса, купить сушильную машину у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Сушильные машины премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите сушильную машину с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России. Можно заказать установку и подключение.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-mashiny/asko/') {


        $h1 = "Сушильные машины Asko";
        $title = "Сушильные машины Asko премиум класса, купить сушильную машинку Аско с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Сушильные машины Asko в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите сушильную машинку Аско по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-mashiny/kuppers/') {


        $h1 = "Сушильные машины Kuppersbusch";
        $title = "Сушильные машины Kuppersbusch премиум класса, купить сушильную машинку Куперсбуш с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Сушильные машины Kuppersbusch в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите сушильную машинку Куперсбуш по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.  ";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-mashiny/smeg/') {


        $h1 = "Сушильные машины Smeg";
        $title = "Сушильные машины Smeg премиум класса, купить сушильную машинку Смег с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Сушильные машины Smeg в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите сушильную машинку Смег по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-mashiny/v-zug/') {


        $h1 = "Сушильные машины V-ZUG";
        $title = "Сушильные машины V-ZUG премиум класса, купить сушильную машинку V-ZUG с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Сушильные машины V-ZUG в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите сушильную машинку V-ZUG по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-shkafy/') {


        $h1 = "Сушильные шкафы";
        $title = "Сушильные шкафы премиум-класса, купить сушильный шкаф у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Сушильные шкафы премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите сушильный шкаф с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России. Можно заказать установку и подключение.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/sushilnye-shkafy/asko/') {


        $h1 = "Сушильные шкафы Asko";
        $title = "Сушильные шкафы Asko премиум класса, купить сушильный шкаф Аско с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Сушильные шкафы Asko в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите сушильный шкаф Аско по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/gladilnye-mashiny/') {


        $h1 = "Гладильные машины";
        $title = "Гладильные машины премиум-класса, купить гладильную систему у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Гладильные системы премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите гладильную машину с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/melkaya-bytovaya-tekhnika/') {


        $h1 = "Мелкая бытовая техника";
        $title = "Мелкая бытовая техника для кухни премиум класса, купить мелкую технику для дома в Москве по ценам дилера в интернет-магазине Еврофлэтт ";

        $description = "Широкий ассортимент мелкой бытовой техники для кухни премиум-класса. Всегда в наличии большое количество различной мелкой техники именитых брендов. Выгодные цены и честная гарантия. Доставка по всей территории России. ";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/izmelchiteli/') {


        $h1 = "Измельчители пищевых отходов";
        $title = "Измельчители пищевых отходов, купить электрический измельчитель под раковину у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Измельчители пищевых отходов в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите измельчитель для раковины с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России. Можно заказать установку и подключение.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/izmelchiteli/bone_crusher/') {


        $h1 = "Измельчители отходов Bone Crusher";
        $title = "Измельчители Bone Crusher под раковину, купить измельчитель пищевых отходов Бон Крашер с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Измельчители пищевых отходов Bone Crusher в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите измельчитель бытовых отходов Бон Крашер по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/izmelchiteli/in_sink_erator/') {


        $h1 = "Измельчители отходов In Sink Erator";
        $title = "Измельчители In Sink Erator под раковину, купить измельчитель пищевых отходов Ин Синк Эратор с доставкой по Москве и России в интернет-магазине Еврофлэтт";


        $description = "Измельчители пищевых отходов In Sink Erator в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите измельчитель бытовых отходов Ин Синк Эратор по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/pylesosy/') {


        $h1 = "Пылесосы";

        $title = "Пылесосы премиум-класса, купить пылесос у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Пылесосы премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите пылесос с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/chayniki/') {


        $h1 = "Чайники";

        $title = "Чайники премиум-класса, купить классический или электрический чайник у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Чайники премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог классических и электрических моделей. У нас вы купите чайник с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/chayniki/smeg/') {


        $h1 = "Чайники Smeg";
        $title = "Чайники Smeg премиум класса, купить электрический чайник Смег с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Чайники Smeg в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите электрический чайник Smeg по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/chayniki/kitchen-aid/') {


        $h1 = "Чайники Kitchen Aid";
        $title = "Чайники Kitchen Aid премиум класса, купить классический или электрический чайник Китчен Эйд с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Чайники Kitchen Aid в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент обычных и электрических моделей. У нас вы купите чайник Китчен Эйд по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/chayniki/bugatti/') {


        $h1 = "Чайники Bugatti";
        $title = "Чайники Bugatti премиум класса, купить электрический чайник Бугатти с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Чайники Bugatti в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите электрический чайник Бугатти по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России. ";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/blendery/') {


        $h1 = "Блендеры";

        $title = "Блендеры премиум-класса, купить блендер у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Блендеры премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите блендер с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/blendery/kitchen-aid/') {


        $h1 = "Блендеры Kitchen Aid";
        $title = "Блендеры Kitchen Aid премиум класса, купить блендер Китчен Эйд с доставкой по Москве и России в интернет-магазине Еврофлэтт";


        $description = "Блендеры Kitchen Aid в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите блендер Китчен Эйд по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/blendery/bugatti/') {


        $h1 = "Блендеры Bugatti";
        $title = "Блендеры Bugatti премиум класса, купить блендер Бугатти с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Блендеры Bugatti в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите блендер Бугатти по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России. ";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/tostery/') {


        $h1 = "Тостеры";

        $title = "Тостеры премиум-класса, купить тостер у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Тостеры премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите тостер с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/tostery/kitchen-aid/') {


        $h1 = "Тостеры Kitchen Aid";
        $title = "Тостеры Kitchen Aid премиум класса, купить тостер Китчен Эйд с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Тостеры Kitchen Aid в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите тостер Китчен Эйд по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/tostery/bugatti/') {


        $h1 = "Тостеры Bugatti";
        $title = "Тостеры Bugatti премиум класса, купить тостер Бугатти с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Тостеры Bugatti в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите тостер Бугатти по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России. ";
    } elseif ($_SERVER['REQUEST_URI'] == '/catalog/miksery/') {


        $h1 = "Миксеры";

        $title = "Миксеры премиум-класса, купить миксер у официального дилера в Москве в интернет-магазине Еврофлэтт";

        $description = "Миксеры премиум-класса в интернет-магазине официального дилера известных брендов в Москве. В наличии большой каталог моделей. У нас вы купите миксер с честной гарантией по выгодной цене. Доставка по Москве и в любой регион России.";


    }  elseif ($_SERVER['REQUEST_URI'] == '/catalog/miksery/kitchen-aid/') {


        $h1 = "Миксеры Kitchen Aid";
        $title = "Миксеры Kitchen Aid премиум класса, купить миксер Китчен Эйд с доставкой по Москве и России в интернет-магазине Еврофлэтт";

        $description = "Миксеры Kitchen Aid в интернет-магазине официального дилера известных брендов «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите миксер Китчен Эйд по выгодной цене. Качественный сервис, честная гарантия, удобные способы оплаты и доставка по России.";


    }
    global $APPLICATION;
    if(!empty($h1)){
        $APPLICATION->SetTitle($h1);
    }
    if(!empty($title)){
        $APPLICATION->SetPageProperty("title", $title, false);
    }
    if(!empty($description)){
        $APPLICATION->SetPageProperty("description", $description);
    }
}
//добавялем номер страницы в мета-теги
// AddEventHandler("main", "OnEpilog", "OnEpilogHandler");
// function OnEpilogHandler(){
// global $APPLICATION;
// 	if (!defined('ERROR_404') && intval($_GET["PAGEN_2"]) > 0) {
// 		$APPLICATION->SetPageProperty("title", $APPLICATION->GetPageProperty("title") . " – " . intval($_GET["PAGEN_2"]) . " страница");
// 		$APPLICATION->SetPageProperty("keywords","");
// 		$APPLICATION->SetPageProperty("description", "");
// 	}
// 	if ($_GET['PAGEN_2']==='1' && isset($_GET['PAGEN_2'])) {
// 		LocalRedirect($APPLICATION->GetCurPageParam("", array("PAGEN_1")));
// 	}
// }

function prn($obj)
{
    define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/log.txt");
    $dump = "<pre style='font-size: 11px; font-family: tahoma;'>" . print_r($obj, true) . "</pre>";
    $files = $_SERVER["DOCUMENT_ROOT"] . "/dump.html";
    $fp = fopen($files, "a+") or die("Не могу открыть $temp");// открываем файл для записи данных
    if (fwrite($fp, $dump) === FALSE) {// добавляем запись в файл
        AddMessage2Log("Не могу произвести запись в файл ($filename)");
        exit;
    }
    fclose($fp);
}

CModule::IncludeModule("iblock");
// функция добавления брендов
function addBrand($arFields)
{
    $SECTIONS = array(657, 838, 682, 792, 813, 933, 753, 780, 723, 930, 741, 1025, 931, 1294, 983, 679, 645, 655, 1049, 739, 750, 773, 779, 1299, 835, 860, 867, 705, 810, 653, 649, 832, 771, 836, 789, 633);
    foreach ($SECTIONS as $S) {

        $res = CIBlockSection::GetByID($S);
        if ($ar_res = $res->GetNext())
            $pictureId = $ar_res['PICTURE'];

        $bs = new CIBlockSection;
        $arFields1 = Array(
            "ACTIVE" => "Y",
            "IBLOCK_SECTION_ID" => $S,
            "IBLOCK_ID" => $ar_res['IBLOCK_ID'],
            "NAME" => $arFields['NAME'],
            "SECTION_CODE" => $arFields['CODE'],
        );
        $ID = $bs->Add($arFields1);
        //echo "<pre>"; print_r($arFields1); echo "</pre>"; echo $bs->LAST_ERROR; die();
        $sec = new CIBlockSection();
        $file = CFile::MakeFileArray($pictureId);
        $result = $sec->Update(
            $ID,
            array(
                "PICTURE" => $file,
            )
        );
    }
}

// Генерируем артикул товара после добавления
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("MyClassUpdate", "OnAfterIBlockElementAddHandler"));

class MyClassUpdate
{
    // создаем обработчик события "OnAfterIBlockElementAdd"
    function OnAfterIBlockElementAddHandler(&$arFields)
    {
        if ($arFields["ID"] > 0) {
            $res = CIBlock::GetByID($arFields["IBLOCK_ID"]);
            if ($ar_res = $res->GetNext())
                $art_first = strtoupper($ar_res['CODE'][0]);
            CIBlockElement::SetPropertyValuesEx($arFields['ID'], false, array('ARTNUMBER' => $art_first . $arFields["ID"]));
        }
        if ($arFields['IBLOCK_ID'] == 9) {
            //die("123");
            addBrand($arFields);
        }
    }
}

//AddEventHandler("main", "OnEpilog", "Redireect404");


function ImportOffersXMLFile()
{

    $DIR = $_SERVER['DOCUMENT_ROOT'] . '/local/import/';

    if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
        return "ImportOffersXMLFile();";

    if (!$log_file = fopen($DIR . '/logs/log_' . date("Y-m-d_H-i-s") . '.txt', 'w'))
        return "ImportOffersXMLFile();";

    if (file_exists($DIR . 'config.xml')) {
        if (!$config = simplexml_load_file($DIR . 'config.xml')) {
            fwrite($log_file, 'ERROR;Не могу загрузить конфиг;' . PHP_EOL);
            return "ImportOffersXMLFile();";
        }
    } else {
        fwrite($log_file, 'ERROR;Не могу найти конфиг;' . PHP_EOL);
        return "ImportOffersXMLFile();";
    }
    unlink($DIR . 'file_old.xml');
    copy($DIR . 'file.xml', $DIR . 'file_old.xml');
    unlink($DIR . 'file.xml');
    if (!copy('http://www.hausdorf.ru/ymnew/file.xml', $DIR . 'file.xml')) {
        fwrite($log_file, 'ERROR;Не могу скопировать файл импорта;' . PHP_EOL);
        return "ImportOffersXMLFile();";
    }
    if (!$xml = simplexml_load_file($DIR . 'file.xml')) {
        fwrite($log_file, 'ERROR;Не могу открыть файл импорта;' . PHP_EOL);
        return "ImportOffersXMLFile();";
    }

    foreach ($config->IBlocks->block as $block)
        if ((int)$block['cat_id'] > 0) {
            $property__available = array();
            $property_enums = CIBlockPropertyEnum::GetList(array(), Array("IBLOCK_ID" => (int)$block['id'], "CODE" => "_AVAILABLE"));
            while ($enum_fields = $property_enums->GetNext())
                $arAvailable[(int)$block['id']][$enum_fields['VALUE']] = $enum_fields['ID'];
            $arrBlocks[(int)$block['cat_id']] = array('IB_ID' => (int)$block['id'], 'CAT_ID' => (int)$block['cat_id'], 'NAME' => (string)$block['name']);
        }

    foreach ($config->brands->brand as $brand)
        $arrBrands[strtoupper((string)$brand['code'])] = array('ID' => (int)$brand['id'], 'NAME' => (string)$brand['name'], 'AVAILABLE' => (string)$brand['available'], 'PRICE' => (string)$brand['price']);

    foreach ($arrBrands as $name => $brand) {
        if (($brand["AVAILABLE"] == 'Y') || ($brand['PRICE'] == 'Y')) {
            foreach ($arrBlocks as $block) {
                $res = CIBlockElement::GetList(Array(), Array('IBLOCK_ID' => $block['IB_ID'], 'PROPERTY__BRAND' => $brand['ID']), false, false, Array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY__AVAILABLE', 'PROPERTY_MODEL', 'PROPERTY__BRAND', 'PROPERTY_SEARCHING'));
                while ($elem = $res->fetch()) {
                    $ar_prod = CCatalogProduct::GetByID($elem['ID']);
                    $arUpdatedEls[$elem['ID']] = array_merge($elem, array('QUANTITY' => $ar_prod['QUANTITY'], 'AVAILABLE' => ($ar_prod['QUANTITY'] > 0 ? 'true' : 'false')));
                    if ($brand["AVAILABLE"] == 'Y') $arUpdatedEls[$elem['ID']]['NEW_AVAILABLE'] = $arUpdatedEls[$elem['ID']]['AVAILABLE'];//'false'
                    $arByCat[$block['CAT_ID']][$brand['ID']][] = (int)$elem['ID'];
                }
            }
        } else $arrBrands[$name]["IGNORE"] = true;
    }

    $log = '';
    $ib_log = '';
    foreach ($xml->shop->offers->offer as $offer) {
        $vendor = strtoupper((string)$offer->vendor);
        $categoryId = (integer)$offer->categoryId;
        $product_id = 0;
        $vendor_id = 0;

        if (empty($arrBlocks[$categoryId]['IB_ID'])) {
            $log .= 'Категория не включена в импорт;' . $offer->typePrefix . ' ' . $offer->vendor . ' ' . $offer->model . ';(xml_id:' . $offer['id'] . ')' . PHP_EOL;
            continue;
        }

        if (!empty($arrBrands[$vendor])) {
            if ($arrBrands[$vendor]['IGNORE']) {
                $log .= 'Бренд не включен в импорт;' . $offer->typePrefix . ' ' . $offer->vendor . ' ' . $offer->model . ';(xml_id:' . $offer['id'] . ')' . PHP_EOL;
                continue;
            } else $vendor_id = $arrBrands[$vendor]['ID'];
        } else {
            foreach ($arrBrands as $code => $brand)
                if (strtoupper($brand['NAME']) == $vendor) {
                    if ($brand['IGNORE']) {
                        $log .= 'Бренд не включен в импорт;' . $offer->typePrefix . ' ' . $offer->vendor . ' ' . $offer->model . ';(xml_id:' . $offer['id'] . ')' . PHP_EOL;
                        $vendor_id = $brand['ID'];
                        continue;
                    } else {
                        $vendor_id = $brand['ID'];
                        $vendor = $code;
                        continue;
                    }
                }
            if (!$vendor_id) {
                $log .= 'Бренд не найден;' . $offer->typePrefix . ' ' . $offer->vendor . ' ' . $offer->model . ';(xml_id:' . $offer['id'] . ')' . PHP_EOL;
                continue;
            }
        }

        foreach ($arByCat[$categoryId][$vendor_id] as $idn => $id) {
            if (($arUpdatedEls[$id]["PROPERTY_MODEL_VALUE"] == (string)$offer->model) || (strpos($arUpdatedEls[$id]["PROPERTY_SEARCHING_VALUE"], (string)$offer->model) !== false)) {
                if ($arrBrands[$vendor]['AVAILABLE'] == 'Y')
                    $arUpdatedEls[$id]['NEW_AVAILABLE'] = (string)$offer['available'];

                if ($arrBrands[$vendor]['PRICE'] == 'Y') {
                    if ($price = CPrice::GetList(array(), array('PRODUCT_ID' => $id, 'CATALOG_GROUP_ID' => 2))->fetch()) {
                        $arUpdatedEls[$id]['OLD_PRICE'] = (int)$price["PRICE"];
                        if (((int)$price["PRICE"] != (int)$offer->price) || ($price["CURRENCY"] != 'RUB'))
                            if (CPrice::Update($price["ID"], array('PRICE' => (int)$offer->price, 'CURRENCY' => 'RUB')))
                                $arUpdatedEls[$id]['NEW_PRICE'] = (string)$offer->price;
                    }
                }
                $product_id = $id;
                if ($arUpdatedEls[$id]["PROPERTY_MODEL_VALUE"] != (string)$offer->model) {
                    $arUpdatedEls[$id]["FROM_SEARCHING"] = true;
                } else {
                    unset ($arByCat[$categoryId][$vendor_id][$idn]);
                }
                break;
            }
        }
        if (empty($product_id))
            $log .= 'Не найдено в каталоге;' . $offer->typePrefix . ' ' . $offer->vendor . ' ' . $offer->model . ';(xml_id:' . $offer['id'] . ')' . PHP_EOL;
    }

    foreach ($arUpdatedEls as $elem) {
        if (($elem['NEW_AVAILABLE'] != $elem["AVAILABLE"]) || isset($elem['NEW_PRICE'])) {
            if ($elem['NEW_AVAILABLE'] != $elem["AVAILABLE"]) {
                //$el = new CIBlockElement;
                //$res = $el->Update($elem['ID'],  array("ACTIVE" => ($elem['NEW_AVAILABLE']=='true'?'Y':'N')));
                CIBlockElement::SetPropertyValues($elem['ID'], $elem["IBLOCK_ID"], $arAvailable[$elem["IBLOCK_ID"]][$elem['NEW_AVAILABLE']], '_AVAILABLE');
                CCatalogProduct::Update($elem['ID'], array('QUANTITY' => ($elem['NEW_AVAILABLE'] == 'true' ? 10 : 0)));
                $ib_log .= 'Переведено в состояние ' . ($elem['NEW_AVAILABLE'] == 'true' ? '' : 'не ') . 'доступно;' . $elem['NAME'] . ';(ib_id:' . $elem['ID'] . ')' . PHP_EOL;
            }
            if (isset($elem['NEW_PRICE']))
                $ib_log .= 'Изменана цена с ' . $elem['OLD_PRICE'] . ' на ' . $elem['NEW_PRICE'] . ';' . $elem['NAME'] . ';(ib_id:' . $elem['ID'] . ')' . PHP_EOL;
        } else $ib_log .= 'Без изменений;' . $elem['NAME'] . ';(ib_id:' . $elem['ID'] . ')' . PHP_EOL;
    }

    fwrite($log_file, $ib_log);
    fwrite($log_file, $log);
    fclose($log_file);

    return "ImportOffersXMLFile();";
}

function Redireect404()
{
    global $APPLICATION;
    $params = 'PAGEN_' . RAND(1, 99) . '=' . RAND(1, 99);
    $page = $APPLICATION->GetCurPageParam($params, array());
    $APPLICATION->AddHeadString('<link rel="canonical" href="http://' . $_SERVER["HTTP_HOST"] . $page . '" />', true);
}


/**
 * Get section names of elements
 * ToDo: check structure
 *
 * @param array $sections
 * @return array
 */
function getElementsSections($sections)
{
    $section_names = array();
    if (!empty($sections)) {
        // Get elements
        $res = CIBlockElement::GetList(
            array(),
            array("ID" => $sections),
            false,
            array("nPageSize" => 999999999999),
            array("ID", "IBLOCK_SECTION_ID")
        );

        $elemnt_section = array();
        while ($ob = $res->GetNext()) {
            $elemnt_section[$ob["ID"]] = $ob["IBLOCK_SECTION_ID"];
        }

        // Get brands
        $brand_sections = array();
        $SectList = CIBlockSection::GetList(
            array(),
            array("ID" => array_unique(array_values($elemnt_section))),
            false,
            array("ID", "NAME"),
            array("nPageSize" => 999999999999)
        );
        while ($arSecList = $SectList->GetNext()) {
            $brand_sections[$arSecList["ID"]] = $arSecList["NAME"];
        }

        foreach ($elemnt_section as $element => $section) {
            // get sections
            $ibsTreeResource = CIBlockSection::GetNavChain(false, $section, array("ID", "NAME"));
            $section_names[$element]['section'] = ($sectionItem = $ibsTreeResource->Fetch()) ? $sectionItem["NAME"] : "";
            $section_names[$element]['brand'] = (!empty($brand_sections[$section])) ? $brand_sections[$section] : "";
        }
    }
    return $section_names;
}

/*function GetRateFromCBR($CURRENCY){
	global $DB;
	global $APPLICATION;

	CModule::IncludeModule('currency');
	if(!CCurrency::GetByID($CURRENCY))
	//такой валюты нет на сайте, агент в этом случае удаляется
	return false;

	$DATE_RATE=date("d.m.Y");//сегодня
	$QUERY_STR = "date_req=".$DB->FormatDate($DATE_RATE, CLang::GetDateFormat("SHORT", $lang), "D.M.Y");

	//делаем запрос к www.cbr.ru с просьбой отдать курс на нынешнюю дату
	$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);

	//получаем XML и конвертируем в кодировку сайта
	$charset = "windows-1251";
	if (preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $strQueryText, $matches))
	   {
		  $charset = Trim($matches[1]);
	   }
	$strQueryText = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $strQueryText);
	$strQueryText = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $strQueryText);
	$strQueryText = $APPLICATION->ConvertCharset($strQueryText, $charset, SITE_CHARSET);

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

	//парсим XML
	$objXML = new CDataXML();
	$res = $objXML->LoadString($strQueryText);
	if($res !== false)
		$arData = $objXML->GetArray();
	else
		$arData = false;

	$NEW_RATE=Array();

	//получаем курс нужной валюты $CURRENCY
	if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"])>0)
	{
		for ($j1 = 0; $j1<count($arData["ValCurs"]["#"]["Valute"]); $j1++)
		{
			if ($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"]==$CURRENCY)
			{
				$NEW_RATE['CURRENCY']=$CURRENCY;
				$NEW_RATE['RATE_CNT'] = IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]);
				$NEW_RATE['RATE'] = DoubleVal(str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"]));
				$NEW_RATE['DATE_RATE']=$DATE_RATE;
				break;
			}
		}
	}

	if ((isset($NEW_RATE['RATE']))&&(isset($NEW_RATE['RATE_CNT'])))
	{
		//курс получили, возможно, курс на нынешнюю дату уже есть на сайте, проверяем
		CModule::IncludeModule('currency');
		$arFilter = array(
			"CURRENCY" => $NEW_RATE['CURRENCY'],
			"DATE_RATE"=>$NEW_RATE['DATE_RATE']
		);
		$by = "date";
		$order = "desc";

		$db_rate = CCurrencyRates::GetList($by, $order, $arFilter);
		if(!$ar_rate = $db_rate->Fetch())
		//такого курса нет, создаём курс на нынешнюю дату
			CCurrencyRates::Add($NEW_RATE);

	}

	//возвращаем код вызова функции, чтобы агент не "убился"
	return 'GetRateFromCBR("'.$CURRENCY.'");';
}*/

require_once __DIR__ . '/include/Elements.class.php';

$handler = Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    array(
        'Tools\Euroflett\Import\Elements',
        'UpdateCheck'
    )
);

function redirWithOutWww()
{
    if (!preg_match('|^www\..*|', $_SERVER ['HTTP_HOST'])) {
        if (isHttps()) {
            $protocol = 'https://www.';
        } else {
            $protocol = 'http://www.';
        }
        $redirect = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

//AddEventHandler("main", "OnPageStart", "redirHttps");
//AddEventHandler("main", "OnPageStart", "redirWithOutWww");

// Вырезаем картинку из хинта
AddEventHandler("main", "OnEndBufferContent", "erasePictureFromHint");

function erasePictureFromHint(&$content)
{
    $content = preg_replace('/<div class="detail__hint-img"><img src=".*?".*?\/><\/div>/', '', $content);
}


function pre($arr, $print = false, $die = false)
{
    global $USER;
    if ($USER->IsAdmin()) {
        if (!$print) echo '<!--HARDKOD DEBUG ';
        else echo '<pre>';
        print_r($arr);
        if (!$print) echo '-->' . "\n";
        else echo '</pre>';

        if ($die) die('HARDKOOOOOOOOOOOOOOD');
    }
}

function _a($arr=[], $die = false)
{
    global $USER;
    if ($USER->IsAdmin()) {
        echo '<pre>';
        var_dump($arr);
        echo '</pre>';

        if ($die) die('HARDKOOOOOOOOOOOOOOD');
    }
}

/**
 * SEO
 */
require_once 'seo.php';

// Подключение агентов fit_files();  10800
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents.php')) {
	
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents.php';
}

// Подключение агентов AgentCheckFeedXB();  1043737
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_xb.php')) {
	
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_xb.php';
}

// AgentCheckFeedDietrich
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_dietrich.php')) {
	
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/agents_dietrich.php';
}

 // CAgent::AddAgent("fit_files();",'mibix.yamexport',"N",10800);
 
function fit_files(){ 
	// require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/cron_file_add.php");
	$MODULE_ID = "mibix.yamexport";
	CModule::IncludeModule($MODULE_ID);
	$YAM_EXPORT = CMibixYandexExport::get_step_settings(1);

	$file_name = $YAM_EXPORT['step_path'];
	$file_name = $_SERVER["DOCUMENT_ROOT"].$file_name;
	
	$files_data = array(
		1 => array(
			'NAME'=>"euroflett_priceru.xml",
			'NAME_FEED'=>"priceru_feed",
			'NAME_FEED_OLD'=>"yml_catalog",
			'UTF_FEED_OLD'=>"utm_source=yamarket",
			'UTF_FEED'=>"utm_source=priceru",
		),
		2 => array(
			'NAME'=>"euroflett_ekatalog.xml",
			'NAME_FEED'=>"ekatalog_feed",
			'NAME_FEED_OLD'=>"yml_catalog",
			'UTF_FEED_OLD'=>"utm_source=yamarket",
			'UTF_FEED'=>"utm_source=ekatalog",
			
		)
	); 
	
	$str = file_get_contents($file_name);
	
	foreach ( $files_data as $file ) {
		
		
		$str_ = str_replace($file['NAME_FEED_OLD'], $file['NAME_FEED'], $str );
		$str_ = str_replace($file['UTF_FEED_OLD'], $file['UTF_FEED'], $str );
		file_put_contents (  $_SERVER["DOCUMENT_ROOT"].'/'.$file['NAME'] , $str_  );
		
	}
	return 'fit_files();';
}
// fit_files();

function L($Message){
	if (is_array($Message)) {
		$Message = print_r($Message,1);
	}
	$file_path = $_SERVER['DOCUMENT_ROOT'].'/!log.txt';
	$handle = fopen($file_path, 'a+');
	@flock($handle, LOCK_EX);
	fwrite($handle, '['.date('d.m.Y H:i:s').'] '.$Message."\r\n");
	@flock($handle, LOCK_UN);
	fclose($handle);
}

define('BX_AGENTS_LOG_FUNCTION', 'AgentsLog');
function AgentsLog($arAgent=false, $strOperation=false, $mEvalResult=false, $mEvalReturn=false){
	$strKey = __FUNCTION__.'_start_time';
	if($strOperation == 'start'){
		$GLOBALS[$strKey] = microtime(true);
		L(sprintf('Start: %s [%s], %s, %s', $arAgent['NAME'], strlen($arAgent['MODULE_ID'])?$arAgent['MODULE_ID']:'--nomodule--', $arAgent['AGENT_INTERVAL'], $arAgent['AGENT_INTERVAL']));
	}
	if($strOperation == 'finish' && isset($GLOBALS[$strKey])){
		L(sprintf('[%s] %s', number_format(microtime(true) - $GLOBALS[$strKey], 4, '.', ''), $arAgent['NAME']));
		unset($GLOBALS[$strKey]);
	}
} 


 function GetEntityDataClass__init($HlBlockId= 3) {
		CModule::IncludeModule('highloadblock');
		if (empty($HlBlockId) || $HlBlockId < 1)
		{
			return false;
		}
		$hlblock = HLBT::getById($HlBlockId)->fetch();	
		$entity = HLBT::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();
		return $entity_data_class;
	}



AddEventHandler("sale", "OnSaleComponentOrderOneStepProcess", array("hardkod", "OnSaleComponentOrderOneStepProcessHandler"));  
 
class hardkod
{
  static function OnSaleComponentOrderOneStepProcessHandler(&$arResult, &$arUserResult, $arParams)
   {   
      global $APPLICATION;
		if($arParams["RECAPTCHA"] == "Y"){

			$request = Context::getCurrent()->getRequest();
			$recaptcha_response = $request["recaptcha_response"];

			if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  $recaptcha_response) {

				$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
				$recaptcha_secret = RECAPTCHA_V3_SECRET_SITE_KEY;
				
			 
				// Отправляем POST запрос и декодируем результаты ответа
				$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
				$recaptcha = json_decode($recaptcha);
			 
				// Принимаем меры в зависимости от полученного результата
				if ($recaptcha->score >= 0.5) {
					// Проверка пройдена - отправляем сообщение.
					
					// prn($arResult); //prn
					// prn($arUserResult);
					// prn($arParams);
				} else {
					// Проверка не пройдена. Показываем ошибку.
					$arResult["ERROR"][] = 'Ошибка капчи!';
					// echo 'Ошибка капчи!';
				}

			}
		}
      

   }
}