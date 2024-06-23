<?php
/**
 * Оптимизация категорий
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Seo\Helper;

$typeAction = stripslashes(strip_tags($_REQUEST['add']));

if ($typeAction == 'yes') {
    $idRule = 0;
    $catRule = $tempRule = [];

    if (isset($_GET['id'])) {
        $idRule = intval($_GET['id']);
        $catRule = $db->super_query("SELECT * FROM " . PREFIX .  "_dle_seo_cats WHERE id='{$idRule}'");
        $category = "<option value=\"all\" " . ('all' == $catRule['cats'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['cat']['_all_'] . "</option>" . CategoryNewsSelection(explode(',', $catRule['cats']), 0, false);

        $meta = xfieldsdataload($catRule['meta']);
        $og = xfieldsdataload($catRule['og']);

        $tempRule['meta_title'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['title'], false));

        $tempRule['meta_descr'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['descr'], false));
		$tempRule['meta_key'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['meta_key'], false));
        $tempRule['meta_speedbar'] = str_replace("&amp;", "&", $parse->decodeBBCodes($meta['speedbar'], false));

        $tempRule['meta_og_title'] = str_replace("&amp;", "&", $parse->decodeBBCodes($og['title'], false));
        $tempRule['meta_og_descr'] = str_replace("&amp;", "&", $parse->decodeBBCodes($og['descr'], false));
        $dleSeoLang['admin']['cat']['add_page'] = $dleSeoLang['admin']['cat']['edit_page'];

        if ($config['allow_admin_wysiwyg']) {
            $row['short_story'] = $parse->decodeBBCodes($catRule['text'], true, $config['allow_admin_wysiwyg']);
        } else {
            $row['short_story'] = $parse->decodeBBCodes($catRule['text'], false);
        }

        $tempRule['h1_title'] = str_replace("&amp;", "&", $parse->decodeBBCodes($catRule['title'], false));
    } else {
        $category = "<option value=\"all\">" . $dleSeoLang['admin']['cat']['_all_'] . "</option>" . CategoryNewsSelection(0, 0, false);
    }

    $ogType = [$og['type'] == 'website' ? 'selected' : '', $og['type'] == 'article' ? 'selected' : ''];

    $idSeo = 'cat_' . $idRule;
    $checkedChange = ($idRule > 0 ? $catRule['replacement'] : 1) ? ['checked', 'on'] : ['', ''];
    $additionalJsAdminScript[] = <<<HTML
<script>
media_upload = function (area, author, seoId, wysiwyg) {
    var shadow = 'none';

    $('#mediaupload').remove();
    $('body').append("<div id='mediaupload' title='"+dle_act_lang[4]+"' style='display:none'></div>");

    $('#mediaupload').dialog({
        autoOpen: true,
        width: 710,
        resizable: false,
        dialogClass: "modalfixed",
        open: function(event, ui) { 
            $("#mediaupload").html("<iframe name='mediauploadframe' id='mediauploadframe' width='100%' height='545' src='engine/lazydev/dle_seo/admin/ajax/upload.php?area=" + area + "&author=" + author + "&seoId={$idSeo}&wysiwyg=" + wysiwyg + "&dle_theme=" + dle_theme + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
            $('.ui-dialog').draggable('option', 'containment', '');
        },
        dragStart: function(event, ui) {
            shadow = $('.modalfixed').css('box-shadow');
            $('.modalfixed').fadeTo(0, 0.7).css('box-shadow', 'none');
            $('#mediaupload').css('visibility', 'hidden');
        },
        dragStop: function(event, ui) {
            $('.modalfixed').fadeTo(0, 1).css('box-shadow', shadow);
            $('#mediaupload').css('visibility', 'visible');
        },
        beforeClose: function(event, ui) { 
            $('#mediaupload').html('');
        }
    });

    if ($(window).width() > 830 && $(window).height() > 530) {
        $('.modalfixed.ui-dialog').css({
            position: 'fixed'
        });
        
        $('#mediaupload').dialog('option', 'position', {
            my: 'center',
            at: 'center',
            of: window
        });
    }

    return false;
};
</script>
HTML;

    $additionalJsAdminScript[] = "<script src=\"engine/classes/uploads/html5/fileuploader.js\"></script>";
    if ($config['allow_admin_wysiwyg'] == 0) {
        $additionalJsAdminScript[] = "<script src=\"engine/classes/js/typograf.min.js\"></script>";
    } elseif ($config['allow_admin_wysiwyg'] == 1) {
        $additionalJsAdminScript[] = "<script src=\"engine/skins/codemirror/js/code.js\"></script>";
        $additionalJsAdminScript[] = "<script src=\"engine/editor/jscripts/froala/editor.js\"></script>";
        $additionalJsAdminScript[] = "<script src=\"engine/editor/jscripts/froala/languages/{$lang['wysiwyg_language']}.js\"></script>";
        $additionalJsAdminScript[] = "<link href=\"engine/editor/jscripts/froala/css/editor.css\" rel=\"stylesheet\" />";
    } elseif ($config['allow_admin_wysiwyg'] == 2) {
        $additionalJsAdminScript[] = '<script src="engine/editor/jscripts/tiny_mce/tinymce.min.js"></script>';
    }
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
        <div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSeoLang['admin']['cat']['add_page']}</div>
        <div class="panel-body" style="padding: 0!important;">
            <div class="alert alert-component text-size-small" style="margin-bottom:0px!important;box-shadow:none!important;">
                <button style="border-radius: 0;background: #fff;border: 1px solid #006c96;color: #000;width: 100%;text-shadow: unset!important;" onclick="ShowHide(this); return false;" class="btn bg-teal btn-raised btn-sm">{$dleSeoLang['admin']['news']['button_show']}</button>
            </div>
            <table class="table table-normal table-hover" id="content_help" style="display: none;">
                <thead>
                    <tr>
                        <td>{$dleSeoLang['admin']['cat']['tags']['tag']}</td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['tag_descr']}</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 300px;"><b>{id}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['id']}</td>
                    </tr>
                    <tr>
                        <td><b>{name}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['name']}</td>
                    </tr>
                    <tr>
                        <td><b>{name low}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['name_low']}</td>
                    </tr>
                    <tr>
                        <td><b>{name up}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['name_up']}</td>
                    </tr>
                    <tr>
                        <td><b>{name case}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['name_case']}</td>
                    </tr>
                    <tr>
                        <td><b>{name first}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['name_first']}</td>
                    </tr>
                    <tr>
                        <td><b>{alt-name}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['alt_name']}</td>
                    </tr>
                    <tr>
                        <td><b>{page}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['page']}</td>
                    </tr>
                    <tr>
                        <td><b>[page] {$dleSeoLang['admin']['cat']['tags']['text']} [/page]</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['page_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[not-page] {$dleSeoLang['admin']['cat']['tags']['text']} [/not-page]</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['not_page_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[parent]{parent-id}[/parent]</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['parent_id']}</td>
                    </tr>
                    <tr>
                        <td><b>[parent]{parent-name}[/parent]</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['parent_name']}</td>
                    </tr>
                    <tr>
                        <td><b>{BREAK}</b></td>
                        <td>{$dleSeoLang['admin']['cat']['tags']['break']}</td>
                    </tr>
                    <tr>
                        <td style="border: none!important;"><b>{count}</b></td>
                        <td style="border: none!important;">{$dleSeoLang['admin']['cat']['tags']['count']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['cat']}</label>
                <div class="col-md-12"><br>
                    <select id="catSelect" name="cat[]" multiple>{$category}</select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['h1_title']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" id="h1_title" name="h1_title" value="{$tempRule['h1_title']}" maxlength="255">
                </div>
            </div>
                
            <div class="form-group editor-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['description']}</label>
                <div class="col-md-12"><br>
HTML;
    if ($config['allow_admin_wysiwyg'] == 2) {
        $config['bbimages_in_wysiwyg'] = true;
		$config['disable_short'] = false;
        include(DLEPlugins::Check(ENGINE_DIR . '/editor/shortnews.php'));
    } elseif ($config['allow_admin_wysiwyg'] == 1) {
        include ENGINE_DIR . '/lazydev/dle_seo/lib/froala.php';
    } elseif ($config['allow_admin_wysiwyg'] == 0) {
        $bb_editor = true;
        include(DLEPlugins::Check(ENGINE_DIR . '/inc/include/inserttag.php'));
        echo "<div class=\"editor-panel\"><div class=\"shadow-depth1\">{$bb_code}<textarea class=\"editor\" style=\"width:100%;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"short_story\" id=\"short_story\" >{$row['short_story']}</textarea></div></div><script>var selField  = \"short_story\";</script>";
    }
    echo <<<HTML
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['change']}</label>
                <div class="col-md-12" style="margin-top: 10px;">
                    <input class="checkBox" type="checkbox" id="change" name="change" value="1" {$checkedChange[0]}>
                    <div class="br-toggle br-toggle-success ' . $checkedChange[1] . '" data-id="change">
                        <div class="br-toggle-switch"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_title']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_title" value="{$tempRule['meta_title']}" maxlength="255">
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_descr']}</label>
                <div class="col-md-12">
                    <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="meta_descr" maxlength="300">{$tempRule['meta_descr']}</textarea>
                </div>
            </div>
            
			<div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_keys']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_key" value="{$tempRule['meta_key']}">
                </div>
            </div>

HTML;
    if ($config['speedbar']) {
        echo <<<HTML
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_speedbar']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_speedbar" value="{$tempRule['meta_speedbar']}">
                </div>
            </div>
HTML;
    }
    echo <<<HTML
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_og_title']}</label>
                <div class="col-md-12">
                    <input type="text" class="inputLazy" name="meta_og_title" value="{$tempRule['meta_og_title']}" maxlength="255">
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_og_descr']}</label>
                <div class="col-md-12">
                    <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="meta_og_descr" maxlength="300">{$tempRule['meta_og_descr']}</textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['seo_og_type']}</label>
                <div class="col-md-12">
                    <select name="meta_og_type" class="selectTag">
                        <option value="website" {$ogType[0]}>website</option>
                        <option value="article" {$ogType[1]}>atricle</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-md-12">{$dleSeoLang['admin']['cat']['og_image']}</label>
                <div class="col-md-12"><br>
                    <div id="xfupload_default_image"></div>
                    <input type="hidden" id="default_image" class="form-control" value="{$og['image']}" name="default_image">
HTML;
    $lang['wysiwyg_language'] = totranslit($lang['wysiwyg_language'], false, false);
    $member_id['name'] = urlencode($member_id['name']);

    if ($og['image']) {
        $img_url = 	$og['image'];
        $og['image'] = explode('/', $img_url);
        $og['image'] = end($og['image']);
        $up_image = "<div class=\"uploadedfile\"><div class=\"info\">{$og['image']}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"xfimagedelete(\\'default_image\\',\\'".$og['image']."\\');return false;\">{$lang['xfield_xfid']}</a></div></div>";
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
	    params: {'subaction': 'upload', 'idRuleCat': '{$idRule}', 'area': 'xfieldsimage', 'author': '{$member_id['name']}', 'xfname': 'default_image', 'user_hash': '{$dle_login_hash}'},
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
        $.post('engine/lazydev/dle_seo/admin/ajax/upload.php', {subaction: 'deluploads', user_hash: '{$dle_login_hash}', idRuleCat: '{$idRule}', author: '{$member_id['name']}', 'images[]' : xfvalue}, function(data) {
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

$('body').on('submit', 'form#formSeo', function(e) {
    e.preventDefault();
    
    if (!$('#catSelect').val()) {
        Growl.error({text: '{$dleSeoLang['admin']['cat']['not_choose_cat']}'});
        return;
    }
    
    let formData = $('form#formSeo').serializeArray();
    formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
    formData.push({name: 'action', value: 'addRuleCat'});
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

    $countCatRules = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_seo_cats {$where}")['count'];
    $categories_list = CategoryNewsSelection($search_field, 0);
    echo <<<HTML
<div class="modal fade" id="advancedsearch" role="dialog" aria-labelledby="advancedsearchLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="?mod=dle_seo&amp;action=cat" method="GET" name="optionsbar" id="optionsbar">
                <input type="hidden" name="mod" value="dle_seo">
                <input type="hidden" name="action" value="cat">
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
                    <button onclick="document.location='?mod=dle_seo&action=cat'; return(false);" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-eraser position-left"></i>{$dleSeoLang['admin']['find_clear']}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        {$dleSeoLang['admin']['cat']['list']} ({$dleSeoLang['admin']['cat']['all']}{$countCatRules})
        <div class="heading-elements">
            <a data-toggle="modal" data-target="#advancedsearch" href="#" style="margin-top: 6px" class="btn btn-sm bg-pink"><i class="fa fa-search position-left"></i>{$dleSeoLang['admin']['find_modal']}</a>
            <a href="{$PHP_SELF}?mod=dle_seo&action=cat&add=yes" style="margin-top: 6px" class="btn btn-sm bg-blue"><i class="fa fa-plus position-left"></i>{$dleSeoLang['admin']['cat']['add']}</a>
        </div>
    </div>
	<table class="table table-xs">
        <thead>
            <tr>
                <th>{$dleSeoLang['admin']['cat']['cat']}</th>
                <th class="text-center">{$dleSeoLang['admin']['cat']['data']}</th>
                <th class="text-center"><i class="fa fa-cogs"></i></th>
            </tr>
        </thead>
        <tbody id="listNews">
HTML;
    $jsonStat = [];
    $selectRuleCats = $db->query("SELECT * FROM " . PREFIX . "_dle_seo_cats {$where}");

    while ($rowCats = $db->get_row($selectRuleCats)) {
        $catName = [];
        if ($rowCats['cats'] == 'all') {
            $catName[] = $dleSeoLang['admin']['cat']['_all_'];
        } else {
            $rowCats['cats'] = explode(',', $rowCats['cats']);
            foreach ($rowCats['cats'] as $catId) {
                $catName[] = $cat_info[$catId]['name'] . ' (<b>ID</b>: ' . $catId . ')';
            }
        }
        $catShow = implode(',', $catName);

        $meta = xfieldsdataload($rowCats['meta']);
        $og = xfieldsdataload($rowCats['og']);

        $value['h1_title'] = $rowCats['title'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($rowCats['title'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['seoText'] = $rowCats['text'] != '' ? '<i class="fa fa-check" style="color: green;"></i>' : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_title'] = $meta['title'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['title'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
		$value['meta_key'] = $meta['meta_key'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['meta_key'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_descr'] = $meta['descr'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['descr'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_speedbar'] = $meta['speedbar'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($meta['speedbar'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_og_title'] = $og['title'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($og['title'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_og_descr'] = $og['descr'] != '' ? str_replace("&amp;", "&", $parse->decodeBBCodes($og['descr'], false)) : '<i class="fa fa-close" style="color: red;"></i>';
        $value['meta_og_image'] = $og['image'] != '' ? '<i class="fa fa-check" style="color: green;"></i>' : '<i class="fa fa-close" style="color: red;"></i>';

        $jsonStat[$rowCats['id']] = <<<HTML
<tr><td>{$dleSeoLang['admin']['cat']['cat']}</td><td>{$catShow}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['h1_title']}</td><td>{$value['h1_title']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['description']}</td><td>{$value['seoText']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_title']}</td><td>{$value['meta_title']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_descr']}</td><td>{$value['meta_descr']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_keys']}</td><td>{$value['meta_key']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_speedbar']}</td><td>{$value['meta_speedbar']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_og_title']}</td><td>{$value['meta_og_title']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_og_descr']}</td><td>{$value['meta_og_descr']}</td></tr><tr><td>{$dleSeoLang['admin']['cat']['seo_og_image']}</td><td>{$value['meta_og_image']}</td></tr>
HTML;

        if (count($catName) > 3) {
            $tempCat = array_slice($catName, 0, 3);
            $catShow = implode('<br>', $catName);
            $catShow = implode(', ', $tempCat) . "...<i class=\"help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right\" data-html=\"true\" data-rel=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-content=\"{$catShow}\"></i>";
        }

        echo <<<HTML
<tr id="cats_{$rowCats['id']}" data-id="{$rowCats['id']}">
    <td>{$catShow}</td>
    <td class="text-center"><input type="button" class="btn btn-sm bg-blue-800" style="border-radius: unset;" value="{$dleSeoLang['admin']['cat']['look_param']}" onclick="showData({$rowCats['id']})"></td>
    <td class="text-center">
        <a href="{$PHP_SELF}?mod=dle_seo&action=cat&add=yes&id={$rowCats['id']}" class="btn btn-primary btn-lazy-list"><i class="fa fa-pencil"></i></a>
        <a href="#" onclick="deleteRule({$rowCats['id']}); return false;" class="btn btn-danger btn-lazy-list"><i class="fa fa-trash"></i></a>
    </td>
</tr>
HTML;
    }
    if ($countCatRules == 0) {
        echo <<<HTML
<tr>
    <td colspan="3" style="padding-right: 0!important;">
        <div class="col-md-12"><div style="margin: 0!important;" class="alert alert-warning alert-styled-left alert-arrow-left alert-component">{$dleSeoLang['admin']['cat']['nothing']}</div></div>
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
    DLEconfirm('{$dleSeoLang['admin']['cat']['delete_text']}', '{$dleSeoLang['admin']['cat']['delete_title']}', function() {
        $.post('engine/lazydev/dle_seo/admin/ajax/ajax.php', {action: 'deleteRuleCat', id: id, dle_hash: dle_login_hash}, function(data) {
            data = jQuery.parseJSON(data);
            if (data.error) {
                Growl.error({
                    title: '{$dleSeoLang['admin']['cat']['error']}',
                    text: data.text
                });
            } else {
                Growl.info({
                    title: '{$dleSeoLang['admin']['cat']['successful']}',
                    text: data.text
                });
                
                $('#cats_' + id).remove();
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

?>