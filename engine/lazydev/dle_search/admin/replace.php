<?php
/**
 * Поисковые подмены
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Search\Data;
use LazyDev\Search\Admin;
use LazyDev\Search\Helper;
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php'));
include_once(DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
$parse = new ParseFilter();

$add = strip_tags($_GET['add']);
if ($add == 'yes') {
    $_id = -1;
    if (isset($_GET['id'])) {
        $_id = intval($_GET['id']);
        $_set = [];
        $check = ['', ''];

        if ($_id >= 0 && isset($dleSearchReplaceVar[$_id])) {
            $dleSearchLangVar['admin']['replace']['add_page'] = $dleSearchLangVar['admin']['replace']['edit_page'];
            $dleSearchLangVar['admin']['add'] = $dleSearchLangVar['admin']['save'];
            $check = ($dleSearchReplaceVar[$_id]['full']) ? ['checked', 'on'] : ['', ''];
            $_set['find'] = implode(PHP_EOL, $dleSearchReplaceVar[$_id]['find']);
            $_set['replace'] = strip_tags(stripslashes($dleSearchReplaceVar[$_id]['replace']));
        }
    }
echo <<<HTML
<form id="formPage" class="form-horizontal">

    <div class="panel panel-flat">
        <div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['replace']['add_page']}</div>
        
        <div class="panel-body">
        
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">{$dleSearchLangVar['admin']['replace']['search']} <span class="form-label-small">...</span></label>
                    <textarea class="form-control" style="width:95%; border:1px solid #ddd;" name="find" rows="6">{$_set['find']}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">{$dleSearchLangVar['admin']['replace']['replace']}</label>
                  <input type="text" class="form-control" name="replace" value="{$_set['replace']}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSearchLangVar['admin']['replace']['full']}</label>
                    <div class="col-md-12" style="margin-top: 10px;">
                        <input class="checkBox" type="checkbox" id="full" name="full" value="1" {$check[0]}>
                        <div class="br-toggle br-toggle-success ' . $check[1] . '" data-id="full">
                            <div class="br-toggle-switch"></div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="panel-footer">
			<button type="submit" class="btn bg-teal btn-raised position-left" style="background-color:#1e8bc3;">{$dleSearchLangVar['admin']['add']}</button>
		</div>
    </div>
</form>
HTML;

$jsAdminScript[] = <<<HTML
$('body').on('submit', 'form#formPage', function(e) {
    e.preventDefault();
    
    let formData = $('form#formPage').serializeArray();
    formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
    formData.push({name: 'action', value: 'addReplace'});
    formData.push({name: 'id', value: '{$_id}'});
    
    if (!$('[name="find"]').val().toString().trim()) {
        Growl.error({text: '{$dleSearchLangVar['admin']['pages']['not-name']}'});
        return;
    }
    
    if (!$('[name=replace]').val().toString().trim()) {
        Growl.error({text: '{$dleSearchLangVar['admin']['pages']['not-url']}'});
        return;
    }
    
    $.ajax({
        type: 'POST',
        data: formData,
        url: 'engine/lazydev/dle_search/admin/ajax/ajax.php',
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                Growl.error({text: data.text});
                return;
            } else {
                window.location.href = "{$PHP_SELF}?mod=dle_search&action=replace";
            }
        }
    });
});
HTML;
} else {
	$count = is_array($dleSearchReplaceVar) ? count($dleSearchReplaceVar) : 0;
echo <<<HTML
<form action="" method="post">
    <div class="panel panel-flat">
		<div class="panel-default">
			<div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['replace']['list']} ({$dleSearchLangVar['admin']['replace']['all']}{$count})
				<div class="heading-elements">
					<a href="{$PHP_SELF}?mod=dle_search&action=replace&add=yes" style="background-color: #1e824c" class="btn bg-teal"><i class="fa fa-plus position-left"></i>{$dleSearchLangVar['admin']['replace']['add']}</a>
				</div>
			</div>
HTML;
    if ($dleSearchReplaceVar) {
        echo <<<HTML
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>{$dleSearchLangVar['admin']['replace']['search']}</th>
							<th class="text-center">{$dleSearchLangVar['admin']['replace']['replace']}</th>
							<th class="text-center">{$dleSearchLangVar['admin']['replace']['full']}</th>
							<th class="text-center">{$dleSearchLangVar['admin']['replace']['edit']}</th>
						</tr>
					</thead>
					<tbody>

HTML;

        foreach ($dleSearchReplaceVar as $key => $item) {
            $itemFind = $item['find'];
            array_walk($itemFind, function (&$val, $key) {
                $val = strip_tags(stripslashes($val));
            });
            $searchWords = count($itemFind) > 3 ? array_slice($itemFind, 0, 3) : $itemFind;
            $searchWords = implode(', ', $searchWords);
            $searchWordsInfo = implode('<br>', $itemFind);
            $item['replace'] = strip_tags(stripslashes($item['replace']));
            $full = $item['full'] == 1 ? "<i style=\"color:green!important;\" class=\"fa fa-check\"></i>" : "<i style=\"color:red!important;\" class=\"fa fa-remove\"></i>";
echo <<<HTML
<tr id="replace_{$key}">
    <td>{$searchWords} <i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right" data-html="true" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$searchWordsInfo}"></i></td>
    <td class="text-center">{$item['replace']}</td>
    <td class="text-center">{$full}</td>
    <td class="text-center">
        <a href="{$PHP_SELF}?mod=dle_search&action=replace&add=yes&id={$key}" class="btn btn-primary btn-dle-filter"><i style="top: -3px" class="fa fa-pencil"></i></a>
        <a href="#" onclick="deleteReplace({$key}); return false;" class="btn btn-danger btn-dle-filter"><i style="top: -3px" class="fa fa-trash"></i></a>
    </td>
</tr>
HTML;

        }
        echo <<<HTML

					</tbody>
				</table>
			</div>
			
		</div>
	</div>
</form>
HTML;
$jsAdminScript[] = <<<HTML
function deleteReplace(id) {
    DLEconfirm('{$dleSearchLangVar['admin']['replace']['delete_text']}', '{$dleSearchLangVar['admin']['replace']['delete_title']}', function() {
        $.post('engine/lazydev/dle_search/admin/ajax/ajax.php', {action: 'deleteReplace', id: id, dle_hash: dle_login_hash}, function(data) {
            data = jQuery.parseJSON(data);
            if (data.error) {
                Growl.error({
                    title: '{$dleSearchLangVar['admin']['replace']['error']}',
                    text: data.text
                });
            } else {
                Growl.info({
                    title: '{$dleSearchLangVar['admin']['replace']['successful']}',
                    text: data.text
                });
                
                $('#replace_' + id).remove();
            }
        });
    });
}
HTML;
    } else {
echo <<<HTML
        <div class="alert alert-info alert-styled-left alert-arrow-left alert-component text-left">
            <h4>{$dleSearchLangVar['admin']['replace']['attention']}</h4>
            {$dleSearchLangVar['admin']['replace']['attention_text']}
        </div>
HTML;
    }
echo <<<HTML
</div>
HTML;
}