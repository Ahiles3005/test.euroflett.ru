<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;
    use CIBlockType;

    class IBlockType extends EntityGetter{
        protected
            $names = array(
                'ibt',
                'iblock-type',
                'iblock-types'
            ),
            $class = 'CIBlockType',
            $args = array(
                'sort',
                'filter',
            );

        function modifyArguments(){
            $arguments = $this->arguments;
            $f = $arguments->get('f');
            $f = array_merge($f, CIBlockType::GetByIDLang($f["ID"], LANG));

            $arguments
                ->set($f, 1, 'f')
                ->remove(2);

            return $this;
        }
    }