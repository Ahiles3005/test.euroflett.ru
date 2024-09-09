<?php

	namespace Webprofy\Tools\Bitrix\Attribute;

	use Webprofy\Tools\Bitrix\Attribute\Attributes;
	use Webprofy\Tools\Bitrix\Attribute\Attribute;
	use Webprofy\Tools\Bitrix\IBlock\IBlocks;
	
	class Value{
		protected
			$many,
			$other,
			$isOtherIBlock_,

			$canMany,
			$canOther,

			$values,
			$otherValues,
			$type = 'string',
			$limit;

		function setType($type){
			$this->type = $type;
			return $this;
		}

		function isOtherIBlock(){
			return $this->isOtherIBlock_;
		}

		function isOther(){
			return $this->other;
		}

		function getType(){
			return $this->type;
		}

		function set($values){
			$this->values = $values;
			return $this;
		}

		function setFromOtherIBlocks(IBlocks $otherIBlocks){
			
			if(!$this->isOtherIBlock()){
				return $this;
			}

			$this->otherValues = array();

			foreach($this->values as $value){
				foreach($otherIBlocks as $otherIBlock){
					if($otherIBlock->getIndex() != $value['iblock']['index']){
						continue;
					}

					$attribute = Attribute::generate(
						$value['value'],
						$otherIBlock
					);


					$this->otherValues[] = $otherIBlock
						->getCurrentEntity()
						->get($attribute);
				}
			}
			return $this;
		}


		function getOtherIBlockAttributes($otherIBlock){
			$as = new Attributes($otherIBlock);
			if(!$this->isOtherIBlock()){
				return $as;
			}

			foreach($this->values as $value){
				if($otherIBlock->getIndex() != $value['iblock']['index']){
					continue;
				}

				$attribute = Attribute::generate(
					$value['value'],
					$otherIBlock
				);

				$as->add($attribute);
			}
			return $as;
		}

		function getAttributes($iblock){
			$attributes = new Attributes($iblock);
			if(!$this->canOther || !$this->other){
				return $attributes;
			}

			foreach($this->values as $value){
				$attributes->add(
					Attribute::generate($value, $iblock)
				);
			}
			return $attributes;
		}

		function get($element = null){
			$values = array();
			if($this->isOtherIBlock()){
				$all = $this->otherValues;
			}
			else{
				$all = $this->values;
			}

			foreach($all as $value){
				$values[] = $this->parseOne($value, $element);
			}

			if(!$this->many && !$this->limit){
				return $values[0];
			}

			return $values;
		}

		protected function parseOne($value, $element = null){
			if($this->canOther && $this->other && $element){
				return $element->get(
					$value['type'],
					$value['id']
				);
			}
			return $value;
		}

		function setLimit($limit){
			$this->limit = $limit;
			return $this;
		}
/*
		function setMany($many){
			$this->many = $many;
			return $this;
		}

		function setOther($other){
			$this->other = $other;
			return $this;
		}
*/
		function setJson($info){
			$this->values = $info['values'];
			$this->type = $info['type'];
			$this->many = $info['many'];
			$this->other = $info['other'];
			$this->isOtherIBlock_ = $info['otherIBlock'];
		}

		function setCanOther($canOther){
			$this->canOther = $canOther;
			return $this;
		}

		function setCanMany($canMany){
			$this->canMany = $canMany;
			return $this;
		}

		function getJson(){
			return array(
				'canMany' => $this->canMany,
				'canOther' => $this->canOther,
				'_many' => $this->many,
				'_other' => $this->other,
				'limit' => $this->limit,
				'values' => $this->values,
				'type' => $this->type
			);
		}
	}