<?php

	namespace Webprofy\Tools\Bitrix\Attribute;

	use Webprofy\Tools\Bitrix\Getter;
	
	class PriceAttribute extends Attribute{
		static function getType(){
			return 'price';
		}

		function getValueType(){
			return 'number';
		}

		function getElementValueType(){
			return 'price';
		}
	}