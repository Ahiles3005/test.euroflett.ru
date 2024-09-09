<?php
	
	namespace Webprofy\Tools\Bitrix\Attribute\Action\Update;

	use Webprofy\Tools\Bitrix\Attribute\Action\UpdateAction;
	use Webprofy\Tools\Bitrix\Attribute\Action\Operators;

	class MathAction extends UpdateAction{
		protected static $operatorsData = array(
			array('+', 'Добавить'),
			array('-', 'Вычесть'),
			array('x', 'Умножить'),
			array(':', 'Поделить')
		);

		public function __construct($operator = null){
			parent::__construct($operator);
			$this->value
				->setCanOther(true)
				->setType('number');
		}

		function checkAttribute($attribute){
			return in_array($attribute->getValueType(), array(
				'number'
			));
		}

		function run($current, $update, $element){
			$current = floatval($current);
			$update = floatval($update);

			switch($this->operators->getId()){
				case '+':
					return $current + $update;

				case '-':
					return $current - $update;

				case 'x':
					return $current * $update;

				case ':':
					return $current / $update;
			}
		}
	}