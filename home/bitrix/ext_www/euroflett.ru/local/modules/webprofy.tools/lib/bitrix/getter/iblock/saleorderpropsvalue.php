<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleOrderPropsValue extends EntityGetter{
        protected
            $names = array(
                'sopv',
                'sale-order-prop-values',
                'sale-order-prop-value',
            ),
            $class = 'CSaleOrderPropsValue',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }