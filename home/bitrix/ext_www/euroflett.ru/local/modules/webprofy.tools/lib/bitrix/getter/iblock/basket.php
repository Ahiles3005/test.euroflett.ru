<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;
    use CSaleBasket;

    class Basket extends EntityGetter{
        protected
            $names = array(
                'b',
                'basket',
                'baskets'
            ),
            $class = 'CSaleBasket',
            $nextMethod = 'Fetch',
            $modules = array('sale', 'iblock'),
            $args = array(
                'sort',
                'filter',
                'group',
                'nav',
                'select',
            );

            function getValue($index){
                switch($index){
                    case 'user':
                    case 'userid':
                        return CSaleBasket::GetBasketUserID();
                }
            }
    }