<?
	if (!check_bitrix_sessid()){
		return;
	}
?>
<form action="<?=$APPLICATION->GetCurPage();?>">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="<?=$currentModule->MODULE_ID?>">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?=CAdminMessage::ShowMessage('Удалить инструменты Webprofy?');?>
	<p>Сохранить данные</p>
	<p>
		<input type="checkbox" name="savedata" id="savedata" value="Y" checked>
		<label for="savedata">Сохранить таблицы</label>
	</p>
	<input type="submit" name="inst" value="Удалить">
</form>