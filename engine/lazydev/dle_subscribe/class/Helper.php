<?php
/**
* Вспомогательный класс с набором функций
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Subscribe;

class Helper
{
    static $modName = 'dle_subscribe';

    /**
     * Получаем URL подписки
     *
     * @param    string    $page
     * @param    string|array    $helper
     * @return   array
     **/
    static function getUrl($page, $helper)
    {
        switch ($page) {
            case 'news':
                return self::urlNews($helper);
                break;
            case 'cat':
                return self::urlCat($helper);
                break;
            case 'xfield':
                return self::urlXfield($helper);
                break;
            case 'tag':
                return self::urlTag($helper);
                break;
            case 'user':
                return self::urlUser($helper);
                break;
        }
    }

    /**
     * Получаем URL подписки на пользователя
     *
     * @param    string    $helper
     * @return   array
     **/
    static function urlUser($helper)
    {
        global $config;

        $helper = xfieldsdataload($helper);
		$helper['name'] = urlencode($helper['name']);

        if ($config['allow_alt_url']) {
            $fullLink = $config['http_home_url'] . 'user/' . $helper['name'] . '/';
        } else {
            $fullLink = $config['http_home_url'] . 'index.php?subaction=userinfo&user=' . $helper['name'];
        }

        return [$fullLink, stripslashes($helper['name'])];
    }

    /**
     * Получаем URL подписки на тег
     *
     * @param    string    $helper
     * @return   array
     **/
    static function urlTag($helper) {
        global $config;

        $helper = xfieldsdataload($helper);
		$helper[0] = $config['version_id'] >= 13.2 ? rawurlencode($helper[0]) : urlencode($helper[0]);

        if ($config['allow_alt_url']) {
            $fullLink = $config['http_home_url'] . 'tags/' . $helper[0] . '/';
        } else {
            $fullLink = $config['http_home_url'] . 'index.php?do=tags&tag=' . $helper[0];
        }

        return [$fullLink, stripslashes($helper[0])];
    }

    /**
     * Получаем URL подписки на дополнительное поле
     *
     * @param    string    $helper
     * @return   array
     **/
    static function urlXfield($helper)
    {
        global $config;
        $helper = xfieldsdataload($helper);
		$helper[1] = $config['version_id'] >= 13.2 ? rawurlencode($helper[1]) : urlencode($helper[1]);

        $fullLink = implode('/', $helper);

        if ($config['allow_alt_url']) {
            $fullLink = $config['http_home_url'] . $fullLink . '/';
        } else {
            $fullLink = $config['http_home_url'] . 'index.php?do=xfsearch&xf=' . $fullLink;
        }

        return [$fullLink, stripslashes($helper[1])];
    }

    /**
     * Получаем URL подписки на категорию
     *
     * @param    array    $helper
     * @return   array
     **/
    static function urlCat($helper)
    {
        global $config, $cat_info;

        if ($config['allow_alt_url']) {
            $fullLink = $config['http_home_url'] . get_url($helper['catid']) . '/';
        } else {
            $fullLink = $config['http_home_url'] . 'index.php?do=cat&category=' . $helper['catid'];
        }

        return [$fullLink, stripslashes($cat_info[$helper['catid']]['name'])];
    }

    /**
     * Получаем URL подписки на новость
     *
     * @param    array    $helper
     * @return   array
     **/
    static function urlNews($helper) {
        global $config;

        if ($config['allow_alt_url']) {
            if ($config['seo_type'] == 1 || $config['seo_type'] == 2) {
                if (intval($helper['category']) && $config['seo_type'] == 2) {
                    $fullLink = $config['http_home_url'] . get_url(intval($helper['category'])) . '/' . $helper['postid'] . '-' . $helper['alt_name'] . '.html';
                } else {
                    $fullLink = $config['http_home_url'] . $helper['postid'] . '-' . $helper['alt_name'] . '.html';
                }
            } else {
                $fullLink = $config['http_home_url'] . date('Y/m/d/', strtotime($helper['date'])) . $helper['alt_name'] . '.html';
            }
        } else {
            $fullLink = $config['http_home_url'] . 'index.php?newsid=' . $helper['postid'];
        }

        return [$fullLink, stripslashes($helper['title'])];
    }

    /**
     * Условия
     *
     * @param    array   $data
     * @return   string
     **/
    static function checkIf($data)
    {
        if ($data[2] == '=') {
            return $data[$data[1]] == $data[3] ? $data[4] : '';
        }

        if ($data[2] == '!=') {
            return $data[$data[1]] != $data[3] ? $data[4] : '';
        }

        return $data[0];
    }

    /**
    * Склонение слов
    *
    * @param    array    $a    [0 => count, 1 => новост|ь|и|ей]
    * @return   string
    **/
    static function declinationLazy($a = [])
    {
        $a[0] = strip_tags($a[0] );
        $a[0] = str_replace(' ', '', $a[0]);

        $a[0] = intval($a[0]);
        $words = explode('|', trim($a[1]));
        $parts_word = [];

        switch (count($words)) {
            case 1:
                $parts_word[0] = $words[0];
                $parts_word[1] = $words[0];
                $parts_word[2] = $words[0];
                break;
            case 2:
                $parts_word[0] = $words[0];
                $parts_word[1] = $words[0] . $words[1];
                $parts_word[2] = $words[0] . $words[1];
                break;
            case 3: 
                $parts_word[0] = $words[0];
                $parts_word[1] = $words[0] . $words[1];
                $parts_word[2] = $words[0] . $words[2];
                break;
            case 4: 
                $parts_word[0] = $words[0] . $words[1];
                $parts_word[1] = $words[0] . $words[2];
                $parts_word[2] = $words[0] . $words[3];
                break;
        }

        $word = $a[0] % 10 == 1 && $a[0] % 100 != 11 ? $parts_word[0] : ($a[0] % 10 >= 2 && $a[0] % 10 <= 4 && ($a[0] % 100 < 10 || $a[0] % 100 >= 20) ? $parts_word[1] : $parts_word[2]);

        return $word;
    }

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
     * Разбор массива
     *
     * @param    array   $arr
     * @return   array
     **/
    static function unsetEmpty($arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = self::unsetEmpty($arr[$key]);
            }

            if (empty($value) || $value == '-') {
                unset($arr[$key]);
            } else {
                $arr[$key] = self::typeValue($arr[$key]);
            }
        }

        return $arr;
    }

	/**
	 * Уникализация данных в массиве
	 *
	 * @param    array   $a
	 * @return   array
	 **/
	static function uniqueArray($a)
	{
		$b = [];
		foreach ($a as $k => $v){
			if (!in_array($v, $b)) {
				$b[$k] = $v;
			}
		}

		return $b;
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
     * Очистить строку для запроса в базу данных
     *
     * @param    string   $v
     * @return   string
     **/
    static function cleanString($v)
    {
        global $db;
        return $db->safesql(strip_tags(stripslashes($v)));
    }

    /**
     * Очистить строку для запроса в базу данных
     *
     * @param    int   $v
     * @return   int
     **/
    static function cleanInt($v)
    {
        return intval($v);
    }

    /**
     * Преобразовывает массив в строку доп полей
     *
     * @param    array   $a
     * @return   string
     **/
    static function xfield($a)
    {
        global $db;

        $f = [];
        foreach ($a as $n => $v) {
            if ($n === '') {
                continue;
            }

            $n = str_replace(['|', '\r\n'], ['&#124;', '__NEWL__'], $n);
            $v = str_replace(['|', '\r\n'], ['&#124;', '__NEWL__'], $v);
            $f[] = $n . '|' . $v;
        }

        return count($f) ? $db->safesql(implode('||', $f)) : '';
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
