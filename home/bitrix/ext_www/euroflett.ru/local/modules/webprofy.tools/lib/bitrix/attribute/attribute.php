<?php

	namespace Webprofy\Tools\Bitrix\Attribute;
	
	use Webprofy\Tools\Bitrix\DataHolder;

	use Webprofy\Tools\Bitrix\Attribute\FieldAttribute;
	use Webprofy\Tools\Bitrix\Attribute\PropertyAttribute;

	use Webprofy\Tools\Bitrix\Attribute\Action\Action;
	use Webprofy\Tools\Bitrix\Attribute\Action\CompareActions;
	use Webprofy\Tools\Bitrix\Attribute\Action\UpdateActions;
	use Webprofy\Tools\Bitrix\Attribute\Action\UpdateAction;


	abstract class Attribute extends DataHolder{
		protected
			$compareActions,
			$updateActions,
			$iblock,
			$action;

		public
			$isForElementUpdate = true;

		function setIBlock($iblock){
			$this->iblock = $iblock;
			return $this;
		}

		static function generate($info, $iblock = null){
			if($iblock && $info['id']){
				$attribute = $iblock
					->getAttributes('all')
					->filter(
						'getId',
						$info['id'],
						true
					);
			}

			if(!$attribute){
				$class = self::getAttributeClassByType($info['type']);
				$attribute = new $class($info['id'], $iblock);
			}
			if(!empty($info['action'])){
				$attribute->setAction($info['action']);
			}
			return $attribute;
		}

		function getElementValue($element){
			return $element->f($this->f('CODE'));
		}

		static function getAttributeClassByType($type){
			foreach(array(
				'Webprofy\Tools\\Bitrix\\Attribute\\PriceAttribute',
				'Webprofy\Tools\\Bitrix\\Attribute\\FieldAttribute',
				'Webprofy\Tools\\Bitrix\\Attribute\\PropertyAttribute',
				'Webprofy\Tools\\Bitrix\\Attribute\\SectionFieldAttribute',
				'Webprofy\Tools\\Bitrix\\Attribute\\SectionUserAttribute',
			) as $class){
				if($class::getType() == $type){
					return $class;
				}
			}
			return null;
		}

		function __construct($id){
			parent::__construct($id);
		}

		static function getType(){
			return 'attribute';
		}

		function getCode(){
			return $this->f('CODE');
		}

		function getSelect(){
			return array(
				'code' => $this->getActionCode(),
				'name' => $this->getName(),
				'type' => $this->getType()
			);
		}

		function getJson(){
			return array(
				'id' => $this->getId(),
				'type' => $this->getType(),
				'name' => $this->getName(),
				'valueType' => $this->getValueType()
			);
		}

		function getActionCode($type = 'select'){
			return $this->f('CODE');
		}

		function getElementValueType(){
			return 'field';
		}

		function getAction(){
			return $this->action;
		}

		function isUpdater(){
			return ($this->getAction()) instanceof UpdateAction;
		}

		function getValueType(){
			if($this->getCode() == 'ID'){
				return 'number';
			}
			return 'string';
		}

		function getValue(){
			return $this->getAction()->getValue();
		}

		function update($element, $value){
			return array(
				'list' => true,
				'code' => $this->getActionCode('select'),
				'value' => $value
			);
		}

		function getListValues(){
			
		}

		function setAction($action){
			if($action instanceof Action){
				$this->action = $action;
				return $this;
			}

			$id = null;
			$info = null;

			if(is_string($action)){
				$id = $action;
			}
			elseif(is_array($action)){
				$info = $action;
				$id = $info['id'];
			}

			if($id){
				$this->action = $this
					->getActions('all')
						->filter('getId', $id)
							->first();

				if($info){
					$this->getAction()->getValue()->setJson($info['values']);
				}
			}
			
			return $this;
		}

		function getActions($type){
			$actions = null;

			switch($type){
				case 'compare':
					$actions = new CompareActions();
					break;
					
				case 'update':
					$actions = new UpdateActions();
					break;

				default:
					return $this
						->getActions('compare')
							->extend($this->getActions('update'));
			}

			return $actions
				->fill()
				->clearByAttribute($this);
		}

		function getName(){
			$name = $this->f('NAME');
			if(empty($name)){
				return $this->getCode();
			}
			return $name;
		}

		protected $additional = false;
		function setAdditional($additional){
			$this->additional = $additional;
		}
		function getAdditional(){
			return $this->additional;
		}
	}