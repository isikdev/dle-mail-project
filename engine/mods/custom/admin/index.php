<?php

use Sandev\AdminController;
use Sandev\AjaxCustom\Blocks;

defined('DATALIFEENGINE') || die('Denied');
$member_id['user_group'] == 1 || msg("error", $lang['index_denied'], $lang['index_denied']);

define('TEMPLATE_DIR', ROOT_DIR . '/templates/' . totranslit($config['skin'], false, false));
define('MOD_DIR', dirname(__DIR__));
mb_internal_encoding($config['charset']);
setlocale(LC_NUMERIC, "C");

$version = '1.3.7';

$mod_config = include_once MOD_DIR . '/data/config.php';
if ($mod_config === false) {
	$mod_config = [
		'nav_type' => 'main',
		'limit' => 10,
		'max_page' => 0,
		'lazyLoad' => false,
		'eventLoad' => false,
		'cache' => true,
		'cookies'	=> true,
	];
	file_put_contents(MOD_DIR . '/data/config.php', '<?php return ' . var_export($mod_config, true) . ';', LOCK_EX);
}


$mod_lang_path = MOD_DIR . '/lang/' . $config['langs'] . '.lng';
file_exists($mod_lang_path) || $mod_lang_path = MOD_DIR . '/lang/Russian.lng';
$mod_lang = include_once $mod_lang_path;

$php_version = str_replace(['.', ','], '', substr(PHP_VERSION, 0, 3));
$lic_path = MOD_DIR . sprintf('/class/lic/%d.php', $php_version);
$mod_error = '';
if (file_exists($lic_path)) {
	ob_start();
	include_once $lic_path;
	ob_get_clean() && $error = $mod_lang['error']['ionCube'];
} else {
	$error = $mod_lang['error']['php_version'];
}
$error == '' || msg("error", $error, 'PHP Version: ' . PHP_VERSION);

$data = new Blocks(MOD_DIR, false);


if (isset($_POST['ajax'])) {

	include_once MOD_DIR . '/class/AdminController.php';
	$ajax = new AdminController($mod_config, $data);
	
	$ajax->setTypeHint('bool', ['lazyLoad', 'eventLoad', 'cache', 'cookies']);
	$ajax->setTypeHint('int', ['limit', 'max_page']);
	$ajax->setTypeHint('nav', ['main', 'pages', 'next', 'more', 'lazy', 'none']);

	$response = $ajax->getResponse();
	if ($error = $ajax->getError()) {
		http_response_code(403);
		echo $mod_lang['error'][$error] ?: $error;
	} else {
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	}

	die();
}

$cache_size = file_exists(MOD_DIR . '/data/cache.dat') ? filesize(MOD_DIR . '/data/cache.dat') : 0;

$mod_name = $db->super_query(sprintf('SELECT title FROM %s_admin_sections WHERE name = "%s"', PREFIX, $db->safesql($mod)));
$mod_name['title'] .= ' v.' . $version;
list($speed_title,) = explode(' by Sander', $mod_name['title']);
echoheader($mod_name['title'], $speed_title);

?>
<script>
var mod_alert = <?= $_SESSION['mod_alert'] ? json_encode($_SESSION['mod_alert']) : 'false' ?>;
var mod_data = <?= $data->getJsonString() ?>;
var mod_config = <?= json_encode($mod_config) ?>;
var mod_lang = <?= json_encode($mod_lang['js']) ?>;
</script>
<script src="/engine/mods/custom/admin/assets/libs.js?v=<?= time() ?>"></script>
<link rel="stylesheet" type="text/css" href="/engine/mods/custom/admin/assets/style.css?v=<?= time() ?>">

<?php if ($data->notActivated()): ?>
<div class="mod-wrapper">
	<div class="mod-header">
		<div class="mod-header-title">
			AJAX-Custom &ndash; Активация модуля
		</div>
	</div>
	<div class="mod-content mod-activation">
		<h2>Host: <b><?= $_SERVER['SERVER_NAME'] ?></b></h2>
		<input type="text" id="lic_key" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" autocomplete="off">
		<button onclick="return custom.activate()">Активировать</button>
<?php if (!is_writable(MOD_DIR . '/data')): ?>
	<div class="mod-activation-error">Нет доступа к папке <b>/engine/mods/custom/data</b></div>
<?php endif; ?>
	</div><!-- .mod-content -->
</div><!-- .mod-wrapper -->
<div class="mod-copyright">Powered by <a href="//sandev.pro/">Sander-Development</a></div>	
	
<?php
echofooter();
die();
endif;
?>

<div class="mod-wrapper">
	<div class="mod-header">
		<div class="mod-header-title">
			AJAX-Custom &ndash; Управление блоками
		</div>
		<button class="mod-button mod-button-gray" onclick="return custom.showSubInfo();"><i class="fa fa-angle-down position-left"></i>Подробности</button>
		<button class="mod-button mod-button-blue" onclick="return custom.addBlock();"><i class="fa fa-plus position-left"></i>Добавить</button>
		<button class="mod-button" onclick="return custom.showSettings();"><i class="fa fa-cog position-left"></i>Настройки</button>
		<button class="mod-button mod-button-red" onclick="return custom.clearCache();" title="Очистить кеш"><i class="fa fa-trash position-left"></i>Кеш: <span id="cacheSize"><?= formatsize($cache_size) ?></span></button>
	</div>
	<div class="mod-content">

		<div class="dd">
			<ol class="dd-list" id="mod-items"></ol>
		</div>

	</div><!-- .mod-content -->

	<ul class="mod-faq">
			<li class="mod-faq-item">
				<div class="mod-faq-q">Как пользоваться модулем?</div>
				<div class="mod-faq-a">
					Основное назначение модуля - это создание AJAX навигации для <b>{custom ...}</b> блоков.<br>
					Для этого необходимо создать блок и внести в него соответствующие настройки.<br>
					После чего данный блок можно будет подключить в шаблоне тегом <b>{ajaxCustom.имяБлока}</b>
				</div>
			</li>
			<li class="mod-faq-item">
				<div class="mod-faq-q">Какие параметры/значения можно использовать в поле <b>"Параметры {custom ...}"</b>?</div>
				<div class="mod-faq-a">
					В данном поле можно указать любые статические параметры и значения, которые поддерживает тег <b>{custom ...}</b>.<br>
					Статические - означает, что поддерживаются только значения введенные вручную.<br>
					Любые конструкции типа <b>xfields="year|[xfvalue_year]"</b> не поддерживаются из-за использования шаблонного тега.<br>
					Отдельно стоит упомянуть параметры <b>cache</b> и <b>limit</b>. Эти значения прописываются отдельно в настройках блока и их значения прописанные в строке параметров будут проигнорированы.
				</div>
			</li>
			<li class="mod-faq-item">
				<div class="mod-faq-q">Что такое <b>"Имя файла шаблона"</b>?</div>
				<div class="mod-faq-a">
					Для корректной работы AJAX навигации - блок <b>custom</b>-а должен быть обернут в определенные блоки с определенными классами и прочими аттрибутами.
					Для этого, по умолчанию, используется файл шаблона <b>{THEME}/custom/main.tpl</b><br>
					Но бывают ситуации, когда у какого-то блока необходимо использовать свой <b>div</b> со своим классом. Для таких случаев можно использовать этот параметр. Он позволит использовать отдельный файл шаблона вместо <b>main.tpl</b><br>
					Прописывать нужно только имя файла, без расширения '<b>.tpl</b>'
				</div>
			</li>
			<li class="mod-faq-item">
				<div class="mod-faq-q">Как изменить внешний вид навигации?</div>
				<div class="mod-faq-a">
					Шаблон для навигации используется один общий для всех блоков - <b>{THEME}/custom/navigation.tpl</b><br>
					Для индивидуальной настройки можно воспользоваться особенностью наследования стилей, например: <pre><code>#ajaxCustom-shorts .navigation * {}</code></pre>
				</div>
			</li>
			<li class="mod-faq-item">
				<div class="mod-faq-q">Как использовать навигацию с динамическими значениями параметров?</div>
				<div class="mod-faq-a">
					Такая возможность есть, но она на половину экспериментальная.<br>
					Я категорически против практики, когда для организации <b>AJAX</b> в теле <b>POST</b> запроса в открытом виде передается строка с <b>custom</b> параметрами.<br>
					В связи с этим, параметры <b>custom</b>-а кешируются и записываются в отдельный файл. Где каждому уникальному <b>custom</b>-у присваивается свой ID.<br>
					Минусы этого способа в том, что в зависимости от количества блоков и разнообразия значений параметров тегов - размер кеша может вырасти до чрезмерных размеров, что потянет за собой излишнее потребление памяти.<br>
					Точных допустимых размеров файла кеша я не могу назвать. Но ориентировочно размер кеша в 100 кб - однозначно не будет являться проблемой. Возможно даже и 500кб.<br>
					Чтобы использовать этот функционал, формат записи в целом на 99% схож со стандартным <b>custom</b>-ом, пример:
					<pre><code>{ajaxCustom category="{category-id}" xfields="year|[xfvalue_year]" limit="14"  nav_type="main" max_page="50" tpl_name="temp" }</code></pre>
					Как видно вместо <b>{custom ...}</b> используется <b>{ajaxCustom ...}</b>, а так же перечислен ряд не штатных параметров, а именно:<br>
					<b>nav_type="main"</b> - используемый тип навигации (main, pages, next, more, lazy, none). По умолчанию <b>main</b><br>
					<b>max_page="50"</b> - максимальное количество страниц навигации. По умолчанию без ограничений<br>
					<b>tpl_name="temp"</b> - используемый файл шаблона. По умолчанию <b>main</b><br>
					Все вышеперечисленные параметры <b>не являются</b> обязательными.<br><br>
					<b>Важно!</b> При изменении каких-либо параметров в строке подключения - настроятельно рекомендуется очистить кеш в модуле, чтобы удалить старые, более не используемые, кеши блоков.
				</div>
			</li>
		</ul>





</div><!-- .mod-wrapper -->
<div class="mod-copyright">Powered by <a href="//sandev.pro/">Sander-Development</a></div>


<script type="text/basis-template" id="itemWrapperTemplate">
<li id="mod-item--{name}" class="dd-item mod-item" data-name="{name}"></li>
</script>


<script type="text/basis-template" id="itemTemplate">
<div class="dd-handle"></div>
<div class="mod-item-row">
	<div class="mod-item-row-status"></div>
	<a href="#" class="mod-item-row-title" onclick="return custom.showBlockConfig('{name}');">{name}</a>
	<div class="mod-item-row-info-descr">{value.descr}</div>
	<input type="text" class="mod-item-row-input hidden-sm hidden-xs" readonly onclick="this.select()" value="&#123;ajaxCustom.{name}&#125;">

	<div class="mod-item-row-bools hidden-sm hidden-xs">
		<span class="{active.lazyLoad} {local.lazyLoad}">lazyLoad</span>
		<span class="{active.eventLoad} {local.eventLoad}">eventLoad</span>
		<span class="{active.cache} {local.cache}">cache</span>
		<span class="{active.cookies} {local.cookies}">cookies</span>
	</div><!-- .mod-item-row-bools -->
</div><!-- .mod-item-row -->
<div class="mod-item-subrow">
	<span style="flex-basis: 90px">limit: {value.limit}</span>
	<span style="flex-basis: 110px">max_page: {value.max_page}</span>
	<span style="flex-basis: 100px">nav: {value.nav_type}</span>
	<span style="min-width: 130px;">template: {value.template}</span>
	<span>{value.params}</span>
</div>
</script>


<script type="text/basis-template" id="addItemTemplate">
<form id="addBlockWrapper">

	<input type="text" name="name" class="mod-input" value="" placeholder="Имя блока, на латинице">
	<input type="text" name="import" class="mod-input" value="" placeholder="Импорт настроек блока">

	<div style="padding: 10px 0 30px;">
		<button class="mod-button mod-button-blue" onclick="return custom.doAddBlock(this.form)">Добавить</button>
		<button class="mod-button mod-button-gray" onclick="return custom.closeAddBlock()">Закрыть</button>
	</div>
</form>
</script>

<script type="text/basis-template" id="editItemTemplate">
<form class="mod-item-edit" id="editItemForm">
	<input type="hidden" name="name" value="{name}">

	<table class="mod-settings">
	<thead>
		<tr>
			<td width="40%">Параметр</td>
			<td width="45%">Значение</td>
			<td width="15%">По умолчанию</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div class="mod-settings-td-title">Имя блока</div>
				<div class="mod-settings-td-info">Уникальное имя на латинице</div>
			</td>
			<td>
				<input type="text" name="con[name]" class="mod-input" value="{name}">
			</td>
			<td>—</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Тег подключения в шаблоне</div>
			</td>
			<td>
				<input type="text" class="mod-input" value="{ajaxCustom.{name}}" readonly onfocus="this.select()">
			</td>
			<td>—</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Включить блок</div>
			</td>
			<td>
				<label class="mod-checkbox"><input type="checkbox" name="con[active]" value="1" {checkbox.active}><span></span></label>
			</td>
			<td>—</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Имя файла шаблона</div>
				<div class="mod-settings-td-info">
					По умолчанию используется файл шаблона <b>{THEME}/custom/main.tpl</b><br>Вводить только имя файла, без папки и расширения<br>
					<b style="color:#e00;">Внимание!</b> Это родительский шаблон, НЕ параметр {custom template="..."}
				</div>
			</td>
			<td>
				<input type="text" name="con[template]" class="mod-input" value="{value.template}" >
			</td>
			<td>main</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Название/описание группы</div>
				<div class="mod-settings-td-info">Для себя, для удобства идентификации блока</div>
			</td>
			<td>
				<input type="text" name="con[descr]" class="mod-input" value="{value.descr}">
			</td>
			<td>—</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Параметры {custom ...}</div>
				<div class="mod-settings-td-info">
					Стандартные параметры прописываемые внутри тега {custom ...}<br>
					<b style="color:#e00;">Важно:</b> сам тег custom и фигурные скобки указывать не надо
				</div>
			</td>
			<td>
				<input type="text" name="con[params]" class="mod-input" value="{value.params}" placeholder="параметры custom-а">
			</td>
			<td>—</td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Количество новостей</div>
				<div class="mod-settings-td-info">Сколько новостей отображать на одной странице<br>
					<i>Оставить поле пустым, чтобы бралось значение по умолчанию</i>
				</div>
			</td>
			<td>
				<input type="number" name="con[limit]" class="mod-input" min="0" value="{value.limit}">
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['limit'] ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Количество страниц</div>
				<div class="mod-settings-td-info">Максимальное количество страниц навигации<br>
					<i>Оставить поле пустым, чтобы бралось значение по умолчанию</i><br/>
					Поставить <b>0</b>, чтобы убрать лимит
				</div>
			</td>
			<td>
				<input type="number" name="con[max_page]" class="mod-input" min="0" value="{value.max_page}">
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['max_page'] ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Тип навигации</div>
				<div class="mod-settings-td-info"></div>
			</td>
			<td>
				<select name="con[nav_type]" class="mod-input">
					<option value="">&mdash;</option>
	<?php foreach ($mod_lang['nav_type'] as $k => $v): ?>
					<option value="<?= $k ?>" {select.nav_type=<?= $k ?>}><?= $v ?></option>
	<?php endforeach; ?>
				</select>
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_lang['nav_type'][$mod_config['nav_type']] ?>"></td>
		</tr>

		<tr>
			<td>
				<div class="mod-settings-td-title">Запоминать номер страницы</div>
				<div class="mod-settings-td-info">Сохранять в cookies номер страницы навигации<br/>Работает только на типах навигации: <b>main, pages, next</b></div>
			</td>
			<td>
				<div class="mod-radio">
					<label><input type="radio" name="con[cookies]" value="" {radio.cookies=} ><span>- по умолчанию -</span></label>
					<label><input type="radio" name="con[cookies]" value="1" {radio.cookies=1} ><span>Включено</span></label>
					<label><input type="radio" name="con[cookies]" value="0" {radio.cookies=0} ><span>Нет</span></label>
				</div>
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['cookies'] ? 'Включено' : 'Нет' ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">lazyLoad</div>
				<div class="mod-settings-td-info">Содержимое блока будет загружено в момент прокрутки скролла до него</div>
			</td>
			<td>
				<div class="mod-radio">
					<label><input type="radio" name="con[lazyLoad]" value="" {radio.lazyLoad=} ><span>- по умолчанию -</span></label>
					<label><input type="radio" name="con[lazyLoad]" value="1" {radio.lazyLoad=1} ><span>Включено</span></label>
					<label><input type="radio" name="con[lazyLoad]" value="0" {radio.lazyLoad=0} ><span>Нет</span></label>
				</div>
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['lazyLoad'] ? 'Включено' : 'Нет' ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Загрузка по клику</div>
				<div class="mod-settings-td-info">Содержимое блока будет загружено при ручном вызове обработчика, пример:<br/>
onclick="return ajaxCustom.show('{name}')"</div>
			</td>
			<td>
				<div class="mod-radio">
					<label><input type="radio" name="con[eventLoad]" value="" {radio.eventLoad=} ><span>- по умолчанию -</span></label>
					<label><input type="radio" name="con[eventLoad]" value="1" {radio.eventLoad=1} ><span>Включено</span></label>
					<label><input type="radio" name="con[eventLoad]" value="0" {radio.eventLoad=0} ><span>Нет</span></label>
				</div>
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['eventLoad'] ? 'Включено' : 'Нет' ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Включить кеширование</div>
				<div class="mod-settings-td-info"></div>
			</td>
			<td>
				<div class="mod-radio">
					<label><input type="radio" name="con[cache]" value="" {radio.cache=} ><span>- по умолчанию -</span></label>
					<label><input type="radio" name="con[cache]" value="1" {radio.cache=1} ><span>Включено</span></label>
					<label><input type="radio" name="con[cache]" value="0" {radio.cache=0} ><span>Нет</span></label>
				</div>
			</td>
			<td><input type="text" class="mod-input" disabled value="<?= $mod_config['cache'] ? 'Включено' : 'Нет' ?>"></td>
		</tr>
		<tr>
			<td>
				<div class="mod-settings-td-title">Экспорт</div>
				<div class="mod-settings-td-info">Чтобы быстро создать копию блока, можно воспользоваться экспортом</div>
			</td>
			<td colspan="2">
				<textarea class="mod-item-export" onclick="return copyToClipboard(this)" readonly title="Копировать в буффер">{export}</textarea>
			</td>
		</tr>
	</tbody>
	</table>

</form>
</script><!-- #editItemTemplate -->

<script type="text/basis-template" id="settingsTemplate">
<form method="post" id="settingsWrapper">

	<table class="mod-settings">
		<thead>
			<tr>
				<td width="60%">Параметр</td>
				<td width="40%">Значение</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="mod-settings-td-title">Тип навигации</div>
					<div class="mod-settings-td-info"></div>
				</td>
				<td>
					<select name="con[nav_type]" class="mod-input">
		<?php foreach ($mod_lang['nav_type'] as $k => $v): ?>
						<option value="<?= $k ?>" {select.nav_type=<?= $k ?>}><?= $v ?></option>
		<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">Количество новостей в блоке</div>
					<div class="mod-settings-td-info">Максимальное количество новостей в блоке</div>
				</td>
				<td>
					<input type="number" name="con[limit]" class="mod-input" min="1" value="{value.limit}">
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">Количество страниц</div>
					<div class="mod-settings-td-info">Максимальное количество страниц навигации</div>
				</td>
				<td>
					<input type="number" name="con[max_page]" class="mod-input" min="1" value="{value.max_page}">
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">Запоминать номер страницы</div>
					<div class="mod-settings-td-info">Сохранять в cookies номер страницы навигации<br/>Работает только на типах навигации: <b>main, pages, next</b></div>
				</td>
				<td>
					<label class="mod-checkbox"><input type="checkbox" name="con[cookies]" value="1" {checkbox.cookies}><span></span></label>
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">LazyLoad подгрузка блока</div>
					<div class="mod-settings-td-info">Содержимое блока будет загружено в момент прокрутки скролла до него</div>
				</td>
				<td>
					<label class="mod-checkbox"><input type="checkbox" name="con[lazyLoad]" value="1" {checkbox.lazyLoad}><span></span></label>
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">Загрузка по клику</div>
					<div class="mod-settings-td-info">Содержимое блока будет загружено при ручном вызове обработчика, пример:<br>
onclick="return ajaxCustom.show('films')"</div>
				</td>
				<td>
					<label class="mod-checkbox"><input type="checkbox" name="con[eventLoad]" value="1" {checkbox.eventLoad}><span></span></label>
				</td>
			</tr>
			<tr>
				<td>
					<div class="mod-settings-td-title">Включить кеширование</div>
					<div class="mod-settings-td-info">Использовать кеширование по умолчанию</div>
				</td>
				<td>
					<label class="mod-checkbox"><input type="checkbox" name="con[cache]" value="1" {checkbox.cache}><span></span></label>
				</td>
			</tr>
		</tbody>
	</table>

	<div style="padding: 10px 0 30px;">
		<button class="mod-button mod-button-blue" onclick="return custom.saveConfig(this.form)">Применить</button>
		<button class="mod-button" onclick="return custom.saveConfig(this.form, true)">Сохранить и закрыть</button>
		<button class="mod-button mod-button-gray" onclick="return custom.hideSetting()">Закрыть</button>
	</div>
</form>
</script><!-- #settingsTemplate -->

<?php
unset($_SESSION['mod_alert']);
echofooter();