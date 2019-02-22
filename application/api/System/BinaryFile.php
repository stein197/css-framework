<?php
	namespace System;

	use \Exception;

	class BinaryFile extends File{
		public function read(?int $length = null):?array{}
		public function readByte():?int{}
		public function write(array $bytes):void{}
		public function writeByte():void{}
		public function insertByte(int $byte):void{}
		public function insertBytes(array $bytes):void{}
	}