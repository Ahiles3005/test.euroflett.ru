<?
	if(!check_bitrix_sessid()){
		return;
	}

	if($currentModule->runSql() && $currentModule->copyAdminDirectory()){
		echo CAdminMessage::ShowNote('Модуль успешно установлен.');
		$currentModule->register();
	}
	else{
		$currentModule->showErrors();
	}
?>

	<form action="<?=$APPLICATION->GetCurPage()?>">
		<input type="hidden" name="lang" value="<?=LANG?>">
		<input type="submit" value="Назад">
	</form>