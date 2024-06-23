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

if (isset($dleSubscribeConfig['lang'])) {
	$dleSubscribeConfig['lang'] = in_array($dleSubscribeConfig['lang'], ['ua', 'en', 'ru']) ? $dleSubscribeConfig['lang'] : 'ru';
} else {
	$dleSubscribeConfig['lang'] = 'ru';
}

$setLangDleSubscribe = $dleSubscribeConfig['lang'];

echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$dleSubscribeLang['admin']['title']}</title>
        <link href="engine/skins/fonts/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
        <link href="engine/skins/stylesheets/application.css" rel="stylesheet" type="text/css">
        <link href="{$config['http_home_url']}engine/lazydev/{$modLName}/admin/template/assets/style.css?v1" rel="stylesheet" type="text/css">
        <script src="engine/skins/javascripts/application.js"></script>
		<script>
            let dle_act_lang = [{$dleSubscribeLang['admin']['other']['jslang']}];
            let cal_language = {
                en: {
                    months: [{$dleSubscribeLang['admin']['other']['jsmonth']}],
                    dayOfWeekShort: [{$dleSubscribeLang['admin']['other']['jsday']}]
                }
            };
            let filedefaulttext = '{$dleSubscribeLang['admin']['other']['jsnotgot']}';
            let filebtntext = '{$dleSubscribeLang['admin']['other']['jschoose']}';
            let dle_login_hash = '{$dle_login_hash}';
        </script>
    </head>
    <body>
        <div class="navbar navbar-inverse">
            <div class="navbar-header">
                <a class="navbar-brand" href="?mod={$modLName}">{$dleSubscribeLang['name']} v2.3.2</a>
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
							<i class="flag-icon mr-50 flag-icon-{$setLangDleSubscribe}"></i><span class="selected-language">{$dleSubscribeLang['admin'][$setLangDleSubscribe]}</span></a>
							<ul class="dropdown-menu" aria-labelledby="dropdown-flag">
								<li>
									<a class="dropdown-item" href="#" onclick="setLang('ru'); return false;"><i class="flag-icon flag-icon-ru mr-50"></i> {$dleSubscribeLang['admin']['ru']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('en'); return false;"><i class="flag-icon flag-icon-en mr-50"></i> {$dleSubscribeLang['admin']['en']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('ua'); return false;"><i class="flag-icon flag-icon-ua mr-50"></i> {$dleSubscribeLang['admin']['ua']}</a>
								</li>
							</ul>
                        </li>
                        <li><a href="{$PHP_SELF}?mod={$modLName}" title="{$dleSubscribeLang['admin']['other']['main']}">{$dleSubscribeLang['admin']['other']['main']}</a></li>
                        <li><a href="{$PHP_SELF}" title="{$dleSubscribeLang['admin']['other']['all_menu_dle']}">{$dleSubscribeLang['admin']['other']['all_menu_dle']}</a></li>
                        <li><a href="{$config['http_home_url']}" title="{$dleSubscribeLang['admin']['other']['site']}" target="_blank">{$dleSubscribeLang['admin']['other']['site']}</a></li>
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
                        </div>
                    </div>
                    
                    <div class="content">
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
?>