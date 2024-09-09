<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<nav class="catalog-menu">
	<? foreach ($arResult['SECTIONS'] as $arParent) { ?>
		<div class="catalog-section-menu">
            <div class="catalog-section-title">
                <span><a href="<?= $arParent['URL'] ?>"><?= $arParent['NAME']?></a></span>
            </div>
	        <ul>
	        	<? foreach ($arParent['SECTIONS'] as $arSection) {
?>
	        		<li>
	        		    <a href="<?= $arSection['URL'] ?>"><?= $arSection['NAME']?></a>
	        		</li>
	        	<? } ?>
	        </ul>
	    </div>
	<? } ?>
</nav>
