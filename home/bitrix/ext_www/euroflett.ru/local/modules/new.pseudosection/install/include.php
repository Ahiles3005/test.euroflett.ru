<?
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	'new.pseudosection',
	array(
		'WPPseudosectionNew' => 'classes/general/webprofy_pseudosection_new.php',
		'WPPseudosectionPropertyNew' => 'classes/general/webprofy_pseudosection_new_property.php',
	)
);

global $DBType;
?>
