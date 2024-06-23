<?php
/**
 * Статиска
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Search\Data;
use LazyDev\Search\Admin;
use LazyDev\Search\Helper;

$allXfield = xfieldsload();
foreach ($allXfield as $value) {
    $xfieldArray[$value[0]] = $value[1];
}

$sortField = [
    'date' => $dleSearchLangVar['admin']['statistics']['sort']['date'],
    'editdate' => $dleSearchLangVar['admin']['statistics']['sort']['editdate'],
    'title' => $dleSearchLangVar['admin']['statistics']['sort']['title'],
    'autor' => $dleSearchLangVar['admin']['statistics']['sort']['autor'],
    'rating' => $dleSearchLangVar['admin']['statistics']['sort']['rating'],
    'comm_num' => $dleSearchLangVar['admin']['statistics']['sort']['comm_num'],
    'news_read' => $dleSearchLangVar['admin']['statistics']['sort']['news_read']
];

if ($xfieldArray) {
    $sortField = $sortField + $xfieldArray;
}

$order = [
    'desc' => $dleSearchLangVar['admin']['settings']['desc'],
    'asc' => $dleSearchLangVar['admin']['settings']['asc']
];

$searchStringArray = ['login', 'query', 'dateFrom', 'dateTo', 'ip'];
$searchIntArray = ['check_name', 'check_query', 'check_reg', 'check_guest', 'check_found', 'check_miss'];

$searchIntArray = array_flip($searchIntArray); $searchStringArray = array_flip($searchStringArray);
$searchKey = [];
$inputKey = [];
foreach ($_GET as $key => $value) {
    if (isset($searchStringArray[$key])) {
        $searchKey[$key] = $db->safesql(strip_tags(stripslashes(trim($value))));
        $inputKey[$key] = stripslashes($searchKey[$key]);
    } elseif (isset($searchIntArray[$key]) && $value > 0) {
        $searchKey[$key] = $db->safesql(intval(trim($value)));
        $inputKey[$key] = stripslashes($searchKey[$key]);
    }
}

$urlNav = [];
foreach ($searchKey as $key => $value) {
    if ($value != '') {
        $urlNav[] = urlencode($key) . '=' . $value;
    }
}
$urlNav = $urlNav ? '&' . implode('&', $urlNav) : '';

$whereSearch = [];

$inputKey['check_name'] = $searchKey['check_name'] == 1 ? 'checked' : '';
$inputKey['check_query'] = $searchKey['check_query'] == 1 ? 'checked' : '';
$inputKey['check_reg'] = $searchKey['check_reg'] == 1 ? 'checked' : '';
$inputKey['check_guest'] = $searchKey['check_guest'] == 1 ? 'checked' : '';
$inputKey['check_found'] = $searchKey['check_found'] == 1 ? 'checked' : '';
$inputKey['check_miss'] = $searchKey['check_miss'] == 1 ? 'checked' : '';

$selectGroupSearch = [];
$selectGroupSearch[] = "<option value=\"0\" " . (!intval($_GET['group']) ? 'selected' : '') . " >" . $dleSearchLangVar['admin']['statistics']['search_form']['allGroup'] . "</option>";
foreach ($user_group as $id => $groupArray) {
    $selectGroupSearch[] = "<option value=\"{$groupArray['id']}\" " . (intval($_GET['group']) == $groupArray['id'] ? 'selected' : '') . ">" . $groupArray['group_name'] . "</option>";
}

if ($searchKey['login']) {
    $whereSearch[] = $searchKey['check_name'] == 1 ? "u.name='{$searchKey['login']}'" : "u.name LIKE '%{$searchKey['login']}%'";
}

if ($searchKey['query']) {
    $whereSearch[] = $searchKey['check_query'] == 1 ? "s.search='{$searchKey['query']}'" : "s.search LIKE '%{$searchKey['query']}%'";
}

if ($searchKey['dateFrom']) {
    $whereSearch[] = "s.date >= '{$searchKey['dateFrom']}'";
}

if ($searchKey['dateTo']) {
    $whereSearch[] = "s.date <= '{$searchKey['dateTo']}'";
}

if ($searchKey['ip']) {
    $whereSearch[] = "s.ip LIKE '%{$searchKey['ip']}%'";
}

if ($searchKey['check_reg']) {
    $whereSearch[] = "s.userId != -1";
}

if ($searchKey['check_guest']) {
    $whereSearch[] = "s.userId = -1";
}

if ($searchKey['check_found']) {
    $whereSearch[] = "s.found = 1";
}

if ($searchKey['check_miss']) {
    $whereSearch[] = "s.found = 0";
}

$selectGroupSearch = implode($selectGroupSearch);

if ($whereSearch) {
    $whereSearch = " WHERE " . implode(' AND ', $whereSearch);
} else {
    $whereSearch = '';
}

$allFilterData = $db->super_query("SELECT COUNT(*) as count FROM `" . PREFIX . "_dle_search_statistics` s LEFT JOIN `" . PREFIX . "_users` u ON(s.userId=u.user_id) {$whereSearch}")['count'];

$startFrom = 0;
if (isset($_GET['start_from']) && $_GET['start_from']) {
    $startFrom = intval($_GET['start_from']);
}

$dataPerPage = 10;
$i = $startFrom;

$sql = $db->query("SELECT s.*, u.name FROM `" . PREFIX . "_dle_search_statistics` s LEFT JOIN `" . PREFIX . "_users` u ON(s.userId=u.user_id) {$whereSearch} ORDER BY `date` DESC LIMIT {$startFrom},{$dataPerPage}");

echo <<<HTML
<form name="searchform" id="searchform" method="GET" action="?mod=dle_search&action=statistics" class="form-horizontal">
    <input type="hidden" name="action" value="statistics">
    <input type="hidden" name="mod" value="dle_search">
    <div class="panel panel-default">
        <div class="panel-heading">{$dleSearchLangVar['admin']['statistics']['search_form']['search']}</div>
        
        <div class="panel-body">
        
            <div class="col-md-5">
            
                <div class="form-group">
                    <label class="control-label col-md-2">{$dleSearchLangVar['admin']['statistics']['search_form']['login']}</label>
                    <div class="col-md-10">
                        <div class="input-group">
                            <input class="form-control" type="text" name="login" id="login" value="{$inputKey['login']}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2">{$dleSearchLangVar['admin']['statistics']['search_form']['query']}</label>
                    <div class="col-md-10">
                        <div class="input-group">
                            <input class="form-control" type="text" name="query" id="query" value="{$inputKey['query']}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2">{$dleSearchLangVar['admin']['statistics']['search_form']['group']}</label>
                    <div class="col-md-10">
                        <select class="selectTag" name="group" id="group">
                            {$selectGroupSearch}
                        </select>
                    </div>
                </div>

               
                
            </div>
            <div class="col-md-7">
            
                <div class="form-group">
                    <label class="control-label col-md-2">{$dleSearchLangVar['admin']['statistics']['search_form']['date']}</label>
                    <div class="col-md-10">
                    {$dleSearchLangVar['admin']['statistics']['search_form']['from']}&nbsp;<input class="form-control" style="width:160px;" type="text" name="dateFrom" id="dateFrom" value="{$inputKey['dateFrom']}" autocomplete="off">
                    {$dleSearchLangVar['admin']['statistics']['search_form']['to']}&nbsp;<input class="form-control" style="width:160px;" type="text" name="dateTo" id="dateTo" value="{$inputKey['dateTo']}" autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-md-2">{$dleSearchLangVar['admin']['statistics']['search_form']['ip']}</label>
                    <div class="col-md-10">
                        <div class="input-group">
                            <input class="form-control" style="width:360px;" type="text" name="ip" id="ip" value="{$inputKey['ip']}">
                        </div>
                    </div>
                </div>
                
            </div>
            
            <div class="col-md-12">
            
                <div class="col-md-4">
                    <div class="form-group">
                    
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" id="check_name" name="check_name" value="1" {$inputKey['check_name']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_name']}</label>
                        </div>
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" name="check_query" value="1" {$inputKey['check_query']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_query']}</label>
                        </div>
                        
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                    
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" name="check_reg" id="check_reg" value="1" {$inputKey['check_reg']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_reg']}</label>
                        </div>
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" name="check_guest" value="1" {$inputKey['check_guest']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_guest']}</label>
                        </div>
                        
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                    
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" name="check_found" id="check_found" value="1" {$inputKey['check_found']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_found']}</label>
                        </div>
                        <div class="checkbox">
                            <label><input class="icheck" type="checkbox" name="check_miss" value="1" {$inputKey['check_miss']}>{$dleSearchLangVar['admin']['statistics']['search_form']['check_miss']}</label>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        
        
        </div>
        <div class="panel-footer">
			<input type="submit" class="btn bg-teal btn-sm btn-raised position-left" value="{$dleSearchLangVar['admin']['statistics']['search_form']['search']}">
			<input type="button" onclick="clearform();" class="btn bg-danger btn-sm btn-raised position-left" value="{$dleSearchLangVar['admin']['statistics']['search_form']['clear']}">
	   </div>
    </div>
</form>
<script>
function clearform() {
	$('#searchform input:not(:hidden):not([type="submit"]):not([type="button"]):not([type="reset"]), #searchform select').each(function() {
		let elementType = $(this).prop('nodeName');

		if (elementType === 'INPUT') {
			if ($(this).attr('type') === 'text') {
				$(this).val('');
			} else if ($(this).attr('type') === 'checkbox') {
				$(this).prop('checked', false).uniform('refresh');
			}
		} else if (elementType === 'SELECT') {
			let idSelect = $(this).attr('id');
			$('#' + idSelect + ' > option').removeAttr("selected");
			$('#' + idSelect).val($('#' + idSelect + ' option:first').val()).selectpicker('refresh');
		}
	});
}
</script>
HTML;

if ($db->num_rows()) {

echo <<<HTML
<script>
$(function() {
	$('#dateFrom').datetimepicker({
		format: 'Y-m-d',
		onShow: function(ct) {
			this.setOptions({
				maxDate: $('#dateTo').val() || false
			})
		},
		closeOnDateSelect: true,
		scrollMonth: false,
		scrollInput: false,
		i18n: cal_language,
        timepicker: false
	});
	
	$('#dateTo').datetimepicker({
		format: 'Y-m-d',
		onShow: function(ct) {
			this.setOptions({
				minDate: $('#dateFrom').val() || false
			})
		},
		closeOnDateSelect: true,
		scrollMonth: false,
		scrollInput: false,
		i18n: cal_language,
        timepicker: false
	});
});
</script>
HTML;

echo <<<HTML
<div class="panel panel-flat">
	<div class="panel-body" style="font-size:20px; font-weight:bold;border-bottom: 1px solid #ddd;">
		{$dleSearchLangVar['admin']['statistics_descr']}
		<input type="button" onclick="clearStatistics();" class="btn bg-warning btn-sm" style="float: right;border-radius: unset;font-size: 13px;" value="{$dleSearchLangVar['admin']['statistics']['clear']}">
	</div>
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th>{$dleSearchLangVar['admin']['statistics']['date']}</th>
					<th>{$dleSearchLangVar['admin']['statistics']['user']}</th>
					<th>{$dleSearchLangVar['admin']['statistics']['IP']}</th>
					<th>{$dleSearchLangVar['admin']['statistics']['query']}</th>
HTML;
if ($dleSearchConfigVar['keyboard']) {
    echo <<<HTML
					<th>{$dleSearchLangVar['admin']['statistics']['alt_query']}</th>
HTML;
}
echo <<<HTML
					<th>{$dleSearchLangVar['admin']['statistics']['found']}</th>
					<th>{$dleSearchLangVar['admin']['statistics']['news']}</th>
					<th>{$dleSearchLangVar['admin']['statistics']['tech_data']}</th>
				</tr>
			</thead>
			<tbody>

HTML;
    $numRow = 0;
    $statList = $paramList = [];
    while ($row = $db->get_row($sql)) {
        $i++;
        $row['mysqlTime'] = $row['mysqlTime'] == -1 ? $dleSearchLangVar['admin']['statistics']['cache'] : round($row['mysqlTime'], 5) . ' ' . $dleSearchLangVar['admin']['statistics']['sec'];

        $row['templateTime'] = $row['templateTime'] == -1 ? $dleSearchLangVar['admin']['statistics']['cache'] : round($row['templateTime'], 5) . ' ' . $dleSearchLangVar['admin']['statistics']['sec'];

$statList[$row['idSearch']] = <<<HTML
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['allTime']}</td>
		<td>{$row['allTime']} {$dleSearchLangVar['admin']['statistics']['sec']}</td>
	</tr>
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['memory']}</td>
		<td>{$row['memoryUsage']} {$dleSearchLangVar['admin']['statistics']['mb']}</td>
	</tr>
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['mysqlTime']}</td>
		<td>{$row['mysqlTime']}</td>
	</tr>
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['templateTime']}</td>
		<td>{$row['templateTime']}</td>
	</tr>
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['queryNumber']}</td>
		<td>{$row['queryNumber']}</td>
	</tr>
	<tr>
		<td>{$dleSearchLangVar['admin']['statistics']['sqlQuery']}</td>
		<td><pre><code style="white-space: pre-wrap;">{$row['sqlQuery']}</code></pre></td>
	</tr>
HTML;

        $row['date'] = date('Y.m.d H:i:s', strtotime($row['date']));
        $foundNews = $row['found'] == 1 ? "<i style=\"color:green!important;\" class=\"fa fa-check\"></i>" : "<i style=\"color:red!important;\" class=\"fa fa-remove\"></i>";
        $row['name'] = $row['userId'] == -1 ? $dleSearchLangVar['admin']['statistics']['guest'] : "<a href=\"{$PHP_SELF}?mod=editusers&action=edituser&id={$row['userId']}\" target=\"_blank\">" . stripslashes($row['name']) . "</a>";
        if ($row['news']) {
            $getNews = $db->super_query("SELECT title FROM " . PREFIX . "_post WHERE id='{$row['news']}'");
            $row['news'] = "<a href=\"/index.php?newsid={$row['news']}\" target=\"_blank\">" . stripslashes($getNews['title']) . "</a>";
        } else {
            $row['news'] = ' — ';
        }

        if ($dleSearchConfigVar['keyboard']) {
            if (strpos($row['searchKeyboard'], 'ru###') !== false) {
                $getKey = explode('ru###', $row['searchKeyboard']);
                if (strpos($getKey[1], '###en###') !== false) {
                    $getKey = explode('###en###', $getKey[1]);
                    $row['searchKeyboard'] = stripslashes($getKey[0]) . '<br>' . stripslashes($getKey[1]);
                } else {
                    $row['searchKeyboard'] = stripslashes($getKey[1]);
                }
            } elseif (strpos($row['searchKeyboard'], 'en###') !== false) {
                $getKey = explode('en###', $row['searchKeyboard']);
                $row['searchKeyboard'] = stripslashes($getKey[1]);
            }
        }

        $row['search'] = stripslashes($row['search']);
echo <<<HTML
				<tr>
					<td>{$row['date']}</td>
					<td>{$row['name']}</td>
					<td>{$row['ip']}</td>
					<td>{$row['search']}</td>
HTML;
        if ($dleSearchConfigVar['keyboard']) {
            echo <<<HTML
					<th>{$row['searchKeyboard']}</th>
HTML;
        }
        echo <<<HTML
					<td>{$foundNews}</td>
					<td>{$row['news']}</td>
					<td><input type="button" class="btn bg-info btn-sm" style="border-radius: unset;" value="{$dleSearchLangVar['admin']['statistics']['view_data']}" onclick="showSearchData({$row['idSearch']})"></td>
				</tr>
HTML;
    }

echo <<<HTML

			</tbody>
		</table>
	</div>

</div>
HTML;
    $jsStat = Helper::json($statList);
    $jsAdminScript[] = <<<HTML

let jsonStat = {$jsStat};
let showSearchData = function(i) {
	$("#dlepopup").remove();
	
	let title = "{$dleSearchLangVar['admin']['statistics']['watch_stat']}";
	let columnTitle = "{$dleSearchLangVar['admin']['statistics']['data']}";
	let contentSearch = jsonStat[i];
	if (contentSearch) {
		$("body").append("<div id='dlepopup' class='dle-alert' title='"+ title + i + "' style='display:none'><div class='panel panel-flat'><div class='table-responsive'><table class='table'><thead><tr><th style='width:250px;'>"+columnTitle+"</th><th>{$dleSearchLangVar['admin']['statistics']['value']}</th></tr></thead><tbody>"+contentSearch+"</tbody></table></div></div></div>");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 800,
			resizable: false,
			dialogClass: "modalfixed dle-popup-alert",
			buttons: {
				"{$dleSearchLangVar['admin']['statistics']['close']}": function() { 
					$(this).dialog("close");
					$("#dlepopup").remove();							
				} 
			}
		});

		$('.modalfixed.ui-dialog').css({position:"fixed", maxHeight:"600px", overflow:"auto"});
		$('#dlepopup').dialog( "option", "position", { my: "center", at: "center", of: window } );
	}
};

HTML;
    if ($allFilterData > $dataPerPage) {
        if ($startFrom > 0) {
            $previous = $startFrom - $dataPerPage;
            $npp_nav .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_search&action=statistics&start_from={$previous}{$urlNav}\"> &lt;&lt; </a></li>";
        }

        $enpages_count = @ceil($allFilterData / $dataPerPage);
        $enpages_start_from = 0;
        $enpages = '';

        if ($enpages_count <= 10) {
            for ($j = 1; $j <= $enpages_count; $j++) {
                if ($enpages_start_from != $startFrom) {
                    $enpages .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_search&action=statistics&start_from={$enpages_start_from}{$urlNav}\">$j</a></li>";
                } else {
                    $enpages .= "<li class=\"page-item active\"><span class=\"page-link\">$j</span></li>";
                }
                $enpages_start_from += $dataPerPage;
            }
            $npp_nav .= $enpages;
        } else {
            $start = 1;
            $end = 10;
            if ($startFrom > 0) {
                if (($startFrom / $dataPerPage) > 4) {
                    $start = @ceil($startFrom / $dataPerPage) - 3;
                    $end = $start + 9;
                    if ($end > $enpages_count) {
                        $start = $enpages_count - 10;
                        $end = $enpages_count - 1;
                    }
                    $enpages_start_from = ($start - 1) * $dataPerPage;
                }
            }

            if ($start > 2) {
                $enpages .= "<li><a href=\"#\">1</a></li> <li><span>...</span></li>";
            }

            for ($j = $start; $j <= $end; $j++) {
                if ($enpages_start_from != $startFrom) {
                    $enpages .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_search&action=statistics&start_from={$enpages_start_from}{$urlNav}\">$j</a></li>";
                } else {
                    $enpages .= "<li class=\"page-item active\"><span class=\"page-link\">$j</span></li>";
                }
                $enpages_start_from += $dataPerPage;
            }
            $enpages_start_from = ($enpages_count - 1) * $dataPerPage;
            $enpages .= "<li><span>...</span></li><li><a href=\"$PHP_SELF?mod=dle_search&action=statistics&start_from={$enpages_start_from}{$urlNav}\">$enpages_count</a></li>";
            $npp_nav .= $enpages;
        }

        if ($allFilterData > $i) {
            $npp_nav .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_search&action=statistics&start_from={$i}{$urlNav}\"> &gt;&gt; </a></li>";
        }

        echo "<nav aria-label=\"Page navigation\"><ul class=\"pagination justify-content-center\">" . $npp_nav . "</ul></nav>";
    }
} else {
echo <<<HTML
<div class="panel panel-default">
    <div class="panel-body" style="font-size:20px; font-weight:bold;border-bottom: 1px solid #ddd;">
        {$dleSearchLangVar['admin']['statistics_descr']}
        <input type="button" onclick="clearStatistics();" class="btn bg-warning btn-sm" style="float: right;border-radius: unset;font-size: 13px;" value="{$dleSearchLangVar['admin']['statistics']['clear']}">
    </div>
    <div class="panel-body">
        <div style="display: table;min-height:100px;width:100%;">
	        <div class="text-center" style="display: table-cell;vertical-align:middle;">{$dleSearchLangVar['admin']['statistics']['search_form']['not']}</div>
	    </div>
    </div>
</div>
HTML;
}

echo <<<HTML
<script>
let clearStatistics = function() {
	DLEconfirm("{$dleSearchLangVar['admin']['statistics']['accept_clear']}", "{$dleSearchLangVar['admin']['try']}", function() {
		coreAdmin.ajaxSend(false, 'clearStatistics', false);
	});
	return false;
}
</script>
HTML;

?>