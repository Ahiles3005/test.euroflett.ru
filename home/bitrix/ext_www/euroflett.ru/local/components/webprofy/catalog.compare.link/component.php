<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock")){ 
	die();
}

$arResult = array( "COMPARE" => $_SESSION[$arParams['COMPARE_NAME']] );

foreach ($arResult["COMPARE"] as $key => $arCompare) {
	if(count($arCompare["ITEMS"])==0){
		unset($arResult["COMPARE"][$key]);
		continue;
	}

	$res = CIBlock::GetByID($key);
	if($ar_res = $res->GetNext()){
		$arResult["COMPARE"][$key]["NAME"] = $ar_res["NAME"];
		$arResult["COMPARE"][$key]["LINK"] = '/catalog/'.$ar_res["CODE"].'/compare/?action=COMPARE';
	}

	
	/*foreach ($arCompare["ITEMS"] as $k => $value) {
		$arResult["COMPARE"][$key]["LINK"] = substr($value["DELETE_URL"], 0, strpos($value["DELETE_URL"], "?")).'compare/?action=COMPARE';
		break;
	}*/
	

}
$this->IncludeComponentTemplate();

?>