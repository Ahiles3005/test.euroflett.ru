<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){
		die();
	}

	krumo($arResult);
	

	include(__DIR__.'/classes/UserInfo.php');

	$user = new UserInfo($arResult);
?>

<section class="order-page">
	<div class="content-center">
			<h1>Оформление заказа</h1>
		<div class="columns">
			<div class="content-area">

				<div class="form-standart">

					<? $user->showTypeFields('user') ?>
					
					<div class="field">
						<div class="left">Плательщик:</div>
						<div class="right">
								<ul class="radio-list-vertical">
									<?
										foreach($user->getTypes() as $type){
											?>
												<li>
													<label>
														<input
															type="radio"
															name="PERSON_TYPE"
															value="<?=$type['id']?>"
															<?=$type['checked'] == 'Y' ? 'checked="checked"' : ''?>
														>
														<?=$type['name']?>
													</label>
												</li>
											<?
										}
									?>
								</ul>
						</div>
						<div class="status">
						</div>
					</div>

					<? $user->showTypeFields('other') ?>
					<? $user->showPayFields() ?>
					
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
					</div*/?>
					
					<div class="hide-pickup">
						<? $user->showTypeFields('address') ?>
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
					</div>
					
					<div class="field">
						<div class="left"></div>
						<div class="buttons">
							<input type="submit" class="button-primary" value="Отправить">
							<input type="button" class="button" value="Вернуться в корзину">
						</div>
					</div>
				</div>
			</div>
			<div class="basket-items">
					<table class="personal-order-details short" cellpadding="0" cellspacing="0">
						<?
							foreach($arResult['BASKET_ITEMS'] as $item){
								?>
									<tr class="order-item">
										<td class="image"><img src="<?=$item['DETAIL_PICTURE_SRC']?>"/></td>
										<td class="title">
											<div class="item-title"><?=$item['NAME']?></div>
											<?/*<div class="sub-article">
												215332
											</div>*/?>
										</td>
										<td class="quantity"><?=$item['QUANTITY']?></td>
										<td class="total"><?=$item['SUM']?> <span class="rub">руб.</span></td>
									</tr>
								<?
							}
						?>
						<tr class="order-additional">
							<td class="image"></td>
							<td class="title" colspan="2">Скидочная карта: -10%</td>
							<td class="total">-150 <span class="rub">руб.</span></td>
						</tr>
						<tr class="order-additional">
							<td class="image"></td>
							<td class="title" colspan="2">Доставка</td>
							<td class="total">0 <span class="rub">руб.</span></td>
						</tr>
						<tr class="order-total">
							<td class="image"></td>
							<td class="title" colspan="2">Итого</td>
							<td class="total">1669 <span class="rub">руб.</span></td>
						</tr>
					</table>

					<div class="delivery-info">
						Ближайшая доставка возможна завтра (17 октября). с 10:00 до 19:00
					</div>
			</div>
		</div>
	</div>
</section>