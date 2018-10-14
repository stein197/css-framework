<?php
	const APP_DIRECTORY = __DIR__;
	const API_DIRECTORY = __DIR__.DIRECTORY_SEPARATOR.'api';
	/** @var string[] $_CLASSES Массив всех загруженных классов */
	$_CLASSES = [];

	/**
	 * Простое форматирование текста данными. Принимает неограниченное количество аргументов
	 * @param string $format Строка для форматирования
	 * @return string Отформатированную строку
	 * @version 1.0
	 */
	function sformat(string $format, ...$data):string{
		$l = sizeof($data);
		return preg_replace_callback('/%\\d+/', function($matches) use ($format, $data, $l){
			$match = $matches[0];
			$pos = (int) substr($match, 1);
			$pos--;
			if($pos >= $l){
				return '';
			}
			$rest = '';
			while($l < $pos){
				$rest = substr((string) $pos, -1).$rest;
				$pos = floor($pos / 10);
			}
			return $data[$pos].$rest;
		}, $format);
	}

	/**
	 * Форматирует строку с указанными параметрами
	 * @param string $format Строка для форматирования
	 * @param array $data Данные. По умолчанию $GLOBALS
	 * @param string $delimiter Разделитель. По умолчанию '%'
	 * @param string $lvlDelimiter Разделитель уровней, если объект имеет сложную структуру. По умолчанию '.'
	 * @return string
	 * @version 1.0
	 */
	function format(string $format, array $data = null, string $delimiter = '%', string $lvlDelimiter = '.'){
		if(!$data){
			$data = $GLOBALS;
		}
		return preg_replace_callback("/{$delimiter}.+?{$delimiter}/", function($matches) use ($data, $lvlDelimiter){
			$match = substr($matches[0], 1, -1);
			$lvls = explode($lvlDelimiter, $match);
			$depth = sizeof($lvls);
			if(!isset($data[$lvls[0]])){
				return '';
			}
			$curLvl = $data[$lvls[0]];
			for($i = 1; $i < $depth; $i++){
				if(!isset($curLvl[$lvls[$i]])){
					return '';
				}
				$curLvl = $curLvl[$lvls[$i]];
			}
			return $curLvl;
		}, $format);
	}

	/**
	 * Возвращает всех родителей указанного класса от самого корня
	 * @param ReflectionClass $class Класс, цепь родитеелей которых нужно построить
	 * @return string[] Полные имена (вместе с пространством имен) классов-родителей
	 */
	function getClassHierarchy(ReflectionClass $class):array{
		$result = [$class->getName()];
		$parent = $class;
		while($parent = $parent->getParentClass()){
			array_unshift($result, $parent->getName());
		}
		return $result;
	}

	/**
	 * Выбрасывает исключение типа <code>ExtensionException</code>, если импортируемый класс использует функции несуществующего/незагруженного расширения.
	 * Используется внутри файлов классов
	 * @param string $name Имя расширения
	 * @return void
	 * @throws ExtensionException
	 */
	function checkExtension(string $name):void{
		if(!extension_loaded($name)){
			$apiDir = \System\Path::normalizeSeparators(API_DIRECTORY, '\\');
			$classDir = \System\Path::normalizeSeparators(debug_backtrace()[0]['file'], '\\');
			throw new ExtensionException($name, '\\'.ltrim(str_replace($apiDir, '\\', $classDir), '\\'));
		}
	}

	/**
	 * Возвращает тип аргумента. Для объектов возвращает его класс
	 * @param mixed $object Аргумент, тип которого нужно выяснить
	 * @param bool $fullName Если аргумент имеет тип объекта, то возвращать полное имя класса, включая пространства имен, иначе только имя класса
	 * @return string
	 */
	function typeof($object, bool $fullName = true):string{
		$type = gettype($object);
		if($type === 'object'){
			if($fullName){
				return '\\'.get_class($object);
			}
			return array_pop(explode('\\', get_class($object)));
		}
		return $type;
	}

	/**
	 * Проверяет, является аргумент <code>$object</code> типом <code>$type</code>
	 * @param mixed $object Переменная, тип которой нужно проверить на соответствие
	 * @param string $type Какой тип должна иметь переменная. Если это класс, то имя класса должно быть полным, т.е. включать в себя название пространства имён
	 * @return bool <code>true</code>, если переменная <code>$object</code> имеент тип <code>$type</code>
	 */
	function typeEqualsTo($object, string $type):bool{
		if($type === 'mixed'){
			return true;
		}
		$oType = gettype($object);
		switch($oType){
			case 'boolean':
				return $type === 'boolean' || $type === 'bool';
			case 'integer':
				return $type === 'integer' || $type === 'int';
			case 'double':
				return $type === 'double' || $type === 'float';
			case 'array':
				return $type === 'array' || preg_match('/^.+\[\]$/', $type) === 1;
			case 'NULL':
				return strtoupper($type) === 'NULL';
			case 'string':
			case 'resource':
				return $type === $oType;
			case 'object':
				return $object instanceof $type;
		}
	}

	// Автозагрузка классов из директории api
	spl_autoload_register(function($ns){
		global $_CLASSES;
		$_CLASSES[] = $ns;
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $ns);
		require_once API_DIRECTORY.DIRECTORY_SEPARATOR.$path.'.php';
	});

	// Бросать исключения при каждой ошибке уровня E_WARNING
	set_error_handler(function($errno, $errstr, $errfile, $errline){
		throw new WarningException($errstr, $errno, $errfile, $errline);
	}, E_WARNING);