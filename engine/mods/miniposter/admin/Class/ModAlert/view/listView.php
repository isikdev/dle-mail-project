<?php
// Русификация заголовков
self::$message_group['success'] = 'Успешно';
self::$message_group['error'] = 'Ошибка';
self::$message_group['warning'] = 'Внимание!';
self::$message_group['info'] = 'Информация';
?>

<div class="modAlert">
<?php
foreach ($_SESSION['mod_info'] as $info) {
?>
<div class="modAlert-item">
	<div class="modAlert-item-div modAlert-<?= $info['type'] ?>">
		<div class="modAlert-title"><?= self::$message_group[$info['type']] ?></div>
		<div class="modAlert-text"><?= $info['message'] ?></div>
		<div class="modAlert-icon"></div>
		<a href="#" class="modAlert-close" title="Close tooltip"></a>
	</div>
</div>
<?php } ?>
</div>
