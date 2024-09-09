<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SaleUser extends EntityGetter{
        protected
            $names = array(
                'su',
                'sale-user',
                'sale-users'
            ),
            $class = 'CSaleUserAccount',
            $nextMethod = 'Fetch',
            $modules = array('sale', 'iblock'),
            $args = array(
                'sort',
                'filter',
                'group',
                'nav',
                'select',
            );
    }