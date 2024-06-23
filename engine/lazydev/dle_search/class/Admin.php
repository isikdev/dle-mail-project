<?php
/**
 * Класс для работы с админ панелью
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/

namespace LazyDev\Search;

class Admin
{
    static function makeUrl($data)
    {
        global $config;

        if ($config['allow_alt_url']) {
            if ($config['seo_type'] == 1 || $config['seo_type'] == 2) {
                if (intval($data['category']) && $config['seo_type'] == 2) {
                    $link = $config['http_home_url'] . get_url(intval($data['category'])) . '/' . $data['id'] . '-' . $data['alt_name'] . '.html';
                } else {
                    $link = $config['http_home_url'] . $data['id'] . '-' . $data['alt_name'] . '.html';
                }
            } else {
                $link = $config['http_home_url'] . date('Y/m/d/', strtotime($data['date'])) . $data['alt_name'] . '.html';
            }
        } else {
            $link = $config['http_home_url'] . 'index.php?newsid=' . $data['id'];
        }

        return $link;
    }

    static function designCard($data)
    {
        global $config, $db;

        if (!$data['title']) {
            $data['search'] = $db->safesql(stripslashes($data['search']));
            $select_query[] = "LOCATE('{$data['search']}', `title`) AS search_title";
            $having_query[] = 'search_title > 0';
            $order_query[] = 'search_title ASC';

            $order_sql = 'ORDER BY ' . implode(', ', $order_query);
            $select_sql = implode(', ', $select_query) . ',';
            $having_sql = 'HAVING ' . implode(' OR ', $having_query);

            $row = $db->super_query("SELECT {$select_sql} id, category, alt_name, date, title FROM " . PREFIX . "_post WHERE (title LIKE '%{$data['search']}%') {$having_sql} {$order_sql} LIMIT 1");
            $row && $data = array_merge($data, $row);
        }

        if ($data['title']) {
            $link = self::makeUrl($data);
            $data['title'] = stripslashes($data['title']);
        } else {
            $data['title'] = Data::get(['admin', 'not'], 'lang');
            $link = '#';
        }

        return ['search' => $data['search'], 'link' => $link, 'count' => $data['count'], 'title' => $data['title']];
    }

    static function designChart($sql, $bool)
    {
        global $db, $cat_info;

        $i = 1;
        $count = [];
        $name = [];
        $query = [];
        $top_cat = [];

        if ($bool) {
            while ($row = $db->get_row($sql)) {
                $cat_id = intval($row['category']);
                $top_cat[$cat_id]++;
            }

            arsort($top_cat);

            foreach ($top_cat as $catid => $count_cat) {
                $count[] = "['data{$i}', {$count_cat}]";
                $name[] = "'data{$i}': '{$cat_info[$catid]['name']}'";
                $i++;
            }
            $name = implode(',', $name);
        } else {
            while ($row = $db->get_row($sql)) {
                $count[] = "['data{$i}', {$row['count']}]";
                $name[] = "'data{$i}': '{$row['search']}'";
                if ($row['search']) {
                    $row['search'] = stripslashes($row['search']);
                }
                $query[] = "'data{$i}': '{$row['search']}'";
                $i++;
            }


            $name = implode(',', $name);
            $query = implode(',', $query);
        }

        return ['name' => $name, 'count' => implode(',', $count), 'many' => count($count), 'query' => $query];
    }

    /**
     * Открытие таблицы с формой
     *
     * @param    string    $lang
     **/
    static function tableOpenList($lang)
    {
        echo '<form>
                <div class="panel panel-default">
                    <div class="panel-heading">' . $lang . '</div>
                    <div class="table-responsive">
                    <table class="table">';
    }

    /**
     * Закрыть таблицу с формой
     *
     * @param    string    $lang
     **/
    static function tableCloseList($lang)
    {
        echo '</table></div><div class="panel-footer"><input type="button" class="btn bg-success btn-sm btn-raised position-left" value="' . $lang . '" id="save"></div></div></form>';
    }

    /**
     * Заголовок таблицы
     *
     * @param    array    $a
     **/
    static function tableHead($a)
    {
        echo '<thead>
                <tr>';
        foreach ($a as $n) {
            echo '<th>' . $n . '</th>';
        }
        echo '</tr>
			</thead>';
    }

    /**
     * Тело таблицы
     *
     **/
    static function tbodyOpen()
    {
        echo '<tbody>';
    }

    /**
     * Данные таблицы
     *
     * @param    array    $a
     **/
    static function tableTd($a)
    {
        echo '<tr>';
        foreach ($a as $d) {
            echo '<td>' . $d . '</td>';
        }
        echo '</tr>';
    }

    /**
     * Закрыть тело таблицы
     *
     **/
    static function tbodyClose()
    {
        echo '</tbody>';
    }

    /**
     * Данные таблицы
     *
     * @param    string    $title
     * @param    string    $description
     * @param    string    $field
     * @param	 string	   $helper
     **/
    static function row($title = '', $description = '', $field = '', $helper = '')
    {
        $description = $description ? '<span class="text-muted text-size-small hidden-xs">' . $description . '</span>' : '';
        $helper = $helper ? '<i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right" data-rel="popover" data-trigger="hover" data-html="true" data-placement="right" data-content="' . $helper . '" data-original-title="" title=""></i>' : '';
        echo '<tr>
            <td class="col-xs-6 col-sm-6 col-md-7">
                <h6 class="media-heading text-semibold">' . $title .  $helper . '</h6>
                ' . $description . '
            </td>
            <td class="col-xs-6 col-sm-6 col-md-5">' . $field . '</td>
        </tr>';
    }

    /**
     * Параметры input
     *
     * @param    array    $data
     * @return   string
     **/
    static function input($data)
    {
        $inputElement = $data[3] ? ' placeholder="' . $data[3] . '"' : '';
        $inputElement .= $data[4] ? ' disabled' : '';
        $class = 'form-control';
        $divStart = '';
        if ($data[1] == 'range') {
            $class = ' custom-range';
            $inputElement .= $data[5] ? ' step="' . $data[5] . '"' : '';
            $inputElement .= $data[6] ? ' min="' . $data[6] . '"' : '';
            $inputElement .= $data[7] ? ' max="' . $data[7] . '"' : '';
        } elseif ($data[1] == 'number') {
            $class = '';
            $divStart = '<div class="quantity">';
            $inputElement .= $data[5] >=0 ? ' min="' . $data[5] . '"' : '';
            $inputElement .= $data[6] ? ' max="' . $data[6] . '"' : '';
        }

        return $divStart . '<input type="' . $data[1] . '" autocomplete="off" value="' . $data[2]. '" class="' . $class . '" name="' . $data[0] . '" ' . $inputElement . '>' . ($divStart != '' ? '</div>' : '');
    }

    /**
     * Параметры checkbox
     *
     * @param    string    $name
     * @param    bool      $checked
     * @param    string    $id
	 * @param 	 bool	   $disabled
	 * @param 	 bool|array $connect
     * @return   string
     **/
    static function checkBox($name, $checked, $id, $disabled = false, $connect = false)
    {
		global $dleSearchLangVar;

		$checked = $checked ? 'checked' : '';
		$disabled = $disabled ? 'disabled' : '';
		$data = '';
		if ($connect) {
			$data = 'data-dis="' . implode(',', $connect[$id]) . '"';
		}
return <<<HTML
<div class="can-toggle can-toggle--size-small">
	<input id="{$id}" {$data} name="{$name}" value="1" type="checkbox" {$checked} {$disabled}>
	<label for="{$id}">
		<div class="can-toggle__switch" data-checked="{$dleSearchLangVar['admin']['turn_on']}" data-unchecked="{$dleSearchLangVar['admin']['turn_off']}"></div>
	</label>
</div>
HTML;
    }

    /**
     * Параметры select
     *
     * @param    array    $data
     * @param    bool     $m
     * @return   string
     **/
    static function selectIn($data, $m = true)
    {
        global $dleSearchLangVar;

        $optionGroup = false;
        $output = '';

        foreach ($data[1] as $key => $val) {
            if (strpos($key, 'p.') === false && !$optionGroup) {
                $optionGroup = true;
                $output = "<optgroup label=\"{$dleSearchLangVar['admin']['settings']['row_news']}\">" . $output . "</optgroup><optgroup label=\"{$dleSearchLangVar['admin']['settings']['xf_row']}\">";
            }
            $output .= "<option value=\"{$key}\"";

            if (is_array($data[2])) {
                foreach ($data[2] as $element) {
                    if ($element == $key) {
                        $output .= ' selected ';
                    }
                }
            } elseif ($data[2] == $key) {
                $output .= ' selected ';
            }

            $output .= ">{$val}</option>\n";
        }

        if ($optionGroup) {
            $output .= "</optgroup>";
        } else {
            $output = "<optgroup label=\"{$dleSearchLangVar['admin']['settings']['row_news']}\">" . $output . "</optgroup>";
        }

        $inputElemet = $data[4] ? ' disabled' : '';
        $inputElemet .= $data[3] ? ' multiple' : '';
        $inputElemet .= $data[5] ? " data-placeholder=\"{$data[5]}\"" : '';
		$dis = '';
		if ($data[6]) {
			$dis = 'data-dis="' . implode(',', $data[6][$data[0]]) . '"';
		}
        $m = $m ? '[]' : '';

        return '<select ' . $dis . ' id="' . $data[0] . '" name="' . $data[0] . $m . '" class="selectTag" ' . $inputElemet . '>' . $output . '</select>';
    }

    /**
     * Параметры select
     *
     * @param    string    $name
     * @param    string    $select
     * @param    string    $placeholder
     * @return   string
     **/
    static function selectTag($name, $select, $placeholder = '')
    {
        return '<select name="' . $name . '" class="selectTag" data-placeholder="' . $placeholder . '" multiple>' . $select . '</select>';
    }

    /**
     * Параметры select
     *
     * @param    array    $data
     * @param    bool     $opt
	 * @param 	 bool|array $dis
     * @return   string
     **/
    static function select($data, $opt = false, $dis = false)
    {
        global $langDleFilter;

        $output = '';
        foreach ($data[1] as $key => $val) {
            if ($opt && $key == 'date') {
                $output .= "<optgroup label=\"{$langDleFilter['admin']['settings']['standard']}\">";
            }
            $output .= $data[2] ? "<option value=\"{$key}\"" : "<option value=\"{$val}\"";

            if (is_array($data[3])) {
                foreach ($data[3] as $element) {
                    if ($data[2] && $element == $key) {
                        $output .= ' selected ';
                    } elseif (!$data[2] && $element == $val) {
                        $output .= ' selected ';
                    }
                }
            } elseif ($data[2] && $data[3] == $key) {
                $output .= ' selected ';
            } elseif (!$data[2] && $data[3] == $val) {
                $output .= ' selected ';
            }

            $output .= ">{$val}</option>\n";

            if ($opt && $key == 'news_read') {
                $output .= "</optgroup><optgroup label=\"{$langDleFilter['admin']['settings']['xfield_field']}\">";
            }
        }

        $output .= "</optgroup>";

        $inputElemet = $data[5] ? ' disabled' : '';
        $inputElemet .= $data[4] ? ' multiple' : '';
        $inputElemet .= $data[6] ? " data-placeholder=\"{$data[6]}\"" : '';

		$disa = '';
		if ($dis) {
			$disa = 'data-dis="' . implode(',', $dis[$data[0]]) . '"';
		}

        return '<select ' . $disa . ' id="'. $data[0] . '" name="' . $data[0] . '" class="selectTag" ' . $inputElemet . '>' . $output . '</select>';
    }

    /**
     * Параметры textarea
     *
     * @param    array    $data
	 * @param 	 bool|array $dise
     * @return   string
     **/
    static function textarea($data, $dise = false)
    {
        $input_elemet = $data[2] ? ' placeholder="' . $data[2] . '"' : '';
        $input_elemet .= $data[3] ? ' disabled' : '';
		$dis = '';
		if ($dise) {
			$dis = 'data-dis="' . implode(',', $dise[$data[0]]) . '"';
		}

        return '<textarea ' . $dis . ' id="' . $data[0] . '" style="min-height:150px;max-height:150px;min-width:333px;max-width:100%;border:1px solid #ddd;padding:5px;" autocomplete="off" class="form-control" name="' . $data[0] . '" ' . $input_elemet . '>' . $data[1] . '</textarea>';
    }

    /**
     * Параметры textarea
     *
     * @param    array    $a
     * @return   string
     **/
    static function menu($a)
    {
        $m = [];
        $i = 1;
        foreach ($a as $menu) {
            if ($i == 1) {
                $m[] = '<div class="row box-section">';
            }
            $m[] = '<div class="col-sm-6 media-list media-list-linked">
                <a class="media-link" href="' . $menu['link'] . '">
                    <div class="media-left"><img src="' . $menu['icon'] . '" class="img-lg section_icon"></div>
                    <div class="media-body">
                        <h6 class="media-heading text-semibold">' . $menu['title'] . '</h6>
                        <span class="text-muted text-size-small">' . $menu['descr'] . '</span>
                    </div>
                </a>
            </div>';
            if ($i == 2) {
                $m[] = '</div>';
                $i = 0;
            }
            $i++;
        }

        if ($i == 2) {
            $m[] = "</div>";
        }

        $m = implode($m);
        return $m;
    }
}
