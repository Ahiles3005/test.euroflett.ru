<?
    namespace Webprofy\Tools\Bitrix;

    use Webprofy\Tools\Bitrix\Getter\Data;
    use Webprofy\Tools\Bitrix\Getter\DataParser;
    use Webprofy\Tools\Bitrix\Getter\Arguments;

    class Getter{
    	// static
	        static function get($a){
	        	$dp = new DataParser($a);
	        	$data = new Data($dp);
	            return new Getter($data);
	        }

	        static function bit($a){
	        	$g = self::get($a);
	        	return $g->run();
	        }

	    // dynamic
	        // protected
		        protected
		            $eg, // EntityGetter
		            $data;

		        protected function getList(){
		            return $this->getEntityGetter()->getList();
		        }

	        // public

		        function __construct(Data $data = null){
		            $this->data = $data;
		        }

		        protected static $entityNames = null;
		        function getEntityGetterNames(){
		        	if(!self::$entityNames){
			        	$names = array();
			        	foreach(scandir(__DIR__.'/getter/iblock/') as $name){
			        		if(in_array($name, array(
			        			'.', '..', 'element.php'
			        		))){
			        			continue;
			        		}

			        		$names[] = substr($name, 0, strlen($name) - 4);
			        	}
		        		self::$entityNames = $names;
		        	}

		        	return self::$entityNames;
		        }

		        function getPagesCount(){
		            return $this->getList()->NavPageCount;
		        }

		        function getPageNumber(){
		            return $this->getList()->NavPageNomer;
		        }

		        function getItemsCount(){
		            return $this->getList()->SelectedRowsCount();
		        }
		        
		        function getEntityGetter(){
		            if(!empty($this->eg)){
		                return $this->eg;
		            }

					foreach($this->getEntityGetterNames() as $name){
		            	$class = 'Webprofy\Tools\Bitrix\Getter\IBlock\\'.$name;
		            	$eg = new $class();
		                if($eg
		                	->setData($this->data)
		                	->checkData()
		                ){
		                    $this->eg = $eg;
		                    return $eg;
		                }
		            }
		            return null;
		        }

		        function run($reset = false){
		        	$eg = $this->getEntityGetter();
		        	if(!$eg){
		        		throw new \Exception('Bit of "'.$this->data->get('of').'" not found.');
		        	}
		        	if($value = $this->data->get('value')){
		        		return $eg->getValue($value);
		        	}
		            return $eg
		        		->setArguments(new Arguments())
		        		->setGetter($this)
		        		->run($reset);
		        }

		        function one($callback){
		        	$this->data->setStep($callback, 'one');
		        	return $this->run(true);
		        }

		        function each($callback){
		        	$this->data->setStep($callback, 'each');
		        	return $this->run(true);
		        }

		        function map($callback){
		        	$this->data->setStep($callback, 'map');
		        	return $this->run(true);
		        }

		        // Iterator

				function current(){
					return $this->getEntityGetter()->current();
				}
			    function rewind(){
					return $this->getEntityGetter()->rewind();
			    }

			    function key(){
					return $this->getEntityGetter()->key();
			    }
			    function next(){
					return $this->getEntityGetter()->next();
			    }
			    function valid(){
					return $this->getEntityGetter()->valid();
			    }
    }