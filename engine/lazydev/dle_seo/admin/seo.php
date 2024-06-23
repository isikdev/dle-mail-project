
-----------------------------239<?php
/**
 * Оптимизация страниц тегов и дополнительных полей
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Seo\Helper;

$typeAction = stripslashes(strip_tags($_REQUEST['add']));
if ($typeAction == 'yes') {
    $id = $idSeo = intval($_GET['id']);
    $type = intval($_GET['type']);
    $row = [];
    $selectedVal = "<option value=\"_all_\">" . $dleSeoLang['admin']['seo']['_all_' . $type] . "</option>";
    if ($idSeo > 0) {
        $row = $db->super_query("SELECT * FROM " . PREFIX . "_dle_seo WHERE id='{$idSeo}'");
        if ($row) {
            $selectedVal = "<option value=\"_all_\" " . ('_all_' == $row['val'] ? 'selected' : '') . ">" . $dleSeoLang['admin']['seo']['_all_' . $type] . "</option>";
            if ('_all_' != $row['val']) {
                $row['val'] = explode(',', $row['val']);
                foreach ($row['val'] as $val) {
                    $val = htmlspecialchars($val);
                    $selectedVal .= "<option value=\"{$val}\" selected>" . $val . "</option>";
                }
            }

            $meta = xfieldsdataload($row['meta']);
            foreach ($meta as $key => $val) {
                $row[$key] = str_replace("&amp;","&", $parse->decodeBBCodes($val, false));
            }

            $og = xfieldsdataload($row['og']);
            foreach ($og as $key => $val) {
                $row[$key] = str_replace("&amp;","&", $parse->decodeBBCodes($val, false));
            }

            if ($config['allow_admin_wysiwyg']) {
                $row['short_story'] = $parse->decodeBBCodes($row['seoText'], true, $config['allow_admin_wysiwyg']);
            } else {
                $row['short_story'] = $parse->decodeBBCodes($row['seoText'], false);
            }
        }
    }
    $allxField = xfieldsload();
    $xfieldSelect = '';
    foreach ($allxField as $value) {
        if (in_array($value[3], ['htmljs', 'image', 'imagegalery', 'file'])) {
            continue;
        }
        if ($value[0] == $row['xfName']) {
            $xfieldSelect .= "<option value=\"{$value[0]}\" selected>" . htmlspecialchars($value[1]) . "</option>";
        } else {
            $xfieldSelect .= "<option value=\"{$value[0]}\">" . htmlspecialchars($value[1]) . "</option>";
        }
    }
    $modAdd = $type == 1 ? 'tag' : 'xfield';
    $dleSeoLang['admin']['seo']['add_page'] .= $type == 1 ? $dleSeoLang['admin']['seo']['add_tag_page'] : $dleSeoLang['admin']['seo']['add_xf_page'];
    $ogType = [$og['type'] == 'website' ? 'selected' : '', $og['type'] == 'article' ? 'selected' : ''];
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
    <input type="hidden" name="idSeo" value="{$idSeo}">
    
    <div class="panel panel-flat">
        <div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSeoLang['admin']['seo']['add_page']}</div>
        <div class="panel-body" style="padding: 0!important;">
        	<div class="alert alert-component text-size-small" style="margin-bottom:0px!important;box-shadow:none!important;">
                <button style="border-radius: 0;background: #fff;border: 1px solid #006c96;color: #000;width: 100%;text-shadow: unset!important;" onclick="ShowHide(this); return false;" class="btn bg-teal btn-raised btn-sm">{$dleSeoLang['admin']['news']['button_show']}</button>
            </div>
            <table class="table table-normal table-hover" id="content_help" style="display: none;">
                <thead>
                    <tr>
                        <td>{$dleSeoLang['admin']['seo']['tags']['tag']}</td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['tag_descr']}</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 300px;"><b>{value}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['value']}</td>
                    </tr>
                    <tr>
                        <td style="width: 300px;"><b>{value low}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['value_low']}</td>
                    </tr>
                    <tr>
                        <td style="width: 300px;"><b>{value up}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['value_up']}</td>
                    </tr>
                    <tr>
                        <td style="width: 300px;"><b>{value case}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['value_case']}</td>
                    </tr>
                    <tr>
                        <td style="width: 300px;"><b>{value first}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['value_first']}</td>
                    </tr>
                    <tr>
                        <td><b>{page}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['page']}</td>
                    </tr>
                    <tr>
                        <td><b>[page] {$dleSeoLang['admin']['seo']['tags']['text']} [/page]</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['page_block']}</td>
                    </tr>
                    <tr>
                        <td><b>[not-page] {$dleSeoLang['admin']['seo']['tags']['text']} [/not-page]</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['not_page_block']}</td>
                    </tr>
                    <tr>
                        <td><b>{count}</b></td>
                        <td>{$dleSeoLang['admin']['seo']['tags']['count']}</td>
                    </tr>
                    <tr>
                        <td style="border: none!important;"><b>{BREAK}</b></td>
                        <td style="border: none!important;">{$dleSeoLang['admin']['seo']['tags']['break']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="navbar navbar-default navbar-component navbar-xs" style="margin-bottom: 0px;">
	        <ul class="nav navbar-nav visible-xs-block">
		        <li class="full-width text-center"><a data-toggle="collapse" data-target="#navbar-filter">
		            <i class="fa fa-bars"></i></a>
                </li>
	        </ul>
            <div class="navbar-collapse collapse" id="navbar-filter">
                <ul class="nav navbar-nav">
                    <li class="active">
						<a onclick="ChangeOption(this, 'block_1');" class="tip">
                        <i class="fa fa-cog"></i> {$dleSeoLang['admin']['seo']['main']}</a>
                    </li>
					<li>
						<a onclick="ChangeOption(this, 'block_2');" class="tip">
                        <i class="fa fa-jsfiddle"></i> {$dleSeoLang['admin']['seo']['og']}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="panel-body">
        
            <div id="block_1">
            
HTML;
    if ($type == 2) {
        echo <<<HTML
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['xfName']}</label>
                    <div class="col-md-12"><br>
                        <select class="xfName" id="xfName" name="xf_name">{$xfieldSelect}</select>
                    </div>
                </div>
HTML;

    }
    echo <<<HTML
            
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['val']}</label>
                    <div class="col-md-12"><br>
                        <div id="searchVal">
                            <select class="searchVal" id="valSeo" name="valSeo[]" multiple>{$selectedVal}</select>
                            <div class="tail-move-container tail-select-container"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['seo_title']}</label>
                    <div class="col-md-12">
                        <input type="text" class="inputLazy" id="seo_title" name="seo_title" value="{$row['seoTitle']}" maxlength="255">
                    </div>
                </div>
                
                <div class="form-group editor-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['description']}</label>
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
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['meta_title']}</label>
                    <div class="col-md-12">
                        <input type="text" class="inputLazy" name="meta_title" value="{$row['meta_title']}" maxlength="255">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['meta_descr']}</label>
                    <div class="col-md-12"><br>
                        <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="meta_descr" maxlength="300">{$row['meta_descr']}</textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['meta_key']}</label>
                    <div class="col-md-12">
                        <input type="text" class="inputLazy" name="meta_key" value="{$row['meta_key']}">
                    </div>
                </div>
HTML;

if ($config['speedbar']) {
echo <<<HTML
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['meta_speedbar']}</label>
                    <div class="col-md-12">
                        <input type="text" class="inputLazy" name="meta_speedbar" value="{$row['meta_speedbar']}">
                    </div>
                </div>
HTML;
}
echo <<<HTML
            </div>
            <div id="block_2" style="display: none;">
            
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['og_title']}</label>
                    <div class="col-md-12">
                        <input type="text" class="inputLazy" name="og_title" value="{$row['title']}" maxlength="255">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['og_descr']}</label>
                    <div class="col-md-12"><br>
                        <textarea style="min-height:150px;min-width:100%;max-width:100%;" autocomplete="off" class="textLazy" name="og_descr" maxlength="300">{$row['description']}</textarea>
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
					<label class="control-label col-md-12">{$dleSeoLang['admin']['seo']['og_photo']}</label>
					<div class="col-md-12"><br>
						<div id="xfupload_photo"></div>
						<input type="hidden" id="photo" class="form-control" value="{$row['image']}" name="og_photo">
HTML;
    $lang['wysiwyg_language'] = totranslit($lang['wysiwyg_language'], false, false);
    $p_name = urlencode($member_id['name']);
    if ($row['image']) {
        $img_url = 	$config['http_home_url'] . 'uploads/dle_seo/' . $row['image'];
        $filename = explode('_', $row['image']);
        unset($filename[0]);
        $filename = implode('_', $filename);
        $up_image = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"xfimagedelete(\\'image\\',\\'".$row['image']."\\');return false;\">{$lang['xfield_xfid']}</a></div></div>";
    }

echo <<<HTML
<script>
function xfimagedelete(xfname, xfvalue) {
    DLEconfirm('{$lang['image_delete']}', '{$lang['p_info']}', function() {
        ShowLoading('');
        $.post('engine/lazydev/dle_seo/admin/ajax/upload.php', {type: 'image', subaction: 'deluploads', user_hash: '{$dle_login_hash}', seoId: '{$idSeo}', author: '{$p_name}', 'images[]' : xfvalue}, function(data) {
            HideLoading('');
            $('#uploadedfile_photo').html('');
            $('#photo').val('');
            $('#xfupload_photo .qq-upload-button, #xfupload_photo .qq-upload-button input').removeAttr('disabled');
        });
        
    });

    return false;
};

$(function() {
    new qq.FileUploader({
		element: document.getElementById('xfupload_photo'),
		action: 'engine/lazydev/dle_seo/admin/ajax/upload.php',
		maxConnections: 1,
		multiple: false,
		allowdrop: false,
		encoding: 'multipart',
        sizeLimit: {$config['max_up_size']} * 1024,
		allowedExtensions: ['gif', 'jpg', 'jpeg', 'png', 'webp', 'jfif'],
	    params: {'subaction': 'upload', 'seoId': '{$idSeo}', 'area': 'xfieldsimage', 'author': '{$p_name}', 'xfname': 'photo', 'user_hash': '{$dle_login_hash}'},
        template: '<div class="qq-uploader">' + 
                '<div id="uploadedfile_photo">{$up_image}</div>' +
                '<div class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$lang['xfield_xfim']}</div>' +
                '<ul class="qq-upload-list" style="display:none;"></ul>' + 
             '</div>',
		onSubmit: function(id, fileName) {
			$('<div id="uploadfile-'+id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+fileName+'</span>&nbsp;<span class="qq-status"><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span></span><div class="progress "><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_photo');
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
				$('#uploadedfile_photo').html( returnbox );
				$('#photo').val(returnval);

				$('#xfupload_photo .qq-upload-button, #xfupload_photo .qq-upload-button input').attr("disabled","disabled");
				
				setTimeout(function() {
					$('#uploadfile-'+id).fadeOut('slow', function() { $(this).remove(); });
				}, 1000);
			} else {
				$('#uploadfile-'+id+' .qq-status').html('{$lang['media_upload_st10']}');
				if (response.error) {
				    $('#uploadfile-'+id+' .qq-status').append('<br /><span class="text-danger">' + response.error + '</span>');
                }
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
	
	if ($('#photo').val() != '') {
		$('#xfupload_photo .qq-upload-button, #xfupload_photo .qq-upload-button input').attr("disabled","disabled");
	}
});
</script>
                    </div>
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
$('body').on('submit', 'form#formSeo', function(e) {
    e.preventDefault();
    
    if (!$('#valSeo').val()) {
        Growl.error({text: '{$dleSeoLang['admin']['seo']['not_choose_value']}'});
        return;
    }
    
    let formData = $('form#formSeo').serializeArray();
    formData.push({name: 'dle_hash', value: '{$dle_login_hash}'});
    formData.push({name: 'action', value: 'addSeo'});
    formData.push({name: 'type', value: '{$type}'});
    
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
                window.location.href = "{$PHP_SELF}?mod=dle_seo&action=info&info=" + data.type + "&type={$type}&id=" + data.id;
            }
        }
    });
});

let searchVal = tail.select('.searchVal', {
    search: true,
    multiSelectAll: true,
    placeholder: "{$dleSeoLang['admin']['seo']['enter']}",
    classNames: "default white",
    multiContainer: true,
    multiShowCount: false
});

if ($('#xfName').length) {
    let xfName = tail.select('.xfName');
}

$('#searchVal .search-input').autocomplete({
    source: function(request, response) {
        let dataName = $('#searchVal .search-input').val();
        let xfName = '';
        if ($('#xfName').length) {
            xfName = $('#xfName').val();
        }
        $.ajax({
            type: 'GET',
            url: 'engine/lazydev/dle_seo/admin/ajax/find.php?dle_hash={$dle_login_hash}&mode={$modAdd}&xf=' + xfName + '&query=' + dataName,
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            success: function (data) {
                let newAddItem = {};
                
                data.forEach(function(item) {
                    newAddItem[item.value] = { key: item.value, value: item.name, description: '' };
                });
                
                [].map.call(searchVal.e.querySelectorAll("[data-select-option='add']"), function(item) {
                    item.parentElement.removeChild(item);
                });
                [].map.call(searchVal.e.querySelectorAll("[data-select-optgroup='add']"), function(item) {
                    item.parentElement.removeChild(item);
                });
                
                let getOp = searchVal.options.items['#'];
                $.each(getOp, function(index, value) {
                    if (value.selected) {
                        newAddItem[value.key] = value;
                    }
                });
                
                let options = new tail.select.options(searchVal.e, searchVal);
                options.add(newAddItem);
                
                let map = {};
                $(options.element).find('option').each(function() {
                    if (map[this.value]) {
                        $(this).remove();
                    }
                    map[this.value] = true;
                });
                
                searchVal.options = options;
                searchVal.query(dataName);
            }
        });
    }
});
function ChangeOption(obj, selectedOption) {
    $('#navbar-filter li').removeClass('active');
    $(obj).parent().addClass('active');
    $('[id*=block_]').hide();
    $('#' + selectedOption).show();

    return false;
}
HTML;

} else {
    $dataPerPage = 25;
    if (isset($_REQUEST['cstart']) && $_REQUEST['cstart']) {
        $cstart = intval($_REQUEST['cstart']);
    } else {
        if (!isset($cstart) || $cstart < 1) {
            $cstart = 0;
        } else {
            $cstart = ($cstart - 1) * $dataPerPage;
        }
    }
    $i = $cstart;

    $urlsearch = '';
    $where = [];
    $search_field = trim(urldecode($_REQUEST['search_field']));
    if ($search_field) {
        $urlsearch = '&search_field=' . urlencode($search_field);
        $search_field = preg_replace('/\s+/u', '%', $db->safesql(addslashes(addslashes($search_field))));
        $where[] = "`val` like '%{$search_field}%' ";
    }

    $search_area = ['', '', ''];
    $_REQUEST['search_area'] = intval($_REQUEST['search_area']);
    $search_area[$_REQUEST['search_area']] = 'selected';
    if (!$search_area[0]) {
        $urlsearch .= '&search_area=' . urlencode($_REQUEST['search_area']);
        $where[] = "`type`=" . $_REQUEST['search_area'];
    }
    $where_search = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $allSeoRows = $db->super_query("SELECT COUNT(*) as count FROM ". PREFIX . "_dle_seo {$where_search}")['count'];
    $getSeo = $db->query("SELECT * FROM " . PREFIX . "_dle_seo {$where_search} ORDER BY id DESC LIMIT {$cstart},{$dataPerPage}");

    $search_field = trim(htmlspecialchars(urldecode($_REQUEST['search_field']), ENT_QUOTES, $config['charset']));

    echo <<<HTML
<style>
.panel-default > .panel-heading {
	color: #000!important;
    background-color: #ffffff!important;
    border-color: #a1a1a1!important;
    text-shadow: unset!important;
}
.panel-default > .panel-heading a {
	color: #fff!important;
}
</style>
<div class="modal fade" id="advancedsearch" role="dialog" aria-labelledby="advancedsearchLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="?mod=dle_seo&amp;action=seo" method="GET" name="optionsbar" id="optionsbar">
                <input type="hidden" name="mod" value="dle_seo">
                <input type="hidden" name="action" value="seo">
                <div class="modal-header ui-dialog-titlebar" style="border-radius: unset!important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <span class="ui-dialog-title" id="newcatsLabel">{$dleSeoLang['admin']['find_title']}</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>{$dleSeoLang['admin']['find_item']}</label>
                                <div class="input-group">
                                    <input name="search_field" value="{$search_field}" type="text" class="form-control">
                                    <span class="input-group-btn">
										<select name="search_area" class="uniform form-control">
											<option value="0" {$search_area[0]}>{$dleSeoLang['admin']['find_all']}</option>
											<option value="1" {$search_area[1]}>{$dleSeoLang['admin']['find_tag']}</option>
											<option value="2" {$search_area[2]}>{$dleSeoLang['admin']['find_xf']}</option>
										</select>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="search_submit(0); return(false);" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-search position-left"></i>{$dleSeoLang['admin']['find_button']}</button>
                    <button onclick="document.location='?mod=dle_seo&action=seo'; return(false);" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-eraser position-left"></i>{$dleSeoLang['admin']['find_clear']}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        {$dleSeoLang['admin']['seo']['list']} ({$dleSeoLang['admin']['seo']['all']}{$allSeoRows})
        <div class="heading-elements">
             <a data-toggle="modal" data-target="#advancedsearch" href="#" style="margin-top: 6px" class="btn btn-sm bg-pink"><i class="fa fa-search position-left"></i>{$dleSeoLang['admin']['find_modal']}</a>
            <a href="{$PHP_SELF}?mod=dle_seo&action=seo&add=yes&type=1" style="margin-top: 6px" class="btn btn-sm bg-blue"><i class="fa fa-plus position-left"></i>{$dleSeoLang['admin']['seo']['add_tag']}</a>
            <a href="{$PHP_SELF}?mod=dle_seo&action=seo&add=yes&type=2" style="margin-top: 6px" class="btn btn-sm bg-indigo"><i class="fa fa-plus position-left"></i>{$dleSeoLang['admin']['seo']['add_xf']}</a>
        </div>
    </div>
	<table class="table table-xs">
        <thead>
            <tr>
                <th>{$dleSeoLang['admin']['seo']['val']}</th>
                <th>{$dleSeoLang['admin']['seo']['type']}</th>
                <th class="text-center">{$dleSeoLang['admin']['seo']['data']}</th>
                <th class="text-center"><i class="fa fa-cogs"></i></th>
            </tr>
        </thead>
        <tbody id="listSeo">
HTML;
    $xfList = xfieldsload();
    while ($row = $db->get_row($getSeo)) {
        $i++;
        $type = $row['type'] == 1 ? $dleSeoLang['admin']['seo']['tag_type'] : $dleSeoLang['admin']['seo']['xf_type'];
        $row['og'] = $row['og'] != '' ? '<i class="fa fa-check" style="color: green;"></i>' : '<i class="fa fa-close" style="color: red;"></i>';

        $row['seoTitle'] = Helper::checkPopupData($row['seoTitle']);
        $row['seoText'] = $row['seoText'] != '' ? '<i class="fa fa-check" style="color: green;"></i>' : '<i class="fa fa-close" style="color: red;"></i>';

        if ($row['xfName']) {
            $xfRuName = array_filter($xfList, function ($k) use ($xfList, $row) {
                return $k[0] == $row['xfName'];
            });
            $xfRuName = array_values($xfRuName);
            $row['xfName'] = ' [ ' . $xfRuName[0][1] . ' ]';
        }

        $meta = xfieldsdataload($row['meta']);
        $meta['meta_title'] = Helper::checkPopupData($meta['meta_title']);
        $meta['meta_descr'] = Helper::checkPopupData($meta['meta_descr']);
        $meta['meta_key'] = Helper::checkPopupData($meta['meta_key']);
        if ($config['speedbar']) {
            $meta['meta_speedbar'] = Helper::checkPopupData($meta['meta_speedbar']);
            $meta['meta_speedbar'] = "<tr><td>{$dleSeoLang['admin']['seo']['meta_speedbar']}</td><td>{$meta['meta_speedbar']}</td></tr>";
        }

        $og = xfieldsdataload($row['og']);
        $og['title'] = Helper::checkPopupData($og['title']);
        $og['description'] = Helper::checkPopupData($og['description']);
        $og['image'] = $og['image'] != '' ? '<i class="fa fa-check" style="color: green;"></i>' : '<i class="fa fa-close" style="color: red;"></i>';

        if ($row['val'] == '_all_') {
            $tempVal = $row['val'] = $dleSeoLang['admin']['seo']['_all_' . $row['type']];
        } else {
            $valData = explode(',', $row['val']);
            $tempVal = $row['val'] = str_replace(',', ', ', $row['val']);
            if (count($valData) > 3) {
                $tempData = array_slice($valData, 0, 3);
                $row['val'] = implode(', ', $tempData) . "...<i class=\"help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right\" data-html=\"true\" data-rel=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-content=\"{$row['val']}\"></i>";
            }
        }
$jsonStat[$row['id']] = <<<HTML
<tr><td>{$dleSeoLang['admin']['seo']['type']}</td><td>{$type}{$row['xfName']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['val']}</td><td>{$tempVal}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['seo_title']}</td><td>{$row['seoTitle']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['description']}</td><td>{$row['seoText']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['meta_title']}</td><td>{$meta['meta_title']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['meta_descr']}</td><td>{$meta['meta_descr']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['meta_key']}</td><td>{$meta['meta_key']}</td></tr>{$meta['meta_speedbar']}<tr><td>{$dleSeoLang['admin']['seo']['og_title']}</td><td>{$og['title']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['og_descr']}</td><td>{$og['description']}</td></tr><tr><td>{$dleSeoLang['admin']['seo']['og_photo']}</td><td>{$og['image']}</td></tr>
HTML;

echo <<<HTML
<tr id="seo_{$row['id']}" data-id="{$row['id']}">
    <td>{$row['val']}</td>
    <td>{$type}{$row['xfName']}</td>
    <td class="text-center"><input type="button" class="btn btn-sm bg-blue-800" style="border-radius: unset;" value="{$dleSeoLang['admin']['seo']['look_param']}" onclick="showData({$row['id']})"></td>
    <td class="text-center">
        <a href="{$PHP_SELF}?mod=dle_seo&action=seo&add=yes&id={$row['id']}&type={$row['type']}" class="btn btn-primary btn-lazy-list"><i class="fa fa-pencil"></i></a>
        <a href="#" onclick="deleteSeo({$row['id']}); return false;" class="btn btn-danger btn-lazy-list"><i class="fa fa-trash"></i></a>
    </td>
</tr>
HTML;
    }
    if ($allSeoRows == 0) {
echo <<<HTML
<tr>
    <td colspan="4" style="padding-right: 0!important;">
        <div class="col-md-12"><div style="margin: 0!important;" class="alert alert-warning alert-styled-left alert-arrow-left alert-component">{$dleSeoLang['admin']['seo']['nothing']}</div></div>
    </td>
</tr>
HTML;
    }
echo <<<HTML
            </tbody>
		</table>

	</div>
</form>
HTML;

    $navigation = '';
    if ($allSeoRows > $dataPerPage) {
        if ($cstart > 0) {
            $previous = $cstart - $dataPerPage;
            if ($previous <= 0) {
                $previous = '';
            } else {
                $previous = '&cstart=' . $previous;
            }
            $navigation .= "<li><a href=\"$PHP_SELF?mod=dle_seo&action=seo{$previous}{$urlsearch}\" title=\"{$lang['edit_prev']}\"><i class=\"fa fa-backward\"></i></a></li>";
        }

        $enpages_count = ceil($allSeoRows / $dataPerPage);
        $enpages_start_from = 0;
        $enpages = '';

        if ($enpages_count <= 10) {
            for ($j = 1; $j <= $enpages_count; $j++) {
                if ($enpages_start_from != $cstart) {
                    $enpages .= "<li><a href=\"$PHP_SELF?mod=dle_seo&action=seo&cstart={$enpages_start_from}{$urlsearch}\">{$j}</a></li>";
                } else {
                    $enpages .= "<li class=\"active\"><span>{$j}</span></li>";
                }

                $enpages_start_from += $dataPerPage;
            }
            $navigation .= $enpages;
        } else {
            $start = 1;
            $end = 10;

            if ($cstart > 0) {
                if (($cstart / $dataPerPage) > 4) {
                    $start = ceil($cstart / $dataPerPage) - 3;
                    $end = $start + 9;

                    if ($end > $enpages_count) {
                        $start = $enpages_count - 10;
                        $end = $enpages_count - 1;
                    }

                    $enpages_start_from = ($start - 1) * $dataPerPage;
                }
            }

            if ($start > 2) {
                $enpages .= "<li><a href=\"$PHP_SELF?mod=dle_seo&action=seo{$urlsearch}\">1</a></li> <li><span>...</span></li>";
            }

            for ($j = $start; $j <= $end; $j++) {
                if ($enpages_start_from != $cstart) {
                    $enpages .= "<li><a href=\"$PHP_SELF?mod=dle_seo&action=seo&cstart={$enpages_start_from}{$urlsearch}\">{$j}</a></li>";
                } else {
                    $enpages .= "<li class=\"active\"><span>{$j}</span></li>";
                }

                $enpages_start_from += $dataPerPage;
            }

            $enpages_start_from = ($enpages_count - 1) * $dataPerPage;
            $enpages .= "<li><span>...</span></li><li><a href=\"$PHP_SELF?mod=dle_seo&action=seo&cstart={$enpages_start_from}{$urlsearch}\">{$enpages_count}</a></li>";

            $navigation .= $enpages;

        }

        if ($allSeoRows > $i) {
            $how_next = $allSeoRows - $i;
            if ($how_next > $dataPerPage) {
                $how_next = $dataPerPage;
            }

            $navigation .= "<li><a href=\"$PHP_SELF?mod=dle_seo&action=seo&cstart={$i}{$urlsearch}\" title=\"{$lang['edit_next']}\"><i class=\"fa fa-forward\"></i></a></li>";
        }

        echo "<ul class=\"pagination pagination-sm mb-20\">" . $navigation . "</ul>";
    }

$jsonStat = Helper::json($jsonStat);

$jsAdminScript[] = <<<HTML
function deleteSeo(id) {
    DLEconfirm('{$dleSeoLang['admin']['seo']['delete_text']}', '{$dleSeoLang['admin']['seo']['delete_title']}', function() {
        $.post('engine/lazydev/dle_seo/admin/ajax/ajax.php', {action: 'deleteSeo', id: id, dle_hash: dle_login_hash}, function(data) {
            data = jQuery.parseJSON(data);
            if (data.error) {
                Growl.error({
                    title: '{$dleSeoLang['admin']['seo']['error']}',
                    text: data.text
                });
            } else {
                Growl.info({
                    title: '{$dleSeoLang['admin']['seo']['successful']}',
                    text: data.text
                });
                
                $('#seo_' + id).remove();
            }
        });
    });
}

jsonStat = {$jsonStat};
let showData = function(i) {
	$('#dlepopup').remove();
	let title = "{$dleSeoLang['admin']['seo']['watch_dialog']}";
	if (jsonStat[i]) {
		$('body').append("<div id='dlepopup' class='dle-alert' title='"+ title + "' style='display:none'><div class='panel panel-flat'><div class='table-responsive'><table class='table'><thead><tr><th style='width:250px;'>{$dleSeoLang['admin']['seo']['data']}</th><th>{$dleSeoLang['admin']['seo']['value']}</th></tr></thead><tbody>"+jsonStat[i]+"</tbody></table></div></div></div>");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 800,
			resizable: false,
			dialogClass: 'modalfixed dle-popup-alert',
			buttons: {
				"{$dleSeoLang['admin']['seo']['close']}": function() { 
					$(this).dialog('close');
					$('#dlepopup').remove();							
				} 
			}
		});

		$('.modalfixed.ui-dialog').css({
		    position: 'fixed',
		    overflow: 'auto'
		});
		
		$('#dlepopup').dialog(
		    'option',
		    'position', {
		        my: 'center',
		        at: 'center',
		        of: window
		    }
		);
	}
};
HTML;
}