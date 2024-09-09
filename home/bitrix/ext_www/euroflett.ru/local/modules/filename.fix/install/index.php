<?php
Class filename_fix extends CModule
{
	var $MODULE_ID = "filename.fix";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function filename_fix()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		
		$this->PARTNER_NAME = "Kokos";

		$this->MODULE_NAME = "filename.fix - модуль исправления ошибки сохранения фото в каталоге";
		$this->MODULE_DESCRIPTION = "Модуль исправляет сохранение фото в каталоге";
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $DB;
		RegisterModule("filename.fix");
		
		$arFields = Array(
			'ID' => 'filename_fix',
			'SECTIONS'=>'N',
			'IN_RSS'=>'N',
			'SORT'=>100,
			'LANG'=>Array(
				'en'=>Array(
					'NAME' => 'filename.fix - модуль исправления ошибки сохранения фото в каталоге',
					'SECTION_NAME'=>'Sections',
					'ELEMENT_NAME'=>'Link'
				),
				'ru'=>Array(
					'NAME' => 'filename.fix - модуль исправления ошибки сохранения фото в каталогек',
					'SECTION_NAME'=>'Sections',
					'ELEMENT_NAME'=>'Ссылка'
				))
			);
		
		RegisterModuleDependences("main", "OnPageStart", "filename.fix", "FileNameFixModule", "OnPageStart");
		
		CAdminMessage::ShowNote("Модуль filename.fix установлен");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $DB;
		
		UnRegisterModuleDependences("main", "OnPageStart", "filename.fix", "FileNameFixModule", "OnPageStart");
		UnRegisterModule("filename.fix");
		CAdminMessage::ShowNote("Модуль filename.fix удален");
	}
}
?>