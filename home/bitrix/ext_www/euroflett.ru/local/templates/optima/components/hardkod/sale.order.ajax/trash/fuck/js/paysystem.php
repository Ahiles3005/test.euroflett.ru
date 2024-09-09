<?
	if(empty($order)){
		return;
	}
?>
<script type="text/javascript">
	function changePaySystem(param)
	{
		if (BX("account_only") && BX("account_only").value == 'Y') // PAY_CURRENT_ACCOUNT checkbox should act as radio
		{
			if (param == 'account')
			{
				if (BX("PAY_CURRENT_ACCOUNT"))
				{
					BX("PAY_CURRENT_ACCOUNT").checked = true;
					BX("PAY_CURRENT_ACCOUNT").setAttribute("checked", "checked");
					BX.addClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');

					// deselect all other
					var el = document.getElementsByName("PAY_SYSTEM_ID");
					for(var i=0; i<el.length; i++)
						el[i].checked = false;
				}
			}
			else
			{
				BX("PAY_CURRENT_ACCOUNT").checked = false;
				BX("PAY_CURRENT_ACCOUNT").removeAttribute("checked");
				BX.removeClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
			}
		}
		else if (BX("account_only") && BX("account_only").value == 'N')
		{
			if (param == 'account')
			{
				if (BX("PAY_CURRENT_ACCOUNT"))
				{
					BX("PAY_CURRENT_ACCOUNT").checked = !BX("PAY_CURRENT_ACCOUNT").checked;

					if (BX("PAY_CURRENT_ACCOUNT").checked)
					{
						BX("PAY_CURRENT_ACCOUNT").setAttribute("checked", "checked");
						BX.addClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
					}
					else
					{
						BX("PAY_CURRENT_ACCOUNT").removeAttribute("checked");
						BX.removeClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
					}
				}
			}
		}

		submitForm();
	}
</script>