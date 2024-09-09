<?
	namespace Webprofy\Tools\Form\Fields;

	use Webprofy\Tools\Form\Field;

	class EmailField extends Field{
		public function check($value){
			$value = htmlspecialchars(trim($value));
			if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
				$this->setError('filter');
				return false;
			}
			$this->value = $value;
			return true;
		}
	}