<?php
	namespace System;

	use \Exception;

	class TextFile extends File{
		
		/**
		 * Читает указанное число байт из файла. При чтении указатель файла смещается
		 * @param int $length Количество считываемых байт
		 * @return string|null Считанные байты или <code>null</code>, если достигнут конец файла
		 * @throws \Exception Если нельзя прочесть файл
		 */
		public function read(int $length):?string{
			$this->checkPermission(self::MODE_READ);
			$result = fread($this->file, $length);
			if($result === false)
				throw new Exception("Can't read {$length} bytes of file '{$this->fullPath}'");
			if($this->hasEOF())
				return null;
			else
				return $result;
		}

		/**
		 * Считывает строку из файла (до <code>\n</code>)
		 * @param int|null $length Количество считываемых байт. По умолчанию считывается вся строка целиком
		 * @return string|null Считанная строка или <code>null</code>, если достигнут конец файл
		 */
		public function readLine(?int $length = null):?string{
			$this->checkPermission(self::MODE_READ);
			if($this->hasEOF())
				return null;
			if($length === null)
				$result = fgets($this->file);
			else
				$result = fgets($this->file, $length);
			return $result === false ? null : $result;
		}

		/**
		 * Возвращает всё содержимое файла, либо только указанный диапазон
		 * Несмотря на то, что для работы <code>file_get_contents()</code> не требуется открытый дескриптор файла, он всё равно должен быть открыт
		 * @param int $offset Смещение в байтах, откуда начать чтение
		 * @param int $length Количество считываемых байт. По умолчанию считывание идёт до конца файла
		 * @return string Содержимое файла
		 */
		public function getContents(int $offset = 0, ?int $length = null):string{
			$this->checkPermission(self::MODE_READ);
			if($length === null)
				return file_get_contents($this->fullPath, false, null, $offset);
			else
				return file_get_contents($this->fullPath, false, null, $offset, $length);
		}

		public function write(string $data):int{}
		public function writeLine(string $line):int{}
		public function putContents(string $data){}

	}