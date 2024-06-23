<?php
/**
* Главная страница админ панель
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

use LazyDev\Search\Admin;
use LazyDev\Search\Helper;

echo <<<HTML
<div class="panel panel-default">
    <div class="panel-heading">{$dleSearchLangVar['admin']['other']['block_menu']}</div>
    <div class="list-bordered">
HTML;
echo Admin::menu([
    [
        'link' => '?mod=' . $modLName . '&action=settings',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/settings.png',
        'title' => $dleSearchLangVar['admin']['settings_title'],
        'descr' => $dleSearchLangVar['admin']['settings_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=statistics',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/statistics.png',
        'title' => $dleSearchLangVar['admin']['statistics_title'],
        'descr' => $dleSearchLangVar['admin']['statistics_descr'],
    ],
    [
        'link' => '?mod=' . $modLName . '&action=replace',
        'icon' => $config['http_home_url'] . 'engine/lazydev/' . $modLName . '/admin/template/assets/icons/replace.png',
        'title' => $dleSearchLangVar['admin']['replace_title'],
        'descr' => $dleSearchLangVar['admin']['replace_descr'],
    ]
]);
echo <<<HTML
    </div>
</div>
HTML;

// Popular query all time
$popularQuery = $db->super_query("SELECT s.search, p.title, p.category, p.date, p.alt_name, p.id, COUNT(*) as count, s.found FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) GROUP BY search ORDER BY count DESC LIMIT 1");
$popularQueryCard = Admin::designCard($popularQuery);

// Popular Query Today
$popularQueryToday = $db->super_query("SELECT s.search, p.title, p.category, p.date, p.alt_name, p.id, COUNT(*) as count, s.found FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) WHERE DATE(s.date)='" . date('Y-m-d') . "' GROUP BY search ORDER BY count DESC LIMIT 1");
$popularQueryTodayCard = Admin::designCard($popularQueryToday);

// News today
$popularNewsChartToday = $db->query("SELECT s.search, p.title, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) WHERE DATE(s.date)='" . date('Y-m-d') . "' AND news!=0 GROUP BY news ORDER BY count DESC LIMIT 0,5");
$popularNewsChartTodayData = Admin::designChart($popularNewsChartToday, false);

// News all time
$popularNewsChart = $db->query("SELECT s.search, p.title, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) WHERE news!=0 GROUP BY news ORDER BY count DESC LIMIT 0,5");
$popularNewsChartData = Admin::designChart($popularNewsChart, false);

// Query today
$popularQChartToday = $db->query("SELECT s.search, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s WHERE DATE(s.date)='" . date('Y-m-d') . "' GROUP BY s.search ORDER BY count DESC LIMIT 0,5");
$popularQChartTodayData = Admin::designChart($popularQChartToday, false);

// Query all time
$popularQChart = $db->query("SELECT s.search, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s GROUP BY s.search ORDER BY count DESC LIMIT 0,5");
$popularQChartData = Admin::designChart($popularQChart, false);

// Cat today
$popularCatChartToday = $db->query("SELECT p.category, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) WHERE DATE(s.date)='" . date('Y-m-d') . "' AND news!=0 GROUP BY p.category ORDER BY count DESC LIMIT 0,5");
$popularCatChartTodayData = Admin::designChart($popularCatChartToday, true);

// Cat all time
$popularCatChart = $db->query("SELECT p.category, COUNT(*) as count FROM ". PREFIX . "_dle_search_statistics s LEFT JOIN " . PREFIX . "_post p ON (s.news=p.id) WHERE news!='' GROUP BY p.category ORDER BY count DESC LIMIT 0,5");
$popularCatChartData = Admin::designChart($popularCatChart, true);

$countPopular = $popularQueryCard['count'] . ' ' . Helper::declinationLazy([$popularQueryCard['count'], 'запрос|а|ов']);
$countPopularToday = $popularQueryTodayCard['count'] . ' ' . Helper::declinationLazy([$popularQueryTodayCard['count'], 'запрос|а|ов']);
echo <<<HTML
<div class="panel panel-default">
HTML;
$collgn = '12'; $widthcoln = '';
if ($popularQueryTodayCard['search']) {
    $collgn = '6'; $widthcoln = 'width: 48%!important;';
echo <<<HTML
    <div class="col-lg-6" style="padding: 0!important;margin-right: 4%;width: 48%!important;">
        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark" >{$dleSearchLangVar['admin']['popular_query_today']}</h3>
            </div>
            <div class="card-body pt-2" style="margin-top: 10px;">
                <div class="d-flex align-items-center mb-10">
                    <div class="symbol symbol-40 symbol-light-success mr-5">
                        <span class="symbol-label"><i class="fa fa-line-chart h-75 align-self-end"></i></span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1 font-weight-bold">
                        <span class="text-dark text-hover-primary mb-1" style="font-size: 18px;">{$popularQueryTodayCard['search']} ({$countPopularToday})</span>
                        <span class="text-muted">{$dleSearchLangVar['admin']['search_mean']} <a href="{$popularQueryTodayCard['link']}">{$popularQueryTodayCard['title']}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;
}
if ($popularQueryCard['search']) {
echo <<<HTML
    <div class="col-lg-{$collgn}" style="padding: 0!important;{$widthcoln}">
        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark" >{$dleSearchLangVar['admin']['popular_query_all']}</h3>
            </div>
            <div class="card-body pt-2" style="margin-top: 10px;">
                <div class="d-flex align-items-center mb-10">
                    <div class="symbol symbol-40 symbol-light-success mr-5">
                        <span class="symbol-label"><i class="fa fa-line-chart h-75 align-self-end"></i></span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1 font-weight-bold">
                        <span class="text-dark text-hover-primary mb-1" style="font-size: 18px;">{$popularQueryCard['search']} <span class="text-muted" style="font-size: 14px;">[ {$countPopular} ]</span></span>
                        <span class="text-muted">{$dleSearchLangVar['admin']['search_mean']} <a href="{$popularQueryCard['link']}">{$popularQueryCard['title']}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;
}
echo <<<HTML
    <!--aa-->
    <div class="col-lg-6" style="padding: 0!important;margin-right: 4%;width: 48%!important;margin-top: 10px;margin-bottom: 50px;">

        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark">{$dleSearchLangVar['admin']['popular_news_today']}</h3>
            </div>
HTML;
if ($popularNewsChartTodayData['many'] >= 4) {
echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-news-today"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-news-today',
                        data: {
                            columns: [
                                {$popularNewsChartTodayData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularNewsChartTodayData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>

    <div class="col-lg-6" style="padding: 0!important;width: 48%!important;margin-top: 10px;margin-bottom: 50px">
        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark" >{$dleSearchLangVar['admin']['popular_news_all']}</h3>
            </div>
HTML;

if ($popularNewsChartData['many'] >= 4) {
    echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-news-all"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-news-all',
                        data: {
                            columns: [
                                {$popularNewsChartData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularNewsChartData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
    echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>
<!--aa-->
    <div class="col-lg-6" style="padding: 0!important;margin-right: 4%;width: 48%!important;margin-top: 10px;margin-bottom: 50px;">

        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark">{$dleSearchLangVar['admin']['popular_querys_today']}</h3>
            </div>
HTML;
if ($popularQChartTodayData['many'] >= 4) {
    echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-q-today"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-q-today',
                        data: {
                            columns: [
                                {$popularQChartTodayData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularQChartTodayData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
    echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>

    <div class="col-lg-6" style="padding: 0!important;width: 48%!important;margin-top: 10px;margin-bottom: 50px">
        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark" >{$dleSearchLangVar['admin']['popular_querys_all']}</h3>
            </div>
HTML;

if ($popularQChartData['many'] >= 4) {
    echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-q-all"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-q-all',
                        data: {
                            columns: [
                                {$popularQChartData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularQChartData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
    echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>
<!--aa-->
    <div class="col-lg-6" style="padding: 0!important;margin-right: 4%;width: 48%!important;margin-top: 10px;margin-bottom: 50px;">

        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark">{$dleSearchLangVar['admin']['popular_cat_today']}</h3>
            </div>
HTML;

if ($popularCatChartTodayData['many']) {
    echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-cat-today"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-cat-today',
                        data: {
                            columns: [
                                {$popularCatChartTodayData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularCatChartTodayData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
    echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>

    <div class="col-lg-6" style="padding: 0!important;width: 48%!important;margin-top: 10px;margin-bottom: 50px">
        <div class="card card-custom card-stretch gutter-b">
            <div class="card-header border-0" style="border-bottom: 1px solid #ddd;">
                <h3 class="card-title font-weight-bolder text-dark" >{$dleSearchLangVar['admin']['popular_cat_all']}</h3>
            </div>
HTML;

if ($popularCatChartData['count']) {
    echo <<<HTML
            <div class="pt-2" style="margin-top: 10px;margin-bottom: 30px;">
                <div id="chart-cat-all"></div>
            </div>
            <script>
                $(document).ready(function() {
                    var chart = c3.generate({
                        bindto: '#chart-cat-all',
                        data: {
                            columns: [
                                {$popularCatChartData['count']}
                            ],
                            type: 'pie',
                            colors: {
                                'data1': '#1c3353',
                                'data2': '#467fcf',
                                'data3': '#45aaf2',
                                'data4': '#6574cd'
                            },
                            names: {
                                {$popularCatChartData['name']}
                            }
                        },
                        axis: {
                        },
                        legend: {
                            show: true,
                        },
                        padding: {
                            bottom: 0,
                            top: 0
                        },
                    });
                });
            </script>
HTML;
} else {
    echo <<<HTML
    <div class="alert alert-info alert-styled-left alert-arrow-left alert-component" style="margin-bottom: 0px!important;">{$dleSearchLangVar['admin']['nope']}</div>
HTML;
}
echo <<<HTML
        </div>
    </div>
    
    
</div>

HTML;

?>