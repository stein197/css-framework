<?php
	namespace System;

	use \ReflectionClass;

	checkExtension('Reflection');

	/**
	 * Набор статичных методов для упрощения отладки переменных и вывода данных на странице в браузере
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
		 * Простой дамп переменной. Если переменную можно преобразовать в JSON-объект (массив или объект класса <code>stdClass</code>),
		 * то выводится форматированное (с отступами) JSON-представление переменной
		 * @param ...$data Аргументы, передаваемые в <code>var_dump()</code>
		 * @return void
		 */
		public static function dump(...$data):void{
			ob_start();
			echo '<pre>';
			foreach($data as $item){
				if(is_array($item) || $item instanceof stdClass){
					echo json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				} elseif(is_object($item)){
					$r = new ReflectionClass(get_class($item));
					$hasTrait = false;
					foreach($r->getTraits() as $trait){
						if($trait->name === ObjectDump::class){
							$hasTrait = true;
							break;
						}
					}
					if($hasTrait){
						echo json_encode($item->dump(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
					} else {
						var_dump($item);
					}
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