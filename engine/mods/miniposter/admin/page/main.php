<?php
/**
 * Контент главной страницы
 * 
 * @package AdminPanel
 * @link http://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

if (isset($_POST['ajax'])) {
	$area = totranslit($_POST['area']);
	if (!$groups[$group]) {
		die("{\"error\":\"Группа $group не найдена или была удалена\"}");
	}
	switch ($area) {
		case 'status':
			$groups[$group]['enabled'] = !$groups[$group]['enabled'];
			$miniposter->setGroups($groups);
			if (!$groups[$group]['enabled']) {
				$miniposter->clearDir($group);
			}
			echo "{\"ok\":1}";
			break;

		case 'clear':
			$miniposter->clearDir($group);
			echo "{\"info\":\"Кеш успешно очищен\"}";
			break;

		default:
			echo "{\"error\": \"Действие не определено\"}";
	}
	die();
}

?>

<div class="pbox-header">
	<a href="<?= $PHP_SELF . '?mod=' . $mod . '&action=add' ?>" class="pbox-header-action">Создать новую группу</a>
	Группы минипостеров
</div>

<table class="ptable">
<thead>
	<tr>
		<td width="30px">Вкл</td>
		<td>Описание поля</td>
		<td width="300px">Название / папка</td>
		<td width="200px">Данные</td>
		<td width="120px">Ширина, px</td>
		<td width="120px">Высота, px</td>
		<td align="right" width="160px">Действие</td>
	</tr>
</thead>
<tbody>
<?php
foreach ($groups as $k => $v) {
	$status = $v['enabled'] ? 'on' : 'off';
	$v['folder_len'] = isset($v['folder_len']) ? $v['folder_len'] : $mod_config['folder_len'];

	if ($mod_config['count_calc']) {
		$folder_info = [
			'size'  => 0,
			'count' => 0,
		];
		$miniposter->getFolderSize($k, $folder_info);
		$folder_info['avg']  = $folder_info['count'] ? formatsize($folder_info['size'] / $folder_info['count']) : '&mdash;';
		$folder_info['size'] = formatsize($folder_info['size']);
	} else {
		$folder_info = [
			'size'  => '&mdash;',
			'count' => '&mdash;',
			'avg' => '&mdash;',
		];
	}
	$edit_link = $PHP_SELF . '?mod=' . $mod . '&action=edit&group=' . $k;

?>
	<tr>
		<td>
			<a href="#" data-mod_status="<?= $k ?>" class="ptable-status ptable-status-<?= $status ?>" title="Включить/выключить группу"></a>
		<td>
			<div class="ptable-td-title"><a href="<?= $edit_link ?>"><?= $v['title'] ?></a></div>
			<div class="ptable-td-info">[miniposter=<?= $k ?>] ... [/miniposter]</div>
		</td>
		<td>
			<div class="ptable-td-title"><?= $k ?></div>
			<div class="ptable-td-info"><?= $mod_config['save_path'] ?>/<?= $k ?>/<?= $v['folder_len'] ? str_repeat('x', $v['folder_len']) . '/' : '' ?>картинка.jpg</div>
		</td>
		<td style="font-size: .9em;">
			Картинок: <b><?= $folder_info['count'] ?></b> шт.<br/>
			Папка: <b><?= $folder_info['size'] ?></b><br/>
			Средний размер: <b><?= $folder_info['avg'] ?></b>
		</td>
		<td>
			<?= $v['width'] ?: '&mdash;' ?>
		</td>
		<td>
			<?= $v['height'] ?: '&mdash;' ?>
		</td>
		<td>
			<a href="<?= $PHP_SELF . '?mod=' . $mod . '&action=delete&group=' . $k ?>" class="ptable-action ptable-action-delete" title="Удалить группу"></a>
			<a href="<?= $edit_link ?>" class="ptable-action ptable-action-edit" title="Редактировать"></a>
			<a href="#" data-mod_clear="<?= $k ?>" class="ptable-action ptable-action-clear" title="Очистить кеш"></a>
		</td>
	</tr>
<?php } ?>
</tbody>
</table>