<?
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	'webprofy.pseudosection',
	array(
		'WPPseudosection' => 'classes/general/webprofy_pseudosection.php',
		'WPPseudosectionProperty' => 'classes/general/webprofy_pseudosection_property.php',
	)
);

global $DBType;
?>