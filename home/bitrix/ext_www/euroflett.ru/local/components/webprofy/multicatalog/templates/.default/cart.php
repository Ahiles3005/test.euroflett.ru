<?php
$this->setFrameMode(false);
/**
 * Created by PhpStorm.
 * User: skinteev
 * Date: 20.01.2015
 * Time: 18:03
 */
print_r($_REQUEST);
if(strstr($APPLICATION->GetCurDir(),"add")){
	echo 'добавить';
}
echo 'корзина';