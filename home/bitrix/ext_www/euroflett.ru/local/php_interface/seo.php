<?php
AddEventHandler("main", "OnAfterEpilog", Array("newSEO", "changeTags"));

class newSEO
{
    static function changeTags() {
        $arSeo = [];

        switch ($_SERVER['REQUEST_URI']) {
            case '/catalog/podogrevateli/gaggenau/':
                $arSeo = [
                    'Подогреватели Gaggenau1',
                    'Подогреватели Gaggenau, купить подогреватель еды Гaггeнay премиум класса с доставкой по Москве и России в интернет-магазине Еврофлэтт',
                    'Подогреватели для еды Gaggenau в магазине бытовой техники премиум-класса «Еврофлэтт». В наличии большой ассортимент моделей. У нас вы купите подогреватель Gaggenau по цене официального дилера. Качественный сервис, гарантия производителя, удобные способы оплаты и доставка.'
                ];
                break;
        }

        global $APPLICATION;

        if (!empty($arSeo[0])) {
            $APPLICATION->SetTitle($arSeo[0]);
        }

        if (!empty($arSeo[1])) {
            $APPLICATION->SetPageProperty("title", $arSeo[1], false);
        }

        if (!empty($arSeo[2])) {
            $APPLICATION->SetPageProperty("description", $arSeo[2]);
        }
    }
}