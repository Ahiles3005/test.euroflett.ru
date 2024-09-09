<?

	class Order{
		public
			$result,
			$templateFolder,
			$params;

		protected
			$data,
			$includes = array(
				'person_type',
				'properties',
				'delivery',
				'paysystem',
				'related_props',
				'summary'
			);

		public function __construct($result = null, $params = null, $templateFolder = null){
			$this->result = $result;
			$this->params = $params;
			$this->templateFolder = $templateFolder;
		}

		public function makeInitialRedirect(){
			if($this->notRegistered()){
				return $this;
			}
			
			if($this->result["USER_VALS"]["CONFIRM_ORDER"] != "Y" && $this->result["NEED_REDIRECT"] != "Y"){
				return $this;
			}

			if(strlen($this->result["REDIRECT_URL"]) == 0){
				return $this;
			}

			$GLOBALS['APPLICATION']->RestartBuffer();
			?>
				<script type="text/javascript">
					window.top.location.href='<?=CUtil::JSEscape($this->result["REDIRECT_URL"])?>';
				</script>
			<?
			die();
		}

		function getColumnName($arHeader){
			return (strlen($arHeader["name"]) > 0) ? $arHeader["name"] : GetMessage("SALE_".$arHeader["id"]);
		}

		public function initJsCss(){
			$GLOBALS['APPLICATION']->SetAdditionalCSS($this->templateFolder."/../style_cart.css");
			$GLOBALS['APPLICATION']->SetAdditionalCSS($this->templateFolder."/../style.css");
			CJSCore::Init(array('fx', 'popup', 'window', 'ajax'));
			return $this;
		}

		function createFunctions(){
			foreach(array(
				'cmpBySort',
				'getColumnName',
				'showFilePropertyField',
				'PrintPropsForm',
			) as $name){
				$s = '
					function '.$name.'(){
						call_user_func_array(array(
							$GLOBALS["order"],
							"'.$name.'"
						), func_get_args());
					}
				';
				eval($s);
			}

			return $this;
		}


		function cmpBySort($array1, $array2){
			if(!isset($array1["SORT"]) || !isset($array2["SORT"]))
				return -1;

			if($array1["SORT"] > $array2["SORT"])
				return 1;

			if($array1["SORT"] < $array2["SORT"])
				return -1;

			if($array1["SORT"] == $array2["SORT"])
				return 0;
		}

		public function notRegistered(){
			return !$GLOBALS['USER']->IsAuthorized() && $this->params["ALLOW_AUTO_REGISTER"] == "N";
		}

		public function showMessages(){
			
			if(!empty($this->result["ERROR"])){
				foreach($this->result["ERROR"] as $v)
					echo ShowError($v);

				return $this;

			}
			
			if(!empty($this->result["OK_MESSAGE"])){
				foreach($this->result["OK_MESSAGE"] as $v)
					echo ShowNote($v);
				return $this;
			}

			return $this;

		}

		public function confirmedAndRedirect(){
			return $this->result["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $this->result["NEED_REDIRECT"] == "Y";
		}

		public function includeFile($fileName = ''){
			require_once(__DIR__.'/../'.$fileName);
			return $this;
		}

		public function isAjax(){
			return $_POST["is_ajax_post"] == "Y";
		}

		public function getIncludes(){
			$includes = $this->includes;

			if($this->params["DELIVERY_TO_PAYSYSTEM"] == "p2d"){
				$includes[2] = 'paysystem';
				$includes[3] = 'delivery';
			}

			foreach($includes as $k => $v){
				$includes[$k] = __DIR__.'/../'.$v.'.php';
			}

			return $includes;
		}

		public function showHiddenInputs(){
			if($_REQUEST['PERMANENT_MODE_STEPS'] == 1){
				?>
					<input type="hidden" name="PERMANENT_MODE_STEPS" value="1" />
				<?
			}

			if(strlen($this->result["PREPAY_ADIT_FIELDS"]) > 0){
				echo $this->result["PREPAY_ADIT_FIELDS"];
			}

			return $this;
		}

		public function showFinalErrors(){
			if(empty($this->result["ERROR"]) || $this->result["USER_VALS"]["FINAL_STEP"] != "Y"){
				return $this;
			}

			foreach($this->result["ERROR"] as $v){
				echo ShowError($v);
			}

			?>
			<script type="text/javascript">
				top.BX.scrollToNode(top.BX('ORDER_FORM'));
			</script>
			<?

			return $this;
		}




		/// BITRIX FUNCTIONS
		function showFilePropertyField($name, $property_fields, $values, $max_file_size_show=50000)
		{
			$res = "";

			if(!is_array($values) || empty($values))
				$values = array(
					"n0" => 0,
				);

			if($property_fields["MULTIPLE"] == "N")
			{
				$res = "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[0]\" id=\"".$name."[0]\"></label>";
			}
			else
			{
				$res = '
				<script type="text/javascript">
					function addControl(item)
					{
						var current_name = item.id.split("[")[0],
							current_id = item.id.split("[")[1].replace("[", "").replace("]", ""),
							next_id = parseInt(current_id) + 1;

						var newInput = document.createElement("input");
						newInput.type = "file";
						newInput.name = current_name + "[" + next_id + "]";
						newInput.id = current_name + "[" + next_id + "]";
						newInput.onchange = function() { addControl(this); };

						var br = document.createElement("br");
						var br2 = document.createElement("br");

						BX(item.id).parentNode.appendChild(br);
						BX(item.id).parentNode.appendChild(br2);
						BX(item.id).parentNode.appendChild(newInput);
					}
				</script>
				';

				$res .= "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[0]\" id=\"".$name."[0]\"></label>";
				$res .= "<br/><br/>";
				$res .= "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[1]\" id=\"".$name."[1]\" onChange=\"javascript:addControl(this);\"></label>";
			}

			return $res;
		}

		function PrintPropsForm($arSource = array(), $locationTemplate = ".default")
		{
			if(!empty($arSource))
			{
				?>
					<div>
						<?
						foreach ($arSource as $arProperties)
						{
							if(CSaleLocation::isLocationProMigrated())
							{
								$propertyAttributes = array(
									'type' => $arProperties["TYPE"],
									'valueSource' => $arProperties['SOURCE'] == 'DEFAULT' ? 'default' : 'form'
								);

								if(intval($arProperties['IS_ALTERNATE_LOCATION_FOR']))
									$propertyAttributes['isAltLocationFor'] = intval($arProperties['IS_ALTERNATE_LOCATION_FOR']);

								if(intval($arProperties['INPUT_FIELD_LOCATION']))
									$propertyAttributes['altLocationPropId'] = intval($arProperties['INPUT_FIELD_LOCATION']);

								if($arProperties['IS_ZIP'] == 'Y')
									$propertyAttributes['isZip'] = true;
							}
							?>
							<div data-property-id-row="<?=intval(intval($arProperties["ID"]))?>">

							<?
							if($arProperties["TYPE"] == "CHECKBOX")
							{
								?>
								<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="">

								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r1x3 pt8">
									<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if($arProperties["CHECKED"]=="Y") echo " checked";?>>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>

								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "TEXT")
							{
								?>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<input type="text" maxlength="250" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" />

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>
								<div style="clear: both;"></div><br/>
								<?
							}
							elseif($arProperties["TYPE"] == "SELECT")
							{
								?>
								<br/>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<select name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
										<?
										foreach($arProperties["VARIANTS"] as $arVariants):
										?>
											<option value="<?=$arVariants["VALUE"]?>"<?if($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
										<?
										endforeach;
										?>
									</select>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>
								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "MULTISELECT")
							{
								?>
								<br/>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
										<?
										foreach($arProperties["VARIANTS"] as $arVariants):
										?>
											<option value="<?=$arVariants["VALUE"]?>"<?if($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
										<?
										endforeach;
										?>
									</select>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>
								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "TEXTAREA")
							{
								$rows = ($arProperties["SIZE2"] > 10) ? 4 : $arProperties["SIZE2"];
								?>
								<br/>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<textarea rows="<?=$rows?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>"><?=$arProperties["VALUE"]?></textarea>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>
								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "LOCATION")
							{
								?>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">

									<?
									$value = 0;
									if(is_array($arProperties["VARIANTS"]) && count($arProperties["VARIANTS"]) > 0){
										foreach ($arProperties["VARIANTS"] as $arVariant)
										{
											if($arVariant["SELECTED"] == "Y")
											{
												$value = $arVariant["ID"];
												break;
											}
										}
									}
									?>

									<?CSaleLocation::proxySaleAjaxLocationsComponent(array(
										"AJAX_CALL" => "N",
										"COUNTRY_INPUT_NAME" => "COUNTRY",
										"REGION_INPUT_NAME" => "REGION",
										"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
										"CITY_OUT_LOCATION" => "Y",
										"LOCATION_VALUE" => $value,
										"ORDER_PROPS_ID" => $arProperties["ID"],
										"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
										"SIZE1" => $arProperties["SIZE1"],
									),
									array(
										"ID" => $arProperties["VALUE"],
										"CODE" => "",
										"SHOW_DEFAULT_LOCATIONS" => "Y",

										// function called on each location change caused by user or by program
										// it may be replaced with global component dispatch mechanism coming soon
										"JS_CALLBACK" => "submitFormProxy", //($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitFormProxy" : "",
										
										// function window.BX.locationsDeferred['X'] will be created and lately called on each form re-draw.
										// it may be removed when sale.order.ajax will use real ajax form posting with BX.ProcessHTML() and other stuff instead of just simple iframe transfer
										"JS_CONTROL_DEFERRED_INIT" => intval($arProperties["ID"]),

										// an instance of this control will be placed to window.BX.locationSelectors['X'] and lately will be available from everywhere
										// it may be replaced with global component dispatch mechanism coming soon
										"JS_CONTROL_GLOBAL_ID" => intval($arProperties["ID"]),

										"DISABLE_KEYBOARD_INPUT" => 'Y'
									),
									$_REQUEST['PERMANENT_MODE_STEPS'] == 1 ? 'steps' : $locationTemplate,
									true,
									'location-block-wrapper'
									)?>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>

								</div>
								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "RADIO")
							{
								?>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<?
									if(is_array($arProperties["VARIANTS"])){
										foreach($arProperties["VARIANTS"] as $arVariants):
										?>
											<input
												type="radio"
												name="<?=$arProperties["FIELD_NAME"]?>"
												id="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"
												value="<?=$arVariants["VALUE"]?>" <?if($arVariants["CHECKED"] == "Y") echo " checked";?> />

											<label for="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"><?=$arVariants["NAME"]?></label></br>
										<?
										endforeach;
									}
									?>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>
								<div style="clear: both;"></div>
								<?
							}
							elseif($arProperties["TYPE"] == "FILE")
							{
								?>
								<br/>
								<div class="bx_block r1x3 pt8">
									<?=$arProperties["NAME"]?>
									<?if($arProperties["REQUIED_FORMATED"]=="Y"):?>
										<span class="bx_sof_req">*</span>
									<?endif;?>
								</div>

								<div class="bx_block r3x1">
									<?=showFilePropertyField("ORDER_PROP_".$arProperties["ID"], $arProperties, $arProperties["VALUE"], $arProperties["SIZE1"])?>

									<?
									if(strlen(trim($arProperties["DESCRIPTION"])) > 0):
									?>
									<div class="bx_description">
										<?=$arProperties["DESCRIPTION"]?>
									</div>
									<?
									endif;
									?>
								</div>

								<div style="clear: both;"></div><br/>
								<?
							}
							?>
							</div>

							<?if(CSaleLocation::isLocationProEnabled()):?>
								<script>

									(window.top.BX || BX).saleOrderAjax.addPropertyDesc(<?=CUtil::PhpToJSObject(array(
										'id' => intval($arProperties["ID"]),
										'attributes' => $propertyAttributes
									))?>);

								</script>
							<?endif?>

							<?
						}
						?>
					</div>
				<?
			}
		}

		function showPropertySelect($showEmpty = false){
			?>
				<select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
					<?
						if($showEmpty){
							?>
								<option value="0">
									<?=GetMessage("SOA_TEMPL_PROP_NEW_PROFILE")?>
								</option>
							<?
						}

						foreach($this->result["ORDER_PROP"]["USER_PROFILES"] as $profile){
							?>
								<option value="<?= $profile["ID"] ?>"<?if($profile["CHECKED"]=="Y") echo " selected";?>><?=$profile["NAME"]?></option>
							<?
						}
					?>
				</select>
			<?
		}

		function showPropertiesSection(){
			
			$hide = false;
			$profiles = $this->result["ORDER_PROP"]["USER_PROFILES"];

			if(!is_array($profiles) || empty($profiles)){
				return $hide;
			}

			$hide = true;
			if(!empty($this->result['ERROR'])){
				$hide = false;
			}

			if($this->params["ALLOW_NEW_PROFILE"] == "Y"){
				?>
					<div class="bx_block r1x3">
						<?=GetMessage("SOA_TEMPL_PROP_CHOOSE")?>
					</div>
					<div class="bx_block r3x1">
						<? $this->showPropertySelect(true) ?>
						<div style="clear: both;"></div>
					</div>
				<?
				return $hide;
			}

			?>
				<div class="bx_block r1x3">
					<?=GetMessage("SOA_TEMPL_EXISTING_PROFILE")?>
				</div>
				<div class="bx_block r3x1">
					<?
						if(count($profiles) > 1){
							$this->showPropertySelect(false);
						}
						else{
							foreach($profiles as $arUserProfiles){
								echo "<strong>".$arUserProfiles["NAME"]."</strong>";
								?>
								<input type="hidden" name="PROFILE_ID" id="ID_PROFILE_ID" value="<?=$arUserProfiles["ID"]?>" />
								<?
							}
						}
					?>
					<div style="clear: both;"></div>
				</div>
			<?

			return $hide;
		}

		function showDelivery($delivery, $deliveryId){
			if(intval($delivery['ID']) >= 0){
				$this->showOneDelivery(array(
					'delivery' => $delivery,
					'profile' => null
				));
				return;
			}

			foreach($delivery["PROFILES"] as $profile){
				$this->showOneDelivery(array(
					'delivery' => $delivery,
					'profile' => $profile,
				));
			}
		}

		protected function showOneDelivery($data){
			$delivery = @$data['delivery'];
			$profile = @$data['profile'];
			$deliveryId = $delivery['ID'];

			if(empty($delivery)){
				return $this;
			}

			if(empty($profile)){
				$html = array(
					'id' => $deliveryId,
					'value' => $deliveryId,
				);	

				$showImages = $this->params["SHOW_STORES_IMAGES"];

				$html['click'] = 'onclick=';
				if(count($delivery["STORE"]) > 0){
					$html['click'] .= sprintf("\"fShowStore('%s','%s','%s','%s')\"",
						$deliveryId,
						$showImages,
						($showImages == "Y") ? 850 : 700,
						SITE_ID
					);
				}
				else{
					$html['click'] .= sprintf("\"BX('ID_DELIVERY_ID_%s').checked=true; submitForm()\"",
						$deliveryId
					);
				}
			}
			else{
				$html = array(
					'id' => $deliveryId.'_'.$profile['ID'],
					'value' => $deliveryId.':'.$profile['ID'],
					'extra' => (($delivery["ISNEEDEXTRAINFO"] == "Y") ? 
						"showExtraParamsDialog('".$deliveryId.":".$profile['ID']."');" :
						''),
					'img-attr' => 'onclick="BX(\'ID_DELIVERY_'.$delivery['ID'].'_'.$profile['ID'].'\').checked=true;'.$extraParams.'submitForm();"'
				);
			}

			$html = array_merge($html, array(
				'id' => 'ID_DELIVERY_ID_'.$html['id'],
				'name' => htmlspecialcharsbx($delivery["FIELD_NAME"]),
				'checked' => $delivery['CHECKED'],
				'img' => $this->templateFolder."/images/logo-default-d.gif"
			));

			if(count($delivery["LOGOTIP"]) > 0){
				$img = CFile::ResizeImageGet(
					$delivery["LOGOTIP"]["ID"],
					array("width" => "95", "height" =>"55"),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$html['img'] = $img["src"];
			}

			?>
				<div class="bx_block w100 vertical">
					<div class="bx_element">
						<input type="radio"
							id="<?=$html['id']?>"
							name="<?=$html['name']?>"
							value="<?=$html['value']?>"
							<?=$html['checked']?>
							onclick="submitForm();"
						/>

						<label for="<?=$html['id']?>" <?=$html['click']?>>
							<div
								class="bx_logotype"
								<?=$html['img-attr']?>
							>
								<span style="background-image:url(<?=$html['img']?>);"></span>
							</div>

							<div class="bx_description">
								<?
									if(empty($profile)){
										?>
											<div class="name">
												<strong>
													<?= htmlspecialcharsbx($delivery["NAME"])?>
												</strong>
											</div>

											<span class="bx_result_price">
												<?=(empty($delivery['PERIOD_TEXT']) ? '' : $delivery['PERIOD_TEXT'].'<br/>')?>
												<?=GetMessage("SALE_DELIV_PRICE");?>:
												<b><?=$delivery["PRICE_FORMATED"]?></b><br />
											</span>
											<p>
												<?=(empty($delivery['DESCRIPTION']) ? '' : $delivery['DESCRIPTION'].'<br/>')?>
												<?
													if(count($delivery["STORE"]) > 0){
														?>
															<span
																id="select_store"
																<?=(strlen($this->result["STORE_LIST"][$this->result["BUYER_STORE"]]["TITLE"]) <= 0) ? 'style="display:none;"' : '';?>
															>
																<span class="select_store">
																	<?=GetMessage('SOA_ORDER_GIVE_TITLE');?>:
																</span>
																<span class="ora-store" id="store_desc">
																	<?=htmlspecialcharsbx($this->result["STORE_LIST"][$this->result["BUYER_STORE"]]["TITLE"])?>
																</span>
															</span>
														<?
													}
												?>
											</p>
										<?
									}
									else{
										?>
											<strong
												onclick="BX('<?=$html['id']?>').checked=true;<?=$extraParams?>submitForm();"
											>
												<? printf('%s (%s)', htmlspecialcharsbx($delivery["TITLE"]), htmlspecialcharsbx($arProfile["TITLE"])) ?>
											</strong>

											<span class="bx_result_price"><!-- click on this should not cause form submit -->
												<?
													if(
														$arProfile["CHECKED"] == "Y" &&
														doubleval($this->result["DELIVERY_PRICE"]) > 0
													){
														?>
															<div>
																<?=GetMessage("SALE_DELIV_PRICE")?>:&nbsp;<b><?=$this->result["DELIVERY_PRICE_FORMATED"]?></b>
															</div>
														<?
														if(
															isset($this->result["PACKS_COUNT"]) &&
															$this->result["PACKS_COUNT"] > 1
														){
															echo GetMessage('SALE_PACKS_COUNT').': <b>'.$this->result["PACKS_COUNT"].'</b>';
														}
													}
													else{
														$GLOBALS['APPLICATION']->IncludeComponent('bitrix:sale.ajax.delivery.calculator', '', array(
															"NO_AJAX" => $this->params["DELIVERY_NO_AJAX"],
															"DELIVERY" => $delivery['ID'],
															"PROFILE" => $profile['ID'],
															"ORDER_WEIGHT" => $this->result["ORDER_WEIGHT"],
															"ORDER_PRICE" => $this->result["ORDER_PRICE"],
															"LOCATION_TO" => $this->result["USER_VALS"]["DELIVERY_LOCATION"],
															"LOCATION_ZIP" => $this->result["USER_VALS"]["DELIVERY_LOCATION_ZIP"],
															"CURRENCY" => $this->result["BASE_LANG_CURRENCY"],
															"ITEMS" => $this->result["BASKET_ITEMS"],
															"EXTRA_PARAMS_CALLBACK" => $extraParams
														), null, array('HIDE_ICONS' => 'Y'));
													}
												?>
											</span>

											<p onclick="BX('ID_DELIVERY_<?=$delivery['ID']?>_<?=$profile['ID']?>').checked=true;submitForm();">
												<?if(strlen($arProfile["DESCRIPTION"]) > 0):?>
													<?=nl2br($arProfile["DESCRIPTION"])?>
												<?else:?>
													<?=nl2br($delivery["DESCRIPTION"])?>
												<?endif;?>
											</p>
										<?
									}
								?>
							</div>
						</label>
						<div class="clear"></div>
					</div>
				</div>
			<?

			return $this;
		}

		function showPaysystem($arPaySystem){
			if (strlen(trim(str_replace("<br />", "", $arPaySystem["DESCRIPTION"]))) > 0 || intval($arPaySystem["PRICE"]) > 0)
			{
				if (count($this->result["PAY_SYSTEM"]) == 1)
				{
					?>
						<div class="bx_block w100 vertical">
							<div class="bx_element">
								<input type="hidden" name="PAY_SYSTEM_ID" value="<?=$arPaySystem["ID"]?>">
								<input type="radio"
									id="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>"
									name="PAY_SYSTEM_ID"
									value="<?=$arPaySystem["ID"]?>"
									<?if ($arPaySystem["CHECKED"]=="Y" && !($this->params["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $this->result["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y")) echo " checked=\"checked\"";?>
									onclick="changePaySystem();"
									/>
								<label for="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>" onclick="BX('ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>').checked=true;changePaySystem();">
									<?
									if (count($arPaySystem["PSA_LOGOTIP"]) > 0):
										$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
									else:
										$imgUrl = $this->templateFolder."/images/logo-default-ps.gif";
									endif;
									?>
									<div class="bx_logotype">
										<span style="background-image:url(<?=$imgUrl?>);"></span>
									</div>
									<div class="bx_description">
										<?if ($this->params["SHOW_PAYMENT_SERVICES_NAMES"] != "N"):?>
											<strong><?=$arPaySystem["PSA_NAME"];?></strong>
										<?endif;?>
										<p>
											<?
											if (intval($arPaySystem["PRICE"]) > 0)
												echo str_replace("#PAYSYSTEM_PRICE#", SaleFormatCurrency(roundEx($arPaySystem["PRICE"], SALE_VALUE_PRECISION), $this->result["BASE_LANG_CURRENCY"]), GetMessage("SOA_TEMPL_PAYSYSTEM_PRICE"));
											else
												echo $arPaySystem["DESCRIPTION"];
											?>
										</p>
									</div>
								</label>
								<div class="clear"></div>
							</div>
						</div>
					<?

					return;
				}


				?>
					<div class="bx_block w100 vertical">
						<div class="bx_element">
							<input type="radio"
								id="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>"
								name="PAY_SYSTEM_ID"
								value="<?=$arPaySystem["ID"]?>"
								<?if ($arPaySystem["CHECKED"]=="Y" && !($this->params["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $this->result["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y")) echo " checked=\"checked\"";?>
								onclick="changePaySystem();" />
							<label for="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>" onclick="BX('ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>').checked=true;changePaySystem();">
								<?
								if (count($arPaySystem["PSA_LOGOTIP"]) > 0):
									$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
								else:
									$imgUrl = $this->templateFolder."/images/logo-default-ps.gif";
								endif;
								?>
								<div class="bx_logotype">
									<span style='background-image:url(<?=$imgUrl?>);'></span>
								</div>
								<div class="bx_description">
									<?if ($this->params["SHOW_PAYMENT_SERVICES_NAMES"] != "N"):?>
										<strong><?=$arPaySystem["PSA_NAME"];?></strong>
									<?endif;?>
									<p>
										<?
										if (intval($arPaySystem["PRICE"]) > 0)
											echo str_replace("#PAYSYSTEM_PRICE#", SaleFormatCurrency(roundEx($arPaySystem["PRICE"], SALE_VALUE_PRECISION), $this->result["BASE_LANG_CURRENCY"]), GetMessage("SOA_TEMPL_PAYSYSTEM_PRICE"));
										else
											echo $arPaySystem["DESCRIPTION"];
										?>
									</p>
								</div>
							</label>
							<div class="clear"></div>
						</div>
					</div>
				<?

				return;
			}

			if (count($this->result["PAY_SYSTEM"]) == 1)
			{
				?>
					<div class="bx_block horizontal">
						<div class="bx_element">
							<input type="hidden" name="PAY_SYSTEM_ID" value="<?=$arPaySystem["ID"]?>">
							<input type="radio"
								id="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>"
								name="PAY_SYSTEM_ID"
								value="<?=$arPaySystem["ID"]?>"
								<?if ($arPaySystem["CHECKED"]=="Y" && !($this->params["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $this->result["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y")) echo " checked=\"checked\"";?>
								onclick="changePaySystem();"
								/>
							<label for="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>" onclick="BX('ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>').checked=true;changePaySystem();">
							<?
							if (count($arPaySystem["PSA_LOGOTIP"]) > 0):
								$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
							else:
								$imgUrl = $this->templateFolder."/images/logo-default-ps.gif";
							endif;
							?>
							<div class="bx_logotype">
								<span style='background-image:url(<?=$imgUrl?>);'></span>
							</div>
							<?if ($this->params["SHOW_PAYMENT_SERVICES_NAMES"] != "N"):?>
								<div class="bx_description">
									<div class="clear"></div>
									<strong><?=$arPaySystem["PSA_NAME"];?></strong>
								</div>
							<?endif;?>
						</div>
					</div>
				<?
				return;
			}

			?>
				<div class="bx_block horizontal">
					<div class="bx_element">

						<input type="radio"
							id="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>"
							name="PAY_SYSTEM_ID"
							value="<?=$arPaySystem["ID"]?>"
							<?if ($arPaySystem["CHECKED"]=="Y" && !($this->params["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $this->result["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y")) echo " checked=\"checked\"";?>
							onclick="changePaySystem();" />

						<label for="ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>" onclick="BX('ID_PAY_SYSTEM_ID_<?=$arPaySystem["ID"]?>').checked=true;changePaySystem();">
							<?
							if (count($arPaySystem["PSA_LOGOTIP"]) > 0):
								$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
							else:
								$imgUrl = $this->templateFolder."/images/logo-default-ps.gif";
							endif;
							?>
							<div class="bx_logotype">
								<span style='background-image:url(<?=$imgUrl?>);'></span>
							</div>
							<?if ($this->params["SHOW_PAYMENT_SERVICES_NAMES"] != "N"):?>
								<div class="bx_description">
									<div class="clear"></div>
									<strong>
										<?if ($this->params["SHOW_PAYMENT_SERVICES_NAMES"] != "N"):?>
											<?=$arPaySystem["PSA_NAME"];?>
										<?else:?>
											<?="&nbsp;"?>
										<?endif;?>
									</strong>
								</div>
							<?endif;?>

						</label>
					</div>
				</div>
			<?

		}

		function showTemplate($name, $data){
			foreach($data as $i => $v){
				$data['%'.$i.'%'] = $v;
				unset($data[$i]);
			}
			echo strtr(file_get_contents(__DIR__.'/../htm/'.$name.'.htm'), $data);
		}
	}