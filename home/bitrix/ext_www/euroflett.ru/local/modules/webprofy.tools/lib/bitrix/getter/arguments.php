<?
    namespace Webprofy\Tools\Bitrix\Getter;

    use Webprofy\Tools\Bitrix\Getter\DataParser;

    class Arguments{
        protected
            $all = array(),
            $end = false,
            $skip = false,
            $index = 0;

        function set($value, $index, $name){
            $this->all[$name] = array(
                'value' => $value,
                'index' => $index,
                'name' => $name
            );
            return $this;
        }

        function remove($index){
            foreach($this->all as $i => $v){
                if($v['index'] == $index){
                    unset($this->all[$i]);
                    return;
                }
            }
        }

        function forStep(){
            $result = array($this);
            foreach($this->all as $one){
                $result[$one['index']] = $one['value'];
            }
        return $result;
        }

        function get($i, $j = null){
            $o = @$this->all[$i]['value'];
            if(!$j){
                return $o;
            }
            return @$o[$j];
        }

        function byNames($names){
            $values = array();
            foreach(DataParser::namesToArray($names) as $i => $v){
                if(!is_array($v)){
                    $value = $this->get($v);
                    if(is_string($i)){
                        $values[$i] = $value;
                    }
                    else{
                        $values[] = $value;
                    }
                    continue;
                }

                foreach($v as $j => $v_){
                    $value = $this->get($i, $v_);
                    if(is_string($j)){
                        $values[$j] = $value;
                    }
                    else{
                        $values[] = $value;
                    }
                }
            }

            switch(count($values)){
                case 0:
                    return null;

                case 1:
                    return $values[0];

                default:
                    return $values;
            }
        }

        function getIndex(){
            return $this->index;
        }

        function setIndex($index, $increase = null){
            if($increase !== null){
                $index = $this->index + $index * ($increase ? -1 : +1);
            }
            $this->index = $index;
            return $this;
        }

        function allFields(){
            $result = array();
            foreach($this->all as $arg){
                $result[$arg['name']] = &$arg['value'];
            }
            return $result;
        }


        function end($end = true){
            $this->end = $end;
            $this->index = 0;
            return $this;
        }

        function skip($skip = true){
            $this->skip = $skip;
            return $this;
        }

        function skipping(){
            return $this->skip;
        }

        function ending(){
            return $this->end;
        }

    }