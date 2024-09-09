<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class SalePersonType extends EntityGetter{
        protected
            $names = array(
                'spt',
                'sale-person-type',
                'sale-person-types',
            ),
            $class = 'CSalePersonType',
            $nextMethod = 'Fetch',
            $modules = array('sale');

            function getValue($index){
                switch($index){
                    case 'defaults':
                        return array(
                            'of' => $this->class,
                            'f' => array(
                                'LID' => SITE_ID,
                                'ACTIVE' => 'Y'
                            )
                        );
                }
            }
    }