<?php

	/**
	 * Исключение для ситуаций, когда загружается класс, который использует функции несуществующего/незагруженного расширения
	 */
	class ExtensionException extends Exception{

		public function __construct(string $ext, string $className){
			$this->message = "Failed to load class {$className}. Extension {$ext} does not exist or loaded";
		}
	}