<?php
/**
* Дизайн админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../../');
	die('Hacking attempt!');
}

$jsAdminScript = implode($jsAdminScript);
$additionalJsAdminScript = implode($additionalJsAdminScript);
$d = date('Y', time());
echo <<<HTML
                        <div class="panel panel-default" style="display: block;position: absolute;left: 0;bottom: 0;width: 97.5%;margin-left: 20px;height: 55px;">
                            <div class="panel-content">
                                <div class="panel-body">
                                    &copy; <a href="https://lazydev.pro/" target="_blank">LazyDev</a> {$d} All rights reserved.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{$config['http_home_url']}engine/lazydev/{$modLName}/admin/template/assets/core.js"></script>
        <script>let coreAdmin = new Admin("{$modLName}"); {$jsAdminScript}</script>
        <script>
        $('body').find('select').each(function(index) {
            $(this).attr('data-select-id', index);
            let selectConfig = {
				search: true,
				multiSelectAll: true,
				classNames: "default white",
				multiContainer: true,
				multiShowCount: false,
				locale: "ru",
				trigger: "change"
			};
            
            tail.select('select[data-select-id="' + index + '"]', selectConfig).on('change', function(item, state) {
				let idEl = item.option.parentElement.tagName.toUpperCase() === 'OPTGROUP' ? item.option.parentElement.parentElement.id : item.option.parentElement.id;
                let data = $('#' + idEl).data('dis');
                if (data) {
                    $('#' + idEl).trigger('change');
                }
            });
        });

        </script>
        {$additionalJsAdminScript}
    </body>
</html>
HTML;

?>