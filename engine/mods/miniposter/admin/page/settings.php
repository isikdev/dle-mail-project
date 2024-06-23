<?php
/**
 * Общие настройки модуля
 * 
 * @package AdminPanel
 * @link http://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

if ($mod_config['force_jpg']) {
	unset($mod_config['force_jpg']);
	$mod_config['force_type'] = 'jpg';
}

if (isset($_POST['pedit'])) {
	$mod_config['enabled'] = isset($_POST['pedit']['enabled']) ? true : false;
	$mod_config['real_name'] = isset($_POST['pedit']['real_name']) ? true : false;
	$mod_config['allow_remote'] = isset($_POST['pedit']['allow_remote']) ? true : false;
	$mod_config['zoom'] = isset($_POST['pedit']['zoom']) ? true : false;
	$mod_config['ignore_gif'] = isset($_POST['pedit']['ignore_gif']) ? true : false;
	$mod_config['jpegoptim'] = isset($_POST['pedit']['jpegoptim']) ? true : false;
	$mod_config['optipng'] = isset($_POST['pedit']['optipng']) ? true : false;
	$mod_config['remote_rename'] = isset($_POST['pedit']['remote_rename']) ? true : false;
	$mod_config['count_calc'] = isset($_POST['pedit']['count_calc']) ? true : false;
	
	$mod_config['force_type'] = totranslit($_POST['pedit']['force_type']);
	if (!in_array($mod_config['force_type'], ['jpg', 'webp'])) {
		$mod_config['force_type'] = '';
	}

	$mod_config['folder_len'] = intval($_POST['pedit']['folder_len']);
	if ($mod_config['folder_len'] < 1) {
		$mod_config['folder_len'] = 0;
	} elseif ($mod_config['folder_len'] > 3) {
		$mod_config['folder_len'] = 3;
		ModAlert::setMsg('Не рекомендуется использовать большое число. 3 - это 4096 подпапок', 'warning');
	}

	$mod_config['timeout'] = intval($_POST['pedit']['timeout']);
	if ($mod_config['timeout'] < 1) {
		$mod_config['timeout'] = 1;
	} elseif ($mod_config['timeout'] > 10) {
		$mod_config['timeout'] = 10;
		ModAlert::setMsg('Не рекомендуется ставить ставить большое число. Среднее время нормального отклика 0-2 сек', 'warning');
	}

	$mod_config['max_width'] = intval($_POST['pedit']['max_width']);
	if ($mod_config['max_width'] < 1) {
		$mod_config['max_width'] = 0;
	}

	$mod_config['max_height'] = intval($_POST['pedit']['max_height']);
	if ($mod_config['max_height'] < 1) {
		$mod_config['max_height'] = 0;
	}

	$mod_config['quality'] = intval($_POST['pedit']['quality']);
	if ($mod_config['quality'] < 1) {
		$mod_config['quality'] = 0;
	} elseif ($mod_config['quality'] > 100) {
		$mod_config['quality'] = 100;
	}

	$default = trim($_POST['pedit']['default']);
	$default = str_replace('../', '', $default);
	if (file_exists(ROOT_DIR . $default)) {
		$info = getimagesize(ROOT_DIR . $default);
		if (stripos($info['mime'], 'image/') !== 0) {
			ModAlert::setMsg('Изображение по умолчанию - не является изображением', 'error');
		} else {
			$mod_config['default'] = $default;
		}
	} else {
		ModAlert::setMsg('Изображение по умолчанию - не найдено', 'error');
	}

	$save_path = trim($_POST['pedit']['save_path'], '/');
	$save_path = '/' . str_replace('../', '', $save_path);
	if (is_dir(ROOT_DIR . $save_path)) {
		if (!is_writable(ROOT_DIR . $save_path)) {
			ModAlert::setMsg("Папка хранения картинок $save_path должна иметь права для записи 755 или 777", 'warning');
		}
		$mod_config['save_path'] = $save_path;
	} else {
		ModAlert::setMsg('Папка хранения картинок не найдена или еще не создана', 'error');
	}
	
	foreach ($groups as $k => $v) {
		$miniposter->clearDir($k, false);
	}

	file_put_contents($config_path, "<?php\nreturn " . var_export($mod_config, true) . ";\n", LOCK_EX);
	ModAlert::setMsg('Настройки успешно сохранены', 'success');

	header('Location: '.$_SERVER['REQUEST_URI']);
	die('Redirect');

}

?>
<div class="pbox-header">
	Глобальные настройки
</div>

<form method="post">
<table class="ptable">
<thead>
	<tr>
		<td width="60%">Параметр</td>
		<td>Значение</td>
	</tr>
</thead>
<tbody>
	<tr>
		<td>
			<div class="ptable-td-title">Включить модуль</div>
			<div class="ptable-td-info">При выключении модуля все группы будут отключены не зависимо от их локальных настроек</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[enabled]" value="1" <?= $mod_config['enabled'] ? 'checked' : '' ?> /><span>Включить</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Путь хранения картинок</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<input type="text" name="pedit[save_path]" class="pinput" value="<?= $mod_config['save_path'] ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Изображение по умолчанию</div>
			<div class="ptable-td-info">На случай если нет доступа к картинке</div>
		</td>
		<td>
			<input type="text" name="pedit[default]" class="pinput" value="<?= $mod_config['default'] ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Оставить исходное имя картинки</div>
			<div class="ptable-td-info">
				<b>Примечание:</b> если будут 2 разные картинки с одинаковым именем, то есть мизерная вероятность, что произойдет их склейка<br/>
				Но если картинки загружены через стандартный загрузчик DLE, то 2 одинаковых имени невозможны в принципе.
			</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[real_name]" value="1" <?= $mod_config['real_name'] ? 'checked' : '' ?> /><span>Оставить родное имя</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Длина имени хеша подпапки</div>
			<div class="ptable-td-info">Данный параметр нужен для оптимизации работы файловой системы</div>
		</td>
		<td>
			<select class="pinput" name="pedit[folder_len]">
				<?= buildSelect([
					0 => '0 уровень (без папки), до 1000 картинок',
					1 => '1 уровень (16 папок), 500 - 15.000 картинок',
					2 => '2 уровень (256 папок), 10.000 - 250.000 картинок',
					3 => '3 уровень (4096 папок), 200.000 и более',
				], $mod_config['folder_len'], true) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Время ожидания, сек</div>
			<div class="ptable-td-info">Время ожидания отклика при создании картинки со стороннего сервера</div>
		</td>
		<td>
			<input type="number" name="pedit[timeout]" class="pinput" min="1" max="10" value="<?= $mod_config['timeout'] ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Максимальная ширина исходной картинки</div>
			<div class="ptable-td-info">Страховка на вский случай, чтобы не обрабатывались слишком большие изображения</div>
		</td>
		<td>
			<input type="number" name="pedit[max_width]" class="pinput" min="0" max="5000" value="<?= $mod_config['max_width'] ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Максимальная высота исходной картинки</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<input type="number" name="pedit[max_height]" class="pinput" min="0" max="3300" value="<?= $mod_config['max_height'] ?>" />
		</td>
	</tr>

	<tr>
		<td>
			<div class="ptable-td-title">Качество создаваемой JPEG картинки</div>
			<div class="ptable-td-info">Качество от 0 до 100. Оптимально 85.</div>
		</td>
		<td>
			<input type="number" name="pedit[quality]" class="pinput" min="0" max="100" value="<?= $mod_config['quality'] ?>" />
		</td>
	</tr>


	<tr>
		<td>
			<div class="ptable-td-title">Увеличивать маленькие картинки до заданного размера постера (не рекомендуется)</div>
			<div class="ptable-td-info">Если размер исходной картинки меньше размера постера, то она будет увеличена</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[zoom]" value="1" <?= $mod_config['zoom'] ? 'checked' : '' ?> /><span>Да, увеличивать</span></label>
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
				], $mod_config['force_type'], true) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Не обрабатывать GIF изображения (для сохранения анимации)</div>
			<div class="ptable-td-info">Данный параметр нужен, чтобы сохранить анимацию в гифках. Но при этом будут пропущены все GIF файлы</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[ignore_gif]" value="1" <?= $mod_config['ignore_gif'] ? 'checked' : '' ?> /><span>Да, оставить GIF</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Использовать утилиту jpegOptim для оптимизации JPG изображений</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[jpegoptim]" value="1" <?= $mod_config['jpegoptim'] ? 'checked' : '' ?> /><span>Да, оптимизировать JPG</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Использовать утилиту OptiPNG для оптимизации PNG изображений</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[optipng]" value="1" <?= $mod_config['optipng'] ? 'checked' : '' ?> /><span>Да, оптимизировать PNG</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Обрабатывать картинки со сторонних сайтов</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[allow_remote]" value="1" <?= $mod_config['allow_remote'] ? 'checked' : '' ?> /><span>Разрешить</span></label>
		</td>
	</tr>
	<tr>
		<td>
			<div class="ptable-td-title">Принудительно переименовывать картинки со сторонних сайтов</div>
			<div class="ptable-td-info"></div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[remote_rename]" value="1" <?= $mod_config['remote_rename'] ? 'checked' : '' ?> /><span>Переименовывать</span></label>
		</td>
	</tr>

	<tr>
		<td>
			<div class="ptable-td-title">Считать количество изображений и их размер</div>
			<div class="ptable-td-info">В некоторых случаях, когда изображений очень много - рекомендуется отключать данную опцию для ускорения работы админпанели</div>
		</td>
		<td>
			<label class="pcheck"><input type="checkbox" name="pedit[count_calc]" value="1" <?= $mod_config['count_calc'] ? 'checked' : '' ?> /><span>Вести подсчет</span></label>
		</td>
	</tr>


</tbody>
</table>

	<button style="margin-top:15px;padding:10px 25px;">Сохранить настройки</button>
</div>
</form>
