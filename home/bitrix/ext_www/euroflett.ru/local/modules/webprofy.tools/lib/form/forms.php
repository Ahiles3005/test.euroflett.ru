<?
	namespace Webprofy\Tools\Form;

	class Forms{
		private
			$forms = array(),
			$form = null;

		function addForm(Form $form){
			$this->forms[$form->getName()] = $form;
			return $this;
		}

		function addForms(array $forms){
			foreach($forms as $form){
				$this->addForm($form);
			}
			return $this;
		}

		function get($name){
			return @$this->forms[$name];
		}

		function run(){
			if(isset($_POST['get_captcha'])){
				global $APPLICATION;
				return $APPLICATION->CaptchaGetCode();
			}
			if($this->runForm()){
				$result = array('ok' => 1);
			}
			else{
				$result = array('ok' => 0);
				if(($form = $this->form) !== null){
					$result['fields'] = $form->getBadFieldsInfo();
				}
			}
			return $result;
		}

		function runForm(){
			$type = $_POST['act'];
			if(!isset($this->forms[$type])){
				return false;
			}
			$this->form = $form = $this->forms[$type];
			if(!$form->checkFields()){
				return false;
			}
			if($form->execute($form->getFieldsValues()) === false){
				return false;
			}
			return true;
		}
	}

?>