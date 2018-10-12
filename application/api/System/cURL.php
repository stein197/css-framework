<?php
	namespace System;
	use \Exception;

	class cURL{
		private $src;
		public static $instances = 0;
		public function __construct(string $url = ''){
			$this->src = curl_init($url ?: null);
			if(!$this->src){
				throw new Exception('Can\'t create cURL instance');
			}
			self::$instances++;
		}
		public function setOpt(int $option, $value = null):bool{
			return curl_setopt($this->src, $option, $value);
		}
		public function exec(){
			return curl_exec($this->src);
		}
		public function close():void{
			curl_close($this->src);
			self::$instances--;
		}
	}