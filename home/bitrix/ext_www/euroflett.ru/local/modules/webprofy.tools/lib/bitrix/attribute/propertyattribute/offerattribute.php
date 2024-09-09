<?

	namespace Webprofy\Tools\Bitrix\Attribute\PropertyAttribute;
	use Webprofy\Tools\Bitrix\Attribute\PropertyAttribute;

	class OfferAttribute extends PropertyAttribute{
		function getCode(){
			return $this->id;
		}

		function getName(){
			return 'Продукт';
		}

		function getValueType(){
			return 'number';
		}
	}