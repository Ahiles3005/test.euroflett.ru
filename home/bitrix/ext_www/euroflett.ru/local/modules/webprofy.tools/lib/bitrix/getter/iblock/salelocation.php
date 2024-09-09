<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleLocation extends EntityGetter{
        protected
            $names = array(
                'sale-location',
                'sale-locations',
            ),
            $class = 'CSaleLocation',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }