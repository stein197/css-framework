<?php
	const API_DIRECTORY = __DIR__;
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
	 * @throws System\ExtensionException
	 */
	function checkExtension(string $name):void{
		if(!extension_loaded($name)){
			throw new ExtensionException("Extension '{$name}' does not exists or does not loaded");
		}
	}

	function typeof($object, string $type):bool{
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
				return $type === 'array' || preg_match('/^\w+\[\]$/', $type) || preg_match('/Collection(?:<.+>)?$/', $type);
			case 'NULL':
				return strtoupper($type) === 'NULL';
			case 'string':
			case 'resource':
				return $type === $oType;
			case 'object':
				if($object instanceof \System\Collection && $type === 'array'){
					return true;
				}
				return $object instanceof $type;
		}
	}

	// Автозагрузка классов из директории api
	spl_autoload_register(function($ns){
		global $_CLASSES;
		$_CLASSES[] = $ns;
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $ns);
		require_once API_DIRECTORY.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.$path.'.php';
	});

	// Бросать исключения при каждой ошибке уровня E_WARNING
	set_error_handler(function($errno, $errstr, $errfile, $errline){
		throw new WarningException($errstr, $errno, $errfile, $errline);
	}, E_WARNING);