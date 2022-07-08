<?php if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/base/registry.php
 *
 * Реєстр
 */

class Registry {
	
	private $records = array();
	private static $instance;
	
	private function __construct(){}
	
	/**
	 * Сінглтон паттерн
     * Контролює створення тільки однієї сутності
	 */
	static function singleton(){
		if(!isset(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Отримуємо об'єкт
	 *
	 * @params $key назва об'єкту
	 *
	 * @return об'єкт
	 */
	function get($key){
		if(isset($this->records[$key])){
			return $this->records[$key];
		}
		return null;
	}

	/**
	 * Зберігаємо об'єкт
	 *
	 * @params $key назва об'єкту
	 * @params $value сутність об'єкту
	 * @params $override перезаписати існуючий об'єкт чи ні
	 */
	function set($key, $value, $override = false){
		if(isset($this->records[$key])){
			if($override){
				$this->records[$key] = $value;
			}
		} else {
			$this->records[$key] = $value;
		}
	}
	
}

?>
