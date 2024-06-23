<?php
/**
* Настройки модуля
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Seo\Admin;

$allXfield = xfieldsload();
foreach ($allXfield as $value) {
    $xfieldArray[$value[0]] = $value[1];
}

$categories = CategoryNewsSelection((empty($dleSeoConfig['amp_cat']) ? 0 : $dleSeoConfig['amp_cat']));

echo <<<HTML
<form action="" method="post">
    <div class="panel panel-flat">
        <div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSeoLang['admin']['settings_title']}</div>
        <div class="table-responsive">
            <table class="table">
HTML;
Admin::row(
    $dleSeoLang['admin']['settings']['turn_off'],
    $dleSeoLang['admin']['settings']['turn_off_descr'],
    Admin::checkBox('turn_off', $dleSeoConfig['turn_off'], 'turn_off')
);
Admin::row(
    $dleSeoLang['admin']['settings']['amp'],
	$dleSeoLang['admin']['settings']['amp_descr'],
    Admin::checkBox('amp', $dleSeoConfig['amp'], 'amp')
);
Admin::row(
    $dleSeoLang['admin']['settings']['amp_cat'],
    $dleSeoLang['admin']['settings']['amp_cat_descr'],
    Admin::selectTag('amp_cat[]', $categories, $dleSeoLang['admin']['settings']['categories'])
);
Admin::row(
    $dleSeoLang['admin']['settings']['cache'],
    $dleSeoLang['admin']['settings']['cache_descr'],
    Admin::checkBox('cache', $dleSeoConfig['cache'], 'cache'),
    $dleSeoLang['admin']['settings']['cache_helper']
);
Admin::row(
    $dleSeoLang['admin']['settings']['xfield_alt'],
    $dleSeoLang['admin']['settings']['xfield_alt_descr'],
    Admin::checkBox('xfield_alt', $dleSeoConfig['xfield_alt'], 'xfield_alt'),
	$dleSeoLang['admin']['settings']['alt_helper']
);
Admin::row(
    $dleSeoLang['admin']['settings']['tags_alt'],
    $dleSeoLang['admin']['settings']['tags_alt_descr'],
    Admin::checkBox('tags_alt', $dleSeoConfig['tags_alt'], 'tags_alt'),
	$dleSeoLang['admin']['settings']['alt_helper']
);
Admin::row(
	$dleSeoLang['admin']['settings']['load_scripts'],
	$dleSeoLang['admin']['settings']['load_scripts_descr'],
	Admin::checkBox('load_scripts', $dleSeoConfig['load_scripts'], 'load_scripts')
);
echo <<<HTML
            </table>
        </div>
		<div class="panel-footer">
			<button type="submit" class="btn bg-teal btn-raised position-left" style="background-color:#1e8bc3;">{$dleSeoLang['admin']['save']}</button>
		</div>
    </div>
</form>
HTML;

$jsAdminScript[] = <<<HTML

$(function() {
    $('body').on('submit', 'form', function(e) {
        coreAdmin.ajaxSend($('form').serialize(), 'saveOptions', false);
		return false;
    });
});

function ChangeOption(obj, selectedOption) {
    $('#navbar-filter li').removeClass('active');
    $(obj).parent().addClass('active');
    $('[id*=block_]').hide();
    $('#' + selectedOption).show();

    return false;
}

HTML;

?>