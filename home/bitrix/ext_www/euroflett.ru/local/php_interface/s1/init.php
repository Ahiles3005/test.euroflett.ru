<?php
function isHttps(){
	$isSecure = false;
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		$isSecure = true;
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
		$isSecure = true;
	}

	return $isSecure;
}

function AdminSection(){
	global $USER, $APPLICATION;
	$isSection = false;
	if(strpos($APPLICATION->GetCurDir(), "/bitrix/")!==false){
		$isSection = true;
	}
	return $isSection;
}

function redirHttps() {
	if (AdminSection()) {return;}
    
    if(php_sapi_name() == 'cli' || count($_POST) > 0) {
        return;
    }
    
	if(!isHttps()){
		if (!is_numeric(stripos($_SERVER['HTTP_HOST'], "www"))) {
			$redirect = 'https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		else {
			$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
} 

AddEventHandler("main", "OnPageStart", "redirHttps");


//запрет на удаление свойства,раздела,элемента для юзеров группы seo-менеджер
AddEventHandler("iblock", "OnBeforeIBlockPropertyDelete", "OnBeforeIBlockElementDeleteHandler");
AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", "OnBeforeIBlockElementDeleteHandler");
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", "OnBeforeIBlockElementDeleteHandler");

function OnBeforeIBlockElementDeleteHandler($ID)
{
    if ( CSite::InGroup( array(8) ) ):
        global $APPLICATION;
        $APPLICATION->throwException("Вам не разрешено ничего удалять!");
        return false;
    endif;
}

//запрет на создание\изменение раздела для юзеров группы seo-менеджер
AddEventHandler("iblock", "OnBeforeIBlockSectionAdd", "OnBeforeIBlockSectionAddHandler");
AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate", "OnBeforeIBlockSectionAddHandler");

function OnBeforeIBlockSectionAddHandler(&$arFields)
{
    if ( CSite::InGroup( array(8) ) ):
        global $APPLICATION;
        $APPLICATION->throwException("Вам не разрешено ничего удалять!");
        return false;
    endif;
}


/*//запрет на удаление ИБ для юзеров группы seo-менеджер
RegisterModuleDependences("iblock",
    "OnBeforeIBlockDelete",
    "catalog",
    "CCatalog",
    "OnIBlockDelete");

class CCatalog
{

    function OnBeforeIBlockDelete($ID)
    {
        if ( CSite::InGroup( array(8) ) ):
            global $APPLICATION;
            $APPLICATION->throwException("Вам не разрешено ничего удалять!");
            return false;
        endif;
    }
}*/
?>