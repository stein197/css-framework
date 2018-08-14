<?php
	const ROOT_DIRECTORY = __DIR__;
	/**
	 * Импортирует класс/интерфейс. Возвращает 1, если файл был подключен и 0, если файл уже был подключен
	 * @param string $path Строка, содержащая пространство имен и имя класса/интерфейса
	 * @return int Возвращает количество попыток загрузки класса
	 * @todo Сделать поддержку импорта пространства имен целиком (т.е. import('System.*'))
	**/
	function import(string $path, bool $useRelativePath = true):int{
		static $classes = [];
		$nc = str_replace('.', '\\', $path);
		// Подключить только если класса/интерфейса/трейта не существует
		if(!class_exists($nc) && !interface_exists($nc) && !trait_exists($nc)){
			if($useRelativePath){
				require_once str_replace('.', DIRECTORY_SEPARATOR, $path).'.php';
			} else {
				require_once ROOT_DIRECTORY.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $path).'.php';
			}
		}
		return @++$classes[$path];
	}

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