<?
	namespace Webprofy\Tools;

	use Webprofy\Tools\Bitrix\IBlock\IBlock;
	use Webprofy\Tools\Bitrix\IBlock\Element;
	use Webprofy\Tools\Bitrix\IBlock\Section;

    use Webprofy\Tools\Bitrix\Getter;
    use Krumo\krumo;
    use CModule;
    use CPHPCache;

	class Functions{

		static function bit($data){
			return Getter::bit($data);
		}

		// Время последнего обновления элементов инфоблока
		static function lastUpdate($iblock){
			return Getter::bit(array(
				'of' => 'element',
				'f' => array(
					'iblock' => $iblock
				),
				'sort' => array(
					'TIMESTAMP_X' => 'desc'
				),
				'sel' => 'TIMESTAMP_X',
				'one' => 'f.TIMESTAMP_X'
			));
		}

		// Проверка, есть ли уменьшенная копия у изображения
		static function hasResized($file, $size){
	        if(!is_array($file) && intval($file) > 0){
	            $file = CFile::GetFileArray($file);
	        }

	        if(!is_array($file) || !array_key_exists("FILE_NAME", $file) || strlen($file["FILE_NAME"]) <= 0){
	            return false;
	        }

	        if ($resizeType !== BX_RESIZE_IMAGE_EXACT && $resizeType !== BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
	            $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;

	        if(!is_array($size)){
	            $size = array();
	        }

	        if(!array_key_exists("width", $size) || intval($size["width"]) <= 0){
	            $size["width"] = 0;
	        }

	        if (!array_key_exists("height", $size) || intval($size["height"]) <= 0){
	            $size["height"] = 0;
	        }

	        $size["width"] = intval($size["width"]);
	        $size["height"] = intval($size["height"]);

	        $uploadDirName = COption::GetOptionString("main", "upload_dir", "upload");
	        $bFilters = is_array($arFilters) && !empty($arFilters);

	        if(
	            ($size["width"] <= 0 || $size["width"] >= $file["WIDTH"])
	            && ($size["height"] <= 0 || $size["height"] >= $file["HEIGHT"])
	        ){
	            if($bFilters){
	                //Only filters. Leave size unchanged
	                $size["width"] = $file["WIDTH"];
	                $size["height"] = $file["HEIGHT"];
	                $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
	            }
	            else{
	                return false;
	            }
	        }

	        $io = CBXVirtualIo::GetInstance();
	        $cacheImageFile = "/".$uploadDirName."/resize_cache/".$file["SUBDIR"]."/".$size["width"]."_".$size["height"]."_".$resizeType.(is_array($arFilters)? md5(serialize($arFilters)): "")."/".$file["FILE_NAME"];
	        $cacheImageFileCheck = $cacheImageFile;
	        if ($file["CONTENT_TYPE"] == "image/bmp")
	            $cacheImageFileCheck .= ".jpg";

	        static $cache = array();
	        $cache_id = $cacheImageFileCheck;
	        if(
	        	isset($cache[$cache_id]) ||
	        	file_exists(
	        		$io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$cacheImageFileCheck
	        	))
	        ){
	        	return true;
	        }
	        return false;
		}

		// Получает рандомную строку
		static function randomString($length = 16, $symbols = 'abcdefghijklmnopqrstuvwxyz0123456789'){
			$result = '';
			$max = strlen($symbols) - 1;
			for($i=0; $i < $length; $i++){
				$symbol = $symbols[mt_rand(0, $max)];
				if(mt_rand(1, 3) == 2){
					$symbol = strtoupper($symbol);
				}
				$result .= $symbol;
			}
			return $result;
		}

		/*
			Проверка совпадения пути со строкой
			http://site.ru/path/hello
			wp('matchDir', '/path/*', 0); // вернёт 'hello'
		*/
		static function matchDir($dir, $index = null){
			global $APPLICATION;
			$dir = strtr($dir, array(
				'*' => '(.*?)'
			));
			preg_match(
				'#'.$dir.'$#',
				$APPLICATION->GetCurDir(),
				$m
			);
			array_shift($m);
			if(is_int($index)){
				return $m[$index];
			}
			return $m;
		}

		// Включить включаемую область.
		static function includeArea($path){
			$path = SITE_TEMPLATE_PATH."/include_areas/".$path.".php";
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
			  "bitrix:main.include",
			  "",
			  Array(
			    "AREA_FILE_SHOW" => "file",
			    "PATH" => $path,
			    "EDIT_TEMPLATE" => "includearea.php"
			  )
			);
		}

		// Получить название умного фильтра
		static function getSmartFilterName($data){
			$filter = isset($data['filter']) ? $data['filter'] : 'arrFilter';
			$property = isset($data['property']) ? $data['property'] : 0;
			$key = isset($data['id']) ? abs(crc32($data['id'])) : 0;
			$name = htmlspecialcharsbx($filter."_".$property."_".$key);
			if(!$data['full']){
				return $name;
			}
			return $data['full'].$name.'=Y&set_filter=Подобрать';
		}

		/*
			Функция для кеширования 
			Пример: 
			$arResult = WP::cache('c_component_name', 3600000, function(){
				return superHardCalculation();
			});
		*/
		static function cache($name, $time, $callback){
			if(is_array($name)){
				$sname = '';
				foreach($name as $value){
					$sname .= '_'.$value;
				}
				$name = substr($sname, 1);
			}
			$cache = new CPHPCache;
			if($time === null){
				$time = 3600000;
			}
			if($cache->StartDataCache($time, $name, "/cache_dir")){
				$result = $callback();
				$cache->EndDataCache(array(
					"result" => $result
				));
			}
			else{
				extract($cache->GetVars());
			}
			return $result;
		}

		/*
			Получить id для редактирования элемента в "Эрмитаже" битрикса.
			Пример:
			<div id="<?= WP::editId(1, 124, $this) ?>">
		*/
		static function editId($iblockID, $elementID = 0, $template = null, $isAttribute = false){
			if(!$template || !$elementID){
				return 0;
			}
			CModule::IncludeModule('iblock');

			$buttons = CIBlock::GetPanelButtons(
				$iblockID,
				$elementID,
				0,
				array(
					"SECTION_BUTTONS" => false,
					"SESSID" => false
				)
			);

			$template->AddEditAction(
				$elementID,
				$buttons["edit"]["edit_element"]["ACTION_URL"],
				CIBlock::GetArrayByID(
					$iblockID,
					"ELEMENT_EDIT"
				)
			);
			
			$template->AddDeleteAction(
				$elementID,
				$buttons["edit"]["delete_element"]["ACTION_URL"],
				CIBlock::GetArrayByID(
					$iblockID,
					"ELEMENT_DELETE"
				),
				array(
					"CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')
				)
			);

			$id = $template->GetEditAreaId($elementID);
			if($isAttribute){
				$id = $id ? 'id="'.$id.'"' : '';
			}
			return $id;
		}

		// Получить ID элементов из списка сравнения
		static function getCompareIDs(){
			$ids = array();
			foreach($_SESSION['CATALOG_COMPARE_LIST'] as $e){
				foreach($e['ITEMS'] as $id => $o){
					$ids[] = intval($id);
				}
			}
			sort($ids);
			return $ids;
		}

		/*
			Добавить события.

			Пример:

			WP::addEvents(array(
				'main' => array(
					'OnPageStart' => array(
						'callback' => function(){
							echo 'ok';
						},
						'priority' => 50
					)
				)
			));
		*/
		static function addEvents($modules){
			foreach($modules as $module => $events){
				foreach($events as $event => $data){
					if(is_array($data) && isset($data['callback'])){
						$callback = $data['callback'];
						$priority = isset($data['priority']) ? $data['priority'] : 50;
					}
					elseif(is_callable($data)){
						$callback = $data;
						$priority = 50;
					}

					AddEventHandler($module, $event, $callback, $priority);
				}
			}
		}

		/*
			Получаем список highload-элементов по ID.
		*/
		private static $getHLElementsCache = array();
		static function getHLElements($id){
			if(isset(self::$getHLElementsCache[$id])){
				return self::$getHLElementsCache[$id];
			}
			CModule::IncludeModule("highloadblock");
			$hldata = \Bitrix\Highloadblock\HighloadBlockTable::getById($id)->fetch();
			$hlentity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hldata);
			$hlDataClass = $hldata['NAME'].'Table';
			$list = $hlDataClass::getList();

			$result = array();
			while($element = $list->Fetch()){
				$result[] = $element;
			}

			self::$getHLElementsCache[$id] = $result;
			return $result;
		}


		/*
			Создать папки в структуре сайта.
			WP::makeStructure(array(
				'names' => array(
					// 'Самокаты',
					'О магазине' => array(
						'Гарантии',
						'Книга отзывов',
					),
					'Услуги',
					'Статьи',
					// ...
				)
			));
		*/
		static function makeStructure($data){
			$names = $data['names'];
			$debug = $data['debug'];
			$parent = isset($data['parent']) ? $data['parent'] : '/';
			$result = '';
			$depth = isset($data['depth']) ? $data['depth'] : 0;

			foreach($names as $k => $v){
				if(is_array($v)){
					$name = $k;
				}
				else{
					$name = $v.'';
				}

				$ename = CUtil::translit(trim($name), 'ru', array('change_case' => 'L'));
				$path = $_SERVER['DOCUMENT_ROOT'].$parent.$ename;

				mkdir($path, 0755, true);

				$pathIndex = $path.'/index.php';
				$pathSection = $path.'/.section.php';
				$added = false; 

				foreach(array(
					array(
						'index.php',
						'<?'.$nl.'	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");'.$nl.'	$APPLICATION->SetTitle("'.$name.'");'.$nl.'?>'.$nl.'	Text.'.$nl.'<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>'
					),
					array(
						'.section.php',
						'<? $sSectionName="'.$name.'"; ?>'
					)
				) as $a){
					list($filename, $content) = $a;
					$file = $path.'/'.$filename;
					if(file_exists($file)){
						self::log('file "'.$file.'" exists');
						continue;
					}
					$added = true;
					if(!$debug){
						self::log('creating "'.$file.'"');
						file_put_contents($file, $content);
					}
				}

				if($added){
					self::log('<a href="'.$parent.$ename.'">'.$name.'</a> '.$parent.$ename.'/');
					$result .= '
	Array(
		"'.str_repeat('-', $depth).' '.$name.'", 
		"'.$parent.$ename.'", 
		Array(), 
		Array(), 
		"" 
	),';
				}
				
				if(is_array($v)){
					$result .= self::makeStructure(array(
						'names' => $v,
						'debug' => $debug,
						'parent' => $parent.$ename.'/',
						'depth' => $depth + 1
					));
				}
			}
			return $result;
		}
		

		private static
			$timeToWordsData = array(
				'ru' => array(
					'instantly' => 'мгновенно',
					'times' => array(
						array('d', 'д', 'ней', 'ень', 'ня'),
						array('h', 'час', 'ов', '', 'а'),
						array('m', 'минут', '', 'а', 'ы'),
						array('s', 'секунд', '', 'а', 'ы'),
						array('ms', 'миллисекунд', '', 'а', 'ы'),
					)
				),
				'en' => array(
					'instantly' => 'instantly',
					'times' => array(
						array('d', 'day', 'days'),
						array('h', 'hour', 'hours'),
						array('m', 'minute', 'minutes'),
						array('s', 'second', 'seconds'),
						array('ms', 'millisecond', 'milliseconds'),
					)
				)
			),
			$times = array(
				array('ms', 1000),
				array('s', 60),
				array('m', 60),
				array('h', 24),
				array('d', 31),
				array('mon', 12),
			);

		/*
			Переводит время из одних единиц в другие.
		*/
		static function time($value, $from = 'm', $to = 's'){
			$t = self::$times;
			$n = count($t);

			$active = false;
			$multiplier = 1;

			for(
				$i = $n - 1;
				$i >= 0;
				$i--
			){
				list($name, $value_) = $t[$i];
				if(!$active){
					if($name == $from){
						$active = true;
					}
					else{
						continue;
					}
				}

				if($active){
					$multiplier *= $value_;
				}
			}

			$active = false;
			$value *= $multiplier;

			$multiplier = 1;

			for(
				$i = $n - 1;
				$i >= 0;
				$i--
			){
				list($name, $value_) = $t[$i];
				if(!$active){
					if($name == $to){
						$active = true;
					}
					else{
						continue;
					}
				}

				if($active){
					$multiplier *= $value_;
				}
			}

			$value /= $multiplier;

			return $value;
		}

		/*
			Переводит время в миллисекундах в словесное представление:
			WP::timeToWords(7453862, 'ru'); // '2 часа 4 минуты 13 секунд'
			WP::timeToWords(7453862, 'ru', false); // '2 часа 4 минуты 13 секунд 862 миллисекунды'
		*/
		static function timeToWords($value /* in ms */, $language = 'ru', $exclude = array('ms')){
			$language = self::$timeToWordsData[$language];

			$neg = false;
			if($value < 0){
				$value = abs($value);
				$neg = true;
			}

			if($value < 1){
				return $language['instantly'];
			}

			$data = array();
			foreach(self::$times as $a){
				$divider = $a[1];
				$remainder = $value % $divider;
				$data[$a[0]] = $remainder;
				$value = ($value - $remainder) / $divider;
				if($value < 1){
					$value = 0;
					break;
				}
			}


			$data['d'] = $value;

			$s = '';

			foreach($language['times'] as $a){
				$measure = $a[0];
				if(is_array($exclude) && in_array($measure, $exclude)){
					continue;
				}
				$v = $data[$measure];
				if(!$v){
					continue;
				}
				$s .= ' '.$v.' ';
				switch(count($a)){
					case 5:
						list($measure, $before, $word0, $word1, $word2) = $a;
						$s .= $before;
						$s .= self::ruPlural(
							$v,
							$word0,
							$word1,
							$word2
						);
						break;

					case 3:
						list($measure, $wordOne, $wordMany) = $a;
						$s .= abs($v) > 1 ? $wordMany : $wordOne;
						break;
				}
			}
			return $s;
		}

		/*
			Сортирует массив по значению ключа.

			Пример:
			WP::sortBy(array(
				array(
					'priority' => 1,
					'name' => 'a'
				),
				array(
					'priority' => 3,
					'name' => 'c'
				),
				array(
					'priority' => 2,
					'name' => 'b'
				),
			), 'priority');

			Результат:

			array(
				array(
					'priority' => 1,
					'name' => 'a'
				),
				array(
					'priority' => 2,
					'name' => 'b'
				),
				array(
					'priority' => 3,
					'name' => 'c'
				),
			);
		*/
		static function sortBy(&$a, $key, $desc = false){
			usort($a, function($a, $b) use ($key, $desc){
				$av = $a;
				$bv = $b;
				foreach(explode('.', $key) as $i){
					$av = $av[$i];
					$bv = $bv[$i];
				}
				return ($av > $bv ? 1 : -1) * ($desc ? -1 : 1);
			});
			return $a;
		}

		/*
			Получает широту и долготу по адресу.
		*/
		static function getLatLng($address){
			$geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
			$output = json_decode($geocode);
			$loc = $output->results[0]->geometry->location;
			return array(
				'lat' => $loc->lat,
				'lng' => $loc->lng
			);
		}

		/*
			Получает дерево из массива.
		
			Пример:

			WP::treeFromArray(
				array(
					array(
						'name' => 'root',
						'id_' => 1,
						'parent' => null,
						'priority' => 100
					),
					array(
						'name' => 'deep2',
						'id_' => 3,
						'parent' => 1,
						'priority' => 200
					),
					array(
						'name' => 'deep1',
						'id_' => 2,
						'parent' => 1,
						'priority' => 100
					),
					array(
						'name' => 'deeper',
						'id_' => 4,
						'parent' => 2,
						'priority' => 100
					)
				)
			), null, 'parent', 'id_', 'priority');


			Вернёт:
			array(
				'id' => 1,
				'element' => array(
					'name' => 'root',
					'id_' => 1,
					'parent' => null,
					'priority' => 100
				),
				'childs' => array(
					array(
						'id' => 2,
						'element' => array(
							'id_' => 2,
							'name' => 'deep1',
							'parent' => 1,
							'priority' => 100
						),
						'childs' => array(
							...
						)
					),
					array(
						'id' => 3,
						'element' => array(
							'id_' => 3,
							'name' => 'deep2',
							'parent' => 1,
							'priority' => 100
						),
						'childs' => array()
					)
				)
			)
		*/
		static function treeFromArray(
			$elements,
			$parentValue = 0,
			$parentField = 'parent',
			$childField = 'id',
			$sortField = 'sort'
		){
			$result = array();
			foreach($elements as $element){
				if($element[$parentField] != $parentValue){
					continue;
				}
				$id = $element[$childField];
				$result[] = array(
					'id' => $id,
					'element' => $element,
					'childs' => self::treeFromArray(
						$elements,
						$id,
						$parentField,
						$childField,
						$sortField
					)
				);
			}

			if($sortField){
				usort($result, function($a, $b) use ($sortField){
					return $a['element'][$sortField] - $b['element'][$sortField];
				});
			}
			return $result;
		}

		// Проходится по дереву.
		static function showTree($tree, $levels, $curLevel = 0){
			$level = empty($levels[$curLevel]) ? $levels[count($levels) - 1] : $levels[$curLevel];
			foreach($tree as $node){
				$element = $node['element'];
				if(is_callable($level['before'])){
					$level['before']($element, $node);
				}

				self::showTree($node['childs'], $levels, $curLevel + 1);

				if(is_callable($level['after'])){
					$level['after']($element, $node);
				}
			}
		}
		

		/*
			Проходится по дереву из предыдущей функции.
			WP::mapTree(WP::treeFromArray($a), function($element, $parentData){
				$parentID = $parentData ? $parentData['id'] : null;
				$currentID = $element['id_'];

				echo $currentID.' имеет родителя '.$parentID;

				return array( // этот массив попадёт в $parentData раздела-"дитя".
					'id' => $currentID;
				);
			})
		*/
		static function mapTree($array, $callback, $parentData = false){
			foreach($array as $node){
				$childData = $callback($node['element'], $parentData);
				if($childData === false){
					return false;
				}
				if(!is_array($node['childs'])){
					return true;
				}
				$result = self::mapTree($node['childs'], $callback, $childData);
				if($result === false){
					return false;
				}
			}
			return true;
		}

		/*
			Получаем актуальный курс валют.
		*/
		static function getCurrency($from = 'EUR', $to = 'RUB'){
			$content = file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date("d/m/Y"));
			$xml = simplexml_load_string($content);

			$node = $xml->xpath('/ValCurs/Valute/CharCode[text()="'.$from.'"]/../Value');
			$from = floatval(strtr($node[0], array(',' => '.')));

			if($to != 'RUB'){
				$node = $xml->xpath('/ValCurs/Valute/CharCode[text()="'.$to.'"]/../Value');
				$to = floatval(strtr($node[0], array(',' => '.')));
			}
			else{
				$to = 1;
			}

			return $from / $to;
		}

		/*
			Корректно работающий strrpos. Не помню, для чего он мне был нужен. :(
		*/
		function strrpos($haystack, $needle, $offset = 0){
			$needleLength = strlen($needle);
			for(
				$i = $offset ? $offset : strlen($haystack);
				$i >= 0;
				$i--
			){
				if(substr($haystack, $i, $needleLength) == $needle){
					return $i;
				}
			}
			return false;
		}

		private static $amIBuffer = array();

		/*
			Проверка, принадлежит ли пользователь определённой группе.
		*/
		static function amI($type = 'admin'){
			global $USER;

			if(isset(self::$amIBuffer[$type])){
				return self::$amIBuffer[$type];
			}

			switch($type){
				case 'admin':
					return in_array(1, $USER->GetUserGroupArray());
			}
		}

		/*
			Обрезает текст.
		*/
		static function cutText($text, $maxLength = 100, $stripTags = false, $min = 40){
			$text = trim($text);
			$text = preg_replace('/(\s|&nbsp;)+/', ' ', $text);


			if($stripTags){
				$text = strip_tags($text);
			}

			if(strlen($text) <= $maxLength){
				return $text;
			}

			$pos = f::strrpos($text, '.', $maxLength - 1) + 1;
			
			if($pos < $min){
				$pos = f::strrpos($text, ' ', $maxLength - 1);
			}

			if($pos < $min){
				$pos = $maxLength;
			}


			$text = substr($text, 0, $pos);

			return $text;
		}

		/*
			Распаковка CSV-файла.
		*/
		static function uncsv($path, $delimiter = "\t"){
			$result = array();
			$handle = fopen($_SERVER['DOCUMENT_ROOT'].$path, "r");
			if($handle === FALSE){
				return $result;
			}

			$titles = array_map('trim', fgetcsv($handle, 0, $delimiter));
			while(($a = fgetcsv($handle, 0, $delimiter)) !== FALSE){
				$row = array();
				foreach($a as $i => $v){
					$row[$titles[$i]] = $v;
				}
				$result[] = $row;
			}

		    fclose($handle);
		    return $result;
		}

		/*
			Вывод лога в HTML
		*/
		static function dump($dump){
			krumo::dump($dump);
		}

		/*
			Вывод лога.
		*/
		static function log($object, $types = ''){
			$objectDump = print_r($object, 1);
			$types = explode(' ', $types);

			if(in_array('string', $types)){
				return $objectDump;
			}
			
			if(in_array('clr', $types)){
				$GLOBALS['APPLICATION']->RestartBuffer();
			}

			if(!in_array('nopre', $types)){
				echo '<pre style="background-color:#fff; overflow:visible;">';
			}

			echo "\n\n".str_repeat('=', 10).'LOG START'.str_repeat('=', 10);
			foreach(debug_backtrace() as $i => $b){
				printf("\n%s:%s", $b['file'], $b['line']);
				if(in_array('all', $types)){
					continue;
				}
				break;
			}
			printf(":\n\"%s\"\n", $objectDump)."\n";
			echo str_repeat('=', 10).'LOG END'.str_repeat('=', 10);

			if(!in_array('nopre', $types)){
				echo '</pre>';
			}

			if(in_array('clr', $types)){
				die();
			}
			return $object;
		}


		static function clog($object){
			echo '<script> console.log('.\CUtil::PhpToJSObject($object).') </script>';
		}

		/*
			$n = 133;
			$n.' товар'.WP::russianCountName($n, 'ов', '', 'а'); // '133 товара'
		*/
		static function ruPlural($n, $w0, $w1, $w2){
			$n00 = $n % 100;
			$n0 = $n00 % 10;
			if($n0 == 0 || $n00 > 10 && $n00 < 20){
				return $w0;
			}
			if($n0 == 1){
				return $w1;
			}
			if($n0 > 1 && $n0 < 5){
				return $w2;
			}
			return $w0;
		}

			private static $months = array(
				'im' => array(
					'январь', 'февраль',
					'март', 'апрель', 'май',
					'июнь', 'июль', 'август',
					'сентябрь', 'октябрь', 'ноябрь',
					'декабрь'
				),
				'rod' => array(
					'января', 'февраля',
					'марта', 'апреля', 'мая',
					'июня', 'июля', 'августа',
					'сентября', 'октября', 'ноября',
					'декабря'
				)
			);

		/*
			Получить русское название месяца. 1 - январь.
		*/
		static function ruMonthName($number = NULL, $pad = 'im'){
			if(!isset(self::$months[$pad])){
				$pad = 'im';
			}
			if($number === NULL){
				$number = date('n');
			}
			$index = intval($number) - 1;
			$index = $index < 0 ? 0 : ($index > 11 ? 11 : $index);
			return self::$months[$pad][$index];
		}

		/*
			Пошаговое удаление инфоблока, ВЫПОЛНЯЕТСЯ В /local/admin/php_iterator.php
		*/
		static function removeIBlock($IBLOCK_ID = 0, $step = null){
			CModule::IncludeModule('iblock');
			$result = array(
				'repeat' => false
			);

			if(!$IBLOCK_ID){
				$result['info'] = 'no iblock is set';
				return $result;
			}

			switch($step){
				case 1:
				case 'elements':
					$LIMIT = 200;
					
					$ids = Getter::bit(array(
						'of' => 'element',
						'max' => $LIMIT,
						'f' => 'iblock='.$IBLOCK_ID,
						'sel' => 'ID',
						'map' => 'f.ID'
					));

					foreach($ids as $id){
						\CIBlockElement::Delete($id);
					}
					
					if(count($ids) < $LIMIT){
						$result['info'] = 'removed elements (1/4)';
						break;
					}

					$result['repeat'] = true;
					$result['info'] = 'removing elements... (1/4...)';
					break;

				case 2:
				case 'properties':
					$ids = Getter::bit(array(
						'of' => 'property',
						'f' => 'iblock='.$IBLOCK_ID,
						'map' => 'f.ID'
					));

					foreach($ids as $id){
						\CIBlockProperty::Delete($id);
					}

					$result['info'] = "removed ".count($ids)." properties (2/4)";
					break;

				case 3:
				case 'sections':
					$LIMIT = 50;
					$ids = Getter::bit(array(
						'of' => 'section',
						'sort' => array(
							'depth_level' => 'DESC'
						),
						'sel' => 'ID',
						'f' => 'iblock='.$IBLOCK_ID,
						'max' => $LIMIT,
						'map' => 'f.ID'
					));

					foreach($ids as $id){
						\CIBlockSection::Delete($id);
					}

					if(count($data) < $LIMIT){
						$result['info'] = 'removed sections (3/4)';
						break;
					}
					$result['info'] = 'removing sections... (3/4...)';
					$result['repeat'] = true;
					break;

				case 4:
				case 'iblock':
					\CModule::IncludeModule('catalog');
					$o = new \CCatalog();
					$o->UnLinkSKUIBlock($IBLOCK_ID);
					$o->Delete($IBLOCK_ID);

					global $DB;
					$DB->StartTransaction();
					if(!\CIBlock::Delete($IBLOCK_ID)){
					   $result['info'] = 'ERROR removing iblock (4/4!)';
					   $DB->Rollback();
					   break;
					}
					$DB->Commit();
					$result['info'] = 'removed iblock (4/4)';
					break;

				case 'iterate-clear':
					$o = new \Webprofy\Tools\Session('everything_deleter');
					$o->clear();
					echo 'cleared';
					break;

				case 'iterate':
					$clear = 0;

					$o = new \Webprofy\Tools\Session('everything_deleter');

					$total = $o->get('total', 0);
					$o->set('total', $total + 1);

					$index = $o->get('index', 0);
					
					$ids = Getter::bit(array(
						'of' => 'iblock',
						'f' => 'TYPE=mht_products',
						'sel' => 'ID',
						'map' => 'f.ID'
					));

					if($o->get('remove-iblocks', false)){
						if(!$ids[0]){
							return;
						}
						echo '
							iblocks left: '.count($ids).'<br/>
							iteration: '.$total.'<br/>
						';
						self::removeIBlock($ids[0], 4);
						echo '#ITERATOR_REPEAT#';
						return;
					}
					
					$step = $o->get('step', 1);

					echo '
						index: '.$index.'/'.count($ids).'<br/>
						step: '.$step.'/3<br/>
						iteration: '.$total.'<br/>
					';

					$result = self::removeIBlock(
						$ids[$index],
						$step
					);

					echo $result['info'].'<br/>';
					
					if($result['repeat']){
						echo '#ITERATOR_REPEAT#';
						return;
					}

					if($index == (count($ids) - 1) && $step == 3){
						$o->set('remove-iblocks', true);
						echo '#ITERATOR_REPEAT#';
						return;
					}

					if($step == 3){
						$index++;
						$step = 1;
					}
					else{
						$step++;
					}

					$o
						->set('index', $index)
						->set('step', $step);


					echo '#ITERATOR_REPEAT#';
					break;
			}

			return $result;
		}
	}
?>