<?
class webprofy_tools extends CModule{
	public
		$MODULE_ID = "webprofy.tools",
		$MODULE_VERSION = 0.1,
		$MODULE_VERSION_DATE = "2015-03-03 00:00:00",
		$MODULE_NAME = 'Инструменты',
		$MODULE_DESCRIPTION = 'Классы, используемые при разработке Wepbrofy',
		$MLANG = "WEBPROFY_TOOLS_",
		$PARTNER_NAME = "Webprofy";

	protected
		$dependences,
		$errors = array();

	function __construct(){
		$this->dependences = array(
			// array("main", "OnBuildGlobalMenu", $this->MODULE_ID, "WebprofyAutoreplace", "OnBuildGlobalMenuHandler"),
			// array("iblock", "OnIBlockPropertyBuildList", "custom_propertyfiles", "CuIBlockPropertyFiles", "GetUserTypeDescription"),
			// array("main", "OnUserTypeBuildList", "custom_propertyfiles", "CuUserPropertyFiles", "GetUserTypeDescription"),
			// array("iblock", "OnAfterIBlockElementDelete", "custom_propertyfiles", "CuIBlockPropertyFiles", "OnAfterIBlockElementDeleteHandler"),
			// array("iblock", "OnAfterIBlockElementAdd", "custom_propertyfiles", "CuIBlockPropertyFiles", "OnAfterIBlockElementAddHandler"),	
		);
	}

	function DoInstall(){
		$this->loadFile('Установка', '/step/1.php');
	}

	function DoUninstall(){
		$step = intval($GLOBALS['step']) == 2 ? 2 : 1;
		$this->loadFile('Удаление, шаг '.$step, '/unstep/'.$step.'.php');
	}

	function InstallFiles($arParams = array()){
		return true;
	}

	function UnInstallFiles(){
		return true;
	}

	protected function loadFile($name, $path){
		$GLOBALS['currentModule'] = $this;
		$GLOBALS['APPLICATION']->IncludeAdminFile($name, __DIR__.$path);
	}

	protected function toggleRegister($activate = false){
		if(!$activate){
			COption::RemoveOption($this->MODULE_ID);
			CAgent::RemoveModuleAgents($this->MODULE_ID);
		}

		$prefix = $activate ? '' : 'Un';

		$toggleModule = $prefix.'RegisterModule';
		$toggleDependences = $prefix.'RegisterModuleDependences';

		$toggleModule($this->MODULE_ID);
		foreach($this->dependences as $a){
			$toggleDependences(
				$a[0],
				$a[1],
				$a[2],
				$a[3],
				$a[4]
			);
		}
	}

	function register(){
		$this->toggleRegister(true);
	}

	function unregister(){
		$this->toggleRegister(false);
	}

	function runSql($remove = false){
		if($remove && $_REQUEST["savedata"] == "Y"){
			return true;
		}
		$errors = $GLOBALS['DB']->RunSQLBatch(__DIR__.'/mysql/'.($remove ? 'un' : '').'install.sql');
		if($errors === false){
			return true;
		}

		$this->errors = array_merge($this->errors, $errors);
		return false;
	}

	function getErrors(){
		return $this->errors;
	}

	function showErrors(){
		echo CAdminMessage::ShowMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => 'Ошибка',
			"DETAILS" => implode('<br/>', $this->getErrors()),
			"HTML" => true
		));
	}

	protected function modifyAdminDirectory($delete = false, $errorMessage = ''){
		$functionName = ($delete ? 'Delete' : 'Copy').'DirFiles';
		if($functionName(
			__DIR__."/../admin",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin",
			$delete ? array() : true
		)){
			return true;
		}

		if($delete){
			return true;
		}

		$this->errors[] = $errorMessage;
		return false;
	}

	function removeAdminDirectory(){
		return $this->modifyAdminDirectory(true, 'Не удалось удалить данные из папки /bitrix/admin');
	}
	
	function copyAdminDirectory(){
		return $this->modifyAdminDirectory(false, 'Не удалось скопировать данные в папку /bitrix/admin');
	}
}

?>