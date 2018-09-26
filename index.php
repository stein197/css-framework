<?php
	class T{
		public $p = 10;
		public function getP(){
			return $p;
		}
	}
	function callparam($classname){
		var_dump($classname);
	}
	callparam(T::class);