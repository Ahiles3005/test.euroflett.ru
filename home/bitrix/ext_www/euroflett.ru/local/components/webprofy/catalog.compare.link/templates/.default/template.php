<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(count($arResult["COMPARE"]) > 0) { ?>
	<? foreach ($arResult["COMPARE"] as $key => $arCompare) { ?>
		<!-- <span class="icon icon-compare"></span>
		<a href="<?= $arCompare['LINK'] ?>" class="compare">Сравнить</a> -->
		<a href="<?= $arCompare['LINK'] ?>" class="compare"><span class="icon icon-compare"></span>Сравнить</a>
		<? break; ?>
	<? } ?>
	<? if(count($arResult["COMPARE"]) > 1) { ?>
		<div class="mobile-compare-links-dropdown">&#9660;</div>
 		<div class="compare-links-dropdown">		
 		<ul>
		<? foreach ($arResult["COMPARE"] as $key => $arCompare) { ?>
			<li><a href="<?= $arCompare['LINK'] ?>"><?= $arCompare["NAME"] ?></a> <span class="compare-items-count"><?= count($arCompare['ITEMS']) ?></span></li>
		<? } ?>
		</ul></div>
	<? } ?>
<? } else { ?>
	<span class="icon icon-compare"></span>
	<span class="compare">Сравнение</span>
<? } ?>