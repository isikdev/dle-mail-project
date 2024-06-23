<?php
/**
 * Класс для работы с Sitemap
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Seo;

use Melbahja\Seo\Exceptions\SitemapException;
use Melbahja\Seo\Sitemap;

use SimpleXMLElement;

include_once ENGINE_DIR . '/lazydev/dle_seo/class/vendor/autoload.php';

class Map {
	public static $allow_url = '';
	public static $home = '';
	public static $limit = 0;
	public static $news_per_file = 30000;

	public static $sitemap = null;
	private static $db_result = null;
	private static $allow_urls = null;
	private static $instance = null;

	public static $maps = [];

	/**
	 * Конструктор
	 *
	 * @return self
	 */
	static function construct()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Берём данные для создания карт
	 *
	 * @param array $data
	 *
	 * @return array|void
	 */
	static function sitemap_data($data) {
		global $dleSeoLang, $member_id, $config, $db, $user_group;

		$json = [
			'limit' => 0,
			'news' 		=> ['priority' => 0.9, 'change' => 'weekly', 'on' => 1, 'count' => -1],
			'cat' 		=> ['priority' => 0.8, 'change' => 'daily', 'on' => 1],
			'xfield' 	=> ['priority' => 0.7, 'change' => 'daily', 'on' => 0],
			'tag' 		=> ['priority' => 0.7, 'change' => 'daily', 'on' => 0],
			'static' 	=> ['priority' => 0.6, 'change' => 'monthly', 'on' => 0],
			'dlefilter' => ['priority' => 0.7, 'change' => 'weekly', 'on' => 0],
			'dlecollections' => ['priority' => 0.7, 'change' => 'weekly', 'on' => 0]
		];

		$change = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

		if ($data['cron'] == 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		if (isset($_POST['limit']) && $_POST['limit'] > 0) {
			$json['limit'] = intval($_POST['limit']);
		}

		if (isset($_POST['news_priority']) && $_POST['news_priority'] > 0) {
			$json['news']['priority'] = floatval($_POST['news_priority']);
		}

		if (isset($_POST['sitemap_news_changefreq']) && in_array($_POST['sitemap_news_changefreq'], $change)) {
			$json['news']['change'] = stripslashes(strip_tags($_POST['sitemap_news_changefreq']));
		}

		if (isset($_POST['cat_priority']) && $_POST['cat_priority'] > 0) {
			$json['cat']['priority'] = floatval($_POST['cat_priority']);
		}

		if (isset($_POST['sitemap_cat_changefreq']) && in_array($_POST['sitemap_cat_changefreq'], $change)) {
			$json['cat']['change'] = stripslashes(strip_tags($_POST['sitemap_cat_changefreq']));
		}

		if (isset($_POST['turn_on_xfield']) && $_POST['turn_on_xfield'] == 1) {
			$json['xfield']['on'] = 1;
		}

		if (isset($_POST['xfield_priority']) && $_POST['xfield_priority'] > 0) {
			$json['xfield']['priority'] = floatval($_POST['xfield_priority']);
		}

		if (isset($_POST['sitemap_xfield_changefreq']) && in_array($_POST['sitemap_xfield_changefreq'], $change)) {
			$json['xfield']['change'] = stripslashes(strip_tags($_POST['sitemap_xfield_changefreq']));
		}

		if (isset($_POST['turn_on_tags']) && $_POST['turn_on_tags'] == 1) {
			$json['tag']['on'] = 1;
		}

		if (isset($_POST['tag_priority']) && $_POST['tag_priority'] > 0) {
			$json['tag']['priority'] = floatval($_POST['tag_priority']);
		}

		if (isset($_POST['sitemap_tag_changefreq']) && in_array($_POST['sitemap_tag_changefreq'], $change)) {
			$json['tag']['change'] = stripslashes(strip_tags($_POST['sitemap_tag_changefreq']));
		}

		if (isset($_POST['turn_on_static']) && $_POST['turn_on_static'] == 1) {
			$json['static']['on'] = 1;
		}

		if (isset($_POST['stat_priority']) && $_POST['stat_priority'] > 0) {
			$json['static']['priority'] = floatval($_POST['stat_priority']);
		}

		if (isset($_POST['sitemap_stat_changefreq']) && in_array($_POST['sitemap_stat_changefreq'], $change)) {
			$json['static']['change'] = stripslashes(strip_tags($_POST['sitemap_stat_changefreq']));
		}

		if (isset($_POST['turn_on_dlefilter']) && $_POST['turn_on_dlefilter'] == 1) {
			$json['dlefilter']['on'] = 1;
		}

		if (isset($_POST['dlefilter_priority']) && $_POST['dlefilter_priority'] > 0) {
			$json['dlefilter']['priority'] = floatval($_POST['dlefilter_priority']);
		}

		if (isset($_POST['sitemap_dlefilter_changefreq']) && in_array($_POST['sitemap_dlefilter_changefreq'], $change)) {
			$json['dlefilter']['change'] = stripslashes(strip_tags($_POST['sitemap_dlefilter_changefreq']));
		}

		if (isset($_POST['turn_on_dlecollections']) && $_POST['turn_on_dlecollections'] == 1) {
			$json['dlecollections']['on'] = 1;
		}

		if (isset($_POST['dlecollections_priority']) && $_POST['dlecollections_priority'] > 0) {
			$json['dlecollections']['priority'] = floatval($_POST['dlecollections_priority']);
		}

		if (isset($_POST['sitemap_dlecollections_changefreq']) && in_array($_POST['sitemap_dlecollections_changefreq'], $change)) {
			$json['dlecollections']['change'] = stripslashes(strip_tags($_POST['sitemap_dlecollections_changefreq']));
		}

		$handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/sitemap.php', 'w');
		fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
		fwrite($handler, var_export($json, true));
		fwrite($handler, ";\n");
		fclose($handler);


		if ($data['cron'] == 'yes') {
			$sitemapConfig = include ENGINE_DIR . '/lazydev/dle_seo/data/sitemap.php';
			$json = $sitemapConfig;
		}

		$allow_list = explode(',', $user_group[5]['allow_cats']);
		$not_allow_cats = explode(',', $user_group[5]['not_allow_cats']);
		$stop_list = '';
		$cat_join = '';

		if ($allow_list[0] != 'all') {
			if ($config['allow_multi_category']) {
				$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $allow_list ) . ")) c ON (p.id=c.news_id) ";
			} else {
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			}
		}

		if ($not_allow_cats[0] != '') {
			if ($config['allow_multi_category']) {
				$stop_list = "p.id NOT IN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ")) AND ";
			} else {
				$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
			}
		}

		$where_date = '';
		$thisdate = date('Y-m-d H:i:s', time());
		if ($config['no_date'] && !$config['news_future']) {
			$where_date = " AND date < '" . $thisdate . "'";
		}

		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post p {$cat_join}WHERE {$stop_list}approve=1{$where_date}");
		$json['news']['count'] = $row['count'];

		if ($data['cron'] != 'yes') {
			echo Helper::json($json);
		} else {
			return $json;
		}
	}

	/**
	 * Работа с новостями для карты сайта
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_news($data) {
		global $db, $config, $user_group;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		if ($data['cron'] != 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		self::$limit = $data['limit'] > 0 ? $data['limit'] : $data['count'];

		if (self::$limit > self::$news_per_file) {
			$pages_count = @ceil(self::$limit / self::$news_per_file);

			$n = 0;
			if ($data['limit'] > 0) {
				for ($i = 0; $i < $pages_count; $i++) {
					++$n;
					$t = self::$limit - (self::$news_per_file*$i);
					self::get_news($data, $n, $t);
				}
			} else {
				for ($i = 0; $i < $pages_count; $i++) {
					++$n;
					self::get_news($data, $n, false);
				}
			}
		} else {
			self::get_news($data, false, false);
		}

		if ($data['cron'] != 'yes') {
			self::$sitemap->save();
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Создаем карты новостей
	 *
	 * @param array $data
	 * @param bool|int $page
	 */
	static function get_news($data, $page = false, $numeric = false) {
		global $db, $config, $user_group;

		$prefix_page = '';
		$limit = '';
		if ($page && $numeric === false) {
			if ($page != 1) $prefix_page = '_' . $page;

			--$page;
			$page *= self::$news_per_file;
			$limit = " LIMIT {$page}, " . self::$news_per_file;
		} else {
			if ($numeric === false) {
				if (self::$limit > 0) {
					$limit = ' LIMIT 0,' . self::$limit;
				}
			} elseif ($numeric !== false) {
				if ($page != 1) $prefix_page = '_' . $page;

				$page = $page - 1;
				$page = $page * self::$news_per_file;
				$limit = " LIMIT {$page}, " . $numeric;
			}
		}

		$thisdate = date('Y-m-d H:i:s', time());
		$where_date = '';
		if ($config['no_date'] && !$config['news_future']) $where_date = " AND date < '" . $thisdate . "'";

		$allow_list = explode(',', $user_group[5]['allow_cats']);
		$not_allow_cats = explode(',', $user_group[5]['not_allow_cats']);
		$stop_list = '';
		$cat_join = '';

		if ($allow_list[0] != 'all') {
			if ($config['allow_multi_category']) {
				$cat_join = " INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $allow_list ) . ")) c ON (p.id=c.news_id) ";
			} else {
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			}
		}

		if ($not_allow_cats[0] != '') {
			if ($config['allow_multi_category']) {
				$stop_list = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") ) AND ";
			} else {
				$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
			}
		}

		self::$db_result = $db->query("SELECT p.id, p.title, p.date, p.alt_name, p.category, e.access, e.editdate, e.disable_index, e.need_pass FROM " . PREFIX . "_post p {$cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1" . $where_date . " ORDER BY date DESC" . $limit);
		self::$maps[] = 'news' . $prefix_page . '.xml';

		self::$sitemap->links('news' . $prefix_page . '.xml', function($map) use($data, $config, $db, $user_group) {
			while ($row = $db->get_row(self::$db_result)) {
				$row['date'] = strtotime($row['date']);

				$row['category'] = intval($row['category']);

				if ($row['disable_index']) continue;

				if ($row['need_pass']) continue;

				if (strpos($row['access'], '5:3') !== false) continue;

				if (self::$allow_urls) {
					if ($config['seo_type'] == 1 || $config['seo_type'] == 2) {
						if ($row['category'] && $config['seo_type'] == 2) {
							$cats_url = get_url($row['category']);
							if ($cats_url) {
								$loc = $cats_url . '/' . $row['id'] . '-' . $row['alt_name'] . '.html';
							} else {
								$loc = $row['id'] . '-' . $row['alt_name'] . '.html';
							}
						} else {
							$loc = $row['id'] . '-' . $row['alt_name'] . '.html';
						}
					} else {
						$loc = date('Y/m/d/', $row['date']) . $row['alt_name'] . '.html';
					}
				} else {
					$loc = 'index.php?newsid=' . $row['id'];
				}

				if ($row['editdate'] && $row['editdate'] > $row['date']) {
					$row['date'] = $row['editdate'];
				}

				$map->loc($loc)->freq($data['change'])->lastMod(date('c', $row['date']))->priority($data['priority']);
			}
		});
	}

	/**
	 * Создаем карты категорий
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_cat($data) {
		global $db, $config, $user_group, $cat_info;

		if (!count($cat_info)) {
			echo Helper::json(['ok' => 'ok']);
			return;
		}

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		if ($data['cron'] != 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		self::$maps[] = 'category.xml';
		self::$sitemap->links('category.xml', function($map) use ($cat_info, $user_group, $data) {
			$allow_list = explode(',', $user_group[5]['allow_cats']);
			$not_allow_cats = explode(',', $user_group[5]['not_allow_cats']);

			foreach ($cat_info as $cats) {
				if ($allow_list[0] != 'all') {
					if (!$user_group[5]['allow_short'] && !in_array($cats['id'], $allow_list)) continue;
				}

				if ($not_allow_cats[0] != '') {
					if (!$user_group[5]['allow_short'] && in_array($cats['id'], $not_allow_cats)) continue;
				}

				if (self::$allow_urls) {
					$loc = self::get_url($cats['id'], $cat_info) . '/';
				} else {
					$loc = 'index.php?do=cat&category=' . $cats['alt_name'];
				}

				$map->loc($loc)->freq($data['change'])->lastMod(date('c'))->priority($data['priority']);
			}
		});



		if ($data['cron'] != 'yes') {
			self::$sitemap->save();
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Берём ссылку на категорию
	 *
	 * @param int $id
	 * @param array $cat_info
	 */
	static function get_url($id, $cat_info) {
		if (!$id) return;

		$parent_id = $cat_info[$id]['parentid'];
		$url = $cat_info[$id]['alt_name'];

		while ($parent_id) {
			$url = $cat_info[$parent_id]['alt_name'] . '/' . $url;
			$parent_id = $cat_info[$parent_id]['parentid'];
			if (isset($cat_info[$parent_id]['parentid']) && $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id']) break;
		}

		return $url;
	}

	/**
	 * Работа с дополнительными полями для карты сайта
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_xfield($data) {
		global $db, $config;

		$saveMap = false;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		$checkAllXfields = $db->super_query("SELECT value FROM " . PREFIX . "_dle_seo_value WHERE type=2 AND value='_all_' LIMIT 1")['value'];
		if ($checkAllXfields) {
			if ($data['cron'] != 'yes') {
				self::$sitemap = new Sitemap($config['http_home_url']);
				self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
				self::$allow_urls = $config['allow_alt_url'];

				if (self::$allow_urls) {
					self::$sitemap->setSitemapsUrl($config['http_home_url']);
				} else {
					self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
				}

				self::$sitemap->setIndexName('sitemap.xml');
			}

			$saveMap = true;
			$countAllXfields = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_seo_value v LEFT JOIN " . PREFIX . "_xfsearch x ON(v.xfieldName=x.tagname) WHERE type=2 AND value='_all_' GROUP BY tagvalue")['count'];

			$count_pages = @ceil($countAllXfields / self::$news_per_file);

			$checkSoloXfields = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_seo_value WHERE type=2 AND value!='_all_'")['count'];
			if ($checkSoloXfields > 0) {
				self::getSoloXfields($data);
			}

			for ($i = 0; $i < $count_pages; $i++) {
				self::getAllXfields($data, $i+1);
			}
		} else {
			$checkSoloXfields = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_seo_value WHERE type=2 AND value!='_all_'")['count'];
			if ($checkSoloXfields > 0) {
				if ($data['cron'] != 'yes') {
					self::$sitemap = new Sitemap($config['http_home_url']);
					self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
					self::$allow_urls = $config['allow_alt_url'];

					if (self::$allow_urls) {
						self::$sitemap->setSitemapsUrl($config['http_home_url']);
					} else {
						self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
					}

					self::$sitemap->setIndexName('sitemap.xml');
				}

				$saveMap = true;
				self::getSoloXfields($data);
			}
		}

		if ($data['cron'] != 'yes') {
			if ($saveMap) {
				self::$sitemap->save();
			}
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Создаем карты дополнительных полей со значениям Для всех
	 *
	 * @param array $data
	 * @param bool|int $page
	 */
	static function getAllXfields($data, $page) {
		global $db, $config, $user_group;

		$prefix_page = '';
		$limit = '';
		if ($page) {
			$prefix_page = '_' . $page;

			--$page;
			$page *= self::$news_per_file;
			$limit = " LIMIT {$page}, " . self::$news_per_file;
		}

		self::$db_result = $db->query( "SELECT tagvalue, tagname FROM " . PREFIX . "_dle_seo_value v LEFT JOIN " . PREFIX . "_xfsearch x ON(v.xfieldName=x.tagname) WHERE type=2 AND value='_all_' GROUP BY tagvalue {$limit}");

		self::$maps[] = 'xfsearch' . $prefix_page . '.xml';

		self::$sitemap->links('xfsearch' . $prefix_page . '.xml', function($map) use($data, $config, $db, $user_group) {
			while ($row = $db->get_row(self::$db_result)) {
				self::xfieldProcessing($row['tagvalue'], $config['version_id'], $map, $data, $row['tagname']);
			}
		});
	}

	/**
	 * Создаем карту дополнительных полей с единичными значениями
	 *
	 * @param array $data
	 */
	static function getSoloXfields($data) {
		global $db, $config;

		self::$db_result = $db->query("SELECT value, xfieldName FROM " . PREFIX . "_dle_seo_value WHERE type=2 AND value!='_all_'");

		self::$maps[] = 'xfsearch.xml';
		self::$sitemap->links('xfsearch.xml', function($map) use ($data, $config, $db) {
			while ($row = $db->get_row(self::$db_result)) {
				self::xfieldProcessing($row['value'], $config['version_id'], $map, $data, $row['xfieldName']);
			}
		});

		self::$db_result = null;
	}

	/**
	 * Работа с тегами для карты сайта
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_tags($data) {
		global $db, $config;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
		$saveMap = false;
		$checkAllTags = $db->super_query("SELECT value FROM " . PREFIX . "_dle_seo_value WHERE type=1 AND value='_all_' LIMIT 1")['value'];
		if ($checkAllTags) {
			if ($data['cron'] != 'yes') {
				self::$sitemap = new Sitemap($config['http_home_url']);
				self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
				self::$allow_urls = $config['allow_alt_url'];

				if (self::$allow_urls) {
					self::$sitemap->setSitemapsUrl($config['http_home_url']);
				} else {
					self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
				}

				self::$sitemap->setIndexName('sitemap.xml');
			}

			$saveMap = true;

			$countTags = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_tags GROUP BY tag")['count'];
			$pages_tag = @ceil($countTags / self::$news_per_file);
			for ($i = 0; $i < $pages_tag; $i++) {
				self::getAllTags($data, $i+1);
			}
		} else {
			$checkTags = $db->super_query("SELECT value FROM " . PREFIX . "_dle_seo_value WHERE type = 1 AND value!='_all_' LIMIT 1")['value'];
			if ($checkTags) {
				if ($data['cron'] != 'yes') {
					self::$sitemap = new Sitemap($config['http_home_url']);
					self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
					self::$allow_urls = $config['allow_alt_url'];

					if (self::$allow_urls) {
						self::$sitemap->setSitemapsUrl($config['http_home_url']);
					} else {
						self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
					}

					self::$sitemap->setIndexName('sitemap.xml');
				}

				$saveMap = true;

				self::getSoloTags($data);
			}
		}



		if ($data['cron'] != 'yes') {
			if ($saveMap) {
				self::$sitemap->save();
			}
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Создаем карты тегов
	 *
	 * @param array $data
	 * @param bool|int $page
	 */
	static function getAllTags($data, $page) {
		global $db, $config, $user_group;

		$prefix_page = '';
		$limit = '';
		if ($page) {
			$prefix_page = '_' . $page;

			--$page;
			$page *= self::$news_per_file;
			$limit = " LIMIT {$page}, " . self::$news_per_file;
		}

		self::$db_result = $db->query("SELECT tag FROM " . PREFIX . "_tags GROUP BY tag {$limit}");

		self::$maps[] = 'tags' . $prefix_page . '.xml';

		self::$sitemap->links('tags' . $prefix_page . '.xml', function($map) use($data, $config, $db, $user_group) {
			while ($row = $db->get_row(self::$db_result)) {
				self::tagProcessing($row['tag'], $config['version_id'], $map, $data);
			}
		});
	}

	/**
	 * Создаем карты тегов с единичными значениями
	 *
	 * @param array $data
	 */
	static function getSoloTags($data) {
		global $db, $config, $user_group;

		self::$db_result = $db->query("SELECT value FROM " . PREFIX . "_dle_seo_value WHERE type = 1 AND value!='_all_'");
		self::$maps[] = 'tags.xml';
		self::$sitemap->links('tags.xml', function($map) use($data, $config, $db, $user_group) {
			while ($row = $db->get_row(self::$db_result)) {
				self::tagProcessing($row['value'], $config['version_id'], $map, $data);
			}
		});
	}

	/**
	 * Создаем карту статических страниц
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_static($data) {
		global $db, $config;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE !disable_index AND sitemap AND !password AND name!='dle-rules-page'");

		if (!$result_count['count']) {
			echo Helper::json(['ok']);
			return;
		}

		if ($data['cron'] != 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		self::$db_result = $db->query( "SELECT name FROM " . PREFIX . "_static WHERE !disable_index AND sitemap AND !password AND name!='dle-rules-page'");
		self::$maps[] = 'static.xml';
		self::$sitemap->links('static.xml', function($map) use($db, $data) {
			while ($row = $db->get_row(self::$db_result)) {
				$loc = self::$allow_urls ? $row['name'] . '.html' : 'index.php?do=static&page=' . $row['name'];
				$map->loc($loc)->freq($data['change'])->lastMod(date('c'))->priority($data['priority']);
			}
		});

		if ($data['cron'] != 'yes') {
			self::$sitemap->save();
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Создаем карту дополнительных страниц DLE Filter
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_dlefilter($data) {
		global $db, $config;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_filter_pages WHERE sitemap AND approve");

		if (!$result_count['count']) {
			echo Helper::json(['ok']);
			return;
		}

		if ($data['cron'] != 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		self::$db_result = $db->query("SELECT page_url, dateEdit, date FROM " . PREFIX . "_dle_filter_pages WHERE sitemap AND approve");
		self::$maps[] = 'filter.xml';
		self::$sitemap->links('filter.xml', function($map) use($db, $data) {
			while ($row = $db->get_row(self::$db_result)) {
				$loc = $row['page_url'] . '/';
				$date = $row['dateEdit'] ? $row['dateEdit'] : ($row['date'] ?: date('c'));
				$date = date('c', strtotime($date));

				$map->loc($loc)->freq($data['change'])->lastMod($date)->priority($data['priority']);
			}
		});

		if ($data['cron'] != 'yes') {
			self::$sitemap->save();
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Создаем карту подборок DLE Collections
	 *
	 * @param array $data
	 * @throws SitemapException
	 */
	static function sitemap_dlecollections($data) {
		global $db, $config;

		if (strpos($config['http_home_url'], '//') === 0) $config['http_home_url'] = 'https:' . $config['http_home_url'];
		elseif (strpos($config['http_home_url'], '/') === 0) $config['http_home_url'] = 'https://' . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

		$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_collections WHERE approve");

		if (!$result_count['count']) {
			echo Helper::json(['ok']);
			return;
		}

		if ($data['cron'] != 'yes') {
			self::$sitemap = new Sitemap($config['http_home_url']);
			self::$sitemap->setSavePath(ROOT_DIR . '/uploads');
			self::$allow_urls = $config['allow_alt_url'];

			if (self::$allow_urls) {
				self::$sitemap->setSitemapsUrl($config['http_home_url']);
			} else {
				self::$sitemap->setSitemapsUrl($config['http_home_url'] . 'uploads');
			}

			self::$sitemap->setIndexName('sitemap.xml');
		}

		$collectionConfig = include ENGINE_DIR . '/lazydev/dle_collections/data/config.php';
		self::$db_result = $db->query("SELECT altName, editDate, date FROM " . PREFIX . "_dle_collections WHERE approve");
		self::$maps[] = 'collections.xml';
		self::$sitemap->links('collections.xml', function($map) use($db, $data, $collectionConfig) {
			while ($row = $db->get_row(self::$db_result)) {
				$loc = $collectionConfig['url'] . '/' . $row['altName'] . '/';
				$date = $row['editDate'] && $row['editDate'] != '0000-00-00 00:00:00' ? $row['editDate'] : ($row['date'] ?: date('c'));
				$date = date('c', strtotime($date));

				$map->loc($loc)->freq($data['change'])->lastMod($date)->priority($data['priority']);
			}
		});


		if ($data['cron'] != 'yes') {
			self::$sitemap->save();
			echo Helper::json(self::$maps);
		}
	}

	/**
	 * Сохраняем все данные в главную карту
	 *
	 * @param array $data
	 */
	static function sitemap_save($data)
	{
		global $config;

		if ($data['cron'] == 'yes') {
			self::$sitemap->save();
		} else {
			$url = $config['allow_alt_url'] ? $config['http_home_url'] : $config['http_home_url'] . 'uploads/';
			$dom = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

			foreach ($data as $key => $file) {
				if (strpos($key, 'map_') !== false) {
					$tempFiles = explode(',', $file);
					foreach ($tempFiles as $value) {
						$sitemap = $dom->addChild('sitemap');
						$sitemap->addChild('loc', $url . trim($value));
						$sitemap->addChild('lastmod', date('c'));
					}
				}
			}

			$tempData = $dom->asXML();

			$handler = fopen(ROOT_DIR . '/uploads/sitemap.xml', 'w');
			fwrite($handler, $tempData);
			fclose($handler);
		}

		if ($data['cron'] != 'yes') {
			echo Helper::json(['ok']);
		}
	}

	/**
	 * @param string	$v
	 * @param float		$version_id
	 * @param			$map
	 * @param array		$data
	 *
	 * @return void
	 */
	public static function tagProcessing($v, $version_id, $map, $data)
	{
		$v = str_replace(["&#039;", "&quot;", "&amp;"], ["'", '"', "&"], trim($v));

		if (Data::get('tags_alt', 'config')) {
			$v = totranslit($v, true, false);
		}

		if ($version_id >= 15.2) {
			$v = dle_strtolower($v);
		}

		$v = $version_id > 13.1 ? rawurlencode($v) : urlencode($v);

		$loc = self::$allow_urls ? 'tags/' . $v . '/' : '?do=tags&amp;tag=' . $v;

		$map->loc($loc)->freq($data['change'])->lastMod(date('c'))->priority($data['priority']);
	}

	/**
	 * @param string	$v
	 * @param float		$version_id
	 * @param			$map
	 * @param array		$data
	 * @param string	$name
	 *
	 * @return void
	 */
	public static function xfieldProcessing($v, $version_id, $map, $data, $name)
	{
		$v = str_replace(["&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;"], ["'", '"', "&", "{", "[", ":"], trim($v));

		if (Data::get('xfield_alt', 'config')) {
			$v = totranslit($v, true, false);
		}

		if ($version_id >= 15.2) {
			$v = dle_strtolower($v);
		}

		$v = $version_id > 13.1 ? rawurlencode($v) : urlencode($v);

		$loc = self::$allow_urls ? 'xfsearch/' . $name . '/' . $v . '/' : '?do=xfsearch&amp;xfname=' . $name . '&amp;xf=' . $v;

		$map->loc($loc)->freq($data['change'])->lastMod(date('c'))->priority($data['priority']);
	}
}