<?

    namespace Webprofy\Tools\Factory\Form;

    use Webprofy\Tools\Form\Form;
    use Webprofy\Tools\Form\Fields\TextField;
    use CIBlockElement;
    use CModule;

    class CallbackForm extends Form{
        function __construct(){
            parent::__construct('callback-form');

            $field = new TextField('name', true);
            $this->addField($field);

            $field = new TextField('phone', true);
            $field->setTemplate('<input type="text" class="mask-phone" name="%NAME" value="%VALUE"/>');
            $this->addField($field);

            $this->setTemplate('
                <form
                    id="faq_form"
                    action="/ajax.php"
                    method="POST"
                    enctype="multipart/form-data"
                    class="js-form"
                    data-onsuccess="$.fancybox.close()"
                >
                    <input type="hidden" name="act" value="%NAME"/>
                    <input type="hidden" name="confirm" value="1"/>

                    <table>
                        <tr>
                            <td>Ваше имя:</td>
                        </tr>
                        <tr>
                            <td>%FIELD_name</td>
                        </tr>
                        <tr>
                            <td>Телефон:</td>
                        </tr>
                        <tr>
                            <td>%FIELD_phone</td>
                        </tr>
                    </table>
                    <input type="submit" value="Отправить">
                </form>
            ');
        }

        function execute($f){
            CModule::IncludeModule('iblock');
            $e = new CIBlockElement();
            $fields = array(
                'IBLOCK_ID' => 4,
                'NAME' => $f['name'],
                'PROPERTY_VALUES' => array(
                    'PHONE' => $f['phone'],
                    'FIO' => $f['name'],
                )
            );
            $e->Add($fields);
        }
    }