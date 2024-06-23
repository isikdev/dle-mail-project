<?php
/**
 * Установка/обновление модуля
 * 
 * @package AdminPanel
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

/**
 * Самовыпил файлов установщика
 * @return void
 */
function selfDestruct($folder)
{
	$dir = scandir($folder);
	foreach ($dir as $file) {
		if ($file != '.' && $file != '..') {
			if (is_dir($folder . '/' . $file)) {
				selfDestruct($folder . '/' . $file);
			} else {
				@unlink($folder . '/' . $file);
			}
		}
	}
	rmdir($folder);
	return is_dir($folder);
}

$row = $db->super_query("SELECT name, title FROM ".PREFIX."_admin_sections WHERE name = '$mod'");
if (!$row['name']) {
	// Устанавливаем модуль
	$db->query("INSERT INTO `".PREFIX."_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`)
		VALUES ('$mod', 'Miniposter PRO v.{$version}', 'Уменьшение изображений и их оптимизация', '{$mod}.png', '1')
	");
	ModAlert::setMsg('Модуль успешно установлен', 'info');
} else {
	// Проверяем версию
	preg_match("# v\.(.*)#i", $row['title'], $current_version);
	if (!$current_version[1] || version_compare($version, $current_version[1]) > 0) {
		//Поэтапно обновляем БД модуля
		
		$row['title'] = str_replace($current_version[0], ' v.' . $version, $row['title']);
		$row['title'] = $db->safesql($row['title']);
		$db->query("UPDATE `".PREFIX."_admin_sections` SET title = '{$row['title']}' WHERE name = '$mod'");
		ModAlert::setMsg('Модуль успешно обновлен', 'info');
	}
}

if (selfDestruct(__DIR__)) {
	ModAlert::setMsg("Обязательно удалите папку /engine/mods/$mod/admin/setup", 'warning');
}
