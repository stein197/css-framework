<?php
	namespace Application;
	use \Exception;

	/**
	 * Класс, который управляет потоком вывода результатов скрипта
	 * С помощью используемых здесь методов, доступно использование отложенных функций,
	 * т.е. вывод контента выше по коду, где определен сам контент
	 * Использутся классом <code>Application</code>
	 * Использование методов класса имеет смысл только после вызова <code>self::start()</code> и до вызова <code>self::end()</code>
	 * Свойства и секции имеют схожий механизм работы, за исключением, что свойства это небольшие порции данных (обычно строковые),
	 * а секции - раздельные куски выводимого контента, заключенные между вызовами методов <code>self::startSection()</code> и <code>self::endSection()</code>
	 * @todo Некорректная работа вложенных секций
	 * @version 1.0
	 */
	abstract class Buffer{

		/** @var string[] $properties Массив имен и значений отложенных свойств */
		private static $properties = [];
		/** @var string[] $sections Массив имен и соответствующие именам содержимое секций */
		private static $sections = [];
		/** @var string[] $stack Стек буферизируемого контента. Если не вызываются функции show*, то этот массив будет содержать только один элемент - полный контент страницы */
		private static $stack = [];
		/** @var bool $hasRun Внутренняя переменная, показывающая, запущен ли механизм буферизации */
		private static $hasRun = false;

		/**
		 * Запускает процесс буферизации. Вложенные запуски (т.е. несколько подряд идущих вызовов без соответствующих <code>self::end()</code>) не допускаются,
		 * вследствие чего, уровень буферизации будет всегда равен <code>1</code>
		 * Обычно используется единственный раз за весь цикл жизни приложения
		 * @return void
		 */
		final public static function start():void{
			if(!self::$hasRun){
				ob_start();
				self::$hasRun = true;
			}
		}

		/**
		 * Завершает процесс буферизации и очищает все содержимое внутренних массивов. После вызова возможно повторное использование <code>self::start()</code>
		 * Обычно используется единственный раз за весь цикл жизни приложения
		 * @return void
		 * @uses self::clear()
		 */
		final public static function end():void{
			self::$hasRun = false;
			self::$stack[] = ob_get_clean();
			foreach(self::$stack as $k => $piece){
				switch($k{0}){
					case 'p':
						echo @self::$properties[substr($k, 2)];
						break;
					case 's':
						echo @self::$sections[substr($k, 2)];
						break;
					default:
						echo $piece;
				}
			}
			self::clear();
		}

		/**
		 * Устанавливает произвольное свойство
		 * @param string $name Имя свойства
		 * @param mixed $value Значение свойства
		 * @return void
		 */
		final public static function setProperty(string $name, $value):void{
			self::checkBufferRunning();
			self::$properties[$name] = $value;
		}

		/**
		 * Возвращает значение свойства или <code>null</code>, если оно еще не було установлено
		 * @param string $name Имя возвращаемого свойства
		 * @return string|null
		 */
		final public static function getProperty(string $name):?string{
			self::checkBufferRunning();
			return @self::$properties[$name] ?: null;
		}

		/**
		 * Выводит указанное значение в браузер
		 * @param string $name Имя
		 * @return void
		 */
		final public static function showProperty(string $name):void{
			self::checkBufferRunning();
			self::$stack[] = ob_get_clean();
			self::$stack['p_'.$name] = '';
			ob_start();
		}

		/**
		 * Запускает процесс буферизации секции для идущего после вызова контента
		 * @param string $name Имя секции
		 * @return void
		 */
		final public static function startSection(string $name):void{
			self::checkBufferRunning();
			self::$stack[] = ob_get_clean();
			self::$sections[$name] = '';
			ob_start();
		}

		/**
		 * Завершает буферизацию секции и сохраняет значение во внутрнений массив
		 * @param string $name Имя секции
		 * @return void
		 */
		final public static function endSection(string $name):void{
			self::checkBufferRunning();
			self::$sections[$name] = ob_get_clean();
			ob_start();
		}

		/**
		 * Выводит указанную секцию в браузер
		 * @param string $name Имя
		 * @return void
		 */
		final public static function showSection(string $name):void{
			self::checkBufferRunning();
			self::$stack[] = ob_get_clean();
			self::$stack['s_'.$name] = '';
			ob_start();
		}
		
		/**
		 * Возвращает содержимое указанное секции или <code>null</code>, если таковой нет
		 * @param string $name Имя секции
		 * @return string|null
		 */
		final public static function getSection(string $name):?string{
			self::checkBufferRunning();
			return @self::$sections[$name] ?: null;
		}

		/**
		 * Очищает внутренний буфер
		 * @return void
		 */
		final public static function clear():void{
			self::$stack = [];
			self::$properties = [];
			self::$sections = [];
		}

		/**
		 * Проверяет, запущен ли процесс буферизации
		 * @return void
		 * @throws \Exception Если есть попытка вызова метода перед вызовом <code>self::start()</code>
		 */
		final protected static function checkBufferRunning():void{
			if(!self::$hasRun){
				$fName = debug_backtrace()[1]['function'];
				throw new Exception('Cannot call \\'.self::class."::{$fName}".' method before starting bufferization');
			}
		}

	}