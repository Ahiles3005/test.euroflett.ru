<?
	namespace Webprofy\Tools\Form\Fields;

    use Webprofy\Tools\Form\Field;

	class FileField extends Field{
		protected $template = '<input type="file" name="%NAME"/>';
		function getValue(){
			return $_FILES[$this->name];
		}
	}