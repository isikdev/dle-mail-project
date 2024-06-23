<?php
/**
 * AJAX обработчик
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Seo\Helper;

@error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -34));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include_once ENGINE_DIR . '/lazydev/dle_seo/loader.php';

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

if ($_COOKIE['dle_skin']) {
    $_COOKIE['dle_skin'] = trim(totranslit((string)$_COOKIE['dle_skin'], false, false));

    if ($_COOKIE['dle_skin'] && is_dir(ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'])) {
        $config['skin'] = $_COOKIE['dle_skin'];
    }
}

define('TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin']);

if (file_exists(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['lang_' . $config['skin']] . '/website.lng'))) {
    include_once(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['lang_' . $config['skin']] . '/website.lng'));
} else {
    include_once(DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));
}

$is_logged = false;
require_once DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php');
if (!$is_logged) {
    $member_id['user_group'] = 5;
}

if ($_GET['dle_hash'] != $dle_login_hash) {
    echo Helper::json(['text' => $dleSeoLang['admin']['ajax']['error'], 'error' => 'true']);
    exit;
}

if (preg_match("/[\||\<|\>|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_GET['query']) || !$_GET['query']) {
    exit;
}

if ($_GET['mode'] == 'xfield') {
    $query = $db->safesql(htmlspecialchars(trim($_GET['query']), ENT_QUOTES, $config['charset']));
    $xfName = $db->safesql(htmlspecialchars(trim($_GET['xf']), ENT_QUOTES, $config['charset']));

    $db->query("SELECT id, tagvalue as name, COUNT(*) AS count FROM " . PREFIX . "_xfsearch WHERE `tagname`='{$xfName}' AND `tagvalue` LIKE '{$query}%' GROUP BY tagvalue ORDER BY count DESC LIMIT 15");
} else {
    $query = $db->safesql(htmlspecialchars(strip_tags(stripslashes(trim($_GET['query']))), ENT_QUOTES, $config['charset']));
    $db->query("SELECT id, tag as name, COUNT(*) AS count FROM " . PREFIX . "_tags WHERE `tag` LIKE '{$query}%' GROUP BY tag ORDER BY count DESC LIMIT 15");
}


$search = [];

while ($row = $db->get_row()) {
    $row['name'] = str_replace("&quot;", '"', $row['name']);
    $row['name'] = str_replace("&#039;", "'", $row['name']);

    $search[] = ['value' => str_replace("'", "&#039;", $row['name']), 'name' => $row['name']];
}

echo json_encode($search);
