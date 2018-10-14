<?php
	namespace System;

	/**
	 * Класс, реулизующий шаблон проектирования Singleton
	 * В процессе работы приложения, для работы будет доступен один и только один экземпляр реализующего класса
	 * Для использования достаточно наследовать класс, не переопределять модификатор доступа к конструктору, а также не переопределять сам метод <code>self::getInstance()</code>
	 * @version 1.0
	 */
	class Singleton{
		
		/** @var Singleton $instance Ссылка на единственный экземлпяр класса */
		protected static $instance;

		/**
		 * Закрытый извне конструктор
		 */
		protected function __construct(){
			static::$instance = $this;
		}

		/**
		 * Метод, реализующий единственную точку доступа к экземпляру класса
		 * @param mixed ...$args Переменное количество принимаемых параметров, которые должны быть переданы в дальнейшем конструктору
		 * @return Singleton Единственный экземпляр класса Singleton
		 */
		final public static function getInstance(...$args):Singleton{
			return static::$instance ?: static::$instance = new static($args);
		}
	}