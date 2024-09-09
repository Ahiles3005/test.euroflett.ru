<?php

	namespace Webprofy\Tools\Bitrix\Attribute;

	use Webprofy\Tools\Bitrix\Attribute\GeneralAttributes;
	
	class Attributes extends GeneralAttributes{
		function getJson(){
			$result = array();
			$this->each(function($attribute) use (&$result){
				$result[] = $attribute->getJson();
			});
			return $result;
		}

		function getByTypeAndId($type, $id){
			$attributes = new Attributes($this->iblock);
			$this->each(function($attribute) use ($type, $id, $attributes){
				if($attribute->getId() != $id || $attribute->getType() != $type){
					return;
				}
				$attributes->add($attribute);
			});
			return $attributes;
		}

		function fill(){
			return $this;
		}

		function getListValues(){
			return array();
		}
		
		function getSelectFields($clearLogic = false){
			$f = array();
			$this->each(function($attribute) use (&$f){
				$value = $attribute->getValue()->get();
				$code = $attribute->getActionCode('filter');
				$attribute->getAction()->run($code, $value);

				$f[$code] = $value;
			});
			return $f;
		}

		function getOtherIBlockAttributes($otherIBlock){
			$all = new self($this->iblock);
			if(!$otherIBlock){
				return $all;
			}
			$me = $this;

			foreach($this as $attribute){
				$action = $attribute->getAction();
				if(!$action){
					continue;
				}

				$value = $action->getValue();
				if(!$value || !$value->isOtherIBlock()){
					continue;
				}


				$all->extend($value->getOtherIBlockAttributes($otherIBlock)->each(function($a){
					$a->setAdditional(true);
				}));
			};

			return $all;
		}

		function getValuesAttributes(){
			$iblock = $this->iblock;
			$all = new self($this->iblock);
			$me = $this;

			$this->each(function($attribute) use ($me, $iblock, &$all){
				$action = $attribute->getAction()->getValue();
				if(!$value || !$value->isOther()){
					return;
				}

				$all->extend($value->getAttributes($iblock)->each(function($a){
					$a->setAdditional(true);
				}));

			});

			return $all;
		}
	}