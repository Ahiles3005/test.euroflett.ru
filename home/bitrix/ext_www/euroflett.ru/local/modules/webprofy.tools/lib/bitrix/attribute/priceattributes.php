<?php

	namespace Webprofy\Tools\Bitrix\Attribute;
	use \WP;
	use Webprofy\Tools\Bitrix\Attribute\PriceAttribute\BasePriceAttribute;
	
	class PriceAttributes extends Attributes{
		function fill(){
			$this->add(new BasePriceAttribute('_BASE_PRICE'));
			return $this;
		}		
	}