<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class ListValue extends EntityGetter{
        protected
            $names = array(
                'lv',
                'list-values',
                'list-value'
            ),
            $class = 'CIBlockPropertyEnum',
            $args = array(
                'sort',
                'filter'
            );
    }