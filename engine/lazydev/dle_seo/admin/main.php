<?php
/**
* Главная страница админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Seo\Admin;

echo <<<HTML
<div class="panel panel-default">
    <div class="panel-heading">{$dleSeoLang['admin']['other']['block_menu']}</div>
    <div class="list-bordered">
HTML;
echo Admin::menu([
    [
        'link' => '?mod=' . $modLName . '&action=seo',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/seo.png',
        'title' => $dleSeoLang['admin']['seo_title'],
        'descr' => $dleSeoLang['admin']['seo_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=news',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/news.png',
        'title' => $dleSeoLang['admin']['news_title'],
        'descr' => $dleSeoLang['admin']['news_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=cat',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/cat.png',
        'title' => $dleSeoLang['admin']['cat_title'],
        'descr' => $dleSeoLang['admin']['cat_descr'],
    ],
	[
		'link' => '?mod=' . $modLName . '&action=sitemap',
		'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/map.png',
		'title' => $dleSeoLang['admin']['sitemap_title'],
		'descr' => $dleSeoLang['admin']['sitemap_descr'],
	]
]);
echo <<<HTML
    </div>
</div>
HTML;

if ($action == 'main') {
    include ENGINE_DIR . '/lazydev/dle_seo/admin/settings.php';
}

?>