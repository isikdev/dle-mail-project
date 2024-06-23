<?php
/**
* Вспомогательный класс с набором функций
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Seo;

class Helper
{
    static $modName = 'dle_seo';

    /**
    * Разбор serialize строки
    *
    * @param    string   $data_form
    * @return   array
    **/
	static function unserializeJs($data_form)
	{
		$new_array = [];
		if ($data_form) {
			parse_str($data_form, $array_post);
			$new_array = self::loop($array_post);
		}

		return $new_array;
	}

	/**
	 *
	 *
	 * @param    array   $array
	 * @return   array
	 **/
	static function loop($array) {
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = self::loop($array[$key]);
			}

			if (!is_array($value)) {
				$array[$key] = self::typeValue($value);
			}
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
        if ($v[strlen($v) - 1] === '/') {
            $v = substr($v, 0, -1);
        }

        return $v;
    }

    /**
     * Тип доп полей
     *
     * @param    array   $arr
     * @return   string
     **/
    static function stringData($arr)
    {
        global $db;
        $arrTemp = [];
        foreach ($arr as $xfielddataname => $xfielddatavalue) {
            if ($xfielddataname == '' || $xfielddatavalue == '') {
                continue;
            }

            $xfielddataname = str_replace(['|', '\r\n'], ['&#124;', '__NEWL__'], $xfielddataname);
            $xfielddatavalue = str_replace(['|', '\r\n'], ['&#124;', '__NEWL__'], $xfielddatavalue);
            $arrTemp[] = $xfielddataname . '|' . $xfielddatavalue;
        }

		return count($arrTemp) ? $db->safesql(implode('||', $arrTemp)) : '';
    }

    /**
     * Тип доп полей
     *
     * @param    mixed   $data
     * @return   string
     **/
    static function checkPopupData($data)
    {
        return $data ?: '<i class="fa fa-close" style="color: red;"></i>';
    }

    /**
     * Обнуление пути
     *
     * @param    string   $url
     * @return   string
     **/
    static function resetUrl($url)
    {
        $url = (string)$url;
        $value = str_replace(["http://", "https://", "www."], '', $url);
        $value = explode('/', $value);
        $value = reset($value);
        return $value;
    }

    /**
     * Очистка пути
     *
     * @param    string   $var
     * @return   string
     **/
    static function cleanDir($var)
    {
        $var = (string)$var;
        $var = str_ireplace('.php', '', $var);
        $var = str_ireplace('.php', '.ppp', $var);
        $var = trim(strip_tags($var));
        $var = str_replace("\\", '/', $var);
        $var = preg_replace("/[^a-z0-9\/\_\-]+/mi", '', $var);
        return $var;
    }

    /**
     * Обработка данных
     *
     * @param   string  $v
     * @return  string
     **/
    static function extString($v)
    {
        global $config;

        $q = ["\x22", "\x60", "\t", "\n", "\r", '"', '\r', '\n', "$", "\\"];

        $v = trim(htmlspecialchars(strip_tags(stripslashes($v)), ENT_COMPAT, $config['charset']));
        $v = preg_replace('/\s+/u', ' ', str_replace($q, '', $v));

        return $v;
    }

    /**
     * @param string $value
     * @param int $length
     *
     * @return string
     *
     **/
    static function cutString($value, $length)
    {
        $value = str_replace('><', '> <', $value);
        $value = strip_tags($value, "<br>");
        $value = trim(str_replace(["<br>", "<br />", "\n"], ' ', str_replace("\r", '', $value)));
        $value = preg_replace('/\s+/u', ' ', $value);

        if ($length && dle_strlen($value, 'UTF-8') > $length) {
            $value = dle_substr($value, 0, $length, 'UTF-8');

            if (($temp_dmax = dle_strrpos($value, ' ', 'UTF-8'))) {
                $value = dle_substr($value, 0, $temp_dmax, 'UTF-8');
            }
        }

        return $value;
    }

	/**
	 * @param string $haystack
	 * @param array $needles
	 * @param int	$offset
	 *
	 * @return bool
	 *
	 **/
	static function strposa($haystack, $needles, $offset = 0)
	{
		foreach ($needles as $needle) {
			if (strpos($haystack, $needle, $offset) !== false) {
				return true;
			}
		}

		return false;
	}
}
