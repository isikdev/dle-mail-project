<?php
/**
* Настройки модуля
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Subscribe\Data;
use LazyDev\Subscribe\Admin;

$accessArray = [];
foreach ($user_group as $group) {
	$accessArray[$group['id']] = $group['group_name'];
}
$allXfield = xfieldsload();
$xfieldArray = ['-' => '-'];
foreach ($allXfield as $value) {
    $xfieldArray[$value[0]] = $value[1];
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
                    <li class="active"><a onclick="ChangeOption(this, 'block_1');" class="tip">
                        <i class="fa fa-cog"></i> {$dleSubscribeLang['admin']['settings']['main_settings']}</a>
                    </li>
                    <li><a onclick="ChangeOption(this, 'block_2');" class="tip">
                        <i class="fa fa-envelope-o"></i> {$dleSubscribeLang['admin']['settings']['block_email_title']}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div id="block_1">
            <div class="panel-body" style="font-size:15px; font-weight:bold;">
                {$dleSubscribeLang['admin']['settings_descr']}
            </div>
            <div class="table-responsive">
                <table class="table">
HTML;
Admin::row(
    $dleSubscribeLang['admin']['settings']['guest_approve_title'],
    $dleSubscribeLang['admin']['settings']['guest_approve_descr'],
    Admin::checkBox('options[guestApprove]', Data::get(['options', 'guestApprove'], 'config'), 'guestApprove')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['group_title'],
    $dleSubscribeLang['admin']['settings']['group_descr'],
    Admin::select(
        ['access[]', $accessArray, true, Data::get('access', 'config'), true, false, $dleSubscribeLang['admin']['settings']['group_title']]
    )
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['show_group_title'],
    $dleSubscribeLang['admin']['settings']['show_group_descr'],
    Admin::checkBox('options[showBlock]', Data::get(['options', 'showBlock'], 'config'), 'showBlock')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_pm_title'],
    $dleSubscribeLang['admin']['settings']['send_pm_descr'],
    Admin::checkBox('options[sendPm]', Data::get(['options', 'sendPm'], 'config'), 'sendPm')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_email_title'],
    $dleSubscribeLang['admin']['settings']['send_email_descr'],
    Admin::checkBox('options[sendEmail]', Data::get(['options', 'sendEmail'], 'config'), 'sendEmail')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_xfield_update_title'],
    $dleSubscribeLang['admin']['settings']['send_xfield_update_descr'],
    Admin::select(
        ['xfield[]', $xfieldArray, true, Data::get('xfield', 'config'), true, false, $dleSubscribeLang['admin']['settings']['xfield_title']]
    )
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_date_update_title'],
    $dleSubscribeLang['admin']['settings']['send_date_update_descr'],
    Admin::checkBox('options[dateUpdate]', Data::get(['options', 'dateUpdate'], 'config'), 'dateUpdate')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_title_update_title'],
    $dleSubscribeLang['admin']['settings']['send_title_update_descr'],
    Admin::checkBox('options[titleUpdate]', Data::get(['options', 'titleUpdate'], 'config'), 'titleUpdate')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_reason_update_title'],
    $dleSubscribeLang['admin']['settings']['send_reason_update_descr'],
    Admin::checkBox('options[reasonUpdate]', Data::get(['options', 'reasonUpdate'], 'config'), 'reasonUpdate')
);
Admin::row(
    $dleSubscribeLang['admin']['settings']['send_edit_update_title'],
    $dleSubscribeLang['admin']['settings']['send_edit_update_descr'],
    Admin::checkBox('options[editUpdate]', Data::get(['options', 'editUpdate'], 'config'), 'editUpdate')
);
echo <<<HTML
                </table>
            </div>
        </div>
        <div id="block_2" style='display:none'>
            <div class="panel-body" style="font-size:15px; font-weight:bold;">
                {$dleSubscribeLang['admin']['settings']['block_email_title']}
            </div>
            <div class="table-responsive">
                <table class="table" id="blockEmail">
                    <tbody>
HTML;
$blockEmail = Data::get('email', 'config');
if ($blockEmail) {
    $indexEmail = 1;
    foreach ($blockEmail as $email) {
echo <<<HTML
                        <tr>
					        <td>
					            <input type="text" class="form-control" name="email[{$indexEmail}]" placeholder="{$dleSubscribeLang['admin']['settings']['email']}" value="{$email}">
					        </td>
					        <td>
					            <a onclick="deleteRule(this); return false;" href="#" style="color: #D32F2F">
					                <i class="fa fa-trash"></i>
                                </a>
					        </td>
				        </tr>
HTML;
        $indexEmail++;
    }
} else {
echo  <<<HTML
                        <tr>
					        <td>
					            <input type="text" class="form-control" name="email[1]" placeholder="{$dleSubscribeLang['admin']['settings']['email']}" value="">
					        </td>
					        <td>
					            <a onclick="deleteRule(this); return false;" href="#" style="color: #D32F2F">
					                <i class="fa fa-trash"></i>
                                </a>
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
    <button type="submit" class="btn bg-teal btn-raised position-left" style="background-color:#1e8bc3;">{$dleSubscribeLang['admin']['save']}</button>
    <a href="#" onclick="addRule(); return false;" id="addEmail" class="btn bg-teal btn-raised" style="display:none;background-color:#e08283;float: right;">{$dleSubscribeLang['admin']['settings']['addEmail']}</a>
</form>
HTML;

$jsAdminScript[] = <<<HTML
$(function() {
    $('body').on('submit', 'form', function(e) {
        e.preventDefault();
        coreAdmin.ajaxSend($('form').serialize(), 'saveOptions', false);
    });
});
function ChangeOption(obj, selectedOption) {
    $('#navbar-filter li').removeClass('active');
    $(obj).parent().addClass('active');
    $('[id*=block_]').hide();
    $('#' + selectedOption).show();
    
    if (selectedOption == 'block_2') {
        $('#addEmail').show();    
    } else {
        $('#addEmail').hide();
    }
    return false;
}
function addRule() {
    var i = $("#blockEmail tbody tr").length + 1;
    $("#blockEmail tbody").append('<tr><td><input type="text" class="form-control" placeholder="{$dleSubscribeLang['admin']['settings']['email']}" name="email['+i+']"></td><td><a onclick="deleteRule(this); return false;" href="#" style="color: #D32F2F"><i class="fa fa-trash"></i></a></td></tr>');
}
function deleteRule(e) {
    $(e).parent().parent().remove();
}
HTML;

?>