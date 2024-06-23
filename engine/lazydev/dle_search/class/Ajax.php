<?php
/**
* Класс AJAX обработки админ панели
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Search;

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
     * Добавление / редактирование подмены запроса
     *
     **/
    static function addReplace()
    {
        $data = include ENGINE_DIR . '/lazydev/dle_search/data/replace.php';
        $action = 'replace_add';

        if (isset($_POST['id']) && intval($_POST['id']) >= 0) {
            $id = intval($_POST['id']);
            $action = 'replace_edit';
        } else {
            $id = array_keys($data)[count($data)-1]+1;
        }

        $arrayFind = explode(PHP_EOL, $_POST['find']);
        array_walk($arrayFind, function (&$item, $key) {
            $item = trim(strip_tags(stripslashes($item)));
        });

        $data[$id] = [
            'find' => $arrayFind,
            'replace' => strip_tags(stripslashes($_POST['replace'])),
            'full' => intval($_POST['full'])
        ];

        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/replace.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Search by LazyDev\n\nreturn ");
        fwrite($handler, var_export($data, true));
        fwrite($handler, ";\n");
        fclose($handler);

        echo Helper::json(['text' => Data::get(['admin', 'ajax', $action], 'lang')]);
    }

    /**
     * Удаление подмены
     *
     */
    static function deleteReplace()
    {
        if (($id = intval($_POST['id'])) < 0) {
            echo Helper::json(['text' => Data::get(['admin', 'ajax', 'error'], 'lang'), 'error' => 'true']);
            exit;
        }

        $data = include ENGINE_DIR . '/lazydev/dle_search/data/replace.php';
        if (!isset($data[$id])) {
            echo Helper::json(['text' => Data::get(['admin', 'ajax', 'error'], 'lang'), 'error' => 'true']);
            exit;
        }

        unset($data[$id]);

        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/replace.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Search by LazyDev\n\nreturn ");
        fwrite($handler, var_export($data, true));
        fwrite($handler, ";\n");
        fclose($handler);
        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'replace_delete'], 'lang')]);
    }

    /**
     * Сохраняем настройки
     *
     **/
    static function saveOptions()
    {
        $arrayConfig = Helper::unserializeJs($_POST['data']);
        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/config.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Search by LazyDev\n\nreturn ");
        fwrite($handler, var_export($arrayConfig, true));
        fwrite($handler, ";\n");
        fclose($handler);

        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'options_save'], 'lang')]);
    }

    /**
     * Очищаем статистику
     *
     */
    static function clearStatistics()
    {
        global $db;

        $db->query("TRUNCATE " . PREFIX . "_dle_search_statistics");
        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'clear_statistics'], 'lang')]);
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

    /**
     * Поиск новостей
     *
     **/
    static function findNews()
    {
        global $db, $config;

        if (preg_match("/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_POST['query']) || !$_POST['query']) {
            exit;
        }

        $query = $db->safesql(htmlspecialchars(strip_tags(stripslashes(trim($_POST['query']))), ENT_QUOTES, $config['charset']));
        $db->query("SELECT id, title as name FROM " . PREFIX . "_post WHERE `title` LIKE '%{$query}%' AND approve ORDER BY date DESC LIMIT 15");

        $search = [];

        while ($row = $db->get_row()) {
            $row['name'] = str_replace("&quot;", '"', $row['name']);
            $row['name'] = str_replace("&#039;", "'", $row['name']);
            $row['name'] = stripslashes($row['name']);

            $search[] = ['value' => $row['id'], 'name' => $row['name']];
        }

        echo json_encode($search);
    }

    /**
     * Включение/Выключение тёмной темы
     *
     */
    static function setDark()
    {
        if (isset($_COOKIE['admin_dle_search_dark'])) {
            set_cookie('admin_dle_search_dark', '', -1);
            $_COOKIE['admin_dle_search_dark'] = null;
        } else {
            set_cookie('admin_dle_search_dark', 'yes', 300);
            $_COOKIE['admin_dle_search_dark'] = 'yes';
        }

        echo 'yes';
    }

	/**
	 * Изменение языка админ панели
	 *
	 */
	static function setLang()
	{
		if (in_array($_POST['lang'], ['ru', 'en', 'ua'])) {
			$_POST['lang'] = trim(strip_tags(stripslashes($_POST['lang'])));
			set_cookie('lang_dle_search', $_POST['lang'], 300);
			$_COOKIE['lang_dle_search'] = $_POST['lang'];
		}

		echo 'yes';
	}
}
