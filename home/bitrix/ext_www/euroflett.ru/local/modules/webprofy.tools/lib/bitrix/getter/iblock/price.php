<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class Price extends EntityGetter{
        protected
            $names = array(
                'pr',
                'price',
                'prices'
            ),
            $class = 'CPrice',
            $nextMethod = 'Fetch',
            $modules = array('catalog', 'iblock'),
            $args = array(
                'sort',
                'filter',
                'group',
                'nav',
                'select',
            );
    }