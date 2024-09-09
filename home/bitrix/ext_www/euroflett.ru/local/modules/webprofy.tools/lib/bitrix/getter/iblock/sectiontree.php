<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\IBlock\SectionGetter;
    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SectionTree extends Section{
        protected
            $names = array(
                'st',
                'section-tree',
                'sections-tree'
            ),
            $getListMethod = 'GetTreeList',
            $args = array(
                'filter',
                'select',
            );
    }