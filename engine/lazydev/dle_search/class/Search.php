<?php
/**
 * Логика поиска
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Search;

setlocale(LC_NUMERIC, 'C');

class Search
{
	private static $instance = null;
	static $modSearch;
	private static $dleConfig, $dleDb, $dleCat, $dleMember, $dleXfields, $dleGroups, $modReplace;
	public static $modConfig, $modLang;
	static $query, $ruQuery, $enQuery, $info;
	static $selectSql = [];
	static $whereSql = [];
	static $orderSql = [];
	static $originalQuery = [];
	static $innerTable, $whereTable;
	static $checks, $link;
	public static $seo = [];

	private static $orderByKeys = [
		'p.date' => 'date',
		'p.editdate' => 'editdate',
		'p.title' => 'title',
		'p.comm_num' => 'comm_num',
		'p.news_read' => 'news_read',
		'p.autor' => 'autor',
		'p.rating' => 'rating',
	];

	static $enChar = [
		'ё' => '`',    'й' => 'q',    'ц' => 'w',    'у' => 'e',    'к' => 'r',    'е' => 't',    'н' => 'y',    'г' => 'u',
		'ш' => 'i',    'щ' => 'o',    'з' => 'p',    'х' => '[',    'ъ' => ']',    'ф' => 'a',    'ы' => 's',    'в' => 'd',
		'а' => 'f',    'п' => 'g',    'р' => 'h',    'о' => 'j',    'л' => 'k',    'д' => 'l',    'ж' => ';',    'э' => '&#039;',
		'я' => 'z',    'ч' => 'x',    'с' => 'c',    'м' => 'v',    'и' => 'b',    'т' => 'n',    'ь' => 'm',    'б' => ',',
		'.' => 'ю',
		'Ё' => '~',    'Й' => 'Q',    'Ц' => 'W',    'У' => 'E',    'К' => 'R',    'Е' => 'T',    'Н' => 'Y',    'Г' => 'U',
		'Ш' => 'I',    'Щ' => 'O',    'З' => 'P',    'Х' => '{',    'Ъ' => '}',    'Ф' => 'A',    'Ы' => 'S',    'В' => 'D',
		'А' => 'F',    'П' => 'G',    'Р' => 'H',    'О' => 'J',    'Л' => 'K',    'Д' => 'L',    'Ж' => ':',    'Э' => '&quot;',
		'Я' => 'Z',    'Ч' => 'X',    'С' => 'C',    'М' => 'V',    'И' => 'B',    'Т' => 'N',    'Ь' => 'M',    'Б' => '<',
		'>' => 'Ю',
	];

	static $ruChar = [
		'`' => 'ё',    'q' => 'й',    'w' => 'ц',    'e' => 'у',    'r' => 'к',    't' => 'е',    'y' => 'н',    'u' => 'г',
		'i' => 'ш',    'o' => 'щ',    'p' => 'з',    '[' => 'х',    ']' => 'ъ',    'a' => 'ф',    's' => 'ы',    'd' => 'в',
		'f' => 'а',    'g' => 'п',    'h' => 'р',    'j' => 'о',    'k' => 'л',    'l' => 'д',    ';' => 'ж',    '&#039;'=> 'э',
		'z' => 'я',    'x' => 'ч',    'c' => 'с',    'v' => 'м',    'b' => 'и',    'n' => 'т',    'm' => 'ь',    ',' => 'б',
		'.' => 'ю',
		'~' => 'ё',    'Q' => 'й',    'W' => 'ц',    'E' => 'у',    'R' => 'к',    'T' => 'е',    'Y' => 'н',    'U' => 'г',
		'I' => 'ш',    'O' => 'щ',    'P' => 'з',    '{' => 'х',    '}' => 'ъ',    'A' => 'ф',    'S' => 'ы',    'D' => 'в',
		'F' => 'а',    'G' => 'п',    'H' => 'р',    'J' => 'о',    'K' => 'л',    'L' => 'д',    ':' => 'ж',    '&quot;' => 'э',
		'Z' => 'я',    'X' => 'ч',    'C' => 'с',    'V' => 'м',    'B' => 'и',    'N' => 'т',    'M' => 'ь',    '&lt;' => 'б',
		'&gt;' => 'ю',
	];

	/**
	 * Конструктор
	 *
	 * @return   Search
	 */
	static function construct()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Старт модуля
	 *
	 * @param    string    $modSearch
	 * @param    string    $thisUrl
	 *
	 * @return   Search
	 */
	static function load($modSearch, $thisUrl = '')
	{
		global $config, $db, $cat_info, $member_id, $user_group;

		self::$dleConfig = $config;
		self::$dleDb = $db;
		self::$dleCat = $cat_info;
		self::$dleMember = $member_id;
		self::$dleGroups = $user_group;
		self::$dleXfields = xfieldsload();
		self::$modConfig = Data::receive('config');
		self::$modLang = Data::receive('lang');
		self::$modReplace = Data::receive('replace');
		self::$link = $thisUrl;

		if (self::$modConfig['match']) {
			unset(self::$modConfig['rows'], self::$modConfig['sort_by_pos'], self::$modConfig['sort_by_pos_xf'], self::$modConfig['concat']);
			self::$modConfig['between_space'] = self::$modConfig['keyboard'] = false;
		} elseif (self::$modConfig['concat'] != '') {
			unset(self::$modConfig['rows'], self::$modConfig['sort_by_pos'], self::$modConfig['sort_by_pos_xf']);
		} elseif (self::$modConfig['sort_by_pos']) {
			unset(self::$modConfig['rows'], self::$modConfig['sort_by_own'], self::$modConfig['sort_by_pos_xf']);
		} elseif (self::$modConfig['sort_by_pos_xf'] && self::$modConfig['sort_by_pos_xf'] != '-') {
			unset(self::$modConfig['rows'], self::$modConfig['sort_by_own'], self::$modConfig['sort_by_pos']);
		}

		self::$modConfig['maximum_news_ajax'] = self::validIntConfig(self::$modConfig['maximum_news_ajax'], 5);
		self::$modConfig['maximum_news_full'] = self::validIntConfig(self::$modConfig['maximum_news_full'], 10);
		self::$modConfig['minimum_char'] = self::validIntConfig(self::$modConfig['minimum_char'], 3);
		self::$modConfig['maximum_char'] = self::validIntConfig(self::$modConfig['maximum_char'], 30);

		self::$modSearch = $modSearch;

		return self::$instance;
	}

	/**
	 *  Валидация данных конфига
	 *
	 * @param int $v
	 * @param int $n
	 *
	 * @return int
	 */
	static function validIntConfig($v, $n)
	{
		return $v <= 0 ? $n : $v;
	}

	/**
	 * Получаем страницу DataLife Engine
	 *
	 */
	static function searchInPage()
	{
		if ((self::$modConfig['ajax_xfield'] && self::$modSearch == 'ajax') || self::$modConfig['full_xfield'] && self::$modSearch == 'full') {
			$letInXf = false;
			if (isset($_SERVER['HTTP_REFERER']) && substr_count($_SERVER['HTTP_REFERER'], 'xfsearch/') || self::$modSearch == 'ajax' && substr_count(self::$link, 'xfsearch/')) {
				self::$checks['xfsearch'] = true;

				$xf = self::$link ?: $_SERVER['HTTP_REFERER'];
				$xf = self::$dleConfig['version_id'] > 13.1 ? rawurldecode($xf) : urldecode($xf);
				$xf = explode('/page', $xf)[0];
				$xf = Helper::cleanSlash($xf);
				$xf = explode('/xfsearch/', $xf)[1];
				$xf = explode('/', $xf);

				if (self::$modSearch == 'ajax' && $xf[0] != '' && $xf[1] != '') {
					$xfName = totranslit(trim($xf[0]));
					$xfValue = htmlspecialchars(strip_tags(stripslashes(trim($xf[1]))), ENT_QUOTES, self::$dleConfig['charset']);
				} elseif (isset($_SERVER['HTTP_REFERER']) && count($xf) == 2) {
					$xfName = totranslit(trim($xf[0]));
					$xfValue = htmlspecialchars(strip_tags(stripslashes(trim($xf[1]))), ENT_QUOTES, self::$dleConfig['charset']);
				}

				if (self::$modConfig['full_xfield'] && self::$modSearch == 'full') {
					if ($xfName && $xfValue) {
						$_SESSION['searchXfName'] = $xfName;
						$_SESSION['searchXfValue'] = $xfValue;
					}

					if (!isset($_SESSION['searchQuery']) || isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] == self::$originalQuery['query']) {
						$_SESSION['searchQuery'] = self::$originalQuery['query'];
					} else {
						$_SESSION['searchQuery'] = false;
					}

					if (isset($_SESSION['searchXfName']) && $_SESSION['searchXfName'] && isset($_SESSION['searchXfValue']) && $_SESSION['searchXfValue'] && isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] && (strpos($_SERVER['HTTP_REFERER'], 'index.php?do=search') !== false || isset($_SESSION['searchXfName']) && $xfName == $_SESSION['searchXfName'] && isset($_SESSION['searchXfValue']) && $xfValue == $_SESSION['searchXfValue'])) {
						if (!$xfName && !$xfValue) {
							$xfName = $_SESSION['searchXfName'];
							$xfValue = $_SESSION['searchXfValue'];
						}
					} else {
						unset($_SESSION['searchXfName'], $_SESSION['searchXfValue']);
					}
				}

				if ($xfName && $xfValue) {
					foreach (self::$dleXfields as $xfieldArray) {
						if ($xfieldArray[0] == $xfName && $xfieldArray[6] == 1) {
							$letInXf = true;
							break;
						}
					}

					if ($letInXf) {
						self::$seo['xf-value'] = $xfValue;
						self::$seo['xf-name'] = $xfName;
						$xfName = self::$dleDb->safesql($xfName);
						$xfValue = self::$dleDb->safesql($xfValue);

						self::$whereTable = " AND xf.tagname='{$xfName}' AND xf.tagvalue='{$xfValue}'";
						self::$innerTable .= " INNER JOIN " . PREFIX . "_xfsearch xf ON (xf.news_id=p.id) ";
					}
				}
			}
		}

		if (!self::$checks && (self::$modConfig['ajax_tags'] && self::$modSearch == 'ajax' || self::$modConfig['full_tags'] && self::$modSearch == 'full')) {
			if (isset($_SERVER['HTTP_REFERER']) && substr_count($_SERVER['HTTP_REFERER'], 'tags/') || self::$modSearch == 'ajax' && substr_count(self::$link, 'tags/')) {
				self::$checks['tag'] = true;

				if (self::$link) {
					$tagTemp = explode('/', self::$link);
					if ($tagTemp[2] != '') {
						$tag = $tagTemp[2];
					}
				} elseif (isset($_SERVER['HTTP_REFERER'])) {
					$tag = $_SERVER['HTTP_REFERER'];
					$tag = explode('/page', $tag)[0];
					$tag = Helper::cleanSlash($tag);
					$tag = explode('/', $tag);
					$tag = trim(end($tag));
				}

				if (self::$modConfig['full_tags'] && self::$modSearch == 'full') {
					if ($tag) {
						$_SESSION['searchTag'] = $tag;
					}

					if (!isset($_SESSION['searchQuery']) || isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] == self::$originalQuery['query']) {
						$_SESSION['searchQuery'] = self::$originalQuery['query'];
					} else {
						$_SESSION['searchQuery'] = false;
					}

					if (isset($_SESSION['searchTag']) && $_SESSION['searchTag'] && isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] && (strpos($_SERVER['HTTP_REFERER'], 'index.php?do=search') !== false || isset($_SESSION['searchTag']) && $tag == $_SESSION['searchTag'])) {
						if (!$tag) {
							$tag = $_SESSION['searchTag'];
						}
					} else {
						unset($_SESSION['searchTag']);
					}
				}

				if ($tag) {
					$tag = self::$dleConfig['version_id'] > 13.1 ? rawurldecode($tag) : urldecode($tag);
					$tag = Helper::cleanSlash($tag);
					$tag = htmlspecialchars(strip_tags(stripslashes(trim($tag))), ENT_COMPAT, self::$dleConfig['charset']);
					self::$seo['tag'] = $tag;
					$tag = self::$dleDb->safesql($tag);

					self::$whereTable = " AND t.tag='{$tag}'";
					self::$innerTable .= " INNER JOIN " . PREFIX . "_tags t ON (t.news_id=p.id) ";
				}
			}
		}

		if (!self::$checks && (self::$modConfig['ajax_category'] && self::$modSearch == 'ajax' || self::$modConfig['full_category'] && self::$modSearch == 'full')) {
			$category_id = 0;
			if (isset($_SERVER['HTTP_REFERER']) || self::$modSearch == 'ajax' && self::$link != '') {
				self::$checks['cat'] = true;
				$cat = self::$link ?: $_SERVER['HTTP_REFERER'];
				$cat = explode('/page', $cat)[0];
				$cat = Helper::cleanSlash($cat);
				$cat = explode('/', $cat);
				$cat = trim(end($cat));
				if ($cat != '') {
					$category_id = get_ID(self::$dleCat, $cat);

					if (self::$modConfig['full_category'] && self::$modSearch == 'full') {
						if ($category_id > 0) {
							$_SESSION['searchCat'] = $category_id;
						}

						if (!isset($_SESSION['searchQuery']) || isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] == self::$originalQuery['query']) {
							$_SESSION['searchQuery'] = self::$originalQuery['query'];
						} else {
							$_SESSION['searchQuery'] = false;
						}

						if (isset($_SESSION['searchCat']) && $_SESSION['searchCat'] && isset($_SESSION['searchQuery']) && $_SESSION['searchQuery'] && (strpos($_SERVER['HTTP_REFERER'], 'index.php?do=search') !== false || isset($_SESSION['searchCat']) && $category_id == $_SESSION['searchCat'])) {
							if (!$category_id) {
								$category_id = intval($_SESSION['searchCat']);
							}
						} else {
							unset($_SESSION['searchCat']);
						}
					}

					if ($category_id > 0) {
						$catId = $category_id;
						$catName = [];
						if (self::$modConfig[self::$modSearch . '_category_all']) {
							$cat = [$category_id => $category_id];
							foreach (self::$dleCat as $cats) {
								if ($cats['parentid'] == $catId) {
									$cats['id'] = intval($cats['id']);
									$cat[$cats['id']] = $cats['id'];
									$catName[$cats['id']] = $cats['name'];
								}
							}

							if (self::$dleConfig['version_id'] > 13.1) {
								$category_id = implode("','", $cat);
							} else {
								$category_id = implode((self::$dleConfig['allow_multi_category'] ? '|' : "','"), $cat);
							}
						}

						self::$seo['cat'] = $catName ? implode(', ', $catName) : self::$dleCat[$catId]['name'];

						if (self::$dleConfig['version_id'] > 13.1) {
							self::$whereTable = '';
							self::$innerTable .= " INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $category_id . "')) c ON (p.id=c.news_id) ";
						} else {
							self::$whereSql[] = self::$dleConfig['allow_multi_category'] ? "category REGEXP '([[:punct:]]|^)('" . $category_id . "')([[:punct:]]|$)'" : "category IN ('" . $category_id . "')";
						}
					}
				}
			}
		}
	}

	/**
	 *  Обработка запроса
	 *
	 * @return    Search
	 */
	static function query()
	{
		$text = Helper::cleanSlash(trim(strip_tags(stripslashes(rawurldecode($_REQUEST['story'])))));
		$text = str_replace(['&amp;amp;', '&frasl;', '+'], ['&amp;', '/', ' '], $text);
		self::$query = str_replace(["#", "'", '"'], ["\#", "\'", '\"'], $text);
		self::$query = preg_replace('/\s+/u', ' ', self::$query);

		$replaceCheck = false;
		if (self::$modConfig['substitution'] && self::$modReplace) {
			$getReplace = array_filter(self::$modReplace, function ($val) {
				if ($val['full'] == 1) {
					foreach ($val['find'] as $item) {
						if (preg_match('/\b' . $item . '\b/iu', self::$query, $m) == 1) {
							$_REQUEST['story'] = self::$query = preg_replace('/' . $item . '/i', $val['replace'], self::$query);
							return true;
						}
					}
				}
			});

			if (!$getReplace) {
				$getReplace = array_filter(self::$modReplace, function ($val) {
					foreach ($val['find'] as $item) {
						if (stripos(mb_strtolower(self::$query), mb_strtolower($item)) !== false && !$val['full']) {
							$_REQUEST['story'] = self::$query = str_ireplace(mb_strtolower($item), $val['replace'], mb_strtolower(self::$query));
							return true;
						}
					}
				});
			}

			if ($getReplace) {
				$replaceCheck = true;
			}
		}

		self::$originalQuery['query'] = self::$query;
		self::$seo['search-value'] = self::$query;
		if (!$replaceCheck) {
			if (mb_strlen(self::$query, 'UTF-8') > self::$modConfig['maximum_char']) {
				self::$query = mb_substr(self::$query, 0, self::$modConfig['maximum_char'], 'UTF-8');
			}

			if (mb_strlen(self::$query, 'UTF-8') < self::$modConfig['minimum_char']) {
				$letter = Helper::declinationLazy([self::$modConfig['minimum_char'], self::$modLang['site']['letter']]);
				self::$info = str_replace(['{number}', '{letter}'], [self::$modConfig['minimum_char'], $letter], self::$modLang['site']['minimum_char']);
				return self::$instance;
			}
		}

		if (self::$modConfig['keyboard']) {
			self::$enQuery = strtr(self::$query, self::$enChar);
			self::$ruQuery = strtr(self::$query, self::$ruChar);

			if (self::$enQuery == self::$query) {
				self::$enQuery = false;
			} else {
				self::$enQuery = preg_replace('/\s+/u', ' ', self::$enQuery);
				self::$originalQuery['en'] = self::$enQuery;
				self::$enQuery = self::$dleDb->safesql(addslashes(self::$enQuery));
			}

			if (self::$ruQuery == self::$query) {
				self::$ruQuery = false;
			} else {
				self::$ruQuery = preg_replace('/\s+/u', ' ', self::$ruQuery);
				self::$originalQuery['ru'] = self::$ruQuery;
				self::$ruQuery = self::$dleDb->safesql(addslashes(self::$ruQuery));
			}
		}

		self::$query = self::$dleDb->safesql(addslashes(self::$query));

		return self::$instance;
	}

	/**
	 * Составляем запрос
	 */
	static function sql()
	{
		$replaceCharStart = $replaceCharEnd = '';

		if (self::$modConfig['replace_char']) {
			$arraySymbol = str_split(self::$modConfig['replace_char']);

			$replaceCharStart = [];
			$replaceCharEnd = [];

			$spaceChar = self::$modConfig['replace_space'] == 1 ? ' ' : '';

			self::$query = str_replace($arraySymbol, $spaceChar, self::$query);

			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					self::$ruQuery = str_replace($arraySymbol, $spaceChar, self::$ruQuery);
				}

				if (self::$enQuery) {
					self::$enQuery = str_replace($arraySymbol, $spaceChar, self::$enQuery);
				}
			}

			foreach ($arraySymbol as $value) {
				$value = self::$dleDb->safesql($value);
				$replaceCharStart[] = "REPLACE(";
				$replaceCharEnd[] = ", '{$value}', '{$spaceChar}')";
			}

			$replaceCharStart = implode($replaceCharStart);
			$replaceCharEnd = implode($replaceCharEnd);
		}

		if (self::$modConfig['between_space']) {
			self::$query = str_replace(' ', '%', self::$query);

			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					self::$ruQuery = str_replace(' ', '%', self::$ruQuery);
				}

				if (self::$enQuery) {
					self::$enQuery = str_replace(' ', '%', self::$enQuery);
				}
			}
		}

		self::$query = preg_replace('/%+/u', '%', self::$query);

		if (self::$modConfig['keyboard']) {
			if (self::$ruQuery) {
				self::$ruQuery = preg_replace('/%+/u', '%', self::$ruQuery);
			}

			if (self::$enQuery) {
				self::$enQuery = preg_replace('/%+/u', '%', self::$enQuery);
			}
		}

		if (self::$modConfig['match']) {
			self::$query = str_replace('%', ' ', self::$query);
			self::$query = preg_replace('/\s+/u', ' ', self::$query);

			if (self::$modConfig['match_all']) {
				$arrayMatch = explode(' ', self::$query);
				array_walk($arrayMatch, function (&$item, $key) {
					$item = '+' . $item;
				});
				$query = implode(' ', $arrayMatch);
			} else {
				$query = "+\"" . self::$query . "\"";
			}

			self::$whereSql[] = "MATCH (title, short_story, full_story, p.xfields) AGAINST ('{$query}')";
		} elseif (self::$modConfig['concat'] != '') {
			$arrayConcat = explode(' ', self::$modConfig['concat']);
			if (count($arrayConcat) > 1) {
				$tempConcat = [];
				foreach ($arrayConcat as $value) {
					$value = trim($value);

					if ($value == '{title}') {
						$tempConcat[] = '`title`';
					} elseif ($value == '{date}') {
						$tempConcat[] = 'date';
					} elseif ($value == '{id}') {
						$tempConcat[] = 'id';
					} elseif (strpos($value, '[xfvalue_') !== false) {
						$xf = str_replace(['[xfvalue_', ']'], '', $value);
						$tempConcat[] = "SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '{$xf}|', -1), '||', 1)";
					}
				}

				if (count($tempConcat) > 0) {
					$tempQuery = str_replace('%', ' ', self::$query);
					$tempArrayRows = [];

					if (self::$modConfig['sort_by_own']) {
						self::$selectSql[] = "LOCATE('{$tempQuery}', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) AS locate_own";

						if (self::$modConfig['true_locate']) {
							$tempArrayRows[] = "LOCATE('{$tempQuery}', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) > 0";
							self::$orderSql[] = 'locate_own ASC';
						} else {
							self::$orderSql[] = 'locate_own < 1 ASC, locate_own ASC';
						}

						if (self::$modConfig['keyboard']) {
							if (self::$ruQuery) {
								self::$selectSql[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) AS locate_ru_own";
							}

							if (self::$enQuery) {
								self::$selectSql[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) AS locate_en_own";
							}

							if (self::$modConfig['true_locate']) {
								if (self::$ruQuery) {
									$tempArrayRows[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) > 0";
									self::$orderSql[] = 'locate_ru_own ASC';
								}

								if (self::$enQuery) {
									$tempArrayRows[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd}) > 0";
									self::$orderSql[] = 'locate_en_own ASC';
								}
							} else {
								if (self::$ruQuery) {
									self::$orderSql[] = 'locate_ru_own < 1 ASC, locate_ru_own ASC';
								}

								if (self::$enQuery) {
									self::$orderSql[] = 'locate_en_own < 1 ASC, locate_en_own ASC';
								}
							}
						}
					}

					$tempArrayRows[] = "{$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd} LIKE '%" . self::$query . "%'";
					if (self::$modConfig['keyboard']) {
						if (self::$ruQuery) {
							$tempArrayRows[] = "{$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd} LIKE '%" . self::$ruQuery . "%'";
						}

						if (self::$enQuery) {
							$tempArrayRows[] = "{$replaceCharStart}CONCAT(" . implode(", ' ', ", $tempConcat) . "){$replaceCharEnd} LIKE '%" . self::$enQuery . "%'";
						}
					}


					if ($tempArrayRows) {
						self::$whereSql[] = '(' . implode(' OR ', $tempArrayRows) . ')';
					}
				}
			}
		} elseif (self::$modConfig['sort_by_pos']) {
			$tempQuery = str_replace('%', ' ', self::$query);
			$tempArrayRows = [];

			self::$modConfig['sort_by_pos_xf'] = self::$dleDb->safesql(self::$modConfig['sort_by_pos_xf']);

			$tempArrayRows[] = "{$replaceCharStart}`title`{$replaceCharEnd} LIKE '%" . self::$query . "%'";
			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					$tempArrayRows[] = "{$replaceCharStart}`title`{$replaceCharEnd} LIKE '%" . self::$ruQuery . "%'";
				}

				if (self::$enQuery) {
					$tempArrayRows[] = "{$replaceCharStart}`title`{$replaceCharEnd} LIKE '%" . self::$enQuery . "%'";
				}
			}

			self::$selectSql[] = "LOCATE('{$tempQuery}', {$replaceCharStart}`title`{$replaceCharEnd}) AS locate_title";

			if (self::$modConfig['true_locate']) {
				$tempArrayRows[] = "LOCATE('{$tempQuery}', {$replaceCharStart}`title`{$replaceCharEnd}) > 0";
				self::$orderSql[] = 'locate_title ASC';
			} else {
				self::$orderSql[] = 'locate_title < 1 ASC, locate_title ASC';
			}

			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					self::$selectSql[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}`title`{$replaceCharEnd}) AS locate_ru_title";
				}

				if (self::$enQuery) {
					self::$selectSql[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}`title`{$replaceCharEnd}) AS locate_en_title";
				}

				if (self::$modConfig['true_locate']) {
					if (self::$ruQuery) {
						$tempArrayRows[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}`title`{$replaceCharEnd}) > 0";
						self::$orderSql[] = 'locate_ru_title ASC';
					}

					if (self::$enQuery) {
						$tempArrayRows[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}`title`{$replaceCharEnd}) > 0";
						self::$orderSql[] = 'locate_en_title ASC';
					}
				} else {
					if (self::$ruQuery) {
						self::$orderSql[] = 'locate_ru_title < 1 ASC, locate_ru_title ASC';
					}

					if (self::$enQuery) {
						self::$orderSql[] = 'locate_en_title < 1 ASC, locate_en_title ASC';
					}
				}
			}

			self::$whereSql[] = '(' . implode(' OR ', $tempArrayRows) . ')';
		} elseif (self::$modConfig['sort_by_pos_xf'] && self::$modConfig['sort_by_pos_xf'] != '-') {
			$tempQuery = str_replace('%', ' ', self::$query);
			$tempArrayRows = [];

			self::$modConfig['sort_by_pos_xf'] = self::$dleDb->safesql(self::$modConfig['sort_by_pos_xf']);

			$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd} LIKE '%" . self::$query . "%'";
			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd} LIKE '%" . self::$ruQuery . "%'";
				}

				if (self::$enQuery) {
					$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd} LIKE '%" . self::$enQuery . "%'";
				}
			}

			self::$selectSql[] = "LOCATE('{$tempQuery}', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) AS locate_xf";

			if (self::$modConfig['true_locate']) {
				$tempArrayRows[] = "LOCATE('{$tempQuery}', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) > 0";
				self::$orderSql[] = 'locate_xf ASC';
			} else {
				self::$orderSql[] = 'locate_xf < 1 ASC, locate_xf ASC';
			}

			if (self::$modConfig['keyboard']) {
				if (self::$ruQuery) {
					self::$selectSql[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) AS locate_ru_xf";
				}

				if (self::$enQuery) {
					self::$selectSql[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) AS locate_en_xf";
				}

				if (self::$modConfig['true_locate']) {
					if (self::$ruQuery) {
						$tempArrayRows[] = "LOCATE('" . self::$ruQuery . "', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) > 0";
						self::$orderSql[] = 'locate_ru_xf ASC';
					}

					if (self::$enQuery) {
						$tempArrayRows[] = "LOCATE('" . self::$enQuery . "', {$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_by_pos_xf'] . "|', -1), '||', 1){$replaceCharEnd}) > 0";
						self::$orderSql[] = 'locate_en_xf ASC';
					}
				} else {
					if (self::$ruQuery) {
						self::$orderSql[] = 'locate_ru_xf < 1 ASC, locate_ru_xf ASC';
					}

					if (self::$enQuery) {
						self::$orderSql[] = 'locate_en_xf < 1 ASC, locate_en_xf ASC';
					}
				}
			}

			self::$whereSql[] = '(' . implode(' OR ', $tempArrayRows) . ')';
		} else {
			if (!self::$modConfig['rows'] || self::$modConfig['rows'][0] == '-') {
				self::$modConfig['rows'] = ['title'];
			}

			$tempArrayRows = [];

			$tempQuery = self::$query;
			foreach (self::$modConfig['rows'] as $value) {
				if (strpos($value, 'xf_') !== false) {
					$xfName = substr_replace($value, '', 0, 3);
					$xfName = self::$dleDb->safesql($xfName);
					$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '{$xfName}|', -1), '||', 1){$replaceCharEnd} LIKE '%{$tempQuery}%'";
					if (self::$modConfig['keyboard']) {
						if (self::$ruQuery) {
							$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '{$xfName}|', -1), '||', 1){$replaceCharEnd} LIKE '%" . self::$ruQuery . "%'";
						}

						if (self::$enQuery) {
							$tempArrayRows[] = "{$replaceCharStart}SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '{$xfName}|', -1), '||', 1){$replaceCharEnd} LIKE '%" . self::$enQuery . "%'";
						}
					}
				} else {
					$keyName = stripslashes($value);
					$tempArrayRows[] = "{$replaceCharStart}{$keyName}{$replaceCharEnd} LIKE '%{$tempQuery}%'";

					if (self::$modConfig['keyboard']) {
						if (self::$ruQuery) {
							$tempArrayRows[] = "{$replaceCharStart}{$keyName}{$replaceCharEnd} LIKE '%" . self::$ruQuery . "%'";
						}

						if (self::$enQuery) {
							$tempArrayRows[] = "{$replaceCharStart}{$keyName}{$replaceCharEnd} LIKE '%" . self::$enQuery . "%'";
						}
					}
				}
			}

			if ($tempArrayRows) {
				self::$whereSql[] = '(' . implode(' OR ', $tempArrayRows) . ')';
			}
		}

		if (!self::$orderSql) {
			if (!self::$modConfig['sort_field']) {
				self::$modConfig['sort_field'] = 'p.date';
			}
			if (self::$orderByKeys[self::$modConfig['sort_field']]) {
				if (self::$modConfig['sort_field'] == 'p.rating') {
					self::$orderSql[] = (!self::$dleConfig['rating_type'] ? 'CEIL(rating / vote_num)' : 'rating') . ' ' . self::$modConfig['order'];
				} else {
					self::$orderSql[] = self::$orderByKeys[self::$modConfig['sort_field']] . ' ' . self::$modConfig['order'];
				}
			} elseif (trim(self::$modConfig['sort_field']) && self::$modConfig['sort_field'] != '-') {
				self::$modConfig['sort_field'] = self::$dleDb->safesql(self::$modConfig['sort_field']);
				self::$orderSql[] = "ABS(SUBSTRING_INDEX(SUBSTRING_INDEX(p.xfields, '" . self::$modConfig['sort_field'] . "|', -1), '||', 1))" . ' ' . self::$modConfig['order'];
			}
		}

		if (self::$modConfig['excludeNews'] || self::$dleMember['news_hide'] != '') {
			$tempArray = [];
			$configNews = self::$modConfig['excludeNews'] ?: [];
			if (self::$dleMember['news_hide'] != '') {
				$memberHideNews = explode(',', self::$dleMember['news_hide']);
				if (count($memberHideNews) > 0) {
					$configNews = array_merge($configNews, $memberHideNews);
				}
			}

			foreach ($configNews as $value) {
				if (($value = intval($value)) > 0) {
					$tempArray[$value] = $value;
				}
			}

			if ($tempArray) {
				self::$whereSql[] = "p.id NOT IN ('" . implode("','", $tempArray) . "')";
			}

			unset($tempArray);
		}

		if (!self::$modConfig['all_date']) {
			self::$whereSql[] = "date < '" . date ('Y-m-d H:i:s', time()) . "'";
		}

		if (self::$modConfig['allow_main']) {
			self::$whereSql[] = "allow_main";
		}

		$categoryIn = [];
		if (self::$modConfig['in_categories']) {
			foreach (self::$modConfig['in_categories'] as $value) {
				if (($value = intval($value)) > 0) {
					$categoryIn[] = $value;
				}
			}

			if ($categoryIn) {
				if (self::$dleConfig['version_id'] > 13.1) {
					self::$innerTable .= " INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode(",", $categoryIn) . ")) z ON (p.id=z.news_id)";
				} else {
					self::$whereSql[] = "category REGEXP '([[:punct:]]|^)(" . implode('|', $categoryIn) . ")([[:punct:]]|$)'";
				}
			}
		} elseif (self::$modConfig['exclude_categories']) {
			$categoryExclude = [];
			foreach (self::$modConfig['exclude_categories'] as $value) {
				if (($value = intval($value)) > 0) {
					$categoryExclude[] = $value;
				}
			}

			if ($categoryExclude) {
				if (self::$dleConfig['version_id'] > 13.1) {
					self::$innerTable .= " INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id NOT IN (" . implode(",", $categoryExclude) . ")) z ON (p.id=z.news_id)";
				} else {
					self::$whereSql[] = "category NOT REGEXP '([[:punct:]]|^)(" . implode('|', $categoryExclude) . ")([[:punct:]]|$)'";
				}
			}
		}

		if (!self::$dleGroups[self::$dleMember['user_group']]['allow_short'] || self::$modSearch == 'ajax') {
			$notAllowedCat = explode(',', self::$dleGroups[self::$dleMember['user_group']]['not_allow_cats']);

			if ($notAllowedCat[0]) {
				if (self::$dleConfig['allow_multi_category']) {
					if (self::$dleConfig['version_id'] > 13.1) {
						self::$whereSql[] = "p.id NOT IN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode(',', $notAllowedCat) . "))";
					} else {
						self::$whereSql[] = "category NOT REGEXP '([[:punct:]]|^)(" . implode ('|', $notAllowedCat) . ")([[:punct:]]|$)'";
					}
				} else {
					self::$whereSql[] = "category NOT IN ('" . implode("','", $notAllowedCat) . "')";
				}
			}
		}

		self::searchInPage();
	}

	/**
	 * Подсчет новостей
	 *
	 * @return string
	 */
	static function sqlCount()
	{
		$orderSql = $whereSql = $selectSql = '';

		if (count(self::$selectSql)) {
			$selectSql = implode(', ', self::$selectSql) . ',';
		}

		if (count(self::$whereSql)) {
			$whereSql = ' AND ' . implode(' AND ', self::$whereSql);
		}

		if (count(self::$orderSql) && (self::$modConfig['sort_by_pos'] || self::$modConfig['sort_by_pos_xf'] && self::$modConfig['sort_by_pos_xf'] != '-' || self::$modConfig['sort_by_own'])) {
			$orderSql = ' ORDER BY ' . implode(', ', self::$orderSql);
		}

		return "SELECT {$selectSql} COUNT(*) as count FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . self::$innerTable . " WHERE approve " . self::$whereTable . $whereSql . $orderSql;

	}

	/**
	 * Выборка новостей
	 *
	 * @param int $fromNews
	 * @param int $newsPerPage
	 *
	 * @return string
	 */
	static function sqlSelect($fromNews, $newsPerPage)
	{
		$orderSql = $whereSql = $selectSql = '';

		if (count(self::$selectSql)) {
			$selectSql = implode(', ', self::$selectSql) . ',';
		}

		if (count(self::$whereSql)) {
			$whereSql = ' AND ' . implode(' AND ', self::$whereSql);
		}

		if (count(self::$orderSql)) {
			$orderSql = implode(', ', self::$orderSql);
		}

		if (self::$modConfig['fixed']) {
			$orderSql = $orderSql ? 'fixed DESC, ' . $orderSql : 'fixed DESC';
		}

		if (self::$modSearch == 'full' && $fromNews) {
			$fromNews = $fromNews * self::$modConfig['maximum_news_full'];
		}

		$limit = ' LIMIT ' . ((self::$modSearch == 'full') ? $fromNews . ',' . $newsPerPage : $newsPerPage);

		$userSelect = $userJoin = '';
		if (self::$dleConfig['user_in_news']) {
			$userSelect = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields ";
			$userJoin = "LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id) ";
		}

		return "SELECT {$selectSql} p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason{$userSelect} FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . $userJoin . self::$innerTable . " WHERE approve " . self::$whereTable . $whereSql . " ORDER BY " . $orderSql . $limit;
	}

	/**
	 * Запись статистики
	 *
	 * @param    array    $param
	 */
	static function setStatistics($param = [])
	{
		global $_IP, $member_id, $microTimerSearch;

		$dateSearch = date('Y-m-d H:i:s', time());
		$memoryUsage = function_exists('memory_get_peak_usage') ? round(memory_get_peak_usage() / (1024*1024), 2) : 0;
		$userId = $member_id['user_id'] ?: -1;
		$query = self::$dleDb->safesql(self::$originalQuery['query']);
		$searchKeyboard = '';
		if (self::$modConfig['keyboard']) {
			if (self::$originalQuery['ru'] && self::$originalQuery['en']) {
				$searchKeyboard = 'ru###' . self::$dleDb->safesql(self::$originalQuery['ru']) . '###en###' . self::$dleDb->safesql(self::$originalQuery['en']);
			} elseif (self::$originalQuery['ru']) {
				$searchKeyboard = 'ru###' . self::$dleDb->safesql(self::$originalQuery['ru']);
			} elseif (self::$originalQuery['en']) {
				$searchKeyboard = 'en###' . self::$dleDb->safesql(self::$originalQuery['en']);
			}
		}
		$param['statistics'] = self::$dleDb->safesql($param['statistics']);

		$check = self::$dleDb->super_query("SELECT idSearch FROM " . PREFIX . "_dle_search_statistics WHERE DATE(`date`)=DATE(NOW()) AND ip='{$_IP}' AND search='{$query}'");
		if (!$check['idSearch']) {
			$allTime = $microTimerSearch->get();
			self::$dleDb->query("DELETE FROM " . PREFIX . "_dle_search_statistics WHERE '{$query}' LIKE CONCAT('%', search ,'%') AND ip='{$_IP}'");
			self::$dleDb->query("INSERT INTO " . PREFIX . "_dle_search_statistics 
            (`date`, `found`, `search`, `searchKeyboard`, `userId`, `queryNumber`, `ip`, `memoryUsage`, `mysqlTime`, `templateTime`, `allTime`, `sqlQuery`) 
            VALUES 
            ('{$dateSearch}', '{$param['foundNews']}', '{$query}', '{$searchKeyboard}', '{$userId}', '{$param['queryNumber']}', '{$_IP}', '{$memoryUsage}', '{$param['mysqlTime']}', '{$param['templateTime']}', '{$allTime}', '{$param['sqlQuery']}')");
		}
	}

	/**
	 * Добавления тегов вне кэша
	 *
	 * @param   string  $s
	 *
	 * @return string
	 */
	static function tags($s)
	{
		global $tpl;

		$s = preg_replace('#\[dle-search pc\](.*?)\[\/dle-search\]#is', $tpl->desktop ? '\\1' : '', $s);
		$s = preg_replace('#\[not-dle-search pc\](.*?)\[\/not-dle-search\]#is', $tpl->desktop ? '' : '\\1', $s);
		$s = preg_replace('#\[dle-search mobile\](.*?)\[\/dle-search\]#is', ($tpl->smartphone || $tpl->tablet ? '\\1' : ''), $s);
		$s = preg_replace('#\[not-dle-search mobile\](.*?)\[\/not-dle-search\]#is', ($tpl->smartphone || $tpl->tablet ? '' : '\\1'), $s);

		$s = preg_replace('#\[dle-search full\](.*?)\[\/dle-search\]#is', self::$modSearch == 'full' ? '\\1' : '', $s);
		$s = preg_replace('#\[not-dle-search full\](.*?)\[\/not-dle-search\]#is', self::$modSearch == 'full' ? '' : '\\1', $s);
		$s = preg_replace('#\[dle-search ajax\](.*?)\[\/dle-search\]#is', self::$modSearch == 'ajax' ? '\\1' : '', $s);
		$s = preg_replace('#\[not-dle-search ajax\](.*?)\[\/not-dle-search\]#is', self::$modSearch == 'ajax' ? '' : '\\1', $s);

		return $s;
	}
}