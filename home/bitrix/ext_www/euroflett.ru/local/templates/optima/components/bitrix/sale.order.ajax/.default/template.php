<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	if($_GET['a'] == 1){
		include __DIR__.'/../factory/template.php';
		return;
	}

	include __DIR__.'/../factory/classes.php';
	include __DIR__.'/classes.php';
		use SimpleOrder\HTML;
		use SimpleOrder\SitePersonProperty;
		use SimpleOrder\PersonProperties;
		use SimpleOrder\Form;

	CModule::IncludeModule('webprofy.tools');
		use WP;
		use Webprofy\Tools\Bitrix\User\UserLegalInfo as UserInfo;

	$form = new Form($arResult,$arParams);
	$form->redirect();
	if($form->showSuccess()){
		require_once (__DIR__.'/success.php');
		return;
	}

	$html = new HTML();

	$properties = new PersonProperties($arResult);
	$properties
		->addGroup(array(
			'name' => 'user',
			'filter' => function($property){
				return in_array($property['CODE'], UserInfo::$personalFields);
			}
		))
		->addGroup(array(
			'name' => 'other',
			'filter' => function($property){
				return !in_array($property['CODE'], UserInfo::$personalFields);
			}
		));

	$showPayment = false;
	foreach ($arResult['BASKET_ITEMS'] as $key => $arItem) {
		$isPreorder = isPreorderById($arItem['PRODUCT_ID']);
		if (!$isPreorder) {
			$showPayment = true;
		}
	}


?>

<section class="order-page">
	<? $form->start() ?>
	<div class="content-center">
		<div class="columns">
			<div class="form-standart">
			<div class="field">
					<div class="left">Плательщик:</div>
					<div class="right">
							<ul class="radio-list-vertical">
								<?
									foreach($arResult["PERSON_TYPE"] as $type){
										?>
											<li>
												<?
													$html
														->labelRadio(
															$type["NAME"],
															'PERSON_TYPE',
															$type['ID'],
															($type["CHECKED"] == "Y")
														)
														->show();
												?>
											</li>
										<?
									}
								?>
							</ul>
					</div>
					<div class="status">
					</div>
				</div>
				<?
					$arrPrp = $properties->getByGroup('user');
					foreach ($arrPrp as $key => $row) {
						$sort[$key]  = $row['SORT'];
						if($property['CODE']=='PHONE'){
							$tel=$property['VALUE'];
						}
					}

					array_multisort($sort, SORT_ASC, $arrPrp);
					foreach($arrPrp as $property){
						$value = $property['VALUE'];
						if(!$value){
							switch($property['CODE']){
								case 'F_EMAIL':
								case 'EMAIL':
									$value = $USER->GetEmail();
									break;

								case 'F_PHONE':
								case 'PHONE':
									if($id = $USER->GetID()){
										$value = WP::bit(array(
											'of' => 'CUser',
											'one' => 'f.PERSONAL_PHONE',
											'f' => 'ID='.$id
										));
									}
									break;

								case 'F_NAME':
								case 'CONTACT_PERSON':
									$value = $USER->GetFullName();
									break;
							}
						}

						if(empty($property["VALUE"]))
					$property["VALUE"] = $_POST[$property['FIELD_NAME']];
							if($property['CODE']=='EMAIL'){ //|| $property['CODE']=='F_EMAIL'){
								$html->hidden($property['FIELD_NAME'], ($value?$value:'auto@automail.com'))->show();
								if(preg_match('|@automail.com|isu',$value))
								{$value='';}
								$property["FIELD_NAME"]='email';
								$property = new SitePersonProperty($property, $arParams);
								$property
									->set('VALUE', $value)
									->show();
							}else{
								$property = new SitePersonProperty($property, $arParams);
								$property
									->set('VALUE', $value)
									->show();

							}

							/*$property = new SitePersonProperty($property, $arParams);
							$property
							->set('VALUE', $value)
							->show();*/



					}
				?>

				<?
					foreach($properties->getByGroup('other') as $property){
						$property = new SitePersonProperty($property, $arParams);
						$property->show();
					};
				?>

				<?if ($showPayment) {?>
					<div class="field global-hide">
						<div class="left">Доставка:</div>
						<div class="right">

								<ul class="radio-list-vertical delivery-holder" data-pickup-values="['3']">
									<?
										foreach($arResult["DELIVERY"] as $delivery){
											$profiles = @$delivery['PROFILES'];
											if(empty($profiles)){
												$profiles = array(null);
											}

											foreach($profiles as $profile){
												$value = $delivery['ID'];
												if(!empty($profile)){
													$value .= ':'.$profile['ID'];
												}

												?>
													<li>
														<?
															$html
																->labelRadio(array(
																	'label' => $delivery['NAME'],
																	'name' => $delivery['FIELD_NAME'],
																	'value' => $value,
																	'checked' => $delivery['CHECKED']
																))
																->show();
														?>
													</li>
												<?
											}
										}
									?>
								</ul>
						</div>
						<div class="status">
						</div>
					</div>
					<div class="field">
						<div class="left">Оплата:</div>
						<div class="right">
								<ul class="radio-list-vertical payway-holder">
									<?
											if($arResult["PAY_FROM_ACCOUNT"] == "Y"){
												$html
													->labelCheckbox(
														'Оплата с текущего аккаунта',
														'PAY_CURRENT_ACCOUNT',
														'Y',
														$arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]
													)
													->show();
											}

											foreach($arResult["PAY_SYSTEM"] as $pay){
												$checked = $pay["CHECKED"]=="Y" && !(
													$arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" &&
													$arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y"
												);

												?>
													<li>
														<?
															$html
																//->hidden('PAY_SYSTEM_ID', $pay['ID'])
																->labelRadio(
																	empty($pay['DESCRIPTION'])?$pay['PSA_NAME']:$pay['DESCRIPTION'],
																	'PAY_SYSTEM_ID',
																	$pay['ID'],
																	$checked
																)
																->show();
														?>
													</li>
												<?
											}
									?>
								</ul>
						</div>
						<div class="status">
						</div>
					</div>
				<?}?>

                <div class="field">
                    <div class="left">Комментарий к заказу:</div>
                    <div class="right">
                        <textarea name="ORDER_DESCRIPTION"></textarea>
                    </div>
                </div>
				<?/*div class="field show-pickup">
					<div class="left">Пункт самовывоза:</div>
					<div class="right">
							<ul class="radio-list-vertical">
								<li><label><input type="radio" name="payway" value="1" checked="">
									Магазин и пункт выдачи
									<span class="details">
										<p>ул. Ленинская слобода, д. 19, стр. 1<br/>м. Автозаводская</p>
										<p><a href="#">Подробности и карта проезда</a></p>
									</span>
								</label></li>
								<li><label><input type="radio" name="payway" value="2">
									Пункт самовывоза номер два
									<span class="details">
										<p>ул. Измайловская, д. 13, стр. 2<br/>м. Автозаводская</p>
										<p><a href="#">Подробности и карта проезда</a></p>
									</span>
								</label></li>
								<li><label><input type="radio" name="payway" value="3">
									Очередной пункт самовывоза
									<span class="details">
										<p>ул. Ленинская слобода, д. 19, стр. 1<br/>м. Автозаводская</p>
										<p><a href="#">Подробности и карта проезда</a></p>
									</span>
								</label></li>
							</ul>
					</div>
					<div class="status">
					</div>
				</div>

				<div class="field hide-pickup">
					<div class="left">Адрес доставки:</div>
					<div class="right">
						<textarea></textarea>
					</div>
					<div class="status">
					</div>
				</div>

				<div class="field">
					<div class="left"></div>
					<div class="right">
						<label>
							<input type="checkbox" name="have_discount">
							У меня есть дисконтная карта
						</label>
					</div>
					<div class="status">
					</div>
				</div>

				<div class="field"
					data-watch="[name=have_discount]"
					data-change="$this.toggle($that.is(':checked')); $this.find('input').focus();"
				>
					<div class="left">Номер дисконтной карты:</div>
					<div class="right">
						<input type="text">
					</div>
					<div class="status">
						<a href="#">Проверить</a>
					</div>
				</div>

				<div class="field">
					<div class="left"></div>
					<div class="right">
						<label>
							<input type="checkbox" name="coupon">
							У меня есть купон
						</label>
					</div>
					<div class="status">
					</div>
				</div>

				<div class="field"
					data-watch="[name=coupon]"
					data-change="$this.toggle($that.is(':checked')); $this.find('input').focus();"
				>
					<div class="left">Номер купона:</div>
					<div class="right">
						<input type="text">
					</div>
					<div class="status">
						<a href="#">Проверить</a>
					</div>
				</div>

				<div class="field">
					<div class="left"></div>
					<div class="right">
						<label>
							<input type="checkbox">
							Я согласен с <a href="#">условиями сайта</a>
						</label>
					</div>
					<div class="status">
					</div>
				</div*/?>
				<div class="field">
					<div class="left"></div>
					<div class="right">
						<input type="checkbox" id="agreement" name="form_checkbox_agreement">
						<label for="agreement">Я подтверждаю, что ознакомлен и согласен с <a href="/usloviia-soglasheniia/" target="_blank">условиями</a> предоставления данных.</label>
					</div>
				</div>
				<script>
					$(function(){
						var agrement = 'section.order-page form input[type=\'submit\']';
						$(agrement).css('opacity', '0.5');
						$(agrement).attr('disabled', true);
						$(document).on("click", ".order-page form input[name=\'form_checkbox_agreement\']", function (e) {
							if($(this).is(":checked")){
								$(agrement).css('opacity', '');
								$(agrement).attr('disabled', false);
							}
							else{
								$(agrement).css('opacity', '0.5');
								$(agrement).attr('disabled', true);
							}
						});
					});
				</script>
				<div class="field">
					<div class="left"></div>
					<div class="buttons">
						<input type="submit" class="button-primary" value="Оформить заказ">
						<a class="button" href="/cart/">Вернуться в корзину</a>
					</div>
				</div>
			</div>
			<div class="basket-items">
					<table class="personal-order-details short" cellpadding="0" cellspacing="0">
						<tbody>
							<?
								foreach($arResult['BASKET_ITEMS'] as $item){
									?>
										<tr class="order-item">
											<td class="image">
												<img src="<?=$item['DETAIL_PICTURE_SRC']?>"/>
											</td>
											<td class="title">
												<a href="<?=$item['DETAIL_PAGE_URL']?>" class="item-title"><?=$item['NAME']?></a>
												<?
												if ($item['CATALOG_QUANTITY']>0){
													echo '<div class="is-available">'.(!empty($item['STATUS_NALICHIJA']) ? $item['STATUS_NALICHIJA']: 'В наличии').'</div>';
												}
												else{echo '<div class="is-not-available">'.(!empty($item['STATUS_NALICHIJA']) ? $item['STATUS_NALICHIJA'] : 'Нет в наличии').'</div>';
												?><div class="info_block">
												*на данный товар возможно потребуется предоплата,<br>подробности уточняйте у консультантов
												</div><?
												}
												?>
												<?/*div class="sub-article">
													215332
												</div*/?>
											</td>
											<td class="quantity">
												<?=$item['QUANTITY']?>
											</td>
											<td class="total">
												<?=$item['SUM']?> <span class="rub">руб.</span>
											</td>
										</tr>
									<?
								}

								$discount = false;
								if(doubleval($arResult['DISCOUNT_PRICE'])){
									$discount = true;
									?>
										<tr>
											<td colspan="2"></td>
											<td>Скидка</td>
											<td><?=$arResult["DISCOUNT_PRICE_FORMATED"]?> <span class="rub">руб.</span></td>
										</tr>
									<?
								}

								if(doubleval($arResult['DELIVERY_PRICE'])){
									?>
										<tr>
											<td colspan="2"></td>
											<td>Доставка</td>
											<td><?=$arResult["DELIVERY_PRICE_FORMATED"]?> <span class="rub">руб.</span></td>
										</tr>
									<?
								}

								if(strlen($arResult['PAYED_FROM_ACCOUNT_FORMATED'])){
									?>
										<tr>
											<td colspan="2"></td>
											<td>Скидка</td>
											<td><?=$arResult["PAYED_FROM_ACCOUNT_FORMATED"]?> <span class="rub">руб.</span></td>
										</tr>
									<?
								}
							?>
							<tr>
								<td colspan="2"></td>
								<td>Итого</td>
								<td><?=$arResult["ORDER_TOTAL_PRICE_FORMATED"]?> <span class="rub">руб.</span></td>
							</tr>
							<?
								if($discount){
									?>
										<tr>
											<td colspan="2"></td>
											<td>Без скидки</td>
											<td><?=$arResult["PRICE_WITHOUT_DISCOUNT"]?> <span class="rub">руб.</span></td>
										</tr>
									<?
								}
							?>
						</tbody>
					</table>

			</div>
		</div>
	</div>

	<input type="hidden" name="recaptcha_response" value="">
	<? $form->end() ?>
</section>
<script>
 $(document).ready(function() {
           $( "input[name*='ORDER_PROP_2']" ).mask("+7 (999) 999-9999");
        });
</script>