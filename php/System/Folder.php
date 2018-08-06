<?php
	namespace System;
	use \Exception;
	import('System.FileDescriptor');
	import('System.Path');
	/**
	 * Класс для работы с папками
	 * Class Folder
	 * @package System
	 * @version 1.0
	 */
	class Folder implements FileDescriptor{
		/** @var string */
		private $path;
		/** @var string */
		private $fullPath;
		/** @var string */
		private $name;

		//TODO Метод нормализации путей. Замена ".." на родителя
		
		/**
		 * @param string $path Относительный (относительно директории скрипта, выполняющего инициализацию объекта) или абсолютный путь до папки
		 * @param bool $normalize Нормализовать ли путь (замена '..' на родителя)
		 * @throws Exception Если передана пустая строка
		 */
		public function __construct(string $path, bool $normalize = true){
			if(!strlen($path)) throw new Exception('Type folder name/path');
			// Добавть в конец '/'
			if($path{strlen($path) - 1} !== '/') $path .= '/';
			$this->name = pathinfo($path)['filename'];
			// Относительный путь
			if($path[0] !== '/'){
				$this->path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])).'/'.$path;
			// Абсолютный путь
			} else {
				$this->path = $path;
			}
			$this->fullPath = "{$_SERVER['DOCUMENT_ROOT']}{$this->path}";
			if($normalize) $this->normalize();
		}

		public function __clone(){
			return new Folder($this->path);
		}

		public function __toString(){
			return $this->path;
		}


		/**
		 * Проверяет, существует ли указанная папка
		 * @return bool
		 */
		public function exists():bool{
			return file_exists($this->fullPath) && is_dir($this->fullPath);
		}

		/**
		 * Создает папку
		 * @param $p int Режим доступа
		 * @return void
		 * @throws Exception Если директория уже существует
		**/
		public function create(int $p = 0777):void{
			if($this->exists()) throw new Exception("Folder '{$this->path}' already exists");
			else mkdir($this->fullPath, $p, true);
		}

		/**
		 * Удаляет папку
		 * @return void
		 * @throws Exception Если директории не существует
		 **/
		public function remove():void{
			if($this->exists()) rmdir($this->fullPath);
			else throw new Exception("Folder '{'$this->path}' not found");
		}

		/**
		 * Переименовывает папку
		 * @param $name string Новое имя папки. Не включает в себя путь
		 * @return void
		 * @throws Exception Если папки не существует
		 **/
		public function rename(string $name):void{
			if(!$this->exists()) throw new Exception("Folder '{$this->path}' not found");
			$info = pathinfo($this->path);
			// Если папка лежит в корне
			if($info['dirname'] === '/' || $info['dirname'] === '\\'){
				rename($this->fullPath, "{$_SERVER['DOCUMENT_ROOT']}/{$name}/");
				$this->path = "/{$name}/";
				$this->fullPath = "{$_SERVER['DOCUMENT_ROOT']}/{$name}/";
			} else {
				rename($this->fullPath, "{$_SERVER['DOCUMENT_ROOT']}{$info['dirname']}/{$name}/");
				$this->path = "{$info['dirname']}/{$name}/";
				$this->fullPath = "{$_SERVER['DOCUMENT_ROOT']}{$info['dirname']}/{$name}/";
			}
			$this->name = $name;
		}

		/**
		 * Возвращает путь до папки (относительно корня сайта либо абсолютный)
		 * @param bool $full Выводить абсолютный путь
		 * @return string
		 */
		public function getPath(bool $full = false):string{
			return $full ? $this->fullPath : $this->path;
		}

		/**
		 * Возвращает родительскую папку
		 * @return Folder
		 * @throws Exception Если папка уже корень сайта
		 **/
		public function getParent():Folder{
			if($this->path === '/') throw new Exception('This folder is already root');
			return new Folder(preg_replace('/[^\/]+\/?$/', '', $this->path));
		}

		/**
		 * Возвращает имя папки
		 * @return string
		 */
		public function getName():string{
			return $this->name;
		}


		/**
		 * Возвращает размер папки в байтах
		 * @param string $path - Параметр, используемый для передачи пути в качестве параметра
		 * @return int Размер папки в байтах
		 */
		public function getSize(?string $path = null):int{
			$size = 0;
			foreach(glob(($path ?? $this->fullPath).'*', GLOB_NOSORT) as $each){
				$size += is_file($each) ? filesize($each) : $this->getSize($each.'/');
			}
			return $size;
		}


		/**
		 * Дата последнего времени доступа к папке
		 * @return int
		 * @throws Exception Если папки не существует
		 */
		public function lastModified():int{
			if(!$this->exists()) throw new Exception("Folder '{$this->getPath()}' does not exists");
			return fileatime($this->fullPath);
		}

		public function listFiles():array{
			return scandir($this->fullPath);
		}

		public function copy():void{
			// TODO: Implement copy() method.
			
		}

		public function move():void{
			// TODO: Implement move() method.
		}
		// TODO: Проверка на выход за пределы DOCUMENT_ROOT
		private function normalize():void{
			while(preg_match('/\/[^\/]+\/\.\./', $this->path)){
				$this->path = preg_replace('/\/[^\/]+\/\.\./', '', $this->path, 1);
				$this->fullPath = preg_replace('/\/[^\/]+\/\.\./', '', $this->fullPath, 1);
			}
		}
	}