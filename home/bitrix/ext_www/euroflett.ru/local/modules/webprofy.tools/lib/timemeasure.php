<?

	namespace Webprofy\Tools;

	class TimeMeasure{
		private $measures;

		function __construct($name = null){
			$this->measures = array();
			if($name){
				$this->add($name)
			}
		}

		function add($name = 'без названия'){
			$this->measures[] = array(
				'name' => $name,
				'ms' => round(microtime(true) * 1000)
			);
			return $this;
		}

		function clear(){
			$this->measures = array();
			return $this;
		}

		function log(){
			$this->updateDeltas();
			$result = array();
			$result['each'] = $this->measures;
			$total = $this->measures[count($this->measures) - 1]['ms'] - $this->measures[0]['ms'];
			$result['total'] = array(
				'delta' => $total,
				'delta-ru' => $this->getName($total)
			);
			Webprofy\Tools\Functions::log($result);
			return $this;
		}

		private function getName($ms){
			return Webprofy\Tools\Functions::timeToWords($ms, 'ru', false)
		}

		function updateDeltas(){
			$previous = null;
			foreach($this->measures as &$measure){
				if($previous !== null){
					$measure['delta'] = $measure['ms'] - $previous['ms'];
					$measure['delta-ru'] = $this->getName($measure['delta']);
				}
				$previous = $measure;
			}
			return $this;
		}

	}