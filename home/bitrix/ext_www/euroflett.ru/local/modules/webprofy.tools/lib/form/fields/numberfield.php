<?
	namespace Webprofy\Tools\Form\Fields;

    use Webprofy\Tools\Form\Field;

	class NumberField extends Field{
		public function check($value){
			$this->value = intval($value);
			return true;
		}
	}