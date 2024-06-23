<?php
/**
* Класс AJAX Обработки
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Subscribe;

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
	 * Работа с подписками
	 *
	 **/
	static function subscribe() {
		Subscribe::subscribe();
	}

	/**
	 * Изменение языка админ панели
	 *
	 */
	static function setLang()
	{
		if (in_array($_POST['lang'], ['ru', 'en', 'ua'])) {
			$_POST['lang'] = trim(strip_tags(stripslashes($_POST['lang'])));

			$arrayConfig = Data::receive('config');
			$arrayConfig['lang'] = $_POST['lang'];

			$handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/config.php', 'w');
			fwrite($handler, "<?php\n\n//DLE Subscribe by LazyDev\n\nreturn ");
			fwrite($handler, var_export($arrayConfig, true));
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
		if (isset($_COOKIE['admin_dle_subscribe_dark'])) {
			set_cookie('admin_dle_subscribe_dark', '', -1);
			$_COOKIE['admin_dle_subscribe_dark'] = null;
		} else {
			set_cookie('admin_dle_subscribe_dark', 'yes', 300);
			$_COOKIE['admin_dle_subscribe_dark'] = 'yes';
		}

		echo 'yes';
	}

    /**
     * Сохраняем настройки
     *
     **/
    static function saveOptions()
    {
        $arrayConfig = Helper::unserializeJs($_POST['data']);

		$arrayConfigTemp = Data::receive('config');
		if (isset($arrayConfigTemp['lang']) && $arrayConfigTemp['lang']) {
			$arrayConfig['lang'] = $arrayConfigTemp['lang'];
		}

        $handler = fopen(ENGINE_DIR . '/lazydev/' . Helper::$modName . '/data/config.php', 'w');
        fwrite($handler, "<?php\n\n//DLE Subscribe by LazyDev\n\nreturn ");
        fwrite($handler, var_export($arrayConfig, true));
        fwrite($handler, ";\n");
        fclose($handler);
        echo Helper::json(['text' => Data::get(['admin', 'ajax', 'options_save'], 'lang')]);
    }

    /**
     * Отменяем подписку
     *
     **/
    static function deleteSubscribe()
    {
        global $db;
        $idSubscribe = isset($_POST['id']) ? $_POST['id'] : false;
        if ($idSubscribe) {
            $idSubscribe = is_array($idSubscribe) ? $idSubscribe : [$idSubscribe];
            $idSubscribe = array_map('intval', $idSubscribe);
            if ($idSubscribe) {
                $db->query("DELETE FROM " . PREFIX . "_dle_subscribe WHERE idSubscribe IN('" . implode("','", $idSubscribe) . "')");
                echo Helper::json(['text' => Data::get(['admin', 'ajax', 'deleteSubscribe'], 'lang')]);
            } else {
                echo Helper::json(['text' => Data::get(['admin', 'ajax', 'deleteSubscribeError'], 'lang')]);
            }
        }
    }

    /**
     * Получаем статистику
     *
     **/
    static function getStatistics()
    {
        $post = Helper::unserializeJs($_POST['data']);
        $post['id'] = intval($post['id']);
        switch ($post['id']) {
            case 0:
                self::getStatisticsWeek();
                break;
            case 1:
                self::getStatisticsMonth();
                break;
            case 2:
                self::getStatisticsDate($post);
                break;
        }
    }

    /**
     * Получаем статистику за неделю
     *
     **/
    static function getStatisticsWeek()
    {
        global $db;

        $countSubscribersWeek = $db->super_query("SELECT count(idSubscribe) as allCount, SUM(IF (user = '__GUEST__', 1, 0)) as guestCount, SUM(IF (user != '__GUEST__', 1, 0)) as userCount, SUM(IF (page = 'news', 1, 0)) as newsCount, SUM(IF (page = 'cat', 1, 0)) as catCount, SUM(IF (page = 'all', 1, 0)) as allnewsCount, SUM(IF (page = 'user', 1, 0)) as userpageCount, SUM(IF (page = 'tag', 1, 0)) as tagCount, SUM(IF (page = 'xfield', 1, 0)) as xfieldCount FROM " . PREFIX . "_dle_subscribe WHERE dateSubscribe BETWEEN DATE_SUB(NOW(),INTERVAL 1 WEEK) AND NOW()");
        $countSubscribersWeek = array_map(function($i) {
            return number_format($i, 0, '', ' ');
        }, $countSubscribersWeek);

        echo json_encode($countSubscribersWeek);
    }

    /**
     * Получаем статистику за месяц
     *
     **/
    static function getStatisticsMonth()
    {
        global $db;

        $countSubscribersMonth = $db->super_query("SELECT count(idSubscribe) as allCount, SUM(IF (user = '__GUEST__', 1, 0)) as guestCount, SUM(IF (user != '__GUEST__', 1, 0)) as userCount, SUM(IF (page = 'news', 1, 0)) as newsCount, SUM(IF (page = 'cat', 1, 0)) as catCount, SUM(IF (page = 'all', 1, 0)) as allnewsCount, SUM(IF (page = 'user', 1, 0)) as userpageCount, SUM(IF (page = 'tag', 1, 0)) as tagCount, SUM(IF (page = 'xfield', 1, 0)) as xfieldCount FROM " . PREFIX . "_dle_subscribe WHERE dateSubscribe BETWEEN DATE_SUB(NOW(),INTERVAL 1 MONTH) AND NOW()");
        $countSubscribersMonth = array_map(function($i) {
            return number_format($i, 0, '', ' ');
        }, $countSubscribersMonth);

        echo json_encode($countSubscribersMonth);
    }

    /**
     * Получаем статистику за выбранные дни
     *
     **/
    static function getStatisticsDate($post)
    {
        global $db;
        $dateMin = $db->safesql(strip_tags(stripslashes($post['dateMin'])));
        $dateMax = $db->safesql(strip_tags(stripslashes($post['dateMax'])));
        $sql = "SELECT count(idSubscribe) as allCount, SUM(IF (user = '__GUEST__', 1, 0)) as guestCount, SUM(IF (user != '__GUEST__', 1, 0)) as userCount, SUM(IF (page = 'news', 1, 0)) as newsCount, SUM(IF (page = 'cat', 1, 0)) as catCount, SUM(IF (page = 'all', 1, 0)) as allnewsCount, SUM(IF (page = 'user', 1, 0)) as userpageCount, SUM(IF (page = 'tag', 1, 0)) as tagCount, SUM(IF (page = 'xfield', 1, 0)) as xfieldCount FROM " . PREFIX . "_dle_subscribe";
        if ($dateMin && $dateMax) {
            $sql .= " WHERE DATE(dateSubscribe) BETWEEN '{$dateMin}' AND '{$dateMax}'";
        } elseif ($dateMax) {
            $sql .= " WHERE DATE(dateSubscribe) = '{$dateMax}'";
        } elseif ($dateMin) {
            $sql .= " WHERE DATE(dateSubscribe) = '{$dateMin}'";
        } else {
            $sql .= " WHERE DATE(dateSubscribe) = CURDATE()";
        }

        $countSubscribersDate = $db->super_query($sql);
        $countSubscribersDate = array_map(function($i) {
            return number_format($i, 0, '', ' ');
        }, $countSubscribersDate);

        echo json_encode($countSubscribersDate);
    }
}
