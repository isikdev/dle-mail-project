<?php
/**
 * Класс AJAX обработки админ панели
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Seo;

use ParseFilter;
use DLEPlugins;

include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php'));
include_once(DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
$parse = new ParseFilter();

class Ajax
{
    /**
     * Определяем AJAX действие
     *
     * @param    string    $action
     *
     **/
    static function ajaxAction($action)
    {
        in_array($action, get_class_methods(self::class)) && self::$action();
    }

	/**
	 * Всё по карте сайта
	 *
	 */
	static function sitemap() {
		global $dleSeoLang, $member_id, $config, $db, $user_group;

		$role = isset($_POST['role']) ? trim(strip_tags($_POST['role'])) : false;
		in_array($role, get_class_methods('LazyDev\Seo\Map')) && Map::$role($_POST);
	}

    /**
     * Изменение языка админ панели
     *
     */
    static function setLang()
    {
		global $dleSeoConfig;

		if (in_array($_POST['lang'], ['ru', 'en', 'ua'])) {
			$dleSeoConfig['lang'] = trim(strip_tags(stripslashes($_POST['lang'])));
			$handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/config.php', 'w');
			fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
			fwrite($handler, var_export($dleSeoConfig, true));
			fwrite($handler, ";\n");
			fclose($handler);
		}

        echo 'yes';
    }

    /**
     * Включение/Выключение тёмной темы
     *
     */
    static function setDark()
    {
        if (isset($_COOKIE['admin_seo_dark'])) {
            set_cookie('admin_seo_dark', '', -1);
            $_COOKIE['admin_seo_dark'] = null;
        } else {
            set_cookie('admin_seo_dark', 'yes', 300);
            $_COOKIE['admin_seo_dark'] = 'yes';
        }

        echo 'yes';
    }

    /**
     * Удаление записи
     *
     */
    static function deleteSeo()
    {
        global $dleSeoLang, $db;

        $id = intval($_POST['id']);
        $db->query("DELETE FROM " . PREFIX . "_dle_seo WHERE id='{$id}'");

        $row = $db->super_query("SELECT name  FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='0'");
        $listimages = explode('|||', $row['name']);

        foreach ($listimages as $dataimages) {
            $url_image = explode('/', $dataimages);

            if (count($url_image) == 2) {
                $folder_prefix = $url_image[0] . '/';
                $dataimages = $url_image[1];
            } else {
                $folder_prefix = '';
                $dataimages = $url_image[0];
            }

            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . $dataimages);
            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . 'thumbs/' . $dataimages);
            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . 'medium/' . $dataimages);
        }
        $db->query("DELETE FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='0'");

        $db->query("SELECT id, onserver FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='1'");
        while ($row = $db->get_row()) {
            $url = explode('/', $row['onserver']);

            $folder_prefix = '';
            $file = $url[0];
            if (count($url) == 2) {
                $folder_prefix = $url[0] . '/';
                $file = $url[1];
            }

            $file = totranslit($file, false);

            if (trim($file) == '.htaccess') {
                continue;
            }

            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . $file);
        }
        $db->query("DELETE FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='1'");
        $db->query("DELETE FROM " . PREFIX . "_dle_seo_value WHERE seoId='{$id}'");
        echo Helper::json(['text' => $dleSeoLang['admin']['ajax']['delete_seo']]);
    }

    /**
     * Удаление правила новостей
     *
     */
    static function deleteRule()
    {
        global $dleSeoLang, $db;

        $id = intval($_POST['id']);
        $newsRule = Data::receive('news');

        unset($newsRule[$id]);
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/news.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
        fwrite($handler, var_export($newsRule, true));
        fwrite($handler, ";\n");
        fclose($handler);

        $db->query("DELETE FROM " . PREFIX . "_dle_seo_news WHERE id='{$id}'");

        echo Helper::json(['text' => $dleSeoLang['admin']['ajax']['delete_rule']]);
    }

    /**
     * Удаление правила категорий
     *
     */
    static function deleteRuleCat()
    {
        global $dleSeoLang, $db;

        $id = intval($_POST['id']);
        $newsRule = Data::receive('cat');

        unset($newsRule[$id]);
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/cats.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
        fwrite($handler, var_export($newsRule, true));
        fwrite($handler, ";\n");
        fclose($handler);

        $db->query("DELETE FROM " . PREFIX . "_dle_seo_cats WHERE id='{$id}'");

        $id = 'cat_' . $id;
        $row = $db->super_query("SELECT name  FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='0'");
        $listimages = explode('|||', $row['name']);

        foreach ($listimages as $dataimages) {
            $url_image = explode('/', $dataimages);

            if (count($url_image) == 2) {
                $folder_prefix = $url_image[0] . '/';
                $dataimages = $url_image[1];
            } else {
                $folder_prefix = '';
                $dataimages = $url_image[0];
            }

            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . $dataimages);
            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . 'thumbs/' . $dataimages);
            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . 'medium/' . $dataimages);
        }
        $db->query("DELETE FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='0'");

        $db->query("SELECT id, onserver FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='1'");
        while ($row = $db->get_row()) {
            $url = explode('/', $row['onserver']);

            $folder_prefix = '';
            $file = $url[0];
            if (count($url) == 2) {
                $folder_prefix = $url[0] . '/';
                $file = $url[1];
            }

            $file = totranslit($file, false);

            if (trim($file) == '.htaccess') {
                continue;
            }

            @unlink(ROOT_DIR . '/uploads/dle_seo/' . $folder_prefix . $file);
        }
        $db->query("DELETE FROM " . PREFIX . "_dle_seo_files WHERE seoId='{$id}' AND type='1'");


        echo Helper::json(['text' => $dleSeoLang['admin']['ajax']['delete_rule']]);
    }

    /**
     * Добавление записи
     */
    static function addSeo()
    {
        global $dleSeoLang, $member_id, $config, $db;

        @header('X-XSS-Protection: 0;');

        $parse = new ParseFilter();
        $idSeo = intval($_POST['idSeo']);
        $type = intval($_POST['type']);

        if (empty($_POST['valSeo'])) {
            echo Helper::json(['text' => $dleSeoLang['admin']['ajax']['not_seo'], 'error' => 'true']);
            exit;
        }

        $arrayVal = [];
        foreach ($_POST['valSeo'] as $nameVal) {
            $arrayVal[] = $db->safesql(trim(strip_tags($nameVal)));
        }
        $arrayVal = array_unique($arrayVal);
        $valStr = implode(',', $arrayVal);

        $xfName = $_POST['xf_name'] ? $parse->process(trim(strip_tags($_POST['xf_name']))) : '';

        $seo_title = $db->safesql(Helper::extString($_POST['seo_title']));

        $description = $parse->process($_POST['short_story']);
        $description = $db->safesql($parse->BB_Parse($description, (bool)$config['allow_admin_wysiwyg']));

        $_meta['meta_title'] = $parse->process(trim(strip_tags($_POST['meta_title'])));
        $_meta['meta_descr'] = Helper::extString($_POST['meta_descr']);
        $_meta['meta_key'] = Helper::extString($_POST['meta_key']);
        $_meta['meta_speedbar'] = Helper::extString($_POST['meta_speedbar']);

        $_og['title'] = Helper::extString($_POST['og_title']);
        $_og['description'] = Helper::extString($_POST['og_descr']);
        $_og['type'] = Helper::extString($_POST['meta_og_type']);
        $_og['image'] = $parse->process(trim(strip_tags($_POST['og_photo'])));

        $meta = Helper::stringData($_meta);
        $og = Helper::stringData($_og);

        $check = $db->super_query("SELECT id FROM " . PREFIX . "_dle_seo WHERE id='{$idSeo}'")['id'];
        if ($check) {
            $db->query("UPDATE " . PREFIX . "_dle_seo SET `type`='{$type}', `meta`='{$meta}', `og`='{$og}', `seoTitle`='{$seo_title}', `seoText`='{$description}', `val`='{$valStr}', `xfName`='{$xfName}' WHERE id='{$idSeo}'");
            $db->query("DELETE FROM " . PREFIX . "_dle_seo_value WHERE seoId='{$idSeo}'");
            foreach ($arrayVal as $Val) {
                $Val = htmlspecialchars($Val, ENT_COMPAT, $config['charset']);
                $db->query("INSERT INTO " . PREFIX . "_dle_seo_value (`seoId`, `type`, `value`, `xfieldName`) VALUES ('{$idSeo}', '{$type}', '{$Val}', '{$xfName}')");
            }
            $db->query("UPDATE " . PREFIX . "_dle_seo_files SET seoId='{$idSeo}' WHERE author='{$member_id['name']}' AND seoId='0'");
            echo Helper::json(['type' => 'edit', 'id' => $idSeo, 'add' => $type]);
        } else {
            $db->query("INSERT INTO " . PREFIX . "_dle_seo (`type`, `meta`, `og`, `seoTitle`, `seoText`, `val`, `xfName`) VALUES ('{$type}', '{$meta}', '{$og}', '{$seo_title}', '{$description}', '{$valStr}', '{$xfName}')");
            $id = $db->insert_id();
            foreach ($arrayVal as $Val) {
                $Val = htmlspecialchars($Val, ENT_COMPAT, $config['charset']);
                $db->query("INSERT INTO " . PREFIX . "_dle_seo_value (`seoId`, `type`, `value`, `xfieldName`) VALUES ('{$id}', '{$type}', '{$Val}', '{$xfName}')");
            }
            $db->query("UPDATE " . PREFIX . "_dle_seo_files SET seoId='{$id}' WHERE author='{$member_id['name']}' AND seoId='0'");
            echo Helper::json(['type' => 'add', 'id' => $id, 'add' => $type]);
        }
    }

    /**
     * Сохранение правил для новостей
     *
     */
    static function addRule()
    {
        global $config, $db;

        @header('X-XSS-Protection: 0;');

        $parse = new ParseFilter();
        $newsRule = Data::receive('news');
        $indexEdit = intval($_POST['id']);

        $catArray = [];
        foreach ($_POST['cat'] as $cat) {
            if (is_numeric($cat)) {
                $cat = intval($cat);
                $catArray[$cat] = $cat;
            } elseif ($cat == 'all') {
                unset($catArray);
                $catArray = ['all'];
                break;
            }
        }
        $cats = $db->safesql(implode(',', $catArray));

        $change = intval($_POST['change']);

        $_meta['title'] = $parse->process(trim(strip_tags($_POST['meta_title'])));
        $_meta['descr'] = Helper::extString($_POST['meta_descr']);
		$_meta['meta_key'] = Helper::extString($_POST['meta_key']);
        $_meta['speedbar'] = Helper::extString($_POST['meta_speedbar']);

        $_og['title'] = Helper::extString($_POST['meta_og_title']);
        $_og['descr'] = Helper::extString($_POST['meta_og_descr']);

        $_og['image'] = $parse->process(trim(strip_tags($_POST['meta_og_image'])));
        $_og['default'] = $parse->process(trim(strip_tags($_POST['default_image'])));

        $meta = Helper::stringData($_meta);
        $og = Helper::stringData($_og);

        if ($indexEdit > 0) {
            $id = $indexEdit;
            $db->query("UPDATE " . PREFIX . "_dle_seo_news SET `cats`='{$cats}', `meta`='{$meta}', `og`='{$og}', `replacement`='{$change}' WHERE id='{$indexEdit}'");
        } else {
            $db->query("INSERT INTO " . PREFIX . "_dle_seo_news (`cats`, `meta`, `og`, `replacement`) VALUES ('{$cats}', '{$meta}', '{$og}', '{$change}')");
            $id = $db->insert_id();
        }

        $newsRule[$id] = $catArray;
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/news.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
        fwrite($handler, var_export($newsRule, true));
        fwrite($handler, ";\n");
        fclose($handler);

        echo Helper::json(['type' => 'rule', 'id' => $id, 'index' => $indexEdit, 'from' => 'news']);
    }

    /**
     * Сохранение правил для категорий
     *
     */
    static function addRuleCat()
    {
        global $config, $db, $member_id;

        @header('X-XSS-Protection: 0;');

        $parse = new ParseFilter();
        $newsRule = Data::receive('cat');
        $indexEdit = intval($_POST['id']);

        $catArray = [];
        foreach ($_POST['cat'] as $cat) {
            if (is_numeric($cat)) {
                $cat = intval($cat);
                $catArray[$cat] = $cat;
            } elseif ($cat === 'all') {
                unset($catArray);
                $catArray = ['all'];
                break;
            }
        }
        $cats = $db->safesql(implode(',', $catArray));

        $change = intval($_POST['change']);

        $_meta['title'] = $parse->process(trim(strip_tags($_POST['meta_title'])));
        $_meta['descr'] = Helper::extString($_POST['meta_descr']);
		$_meta['meta_key'] = Helper::extString($_POST['meta_key']);
        $_meta['speedbar'] = Helper::extString($_POST['meta_speedbar']);

        $_og['title'] = Helper::extString($_POST['meta_og_title']);
        $_og['descr'] = Helper::extString($_POST['meta_og_descr']);
        $_og['type'] = Helper::extString($_POST['meta_og_type']);

        $_og['image'] = $parse->process(trim(strip_tags($_POST['default_image'])));

        $meta = Helper::stringData($_meta);
        $og = Helper::stringData($_og);

        $title = $db->safesql($parse->process(trim(strip_tags($_POST['h1_title']))));
        $description = $parse->process($_POST['short_story']);
        $description = $parse->BB_Parse($description, (bool)$config['allow_admin_wysiwyg']);
        $description = $db->safesql($description);

        if ($indexEdit > 0) {
            $id = $indexEdit;
            $db->query("UPDATE " . PREFIX . "_dle_seo_cats SET `cats`='{$cats}', `meta`='{$meta}', `og`='{$og}', `replacement`='{$change}', `title`='{$title}', `text`='{$description}' WHERE id='{$indexEdit}'");
        } else {
            $db->query("INSERT INTO " . PREFIX . "_dle_seo_cats (`cats`, `meta`, `og`, `replacement`, `title`, `text`) VALUES ('{$cats}', '{$meta}', '{$og}', '{$change}', '{$title}', '{$description}')");
            $id = $db->insert_id();
        }

        $newsRule[$id] = $catArray;
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/cats.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
        fwrite($handler, var_export($newsRule, true));
        fwrite($handler, ";\n");
        fclose($handler);

        $db->query("UPDATE " . PREFIX . "_dle_seo_files SET seoId='cat_{$id}' WHERE author='{$member_id['name']}' AND seoId='cat_-1'");
        echo Helper::json(['type' => 'rule', 'id' => $id, 'index' => $indexEdit, 'from' => 'cat']);
    }

    /**
     * Сохраняем настройки
     *
     **/
    static function saveOptions()
    {
        $arrayConfig = Helper::unserializeJs($_POST['data']);
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/config.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Seo by LazyDev\n\nreturn ");
        fwrite($handler, var_export($arrayConfig, true));
        fwrite($handler, ";\n");
        fclose($handler);

        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'options_save'], 'lang')]);
    }

    /**
     * Очищаем кэш
     *
     **/
    static function clearCache()
    {
        Cache::clear();
        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'clear_cache'], 'lang')]);
    }
}
