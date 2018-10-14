<?php
	namespace System\Request;

	use \System\Singleton;

	/**
	 * @property-read array $headers
	 */
	class HTTPHeaders extends Singleton{

		use \System\PropertyAccess;
		
		private $headers;

		protected function __construct(array $headers = null){
			$this->headers = $headers;
		}
		
		public function addHeader(string $name, string $value):void{
			$this->headers[$name] = $value;
		}
		
		public function addHeaders(array $headers):void{
			foreach($headers as $name => $value){
				$this->addHeader($name, $value);
			}
		}
		
		public function removeHeader(string $name):void{
			unset($this->headers[$name]);
		}
		
		public function clean():void{
			$this->headers = null;
		}
		
		public function send():void{
			foreach($this->headers as $name => $value){
				header("{$name}: {$value}");
			}
			$this->clean();
		}

		public static function getClientHeaders():array{
			return getallheaders();
		}
	}