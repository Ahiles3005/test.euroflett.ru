<?
    namespace Webprofy\Tools\Bitrix\Getter;

    use Webprofy\Tools\Bitrix\Getter\DataParser;
    use Webprofy\Tools\Functions as F;

    class Data{
        protected
            $parser,
            $map,
            $outputContainer,
            $step;

        function __construct(DataParser $parser = null){
            $this->parser = $parser;
        }

        function _log(){
            $this->parser->_log();
        }

        function getStep(){
            if(!empty($this->step)){
                return $this->step;
            }

            foreach(array(
                'mapa',
                'map',
                'one',
                'each',
            ) as $i){
                $step = $this->get($i);
                if(is_callable($step) || is_string($step)){
                    $this->setStep($step, $i);
                    return $this->step;
                }
            }

            $this->setStep(function($event){
                return $event->allFields();
            }, 'map');
            return $this->step;
        }


        function resetStep(){
            $this->step = null;
            return $this;
        }

        function setStep($step, $map = 'one'){
            if(!is_string($step) && is_callable($step)){
                $this->step = $step;
                $this->map = $map;
                return $this;
            }

            if(!is_string($step)){
                $this->step = null;
                return $this;
            }


            $this->map = $map;
            
            if($step == 'log'){
                $this->step = function($event){
                    $f = $event->allFields();
                    F::log($f);
                    return $f;
                };
                return $this;
            }

            $this->step = function($event) use ($step){
                return $event->byNames($step);
            };
            return $this;
        }

        function getMap(){
            return $this->map;
        }

        function setArgumentType($argumentType){
            $this->argumentType = $argumentType;
            return $this;
        }

        function get($index){
            return $this->parser->get($index);
        }

        function set($index, $value){
            $this->parser->set($index, $value);
            return $this;
        }

        function checkArgumentType($argumentType){
            return $this->argumentType == $argumentType;
        }

        function hasSelect(){
            return !empty($this->select);
        }

        function getListArguments($args){
            $result = array();
            foreach($args as $arg){
                $value = $this->parser->get($arg);
                if(substr($arg, 0, 1) == '&'){
                    $result[] = &$value;
                }
                else{
                    $result[] = $value;
                }
            }
            return $result;
        }

        protected $output = null;

        function setOutputContainer($outputContainer){
            $this->outputContainer = $outputContainer;
        }

        function setOutput($output){
            $this->output = $output;
            return $this;
        }

        function addOutput($output){
            if(empty($this->outputContainer) || !$this->get('object')){
                $this->output[] = $output;
            }
            else{
                $this->outputContainer->add($output);
            }
            return $this;
        }

        function getOutput(){
            if(!empty($this->outputContainer) && $this->get('object')){
                return $this->outputContainer;
            }

            if($this->map != 'mapa'){
                return $this->output;
            }

            $result = array();
            foreach($this->output as $a){
                @list($i, $v) = $a;
                $result[$i] = $v;
            }

            return $result;
        }
    }