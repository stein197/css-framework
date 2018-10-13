<?php

	/**
	 * Исключения в случаях, когда свойство имеет модификатор <code>private</code> или <code>protected</code> и к этому свойству обращаются извне
	 */
	class PropertyAccessException extends Exception{
		public const M_READ = 'read-only';
		public const M_WRITE = 'write-only';

		public function __construct(string $propname, string $modificator, string $classname, string $mode){
			var_dump($mode);
			if($mode === self::M_READ){
				$this->message = "Cannot write to {$mode} {$modificator} {$classname}::{$propname}";
			} else {
				$this->message = "Cannot read from {$mode} {$modificator} {$classname}::{$propname}";
			}
		}
	}