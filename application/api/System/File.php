<?php
	namespace System;
	use \Exception;
	use \DateTime;

	/**
	 * Класс для работы с файлами, т.е. здесь определены такие операции, как чтение, запись, удаление и т.п.
	 * @todo Реализовать блокировку
	 * @todo запись в файл только при закрытии/завершении работы скрипта. Сохранение всех изменений во внутренний буфер перед записью
	 * @todo Производные - CSVFile, Image, JSONFile, XMLFile и т.д.
	 * @property-read string $path
	 * @property-read string $fullPath
	 * @property-read string $name
	 */
	class File implements FileDescriptor, FileIO{

		use PropertyAccess;
		use ObjectDump;

		/** @var int MODE_READ Флаг режима чтения файла */
		public const MODE_READ = 0b01;
		/** @var int MODE_WRITE Флаг режима записи в файл */
		public const MODE_WRITE = 0b10;

		/** @var int CURSOR_START Флаг установки указателя в начало файла */
		public const CURSOR_START = 0;
		/** @var int CURSOR_END Флаг установки указателя в конец файла */
		public const CURSOR_END = 1;

		/** @var string $path Абсолютный путь до файла */
		protected $path;
		/** @var string $fullPath Полный путь до файла */
		protected $fullPath;
		/** @var string $name Имя файла */
		protected $name;
		/** @var bool $opened Открыт ли файл для чтения/записи */
		protected $opened = false;
		/** @var resource $file Ссылка на файловый дескриптор */
		protected $file;
		/** @var int $length Длина файла в байтах */
		protected $length = 0;
		/** @var int $mode Режим доступа к файлу */
		protected $mode;
		/** @var bool $locked Флаг, указывающий на то, стоит ли блокировка на файле */
		protected $locked = false;

		/**
		 * Возвращает эксземпляр <code>File</code>. По возможности открывает файл сразу для чтения/записи
		 * Если файл не существует и будет попытка открыть его для взаимодействия сразу после инстанциирования,
		 * то будет выброшено исключение
		 * @param string $path Путь до файла
		 * @param bool $autoopen Если <code>true</code>, то файл сразу откроется для взаимодействия
		 * @param string $dir Директория того скрипта (<code>__DIR__</code>), в котором инстанциируется объект <code>File</code>. Имеет эффект только в том случае, елс если предоставлен относительный путь до файла
		 * @throws \Exception Если не передан параметр пути
		 */
		public function __construct(string $path, bool $autoopen = false, string $dir = null){
			if(!$path)
				throw new Exception('Empty filename');
			$this->path = Path::getAbsolute($path, '/', $dir);
			$this->fullPath = Path::getFull($path, '/', $dir);
			$this->name = pathinfo($path, PATHINFO_BASENAME);
			if($autoopen)
				$this->open();
		}

		public function __destruct(){
			$this->close();
		}

		/**
		 * Создает новый файл, но не открывает его
		 * @return void
		 * @throws \Exception Если файл уже создан
		 */
		public function create():void{
			if($this->exists())
				throw new Exception("Can't create file. File '{$this->path}' already exists");
			touch($this->fullPath);
		}

		/**
		 * Открывает файл для чтения/записи
		 * @param int $mode Режим доступа. Одна из констант <code>self::MODE_READ</code> или <code>self::MODE_WRITe</code>, или битовая маска этих констант
		 * @param int $seek Куда ставить курсор при открытии. <code>self::CURSOR_END</code> в конец файла или <code>self::CURSOR_START</code> в начало
		 * @param bool $truncate обрезать ли файл после открытия
		 */
		public function open(int $mode = self::MODE_READ | self::MODE_WRITE, int $seek = self::CURSOR_END):void{
			if(!$this->exists())
				throw new Exception("Can't open not existing file '{$this->path}'");
			$this->mode = $mode;

			if($mode === self::MODE_READ | self::MODE_WRITE)
				$this->file = fopen($this->fullPath, 'r+');
			elseif($mode === self::MODE_WRITE)
				$this->file = fopen($this->fullPath, 'c');
			else
				$this->file = fopen($this->fullPath, 'r');

			if($seek === self::CURSOR_END)
				fseek($this->file, 0, \SEEK_END);
			else
				fseek($this->file, 0, \SEEK_SET);
		}

		/**
		 * Закрывает файл
		 * @return void
		 * @throws \Exception Если не удаётся закрыть файл
		 */
		public function close():void{
			if(!fclose($this->file))
				throw new Exception("Can't close file '{$this->path}'");
		}

		/**
		 * Обрезает файл до длины <code>$size</code>. По умолчанию полностью обрезает файл
		 * Если <code>$size</code> больше нуля, то файл обрезается/дополняется до указанной длины
		 * @param int $size Количество байт, до которого нужно обрезать файл
		 * @return void
		 * @throws \Exception Если не получается обрезать файл
		 */
		public function truncate(int $size = 0):void{
			if(!ftruncate($this->file, $size))
				throw new Exception("Can't truncate file '{$this->path}'");
		}

		// -------------------------
		public function lock(int $op = \LOCK_EX, int $wouldblock = null):void{
			if($wouldblock === null)
				$lock = flock($this->file, $op);
			else
				$lock = flock($this->file, $op, $wouldblock);
			if(!$lock)
				throw new Exception("Can't lock file '{$this->path}'");
			$this->locked = true;
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

		public function copy(Directory $dir, string $name = null):File{
			$res = copy($this->fullPath, $dir->fullPath.'/'.($name ?? $this->name));
			if(!$res)
				throw new Exception("Can't copy '{$this->path}' file to '{$dir->fullPath}' directory");
			return new self($dir->path.'/'.($name ?? $this->name));
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

		public function getSize():int{
			return filesize($this->fullPath);
		}

		/**
		 * Возвращает время последнего изменения файла
		 * @return \DateTime
		 */
		public function lastModified():DateTime{
			if(!$this->exists())
				throw new Exception("File does not exist");
			return new DateTime(date('Y-m-d H:i:s.u', filemtime($this->fullPath)));
		}

		/**
		 * Возвращает время последнего доступа файла
		 * @return \DateTime
		 */
		public function lastAccess():DateTime{
			if(!$this->exists())
				throw new Exception("File does not exist");
			return new DateTime(date('Y-m-d H:i:s.u', fileatime($this->fullPath)));
		}

		public function move(Directory $dir):void{
			$res = rename($this->fullPath, $dir->fullPath.'/'.$this->name);
			if(!$res)
				throw new Exception("Can't move '{$this->path}' file to '{$dir->path}' directory");
			$this->path = $dir->path.'/'.$this->name;
			$this->fullPath = $dir->fullPath.'/'.$this->name;
		}

		public function remove(): void
		{
			// TODO: Implement remove() method.
		}

		public function rename(string $name): void
		{
			// TODO: Implement rename() method.
		}

		/**
		 * Очищает кэш состояния файлов
		 * @param bool $realpath Очищать кеш realpath или нет
		 * @param File $f Если предоставлен файл, то кэш очищается только для него
		 * @return void
		 */
		public static function clearStatCache(bool $realpath = false, File $f = null):void{
			clearStatCache($realpath, $f ? $f->fullPath : $f);
		}

		// return File[]
		public static function glob(string $pattern):array{

		}

		public function unlock():void{}
		
		public function read(int $length):?string{}
		public function readChar():?string{}
		public function readLine():?string{}
		public function getContents():string{}

		public function write(string $data):int{}
		public function writeLine(string $line):int{}
		public function putContents(string $data){}

		public function isReadable():bool{}
		public function isWritable():bool{}
		public function isExecutable():bool{}

		public function hasEOF():bool{}

		public function getInfo():array{}
		public function setPointer(int $offset, int $mode = \SEEK_SET):void{}
		public function getPointer():int{}
		
		public function getOwner():array{}
		public function chmod(int $mode):void{}
		public function chown(int $mode, int $uID):void{}
		public function chgrp(int $mode, int $gID):void{}
	}