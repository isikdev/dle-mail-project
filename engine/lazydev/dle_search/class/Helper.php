<?php
/**
* Вспомогательный класс с набором функций
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Search;

setlocale(LC_NUMERIC, 'C');

class Helper
{
    static $modName = 'dle_search';

    /**
    * Склонение слов
    *
    * @param    array    $a    [0 => count, 1 => новост|ь|и|ей]
    * @return   string
    **/
    static function declinationLazy($a = [])
    {
        $a[0] = intval(str_replace(' ', '', strip_tags($a[0])));

        $words = explode('|', trim($a[1]));
        $partsWord = [
            0 => $words[0],
            1 => $words[0],
            2 => $words[0]
        ];

        switch (count($words)) {
            case 2:
                $partsWord[1] .= $words[1];
                $partsWord[2] .= $words[1];
                break;
            case 3:
                $partsWord[1] .= $words[1];
                $partsWord[2] .= $words[2];
                break;
            case 4:
                $partsWord[0] .= $words[1];
                $partsWord[1] .= $words[2];
                $partsWord[2] .= $words[3];
                break;
        }

        return $a[0] % 10 == 1 && $a[0] % 100 != 11 ? $partsWord[0] : ($a[0] % 10 >= 2 && $a[0] % 10 <= 4 && ($a[0] % 100 < 10 || $a[0] % 100 >= 20) ? $partsWord[1] : $partsWord[2]);
    }

    /**
     * Разбор serialize строки
     *
     * @param    string   $data
     * @return   array
     **/
    static function unserializeJs($data)
    {
        $newArray = [];
        if ($data) {
            parse_str($data, $arrayPost);
            $newArray = self::loop($arrayPost);
        }

        return $newArray;
    }

    /**
     * Рекурсия
     *
     * @param   array    $array
     * @return  array
     */
    static function loop($array)
    {
        foreach ($array as $key => $value) {
            if ($key == 'replace_char') continue;
            $array[$key] = is_array($value) ? self::loop($value) : self::typeValue($value);
        }

        return $array;
    }

    /**
    * Типизация данных
    *
    * @param    mixed   $v
    * @return   float|int|string
    **/
    static function typeValue($v)
    {
        if (is_numeric($v)) {
            $v = is_float($v) ? floatval($v) : intval($v);
        } else {
            $v = strip_tags(stripslashes($v));
        }

        return $v;
    }
    
    /**
    * Json для js
    *
    * @param    array   $v
    * @return   string
    **/
    static function json($v)
    {
        return json_encode($v, JSON_UNESCAPED_UNICODE);
    }
    
    /**
    * Получить данные с массива по массиву ключей
    *
    * @param    array   $a
    * @param    array   $k
    * @param    int     $c
    * @return   string
    **/
    static public function multiArray($a, $k, $c)
    {
        return ($c > 1) ? self::multiArray($a[$k[count($k) - $c]], $k, ($c - 1)) : $a[$k[(count($k) - 1)]];
    }

	/**
     * Очистить строку от последнего слэша
     *
     * @param    string   $v
     * @return   string
     **/
    static function cleanSlash($v)
    {
		return substr($v, -1, 1) == '/' ? substr($v, 0, -1) : $v;
    }
	
	/**
     * Получаем ID категории
     *
     * @param    array     $cat_info
	 * @param    string    $category
     * @return   int|bool
     **/
	static function getCategoryId($cat_info, $category)
	{
		foreach ($cat_info as $cats) {
			if ($cats['alt_name'] == $category) {
				return $cats['id'];
			}
		}
		
		return false;
	}

    /**
     * Проверяем протокол сайта
     *
     * @return bool
     */
    static function ssl()
    {
        if (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
            || (isset($_SERVER['CF_VISITOR']) && $_SERVER['CF_VISITOR'] == '{"scheme":"https"}')
            || (isset($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR'] == '{"scheme":"https"}')
        ) {
            return true;
        }

        return false;
    }
}
