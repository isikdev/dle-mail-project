<?php
/**
 * Seo оптимизация страниц
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

use LazyDev\Search\Conditions;
use LazyDev\Search\Search;
use LazyDev\Search\Helper;

$config['fast_search'] = false;

if ($do == 'search' && $Search instanceof LazyDev\Search\Search) {
    $seoView = new dle_template();
    $seoView->dir = TEMPLATE_DIR . '/lazydev/dle_search';
    $seoView->load_template('seo.tpl');
    foreach ($Search::$seo as $key => $value) {
        if ($value) {
            $seoView->set('{' . $key . '}', $value);
            $seoView->set('[' . $key . ']', '');
            $seoView->set('[/' . $key . ']', '');
        }
    }

    if ($pageSpeed > 1) {
        $seoView->set_block("'\\[second\\](.*?)\\[/second\\]'si", '\\1');
        $seoView->set_block("'\\[first\\](.*?)\\[/first\\]'si", '');
        $seoView->set('{page}', $pageSpeed);
        $seoView->set('[page]', '');
        $seoView->set('[/page]', '');
    } else {
        $seoView->set_block("'\\[second\\](.*?)\\[/second\\]'si", '');
        $seoView->set_block("'\\[first\\](.*?)\\[/first\\]'si", '\\1');
        $seoView->set('{page}', '');
        $seoView->set_block("'\\[page\\](.*?)\\[/page\\]'si", '');
    }

    $seoView->set('{count-news}', $countNews ?: 0);

    if ($countNews) {
        $seoView->set_block("'\\[count-news\\](.*?)\\[/count-news\\]'si", '\\1');
        $seoView->set_block("'\\[not-count-news\\](.*?)\\[/not-count-news\\]'si", '');
    } else {
        $seoView->set_block("'\\[not-count-news\\](.*?)\\[/not-count-news\\]'si", '\\1');
        $seoView->set_block("'\\[count-news\\](.*?)\\[/count-news\\]'si", '');
    }
    $seoView->compile('seo');

    $Conditions = Conditions::construct();
    $seoView->result['seo'] = $Conditions::realize($seoView->result['seo'], $Search::$seo);
    if (substr_count($seoView->result['seo'], '[dle-search declination')) {
        $seoView->result['seo'] = preg_replace_callback('#\\[dle-search declination=(.+?)\\](.*?)\\[/declination\\]#is', function ($m) {
            return Helper::declinationLazy([$m[1], $m[2]]);
        }, $seoView->result['seo']);
        $seoView->result['seo'] = preg_replace('#\\[dle-search declination(.+?)\\](.*?)\\[/declination\\]#is', '', $seoView->result['seo']);
    }

    preg_match("'\[meta-title\](.*?)\[/meta-title\]'si", $seoView->result['seo'], $metaTitle);
    if ($metaTitle[1] != '') {
        $metatags['title'] = $metaTitle[1];
    }

    preg_match("'\[meta-description\](.*?)\[/meta-description\]'si", $seoView->result['seo'], $metaDescr);
    if ($metaDescr[1] != '') {
        $metatags['description'] = $metaDescr[1];
    }

    preg_match("'\[meta-keywords\](.*?)\[/meta-keywords\]'si", $seoView->result['seo'], $metaKeys);
    if ($metaKeys[1] != '') {
        $metatags['keywords'] = $metaKeys[1];
    }

    preg_match("'\[meta-robots\](.*?)\[/meta-robots\]'si", $seoView->result['seo'], $metaRobots);
    if ($metaRobots[1] != '') {
        if ($config['version_id'] >= 15.1) {
            $metatags['robots'] = $metaRobots[1];
        } else {
            $metatags['keywords'] = $metatags['keywords'] . "\">\n<meta name=\"robots\" content=\"" . $metaRobots[1];
        }
    }

    preg_match("'\[meta-speedbar\](.*?)\[/meta-speedbar\]'si", $seoView->result['seo'], $metaBread);
    if ($metaBread[1] != '') {
        $metatags['speedbar'] = $metaBread[1];
    }

    $metatags = $Conditions::cleanArray($metatags);

    if ($config['speedbar'] && $metatags['speedbar']) {
        $tpl->load_template('lazydev/dle_search/speedbar.tpl');
        $tpl->set('{site-name}', $config['short_title']);
        $tpl->set('{site-url}', $config['http_home_url']);
        $tpl->set('{separator}', $config['speedbar_separator']);
        $tpl->set('{search-name}', $metatags['speedbar']);
        if ($dleSearchConfigVar['url_on']) {
            $tpl->set('{search-url}', '/search/' . rawurlencode($originalQuery) . '/');
        } else {
            $tpl->set('{search-url}', '/?do=search&subaction=search&story=' . rawurlencode($originalQuery));
        }
        $tpl->set('{page-descr}', $lang['news_site']);
        $tpl->set('{page}', $pageSpeed);

        if ($dleSearchConfigVar['url_on']) {
            $tpl->set_block("'\\[url\\](.*?)\\[/url\\]'si", '\\1');
            $tpl->set_block("'\\[not-url\\](.*?)\\[/not-url\\]'si", '');
        } else {
            $tpl->set_block("'\\[url\\](.*?)\\[/url\\]'si", '');
            $tpl->set_block("'\\[not-url\\](.*?)\\[/not-url\\]'si", '\\1');
        }

        if ($pageSpeed > 1) {
            $tpl->set_block("'\\[second\\](.*?)\\[/second\\]'si", '\\1');
            $tpl->set_block("'\\[first\\](.*?)\\[/first\\]'si", '');
        } else {
            $tpl->set_block("'\\[second\\](.*?)\\[/second\\]'si", '');
            $tpl->set_block("'\\[first\\](.*?)\\[/first\\]'si", '\\1');
        }

        $tpl->compile('speedbar');
        $tpl->clear();

        $config['speedbar'] = false;
    }
}