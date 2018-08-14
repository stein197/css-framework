<?php
	namespace System;
	import('System.FileDescriptor');
	import('System.Path');
	use \Exception;

	/**
	 * Класс для работы с файлами.
	 * @todo запись в файл только при закрытии/завершении работы скрипта. Сохранение всех изменений во внутренний буфер перед записью
	 */
	class File implements FileDescriptor{


		/** @var string $path Абсолютный путь до файла */
		private $path;
		/** @var string $fullPath Полный путь до файла */
		private $fullPath;
		/** @var string $name Имя директории */
		private $name;
		/** @var bool $opened Открыт ли файл для чтения/записи */
		private $opened = false;
		/** @var resource $file Ссылка на файловый дескриптор */
		private $file;
		/** @var int $length Длина файла в байтах */
		private $length = 0;
		/** @var int $instances Общее количество незакрытых файлов */
		public static $instances = 0;

		/** @var int MODE_READ Флаг режима чтения файла */
		public const MODE_READ = 0b01;
		/** @var int MODE_WRITE Флаг режима записи в файл */
		public const MODE_WRITE = 0b10;

		/** @var int CURSOR_START Флаг установки указателя в начало файла */
		public const CURSOR_START = 0;
		/** @var int CURSOR_END Флаг установки указателя в конец файла */
		public const CURSOR_END = 1;

		/**
		 * @param string $path Путь до файла
		 * @throws Exception Если не передан параметр пути
		 */
		public function __construct(string $path){
			if(!strlen($path)){
				throw new Exception('Empty filename');
			}
			$this->path = Path::getAbsolute($path);
			$this->fullPath = Path::getFull($path);
			$this->name = pathinfo($path, PATHINFO_BASENAME);
		}

		public function __destruct(){
			$this->close();
		}

		/**
		 * Создает новый файл в режиме 'x+' (чтение и запись)
		 * @throws Exception Если файл с таким именем уже существует
		 * @return void
		 */
		public function create():void{
			if($this->exists()){
				throw new Exception('File already exists');
			}
			$this->file = fopen($this->fullPath, 'x+');
		}

		public function open(int $mode = self::MODE_READ & self::MODE_WRITE, $seek = self::CURSOR_END, $truncate = false){
			if(!$this->exists()){
				throw new Exception("File {$this->name} does not exist");
			}
			if($mode === self::MODE_READ){
				$this->file = fopen('r');
				
			}
		}

		public function close(){
			if(!fclose($this->file) || !$this->exists()){
				throw new Exception('Can\'t close file');
			}
			$this->opened = false;
			self::$instances--;
		}

		/**
		 * Перемещает указатель файла
		 * @param int $offset Смещение указателя
		 * @param int $whence Режим смещения
		 * @return bool
		 */
		public function seek(int $offset, int $whense = SEEK_SET):bool{
			return fseek($this->file, $offset, $whense) ? false : true;
		}

		/**
		 * Возвращает текущую позицию указателя
		 * @return int
		 */
		public function tell():int{
			return ftell($this->file);
		}

		public function resetSeek(int $position = self::CURSOR_START){
			if($position = self::CURSOR_START){
				rewind($this->file);
			} else {
				// $l = 
			}
		}

		public function copy(): void
		{
			// TODO: Implement copy() method.
		}

		/**
		 * Проверяет существование файла в системе
		 * @return bool Возвращает true если файл существует, иначе false
		 */
		public function exists():bool{
			return file_exists($this->fullPath) && is_file($this->fullPath);
		}

		/**
		 * Возвращает имя файла вместе с расширением
		 * @return string
		 */
		public function getName():string{
			return $this->name;
		}

		/**
		 * Возвращает путь до файла (вместе с файлом)
		 * @param bool $full Возвращать полный путь до файла. По умолчанию false
		 * @return string Путь до файла
		 */
		public function getPath(bool $full = false):string{
			return $full ? $this->fullPath : $this->path;
		}

		public function getSize(): int
		{
			// TODO: Implement getSize() method.
		}

		public function lastModified(): int
		{
			// TODO: Implement lastModified() method.
		}

		public function move(): void
		{
			// TODO: Implement move() method.
		}

		public function remove(): void
		{
			// TODO: Implement remove() method.
		}

		public function rename(string $name): void
		{
			// TODO: Implement rename() method.
		}
	}