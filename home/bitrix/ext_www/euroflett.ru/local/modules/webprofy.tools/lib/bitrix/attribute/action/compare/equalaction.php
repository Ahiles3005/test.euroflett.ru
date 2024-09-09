<?php
	
	namespace Webprofy\Tools\Bitrix\Attribute\Action\Compare;
	
	use Webprofy\Tools\Bitrix\Attribute\Action\CompareAction;

	class EqualAction extends CompareAction{
		protected static $operatorsData = array(
			array('>', 'Больше'),
			array('%', 'Содержит'),
			array('=', 'Равно'),
			array('<', 'Меньше'),
			array('>=', 'Больше или равно'),
			array('!', 'Не равно'),
			array('<=', 'Меньше или равно'),
		);

		function __construct($operator){
			parent::__construct($operator);
			$this->value->setCanMany($operator == '=');
		}

		function checkAttribute($attribute){
			$operator = $this->operators->getId();

			switch($operator){
				case '=':
				case '!':
					return true;

				default:
					switch($attribute->getValueType()){
						case 'string':
							return $operator == '%';

						case 'number':
							return true;

						default:
							return false;
					}
			}
		}

		function run(&$code, &$value){
			$operator = $this->operators->getId();
			switch($operator){
				case '%':
					$value = '%'.$value.'%';
					break;

				case '=':
					return;

				default:
					$code = $operator.$code;
					
			}
		}
	}