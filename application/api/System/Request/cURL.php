<?php
	namespace System\Request;

	use \Exception;

	checkExtension('curl');

	class cURL{
		private $url;
		private $opts;
		private $src;

		public function __construct(string $url, array $opts = null){
			$this->url = $url;
			$this->opts = $opts;
			$this->src = curl_init($url);
		}

		public function setURL(string $name):void{
			curl_setopt($this->src, CURLOPT_URL, $name);
		}

		public function setOpt(int $option, $value = null):void{
			if(!curl_setopt($this->src, $option, $value)){
				throw new Exception($this->lastError()['MESSAGE']);
			}
		}

		public function lastError():array{
			return [
				'NO' => curl_errno($this->src),
				'MESSAGE' => curl_error($this->src)
			];
		}
		// public function setOpts
		public function exec(){
			return curl_exec($this->src);
		}
		public function close():void{
			curl_close($this->src);
		}
		public function __destruct(){
			$this->close();
		}
	}