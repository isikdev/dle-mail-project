<?php
/**
* Админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../../');
	die('Hacking attempt!');
}

include realpath(__DIR__ . '/..') . '/loader.php';
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php'));
include_once(DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
$parse = new ParseFilter();

$jsAdminScript = [];
$additionalJsAdminScript = [];

$action = strip_tags($_GET['action']) ?: 'main';
$action = totranslit($action, true, false);

if ($action !== 'main') {
    $speedbar = '<li><i class="fa fa-home position-left"></i><a href="?mod=' . $modLName . '" style="color:#2c82c9">' . $dleSeoLang['admin']['main_speedbar'] . '</a></li>';
} else {
    $speedbar = '<li><i class="fa fa-home position-left"></i>' . $dleSeoLang['admin']['main_speedbar'] . '</li>';
}

if ($action == 'settings' || $action == 'sitemap') {
	$speedbar .= '<li>' . $dleSeoLang['admin'][$action . '_speedbar'] . '</li>';
}

if ($action == 'seo') {
    if ($_GET['add'] == 'yes') {
        $speedbar .= '<li><a href="?mod=' . $modLName . '&action=seo" style="color:#2c82c9">' . $dleSeoLang['admin'][$action . '_speedbar'] . '</a></li>';
        $speedbar .= '<li>' . ($_GET['id'] ? $dleSeoLang['admin'][$action . '_edit_speedbar'] : $dleSeoLang['admin'][$action . '_add_speedbar']) . ($_GET['type'] == 1 ? $dleSeoLang['admin'][$action . '_tag_speedbar'] : $dleSeoLang['admin'][$action . '_xf_speedbar']) . '</li>';
    } else {
        $speedbar .= '<li>' . $dleSeoLang['admin'][$action . '_speedbar'] . '</li>';
    }
} elseif ($action == 'info') {
    if ($_GET['info'] == 'add' || $_GET['info'] == 'edit') {
        $speedbar .= '<li><a href="?mod=' . $modLName . '&action=seo" style="color:#2c82c9">' . $dleSeoLang['admin']['seo_speedbar'] . '</a></li>';
    }
    if ($_GET['info'] == 'rule') {
        if ($_GET['from'] == 'news') {
            $speedbar .= '<li><a href="?mod=' . $modLName . '&action=news" style="color:#2c82c9">' . $dleSeoLang['admin']['news_speedbar'] . '</a></li>';
        } else {
            $speedbar .= '<li><a href="?mod=' . $modLName . '&action=cat" style="color:#2c82c9">' . $dleSeoLang['admin']['cat_speedbar'] . '</a></li>';
        }
    }
    $speedbar .= '<li>' . $dleSeoLang['admin'][$action . '_speedbar'] . '</li>';
} elseif ($action == 'news') {
    if ($_GET['add'] == 'yes') {
        $speedbar .= '<li><a href="?mod=' . $modLName . '&action=news" style="color:#2c82c9">' . $dleSeoLang['admin'][$action . '_speedbar'] . '</a></li>';
        $speedbar .= '<li>' . (isset($_GET['id']) ? $dleSeoLang['admin'][$action . '_edit_speedbar'] : $dleSeoLang['admin'][$action . '_add_speedbar']) . '</li>';
    } else {
        $speedbar .= '<li>' . $dleSeoLang['admin'][$action . '_speedbar'] . '</li>';
    }
} elseif ($action == 'cat') {
    if ($_GET['add'] == 'yes') {
        $speedbar .= '<li><a href="?mod=' . $modLName . '&action=cat" style="color:#2c82c9">' . $dleSeoLang['admin'][$action . '_speedbar'] . '</a></li>';
        $speedbar .= '<li>' . (isset($_GET['id']) ? $dleSeoLang['admin'][$action . '_edit_speedbar'] : $dleSeoLang['admin'][$action . '_add_speedbar']) . '</li>';
    } else {
        $speedbar .= '<li>' . $dleSeoLang['admin'][$action . '_speedbar'] . '</li>';
    }
}

include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/template/main.php';
if (file_exists(ENGINE_DIR . '/lazydev/' . $modLName . '/admin/' . $action . '.php')) {
    include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/' . $action . '.php';
}
include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/template/footer.php';

?>