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
	 * Секции могут быть вложены друг в друга, при этом уроведь буферизации всегда будет равен 2
	 * @version 1.1
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
		/** @var string[] $sectionDepth Массив, показывающий текущий уровень вложенности секций. Содержит имена секций */
		private static $sectionDepth = [];

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
		 * @param bool $print Если <code>true</code>, то результат выведется в браузер, иначе вернется содержимое буфера в виде строки
		 * @return string|null
		 * @uses self::clear()
		 */
		final public static function end(bool $print = true):?string{
			self::$hasRun = false;
			self::$stack[] = ob_get_clean();
			$result = '';
			foreach(self::$stack as $k => $piece){
				switch($k{0}){
					case 'p':
						$result .= @self::$properties[substr($k, 2)];
						break;
					case 's':
						$result .= @self::$sections[substr($k, 2)];
						break;
					default:
						$result .= $piece;
				}
			}
			self::clear();
			if($print){
				echo $result;
				return null;
			} else {
				return $result;
			}
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
			if($depth = sizeof(self::$sectionDepth)){
				$parentName = self::$sectionDepth[$depth - 1];
				self::$sections[$parentName] .= ob_get_clean();
			}
			self::$sectionDepth[] = $name;
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
			array_pop(self::$sectionDepth);
			self::$sections[$name] .= ob_get_clean();
			if($depth = sizeof(self::$sectionDepth)){
				$last = self::$sectionDepth[$depth - 1];
				self::$sections[$last] .= self::$sections[$name];
				ob_start();
			}
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
		 * Возвращает цепочку секций. Возвращемый массив будет пуст, если используется вне секции
		 * Последний элемент массива это всегда текущая секция, а длина массива показывает,
		 * насколько глубоко вложена текущая секция
		 * @return string[]
		 */
		final public static function getSectionChain():array{
			return self::$sectionDepth;
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