<?php
/**
 * Проверка новостей
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Subscribe\Data;
include_once realpath(__DIR__ . '/..')  . '/loader.php';

if ($action == 'doeditnews' || $action == 'doaddnews') {
    $id = $item_db[0] ?: $id;

    $dleSubscribeUpdate = false;
    $getOldNews['title'] = stripslashes($getOldNews['title']);
    $getOldNews['reason'] = stripslashes($getOldNews['reason']);

    if ($dleSubscribeConfig['options']['editUpdate']) {
        $dleSubscribeUpdate = true;
    } else {
        if ($dleSubscribeConfig['options']['reasonUpdate'] && $getOldNews['reason'] != $editreason) {
            $dleSubscribeUpdate = true;
        }

        if ($dleSubscribeConfig['options']['titleUpdate'] && $getOldNews['title'] != $title) {
            $dleSubscribeUpdate = true;
        }

        if ($dleSubscribeConfig['options']['dateUpdate']) {
            if ($_POST['allow_now'] == 'yes') {
                $thisTime = date('Y-m-d H:i:s', time());
            } else {
                $thisTime = date('Y-m-d H:i:s', strtotime($_POST['newdate']));
            }
            $getOldNews['date'] = date('Y-m-d H:i:s', strtotime($getOldNews['date']));
            if ($getOldNews['date'] != $thisTime) {
                $dleSubscribeUpdate = true;
            }
        }

        $xfieldsConfig = $dleSubscribeConfig['xfield'];
        if ($xfieldsConfig) {
            $xfieldsPost = xfieldsdataload($getOldNews['xfields']);

            foreach ($xfieldsConfig as $key) {
                $xfieldsPost[$key] = stripslashes($xfieldsPost[$key]);
                if ($xfieldsPost[$key] != $_POST['xfield'][$key]) {
                    $dleSubscribeUpdate = true;
                    break;
                }
            }
        }
    }

    if ((($action == 'doeditnews' && ($dleSubscribeUpdate || $_POST['subscribe_update'] == 1)) || $action == 'doaddnews' && $_POST['subscribe_update'] == 1) && intval($_POST['approve']) == 1) {
        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
		
		$data = [
			'id' => $id,
			'action' => 'send',
			'news' => $action,
			'author' => $member_id['name'],
			'uid' => $member_id['user_id'],
			'dle_hash' => $dle_login_hash
		];
		
        file_get_contents($config['http_home_url'] . 'engine/lazydev/dle_subscribe/lib/send.php?' . http_build_query($data), false, stream_context_create($arrContextOptions));
    }
}