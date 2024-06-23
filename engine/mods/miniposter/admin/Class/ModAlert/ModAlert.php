<?php
/**
 * Обработка и отображение информационных сообщений
 *
 * @package ModAlert
 * @link https://sandev.pro/
 * @link https://github.com/San-Dev/ModAlert/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 * @version 1.0 (30.12.2017)
 * @license MIT License
 */

/**
 * Пример использования:
 * ModAlert::setMsg('Настройки усешно сохранены', 'success');
 * ModAlert::setMsg('Файл настроек не доступен для записи', 'error');
 * ModAlert::setMsg('Что-то может пойти не так или требует внимания', 'warning');
 * ModAlert::setMsg('Сообщение ни о чем, просто так для информации, чтобы было', 'info');
 *
 * Для вывода сообщений:
 * echo ModAlert::getList();
 */


class ModAlert
{

	/**
	 * Possible message groups and their titles
	 * @var array
	 */
	private static $message_group = [
		'success' => 'Success',
		'error' => 'Error',
		'warning' => 'Warning',
		'info' => 'Information'
	];


	/**
	 * Something like singleton
	 * @var boolean
	 */
	private static $init = false;


	/**
	 * Gets messages list
	 * 
	 * @return mixed
	 */
	public static function getList()
	{
		if (!$_SESSION['mod_info']) {
			return '';
		}
		$html = self::buildHtml();
		return $html;
	}


	/**
	 * Build html
	 * 
	 * @return mixed
	 */
	private static function buildHtml()
	{
		ob_start();
		include_once __DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'listView.php';
		$_SESSION['mod_info'] = [];
		$content = ob_get_clean();
		return $content;
	}


	/**
	 * Set message
	 * 
	 * @param string|array $message message text
	 * @param string $group   message group
	 */
	public static function setMsg($message, $group = 'info')
	{
		if (!self::$init) {
			self::$init = true;
			$_SESSION['mod_info'] = [];
		}

		try {
			if (is_array($message)) {
				foreach ($message as $mv) {
					self::setMessage($mv, $group);
				}
			} else {
				self::setMessage($message, $group);
			}
		} catch (Exception $e) {
			msg('error', __CLASS__ . ' error', $e->getMessage());
		}
	}


	/**
	 * Save message
	 * 
	 * @param string $message
	 * @param string $group   message group
	 * @throws Exception input errors
	 */
	private static function setMessage($message, $group){
		if (!$group || !self::$message_group[$group]) {
			throw new Exception('Undefined message group "'. $group . '"');
		}
		$message = trim($message);
		if (!$message) {
			throw new Exception('Empty message');
		}
		if (!is_array($_SESSION['mod_info'])) {
			$_SESSION['mod_info'] = [];
		}
		$_SESSION['mod_info'][] = [
			'type'    => $group,
			'message' => $message
		];
	}
}
