<?php

if (CModule::IncludeModule("search")) {

	class HkCSearchSphinx extends CSearchSphinx {
		
		public static $trigramData = array();
		
		public $sphinxTrigram = false;
		public $debugShowLog = false;
		
		/**
		 * Получает первый ключ массива
		 * @param array $arr - массив
		 * @return string
		 */
		
		public function array_key_first(array $arr) {
			foreach($arr as $key => $unused) {
				return $key;
			}
			return NULL;
		}
		
		/**
		 * Транслит слова, возвращает 4 варианта:
		 * 
		 * 0. Русский текст в транслите (tehnika = техника)
		 * 1. Английский текст в транслите (миеле = miele)
		 * 2. Кирилица текст в лантинской раскладке (nt[ybrf - техника)
		 * 3. Латинский текст в кирилице (ьшуду = miele)
		 * 
		 * @param string $word - слово
		 * @return array
		 */

		public function translit($word) {
			$search1 = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
			$search2 = array('ch', 'ya', 'zh', 'sh', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
			$search3 = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
			$search4 = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', ',', '.', '\'', '[', ']', '`');
			$replace1 = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sh', '', 'i', '', 'e', 'u', 'ya');
			$replace2 = array('ч', 'я', 'ж', 'ш', 'а', 'б', 'с', 'д', 'е', 'ф', 'г', 'х', 'и', 'г', 'к', 'л', 'м', 'н', 'о', 'п', 'к', 'р', 'с', 'т', 'у', 'в', 'в', 'х', 'ю', 'я');
			$replace3 = array('f', ',', 'd', 'u', 'l', 't', '`', ';', 'p', 'b', 'q', 'r', 'k', 'v', 'y', 'j', 'g', 'h', 'c', 'n', 'e', 'a', '[', 'w', 'x', 'i', 'o', ']', 's', 'm', '\'', '.', 'z');
			$replace4 = array('ф', 'и', 'с', 'в', 'у', 'а', 'п', 'р', 'ш', 'о', 'л', 'д', 'ь', 'т', 'щ', 'з', 'й', 'к', 'ы', 'е', 'г', 'м', 'ц', 'ч', 'н', 'я', 'б', 'ю', 'э', 'х', 'ъ', 'ё');

			return array(
				array(str_replace($search1, $replace1, $word), 35), // Русский текст в транслите (tehnika = техника)
				array(str_replace($search2, $replace2, $word), 35), // Английский текст в транслите (миеле = miele)
				array(str_replace($search3, $replace3, $word), 35), // Кирилица текст в лантинской раскладке (nt[ybrf - техника)
				array(str_replace($search4, $replace4, $word), 35), // Латинский текст в кирилице (ьшуду = miele)
			);
		}

		/**
		 * Создание списка уже существующих слов для более быстрой обработки
		 * @global type $DB
		 */

		public static function createIndexWord(){
			global $DB;
			$dbKeyword = $DB->Query("SELECT `keyword` FROM `sphinx_trigrams`");
			while($row = $dbKeyword->Fetch()) {
				self::$trigramData[] = $row['keyword'];
			}
		}

		/**
		 * Создание словоформы
		 * @param string $word - слово
		 * @param integer $min - количество символов, на которые нужно делить слово
		 * @return string
		 */

		public static function createTrigramWord($word, $min = 3) {

			$wordGr = array();
			$wordCount = mb_strlen($word, "UTF-8");
			for ($i = 0; $i < $wordCount; $i++) {
				for ($y = $i; $y < ($i + $min); $y++) {
					$wordGr[$y][] = mb_substr($word, $i, 1, "UTF-8");
				}
			}

			foreach ($wordGr as $key => $words) {
				$sub = '';
				if (count($words) < $min) {

					for ($i = 0; $i < ($min - count($words)); $i++) {
						$sub .= '_';
					}
				}

				if ($key < $min) {
					$wordGr[$key] = $sub . implode('', $words);
				} else {
					$wordGr[$key] = implode('', $words) . $sub;
				}
			}

			return implode(' ', $wordGr);
		}
		
		/**
		 * Сравнивает 2 слова и выдаёт процент совпадения символов (возможен вариант до 200%)
		 * @param string $word1
		 * @param string $word2
		 * @return float
		 */
		
		public function comparisonWords($word1, $word2) {
			$noSymbol = array(
				array('ь', 'ъ', 'ы', 'и', 'й'),
				array('', '', '', '', '')
			);

			$word1 = str_replace($noSymbol[0], $noSymbol[1], $word1);
			$word2 = str_replace($noSymbol[0], $noSymbol[1], $word2);

			if (mb_strtolower($word1, "UTF-8") == mb_strtolower($word2, "UTF-8")) {
				return 1;
			}

			if (stripos($word1, $word2) !== false) {
				return 40;
			}

			$countOtherWord = 0;
			$symbols = array();

			for ($i = 0; $i < mb_strlen($word1, "UTF-8"); $i++) {
				$symbol = mb_strtolower(mb_substr($word1, $i, 1, "UTF-8"), "UTF-8");
				if (!isset($symbols[$symbol])) {
					$symbols[$symbol] = 0;
				}

				$symbols[$symbol]++;
			}

			for ($i = 0; $i < mb_strlen($word2, "UTF-8"); $i++) {
				$symbol = mb_strtolower(mb_substr($word2, $i, 1, "UTF-8"), "UTF-8");
				if (!isset($symbols[$symbol])) {
					$countOtherWord++;
					continue;
				}

				$symbols[$symbol]--;
				if ($symbols[$symbol] <= 0) {
					unset($symbols[$symbol]);
				}
			}

			if (count($symbols) > 0) {
				$countOtherWord = $countOtherWord + array_sum($symbols);
			}

			return (float) ((100 / mb_strlen($word1, "UTF-8")) * $countOtherWord);
		}
		
		/**
		 * Поиск похожих фраз для запроса с исправлением слов
		 * @param string $word - запрос запроса
		 * @param boolean $keybord - исправлять раскладку клавиатуры и опечатки в словах
		 * @param integer $limit - максимальное количество возможных запросов
		 * @return array|boolean
		 */

		public function findWord($word, $keybord = false, $limit = 20){
			global $DB;

			if ($word) {

				if ($keybord === true) {
					$wordsKeyboard = $this->translit($word);
					foreach ($wordsKeyboard as $wordKeyboard) {
						if ($wordKeyboard[0] != $word) {
							$wordSel = $this->findWord($wordKeyboard[0], false, 1);
							if ($this->debugShowLog === true) {
								echo $wordKeyboard[0] . ' - ' . $wordSel[0]. "<br>";
							}
							$comparisonPercent = $this->comparisonWords($wordSel[0], $wordKeyboard[0]);
							if ($comparisonPercent < $wordKeyboard[1]) {
								if ($this->debugShowLog === true) {
									echo "Слово <b>" . $word . "</b> изменено на <b>" . $wordSel[0] . " (процент отклонения от " . $wordKeyboard[0] . " = " . $comparisonPercent . ", допустимый не более " . $wordKeyboard[1] . ")</b><br>";
								}
								$word = $wordSel[0];
								break;
							} else {
								if ($this->debugShowLog === true) {
									echo "Слово <b>" . $word . "</b> не изменено на " . $wordSel[0] . " (процент отклонения от " . $wordKeyboard[0] . " = " . $comparisonPercent . ", допустимый не более " . $wordKeyboard[1] . ")<br>";
								}
							}
						}
					}
				}

				$searchId = array();
				if ($this->sphinxTrigram === false) {
					$this->sphinxTrigram = new CSearchSphinx;
					$this->sphinxTrigram->connect(COption::GetOptionString("search", "sphinx_connection"), "trigramindex");
				}

				$sqlWord = $this->sphinxTrigram->Escape($this->createTrigramWord(trim($word)));
				$findSql = $this->sphinxTrigram->query("SELECT * FROM trigramindex WHERE MATCH('\"" . $sqlWord . "\"/2') LIMIT " . $limit . ";");
				if ($findSql) {
					while($row = $this->sphinxTrigram->fetch($findSql)) {
						$searchId[] = $row['id'];
					}

					if (count($searchId) > 0) {
						$dbKeywordId = array();
						$dbKeyword = $DB->Query("SELECT `id`, `keyword` FROM `sphinx_trigrams` WHERE `id` IN (" . implode(',', $searchId) . ")");
						while($row = $dbKeyword->Fetch()) {
							if (!preg_match("/[A-Za-zА-Яа-я]/is", $word) && mb_strlen($word) > mb_strlen($row['keyword'])) {
								continue;
							}

							$dbKeywordId[$row['id']] = $row['keyword'];
						}

						$return = array();
						foreach ($searchId as $id) {
							if (!isset($dbKeywordId[$id])) {
								continue;
							}

							$return[] = $dbKeywordId[$id];
						}

						return $keybord !== true ? $return : array(
							'list'		=> $return,
							'word'		=> $word
						);
					}
				}
			}

			return false;
		}
		
		/**
		 * Поиск id записей индекса в sphinx
		 * @param string $query - запрос
		 * @return array|boolean
		 */
		
		public function findQuery($query){
			if ($query) {

				$searchId = array();
				if ($this->sphinxTrigram === false) {
					$this->sphinxTrigram = new CSearchSphinx;
					$this->sphinxTrigram->connect(COption::GetOptionString("search", "sphinx_connection"), "trigramindex");
				}

				$sqlWord = $this->sphinxTrigram->Escape($this->createTrigramWord(trim($word)));
				if ($this->debugShowLog === true) {
					echo "SELECT * FROM euroflettindex WHERE MATCH('" . $query . "') LIMIT 100;";
				}
				$findSql = $this->sphinxTrigram->query("SELECT * FROM euroflettindex WHERE MATCH('" . $query . "') LIMIT 100;");
				if ($findSql) {
					while($row = $this->sphinxTrigram->fetch($findSql)) {
						$searchId[$row['id']] = $row['id'];
					}

					return $searchId;
				}
			}

			return false;
		}
		
		/**
		 * See CSearchSphinx::search
		 */
		
		public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
		{		
			if ($arParams['QUERY']) {
				if ($_GET['debugg'] == '1') {
					$this->debugShowLog = true;
				}
				$queryReal 		= str_replace('"', '', $arParams['QUERY']);

				$show_query 	= array($queryReal, "*" . $queryReal . "*");

				$new_query 		= array($queryReal, "*" . $queryReal . "*");

				$word_searches 	= array();

				$words 			= explode(" ", $queryReal);

				foreach ($words as $key => $value) {
					$word_searches_arr = $this->findWord($value, true);
					$word_searches[$key] = $word_searches_arr['list'];
					if (is_array($word_searches[$key])) {
						foreach ($word_searches[$key] as $key_search => $word_search) {
							if (!preg_match("/[A-Za-zА-Яа-я]/is", $word_searches_arr['word'])) {
								// Подразумевается поиск по артикулу т.к. в запрос введены только цифры
								if (stripos($word_search, $word_searches_arr['word']) !== false) {
									// Если в предложенном варианте от self::findWord есть точное совпадение фразы
									$comparisonPercent = 30;
								} else {
									// Если нет точного совпадение запроса в варианте, то технически просто откидываем данный вариант
									$comparisonPercent = 100;
								}
							} else {
								$comparisonPercent = $this->comparisonWords($word_searches_arr['word'], $word_search);
							}

							if ($comparisonPercent > 49) {
								if ($this->debugShowLog === true) {
									echo "Фраза " . $word_search . " отклонена из-за того, что процент отклонения от " . $value . " = " . $comparisonPercent . " (максимум 49)<br>";
								}
								unset($word_searches[$key][$key_search]);
							} else {
								if ($this->debugShowLog === true) {
									echo "<b>Фраза " . $word_search . " принята</b>, процент отклонения от " . $value . " = " . $comparisonPercent . " (максимум 49)<br>";
								}
							}
						}
					}

					if (!is_array($word_searches[$key]) || count($word_searches[$key]) <= 0) {
						unset($word_searches[$key]);
					}
				}
				
				if ($this->debugShowLog === true) {
					echo "<pre>";
					print_r($word_searches);
					echo "</pre>";
				}
				
				$word_searches = array_values($word_searches);
				foreach ($word_searches[0] as $key => $value) {
					$string_query = array();
					for ($i = 0; $i < count($word_searches); $i++) {
						if ($word_searches[$i][$key]) {
							$string_query[] = $this->Escape($word_searches[$i][$key]);
						}
					}

					$show_query[] = implode(' ', $string_query);
					$new_query[] = implode(' ', $string_query);
				}
				
				if ($this->debugShowLog === true) {
					echo "Искомые подстроки 2: " . implode(' | ', $show_query) . "<br><br>";
				}
			}

			$result = array();
			$this->errorText = "";
			$this->errorNumber = 0;

			$this->tags = trim($arParams["TAGS"]);

			$limit = 0;
			if (is_array($aParamsEx) && isset($aParamsEx["LIMIT"]))
			{
				$limit = intval($aParamsEx["LIMIT"]);
				unset($aParamsEx["LIMIT"]);
			}

			$offset = 0;
			if (is_array($aParamsEx) && isset($aParamsEx["OFFSET"]))
			{
				$offset = intval($aParamsEx["OFFSET"]);
				unset($aParamsEx["OFFSET"]);
			}

			if (is_array($aParamsEx) && !empty($aParamsEx))
			{
				$aParamsEx["LOGIC"] = "OR";
				$arParams[] = $aParamsEx;
			}

			$this->SITE_ID = $arParams["SITE_ID"];

			$arWhere = array();
			$cond1 = implode("\n\t\t\t\t\t\tand ", $this->prepareFilter($arParams, true));

			$rights = $this->CheckPermissions();
			if ($rights)
				$arWhere[] = "right in (".$rights.")";

			$strQuery = trim($arParams["QUERY"]);

			if ($strQuery != "")
			{
				// @see https://hardkod.megaplan.ru/task/1038878/card/
				if ( preg_match("/^[\sa-z0-9]+$/ui", trim($queryReal)) )
				{
					$append = trim(preg_replace("/\s*/ui", "", $queryReal));
					$new_query[] = "*" . $append . "*";
				}

				$searchIds = $this->findQuery("\"". (count($new_query) > 0 ? implode('"/2 | "', $new_query) : $this->Escape($strQuery))."\"/2");

				if (count($searchIds) > 0) 
				{
					$result = $searchIds;
					$arWhere[] = "`id` IN (" . implode(',', $searchIds) . ")";
				} 
				else 
				{
					return array();
					$arWhere[] = "MATCH('\"*" . $this->Escape($strQuery) . "*\"')";
				}

				$this->query = $strQuery;
			}

			if ($cond1 != "")
				$arWhere[] = "cond1 = 1";

			if ($strQuery || $this->tags || $bTagsCloud)
			{
				if ($limit <= 0)
				{
					$limit = intval(COption::GetOptionInt("search", "max_result_size"));
				}

				if ($limit <= 0)
				{
					$limit = 500;
				}

				$ts = time()-CTimeZone::GetOffset();
				if ($bTagsCloud)
				{
					$sql = "
						select groupby() tag_id
						,count(*) cnt
						,max(date_change) dc_tmp
						,if(date_to, date_to, ".$ts.") date_to_nvl
						,if(date_from, date_from, ".$ts.") date_from_nvl
						".($cond1 != ""? ",$cond1 as cond1": "")."
						from ".$this->indexName."
						where ".implode("\nand\t", $arWhere)."
						group by tags
						#order by cnt desc
						limit 0, ".$limit."
						option ranker=fieldmask, max_matches = ".$limit."
					";
					$DB = CDatabase::GetModuleConnection('search');
					$startTime = microtime(true);
					if ($this->debugShowLog === true) {
						echo $sql . "!!!<br>";
					}
					$r =  $this->query($sql);

					if($DB->ShowSqlStat)
						$DB->addDebugQuery($sql, microtime(true)-$startTime);

					if (!$r)
					{
						throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', mysql_error($this->db), $sql);
					}
					else
					{
						while($res = $this->fetch($r)) {
							if (count($searchIds) > 0) {
								$result[$res['id']] = $res;
							} else {
								$result[] = $res;
							}
						}
					}
					
					if ($this->debugShowLog === true) {
						//print_r($result);
					}
				}
				else
				{
					$sql = "
						select id
						,item
						,param1
						,param2
						,module_id
						,param2_id
						,date_change
						,custom_rank
						,weight() as rank
						".($cond1 != ""? ",$cond1 as cond1": "")."
						,if(date_to, date_to, ".$ts.") date_to_nvl
						,if(date_from, date_from, ".$ts.") date_from_nvl
						from ".$this->indexName."
						where ".implode("\nand\t", $arWhere)."
						".$this->__PrepareSort($aSort)."
						limit ".$offset.", ".$limit."
						option ranker=fieldmask, max_matches = ".($offset + $limit)."
					";

					$DB = CDatabase::GetModuleConnection('search');
					$startTime = microtime(true);
					
					if ($this->debugShowLog === true) {
						echo $sql . "***<br>";
					}
					
					$r =  $this->query($sql);

					if($DB->ShowSqlStat)
						$DB->addDebugQuery($sql, microtime(true)-$startTime);

					if (!$r)
					{
						throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', mysqli_error($this->db), $sql);
					}
					else
					{
						$forum = sprintf("%u", crc32("forum"));
						while($res = $this->fetch($r))
						{
							if($res["module_id"] == $forum)
							{
								if (array_key_exists($res["param2_id"], $this->arForumTopics))
									continue;
								$this->arForumTopics[$res["param2_id"]] = true;
							}
							
							//$queryReal
							
							if (count($searchIds) > 0) {
								$result[$res['id']] = $res;
							} else {
								$result[] = $res;
							}
						}
					}

					// @see https://hardkod.megaplan.ru/task/1038878/card/
					if (preg_match("/^[\sa-z0-9]+$/ui", trim($queryReal)))
					{
						$append = trim(preg_replace("/\s*/ui", "", $queryReal));

						if ( $append != $queryReal )
						{
							$sql = "
									select id
									,item
									,param1
									,param2
									,module_id
									,param2_id
									,date_change
									,custom_rank
									,weight() as rank
									".($cond1 != ""? ",$cond1 as cond1": "")."
									,if(date_to, date_to, ".$ts.") date_to_nvl
									,if(date_from, date_from, ".$ts.") date_from_nvl
									from ".$this->indexName."
									where MATCH('(*" . $queryReal . "*)|(*" . $append  . "*)') 
									".$this->__PrepareSort($aSort)."
									limit ".$offset.", ".$limit."
									option ranker=fieldmask, max_matches = ".($offset + $limit)."
								";

							$DB = CDatabase::GetModuleConnection('search');
							$startTime = microtime(true);

							$r =  $this->query($sql);

							if($DB->ShowSqlStat)
								$DB->addDebugQuery($sql, microtime(true)-$startTime);

							if (!$r)
							{
								throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', mysqli_error($this->db), $sql);
							}
							else
							{
								$forum = sprintf("%u", crc32("forum"));
								while($res = $this->fetch($r))
								{
									if($res["module_id"] == $forum)
									{

										if (array_key_exists($res["param2_id"], $this->arForumTopics))
											continue;
										$this->arForumTopics[$res["param2_id"]] = true;
									}
									
									if (count($searchIds) > 0) {
										$result[$res['id']] = $res;
									} else {
										$result[] = $res;
									}
								}
							}
						}
					}
					
					if ($this->debugShowLog === true) {
						//echo "<pre>";
						//var_dump($result);
						//echo "</pre>";
					}
				}
			}
			else
			{
				$this->errorText = GetMessage("SEARCH_ERROR3");
				$this->errorNumber = 3;
			}
			
			return $result;
		}

		/**
		 * Создание индекса слова для строки
		 * @global type $DB
		 * @param string $content - строка со словами
		 */

		public static function createTrigrams($content) {
			global $DB;
			if (count(self::$trigramData) == 0) {
				self::createIndexWord();
			}

			$content = str_replace(array('(', ')', "'", '"', '{', '}'), array('', '', '', '', '', ''), $content);
			$content = preg_replace( "#\\s+#ism", " ", $content );
			$content = explode(" ", $content);
			foreach ($content as $word) {
				$word = trim($word);
				if (!in_array($word, self::$trigramData) && mb_strlen($word, "UTF-8") >= 3) {
					self::$trigramData[] = $word;
					$wordTr = self::createTrigramWord($word);
					$strSql = "INSERT INTO sphinx_trigrams (`keyword`, `trigrams`, `freq`) VALUES ('" . $DB->ForSql($word) . "', '" . $DB->ForSql($wordTr) . "', '')";
					$DB->Query($strSql);
				}
			}
		}

		/**
		 * Событие перед индексацией для добавления слов в словарь
		 * @param type $arFields
		 * @return type
		 */

		public static function onBeforeIndex($arFields){
			if ($arFields['MODULE_ID'] == 'iblock' && CModule::IncludeModule("iblock")) {	
				
				$arSelect = array("ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT", "IBLOCK_ID", "PROPERTY_*");
				$arFilter = array("CHECK_PERMISSIONS" => "N", "ID" => $arFields["ITEM_ID"], "IBLOCK_ID" => $arFields["PARAM2"]);
				$arOrder = array();
				$rsItem = CIBlockElement::GetList($arOrder, $arFilter, false, array(), $arSelect);
				if (intval($rsItem->SelectedRowsCount())>0){
					$obItem = $rsItem->GetNextElement();
					$arItem = $obItem->GetFields();
					$arProps = $obItem->GetProperties();
					$content_text = $arItem['DETAIL_TEXT'] ? $arItem['DETAIL_TEXT'] : $arItem['PREVIEW_TEXT'];
					self::createTrigrams(strip_tags($arItem['NAME']));
					self::createTrigrams(strip_tags($content_text));
					$arFields['BODY'] = array();
					
					foreach ($arProps as $code => $arProp) {
						if ($arProp['SEARCHABLE'] == 'Y' && !in_array($arProp['CODE'], array('SEARCHING'))) {
							self::createTrigrams($arProp['VALUE']);
							$arFields['BODY'][] = $arProp['VALUE'];
						}
					}
					
					$arFields['BODY'][] = $content_text;
					$arFields['BODY'] = implode("\n", $arFields['BODY']);
				}
			} else {
				self::createTrigrams($arFields['TITLE']);
				self::createTrigrams($arFields['BODY']);
			}

			return $arFields;
		}

	}

	class HkCSearch extends CSearch {
		function Search($arParams, $aSort = array(), $aParamsEx = array(), $bTagsCloud = false) {
			$DB = CDatabase::GetModuleConnection('search');

			if (!is_array($arParams))
				$arParams = array("QUERY" => $arParams);

			if (!is_set($arParams, "SITE_ID") && is_set($arParams, "LID"))
			{
				$arParams["SITE_ID"] = $arParams["LID"];
				unset($arParams["LID"]);
			}

			if (array_key_exists("TAGS", $arParams))
			{
				$this->strTagsText = $arParams["TAGS"];
				$arTags = explode(",", $arParams["TAGS"]);
				foreach ($arTags as $i => $strTag)
				{
					$strTag = trim($strTag);
					if (strlen($strTag))
						$arTags[$i] = str_replace("\"", "\\\"", $strTag);
					else
						unset($arTags[$i]);
				}

				if (count($arTags))
					$arParams["TAGS"] = '"'.implode('","', $arTags).'"';
				else
					unset($arParams["TAGS"]);
			}

			$this->strQueryText = $strQuery = trim($arParams["QUERY"]);
			$this->strTags = $strTags = $arParams["TAGS"];

			if ((strlen($strQuery) <= 0) && (strlen($strTags) > 0))
			{
				$strQuery = $strTags;
				$bTagsSearch = true;
			}
			else
			{
				if (strlen($strTags))
					$strQuery .= " ".$strTags;
				$strQuery = preg_replace_callback("/&#(\\d+);/", array($this, "chr"), $strQuery);
				$bTagsSearch = false;
			}

			$fullTextParams = $aParamsEx;
			$fullTextParams["LIMIT"] = $this->limit;
			$fullTextParams["OFFSET"] = $this->offset;
			$result = HkCSearchFullText::getInstance()->search($arParams, $aSort, $fullTextParams, $bTagsCloud);
			if (is_array($result))
			{
				$this->error = HkCSearchFullText::getInstance()->getErrorText();
				$this->errorno = HkCSearchFullText::getInstance()->getErrorNumber();
				$this->formatter = HkCSearchFullText::getInstance()->getRowFormatter();
				if ($this->errorno > 0)
					return;
			}
			else
			{
				if (!array_key_exists("STEMMING", $aParamsEx))
					$aParamsEx["STEMMING"] = COption::GetOptionString("search", "use_stemming") == "Y";

				$this->Query = new CSearchQuery("and", "yes", 0, $arParams["SITE_ID"]);
				if ($this->_opt_NO_WORD_LOGIC)
					$this->Query->no_bool_lang = true;

				$query = $this->Query->GetQueryString((BX_SEARCH_VERSION > 1? "sct": "sc").".SEARCHABLE_CONTENT", $strQuery, $bTagsSearch, $aParamsEx["STEMMING"], $this->_opt_ERROR_ON_EMPTY_STEM);
				if (!$query || strlen(trim($query)) <= 0)
				{
					if ($bTagsCloud)
					{
						$query = "1=1";
					}
					else
					{
						$this->error = $this->Query->error;
						$this->errorno = $this->Query->errorno;
						return;
					}
				}

				if (strlen($query) > 2000)
				{
					$this->error = GetMessage("SEARCH_ERROR4");
					$this->errorno = 4;
					return;
				}
			}

			foreach (GetModuleEvents("search", "OnSearch", true) as $arEvent)
			{
				$r = "";
				if ($bTagsSearch)
				{
					if (strlen($strTags))
						$r = ExecuteModuleEventEx($arEvent, array("tags:".$strTags));
				}
				else
				{
					$r = ExecuteModuleEventEx($arEvent, array($strQuery));
				}
				if ($r <> "")
					$this->url_add_params[] = $r;
			}

			if (is_array($result))
			{
				$r = new CDBResult;
				$r->InitFromArray($result);
			}
			elseif (
				BX_SEARCH_VERSION > 1
				&& count($this->Query->m_stemmed_words_id)
				&& array_sum($this->Query->m_stemmed_words_id) === 0
			)
			{
				$r = new CDBResult;
				$r->InitFromArray(array());
			}
			else
			{
				$this->strSqlWhere = "";
				$bIncSites = false;

				$arSqlWhere = array();
				if (is_array($aParamsEx) && !empty($aParamsEx))
				{
					foreach ($aParamsEx as $aParamEx)
					{
						$strSqlWhere = CSearch::__PrepareFilter($aParamEx, $bIncSites);
						if ($strSqlWhere != "")
							$arSqlWhere[] = $strSqlWhere;
					}
				}
				if (!empty($arSqlWhere))
				{
					$arSqlWhere = array(
						"\n\t\t\t\t(".implode(")\n\t\t\t\t\tOR(", $arSqlWhere)."\n\t\t\t\t)",
					);
				}

				$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
				if ($strSqlWhere != "")
					array_unshift($arSqlWhere, $strSqlWhere);

				$strSqlOrder = $this->__PrepareSort($aSort, "sc.", $bTagsCloud);

				if (!array_key_exists("USE_TF_FILTER", $aParamsEx))
					$aParamsEx["USE_TF_FILTER"] = COption::GetOptionString("search", "use_tf_cache") == "Y";

				$bStem = !$bTagsSearch && count($this->Query->m_stemmed_words) > 0;
				//calculate freq of the word on the whole site_id
				if ($bStem && count($this->Query->m_stemmed_words))
				{
					$arStat = $this->GetFreqStatistics($this->Query->m_lang, $this->Query->m_stemmed_words, $arParams["SITE_ID"]);
					$this->tf_hwm_site_id = (strlen($arParams["SITE_ID"]) > 0? $arParams["SITE_ID"]: "");

					//we'll make filter by it's contrast
					if (!$bTagsCloud && $aParamsEx["USE_TF_FILTER"])
					{
						$hwm = false;
						foreach ($this->Query->m_stemmed_words as $i => $stem)
						{
							if (!array_key_exists($stem, $arStat))
							{
								$hwm = 0;
								break;
							}
							elseif ($hwm === false)
							{
								$hwm = $arStat[$stem]["TF"];
							}
							elseif ($hwm > $arStat[$stem]["TF"])
							{
								$hwm = $arStat[$stem]["TF"];
							}
						}

						if ($hwm > 0)
						{
							$arSqlWhere[] = "st.TF >= ".number_format($hwm, 2, ".", "");
							$this->tf_hwm = $hwm;
						}
					}
				}

				if (!empty($arSqlWhere))
				{
					$this->strSqlWhere = "\n\t\t\t\tAND (\n\t\t\t\t\t(".implode(")\n\t\t\t\t\tAND(", $arSqlWhere).")\n\t\t\t\t)";
				}

				if ($bTagsCloud)
					$strSql = $this->tagsMakeSQL($query, $this->strSqlWhere, $strSqlOrder, $bIncSites, $bStem, $aParamsEx["LIMIT"]);
				else
					$strSql = $this->MakeSQL($query, $this->strSqlWhere, $strSqlOrder, $bIncSites, $bStem);

				$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			parent::CDBResult($r);
		}
	}
	
	class HkCSearchFullText extends CSearchFullText {
		/**
		* Returns current instance of the full text indexer.
		*
		* @return CSearchFullText
		*/
	   public static function getInstance()
	   {
		   if (!isset(static::$instance))
		   {
			   if (COption::GetOptionString("search", "full_text_engine") === "sphinx")
			   {
				   self::$instance = new HkCSearchSphinx;
				   self::$instance->connect(
					   COption::GetOptionString("search", "sphinx_connection"),
					   COption::GetOptionString("search", "sphinx_index_name")
				   );
			   }
			   else
			   {
				   self::$instance = new CSearchStemTable();
			   }
		   }
		   return static::$instance;
	   }
	}
}