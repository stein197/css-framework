<?php
	namespace System;

	use \Exception;

	class BinaryMask{
		protected $value = 0;
		protected $assigned = false;
		protected $labels = [];

		public function __construct(int $mask, ?array $data = null){
			$this->value = $mask;
			if($data !== null)
				$this->assign($data);
		}

		public function __toString():string{}

		public function assign(array $data):void{
			// $length = sizeof($data);
			// if($length !== $this->size)
			// 	throw new Exception("Can't assign ", 0);
			// if($this->assigned)
			// 	throw new Exception("Flag names reassigning does not permitted", 1);
			// $this->labels = array_flip($data);
			// $this->assigned = true;
		}

		public function get(string $name):int{}
		public function set(string $name, int $value):void{}
		public function switch(string $name):void{}
	}