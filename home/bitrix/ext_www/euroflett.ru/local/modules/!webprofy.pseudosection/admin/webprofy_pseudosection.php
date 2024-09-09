<?php

ClearVars();

$MODULE_ID = "webprofy.pseudosection";
$MLANG = "WP_PSEUDOSECTION_";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
//require_once($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$MODULE_ID."/classes/general/webprofy_pseudosection.php"));

IncludeModuleLangFile(__FILE__);
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage($MLANG."TITLE"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("WP_PSEUDOSECTION_GENERATE_LINKS"), "ICON" => "main_channel_edit", "TITLE" => GetMessage("WP_PSEUDOSECTION_GENERATE_LINKS")),

);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/********************************************************************
 * Functions
 ********************************************************************/
if(!function_exists('getClosestNotPseudoSectionAdmin')){
	function getClosestNotPseudoSectionAdmin($sectionId, $arFilter, $iblockID){
		$resInside = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $iblockID, 'ID' => $sectionId, 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'UF_PSEUDO_SECTION'));
		if($obInside = $resInside->GetNext()){
			$sectionValue = unserialize(htmlspecialchars_decode($obInside['UF_PSEUDO_SECTION']));
			if(count($sectionValue) > 0){
				if($sectionValue['is_pseudosection'] == 'Y'){
					$obCond = new CCatalogCondTree();
					$obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
					$conditions = $obCond->Parse($sectionValue['rule']);
					$strEval = $obCond->Generate($conditions, array('FIELD' => '$arElement'), array('MULTIPLE' => 'N'));
					$arFilter.=" && ".$strEval;
					$sectionId = $obInside['IBLOCK_SECTION_ID'];
					$functionReturn = getClosestNotPseudoSectionAdmin($sectionId, $arFilter, $iblockID);
					if($functionReturn){
						$sectionId = $functionReturn['SECTION_ID'];
						$arFilter = $functionReturn['FILTER'];
					}
					return array('SECTION_ID' => $sectionId, 'FILTER' => $arFilter);
				}
			}
		}
		return false;
	}
}

if(!function_exists('wpPreparePropsForFilter')){
	function wpPreparePropsForFilter($arElement){
		foreach($arElement['PROPERTIES'] as $key => $value){
			// Для свойства список.
			if(is_array($value['VALUE_ENUM_ID'])){
				$arElement['PROPERTY_'.$value['ID'].'_VALUE'] = $value['VALUE_ENUM_ID'];
			}else{
				if(is_array($value['VALUE'])){
					$arElement['PROPERTY_'.$value['ID'].'_VALUE'] = $value['VALUE'];
				}else{
					if(isset($value['VALUE_ENUM_ID'])){
						$arElement['PROPERTY_'.$value['ID'].'_VALUE'] = array($value['VALUE_ENUM_ID']);
					}else{
						$arElement['PROPERTY_'.$value['ID'].'_VALUE'] = array($value['VALUE']);
					}					
				}

			}
		}
		foreach($arElement as $key => $value){
			if(mb_strpos($key, 'PROPERTY_') !== false && mb_strpos($key, '_VALUE') === false){
				if(!is_array($value)){
					$arElement[$key.'_VALUE'] = array($value['VALUE']);
				}
			}
		}

		return $arElement;
	}
}

/********************************************************************
 * Actions
 ********************************************************************/
$ID = intval($ID);
$IBLOCK_ID = 1;
$addedToSection = array();
$removedFromSection = array();
$cachedir = $_SERVER['DOCUMENT_ROOT'].'/upload/custom_cache/';
$cachefileiblock = $cachedir.'custom_pseudosection_admin.php';
$cachefileiblockprops = $cachedir.'custom_pseudosection_admin_props.php';
$cachefileelem = $cachedir.'custom_pseudosection_admin_elems.php';

if(!is_dir($cachedir)){
	mkdir($cachedir, 0777, true);
}

if(!is_file($cachefileiblockprops)){
	$message = new CAdminMessage(array(
		"MESSAGE" => GetMessage("WP_PSEUDOSECTION_NO_PROPS_CACHE"),
		"HTML" => true,
		"TYPE" => "ERROR"
	));
}

if(!CModule::IncludeModule("catalog")){
	$message = new CAdminMessage(array(
		"MESSAGE" => GetMessage("WP_PSEUDOSECTION_NO_CATALOG_MODULE"),
		"HTML" => true,
		"TYPE" => "ERROR"
	));
}else{
	//генерируем кэш свойств для редиректа и вывода ссылко на свойства
	if(!empty($_REQUEST['generate_cache_iblock_props'])){

		include_once($_SERVER["DOCUMENT_ROOT"].getLocalPath("php_interface/classes/CustomCCatalogCondTree.php"));
		$allFilters = array();
		$obCond = new CCCatalogCondTree();
		$boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
		$res = CIBlockSection::GetList(array("left_margin"=>"ASC"), array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE'=>'Y', '!UF_PSEUDO_SECTION'=>false), true, array('ID', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID','UF_PSEUDO_SECTION'));

		while($ob = $res->GetNext()){
			$sectionValue = unserialize(htmlspecialchars_decode($ob['UF_PSEUDO_SECTION']));
			if(count($sectionValue) > 0){
				if($sectionValue['is_pseudosection'] == 'Y'){
					$conditions = $obCond->Parse($sectionValue['rule']);
					$strEval = $obCond->Generate($conditions, array());
					$strEval = preg_replace('/([\"\'])\\1+/', '$1', $strEval);
					eval('$arFilter2 = '.$strEval);
					foreach($arFilter2 as $fKey => $arFElem){
						$newKey = str_ireplace('_VALUE', '', $fKey);
						$newKey = '='.$newKey;
						$arFilter2[$newKey] = array($arFElem);
						unset($arFilter2[$fKey]);
					}
					$arFilter2["IBLOCK_ID"] = $ob['IBLOCK_ID'];
					$arFilter2["SECTION_ID"] = $ob['IBLOCK_SECTION_ID'];

					$arSectionsWr = array($ob['IBLOCK_SECTION_ID'], $ob['ID']);


					//Используется для применения фильтра родительского псевдораздела
					if(array_key_exists($ob['IBLOCK_SECTION_ID'], $arSectionFilters)){
						$arFilter2 = array_merge($arFilter2, $arSectionFilters[$ob['IBLOCK_SECTION_ID']]);
					}
					if(array_key_exists($ob['IBLOCK_SECTION_ID'], $arSectionParents)){
						$arSectionsWr = array_merge($arSectionsWr, $arSectionParents[$ob['IBLOCK_SECTION_ID']]);
					}
					asort($arFilter2);
					$arSectionFilters[$ob['ID']] = $arFilter2;
					$arSectionParents[$ob['ID']] = $arSectionsWr;

					$arList = array();
					$arAdditionalGet = array();
					$AdditionalGet = '';

					$dbParents = CIBlockSection::GetNavChain(false, $ob['IBLOCK_SECTION_ID']);
					while($arParents = $dbParents->Fetch()){
						$arList[] = $arParents['CODE'];
					}
					$sectionPath = implode('/', $arList);

					foreach($sectionValue['rule'] as $rule){
						if(mb_strpos($rule['controlId'], 'CondIBProp')!==false){
							$exp = explode(':', $rule['controlId']);
							$propName = 'PROPERTY_'.$exp[2];
							$propVal = $rule['value'];
						}
					}
					$arSectionsWr = array_unique($arSectionsWr);


					$allFilters[] = array('FILTER'=>$arFilter2, 'URL'=>'/catalog/'.$sectionPath.'/'.$ob['CODE'].'/', 'PROP_NAME'=>$propName, 'PROP_VAL'=>$propVal, 'SECTIONS'=>$arSectionsWr, 'SECTION_ID'=>$ob['ID']);
					if($fp = @fopen($cachefileiblockprops, "w+")){
						$strArCache = json_encode($allFilters);
						fwrite($fp, $strArCache);
						fclose($fp);
					}
				}
			}
		}
	}

	if(!empty($_REQUEST['generate_cache_iblock'])){
		$arCache = array();
		$haveMore = false;

		$page = intval($_REQUEST['page']);
		if($page<=0){
			$page = 1;
		}

		if($page > 1){
			$handle = fopen($cachefileiblock, "r");
			$contents = fread($handle, filesize($cachefileiblock));
			$arCache = json_decode($contents, true);
			fclose($handle);
		}

		$added = intval($_REQUEST['added']);

		$arFilter = array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y', '!UF_PSEUDO_SECTION' => false, 'CHECK_PERMISSIONS' => 'N');
		$res = CIBlockSection::GetList(array('depth_level' => 'DESC', 'ID' => "ASC"), $arFilter, false, array("ID", "IBLOCK_SECTION_ID"));

		$countSections = CIBlockSection::GetCount($arFilter);
		if($countSections>0){
			if(50*($page-1)<$countSections){
				$obCond = new CCatalogCondTree();
				$boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());

				$res->NavStart(50, true, $page);
				while($ob = $res->GetNext()){
					$haveMore = true;
					$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION", $ob['ID']);
					$sectionValue = unserialize($arUF["UF_PSEUDO_SECTION"]["VALUE"]);
					if(count($sectionValue) > 0){
						if($sectionValue['is_pseudosection'] == 'Y'){
							$conditions = $obCond->Parse($sectionValue['rule']);
							$strEval = $obCond->Generate($conditions, array('FIELD' => '$arElement'), array('MULTIPLE' => 'N'));

							$nonPSIblock = WPPseudosection::getClosestNotPseudoSectionAdmin($ob['IBLOCK_SECTION_ID'], $strEval, $IBLOCK_ID);
							if(!$nonPSIblock){
								$nonPSIblock['SECTION_ID'] = $ob['IBLOCK_SECTION_ID'];
							}else{
								$strEval = "(".$nonPSIblock['FILTER'].")";
							}

							$arCache[$ob['ID']] = array('ID' => $ob['ID'], 'SECTION_ID' => $nonPSIblock['SECTION_ID'], 'EVAL_FILTER' => $strEval);
							$added++;
						}
					}
				}
			}
		}

		if($fp = @fopen($cachefileiblock, "w+")){
			$strArCache = json_encode($arCache);
			fwrite($fp, $strArCache);
			fclose($fp);
		}
		if($haveMore){
			$page++;
			$pageUrl = $APPLICATION->GetCurPageParam("generate_cache_iblock=Y&added=".$added."&page=".$page, array("generate_cache_iblock", "added", "page"));
			echo '<script type="text/javascript">window.location.href = "'.$pageUrl.'";</script>';
		}
	}

	if(!empty($_REQUEST['link_items'])){

		$page = intval($_REQUEST['page']);
		if($page<=0){
			$page = 1;
		}
		$elementsPerPage = 50;

		$added = intval($_REQUEST['added']);
		$removed = intval($_REQUEST['removed']);

		$haveMore = false;

		$handle = fopen($cachefileiblock, "r");
		$contents = fread($handle, filesize($cachefileiblock));
		$arSections = json_decode($contents, true);
		fclose($handle);

		$arSelect = array("ID", "IBLOCK_ID", "PROPERTY_*"); //IBLOCK_ID и ID обязательно должны быть указаны
		$arFilter = array("IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y");
		$rsElements = CIBlockElement::GetList(array('ID'=>"ASC"), $arFilter, false, false, $arSelect);
		$cntElems = intval($rsElements->SelectedRowsCount());

		$IBLOCK_OFFER_ID = false;

		$mxResult = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);
		if (is_array($mxResult)){
		    $IBLOCK_OFFER_ID = $mxResult['IBLOCK_ID'];
		}

		if($elementsPerPage*($page-1)<$cntElems){
			$haveMore = true;
			$rsElements->NavStart($elementsPerPage, true, $page);
			while($obElement = $rsElements->GetNextElement()){
				$added++;
				$arElement = $obElement->GetFields();

				$arElement['PROPERTIES'] = $obElement->GetProperties();

				$arElement = wpPreparePropsForFilter($arElement);


				$db_old_groups = CIBlockElement::GetElementGroups($arElement['ID'], true, array("ID"));
				$ar_new_groups = array();
				$ar_old_groups = array();
				while($ar_group = $db_old_groups->Fetch()){
					if(!array_key_exists($ar_group["ID"], $arSections)){
						$ar_old_groups[] = $ar_group["ID"];
					}
				}

				if($IBLOCK_OFFER_ID){
					$arElementClear = $arElement;
					$rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $IBLOCK_OFFER_ID, 'PROPERTY_CML2_LINK' => $arElement['ID']), false, false, array("ID", "IBLOCK_ID", "PROPERTY_*"));
					while($obOffer = $rsOffers->GetNextElement()){
						$arNewElement = $arElementClear;
						$arNewElement['PROPERTIES'] = $obOffer->GetProperties();
						$arNewElement = wpPreparePropsForFilter($arNewElement);

						$arElement = array_merge($arElementClear, $arNewElement);

						foreach ($arSections as $arSection){
							if(eval('return('.$arSection['EVAL_FILTER'].');') && array_search($arSection['SECTION_ID'], $ar_old_groups) !== false){
								if(array_search($arSection['ID'], $ar_new_groups) === false){
									$addedToSection[] = $arElement['ID'];
									$ar_new_groups[] = $arSection['ID'];
								}
							}
						}
					}
					$ar_new_groups = array_keys(array_flip(array_merge($ar_new_groups, $ar_old_groups)));
				}

				foreach ($arSections as $arSection){
					if(eval('return('.$arSection['EVAL_FILTER'].');')){
						if((array_search($arSection['ID'], $ar_new_groups)) === false){
							$addedToSection[] = $arElement['ID'];
							$ar_new_groups[] = $arSection['ID'];
						}
					}
				}

				$ar_new_groups = array_keys(array_flip(array_merge($ar_new_groups, $ar_old_groups)));

				if($ar_unchanged_groups!=$ar_new_groups){
					CIBlockElement::SetElementSection($arElement['ID'], $ar_new_groups);
				}
			}
		}


		//$added += count($addedToSection);
		$removed += count($removedFromSection);

		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("WP_PSEUDOSECTION_GOT_SUCCESS", array("#GENERATED_COUNT#" => $added." из ".$cntElems, "#UNGENERATED_COUNT#" => $removed)),
			"HTML" => true,
			"TYPE" => "OK"
		));
		$page++;
	}

	/********************************************************************
	 * Form
	 ********************************************************************/

	if($message){
		echo $message->Show();
	}

		if($haveMore){
			$pageUrl = $APPLICATION->GetCurPageParam("link_items=Y&added=".$added."&removed=".$removed."&page=".$page, array("link_items", "added", "removed", "page"));
			echo '<script type="text/javascript">window.location.href = "'.$pageUrl.'";</script>';
			//LocalRedirect($pageUrl);
		}

	?>
	<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>" name="post_form">
	<?= bitrix_sessid_post() ?>
		<input type="hidden" name="ID" value=<?= $ID ?>>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<?
		$tabControl->Begin();
		?>
		<?
		//********************
		//General Tab
		//********************
		$tabControl->BeginNextTab();
		?>

		<tr>
			<td>
				Шаг 1:<br>
				<input class="adm-btn" name="generate_cache_iblock" title="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_CACHE_IBLOCK_BTN") ?>" onclick="" value="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_CACHE_IBLOCK_BTN") ?>" type="submit">
				<br><br>
			</td>
		</tr>

		<tr>
			<td>
				Шаг 2:<br>
				<input class="adm-btn" name="generate_cache_iblock_props" title="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_CACHE_IBLOCK_PROPS_BTN") ?>" onclick="" value="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_CACHE_IBLOCK_PROPS_BTN") ?>" type="submit">
				<br><br>
			</td>
		</tr>

		<tr>
			<td>
				Шаг 3:<br>
				<input class="adm-btn" name="link_items" title="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_BTN") ?>" onclick="" value="<?= GetMessage("WP_PSEUDOSECTION_GENERATE_BTN") ?>" type="submit" <?if(!is_file($cachefileiblock)){?>disabled="disabled"<?}?>> <?if(!is_file($cachefileiblock)){echo GetMessage("WP_PSEUDOSECTION_NO_IBLOCK_CACHE");}?>
				<br><br>
			</td>
		</tr>
		<?
		$tabControl->End();
		?>
	</form>
	<?
	$tabControl->ShowWarnings("post_form", $message);
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");