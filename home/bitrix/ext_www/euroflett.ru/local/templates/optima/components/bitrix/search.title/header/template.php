<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$INPUT_ID = trim($arParams["~INPUT_ID"]);
if(strlen($INPUT_ID) <= 0)
	$INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if(strlen($CONTAINER_ID) <= 0)
	$CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

if($arParams["SHOW_INPUT"] !== "N"):?>
<div id="<?echo $CONTAINER_ID?>" class="search-container">
	<!-- <a class="icon icon-search" href="#">Поиск</a> -->
	<form action="<?echo $arResult["FORM_ACTION"]?>" id="header-search-form">
		<div class="search-field">
			<input id="<?echo $INPUT_ID?>" type="text" name="q" value="<?=htmlspecialcharsbx($_REQUEST["q"])?>" autocomplete="off" class="search-field-input"/>
			<input name="s" type="hidden" value="" />
			<a href="#" class="search-field-submit" onclick="document.getElementById('header-search-form').submit(); return false;"></a>
		</div>
		<img src="/images/social/fb.jpg" class="img-icon" />
		<img src="/images/social/inst.jpg" class="img-icon" />
	</form>
	<script>
	BX.ready(function(){
		new JCTitleSearch({
			'AJAX_PAGE' : '<?echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
			'CONTAINER_ID': '<?echo $CONTAINER_ID?>',
			'INPUT_ID': '<?echo $INPUT_ID?>',
			'MIN_QUERY_LEN': 2
		});
	});
	</script>
</div>
<?endif?>

