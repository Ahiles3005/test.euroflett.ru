<?
	if(empty($order)){
		return;
	}
?>
<div class="section">
	<? include __DIR__.'/js/paysystem.php' ?>
	<div class="bx_section">
		<h4><?=GetMessage("SOA_TEMPL_PAY_SYSTEM")?></h4>
		<?
			if ($arResult["PAY_FROM_ACCOUNT"] == "Y")
			{
				?>
				<input type="hidden" id="account_only" value="<?=($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y") ? "Y" : "N"?>" />
				<div class="bx_block w100 vertical">
					<div class="bx_element">
						<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="N">
						<label for="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT_LABEL" onclick="changePaySystem('account');" class="<?if($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y") echo "selected"?>">
							<input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" value="Y"<?if($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y") echo " checked=\"checked\"";?>>
							<div class="bx_logotype">
								<span style="background-image:url(<?=$templateFolder?>/images/logo-default-ps.gif);"></span>
							</div>
							<div class="bx_description">
								<strong><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT")?></strong>
								<p>
									<div><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT1")." <b>".$arResult["CURRENT_BUDGET_FORMATED"]?></b></div>
									<? if ($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y"):?>
										<div><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT3")?></div>
									<? else:?>
										<div><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT2")?></div>
									<? endif;?>
								</p>
							</div>
						</label>
						<div class="clear"></div>
					</div>
				</div>
				<?
			}

			uasort($arResult["PAY_SYSTEM"], "cmpBySort"); // resort arrays according to SORT value

			foreach($arResult["PAY_SYSTEM"] as $arPaySystem)
			{
				$order->showPaysystem();
				
			}
		?>
		<div style="clear: both;"></div>
	</div>
</div>