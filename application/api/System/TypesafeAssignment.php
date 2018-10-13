<?php
	namespace System;

	use \ReflectionClass;

	trait TypesafeAssignment{
		public function __set(string $name, $value){
			if(property_exists(self::class, $name)){
				$prop = (new ReflectionClass(self::class))->getProperty($name);
				$doc = new DocComment($prop->getDocComment());
				$types = explode('|', $doc->getAnnotation('var')['TYPE']);
				// foreach($types as $type){
				// 	$realType = gettype($value);
				// }
			} else {
				$this->{$name} = $value;
			}
		}
	}