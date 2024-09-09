<?php
	
	namespace Webprofy\Tools\Bitrix\Attribute\Action\Update;

	use Webprofy\Tools\Bitrix\Attribute\Action\UpdateAction;

	class SetAction extends UpdateAction{
		public function __construct($operator = null){
			parent::__construct($operator);
			$this->value->setCanOther(true);
		}

		function run($current, $update, $element){
			return $update;
		}

		function getName(){
			return '[=] Установить значение';
		}
	}