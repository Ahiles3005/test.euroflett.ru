<?

    namespace Webprofy\Tools\Factory;

    use Webprofy\Tools\Form\Forms as _Forms;
    use Webprofy\Tools\Factory\Form\CallbackForm;

    class Forms extends _Forms{
        function __construct(){
            $this->addForms(array(
                new CallbackForm()
            ));
        }
    }