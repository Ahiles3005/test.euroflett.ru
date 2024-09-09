<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;
    use Webprofy\Tools\Bitrix\IBlock\IBlock as IBlock_;

    class IBlock extends EntityGetter{
        protected
            $names = array(
                'ib',
                'iblock',
                'iblocks'
            ),
            $class = 'CIBlock',
            $args = array(
                'sort',
                'filter',
                'group',
                'nav',
                'select'
            );

        function modifyArguments(){
            $arguments = $this->arguments;

            $f = $arguments->get('f');

            if(!$this->data->get('object')){
                return $this;
            }

            $object = new IBlock_($f['ID']);
            $object->setData($f);

            $arguments
                ->set($object, 1, 'o')
                ->remove(2);

            return $this;
        }
    }