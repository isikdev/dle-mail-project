<?php
/**
 * Создание новой группа
 * 
 * @package AdminPanel
 * @link http://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

// Сохранение настроек
if (isset($_POST['doadd'])) {
	$stop = [];

	$name = totranslit($_POST['pedit']['name']);
	if (!$name) {
		$stop[] = "Не указано имя группы";
	} elseif ($groups[$name]) {
		$stop[] = "Группа с таким именем уже существует";
	}

	$title = trim(strip_tags(stripslashes($_POST['pedit']['title'])));
	if (!$title) {
		$stop[] = "Не указан заголовок группы";
	}

	if ($stop) {
		ModAlert::setMsg($stop, 'error');
	} else {
		$groups[$name] = [
			'title' =>  $title
		];
		$miniposter->setGroups($groups);
		ModAlert::setMsg('Группа успешно создана', 'success');

		header('Location: ' . $PHP_SELF . '?mod=' . $mod . '&action=edit&group=' . $name);
		die('Redirect');
	}
}

// Для красоты добавляем пункт в меню
$menu_list[] = [
	'add',
	'Новая группа'
];

?>

<div class="pbox-header">
	Создание новой группы
</div>

<form method="post">
<table class="ptable">
<thead>
	<tr>
		<td width="50%">Параметр</td>
		<td width="50%">Значение</td>
	</tr>
</thead>
<tbody>
	<tr>
		<td>
			<div class="ptable-td-title">Имя группы</div>
			<div class="ptable-td-info">Уникальное имя на латинице</div>
		</td>
		<td>
			<input type="text" name="pedit[name]" class="pinput" value="" />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Название/описание группы</div>
			<div class="ptable-td-info">Чисто для себя, для удобства</div>
		</td>
		<td>
			<input type="text" name="pedit[title]" class="pinput" value="" />
		</td>
	</tr>
</tbody>
</table>
<button type="submit" name="doadd" style="margin-top: 15px;padding: 10px 30px;">Создать</button>
</form>