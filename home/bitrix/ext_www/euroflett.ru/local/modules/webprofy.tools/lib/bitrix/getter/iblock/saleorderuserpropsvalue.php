<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;
    use CSaleBasket;

    class SaleOrderUserPropsValue extends EntityGetter{
        protected
            $names = array(
                'order-user-prop-values',
                'order-user-prop-value',
                'oup',
            ),
            $class = 'CSaleOrderUserPropsValue',
            $nextMethod = 'Fetch',
            $modules = array('sale');

    }