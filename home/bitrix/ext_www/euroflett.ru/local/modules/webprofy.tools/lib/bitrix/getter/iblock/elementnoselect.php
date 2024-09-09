<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\IBlock\Element;

    class ElementNoSelect extends Element{
        protected
            $nextMethod = 'GetNextElement';

        function checkData(){
            return (
                parent::checkData() &&
                !count($this->data->get('select'))
            );
        }

        function modifyArguments(){
            $this
                ->arguments
                    ->set($this->fields->GetFields(), 1, 'f')
                    ->set($this->fields->GetProperties(), 2, 'p');

            return parent::modifyArguments();
        }
    }