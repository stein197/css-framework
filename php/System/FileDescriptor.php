<?php
namespace System;
interface FileDescriptor{
    public function create():void;
    public function copy():void;
    public function exists():bool;
    public function getName():string;
    public function getPath(bool $full = false):string;
    public function getSize(?string $path = null):int;
    public function lastModified():int;
    public function move():void;
    public function remove():void;
    public function rename(string $name):void;
}