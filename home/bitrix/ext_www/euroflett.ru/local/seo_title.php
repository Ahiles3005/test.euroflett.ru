<?php
switch($_SERVER['REQUEST_URI']) {
    case "/catalog/vstraivaemaya-bytovaya-tekhnika/":
        $seo_title = "Встраиваемая бытовая техника для кухни в Москве, большой каталог, доступные цены, купить технику в интернет-магазине Еврофлэтт";
        $seo_h1 = "Встраиваемая бытовая техника";
        break;
case "/catalog/podogrevateli/gaggenau/":
$seo_title = "Подогреватели Gaggenau, купить подогреватель еды Гaггeнay премиум класса с доставкой по Москве и России в интернет-магазине Еврофлэтт";
$seo_h1 = "Подогреватели Gaggenau";
break;

case "/catalog/krupnaya-bytovaya-tekhnika/":
$seo_title = "Крупная бытовая техника для кухни в Москве, купить крупную бытовую технику в интернет-магазине Еврофлетт";
$seo_h1 = "Встраиваемая бытовая техника";
break;

case "/catalog/vstraivaemaya-bytovaya-tekhnika/dukhovye-shkafy/gazovye/":
$seo_title = "Крупная бытовая техника купить в Москве, цены на каталог крупной бытовой техники в интернет-магазине Еврофлетт";
$seo_h1 = "Встраиваемая бытовая техника";
break;
case "/catalog/varochnye-paneli/kombinirovannye/":
$seo_h1 = "Комбинированные варочные панели";
break;
case "/catalog/varochnye-paneli/gazovye/smeg/":
$seo_title = "Варочные панели Smeg в Москве, каталог варочных панелей от бренда Смег в магазине бытовой техники Еврофлэт";
break;
case "/catalog/dukhovye-shkafy/gazovye/smeg/":
$seo_title = "Духовые шкафы Smeg в Москве, каталог духовых шкафов от бренда Смег в магазине бытовой техники Еврофлэт";
break;

    default:
        $seo_title = "";
        $seo_h1 = "";
        break;
}
?>