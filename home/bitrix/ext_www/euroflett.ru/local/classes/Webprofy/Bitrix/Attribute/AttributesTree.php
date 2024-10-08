<?php

	namespace Webprofy\Bitrix\Attribute;


	use Webprofy\Bitrix\Attribute\Attribute;
	use Webprofy\Bitrix\Attribute\Attributes;
	use Webprofy\Bitrix\Attribute\GeneralAttributes;
	
	class AttributesTree extends GeneralAttributes{
		private $type;

		function setType($type){
			$this->type = $type;
		}

		static function generate($infos, $type, $iblock = null){
			$tree = new AttributesTree();
			$tree->setType($type);

			foreach($infos as $info){
				$element = null;

				if($info['condition'] == 'single'){
					$element = Attribute::generate($info['attribute'], $iblock);
				}
				else{
					$element = AttributesTree::generate($info['holders'], $info['condition'], $iblock);
				}

				$tree->add($element);
			}
			return $tree;
		}

		function getAttributes(){
			$result = new Attributes();
			$this->each(function($one) use (&$result){
				if($one instanceof Attribute){
					$result->add($one);
				}

				if($one instanceof AttributesTree){
					$result->extend($one->getAttributes());
				}
			});
			return $result;
		}

		function getSelectFields($clearLogic = false){
			$f = array();
			if(!$clearLogic){
				$f['LOGIC'] = strtoupper($this->type);
			}

			$this->each(function($one) use (&$f, $clearLogic){
				if($one instanceof Attribute){
					$value = $one->getValue()->get();
					$code = $one->getActionCode('filter');
					$one->getAction()->run($code, $value);
					if($clearLogic){
						$f[$code] = $value;
					}
					else{
						$f[] = array(
							$code => $value
						);
					}
				}

				if($one instanceof AttributesTree){
					if($clearLogic){
						$f = array_merge($f, $one->getSelectFields(true));
					}
					else{
						$f[] = $one->getSelectFields(false);
					}
				}
			});


			return $f;
		}
	}