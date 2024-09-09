<?php

	namespace Webprofy\Tools\Bitrix\Attribute;
	
	use Webprofy\Tools\Bitrix\Attribute\Action\Update\SetAction;

	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\BetweenAction;
	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\EqualAction;

	class FieldAttribute extends Attribute{
		function __construct($id){
			parent::__construct($id);
			$this->setData(array(
				'CODE' => $id // mother fucking bitrix logic))
			));
		}

		function getName(){
			if($this->f('CODE') == 'ID'){
				return 'ID';
			}
			return parent::getName();
		}

		static function getType(){
			return 'field';
		}

		private static $valueTypesByCode = array(
			'number' => array(
				'ID',
				'IBLOCK_SECTION_ID',
				'SORT',
			),
			'check_char' => array(
				'ACTIVE',
			)
		);

		function getValueType(){
			$code = $this->f('CODE');
			foreach(self::$valueTypesByCode as $type => $codes){
				if(in_array($code, $codes)){
					return $type;
				}
			}
			return 'string';
		}

	}