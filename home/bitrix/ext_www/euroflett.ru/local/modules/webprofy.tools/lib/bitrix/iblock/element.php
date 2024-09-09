<?
	namespace Webprofy\Tools\Bitrix\IBlock;

	use Webprofy\Tools\Bitrix\Getter;
	use Webprofy\Tools\Bitrix\IBlock\IBlock;
	use Webprofy\Tools\Bitrix\IBlockEntity;
	use Webprofy\Tools\Bitrix\Attribute\Attribute;
	use Webprofy\Tools\Bitrix\Attribute\Attributes;
	use Webprofy\Tools\Bitrix\Attribute\FieldAttributes;
	use Webprofy\Tools\Bitrix\Attribute\PropertyAttributes;
	use Webprofy\Tools\Bitrix\Attribute\PriceAttributes;
	use \CIBlockElement;
	use \WP;


	class Element extends IBlockEntity{
		protected function createData(){
			$iblock = Getter::bit(array(
				'of' => 'element',
				'sel' => 'IBLOCK_ID',
				'f' => array(
					'ID' => $this->id
				),
				'one' => 'f.IBLOCK_ID'
			));

			return Getter::bit(array(
				'of' => 'element',
				'f' => array(
					'ID' => $this->id,
					'iblock' => $iblock
				),
				'one' => function($d, $f, $p){
					return array(
						'f' => $f,
						'p' => $p
					);
				}
			));
		}

		static function add($data){
			CModule::IncludeModule('iblock');
			$e = new CIBlockElement();
			$properties = array();
			if(isset($data['p'])){
				if(is_string($data['p'])){
					$data['p'] = self::getListStringToArray($data['p']);
				}
				self::replaceShortenIndeces($data['p']);
				foreach($data['p'] as $name => $value){
					list($name, $type, $other1) = explode(':', $name);
					switch($type){
						case 'html':
						case 'text':
							$value = array(
								'VALUE' => array(
									'TYPE' => strtoupper($type),
									'TEXT' => $value
								)
							);
							break;

						case 'file':
							if(is_array($value) && isset($value['tmp_name'])){
								break;
							}
							$value = array(
								'name' => $other1,
								'tmp_name' => $value,
							);
							break;
					}
					$properties[$name] = $value;
				}
			}
			$fields = array(
				'MODIFIED_BY' => 1,
				'IBLOCK_ID' => 57,
				'ACTIVE' => 'N',
				'CODE' => 'random_'.mt_rand(0, 10000),
				'NAME' => '(без названия)',
				'PROPERTY_VALUES' => $properties
			);
			if(isset($data['f'])){
				if(is_string($data['f'])){
					$data['f'] = self::getListStringToArray($data['f']);
				}
				self::replaceShortenIndeces($data['f']);
				$fields = array_merge($fields, $data['f']);
			}
			if($data['debug']){
				WP::log($fields);
			}
			return $e->Add($fields);
		}

		function f($index){
			return parent::f('f', $index);
		}

		function p($index){
			return parent::f('p', $index, 'VALUE');
		}

		function get($type, $index = null){
			if($type instanceof Attribute){
				$attribute = $type;
			}
			else{
				if($index == null){
					list(
						$type,
						$index
					) = explode('.', $type);
				}

				$attribute = Attribute::generate(array(
					'type' => $type,
					'id' => $index
				));
			}
	
			return $attribute->getElementValue($this);
		}

		function getUpdateValues(Attribute $attribute, $index = null){
			$before = $this->get($attribute);
			$action = $attribute->getAction();
			$value = $action->getValue();
			
			$after = $action->run(
				$before,
				$value->get($this),
				$this
			);

			$result = array(
				'before' => $before,
				'after' => $after
			);

			if($index){
				return $result[$index];
			}

			return $result;
		}

		function setAttributesValues(Attributes $attributes = null){
			$results = array();
			$list = array();
			foreach($attributes as $attribute){
				$values = $this->getUpdateValues($attribute);
				$result = $attribute->update($this, $values['after']);
				if($result['list']){
					$list[$result['code']] = $result['value'];
				}
				if($result[''])
				$results[] = $result;
			}

			if(!empty($list)){
				\WP::log($list);
				//CIBlockElement::Update($this->f('ID'), $list);
			}
		}

		function getAttributes($type){
			if(!$this->iblock){
				return null;
			}
			
			switch($type){
				case 'field':
				case 'f':
					$a = new FieldAttributes($this->iblock);
					break;

				case 'property':
				case 'p':
					$a = new PropertyAttributes($this->iblock);
					break;

				case 'price':
				case 'pr':
					$a = new PriceAttributes($this->iblock);
					break;

				case 'all':
				case 'a':
					$as = new Attributes($this->iblock);
					return $as
						->extend($this->getAttributes('f'))
						->extend($this->getAttributes('p'))
						->extend($this->getAttributes('pr'));

				default:
					return null;
			}

			return $a->fill();
		}
	}