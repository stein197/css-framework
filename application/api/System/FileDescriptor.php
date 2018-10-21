<?php
namespace System;

/**
 * Общий интерфейс для классов работы с файловой системой, в частности с файлами
 */
interface FileDescriptor{
	public function create():void;
	public function copy():void;
	public function exists():bool;
	// public function getName():string;
	// public function getPath(bool $full = false):string;
	public function getSize():int;
	public function lastModified():int;
	public function lastAccess():int;
	public function move(string $dir):void;
	public function remove():void;
	public function rename(string $name):void;
	// chmod, chown, chgrp
}