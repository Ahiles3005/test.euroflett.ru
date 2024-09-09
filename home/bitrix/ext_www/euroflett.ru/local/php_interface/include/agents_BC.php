<?
// php /home/bitrix/ext_www/euroflett.ru/local/php_interface/cron_events_BC.php
function countDaysBetweenDatesBC($d1, $d2)
{
    $d1_ts = strtotime($d1); 
    $d2_ts = strtotime($d2);

    $seconds = abs($d1_ts - $d2_ts);
    
    return (int)($seconds / 86400)  ;
}


function getElementsBC($elements)
{
   while ($element = $elements->GetNext(true, false)) {

       yield $element;
   }
}

function AgentCheckFeedBC()
{
    if (CModule::IncludeModule('iblock') && CModule::IncludeModule('catalog')) {
		
		
		$d2 = date('d.m.Y');//  
		
		
        //Получаем список брендов, участвующих в фиде
        $dbObj = CIBlockElement::GetList([], array(
				"LOGIC" => "OR",
				array('IBLOCK_ID' => BRANDS_IBLOCK_ID, 'PROPERTY_IN_FEED_BC_VALUE' => "Y"),
				array('IBLOCK_ID' => BRANDS_IBLOCK_ID, 'PROPERTY_IN_FEED_BC_PROCENT_VALUE' => "Y", "!=PROPERTY_BC_FEED_PROCENT" => '')
				
			), false, false, ['NAME', 'ID', 'PROPERTY_BC_FEED_PROCENT', 'IBLOCK_ID']);
        $arBrands = [];
        $arBrandsId = [];
        $arBrandsArr = [];
		
       foreach (getElementsBC($dbObj) as $arObj ) {

            $arBrands[] = $arObj['NAME'];
            $arBrandsId[] = $arObj['ID'];
			$arObj['PROPERTY_BC_FEED_PROCENT_VALUE'] = (int)$arObj['PROPERTY_BC_FEED_PROCENT_VALUE'];
			/* Не у всех товаров ID в значении, поэтому проверяем на имя и на ID  */
			$arBrandsArr[$arObj['NAME']] = $arObj;
			$arBrandsArr[$arObj['ID']] = $arObj;
			
        }
		
		
		
		if(empty($arBrandsId) || empty($arBrands)){
			
			return "AgentCheckFeedBC();";
		}
		$ARRAY_FILTER =	array(
				"LOGIC" => "OR",
				array('PROPERTY__BRAND' => $arBrandsId, 'CATALOG_AVAILABLE' => 'N'),
				array('?NAME' => $arBrands, 'CATALOG_AVAILABLE' => 'N'),
			);
		print_r($ARRAY_FILTER ); 
        //получаем список элементов по названиям брендов
        $dbObj = CIBlockElement::GetList([], ['IBLOCK_TYPE' => 'catalog', $ARRAY_FILTER ], false, false, ['ID', 'NAME', 'CATALOG_GROUP_2', 'DETAIL_PAGE_URL','PROPERTY_YM_MODEL',  'IBLOCK_ID','PROPERTY_MODEL', 'CATALOG_AVAILABLE', 'PROPERTY__BRAND' ]);
		$ar_id_block = array();
        foreach (getElementsBC($dbObj) as $arObj ) {
			
			$arObj['PROPERTY_YM_MODEL_VALUE'] = trim($arObj['PROPERTY_YM_MODEL_VALUE']);
			$arObj['PROPERTY_MODEL_VALUE'] = trim($arObj['PROPERTY_MODEL_VALUE']);
			
			if(!empty($arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]) &&  
				$arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['PROPERTY_BC_FEED_PROCENT_VALUE']){
				
				$arObj['PROPERTY_BC_FEED_PROCENT'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['PROPERTY_BC_FEED_PROCENT_VALUE'];
				$arObj['BRAND'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['NAME'];
				$arObj['BRAND'] = preg_replace( "/[^a-zA-Z0-9]/", '', $arObj['BRAND']) ;
			}elseif(!empty($arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']])){
				
				$arObj['BRAND'] = $arBrandsArr[$arObj['PROPERTY__BRAND_VALUE']]['NAME'];
				$arObj['BRAND'] = preg_replace( "/[^a-zA-Z0-9]/", '', $arObj['BRAND']) ;
				
			}
			
			
            $arProds[] = $arObj;
			$ar_id_block[$arObj['IBLOCK_ID']] = $arObj['IBLOCK_ID'];
			
        }
		// print_r($arProds);
		
		$data_array = array();
		
		
		
        // $url = "https://b2b.direct-delivery.ru/bitrix/catalog_export/export_TWQ.xml";
		
		// $connection = curl_init();
		// curl_setopt($connection, CURLOPT_URL, "https://newb2bapi.direct-delivery.ru/api/feed"); //https://b2b.direct-delivery.ru/api/fid
		// curl_setopt($connection, CURLOPT_HTTPHEADER, array(
			// 'Content-length: 0',
			// "Content-Type: application/x-www-form-urlencoded",
			// "Authorization-Login: +7 (985) 643-50-80",//euroflett.ru",
			// "Authorization-Password: hZlWtlIQ"//EF1500966"
			// ));
			
		// curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); 
		// $object = curl_exec($connection);
		// curl_close($connection); 
		// $xml = new SimpleXMLElement($object, null );
		
		$url = "https://vstroyka-solo.ru/xml/partner_common.xml?nn62GsfMqf";
        
		
		
		
		file_put_contents($_SERVER["DOCUMENT_ROOT"].'/upload/partner_common.xml', file_get_contents($url) ); 	
		$xml = new SimpleXMLElement($_SERVER["DOCUMENT_ROOT"].'/upload/partner_common.xml', null, true);
		// $xml = new SimpleXMLElement($_SERVER["DOCUMENT_ROOT"].'/upload/test.xml', null, true);
		
		
      
        //Бежим по элементам XML
        foreach ($xml->shop[0]->offers->offer as $offer) {
			
			// print_r($offer->{"delivery-options"}->option['date']->__toString());
			   
			usleep(2000);
			$data_delivery = '';
			$count_days = '33';
			if ( isset($offer->{"delivery-options"}->option['date']) ) {
				
				$data_delivery = $offer->{"delivery-options"}->option['date']->__toString();
				$data_delivery_sekond = strtotime($data_delivery);
				$data_delivery_sekond += 86400;
				$data_delivery = date('d-m-Y', $data_delivery_sekond );
				
				$count_days = countDaysBetweenDatesBC($data_delivery, $d2);
				// $count_days += 1;
				
			}
            //Бежим по элементам каталога
			
			$log_item_EK_60_2	= array();
			
			$offer_vendor =  preg_replace( "/[^a-zA-Z0-9]/", '', $offer->vendor->__toString()) ;
			$offer_vendor__mb_strtoupper =  mb_strtoupper( $offer_vendor) ;
			
			
            foreach ($arProds as $key => $product) {
				$product_BRAND__mb_strtoupper = mb_strtoupper($product['BRAND']);
				
				if(($product['BRAND'] == $offer_vendor) || ($offer_vendor__mb_strtoupper == $product_BRAND__mb_strtoupper)){
					
					if ($product['ID']== 34638 && $offer['id']->__toString() == '00035640') {
						
						$log_item_EK_60_2['$product["PROPERTY_MODEL_VALUE"]'] = $product['PROPERTY_MODEL_VALUE'];
						$log_item_EK_60_2['$product["PROPERTY_YM_MODEL_VALUE"]'] = $product['PROPERTY_YM_MODEL_VALUE'];
						$log_item_EK_60_2['$offer->model->__toString()'] = $offer->model->__toString();
						$log_item_EK_60_2['$product["PROPERTY_YM_MODEL_VALUE"] == $offer->model->__toString()'] = $product['PROPERTY_YM_MODEL_VALUE'] == $offer->model->__toString() ;
						$log_item_EK_60_2['$product["PROPERTY_MODEL_VALUE"] == $offer->model->__toString()']= $product['PROPERTY_MODEL_VALUE'] == $offer->model->__toString() ;
						
						$log_item_EK_60_2['preg_replace( "/[^a-zA-Z0-9]/", "", $offer->name->__toString()) == preg_replace( "/[^a-zA-Z0-9]/", "",$product["PROPERTY_YM_MODEL_VALUE"])'] = preg_replace( "/[^a-zA-Z0-9]/", '', $offer->name->__toString()) == preg_replace( "/[^a-zA-Z0-9]/", '',$product['PROPERTY_YM_MODEL_VALUE']) ;
						
						
						// file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log_item_EK_60_2.txt', print_r($log_item_EK_60_2, 1));
					}
					 
					if(
					
						$product['PROPERTY_YM_MODEL_VALUE'] == $offer->model->__toString() || 
						
						$product['PROPERTY_MODEL_VALUE'] == $offer->model->__toString() || 
						
						preg_replace( "/[^a-zA-Z0-9]/", '', $offer->name->__toString()) == preg_replace( "/[^a-zA-Z0-9]/", '',$product['PROPERTY_YM_MODEL_VALUE']) 
					
					){ 
						$price = 0;
						if($product['PROPERTY_BC_FEED_PROCENT']){
							
							$price = (int)$offer->purchase_price->__toString();
							$price +=  ($price * $product['PROPERTY_BC_FEED_PROCENT']) / 100 ;
						}else{
							
							$price = $offer->price->__toString();
						}
						
						$arResult[] = [
							"ID" => $product['ID'],
							'OFFER_PRICE' => $price,
							'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
							'OFFER_AVAILABLE' => $offer['available']->__toString(),
							'OFFER_ID' => $offer['id']->__toString(),
							'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
							'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL'],
							'DATA_DOSTAVKI_BX' => $data_delivery,
							'DATA_DOSTAVKI_DAY_BX' => $count_days
						];
						$arProds[$key]['IS_UPDATE'] = true;
						
						// Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
						continue 2;

					
					}
				}
			}
				
				
			foreach ($arProds as $key => $product) {	
			
				if($product['BRAND'] == $offer_vendor){
				
					if (stripos(preg_replace( "/[^a-zA-Z0-9]/", '', $product['NAME']), preg_replace( "/[^a-zA-Z0-9]/", '',$offer->model->__toString()))) {
						
						$price = 0;
						if($product['PROPERTY_BC_FEED_PROCENT']){
							
							$price = (int)$offer->purchase_price->__toString();
							$price +=  ($price * $product['PROPERTY_BC_FEED_PROCENT']) / 100 ;
						}else{
							
							$price = $offer->price->__toString();
						}
						
						$arResult[] = [
							"ID" => $product['ID'],
							'OFFER_PRICE' => $price,
							'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
							'OFFER_AVAILABLE' => $offer['available']->__toString(),
							'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
							'OFFER_ID' => $offer['id']->__toString(),
							'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL'],
							'DATA_DOSTAVKI_BX' => $data_delivery,
							'DATA_DOSTAVKI_DAY_BX' => $count_days
						];
						$arProds[$key]['IS_UPDATE'] = true;
						// Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
						continue 2;
					}elseif (stripos(preg_replace( "/[^a-zA-Z0-9]/", '', $offer->name->__toString()), preg_replace( "/[^a-zA-Z0-9]/", '',$product['PROPERTY_YM_MODEL_VALUE']))) {
						
						$price = 0;
						if($product['PROPERTY_BC_FEED_PROCENT']){
							
							$price = (int)$offer->purchase_price->__toString();
							$price +=  ($price * $product['PROPERTY_BC_FEED_PROCENT']) / 100 ;
						}else{
							
							$price = $offer->price->__toString();
						}
						
						$arResult[] = [
							"ID" => $product['ID'],
							'OFFER_PRICE' => $price,
							'PRODUCT_PRICE' => intval($product['CATALOG_PRICE_2']),
							'OFFER_AVAILABLE' => $offer['available']->__toString(),
							'PRODUCT_AVAILABLE' => $product['CATALOG_QUANTITY'] > 0 ? 'true' : 'false',
							'OFFER_ID' => $offer['id']->__toString(),
							'URL' => "https://www.euroflett.ru" . $product['DETAIL_PAGE_URL'],
							'DATA_DOSTAVKI_BX' => $data_delivery,
							'DATA_DOSTAVKI_DAY_BX' => $count_days
						];
						$arProds[$key]['IS_UPDATE'] = true;
						// Если есть такой элемент на сайте, то останавливаем внешнюю итерацию
						continue 2;
					}
				}
            }
            $arResult[] = [
                'NOT_ON_SITE' => 'true',
                'OFFER_NAME' => $offer->name->__toString() ? $offer->name->__toString() :  $offer->typePrefix->__toString() .' '. $offer->model->__toString() ,
                'URL' => $offer->url->__toString(),
				'DATA_DOSTAVKI_BX' => $data_delivery,
				'DATA_DOSTAVKI_DAY_BX' => $count_days,
				'OFFER_ID' => $offer['id']->__toString(),
				
            ];
			
        }
		
		
		// print_r($arResult );
		
		
		// die();
		// file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log_item_arResult.txt', print_r($arResult, 1));
        //собираем отчет
        $arReport = [];
        foreach ($arResult as $item) {
			 
			// usleep(100000);
			
            $reportStr = '';
            if (!empty($item['PAIR'])) {
				
                $reportStr .= $item['A'].":".$item['B'].":".$item['C'].":";
            }
			// $reportStr   = "\n\n".'!empty($item["ID"]) ID = '.$item['ID'].' '.var_export($item['ID'])."\n\n";
			
			$reportStr   .= " " ;
            if (!empty($item['ID'])) { 
				
                $reportStr .= "У элемента с ID <a href=\"{$item['URL']}\">{$item['ID']}</a> ";
                if ($item['OFFER_PRICE'] == $item['PRODUCT_PRICE']) {
					
                    $reportStr .= 'цена не изменилась,';
                } else {
					
                    $reportStr .= 'изменилась цена ';
                    $res = CPrice::GetList([], ['PRODUCT_ID' => $item['ID'], 'CATALOG_GROUP_ID' => 2]);
                    if ($price = $res->Fetch()) {
						
                        if (CPrice::Update($price['ID'], ['PRICE' => $item['OFFER_PRICE']])) {
							
                            $reportStr .= '(цена успешно обновлена), ';
                        } else {
							
                            $reportStr .= '(не удалось обновить цену), ';
                        }
                    } else {
						
                        $reportStr .= '(не удалось получить цену), ';
                    }
                }
				if ( $item['DATA_DOSTAVKI_BX']) { 
				
					$reportStr .= 'изменилась дата доставки ';
					
					CIBlockElement::SetPropertyValuesEx($item['ID'], false, array('DATA_DOSTAVKI_BX' => $item['DATA_DOSTAVKI_BX']));
					CIBlockElement::SetPropertyValuesEx($item['ID'], false, array('DATA_DOSTAVKI_DAY_BX' => $item['DATA_DOSTAVKI_DAY_BX']));
					
				}else{
					CIBlockElement::SetPropertyValuesEx ($item['ID'], false, 
						array(
							'DATA_DOSTAVKI_DAY_BX' => $item['DATA_DOSTAVKI_DAY_BX'] ? $item['DATA_DOSTAVKI_DAY_BX'] : '33'
						)
					);
					$reportStr .= 'дата доставки отсутствует ';
				} 
				
                if ($item['OFFER_AVAILABLE'] == 'true' && $item['PRODUCT_AVAILABLE'] == 'true') {
					
                    $reportStr .= ' в наличии и у поставщика и в магазине.<br>';
                } elseif ($item['OFFER_AVAILABLE'] == 'false' && $item['PRODUCT_AVAILABLE'] == 'true') {
					
                    $reportStr .= ' недоступен в фиде ';
					/*
                    if (CCatalogProduct::Update($item['ID'], ['QUANTITY' => 0])) {
						
                        $reportStr .= '(количество приведено к нулю).<br>';
                    } else {
						
                        $reportStr .= '(не удалось обновить количество товара на сайте).<br>';
                    }*/
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
		 
		foreach ($arProds as $key => $product) {
			
			if (!$product['IS_UPDATE']) {
				
				// CCatalogProduct::Update($product['ID'], ['QUANTITY' => 0]);
				// CIBlockElement::SetPropertyValuesEx ($product['ID'], false, 
					// array(
						// 'DATA_DOSTAVKI_DAY_BX' => '33'
					// )
				// );
				 $arReport[] = 'Товар <a href="'."https://www.euroflett.ru" . $product['DETAIL_PAGE_URL'].'" >'. $product['NAME'].'</a> с id = '.$product['ID'].' отсутствует в выгрузке поставщика ';
			}
		}
		
		
    } else {
		
        $arReport[] = 'Не удалось подключить модуль информационных блоков.';
    }

    sort($arReport, SORT_STRING);
	
	
	$report = '<!doctype html>
	<html>
	  <head>
		<meta charset="utf-8" />
		<title>Отчет о выполенении обновления, по фиду BC от, ' . date("d-m-Y H:i:s") . '</title>
	  </head>
	  <body>
		' . implode("<br>", $arReport) . '
	  </body>
	</html>' ;
	$file_name =  date("d-m-Y--H--i") . '.html' ;
	
	file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/upload/report/' . $file_name , $report ); 
	// file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log_item_arReport.txt', print_r($arReport, 1));
	//Логгируем
    // CEventLog::Add([
        // 'SEVERITY' => 'SECURITY',
        // 'AUDIT_TYPE_ID' => 'CHECK_FEED',
        // "MODULE_ID" => "main",
        // "ITEM_ID" => '',
        // "DESCRIPTION" =>  '<a href="https://www.euroflett.ru/upload/report/' . $file_name . '">Отчет  о выполенении обновления, по фиду BC от, ' . date("d-m-Y H:i:s") . '</a>';
    // ]);
	
	
	
	 
    // отправляем отчен на почту
    $arFields = [
        'REPORT' => '<a href="https://www.euroflett.ru/upload/report/' . $file_name . '" target="_blank">Отчет  о выполенении обновления, по фиду BC от, ' . date("d-m-Y H:i:s") . '</a>',
		'BREND' => "BC",
         'EMAILS' => 'svp@euroflett.ru, 89154202923@mail.ru, smirnov.d@hardkod.com, egorov.anton@hardkod.ru'
    ];
   CEvent::Send("CHECK_FEED", 's1', $arFields);/**/

    return "AgentCheckFeedBC();";
}

 



