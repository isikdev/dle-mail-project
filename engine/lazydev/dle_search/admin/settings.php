<?php
/**
* Настройки модуля
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Search\Data;
use LazyDev\Search\Admin;

$allXfield = xfieldsload();

$categories = CategoryNewsSelection((empty($dleSearchConfigVar['exclude_categories']) ? 0 : $dleSearchConfigVar['exclude_categories']), 0, false);
$in_categories = CategoryNewsSelection((empty($dleSearchConfigVar['in_categories']) ? 0 : $dleSearchConfigVar['in_categories']), 0, false);

$sortField = [
    'p.date' => $dleSearchLangVar['admin']['settings']['p.date'],
    'p.editdate' => $dleSearchLangVar['admin']['settings']['e.editdate'],
    'p.title' => $dleSearchLangVar['admin']['settings']['p.title'],
    'p.autor' => $dleSearchLangVar['admin']['settings']['p.autor'],
    'p.rating' => $dleSearchLangVar['admin']['settings']['e.rating'],
    'p.comm_num' => $dleSearchLangVar['admin']['settings']['p.comm_num'],
    'p.news_read' => $dleSearchLangVar['admin']['settings']['e.news_read']
];
$order = [
    'desc' => $dleSearchLangVar['admin']['settings']['desc'],
    'asc' => $dleSearchLangVar['admin']['settings']['asc']
];
$rowsLang = [
    'p.title' => $dleSearchLangVar['admin']['settings']['p.title'],
    'p.short_story' => $dleSearchLangVar['admin']['settings']['p.short_story'],
    'p.full_story' => $dleSearchLangVar['admin']['settings']['p.full_story'],
    'p.metatitle' => $dleSearchLangVar['admin']['settings']['p.metatitle'],
    'p.descr' => $dleSearchLangVar['admin']['settings']['p.descr'],
    'p.tags' => $dleSearchLangVar['admin']['settings']['p.tags'],
];

$xfieldTArray = ['-' => '-'];
$xfieldArray = [];
$xfieldAArray = [];
foreach ($allXfield as $value) {
    $xfieldArray['xf_' . $value[0]] = $value[1];
    $xfieldAArray[$value[0]] = $xfieldTArray[$value[0]] = $value[1];
}

if ($xfieldArray) {
    $rowsLang = $rowsLang + $xfieldArray;
    $sortField = $sortField + $xfieldAArray;
}

$excludeNews = '';
if ($dleSearchConfigVar['excludeNews']) {
    $newsId = implode(',', $dleSearchConfigVar['excludeNews']);
    $db->query("SELECT id, title FROM " . PREFIX . "_post WHERE id IN({$newsId})");
    while ($row = $db->get_row()) {
        $row['title'] = str_replace("&quot;", '"', $row['title']);
        $row['title'] = str_replace("&#039;", "'", $row['title']);
        $row['title'] = stripslashes($row['title']);
        $excludeNews .= "<option value=\"{$row['id']}\" selected>" . $row['title'] . "</option>";
    }
}

echo <<<HTML
<form action="" method="post">
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
						<a onclick="ChangeOption(this, 'block_1');" class="tip">
                        <i class="fa fa-cog"></i> {$dleSearchLangVar['admin']['settings']['main']}</a>
                    </li>
					<li>
						<a onclick="ChangeOption(this, 'block_2');" class="tip">
                        <i class="fa fa-jsfiddle"></i> {$dleSearchLangVar['admin']['settings']['ajax']}</a>
                    </li>
                    <li>
						<a onclick="ChangeOption(this, 'block_3');" class="tip">
                        <i class="fa fa-search"></i> {$dleSearchLangVar['admin']['settings']['full']}</a>
                    </li>
                    <li>
						<a onclick="ChangeOption(this, 'block_4');" class="tip">
                        <i class="fa fa-ellipsis-h"></i> {$dleSearchLangVar['admin']['settings']['integration']}</a>
                    </li>
                </ul>
            </div>
        </div>
		<div id="block_1">
			<div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['settings']['main']}</div>
			<div class="table-responsive">
				<table class="table">
HTML;

$disabled = [
	'rows' => ['#match', '#match_all', '#concat', '#sort_by_own', '#sort_by_pos', '#sort_by_pos_xf', '#true_locate'],
	'match' => ['#concat', '#sort_by_own', '#sort_by_pos', '#sort_by_pos_xf', '#true_locate', '#keyboard', '#between_space', '#replace_char', '#replace_space', '#rows'],
	'match_all' => ['#concat', '#sort_by_own', '#sort_by_pos', '#sort_by_pos_xf', '#true_locate', '#keyboard', '#between_space', '#replace_char', '#replace_space', '#rows'],
	'concat' => ['#match', '#match_all', '#sort_by_pos', '#sort_by_pos_xf', '#rows'],
	'sort_by_own' => ['#match', '#match_all', '#sort_by_pos', '#sort_by_pos_xf', '#rows'],
	'sort_by_pos' => ['#match', '#match_all', '#concat', '#sort_by_own', '#sort_by_pos_xf', '#rows'],
	'sort_by_pos_xf' => ['#match', '#match_all', '#concat', '#sort_by_own', '#sort_by_pos', '#rows']
];

Admin::row(
    $dleSearchLangVar['admin']['settings']['rows'],
    $dleSearchLangVar['admin']['settings']['rows_descr'],
    Admin::selectIn(['rows', $rowsLang, $dleSearchConfigVar['rows'], true, false, $dleSearchLangVar['admin']['settings']['rows_placeholder'], $disabled]),
    $dleSearchLangVar['admin']['settings']['search_row_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['match'],
    $dleSearchLangVar['admin']['settings']['match_descr'],
    Admin::checkBox('match', $dleSearchConfigVar['match'], 'match', false, $disabled)
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['match_all'],
    $dleSearchLangVar['admin']['settings']['match_all_descr'],
    Admin::checkBox('match_all', $dleSearchConfigVar['match_all'], 'match_all', false, $disabled)
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['concat'],
    $dleSearchLangVar['admin']['settings']['concat_descr'],
    Admin::textarea(['concat', $dleSearchConfigVar['concat']], $disabled),
    $dleSearchLangVar['admin']['settings']['concat_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['sort_by_own'],
    $dleSearchLangVar['admin']['settings']['sort_by_own_descr'],
    Admin::checkBox('sort_by_own', $dleSearchConfigVar['sort_by_own'], 'sort_by_own', false, $disabled),
    $dleSearchLangVar['admin']['settings']['sort_by_pos_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['sort_by_pos'],
    $dleSearchLangVar['admin']['settings']['sort_by_pos_descr'],
    Admin::checkBox('sort_by_pos', $dleSearchConfigVar['sort_by_pos'], 'sort_by_pos', false, $disabled),
    $dleSearchLangVar['admin']['settings']['sort_by_pos_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['sort_by_pos_xf'],
    $dleSearchLangVar['admin']['settings']['sort_by_pos_xf_descr'],
    Admin::select(['sort_by_pos_xf', $xfieldTArray, true, $dleSearchConfigVar['sort_by_pos_xf'], false, false, $dleSearchLangVar['admin']['settings']['pos_xf_placeholder']], false, $disabled),
    $dleSearchLangVar['admin']['settings']['sort_by_pos_xf_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['true_locate'],
    $dleSearchLangVar['admin']['settings']['true_locate_descr'],
    Admin::checkBox('true_locate', $dleSearchConfigVar['true_locate'], 'true_locate')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['keyboard'],
    $dleSearchLangVar['admin']['settings']['keyboard_descr'],
    Admin::checkBox('keyboard', $dleSearchConfigVar['keyboard'], 'keyboard')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['between_space'],
    $dleSearchLangVar['admin']['settings']['between_space_descr'],
    Admin::checkBox('between_space', $dleSearchConfigVar['between_space'], 'between_space')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['replace_char'],
    $dleSearchLangVar['admin']['settings']['replace_char_descr'],
    Admin::textarea(['replace_char', $dleSearchConfigVar['replace_char']])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['replace_space'],
    $dleSearchLangVar['admin']['settings']['replace_space_descr'],
    Admin::checkBox('replace_space', $dleSearchConfigVar['replace_space'], 'replace_space')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['substitution'],
    $dleSearchLangVar['admin']['settings']['substitution_descr'],
    Admin::checkBox('substitution', $dleSearchConfigVar['substitution'], 'substitution')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['cache'],
    $dleSearchLangVar['admin']['settings']['cache_descr'],
    Admin::checkBox('cache', $dleSearchConfigVar['cache'], 'cache'),
    $dleSearchLangVar['admin']['settings']['cache_helper']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['statistics'],
    $dleSearchLangVar['admin']['settings']['statistics_descr'],
    Admin::checkBox('statistics', $dleSearchConfigVar['statistics'], 'statistics')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['clear_statistics'],
    $dleSearchLangVar['admin']['settings']['clear_statistics_descr'],
    Admin::input(['clear_statistics', 'number', $dleSearchConfigVar['clear_statistics'] ?: 0, false, false, 0, 30])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['exclude_categories'],
    $dleSearchLangVar['admin']['settings']['exclude_categories_descr'],
    Admin::selectTag('exclude_categories[]', $categories, $dleSearchLangVar['admin']['settings']['categories'])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['in_categories'],
    $dleSearchLangVar['admin']['settings']['in_categories_descr'],
    Admin::selectTag('in_categories[]', $in_categories, $dleSearchLangVar['admin']['settings']['categories'])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['exclude_news'],
    $dleSearchLangVar['admin']['settings']['exclude_news_descr'],
    "<div id=\"searchVal\">
        <select class=\"excludeNews\" id=\"excludeNews\" name=\"excludeNews[]\" multiple>{$excludeNews}</select>
    </div>"
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['minimum_char'],
    $dleSearchLangVar['admin']['settings']['minimum_char_descr'],
    Admin::input(['minimum_char', 'number', $dleSearchConfigVar['minimum_char'] ?: 3, false, false, 1, 100])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['maximum_char'],
    $dleSearchLangVar['admin']['settings']['maximum_char_descr'],
    Admin::input(['maximum_char', 'number', $dleSearchConfigVar['maximum_char'] ?: 15, false, false, 1, 100])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['all_date'],
    $dleSearchLangVar['admin']['settings']['all_date_descr'],
    Admin::checkBox('all_date', $dleSearchConfigVar['all_date'], 'all_date')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['allow_main'],
    $dleSearchLangVar['admin']['settings']['allow_main_descr'],
    Admin::checkBox('allow_main', $dleSearchConfigVar['allow_main'], 'allow_main')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['fixed'],
    $dleSearchLangVar['admin']['settings']['fixed_descr'],
    Admin::checkBox('fixed', $dleSearchConfigVar['fixed'], 'fixed')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['sort_field'],
    $dleSearchLangVar['admin']['settings']['sort_field_descr'],
    Admin::selectIn(['sort_field', $sortField, $dleSearchConfigVar['sort_field'], false, false], false),
    $dleSearchLangVar['admin']['settings']['sort_field_helper_2']
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['order'],
    $dleSearchLangVar['admin']['settings']['order_descr'],
    Admin::select(['order', $order, true, $dleSearchConfigVar['order'], false, false])
);
echo <<<HTML
				</table>
			</div>
		</div>
		
		
		<div id="block_2" style='display:none'>
			<div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['settings']['ajax']}</div>
			<div class="table-responsive">
				<table class="table">
HTML;
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_on'],
    $dleSearchLangVar['admin']['settings']['ajax_on_descr'],
    Admin::checkBox('ajax_on', $dleSearchConfigVar['ajax_on'], 'ajax_on')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['maximum_news_ajax'],
    $dleSearchLangVar['admin']['settings']['maximum_news_ajax_descr'],
    Admin::input(['maximum_news_ajax', 'number', $dleSearchConfigVar['maximum_news_ajax'] ?: 5, false, false, 1, 100])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_category'],
    $dleSearchLangVar['admin']['settings']['ajax_category_descr'],
    Admin::checkBox('ajax_category', $dleSearchConfigVar['ajax_category'], 'ajax_category')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_category_all'],
    $dleSearchLangVar['admin']['settings']['ajax_category_all_descr'],
    Admin::checkBox('ajax_category_all', $dleSearchConfigVar['ajax_category_all'], 'ajax_category_all')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_tags'],
    $dleSearchLangVar['admin']['settings']['ajax_tags_descr'],
    Admin::checkBox('ajax_tags', $dleSearchConfigVar['ajax_tags'], 'ajax_tags')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_xfield'],
    $dleSearchLangVar['admin']['settings']['ajax_xfield_descr'],
    Admin::checkBox('ajax_xfield', $dleSearchConfigVar['ajax_xfield'], 'ajax_xfield')
);
echo <<<HTML
				</table>
			</div>
		</div>

	    <div id="block_3" style='display:none'>
			<div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['settings']['full']}</div>
			<div class="table-responsive">
				<table class="table">
HTML;
Admin::row(
    $dleSearchLangVar['admin']['settings']['full_on'],
    $dleSearchLangVar['admin']['settings']['full_on_descr'],
    Admin::checkBox('full_on', $dleSearchConfigVar['full_on'], 'full_on')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['url_on'],
    $dleSearchLangVar['admin']['settings']['url_on_descr'],
    Admin::checkBox('url_on', $dleSearchConfigVar['url_on'], 'url_on')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['maximum_news_full'],
    $dleSearchLangVar['admin']['settings']['maximum_news_full_descr'],
    Admin::input(['maximum_news_full', 'number', $dleSearchConfigVar['maximum_news_full'] ?: 10, false, false, 1, 100])
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['full_category'],
    $dleSearchLangVar['admin']['settings']['full_category_descr'],
    Admin::checkBox('full_category', $dleSearchConfigVar['full_category'], 'full_category')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['ajax_category_all'],
    $dleSearchLangVar['admin']['settings']['ajax_category_all_descr'],
    Admin::checkBox('full_category_all', $dleSearchConfigVar['full_category_all'], 'full_category_all')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['full_tags'],
    $dleSearchLangVar['admin']['settings']['full_tags_descr'],
    Admin::checkBox('full_tags', $dleSearchConfigVar['full_tags'], 'full_tags')
);
Admin::row(
    $dleSearchLangVar['admin']['settings']['full_xfield'],
    $dleSearchLangVar['admin']['settings']['full_xfield_descr'],
    Admin::checkBox('full_xfield', $dleSearchConfigVar['full_xfield'], 'full_xfield')
);
echo <<<HTML
				</table>
			</div>
		</div>
		
	    <div id="block_4" style='display:none'>
			<div class="panel-body" style="font-size:15px; font-weight:bold;">{$dleSearchLangVar['admin']['settings']['integration_text']}</div>
			<div class="table-responsive">
				<table class="table">
HTML;
if (file_exists(ENGINE_DIR . '/lazydev/dle_youwatch/index.php')) {
    Admin::row(
        $dleSearchLangVar['admin']['settings']['youwatch'],
        $dleSearchLangVar['admin']['settings']['integration_install_descr'],
        "<i class=\"fa fa-check\" style=\"font-size: 20px;color: #26a65b;\"></i>"
    );
}
if (file_exists(ENGINE_DIR . '/lazydev/dle_emote_lite/index.php')) {
    Admin::row(
        $dleSearchLangVar['admin']['settings']['emote'],
        $dleSearchLangVar['admin']['settings']['integration_install_descr'],
        "<i class=\"fa fa-check\" style=\"font-size: 20px;color: #26a65b;\"></i>"
    );
}
if (file_exists(ENGINE_DIR . '/lazydev/dle_conditions/dle_conditions.php')) {
    Admin::row(
        $dleSearchLangVar['admin']['settings']['conditions'],
        $dleSearchLangVar['admin']['settings']['integration_install_descr'],
        "<i class=\"fa fa-check\" style=\"font-size: 20px;color: #26a65b;\"></i>"
    );
}
if (file_exists(ENGINE_DIR . '/mods/miniposter/loader.php')) {
    Admin::row(
        $dleSearchLangVar['admin']['settings']['miniposter3'],
        $dleSearchLangVar['admin']['settings']['integration_install_descr'],
        "<i class=\"fa fa-check\" style=\"font-size: 20px;color: #26a65b;\"></i>"
    );
}
if (file_exists(ENGINE_DIR . '/mods/favorites/index.php')) {
    Admin::row(
        $dleSearchLangVar['admin']['settings']['fav_san'],
        $dleSearchLangVar['admin']['settings']['integration_install_descr'],
        "<i class=\"fa fa-check\" style=\"font-size: 20px;color: #26a65b;\"></i>"
    );
}
echo <<<HTML
                        <tr>
                        <td colspan="2">
                            <div class="alert alert-info alert-styled-left alert-arrow-left alert-component">{$dleSearchLangVar['admin']['settings']['integration_descr']}</div>
                        </td>
                    </tr>
				</table>
			</div>
		</div>
		
		<div class="panel-footer">
			<button type="submit" class="btn bg-teal btn-raised position-left" style="background-color:#1e8bc3;">{$dleSearchLangVar['admin']['save']}</button>
		</div>
    </div>
</form>
HTML;

$jsAdminScript[] = <<<HTML

$(function() {
    $('body').on('submit', 'form', function(e) {
        coreAdmin.ajaxSend($('form').serialize(), 'saveOptions', false);
		return false;
    });
    
    $('body').find('[data-dis]').each(function(index, e) {
    	let dis = $(this).data('dis');

    	$(dis).prop('disabled', false).attr('disabled', false);
    	tail.select('select#rows').enable();
		tail.select('select#sort_by_pos_xf').enable();
    	if (e.type == 'checkbox') {
    		if ($(this).prop('checked') == true) {
				$(dis).prop('disabled', true).attr('disabled', 'disabled');
				tail.select('select#rows').disable();
				tail.select('select#sort_by_pos_xf').disable();
				return false;
    		}
    	} else if (e.tagName == 'TEXTAREA') {
    		if ($(this).val().trim() != '') {
    			$(dis).prop('disabled', true).attr('disabled', 'disabled');
				tail.select('select#rows').disable();
				tail.select('select#sort_by_pos_xf').disable();
				return false;
    		}
    	} else if (e.tagName == 'SELECT') {
    		if ($(this).val() && $(this).val() !== '-') {
				$(dis).prop('disabled', true).attr('disabled', 'disabled');
				let wichselect = e.id == 'rows' ? 'sort_by_pos_xf' : 'rows';
				tail.select('select#' + wichselect).disable();
				return false;
			}
    	}
    });
    
    $('body').on('change', '[data-dis]', function(e) {
    	let dis = $(this).data('dis');
    	
    	$(dis).prop('disabled', false).attr('disabled', false);
    	tail.select('select#rows').enable();
		tail.select('select#sort_by_pos_xf').enable();
    	if (e.target.type == 'checkbox') {
    		if ($(this).prop('checked') == true) {
				$(dis).prop('disabled', true).attr('disabled', 'disabled');
				tail.select('select#rows').disable();
				tail.select('select#sort_by_pos_xf').disable();
    		}
    	} else if (e.target.tagName == 'TEXTAREA') {
    		if ($(this).val().trim() != '') {
    			$(dis).prop('disabled', true).attr('disabled', 'disabled');
				tail.select('select#rows').disable();
				tail.select('select#sort_by_pos_xf').disable();
    		}
    	} else if (e.target.tagName == 'SELECT') {
    		if ($(this).val()) {
				$(dis).prop('disabled', true).attr('disabled', 'disabled');
				let wichselect = e.target.id == 'rows' ? 'sort_by_pos_xf' : 'rows';
				tail.select('select#' + wichselect).disable();
			}
    	}
    });
    
});

function ChangeOption(obj, selectedOption) {
    $('#navbar-filter li').removeClass('active');
    $(obj).parent().addClass('active');
    $('[id*=block_]').hide();
    $('#' + selectedOption).show();

    return false;
}

let excludeNews = tail.select('.excludeNews', {
    search: true,
    multiSelectAll: true,
    placeholder: "{$dleSearchLangVar['admin']['settings']['enter']}",
    classNames: "default white",
    multiContainer: true,
    multiShowCount: false,
    locale: "ru"
});

$('#searchVal .search-input').autocomplete({
    source: function(request, response) {
        let dataName = $('#searchVal .search-input').val();
        $.post('engine/lazydev/dle_search/admin/ajax/ajax.php', {dle_hash: "{$dle_login_hash}", query: dataName, action: 'findNews'}, function(data) {
            data = jQuery.parseJSON(data);
            let newAddItem = {};

            data.forEach(function(item) {
                newAddItem[item.value] = { key: item.value, value: item.name, description: '' };
            });
            
            [].map.call(excludeNews.e.querySelectorAll("[data-select-option='add']"), function(item) {
                item.parentElement.removeChild(item);
            });
            [].map.call(excludeNews.e.querySelectorAll("[data-select-optgroup='add']"), function(item) {
                item.parentElement.removeChild(item);
            });
            
            let getOp = excludeNews.options.items['#'];
            $.each(getOp, function(index, value) {
                if (value.selected) {
                    newAddItem[value.key] = value;
                }
            });
            
            let options = new tail.select.options(excludeNews.e, excludeNews);
            options.add(newAddItem);
            
            let map = {};
            $(options.element).find('option').each(function() {
                if (map[this.value]) {
                    $(this).remove();
                }
                map[this.value] = true;
            });
            
            excludeNews.options = options;
            excludeNews.query(dataName);
        });
        
    }
});

HTML;

?>