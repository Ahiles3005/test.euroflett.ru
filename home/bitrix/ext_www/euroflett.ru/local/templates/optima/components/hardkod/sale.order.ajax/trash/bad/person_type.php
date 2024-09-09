<?
	if(empty($order)){
		return;
	}

	if(count($arResult["PERSON_TYPE"]) > 1){
		?>
			<div class="section">
				<h4><?=GetMessage("SOA_TEMPL_PERSON_TYPE")?></h4>
				<?
					foreach($arResult["PERSON_TYPE"] as $type){
						?>
							<div class="label left">
								<input
									type="radio"
									id="PERSON_TYPE_<?=$type["ID"]?>"
									name="PERSON_TYPE"
									value="<?=$type["ID"]?>"
									<?=($type["CHECKED"]=="Y" ? " checked=\"checked\"" : '')?>
									onClick="submitForm()"
								>
									<label for="PERSON_TYPE_<?=$type["ID"]?>">
										<?=$type["NAME"]?>
									</label>
								<br />
							</div>
						<?
					}
				?>
				<div class="clear"></div>
				<input
					type="hidden"
					name="PERSON_TYPE_OLD"
					value="<?=$arResult["USER_VALS"]["PERSON_TYPE_ID"]?>"
				/>
			</div>
		<?
		return;
	}

?>

<span class="global-hide">
	<?
		if(intval($arResult["USER_VALS"]["PERSON_TYPE_ID"]) > 0){
			?>
				<input type="text" name="PERSON_TYPE" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>" />
				<input type="text" name="PERSON_TYPE_OLD" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>" />
			<?
		}
		else{
			foreach($arResult["PERSON_TYPE"] as $type){
				?>
				<input type="hidden" id="PERSON_TYPE" name="PERSON_TYPE" value="<?=$type["ID"]?>" />
				<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$type["ID"]?>" />
				<?
			}
		}

	?>
</span>
