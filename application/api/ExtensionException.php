<?php
	namespace System;

	/**
	 * Исключение для ситуаций, когда загружается класс, который использует функции несуществующего/незагруженного расширения
	 */
	class ExtensionException extends \Exception{}