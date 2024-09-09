<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class Subscription extends EntityGetter{
        protected
            $names = array(
                'subscription',
                'subscriptions',
            ),
            $class = 'CSubscription',
            $args = array(
                'sort',
                'filter',
                'nav'
            ),
            $nextMethod = 'Fetch',
            $modules = array('subscribe');
    }