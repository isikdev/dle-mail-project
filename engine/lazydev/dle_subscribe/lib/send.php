<?php
/**
 * Отправка уведомлений
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

@error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -33));
define('ENGINE_DIR', ROOT_DIR . '/engine');

use LazyDev\Subscribe\Data;
use LazyDev\Subscribe\Helper;

include ENGINE_DIR . '/lazydev/dle_subscribe/loader.php';
$_TIME = time();
header('Content-type: text/html; charset=' . $config['charset']);
date_default_timezone_set($config['date_adjust']);
setlocale(LC_NUMERIC, 'C');

require_once DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng');
require_once DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php');
require_once DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
dle_session();

$user_group = get_vars('usergroup');
if (!$user_group) {
    $user_group = [];
    $db->query('SELECT * FROM ' . USERPREFIX . '_usergroups ORDER BY id ASC');
    while ($row = $db->get_row()) {
        $user_group[$row['id']] = [];
        foreach ($row as $key => $value) {
            $user_group[$row['id']][$key] = stripslashes($value);
        }
    }
    set_vars('usergroup', $user_group);
    $db->free();
}

$cat_info = get_vars('category');
if (!$cat_info) {
    $cat_info = [];
    $db->query('SELECT * FROM ' . PREFIX . '_category ORDER BY posi ASC');
    while ($row = $db->get_row()) {
        $cat_info[$row['id']] = [];
        foreach ($row as $key => $value) {
            $cat_info[$row['id']][$key] = stripslashes($value);
        }
    }
    set_vars('category', $cat_info);
    $db->free();
}

if ($_REQUEST['skin']) {
    $_REQUEST['skin'] = $_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['skin'], false, false));
}

if ($_REQUEST['dle_skin']) {
    $_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['dle_skin'], false, false));

    if ($_REQUEST['dle_skin'] AND is_dir(ROOT_DIR . '/templates/' . $_REQUEST['dle_skin'])) {
        $config['skin'] = $_REQUEST['dle_skin'];
    } else {
        $_REQUEST['dle_skin'] = $_REQUEST['skin'] = $config['skin'];
    }
} elseif ($_COOKIE['dle_skin']) {
    $_COOKIE['dle_skin'] = trim(totranslit((string)$_COOKIE['dle_skin'], false, false));

    if ($_COOKIE['dle_skin'] AND is_dir(ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'])) {
        $config['skin'] = $_COOKIE['dle_skin'];
    }
}

define('TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin']);

if (file_exists(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['lang_' . $config['skin']] . '/website.lng'))) {
    include_once(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['lang_' . $config['skin']] . '/website.lng'));
} else {
    include_once(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));
}

if ($_GET['action'] == 'send') {
    if (ob_get_level() == 0) {
        ob_start();
    }

    ob_flush();
    flush();

    $id = intval($_GET['id']);
    if (!$id) {
        die('error');
    }

    $newsAction = strip_tags($_GET['news']);
    if (!$newsAction) {
        die('error');
    }

    $userId = intval($_GET['uid']);
    if (!$userId) {
        die('error');
    }

    $memberName = $db->safesql(strip_tags(stripslashes($_GET['author'])));
    if (!$memberName) {
        die('error');
    }

    include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
    $tpl = new dle_template();
    $tpl->dir = ROOT_DIR . '/templates/' . $config['skin'] . '/lazydev/dle_subscribe/';

    $parse = new ParseFilter();
    $newsQuery = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE id='{$id}'";

    if ($dleSubscribeConfig['options']['sendPm'] || $dleSubscribeConfig['options']['sendEmail']) {
        $pmTemplateNotifyConst = '';
        if ($dleSubscribeConfig['options']['sendPm']) {
            $sql_result = $db->query($newsQuery);
            $tpl->load_template('pm_notify.tpl');
            include(DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));
            $pmTemplateNotifyConst = $tpl->result['content'];
            $tpl->global_clear();
        }

        $emailTemplateNotifyConst = '';
        if ($dleSubscribeConfig['options']['sendEmail']) {
            $sql_result = $db->query($newsQuery);
            $tpl->load_template('email_notify.tpl');
            include (DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));
            $emailTemplateNotifyConst = $tpl->result['content'];
            $tpl->global_clear();

            include (DLEPlugins::Check(ENGINE_DIR . '/classes/mail.class.php'));
            $mail = new dle_mail($config, true);
        }

        $subscribeQuery = "SELECT s.*, p.id as postid, p.date, p.alt_name, p.category, p.title FROM  " . PREFIX . "_dle_subscribe s LEFT JOIN " . PREFIX . "_post p ON (s.page='news' AND p.id=s.pageValue) LEFT JOIN " . USERPREFIX . "_users u ON(s.userId=u.user_id)";
        if ($newsAction == 'doeditnews') {
            $subscribeQuery .= "WHERE page='news' AND pageValue='{$id}' GROUP BY email";
        } elseif ($newsAction == 'doaddnews') {
            $getNews = $db->super_query("SELECT category, tags, xfields FROM " . PREFIX . "_post WHERE id='{$id}'");
            $categoryChecked = explode(',', $getNews['category']);
            $blockUserGroup = [];
            $guestSubBlock = false;
            foreach ($user_group as $idGroup => $item) {
                $blockedCat = explode(',', $user_group[$idGroup]['not_allow_cats']);
                if ($blockedCat[0] != '') {
                    if (array_intersect($categoryChecked, $blockedCat)) {
                        if ($idGroup == 5) {
                            $guestSubBlock = true;
                        } else {
                            $blockUserGroup[] = $idGroup;
                        }
                    }
                }
            }
            $notGroup = '';
            if ($blockUserGroup) {
                if ($guestSubBlock) {
                    $notGroup = " AND u.user_group NOT IN ('" . implode("','", $blockUserGroup) . "') AND s.user!='__GUEST__'";
                } else {
                    $notGroup = " AND u.user_group NOT IN ('" . implode("','", $blockUserGroup) . "')";
                }
            }
            if (version_compare($db->mysql_version, '5.5.3', '<') ) {
                $subscribeQuery .= "WHERE ((page='user' AND pageValue='{$memberName}') OR (page='cat' AND '{$getNews['category']}' REGEXP CONCAT('[[:<:]]', pageValue, '[[:>:]]')) OR (page='tag' AND '{$getNews['tags']}' REGEXP CONCAT('[[:<:]]', pageValue, '[[:>:]]'))OR (page='xfield' AND '{$getNews['xfields']}' REGEXP CONCAT('[[:<:]]', REPLACE(pageValue, '/', '|'), '[[:>:]]')) OR (page='all')) {$notGroup} GROUP BY email";
            } else {
                $subscribeQuery .= "WHERE ((page='user' AND pageValue='{$memberName}') OR (page='cat' AND '{$getNews['category']}' REGEXP CONCAT('([[:punct:]]|^)(', pageValue, ')([[:punct:]]|$)')) OR (page='tag' AND '{$getNews['tags']}' REGEXP CONCAT('([[:punct:]]|^)(', pageValue, ')([[:punct:]]|$)'))OR (page='xfield' AND '{$getNews['xfields']}' REGEXP CONCAT('([[:punct:]]|^)(', REPLACE(pageValue, '/', '|'), ')([[:punct:]]|$)')) OR (page='all')) {$notGroup} GROUP BY email";
            }
        } else {
            exit;
        }
        $selectSubscribers = $db->query($subscribeQuery);

        while ($subscriber = $db->get_row($selectSubscribers)) {
            $urlUnsubscribe = $config['http_home_url'] . 'subscribe/decline/' . $subscriber['hash'] . '/';
            $subscriber['user'] = $subscriber['user'] == '__GUEST__' ? $dleSubscribeLang['admin']['subscribers']['__GUEST__'] : $subscriber['user'];
            if ($subscriber['page'] != 'all' && $subscriber['page'] != 'cat' && $subscriber['page'] != 'news') {
                $pageData = Helper::getUrl($subscriber['page'], $subscriber['helper']);
            } elseif ($subscriber['page'] == 'news') {
                $pageData = Helper::urlNews($subscriber);
            } elseif ($subscriber['page'] == 'cat') {
                $pageData = Helper::urlCat(['catid' => $subscriber['pageValue']]);
            }

            if (!$pageData[0]) {
                $pageData[0] = $config['http_home_url'];
                $pageData[1] = $config['short_title'];
            }
            if ($emailTemplateNotifyConst) {
                $emailTemplateNotify = $emailTemplateNotifyConst;
                $emailTemplateNotify = str_replace('{unsubscribe-url}', $urlUnsubscribe, $emailTemplateNotify);
                $emailTemplateNotify = str_replace('{subscriber-name}', $subscriber['user'], $emailTemplateNotify);

                $emailTemplateNotify = preg_replace_callback("#\\[page (value|page)(!?=)\"(.+?)\"\\](.+?)\\[/page\\]#is", function($matches) use($subscriber) {
                    $matches['value'] = $subscriber['pageValue'];
                    $matches['page'] = $subscriber['page'];
                    return Helper::checkIf($matches);
                }, $emailTemplateNotify);

                $emailTemplateNotify = str_replace('{page-url}', $pageData[0], $emailTemplateNotify);
                $emailTemplateNotify = str_replace('{page-name}', $pageData[1], $emailTemplateNotify);
                $emailTemplateNotify = str_replace('{template}', $config['http_home_url'] . 'templates/' . $config['skin'] . '/lazydev/dle_subscribe', $emailTemplateNotify);
                $emailTemplateNotify = str_replace('{site}', $config['http_home_url'], $emailTemplateNotify);
                preg_match("#\\[subject\\](.*?)\\[/subject\\]#is", $emailTemplateNotify, $emailSubject);
                $emailTemplateNotify = preg_replace("#\\[subject\\](.*?)\\[/subject\\]#is", '', $emailTemplateNotify);
                if (!$emailSubject[1]) {
                    $emailSubject[1] = $dleSubscribeLang['site']['emailSubject'] . $config['http_home_url'];
                }
                $emailSubject[1] = $db->safesql($emailSubject[1]);
                $mail->send($subscriber['email'], $emailSubject[1], $emailTemplateNotify);
            }

            if ($pmTemplateNotifyConst && $subscriber['userId'] > 0) {
                $pmTemplateNotify = $pmTemplateNotifyConst;
                $pmTemplateNotify = str_replace('{unsubscribe-url}', $urlUnsubscribe, $pmTemplateNotify);
                $pmTemplateNotify = str_replace('{subscriber-name}', $subscriber['user'], $pmTemplateNotify);

                $pmTemplateNotify = preg_replace_callback("#\\[page (value|page)(!?=)\"(.+?)\"\\](.+?)\\[/page\\]#is", function($matches) use($subscriber) {
                    $matches['value'] = $subscriber['pageValue'];
                    $matches['page'] = $subscriber['page'];
                    return Helper::checkIf($matches);
                }, $pmTemplateNotify);

                $pmTemplateNotify = str_replace('{page-url}', $pageData[0], $pmTemplateNotify);
                $pmTemplateNotify = str_replace('{page-name}', $pageData[1], $pmTemplateNotify);
                $pmTemplateNotify = str_replace('{template}', $config['http_home_url'] . 'templates/' . $config['skin'] . '/lazydev/dle_subscribe', $pmTemplateNotify);
                $pmTemplateNotify = str_replace('{site}', $config['http_home_url'], $pmTemplateNotify);
                preg_match("#\\[subject\\](.*?)\\[/subject\\]#is", $pmTemplateNotify, $pmSubject);
                $pmTemplateNotify = preg_replace("#\\[subject\\](.*?)\\[/subject\\]#is", '', $pmTemplateNotify);
                if (!$pmSubject[1]) {
                    $pmSubject[1] = $dleSubscribeLang['site']['pmSubject'];
                }
                $pmSubject[1] = $db->safesql($pmSubject[1]);


                if ($config['allow_comments_wysiwyg'] > 0) {
                    $parse->wysiwyg = true;
                    $pmTemplateNotify = $db->safesql($parse->BB_Parse($parse->process(trim($pmTemplateNotify))));
                } else {
                    if ($config['allow_comments_wysiwyg'] == -1) {
                        $parse->allowbbcodes = false;
                    }
                    $pmTemplateNotify = $db->safesql($parse->BB_Parse($parse->process(trim($pmTemplateNotify)), false));
                }
                $time = time();
                $db->query("INSERT INTO " . PREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, reply, sendid) VALUES ('{$pmSubject[1]}', '{$pmTemplateNotify}', '{$subscriber['userId']}', '{$memberName}', '{$time}', 0, 'inbox', 0, 0)");
                $db->query("UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$subscriber['userId']}'");
            }
        }
    }

    ob_end_flush();

}