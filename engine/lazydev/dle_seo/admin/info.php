<?php

$info = stripslashes(strip_tags($_GET['info']));

if ($info == 'add' || $info == 'edit') {
    if ($info == 'edit') {
        $dleSeoLang['admin']['seo']['info-add'] = $dleSeoLang['admin']['seo']['info-edit'];
        $dleSeoLang['admin']['seo']['info-add_descr'] = $dleSeoLang['admin']['seo']['info-edit_descr'];
    }

	$id = intval($_GET['id']);
	$type = intval($_GET['type']);
echo <<<HTML
    <div class="alert alert-success alert-styled-left alert-arrow-left alert-component message_box">
      <h4>{$dleSeoLang['admin']['seo']['info-add']}</h4>
      <div class="panel-body">
            <table width="100%">
                <tbody>
					<tr>
						<td height="80" class="text-center">{$dleSeoLang['admin']['seo']['info-add_descr']}</td>
					</tr>
				</tbody>
			</table>
        </div>
        <div class="panel-footer">
            <div class="text-center">
                <a class="btn btn-sm bg-teal btn-raised position-left legitRipple" href="{$PHP_SELF}?mod=dle_seo&action=seo">{$dleSeoLang['admin']['seo']['list-seo']}</a>
                <a class="btn btn-sm bg-slate-600 btn-raised position-left legitRipple" href="{$PHP_SELF}?mod=dle_seo&action=seo&add=yes&id={$id}&type={$type}">{$dleSeoLang['admin']['seo']['go-edit']}</a>
            </div>
        </div>
    </div>
HTML;
}

if ($info == 'rule') {
    $id = intval($_GET['id']);
    $index = intval($_GET['index']);
    if ($index > 0) {
        $dleSeoLang['admin']['news']['info-add'] = $dleSeoLang['admin']['news']['info-edit'];
        $dleSeoLang['admin']['news']['info-add_descr'] = $dleSeoLang['admin']['news']['info-edit_descr'];
    }

    $from = strip_tags(stripslashes($_GET['from']));
    echo <<<HTML
    <div class="alert alert-success alert-styled-left alert-arrow-left alert-component message_box">
      <h4>{$dleSeoLang['admin']['news']['info-add']}</h4>
      <div class="panel-body">
            <table width="100%">
                <tbody>
					<tr>
						<td height="80" class="text-center">{$dleSeoLang['admin']['news']['info-add_descr']}</td>
					</tr>
				</tbody>
			</table>
        </div>
        <div class="panel-footer">
            <div class="text-center">
                <a class="btn btn-sm bg-teal btn-raised position-left legitRipple" href="{$PHP_SELF}?mod=dle_seo&action={$from}">{$dleSeoLang['admin']['news']['list-seo']}</a>
                <a class="btn btn-sm bg-slate-600 btn-raised position-left legitRipple" href="{$PHP_SELF}?mod=dle_seo&action={$from}&add=yes&id={$id}">{$dleSeoLang['admin']['news']['go-edit']}</a>
            </div>
        </div>
    </div>
HTML;
}
?>