<?
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
    $id = 'callback_holder';
?>

<div>
    <a href="#<?=$id?>" class="fancybox">
        Обратная связь
    </a>
    <div class="global-hide">
        <div id="<?=$id?>">
            <?
                $form = new Webprofy\Factory\Form\CallbackForm();
                echo $form->html();
            ?>   
        </div>
    </div>
</div>