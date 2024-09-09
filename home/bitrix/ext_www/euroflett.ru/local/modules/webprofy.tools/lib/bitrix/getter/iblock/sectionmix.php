<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\IBlock\SectionGetter;
    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    use Webprofy\Tools\Bitrix\IBlock\Section as Section_;
    use Webprofy\Tools\Bitrix\IBlock\Element as Element_;

    class SectionMix extends Section{
        protected
            $names = array(
                'sm',
                'section-mix',
                'sections-mix',
            ),
            $getListMethod = 'GetMixedList',
            $args = array(
                'sort',
                'filter',
                'count',
                'select',
            );

        function modifyArguments(){
            $f = $this->fields;

            if(!$this->data->get('object')){
                return $this;
            }

            $object = null;
            $type = null;

            switch($f['TYPE']){
                case 'S':
                    $type = 's';
                    $object = new Section_($f['ID']);
                    $object->setData(array(
                        'f' => $f,
                        'u' => null
                    ));
                    break;

                case 'E':
                    $type = 'e';
                    $object = new Element_($f['ID']);
                    $object->setData(array(
                        'f' => $f,
                        'p' => null
                    ));
                    break;
            }

            if($object){
                $this->arguments
                    ->set($object, 1, 'o')
                    ->set($type, 2, 't');
            }

            return $this;
        }
    }