<?php
/**
 * Удаление группы
 * 
 * @package AdminPanel
 * @link http://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

// Имя поля не задано, goto главную
if (!$group) {
	header('Location: ' . $PHP_SELF . '?mod=' . $mod);
	die();
}

// Такого поля нет или оно было удалено
if (!$groups[$group]) {
	return include __DIR__ . "/404.php";
}

if (isset($_POST['dodelete'])) {
	unset($groups[$group]);
	$miniposter->clearDir($group);
	$miniposter->setGroups($groups);
	ModAlert::setMsg('Группа успешно удалена', 'success');
	header('Location: ' . $PHP_SELF . '?mod=' . $mod);
	die('Redirect');
}

$menu_list[] = [
	'delete',
	'Удалить: ' . $group,
	'&group=' . $group
];

?>
<form method="post" style="margin: 0 -25px -25px; text-align: center;line-height: 150%;">
	<div style="font-weight: bold;font-size: 1.15em;display: block;">Точно удалить?</div>
	Вы точно уверены что хотите удалить эту группу минипостеров и все созданные картинки?
	<div style="background:#f3f3f3;padding: 15px;border-top: 1px solid #ddd;margin-top:25px;">
		<button name="dodelete" style="padding: 7px 30px;">Да, удалить</button>
		<button onclick="history.go(-1);return false;" style="padding: 7px 30px;">Я передумал</button>
	</div>
</form>