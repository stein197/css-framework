<?php
	namespace System;

	/**
	 * Интерфейс базового ввода-ввывода для файлов
	 */
	interface FileIO{
		public function open(int $mode, int $seek):void;
		public function close():void;
		public function truncate(int $size = -1):void;
		public function lock():void;
		public function unlock():void;
		public function hasLock():bool;
		
		public function read(int $length):?string;
		public function readChar():?string;
		public function readLine():?string;
		public function getContents():string;

		public function write(string $data):int;
		public function writeChar(int $char):void;
		public function writeLine(string $line):int;
		public function putContents(string $data);

		public function isReadable():bool;
		public function isWritable():bool;
		public function isExecutable():bool;

		public function setPointer(int $offset):void;
		public function resetPointer(int $mode):void;
		public function shiftPointer(int $offset):void;
		public function getPointer():int;

		public function hasEOF():bool;
	}