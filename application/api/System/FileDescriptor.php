<?php
	namespace System;
	use \DateTime;

	/**
	 * Общий интерфейс для классов работы с файловой системой, в частности с файлами
	 */
	interface FileDescriptor{
		public function create():void;
		public function remove():void;
		public function exists():bool;

		public function rename(string $name):void;
		public function copy(Directory $dir, string $name = null);
		public function move(Directory $dir):void;
		
		public function getSize():int;
		public function getInfo():array;
		public function lastModified():DateTime;
		public function lastAccess():DateTime;

		public function getOwner():array;
		public function chmod(int $mode):void;
		public function chown(int $mode, int $uID):void;
		public function chgrp(int $mode, int $gID):void;
		// public function created():DateTime;
		// chmod, chown, chgrp
	}