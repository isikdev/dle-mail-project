<?php
/**
 * Miniposter main class
 * 
 * @package Miniposter
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

class Miniposter extends Miniposter_pro
{
	/**
	 * Доступные форматы для обработки
	 * @var array
	 */
	protected $available_type = ['jpg', 'png', 'gif', 'webp'];

	/**
	 * Исходный тип файла
	 * @var string
	 */
	protected $type = '';


	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Initialization
	 * 
	 * @param  string &$content контент
	 * @return void
	 */
	public function build(&$content = '')
	{
		$content = preg_replace_callback("#\[miniposter\s*=\s*([^\]]+)\](.*?)\[/miniposter\]#is", [$this, 'parse'], $content);
	}


	/**
	 * Parsing image srcs and replace it
	 * 
	 * @param  array $matches group area
	 * @return string
	 */
	protected function parse($matches = [])
	{
		$content = trim($matches[2]);
		if (isset($this->group[$matches[1]]) && $this->config['enabled']) {
			if ($this->group[$matches[1]]['enabled']) {
				self::$local = array_merge($this->config, $this->group[$matches[1]]);
				self::$local['folder'] = $matches[1];
				// <img src="..." />
				$content = preg_replace_callback("#<img.*?src=['\"]([^'\"]+)['\"]#i", [$this, 'createImage'], $content);
				// background: url(...);
				$content = preg_replace_callback("#url\(['\"]?([^'\")]+)['\"]?\)#i", [$this, 'createImage'], $content);
				// Для lazyLoad data-bg="..."
				$content = preg_replace_callback("#data-bg=['\"]([^'\"]+)['\"]#i", [$this, 'createImage'], $content);
			}
		}
		return $content;
	}

	/**
	 * Real src data, for callback
	 * 
	 * @param  array $matches
	 * @param  string $save_path file path
	 * @return string
	 */
	protected function replaceSrc($matches = [], $save_path = '')
	{
		return str_replace($matches[1], $save_path, $matches[0]);
	}

	/**
	 * Trying to create poster
	 * 
	 * @param  array $matches src data
	 * @return string new poster src
	 */
	protected function createImage($matches = [])
	{
		$def_type = $type = $this->getFileType($matches[1]);

		self::$local['force_jpg'] && $type = 'jpg';
		self::$local['force_type'] && $type = self::$local['force_type'];
		!in_array($type, $this->available_type) && $type = 'jpg';

		if ($type == 'gif' && self::$local['ignore_gif']) {
			return $matches[0];
		}

		if ($type == 'webp' && stripos((string)$_SERVER['HTTP_ACCEPT'], 'image/webp') === false) {
			$type = $def_type == 'png' ? 'png' : 'jpg';
		}
		
		$is_remote = 0;
		$src_parse = parse_url($matches[1]);
		if (!$src_parse['host'] || strtolower($src_parse['host']) == strtolower($_SERVER['HTTP_HOST'])) {
			$matches[1] = $src_parse['path'];
		} elseif($src_parse['host']) {
			$is_remote = 1;
		}

		// No external images
		if ($is_remote && !self::$local['allow_remote']) {
			return $matches[0];
		}

		// This is "data:image/xxx;base64,..." image. Ignore 'em
		if (stripos($matches[1], 'data') === 0) {
			return $matches[0];
		}

		// Ignore template images. You should use other static external services
		if (stripos($matches[1], '/templates/') === 0) {
			return $matches[0];
		}

		// Smileys? For real?
		if (stripos($matches[1], '/engine/data/') === 0) {
			return $matches[0];
		}

		$save_path = [''];
		$save_path[] = trim(self::$local['save_path'], '/');
		$save_path[] = self::$local['folder'];

		$hash = md5($matches[1]);
		if (self::$local['folder_len']) {
			$save_path[] = substr($hash, 0, self::$local['folder_len']);
		}

		$root_path = ROOT_DIR . join(DIRECTORY_SEPARATOR, $save_path);
		if (!is_dir($root_path)) {
			mkdir($root_path, 0777, true);
			@chmod($root_path, 0777);
		}

		// Image name
		if (($is_remote && $this->config['remote_rename']) || !self::$local['real_name']) {
			$save_path[] = substr($hash, self::$local['folder_len'], 30) . '.' . $type;
		} else {
			$basename = basename($matches[1]);
			$basename = explode('.', $basename);
			array_pop($basename);
			$basename = join('.', $basename);
			if (!$basename) {
				$basename = substr($hash, self::$local['folder_len'], 30);
			}
			$save_path[] = totranslit($basename, true, false) . '.' . $type;
		}
		$root_path = ROOT_DIR . join(DIRECTORY_SEPARATOR, $save_path);
		$image_path = join('/', $save_path);

		// Poster already exists
		if (file_exists($root_path)) return $this->replaceSrc($matches, $image_path);

		$image = $this->imageCreate($matches[1], $is_remote);
		if (!$image) return $this->replaceSrc($matches, self::$local['default']);

		$iw = @imagesx($image);
		$ih = @imagesy($image);
		
		// This file is not an image
		if ($iw<1 || $ih<1) {
			return $this->replaceSrc($matches, self::$local['default']);
		}

		$poster = $this->getPoster($image, str_replace('webp', 'png', $type));
		$this->createPoster($poster, $root_path, $type);

		return $this->replaceSrc($matches, $image_path);
	}

	/**
	 * Создание изображения
	 * @return resource
	 */
	protected function imageCreate($src = '', $is_remote = false)
	{
		if ($is_remote) {
			$ch = curl_init($src);
			curl_setopt_array($ch, [
				CURLOPT_HEADER         => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_CONNECTTIMEOUT => self::$local['timeout'],
				CURLOPT_TIMEOUT        => self::$local['timeout'],
				CURLOPT_FOLLOWLOCATION => 1,
			]);
			$img_data = curl_exec($ch);
			curl_close($ch);
			!$img_data && $img_data = file_get_contents(ROOT_DIR . '/' . trim(self::$local['default'], '/'));
			$image = imagecreatefromstring($img_data);
		} else {
			$info = getimagesize(ROOT_DIR . $src);
			$type = explode('/', $info['mime']);
			$type = end($type);
			$type = strtolower($type);
			$type = str_replace('jpeg', 'jpg', $type);
			$this->type = $type;
			!in_array($this->type, $this->available_type) && $this->type = '';

			if ($this->type == 'jpg') {
				$image = imagecreatefromjpeg(ROOT_DIR . $src);
			} elseif ($this->type == 'gif') {
				$image = imagecreatefromgif(ROOT_DIR . $src);
			} elseif ($this->type == 'png') {
				$image = imagecreatefrompng(ROOT_DIR . $src);
			} elseif ($this->type == 'webp') {
				$image = imagecreatefromwebp(ROOT_DIR . $src);
			} else {
				$img_data = file_get_contents(ROOT_DIR . $src);
				$image = imagecreatefromstring($img_data);
			}
		}
		return $image;

	}

	/**
	 * Получить расширение файла
	 * @param  string $path адрес файла
	 * @return string
	 */
	protected function getFileType($path = '')
	{
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$type = strtolower($type);
		$type = str_replace('jpeg', 'jpg', $type);
		!in_array($type, $this->available_type) && $type = 'jpg';
		$this->type = $type;
		return $type;
	}

	/**
	 * Making small poster
	 * 
	 * @param  resource $image     Source image
	 * @param  string   $save_path Image save path
	 * @param  string   $type      Image type
	 * @return void
	 */
	protected function createPoster($poster, $save_path = '', $type = '')
	{
		if ($type == 'gif') {
			imagegif( $poster, $save_path);
		} elseif ($type == 'png') {
			imagepng( $poster, $save_path, 9);
			$this->optipng($save_path);
		} elseif ($type == 'webp') {
			imagewebp($poster, $save_path, self::$local['quality']);
		} else {
			imagejpeg( $poster, $save_path, self::$local['quality'] );
			$this->jpegOptim($save_path);
		}
		imagedestroy($poster);
	}


	/**
	 * Using utility jpegoptim to optimize/compress JPEG images
	 * @link https://github.com/tjko/jpegoptim
	 * 
	 * @param  string  $file    full path to image
	 * @param  integer $quality image quality
	 * @return void
	 */
	protected function jpegOptim($file = '')
	{
		if (self::$local['jpegoptim']) {
			exec("jpegoptim --max=" . self::$local['quality'] . " --strip-all $file");
		}
	}

	/**
	 * Using utility OptiPNG to optimize/compress PNG images
	 * 
	 * @param  string  $file    full path to image
	 * @return void
	 */
	protected function optipng($file = '')
	{
		if (self::$local['optipng']) {
			exec('optipng -o2 -strip all ' . $file);
		}
	}
}