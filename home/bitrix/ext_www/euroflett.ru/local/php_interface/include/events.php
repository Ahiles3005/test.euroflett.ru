<?


//Каноничная ссылка на товар
AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyLinkToSection", "GetUserTypeDescription"));

CModule::IncludeModule('iblock');

class CIBlockPropertyLinkToSection extends CIBlockPropertyElementList{

	function GetUserTypeDescription(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'wp_iblock_canonical_section',
			"DESCRIPTION" => "Web Profy: Привязка к разделу",
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
		);
	}


	function createTree(&$ar, $parent){
		$tree = array();
		foreach($parent as $val){
			if(isset($ar[$val['ID']]) && $val['ID'] !== 0){
				$val['CHILDREN'] = self::createTree($ar, $ar[$val['ID']]);
			}
			$tree[] = $val;
		}
		return $tree;
	}

	function showTree($tree, $arValue, $level = 0){
		foreach($tree as $category){
			if($category["ID"] === $arValue['VALUE']){
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			//Показываем название как во вкладке "Разделы", с точками.
			$ret .= '<option'.$selected.' value="'.$category["ID"].'">'.str_repeat(" . ", $category["DEPTH_LEVEL"]).$category["NAME"].'</option>';
			$ret .= self::showTree($category['CHILDREN'], $arValue, $level + 1);
		}
		return $ret;
	}

	public function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName){
		$options = '';
		$options = '<option value="">(Не выбран)</option>';
		$dbRes = CIBlockElement::GetList(Array(), $arFilter = Array("ID" => intval($_REQUEST['ID']), "SHOW_HISTORY" => "Y"), false, false);
		$arRes = $dbRes->GetNext();
		//Список разделов, к которым привязан элемент.
		$db_old_groups = CIBlockElement::GetElementGroups(intval($_REQUEST['ID']), false);
		while($ar_group = $db_old_groups->Fetch()){
			//Список всех родителей группы.
			$dbParents = CIBlockSection::GetNavChain(false, intval($ar_group["IBLOCK_SECTION_ID"]));
			while($arParents = $dbParents->Fetch()){
				//Для того, чтобы лишний раз не выбирать уникальные элементы, пишем их с ключом ID.
				$arList[$arParents["ID"]] = array('ID' => $arParents["ID"], 'PARENT' => intval($arParents["IBLOCK_SECTION_ID"]), 'NAME' => $arParents["NAME"], 'DEPTH_LEVEL' => $arParents["DEPTH_LEVEL"]);
			}
			$arList[$ar_group["ID"]] = array('ID' => $ar_group["ID"], 'PARENT' => intval($ar_group["IBLOCK_SECTION_ID"]), 'NAME' => $ar_group["NAME"], 'DEPTH_LEVEL' => $ar_group["DEPTH_LEVEL"]);
		}

		//Формируем массив для функции создания дерева.
		$new = array();
		foreach($arList as $val){
			$new[$val['PARENT']][] = $val;
		}

		//Это, конечно, стоит переосмыслить, но пока сойдёт. Да и функции полезные сами по себе.
		$tree = self::createTree($new, $new[0]);
		$options .= self::showTree($tree, $arValue);
		$html = str_replace('#OPTIONS#', $options, '
			<select name="'.$strHTMLControlName['VALUE'].'">
				#OPTIONS#
			</select>
		');

		return $html;
	}
}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("EuroflettCatalog", "OnAfterIBlockElementUpdateHandler"));
//AddEventHandler("catalog", "OnProductUpdate", Array("EuroflettCatalog", "OnProductUpdateHandler"));

class EuroflettCatalog
{
	function OnAfterIBlockElementUpdateHandler(&$arFields)
	{
		$ob = CIBlock::GetByID($arFields['IBLOCK_ID']);
		$arIblock = $ob->GetNext();
		if($arIblock['IBLOCK_TYPE_ID'] == 'catalog'){
			self::updatePreorderForElement($arFields['ID'], $arFields['IBLOCK_ID']);
			AddMessage2Log("Element updated");
		}
	}

	function OnProductUpdateHandler($id, &$arFields)
	{
		//$ob = CIBlock::GetByID($arFields['IBLOCK_ID']);
		//$arIblock = $ob->GetNext();
		//if($arIblock['IBLOCK_TYPE_ID'] == 'catalog'){
		self::updatePreorderForElement($id);
		AddMessage2Log("Product updated");
		//}
	}

	function isAvailableToBuy($id){
		CModule::IncludeModule('iblock');
		CModule::IncludeModule('catalog');
		$arProduct = CCatalogProduct::GetByID($id);
		$arPrice = CCatalogProduct::GetOptimalPrice($id, 1);
		if(!$arProduct)
			return false;

		// Товары с нулевым кол-вом или ценой — под заказ
		if($arProduct['QUANTITY'] <= 0 || $arPrice['PRICE']['PRICE'] <= 0)
			return false;

		/*
		// Особые случаи
		$BRAND_MIELE = 46;
		$VACUUM_CLEANERS_SECTION_MIELE = 812;

		if(intval($arBrand['VALUE']) == $BRAND_MIELE && $arItem['IBLOCK_SECTION_ID'] != $VACUUM_CLEANERS_SECTION_MIELE)
			return false;
		*/

		return true;
	}

	function isPrepayment($id, $isAvailable){
		CModule::IncludeModule('iblock');
		$ob = CIBlockElement::GetByID($id);
		$arItem = $ob->GetNext();
		$ob = CIBlockElement::GetProperty($arItem['IBLOCK_ID'], $id, "sort", "asc", array("CODE" => "_BRAND"));
		$arBrand = $ob->Fetch();

		// Особые случаи
		$BRAND_MIELE = 46;
		$VACUUM_CLEANERS_SECTION_MIELE = 812;

		if(intval($arBrand['VALUE']) == $BRAND_MIELE && $arItem['IBLOCK_SECTION_ID'] != $VACUUM_CLEANERS_SECTION_MIELE)
			return true;

		return !$isAvailable;
	}

	function updateMarketStatuses(){
		// Обновляет состояние флагов "Есть в наличии" и "Предоплата"
		// Предполагается запуск в агенте
		CModule::IncludeModule('iblock');
		CModule::IncludeModule('catalog');
		$ob = CIBlock::GetList(array(), array("TYPE" => "catalog", "LID" => "s1"));
		while($arIblock = $ob->GetNext()){
			$ob2 = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arIblock['ID'], "ACTIVE" => "Y", "SECTION_GLOBAL_ACTIVE" => "Y", "!PROPERTY_PROP_SHOW_ON_MAIN_VALUE" => "Да"), false, false, array("ID","IBLOCK_ID","ACTIVE", "CATALOG_GROUP_2","PROPERTY__AVAILABLE"));
			while($ar = $ob2->GetNext()){
				if(($ar["PROPERTY__AVAILABLE_VALUE"] == 'true' && ($ar["CATALOG_QUANTITY"] <= 0 || $ar["CATALOG_PRICE_2"] == 0)) || ($ar["PROPERTY__AVAILABLE_VALUE"] == 'false' && $ar["CATALOG_QUANTITY"] > 0 && $ar["CATALOG_PRICE_2"] > 0)){
					EuroflettCatalog::updatePreorderForElement($ar['ID'], $arIblock['ID']);
					AddMessage2Log("Element ".$arIblock['ID'].":".$ar['ID']." updated!");
				}
			}
		}
		AddMessage2Log("updateMarketStatuses complete");
		return 'EuroflettCatalog::updateMarketStatuses();';
	}

	function updatePreorderForElement($id, $iblock_id){
		$isAvailable = self::isAvailableToBuy($id);
		$isPrepayment = self::isPrepayment($id, $isAvailable);
		CModule::IncludeModule('iblock');

		if(!$iblock_id){
			$ob = CIBlockElement::GetByID($id);
			$arItem = $ob->GetNext();
			if(!$arItem['IBLOCK_ID']){
				AddMessage2Log("Element not found");
				return;
			}
			$iblock_id = $arItem['IBLOCK_ID'];
		}

		$arValues = array();
		$property_enums = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblock_id, "CODE"=>"_AVAILABLE"));
		while($enum_fields = $property_enums->GetNext())
		{
			$arValues[$enum_fields['XML_ID']] = $enum_fields["ID"];
		}
//		CIBlockElement::SetPropertyValuesEx($id, false, array("_AVAILABLE" => $isAvailable ? $arValues['true'] : $arValues['false'], "_PREPAYMENT" => $isPrepayment ? "Требуется предоплата" : ""));
		//AddMessage2Log("Updated Preorder property for ID: ".$id." - ".($isAvailable ? "false" : "true"));
	}
}

use Bitrix\Sale\Basket;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryServicesManager;

//Bitrix\Main\EventManager::getInstance()->addEventHandler(
//    'sale',
//    'OnSaleOrderSaved',
//    'SplitPreorderNewSave'
//);
function SplitPreorderNewSave(Bitrix\Main\Event $event)
{
    global $USER;
    global $DB;

    $order = $event->getParameter("ENTITY");
    $isNew = $event->getParameter("IS_NEW");
    $isExtraOrder = $order->EXTRA_ORDER;
    if ($isNew && !$isExtraOrder)
    {
        $ord = Order::load($order->getField('ID'));
        $bskt = $ord->getBasket();
        $preorder_items = array();
        foreach ($bskt as $basketItem) {
            $isPreorder = isPreorderById($basketItem->getField('PRODUCT_ID'));
            if ($isPreorder) {
                $basketItem->MY_QUANTITY = $basketItem->getQuantity(); // костыль с количеством. При удалении оно обнуляется
                $preorder_items[$basketItem->getField('PRODUCT_ID')] = $basketItem;
                // Remove from current basket
                $basketItem->delete();
            }
        }

        // New order
        $siteId = Context::getCurrent()->getSite();
        DiscountCouponsManager::init();
        $order = Order::create($siteId, $USER->getId());
        $basket = Basket::create($siteId);
        $settableFields = array_fill_keys(BasketItem::getSettableFields(), true);

        foreach ($preorder_items as $product_id => $item) {
            $elem = $basket->createItem($item->getField('MODULE'), $item->getField('PRODUCT_ID'));
            $values = array_intersect_key($item->getFields()->getValues(), $settableFields);
            $elem->setFields($values);
            $elem->setField('QUANTITY', $item->MY_QUANTITY);
        }
        $order->setBasket($basket);
        $order->EXTRA_ORDER = TRUE;

        /*Shipment*/
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $order->getCurrency());
        foreach ($order->getBasket() as $item)
        {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }
        $arDeliveryServiceAll = DeliveryServicesManager::getRestrictedObjectsList($shipment);
        $shipmentCollection = $shipment->getCollection();
        if (!empty($arDeliveryServiceAll)) {
            reset($arDeliveryServiceAll);
            $deliveryObj = current($arDeliveryServiceAll);
            if ($deliveryObj->isProfile()) {
                $name = $deliveryObj->getNameWithParent();
            } else {
                $name = $deliveryObj->getName();
            }
            $shipment->setFields(array(
                'DELIVERY_ID' => $deliveryObj->getId(),
                'DELIVERY_NAME' => $name,
                'CURRENCY' => $order->getCurrency()
            ));
            $shipmentCollection->calculateDelivery();
        }
        /**/
        /*Payment*/
        $arPaySystemServiceAll = array();
        $paySystemId = 1;
        $paymentCollection = $order->getPaymentCollection();
        $remainingSum = $order->getPrice() - $paymentCollection->getSum();
        if ($remainingSum > 0 || $order->getPrice() == 0)
        {
            $extPayment = $paymentCollection->createItem();
            $extPayment->setField('SUM', $remainingSum);
            $arPaySystemServices = PaySystemManager::getListWithRestrictions($extPayment);
            $arPaySystemServiceAll += $arPaySystemServices;
            if (array_key_exists($paySystemId, $arPaySystemServiceAll)) {
                $arPaySystem = $arPaySystemServiceAll[$paySystemId];
            } else {
                reset($arPaySystemServiceAll);
                $arPaySystem = current($arPaySystemServiceAll);
            }
            if (!empty($arPaySystem)) {
                $extPayment->setFields(array(
                    'PAY_SYSTEM_ID' => $arPaySystem["ID"],
                    'PAY_SYSTEM_NAME' => $arPaySystem["NAME"]
                ));
            } else {
                $extPayment->delete();
            }
        }
        /**/
        $order->doFinalAction(true);
        $order->refreshData();
        $order->save();


        $ord->refreshData();
        $ord->save();

        // mail event
        $strOrderList = '';
        foreach ($basket as $arItem) {
            $measure_name = $arItem->getField('MEASURE_NAME');
            $measureText = (!empty($measure_name) && strlen($measure_name)) ? $measure_name : GetMessage("SOA_SHT");

            $strOrderList .= $arItem->getField("NAME")." - ".$arItem->getField("QUANTITY")." ".$measureText.": ".SaleFormatCurrency($arItem->getField("PRICE"), $arItem->getField("CURRENCY"));
            $strOrderList .= "\n";
        }
        $arFields = array(
            "ORDER_ID" => $order->getField('ACCOUNT_NUMBER'),
            "ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $siteId))),
            "ORDER_USER" => $USER->GetFormattedName(false) . '.',
            "PRICE" => SaleFormatCurrency($order->getPrice(), $order->getCurrency()),
            "BCC" => COption::GetOptionString("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
            "EMAIL" => $USER->GetEmail(),
            "ORDER_LIST" => $strOrderList,
            "SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
            "DELIVERY_PRICE" => $order->getDeliveryPrice(),
        );

        $eventName = "SALE_NEW_PREORDER";
        // send emails (old style)

        $bSend = true;
        foreach(GetModuleEvents("sale", "OnPreOrderNewSendEmail", true) as $arEvent) {
            if (ExecuteModuleEventEx($arEvent, array($arResult["ORDER_ID"], &$eventName, &$arFields)) === false) {
                $bSend = false;
            }
        }
        if($bSend){
            $event = new CEvent;
            $event->Send($eventName, $siteId, $arFields, "N");
        }
    }
}


//AddEventHandler("sale", "OnSaleComponentOrderOneStepProcess", "SplitPreorder");
function SplitPreorder (&$arResult, &$arUserResult, &$arParams){

	global $USER;
	global $DB;
	global $APPLICATION;
	if ($arUserResult["CONFIRM_ORDER"] == "Y" && empty($arResult["ERROR"]) && $USER->IsAuthorized() && empty($arResult["ERROR"])) {
		//ORDER_PROP  [USER_PROPS_Y]   [USER_PROPS_N]
		$allSum = $arResult['ORDER_PRICE'];
		$normalSum = $preorderSum = 0;
		$hasPreorder = false;
		$allItems = $arResult['BASKET_ITEMS'];
		$normalItems = $arResult['BASKET_ITEMS'];
		$arNormalItems = $arPreorderItems = array();
		foreach ($arResult['BASKET_ITEMS'] as $key => $arItem) {
			$isPreorder = isPreorderById($arItem['PRODUCT_ID']);
			if (!$isPreorder) {
				$arNormalItems[] = $arItem;
				$normalSum += $arItem['PRICE']*$arItem['QUANTITY'];
				unset($arResult['BASKET_ITEMS'][$key]);
			}else{
				$arPreorderItems[] = $arItem;
				$hasPreorder = true;
				$preorderSum += $arItem['PRICE']*$arItem['QUANTITY'];
				unset($normalItems);
			}
		}
		//var_dump($hasPreorder);
		//die('test');

		if ($hasPreorder) {
			$arResult['ORDER_PRICE'] = $preorderSum;
			//dump($normalSum);
			//dump($preorderSum);
			//делаем заказ

			$arOrderDat = $arResult;

			$arFields = array(
					"LID" => SITE_ID,
					"PERSON_TYPE_ID" => $arUserResult["PERSON_TYPE_ID"],
					"PAYED" => "N",
					"CANCELED" => "N",
					"STATUS_ID" => "N",
					"PRICE" => $arResult['ORDER_PRICE'] + $arResult["DELIVERY_PRICE"] + $arResult["TAX_PRICE"] - $arResult["DISCOUNT_PRICE"],
					"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
					"USER_ID" => (int)$USER->GetID(),
					"PAY_SYSTEM_ID" => $arUserResult["PAY_SYSTEM_ID"],
					"PRICE_DELIVERY" => $arResult["DELIVERY_PRICE"],
					"DELIVERY_ID" => (strlen($arUserResult["DELIVERY_ID"]) > 0 ? $arUserResult["DELIVERY_ID"] : false),
					"DISCOUNT_VALUE" => $arResult["DISCOUNT_PRICE"],
					"TAX_VALUE" => $arResult["bUsingVat"] == "Y" ? $arResult["VAT_SUM"] : $arResult["TAX_PRICE"],
					"USER_DESCRIPTION" => $arUserResult["ORDER_DESCRIPTION"]
			);

			$arOrderDat['USER_ID'] = $arFields['USER_ID'];

			if (IntVal($_POST["BUYER_STORE"]) > 0 && $arUserResult["DELIVERY_ID"] == $arUserResult["DELIVERY_STORE"])
				$arFields["STORE_ID"] = IntVal($_POST["BUYER_STORE"]);

			// add Guest ID
			if (CModule::IncludeModule("statistic"))
				$arFields["STAT_GID"] = CStatistic::GetEventParam();

			$affiliateID = CSaleAffiliate::GetAffiliate();
			if ($affiliateID > 0){
				$dbAffiliat = CSaleAffiliate::GetList(array(), array("SITE_ID" => SITE_ID, "ID" => $affiliateID));
				$arAffiliates = $dbAffiliat->Fetch();
				if (count($arAffiliates) > 1)
					$arFields["AFFILIATE_ID"] = $affiliateID;
			}
			else
				$arFields["AFFILIATE_ID"] = false;

			CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

			foreach ($arPreorderItems as $key => $arBasketItem) {
				Add2BasketByProductID($arBasketItem['PRODUCT_ID'], $arBasketItem['QUANTITY']);
			}
			//die('ew');
			$arResult["ORDER_ID"] = (int)CSaleOrder::DoSaveOrder($arOrderDat, $arFields, 0, $arResult["ERROR"]);


			$arOrder = array();
			if ($arResult["ORDER_ID"] > 0 && empty($arResult["ERROR"]))
			{
				$arOrder = CSaleOrder::GetByID($arResult["ORDER_ID"]);
				CSaleBasket::OrderBasket($arResult["ORDER_ID"], CSaleBasket::GetBasketUserID(), SITE_ID, false);
				CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

				$arResult["ACCOUNT_NUMBER"] = ($arResult["ORDER_ID"] <= 0) ? $arResult["ORDER_ID"] : $arOrder["ACCOUNT_NUMBER"];
			}

			$strOrderList = "";
			$arBasketList = array();
			$dbBasketItems = CSaleBasket::GetList(
					array("ID" => "ASC"),
					array("ORDER_ID" => $arResult["ORDER_ID"]),
					false,
					false,
					array("ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE", "CURRENCY", "TYPE", "SET_PARENT_ID")
				);
			while ($arItem = $dbBasketItems->Fetch())
			{
				if (CSaleBasketHelper::isSetItem($arItem))
					continue;

				$arBasketList[] = $arItem;
			}

			$arBasketList = getMeasures($arBasketList);

			if (!empty($arBasketList) && is_array($arBasketList))
			{
				foreach ($arBasketList as $arItem)
				{
					$measureText = (isset($arItem["MEASURE_TEXT"]) && strlen($arItem["MEASURE_TEXT"])) ? $arItem["MEASURE_TEXT"] : GetMessage("SOA_SHT");

					$strOrderList .= $arItem["NAME"]." - ".$arItem["QUANTITY"]." ".$measureText.": ".SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"]);
					$strOrderList .= "\n";
				}
			}

			$arFields = array(
				"ORDER_ID" => $arOrder["ACCOUNT_NUMBER"],
				"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID))),
				"ORDER_USER" => ( (strlen($arUserResult["PAYER_NAME"]) > 0) ? $arUserResult["PAYER_NAME"] : $USER->GetFormattedName(false)) . ',',
				"PRICE" => SaleFormatCurrency($orderTotalSum, $arResult["BASE_LANG_CURRENCY"]),
				"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
				"EMAIL" => (strlen($arUserResult["USER_EMAIL"])>0 ? $arUserResult["USER_EMAIL"] : $USER->GetEmail()),
				"ORDER_LIST" => $strOrderList,
				"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
				"DELIVERY_PRICE" => $arResult["DELIVERY_PRICE"],
			);

			$eventName = "SALE_NEW_PREORDER";

			$bSend = true;
			foreach(GetModuleEvents("sale", "OnPreOrderNewSendEmail", true) as $arEvent)
				if (ExecuteModuleEventEx($arEvent, array($arResult["ORDER_ID"], &$eventName, &$arFields))===false)
					$bSend = false;

			if($bSend){
				$event = new CEvent;
				$event->Send($eventName, SITE_ID, $arFields, "N");
			}

			//после заказа
			if (count($arNormalItems)>0) {
				$arResult['ORDER_PRICE'] = $normalSum;
				$arResult['BASKET_ITEMS'] = $arNormalItems;
				foreach ($arNormalItems as $key => $arBasketItem) {				
					Add2BasketByProductID($arBasketItem['PRODUCT_ID'], $arBasketItem['QUANTITY']);
				}
			}else{
				CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
				$arResult["REDIRECT_URL"] = $APPLICATION->GetCurPageParam("ORDER_ID=".urlencode(urlencode($arResult["ORDER_ID"])), Array("ORDER_ID"));
				$APPLICATION->RestartBuffer();
				echo json_encode(array("success" => "Y", "redirect" => $arResult["REDIRECT_URL"]));
				die();
			}

			
			
		}else{
			$arResult['BASKET_ITEMS'] = $allItems;
		}
	}	
}


Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'setPayLink'
);
function setPayLink(Bitrix\Main\Event $event)
{
    /** @var Order $order */
    $order = $event->getParameter("ENTITY");
    $ID = $order->getField("ID");
    $isNew = $event->getParameter("IS_NEW");

    if ($isNew) {
        CModule::IncludeModule('sale');
        if ($order->getField('PAY_SYSTEM_ID') == 5 || $order->getField('PAY_SYSTEM_ID') == 11) {
            $link = 'https://www.euroflett.ru/cart/order/pay/?c='.md5($ID.$order->getPrice());
            $prop_link = NULL;
            $propertyCollection = $order->getPropertyCollection();

            foreach ($propertyCollection as $property) {
                if($property->getField('CODE') == "PAY_LINK") {
                    $prop_link = $property;
                }
            }
            if (!empty($prop_link)) {
                $prop_link->setValue($link);
                $order->save();
            }
        }
    }
}
//AddEventHandler("sale", "OnSaleComponentOrderOneStepComplete", "GenerateUrl");
function GenerateUrl ($ID, $arOrder, $arParams) {
	CModule::IncludeModule('sale');
	if ($arOrder['PAY_SYSTEM_ID'] == 4) {
		$link = 'https://www.euroflett.ru/cart/order/pay/?c='.md5($ID.$arOrder['PRICE']);
		$db_order = CSaleOrder::GetList(
			array("DATE_UPDATE" => "DESC"),
			array("ID" => $ID),
			false,
			false,
			array('ID', 'PERSON_TYPE_ID')
		);
		if ($arOrderProp = $db_order->Fetch()){
			if ($arOrderProp["PERSON_TYPE_ID"]==1) {
				$code = 'PAY_LINK';
			} else {
				$code = 'F_PAY_LINK';
			}
			$db_props = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
					"PERSON_TYPE_ID"	=> $arOrderProp["PERSON_TYPE_ID"], // тип плательщика
					"CODE"				=> $code
				)
			);

			if ($arProps = $db_props->Fetch()) {
				$db_vals = CSaleOrderPropsValue::GetList(
					array("SORT" => "ASC"),
					array(
							"ORDER_ID" => $ID,
							"ORDER_PROPS_ID" => $arProps["ID"]
						)
				);
				if ($arVals = $db_vals->Fetch()){
						CSaleOrderPropsValue::Update($arVals['ID'], array("ORDER_ID"=>$arVals['ORDER_ID'], "VALUE"=>$link));
				}else{
					$arFields = array(
						"ORDER_ID" => $ID,
						"ORDER_PROPS_ID" => $arProps["ID"],
						"NAME" => $arProps["NAME"],
						"CODE" => $arProps["CODE"],
						"VALUE" => $link,
					);
					CSaleOrderPropsValue::Add($arFields);
				}
			}
		}
	}	
}


//Кастомные функции для вкладки СЕО
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/lib/template/functions/fabric.php');

use Bitrix\Main;

$eventManager = Main\EventManager::getInstance();
$eventManager->addEventHandler("iblock", "OnTemplateGetFunctionClass", "myOnTemplateGetFunctionClass");

function myOnTemplateGetFunctionClass(Bitrix\Main\Event $event) {
	$arParam = $event->getParameters();
	$functionClass = $arParam[0];
	if (is_string($functionClass) && class_exists($functionClass) && (
		$functionClass=='enumval' 
		|| $functionClass=='iffilled' 
		|| $functionClass=='capitalize' 
		|| $functionClass=='rusbrand'
	)){
		$result = new Bitrix\Main\EventResult(1,$functionClass);
		return $result;
	}
}

class enumval extends Bitrix\Iblock\Template\Functions\FunctionBase
{
	public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, $parameters)
	{
		$arguments = array();
		/** @var \Bitrix\Iblock\Template\NodeBase $parameter */
		foreach ($parameters as $parameter){
			$arguments[] = $parameter->process($entity);
		}
		return $arguments;
	}

	public function calculate(array $parameters)
	{
		if(isset($parameters[0]) && $parameters[0]) {
			$rs = CUserFieldEnum::GetList(array(), array("ID" => $parameters[0]));
			if($ar = $rs->GetNext()){
				return $ar['VALUE'];
			}
		}
		return "";
	}
}

class iffilled extends Bitrix\Iblock\Template\Functions\FunctionBase
{
	public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, $parameters)
	{
		$arguments = array();
		/** @var \Bitrix\Iblock\Template\NodeBase $parameter */
		foreach ($parameters as $parameter){
			$arguments[] = $parameter->process($entity);
		}
		return $arguments;
	}

	public function calculate(array $parameters)
	{
		if(isset($parameters[0]) && $parameters[0] && isset($parameters[1]) && isset($parameters[2])) {
			if(substr($parameters[2],0,1)=="!") {
				$parameters[2] = substr($parameters[2],1,mb_strlen($parameters[2]));
				if($parameters[2]==$parameters[0]) return "";
			}else{
				if($parameters[2]!=$parameters[0]) return "";
			}		 
			return sprintf($parameters[1],$parameters[0]);
		}elseif(isset($parameters[0]) && $parameters[0] && isset($parameters[1])) {
			return sprintf($parameters[1],$parameters[0]);
		}
		return "";
   }
}

class capitalize extends Bitrix\Iblock\Template\Functions\FunctionBase
{
	public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, $parameters)
	{
		$arguments = array();
		/** @var \Bitrix\Iblock\Template\NodeBase $parameter */
		foreach ($parameters as $parameter){
			$arguments[] = $parameter->process($entity);
		}
		return $arguments;
	}

	public function calculate(array $parameters)
	{
		if(isset($parameters[0]) && $parameters[0]) {	    	
			return mb_strtoupper(mb_substr($parameters[0], 0, 1)).mb_strtolower(mb_substr($parameters[0], 1));
		}
		return "";
	}
}

class rusbrand extends Bitrix\Iblock\Template\Functions\FunctionBase
{
	public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, $parameters)
	{
		$arguments = array();
		/** @var \Bitrix\Iblock\Template\NodeBase $parameter */
		foreach ($parameters as $parameter){
			$arguments[] = $parameter->process($entity);
		}
		return $arguments;
	}

	public function calculate(array $parameters)
	{
		if(isset($parameters[0]) && $parameters[0]) {
			CModule::IncludeModule('iblock');
			$dbRes = CIBlockElement::GetList(Array(), $arFilter = Array("NAME" => $parameters[0], "IBLOCK_ID" => BRANDS_IBLOCK_ID), false, false, array('PROPERTY_NAME_RU'));
			if($arRes = $dbRes->GetNext()){
				if ($arRes['PROPERTY_NAME_RU_VALUE']) {
					return $arRes['PROPERTY_NAME_RU_VALUE'];
				}else{
					return $parameters[0];
				}				
			}
		}
		return $parameters[0];
	}
}

AddEventHandler("catalog", "OnDiscountAdd", array("CKokoc", "CheckDiscount"));
AddEventHandler("catalog", "OnDiscountUpdate", array("CKokoc", "CheckDiscount"));
AddEventHandler("catalog", "OnBeforeDiscountDelete", array("CKokoc", "CheckDiscount"));
class CKokoc
{
	public static function CheckDiscount($ID, $arFields = false)
	{
		if (!$arFields){
			$arFields = CCatalogDiscount::GetByID($ID);
		}
		if (empty($arFields['CONDITIONS'])) return;

		if ($arFields["ACTIVE_FROM"]){
			CAgent::AddAgent(
				'CKokoc::CheckDiscountAgent('.$ID.');',
				'catalog',
				'N',
				86400,
				'',
				'Y',
				$arFields["ACTIVE_FROM"]
			);
		}
		if ($arFields["ACTIVE_TO"]){
			CAgent::AddAgent(
				'CKokoc::CheckDiscountAgent('.$ID.');',
				'catalog',
				'N',
				86400,
				'',
				'Y',
				$arFields["ACTIVE_TO"]
			);
		}

		self::CheckDiscountProducts($arFields['CONDITIONS']);
	}

	static function CheckDiscountProducts($conditions)
	{
		if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('highloadblock')) return;
		$conditions = unserialize($conditions);
		$arFilter = IblockComponentBase::parseCondition($conditions);
		if ($arFilter){
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array('ID'));
			while($el = $res->Fetch())
			{
				$arElementIDs[$el['ID']] = $el['ID'];
			}
			if (empty($arElementIDs)) return;

			$arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
			$obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
			$strEntityDataClass = $obEntity->getDataClass();

			$query = $strEntityDataClass::getList(array('filter' => array('UF_ELEMENT_ID' => $arElementIDs)));
			$DateTime = new Bitrix\Main\Type\DateTime();
			foreach ($query->fetchAll() as $el){
				$strEntityDataClass::update($el['ID'], array('UF_UPDATE_TIME' => $DateTime));
				unset($arElementIDs[$el['UF_ELEMENT_ID']]);
			}
			if (!empty($arElementIDs)){
				foreach ($arElementIDs as $id)
					$strEntityDataClass::add(array('UF_ELEMENT_ID' => $id, 'UF_UPDATE_TIME' => $DateTime));
			}
		}
	}
	

	public static function CheckDiscountAgent($ID)
	{
		$arFields = CCatalogDiscount::GetByID($ID);
		if (!empty($arFields['CONDITIONS'])) {
			self::CheckDiscountProducts($arFields['CONDITIONS']);
		}

		return false;
	}

	public static function GetElementUpdateTime($IBLOCK_ID, $arFields)
	{
		$update = '';
		if (!empty($arFields['ELEMENT_ID'])) {
			$ELEMENT_ID = (int)$arFields['ELEMENT_ID'];
		} elseif (!empty($arFields['ELEMENT_CODE'])){
			$query = \Bitrix\Iblock\ElementTable::getList(array(
				'filter' => array('IBLOCK_ID' => $IBLOCK_ID, 'CODE' => $arFields['ELEMENT_CODE']),
				'select' => array('ID'), 'limit' => '1'
			));
			if (($el = $query->fetch())){
				$ELEMENT_ID = (int)$el['ID'];
			}
		}
		if (isset($ELEMENT_ID) && CModule::IncludeModule('highloadblock')) {
			$arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
			if ($arHLBlock) {
				$obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
				$strEntityDataClass = $obEntity->getDataClass();

				$query = $strEntityDataClass::getList(array(
					'filter' => array('=UF_ELEMENT_ID' => $ELEMENT_ID),
					'limit' => '1'
				));
				if (($el = $query->fetch())){
					$update = $el["UF_UPDATE_TIME"]->toString();
				}
			}
		}
		return $update;
	}
}

if (file_exists(__DIR__ ."/dope.php")) require_once(__DIR__ ."/dope.php");
?>