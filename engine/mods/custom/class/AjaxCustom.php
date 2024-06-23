<?php

namespace Sandev;

use dle_template;
use Sandev\AjaxCustom\Blocks;

class AjaxCustom
{

	/** @var object Модель работы с блоками */
	private static $data;

	/**
	 * Инициализация класса
	 */
	public static function init()
	{
		static::$data = new Blocks(ENGINE_DIR . '/mods/custom/');
	}

	/**
	 * Обработчик тега {ajaxCustom.xxx} через preg_replace_callback
	 * @param  array  $match 
	 */
	public static function parseTag(array $match): string
	{
		return static::print($match[1]);
	}

	/**
	 * Обработчик строкового подключения тега {ajaxCustom ...}
	 * @param  array  $match перечень параметров
	 */
	public static function parseCustom(array $match): string
	{
		$paramStr = trim($match[1]);
		if ($name = static::$data->initBlock($match[1])) {
			return static::getPageInitContent($name);
		}
		return 'Module not activated';
	}

	/**
	 * Загрузить данные строкового блока из кеша
	 * @param  string $name сгенерированный id блока - cm__{hash}
	 */
	public static function loadCacheData(string $name): void
	{
		static::$data->loadCacheData($name);
	}

	/**
	 * Формирование блока custom-а
	 * @param  string       $name   имя группы
	 */
	public static function print(string $name): string
	{
		global $config;
		$name = totranslit($name);
		if (!static::$data->isset($name)) return 'Block config not found: ' . $name;

		$data = static::$data->getItem($name);
		if (!$data['active']) return 'Block disabled: ' . $name;

		if ($data['lazyLoad'] && $config['image_lazy']) {
			return sprintf('<div data-custom="%s"></div>', $name, $name);
		}

		if ($data['eventLoad']) {
			return sprintf('<div id="ajaxCustom-holder-%s"></div>', $name);
		}

		$content = static::getPageInitContent($name);
		return $content;
	}

	/**
	 * Формирование шаблона блока постраничной навигации
	 * @param  string      $name        имя блока
	 * @param  int         $pages_count количество страниц
	 * @param  int|integer $cstart      номер текущей страницы
	 */
	public static function getNavigation(string $name, int $pages_count, int $cstart = 1): string
	{
		$data = static::$data->getItem($name);
		if ($data['nav_type'] == 'none') return '';
		$pagination = new Pagination($pages_count, $cstart);
		return $pagination->buildHtml($data['nav_type'], $name);
	}

	/**
	 * Первичная инициализация блока, формирование JS оболочки
	 * @param  string $name имя блока
	 */
	public static function getPageInitContent(string $name): string
	{
		$content = static::getPageContent($name);
		$content = sprintf('<div id="ajaxCustom-%s">%s</div>', $name, $content);
		return $content;
	}

	/**
	 * Получить контент блока
	 * @param  string       $name     имя блока
	 * @param  int          $cstart   номер страницы навигации
	 * @return string                 сформированный html
	 */
	public static function getPageContent(string $name, int $cstart = 0): string
	{
		global $config, $member_id;
		$name = totranslit($name);
		if (!static::$data->isset($name)) return 'Block config not found: ' . $name;
		
		$data = static::$data->getItem($name);
		if (!$data['active']) return 'Block disabled: ' . $name;

		in_array($data['nav_type'], ['main', 'pages', 'next']) || $data['cookies'] = false;
		
		if ($data['cookies'] && $cstart === 0 && isset($_COOKIE['ajcm_' . $name])) {
			$cstart = (int)$_COOKIE['ajcm_' . $name];
		}

		$cstart < 1 && $cstart = 1;
		if ($data['max_page'] && $cstart > $data['max_page']) {
			$cstart = $data['max_page'];
		}

		$data['cookies'] && set_cookie("ajcm_" . $name, $cstart, 365);

		//для кеша
		$data['cstart'] = $cstart;
		$data['user_group'] = (int)$member_id['user_group'];

		$allow_cache = false;
		if (($config['allow_cache'] || $data['cache'])
			&& $data['cache'] !== false
			&& $config['max_cache_pages'] >= $cstart
		) {
			$allow_cache = true;
			$cache = static::getCache($name, $data);
			if ($cache !== false) {
				return $cache;
			}
		}

		$from = ($cstart - 1) * $data['limit'];
		if (preg_match('#from=["\'](\d+)["\']#i', $data['params'], $match)) {
			$from += (int)$match[1];
		}

		$paramStr = join(' ', [
			sprintf('from="%d"', $from),
			sprintf('limit="%d"', $data['limit']),
			'navigation="ajax"',
			'cache="no"',
			$data['params'],
		]);

		$tpl = new dle_template();
		$tpl->dir = TEMPLATE_DIR;

		if ($data['template']) {
			$tpl_name = totranslit($data['template'], true, false);
			if (!file_exists($tpl->dir . '/custom/' . $tpl_name . '.tpl')) {
				return 'Custom template not found: ' . $tpl_name . '.tpl';
			}
		} else {
			$tpl_name = 'main';
		}

		$custom = custom_print(['', $paramStr]);

		static::parseFavorites($custom['content']);

		$tpl->load_template('custom/' . $tpl_name . '.tpl');
		$tpl->set('{custom.name}', $name);
		$tpl->set('{custom.nav_type}', $data['nav_type']);
		$tpl->set('{content}', $custom['content']);

		$tpl->set('{navigation}', static::getNavigation(
			$name,
			static::getPagesCount($name, $custom['count_sql']),
			$cstart
		));
		$tpl->compile('content');

		$allow_cache && static::setCache($name, $tpl->result['content'], $data);

		return $tpl->result['content'];
	}

	/**
	 * Получить кеш
	 * @param  string $name имя группы
	 * @return bool|string  false - если нет кеша, string - содержимое кеша
	 */
	private static function getCache(string $name, array $data = [])
	{
		global $mcache;
		$key = 'news_custom_' . $name . '_' . md5(json_encode($data));
		if ($mcache !== false) {
			if ($mcache->connection > 0) {
				return $mcache->get($key);
			}
		}

		$cache_path = ENGINE_DIR . '/cache/' . $key . '.tmp';
		if (!file_exists($cache_path)) return false;
		return file_get_contents($cache_path);
	}

	/**
	 * Сохранить кеш в файл
	 * @param string $name    имя группы
	 * @param string $content содержимое кеша
	 */
	private static function setCache(string $name, string $content, array $data): void
	{
		global $mcache;

		$key = 'news_custom_' . $name . '_' . md5(json_encode($data));
		if ($mcache !== false) {
			$mcache->connection > 0 && $mcache->set($key, (string)$content);
		} else {
			file_put_contents(ENGINE_DIR . '/cache/' . $key . '.tmp', $content, LOCK_EX);
		}
	}


	private static function getPagesCount(string $name, string $count_sql): int
	{
		global $db;
		$data = static::$data->getItem($name);
		if ($data['nav_type'] == 'none') return 1;
		$pages_count = static::getCountCache();
		if (!isset($pages_count[$name])) {
			$count_all = $db->super_query($count_sql);
			$pages_count[$name] = ceil($count_all['count'] / $data['limit']);
			if ($data['max_page']
				&& $data['max_page'] < $pages_count[$name]
			) {
				$pages_count[$name] = $data['max_page'];
			}
			static::setCountCache($pages_count);
		}
		return (int)$pages_count[$name];
	}

	private static function getCountCache(): array
	{
		global $mcache;
		$path = ENGINE_DIR . '/cache/news_custom_count.tmp';

		if ($mcache !== false) {
			$mcache->connection > 0 && $content = $mcache->get('news_custom_count');
		} elseif (file_exists($path)) {
			$content = file_get_contents($path);
		}
		$data = $content ? json_decode($content, true) : [];
		return $data;
	}

	private static function setCountCache(array $data): void
	{
		global $mcache;
		$content = json_encode($data, JSON_UNESCAPED_SLASHES);
		if ($mcache !== false) {
			$mcache->connection > 0 && $mcache->set('news_custom_count', $content);
		} else {
			$path = ENGINE_DIR . '/cache/news_custom_count.tmp';
			file_put_contents($path, $content, LOCK_EX);
		}
	}

	private static function parseFavorites(string &$content): void
	{
		global $is_logged, $member_id, $PHP_SELF, $config;
		if ($is_logged && stripos($content, "-favorites-" ) !== false) {
			$fav_arr = explode(',', $member_id['favorites']);
			foreach ($fav_arr as $fav_id) {
				$content = str_replace ( "{-favorites-{$fav_id}}", "<a id=\"fav-id-{$fav_id}\" class=\"favorite-link del-favorite\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=del&amp;id={$fav_id}\"><img src=\"{$config['http_home_url']}templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('{$fav_id}', 'minus', 0); return false;\" title=\"{$lang['news_minfav']}\" alt=\"\"></a>", $content );
				$content = str_replace ( "[del-favorites-{$fav_id}]", "<a id=\"fav-id-{$fav_id}\" onclick=\"doFavorites('{$fav_id}', 'minus', 1); return false;\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=del&amp;id={$fav_id}\">", $content );
				$content = str_replace ( "[/del-favorites-{$fav_id}]", "</a>", $content );
				$content = preg_replace( "'\\[add-favorites-{$fav_id}\\](.*?)\\[/add-favorites-{$fav_id}\\]'is", "", $content );
			}
			
			$content = preg_replace( "'\\{-favorites-(\d+)\\}'i", "<a id=\"fav-id-\\1\" class=\"favorite-link add-favorite\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=add&amp;id=\\1\"><img src=\"{$config['http_home_url']}templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('\\1', 'plus', 0); return false;\" title=\"{$lang['news_addfav']}\" alt=\"\"></a>", $content );
			$content = preg_replace( "'\\[add-favorites-(\d+)\\]'i", "<a id=\"fav-id-\\1\" onclick=\"doFavorites('\\1', 'plus', 1); return false;\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=add&amp;id=\\1\">", $content );
			$content = preg_replace( "'\\[/add-favorites-(\d+)\\]'i", "</a>", $content );
			$content = preg_replace( "'\\[del-favorites-(\d+)\\](.*?)\\[/del-favorites-(\d+)\\]'si", "", $content );
		}
	}
}
