<?php
	namespace System;
	use \Exception;
	use \DateTime;

	/**
	 * Класс для работы с файлами
	 * Здесь определены такие операции, как чтение, запись, удаление и т.п.
	 * @todo Реализовать блокировку
	 * @todo запись в файл только при закрытии/завершении работы скрипта. Сохранение всех изменений во внутренний буфер перед записью
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

		/** @var string $path Абсолютный путь до файла от корня сайта */
		protected $path = '';
		/** @var string $fullPath Полный путь до файла относительно корня ОС */
		protected $fullPath = '';
		/** @var string $name Имя файла вместе с расширением */
		protected $name = '';
		/** @var bool $opened <code>true</code>, если файл открыт (т.е. был вызван метод <code>File::open()</code>) */
		protected $opened = false;
		/** @var resource $file Ссылка на файловый дескриптор */
		protected $file;
		/** @var int $length Длина файла в байтах */
		protected $length = 0;
		/** @var int $mode Режим доступа к файлу - чтение или запись */
		protected $mode;
		/** @var bool $lock Заблокирован ли файл */
		protected $locked = false;

		/**
		 * Возвращает эксземпляр <code>File</code>. По возможности открывает файл сразу для чтения/записи
		 * Если файл не существует и будет попытка открыть его для взаимодействия сразу после инстанциирования,
		 * то будет выброшено исключение
		 * @param string $path Путь до файла
		 * @param bool $autoopen Если <code>true</code>, то файл сразу откроется для взаимодействия
		 * @param string $dir Директория того скрипта (<code>__DIR__</code>), в котором инстанциируется объект <code>File</code>. Имеет эффект только в том случае, если предоставлен относительный путь до файла
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
			if($this->opened)
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
		 * @param int $mode Режим доступа. Одна из констант <code>self::MODE_READ</code> для чтения или <code>self::MODE_WRITE</code> для записи, или битовая маска этих констант
		 * @param int $seek Куда ставить курсор при открытии. <code>self::CURSOR_END</code> в конец файла или <code>self::CURSOR_START</code> в начало
		 * @param bool $truncate обрезать ли файл после открытия
		 */
		public function open(int $mode = self::MODE_READ | self::MODE_WRITE, int $seek = self::CURSOR_END):void{
			if(!$this->exists())
				throw new Exception("Can't open not existing file '{$this->path}'");
			$this->mode = $mode;
			$this->opened = true;

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
		 * Закрывает файл. Вызывается автоматически при вызове деструктора на объекте
		 * @return void
		 * @throws \Exception Если не удаётся закрыть файл
		 */
		public function close():void{
			if(!fclose($this->file))
				throw new Exception("Can't close file '{$this->path}'");
			$this->opened = false;
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

		/**
		 * Блокирует файл для чтения/записи
		 * @return void
		 */
		public function lock():void{
			flock($this->file, \LOCK_EX);
			$this->locked = true;
		}

		/**
		 * Снимает блокировку с файла
		 * @return void
		 */
		public function unlock():void{
			flock($this->file, \LOCK_UN);
			$this->locked = false;
		}

		/**
		 * Возвращает <code>true</code>, если на файле стоит блокировка
		 * Например если есть файл 'file.txt' и на нём был вызван метод <code>self::lock()</code>,
		 * то вызов этого метода вернёт <code>true</code>
		 * Для корректной работы используется внутренний флаг <code>$locked</code>, устанавливаемые в <code>true</code> после вызова <code>self::lock()</code>,
		 * и в <code>false</code> после <code>self::unlock()</code>
		 * Тем не менее, метод может вернуть <code>true</code> и в случае, когда внутренний флаг выставлен в <code>false</code> в случае,
		 * когда создаётся ещё один объект <code>File</code>, ссылающийся на тот же файл в системе, что и другой объект, а на первом был вызван <code>self::lock()</code>
		 * @return bool <code>true</code>, если на файле стоит блокировка
		 * @todo Проверить работу метода
		 */
		public function hasLock():bool{
			return $this->locked ?: !flock($this->file, \LOCK_EX | \LOCK_NB);
		}

		/**
		 * Устанавливает указатель файла в указанное значение
		 * @param int $offset Смещение указателя в байтах
		 * @return void
		 * @throws \Exception В случае ошибки
		 */
		public function setPointer(int $offset):void{
			if(fseek($this->file, $offset, \SEEK_SET) < 0)
				throw new Exception("Can't set file pointer position to {$offset} value");
		}

		/**
		 * Смещает внутренний указатель на указанное количество байт относительно текущего положения
		 * @param int $offset Значение смещения указателя в байтах
		 * @return void
		 * @throws \Exception В случае ошибки
		 */
		public function shiftPointer(int $offset):void{
			if(fseek($this->file, $offset, \SEEK_CUR) < 0)
				throw new Exception("Can't shift file pointer position by {$offset} value");
		}

		/**
		 * Сбрасывает значение файлового указателя в начало или конец файла (в зависимости от параметра)
		 * @param int $mode В какую часть файла сбрасывать указатель - в конец (<code>CURSOR_END</code>) или в начало (<code>CURSOR_START</code>)
		 * @return void
		 * @throws \Exception В случае ошибки
		 */
		public function resetPointer(int $mode = self::CURSOR_START):void{
			if(fseek($this->file, 0, $mode === self::CURSOR_START ? \SEEK_SET : \SEEK_END) < 0)
				throw new Exception('Can\'t reset file pointer to the '.($mode === self::CURSOR_START ? 'start' : 'end').' of file');
		}

		/**
		 * Возвращает текущую позицию указателя в байтах от начала файла
		 * @return int
		 * @throws \Exception Если нельзя прочитать смещение указателя в файле
		 */
		public function getPointer():int{
			$offset = ftell($this->file);
			if($offset === false)
				throw new Exception("Can't retrieve the file pointer position");
			return $offset;
		}

		/**
		 * Копирует файл в указанную директорию. Если есть попытка скопировать файл в директорию,
		 * где уже существует файл с таким же именем - будет выброшено исключение. Например,
		 * если файл копируется в свою же директорию и при этом аргумент <code>$name</code> опускается
		 * @param \System\Directory $dir Директория, в которую нужно скопировать файл
		 * @param string|null $name Имя, присваеваемое новому файлу. Если опустить параметр, то имя сохраняется
		 * @return \System\File Возвращает ссылку на скопированный файл
		 * @throws \Exception Если есть попытка скопировать файл в директорию где есть файл с таким же именем, либо из-за внутренней ошибки функции <code>copy()</code>
		 */
		public function copy(Directory $dir, string $name = null):self{
			$newPath = $dir->fullPath.DIRECTORY_SEPARATOR.($name ?? $this->name);
			if(file_exists($newPath))
				throw new Exception("Can't copy '{$this->fullPath}' file to '{$dir->fullPath}' directory. File already exists.", 0);
			$res = copy($this->fullPath, $newPath);
			if(!$res)
				throw new Exception("Can't copy '{$this->fullPath}' file to '{$dir->fullPath}' directory due to an unknown error.", 1);
			return new static($dir->path.'/'.($name ?? $this->name));
		}

		/**
		 * Перемещает файл в указанную директорию. Возможно перемещение файла в директорию самого файла,
		 * фактически же никаких действий производиться не будет
		 * @param \System\Directory $dir Директория в которую перемещается файл
		 * @return void
		 * @throws \Exception Если в директорию, куда перемащется файл уже существует файл с таким же именем, или в случае внутренней ошибки
		 */
		public function move(Directory $dir):void{
			if($this->getDirectory()->fullPath === $dir->fullPath)
				return;
			$newPath = $dir->fullPath.'/'.$this->name;
			if(file_exists($newPath && is_file($newPath)))
				throw new Exception("Can't move '{$this->fullPath}' file to '{$dir->fullPath}' directory. File with the same name already exists in that directory.", 0);
			$res = rename($this->fullPath, $newPath);
			if(!$res)
				throw new Exception("Can't move '{$this->fullPath}' file to '{$dir->fullPath}' directory due to unknown error.", 1);
			$this->path = $dir->path.'/'.$this->name;
			$this->fullPath = $dir->fullPath.'/'.$this->name;
		}

		/**
		 * Проверяет существование файла в системе
		 * @return bool Возвращает <code>true</code> если файл существует, иначе false
		 */
		public function exists():bool{
			return file_exists($this->fullPath) && is_file($this->fullPath);
		}

		/**
		 * Возвращает размер файла в байтах
		 * @return int Размер в байтах
		 * @throws \Exception В случае внутренней ошибки
		 */
		public function getSize():int{
			$result = filesize($this->fullPath);
			if($result === false)
				throw new Exception("Can't get file size");
			return $result;
		}

		/**
		 * Возвращает время последнего изменения файла
		 * @return \DateTime
		 */
		public function lastModified():DateTime{
			$mtime = filemtime($this->fullPath);
			if(!$this->exists())
				throw new Exception("File does not exist");
			if($mtime === false)
				throw new Exception("Can't get file last modified time");
			return new DateTime(date('Y-m-d H:i:s.u', $mtime));
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

		public function remove(): void
		{
			// TODO: Implement remove() method.
		}

		/**
		 * Переименовывает файл. Имя должно включать в себя также расширение файла, т.е. даже если меняется только имя,
		 * то расширение также указывается.
		 * @param string $name Новое имя
		 * @return void
		 * @throws \Exception Если файл не существует или новое имя совпадает с именем уже существу
		 */
		public function rename(string $name):void{
			if(!$this->exists())
				throw new Exception("File '{$this->fullPath}' does not exist.", 0);
			if($this->name === $name)
				return;
			$newname = dirname($this->fullPath).'/'.$name;
			if(file_exists($newname) && is_file($newname))
				throw new Exception("Can't rename file '{$this->fullPath}' to '{$newname}'. File already exists.", 1);
			rename($this->fullPath, $newname);
			$this->fullPath = $newname;
			$this->path = Path::getAbsolute($this->fullPath, '/');
			$this->name = $name;
		}

		/**
		 * Возвращает директорию, в которой лежит файл
		 * @return \System\Directory Родительскую директорию
		 */
		public function getDirectory():Directory{
			return new Directory(preg_replace('/\/[^\/]+?$/', '', $this->fullPath));
		}

		/**
		 * Очищает кэш состояния файлов
		 * @param bool $realpath Очищать кеш realpath или нет
		 * @param \System\File $f Если предоставлен файл, то кэш очищается только для него
		 * @return void
		 */
		public static function clearStatCache(bool $realpath = false, File $f = null):void{
			clearStatCache($realpath, $f ? $f->fullPath : $f);
		}

		// return File[]
		public static function glob(string $pattern):array{

		}
		
		
		public function read(int $length):?string{}
		public function readChar():?string{}
		public function readLine():?string{}
		public function getContents():string{}

		public function write(string $data):int{}
		public function writeLine(string $line):int{}
		public function writeChar(int $char):void{}
		public function putContents(string $data){}

		public function isReadable():bool{}
		public function isWritable():bool{}
		public function isExecutable():bool{}

		public function hasEOF():bool{}

		public function getInfo():array{}
		
		public function getOwner():array{}
		public function chmod(int $mode):void{}
		public function chown(int $mode, int $uID):void{}
		public function chgrp(int $mode, int $gID):void{}
	}