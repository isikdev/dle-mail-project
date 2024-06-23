<?php
/**
* Главная страница админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Subscribe\Admin;

echo <<<HTML
<div class="panel panel-default">
    <div class="panel-heading">{$dleSubscribeLang['admin']['other']['block_menu']}</div>
    <div class="list-bordered">
HTML;
echo Admin::menu([
    [
        'link' => '?mod=' . $modLName . '&action=settings',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/settings.png',
        'title' => $dleSubscribeLang['admin']['settings_title'],
        'descr' => $dleSubscribeLang['admin']['settings_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=subscribers',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/users.png',
        'title' => $dleSubscribeLang['admin']['subscribers_title'],
        'descr' => $dleSubscribeLang['admin']['subscribers_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=statistics',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/statistics.png',
        'title' => $dleSubscribeLang['admin']['statistics_title'],
        'descr' => $dleSubscribeLang['admin']['statistics_descr'],
    ]
]);
echo <<<HTML
    </div>
</div>
HTML;

?>