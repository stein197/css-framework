<?php
	namespace System;
	use \Exception;
	use \Iterator;

	/**
	 * Класс для работы с папками внутри директории <code>$_SERVER['DOCUMENT_ROOT']</code>
	 * Позволяет создавать, удалять и изменять директории и их содержимое
	 * @property-read string $path
	 * @property-read string $fullPath
	 * @property-write string $fullPath
	 * @version 1.0
	 */
	class Directory implements FileDescriptor, Iterator{

		use PropertyAccess;
		
		/** @var string $path Абсолютный путь до директории. Последний слэш обрезается */
		private $path;
		/** @var string $fullPath Полный путь до директории. Последний слэш обрезается */
		private $fullPath;
		/** @var string $name Имя директории */
		private $name;
		/** @var string $dir Имя директории, в которой находится скрипт, инстанциировавший объект */
		private $dir = null;
		/** @var array $files Файлы и папки, хранящиеся внутри текущей папки. Данные кэшируются */
		private $files = [];
		/** @var int $cursor Указатель на текущий элемент массива $name для обхода директории в foreach */
		private $cursor = 0;
		
		/**
		 * @param string $path Относительный (относительно директории скрипта, выполняющего инициализацию объекта) или абсолютный путь до папки
		 * @param bool $normalize Нормализовать ли путь (замена '..' на родителя)
		 * @throws Exception Если передана пустая строка
		 */
		public function __construct(string $path, string $dir = null){
			if(!strlen($path))
				throw new Exception('Directory path is not specified');
			$path = Path::getFull($path, '/', $dir);
			$this->dir = $dir;
			$this->name = pathinfo($path, PATHINFO_FILENAME);
			$this->path = Path::getAbsolute($path, '/', $dir);
			$this->fullPath = $path;
		}

		public function __clone(){
			return new Directory($this->path);
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
		 */
		public function create(int $p = 0777):void{
			if($this->exists())
				throw new Exception("Directory '{$this->path}' already exists");
			mkdir($this->fullPath, $p, true);
		}

		/**
		 * Удаляет папку
		 * @return void
		 * @throws Exception Если директории не существует
		 **/
		public function remove():void{
			if(!$this->exists())
				throw new Exception("Directory '{'$this->path}' not found");
			rmdir($this->fullPath);
		}

		/**
		 * Переименовывает папку
		 * @param $name string Новое имя папки. Не включает в себя путь
		 * @return void
		 * @throws Exception Если папки не существует, или есть папка с таким же именем
		 **/
		public function rename(string $name):void{
			if($this->name === $name)
				return;
			if(!$this->exists())
				throw new Exception("Directory '{$this->path}' not found");
			$newname = dirname($this->fullPath).'/'.$name;
			if(file_exists($newname) && is_dir($newname))
				throw new Exception("Directory with name '{$name}' already exists");
			rename($this->fullPath, $newname);
			$this->fullPath = $newname;
			$this->path = Path::getAbsolute($this->fullPath, '/', $this->dir);
			$this->name = $name;
		}

		/**
		 * Возвращает родительскую папку
		 * @return Directory
		 * @throws Exception Если папка уже корень сайта
		 **/
		public function getParent():Directory{
			return new static(preg_replace('/[^\/]+$/', '', $this->path));
		}

		/**
		 * Возвращает размер папки в байтах
		 * @param string $path - Параметр, используемый для передачи пути в качестве параметра
		 * @return int Размер папки в байтах
		 */
		public function getSize():int{
			$size = 0;
			$path = null;
			if(func_num_args()){
				$path = func_get_arg(0);
			}
			foreach(glob(($path ?? $this->fullPath).'*', GLOB_NOSORT) as $each){
				$size += is_file($each) ? filesize($each) : $this->getSize($each.'/');
			}
			return $size;
		}


		/**
		 * Дата последнего времени модификации папки
		 * @return int
		 * @throws Exception Если папки не существует
		 */
		public function lastModified():int{
			if(!$this->exists())
				throw new Exception("Directory '{$this->getPath()}' does not exists");
			return filemtime($this->fullPath);
		}

		public function lastAccess():int{
			if(!$this->exists())
				throw new Exception("Directory '{$this->getPath()}' does not exists");
			return fileatime($this->fullPath);
		}

		/**
		 * Возвращает массив файлов, лежащих в директории
		 * @param bool $resetCache Сбросить локальный кэш списка файлов
		 * @return array
		 */
		public function listFiles(bool $resetCache = false):array{
			if(!sizeof($this->files) || $resetCache){
				$this->cacheList();
			}
			return $this->files;
		}

		public function copy():void{
			// TODO: Implement copy() method.
			
		}

		public function move(string $dir):void{

			// TODO: Implement move() method.
		}

		public function rewind(){
			if(!sizeof($this->files)){
				$this->cacheList();
			}
		}
		public function current(){
			return "{$this->path}{$this->files[$this->cursor]}";
		}
		/**
		 * Кэширует результаты выборки листинга файлов директории
		 * @return void
		 */
		private function cacheList():void{
			$this->files = scandir($this->fullPath);
		}
		public function key(){
			return $this->cursor;
		}
		public function next(){
			$this->cursor++;
		}
		public function valid(){
			return isset($this->files[$this->cursor]);
		}
	}