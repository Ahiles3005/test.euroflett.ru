<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

	$user = $arResult['arUser'];
?>


<?ShowError($arResult["strProfileError"]);?>

<h1>Личные данные</h1>

<div class="form-standart">
	<form method="POST" action="/ajax/" enctype="multipart/form-data" class="ajax-form-send">
		<input type="hidden" name="act" value="profile" />
		<input type="hidden" name="act2" value="info" />
		<?
			foreach(array(
				array('Ваше имя', 'NAME'),
				array('E-main', 'EMAIL'),
				array('Телефон', 'PERSONAL_PHONE'),
			) as $a){
				list($label, $name) = $a;
				$value = $user[$name];
				?>
					<div class="field">
						<div class="left"><?=$label?></div>
						<div class="right">
							<input type="text" name="<?=$name?>" value="<?=$value?>"/>
						</div>
						<div class="status">
							<span class="status-ok"></span>
						</div>
					</div>
				<?
			}
		?>

		<div class="click-to-change" data-state-input="changepassword">
			<input type="hidden" name="changepassword" value="" />
			<div class="state-view">
				<div class="field">
					<div class="left">Пароль:</div>
					<div class="right"><a href="#clicktochange" class="click-to-change-link samepage">Изменить пароль</a></div>
				</div>
			</div>
			<div class="state-edit">
				<div class="field">
					<div class="left">&nbsp;</div>
					<div class="right"><a href="#clicktochange" class="click-to-cancel-link samepage">Отменить смену пароля</a></div>
				</div>
				<div class="field">
					<div class="left">Старый пароль:</div>
					<div class="right"><input type="password" name="PASSWORD_OLD" value="" /></div>
					<div class="status"></div>
				</div>
				<div class="field">
					<div class="left">Новый пароль:</div>
					<div class="right"><input type="password" name="PASSWORD" value="" /></div>
					<div class="status"></div>
				</div>
				<div class="field">
					<div class="left">Новый пароль ещё раз:</div>
					<div class="right"><input type="password" name="PASSWORD_CONFIRM" value="" /></div>
					<div class="status"></div>
				</div>
			</div>
		</div>
		<div class="field">
			<div class="left">&nbsp;</div>
			<div class="right">
				<div class="buttons"><input type="submit" name="submitpersonaldata" class="button-primary" value="Сохранить изменения" /></div>
			</div>
		</div>
	</form>
</div>

<h2>Адрес доставки</h2>

<div class="form-standart">
	<form method="POST" action="/ajax/" enctype="multipart/form-data" class="ajax-form-send">
		<input type="hidden" name="act" value="profile" />
		<input type="hidden" name="act2" value="address" />

		<div class="field">
			<div class="left">Адрес доставки:</div>
			<div class="right"><textarea name="PERSONAL_STREET"><?=$user['PERSONAL_STREET']?></textarea></div>
			<div class="status"><span class="status-ok"></span></div>
		</div>

		<div class="field">
			<div class="left">&nbsp;</div>
			<div class="right">
				<div class="buttons"><input type="submit" name="submitpersonaldata" class="button-primary" value="Сохранить изменения" /></div>
			</div>
		</div>
	</form>
</div>

<h2>Юридические данные</h2>
<?
	$info = new Webprofy\Bitrix\User\UserLegalInfo();
	$types = $info->getTypesData();
?>
<div class="form-standart">
	<form method="POST" action="/ajax/" enctype="multipart/form-data" class="ajax-form-send">
		<input type="hidden" name="act" value="profile" />
		<input type="hidden" name="act2" value="jur" />

		<div class="field">
			<div class="left">Плательщик:</div>
			<div class="right"><ul class="radio-list-horizontal">
				<?
					foreach($types as $type){
						?>
							<li>
								<label>
									<input
										type="radio"
										name="payertype"
										value="<?=$type['id']?>"
										<?=$type['checked'] ? 'checked' : ''?>
									/>
									<?=$type['name']?>
								</label>
							</li>
						<?
					}
				?>
			</ul></div>
		</div>

		<?
			foreach($types as $type){
				?>
					<div class="show-on-condition" data-condition="payertype is <?=$type['id']?>">
						<?
							foreach($type['groups'] as $group){
								foreach($group['props'] as $prop){
									?>
										<div class="field">
											<div class="left"><?=$prop['name']?></div>
											<div class="right"><input type="text" name="<?=$prop['code']?>" value="<?=$prop['value']?>"/></div>
											<div class="status"></div>
										</div>
									<?
								}
							}
						?>
					</div>
				<?
			}
		?>

		<div class="field">
			<div class="left">&nbsp;</div>
			<div class="right">
				<div class="buttons"><input type="submit" name="submitpersonaldata" class="button-primary" value="Сохранить изменения" /></div>
			</div>
		</div>
	</form>
</div>

