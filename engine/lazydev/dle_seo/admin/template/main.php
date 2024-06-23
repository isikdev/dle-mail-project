<?php
/**
* Дизайн админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../../');
	die('Hacking attempt!');
}

use LazyDev\Seo\Data;

$styleNight = $night = '';
if ($_COOKIE['admin_seo_dark']) {
    $night = 'dle_theme_dark';
    $styleNight = <<<HTML
<link href="engine/lazydev/{$modLName}/admin/template/assets/dark.css" rel="stylesheet" type="text/css">
<link href="engine/lazydev/{$modLName}/admin/template/assets/tail.select-dark.min.css" rel="stylesheet" type="text/css">
HTML;
	$background_theme = 'background-color: #fbffff!important; color: #000!important;';
	$dleSeoLang['admin']['dark_theme'] = $dleSeoLang['admin']['white_theme'];
} else {
$styleNight = <<<HTML
<link href="engine/lazydev/{$modLName}/admin/template/assets/tail.select-light.min.css" rel="stylesheet" type="text/css">
HTML;
	$background_theme = 'background-color: #282626;';
}

$setLangDleSeo = $dleSeoConfig['lang'] ?: 'ru';

echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$dleSeoLang['admin']['title']}</title>
        <link href="engine/skins/fonts/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
        <link href="engine/skins/stylesheets/application.css" rel="stylesheet" type="text/css">
        <link href="{$config['http_home_url']}engine/lazydev/{$modLName}/admin/template/assets/style.css?v2" rel="stylesheet" type="text/css">
        <script src="engine/skins/javascripts/application.js"></script>
        <script>
            let dle_act_lang = [{$dleSeoLang['admin']['other']['jslang']}];
            let cal_language = {
                en: {
                    months: [{$dleSeoLang['admin']['other']['jsmonth']}],
                    dayOfWeekShort: [{$dleSeoLang['admin']['other']['jsday']}]
                }
            };
            let filedefaulttext = '{$dleSeoLang['admin']['other']['jsnotgot']}';
            let filebtntext = '{$dleSeoLang['admin']['other']['jschoose']}';
            let dle_login_hash = '{$dle_login_hash}';
        </script>
        {$styleNight}
    </head>
    <body class="{$night}">
        <div class="navbar navbar-inverse">
            <div class="navbar-header">
                <a class="navbar-brand" href="?mod={$modLName}">{$dleSeoLang['name']} v2.2.0</a>
                <ul class="nav navbar-nav visible-xs-block">
                    <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="fa fa-angle-double-down"></i></a></li>
                    <li><a class="sidebar-mobile-main-toggle"><i class="fa fa-bars"></i></a></li>
                </ul>
            </div>
            <div class="navbar-collapse collapse" id="navbar-mobile">
                <div class="navbar-right">	
                    <ul class="nav navbar-nav">
                        <li class="dropdown dropdown-language nav-item">
							<a class="dropdown-toggle nav-link" id="dropdown-flag" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="flag-icon mr-50 flag-icon-{$setLangDleSeo}"></i><span class="selected-language">{$dleSeoLang['admin'][$setLangDleSeo]}</span></a>
							<ul class="dropdown-menu" aria-labelledby="dropdown-flag">
								<li>
									<a class="dropdown-item" href="#" onclick="setLang('ru'); return false;"><i class="flag-icon flag-icon-ru mr-50"></i> {$dleSeoLang['admin']['ru']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('en'); return false;"><i class="flag-icon flag-icon-en mr-50"></i> {$dleSeoLang['admin']['en']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('ua'); return false;"><i class="flag-icon flag-icon-ua mr-50"></i> {$dleSeoLang['admin']['ua']}</a>
								</li>
							</ul>
                        </li>
                        <li><a href="{$PHP_SELF}?mod={$modLName}" title="{$dleSeoLang['admin']['other']['main']}">{$dleSeoLang['admin']['other']['main']}</a></li>
                        <li><a href="{$PHP_SELF}" title="{$dleSeoLang['admin']['other']['all_menu_dle']}">{$dleSeoLang['admin']['other']['all_menu_dle']}</a></li>
                        <li><a href="{$config['http_home_url']}" title="{$dleSeoLang['admin']['other']['site']}" target="_blank">{$dleSeoLang['admin']['other']['site']}</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="page-container">
            <div class="page-content">
                
                <div class="content-wrapper">
                    <div class="page-header page-header-default">
                        <div class="breadcrumb-line">
                            <ul class="breadcrumb">
                                {$speedbar}
                            </ul>
                            <input type="button" onclick="setDark(); return false;" class="btn bg-teal btn-sm" style="{$background_theme}float: right;border-radius: unset;font-size: 13px;margin-top: 4px;margin-left: 4px;text-shadow: unset!important;" value="{$dleSeoLang['admin']['dark_theme']}">
HTML;
$jsAdminScript[] = <<<HTML

let setDark = function() {
	$.post('engine/lazydev/' + coreAdmin.mod + '/admin/ajax/ajax.php', {action: 'setDark', dle_hash: dle_login_hash}, function(info) {
        if (info) {
            window.location.reload();
        }
    });
    
	return false;
}
let setLang = function(lang) {
	$.post('engine/lazydev/' + coreAdmin.mod + '/admin/ajax/ajax.php', {action: 'setLang', lang: lang, dle_hash: dle_login_hash}, function(info) {
        if (info) {
            window.location.reload();
        }
    });
    
	return false;
}

HTML;
if (Data::get('cache', 'config')) {
    echo <<<HTML
							<input type="button" onclick="clearCache();" class="btn bg-danger btn-sm" style="float: right;border-radius: unset;font-size: 13px;margin-top: 4px;" value="{$dleSeoLang['admin']['clear_cache']}">
HTML;
$jsAdminScript[] = <<<HTML

let clearCache = function() {
	DLEconfirm("{$dleSeoLang['admin']['accept_cache']}", "{$dleSeoLang['admin']['try']}", function() {
		coreAdmin.ajaxSend(false, 'clearCache', false);
	});
	return false;
};

HTML;
}
echo <<<HTML
                        </div>
                    </div>
                    
                    <div class="content">
HTML;

?>