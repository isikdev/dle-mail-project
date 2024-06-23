<?php
/**
 * Активация модуля
 * 
 * @package AdminPanel
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

if (isset($_POST['lic_key'])) {
	if ($miniposter->setKey($_POST['lic_key'])){
		if (!is_writable(__DIR__.'/../../data/lic.key')) {
			ModAlert::setMsg('Нет доступа к файлу с лицензиями. Права на файл engine/mods/'.$mod.'/data/lic.key должны быть 644', 'warning');
		} else {
			ModAlert::setMsg('Модуль успешно активирован', 'success');
		}
	} else {
		ModAlert::setMsg('Введен ошибочный код активации', 'error');
	}
}

if ($miniposter->getLic()) {
	header('Location: ' . $PHP_SELF . '?mod=' . $mod);
	die();
}

$menu_list = [
	[
		'',
		'Активация модуля'
	]
];

?>

<form method="post">
	<div style="text-align: center;margin-bottom: 10px;">Домен для активации: <b><?= $_SERVER['SERVER_NAME'] ?></b></div>
	<input type="text" name="lic_key" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" style="height:35px;border: 1px solid #ddd;border-radius: 3px;padding: 0 15px;display:block;width: 400px;text-align:center;font-size:1.2em;margin: 0 auto;" />
	<button style="display:block;width:400px;margin: 15px auto 0;height:35px;">Активировать</button>
</form>