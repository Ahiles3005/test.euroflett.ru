<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleOrderPropsGroup extends EntityGetter{
        protected
            $names = array(
                'sopg',
                'sale-order-prop-groups',
                'sale-order-prop-group',
            ),
            $class = 'CSaleOrderPropsGroup',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }