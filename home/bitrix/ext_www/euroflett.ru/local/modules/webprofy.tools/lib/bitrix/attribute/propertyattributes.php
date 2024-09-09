<?php

	namespace Webprofy\Tools\Bitrix\Attribute;
	use Webprofy\Tools\Bitrix\Getter;
	use Webprofy\Tools\Bitrix\Attribute\PropertyAttribute\OfferAttribute;
	
	class PropertyAttributes extends Attributes{
		function fill(){

			if($info = $this->iblock->getOfferInfo()){
				$this->add(new OfferAttribute($info['property_id']));
			}

			$pas = $this;
			Getter::bit(array(
				'of' => 'properties',
				'f' => array(
					'iblock' => $this->iblock->getId()
				),
				'each' => function($d, $f) use ($pas){
					$pa = new PropertyAttribute($f['ID']);
					$pa->setData($f);
					$pas->add($pa);
				}
			));

			return $this;
		}		
	}