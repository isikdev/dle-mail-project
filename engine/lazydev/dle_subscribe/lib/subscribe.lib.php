<?php
/**
 * Работа с подписками с сайта
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

$hash = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_BASENAME);
$hash = $db->safesql(trim(strip_tags(stripslashes($hash))));
use LazyDev\Subscribe\Data;

if (mb_strlen($hash, 'UTF-8') == 40) {
    $getSubscribe = $db->super_query("SELECT idSubscribe, confirmed FROM " . PREFIX . "_dle_subscribe WHERE hash='{$hash}'");
    if ($getSubscribe['idSubscribe'] > 0) {
        if (substr_count($_SERVER['REQUEST_URI'], 'accept')) {
            if ($getSubscribe['confirmed'] == 1) {
                msgbox($dleSubscribeLang['site']['error'], $dleSubscribeLang['site']['errorAccept']);
            } else {
                $db->query("UPDATE " . PREFIX . "_dle_subscribe SET confirmed='1' WHERE hash='{$hash}'");
                msgbox($dleSubscribeLang['site']['acceptEmail'], $dleSubscribeLang['site']['acceptEmailText']);
            }
        } elseif (substr_count($_SERVER['REQUEST_URI'], 'decline')) {
            $db->query("DELETE FROM " . PREFIX . "_dle_subscribe WHERE hash='{$hash}'");
            msgbox($dleSubscribeLang['site']['declineEmail'], $dleSubscribeLang['site']['declineEmailText']);
        }
    } elseif (substr_count($_SERVER['REQUEST_URI'], 'decline') && !$getSubscribe['idSubscribe']) {
        msgbox($dleSubscribeLang['site']['error'], $dleSubscribeLang['site']['errorDecline']);
    } else {
        msgbox($dleSubscribeLang['site']['error'], $dleSubscribeLang['site']['errorData']);
    }
} else {
    msgbox($dleSubscribeLang['site']['error'], $dleSubscribeLang['site']['errorData']);
}

$allow_active_news = false;