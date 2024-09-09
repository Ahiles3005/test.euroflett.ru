<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleOrderProps extends EntityGetter{
        protected
            $names = array(
                'sop',
                'sale-order-props',
                'sale-order-prop',
            ),
            $class = 'CSaleOrderProps',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }