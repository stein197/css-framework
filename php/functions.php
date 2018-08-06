<?php
	const ROOT_DIRECTORY = __DIR__;
	/**
	 * Импортирует класс/интерфейс. Возвращает 1, если файл был подключен и 0, если файл уже был подключен
	 * @param string $path Строка, содержащая пространство имен и имя класса/интерфейса
	 * @return int Возвращает количество попыток загрузки класса
	 * @todo Сделать поддержку импорта пространства имен целиком (т.е. import('Sustem.*'))
	**/
	function import(string $path, bool $useRelativePath = true):int{
		static $classes = [];
		if(isset($classes[$path])){
			$classes[$path]++;
		} else {
			$classes[$path] = 1;
		}
		$nc = str_replace('.', '\\', $path);
		// Подключить только если класса/интерфейса не существует
		if(!class_exists($nc) && !interface_exists($nc)){
			if($useRelativePath){
				require_once str_replace('.', DIRECTORY_SEPARATOR, $path).'.php';
			} else {
				require_once ROOT_DIRECTORY.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $path).'.php';
			}
		}
		return $classes[$path];
	}