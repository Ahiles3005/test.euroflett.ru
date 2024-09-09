<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;
    use CSaleBasket;

    class SaleOrderUserProps extends EntityGetter{
        protected
            $names = array(
                'order-user-props',
                'order-user-prop',
                'oup',
            ),
            $class = 'CSaleOrderUserProps',
            $nextMethod = 'Fetch',
            $modules = array('sale');

    }