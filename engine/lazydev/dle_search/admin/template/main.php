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

use LazyDev\Search\Data;

$styleNight = $night = '';
if ($_COOKIE['admin_dle_search_dark']) {
    $night = 'dle_theme_dark';
    $styleNight = <<<HTML
<style>
.navbar-inverse {
    background: #2e3131!important;
}
.chosen-container-multi .chosen-choices li.search-field input[type="text"] {
    color: #fff;
}
.panel-body + .panel-body, .panel-body + .table, .panel-body + .table-responsive, .panel-body.has-top-border {
    border-top: 1px solid rgba(255,255,255,0.2)!important;;
}
.chosen-container-single .chosen-single span {
    color: #f2f1ef!important;
}
.dle_theme_dark .panel, .dle_theme_dark .modal-content {
    color: #ffffff!important;
    background-color: #2e3131!important;
}
.chosen-container-single .chosen-search {
    background: #403c3c!important;
}
.chosen-container-single .chosen-search input[type=text] {
    color: #000!important;
}
body.dle_theme_dark {
    background-color: #545454!important;
}
.section_icon {
    background: transparent!important;
    box-shadow: none!important;
    -webkit-box-shadow: none!important;
}
.gray-theme.fr-box.fr-basic .fr-wrapper {
    background: #2e3131!important;
}
label.status {
    background: #2e3131;
}
.widget-four {
    background: #2e3131!important;
}
.progress-dle-filter {
    background-color: #191e3a;
    box-shadow: unset!important;
}
.widget-four .widget-heading h5 {
    color: #fff!important;
}
.inputLazy, .textLazy {
    background: #2e3131;
    color: #fff;
}
</style>
<link href="engine/lazydev/{$modLName}/admin/template/assets/tail.select-dark.min.css" rel="stylesheet" type="text/css">
HTML;
	$background_theme = 'background-color: #fbffff!important; color: #000!important;';
	$dleSearchLangVar['admin']['dark_theme'] = $dleSearchLangVar['admin']['white_theme'];
} else {
    $styleNight = <<<HTML
<link href="engine/lazydev/{$modLName}/admin/template/assets/tail.select-light.min.css" rel="stylesheet" type="text/css">
HTML;
	$background_theme = 'background-color: #282626;';
}

$setLangDleSearch = $_COOKIE['lang_dle_search'] ?: 'ru';

echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$dleSearchLangVar['admin']['title']}</title>
        <link href="engine/skins/fonts/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
        <link href="engine/skins/stylesheets/application.css" rel="stylesheet" type="text/css">
        <link href="engine/lazydev/{$modLName}/admin/template/assets/charts.css" rel="stylesheet" type="text/css">
        <link href="engine/lazydev/{$modLName}/admin/template/assets/style.css?v12" rel="stylesheet" type="text/css">
        <script src="engine/skins/javascripts/application.js"></script>
        <script src="engine/lazydev/{$modLName}/admin/template/assets/d3.v3.min.js"></script>
        <script src="engine/lazydev/{$modLName}/admin/template/assets/c3.min.js"></script>
        {$styleNight}
    </head>
    <body class="{$night}">
        <script>
            let dle_act_lang = [{$dleSearchLangVar['admin']['other']['jslang']}];
            let cal_language = {en: {months: [{$dleSearchLangVar['admin']['other']['jsmonth']}],dayOfWeekShort: [{$dleSearchLangVar['admin']['other']['jsday']}]}};
            let filedefaulttext = '{$dleSearchLangVar['admin']['other']['jsnotgot']}';
            let filebtntext = '{$dleSearchLangVar['admin']['other']['jschoose']}';
            let dle_login_hash = '{$dle_login_hash}';
        </script>
        <div class="navbar navbar-inverse">
            <div class="navbar-header">
                <a class="navbar-brand" href="?mod={$modLName}">{$dleSearchLangVar['name']} v1.3.1</a>
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
							<i class="flag-icon mr-50 flag-icon-{$setLangDleSearch}"></i><span class="selected-language">{$dleSearchLangVar['admin'][$setLangDleSearch]}</span></a>
							<ul class="dropdown-menu" aria-labelledby="dropdown-flag">
								<li>
									<a class="dropdown-item" href="#" onclick="setLang('ru');"><i class="flag-icon flag-icon-ru mr-50"></i> {$dleSearchLangVar['admin']['ru']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('en');"><i class="flag-icon flag-icon-en mr-50"></i> {$dleSearchLangVar['admin']['en']}</a>
									<a class="dropdown-item" href="#" onclick="setLang('ua');"><i class="flag-icon flag-icon-ua mr-50"></i> {$dleSearchLangVar['admin']['ua']}</a>
								</li>
							</ul>
                        </li>
                        <li><a href="{$PHP_SELF}?mod={$modLName}" title="{$dleSearchLangVar['admin']['other']['main']}">{$dleSearchLangVar['admin']['other']['main']}</a></li>
                        <li><a href="{$PHP_SELF}?mod=options&action=options" title="{$dleSearchLangVar['admin']['other']['all_menu_dle']}">{$dleSearchLangVar['admin']['other']['all_menu_dle']}</a></li>
                        <li><a href="{$config['http_home_url']}" title="{$dleSearchLangVar['admin']['other']['site']}" target="_blank">{$dleSearchLangVar['admin']['other']['site']}</a></li>
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
                            <a href="#" onclick="setDark();" class="btn bg-teal btn-sm" style="{$background_theme}float: right;border-radius: unset;font-size: 13px;margin-top: 4px;margin-left: 4px;">{$dleSearchLangVar['admin']['dark_theme']}</a>
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
if ($dleSearchConfigVar['cache']) {
echo <<<HTML
							<input type="button" onclick="clearCache();" class="btn bg-danger btn-sm" style="float: right;border-radius: unset;font-size: 13px;margin-top: 4px;" value="{$dleSearchLangVar['admin']['clear_cache']}">
HTML;
$jsAdminScript[] = <<<HTML

let clearCache = function() {
	DLEconfirm("{$dleSearchLangVar['admin']['accept_cache']}", "{$dleSearchLangVar['admin']['try']}", function() {
		coreAdmin.ajaxSend(false, 'clearCache', false);
	});
	return false;
}

HTML;
}
echo <<<HTML
                        </div>
                    </div>
                    
                    <div class="content">
HTML;

?>