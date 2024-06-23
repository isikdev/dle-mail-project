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

$jsAdminScript = [];
$additionalJsAdminScript = [];
$action = strip_tags($_GET['action']) ?: 'main';
$action = totranslit($action, true, false);

$speedbar = '<li><i class="fa fa-home position-left"></i><a href="?mod=' . $modLName . '" style="color:#2c82c9">' . $dleSubscribeLang['admin']['speedbar_main'] . '</a></li>';
if ($action !== 'main') {
    $speedbar .= '<li>' . $dleSubscribeLang['admin']['speedbar_' . $action] . '</li>';
}

if (intval($_GET['export']) !== 1) {
    include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/template/main.php';
}

if (file_exists(ENGINE_DIR . '/lazydev/' . $modLName . '/admin/' . $action . '.php')) {
    include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/' . $action . '.php';
}

if (intval($_GET['export']) !== 1) {
    include ENGINE_DIR . '/lazydev/' . $modLName . '/admin/template/footer.php';
}

?>