<?
	namespace Webprofy\Import;
	$PHPExcelPath = $_SERVER['DOCUMENT_ROOT'].'/local/classes_noauto/PHPExcel/';
	require $PHPExcelPath.'IOFactory.php';
	use \CModule;
	use \CEventLog;
	use \CIBlockElement;
	use \CIBlockProperty;
	use \CIBlockPropertyEnum;
	use \CIBlockSection;
	use \CUtil;
	use \Webprofy\Import\Tools as Tools;
	use \CCatalogSKU;
	use \CPrice;
	use \CCatalogProduct;
	use \PHPExcel_IOFactory;
	use \PHPExcel_CachedObjectStorageFactory;
	use \PHPExcel_Settings;

class Import{


		protected
			$arBrands = array(),
			$arImport = array(),
			$filename = "",
			$preset = "",
			$doLookup = true;

        protected $brand;
        
        protected $updatePrice = true;

		public static
			$settings = array();

		function loadBrands(){
			$mtime = microtime();
			// Соберём все бренды и инфоблоки, в которых они есть
			// Предполагается следующая архитектура каталога:
			// Каждая большая категория располагается в отдельном инфоблоке с типом 'catalog'
			// Внутри этого инфоблока есть одна секция, дублирующая название инфоблока (корневая группа)
			// Внутри этой секции находятся секции по брендам (название секции совпадает с названием бренда)

			$arBrands = array();

			$ob = CIBlockSection::GetList(array('sort' => 'asc'), array('IBLOCK_TYPE' => 'catalog', 'DEPTH_LEVEL' => 2));
			while($arSection = $ob->GetNext()){
				$brand = strtoupper($arSection['NAME']);
				if(!$arBrands[$brand]){
					$arBrands[$brand] = array("NAME" => $brand, "IBLOCK_IDS" => array(), "SECTION_IDS" => array());
				}
				$arBrands[$brand]['IBLOCK_IDS'][] = $arSection['IBLOCK_ID'];
				$arBrands[$brand]['SECTION_IDS'][] = $arSection['ID'];
			}
			$this->arBrands = $arBrands;
			$mtime = microtime() - $mtime;
			$GLOBALS['LOG']->write('LOADBRANDS_PERFORMANCE_MS', $mtime);
		}

		function initSyncTable(){
			// Инициализирует работу с БД, создаёт таблицу соответствий, если её нет
			if(!$this->preset)
				$GLOBALS['LOG']->fatal('NO_PRESET_CODE_PROVIDED'); // fatal = die;

			$dbTable = 'pricelist_sync_'.strtolower($this->preset);
            
            if($this -> preset == 'BRAND_COUNT' && $this -> brand['NAME'] != '') {
                $dbTable .= '_' . Cutil::translit(strtolower($this -> brand['NAME']), 'ru', array('replace_space' => '_', 'replace_other' => '_'));
            }
            
			$this->dbTable = $dbTable;
			$GLOBALS['DB']->Query('CREATE TABLE IF NOT EXISTS '.$dbTable.' (
				code varchar(80) NOT NULL,
				iblock_id int NOT NULL,
				element_id int,
				offer_id int,
				last_import_id varchar(40),
				PRIMARY KEY (code)
			)');
			$GLOBALS['LOG']->write('CREATE_TABLE', $dbTable);
		}

		function setPreset($code){
			if(!$code)
				$GLOBALS['LOG']->fatal('NO_PRESET_CODE_PROVIDED'); // fatal = die;
			$this->preset = $code;
			$this->arPreset = $this->settings['PRESETS'][$code];
		}

		function setDoLookup($bool){
			// Искать ли элемент, если его нет в таблице соответствий
			$this->doLookup = $bool;
		}

		function setImportFile($filename){
			$this->filename = $filename;
		}

        public function setBrand($brandId) {
            if((int)$brandId == 0) {
                $GLOBALS['LOG'] -> fatal('Не указан бренд');
            }
            
            $brand = $brand_res = CIBlockElement::GetList(
                array('NAME' => 'ASC'),
                array('IBLOCK_ID' => 9, 'ID' => (int)$brandId, 'ACTIVE' => 'Y'),
                false,
                false,
                array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'ACTIVE')
            ) -> GetNext();
            
            if(is_array($brand) && count($brand) > 0 && is_array($this -> arBrands[strtoupper($brand['NAME'])])) {
                $this -> brand = $this -> arBrands[strtoupper($brand['NAME'])];
            } else {
                $GLOBALS['LOG'] -> fatal('Указанный бренд не существует или не активен');
            }
        }
        
        public function setUpdatePrice($val) {
            $this -> updatePrice = (bool)$val;
        }

		function loadImportElements($num = 100, $offset = 0){
			$mtime = microtime();
			$GLOBALS['LOG']->write('ELEMENTS_GET');
			$fileName = $this->filename;

			if(!$this->preset){
				$GLOBALS['LOG']->fatal('NO_PRESET_CODE_PROVIDED'); // fatal = die;
			}

			$arConfig = $this->arPreset;

			//Собираем и валидируем параметры
			$columnFrom = $arConfig["COLUMN_FROM"];
			$columnTo = $arConfig["COLUMN_TO"];
			$rowCount = $num;

			foreach(range(ord($columnFrom), ord($columnTo)) as $v){
				$alphabet[] = chr($v);
			}
			
			$firstRow = $arConfig['FIRST_ROW'];
			if (empty($firstRow)){
				$firstRow = 1;
			}
			$stepRow = $firstRow + $offset;

			$titleRow = $arConfig['TITLE_ROW'];
			if (empty($titleRow)){
				$titleRow = $firstRow;
			}
            
            $GLOBALS['LOG']->write('START_READ_FILE');
            
			// Открываем файл
			try {
				$inputFileType = PHPExcel_IOFactory::identify($fileName);
				
				if ($inputFileType=='CSV') {
					if (ini_get('mbstring.func_overload') & 2) {
						$GLOBALS['LOG']->fatal('MCART_WRONG_FILE_FORMAT'); // fatal = die;
					}
				}
				$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				$cacheSettings = array(' memoryCacheSize ' => '8MB');
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load($fileName);
				$worksheet_names = $objReader->listWorksheetNames($fileName);
			} catch(Exception $e) {
				$GLOBALS['LOG']->fatal('IMPORT_FILE_OPEN_ERROR', $fileName); // fatal = die;
			}
            
            $GLOBALS['LOG']->write('END_READ_FILE');

			$sheet = $objPHPExcel->getSheet(0); 
			$highestRow = $sheet->getHighestRow();

            $GLOBALS['LOG']->write('END_FILE_ARRAY_CREATE');

			// Валидация файла

			$rows = array();
			$cols = array();
			foreach ($arConfig['VALIDATE_FILE'] as $key => $arJob) {
				$rows[] = $arJob['ROW'];
				$cols[] = $arJob['COLUMN'];
			}

			$maxrow = max($rows);
			$maxcol = max($cols);

			$raw = $sheet->rangeToArray('A1:' . $maxcol . $maxrow, NULL, TRUE, FALSE);
			array_unshift($raw, array());

			foreach ($raw as $row => &$arRow) {
				$rowData = array();
				foreach ($alphabet as $key => $letter) {
					$rowData[$letter] = $arRow[$key]; // Переделали в массив вида ('A' => ..., 'B' => ...)
				}
				$arRow = $rowData;
			}
			unset($arRow);
			// $GLOBALS['LOG']->write('VALIDATE_FILE_raw11', json_encode($arConfig['VALIDATE_FILE']));
			 // die();
			foreach ($arConfig['VALIDATE_FILE'] as $key => $arJob) {
				switch($arJob['FUNCTION']){
					case 'EQUAL':
						if(Cutil::translit(trim(strtolower ($raw[$arJob['ROW']][$arJob['COLUMN']])), 'ru', array('replace_space' => '_', 'replace_other' => '_')) != Cutil::translit(trim(strtolower ($arJob['VALUE'])), 'ru', array('replace_space' => '_', 'replace_other' => '_'))){
							
							
							
							$GLOBALS['LOG']->fatal('FILE_VALIDATION_FAILED', array($arJob, $raw,
							Cutil::translit(strtolower ($raw[$arJob['ROW']][$arJob['COLUMN']]), 'ru', array('replace_space' => '_', 'replace_other' => '_')),
							Cutil::translit(strtolower ($arJob['VALUE']), 'ru', array('replace_space' => '_', 'replace_other' => '_'))
							) ); // fatal = die;
						}
						break;
					case 'CONTAIN':
						if(strpos(Cutil::translit(trim(strtolower ($raw[$arJob['ROW']][$arJob['COLUMN']])), 'ru', array('replace_space' => '_', 'replace_other' => '_')), Cutil::translit(trim(strtolower ($arJob['VALUE'])), 'ru', array('replace_space' => '_', 'replace_other' => '_'))) == -1)
							$GLOBALS['LOG']->fatal('FILE_VALIDATION_FAILED', array($arJob, $raw)); // fatal = die;
						break;
				}
			} 
			
			$GLOBALS['LOG']->write('VALIDATE_FILE_Y');
			
			// Если мы тут — валидация пройдена, работаем дальше

			$GLOBALS['LOG']->write('VALIDATE_FILE_raw', json_encode($raw));
			for ($row = $stepRow; $row < ($stepRow+$rowCount); $row++){ 
				if ($row > $highestRow) {
					break;
				}
				$raw = $sheet->rangeToArray($columnFrom . $row . ':' . $columnTo . $row, NULL, TRUE, FALSE);
				$GLOBALS['LOG']->write('VALIDATE_FILE_raw_' . $row, json_encode($raw));
				
				$rowData = array();
				foreach ($alphabet as $key => $letter) {
					$rowData[$letter] = $raw[0][$key]; // Переделали в массив вида ('A' => ..., 'B' => ...)
				}
				$this->arImport[] = $rowData;
			}
			
			$GLOBALS['LOG']->write('=========================1' );
			$GLOBALS['LOG']->write('VALIDATE_FILE_stepRow', json_encode($stepRow));
			$GLOBALS['LOG']->write('VALIDATE_FILE_rowCount', json_encode($rowCount));
			$GLOBALS['LOG']->write('VALIDATE_FILE_highestRow', json_encode($highestRow));
			$GLOBALS['LOG']->write('VALIDATE_FILE_row', json_encode($row));
			$GLOBALS['LOG']->write('VALIDATE_FILE_columnFrom', json_encode($columnFrom));
			$GLOBALS['LOG']->write('VALIDATE_FILE_columnTo', json_encode($columnTo));
			$GLOBALS['LOG']->write('VALIDATE_FILE_alphabet', json_encode($alphabet));
			$GLOBALS['LOG']->write('=========================2' );
			// $GLOBALS['LOG']->write('VALIDATE_FILE_sheet', print_r($sheet, 1));
			// $GLOBALS['LOG']->write('VALIDATE_FILE_objPHPExcel', print_r($objPHPExcel, 1));
			
			if($this->arImport){
				$GLOBALS['LOG']->write('ELEMENTS_GET_COMPLETE');
			} else {
				$GLOBALS['LOG']->fatal('ELEMENTS_GET_ERROR'); // fatal = die;
			}

			$mtime = microtime() - $mtime;
			$GLOBALS['LOG']->write('EXCEL_OPEN_PERFORMANCE_MS', $mtime);

			return count($this->arImport);
		}


		function prepareItem($rowData){
			$arConfig = $this->arPreset;

			// VALIDATE
			foreach ($arConfig['VALIDATE'] as $arJob) {
				switch ($arJob['FUNCTION']) {
					case 'ISNOTEMPTY':
						if(trim($rowData[$arJob['COLUMN']]) == ""){
							$GLOBALS['LOG']->write('SKIP_ROW', array($arJob['FUNCTION'], $rowData));
							return false;
						}
						break;
					case 'ISEMPTY':
						if(trim($rowData[$arJob['COLUMN']]) != ""){
							$GLOBALS['LOG']->write('SKIP_ROW', array($arJob['FUNCTION'], $rowData));
							return false;
						}
						break;
					case 'ISNUMBERIC':
						if(!is_numeric($rowData[$arJob['COLUMN']])){
							$GLOBALS['LOG']->write('SKIP_ROW', array($arJob['FUNCTION'], $rowData));
							return false;
						}
						break;
					case 'NOTLIKE':
					$val=strtoupper($rowData[$arJob['COLUMN']]);
					$name=$arJob['LIKE'];
						if(strpos($val,$name)!==false){
							$GLOBALS['LOG']->write('SKIP_ROW', array($arJob['FUNCTION'], $rowData));
							return false;
						}
						break;
					default:
						# code...
						break;
				}
			}

			// PREPROCESS
			foreach ($arConfig['PREPROCESS'] as $arJob) {
				switch ($arJob['FUNCTION']) {
					case 'REPLACE':
						$rowData[$arJob['COLUMN']] = str_replace($arJob['FROM'], $arJob['TO'], $rowData[$arJob['COLUMN']]);
						break;
					case 'COMBINE':
						switch($arJob['TYPE']){
							case 'ADD':
								$res = 0;
								foreach ($arJob['COLUMNS'] as $col) {
									$res += intval($rowData[$col]);
								}
								$rowData[$arJob['TO']] = $res;
								break;
							case 'CONCATENATE':
								$res = '';
								foreach ($arJob['COLUMNS'] as $col) {
									$res .= $rowData[$col];
								}
								$rowData[$arJob['TO']] = $res;
								break;
                            case 'IFNOT':
                                $res = 0;
								foreach ($arJob['COLUMNS'] as $col) {
                                    if($res==0){
                                        $res = intval($rowData[$col]);
									}
								}
								$rowData[$arJob['TO']] = $res;
								break;
						}
						break;
					case 'MD5':
						$res = '';
						foreach ($arJob['COLUMNS'] as $col) {
							$res .= $rowData[$col];
						}
						$rowData[$arJob['TO']] = md5($res);
						break;
					case 'COPY':
						$rowData[$arJob['TO']] = $rowData[$arJob['COLUMN']];
						break;
					case 'SET':
						$rowData[$arJob['TO']] = $arJob['FROM'];
						break;
					case 'STRIPMODEL':
						$data = strtoupper($rowData[$arJob['COLUMN']]);
						$data = preg_replace('/[^A-Za-z0-9\-\/.]/', ' ', $data);
						$data = strtoupper($data);
						$words = preg_split('/\s+/', $data);

						$brand = strtoupper($rowData[$arConfig['FIELDS']['BRAND']]);
						$brand = preg_split('/\s+/', $brand);

						foreach ($words as $index => $word) {
							if(!$word){
								unset($words[$index]);
								continue;
							}

							if(preg_replace('/[^A-Z0-9]/', '', $word) == ""){
								unset($words[$index]);
								continue;
							}

							// Проверяем, является ли это слово брендом.
							// Для брендов из двух слов — проверяем в т. ч. следующие слова
							$match = true;
							foreach ($brand as $brandindex => $brandword) {
								if($brandword != $words[$index + $brandindex])
									$match = false;
							}
							if($match){
								foreach ($brand as $brandindex => $brandword) {
									unset($words[$index + $brandindex]);
								}
							}
						}
						$rowData[$arJob['COLUMN']] = join(" ", $words);

						break;
					case 'GETBRANDANDMODEL':
						$data = strtoupper($rowData[$arJob['COLUMN']]);
						$data = preg_replace('/\(.*\)/', ' ', $data);
						$data = preg_replace('/[^A-Za-z0-9\-\/.]/', ' ', $data);
						$data = strtoupper($data);
						$words = preg_split('/\s+/', $data);

						$brand='';
						foreach ($words as $index => $word) {
							if(!$word){
								unset($words[$index]);
								continue;
							}
							
							foreach ($this->arBrands as$key=>  $val) {
								if(preg_replace('/'.$key.'/', '', $word) == ""){
									unset($words[$index]);
									$brand=$key;
									continue;
								}
							}

							if(preg_replace('/[^A-Z0-9]/', '', $word) == ""){
								unset($words[$index]);
								continue;
							}

						}
						$model=join(" ", $words);
						$rowData[$arJob['BRAND_TO']] = $brand;
						$rowData[$arJob['MODEL_TO']] = $model;
					break;
                    
                    case 'SET_BRAND_POST' :
                        $rowData['BRAND'] = strtoupper($this -> brand['NAME']);
                        
                        break;
                    
					default:
						# code...
						break;
				}
			}

			// POPULATE
			$arData = array();

			foreach ($arConfig['FIELDS'] as $key => $col) {
				$arData[$key] = $rowData[$col]; 
			}
			$arData['UID'] = ''.$arData['UID']; // convert to string
			if(!$arData['UID']){
				$GLOBALS['LOG']->write("ERROR_NO_UID_COLUMN", $arData);
				$GLOBALS['LOG']->write("ERROR_NO_UID_COLUMN_Config", $arConfig['FIELDS']);
				return false;
			}
			if($arData['BRAND'])
				$arData['BRAND'] = trim(strtoupper($arData['BRAND']));

			if($arData['MODEL'])
				$arData['MODEL'] = trim(strtoupper($arData['MODEL']));

			return $arData;
		}

		function getSyncElement($arItemPrepared){
			$res = $GLOBALS['DB']->Query('SELECT * FROM '.$this->dbTable.' WHERE code = "'.$arItemPrepared['UID'].'"');
			$ar = $res->Fetch();
			 
			if($ar){
				$GLOBALS['LOG']->write('ELEMENT_FOUND_IN_SYNC_TABLE', $ar);
				
			}else{//if($this->arPreset['CODE'] == 'KUPPERSBUSCH'){ 
				
				$arSelect = array(
					"ID",
					'NAME',
					"IBLOCK_ID",
					"PREVIEW_TEXT",
					"PREVIEW_PICTURE",
					"DETAIL_PICTURE", 
					"DETAIL_PAGE_URL",
					"ACTIVE_FROM", 
					"PROPERTY_ARTNUMBER",
					"PROPERTY_MODEL",
					//'CATALOG_GROUP_1'
				);
				$rsElements = CIBlockElement::GetList(array(), array( 'ACTIVE' => 'Y', 'PROPERTY_MODEL' => '%'.$arItemPrepared['UID'].'%'), false, false, $arSelect);
				while($arElement = $rsElements->GetNext())
				{
					$arElement['element_id'] = $arElement['ID'];
					return $arElement;
				}
				
				
				// if(!empty($arItemPrepared['MODEL'])){
					
					
					// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/classes/Webprofy/Import/report.txt',  "<br>4 На сайте товара нет модели " . $arItemPrepared['MODEL'], FILE_APPEND);
				// }
				
				// $GLOBALS['LOG']->write('ELEMENT_NOT_FOUND', array($arItemPrepared, ' не найден '));
			}
			return $ar;
		}

		function addSyncElement($arElement){
			// Добавляет товар в таблицу синхронизации. На входе массив:
			// $arElement = array(
			// 	"code" => $arItemPrepared['UID'],
			// 	"iblock_id" => $iblock_id,
			// 	"element_id" => $element_id,
			// 	"offer_id" => false
			// );
			$arInsert = $GLOBALS['DB']->PrepareInsert($this->dbTable, $arElement);
			$GLOBALS['DB']->Query('INSERT INTO '.$this->dbTable.' ('.$arInsert[0].') VALUES ('.$arInsert[1].')');
			$GLOBALS['LOG']->write('ELEMENT_ADDED_TO_SYNC_TABLE', $arElement);
		}

		function updateSyncElementLastImport($uid){
			// Обновляет дату последнего импорта
			if($uid!=''){$GLOBALS['DB']->Query('UPDATE '.$this->dbTable.' SET last_import_id = "'.$GLOBALS['IMPORT_SESSION'].'" WHERE code = "'.$uid.'"');}
		}

		function setDummyRun($bool){
			$this->isDummyRun = $bool;
			if($bool){
				$GLOBALS['LOG']->write('DUMMY_RUN_IS_SET');
			}
		}

		function lookupElement($arItemPrepared) {
			// SEARCHING
			$found = 0;
			$GLOBALS['LOG']->write('FIRST_IF', [$arItemPrepared]);

			if($arItemPrepared['BRAND'] && $arItemPrepared['MODEL']){
				$GLOBALS['LOG']->write('SECOND_IF', [$this->arBrands[$arItemPrepared['BRAND']]]);

				if($this->arBrands[$arItemPrepared['BRAND']]){
					$arBrand = $this->arBrands[$arItemPrepared['BRAND']];
					foreach ($arBrand['IBLOCK_IDS'] as $key => $iblock_id) {
						$ob = CIBlockElement::GetList(array("sort" => "asc"), array(
							"IBLOCK_ID" => $iblock_id,
							"SECTION_ID" => $arBrand['SECTION_IDS'][$key]
						), false, false, array(
							"ID",
							"NAME",
							"IBLOCK_ID",
							"PROPERTY_".$this->settings['PROPERTY_MODEL'])
						);
						
						/*
							1066019 - тут оишбка находится лишний товар и для артикула
						*/
						
						while($arItem = $ob->Fetch()){
							// Тут нужно бы поработать над строкой, чтобы избежать разницу между пробелами и дефисами
							$arItem['MODEL'] = trim($arItem['MODEL']);
							$arItemPrepared['MODEL'] = trim($arItemPrepared['MODEL']);
							
							$arItem['MODEL'] = trim(strtoupper($arItem['PROPERTY_'.$this->settings['PROPERTY_MODEL'].'_VALUE']));
							$arItem['MODEL_MINI']=str_replace(" ","",$arItem['MODEL']);
							$arItemPrepared['MODEL_MINI']=str_replace(" ","",$arItemPrepared['MODEL']);
							$GLOBALS['LOG']->write('PAIR', [$arItem['MODEL']."---".$arItemPrepared['MODEL']]);
								
							if('GRAUDE' != $arItemPrepared['BRAND']){
								
								//На сайте отличаются названия, нужны соответствия
								if(($arItem['MODEL'] && $arItemPrepared['MODEL'] == $arItem['MODEL']) || strripos($arItemPrepared['MODEL'],$arItem['MODEL']) !== FALSE||strripos($arItem['MODEL'],$arItemPrepared['MODEL']) !== FALSE){
									
									$found = 1;
									$element_id = $arItem['ID'];
									// $iblock_id уже заполнен
								}else if($arItem['MODEL_MINI'] && $arItemPrepared['MODEL_MINI'] == $arItem['MODEL_MINI']){
									
									$found = 1;
									$element_id = $arItem['ID'];
								} else if($arItemPrepared['NAME'] && trim(strtoupper($arItemPrepared['NAME'])) == trim(strtoupper($arItem['NAME']))) {
									
									$found = 1;
									$element_id = $arItem['ID'];
									// $iblock_id уже заполнен
								}
								 
							}else{
								
								if($arItem['MODEL'] == $arItemPrepared['MODEL'] ){
									
									$found = 1;
									$element_id = $arItem['ID'];
									
								}elseif($arItem['MODEL_MINI'] && $arItemPrepared['MODEL_MINI'] == $arItem['MODEL_MINI']){
										
									$found = 1;
									$element_id = $arItem['ID'];
									
								}
								 
								
							}
							
							if($found){
								
								break;
							}
						}
						if($found){
							
							
							break;
						}
					}
					if($found){
						// Элемент найден. Добавляем в таблицу
						$arElement = array(
							"code" => $arItemPrepared['UID'],
							"iblock_id" => $iblock_id,
							"element_id" => $element_id,
							"offer_id" => false
						);
						$this->addSyncElement($arElement);
						return $arElement;
					} else {
						
						 
						return false;
					}
				} else {
					// Не знаем такого бренда, пропускаем
					$GLOBALS['LOG']->write('UNKNOWN_BRAND', $arItem['BRAND']);
					return false;
				}
			}
		}

		function updateCache($element_id){
            //добавил новую переменную в сессию, чтобы обработчик не вызывался при апдейте из импорта
            $_SESSION['IMPORT_UPDATE_HANDLER'] = 'Y';
            
			// Меняет дату изменения самого элемента, чтобы сбросить управляемый кэш
			$el = new CIBlockElement();
			$el->Update($element_id, array());
		}

		function updateItem(&$arElement, &$arItemPrepared){
			$dummyRun = $this->isDummyRun; // Холостой прогон, без реальных изменений
			$needUpdate = false;
			if($this -> updatePrice) {
                $arFields = array(
                    "PRODUCT_ID" => $arElement['element_id'],
                    "CATALOG_GROUP_ID" => $this->settings['PRICE_TYPE_ID'],
                    "PRICE" => $arItemPrepared['PRICE'],
                    "CURRENCY" => $this->settings['PRICE_CURRENCY'],
                );
                $ob3 = CPrice::GetList(array(), array("PRODUCT_ID" => $arElement['element_id'], "CATALOG_GROUP_ID" => $this->settings['PRICE_TYPE_ID']));
                $ar3 = $ob3->Fetch();
                if($ar3 && $ar3['CURRENCY'] != $this->settings['PRICE_CURRENCY']){
                    $GLOBALS['LOG']->write("PRICE_IN_ANOTHER_CURRENCY", array($arElement, $arItemPrepared));
                } else if($ar3 && $ar3['PRICE'] == $arItemPrepared['PRICE']){
                    $GLOBALS['LOG']->write("PRICE_NOT_CHANGED", array($arElement, $arItemPrepared));
					$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'PRICE_NOT_CHANGED');
					//TODO сюда можно воткнуть лог не изменившейся цены
                } else if ($ar3) {
                    if(!$dummyRun){
                        CPrice::Update($ar3["ID"], $arFields);
                        $needUpdate = true;	
                    }
                    $GLOBALS['LOG']->write("PRICE_UPDATED", array($arElement, $arItemPrepared));
					$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'PRICE_UPDATED');
					//TODO сюда втыкаем лог изменившейся цены
                } else {
                    if(!$dummyRun){
                        CPrice::Add($arFields);
                        $needUpdate = true;
                    }
                    //TODO сюда втыкаем лог добавленной цены
					$GLOBALS['LOG']->write("PRICE_ADDED", array($arElement, $arItemPrepared));
					$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'PRICE_ADDED');
				}
            }            
             
			$arProduct = CCatalogProduct::GetByID($arElement['element_id']);
			if(isset($arItemPrepared['QUANTITY'])){
				if($arProduct && ($arProduct['QUANTITY'] == $arItemPrepared['QUANTITY'])){
					$GLOBALS['LOG']->write("QUANTITY_NOT_CHANGED", array($arElement, $arItemPrepared));
					//TODO сюда лог что количество не изменилось
					if ($arProduct['QUANTITY'] == 0) {
						$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'QUANTITY_ALL_NULL');
					} else {
						$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'QUANTITY_NOT_NULL');
					}
				} else {
					if(!$dummyRun){
						
						CCatalogProduct::Add(array('ID' => $arElement['element_id'], 'QUANTITY'=>(int)$arItemPrepared['QUANTITY']), TRUE);
						$needUpdate = true;
					}
					//TODO сюда лог, что количество было обновлено
					if ($arProduct['QUANTITY'] == 0) {
						$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'QUANTITY_SITE_NULL');
					} elseif ($arItemPrepared['QUANTITY'] == 0) {
						$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'QUANTITY_SUPP_NULL');
					} else {
						$GLOBALS["LOG"]->setLogElement($arElement['element_id'], 'QUANTITY_DIFF');
					} 
					
					$GLOBALS['LOG']->write("QUANTITY_UPDATED", 
						array( 'arElement' => $arElement, 
							'arItemPrepared' =>  $arItemPrepared, 
							'arProduct' => $arProduct,
							$dummyRun)
					);
					

				} 
			}   
			if($needUpdate && !$dummyRun){
				$this->updateCache($arElement['element_id']);
			}
			$this->updateSyncElementLastImport($arItemPrepared['UID']);
		}

		function setZeroQuantityForAbsentItems(){
			$dummyRun = $this->isDummyRun; // Холостой прогон, без реальных изменений
            $arConfig = $this->arPreset;
            
			$zero=true;
			$zero=$arConfig["QUANTITY_SET_TO_ZERO"];
            
			$ob = $GLOBALS['DB']->Query('SELECT * FROM '.$this->dbTable.' WHERE last_import_id != "'.$GLOBALS['IMPORT_SESSION'].'" OR last_import_id IS NULL;');
			while($arElement = $ob->Fetch()){
				if($zero){
					if(!$dummyRun){
						CCatalogProduct::Add(array('ID' => $arElement['element_id'], 'QUANTITY'=>'0'));
						$this->updateCache($arElement['element_id']);
					}	
					$GLOBALS['LOG']->write("QUANTITY_SET_TO_ZERO", $arElement);
				}
			}
			$GLOBALS['LOG']->write("QUANTITY_SET_TO_ZERO_COMPLETE",$this);
            
			return true;
		}

		function import(){
			foreach ($this->arImport as $key => $arItem) {

				// Подготовка полей
				$arItemPrepared = $this->prepareItem($arItem);
				//$GLOBALS['LOG']->write("MODEL", $arItemPrepared['MODEL']);
				// Пропускаем пустые строки и строки не прошедшие валидацию
				// В лог пишет функция проверки
				if(!$arItemPrepared) {
					
					if(!empty($arItem['A'])){
						
						file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/classes/Webprofy/Import/report.txt',  "<br>На сайте товара нет модели " . $arItem['A'] , FILE_APPEND);
					}
					
					
					$GLOBALS['LOG']->write('ELEMENT_NOT_FOUND', array($key, $arItem));
					continue;
				}

				$arElement = $this->getSyncElement($arItemPrepared);

				if(!$arElement && $this->doLookup){
					
					$arElement = $this->lookupElement($arItemPrepared);
					if(!empty($arItem['A'])){
						
						file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/classes/Webprofy/Import/report.txt',  "<br>На сайте товара нет модели " . $arItem['A'] . "<br>" , FILE_APPEND);
					}
					$GLOBALS['LOG']->write('ELEMENT_NOT_FOUND', array($arItemPrepared, $arItem));
				}

				if(!$arElement){
					
					if(!empty($arItem['A'])){
						
						file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/classes/Webprofy/Import/report.txt',  "<br>На сайте товара нет модели " . $arItem['A'] , FILE_APPEND);
					}
					$GLOBALS['LOG']->write('ELEMENT_NOT_FOUND', array($arItemPrepared, $arItem));
					continue;
				}

				// Обновить товар. Функция пишет в лог сама.
				$this->updateItem($arElement, $arItemPrepared);
			}
			$GLOBALS['LOG']->write('ELEMENTS_IMPORT_COMPLETE_BATCH', array("from" => $_REQUEST['offset'], "to" => $_REQUEST['offset'] + count($this->arImport)));
			return true;
		}

		function __construct($CONF){
			CModule::IncludeModule('iblock');
			CModule::IncludeModule('catalog');
			CModule::IncludeModule("sale");
			$this->settings = $CONF;
			$this->loadBrands();
		}
	}
?>
