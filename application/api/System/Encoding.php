<?php
	namespace System;
	use \Exception;

	checkExtension('mbstring');

	/**
	 * Содержит методы для работы с кодировками
	 * Кодировка, используемая по умолчанию в методах - UTF-8
	 */
	abstract class Encoding{
		public static function detect(string $str):string{
			$encoding = mb_detect_encoding($str);
			if(!$encoding)
				throw new Exception("Can't detect encoding of '{$str}' string");
			return $encoding;
		}

		public static function convert(string $str, string $from = null, string $to = 'UTF-8'):string{
			$from = $from ?? mb_internal_encoding();
			return mb_convert_encoding($str, $to, $from);
		}

		public static function getCharCode(string $char):int{
			$k1 = ord(substr($char, 0, 1));
			$k2 = ord(substr($char, 1, 1));
			return $k2 * 256 + $k1;
		}

		public static function fromCharCode(int $char):string{
			return mb_convert_encoding('&#' . intval($char) . ';', 'UTF-8', 'HTML-ENTITIES');
		}
	}