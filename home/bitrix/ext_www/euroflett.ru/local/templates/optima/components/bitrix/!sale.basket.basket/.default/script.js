$(function() {
	//TODO Обновлять hidden поле с quantity

	//Переключение вкладок корзины
	$(document).on('click', '.e_change_basket_list', function(e){
		e.preventDefault();
		var $this = $(this);
		if(!$this.hasClass('current')) {
			$('.e_change_basket_list').removeClass('current');
			$this.addClass('current');
			$('.basket_items').hide();
			$('.basket_items.'+$this.data('list')).show();
		}
	});

	//Увеличить количество товара
	$(document).on('click', '[data-action=quantity-up]', function(e){
		e.preventDefault();
		var $this = $(this);
		var element = $this.closest('.cart-item');
		var quantity = element.find('[data-action=quantity]');
		var cur_quantity = quantity.val();
		if(cur_quantity>=9999){
			quantity.val(9999).keyup();
		}else {
			quantity.val(+cur_quantity+1).keyup();
		}
	});

	//Уменьшить количество товара
	$(document).on('click', '[data-action=quantity-down]', function(e){
		e.preventDefault();
		var $this = $(this);
		var element = $this.closest('.cart-item');
		var quantity = element.find('[data-action=quantity]');
		var cur_quantity = quantity.val();
		if(cur_quantity<=1){
			quantity.val(1).keyup();
		}else {
			quantity.val((+cur_quantity-1)).keyup();
		}
	});

	//Удалить товар из корзины
	$(document).on('click', '[data-action=delete]', function(e){
		e.preventDefault();
		var $this = $(this);
		var controlsBlock = $this.closest('.buttons');
		controlsBlock.find('[data-selector=delete-input]').val('Y');
		recalcBasketAjax();
	});

	//Отложить товар
	$(document).on('click', '[data-action=delay]', function(e){
		e.preventDefault();
		var $this = $(this);
		var controlsBlock = $this.closest('.buttons');
		controlsBlock.find('[data-selector=delay-input]').val('Y');
		recalcBasketAjax();
	});

	//Вернуть отложенный товар в корзину
	$(document).on('click', '[data-action=return]', function(e){
		e.preventDefault();
		var $this = $(this);
		var controlsBlock = $this.closest('.buttons');
		controlsBlock.find('[data-selector=return-input]').val('Y');
		recalcBasketAjax();
	});


	$(document).on('keyup change', '[data-action=quantity]', function(e){
		if($(this).val().length > 0) {
			recalcBasketAjax();
		}
	});

	//Применить купон
	$(document).on('click', '.e_apply_coupon', function(e){
		e.preventDefault();
		recalcBasketAjax();
	});

	//Пересчитать
	$(document).on('click', '[data-action=cart-recalculate]', function(e){
		e.preventDefault();
		recalcBasketAjax();
	});


	//Вводим только числа
	$("[data-action=quantity]").mask("9999",{placeholder:"",autoclear: false});

});

function updateBasketList(result){
	var items = result['BASKET_DATA']['ITEMS']['AnDelCanBuy'];
	var curElem;
	var allItems = [];
	for (i = 0; i < items.length; i++)
	{
		allItems.push(items[i]['ID']);
		//console.log(items[i]);

		curElem = $('.cart-item').filter(function() {
			return $(this).data('id') && $(this).data('id') == items[i]['ID'];
		});
		if(curElem.length !== 0){
			//console.log('we have you');
		}else{
			curElem = $('.cart-item').first().clone().data('id', items[i]['ID']).attr('data-id', items[i]['ID']).appendTo('.basket_items');
			//console.log('who are you?');
		}
		var imageURL;
		if (items[i]['PREVIEW_PICTURE_SRC'].length > 0)
			imageURL = items[i]['PREVIEW_PICTURE_SRC'];
		else if (items[i]['DETAIL_PICTURE_SRC'].length > 0)
			imageURL = items[i]['DETAIL_PICTURE_SRC'];
		else
			imageURL = basketJSParams['TEMPLATE_FOLDER'] + '/images/no_photo.png';

		var props = '';

		curElem.find('[data-selector=image]').find('img').attr('src', imageURL);
		curElem.find('[data-selector=title] a').attr('href', items[i]['DETAIL_PAGE_URL']).text(items[i]['NAME']);
		for (j = 0; j < items[i]['PROPS'].length; j++)
		{
			props += ''+items[i]['PROPS'][j]['NAME']+':&nbsp;<span>'+items[i]['PROPS'][j]['VALUE']+'<span><br>';
		}
		curElem.find('[data-selector=props]').html(props);
		curElem.find('[data-action=quantity]').val(items[i]['QUANTITY']);
		curElem.find('[data-selector=price]').html(items[i]['PRICE_FORMATED']+' <span class="rub">руб.</span>');
		//priceBlock.find('.e_old_price').text(items[i]['FULL_PRICE_FORMATED']);
		curElem.find('[data-selector=sum]').html(items[i]['SUM']+' <span class="rub">руб.</span>');
		var controlsBlock = curElem.find('.buttons');
		controlsBlock.find('[data-action=delete]').attr('href', basketJSParams['DELETE_URL'].replace('#ID#', items[i]['ID']));
		controlsBlock.find('[data-action=delay]').attr('href', basketJSParams['DELAY_URL'].replace('#ID#', items[i]['ID']));

		//console.log(curElem);
	}
	//console.log(result);
	var itemsPlural = plural(items.length, ['товар', 'товара', 'товаров']);
	var itemsWrapper = $('.AnDelCanBuy');
	itemsWrapper.find('[data-selector=total-text]').text(items.length + ' ' + itemsPlural);
	itemsWrapper.find('[data-selector=total-sum]').html(result['BASKET_DATA']['allSum_FORMATED'] + ' <span class="rub">руб.</span>');

}

//TODO Реализовать смену торговых предложений на основе кода битрикса
function skuPropClickHandler(e) {
	if (!e) e = window.event;
	var target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-value-id'))
	{
		BX.showWait();

		var basketItemId = target.getAttribute('data-element'),
			property = target.getAttribute('data-property'),
			property_values = {},
			postData = {},
			action_var = BX('action_var').value;

		property_values[property] = target.getAttribute('data-value-id');

		// if already selected element is clicked
		if (BX.hasClass(target, 'bx_active'))
		{
			BX.closeWait();
			return;
		}

		// get other basket item props to get full unique set of props of the new product
		var all_sku_props = BX.findChildren(BX(basketItemId), {tagName: 'ul', className: 'sku_prop_list'}, true);
		if (!!all_sku_props && all_sku_props.length > 0)
		{
			for (var i = 0; all_sku_props.length > i; i++)
			{
				if (all_sku_props[i].id == 'prop_' + property + '_' + basketItemId)
				{
					continue;
				}
				else
				{
					var sku_prop_value = BX.findChildren(BX(all_sku_props[i].id), {tagName: 'li', className: 'bx_active'}, true);
					if (!!sku_prop_value && sku_prop_value.length > 0)
					{
						for (var m = 0; sku_prop_value.length > m; m++)
						{
							if (sku_prop_value[m].hasAttribute('data-value-id'))
								property_values[sku_prop_value[m].getAttribute('data-property')] = sku_prop_value[m].getAttribute('data-value-id');
						}
					}
				}
			}
		}

		postData = {
			'basketItemId': basketItemId,
			'sessid': BX.bitrix_sessid(),
			'site_id': BX.message('SITE_ID'),
			'props': property_values,
			'action_var': action_var,
			'select_props': BX('column_headers').value,
			'offers_props': BX('offers_props').value,
			'quantity_float': BX('quantity_float').value,
			'count_discount_4_all_quantity': BX('count_discount_4_all_quantity').value,
			'price_vat_show_value': BX('price_vat_show_value').value,
			'hide_coupon': BX('hide_coupon').value,
			'use_prepayment': BX('use_prepayment').value
		};

		postData[action_var] = 'select_item';

		BX.ajax({
			url: '/bitrix/components/bitrix/sale.basket.basket/ajax.php',
			method: 'POST',
			data: postData,
			dataType: 'json',
			onsuccess: function(result)
			{
				BX.closeWait();
				updateBasketTable(basketItemId, result);
			}
		});
	}
}

function updateBasketTable(basketItemId, res) {
	var table = BX("basket_items");

	if (!table)
		return;

	var rows = table.rows,
		newBasketItemId = res['BASKET_ID'],
		arItem = res['BASKET_DATA']['GRID']['ROWS'][newBasketItemId],
		lastRow = rows[rows.length - 1],
		newRow = document.createElement('tr'),
		arColumns = res['COLUMNS'].split(','),
		bShowDeleteColumn = false,
		bShowDelayColumn = false,
		bShowPropsColumn = false,
		bShowPriceType = false,
		bUseFloatQuantity = (res['PARAMS']['QUANTITY_FLOAT'] == 'Y') ? true : false;

	// insert new row instead of original basket item row
	if (basketItemId !== null)
	{
		var origBasketItem = BX(basketItemId);

		newRow.setAttribute('id', res['BASKET_ID']);
		lastRow.parentNode.insertBefore(newRow, origBasketItem.nextSibling);

		if (res['DELETE_ORIGINAL'] == 'Y')
			origBasketItem.parentNode.removeChild(origBasketItem);

		// fill row with fields' values
		var oCellMargin = newRow.insertCell(-1);
			oCellMargin.setAttribute('class', 'margin');

		for (i = 0; i < arColumns.length; i++)
		{
			if (arColumns[i] == 'DELETE')
				bShowDeleteColumn = true;

			if (arColumns[i] == 'DELAY')
				bShowDelayColumn = true;

			if (arColumns[i] == 'PROPS')
				bShowPropsColumn = true;

			if (arColumns[i] == 'TYPE')
				bShowPriceType = true;
		}

		for (i = 0; i < arColumns.length; i++)
		{
			if (arColumns[i] == 'PROPS' || arColumns[i] == 'DELAY' || arColumns[i] == 'DELETE' || arColumns[i] == 'TYPE')
				continue;


			if (arColumns[i] == 'NAME')
			{
				// first <td> - image and brand
				var oCellName = newRow.insertCell(-1),
					imageURL = '',
					cellNameHTML = '';

				oCellName.setAttribute('class', 'itemphoto');

				if (arItem['PREVIEW_PICTURE_SRC'].length > 0)
					imageURL = arItem['PREVIEW_PICTURE_SRC'];
				else if (arItem['DETAIL_PICTURE_SRC'].length > 0)
					imageURL = arItem['DETAIL_PICTURE_SRC'];
				else
					imageURL = basketJSParams['TEMPLATE_FOLDER'] + '/images/no_photo.png';

				if (arItem['DETAIL_PAGE_URL'].length > 0)
				{
					cellNameHTML = '<div class="bx_ordercart_photo_container">\
						<a href="' + arItem['DETAIL_PAGE_URL'] + '">\
							<div class="bx_ordercart_photo" style="background-image:url(\'' + imageURL + '\')"></div>\
						</a>\
					</div>';
				}
				else
				{
					cellNameHTML = '<div class="bx_ordercart_photo_container">\
						<div class="bx_ordercart_photo" style="background-image:url(\'' + imageURL + '\')"></div>\
					</div>';
				}

				if (arItem['BRAND'] && arItem['BRAND'].length > 0)
				{
					cellNameHTML += '<div class="bx_ordercart_brand">\
						<img alt="" src="' + arItem['BRAND'] + '"/>\
					</div>';
				}

				oCellName.innerHTML = cellNameHTML;

				// second <td> - name, basket props, sku props
				var oCellItem = newRow.insertCell(-1),
					cellItemHTML = '';
				oCellItem.setAttribute('class', 'item');

				if (arItem['DETAIL_PAGE_URL'].length > 0)
					cellItemHTML += '<h2 class="bx_ordercart_itemtitle"><a href="' + arItem['DETAIL_PAGE_URL'] + '">' + arItem['NAME'] + '</a></h2>';
				else
					cellItemHTML += '<h2 class="bx_ordercart_itemtitle">' + arItem['NAME'] + '</h2>';

				cellItemHTML += '<div class="bx_ordercart_itemart">';

				if (bShowPropsColumn)
				{
					var bSkip;
					for (var j = 0; j < arItem['PROPS'].length; j++)
					{
						var val = arItem['PROPS'][j];

						if (arItem.SKU_DATA)
						{
							bSkip = false;
							for (var propId in arItem.SKU_DATA)
							{
								if (arItem.SKU_DATA.hasOwnProperty(propId))
								{
									var arProp = arItem.SKU_DATA[propId];

									if (arProp['CODE'] == val['CODE'])
									{
										bSkip = true;
										break;
									}
								}
							}
							if (bSkip)
								continue;
						}

						cellItemHTML += val['NAME'] + ':&nbsp;<span>' + val['VALUE'] + '</span><br/>';
					};
				}
				cellItemHTML += '</div>';

				if (arItem.SKU_DATA)
				{
					var arProp, bIsImageProperty, full, arVal;

					for (var propId in arItem.SKU_DATA)
					{
						if (arItem.SKU_DATA.hasOwnProperty(propId))
						{
							arProp = arItem.SKU_DATA[propId];
							bIsImageProperty = false;
							full = (BX.util.array_keys(arProp['VALUES']).length > 5) ? 'full' : '';

							for (var valId in arProp['VALUES'])
							{
								arVal = arProp['VALUES'][valId];

								if (arVal['PICT'] !== false)
								{
									bIsImageProperty = true;
									break;
								}
							}

							// sku property can contain list of images or values
							if (bIsImageProperty)
							{
								cellItemHTML += '<div class="bx_item_detail_scu_small_noadaptive ' + full + '">';
								cellItemHTML += '<span class="bx_item_section_name_gray">' + arProp['NAME'] + '</span>';
								cellItemHTML += '<div class="bx_scu_scroller_container">';
								cellItemHTML += '<div class="bx_scu">';

								cellItemHTML += '<ul id="prop_' + arProp['CODE'] + '_' + arItem['ID'] + '" style="width: 200%; margin-left:0%;" class="sku_prop_list">';

								var arSkuValue, selected;

								for (var valueId in arProp['VALUES'])
								{
									arSkuValue = arProp['VALUES'][valueId];
									selected = '';

									// get current selected item
									for (var k = 0; k < arItem['PROPS'].length; k++)
									{
										var arItemProp = arItem['PROPS'][k];

										if (arItemProp['CODE'] == arProp['CODE'])
										{
											if (arItemProp['VALUE'] == arSkuValue['NAME'] || arItemProp['VALUE'] == arSkuValue['XML_ID'])
												selected = 'bx_active';
										}
									}

									cellItemHTML += '<li style="width:10%;"\
														class="sku_prop ' + selected + '"\
														data-value-id="' + arSkuValue['XML_ID'] + '"\
														data-element="' + arItem['ID'] + '"\
														data-property="' + arProp['CODE'] + '"\
														>\
														<a href="javascript:void(0);">\
															<span style="background-image:url(' + arSkuValue['PICT']['SRC'] + ')"></span>\
														</a>\
													</li>';
								}

								cellItemHTML += '</ul>';
								cellItemHTML += '</div>';

								cellItemHTML += '<div class="bx_slide_left" onclick="leftScroll(\'' + arProp['CODE'] + '\', ' + arItem['ID'] + ', '+ BX.util.array_keys(arProp['VALUES']).length + ');"></div>';
								cellItemHTML += '<div class="bx_slide_right" onclick="rightScroll(\'' + arProp['CODE'] + '\', ' + arItem['ID'] + ', '+ BX.util.array_keys(arProp['VALUES']).length + ');"></div>';

								cellItemHTML += '</div>';
								cellItemHTML += '</div>';
							}
							else // not image
							{
								cellItemHTML += '<div class="bx_item_detail_size_small_noadaptive ' + full + '">';
								cellItemHTML += '<span class="bx_item_section_name_gray">' + arProp['NAME'] + '</span>';
								cellItemHTML += '<div class="bx_size_scroller_container">';
								cellItemHTML += '<div class="bx_size">';

								cellItemHTML += '<ul id="prop_' + arProp['CODE'] + '_' + arItem['ID'] + '" style="width: 200%; margin-left:0%;" class="sku_prop_list">';

								for (var valueId in arProp['VALUES'])
								{
									var arSkuValue = arProp['VALUES'][valueId],
										selected = '';

									// get current selected item
									for (var k = 0; k < arItem['PROPS'].length; k++)
									{
										var arItemProp = arItem['PROPS'][k];

										if (arItemProp['CODE'] == arProp['CODE'])
										{
											if (arItemProp['VALUE'] == arSkuValue['NAME'])
												selected = 'bx_active';
										}
									}

									cellItemHTML += '<li style="width:10%;"\
														class="sku_prop ' + selected + '"\
														data-value-id="' + arSkuValue['NAME'] + '"\
														data-element="' + arItem['ID'] + '"\
														data-property="' + arProp['CODE'] + '"\
														>\
														<a href="javascript:void(0);">' + arSkuValue['NAME'] + '</span></a>\
													</li>';
								}

								cellItemHTML += '</ul>';
								cellItemHTML += '</div>';

								cellItemHTML += '<div class="bx_slide_left" onclick="leftScroll(\'' + arProp['CODE'] + '\', ' + arItem['ID'] + ', '+ BX.util.array_keys(arProp['VALUES']).length + ');"></div>';
								cellItemHTML += '<div class="bx_slide_right" onclick="rightScroll(\'' + arProp['CODE'] + '\', ' + arItem['ID'] + ', '+ BX.util.array_keys(arProp['VALUES']).length + ');"></div>';

								cellItemHTML += '</div>';
								cellItemHTML += '</div>';
							}
						}
					}
				}

				oCellItem.innerHTML = cellItemHTML;
			}
			else if (arColumns[i] == 'QUANTITY')
			{
				var oCellQuantity = newRow.insertCell(-1),
					oCellQuantityHTML = '',
					ratio = (parseFloat(arItem['MEASURE_RATIO']) > 0) ? arItem['MEASURE_RATIO'] : 1,
					max = (parseFloat(arItem['AVAILABLE_QUANTITY']) > 0) ? 'max="' + arItem['AVAILABLE_QUANTITY'] + '"' : '';


				var isUpdateQuantity = false;

				if (ratio != 0 && ratio != '')
				{
					var oldQuantity = arItem['QUANTITY'];
					arItem['QUANTITY'] = getCorrectRatioQuantity(arItem['QUANTITY'], ratio, bUseFloatQuantity);

					if (oldQuantity != arItem['QUANTITY'])
					{
						isUpdateQuantity = true;
					}
				}

				oCellQuantity.setAttribute('class', 'custom');
				oCellQuantityHTML += '<span>' + getColumnName(res, arColumns[i]) + ':</span>';

				oCellQuantityHTML += '<div class="centered">';
				oCellQuantityHTML += '<table cellspacing="0" cellpadding="0" class="counter">';
				oCellQuantityHTML += '<tr>';
				oCellQuantityHTML += '<td>';

				oCellQuantityHTML += '<input\
										type="text"\
										size="3"\
										id="QUANTITY_INPUT_' + arItem['ID'] + '"\
										name="QUANTITY_INPUT_' + arItem['ID'] + '"\
										size="2"\
										maxlength="18"\
										min="0"\
										' + max + '\
										step=' + ratio + '\
										style="max-width: 50px"\
										value="' + arItem['QUANTITY'] + '"\
										onchange="updateQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\',\'' + arItem['ID'] + '\', ' + ratio + ',' + bUseFloatQuantity + ')"\
					>';

				oCellQuantityHTML += '</td>';

				if (ratio != 0
					&& ratio != ''
					) // if not Set parent, show quantity control
				{
					oCellQuantityHTML += '<td id="basket_quantity_control">\
						<div class="basket_quantity_control">\
							<a href="javascript:void(0);" class="plus" onclick="setQuantity(' + arItem['ID'] + ', ' + ratio + ', \'up\', ' + bUseFloatQuantity + ');"></a>\
							<a href="javascript:void(0);" class="minus" onclick="setQuantity(' + arItem['ID'] + ', ' + ratio + ', \'down\', ' + bUseFloatQuantity + ');"></a>\
						</div>\
					</td>';
				}

				if (arItem.hasOwnProperty('MEASURE_TEXT') && arItem['MEASURE_TEXT'].length > 0)
					oCellQuantityHTML += '<td style="text-align: left">' + arItem['MEASURE_TEXT'] + '</td>';

				oCellQuantityHTML += '</tr>';
				oCellQuantityHTML += '</table>';
				oCellQuantityHTML += '</div>';

				oCellQuantityHTML += '<input type="hidden" id="QUANTITY_' + arItem['ID'] + '" name="QUANTITY_' + arItem['ID'] + '" value="' + arItem['QUANTITY'] + '" />';

				oCellQuantity.innerHTML = oCellQuantityHTML;

				if (isUpdateQuantity)
				{
					updateQuantity('QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], ratio, bUseFloatQuantity);
				}
			}
			else if (arColumns[i] == 'PRICE')
			{
				var oCellPrice = newRow.insertCell(-1),
					fullPrice = (arItem['FULL_PRICE_FORMATED'] != arItem['PRICE_FORMATED']) ? arItem['FULL_PRICE_FORMATED'] : '';

				oCellPrice.setAttribute('class', 'price');
				oCellPrice.innerHTML += '<div class="current_price" id="current_price_' + arItem['ID'] + '">' + arItem['PRICE_FORMATED'] + '</div>';
				oCellPrice.innerHTML += '<div class="old_price" id="old_price_' + arItem['ID'] + '">' + fullPrice + '</div>';

				if (bShowPriceType && arItem['NOTES'].length > 0)
				{
					oCellPrice.innerHTML += '<div class="type_price">' + basketJSParams['SALE_TYPE'] + '</div>';
					oCellPrice.innerHTML += '<div class="type_price_value">' + arItem['NOTES'] + '</div>';
				}
			}
			else if (arColumns[i] == 'DISCOUNT')
			{
				var oCellDiscount = newRow.insertCell(-1);
				oCellDiscount.setAttribute('class', 'custom');
				oCellDiscount.innerHTML = '<span>' + getColumnName(res, arColumns[i]) + ':</span>';
				oCellDiscount.innerHTML += '<div id="discount_value_' + arItem['ID'] + '">' + arItem['DISCOUNT_PRICE_PERCENT_FORMATED'] + '</div>';
			}
			else if (arColumns[i] == 'WEIGHT')
			{
				var oCellWeight = newRow.insertCell(-1);
				oCellWeight.setAttribute('class', 'custom');
				oCellWeight.innerHTML = '<span>' + getColumnName(res, arColumns[i]) + ':</span>';
				oCellWeight.innerHTML += arItem['WEIGHT_FORMATED'];
			}
			else
			{
				var oCellCustom = newRow.insertCell(-1),
					customColumnVal = '';

				oCellCustom.setAttribute('class', 'custom');
				oCellCustom.innerHTML = '<span>' + getColumnName(res, arColumns[i]) + ':</span>';

				if (arColumns[i] == 'SUM')
					customColumnVal += '<div id="sum_' + arItem['ID'] + '">';

				if (typeof(arItem[arColumns[i]]) != 'undefined' )
				{
					customColumnVal += arItem[arColumns[i]];
				}

				if (arColumns[i] == 'SUM')
					customColumnVal += '</div>';

				oCellCustom.innerHTML += customColumnVal;
			}
		}

		if (bShowDeleteColumn || bShowDelayColumn)
		{
			var oCellControl = newRow.insertCell(-1);
				oCellControl.setAttribute('class', 'control');

			if (bShowDeleteColumn)
				oCellControl.innerHTML = '<a href="' + basketJSParams['DELETE_URL'].replace('#ID#', arItem['ID']) +'">' + basketJSParams['SALE_DELETE'] + '</a><br />';

			if (bShowDelayColumn)
				oCellControl.innerHTML += '<a href="' + basketJSParams['DELAY_URL'].replace('#ID#', arItem['ID']) + '">' + basketJSParams['SALE_DELAY'] + '</a>';
		}

		var oCellMargin2 = newRow.insertCell(-1);
			oCellMargin2.setAttribute('class', 'margin');

		// set sku props click handler
		var sku_props = BX.findChildren(BX(newBasketItemId), {tagName: 'li', className: 'sku_prop'}, true);
		if (!!sku_props && sku_props.length > 0)
		{
			for (i = 0; sku_props.length > i; i++)
			{
				BX.bind(sku_props[i], 'click', BX.delegate(function(e){ skuPropClickHandler(e);}, this));
			}
		}
	}

	// update product params after recalculation
	for (var id in res.BASKET_DATA.GRID.ROWS)
	{
		if (res.BASKET_DATA.GRID.ROWS.hasOwnProperty(id))
		{
			var item = res.BASKET_DATA.GRID.ROWS[id];

			if (BX('discount_value_' + id))
				BX('discount_value_' + id).innerHTML = item.DISCOUNT_PRICE_PERCENT_FORMATED;

			if (BX('current_price_' + id))
				BX('current_price_' + id).innerHTML = item.PRICE_FORMATED;

			if (BX('old_price_' + id))
				BX('old_price_' + id).innerHTML = (item.FULL_PRICE_FORMATED != item.PRICE_FORMATED) ? item.FULL_PRICE_FORMATED : '';

			if (BX('sum_' + id))
				BX('sum_' + id).innerHTML = item.SUM;

			// if the quantity was set by user to 0 or was too much, we need to show corrected quantity value from ajax response
			if (BX('QUANTITY_' + id))
			{
				BX('QUANTITY_INPUT_' + id).value = item.QUANTITY;
				BX('QUANTITY_INPUT_' + id).defaultValue = item.QUANTITY;

				BX('QUANTITY_' + id).value = item.QUANTITY;
			}
		}
	}

	// update coupon info
	if (BX('coupon'))
	{
		var couponClass = "";

		if (BX('coupon_approved') && BX('coupon').value.length == 0)
		{
			BX('coupon_approved').value = "N";
		}

		if (res.hasOwnProperty('VALID_COUPON'))
		{
			couponClass = (!!res['VALID_COUPON']) ? 'good' : 'bad';

			if (BX('coupon_approved'))
			{
				BX('coupon_approved').value = (!!res['VALID_COUPON']) ? 'Y' : 'N'
			}
		}

		if (BX('coupon_approved') && BX('coupon').value.length > 0)
		{
			couponClass = BX('coupon_approved').value == "Y" ? "good" : "bad";
		}else
		{
			couponClass = "";
		}

		BX('coupon').className = couponClass;
	}

	// update warnings if any
	if (res.hasOwnProperty('WARNING_MESSAGE'))
	{
		var warningText = '';

		for (var i = res['WARNING_MESSAGE'].length - 1; i >= 0; i--)
			warningText += res['WARNING_MESSAGE'][i] + '<br/>';

		BX('warning_message').innerHTML = warningText;
	}

	// update total basket values
	if (BX('allWeight_FORMATED'))
		BX('allWeight_FORMATED').innerHTML = res['BASKET_DATA']['allWeight_FORMATED'].replace(/\s/g, '&nbsp;');

	if (BX('allSum_wVAT_FORMATED'))
		BX('allSum_wVAT_FORMATED').innerHTML = res['BASKET_DATA']['allSum_wVAT_FORMATED'].replace(/\s/g, '&nbsp;');

	if (BX('allVATSum_FORMATED'))
		BX('allVATSum_FORMATED').innerHTML = res['BASKET_DATA']['allVATSum_FORMATED'].replace(/\s/g, '&nbsp;');

	if (BX('allSum_FORMATED'))
		BX('allSum_FORMATED').innerHTML = res['BASKET_DATA']['allSum_FORMATED'].replace(/\s/g, '&nbsp;');

	if (BX('PRICE_WITHOUT_DISCOUNT'))
		BX('PRICE_WITHOUT_DISCOUNT').innerHTML = (res['BASKET_DATA']['PRICE_WITHOUT_DISCOUNT'] != res['BASKET_DATA']['allSum_FORMATED']) ? res['BASKET_DATA']['PRICE_WITHOUT_DISCOUNT'].replace(/\s/g, '&nbsp;') : '';
}

function checkOut() {
	BX("basket_form").submit();
	return true;
}

function recalcBasketAjax() {
	BX.showWait();

	var property_values = {},
		action_var = BX('action_var').value,
		items = BX('basket_items'),
		delayedItems = BX('delayed_items');

	var postData = {
		'sessid': BX.bitrix_sessid(),
		'site_id': BX.message('SITE_ID'),
		'props': property_values,
		'action_var': action_var,
		'select_props': BX('column_headers').value,
		'offers_props': BX('offers_props').value,
		'quantity_float': BX('quantity_float').value,
		'count_discount_4_all_quantity': BX('count_discount_4_all_quantity').value,
		'price_vat_show_value': BX('price_vat_show_value').value,
		'hide_coupon': BX('hide_coupon').value,
		'use_prepayment': BX('use_prepayment').value,
		'coupon': !!BX('coupon') ? BX('coupon').value : ""
	};

	postData[action_var] = 'recalculate';
	var basketItems = $('.all_basket_items');
	basketItems.find('[data-action=quantity]').each(function(){
		var $this = $(this);
		var id = $this.closest('.cart-item').data('id');
		postData['QUANTITY_'+id] = $this.val();
	});

	// Удалить товар
	basketItems.find(':input[name*="DELETE_"]').each(function(){
		var $this = $(this);
		if($this.val()=="Y"){
			var element = $this.closest('.cart-item');
			postData['DELETE_' + element.data('id')] = 'Y';
			element.remove();
		}
	});

	// Отложить товар
	basketItems.find(':input[name*="DELAY_"]').each(function(){
		var $this = $(this);
		if($this.val()=="Y"){
			var element = $this.closest('.cart-item');
			postData['DELAY_' + element.data('id')] = 'Y';
			element.remove();
		}
	});

	// Добавить товар в корзину из отложенных
	basketItems.find(':input[name*="RETURN_"]').each(function(){
		var $this = $(this);
		if($this.val()=="Y"){
			var element = $this.closest('.cart-item');
			postData['DELAY_' + element.data('id')] = 'N';
			element.remove();
		}
	});

	BX.ajax({
		url: '/bitrix/components/bitrix/sale.basket.basket/ajax.php',
		method: 'POST',
		data: postData,
		dataType: 'json',
		onsuccess: function(result)
		{
			BX.closeWait();
			updateBasketList(result);
			updateBasketTable(null, result);
		}
	});
}
