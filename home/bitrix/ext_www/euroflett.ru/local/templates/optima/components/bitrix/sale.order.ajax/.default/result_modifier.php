<?
	/*foreach($arResult['BASKET_ITEMS'] as &$item){
		$item['DETAIL_PICTURE_SRC'] = resizeImageGetSrc(
			$item['DETAIL_PICTURE_SRC'],
			'CART_ITEM'
		);
	}
	krumo($arResult);*/	
		foreach($arResult['BASKET_ITEMS'] as $listName => $arList){
				$arSelect = Array("ID", "NAME", "PROPERTY_STATUS_NALICHIJA",'CATALOG_QUANTITY');
				$arFilter = Array("ID"=>$arList["PRODUCT_ID"]);
				$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
				if($ob = $res->GetNext())
				{
					$arResult['BASKET_ITEMS'][$listName]['STATUS_NALICHIJA']=$ob['PROPERTY_STATUS_NALICHIJA_VALUE'];
					$arResult['BASKET_ITEMS'][$listName]['CATALOG_QUANTITY']=$ob['CATALOG_QUANTITY'];
				}	
		}
?>