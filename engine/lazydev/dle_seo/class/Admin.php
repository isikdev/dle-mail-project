<?php
/**
* Класс для работы с админ панелью
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Seo;

class Admin
{

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
		$helper = $helper ? '<i class="color-warning help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right" data-rel="popover" data-trigger="hover" data-placement="right" data-content="' . $helper . '" data-original-title="" title=""></i>' : '';
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
        $inputElemet = $data[3] ? ' placeholder="' . $data[3] . '"' : '';
        $inputElemet .= $data[4] ? ' disabled' : '';
        if ($data[1] == 'range') {
            $class = ' custom-range';
            $inputElemet .= $data[5] ? ' step="' . $data[5] . '"' : '';
            $inputElemet .= $data[6] ? ' min="' . $data[6] . '"' : '';
            $inputElemet .= $data[7] ? ' max="' . $data[7] . '"' : '';
        } elseif ($data[1] == 'number') {
            $class = ' w-9';
            $inputElemet .= $data[5] ? ' min="' . $data[5] . '"' : '';
            $inputElemet .= $data[6] ? ' max="' . $data[6] . '"' : '';
        }
        return '<input type="' . $data[1] . '" autocomplete="off" style="float: right;" value="' . $data[2]. '" class="form-control' . $class . '" name="' . $data[0] . '" ' . $inputElemet . '>';
    }

	/**
	 * Параметры checkbox
	 *
	 * @param string $name
	 * @param bool $checked
	 * @param string $id
	 * @param bool $disabled
	 * @param bool|array $connect
	 * @param bool|array $lang
	 *
	 * @return string
	 */
	static function checkBox($name, $checked, $id, $disabled = false, $connect = false, $lang = false)
	{
		global $dleSeoLang;

		$checked = $checked ? 'checked' : '';
		$disabled = $disabled ? 'disabled' : '';
		$data = '';
		if ($connect) {
			$data = 'data-dis="' . implode(',', $connect[$id]) . '"';
		}

		if (!$lang) {
			$lang = [$dleSeoLang['admin']['turn_on'], $dleSeoLang['admin']['turn_off']];
		}

return <<<HTML
<div class="can-toggle can-toggle--size-small">
	<input id="{$id}" {$data} name="{$name}" value="1" type="checkbox" {$checked} {$disabled}>
	<label for="{$id}">
		<div class="can-toggle__switch" data-checked="{$lang[0]}" data-unchecked="{$lang[1]}"></div>
	</label>
</div>
HTML;
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
     * @return   string
     **/
    static function select($data)
    {
        $output = '';
        foreach ($data[1] as $key => $val) {
            if ($data[2]) {
                $output .= "<option value=\"{$key}\"";
            } else {
                $output .= "<option value=\"{$val}\"";
            }
            
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
        }
        $inputElemet = $data[5] ? ' disabled' : '';
        $inputElemet .= $data[4] ? ' multiple' : '';
        $inputElemet .= $data[6] ? " data-placeholder=\"{$data[6]}\"" : '';

        return '<select name="' . $data[0] . '" class="selectTag" ' . $inputElemet . '>' . $output . '</select>';
    }

    /**
     * Параметры textarea
     *
     * @param    array    $data
     * @return   string
     **/
    static function textarea($data)
    {
		$input_elemet = $data[2] ? ' placeholder="' . $data[2] . '"' : '';
		$input_elemet .= $data[3] ? ' disabled' : '';

        return '<textarea style="min-height:150px;max-height:150px;min-width:333px;max-width:100%;border:1px solid #ddd;padding:5px;" autocomplete="off" class="form-control" name="' . $data[0] . '" ' . $input_elemet . '>' . $data[1] . '</textarea>';
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
            $m[] = '<div class="col-sm-4 media-list media-list-linked">
                <a class="media-link" href="' . $menu['link'] . '">
                    <div class="media-left"><img src="' . $menu['icon'] . '" class="img-lg section_icon"></div>
                    <div class="media-body">
                        <h6 class="media-heading text-semibold">' . $menu['title'] . '</h6>
                        <span class="text-muted text-size-small">' . $menu['descr'] . '</span>
                    </div>
                </a>
            </div>';
            if ($i == 3) {
                $m[] = '</div>';
                $i = 0;
            }
            $i++;
        }

		$m[] = "</div>";
        
        $m = implode($m);
        return $m;
    }
}
