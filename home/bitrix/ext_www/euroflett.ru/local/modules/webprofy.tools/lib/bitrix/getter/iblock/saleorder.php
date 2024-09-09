<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleOrder extends EntityGetter{
        protected
            $names = array(
                'order',
                'orders',
            ),
            $class = 'CSaleOrder',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }