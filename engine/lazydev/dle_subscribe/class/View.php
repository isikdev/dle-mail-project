<?php
/**
* Вспомогательный класс шаблонизатора
*
* @link https://lazydev.pro/
* @author LazyDev <email@lazydev.pro>
**/

namespace LazyDev\Subscribe;

use dle_template;

class View extends dle_template
{
    /**
    * Конструктор
    *
    * @param    string    $tpl
    * @param    string    $dleModule
    * @param    string    $template
    **/
	public function __construct($tpl = '', $dleModule = '', $template = '')
	{
		parent::__construct();
		$this->dir = TEMPLATE_DIR . '/lazydev/dle_subscribe';
		if ($tpl) {
            if (file_exists($this->dir . '/' . $template . '.tpl')) {
                $tpl = $template;
            } elseif (file_exists($this->dir . '/' . $tpl . '_' . $dleModule . '.tpl')) {
                $tpl = $tpl . '_' . $dleModule;
            }

			$this->load_template($tpl . '.tpl');
		}
	}
    
    /**
    * Упрощенный код шаблонизации блоков
    *
    * @param    mixed   $data
    * @param    string  $tag
    **/
    public function tagIf($data, $tag)
    {
        $this->copy_template = preg_replace_callback("#\\[if\s(!?){$tag}\\](.*?)\\[/if\s{$tag}\\]#is", function ($m) use($data, $tag) {
            if ($m[2]) {
                $elseBool = false;
                if (strpos($m[2], '[else ' . $tag . ']') > 1) {
                    $elseBool = true;
                }
                
                if ($elseBool && (!$data && !$m[1] || $data && $m[1])) {
                    return explode('[else ' . $tag . ']', $m[2])[1];
                } elseif ($elseBool && (!$m[1] && $data || !$data && $m[1])) {
                    return explode('[else ' . $tag . ']', $m[2])[0];
                } elseif (!$m[1] && $data || !$data && $m[1]) {
                    return $m[2];
                }
            }
        }, $this->copy_template);
    }
    
    /**
    * Условия
    *
    * @param    array   $data
    * @return   string
    **/
    public function checkIf($data)
    {
        if ($data[2] == '=') {
            return $data[$data[1]] == $data[3] ? $data[4] : '';
        }

        if ($data[2] == '!=') {
            return $data[$data[1]] != $data[3] ? $data[4] : '';
        }

        return $data[0];
    }
}
