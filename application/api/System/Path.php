<?php
	namespace System;

	/**
	 * Содержит методы для работы с путями, такими как выяснение полного, относительного пути и т.д.
	 * Полный путь - это путь вместе с <code>$_SERVER['DOCUMENT_ROOT']</code>,
	 * абсолютный - относительно корня сайта, относительный - относительно работающего скрипта
	 */
	abstract class Path{

		/**
		 * Получает полный путь до указанного файла/директории
		 * Является эквивалентом функции <code>self::normalize()</code>, т.к. он так же возвращает полный путь
		 * @param string $path Путь
		 * @param string $ds Разделитель директорий
		 * @param string $dir Если поставляется относительный путь, то в этом параметре должен быть путь до директории файла, в котором вызывается метод
		 * @return string Полный путь вместе с корнем
		 */
		public static function getFull(string $path, string $ds = DIRECTORY_SEPARATOR, string $dir = null):string{
			return self::normalize($path, $ds, $dir);
		}

		/**
		 * Получает абсолютный путь до указанного файла/директории
		 * @param string $path Путь
		 * @param string $path $ds Разделитель директорий
		 * @param string $dir Если поставляется относительный путь, то в этом параметре должен быть путь до директории файла, в котором вызывается метод
		 * @return string Путь относительно корня сайта
		 */
		public static function getAbsolute(string $path, string $ds = DIRECTORY_SEPARATOR, string $dir = null):string{
			$path = self::normalize($path, $ds, $dir);
			$root = preg_replace('#[\\\/]#', $ds, $_SERVER['DOCUMENT_ROOT']);
			return str_replace($root, '', $path);
		}

		/**
		 * Получает относительный путь до указанного файла/директории
		 * @param string $path Путь
		 * @param string $path $ds Разделитель директорий
		 * @param string $dir Если поставляется относительный путь, то в этом параметре должен быть путь до директории файла, в котором вызывается метод
		 * @return string Путь относительно исполняемого скрипта
		 */
		public static function getRelative(string $path, string $ds = DIRECTORY_SEPARATOR, string $dir = null):string{
			$path = self::normalize($path, $ds, $dir);
			$dir = preg_replace('#[\\\/]#', $ds, $dir ?: dirname($_SERVER['SCRIPT_FILENAME']));
			return ltrim(str_replace($dir, '', $path), $ds);
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
		 * Нормализует путь. Убирает переходы типа <code>/../</code> и <code>/./</code>,
		 * приводит слэши к единому значению (если в пути содержатся и обратные и прямые слэши)
		 * Используется всеми методами <code>self::get*()</code>
		 * @param string $path Путь который нужно нормализовать
		 * @param string $ds Разделитель директорий. Или '/', или '\'
		 * @param string $dir Если поставляется относительный путь, то в этом параметре должен быть путь до директории файла, в котором вызывается метод, иначе возвращаемый путь может быть непредсказуемым
		 * @return string Полный путь
		 */
		public static function normalize(string $path, string $ds = DIRECTORY_SEPARATOR, string $dir = null){
			if(self::isAbsolute($path))
				$path = $_SERVER['DOCUMENT_ROOT'].$path;
			elseif(!self::isFull($path))
				$path = $dir ? $dir.$ds.$path : dirname($_SERVER['SCRIPT_FILENAME']).$ds.$path;
			$path = preg_replace('#[\\\/]#', $ds, $path);
			$result = [];
			$parts = preg_split('/\\'.$ds.'/', $path);
			foreach($parts as $part){
				if($part === '..')
					array_pop($result);
				elseif($part === '.')
					continue;
				else
					$result[] = $part;
			}
			return join($ds, $result);
		}
	}