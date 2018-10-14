<?php
	namespace System;
	use \Exception;
	use \Iterator;

	/**
	 * Класс для работы с папками внутри директории <code>$_SERVER['DOCUMENT_ROOT']</code>
	 * Позволяет создавать, удалять и изменять директории и их содержимое
	 * @version 1.0
	 */
	class Directory implements FileDescriptor, Iterator{
		/** @var string $path Абсолютный путь до директории */
		private $path;
		/** @var string $fullPath Полный путь до директории */
		private $fullPath;
		/** @var string $name Имя директории */
		private $name;
		/** @var array $files Файлы и папки, хранящиеся внутри текущей папки. Данные кэшируются */
		private $files = [];
		/** @var int $cursor Указатель на текущий элемент массива $name для обхода директории в foreach */
		private $cursor = 0;

		//TODO Метод нормализации путей. Замена ".." на родителя
		
		/**
		 * @param string $path Относительный (относительно директории скрипта, выполняющего инициализацию объекта) или абсолютный путь до папки
		 * @param bool $normalize Нормализовать ли путь (замена '..' на родителя)
		 * @throws Exception Если передана пустая строка
		 */
		public function __construct(string $path, bool $normalize = true){
			if(!strlen($path)) throw new Exception('Type Directory name/path');
			// Добавть в конец '/'
			if($path{strlen($path) - 1} !== '/') $path .= '/';
			$this->name = pathinfo($path)['filename'];
			$this->path = Path::getAbsolute($path);
			$this->fullPath = Path::getFull($path);
			if($normalize) $this->normalize();
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
		**/
		public function create(int $p = 0777):void{
			if($this->exists()){
				throw new Exception("Directory '{$this->path}' already exists");
			}
			mkdir($this->fullPath, $p, true);
		}

		/**
		 * Удаляет папку
		 * @return void
		 * @throws Exception Если директории не существует
		 **/
		public function remove():void{
			if($this->exists()){
				rmdir($this->fullPath);
				return;
			}
			throw new Exception("Directory '{'$this->path}' not found");
		}

		/**
		 * Переименовывает папку
		 * @param $name string Новое имя папки. Не включает в себя путь
		 * @return void
		 * @throws Exception Если папки не существует
		 **/
		public function rename(string $name):void{
			if(!$this->exists()){
				throw new Exception("Directory '{$this->path}' not found");
			}
			$parent = dirname($this->fullPath);
			$newname = $parent.'/'.$name;
			if(file_exists($newname) && is_dir($newname)){
				throw new Exception("Directory with name '{$name}' already exists");
			}
			rename($this->fullPath, $newname);
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
		 * @return Directory
		 * @throws Exception Если папка уже корень сайта
		 **/
		public function getParent():Directory{
			if($this->path === '/') throw new Exception('This Directory is already root');
			return new Directory(preg_replace('/[^\/]+\/?$/', '', $this->path));
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
			if(!$this->exists()) throw new Exception("Directory '{$this->getPath()}' does not exists");
			return filemtime($this->fullPath);
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