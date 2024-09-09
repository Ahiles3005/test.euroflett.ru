<?
function AgentCheckFeed()
{   
    if (CModule::IncludeModule('iblock') && CModule::IncludeModule('catalog')) {
        //Получаем список брендов, участвующих в фиде
        $dbObj = CIBlockElement::GetList([], ['IBLOCK_ID' => BRANDS_IBLOCK_ID, 'PROPERTY_IN_FEED_VALUE' => "Y"], false, false, ['NAME', 'ID', 'PROPERTY_LIMARS_FEED_PROCENT', 'IBLOCK_ID']);
        $arBrands = [];
		$arBrandsArr = [];

        while ($arObj = $dbObj->GetNext(true, false)) {
            $arBrands[] = $arObj['NAME'];
		
			$arObj['PROPERTY_LIMARS_FEED_PROCENT_VALUE'] = (int)$arObj['PROPERTY_LIMARS_FEED_PROCENT_VALUE'];
			$arBrandsArr[$arObj['NAME']] = $arObj;
			$arBrandsArr[$arObj['ID']] = $arObj;
        }
        //получаем список элементов по названиям брендов
        $dbObj = CIBlockElement::GetList([], ['IBLOCK_TYPE' => 'catalog', '?NAME' => $arBrands], false, false, ['ID', 'NAME', 'CATALOG_GROUP_2', 'DETAIL_PAGE_URL','PROPERTY_YM_MODEL', 'PROPERTY_MODEL', 'PROPERTY__BRAND']);
        while ($arObj = $dbObj->GetNext(true, false)) {


			if(!empty($arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]) &&  
				$arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['PROPERTY_LIMARS_FEED_PROCENT_VALUE']){
				
				$arObj['PROPERTY_LIMARS_FEED_PROCENT'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['PROPERTY_LIMARS_FEED_PROCENT_VALUE'];
				$arObj['BRAND'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['NAME'];
				$arObj['BRAND'] = preg_replace( "/[^a-zA-Z0-9]/", '', $arObj['BRAND']) ;
			}elseif(!empty($arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']])){
				
				$arObj['BRAND'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['NAME'];
				$arObj['BRAND'] = preg_replace( "/[^a-zA-Z0-9]/", '', $arObj['BRAND']) ;
				
			}

            $arProds[] = $arObj;
			

        }
 
        //получаем XML
        $url = "https://limars.ru/partners.xml";
        $xml = new SimpleXMLElement($url, null, true);
        //Бежим по элементам XML
        foreach ($xml->shop[0]->offers->offer as $offer) {
            //Бежим по элементам каталога
						$price = 0;

			foreach ($arProds as $key => $product) {
				
				  

					
				if($product['PROPERTY_YM_MODEL_VALUE'] == $offer->model->__toString() || $product['PROPERTY_MODEL_VALUE'] == $offer->model->__toString() || preg_replace( "/[^a-zA-Z0-9]/", '', $offer->name->__toString()) == preg_replace( "/[^a-zA-Z0-9]/", '',$product['PROPERTY_YM_MODEL_VALUE']) ){
				
					if($product['PROPERTY_LIMARS_FEED_PROCENT']){
				
						$price = (int)$offer->price->__toString();
						$price +=  ($price * $product['PROPERTY_LIMARS_FEED_PROCENT']) / 100 ;
					}else{
						
						$price = $offer->price->__toString();
					}
				    $arResult[] = [
                        "ID" => $product['ID'],
                        'OFFER_PRICE' => $price ,
                        'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
                        'OFFER_AVAILABLE' => $offer['available']->__toString(),
                        'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
                        'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL'] 
						 
                    ];
					unset( $arProds[$key]);
					
                    // Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
                    continue 2;

				
				}
			}

			
            foreach ($arProds as $product) {


                if (stripos(preg_replace( "/[^a-zA-ZА-Яа-я0-9]/", '', $product['NAME']), preg_replace( "/[^a-zA-ZА-Яа-я0-9]/", '',$offer->model->__toString()))) {

					if($product['PROPERTY_LIMARS_FEED_PROCENT']){
					
						$price = (int)$offer->price->__toString();
						$price +=  ($price * $product['PROPERTY_LIMARS_FEED_PROCENT']) / 100 ;
					}else{
						
						$price = $offer->price->__toString();
					}
                    $arResult[] = [
                        "ID" => $product['ID'],
                        'OFFER_PRICE' => $price,
                        'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
                        'OFFER_AVAILABLE' => $offer['available']->__toString(),
                        'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
                        'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL']
                    ];
                    // Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
                    continue 2;
                }elseif (stripos(preg_replace( "/[^a-zA-ZА-Яа-я0-9]/", '', $offer->name->__toString()), preg_replace( "/[^a-zA-ZА-Яа-я0-9]/", '',$product['PROPERTY_YM_MODEL_VALUE']))) {

					if($product['PROPERTY_LIMARS_FEED_PROCENT']){
					
						$price = (int)$offer->price->__toString();
						$price +=  ($price * $product['PROPERTY_LIMARS_FEED_PROCENT']) / 100 ;
					}else{
						
						$price = $offer->price->__toString();
					}

                    $arResult[] = [
                        "ID" => $product['ID'],
                        'OFFER_PRICE' => $price,
                        'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
                        'OFFER_AVAILABLE' => $offer['available']->__toString(),
                        'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
                        'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL']
                    ];
                    // Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
                    continue 2;
                }
            }
            $arResult[] = [
                'NOT_ON_SITE' => 'true',
                'OFFER_NAME' => $offer->name->__toString(),
                'URL' => $offer->url->__toString()
            ];
        }
// echo '<pre  >';
		// print_r($arResult ); 
		// echo '</pre>';  
		  // die();
        //собираем отчет
        $arReport = [];
        foreach ($arResult as $item) {
            $reportStr = '';
            if (!empty($item['PAIR'])) {
                $reportStr .= $item['A'].":".$item['B'].":".$item['C'].":";
            }
            if (!empty($item['ID'])) {
                $reportStr .= "У элемента с ID <a href=\"{$item['URL']}\">{$item['ID']}</a> ";
                if ($item['OFFER_PRICE'] == $item['PRODUCT_PRICE']) {
                    $reportStr .= 'цена не изменилась, осталась ' . $item['PRODUCT_PRICE'];
                } else {
                    $reportStr .= 'изменилась цена ';
                    $res = CPrice::GetList([], ['PRODUCT_ID' => $item['ID'], 'CATALOG_GROUP_ID' => 2]);
                    if ($price = $res->Fetch()) {
                        if (CPrice::Update($price['ID'], ['PRICE' => $item['OFFER_PRICE']])) {
                            $reportStr .= '(цена успешно обновлена), цена была '. $item['PRODUCT_PRICE'] .' цена стала ' . $item['OFFER_PRICE'].", ";
                        } else {
                            $reportStr .= '(не удалось обновить цену), ';
                        }
                    } else {
                        $reportStr .= '(не удалось получить цену), ';
                    }
                }
                if ($item['OFFER_AVAILABLE'] == 'true' && $item['PRODUCT_AVAILABLE'] == 'true') {
                    $reportStr .= ' в наличии и у поставщика и в магазине.<br>';
                } elseif ($item['OFFER_AVAILABLE'] == 'false' && $item['PRODUCT_AVAILABLE'] == 'true') {
                    $reportStr .= ' есть на сайте, но нет в фиде ';
                    if (CCatalogProduct::Update($item['ID'], ['QUANTITY' => 0])) {
                        $reportStr .= '(количество приведено к нулю).<br>';
                    } else {
                        $reportStr .= '(не удалось обновить количество товара на сайте).<br>';
                    }
                } elseif ($item['OFFER_AVAILABLE'] == 'true' && $item['PRODUCT_AVAILABLE'] == 'false') {
                    $reportStr .= ' в наличии у поставщика, но отсутствует в магазине ';
                    if (CCatalogProduct::Update($item['ID'], ['QUANTITY' => 10])) {
                        $reportStr .= '(количество увеличено).<br>';
                    } else {
                        $reportStr .= '(не удалось обновить количество товара на сайте).<br>';
                    }
                } else {
                    $reportStr .= ' нет в наличии ни у поставщика, ни в магазине.<br>';
                }
            } elseif (!empty($item['NOT_ON_SITE'])) {
                $reportStr .= "Товара <a href=\"{$item['URL']}\">\"{$item['OFFER_NAME']}\"</a> нет на сайте.<br>";
            }
            $arReport[] = $reportStr;
        }
    } else {
        $arReport[] = 'Не удалось подключить модуль информационных блоков.';
    }
 
    sort($arReport, SORT_STRING);
    //Логгируем
    CEventLog::Add([
        'SEVERITY' => 'SECURITY',
        'AUDIT_TYPE_ID' => 'CHECK_FEED',
        "MODULE_ID" => "main",
        "ITEM_ID" => '',
        "DESCRIPTION" => implode("<br>", $arReport),
    ]);
    // отправляем отчен на почту
    $arFields = [
        'REPORT' => implode("<br>", $arReport),
        'EMAILS' => 'svp@euroflett.ru, 89154202923@mail.ru, smirnov.d@hardkod.ru'
    ];
   CEvent::Send("CHECK_FEED", 's1', $arFields);

    return "AgentCheckFeed();";
}
