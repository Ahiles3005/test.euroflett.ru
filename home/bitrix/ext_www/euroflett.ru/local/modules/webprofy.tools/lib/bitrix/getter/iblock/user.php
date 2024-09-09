<?
    namespace Webprofy\Tools\Bitrix\Getter\IBlock;

    use Webprofy\Tools\Bitrix\Getter\EntityGetter;

    class User extends EntityGetter{
        protected
            $names = array(
                'u',
                'user',
                'users'
            ),
            $class = 'CUser',
            $args = array(
                '&by',
                '&order',
                'filter',
                'params'
            );

        function beforeGetList(){
            $data = $this->data;
            if($sort = $data->get('sort')){
                $data
                    ->set('&by', $sort)
                    ->set('&order', reset($sort));
            }

            if(!$data->get('&by')){
                $data->set('&by', array(
                    'sort' => 'asc'
                ));
            }

            if(!$data->get('&order')){
                $data->set('&order', 'asc');
            }
        }
    }