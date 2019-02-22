<?php
	namespace System;
	use \Exception;
	use \Iterator;
	use \DateTime;

	/**
	 * Класс для работы с директориями локальной машины
	 * Позволяет создавать, удалять и изменять директории и их содержимое
	 * @property-read string $path
	 * @property-read string $fullPath
	 * @property-read string $name
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
		 * 
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

		public function __toString(){
			return $this->path;
		}

		/**
		 * Проверяет, существует ли указанная папка
		 * @return bool Возвращает <code>true</code>, если директория существует
		 */
		public function exists():bool{
			return file_exists($this->fullPath) && is_dir($this->fullPath);
		}

		/**
		 * Создает папку, при этом вне зависимости от уровня вложенности, создание папки всегда идёт рекурсивно
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
		 * Возвращает родительскую папку. Если это корневая папка, то возвращается текущий объект
		 * @return \System\Directory
		 * @todo Проверить, работает ли метод с корневыми папками
		 **/
		public function getParent():self{
			$newPath = dirname($this->fullPath);
			if($newPath === '.' || $newPath === $this->fullPath)
				return $this;
			return new static($newPath);
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

		public function lastModified():DateTime{}

		public function lastAccess():DateTime{}

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

		public function copy(Directory $dir, string $name = null):Directory{
			// TODO: Implement copy() method.
			
		}

		public function move(Directory $dir):void{

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
		public function getInfo():array{}
		public function getOwner():array{}
		public function chmod(int $mode):void{}
		public function chown(int $mode, int $uID):void{}
		public function chgrp(int $mode, int $gID):void{}
	}