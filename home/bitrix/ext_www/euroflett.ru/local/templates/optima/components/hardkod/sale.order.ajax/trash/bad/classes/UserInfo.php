<?
	CModule::IncludeModule('webprofy.tools');
	use Webprofy\Tools\Bitrix\User\UserLegalInfo;
	use Webprofy\Tools\Bitrix\User\UserPayInfo;

	class UserInfo{
		protected
			$legal,
			$pay,
			$types;

		function __construct($arResult){
			$this->legal = new UserLegalInfo();
			$this->pay = new UserPayInfo();
		}

		function getTypes(){
			if(empty($this->types)){
				$this->types = $this->legal->getTypesData();
			}
			return $this->types;
		}

		function showPayFields(){
			$deliveries = $this->pay->getDeliveryData();
			$paysystems = $this->pay->getPaysystemData();
			?>
				<div class="field">
					<div class="left">Доставка:</div>
					<div class="right">
							<ul class="radio-list-vertical delivery-holder" data-pickup-values="['1']">
								<?
									$checked = true;
									foreach($this->pay->getDeliveryData() as $delivery){
										?>
											<li>
												<label>
													<input type="radio" class="delivery-radio" name="delivery" value="<?=$delivery['ID']?>" <?=$checked ? 'checked="checked"' : ''?>>
													<?=$delivery['NAME']?> <?=$delivery['PRICE'] > 0 ? sprintf('(+%d Р)', $delivery['PRICE']) : ''?>
												</label>
											</li>
										<?
										$checked = false;
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
									$checked = true;
									foreach($this->pay->getPaysystemData() as $paysystem){
										?>
											<li>
												<label>
													<input type="radio" class="payway-radio" data-deliveries="['2', '1']" name="payway" value="<?=$paysystem['ID']?>" <?=$checked ? 'checked="checked"' : ''?>>
													<?=$paysystem['NAME']?>
												</label>
											</li>
										<?
										$checked = false;
									}
								?>
							</ul>
					</div>
					<div class="status">
					</div>
				</div>
			<?
		}

		function showTypeFields($filter = 'other'){
			foreach($this->legal->getFilteredTypesData($filter) as $type){
				?>
					<div
						data-watch="[name=PERSON_TYPE]"
						data-change="var show = $that.filter(':checked').val() == '<?=$type['id']?>'; $this.toggle(show); show && $this.find('input').eq(0).focus();"
					>
						<?
							foreach($type['groups'] as $group){
								foreach($group['props'] as $prop){
									?>
										<div class="field">
											<div class="left"><?=$prop['name']?></div>
											<div class="right">
												<input type="text" name="<?=$prop['code']?>" value="<?=$prop['value']?>" <?=$attrs?>/><?=$after?>
											</div>
											<div class="status"></div>
										</div>
									<?
								}
							}
						?>
					</div>
				<?
			}
		}
	}