<?php

namespace Sandev;

class Pagination
{
	private $shifts = ['from' => 0, 'to' => 0];

	private $page_current = 1;

	private $page_count = 1;

	public function __construct(int $page_count, int $page_current)
	{
		$this->page_count = $page_count;
		$this->page_current = $page_current;
	}

	public function buildHtml(string $type): string
	{
		if ($this->page_count <= 1) return '';

		$tpl = file_get_contents(TEMPLATE_DIR . '/custom/navigation.tpl');

		$tpl = preg_replace("'\\{\\*(.*?)\\*\\}'si", '', $tpl);

		$tpl = preg_replace_callback('#\\[type=(.+?)\\](.+?)\\[/type\\]#is', function($m) use ($type){
			$types = explode(',', strtolower($m[1]));
			return in_array($type, $types) ? $m[2] : '';
		}, $tpl);

		$tpl = str_ireplace('{page-num}', $this->page_current, $tpl);
		$tpl = str_ireplace('{page-max}', $this->page_count, $tpl);

		$tpl = preg_replace_callback('#\\[prev\\](.+?)\\[/prev\\]#is', function($m){
			$prev = explode('[else]', $m[1]);
			if ($this->page_current == 1) {
				return $prev[1];
			} else {
				$prev[0] = str_ireplace('{prev-page}', $this->page_current - 1, $prev[0]);
				return $prev[0];
			}
		}, $tpl);

		$tpl = preg_replace_callback('#\\[next\\](.+?)\\[/next\\]#is', function($m){
			$prev = explode('[else]', $m[1]);
			if ($this->page_current >= $this->page_count) {
				return $prev[1];
			} else {
				$prev[0] = str_ireplace('{next-page}', $this->page_current + 1, $prev[0]);
				return $prev[0];
			}
		}, $tpl);

		if (stripos($tpl, '{pages') !== false) {
			$tpl = preg_replace_callback('#\\{pages(.*?)\\}#i', [$this, 'buildPagesList'], $tpl);
		}
		
		return $tpl;
	}

	private function buildPagesList(array $m): string
	{
		if ($this->page_count <= 1) return '';
		$paramStr = trim($m[1]);

		$spread = 3;
		if (preg_match('#spread="(\d+)"#', $m[1], $m)) {
			$temp = (int)$m[1];
			$temp > 0 && $spread = $temp;
		}
		$this->getShifts($spread);

		$pages = [];
		$pages[] = $this->buildItem(1);

		if ($this->shifts['from'] == 3) {
			$pages[] = $this->buildItem(2);
		} elseif ($this->shifts['from'] > 3) {
			$pages[] = $this->getExt();
		}

		for ($i = $this->shifts['from']; $i <= $this->shifts['to']; $i++) {
			$pages[] = $this->buildItem($i);
		}

		if ($this->shifts['to'] == $this->page_count - 2) {
			$pages[] = $this->buildItem($this->page_count - 1);
		} elseif ($this->shifts['to'] < $this->page_count - 1) {
			$pages[] = $this->getExt();
		}

		$pages[] = $this->buildItem($this->page_count);

		return join($pages);
	}

	private function getShifts(int $spread = 3): void
	{
		// Начало каретки
		$shift_start = max($this->page_current - $spread, 2);
		// Конец каретки
		$shift_end = min($this->page_current + $spread, $this->page_count - 1);

		// Находимся в начале, поэтому нужно показывать больше кнопочек
		if ($shift_end < $spread * 2) {
			$shift_end = min($spread * 2, $this->page_count - 1);
		}
		// Находимся в конце, поэтому нужно показывать больше кнопочек
		if ($shift_end == $this->page_count - 1 && $shift_start > 3) {
			$shift_start = max(3, min($this->page_count - $spread * 2 + 1, $shift_start));
		}
		$this->shifts = [
			'from'	=> $shift_start,
			'to'	=> $shift_end,
		];
	}

	private function buildItem(int $page): string
	{
		if ($page == $this->page_current) {
			return sprintf('<span class="active">%d</span>', $page);
		} else {
			return sprintf('<a href="#" data-page="%1$d">%1$d</a>', $page);
		}
	}

	private function getExt(): string
	{
		return '<span class="nav_ext">...</span>';
	}
}
