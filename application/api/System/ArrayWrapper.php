<?php
	namespace System;

	use \ArrayAccess;
	use \Iterator;
	use \Countable;

	/**
	 * Обертка над типом <code>array</code>
	 * Возможно использование методов на массивоподобном объекте вместо функций <code>array_pop()</code> и подобных
	 * @todo Рекурсивное добавление ArrayWrapper если массив многомерный
	 */
	class ArrayWrapper implements ArrayAccess, Iterator, Countable{

		/** @var array $data Внутренний массив, содержащий данные */
		protected $data = [];
		protected $offset;

		protected $recursive = false;
		protected $firstKey;
		protected $lastKey;
		protected $firstValue;
		protected $lastValue;

		/**
		 * Создает объект с внутренним массивом
		 * @param array $data Необязательный массив с данными, над котором будет установлена обёртка
		 * @param bool $recursive Делать ли объект рекурсивным. Если да, то внутренние массивы многомерного ассива будут превращены в объекты <code>ArrayWrapper</code>
		 */
		public function __construct(?array $data = null, bool $recursive = false){
			$data = $data ?: [];
			$this->recursive = $recursive;
			if($recursive)
				foreach($data as $k => $v){
					if(is_array($v))
						$this->data[$k] = new self($v, true);
					else
						$this->data[$k] = $v;
				}
			else
				$this->data = $data;
			$this->offset = key($this->data);
		}

		public function __toString():string{
			return json_encode($this->data, JSON_UNESCAPED_UNICODE);
		}

		/**
		 * Определяет, существует ли заданный ключ
		 * @param mixed $offset Ключ для проверки
		 * @return bool
		 */
		public function offsetExists($offset):bool{
			return isset($this->data[$offset]);
		}

		/**
		 * Возвращает значение по заданному ключу
		 * @param mixed $offset Ключ, значение которого будет возвращено
		 * @return mixed
		 */
		public function offsetGet($offset){
			return $this->data[$offset];
		}

		/**
		 * Присваивает значение по заданному ключу
		 * @param mixed $offset По какому смещению присваивать значение
		 * @param mixed $value Присваемое значение
		 * @return void
		 */
		public function offsetSet($offset, $value):void{
			if($offset === null)
				$this->data[] = $value;
			elseif(is_array($value) && $this->recursive)
				$this->data[$offset] = new self($value, true);
			else
				$this->data[$offset] = $value;
		}

		/**
		 * Удаляет значение по указанному ключу
		 * @param mixed $offset Ключ
		 * @return void
		 */
		public function offsetUnset($offset):void{
			unset($this->data[$offset]);
		}

		/**
		 * Возвращает текущий элемент
		 * @return mixed
		 */
		public function current(){
			$this->offset = key($this->data);
			return current($this->data);
		}

		/**
		 * Возвращает ключ текущего элемента
		 * @return mixed
		 */
		public function key(){
			return $this->offset = key($this->data);
		}
		
		/**
		 * Смещает внутренний указатель массива на следующий элемент
		 * @return void
		 */
		public function next():void{
			next($this->data);
			$this->offset = key($this->data);
		}

		/**
		 * Сбрасывает внутренний указаткль массива на первый элемент
		 * @return void
		 */
		public function rewind():void{
			reset($this->data);
			$this->offset = key($this->data);
		}

		/**
		 * Проверяет корректность текущей позиции
		 * @return bool
		 */
		public function valid():bool{
			return isset($this->data[$this->offset]);
		}

		/**
		 * Возвращает размер массива
		 * @return int
		 */
		public function count():int{
			return sizeof($this->data);
		}

		public function push(...$value):int{
			if($this->recursive){
				foreach($value as $v){
					$this[] = is_array($v) ? new self($v, true) : $v;
				}
				return sizeof($this);
			} else {
				return array_push($this->data, $value);
			}
		}

		public function pop(){
			return array_pop($this->data);
		}

		public function getFirstKey(){

		}
	}