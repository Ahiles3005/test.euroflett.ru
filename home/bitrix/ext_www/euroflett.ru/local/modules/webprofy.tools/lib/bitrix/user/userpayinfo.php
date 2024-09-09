<?

	namespace Webprofy\Tools\Bitrix\User;

	use Webprofy\Tools\Bitrix\Getter;

	class UserPayInfo{
		function __construct(){

		}

		function getDeliveryData(){
			return Getter::bit(array(
				'of' => 'CSaleDelivery',
				'where' => array(
					'LID' => SITE_ID,
					'ACTIVE' => 'Y'
				),
				'map' => function($e, $delivery){
					return $delivery;
				}
			));
		}

		function getPaysystemData(){
			return Getter::bit(array(
				'of' => 'CSalePaySystem',
				'where' => array(
					'ACTIVE' => 'Y',
					// 'PERSON_TYPE_ID' => 4,
					// 'CURRENCY' => 'RUB'
				),
				'map' => function($e, $paysystem){
					return $paysystem;
				}
			));
		}
	}