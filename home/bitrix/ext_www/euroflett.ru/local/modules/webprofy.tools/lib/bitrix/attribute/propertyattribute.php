<?php

	namespace Webprofy\Tools\Bitrix\Attribute;

	use Webprofy\Tools\Bitrix\Getter;
	
	class PropertyAttribute extends Attribute{
		function createData(){
			return	Getter::bit(array(
				'of' => 'properties',
				'f' => array(
					'ID' => $this->id
				),
				'one' => 'f'
			));
		}

		static function getType(){
			return 'property';
		}

		private static $valueTypesByCode = array(
			'number' => array(
				'N',
				'F',
				'S:UserID',
				'S:FileMan',
				'N:SASDCheckboxNum',
				'N:Sequence',
			),
			'list' => array(
				'L',
				'G',
				'E',
				'G:SectionAuto',
				'N:SASDSection',
				'S:TopicID',
				'E:SKU',
				'E:EList',
				'S:ElementXmlID',
				'E:EAutocomplete',
				'S:directory',
			)
		);

		function getElementValue($element){
			return $element->p($this->f('CODE'));
		}

		function getPropertyType(){
			$full = $this->f('PROPERTY_TYPE');
			if(strlen($this->f('USER_TYPE'))){
				$full .= ':'.$this->f('USER_TYPE');
			}
			return $full;
		}

		function getValueType(){
			$code = $this->getPropertyType();
			foreach(self::$valueTypesByCode as $type => $codes){
				if(in_array($code, $codes)){
					return $type;
				}
			}
			return 'string';
		}

		function getActionCode(){
			return 'PROPERTY_'.$this->f('CODE');
		}

		function getCode(){
			$code = $this->f('CODE');
			if(!$code){
				return $this->id;
			}
			return $code;
		}

		function getElementValueType(){
			return 'property';
		}

		function getListValues($only = null){
			$type = $this->getPropertyType();

			$listValues = null;

			switch($type){
				case 'L':
					$listValues = Getter::bit(array(
						'of' => 'list-values',
						'f' => array(
							'PROPERTY_ID' => $this->f('ID')
						),
						'map' => function($d, $f) use (&$only){
							if($only && !in_array($f['ID'], $only)){
								return null;
							}

							return array(
								'id' => $f['ID'],
								'name' => $f['VALUE']
							);
						}
					));
					break;

				case 'S:directory':
					$DB = $GLOBALS['DB'];
					$fields = array(
						'name' => 'UF_NAME',
						// 'id' => 'ID',
						'id' => 'UF_XML_ID'
					);

					$r = $DB->Query('
						SELECT '.implode(',', array_values($fields)).'
						FROM `'.$this->f('USER_TYPE_SETTINGS', 'TABLE_NAME').'`
					');

					$listValues = array();


					while($row = $r->Fetch()){
						$one = array();
						foreach($fields as $i => $j){
							$one[$i] = $row[$j];
						}
						// \WP::log($one['id']);
						if($only && !in_array($one['id'], $only)){
							continue;
						}
						$listValues[] = $one;
					}
					return $listValues;
			}

			return $listValues;
		}
	}