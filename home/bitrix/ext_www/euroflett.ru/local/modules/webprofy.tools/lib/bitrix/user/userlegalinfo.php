<?
	namespace Webprofy\Tools\Bitrix\User;

	use Webprofy\Tools\Bitrix\Getter;
	use Webprofy\Tools\Functions as F;
	use CUser;
	use CSaleOrderUserProps;
	use CSaleOrderUserPropsValue;

	class UserLegalInfo{

		static $personalFields = array(
			'EMAIL',
			'PHONE',
			'CONTACT_PERSON',

			'F_NAME',
			'F_EMAIL',
			'F_PHONE'
		);

		protected
			$id,
			$profile,
			$propertyValues,
			$typesData,
			$fieldsInfo = array(
				'user' => array(
					'CONTACT_PERSON' => 'name',
					'EMAIL' => 'mail',
					'PHONE' => 'phone',

					'F_NAME' => 'name',
					'F_EMAIL' => 'mail',
					'F_PHONE' => 'phone',
				),
				'address' => array(
					'LOCATION' => 'location',
					'ADDRESS' => 'address',

					'F_LOCATION' => 'location',
					'F_ADDRESS' => 'address',
				)
			);

		function __construct($id = null){
			if(!$id){
				$id = CUser::GetID();
			}
			$this->id = $id;
		}

		function loadProfile(){
			$profile = Getter::bit(array(
				'of' => 'CSaleOrderUserProps',
				'f' => array(
					'USER_ID' => $this->id
				),
				'one' => 'f'
			));

			if($profile['NAME'] !== 'ONLY_PROFILE'){
				return null;
			}
		}

		function setProfile($personTypeId = null){
			$this->getProfile($personTypeId, true);
		}


		function getProfile($personTypeId = null, $forceUpdate = false){
			if(!$forceUpdate && !empty($this->profile)){
				return $this->profile;
			}

			$profiles = Getter::bit(array(
				'of' => 'CSaleOrderUserProps',
				'f' => array(
					'USER_ID' => $this->id
				),
				'map' => 'f'
			));

			$amount = $profiles ? count($profiles) : 0;

			if(
				$amount > 1 ||
				$amount == 0 ||
				(
					$personTypeId &&
					$profiles[0]['PERSON_TYPE_ID'] != $personTypeId
				)
			){
				foreach($profiles as $i => $profile){
					CSaleOrderUserProps::Delete($profile['ID']);
				}
				$profiles = null;
			}

			if(!empty($profiles)){
				$this->profile = $profiles[0];
				return $this->profile;
			}

			if(empty($personTypeId)){
				$personTypeId = Getter::bit(array(
					'of' => 'CSalePersonType',
					'where' => array(
						'LID' => SITE_ID,
						'ACTIVE' => 'Y'
					),
					'one' => 'f.ID',
				));
			}

			$fields = array(
				'PERSON_TYPE_ID' => $personTypeId,
				'USER_ID' => $this->id,
				'NAME' => 'ONLY_PROFILE'
			);

			$id = CSaleOrderUserProps::Add($fields);
			if(!$id){
				return false;
			}

			$fields['ID'] = $id;
			$this->profile = $fields;
			return $this->profile;
		}

		function editValue($propId, $value){
			$profile = $this->getProfile();

			$info = array(
				'USER_PROPS_ID' => $profile['ID'],
				'ORDER_PROPS_ID' => $propId
			);

			$propertyValue = Getter::bit(array(
				'of' => 'CSaleOrderUserPropsValue',
				'where' => $info,
				'one' => 'f'
			));

			$info = array_merge($info, array(
				'NAME' => 'NAME',
				'VALUE' => $value
			));

			if(empty($propertyValue)){
				return CSaleOrderUserPropsValue::Add($info);
			}
			return CSaleOrderUserPropsValue::Update($propertyValue['ID'], $info);
		}

		function getPropertyValueById($id){
			$values = $this->getPropertyValues();
			foreach($values as $value){
				if($value['ORDER_PROPS_ID'] != $id){
					continue;
				}
				return $value['VALUE'];
			}
			return null;
		}

		function getPropertyValues(){
			if(!empty($this->propertyValues)){
				return $this->propertyValues;
			}
			$profile = $this->getProfile();
			if(!$profile){
				return false;
			}
			$this->propertyValues = Getter::bit(array(
				'of' => 'CSaleOrderUserPropsValue',
				'where' => array(
					'USER_PROPS_ID' => $profile['ID'],
				),
				'map' => 'f'
			));

			return $this->propertyValues;
		}

		function getFilteredTypesData($type){
			$include = array();
			$exclude = array();

			switch($type){
				case 'user':
					$include = array_keys($this->fieldsInfo['user']);
					break;

				case 'address':
					$include = array_keys($this->fieldsInfo['address']);
					break;

				case 'other':
					$exclude = array_merge(
						array_keys($this->fieldsInfo['address']),
						array_keys($this->fieldsInfo['user'])
					);
					break;
			}

			$types = $this->getTypesData();

			if(!count($include) && !count($exclude)){
				return $types;
			}

			foreach($types as $i => $type){
				foreach($type['groups'] as $j => $group){
					foreach($group['props'] as $k => $prop){
						if(
							(count($include) && !in_array($prop['code'], $include)) ||
							(count($exclude) && in_array($prop['code'], $exclude))
						){
							unset($types[$i]['groups'][$j]['props'][$k]);
						}
					}
				}
			}
			return $types;
		}

		function getTypesData(){
			if(!empty($this->typesData)){
				return $this->typesData;
			}

			$me = $this;

			$this->typesData = Getter::bit(array(
				'of' => 'SalePersonType',
				'where' => array(
					'LID' => SITE_ID,
					'ACTIVE' => 'Y'
				),
				'map' => function($e, $type) use ($me){
					$profile = $me->getProfile();

					return array(
						'name' => $type['NAME'],
						'id' => $type['ID'],
						'checked' => (@$profile['PERSON_TYPE_ID'] == $type['ID']),
						'groups' => Getter::bit(array(
							'of' => 'SaleOrderPropsGroup',
							'where' => array(
								'PERSON_TYPE_ID' => $type['ID'],
							),
							'map' => function($e, $group) use ($me){
								return array(
									'props' => Getter::bit(array(
										'of' => 'CSaleOrderProps',
										'where' => array(
											'PERSON_TYPE_ID' => $group['PERSON_TYPE_ID'],
											'PROPS_GROUP_ID' => $group['ID']
										),
										'map' => function($e, $prop) use ($me){
											return array(
												'id' => $prop['ID'],
												'name' => $prop['NAME'],
												'type' => $prop['TYPE'],
												'code' => $prop['CODE'],
												'value' => $me->getPropertyValueById($prop['ID'])
											);
										}
									)),
									'name' => $group['NAME']
								);
							}
						))
					);
				}
			));
	
			return $this->typesData;
		}
	}