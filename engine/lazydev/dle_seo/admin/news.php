           }
        }
    });
})<?php
/**
 * Оптимизация новостей
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Seo\Helper;

include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php'));
include_once(DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
$parse = new ParseFilter();

$typeAction = stripslashes(strip_tags($_REQUEST['add']));

if ($typeAction == 'yes') {
    $idRule = 0;
    $newsRule = $tempRule = [];

    if (isset($_GET['id'])) {
        $idRule = intval($_GET['id']);
        $newsRule = $db->super_query("SELECT * FROM " . PREFIX .  "_dle_seo_news WHERE id='{$idRule}'");
        $category = "<option value=\"all\" " . ('all' == $newsRule['cats'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['news']['_all_'] . "</option>" . CategoryNewsSelection(explode(',', $newsRule['cats']), 0, false);

        $meta = xfieldsdataload($newsRule['meta']);
        $og = xfieldsdataload($newsRule['og']);

        $tempRule['meta_title'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['title'], false));
        
        $tempRule['meta_descr'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['descr'], false));
		$tempRule['meta_key'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['meta_key'], false));
        $tempRule['meta_speedbar'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['speedbar'], false));

        $tempRule['meta_og_title'] = str_replace("&amp;", "&", $parse->decodeBBCodes($og['title'], false));
        $tempRule['meta_og_descr'] = str_replace("&amp;", "&", $parse->decodeBBCodes($og['descr'], false));
        $dleSeoLang['admin']['news']['add_page'] = $dleSeoLang['admin']['news']['edit_page'];
    } else {
        $category = "<option value=\"all\">" . $dleSeoLang['admin']['news']['_all_'] . "</option>" . CategoryNewsSelection(0, 0, false);
    }

    $allxField = xfieldsload();

    $xfieldSelect = '';
    $xfieldSelect .= "<option value=\"_none_\" " . ('_none_' == $og['image'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['news']['_none_'] . "</option>";
    $xfieldSelect .= "<option value=\"_short_story_\" " . ('_short_story_' == $og['image'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['news']['_short_story_'] . "</option>";
    $xfieldSelect .= "<option value=\"_full_story_\" " . ('_full_story_' == $og['image'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['news']['_full_story_'] . "</option>";

    foreach ($allxField as $value) {
        if (!in_array($value[3], ['text', 'textarea', 'image', 'imagegalery'])) {
            continue;
        }

        $xfieldSelect .= "<option value=\"{$value[0]}\" " . ($value[0] == $og['image'] ? 'selected' : '') . ">" . htmlspecialchars($value[1]) . "</option>";
    }

    $checkedChange = ($idRule > 0 ? $newsRule['replacement'] : 1) ? ['checked', 'on'] : ['', ''];
    $additionalJsAdminScript[] = "<script src=\"engine/classes/uploads/html5/fileuploader.js\"></script>";
echo <<<HTML
<script>
function ShowHide(d) {
	if ($(d).text() === '{$dleSeoLang['admin']['news']['button_show']}') {
		$('#content_help').show();
		$(d).text('{$dleSeoLang['admin']['news']['button_hide']}');
		$(d).css('border-color', '#e53935');
	} else {
		$('#content_help').hide();
		$(d).text('{$dleSeoLang['admin']['news']['button_show']}');
		$(d).css('border-color', '#006c96');
	}
}
</script>
<form id="formSeo" class="form-horizontal">
    <div class="panel panel-flat">
        <div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSeoLang['admin']['news']['add_page']}</div>
        <div class="panel-body" style="padding: 0!important;">
            <div class="alert alert-component text-size-small" style="margin-bottom:0px!important;box-shadow:none!important;">
                <button style="border-radius: 0;background: #fff;border: 1px solid #006c96;color: #000;width: 100%;text-shadow: unset!important;" onclick="ShowHide(this); return false;" class="btn bg-teal btn-raised btn-sm">{$dleSeoLang['admin']['news']['button_show']}</button>
            </div>
            <table class="table table-normal table-hover" id="content_help" style="display: none;">
                <thead>
                    <tr>
                        <td>{$dleSeoLang['admin']['news']['tags']['tag']}</td>
                        <td>{$dleSeoLang['admin']['news']['tags']['tag_descr']}</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 600px;"><b>{id}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['id']}</td>
                    </tr>
                    <tr>
                        <td><b>{title}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['title']}</td>
                    </tr>
                    <tr>
                        <td><b>{title low}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['title_low']}</td>
                    </tr>
                    <tr>
                        <td><b>{title up}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['title_up']}</td>
                    </tr>
                    <tr>
                        <td><b>{title case}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['title_case']}</td>
                    </tr>
                    <tr>
                        <td><b>{title first}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['title_first']}</td>
                    </tr>
                    <tr>
                        <td><b>{date={$dleSeoLang['admin']['news']['tags']['format_date']}}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['date']}</td>
                    </tr>
                    <tr>
                        <td><b>{alt-name}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['alt_name']}</td>
                    </tr>
                    <tr>
                        <td><b>{cat}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['cat']}</td>
                    </tr>
                    <tr>
                        <td><b>{tags}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['tags']}</td>
                    </tr>
                    <tr>
                        <td><b>[tags] {$dleSeoLang['admin']['news']['tags']['text']} [/tags]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['tags_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[not-tags] {$dleSeoLang['admin']['news']['tags']['text']} [/not-tags]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['not_tags_block']}</td>
                    </tr>
                    <tr>
                        <td><b>{author}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['author']}</td>
                    </tr>
                    <tr>
                        <td><b>[xfvalue_x]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['xfvalue']}</td>
                    </tr>
                    <tr>
                        <td><b>[xfvalue_X limit="X2"]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['xfvalue_limit']}</td>
                    </tr>
                    <tr>
                        <td><b>[xfgiven_x] {$dleSeoLang['admin']['news']['tags']['text']} [/xfgiven_x]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['xf_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[xfnotgiven_X] {$dleSeoLang['admin']['news']['tags']['text']} [/xfnotgiven_X]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['not_xf_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[short-story] {$dleSeoLang['admin']['news']['tags']['text']} [/short-story]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['short_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[not-short-story] {$dleSeoLang['admin']['news']['tags']['text']} [/not-short-story]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['not_short_block']}</td>
                    </tr>
                    <tr>
                        <td><b>{short-story}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['short']}</td>
                    </tr>
                    <tr>
                        <td><b>{short-story limit="X"}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['short_limit']}</td>
                    </tr>
                    <tr>
                        <td><b>[full-story] {$dleSeoLang['admin']['news']['tags']['text']} [/full-story]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['full_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[not-full-story] {$dleSeoLang['admin']['news']['tags']['text']} [/not-full-story]</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['not_full_block']}</td>
                    </tr>
                    <tr>
                        <td><b>{full-story}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['full']}</td>
                    </tr>
                    <tr>
                        <td><b>{full-story limit="X"}</b></td>
                        <td>{$dleSeoLang['admin']['news']['tags']['full_limit']}</td>
                    </tr>
                    <tr>
                        <td>[if field={$dleSeoLang['admin']['news']['tags']['text']}]{$dleSeoLang['admin']['news']['tags']['if=']}[/if]<br><br>
[if field!={$dleSeoLang['admin']['news']['tags']['text']}]{$dleSeoLang['admin']['news']['tags']['if!=']}[/if]<br><br>
[if field=={$dleSeoLang['admin']['news']['tags']['text_multiple']}]{$dleSeoLang['admin']['news']['tags']['if==']}[/if] // {$dleSeoLang['admin']['news']['tags']['if==_note']}<br><br>
[if field!=={$dleSeoLang['admin']['news']['tags']['text_multiple']}]{$dleSeoLang['admin']['news']['tags']['if!==']}[/if] // {$dleSeoLang['admin']['news']['tags']['if==_note']}<br><br>
[if field>100]{$dleSeoLang['admin']['news']['tags']['if>']}[/if]<br><br>
[if field>=55]{$dleSeoLang['admin']['news']['tags']['if>=']}[/if]<br><br>
[if field<300]{$dleSeoLang['admin']['news']['tags']['if<']}[/if]<br><br>
[if field<=444]{$dleSeoLang['admin']['news']['tags']['if<=']}[/if]<br><br>
[if field~{$dleSeoLang['admin']['news']['tags']['if_co']}]{$dleSeoLang['admin']['news']['tags']['if~']}[/if]<br><br>
[if field!~{$dleSeoLang['admin']['news']['tags']['if_co']}]{$dleSeoLang['admin']['news']['tags']['if!~']}[/if]</td>
                        <td>{$dleSeoLang['admin']['news']['tags']['if_field']}<br>
                        <ol>
                            <li><b>id</b> - {$dleSeoLang['admin']['news']['tags']['if_id']}</li>
                            <li><b>title</b> - {$dleSeoLang['admin']['news']['tags']['if_title']}</li>
                            <li><b>xfvalue_X</b> - {$dleSeoLang['admin']['news']['tags']['if_xf']}</li>
                            <li><b>full_story</b> - {$dleSeoLang['admin']['news']['tags']['if_full']}</li>
                            <li><b>short_story</b> - {$dleSeoLang['admin']['news']['tags']['if_short']}</li>
                            <li><b>tags</b> - {$dleSeoLang['admin']['news']['tags']['if_tags']}</li>
                            <li><b>autor</b> - {$dleSeoLang['admin']['news']['tags']['if_author']}</li>
                        </ol>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none!important;"><b>{description}</b></td>
                        <td style="border: none!important;">{$dleSeoLang['admin']['news']['tags']['description']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="panel-body">
        
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['cat']}</label>
                <div class="col-md-12"><br>
                    <select id="catSelect" name="cat[]" multiple>{$category}</select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['change']}</label>
                <div class="col-md-12" style="margin-top: 10px;">
                    <input class="checkBox" type="checkbox" id="change" name="change" value="1" {$checkedChange[0]}>
                    <div class="br-toggle br-toggle-success ' . $checkedChange[1] . '" data-id="change">
                        <div class="br-toggle-switch"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_title']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_title" value="{$tempRule['meta_title']}" maxlength="255">
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_descr']}</label>
                <div class="col-md-12">
                    <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="meta_descr" maxlength="300">{$tempRule['meta_descr']}</textarea>
                </div>
            </div>
            
            <div class="form-group">
				<label class="control-label col-md-12">{$dleSeoLang['admin']['news']['meta_key']}</label>
				<div class="col-md-12">
					<input type="text" class="inputLazy" name="meta_key" value="{$tempRule['meta_key']}">
				</div>
			</div>
            
HTML;
    if ($config['speedbar']) {
echo <<<HTML
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_speedbar']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_speedbar" value="{$tempRule['meta_speedbar']}">
                </div>
            </div>
HTML;
    }
    echo <<<HTML
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_og_title']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_og_title" value="{$tempRule['meta_og_title']}" maxlength="255">
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_og_descr']}</label>
                <div class="col-md-12">
                    <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="meta_og_descr" maxlength="300">{$tempRule['meta_og_descr']}</textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['news']['seo_og_image']}</label>
                <div class="col-md-12"><br>
                    <select id="ogImage" name="meta_og_image">{$xfieldSelect}</select>
                </div>
            </div>
            
            <div class="form-group">
					<label class="control-label col-md-12">{$dleSeoLang['admin']['news']['default_image']}</label>
					<div class="col-md-12"><br>
						<div id="xfupload_default_image"></div>
						<input type="hidden" id="default_image" class="form-control" value="{$og['default']}" name="default_image">
HTML;
    $lang['wysiwyg_language'] = totranslit($lang['wysiwyg_language'], false, false);
    $member_id['name'] = urlencode($member_id['name']);

    if ($og['default']) {
        $img_url = $og['default'];
        $og['default'] = explode('/', $img_url);
        $og['default'] = end($og['default']);
        $up_image = "<div class=\"uploadedfile\"><div class=\"info\">{$og['default']}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"xfimagedelete(\\'default_image\\',\\'".$og['default']."\\');return false;\">{$lang['xfield_xfid']}</a></div></div>";
    }
$uploadscript = <<<HTML
	new qq.FileUploader({
		element: document.getElementById('xfupload_default_image'),
		action: 'engine/lazydev/dle_seo/admin/ajax/upload.php',
		maxConnections: 1,
		multiple: false,
		allowdrop: false,
		encoding: 'multipart',
        sizeLimit: {$config['max_up_size']} * 1024,
		allowedExtensions: ['gif', 'jpg', 'jpeg', 'png', 'webp', 'jfif'],
	    params: {'subaction': 'upload', 'idRule': '{$idRule}', 'area': 'xfieldsimage', 'author': '{$member_id['name']}', 'xfname': 'default_image', 'user_hash': '{$dle_login_hash}'},
        template: '<div class="qq-uploader">' + 
                '<div id="uploadedfile_default_image">{$up_image}</div>' +
                '<div class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$lang['xfield_xfim']}</div>' +
                '<ul class="qq-upload-list" style="display:none;"></ul>' + 
             '</div>',
		onSubmit: function(id, fileName) {
			$('<div id="uploadfile-'+id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+fileName+'</span>&nbsp;<span class="qq-status"><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span></span><div class="progress "><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_default_image');
        },
		onProgress: function(id, fileName, loaded, total) {
			$('#uploadfile-'+id+' .qq-upload-size').text(DLEformatSize(loaded)+' {$lang['media_upload_st8']} '+DLEformatSize(total));
			var proc = Math.round(loaded / total * 100);
			$('#uploadfile-'+id+' .progress-bar').css("width", proc + '%');
			$('#uploadfile-'+id+' .qq-upload-spinner').css("display", "inline-block");
		},
		onComplete: function(id, fileName, response) {
			if (response.success) {
				var returnbox = response.returnbox;
				var returnval = response.xfvalue;

				returnbox = returnbox.replace(/&lt;/g, "<");
				returnbox = returnbox.replace(/&gt;/g, ">");
				returnbox = returnbox.replace(/&amp;/g, "&");

				$('#uploadfile-'+id+' .qq-status').html('{$lang['media_upload_st9']}');
				$('#uploadedfile_default_image').html(returnbox);
				$('#default_image').val(returnval);

				$('#xfupload_default_image .qq-upload-button, #xfupload_default_image .qq-upload-button input').attr("disabled","disabled");
				
				setTimeout(function() {
					$('#uploadfile-'+id).fadeOut('slow', function() { $(this).remove(); });
				}, 1000);
			} else {
				$('#uploadfile-'+id+' .qq-status').html('{$lang['media_upload_st10']}');
				if( response.error ) $('#uploadfile-'+id+' .qq-status').append( '<br /><span class="text-danger">' + response.error + '</span>' );
				setTimeout(function() {
					$('#uploadfile-'+id).fadeOut('slow');
				}, 4000);
			}
		},
        messages: {
            typeError: "{$lang['media_upload_st11']}",
            sizeError: "{$lang['media_upload_st12']}",
            emptyError: "{$lang['media_upload_st13']}"
        },
		debug: false
    });
	
	if ($('#default_image').val() != '') {
		$('#xfupload_default_image .qq-upload-button, #xfupload_default_image .qq-upload-button input').attr("disabled","disabled");
	}
HTML;
echo <<<HTML
<script>
function xfimagedelete(xfname, xfvalue) {
    DLEconfirm('{$lang['image_delete']}', '{$lang['p_info']}', function() {
        ShowLoading('');
        $.post('engine/lazydev/dle_seo/admin/ajax/upload.php', {subaction: 'deluploads', user_hash: '{$dle_login_hash}', idRule: '{$idRule}', author: '{$member_id['name']}', 'images[]' : xfvalue}, function(data) {
            HideLoading('');
            $('#uploadedfile_default_image').html('');
            $('#default_image').val('');
            $('#xfupload_default_image .qq-upload-button, #xfupload_default_image .qq-upload-button input').removeAttr('disabled');
        });
        
    });

    return false;
};

$(function() {
    {$uploadscript}
});
</script>
                    </div>
                </div>
            
        </div>
        <div class="panel-footer">
			<button type="submit" class="btn bg-teal btn-raised position-left" style="background-color:#1e8bc3;">{$dleSeoLang['admin']['save']}</button>
		</div>
    </div>
</form>
HTML;

$jsAdminScript[] = <<<HTML
let catSelect = tail.select('#catSelect', {
    search: true,
    multiSelectAll: true,
    placeholder: "{$dleSeoLang['admin']['seo']['enter']}",
    classNames: "default white",
    multiContainer: true,
    multiShowCount: false
});
let ogImage = tail.select('#ogImage');
$('body').on('submit', 'form#formSeo', function(e) {
    e.preventDefault();
    
    if (!$('#catSelect').val()) {
        Growl.error({text: '{$dleSeoLang['admin']['news']['not_choose_cat']}'});
        return;
    }
    
    let formData = $('form#formSeo').serializeArray();
    formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
    formData.push({name: 'action', value: 'addRule'});
    formData.push({name: 'id', value: '{$idRule}'});
    
    $.ajax({
        type: 'POST',
        data: formData,
        url: 'engine/lazydev/dle_seo/admin/ajax/ajax.php',
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                Growl.error({text: data.text});
            }
            
            if (data.type !== undefined && data.id !== undefined) {
                window.location.href = "{$PHP_SELF}?mod=dle_seo&action=info&info=" + data.type + "&id=" + data.id + "&index=" + data.index + "&from=" + data.from;
            }
        }
    });
});
HTML;

} else {
    $where = '';
    $search_field = intval($_REQUEST['cat']);
    if ($search_field > 0) {
        $where = "WHERE `cats` REGEXP '([[:punct:]]|^)(" . $search_field . ")([[:punct:]]|$)' ";
    }

    $selectRuleNews = $db->query("SELECT * FROM " . PREFIX . "_dle_seo_news {$where}");
    $countNewsRules = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_seo_news {$where}")['count'];

    $categories_list = CategoryNewsSelection($search_field, 0);
echo <<<HTML
<div class="modal fade" id="advancedsearch" role="dialog" aria-labelledby="advancedsearchLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="?mod=dle_seo&amp;action=news" method="GET" name="optionsbar" id="optionsbar">
                <input type="hidden" name="mod" value="dle_seo">
                <input type="hidden" name="action" value="news">
                <div class="modal-header ui-dialog-titlebar" style="border-radius: unset!important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <span class="ui-dialog-title" id="newcatsLabel">{$dleSeoLang['admin']['find_title']}</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>{$dleSeoLang['admin']['cat_find']}</label>
                                <div class="input-group">
                                    <select name="cat" class="uniform form-control">
                                        {$categories_list}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="search_submit(0); return(false);" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-search position-left"></i>{$dleSeoLang['admin']['find_button']}</button>
                    <button onclick="document.location='?mod=dle_seo&action=news'; return(false);" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-eraser position-left"></i>{$dleSeoLang['admin']['find_clear']}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        {$dleSeoLang['admin']['news']['list']} ({$dleSeoLang['admin']['news']['all']}{$countNewsRules})
        <div class="heading-elements">
            <a data-toggle="modal" data-target="#advancedsearch" href="#" style="margin-top: 6px" class="btn btn-sm bg-pink"><i class="fa fa-search position-left"></i>{$dleSeoLang['admin']['find_modal']}</a>
            <a href="{$PHP_SELF}?mod=dle_seo&action=news&add=yes" style="margin-top: 6px" class="btn btn-sm bg-blue"><i class="fa fa-plus position-left"></i>{$dleSeoLang['admin']['news']['add']}</a>
        </div>
    </div>
	<table class="table table-xs">
        <thead>
            <tr>
                <th>{$dleSeoLang['admin']['news']['cat']}</th>
                <th class="text-center">{$dleSeoLang['admin']['news']['data']}</th>
                <th class="text-center"><i class="fa fa-cogs"></i></th>
            </tr>
        </thead>
        <tbody id="listNews">
HTML;
            $allxField = xfieldsload();
            $xf = [];
            foreach ($allxField as $value) {
                if (in_array($value[3], ['htmljs', 'file', 'select', 'yesorno'])) {
                    continue;
                }

                $xf[$value[0]] = $value[1];
            }

            $jsonStat = [];

            while ($rowNews = $db->get_row($selectRuleNews)) {
                $catName = [];
                if ($rowNews['cats'] == 'all') {
                    $catName[] = $dleSeoLang['admin']['news']['_all_'];
                } else {
                    $rowNews['cats'] = explode(',', $rowNews['cats']);
                    foreach ($rowNews['cats'] as $catId) {
                        $catName[] = $cat_info[$catId]['name'] . ' (<b>ID</b>: ' . $catId . ')';
                    }
                }
                $catShow = implode(',', $catName);

                $meta = xfieldsdataload($rowNews['meta']);
                $og = xfieldsdataload($rowNews['og']);

                $value['meta_title'] = $meta['title'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['title'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
				$value['meta_key'] = $meta['meta_key'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['meta_key'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
                $value['meta_descr'] = $meta['descr'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['descr'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
                $value['meta_speedbar'] = $meta['speedbar'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['speedbar'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
                $value['meta_og_title'] = $og['title'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($og['title'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
                $value['meta_og_descr'] = $og['descr'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($og['descr'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
                if (in_array($og['image'], ['_none_', '_short_story_', '_full_story_'])) {
                    $value['meta_og_image'] = $dleSeoLang['admin']['news'][$og['image']];
                } else {
                    $value['meta_og_image'] = $dleSeoLang['admin']['news']['xf_image'] . $xf[$og['image']];
                }
$jsonStat[$rowNews['id']] = <<<HTML
<tr><td>{$dleSeoLang['admin']['news']['cat']}</td><td>{$catShow}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_title']}</td><td>{$value['meta_title']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_descr']}</td><td>{$value['meta_descr']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['meta_key']}</td><td>{$value['meta_key']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_speedbar']}</td><td>{$value['meta_speedbar']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_og_title']}</td><td>{$value['meta_og_title']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_og_descr']}</td><td>{$value['meta_og_descr']}</td></tr><tr><td>{$dleSeoLang['admin']['news']['seo_og_image']}</td><td>{$value['meta_og_image']}</td></tr>
HTML;

                if (count($catName) > 3) {
                    $tempCat = array_slice($catName, 0, 3);
                    $catShow = implode('<br>', $catName);
                    $catShow = implode(', ', $tempCat) . "...<i class=\"help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right\" data-html=\"true\" data-rel=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-content=\"{$catShow}\"></i>";
                }
                echo <<<HTML
<tr id="news_{$rowNews['id']}" data-id="{$rowNews['id']}">
    <td>{$catShow}</td>
    <td class="text-center"><input type="button" class="btn btn-sm bg-blue-800" style="border-radius: unset;" value="{$dleSeoLang['admin']['news']['look_param']}" onclick="showData({$rowNews['id']})"></td>
    <td class="text-center">
        <a href="{$PHP_SELF}?mod=dle_seo&action=news&add=yes&id={$rowNews['id']}" class="btn btn-primary btn-lazy-list"><i class="fa fa-pencil"></i></a>
        <a href="#" onclick="deleteRule({$rowNews['id']}); return false;" class="btn btn-danger btn-lazy-list"><i class="fa fa-trash"></i></a>
    </td>
</tr>
HTML;
            }
    if ($countNewsRules == 0) {
        echo <<<HTML
<tr>
    <td colspan="3" style="padding-right: 0!important;">
        <div class="col-md-12"><div style="margin: 0!important;" class="alert alert-warning alert-styled-left alert-arrow-left alert-component">{$dleSeoLang['admin']['news']['nothing']}</div></div>
    </td>
</tr>
HTML;
    }
echo <<<HTML
        </tbody>
    </table>
</div>
HTML;

$jsonStat = Helper::json($jsonStat);
$jsAdminScript[] = <<<HTML
function deleteRule(id) {
    DLEconfirm('{$dleSeoLang['admin']['news']['delete_text']}', '{$dleSeoLang['admin']['news']['delete_title']}', function() {
        $.post('engine/lazydev/dle_seo/admin/ajax/ajax.php', {action: 'deleteRule', id: id, dle_hash: dle_login_hash}, function(data) {
            data = jQuery.parseJSON(data);
            if (data.error) {
                Growl.error({
                    title: '{$dleSeoLang['admin']['news']['error']}',
                    text: data.text
                });
            } else {
                Growl.info({
                    title: '{$dleSeoLang['admin']['news']['successful']}',
                    text: data.text
                });
                
                $('#news_' + id).remove();
            }
        });
    });
}

jsonStat = {$jsonStat};
let showData = function(i) {
	$('#dlepopup').remove();
	let title = '{$dleSeoLang['admin']['news']['watch_dialog']}';
	if (jsonStat[i]) {
		$('body').append("<div id='dlepopup' class='dle-alert' title='"+ title + "' style='display:none'><div class='panel panel-flat'><div class='table-responsive'><table class='table'><thead><tr><th style='width:250px;'>{$dleSeoLang['admin']['news']['data']}</th><th>{$dleSeoLang['admin']['news']['value']}</th></tr></thead><tbody>"+jsonStat[i]+"</tbody></table></div></div></div>");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 800,
			resizable: false,
			dialogClass: 'modalfixed dle-popup-alert',
			buttons: {
				'{$dleSeoLang['admin']['news']['close']}': function() { 
					$(this).dialog('close');
					$('#dlepopup').remove();							
				} 
			}
		});

		$('.modalfixed.ui-dialog').css({
            position:'fixed',
            maxHeight:'600px',
            overflow:'auto'
		});
		
		$('#dlepopup').dialog('option', 'position', {
            my: 'center',
            at: 'center',
            of: window
		});
	}
};
HTML;
}