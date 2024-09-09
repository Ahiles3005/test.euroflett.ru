<?php

	namespace Webprofy\Tools\Bitrix\Attribute;
	
	use Webprofy\Tools\Bitrix\Attribute\Action\Update\SetAction;

	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\BetweenAction;
	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\EqualAction;

	class SectionFieldAttribute extends Attribute{
		function __construct($id){
			parent::__construct($id);
			$this->setData(array(
				'CODE' => $id
			));
		}

		static function getType(){
			return 'section-field';
		}
	}