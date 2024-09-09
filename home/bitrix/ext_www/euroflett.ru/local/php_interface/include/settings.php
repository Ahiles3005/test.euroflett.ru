<?

	/*
	BX_RESIZE_IMAGE_EXACT - масштабирует в прямоугольник $arSize c сохранением пропорций, обрезая лишнее;
	BX_RESIZE_IMAGE_PROPORTIONAL - масштабирует с сохранением пропорций, размер ограничивается $arSize;
	BX_RESIZE_IMAGE_PROPORTIONAL_ALT - масштабирует с сохранением пропорций, размер ограничивается $arSize, улучшенная обработка вертикальных картинок.
	*/

	$arImageSizes = array(
		'CATALOG_ITEM_LIST' => array(
			'TITLE' => 'Список товаров плиткой',
			'WIDTH' => 320,
			'HEIGHT' => 480,
			'RESIZE' => BX_RESIZE_IMAGE_PROPORTIONAL
		),
		'CATALOG_ITEM_PREVIEW' => array(
			'TITLE' => 'Большая картинка на карточке товара',
			'WIDTH' => 1000,
			'HEIGHT' => 1000,
			'RESIZE' => BX_RESIZE_IMAGE_PROPORTIONAL
		),
		'CATALOG_ITEM_FULL' => array(
			'TITLE' => 'Полный размер товара',
			'WIDTH' => 2000,
			'HEIGHT' => 2000,
			'RESIZE' => BX_RESIZE_IMAGE_PROPORTIONAL
		),
		'CATALOG_ITEM_THUMBNAIL' => array(
			'TITLE' => 'Превьюшки дополнительных фото товара',
			'WIDTH' => 250,
			'HEIGHT' => 250,
			'RESIZE' => BX_RESIZE_IMAGE_EXACT
		),
		'CATALOG_SECTION_LIST' => array(
			'TITLE' => 'Картинка раздела каталога',
			'WIDTH' => 560,
			'HEIGHT' => 400,
			'RESIZE' => BX_RESIZE_IMAGE_EXACT
		),
		'NEWS_LIST' => array(
			'TITLE' => 'Картинка в списке новостей',
			'WIDTH' => 460,
			'HEIGHT' => 200,
			'RESIZE' => BX_RESIZE_IMAGE_EXACT
		),
		'BRANDS_LIST' => array(
			'TITLE' => 'Логотип бренда',
			'WIDTH' => 500,
			'HEIGHT' => 80,
			'RESIZE' => BX_RESIZE_IMAGE_PROPORTIONAL
		)
	);
	$arImageSizes['CART_ITEM'] = $arImageSizes['CATALOG_ITEM_THUMBNAIL'];
	$arImageSizes['CART_ITEM']['TITLE'] = 'Картинка товара в корзине';
	$arImageSize['OFFER'] = $arImageSizes['CATALOG_SECTION_LIST'];
	$arImageSizes['OFFER']['TITLE'] = 'Картинка акции в списке';

	// Стандартные параметры для Catalog.top

	$arCatalogTopParamsDefault = array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "1",
		"ELEMENT_SORT_FIELD" => "shows",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_FIELD2" => "id",
		"ELEMENT_SORT_ORDER2" => "desc",
		"FILTER_NAME" => "",
		"HIDE_NOT_AVAILABLE" => "N",
		"ELEMENT_COUNT" => "8",
		"LINE_ELEMENT_COUNT" => "4",
		"PROPERTY_CODE" => array(
			0 => "BRAND",
			1 => "WATERPROOF",
			2 => "BODY_MATERIAL",
			3 => "BAND_MATERIAL",
			4 => "MECHANISM",
			5 => "",
		),
		"OFFERS_LIMIT" => "5",
		"VIEW_MODE" => "SECTION",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "N",
		"SHOW_CLOSE_POPUP" => "N",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Под заказ",
		"SECTION_URL" => "/catalog/#SECTION_CODE_PATH#/",
		"DETAIL_URL" => "/catalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"CACHE_FILTER" => "N",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRICE_CODE" => array(
			0 => "MSK",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "Y",
		"BASKET_URL" => "/cart/",
		"USE_PRODUCT_QUANTITY" => "N",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(
		),
		"ADD_TO_BASKET_ACTION" => "ADD",
		"DISPLAY_COMPARE" => "N",
		"TEMPLATE_THEME" => "blue",
		"MESS_BTN_COMPARE" => "Сравнить",
		"OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"PRODUCT_DISPLAY_MODE" => "N",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"OFFERS_CART_PROPERTIES" => array(
		),
		"CURRENCY_ID" => "RUB",
		"WP_H2_TITLE" => "Популярные модели",
		"WP_SHOW_ALL_LINK" => "Y",
		"WP_ALL_LINK_TEXT" => "Все модели",
		"WP_ALL_LINK" => "/catalog/",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"WP_ITEMLIST_CATEGORY_LINE" => "BRAND"
	);
?>