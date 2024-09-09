<?
    namespace Webprofy\Tools\Bitrix\Getter;

    use Webprofy\Tools\Bitrix\Getter;
    use Webprofy\Tools\Bitrix\Getter\Data;
    use Webprofy\Tools\Bitrix\Getter\Arguments;
    use Webprofy\Tools\Functions as F;

    abstract class EntityGetter{
        protected
            $names,
            $data,
            $fields,
            $class,
            $args = array(
                'sort',
                'filter',
                'group',
                'nav',
                'select',
            ),
            $getter,
            $list,
            $modules = array('iblock'),
            $nextMethod = 'GetNext',
            $nextMethodArguments = array(),
            $getListMethod = 'GetList',
            $objectClass,
            $arguments;

        function getOutputContainer(){
            return null;
        }

        function setGetter(Getter $getter = null){
            $this->getter = $getter;
            return $this;
        }


        function setData(Data $data = null){
            $this->data = $data;
            return $this;
        }

        function getObjectClass($iblock){
            return $this->objectClass;
        }

        function setArguments(Arguments $arguments = null){
            $this->arguments = $arguments;
            return $this;
        }

        function checkData(){
            return in_array(
                $this->data->get('of'),
                array_merge(
                    $this->names,
                    array(
                        $this->class,
                        substr($this->class, 1)
                    )
                )
            );
        }

        function modifyArguments(){
            return $this;
        }

        function setFields($fields){
            $this->fields = $fields;
            return $this;
        }

        function getFields(){
            return $this->fields;
        }

        function beforeGetList(){}

        function getList($reset = false){
            if(!$reset && !empty($this->list)){
                return $this->list;
            }

            foreach($this->modules as $module){
                \CModule::IncludeModule($module);
            }

            $this->beforeGetList();

            if($this->data->get('debug')){
                F::log(array(
                    get_class($this),
                    array(
                        $this->class,
                        $this->getListMethod
                    ),
                    $this->data->getListArguments($this->args)
                ), 'all');
            }

            $this->list = call_user_func_array(
                array(
                    $this->class,
                    $this->getListMethod
                ),
                $this->data->getListArguments($this->args)
            );

            return $this->list;
        }

        function updateState(){
            if($this->arguments->ending()){
                $this->arguments->end(false);
                return false;
            }
            $this->fields = call_user_func(
                array(
                    $this->getList(),
                    $this->nextMethod
                ),
                $this->nextMethodArguments
            );

            $this->arguments->set(
                $this->fields,
                1,
                'f'
            );
            return $this->fields ? true : false;
        }

        function makeStep(){
            $data = $this->data;
            $arguments = $this->arguments;
            $result = call_user_func_array(
                $data->getStep(),
                $arguments->forStep()
            );
            $arguments->setIndex(1, true);
            $map = $data->getMap();

            if($arguments->ending()){
                return;
            }

            switch($map){
                case 'one':
                    $data->setOutput($result);
                    $arguments->end();
                    break;

                case 'mapa':
                case 'map':
                case 'each':
                    if($arguments->skipping()){
                        $arguments->skip(false);
                        return;
                    }
                    switch($map){
                        case 'mapa':
                        case 'map':
                            if($result !== null){
                                $data->addOutput($result);
                            }
                            break;
                            
                        case 'each':
                            if($result === false){
                                $arguments->end();
                            }
                            break;
                    }
                    break;
            }
        }

        function run($reset = false){
            $list = $this->getList($reset);
            if($oc = $this->getOutputContainer()){
                $this->data->setOutputContainer($oc);
            }
            while($this->updateState()){
                $this
                    ->modifyArguments()
                    ->makeStep();
            }
            if($this->data->getMap() == 'each'){
                return $this->getter;
            }
            return $this->data->getOutput();
        }

    }