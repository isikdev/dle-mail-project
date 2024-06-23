<?php
/**
 * Подключение и инициализация минипостера для главной страницы
 * 
 * @package Miniposter
 * @link https://sandev.pro/
 * @author Oleg Odoevskyi (Sander) <oleg.sandev@gmail.com>
 */

require_once ENGINE_DIR . '/mods/miniposter/loader.php';
(new Miniposter())->build($tpl->result['main']);
