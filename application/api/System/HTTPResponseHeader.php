<?php
	namespace System;

	class HTTPResponseHeader{
		private $headers = [];

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
			$this->headers = [];
		}

		public function send():void{
			foreach($this->headers as $name => $value){
				header("{$name}: {$value}");
			}
		}
	}