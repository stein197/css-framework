<?php
	namespace System;

	/**
	 * Содержит методы для работы с путями
	 * @todo выяснение пути относительно запрошенного URL
	 */
	abstract class Path{
		/**
		 * Получает полный путь до указанного файла/директории
		 * @param string $path Путь
		 * @return string Путь вместе с корнем
		 */
		public static function getFull(string $path):string{
			// Полный путь
			if(strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0){
				return $path;
			}
			// Абсолютный путь
			if($path[0] === '/'){
				return $_SERVER['DOCUMENT_ROOT'].$path;
			}
			// Относительный путь
			return dirname($_SERVER['SCRIPT_FILENAME']).'/'.$path;
		}

		/**
		 * Получает абсолютный путь до указанного файла/директории
		 * @param string $path Путь
		 * @return string Путь относительно корня сайта
		 */
		public static function getAbsolute(string $path):string{
			// Полный путь
			if(strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0){
				return str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
			}
			// Абсолютный путь
			if($path[0] === '/'){
				return $path;
			}
			// Относительный путь
			return str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])).'/'.$path;
		}

		/**
		 * Получает относительный путь до указанного файла/директории
		 * @param string $path Путь
		 * @return string Путь относительно исполняемого скрипта
		 */
		public static function getRelative(string $path):string{
			if(self::isFull($path)){
				return str_replace(dirname($_SERVER['SCRIPT_FILENAME']).'/', '', $path);
			}
			if(self::isAbsolute($path)){
				return str_replace(dirname($_SERVER['SCRIPT_FILENAME']).'/', '', self::getFull($path));
			}
			return $path;
		}

		public static function isFull(string $path):bool{
			return strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0;
		}
		public static function isAbsolute(string $path):bool{
			return $path[0] === '/';
		}
		public static function isRelative(string $path):bool{
			return !self::isFull($path) && !self::isAbsolute($path);
		}
	}