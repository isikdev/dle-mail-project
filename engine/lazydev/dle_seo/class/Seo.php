<?php
/**
* Основной класс для работы с модулем
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Seo;

use dle_template;

class Seo
{
    private static $instance = null;
    static $information = [];
    static $dleConfig, $dleDb, $dleCat, $dleMember, $dleXfields;
    static $Condition;

    /**
     * Конструктор
     *
     * @return Seo
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
     * @return Seo
     */
    static function load()
    {
        global $config, $db, $cat_info, $member_id;

        self::$dleConfig = $config;
        self::$dleDb = $db;
        self::$dleCat = $cat_info;
        self::$dleMember = $member_id;
        self::$dleXfields = xfieldsload();

        return self::$instance;
    }

    /**
     * Проверка страницы
     *
     * @return Seo
     */
    static function checkPage($page)
    {
        switch ($page) {
            case 'tags':
                self::tags();
            break;
            case 'xfsearch':
                self::xfields();
            break;
        }

        return self::$instance;
    }

    /**
     * Обработка тега
     *
     */
    static function tags()
    {
        self::$information['tags']['value'] = $_GET['tag'];
        self::$information['tags']['value'] = self::$dleConfig['version_id'] > 13.1 ? rawurldecode(self::$information['tags']['value']) : urldecode(self::$information['tags']['value']);
        self::$information['tags']['value'] = Helper::cleanSlash(self::$information['tags']['value']);
        self::$information['tags']['value'] = htmlspecialchars(strip_tags(stripslashes(trim(self::$information['tags']['value']))), ENT_COMPAT, self::$dleConfig['charset']);
        self::$information['tags']['url'] = self::$dleConfig['version_id'] > 13.1 ? rawurlencode(str_replace(["&#039;", "&quot;", "&amp;"], ["'", '"', "&"], self::$information['tags']['value'])) : urlencode(self::$information['tags']['value']);
        self::$information['tags']['value'] = self::$dleDb->safesql(self::$information['tags']['value']);
    }

    /**
     * Обработка дополнительного поля
     *
     */
    static function xfields()
    {
        self::$information['xfsearch']['value'] = $_GET['xf'];
        self::$information['xfsearch']['value'] = self::$dleConfig['version_id'] > 13.1 ? rawurldecode(self::$information['xfsearch']['value']) : urldecode(self::$information['xfsearch']['value']);
        self::$information['xfsearch']['value'] = Helper::cleanSlash(self::$information['xfsearch']['value']);
        $xfTemp = explode('/', self::$information['xfsearch']['value']);

        if (isset($_GET['xf']) && count($xfTemp) == 2) {
            self::$information['xfsearch']['name'] = self::$dleDb->safesql(totranslit(trim($xfTemp[0])));
            self::$information['xfsearch']['value'] = self::$dleDb->safesql(htmlspecialchars(strip_tags(stripslashes(trim($xfTemp[1]))), ENT_QUOTES, self::$dleConfig['charset']));
        } elseif ($_GET['xf'] && $_GET['xn']) {
			self::$information['xfsearch']['name'] = self::$dleDb->safesql(totranslit(trim($_GET['xn'])));
			self::$information['xfsearch']['value'] = self::$dleDb->safesql(htmlspecialchars(strip_tags(stripslashes(trim($_GET['xf']))), ENT_QUOTES, self::$dleConfig['charset']));
        } else {
            self::$information['xfsearch']['value'] = self::$dleDb->safesql(htmlspecialchars(strip_tags(stripslashes(trim(implode('/', $xfTemp)))), ENT_QUOTES, self::$dleConfig['charset']));
        }
    }

    /**
     * Скачать файл
     *
     * @param string $content
     * @param int $id
     *
     * @return string
     */
    static function attach($content, $id)
    {
        global $db, $config, $lang, $user_group, $member_id, $_TIME, $news_date;

        $find_1 = $find_2 = $replace_1 = $replace_2 = [];

        $tpl = new dle_template();
        $tpl->dir = TEMPLATE_DIR;

        $db->query("SELECT * FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='1'");

        $tpl->load_template('lazydev/dle_seo/attachment.tpl');

        while ($row = $db->get_row()) {
            $row['name'] = explode('/', $row['name']);
            $row['name'] = end($row['name']);

            $filename_arr = explode('.', $row['onserver']);
            $type = strtolower(end($filename_arr));

            $find_1[] = '[attachment=' . $row['id'] . ']';
            $find_2[] = "#\[attachment={$row['id']}:(.+?)\]#i";

            if (stripos($tpl->copy_template, '{size}') !== false) {
                if ($row['size']) {
                    $tpl->set('{size}', formatsize($row['size']));
                } else {
                    $tpl->set('{size}', formatsize(@filesize(ROOT_DIR . '/uploads/dle_seo/' . $row['onserver'])));
                }
            }

            if ($user_group[$member_id['user_group']]['allow_files']) {
                $tpl->set('[allow-download]', '');
                $tpl->set('[/allow-download]', '');
                $tpl->set_block("'\\[not-allow-download\\](.*?)\\[/not-allow-download\\]'si", '');
            } else {
                $tpl->set('[not-allow-download]', '');
                $tpl->set('[/not-allow-download]', '');
                $tpl->set_block("'\\[allow-download\\](.*?)\\[/allow-download\\]'si", '');
            }

            $row['date'] = strtotime($row['date']);

            if (date('Ymd', $row['date']) == date('Ymd', $_TIME)) {
                $tpl->set('{date}', $lang['time_heute'] . langdate(", H:i", $row['date']));
            } elseif (date('Ymd', $row['date']) == date('Ymd', ($_TIME - 86400))) {
                $tpl->set('{date}', $lang['time_gestern'] . langdate(", H:i", $row['date']));
            } else {
                $tpl->set('{date}', langdate($config['timestamp_active'], $row['date']));
            }

            $news_date = $row['date'];
            $tpl->copy_template = preg_replace_callback("#\{date=(.+?)\}#i", 'formdate', $tpl->copy_template);

            $tpl->set('{name}', $row['name']);
            $tpl->set('{extension}', $type);
            $tpl->set('{link}', $config['http_home_url'] . 'index.php?do=dle_seo_download&id=' . $row['id']);
            $tpl->set('{id}', $row['id']);

            $tpl->compile('attachment');

            $replace_1[] = $tpl->result['attachment'];
            $tpl->result['attachment'] = str_replace($row['name'], "\\1", $tpl->result['attachment']);
            $replace_2[] = $tpl->result['attachment'];

            $tpl->result['attachment'] = '';
        }

        $tpl->clear();
        $db->free();

        $content = str_replace($find_1, $replace_1, $content);
        $content = preg_replace($find_2, $replace_2, $content);

        return $content;
    }

	static function setFirstUp($str) {
		$fc = mb_strtoupper(mb_substr($str, 0, 1), 'UTF-8');
		return $fc . mb_substr($str, 1);
	}

    /**
     * Теги для новостей
     *
     * @param array $newsData
     * @param array $xfieldsdata
     * @param string $string
     *
     * @return string
     */
    static function setMetaTags($newsData, $xfieldsdata, $string)
    {
        global $xfields, $cat_info, $category_id, $config;

        $catId = explode(',', $category_id);
        $catId = intval($catId[0]);

		$newsData['title'] = stripslashes($newsData['title']);
		$newsData['description'] = stripslashes($newsData['description']);
		$newsData['keywords'] = stripslashes($newsData['keywords']);

        $Condition = \LazyDev\Seo\Conditions::construct();
		$string = $Condition::realize($string, $newsData);

		$string = str_replace(
		['{title}', '{title low}', '{title up}', '{title case}', '{title first}'],
		[
			$newsData['title'],
			mb_convert_case($newsData['title'], MB_CASE_LOWER, 'UTF-8'),
			mb_convert_case($newsData['title'], MB_CASE_UPPER, 'UTF-8'),
			mb_convert_case($newsData['title'], MB_CASE_TITLE, 'UTF-8'),
			self::setFirstUp($newsData['title'])
		], $string);

        $news_date = strtotime($newsData['date']);
        $string = preg_replace_callback("#\{date=(.+?)\}#i", 'formdate', $string);

		$string = str_replace(['{cat}', '{alt-name}', '{author}', '{id}'], [$cat_info[$catId]['name'], $newsData['alt_name'], $newsData['autor'], $newsData['id']], $string);

        $tags = [];
        if ($newsData['tags']) {
            $newsData['tags'] = explode(',', $newsData['tags']);

            foreach ($newsData['tags'] as $value) {
                $value = str_replace(["&#039;", "&quot;", "&amp;"], ["'", '"', "&"], trim($value));
                $tags[] = $value;
            }

            if ($tags) {
                $string = str_replace('{tags}', implode(', ', $tags), $string);
                $string = preg_replace("'\\[not-tags\\](.*?)\\[/not-tags\\]'is", '', $string);
                $string = str_ireplace("[tags]", '', $string);
                $string = str_ireplace("[/tags]", '', $string);
            }
        }

        if (!$tags) {
            $string = preg_replace("'\\[not-tags\\](.*?)\\[/not-tags\\]'is", '\\1', $string);
            $string = preg_replace("'\\[tags\\](.*?)\\[/tags\\]'is", '', $string);
        }

        $string = html_entity_decode($string, ENT_COMPAT, $config['charset']);

        foreach ($xfields as $value) {
            $preg_safe_name = preg_quote($value[0], "'");

            if ($xfieldsdata[$value[0]]) {
                $string = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", '', $string);
                $string = str_ireplace("[xfgiven_{$value[0]}]", '', $string);
                $string = str_ireplace("[/xfgiven_{$value[0]}]", '', $string);
            } else {
                $string = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", '', $string);
                $string = str_ireplace("[xfnotgiven_{$value[0]}]", '', $string);
                $string = str_ireplace("[/xfnotgiven_{$value[0]}]", '', $string);
            }

            $string = str_ireplace("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $string);

            if (preg_match("#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $string, $matches)) {
                $count = intval($matches[1]);

                $xfieldsdata[$value[0]] = str_replace('><', '> <', $xfieldsdata[$value[0]]);
                $xfieldsdata[$value[0]] = strip_tags($xfieldsdata[$value[0]], '<br>');
                $xfieldsdata[$value[0]] = trim(str_replace(['<br>', '<br />', '\n', '\r'], ' ', $xfieldsdata[$value[0]]));
                $xfieldsdata[$value[0]] = preg_replace('/\s+/u', ' ', $xfieldsdata[$value[0]]);

                if ($count && dle_strlen($xfieldsdata[$value[0]], $config['charset']) > $count) {
                    $xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $count, $config['charset']);
                    if (($temp_dmax = dle_strrpos($xfieldsdata[$value[0]], ' ', $config['charset']))) {
                        $xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset']);
                    }
                }

                $string = str_ireplace($matches[0], $xfieldsdata[$value[0]], $string);
            }
        }

        $newsData['full_story'] = preg_replace("#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", '', $newsData['full_story']);
        $newsData['full_story'] = preg_replace("#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", '', $newsData['full_story']);
        $newsData['full_story'] = preg_replace("'\[attachment=(.*?)\]'si", '', $newsData['full_story'] );
        $newsData['full_story'] = preg_replace("#\[hide(.*?)\](.+?)\[/hide\]#is", '', $newsData['full_story'] );

        $newsData['full_story'] = str_replace('><', '> <', $newsData['full_story']);
        $newsData['full_story'] = strip_tags($newsData['full_story'], "<br>");
        $newsData['full_story'] = trim(str_replace(["<br>", "<br />", "\n", "\r"], ' ', $newsData['full_story']));
        $newsData['full_story'] = preg_replace('/\s+/u', ' ', $newsData['full_story']);

        if ($newsData['full_story']) {
            $string = preg_replace("'\\[not-full-story\\](.*?)\\[/not-full-story\\]'is", '', $string);
            $string = preg_replace("'\\[full-story\\](.*?)\\[/full-story\\]'is", '\\1', $string);
            $string = str_replace('{full-story}', $newsData['full_story'], $string);
        } else {
            $string = preg_replace("'\\[not-full-story\\](.*?)\\[/not-full-story\\]'is", '\\1', $string);
            $string = preg_replace("'\\[full-story\\](.*?)\\[/full-story\\]'is", '', $string);
            $string = str_replace('{full-story}', '', $string);
        }

        if (preg_match("#\\{full-story limit=['\"](.+?)['\"]\\}#i", $string, $matches)) {
            $count = intval($matches[1]);

            if ($count && dle_strlen($newsData['full_story'], $config['charset']) > $count) {
                $newsData['full_story'] = dle_substr($newsData['full_story'], 0, $count, $config['charset']);
                if (($temp_dmax = dle_strrpos($newsData['full_story'], ' ', $config['charset']))) {
                    $newsData['full_story'] = dle_substr($newsData['full_story'], 0, $temp_dmax, $config['charset']);
                }
            }

            $string = str_replace($matches, $newsData['full_story'], $string);
        }

        $newsData['short_story'] = preg_replace("#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", '', $newsData['short_story']);
        $newsData['short_story'] = preg_replace("#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", '', $newsData['short_story']);
        $newsData['short_story'] = preg_replace("'\[attachment=(.*?)\]'si", '', $newsData['short_story'] );
        $newsData['short_story'] = preg_replace("#\[hide(.*?)\](.+?)\[/hide\]#is", '', $newsData['short_story'] );

        $newsData['short_story'] = str_replace('><', '> <', $newsData['short_story']);
        $newsData['short_story'] = strip_tags($newsData['short_story'], "<br>");
        $newsData['short_story'] = trim(str_replace(["<br>", "<br />", "\n", "\r"], ' ', $newsData['short_story']));
        $newsData['short_story'] = preg_replace('/\s+/u', ' ', $newsData['short_story']);

        if ($newsData['short_story']) {
            $string = preg_replace("'\\[not-short-story\\](.*?)\\[/not-short-story\\]'is", '', $string);
            $string = preg_replace("'\\[short-story\\](.*?)\\[/short-story\\]'is", '\\1', $string);
            $string = str_replace('{short-story}', $newsData['short_story'], $string);
        } else {
            $string = preg_replace("'\\[not-short-story\\](.*?)\\[/not-short-story\\]'is", '\\1', $string);
            $string = preg_replace("'\\[short-story\\](.*?)\\[/short-story\\]'is", '', $string);
            $string = str_replace('{short-story}', '', $string);
        }

        if (preg_match("#\\{short-story limit=['\"](.+?)['\"]\\}#i", $string, $matches)) {
            $count = intval($matches[1]);

            if ($count && dle_strlen($newsData['short_story'], $config['charset']) > $count) {
                $newsData['short_story'] = dle_substr($newsData['short_story'], 0, $count, $config['charset']);
                if (($temp_dmax = dle_strrpos($newsData['short_story'], ' ', $config['charset']))) {
                    $newsData['short_story'] = dle_substr($newsData['short_story'], 0, $temp_dmax, $config['charset']);
                }
            }

            $string = str_replace($matches, $newsData['short_story'], $string);
        }

        $string = preg_replace("#\\[xfvalue_(.+?)\\]#i", '', $string);
		$string = str_replace("&amp;amp;", "&amp;", $string);
        return $string;
    }

    /**
     * Теги для категорий
     *
     * @param string $string
     * @param int $count_all
     *
     * @return string
     */
    static function setMetaTagsCat($string, $count_all)
    {
        global $category_id, $cat_info, $config;

		$cat_info[$category_id]['name'] = stripslashes($cat_info[$category_id]['name']);

		$string = str_replace(
		['{id}', '{name}', '{name low}', '{name up}', '{name case}', '{name first}', '{alt-name}', '{count}'],
		[
			$category_id,
			$cat_info[$category_id]['name'],
			mb_convert_case($cat_info[$category_id]['name'], MB_CASE_LOWER, 'UTF-8'),
			mb_convert_case($cat_info[$category_id]['name'], MB_CASE_UPPER, 'UTF-8'),
			mb_convert_case($cat_info[$category_id]['name'], MB_CASE_TITLE, 'UTF-8'),
			self::setFirstUp($cat_info[$category_id]['name']),
			$cat_info[$category_id]['alt_name'],
			$count_all ?: 0
		],
		$string);

        if (isset($_GET['cstart']) && intval($_GET['cstart']) > 1) {
			$string = str_replace(['{page}', '[page]', '[/page]'], [intval($_GET['cstart']), '', ''], $string);
            $string = preg_replace("'\\[not-page\\](.*?)\\[/not-page\\]'is", '', $string);
        } else {
			$string = str_replace(['[not-page]', '[/not-page]'], '', $string);
            $string = preg_replace("'\\[page\\](.*?)\\[/page\\]'is", '', $string);
        }

        if ($cat_info[$category_id]['parentid'] > 0) {
			$string = str_replace(['{parent-id}', '{parent-name}', '[parent]', '[/parent]'], [$cat_info[$category_id]['parentid'], $cat_info[$cat_info[$category_id]['parentid']]['name'], '', ''], $string);
        } else {
            $string = preg_replace("'\\[parent\\](.*?)\\[/parent\\]'is", '', $string);
        }

        $string = html_entity_decode($string, ENT_COMPAT, $config['charset']);
		$string = str_replace("&amp;amp;", "&amp;", $string);
        return $string;
    }

	static function setXTMeta($string, $count_all, $value)
	{
		$string = str_replace(
			['{value}', '{value low}', '{value up}', '{value case}', '{value first}', '{count}'],
			[
				$value,
				mb_convert_case($value, MB_CASE_LOWER, 'UTF-8'),
				mb_convert_case($value, MB_CASE_UPPER, 'UTF-8'),
				mb_convert_case($value, MB_CASE_TITLE, 'UTF-8'),
				self::setFirstUp($value),
				$count_all
			], $string);

		if (isset($_GET['cstart']) && intval($_GET['cstart']) > 1) {
			$string = str_replace(['{page}', '[page]', '[/page]'], [intval($_GET['cstart']), '', ''], $string);
			$string = preg_replace("'\\[not-page\\](.*?)\\[/not-page\\]'is", '', $string);
		} else {
			$string = str_replace(['{page}', '[not-page]', '[/not-page]'], '', $string);
			$string = preg_replace("'\\[page\\](.*?)\\[/page\\]'is", '', $string);
		}

		return $string;
	}

	static function decodeMeta($string)
	{
		global $config, $parse, $newsRows, $xfieldsSeo;

		$string = self::setMetaTags($newsRows, $xfieldsSeo, $string);
		$string = $parse->decodeBBCodes($string, false);
		$string = str_replace("&amp;","&", $string);

		return str_replace(['&', '{', '}', '[', ']', "&amp;amp;", '&amp;#', '&#039;'], ['&amp;', '&#123;', '&#125;', '&#91;', '&#93;', "&amp;", '&#', "'"], $string);
	}

	static function decodeCatMeta($string, $count_all)
	{
		global $config, $parse;

		$string = self::setMetaTagsCat($string, $count_all);
		$string = $parse->decodeBBCodes($string, false);
		$string = str_replace("&amp;", "&", $string);

		return str_replace(['&', '{', '}', '[', ']', "&amp;amp;", '&amp;#', '&#039;'], ['&amp;', '&#123;', '&#125;', '&#91;', '&#93;', "&amp;", '&#', "'"], $string);
	}

	static function decodeXTMeta($string)
	{
		global $config, $parse;

		$string = $parse->decodeBBCodes($string, false);
		$string = str_replace("&amp;", "&", $string);
		$string = html_entity_decode($string, ENT_COMPAT, $config['charset']);
		$string = str_replace("&amp;amp;", "&amp;", $string);

		return str_replace(['&', '{', '}', '[', ']', "&amp;amp;", '&amp;#', '&#039;'], ['&amp;', '&#123;', '&#125;', '&#91;', '&#93;', "&amp;", '&#', "'"], $string);
	}

	static function decodeOGMeta($string)
	{
		global $config, $parse;

		$string = $parse->decodeBBCodes($string, false);
		$string = str_replace("&amp;", "&", $string);
		$string = html_entity_decode($string, ENT_COMPAT, $config['charset']);
		$string = str_replace(["&amp;amp;", '&amp;#', '&#039;'], ["&amp;", '&#', "'"], $string);
		$string = str_replace(['{', '}', '[', ']'], '', $string);
		$string = str_replace('&', '&amp;', $string);
		$string = str_replace(["&amp;amp;", "&amp;#", '"'], ["&amp;", "&#", '&quot;'], $string);

		$string = trim($string);

		return str_replace(['&', '{', '}', '[', ']', "&amp;amp;", '&amp;#', '&#039;'], ['&amp;', '&#123;', '&#125;', '&#91;', '&#93;', "&amp;", '&#', "'"], $string);
	}

    /**
     * Получение картинки
     *
     * @param string $row
     * @return mixed
     */
    static function getImage($row)
    {
        $images = [];
        preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row, $media);
        $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

        foreach ($data as $url) {
            $info = pathinfo($url);
            if (isset($info['extension'])) {
                if ($info['filename'] === 'spoiler-plus' || $info['filename'] === 'spoiler-minus' || strpos($info['dirname'], 'engine/data/emoticons') !== false) {
                    continue;
                }

                $info['extension'] = strtolower($info['extension']);
                if (($info['extension'] === 'jpg') || ($info['extension'] === 'jpeg') || ($info['extension'] === 'gif') || ($info['extension'] === 'png') || ($info['extension'] === 'webp') || ($info['extension'] === 'jfif')) {
                    $images[] = $url;
                }
            }
        }

        if (count($images)) {
            return $images[0];
        }

        return false;
    }
}