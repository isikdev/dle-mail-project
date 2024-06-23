<?php
/**
 * Конфиг и языковый файл
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Search;

class Data
{
    static private $data = [];

    /**
     * Загрузить конфиг и языковый пакет
     */
    static function load()
    {
		$path = realpath(__DIR__ . '/..');
        self::$data['config']  = include $path . '/data/config.php';
		$_COOKIE['lang_dle_search'] = in_array($_COOKIE['lang_dle_search'], ['ua', 'en', 'ru']) ? $_COOKIE['lang_dle_search'] : 'ru';

        self::$data['lang']    = include $path . '/lang/lang_' . $_COOKIE['lang_dle_search'] . '.lng';
        self::$data['replace'] = include $path . '/data/replace.php';
    }

    /**
     * Вернуть массив данных
     *
     * @param   string  $key
     * @return  array
     */
    static function receive($key)
    {
        return self::$data[$key];
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
        if (is_array($key)) {
            return Helper::multiArray(self::$data[$type], $key, count($key));
        }
		
		if (self::$data[$type][$key]) {
			return self::$data[$type][$key];
		}
		
		return false;
    }

}