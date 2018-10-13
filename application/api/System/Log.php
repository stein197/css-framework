<?php
	namespace System;

	/**
	 * Набор статичных методов для упрощения отладки переменных на странице в браузере
	 */
	abstract class Log{

		/** @var int $level Уровень табуляции для вывода <code>self::println()</code> */
		public static $level = 0;

		/**
		 * Простой вывод строки в браузер
		 * @param string ...$data Выводимые строки
		 * @return void
		 */
		public static function println(...$data):void{
			if(self::$level){
				self::printLevel(...$data);
				return;
			}
			echo '<pre>';
			foreach($data as $item){
				echo $item.'<br/>';
			}
			if(!sizeof($data)){
				echo '<br/>';
			}
			echo '</pre>';
		}

		/**
		 * Простой дамп переменной
		 * @param ...$data Аргументы, передаваемые в <code>vard_dump()</code>
		 * @return void
		 */
		public static function dump(...$data):void{
			ob_start();
			echo '<pre>';
			foreach($data as $item){
				if(is_array($item) || $item instanceof stdClass){
					echo json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				} else {
					var_dump($item);
				}
			}
			echo '</pre>';
			echo ob_get_clean();
		}

		public static function printStackTrace():void{
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_clean();
			self::println($trace);
		}

		/**
		 * Вывод строки в браузер с указанным уровнем табуляции. Работает только при указании уровня
		 */
		private static function printLevel(...$data):void{
			echo '<pre>';
			foreach($data as $item){
				echo str_repeat("\t", self::$level).$item.'<br/>';
			}
			if(!sizeof($data)){
				echo '<br/>';
			}
			echo '</pre>';
		}
	}