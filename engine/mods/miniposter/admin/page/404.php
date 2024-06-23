<?php
header('HTTP/1.1 404 Not Found');
header("Status: 404 Not Found");

if ($action == 'edit') {
	$msg = "Судя по всему группа <b>$group</b> была удалена кем-то до меня";
} else {
	$msg = "Я... я не понимаю как подобное могло произойти.<br/>Видимо что-то пошло не так";
}

?>
<div class="p404">
	Уупс, страница не найдена
	<div class="p404_fun"><?= $msg ?></div>
</div>