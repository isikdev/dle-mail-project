<?php
/**
 * Статистика
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

$countSubscribers = $db->super_query("SELECT 
  count(idSubscribe) as allCount,
  SUM(IF (user = '__GUEST__', 1, 0)) as guestCount,
  SUM(IF (user != '__GUEST__', 1, 0)) as userCount
FROM " . PREFIX . "_dle_subscribe");

if ($countSubscribers['allCount'] > 0) {
    $countSubscribers += $db->super_query("SELECT 
      count(idSubscribe) as allCountToday,
      SUM(IF (user = '__GUEST__', 1, 0)) as guestCountToday,
      SUM(IF (user != '__GUEST__', 1, 0)) as userCountToday,
      SUM(IF (page = 'news', 1, 0)) as newsCountToday,
      SUM(IF (page = 'cat', 1, 0)) as catCountToday,
      SUM(IF (page = 'all', 1, 0)) as allnewsCountToday,
      SUM(IF (page = 'user', 1, 0)) as userpageCountToday,
      SUM(IF (page = 'tag', 1, 0)) as tagCountToday,
      SUM(IF (page = 'xfield', 1, 0)) as xfieldCountToday
    FROM " . PREFIX . "_dle_subscribe WHERE DATE(dateSubscribe) = CURDATE()");
}

$countSubscribers = array_map(function($i) {
    return number_format($i, 0, '', ' ');
}, $countSubscribers);
$countSubscribers['allCountToday'] = $countSubscribers['allCountToday'] ?: 0;
$countSubscribers['userCountToday'] = $countSubscribers['userCountToday'] ?: 0;
$countSubscribers['guestCountToday'] = $countSubscribers['guestCountToday'] ?: 0;

echo <<<HTML
<div class="wrapper">
    <div class="panel-stat">
        <div class="panel-header-stat">
            <h3 class="title-stat">{$dleSubscribeLang['admin']['statistics']['statistics']} <span>{$dleSubscribeLang['admin']['statistics']['today']}</span></h3>

            <div class="calendar-views-stat">
                <span class="active" data-calendarid="-1">{$dleSubscribeLang['admin']['statistics']['day']}</span>
                <span data-calendarid="0">{$dleSubscribeLang['admin']['statistics']['week']}</span>
                <span data-calendarid="1">{$dleSubscribeLang['admin']['statistics']['month']}</span>
                <input type="text" class="form-control" style="width: 80px;margin-left: 10px;" name="dateFrom" id="dateFrom" autocomplete="off">
                <input type="text" class="form-control" style="width: 80px;" name="dateTo" id="dateTo" autocomplete="off">
            </div>
        </div>

        <div class="panel-body-stat">
            <div class="categories-stat">
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['all']}</span>
                    <span>{$countSubscribers['allCount']}</span>
                </div>
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['all_user']}</span>
                    <span>{$countSubscribers['userCount']}</span>
                </div>
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['all_guest']}</span>
                    <span>{$countSubscribers['guestCount']}</span>
                </div>
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['new']}</span>
                    <span>{$countSubscribers['allCountToday']}</span>
                </div>
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['new_user']}</span>
                    <span>{$countSubscribers['userCountToday']}</span>
                </div>
                <div class="category-stat">
                    <span>{$dleSubscribeLang['admin']['statistics']['new_guest']}</span>
                    <span>{$countSubscribers['guestCountToday']}</span>
                </div>
            </div>

            <div class="chart-stat">
                <div class="type-stat">
                    <span class="news-type" data-index="0">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['news']}</span>
                    </span>
                    <span class="cat-type" data-index="1">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['cats']}</span>
                    </span>
                    <span class="allnews-type" data-index="2">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['allnews']}</span>
                    </span>
                    <span class="user-type" data-index="3">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['users']}</span>
                    </span>
                    <span class="xfields-type" data-index="4">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['xfield']}</span>
                    </span>
                    <span class="tag-type" data-index="5">
                        <span></span><span>{$dleSubscribeLang['admin']['statistics']['tags']}</span>
                    </span>
                </div>
                    <canvas id="chartStatic" width="400" height="170"></canvas>
                    <div class="alert alert-danger alert-styled-left alert-arrow-left alert-component" id="alertJs" style="width: 95%;display: none;">
                        <b>{$dleSubscribeLang['admin']['alert']}</b><br>{$dleSubscribeLang['admin']['statistics']['not_data']}
                    </div>
            </div>
            
        </div>
    </div>
</div>

<script src="{$config['http_home_url']}engine/lazydev/{$modLName}/admin/template/assets/Chart.min.js"></script>
<script src="{$config['http_home_url']}engine/lazydev/{$modLName}/admin/template/assets/chartjs-plugin-labels.min.js"></script>
HTML;

$jsAdminScript[] = <<<HTML

let ctx = $("#chartStatic");
let colorSet = [
    'rgba(115, 101, 152, 1)', // Новости
    'rgba(44, 62, 80, 1)', // Категории
    'rgba(37, 116, 169, 1)', // Новые новости
    'rgba(4, 147, 114, 1)', // Пользователи
    'rgba(249, 191, 59, 1)', // Дополнительные поля
    'rgba(108, 122, 137, 1)' // Теги
];
let labelsName = [
    '{$dleSubscribeLang['admin']['statistics']['news']}'.toLowerCase(),
    '{$dleSubscribeLang['admin']['statistics']['cats']}'.toLowerCase(),
    '{$dleSubscribeLang['admin']['statistics']['allnews']}'.toLowerCase(),
    '{$dleSubscribeLang['admin']['statistics']['usersJs']}'.toLowerCase(),
    '{$dleSubscribeLang['admin']['statistics']['xfield']}'.toLowerCase(),
    '{$dleSubscribeLang['admin']['statistics']['tag']}'.toLowerCase()
];
let periodName = [
    '{$dleSubscribeLang['admin']['statistics']['today']}',
    '{$dleSubscribeLang['admin']['statistics']['toweek']}',
    '{$dleSubscribeLang['admin']['statistics']['tomonth']}'
];
let dataDay = [
    {$countSubscribers['newsCountToday']},
    {$countSubscribers['catCountToday']},
    {$countSubscribers['allnewsCountToday']},
    {$countSubscribers['userpageCountToday']},
    {$countSubscribers['xfieldCountToday']},
    {$countSubscribers['tagCountToday']}
];

let chartStatic = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: labelsName,
        datasets: [{
            data: dataDay,
            backgroundColor: colorSet,
            borderColor: colorSet
        }],
    }, options: {
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    return '{$dleSubscribeLang['admin']['statistics']['subersOnPage']} ' + data.labels[tooltipItem.index] + ': ' + data.datasets[0].data[tooltipItem.index];
                }
            }
        },
        legend : {
            display: false,
        },
        plugins: {
            labels: {
                render: function (args) {
                    return args.value;
                },
                fontColor: '#fff',
                fontSize: 14,                
            }
        }
    }
});
if ({$countSubscribers['allCountToday']} == 0) {
    $('#chartStatic').hide();
    $('#alertJs').show();
}

function setGraphs(Graphs) {
    if (Graphs.allCount == 0) {
        $('#chartStatic').hide();
        $('#alertJs').show();
    } else {
        $('#chartStatic').show();
        $('#alertJs').hide();
        chartStatic.data.datasets[0].data = [
            Graphs.newsCount,
            Graphs.catCount,
            Graphs.allnewsCount,
            Graphs.userpageCount,
            Graphs.xfieldCount,
            Graphs.tagCount,
        ];
    }
    
    chartStatic.update();
}

$(function() {
    $('body').on('click', '[data-index]', function() {
        let index = $(this).data('index');
        if ($(this).css('text-decoration').indexOf('through') > 0) {
            $(this).css('text-decoration', 'none').css('color', '#fff');
        } else {
            $(this).css('text-decoration', 'line-through').css('color', '#000');
        }
        let arc = chartStatic.getDatasetMeta(0).data[index];
        arc.hidden = !arc.hidden;
        chartStatic.update();
    });
    
    let staticData = [];
    
    $('#dateFrom').datetimepicker({
        format: 'Y-m-d',
        onShow: function(ct) {
            this.setOptions({
                maxDate: $('#dateTo').val() || false
            });
        },
        onSelectDate: function(ct, \$i) {
            let dateMax = $('#dateTo').val();
            let dateMin = $('#dateFrom').val();
            let dateGraph = dateMin.replace(/-/gi, '') + dateMax.replace(/-/gi, '');
            if (!staticData[dateGraph]) {
                ShowLoading('');
                coreAdmin.ajaxSend("id=2&dateMin=" + dateMin + "&dateMax=" + dateMax, 'getStatistics', function(i) {
                    staticData[dateGraph] = i;
                    setGraphs(staticData[dateGraph]);
                    HideLoading();
                });
            } else {
                setGraphs(staticData[dateGraph]);
            }
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
        onSelectDate: function(ct, \$i) {
            let dateMax = $('#dateTo').val();
            let dateMin = $('#dateFrom').val();
            let dateGraph = dateMin.replace(/-/gi, '') + dateMax.replace(/-/gi, '');
            if (!staticData[dateGraph]) {
                ShowLoading('');
                coreAdmin.ajaxSend("id=2&dateMin=" + dateMin + "&dateMax=" + dateMax, 'getStatistics', function(i) {
                    staticData[dateGraph] = i;
                    setGraphs(staticData[dateGraph]);
                    HideLoading();
                });
            } else {
                setGraphs(staticData[dateGraph]);
            }
        },
        closeOnDateSelect: true,
        scrollMonth: false,
        scrollInput: false,
        i18n: cal_language,
        timepicker: false
    });
    
    $('#dateFrom, #dateTo').on('change', function() {
        $('.calendar-views-stat>span').removeClass('active');
        let dateMax = $('#dateTo').val();
        let dateMin = $('#dateFrom').val();
        if (dateMin || dateMax) {
            if (dateMin && dateMax) {
                $('h3.title-stat>span').text('{$dleSubscribeLang['admin']['statistics']['from']} ' + dateMin + ' {$dleSubscribeLang['admin']['statistics']['to']} ' + dateMax);
            } else {
                $('h3.title-stat>span').text('{$dleSubscribeLang['admin']['statistics']['by']} ' + (dateMin || dateMax));
            }
        } else {
            if ($('.calendar-views-stat>span.active').length == 0) {
                $('.calendar-views-stat>span')[0].click();
            }
        }
    });
    
    $('body').on('click', '.calendar-views-stat>span', function() {
        $('.calendar-views-stat>span').removeClass('active');
        $(this).addClass('active');
        let id = $(this).data('calendarid');
        $('h3.title-stat>span').text(periodName[id+1]);
        $('#dateFrom, #dateTo').val('');
        if (id == -1) {
            chartStatic.data.datasets[0].data = dataDay;
            if ({$countSubscribers['allCountToday']} == 0) {
                $('#chartStatic').hide();
                $('#alertJs').show();
            } else {
                $('#chartStatic').show();
                $('#alertJs').hide();
            }
        } else {
            if (!staticData[id]) {
                ShowLoading('');
                coreAdmin.ajaxSend("id=" + id, 'getStatistics', function(i) {
                    staticData[id] = i;
                    setGraphs(staticData[id]);
                    HideLoading();
                });
            } else {
                setGraphs(staticData[id]);
            }
        }
    });
});

HTML;

?>