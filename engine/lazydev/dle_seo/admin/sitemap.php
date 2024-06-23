<?php
/**
 * Управление картой сайта
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Seo\Admin;
use LazyDev\Seo\Helper;

$langformatdatefull = 'd.m.Y H:i:s';

if (file_exists(ROOT_DIR . '/uploads/sitemap.xml')) {
echo <<<HTML
<div class="panel panel-default">
    <div class="panel-heading">{$dleSeoLang['admin']['sitemap']['list']}</div>
	<table class="table table-xs">
        <thead>
            <tr>
                <th>{$dleSeoLang['admin']['sitemap']['val']}</th>
                <th>{$dleSeoLang['admin']['sitemap']['date']}</th>
                <th>{$dleSeoLang['admin']['sitemap']['url']}</th>
            </tr>
        </thead>
        <tbody id="listSitemap">
HTML;
	$sitemapsFiles = [];
	$sitemapDir = opendir(ROOT_DIR . '/uploads');
	while ($file = readdir($sitemapDir)) {
		if ($file != '.htaccess' && !is_dir(ROOT_DIR . '/uploads/' . $file) && Helper::strposa($file, ['sitemap', 'news', 'category', 'xfsearch', 'tags', 'filter', 'static', 'collections']) !== false) {
			$file_date = date($langformatdatefull, filemtime(ROOT_DIR . '/uploads/' . $file));
			$sitemapsFiles[$file] = $file_date;
		}
	}
	closedir($sitemapDir);
	asort($sitemapsFiles);

	foreach ($sitemapsFiles as $file => $date) {
		$urlSitemap = $config['http_home_url'] . 'uploads/' . $file;
echo <<<HTML
<tr>
    <td>{$file}</td>
    <td>{$date}</td>
    <td><a href="{$urlSitemap}" target="_blank">{$urlSitemap}</td>
</tr>
HTML;
	}
echo <<<HTML
		</tbody>
	</table>
</div>
HTML;
}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" name=\"$name\">\r\n";
	foreach ($options as $value => $description) {
		$output .= "<option value=\"$value\"";
		if ($selected == $value) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";

	return $output;
}

$arraySelectChange = [
	'always' => $dleSeoLang['admin']['sitemap']['change_always'],
	'hourly' => $dleSeoLang['admin']['sitemap']['change_hourly'],
	'daily' => $dleSeoLang['admin']['sitemap']['change_daily'],
	'weekly' => $dleSeoLang['admin']['sitemap']['change_weekly'],
	'monthly' => $dleSeoLang['admin']['sitemap']['change_monthly'],
	'yearly' => $dleSeoLang['admin']['sitemap']['change_yearly'],
	'never'	=> $dleSeoLang['admin']['sitemap']['change_never']
];

$dleSeoSitemap['limit'] = $dleSeoSitemap['limit'] > 0 ? $dleSeoSitemap['limit'] : '';

$dleSeoSitemap['news']['priority'] = $dleSeoSitemap['news']['priority'] ?: 0.6;
$dleSeoSitemap['news']['change'] = $dleSeoSitemap['news']['change'] ?: 'weekly';
$sitemap_news_changefreq = makeDropDown($arraySelectChange, 'sitemap_news_changefreq', $dleSeoSitemap['news']['change']);

$dleSeoSitemap['cat']['priority'] = $dleSeoSitemap['cat']['priority'] ?: 0.6;
$dleSeoSitemap['cat']['change'] = $dleSeoSitemap['cat']['change'] ?: 'daily';
$sitemap_cat_changefreq = makeDropDown($arraySelectChange, 'sitemap_cat_changefreq', $dleSeoSitemap['cat']['change']);

$dleSeoSitemap['xfield']['priority'] = $dleSeoSitemap['xfield']['priority'] ?: 0.6;
$dleSeoSitemap['xfield']['change'] = $dleSeoSitemap['xfield']['change'] ?: 'daily';
$dleSeoSitemap['xfield']['on'] = $dleSeoSitemap['xfield']['on'] == 1 ? 'checked' : '';
$sitemap_xfield_changefreq = makeDropDown($arraySelectChange, 'sitemap_xfield_changefreq', $dleSeoSitemap['xfield']['change']);

$dleSeoSitemap['tag']['priority'] = $dleSeoSitemap['tag']['priority'] ?: 0.6;
$dleSeoSitemap['tag']['change'] = $dleSeoSitemap['tag']['change'] ?: 'daily';
$dleSeoSitemap['tag']['on'] = $dleSeoSitemap['tag']['on'] == 1 ? 'checked' : '';
$sitemap_tag_changefreq = makeDropDown($arraySelectChange, 'sitemap_tag_changefreq', $dleSeoSitemap['tag']['change']);

$dleSeoSitemap['static']['priority'] = $dleSeoSitemap['static']['priority'] ?: 0.6;
$dleSeoSitemap['static']['change'] = $dleSeoSitemap['static']['change'] ?: 'monthly';
$dleSeoSitemap['static']['on'] = $dleSeoSitemap['static']['on'] == 1 ? 'checked' : '';
$sitemap_stat_changefreq = makeDropDown($arraySelectChange, 'sitemap_stat_changefreq', $dleSeoSitemap['static']['change']);

$dleFilter = false;
if (file_exists(ENGINE_DIR . '/lazydev/dle_filter/index.php')) {
	$dleFilter = true;
	$dleSeoSitemap['dlefilter']['priority'] = $dleSeoSitemap['dlefilter']['priority'] ?: 0.7;
	$dleSeoSitemap['dlefilter']['change'] = $dleSeoSitemap['dlefilter']['change'] ?: 'weekly';
	$dleSeoSitemap['dlefilter']['on'] = $dleSeoSitemap['dlefilter']['on'] == 1 ? 'checked' : '';
	$sitemap_dlefilter_changefreq = makeDropDown($arraySelectChange, 'sitemap_dlefilter_changefreq', $dleSeoSitemap['dlefilter']['change']);
}

$dleCollections= false;
if (file_exists(ENGINE_DIR . '/lazydev/dle_collections/index.php')) {
	$dleCollections = true;
	$dleSeoSitemap['dlecollections']['priority'] = $dleSeoSitemap['dlecollections']['priority'] ?: 0.7;
	$dleSeoSitemap['dlecollections']['change'] = $dleSeoSitemap['dlecollections']['change'] ?: 'weekly';
	$dleSeoSitemap['dlecollections']['on'] = $dleSeoSitemap['dlecollections']['on'] == 1 ? 'checked' : '';
	$sitemap_dlecollections_changefreq = makeDropDown($arraySelectChange, 'sitemap_dlecollections_changefreq', $dleSeoSitemap['dlecollections']['change']);
}

echo <<<HTML
<div class="row">
    <div class="col-md-12">
        <form action="" method="post" class="form-horizontal" id="createSitemap">
            <div class="panel panel-default">
                <div class="panel-heading">{$dleSeoLang['admin']['sitemap']['title']}</div>
				<div class="panel-body">
				  {$dleSeoLang['admin']['sitemap']['about']}
				</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['count_news_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:3.75rem;" name="limit" value="{$dleSeoSitemap['limit']}">
                            <i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$dleSeoLang['admin']['sitemap']['count_news_helper']}"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['news_prio_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:3.75rem;" name="news_priority" value="{$dleSeoSitemap['news']['priority']}">
                            <span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
                            {$sitemap_news_changefreq}
                            <i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$dleSeoLang['admin']['sitemap']['prio_descr']}"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['cat_prio_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:3.75rem;" name="cat_priority" value="{$dleSeoSitemap['cat']['priority']}">
                            <span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
                            {$sitemap_cat_changefreq}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['turn_on_xfield']}</label>
                        <div class="col-sm-9 col-xs-6">
                        	<div class="can-toggle can-toggle--size-small" style="width: 100px;">
								<input id="turn_on_xfield" data-show="block-xfield" name="turn_on_xfield" value="1" type="checkbox" {$dleSeoSitemap['xfield']['on']}>
								<label for="turn_on_xfield"><div class="can-toggle__switch" data-checked="{$dleSeoLang['admin']['turn_on']}" data-unchecked="{$dleSeoLang['admin']['turn_off']}"></div></label>
							</div>
                        </div>
                    </div>
                    <div class="form-group" style="display: none;" id="block-xfield">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['xfield_prio_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:60px;" name="xfield_priority" value="{$dleSeoSitemap['xfield']['priority']}">
                            <span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
                            {$sitemap_xfield_changefreq}
                        </div>
                    </div>
					<div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['turn_on_tags']}</label>
                        <div class="col-sm-9 col-xs-6">
							<div class="can-toggle can-toggle--size-small" style="width: 100px;">
								<input id="turn_on_tags" data-show="block-tags" name="turn_on_tags" value="1" type="checkbox" {$dleSeoSitemap['tag']['on']}>
								<label for="turn_on_tags"><div class="can-toggle__switch" data-checked="{$dleSeoLang['admin']['turn_on']}" data-unchecked="{$dleSeoLang['admin']['turn_off']}"></div></label>
							</div>
                        </div>
                    </div>
                    <div class="form-group" style="display: none;" id="block-tags">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['tag_prio_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:60px;" name="tag_priority" value="{$dleSeoSitemap['tag']['priority']}">
                            <span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
                            {$sitemap_tag_changefreq}
                        </div>
                    </div>
HTML;

if ($dleFilter) {
echo <<<HTML
	<div class="form-group">
		<label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['turn_on_dle_filter']}</label>
		<div class="col-sm-9 col-xs-6">
			<div class="can-toggle can-toggle--size-small" style="width: 100px;">
				<input id="turn_on_dlefilter" data-show="block-dlefilter" name="turn_on_dlefilter" value="1" type="checkbox" {$dleSeoSitemap['dlefilter']['on']}>
				<label for="turn_on_dlefilter"><div class="can-toggle__switch" data-checked="{$dleSeoLang['admin']['turn_on']}" data-unchecked="{$dleSeoLang['admin']['turn_off']}"></div></label>
			</div>
		</div>
	</div>
	<div class="form-group" style="display: none;" id="block-dlefilter">
		<label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['dlefilter_prio_title']}</label>
		<div class="col-sm-9 col-xs-6">
			<input type="text" class="form-control" style="width:60px;" name="dlefilter_priority" value="{$dleSeoSitemap['dlefilter']['priority']}">
			<span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
			{$sitemap_dlefilter_changefreq}
		</div>
	</div>
HTML;

}

if ($dleCollections) {
	echo <<<HTML
	<div class="form-group">
		<label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['turn_on_dle_collections']}</label>
		<div class="col-sm-9 col-xs-6">
			<div class="can-toggle can-toggle--size-small" style="width: 100px;">
				<input id="turn_on_dlecollections" data-show="block-dlecollections" name="turn_on_dlecollections" value="1" type="checkbox" {$dleSeoSitemap['dlecollections']['on']}>
				<label for="turn_on_dlecollections"><div class="can-toggle__switch" data-checked="{$dleSeoLang['admin']['turn_on']}" data-unchecked="{$dleSeoLang['admin']['turn_off']}"></div></label>
			</div>
		</div>
	</div>
	<div class="form-group" style="display: none;" id="block-dlecollections">
		<label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['dlecollections_prio_title']}</label>
		<div class="col-sm-9 col-xs-6">
			<input type="text" class="form-control" style="width:60px;" name="dlecollections_priority" value="{$dleSeoSitemap['dlecollections']['priority']}">
			<span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
			{$sitemap_dlecollections_changefreq}
		</div>
	</div>
HTML;

}

echo <<<HTML
					<div class="form-group">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['turn_on_static']}</label>
                        <div class="col-sm-9 col-xs-6">
                        	<div class="can-toggle can-toggle--size-small" style="width: 100px;">
								<input id="turn_on_static" data-show="block-static" name="turn_on_static" value="1" type="checkbox" {$dleSeoSitemap['static']['on']}>
								<label for="turn_on_static"><div class="can-toggle__switch" data-checked="{$dleSeoLang['admin']['turn_on']}" data-unchecked="{$dleSeoLang['admin']['turn_off']}"></div></label>
							</div>
                        </div>
                    </div>
                    <div class="form-group" style="display: none;" id="block-static">
                        <label class="control-label col-sm-3 col-xs-6">{$dleSeoLang['admin']['sitemap']['static_prio_title']}</label>
                        <div class="col-sm-9 col-xs-6">
                            <input type="text" class="form-control" style="width:3.75rem;" name="stat_priority" value="{$dleSeoSitemap['static']['priority']}">
                            <span class="position-right position-left">{$dleSeoLang['admin']['sitemap']['change']}</span>
                            {$sitemap_stat_changefreq}
                        </div>
                    </div>
                </div>
				<div class="panel-body">
					<div class="progress">
						<div id="progressbar" class="progress-bar progress-blue" style="width:0%;"><span></span></div>
					</div>
					
					<span id="progress"></span>
					<ul style="list-style: none; padding: 0;" id="listOfWork"></ul>
				</div>
                <div class="panel-footer">
                    <input type="submit" id="start_button" class="btn bg-teal btn-sm btn-raised" value="{$dleSeoLang['admin']['sitemap']['start_button']}">
                </div>
            </div>
            <input type="hidden" id="sitemap_pos" name="sitemap_pos" value="0">
        </form>
    </div>
</div>
HTML;

if ($config['allow_alt_url']) {
	$dleSeoLang['admin']['sitemap']['yandex_descr'] = str_replace('{site}', $config['http_home_url'] . 'sitemap.xml', $dleSeoLang['admin']['sitemap']['yandex_descr']);
	$dleSeoLang['admin']['sitemap']['google_descr'] = str_replace('{site}', $config['http_home_url'] . 'sitemap.xml', $dleSeoLang['admin']['sitemap']['google_descr']);
} else {
	$dleSeoLang['admin']['sitemap']['yandex_descr'] = str_replace('{site}', $config['http_home_url'] . 'uploads/sitemap.xml', $dleSeoLang['admin']['sitemap']['yandex_descr']);
	$dleSeoLang['admin']['sitemap']['google_descr'] = str_replace('{site}', $config['http_home_url'] . 'uploads/sitemap.xml', $dleSeoLang['admin']['sitemap']['google_descr']);
}

echo <<<HTML
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-flat">
			
				<div class="navbar navbar-default navbar-component navbar-xs" style="margin-bottom: 0px;">
					<ul class="nav navbar-nav visible-xs-block">
						<li class="full-width text-center"><a data-toggle="collapse" data-target="#navbar-filter">
							<i class="fa fa-bars"></i></a>
						</li>
					</ul>
					<div class="navbar-collapse collapse" id="navbar-filter">
						<ul class="nav navbar-nav">
							<li class="active">
								<a onclick="ChangeOption(this, 'block_1');" class="tip">{$dleSeoLang['admin']['sitemap']['google']}</a>
							</li>
							<li>
								<a onclick="ChangeOption(this, 'block_2');" class="tip">{$dleSeoLang['admin']['sitemap']['yandex']}</a>
							</li>
						</ul>
					</div>
				</div>
			
			<div class="panel-body" id="block_1">
				{$dleSeoLang['admin']['sitemap']['google_descr']}
			</div>
			<div class="panel-body" id="block_2" style="display: none">
				{$dleSeoLang['admin']['sitemap']['yandex_descr']}
			</div>
		</div>
	</div>
</div>
HTML;


$jsAdminScript[] = <<<HTML

function ChangeOption(obj, selectedOption) {
    $('#navbar-filter li').removeClass('active');
    $(obj).parent().addClass('active');
    $('[id*=block_]').hide();
    $('#' + selectedOption).show();

    return false;
}

let total = [];
let all = 2;
let maps = [];

function sendXfields() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.xfield.priority});
	formData.push({name: 'change', value: total.xfield.change});
	formData.push({name: 'role', value: 'sitemap_xfield'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['xfield_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['xfield_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			maps.push(data);
			
			if (total.tag.on != 1 && total.static.on != 1 && total.dlefilter.on != 1 && total.dlecollections.on != 1) {
				saveMaps();
			}
			
			if (total.tag.on == 1) {
				sendTags();
			} else if (total.static.on == 1) {
				sendStatic();
			} else if (total.dlefilter.on == 1) {
				sendFilter();
			} else if (total.dlecollections.on == 1) {
				sendCollections();
			}
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['xfield_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function sendTags() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.tag.priority});
	formData.push({name: 'change', value: total.tag.change});
	formData.push({name: 'role', value: 'sitemap_tags'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['tags_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['tags_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			maps.push(data);
			
			if (total.static.on != 1 && total.dlefilter.on != 1 && total.dlecollections.on != 1) {
				saveMaps();
			}
			
			if (total.static.on == 1) {
				sendStatic();
			} else if (total.dlefilter.on == 1) {
				sendFilter();
			} else if (total.dlecollections.on == 1) {
				sendCollections();
			}
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['tags_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function sendStatic() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.static.priority});
	formData.push({name: 'change', value: total.static.change});
	formData.push({name: 'role', value: 'sitemap_static'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['static_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['static_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			if (data[0] != 'ok') {
				maps.push(data);
			}
			
			if (total.dlefilter.on != 1 && total.dlecollections.on != 1) {
				saveMaps();
			}
			
			if (total.dlefilter.on == 1) {
				sendFilter();
			} else if (total.dlecollections.on == 1) {
				sendCollections();
			}
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['static_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function sendFilter() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.dlefilter.priority});
	formData.push({name: 'change', value: total.dlefilter.change});
	formData.push({name: 'role', value: 'sitemap_dlefilter'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlefilter_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlefilter_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			if (data[0] != 'ok') {
				maps.push(data);
			}
			
			if (total.dlecollections.on != 1) {
				saveMaps();
			}
			
			if (total.dlecollections.on == 1) {
				sendCollections();
			}
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlefilter_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function sendCollections() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.dlecollections.priority});
	formData.push({name: 'change', value: total.dlecollections.change});
	formData.push({name: 'role', value: 'sitemap_dlecollections'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlecollections_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlecollections_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			if (data[0] != 'ok') {
				maps.push(data);
			}
			
			saveMaps();
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['dlecollections_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function sendCat() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'priority', value: total.cat.priority});
	formData.push({name: 'change', value: total.cat.change});
	formData.push({name: 'role', value: 'sitemap_cat'});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['cat_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['cat_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			maps.push(data);
			
			if (total.xfield.on != 1 && total.tag.on != 1 && total.static.on != 1 && total.dlefilter.on != 1 && total.dlecollections.on != 1) {
				saveMaps();
			}
			
			if (total.xfield.on == 1) {
				sendXfields();
			} else if (total.tag.on == 1) {
				sendTags();
			} else if (total.static.on == 1) {
				sendStatic();
			} else if (total.dlefilter.on == 1) {
				sendFilter();
			} else if (total.dlecollections.on == 1) {
				sendCollections();
			}
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['cat_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

function saveMaps() {
	let temp = [];
	
	$.each(maps, function(index, value) {
		temp.push({name: 'map_' + index, value: value.join(',')});
	});
	temp.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	temp.push({name: 'action', value: 'sitemap'});
	temp.push({name: 'role', value: 'sitemap_save'});
	$.ajax({
		type: 'POST',
		data: temp,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		Growl.info({title: '', text: '{$dleSeoLang['admin']['sitemap']['save_ok_alert']}', life: 7000});
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['save_ok']}');
		let proc = Math.round(100 / all);
		all--;
		$('#progressbar').css('width', proc + '%');
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['save_error']}');
		$('#start_button').attr('disabled', false);
	});
}

function sendNews() {
	let formData = [];
	formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
	formData.push({name: 'action', value: 'sitemap'});
	formData.push({name: 'role', value: 'sitemap_news'});
	formData.push({name: 'priority', value: total.news.priority});
	formData.push({name: 'change', value: total.news.change});
	formData.push({name: 'count', value: total.news.count});
	
	$.ajax({
		type: 'POST',
		data: formData,
		url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
		dataType: 'json'
	}).done(function(data) {
		if (!Array.isArray(data)) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['news_error']}');
			$('#start_button').attr('disabled', false);
		} else {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['news_ok']}');
			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			sendCat();
			maps.push(data);
		}
  	}).fail(function() {
		$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['news_error']}');
		$('#start_button').attr('disabled', false);
	});
	
	return false;
}

$(function() {
	$('[data-show]').each(function(index) {
		this.checked ? $('#' + $(this).data('show')).show() : $('#' + $(this).data('show')).hide();
	});
	
    $('body').on('change', '[data-show]', function(e) {
		this.checked ? $('#' + $(this).data('show')).show() : $('#' + $(this).data('show')).hide();
    });
    
    $('body').on('submit', 'form#createSitemap', function(e) {
    	e.preventDefault();
    	$('#start_button').attr('disabled', true);
    	
    	$('#progress').ajaxError(function(event, request, settings) {
			$(this).html('{$dleSeoLang['admin']['sitemap']['error_ajax']}');
			$('#start_button').attr('disabled', false);
	 	});
    	
    	
    	$('#progress').html('{$dleSeoLang['admin']['sitemap']['get_data']}');
    	
    	let formData = $('form#createSitemap').serializeArray();
		formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
		formData.push({name: 'action', value: 'sitemap'});
		formData.push({name: 'role', value: 'sitemap_data'});
    	
    	$.ajax({
			type: 'POST',
			data: formData,
			url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
			dataType: 'json'
		}).done(function(data) {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['data_ok']}');
			total = data;
			$.each(total, function(key, value) {
				if (value.on === 1) {
					all++;
				}
			});

			let proc = Math.round(100 / all);
			all--;
			$('#progressbar').css('width', proc + '%');
			
			sendNews();
	  	}).fail(function() {
			$('#listOfWork').append('{$dleSeoLang['admin']['sitemap']['data_error']}');
			$('#start_button').attr('disabled', false);
	  	});
    });
});


HTML;

?>