<?php
	namespace System;

	/**
	 * Содержит методы для работы с путями. Полный путь - это путь вместе с <code>$_SERVER['DOCUMENT_ROOT']</code>,
	 * абсолютный - относительно корня сайта, относительный - относительно работающего скрипта
	 * @todo выяснение пути относительно запрошенного URL
	 */
	abstract class Path{
		/**
		 * Получает полный путь до указанного файла/директории
		 * @param string $path Путь
		 * @return string Путь вместе с корнем
		 */
		public static function getFull(string $path):string{
			if(self::isFull($path)){
				return $path;
			}
			if(self::isAbsolute($path)){
				return $_SERVER['DOCUMENT_ROOT'].$path;
			}
			return dirname($_SERVER['SCRIPT_FILENAME']).'/'.$path;
		}

		/**
		 * Получает абсолютный путь до указанного файла/директории
		 * @param string $path Путь
		 * @return string Путь относительно корня сайта
		 */
		public static function getAbsolute(string $path):string{
			if(self::isFull($path)){
				return str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
			}
			if(self::isAbsolute($path)){
				return $path;
			}
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

		/**
		 * Полный ли переданный путь
		 * @return bool
		 */
		public static function isFull(string $path):bool{
			return strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0;
		}

		/**
		 * Абсолютный ли переданный путь
		 * @return bool
		 */
		public static function isAbsolute(string $path):bool{
			return $path{0} === '/';
		}

		/**
		 * Относителен ли переданный путь
		 * @return bool
		 */
		public static function isRelative(string $path):bool{
			return !self::isFull($path) && !self::isAbsolute($path);
		}

		/**
		 * Нормализует путь. Т.е. приводит все слеэши в пути (прямые и обратные) к единому формату
		 * @param string $path Путь, который нужно нормализовать
		 * @param string $ds Слэш, который будет разделять директории в возвращаемом значении
		 * @return string
		 */
		public static function normalizeSeparators(string $path, string $ds = DIRECTORY_SEPARATOR):string{
			return preg_replace('#[\\\/]#', $ds, $path);
		}
	}