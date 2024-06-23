<?php
/**
 * Подключение файлов
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

if ($action == 'doeditnews' || $action == 'doaddnews') {
    include __DIR__ . '/check.news.php';
} elseif (substr_count($_SERVER['REQUEST_URI'], 'subscribe')) {
    $do = $dle_module = 'dle_subscribe';
    include_once realpath(__DIR__ . '/..')  . '/loader.php';
    include __DIR__ . '/subscribe.lib.php';
}