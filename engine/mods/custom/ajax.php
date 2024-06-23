<?php

use Sandev\AjaxCustom;

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', true);
ini_set('html_errors', false);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__, 3));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include_once __DIR__ . '/loader.php';
header("Content-type: text/html; charset=" . $config['charset']);
date_default_timezone_set($config['date_adjust']);

require_once DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php');
dle_session();

$_POST['skin'] = totranslit($_POST['skin'], false, false);
if ($_POST['skin'] == "" || !@is_dir(ROOT_DIR . '/templates/' . $_POST['skin'])) {
	$_POST['skin'] = $config['skin'];
}
if ($config["lang_" . $_POST['skin']]) {
	if (file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_POST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_POST['skin']] . '/website.lng');
	} else {
		die("Language file not found");
	}
} else {
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';
}
setlocale(LC_NUMERIC, "C");

$_TIME = time();

$cat_info = get_vars( "category" );
if (!is_array($cat_info)) {
	$cat_info = array();
	$db->query("SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC");
	while ($row = $db->get_row()) {
		$cat_info[$row['id']] = array_map('stripslashes', $row);
	}
	set_vars("category", $cat_info);
	$db->free();
}

$user_group = get_vars("usergroup");
if (!$user_group) {
	$user_group = [];
	$db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");
	while ($row = $db->get_row()) {
		$user_group[$row['id']] = array_map('stripslashes', $row);
	}
	set_vars("usergroup", $user_group);
	$db->free();
}

require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php'));
require_once DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php');
$is_logged || $member_id = ['user_group' => 5];

$tpl = new dle_template;
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
define('TEMPLATE_DIR', $tpl->dir);

$banner_in_news = $banners = [];

$action = totranslit($_POST['action']);
$name   = totranslit($_POST['name']);

if ($action == 'init') {
	//Инициализация и отображение блока по eventLoad или lazyLoad
	$content = AjaxCustom::getPageInitContent($name);
} elseif ($action == 'getpage') {
	//Постраничная навигация
	stripos($name, 'cm__') === 0 && AjaxCustom::loadCacheData($name);	//Костыль для шаблонного использования навигации
	$content = AjaxCustom::getPageContent($name, (int)$_POST['cstart']);
} else {
	$content = 'Undefined action';
}

if (file_exists(ENGINE_DIR . '/mods/miniposter/loader.php')) {
	require_once ENGINE_DIR . '/mods/miniposter/loader.php';
	(new Miniposter)->build($content);
}

$content = str_ireplace('{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $content);

echo $content;
