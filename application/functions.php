<?php
	const LIBRARY_DIRECTORY = __DIR__;
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

	// Автозагрузка классов
	spl_autoload_register(function($ns){
		global $_CLASSES;
		$_CLASSES[] = $ns;
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $ns);
		require_once LIBRARY_DIRECTORY.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.$path.'.php';
	});