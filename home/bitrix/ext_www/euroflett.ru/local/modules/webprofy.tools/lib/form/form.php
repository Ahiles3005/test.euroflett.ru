<?
	namespace Webprofy\Tools\Form;

	
	class Form{
		private
			$name,
			$template = '
				<form action="/ajax.php" method="POST" enctype="multipart/form-data" class="js-form">
					<input type="hidden" name="act" value="%NAME"/>
					<input type="hidden" name="confirm" value="1"/>
					%FIELDS
					<input type="submit" name="send" value="Отправить"/>
				</form>
			';

		protected
			$fields = array();

		function __construct($name = null){
			if($name === null){
				$name = get_class($this);
			}
			$this->name = $name;
		}

		function getName(){
			return $this->name;
		}

		function addField(Field $field){
			$this->fields[$field->getName()] = $field;
		}

		function addFields(array $fields){
			foreach($fields as $field){
				$this->addField($field);
			}
		}

		function checkFields(){
			$ok = true;
			foreach($this->fields as $field){
				if(!$field->checkValue()){
					$ok = false;
				}
			}
			return $ok;
		}

		function getField($name){
			return empty($this->fields[$name]) ? null : $this->fields[$name];
		}

		function getFieldsValues(){
			$values = array();
			foreach($this->fields as $field){
				$values[$field->getName()] = $field->getValue();
			}
			return $values;
		}

		function getBadFieldsInfo(){
			$result = array();
			foreach($this->fields as $field){
				if($field->isBad()){
					$result[] = $field->getBadInfo();
				}
			}
			return $result;
		}

		private static $jsShown = false;

		function html(){
			$fields = '';

			$replace = array(
				'%FIELDS' => '',
				'%NAME' => $this->name,
			);
			foreach($this->fields as $field){
				$html = $field->html();
				$replace['%FIELDS'] .= $html;
				$replace['%FIELD_'.$field->getName()] = $html;
			}
			return strtr($this->template, $replace);
		}

		function setTemplate($template){
			$this->template = $template;	
		}

		function execute(){/* ... */}
	}