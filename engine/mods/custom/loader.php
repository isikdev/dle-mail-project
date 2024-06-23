<?php

use Sandev\AjaxCustom;

defined('DATALIFEENGINE') || die('Later, maybe');

if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
	include_once ENGINE_DIR . '/classes/plugins.class.php';
} elseif (!is_array($config)) {

	@ini_set('pcre.recursion_limit', 10000000 );
	@ini_set('pcre.backtrack_limit', 10000000 );
	@ini_set('pcre.jit', false);

	@include_once (ENGINE_DIR . '/data/config.php');
	require_once (ENGINE_DIR . '/classes/mysql.php');
	require_once (ENGINE_DIR . '/data/dbconfig.php');

	abstract class DLEPlugins {
		public static function Check($source = '') {
			return $source;
		}
	}
}

include_once __DIR__ . '/class/AjaxCustom.php';
include_once __DIR__ . '/class/Pagination.php';

$php_version = str_replace(['.', ','], '', substr(PHP_VERSION, 0, 3));
$lic_path = __DIR__ . sprintf('/class/lic/%d.php', $php_version);
$mod_error = '';
if (file_exists($lic_path)) {
	ob_start();
	include_once $lic_path;
	ob_get_clean() && $error = 'ionCube Loader not installed';
} else {
	$error = 'PHP Version not supported';
}
if ($error) include_once __DIR__ . '/class/lic/Blocks.php';

$error == '' || msgbox('Ajax-Custom', $error);

AjaxCustom::init();

$js_array[] = 'templates/' . $config['skin'] . '/custom/assets/libs.js';
