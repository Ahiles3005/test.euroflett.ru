<?

	namespace Webprofy\Tools\Bitrix\Attribute\FieldAttribute;
	use Webprofy\Tools\Bitrix\Attribute\FieldAttribute;
	
	class IdAttribute extends FieldAttribute{
		function __construct(){
			parent::__construct('ID');
		}

		function getName(){
			return 'ID';
		}

		function getValueType(){
			return 'number';
		}
	}