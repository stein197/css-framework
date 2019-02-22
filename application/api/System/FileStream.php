<?php
	namespace System;

	abstract class FileStream{

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

			if($mode === (self::MODE_READ | self::MODE_WRITE))
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
	}