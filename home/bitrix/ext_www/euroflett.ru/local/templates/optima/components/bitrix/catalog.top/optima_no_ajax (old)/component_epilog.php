<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
global $APPLICATION;
$APPLICATION->AddHeadScript('/local/templates/optima/components/bitrix/catalog.compare.list/compare_ajax/script.js');
if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateData['TEMPLATE_THEME']);
}
CJSCore::Init(array('popup'));
/*
print_r('<div style="display:none;">');
print_r($arParams);
print_r($_SESSION["CATALOG_COMPARE_LIST"]);
print_r('</div>');
*/
?>
<script>
	$(document).ready(function(){
	
		var id_list = '<?=json_encode($_SESSION["CATALOG_COMPARE_LIST"][$arParams['IBLOCK_ID']]["ITEMS"])?>';
		var list_json = JSON.parse(id_list);
		$('.compare_check_box input').each(function(i,elem) {
			var id = $(this).attr("data-attr-id")
			if((list_json!==null)&&(id in list_json)){
				$(this).prop('checked', true);
			}
		});
	$('.compare_check_box input').on( "click", function() {
	//function docompare(id)
	//{
		var id = $(this).attr('data-attr-id');
		var iblock = $(this).attr('data-attr-iblock');
	  var chek = document.getElementById('compare_'+id);
	   var chekbox=$(this);
		if (chek.checked)
			{
			//Добавить
			var AddedGoodId = id;
				$.get("<?=$_SERVER['REQUEST_URL']?>",
				{ 
					action: "ADD_TO_COMPARE_LIST", id: AddedGoodId, iblock: iblock},
					function(data) {
						if(data.length>0){
							$("#compare_list_count").html(data);
						}else{
							chekbox.removeAttr('checked');
							obPopupWin = BX.PopupWindowManager.create('CatalogElementCompare' + id, null, {
							autoHide: false,
							offsetLeft: 0,
							offsetTop: 0,
							overlay: true,
							closeByEsc: true,
							titleBar: true,
							closeIcon: true,
							contentColor: 'white',
							className: ''
						});
						popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>Нельзя добавить к сравнению больше 3 товаров</p></div>';	
						obPopupWin.setTitleBar('Ошибка');
						obPopupWin.setContent(popupContent);
						obPopupWin.show();
						
						}
				}
			);
			}
		else
		   {
			//Удалить
			var AddedGoodId = id;
				$.get("<?=$_SERVER['REQUEST_URL']?>",
				{ 
					action: "DELETE_FROM_COMPARE_LIST", id: AddedGoodId, iblock: iblock},
					function(data) {
				$("#compare_list_count").html(data);
				}
				);
		}
	});
	});
</script>