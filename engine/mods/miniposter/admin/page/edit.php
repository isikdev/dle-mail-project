<?php
/**
 * Редактирование
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

if ($groups[$group]['force_jpg']) {
	unset($groups[$group]['force_jpg']);
	$groups[$group]['force_type'] = 'jpg';
}

// Сохранение настроек
if (isset($_POST['pedit'])) {
	$stop = [];
	$name = totranslit($_POST['pedit']['name']);
	if (!$name) {
		$stop[] = "Не указано имя группы";
	} elseif ($groups[$name] && $name != $group) {
		$stop[] = "Группа с таким именем уже существует";
	}

	$groups[$group]['enabled'] = isset($_POST['pedit']['enabled']) ? true : false;

	$groups[$group]['title'] = trim(strip_tags(stripslashes($_POST['pedit']['title'])));
	if (!$groups[$group]['title']) {
		$stop[] = "Не указан заголовок группы";
	}
	$groups[$group]['width'] = intval($_POST['pedit']['width']);
	if ($groups[$group]['width'] < 1) {
		unset($groups[$group]['width']);
	}

	$groups[$group]['height'] = intval($_POST['pedit']['height']);
	if ($groups[$group]['height'] < 1) {
		unset($groups[$group]['height']);
	}
	if ($_POST['pedit']['quality'] != '') {
		$groups[$group]['quality'] = intval($_POST['pedit']['quality']);
		if ($groups[$group]['quality'] < 1) {
			$groups[$group]['quality'] = 0;
		} elseif ($groups[$group]['quality'] > 100) {
			$groups[$group]['quality'] = 100;
		}
	} else {
		unset($groups[$group]['quality']);
	}
	if ($_POST['pedit']['folder_len'] != '') {
		$groups[$group]['folder_len'] = intval($_POST['pedit']['folder_len']);
	} else {
		unset($groups[$group]['folder_len']);
	}
	if ($_POST['pedit']['enabled'] != '') {
		$groups[$group]['enabled'] = intval($_POST['pedit']['enabled']);
	} else {
		unset($groups[$group]['enabled']);
	}
	if ($_POST['pedit']['real_name'] != '') {
		$groups[$group]['real_name'] = intval($_POST['pedit']['real_name']);
	} else {
		unset($groups[$group]['real_name']);
	}
	if ($_POST['pedit']['zoom'] != '') {
		$groups[$group]['zoom'] = intval($_POST['pedit']['zoom']);
	} else {
		unset($groups[$group]['zoom']);
	}

	$groups[$group]['force_type'] = totranslit($_POST['pedit']['force_type']);
	if (!in_array($groups[$group]['force_type'], ['jpg','webp'])) {
		unset($groups[$group]['force_type']);
	}

	if ($_POST['pedit']['ignore_gif'] != '') {
		$groups[$group]['ignore_gif'] = intval($_POST['pedit']['ignore_gif']);
	} else {
		unset($groups[$group]['ignore_gif']);
	}
	if ($_POST['pedit']['default'] != '') {
		$default = '/' . trim($_POST['pedit']['default'], '/');
		$default = str_replace('../', '', $default);
		if (file_exists(ROOT_DIR . $default)) {
			$info = getimagesize(ROOT_DIR . $default);
			if (stripos($info['mime'], 'image/') !== 0) {
				ModAlert::setMsg('Изображение по умолчанию - не является изображением', 'error');
			} else {
				$groups[$group]['default'] = $default;
			}
		} else {
			ModAlert::setMsg('Изображение по умолчанию - не найдено', 'error');
		}
	} else {
		unset($groups[$group]['default']);
	}

	if ($stop) {
		ModAlert::setMsg($stop, 'error');
	} else {
		$miniposter->clearDir($group);
		ModAlert::setMsg('Кеш картинок очищен', 'info');

		if ($name != $group) {
			$groups[$name] = $groups[$group];
			unset($groups[$group]);
		}

		$miniposter->setGroups($groups);
		ModAlert::setMsg('Настройки успешно сохранены', 'success');

		header('Location: '.$_SERVER['REQUEST_URI']);
		die('Redirect');
	}
}

// Для красоты добавляем пункт в меню
$menu_list[] = [
	'edit',
	'Группа: ' . $group,
	'&group=' . $group
];

$folder_info = [
	'size'  => 0,
	'count' => 0
];
$miniposter->getFolderSize($group, $folder_info);

$groups[$group]['title'] = htmlspecialchars($groups[$group]['title'], ENT_QUOTES, $config['charset']);
?>

<div class="pbox-header">
	<a href="<?= $PHP_SELF . '?mod=' . $mod . '&action=delete&group=' . $group ?>" class="pbox-header-action" style="background:#c00">Удалить</a>
	<?= $groups[$group]['title'] ?>, <?= $groups[$group]['width'] ?: '0' ?> x <?= $groups[$group]['height'] ?: '0' ?> px
</div>

<form method="post">

<div style="line-height: 150%;margin-bottom: 20px;padding:7px 10px;border: 1px solid #ddd;background: #fafafa;border-radius: 2px;">
	Размер кеша: <b><?= formatsize($folder_info['size']) ?></b><br/>
	Создано картинок: <b><?= $folder_info['count'] ?></b> шт.
</div>

<table class="ptable">
<thead>
	<tr>
		<td width="50%">Параметр</td>
		<td width="25%">Значение</td>
		<td width="25%">По умолчанию</td>
	</tr>
</thead>
<tbody>
	<tr>
		<td>
			<div class="ptable-td-title">Включить группу</div>
			<div class="ptable-td-info">[miniposter=<?= $group ?>] ... [/miniposter]</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[enabled]" value="1" <?= $groups[$group]['enabled'] ? 'checked' : '' ?> /><span>Включить</span></label>
		</td>
		<td>&mdash;</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Имя группы</div>
			<div class="ptable-td-info">Уникальное имя на латинице</div>
		</td>
		<td>
			<input type="text" name="pedit[name]" class="pinput" value="<?= $group ?>" />
		</td>
		<td>&mdash;</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Название/описание группы</div>
			<div class="ptable-td-info">Чисто для себя, для удобства</div>
		</td>
		<td>
			<input type="text" name="pedit[title]" class="pinput" value="<?= $groups[$group]['title'] ?>" />
		</td>
		<td>&mdash;</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Ширина постера, px</div>
			<div class="ptable-td-info">Отсавить поле пустым или поставить 0, чтобы ширина бралась автоматически</div>
		</td>
		<td>
			<input type="number" name="pedit[width]" class="pinput" min="0" value="<?= $groups[$group]['width'] ?>" />
		</td>
		<td>&mdash;</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Высота постера, px</div>
			<div class="ptable-td-info">
				Отсавить поле пустым или поставить 0, чтобы высота бралась автоматически<br/>
				<b style="color:#e00;">Важно:</b> если не указывать размеры вовсе, то изображение будет просто оптимизировано и сохранено в исходном размере
			</div>
		</td>
		<td>
			<input type="number" name="pedit[height]" class="pinput" min="0" value="<?= $groups[$group]['height'] ?>" />
		</td>
		<td>&mdash;</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Качество</div>
			<div class="ptable-td-info">Качество создаваемой картинки. От 0 до 100. Оптимально 85.</div>
		</td>
		<td>
			<input type="number" name="pedit[quality]" class="pinput" min="0" max="100" value="<?= $groups[$group]['quality'] ?>" />
		</td>
		<td>
			<input type="text" class="pinput" value="<?= $mod_config['quality'] ?>" disabled />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Изображение по умолчанию</div>
		</td>
		<td>
			<input type="text" name="pedit[default]" class="pinput" value="<?= $groups[$group]['default'] ?>" />
		</td>
		<td>
			<input type="text" class="pinput" value="<?= $mod_config['default'] ?>" disabled />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Длина префикса подпапки</div>
			<div class="ptable-td-info">Данный параметр нужен для оптимизации работы файловой системы</div>
		</td>
		<td>
			<select class="pinput" name="pedit[folder_len]">
				<?= buildSelect([
					0 => '0 уровень (без папки), до 1000 картинок',
					1 => '1 уровень (16 папок), 500 - 15.000 картинок',
					2 => '2 уровень (256 папок), 10.000 - 250.000 картинок',
					3 => '3 уровень (4096 папок), 200.000 и более',
				], $groups[$group]['folder_len'], true) ?>
			</select>
		</td>
		<td>
			<?= $mod_config['folder_len'] ?>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Оставить исходное имя картинки</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<select class="pinput" name="pedit[real_name]">
				<?= buildSelect([
					1 => 'Да, сохранить имя файла',
					0 => 'Нет, присвоить новое хеш имя',
				], $groups[$group]['real_name'], true) ?>
			</select>
		</td>
		<td>
			<?= $mod_config['real_name'] ? 'Да' : 'Нет' ?>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Увеличивать маленькие картинки (не рекомендуется)</div>
			<div class="ptable-td-info">Если размер картинки меньше постера, то она будет увеличена до заданного размера</div>
		</td>
		<td>
			<select class="pinput" name="pedit[zoom]">
				<?= buildSelect([
					1 => 'Да, увеличивать',
					0 => 'Нет, пусть будет маленькая',
				], $groups[$group]['zoom'], true) ?>
			</select>
		</td>
		<td>
			<?= $mod_config['zoom'] ? 'Да' : 'Нет' ?>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Принудительно сохранять в заданном формате</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<select class="pinput" name="pedit[force_type]">
				<?= buildSelect([
					'jpg' => 'JPEG',
					'webp' => 'WebP',
				], $groups[$group]['force_type'], true) ?>
			</select>
		</td>
		<td>
			<?= $mod_config['force_type'] ?: '&mdash;' ?>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Не обрабатывать GIF изображения (сохранить анимацию)</div>
			<div class="ptable-td-info">Данный параметр нужен, чтобы сохранить анимацию в гифках. Но при этом будут пропущены все GIF файлы</div>
		</td>
		<td>
			<select class="pinput" name="pedit[ignore_gif]">
				<?= buildSelect([
					1 => 'Да, оставить GIF с анимацией',
					0 => 'Нет, обрабатывать о общем порядке',
				], $groups[$group]['ignore_gif'], true) ?>
			</select>
		</td>
		<td>
			<?= $mod_config['ignore_gif'] ? 'Да' : 'Нет' ?>
		</td>
	</tr>
</tbody>
</table>

	<button type="submit" style="margin-top: 15px;padding: 10px 30px;">Сохранить</button>

</form>