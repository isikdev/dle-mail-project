<?php
/**
 * Конфиг и языковый файл
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Seo;

class Data
{
    static private $data = [];

    /**
     * Загрузить конфиг и языковый пакет
     */
    static function load()
    {
		$path = realpath(__DIR__ . '/..');
        self::$data['config'] = include_once $path . '/data/config.php';
		self::$data['config']['lang'] = in_array(self::$data['config']['lang'], ['en', 'ru', 'ua']) ? self::$data['config']['lang'] : 'ru';

		self::$data['lang'] = include_once $path . '/lang/lang_' . self::$data['config']['lang'] . '.lng';
        self::$data['news'] = include_once $path . '/data/news.php';
        self::$data['cat'] = include_once $path . '/data/cats.php';
		self::$data['sitemap'] = include_once $path . '/data/sitemap.php';
    }

    /**
     * Вернуть массив данных
     *
     * @param   string  $key
     * @return  array
     */
    static function receive($key)
	{
        return self::$data[$key] ?: [];
    }

    /**
     * Получить данные с массива по ключу
     *
     * @param    string|array   $key
     * @param    string         $type
     * @return   mixed
     */
    static public function get($key, $type)
    {
        if (is_array($key) && !empty(self::$data[$type])) {
            return Helper::multiArray(self::$data[$type], $key, count($key));
        }
		
		if (!empty(self::$data[$type][$key])) {
			return self::$data[$type][$key];
		}
		
		return false;
    }

}