<?
	namespace Webprofy\Form\Fields;

	use Webprofy\Form\Field;

	class TextField extends Field{
		private $pattern = null;

		public function addPattern($pattern){
			$this->pattern = $pattern;
			return $this;
		}

		public function check($value){
			$value = htmlspecialchars(trim($value));
			if(
				$this->pattern !== null &&
				!preg_match($this->pattern, $value)
			){
				return false;
			}
			$this->value = $value;
			return true;
		}


	}