<? 
	if(!check_bitrix_sessid()){
		return;
	}

	if($currentModule->removeAdminDirectory() && $currentModule->runSql(true)){
		echo CAdminMessage::ShowNote('Модуль успешно удалён');
		$currentModule->unregister();
	}
	else{
		$currentModule->showErrors();
	}

?>
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="Назад">
<form>