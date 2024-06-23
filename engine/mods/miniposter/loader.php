<?php
/**
 * Подключение и инициализация минипостера
 * 
 * @package Miniposter
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

$php_version = str_replace(array(".",","),"",substr(PHP_VERSION,0,3));

$path = __DIR__ . '/Class/Miniposter_pro.' . $php_version . '.php';
if (file_exists($path)) {
	require_once $path;
}

if (!class_exists('Miniposter_pro')) {
	class Miniposter_pro
	{
		public static $instance = null;
		public static $group = [];
		function getPoster($a, $b){}
		function getGroups(){}
		function errorLoad(){
			global $path, $php_version;
			$path = str_replace(ROOT_DIR, '', $path);
			$msg = "Не найден файл <b>$path</b>";
			if (file_exists(ROOT_DIR . '/' . $path)) {
				$msg = "Не удалось обработать файл <b>$path</b>.<br/>Проверьте, установлен ли <b>ionCube Loader</b>, при необходимости обновите его до актуальной версии";
				if ($php_version == 70) {
					$msg .= "<br/>У вас <b>PHP 7.x</b>, попробуйте переименовать файл <b>Miniposter_pro.7x.php</b> в <b>Miniposter_pro.70.php</b>";
				}
			} elseif ($php_version <= 53) {
				$msg .= "<br/>У вас используется не поддерживаемая версия PHP. Должна быть <b>PHP 5.4</b> или старше";
			}
			msg('error', 'Ошибка', $msg);
		}
		function getLic(){}
	}
}

require_once __DIR__ . '/Class/Miniposter.php';
