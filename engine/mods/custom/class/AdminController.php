<?php

namespace Sandev;

class AdminController
{

	/** @var array Глобальный конфиг модуля */
	private $config = [];

	/** @var Object Модель работы с данными блоков */
	private $data;

	/** @var string Текст AJAX ошибки */
	private $error = '';

	/** @var array типы данных у полей */
	private $type = [];

	/**
	 * Конструктор
	 * @param array $config  глобальный конфиг модуля
	 * @param object &$data  модель работы с блоками
	 */
	public function __construct($config, &$data)
	{
		$this->config = $config;
		$this->data = $data;
	}

	/**
	 * Парсер имени блока
	 */
	private function latinParse($val = ''): string
	{
		$val = (string)$val;
		$val = totranslit($val, true, false);
		$val = preg_replace('#^[0-9]+#is', '', $val);
		$val = trim($val);
		return $val;
	}

	/**
	 * Установка типов полей
	 * @param string $type   тип: bool, int, nav
	 * @param array  $values имена полей
	 */
	public function setTypeHint(string $type, array $values): void
	{
		$this->type[$type] = $values;
	}

	/**
	 * Получить ответ AJAX, выбор действия _POST['action']
	 * @return bool|array
	 * Если false, значит можно получить ошибку методом getError()
	 */
	public function getResponse()
	{
		$action = $this->latinParse($_POST['action']);
		$action = ucfirst($action) . 'Action';
		if (method_exists($this, $action)) {
			return $this->$action();
		}
		return $this->setError('undefined_action');
	}

	/**
	 * Получить данные блока для окна редактирования
	 * @return bool|array
	 */
	private function openEditAction()
	{
		$name = $this->latinParse($_POST['name']);
		if (!$this->data->isset($name)) return $this->setError('block_not_found');
		return $this->data->getItem($name);
	}

	/**
	 * Сохранить параметры блока
	 * @return bool|array
	 */
	private function saveBlockAction()
	{
		$name = $this->latinParse($_POST['name']);
		if (!$this->data->isset($name)) return $this->setError('block_not_found');
		if (!isset($_POST['con'])) return $this->setError('wrong_data');

		$this->data->updateItem($name, $_POST['con'], $this->type);
		
		if ($this->data->getItem($name)['template']) {
			if (in_array($this->data->getItem($name)['template'], ['main', 'navigation'])) {
				return $this->setError('tpl_not_allowed');
			} elseif (!file_exists(TEMPLATE_DIR . '/custom/' . $this->data->getItem($name)['template'] . '.tpl')) {
				return $this->setError('tpl_not_found');
			}
		}

		$new_name = $this->latinParse($_POST['con']['name']);
		if ($new_name && $new_name !== $name) {
			if ($this->data->renameItem($name, $new_name)) {
				$name = $new_name;
			} else {
				return $this->setError('name_exists');
			}
		} else {
			$new_name = '';
		}

		$this->data->saveData();
		clear_cache('news');

		$data = $this->data->getItem($name);
		$data['new_name'] = $new_name;
		return $data;
	}

	/**
	 * Добавить/импортировать новый блок
	 * @return bool|array
	 */
	private function doAddBlockAction()
	{
		$name = $this->latinParse($_POST['name']);
		if (!$name) return $this->setError('name_empty');
		if ($this->data->isset($name)) return $this->setError('name_exists');

		$this->data->addItem($name);

		$import = trim($_POST['import']);
		if ($import) {
			$import = json_decode($import, true);
			if (is_array($import)) {
				$import['active'] = false;
				$this->data->updateItem($name, $import, $this->type);
			} else {
				return $this->setError('import_error');
			}
		}

		$this->data->saveData();
		return $this->data->getItem($name);
	}

	/**
	 * Удалить блок
	 * @return bool|array
	 */
	private function deleteBlockAction()
	{
		$name = $this->latinParse($_POST['name']);
		if (!$this->data->isset($name)) return $this->setError('block_not_found');
		$this->data->dropItem($name);
		$this->data->saveData();
		return ['del' => 'ok'];
	}

	/**
	 * Изменить сортировку блоков
	 * @return bool|array
	 */
	private function changeSortAction()
	{
		if (!isset($_POST['sort']) || !is_array($_POST['sort'])) return $this->setError('wrong_data');
		$this->data->changeSort($_POST['sort']);
		$this->data->saveData();
		return ['sort' => 'ok'];
	}

	private function clearCacheAction()
	{
		unlink(ENGINE_DIR . '/mods/custom/data/cache.dat');
		return ['clear' => 'ok'];
	}

	/**
	 * Открыть окно глобальных настроек
	 */
	private function showConfigAction(): array
	{
		return $this->config;
	}

	/**
	 * Сохранить настройки
	 * @return bool|array
	 */
	private function saveConfigAction()
	{
		if (!isset($_POST['con'])) return $this->setError('wrong_data');
		foreach ($this->type['bool'] as $v) {
			$this->config[$v] = isset($_POST['con'][$v]);
		}
		
		foreach ($this->type['int'] as $v) {
			$val = (int)$_POST['con'][$v];
			$val < 0 && $val = 0;
			$this->config[$v] = $val;
		}
		
		$this->config['nav_type'] = $this->latinParse($_POST['con']['nav_type']);
		in_array($this->config['nav_type'], $this->type['nav']) || $this->config['nav_type'] = $this->type['nav'][0];

		file_put_contents(ENGINE_DIR . '/mods/custom/data/config.php', '<?php return ' . var_export($this->config, true) . ';', LOCK_EX);
		function_exists('opcache_reset') && opcache_reset();
		clear_cache('news');
		return $this->config;
	}

	/**
	 * Активация модуля
	 * @return bool|array
	 */
	private function activateAction()
	{
		$key = (string)$_POST['key'];
		if ($this->data->activate($key)) {
			return ['key' => 'ok'];
		}
		return $this->setError('wrong_key');
	}

	/**
	 * Зафиксировать ошибку обработчика и вернуть false
	 */
	private function setError(string $error): bool
	{	
		$this->error = $error;
		return false;
	}

	/**
	 * Получить текст ошибки, если он есть
	 */
	public function getError(): string
	{
		return $this->error;
	}

}
