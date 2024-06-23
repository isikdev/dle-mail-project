<?php
/**
 * Локализация
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Subscribe;

use DLEPlugins;
use dle_mail;

class Subscribe
{
    static $mod = '';
    static $newsId, $userName, $xfieldName, $xfieldValue, $category, $tagValue, $pageValue, $pageType, $titlePageValue;
    static $guestEmail, $typeSub, $idBlock, $helper, $guestHash, $ajax, $only, $template;

    /**
     * Старт модуля
     *
     * @param    string    $action
     */
    static function load($action = '')
    {
        global $dle_module, $user_group, $member_id, $category_id;

        self::checkAction($action);

        if (!self::$mod) {
            self::$mod = $dle_module;
        }
        if (self::$mod == 'cat') {
            $blockedCat = explode (',', $user_group[$member_id['user_group']]['not_allow_cats']);

            if ($blockedCat[0] != '') {
                $category_id = self::$category ?: $category_id;
                if (in_array($category_id, $blockedCat)) {
                    return;
                }
            }
        }
        self::typeBlock();
    }

    /**
     * Проверка пользовательского вывода блока
     *
     * @param    string    $action
     * @return   string
     */
    static function checkAction($action)
    {
        $subscribeParam = Helper::unserializeJs($action);

        switch ($subscribeParam['action']) {
            case 'news':
                self::$newsId = $subscribeParam['id'] > 0 ? intval($subscribeParam['id']) : false;
                break;
            case 'user':
                self::$userName = $subscribeParam['user'] ?: false;
                break;
            case 'xfield':
                self::$xfieldName = $subscribeParam['xf'] ?: false;
                self::$xfieldValue = $subscribeParam['val'] ?: false;
                break;
            case 'tag':
                self::$tagValue = $subscribeParam['tag'] ?: false;
                break;
            case 'cat':
                self::$category = $subscribeParam['cat'] > 0 ? intval($subscribeParam['cat']) : false;
                break;
        }

        self::$mod = $subscribeParam['action'] ?: '';
        self::$template = $subscribeParam['template'] ?: '';
        self::$only = $subscribeParam['only'] != '' ? Helper::cleanString($subscribeParam['only']) : false;
    }

    /**
     * Определение блока подписки
     *
     */
    static function typeBlock()
    {
        global $db, $config, $category_id, $row, $cat_info, $dle_module;

        $pageValue = '';
        if (self::$mod == 'showfull' && ($pageValue = intval($_GET['newsid'])) > 0 || self::$mod == 'news' && self::$newsId !== false) {
            self::$pageValue = self::$newsId ?: $pageValue;
            self::$pageType = 'news';
            if (self::$newsId > 0) {
                $titlePageValue = self::helperData();
                self::$titlePageValue = stripslashes($titlePageValue['name']);
            } else {
                self::$titlePageValue = stripslashes($row['title']);
            }
        } elseif (self::$mod == 'xfsearch' || self::$mod == 'xfield') {
            self::$pageType = 'xfield';
            $xfName = '';
            if (self::$xfieldName && self::$xfieldValue && self::$mod == 'xfield') {
                $pageValue = self::$xfieldValue;
                $xfName = self::$xfieldName . '/';
            } elseif (self::$mod == 'xfsearch') {
                $pageValue = urldecode($_GET['xf']);
                if (dle_substr($pageValue, -1, 1, $config['charset']) == '/') {
                    $pageValue = dle_substr($pageValue, 0, -1, $config['charset']);
                }

                $pageValue = explode('/', $pageValue);
                if (count($pageValue) > 1 ) {
                    $xfName = $db->safesql(totranslit(trim($pageValue[0]))) . '/';
                    unset($pageValue[0]);
                }

                $pageValue = implode(' ', $pageValue);
            }

            $pageValue = $db->safesql(htmlspecialchars(strip_tags(stripslashes(trim($pageValue))), ENT_QUOTES, $config['charset']));
            self::$titlePageValue = str_replace("&#039;", "'", $pageValue);
            self::$pageValue = str_replace('"', "'", $xfName . $pageValue);
        } elseif (self::$mod == 'tags' || self::$mod == 'tag') {
            self::$pageType = 'tag';
            $pageValue = self::$tagValue ? self::$tagValue : urldecode($_GET['tag']);
            $pageValue = htmlspecialchars(strip_tags(stripslashes(trim($pageValue))), ENT_COMPAT, $config['charset']);
            self::$titlePageValue = $pageValue;
            self::$pageValue = $db->safesql($pageValue);
        } elseif (self::$mod == 'cat') {
            self::$pageType = 'cat';
            self::$pageValue = self::$category ?: $category_id;
            self::$titlePageValue = $cat_info[self::$pageValue]['name'];
        } elseif (self::$mod == 'userinfo' || self::$mod == 'user') {
            self::$pageType = 'user';
            $pageValue = self::$userName ?: urldecode((string)$_GET['user']);
            $pageValue = strip_tags(str_replace('/', '', $pageValue));
            if (preg_match("/[\||\'|\<|\>|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\+]/", $pageValue)) {
                $pageValue = '';
            }
            self::$titlePageValue = $pageValue;
            self::$pageValue = $db->safesql($pageValue);
        } elseif (self::$mod == 'all') {
            self::$pageType = self::$pageValue = 'all';
        }

        if (self::$only) {
            if (dle_strpos(self::$only, ',', 'UTF-8')) {
                self::$only = explode(',', self::$only);
                self::$only = array_flip(self::$only);
                if (!isset(self::$only[self::$pageValue])) {
                    return;
                }
            } else {
                if (self::$pageValue != self::$only) {
                    return;
                }
            }
        }

        if (!self::$pageType && !self::$pageValue) {
            return;
        }

        self::showBlock();
    }

    /**
     * Вывод блока подписки
     *
     */
    static function showBlock()
    {
        global $is_logged, $member_id, $db;

        $tpl = new View('dle_subscribe', self::$mod, self::$template);
        $idGuest = 0;
        $rowSubscribe = [];
        self::$idBlock = 'ds_' . md5(self::$pageValue . self::$pageType);
        if ($is_logged) {
            $rowSubscribe = $db->super_query("SELECT idSubscribe FROM " . PREFIX . "_dle_subscribe WHERE userId='{$member_id['user_id']}' AND page='" . self::$pageType . "' AND pageValue='" . self::$pageValue . "'");
        } elseif (!$is_logged && $_COOKIE['dle_subscribe'][self::$idBlock]) {
            $idGuest = $_COOKIE['dle_subscribe'][self::$idBlock];
            $rowSubscribe['idSubscribe'] = 1;
        }

        $countSubscribe = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_subscribe WHERE page='" . self::$pageType . "' AND pageValue='" . self::$pageValue . "' AND confirmed='1'")['count'];

        $tpl->tagIf($is_logged === false, 'guest');
        $tpl->tagIf(($rowSubscribe['idSubscribe'] == 1 && $idGuest), 'guest-subscribe');
        $tpl->tagIf(($rowSubscribe['idSubscribe'] > 0), 'subscribe');
        $tpl->tagIf(($countSubscribe > 0), 'count');

        $tpl->copy_template = preg_replace_callback("#\\[if (value|page)(!?=)\"(.+?)\"\\](.+?)\\[/if\\]#is", function($matches) use($tpl) {
            $matches['value'] = self::$pageValue;
            $matches['page'] = self::$pageType;
            return $tpl->checkIf($matches);
        }, $tpl->copy_template);

        $tpl->set('{guest-id}', '');
        if ($rowSubscribe['idSubscribe'] == 1 && $idGuest) {
            $tpl->set('{guest-id}', $idGuest);
        }

        $tpl->set('{count}', $countSubscribe ?: 0);
        $tpl->set('{block-id}', self::$idBlock);
        $tpl->set('{subscribe-id}', base64_encode(self::$pageValue));
        $tpl->set('{template-id}', base64_encode(self::$template));
        $tpl->set('{page-value}', self::$pageValue);
        $tpl->set('{subscribe-page}', self::$pageType);

        $tpl->set('{title}', self::$titlePageValue ?: '');

        $tpl->compile('dle_subscribe_' . self::$pageType);
        $tpl->clear();
        $tpl->result['dle_subscribe_' . self::$pageType] = '<!-- DLE Subscribe developed by https://lazydev.pro -->' . $tpl->result['dle_subscribe_' . self::$pageType] . '<!-- DLE Subscribe developed by https://lazydev.pro -->';
        if (self::$ajax) {
            echo Helper::json(['tpl' => $tpl->result['dle_subscribe_' . self::$pageType], 'guest' => Data::get(['options', 'guestApprove'], 'config')]);
        } else {
            echo $tpl->result['dle_subscribe_' . self::$pageType];
        }
    }

    /**
     * Получаем название страницы когда работаем с AJAX
     *
     */
    static function switchTitle()
    {
        switch(self::$pageType) {
            case 'news':
            case 'cat':
            case 'user':
                self::$titlePageValue = stripslashes(self::$helper['name']);
                break;
            case 'tag':
                self::$titlePageValue = stripslashes(self::$helper[0]);
                break;
            case 'all':
                self::$titlePageValue = '';
                break;
            case 'xfield':
                self::$titlePageValue = stripslashes(self::$helper[1]);
                break;
        }
    }

    /**
     * Обработка данных
     *
     */
    static function setVar()
    {
        self::$pageType = Helper::cleanString($_POST['pageType']);
        self::$pageValue = Helper::cleanString(base64_decode($_POST['pageValue']));
        self::$typeSub = Helper::cleanString($_POST['typeSub']);
        self::$idBlock = 'ds_'. md5(self::$pageValue . self::$pageType);
        self::$guestEmail = Helper::cleanString($_POST['email']);
        self::$helper = self::helperData();
        self::$ajax = true;
        self::$template = $_POST['template'];
        if (self::$template) {
            self::$template = base64_decode(self::$template);
        }
    }

    /**
     * Подписка
     *
     */
    static function subscribe()
    {
        global $member_id, $db, $user_group;
        self::setVar();
        self::switchTitle();
        self::$helper = Helper::xfield(self::$helper);

        if ($member_id['user_group'] == 5 && !(self::$guestEmail = filter_var(self::$guestEmail, FILTER_VALIDATE_EMAIL))) {
            echo Helper::json(['error' => 1, 'text' => Data::get(['site', 'error_message_email'], 'lang')]);
            exit;
        }

        $blockEmail = Data::get('email', 'config');
        if ($blockEmail) {
			if (isset($blockEmail[1]) && $blockEmail[1]) {
				$blockEmail = array_flip($blockEmail);

				if ($blockEmail[self::$guestEmail] || $blockEmail[$member_id['email']]) {
					echo Helper::json(['error' => 1, 'text' => Data::get(['site', 'error_message_block_email'], 'lang')]);
					exit;
				}
			}
        }

        $accessArray = Data::get('access', 'config') ?: [];
        $accessArray = array_flip($accessArray);
        if ($accessArray && $accessArray[$member_id['user_group']] === NULL) {
            $group = str_replace('{group}', $user_group[$member_id['user_group']]['group_name'], Data::get(['site', 'error_message_group_block'], 'lang'));
            echo Helper::json(['error' => 1, 'text' => $group]);
            exit;
        }

        if ($member_id['user_group'] == 5) {
            $checkEmail = $db->super_query("SELECT user_id FROM " . USERPREFIX . "_users WHERE email='" . self::$guestEmail . "'");
            if ($checkEmail['user_id'] > 0) {
                echo Helper::json(['error' => 1, 'text' => Data::get(['site', 'error_message_user_email'], 'lang')]);
                exit;
            }
        }
        $userType = $member_id['user_group'] !== 5 ? "userId='{$member_id['user_id']}'" : "email='" . self::$guestEmail . "'";

        $row = $db->super_query("SELECT idSubscribe, hash FROM " . PREFIX  . "_dle_subscribe WHERE page='" . self::$pageType . "' AND pageValue='" . self::$pageValue . "' AND {$userType}");
        self::$guestHash = $row['hash'];
        if ($member_id['user_group'] !== 5) {
            if ($row['idSubscribe']) {
                self::unSubscribeUser();
            } else {
                self::subscribeUser();
            }
        } else {
            if ($row['idSubscribe']) {
                self::unSubscribeGuest();
            } else {
                self::subscribeGuest();
            }
        }

        self::showBlock();
    }

    /**
     * Вспомогательные данные
     *
     */
    static function helperData()
    {
        global $db;

        $result = [];
        if (self::$pageType == 'news' && intval(self::$pageValue) > 0) {
            self::$pageValue = intval(self::$pageValue);
            $result = $db->super_query("SELECT id, title as name, alt_name, category, date FROM " . PREFIX . "_post WHERE id='" . self::$pageValue . "'");
        } elseif (self::$pageType == 'user' && self::$pageValue != '') {
            $result = $db->super_query("SELECT name, user_id FROM " . USERPREFIX . "_users WHERE name='" . self::$pageValue . "'");
        } elseif (self::$pageType == 'xfield' && self::$pageValue != '') {
            $result = explode('/', self::$pageValue);
        } elseif (self::$pageType == 'tag') {
            $result = [self::$pageValue];
        } elseif (self::$pageType == 'cat' && intval(self::$pageValue) > 0) {
            self::$pageValue = intval(self::$pageValue);
            $result = [self::$pageValue];
        }

        return $result;
    }

    /**
     * Отписка зарегистрированного пользователя
     *
     */
    static function unSubscribeUser()
    {
        global $member_id, $db;

        $db->query("DELETE FROM " . PREFIX . "_dle_subscribe WHERE page='" . self::$pageType . "' AND pageValue='" . self::$pageValue . "' AND userId='" . $member_id['user_id'] . "'");
    }

    /**
     * Отписка гостя
     *
     */
    static function unSubscribeGuest()
    {
        global $db;
        if (!$_COOKIE['dle_subscribe'][self::$idBlock] || $_COOKIE['dle_subscribe'][self::$idBlock] != self::$guestHash) {
            self::sendApproveGuest('unsubscribe_guest');
        } else {
            $db->query("DELETE FROM " . PREFIX . "_dle_subscribe WHERE hash='" . self::$guestHash . "'");
            set_cookie('dle_subscribe[' . self::$idBlock . ']', '', -1);
            unset($_COOKIE['dle_subscribe'][self::$idBlock]);
        }
    }

    /**
     * Подписка гостя
     *
     */
    static function subscribeGuest()
    {
        global $db;

        $date = date('Y-m-d H:i:s', time());
        $hash = hash('ripemd160', self::$pageValue . self::$pageType . self::$guestEmail);
        $approve = Data::get(['options', 'guestApprove'], 'config') == 1 ? 0 : 1;
        $db->query("INSERT INTO " . PREFIX . "_dle_subscribe 
        (`user`, `userId`, `dateSubscribe`, `email`, `hash`, `page`, `pageValue`, `confirmed`, `helper`) 
        VALUES
        ('__GUEST__', '0', '{$date}', '" . self::$guestEmail . "', '{$hash}', '" . self::$pageType . "', '" . self::$pageValue . "', '" . $approve . "', '" . self::$helper . "')");
        self::$guestHash = $hash;
        if (!$approve) {
            self::sendApproveGuest('subscribe_guest');
        }
        set_cookie('dle_subscribe[' . self::$idBlock . ']', $hash, 365);
        $_COOKIE['dle_subscribe'][self::$idBlock] = $hash;
    }

    /**
     * Подписка зарегистрированного пользователя
     *
     */
    static function subscribeUser()
    {
        global $member_id, $db;

        $date = date('Y-m-d H:i:s', time());
        $hash = hash('ripemd160', self::$pageValue . self::$pageType . $member_id['user_id'] . $member_id['name'] . $member_id['email']);
        $db->query("INSERT INTO " . PREFIX . "_dle_subscribe 
        (`user`, `userId`, `dateSubscribe`, `email`, `hash`, `page`, `pageValue`, `confirmed`, `helper`) 
        VALUES
        ('{$member_id['name']}', '{$member_id['user_id']}', '{$date}', '{$member_id['email']}', '{$hash}', '" . self::$pageType . "', '" . self::$pageValue . "', '1', '" . self::$helper . "')");
    }

    /**
     * Отправка уведомление об отписке
     *
     * @param    string    $tpl
     */
    static function sendApproveGuest($tpl)
    {
        global $config, $db;

        $emailTemplate = file_get_contents(TEMPLATE_DIR . '/lazydev/dle_subscribe/' . $tpl . '.tpl');
        $emailTemplate = preg_replace_callback("#\\[if (value|page)(!?=)\"(.+?)\"\\](.+?)\\[/if\\]#is", function($matches) {
            $matches['value'] = self::$pageValue;
            $matches['page'] = self::$pageType;
            return Helper::checkIf($matches);
        }, $emailTemplate);
        $emailTemplate = str_replace('{subsсriber-name}', Data::get(['admin', 'subscribers', '__GUEST__'], 'lang'), $emailTemplate);
        if (self::$pageType == 'news') {
            $getNews = $db->super_query("SELECT id as postid, title, alt_name, date, category FROM " . PREFIX . "_post WHERE id='" . self::$pageValue . "'");
            $pageData = Helper::urlNews($getNews);
        } elseif (self::$pageType == 'cat') {
            $pageData = Helper::urlCat(['catid' => self::$pageValue]);
        } else {
            $pageData = Helper::getUrl(self::$pageType, self::$helper);
        }
        if (!$pageData[0]) {
            $pageData[0] = $config['http_home_url'];
            $pageData[1] = $config['short_title'];
        }
        $emailTemplate = str_replace('{page-url}', $pageData[0], $emailTemplate);
        $emailTemplate = str_replace('{page-name}', $pageData[1], $emailTemplate);
        preg_match("#\\[subject\\](.*?)\\[/subject\\]#is", $emailTemplate, $emailSubject);
        $emailTemplate = preg_replace("#\\[subject\\](.*?)\\[/subject\\]#is", '', $emailTemplate);
        if (!$emailSubject[1]) {
            $emailSubject[1] = Data::get(['site', 'emailSubjectUnsubscribe'], 'lang') . $config['http_home_url'];
        }
        $emailSubject[1] = $db->safesql($emailSubject[1]);
        $urlUnsubscribe = $config['http_home_url'] . 'subscribe/decline/' . self::$guestHash . '/';
        $emailTemplate = str_replace('{unsubscribe-url}', $urlUnsubscribe, $emailTemplate);
        $urlSubscribe = $config['http_home_url'] . 'subscribe/accept/' . self::$guestHash . '/';
        $emailTemplate = str_replace('{subscribe-url}', $urlSubscribe, $emailTemplate);
        $emailTemplate = str_replace('{site}', $config['http_home_url'], $emailTemplate);
        $emailTemplate = str_replace('{template}', $config['http_home_url'] . 'templates/' . $config['skin'] . '/lazydev/dle_subscribe', $emailTemplate);
        include DLEPlugins::Check(ENGINE_DIR . '/classes/mail.class.php');
        $mail = new dle_mail($config, true);
        $mail->send(self::$guestEmail, $emailSubject[1], $emailTemplate);
    }
}
