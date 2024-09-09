<?
	// %RU_NAME% — %RU_DESC%
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	use Webprofy\Tools\Bitrix\Getter;
	use Webprofy\Tools\Functions as F;

	$arResult = F::cache(
		array(
			'c_%UNDER%'%LAST_UPDATE%
		),
		F::time(%CACHE_TIME%),
		function(){
%EXAMPLE_COMPONENT%
		}
	);

	$this->IncludeComponentTemplate();

?>