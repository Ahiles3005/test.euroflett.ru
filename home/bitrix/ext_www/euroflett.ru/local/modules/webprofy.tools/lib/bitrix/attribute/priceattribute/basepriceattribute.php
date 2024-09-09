<?

	namespace Webprofy\Tools\Bitrix\Attribute\PriceAttribute;
	use Webprofy\Tools\Bitrix\Attribute\PriceAttribute;

	class BasePriceAttribute extends PriceAttribute{
		function getCode(){
			return $this->id;
		}

		function getName(){
			return 'Базовая цена';
		}

		function getActionCode(){
			return 'CATALOG_PRICE_1';
		}

		function update($element, $value){
			
		}

		function getElementValue($element){
			$price = \CPrice::GetBasePrice($element->f('ID'));
			return $price['PRICE'];
		}
	}