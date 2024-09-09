<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SalePaySystem extends EntityGetter{
        protected
            $names = array(
                'sale-paysystem',
            ),
            $class = 'CSalePaySystem',
            $nextMethod = 'Fetch',
            $modules = array('sale');
    }