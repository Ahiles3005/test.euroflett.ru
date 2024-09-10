<?
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

define(SF_PROPERTY_DOCUMENT_ROOT, Bitrix\Main\Application::getDocumentRoot());

if (class_exists('simai_property'))
{
	return;
}

class simai_property extends CModule
{
	var $MODULE_ID = 'simai.property';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = 'N';
    var $PARTNER_NAME;
    var $PARTNER_URI;
	
	function simai_property()
	{
		$arModuleVersion = array();
		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = GetMessage('SF_PROPERTY_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('SF_PROPERTY_MODULE_DESCRIPTION');
		$this->PARTNER_NAME = 'SIMAI';
		$this->PARTNER_URI = 'http://simai.ru';
	}
	
	function InstallDB($arParams = array())
	{		
		$this->errors = false;
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();
			
			RegisterModule('simai.property');
		}
		return true;
	}
	
	function UnInstallDB($arParams = array())
	{	
		$this->errors = false;
		
		// remove all module options
		\Bitrix\Main\Config\Option::delete('simai.property');

		UnRegisterModule('simai.property');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		return true;
	}
	
	function InstallEvents()
	{
		return true;
	}
	
	function UnInstallEvents()
	{
		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		$this->errors = false;
		if ($arParams['properties_folder'] == 'custom' && $arParams['prop_folder'])
		{
			$arParams['prop_folder'] = htmlspecialcharsex(str_replace('.','',$arParams['prop_folder']));		
			\Bitrix\Main\IO\Directory::createDirectory(SF_PROPERTY_DOCUMENT_ROOT.$arParams['prop_folder']);
			
			CopyDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/property/', SF_PROPERTY_DOCUMENT_ROOT.$arParams['prop_folder'], true, true);
			
			\Bitrix\Main\Config\Option::set('simai.property', 'folder_path', $arParams['prop_folder']);
		}
		
		\Bitrix\Main\IO\Directory::createDirectory(SF_PROPERTY_DOCUMENT_ROOT.'/simai/admin');
		CopyDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/simai/admin/', SF_PROPERTY_DOCUMENT_ROOT.'/simai/admin');
		
		CopyDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/admin/', SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/admin');
		
		CopyDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/js/', SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/js', true, true);
		return true;
	}
	
	function UnInstallFiles($arParams = array())
	{
		if ($arParams['save_properties_folder'] != 'Y')
		{
			$prop_folder = \Bitrix\Main\Config\Option::get('simai.property', 'folder_path', '');
			if ($prop_folder)
			{
				//\Bitrix\Main\IO\Directory::deleteDirectory(SF_PROPERTY_DOCUMENT_ROOT.$arParams['prop_folder']);
			}
		}
		DeleteDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/admin/', SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/admin');
		
		DeleteDirFiles(SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/admin/', SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/admin');
		DeleteDirFilesEx('/bitrix/js/simai.property');		
		
		//DeleteDirFilesEx('/simai/admin');
		
		return true;
	}
	
	function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		if ($USER->IsAdmin())
		{
			/*$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('SF_PROPERTY_INSTALL_TITLE'), SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/step1.php');
			}
			elseif ($step == 2)
			{*/
				$this->InstallDB();
				$this->InstallEvents();
				$this->InstallFiles(array(
					'properties_folder' => "", //$request->getQuery('properties_folder'),
					'prop_folder' => "", //$request->getQuery('prop_folder'),
				));
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('SF_PROPERTY_INSTALL_TITLE'), SF_PROPERTY_DOCUMENT_ROOT.'/bitrix/modules/simai.property/install/step2.php');
			/*}*/
		}
	}
	
	function DoUninstall()
	{
		global $USER, $APPLICATION, $step;
		
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		
		if ($USER->IsAdmin())
		{
			$this->UnInstallFiles();
			$this->UnInstallDB();
			$GLOBALS['errors'] = $this->errors;
		}
	}
}
