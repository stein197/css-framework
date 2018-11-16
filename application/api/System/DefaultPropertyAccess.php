<?php
	namespace System;

	/**
	 * Трейт, имитирующий геттеры и сеттеры ко всем закрытым свойствам класса
	 * В отличие от трейта <code>PropertyAccess</code>, этот трейт лишь вызывает внутренние методы <code>get*</code> и <code>set*</code> для каждого свойства
	 * Соотвественно, если свойство требуется читать/перезаписывать извне, то для него следует определить два соответствующих метода
	 * Любой доступ к такому свойствую извне неявно вызывает методы геттера и сеттера
	 * Т.к. этот трейт основан на магических методах <code>__get()</code> и <code>__set()</code>,
	 * то его использование совместно с трейтом <code>PropertyAccess</code> не представляется возможным
	 */
	trait DefaultPropertyAccess{

		/**
		 * При обращении к защищённому свойству, вызывает внутренний метод <code>get()</code>
		 * @param string $name Имя свойства
		 * @return mixed
		 */
		public function __get(string $name){
			$name = 'get'.ucfirst($name);
			$this->{$name}();
		}

		/**
		 * При записи в защищённое свойство, вызывает внутренний метод <code>set()</code>
		 * @param string $name Имя свойства
		 * @param mixed $value Значение записываемого свойства
		 * @return mixed Старое значение
		 */
		public function __set(string $name, $value){
			$name = 'set'.ucfirst($name);
			$old = $this->name;
			$this->{$name}($value);
			return $old;
		}
	}