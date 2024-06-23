<?php
/**
* Список подписчиков
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Subscribe\Admin;
use LazyDev\Subscribe\Helper;

$searchStringArray = ['userName', 'userEmail', 'pageSubscribe', 'pageValue', 'dateFrom', 'dateTo', 'sort'];
$searchIntArray = ['userNameEq', 'userEmailEq', 'confirmed'];

$searchIntArray = array_flip($searchIntArray); $searchStringArray = array_flip($searchStringArray);
$searchKey = [];
foreach ($_GET as $key => $value) {
    if (isset($searchStringArray[$key])) {
        $searchKey[$key] = $db->safesql(strip_tags(stripslashes(trim($value))));
    } elseif ($searchIntArray[$key] && $value > 0) {
        $searchKey[$key] = $db->safesql(intval(trim($value)));
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

if ($searchKey['userName']) {
    if ($searchKey['userNameEq'] == 1) {
        $searchKey['userNameEq'] = 'checked';
        $whereSearch[] = "s.user='{$searchKey['userName']}'";
    } else {
        $whereSearch[] = "s.user LIKE '%{$searchKey['userName']}%'";
    }
}

if ($searchKey['userEmail']) {
    if ($searchKey['userEmailEq'] == 1) {
        $searchKey['userEmailEq'] = 'checked';
        $whereSearch[] = "s.email='{$searchKey['userEmail']}'";
    } else {
        $whereSearch[] = "s.email LIKE '%{$searchKey['userEmail']}%'";
    }
}

$selectedPage = [];
if ($searchKey['pageSubscribe']) {
    $whereSearch[] = "s.page='{$searchKey['pageSubscribe']}'";
    $selectedPage[$searchKey['pageSubscribe']] = ' selected';
}

if ($searchKey['pageValue']) {
    $whereSearch[] = "s.pageValue LIKE '%{$searchKey['pageValue']}%'";
}

if ($searchKey['confirmed']) {
    $whereSearch[] = "s.confirmed='{$searchKey['confirmed']}'";
}

if ($searchKey['dateFrom'] && $searchKey['dateTo']) {
    $whereSearch[] = "DATE(s.dateSubscribe) BETWEEN '{$searchKey['dateFrom']}' AND '{$searchKey['dateTo']}'";
} elseif ($searchKey['dateTo']) {
    $whereSearch[] = "DATE(s.dateSubscribe) = '{$searchKey['dateTo']}'";
} elseif ($searchKey['dateFrom']) {
    $whereSearch[] = "DATE(s.dateSubscribe) = '{$searchKey['dateFrom']}'";
}

$orderBy = 'ORDER BY s.dateSubscribe DESC';
$orderBySelected = [];
if ($searchKey['sort']) {
    switch ($searchKey['sort']) {
        case 'dateDesc':
            $orderBy = 'ORDER BY s.dateSubscribe DESC';
            break;
        case 'dateAsc':
            $orderBy = 'ORDER BY s.dateSubscribe ASC';
            break;
        case 'confirmedDesc':
            $orderBy = 'ORDER BY s.confirmed DESC';
            break;
        case 'confirmedAsc':
            $orderBy = 'ORDER BY s.confirmed ASC';
            break;
    }
    $orderBySelected[$searchKey['sort']] = ' selected';
}

$searchKey = array_map('stripslashes', $searchKey);
if ($whereSearch) {
    $whereSearch = " WHERE " . implode(' AND ', $whereSearch);
} else {
    $whereSearch = '';
}

if (intval($_GET['export']) == 1) {
    $sqlSubscribers = $db->query("SELECT s.*, u.foto, u.user_group, p.id as postid, p.date, p.alt_name, p.category, p.title FROM " . PREFIX . "_dle_subscribe s LEFT JOIN " . USERPREFIX . "_users u ON(s.userId=u.user_id) LEFT JOIN " . PREFIX . "_post p ON (s.page='news' AND p.id=s.pageValue) {$whereSearch} {$orderBy}");
    if ($db->num_rows()) {
        if ($_GET['type'] == 'excel') {
            $rows = "<Table><Row>";
            $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$dleSubscribeLang['admin']['subscribers']['user']}</Data></Cell>";
            $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$dleSubscribeLang['admin']['subscribers']['date']}</Data></Cell>";
            $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$dleSubscribeLang['admin']['subscribers']['email']}</Data></Cell>";
            $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$dleSubscribeLang['admin']['subscribers']['type']}</Data></Cell>";
            $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$dleSubscribeLang['admin']['subscribers']['urlEx']}</Data></Cell>";
            $rows .= "</Row>";
            while ($row = $db->get_row()) {
                $cells = '';

                $namePage = [];
                if ($row['page'] != 'all' && $row['page'] != 'news' && $row['page']) {
                    $namePage = Helper::getUrl($row['page'], $row['helper']);
                } elseif ($row['page'] == 'news') {
                    $namePage = Helper::urlNews($row);
                } elseif ($row['page'] == 'cat') {
                    $namePage = Helper::urlCat(['catid' => $row['pageValue']]);
                }
                if (!$namePage) {
                    $namePage[0] = $config['http_home_url'];
                    $namePage[1] = $config['short_title'];
                }
                $row['page'] = $dleSubscribeLang['admin']['subscribers']['page'][$row['page']];
                $confirmed = '';
                if ($row['user'] == '__GUEST__') {
                    $row['user'] = $dleSubscribeLang['admin']['subscribers']['__GUEST__'];
                    $confirmed = $row['confirmed'] == 1 ? $dleSubscribeLang['admin']['subscribers']['confirmed'] : $dleSubscribeLang['admin']['subscribers']['not_confirmed'];
                }

                $cells .= "<Cell><Data ss:Type=\"String\">{$row['user']} {$confirmed}</Data></Cell>";
                $cells .= "<Cell><Data ss:Type=\"String\">{$row['dateSubscribe']}</Data></Cell>";
                $cells .= "<Cell><Data ss:Type=\"String\">{$row['email']}</Data></Cell>";
                $cells .= "<Cell><Data ss:Type=\"String\">{$row['page']} {$namePage[1]}</Data></Cell>";
                $cells .= "<Cell><Data ss:Type=\"String\">{$namePage[0]}</Data></Cell>";

                $rows .= "<Row>{$cells}</Row>";
            }
            $db->free();
            $db->close();
            $rows .= "</Table>";

            $rows = <<<HTML
	<?xml version="1.0" encoding="{$config['charset']}"?>
	<?mso-application progid="Excel.Sheet"?>
	<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
		<Styles>
			<Style ss:ID="bold">
				<Font ss:Bold="1"/>
			</Style>
		</Styles> 
		<Worksheet ss:Name="users">
		{$rows}
		</Worksheet>
	</Workbook>	
HTML;
            $dateExport = date('Y_m_d H_i_s', time());
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/x-msexcel; charset={$config['charset']}");
            header('Content-Disposition: attachment; filename="subscribe_users_' . $dateExport . '.xls"');
            header("Content-Transfer-Encoding: binary");
            header("Connection: close");
            print($rows);

            die();
        } else {
            $dateExport = date('Y_m_d H_i_s', time());
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: text/csv; charset=utf-8");
            header('Content-Disposition: attachment; filename="subscribe_users_' . $dateExport . '.csv"');

            $config['charset'] = strtolower($config['charset']);

            $output = fopen('php://output', 'w');
            fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            $header_column = [];
            $header_column[] = $dleSubscribeLang['admin']['subscribers']['user'];
            $header_column[] = $dleSubscribeLang['admin']['subscribers']['date'];
            $header_column[] = $dleSubscribeLang['admin']['subscribers']['email'];
            $header_column[] = $dleSubscribeLang['admin']['subscribers']['type'];
            $header_column[] = $dleSubscribeLang['admin']['subscribers']['urlEx'];

            fputcsv($output, $header_column, ';');
            while ($row = $db->get_row()) {
                $cells = [];

                $namePage = [];
                if ($row['page'] != 'all' && $row['page'] != 'news' && $row['page']) {
                    $namePage = Helper::getUrl($row['page'], $row['helper']);
                } elseif ($row['page'] == 'news') {
                    $namePage = Helper::urlNews($row);
                } elseif ($row['page'] == 'cat') {
                    $namePage = Helper::urlCat(['catid' => $row['pageValue']]);
                }
                if (!$namePage) {
                    $namePage[0] = $config['http_home_url'];
                    $namePage[1] = $config['short_title'];
                }
                $row['page'] = $dleSubscribeLang['admin']['subscribers']['page'][$row['page']];
                $confirmed = '';
                if ($row['user'] == '__GUEST__') {
                    $row['user'] = $dleSubscribeLang['admin']['subscribers']['__GUEST__'];
                    $confirmed = $row['confirmed'] == 1 ? $dleSubscribeLang['admin']['subscribers']['confirmed'] : $dleSubscribeLang['admin']['subscribers']['not_confirmed'];
                }

                $cells[] = $row['user'] . ' ' . $confirmed;
                $cells[] = $row['dateSubscribe'];
                $cells[] = $row['email'];
                $cells[] = $row['page'] . ' '  . $namePage[1];
                $cells[] = $namePage[0];

                fputcsv($output, $cells, ";");
            }

            fclose($output);

            $db->free();
            $db->close();

            die();
        }
    } else {
        header("Location: $PHP_SELF?mod=dle_subscribe&action=subscribers{$urlNav}", true, 301);
    }
} else {
    $countSubscribers = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_dle_subscribe s {$whereSearch}")['count'];

    $startFrom = 0;
    if (isset($_GET['start_from']) && $_GET['start_from']) {
        $startFrom = intval($_GET['start_from']);
    }

    $dataPerPage = 50;
    $i = $startFrom;

    $sqlSubscribers = $db->query("SELECT s.*, u.foto, u.user_group, p.id as postid, p.date, p.alt_name, p.category, p.title FROM " . PREFIX . "_dle_subscribe s LEFT JOIN " . USERPREFIX . "_users u ON(s.userId=u.user_id) LEFT JOIN " . PREFIX . "_post p ON (s.page='news' AND p.id=s.pageValue) {$whereSearch} {$orderBy} LIMIT {$startFrom}, {$dataPerPage}");

    echo <<<HTML
<form method="get" class="form-horizontal" id="searchform">
    <input type="hidden" name="mod" id="mod" value="dle_subscribe">
	<input type="hidden" name="action" id="action" value="subscribers">
	<div class="panel panel-default">
	    <div class="panel-heading">{$dleSubscribeLang['admin']['subscribers']['search']['title']}</div>
	    <div class="panel-body">
	        
	        <div class="col-md-6">
	            <div class="form-group">
					<label class="control-label col-md-2">{$dleSubscribeLang['admin']['subscribers']['search']['userName']}</label>
					<div class="col-md-10">
						<div class="input-group">
							<input class="form-control" type="text" name="userName" id="userName" value="{$searchKey['userName']}" placeholder="{$dleSubscribeLang['admin']['subscribers']['search']['userName']}">
						</div>
						<br>
						<div class="checkbox">
							<label>
								<input class="icheck" type="checkbox" name="userNameEq" value="1" {$searchKey['userNameEq']}>{$dleSubscribeLang['admin']['subscribers']['search']['userNameEq']}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2">{$dleSubscribeLang['admin']['subscribers']['search']['userEmail']}</label>
					<div class="col-md-10">
						<div class="input-group">
							<input class="form-control" type="text" name="userEmail" id="userEmail" value="{$searchKey['userEmail']}" placeholder="{$dleSubscribeLang['admin']['subscribers']['search']['userEmail']}">
						</div>
						<br>
						<div class="checkbox">
							<label>
								<input class="icheck" type="checkbox" name="userEmailEq" value="1" {$searchKey['userEmailEq']}>{$dleSubscribeLang['admin']['subscribers']['search']['userEmailEq']}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2">{$dleSubscribeLang['admin']['subscribers']['type']}</label>
					<div class="col-md-10">
						<select class="uniform" name="pageSubscribe" id="pageSubscribe">
							<option value="">{$dleSubscribeLang['admin']['subscribers']['all_select']}</option>
							<option value="news"{$selectedPage['news']}>{$dleSubscribeLang['admin']['subscribers']['page']['news']}</option>
							<option value="cat"{$selectedPage['cat']}>{$dleSubscribeLang['admin']['subscribers']['page']['cat']}</option>
							<option value="xfield"{$selectedPage['xfield']}>{$dleSubscribeLang['admin']['subscribers']['page']['xfield']}</option>
							<option value="tag"{$selectedPage['tag']}>{$dleSubscribeLang['admin']['subscribers']['page']['tag']}</option>
							<option value="user"{$selectedPage['user']}>{$dleSubscribeLang['admin']['subscribers']['page']['user']}</option>
							<option value="all"{$selectedPage['all']}>{$dleSubscribeLang['admin']['subscribers']['page']['all']}</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2">{$dleSubscribeLang['admin']['subscribers']['search']['pageValue']}</label>
					<div class="col-md-10">
						<div class="input-group">
							<input class="form-control" type="text" name="pageValue" id="pageValue" value="{$searchKey['pageValue']}" placeholder="{$dleSubscribeLang['admin']['subscribers']['search']['pageValue']}">
						</div>
					</div>
				</div>
	        </div>
	        
	        <div class="col-md-6">
	        
	            <div class="form-group">
					<label class="control-label col-md-3">{$dleSubscribeLang['admin']['subscribers']['search']['dateFrom']}</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="dateFrom" id="dateFrom" value="{$searchKey['dateFrom']}" autocomplete="off">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">{$dleSubscribeLang['admin']['subscribers']['search']['dateTo']}</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="dateTo" id="dateTo" value="{$searchKey['dateTo']}" autocomplete="off">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-2">{$dleSubscribeLang['admin']['subscribers']['search']['sort']}</label>
					<div class="col-md-10">
						<select class="uniform" name="sort" id="sort">
							<option value="">{$dleSubscribeLang['admin']['subscribers']['search']['defaul']}</option>
							<option value="dateDesc"{$orderBySelected['dateDesc']}>{$dleSubscribeLang['admin']['subscribers']['search']['sortValue'][0]}</option>
							<option value="dateAsc"{$orderBySelected['dateAsc']}>{$dleSubscribeLang['admin']['subscribers']['search']['sortValue'][1]}</option>
							<option value="confirmedDesc"{$orderBySelected['confirmedDesc']}>{$dleSubscribeLang['admin']['subscribers']['search']['sortValue'][2]}</option>
							<option value="confirmedAsc"{$orderBySelected['confirmedAsc']}>{$dleSubscribeLang['admin']['subscribers']['search']['sortValue'][3]}</option>
						</select>
					</div>
				</div>
	        </div>
	        
	    </div>
	    <div class="panel-footer">
			<input type="submit" class="btn bg-teal btn-sm btn-raised position-left" value="{$dleSubscribeLang['admin']['subscribers']['search']['search']}">
			<input type="button" onclick="clearform();" class="btn bg-danger btn-sm btn-raised position-left" value="{$dleSubscribeLang['admin']['subscribers']['search']['clear']}">
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
<form name="subscribersList" id="subscribersList">
	<div class="panel panel-default">
		<div class="panel-heading">
			{$dleSubscribeLang['admin']['subscribers']['list_subscribers']} ({$dleSubscribeLang['admin']['subscribers']['all_subscribers']}{$countSubscribers})
			<div class="heading-elements">
				<ul class="icons-list">
					<li>
						<a href="#" data-toggle="modal" data-target="#userexport"><i class="fa fa-upload position-left"></i>{$dleSubscribeLang['admin']['subscribers']['export']}</a>
					</li>
				</ul>
			</div>
		</div>
		<table class="table table-xs">
			<thead>
				<tr>
					<th>{$dleSubscribeLang['admin']['subscribers']['user']}</th>
					<th>{$dleSubscribeLang['admin']['subscribers']['date']}</th>
					<th>{$dleSubscribeLang['admin']['subscribers']['email']}</th>
					<th>{$dleSubscribeLang['admin']['subscribers']['type']}</th>
					<th><i class="fa fa-trash"></i></th>
				</tr>
			</thead>
			<tbody>
HTML;

    if ($countSubscribers > 0) {
        while ($row = $db->get_row($sqlSubscribers)) {
            $i++;

            $urlPage = '';
            if ($row['page'] != 'all' && $row['page'] != 'cat' && $row['page'] != 'news') {
                $getPage = Helper::getUrl($row['page'], $row['helper']);
                $urlPage = ": <a href=\"{$getPage[0]}\" target='_blank'>" . $getPage[1] . "</a>";
            } elseif ($row['page'] == 'news') {
                $getPage = Helper::urlNews($row);
                $urlPage = ": <a href=\"{$getPage[0]}\" target='_blank'>" . $getPage[1] . "</a>";
            } elseif ($row['page'] == 'cat') {
                $getPage = Helper::urlCat(['catid' => $row['pageValue']]);
                $urlPage = ": <a href=\"{$getPage[0]}\" target='_blank'>" . $getPage[1] . "</a>";
            }
            $row['page'] = $dleSubscribeLang['admin']['subscribers']['page'][$row['page']];

            $photo = $config['http_home_url'] . 'templates/' . $config['skin'] . '/dleimages/noavatar.png';
            if (count(explode('@', $row['foto'])) == 2) {
                $photo = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto']));
            } elseif ($row['foto']) {
                $avatar = $row['foto'];
                if (strpos($row['foto'], '//') === 0) {
                    $avatar = 'http:' . $row['foto'];
                }

                $avatar = @parse_url($avatar);

                $photo = $config['http_home_url'] . 'uploads/fotos/' . $row['foto'];
                if ($avatar['host']) {
                    $photo = $row['foto'];
                }
            }

            if ($row['user'] == '__GUEST__') {
                $row['user'] = $dleSubscribeLang['admin']['subscribers']['__GUEST__'];

                $row['confirmed'] = $row['confirmed'] == 1 ? '<span style="color:darkgreen;">' . $dleSubscribeLang['admin']['subscribers']['confirmed'] . '</span>' : '<span style="color:darkred;">' . $dleSubscribeLang['admin']['subscribers']['not_confirmed'] . '</span>';
                $userNick = "<div class=\"user-list\">
    <img src=\"{$photo}\" class=\"img-circle img-responsive hidden-xs\">
    <h6>{$row['user']}</h6>
    <span class=\"text - size - small\">{$row['confirmed']}</span>
</div>";
            } else {
                $userLink = $config['http_home_url'] . 'user/' . urlencode($row['user']) . '/';
                $userNick = "<div class=\"user-list\">
    <img src=\"{$photo}\" class=\"img-circle img-responsive hidden-xs\">
    <h6><a href=\"{$userLink}\" target=\"_blank\">{$row['user']}</a></h6>
    <span class=\"text - size - small\">{$user_group[$row['user_group']]['group_name']}</span>
</div>";
            }
            echo <<<HTML
<tr id="subscribe_{$row['idSubscribe']}">
    <td>{$userNick}</td>
    <td>{$row['dateSubscribe']}</td>
    <td>{$row['email']}</td>
    <td>{$row['page']}{$urlPage}</td>
    <td><a onclick="deleteSubscribe('{$row['idSubscribe']}'); return(false);" style="color: #0a0a0a!important;" href="#" class="icon"><i class="fa fa-trash"></i></a></td>
</tr>
HTML;

        }
    }
    echo <<<HTML
			</tbody>
		</table>

		<div class="panel-footer hidden-xs">
			<div class="pull-right">
				<select class="uniform" name="masssubs">
					<option value="">{$dleSubscribeLang['admin']['subscribers']['do']}</option>
					<option value="mass_unsub">{$dleSubscribeLang['admin']['subscribers']['unsub']}</option>
				</select>&nbsp;<input class="btn bg-brown-600 btn-sm btn-raised" type="submit" value="{$dleSubscribeLang['admin']['subscribers']['doit']}">
			</div>
		</div>
	</div>
</form>
<div class="modal fade" id="userexport">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			
			<div class="modal-header ui-dialog-titlebar">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="ui-dialog-title">{$dleSubscribeLang['admin']['subscribers']['export']}</span>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="col-sm-6">
						<label class="radio-inline">
							<input class="icheck" type="radio" name="typeExport" value="csv" checked>{$dleSubscribeLang['admin']['subscribers']['csv']}
						</label>
					</div>
					<div class="col-sm-6">
						<label class="radio-inline">
							<input class="icheck" type="radio" name="typeExport" value="excel">{$dleSubscribeLang['admin']['subscribers']['excel']}
						</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="exportSubs" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-upload position-left"></i>{$dleSubscribeLang['admin']['subscribers']['export_button']}</button>
				<button type="button" class="btn bg-slate-600 btn-sm btn-raised" data-dismiss="modal">{$dleSubscribeLang['admin']['subscribers']['export_cancel']}</button>
			</div>
	  
		</div>
	</div>
</div>
<script>
$(function() {
    $('body').on('click', '#exportSubs', function(e) {
        e.preventDefault();
        DLEconfirm('{$dleSubscribeLang['admin']['subscribers']['export_try']}', '{$dleSubscribeLang['admin']['try']}', function() {
            window.location.href = "{$PHP_SELF}?mod=dle_subscribe&action=subscribers{$urlNav}&export=1&type=" + $('[name=typeExport]:checked').val();
        });
    });
});
function deleteSubscribe(id) {
    DLEconfirm('{$dleSubscribeLang['admin']['subscribers']['delete_subscribe_text']}', '{$dleSubscribeLang['admin']['subscribers']['delete_subscribe_title']}', function() {
        $.post('engine/lazydev/dle_subscribe/ajax.php', {action: 'deleteSubscribe', id: id, dle_hash: dle_login_hash}, function(data) {
            data = jQuery.parseJSON(data);
            if (data.error) {
                Growl.error({
                    title: '{$dleSubscribeLang['admin']['subscribers']['error']}',
                    text: data.text
                });
            } else {
                Growl.info({
                    title: '{$dleSubscribeLang['admin']['subscribers']['successful']}',
                    text: data.text
                });
                
                $('#subscribe_' + id).remove();
            }
        });
    });
}
</script>
HTML;
    if ($countSubscribers > $dataPerPage) {
        if ($startFrom > 0) {
            $previous = $startFrom - $dataPerPage;
            $npp_nav .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_subscribe&action=subscribers&start_from={$previous}{$urlNav}\"> &lt;&lt; </a></li>";
        }

        $enpages_count = @ceil($countSubscribers / $dataPerPage);
        $enpages_start_from = 0;
        $enpages = '';

        if ($enpages_count <= 10) {
            for ($j = 1; $j <= $enpages_count; $j++) {
                if ($enpages_start_from != $startFrom) {
                    $enpages .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_subscribe&action=subscribers&start_from={$enpages_start_from}{$urlNav}\">$j</a></li>";
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
                    $enpages .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_subscribe&action=subscribers&start_from={$enpages_start_from}{$urlNav}\">$j</a></li>";
                } else {
                    $enpages .= "<li class=\"page-item active\"><span class=\"page-link\">$j</span></li>";
                }
                $enpages_start_from += $dataPerPage;
            }
            $enpages_start_from = ($enpages_count - 1) * $dataPerPage;
            $enpages .= "<li><span>...</span></li><li><a href=\"$PHP_SELF?mod=dle_subscribe&action=subscribers&start_from={$enpages_start_from}{$urlNav}\">$enpages_count</a></li>";
            $npp_nav .= $enpages;
        }

        if ($countSubscribers > $i) {
            $npp_nav .= "<li class=\"page-item\"><a class=\"page-link\" href=\"$PHP_SELF?mod=dle_subscribe&action=subscribers&start_from={$i}{$urlNav}\"> &gt;&gt; </a></li>";
        }

        echo "<nav aria-label=\"Page navigation\"><ul class=\"pagination justify-content-center\">" . $npp_nav . "</ul></nav>";
    }
}
?>