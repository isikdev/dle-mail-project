<?php
if (!defined('DATALIFEENGINE')) {
    header('HTTP/1.1 403 Forbidden');
    die('Hacking attempt!');
}

$dleSeoAmp = false;

$pattern = "#^/([0-9]{4})/([0-9]{2})/([0-9]{2})/(.*)/amp.html$#";

if ($config['seo_type'] == 2) {
    $pattern = "#^([^.]+)/([0-9]+)-(.*)/amp.html$#";
} elseif ($config['seo_type'] == 1) {
    $pattern = "#^/([0-9]+)-(.*)/amp.html$#";
}

preg_match($pattern, $_SERVER['REQUEST_URI'], $matches);

if (!empty($matches) && is_array($matches) && count($matches)) {
    $_GET['subaction'] = 'showfull';

    if ($config['seo_type'] == 1) {
        $_GET['newsid'] = $matches[1];
        $_GET['seourl'] = $matches[2];
    }

    if ($config['seo_type'] == 2) {
        $_GET['newsid'] = $matches[2];
        $_GET['seourl'] = $matches[3];
        $_GET['seocat'] = substr($matches[1], 1);
    }

    if (!$config['seo_type']) {
        $_GET['seourl'] = $_GET['news_name'] = $matches[4];
        $_GET['year'] = $matches[1];
        $_GET['month'] = $matches[2];
        $_GET['day'] = $matches[3];
    }

    $dleSeoAmp = true;
}

unset($matches);