<?
CModule::IncludeModule('iblock');
include_once(__DIR__."/CustomCCatalogCondTree.php");
class WPPseudosectionPropertyNew extends CUserTypeString {
	static function GetUserTypeDescription() {
		return array(
			"USER_TYPE_ID" => "new_webprofy_pseudosection",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => "WebProfy: Псевдоразделы New",
			"BASE_TYPE" => "string",
			"GetEditFormHTML" => array(__CLASS__, "GetEditFormHTML"),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml')
		);
	}

	function getBySectionID($section_id){
		CModule::IncludeModule('iblock');
		$res = CIBlockSection::GetByID($section_id)->GetNext();
		return unserialize(
			self::GetUserField('IBLOCK_'.$res["IBLOCK_ID"].'_SECTION', $section_id, 'UF_FILTER')
		);
	}

	function GetEditFormHTML($arUserField, $arHtmlControl) {
		global $APPLICATION;
		$res = CIBlockSection::GetByID($arUserField["VALUE_ID"])->GetNext();
		$checked = '';
		$is_active = false;
		$iIBlockId = intval($res['IBLOCK_ID']);
		$values = unserialize($arUserField['VALUE']);
		$arDiscount['CONDITIONS'] = $values['rule'];
		if($values['is_filter']=="Y"){
			$checked = ' checked="checked"';
			$is_active = true;
		}
		$index = 0;
		ob_start();
		CJSCore::Init(array("jquery"));
		?>
		<script type="text/javascript">
			$(document).ready(function() {
				$('input.pseudosection_filter_new_checkbox').on('change', function(){
					$('.pseudosection_filter_new_div').toggle("slow");
				});
			});
		</script>
		<?
		echo '<div id="admin_pdeudosection_block">';
		echo '<input type="checkbox" value="Y" class="pseudosection_filter_new_checkbox" name="is_filter"'.$checked.'> Является категорией фильтра<br><br>';
		echo '<span class="pseudosection_filter_new_div" style="display:';
		echo ($is_active)?'block':'none';
		echo '">';
		echo '<span class="wp_filter_headline_pseudosection">Условия фильтра:</span>';
		echo '<div id="tree_filrer" class="wp_filter_pseudosection"></div>';

		if (count($values['rule']>0)){
			$obCond2 = new CCCatalogCondTreeNew();
			$obCond2->Init(BT_COND_MODE_PARSE, BT_COND_BUILD_CATALOG, array());
			$conditions = $obCond2->Parse($values['rule']);
		}else{
			$conditions = '';
		}
		$obCond = new CCCatalogCondTreeNew();
		$boolCond = $obCond->Init(BT_COND_MODE_DEFAULT, BT_COND_BUILD_CATALOG, array('FORM_NAME' => 'form_section_'.$iIBlockId.'_form', 'CONT_ID' => 'tree_filrer', 'JS_NAME' => 'JSCatCondNew'));

		if (!$boolCond){
			if ($ex = $APPLICATION->GetException()){
				echo $ex->GetString()."<br>";
			}
			echo "Не удалось показать условия фильтра. Если ошибка повторяется после обновления страницы, попробуйте сбросить условия для этой группы.<br><br>";
			echo '<input type="submit" value="Сбросить условия" name="clear_rules_filter">';
			echo '<input type="hidden" value="Y" name="clear_rules_filter_for_real">';
		}else{
			$obCond->Show($conditions);
		}
		echo '</div>';
		$sReturn = ob_get_clean();
		return $sReturn;
	}

	static function SetUserField($entity_id, $value_id, $uf_id, $uf_value) {
		return $GLOBALS["USER_FIELD_MANAGER"]->Update($entity_id, $value_id, Array ($uf_id => $uf_value));
	}

	static function GetUserField ($entity_id, $value_id, $uf_id) {
		$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields ($entity_id, $value_id);
		return $arUF[$uf_id]["VALUE"];
	}

	function updateUserField(&$arFields){
		if(isset($_REQUEST['rule'])){
			if($_REQUEST['is_filter']=="Y"){
				$arPseudosection['is_filter'] = 'Y';
				$arPseudosection['rule'] = $_REQUEST['rule'];
			}else{
				$arPseudosection['is_filter'] = 'N';
				$arPseudosection['rule'] = array();
			}
			$UF_FILTER = serialize($arPseudosection);
			self::SetUserField("IBLOCK_".$arFields["IBLOCK_ID"]."_SECTION", $arFields["ID"], 'UF_FILTER',  $UF_FILTER);
		}

		if(isset($_REQUEST['clear_rules_filter'])){
			if($_REQUEST['clear_rules_filter_for_real']=='Y'){
				if($_REQUEST['is_filter']=="Y"){
					$arPseudosection['is_filter'] = 'Y';
				}else{
					$arPseudosection['is_filter'] = 'N';
				}
				$arPseudosection['rule'] = array();
				$UF_FILTER = serialize($arPseudosection);
				self::SetUserField("IBLOCK_".$arFields["IBLOCK_ID"]."_SECTION", $arFields["ID"], 'UF_FILTER',  $UF_FILTER);
			}
		}
	}
}
