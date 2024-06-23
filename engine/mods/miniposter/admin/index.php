<?php
/**
 * Miniposter main class
 * 
 * @package Miniposter
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 * @version 3.0 (02.01.2018)
 */

if ($member_id['user_group'] != 1) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

// Current mod version
$version = '3.4.4';

require_once __DIR__ . '/Class/ModAlert/ModAlert.php';
if (file_exists(__DIR__ . '/setup/index.php')) {
	include_once __DIR__ . '/setup/index.php';
}

$data_files = [
	'/engine/mods/miniposter/data/group.dat',
	'/engine/mods/miniposter/data/lic.key',
	'/engine/mods/miniposter/data/config.php',
];
foreach ($data_files as $file_path) {
	if (!is_writable(ROOT_DIR . $file_path)) {
		ModAlert::setMsg('Файл ' . $file_path . ' не доступен для записи', 'warning');
	}
}

// Заголовки меню и action адрес страницы
$menu_list = [
	[
		'',
		'Главная',
	],
	[
		'settings',   //URL action
		'Настройки',  //заголовок
		'',           //параметры
		'pright',     //class=''
	]
];

$config_path = __DIR__ . '/../data/config.php';
$mod_config = include $config_path;
require_once __DIR__ . '/../loader.php';
$miniposter = new Miniposter();

function buildSelect($list, $value, $none = false)
{
	$select = '';
	if ($none) {
		$select = '<option value="">По умолчанию</option>';
	}
	foreach ($list as $k => $v) {
		$select .= '<option value="' . $k . '"' . ($k===$value ? ' selected' : '') . '>' . $v . '</option>';
	}
	return $select;
}

$groups = $miniposter->getGroups();
if (!$miniposter->getLic()) {
	$action = 'activation';
}

// Имя поля
$group = isset($_REQUEST['group']) ? totranslit($_REQUEST['group'], true, false) : false;

// Определение файла страницы
$page_file = $action ?: 'main';
$file_path = __DIR__ . '/page/' . $page_file . '.php';
if (!file_exists($file_path)) $file_path = __DIR__ . '/page/404.php';

// Получаем контент страницы
ob_start();
include_once $file_path;
$content = ob_get_clean();

echoheader('<i class="icon-filter fa fa-filter position-left"></i> Минипостер PRO v.' . $version, 'Управление блоками уменьшенных изображений. Их настройка и оптимизация');

// Список табов
$menu_li = '';
foreach ($menu_list as $v) {
	$link = $PHP_SELF . '?mod=' . $mod;
	if ($v[0]) {
		$link .= '&action=' . urlencode($v[0]);
	}
	if ($v[2]) {
		$link .= $v[2];
	}
	$class = $v[0] == $action ? 'current' : '';
	$menu_li .= "<li class=\"{$v[3]}\"><a href=\"$link\" class=\"$class\">{$v[1]}</a></li>";
}


echo <<<HTML
<link rel="stylesheet" type="text/css" href="/engine/mods/$mod/admin/assets/style.css" />
<script src="/engine/mods/$mod/admin/assets/libs.js"></script>

<div class="pbox">
	<ul class="pbox-menu">
		$menu_li
	</ul>
	<div class="pbox-content">
		$content
	</div>
	<a href="//sandev.pro/" class="pbox-logo" target="_blank" title="Это я :)">Powered by Sander-Development</a>
</div>
HTML;

echo ModAlert::getList();

echofooter();
