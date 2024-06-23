<?php
/**
 * Навигация полного поиска
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/
 
use LazyDev\Search\Search;

$nowNumNews = $fromPage + $foundNumNews;
if ($foundNumNews > 0) {
    $tpl->load_template('lazydev/dle_search/navigation.tpl');
    $no_prev = false;
    $no_next = false;
    if ($dleSearchConfigVar['url_on']) {
        if (isset($fromPage) && $fromPage > 0) {
            $prev = $fromPage / $dleSearchConfigVar['maximum_news_full'];
            $prev_page = $url_page . ($prev == 1 ?  '/' : '/page/' . $prev . '/');

            $tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );
        } else {
            $tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
            $no_prev = true;
        }

        if ($dleSearchConfigVar['maximum_news_full']) {
            $pages = '';

            if ($countNews > $dleSearchConfigVar['maximum_news_full']) {
                $enpages_count = @ceil($countNews / $dleSearchConfigVar['maximum_news_full']);
                $fromPage = ($fromPage / $dleSearchConfigVar['maximum_news_full']) + 1;

                if ($enpages_count <= 10) {
                    for ($j = 1; $j <= $enpages_count; $j++) {
                        if ($j != $fromPage) {
                            $pages .= $j == 1 ? "<a href=\"" . $url_page . "/\">$j</a> " : "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";
                        } else {
                            $pages .= "<span>$j</span> ";
                        }
                    }
                } else {
                    $start = 1;
                    $end = 10;
                    $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

                    if ($fromPage > 0) {
                        if ($fromPage > 6) {
                            $start = $fromPage - 4;
                            $end = $start + 8;

                            if ($end >= $enpages_count - 1) {
                                $start = $enpages_count - 9;
                                $end = $enpages_count - 1;
                            }
                        }
                    }

                    $nav_prefix = $end >= $enpages_count - 1 ? '' : "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

                    if ($start >= 2) {
                        $before_prefix = $start >= 3 ? "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> " : '';
                        $pages .= "<a href=\"" . $url_page . "/\">1</a> ".$before_prefix;
                    }

                    for ($j = $start; $j <= $end; $j++) {
                        if ($j != $fromPage) {
                            $pages .= $j == 1 ? "<a href=\"" . $url_page . "/\">$j</a> " : "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";
                        } else {
                            $pages .= "<span>$j</span> ";
                        }
                    }

                    $pages .= $fromPage != $enpages_count ?  $nav_prefix . "<a href=\"" . $url_page . "/page/{$enpages_count}/\">{$enpages_count}</a>" : "<span>{$enpages_count}</span> ";
			}

            }

            $tpl->set('{pages}', $pages);
        }


        if ($dleSearchConfigVar['maximum_news_full'] < $countNews && $nowNumNews < $countNews) {
            $next_page = $nowNumNews / $dleSearchConfigVar['maximum_news_full'] + 1;

            $next = $url_page . '/page/' . $next_page . '/';
            $tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
        } else {
            $tpl->set_block("'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
            $no_next = true;
        }

        if (!$no_prev || !$no_next) {
            $tpl->compile('navigation');

            switch ($config['news_navigation']) {
                case '2':
                    $tpl->result['content'] = ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']) . $tpl->result['content'];
                    break;
                case '3':
                    $tpl->result['content'] = ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']) . $tpl->result['content'] . ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']);
                    break;
                default:
                    $tpl->result['content'] .= ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']);
                    break;
            }
        } else {
            $tpl->result['navigation'] = '';
        }
    } else {
        if (isset($fromPage) && $fromPage != '' && $fromPage > 0) {
            $prev = $fromPage / $dleSearchConfigVar['maximum_news_full'];
            $tpl->set_block("'\[prev-link\](.*?)\[/prev-link\]'si", "<a id=\"prevlink\" onclick=\"formNavigation($prev); return false;\" href=\"#\">\\1</a>");
        } else {
            $tpl->set_block("'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>");
            $no_prev = true;
        }

        $pages_count = @ceil($countNews / $dleSearchConfigVar['maximum_news_full']);
        $pages_start_from = 0;
        $pages = '';

        if ($pages_count <= 10) {
            for ($j = 1; $j <= $pages_count; $j++) {
                $pages .= ($pages_start_from != $fromPage) ? "<a onclick=\"formNavigation($j); return false;\" href=\"#\">$j</a> " : " <span>$j</span> ";
                $pages_start_from += $dleSearchConfigVar['maximum_news_full'];
            }
        } else {
            $start = 1;
            $end = 10;
            $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

            if ($fromPage > 0) {
                if (($fromPage / $dleSearchConfigVar['maximum_news_full']) > 6) {
                    $start = @ceil($fromPage / $dleSearchConfigVar['maximum_news_full']) - 4;
                    $end = $start + 8;
                    if ($end >= $pages_count - 1) {
                        $start = $pages_count - 9;
                        $end = $pages_count - 1;
                    }
                    $pages_start_from = ($start - 1) * $dleSearchConfigVar['maximum_news_full'];
                }
            }

            $nav_prefix = $end >= $pages_count - 1 ? '' : "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

            if ($start >= 2) {
                $before_prefix = $start >= 3 ? "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> " : '';
                $pages .= "<a onclick=\"formNavigation(1); return false;\" href=\"#\">1</a> " . $before_prefix;
            }

            for ($j = $start; $j <= $end; $j++) {
                $pages .= $pages_start_from != $fromPage ? "<a onclick=\"formNavigation($j); return false;\" href=\"#\">$j</a> " : "<span>$j</span> ";
                $pages_start_from += $dleSearchConfigVar['maximum_news_full'];
            }

            $pages .= $pages_start_from != $fromPage ? $nav_prefix . "<a onclick=\"formNavigation($pages_count); return false;\" href=\"#\">{$pages_count}</a>" : "<span>{$pages_count}</span> ";
        }

        $tpl->set('{pages}', $pages);


        if ($dleSearchConfigVar['maximum_news_full'] < $countNews && $nowNumNews < $countNews) {
            $next_page = $nowNumNews / $dleSearchConfigVar['maximum_news_full'] + 1;
            $tpl->set_block("'\[next-link\](.*?)\[/next-link\]'si", "<a id=\"nextlink\" onclick=\"formNavigation($next_page); return false;\" href=\"#\">\\1</a>");
        } else {
            $tpl->set_block("'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>");
            $no_next = true;
        }

        if (!$no_prev || !$no_next) {
            $tpl->compile('navigation');

            switch ($config['news_navigation']) {
                case '2':
                    $tpl->result['content'] = ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']) . $tpl->result['content'];
                    break;
                case '3':
                    $tpl->result['content'] = ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']) . $tpl->result['content'] . ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']);
                    break;
                default:
                    $tpl->result['content'] .= ((int)$config['version_id'] >= 14 ? '{newsnavigation}' : $tpl->result['navigation']);
                    break;
            }
        }
    }

    $tpl->clear();
}