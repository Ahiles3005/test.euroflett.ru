<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleDelivery extends EntityGetter{
        protected
            $names = array(
                'sale-delivery',
            ),
            $class = 'CSaleDelivery',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }