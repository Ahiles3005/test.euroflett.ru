<?
$arTemplateParameters = array();

$rsQuestions = CFormField::GetList($arCurrentValues['WEB_FORM_ID'], "N");
$arHiddenFields = array();
while ($arQuestion = $rsQuestions->Fetch())
{
	//dump($arQuestion);
	if($arQuestion['FIELD_TYPE'] == ""){
		$arHiddenFields[] = $arQuestion;
	}
}

$arTemplateParameters["WEB_FORM_ID2"] = array(
	"NAME" => "Нажмите, чтобы обновить",
	"TYPE" => "CHECKBOX",
	"ADDITIONAL_VALUES"	=> "N",
	"PARENT" => "DATA_SOURCE",
	"REFRESH" => "Y"
);

foreach ($arHiddenFields as $key => $field) {
	$arTemplateParameters[strtoupper($field['SID'])."_VALUE"] = array(
		"NAME" => "Значение для поля «".$field['TITLE']."»",
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "DATA_SOURCE"
	);
}

$arTemplateParameters["FORM_CONTAINER_CLASS"] = array(
	"NAME" => "Класс контейнера формы",
	"TYPE" => "STRING",
	"DEFAULT" => "form-standart",
	"PARENT" => "DATA_SOURCE"
);

$arTemplateParameters["FORM_CONTAINER_ID"] = array(
	"NAME" => "ID контейнера формы",
	"TYPE" => "STRING",
	"DEFAULT" => "webform_".$arCurrentValues['WEB_FORM_ID'],
	"PARENT" => "DATA_SOURCE"
);

$arTemplateParameters["THANKYOU_URL"] = array(
	"NAME" => "URL страницы «Спасибо» (если нужен редирект)",
	"TYPE" => "STRING",
	"DEFAULT" => "",
	"PARENT" => "DATA_SOURCE"
);

?>