<?php
	namespace System;

	use \ArrayAccess;
	use \Iterator;
	use \Countable;

	/**
	 * Обертка над типом <code>array</code>
	 * Является тем же массивом, с тем лишь отличием, что для манипуляций над массивом используются методы класса, а не функции
	 * По сути, класс объединяет все функции и операции над массивом
	 * Если массив многомерный или содержит вложенные массивы, то они будут рекурсивно обёрнуты в <code>ArrayWrapper</code>
	 * @todo Рекурсивное добавление ArrayWrapper если массив многомерный
	 * @todo Реализовать быстрый доступ к первому и последнему ключу/значению массива
	 * @property-read mixed $firstKey
	 * @property-read mixed $lastKey
	 * @property-read mixed $firstValue
	 * @property-read mixed $lastValue
	 */
	class ArrayWrapper implements ArrayAccess, Iterator, Countable{

		use PropertyAccess;

		/** @var array $data Внутренний массив, содержащий данные */
		protected $data = [];
		/** @var mixed $offset Текущий ключ массива, на который указывает указатель */
		protected $offset;
		protected $firstKey;
		protected $lastKey;
		protected $firstValue;
		protected $lastValue;
		/** @var int $innerArrays Внутренний счётчик для подсчёта количества внутренних массивов. Нужен для оптимизации методов, которые могут иметь рекурсивную природу */
		protected $innerArrays = 0;

		/**
		 * Создает объект с внутренним массивом
		 * @param array $data Необязательный массив с данными, над котором будет установлена обёртка
		 */
		public function __construct(array $data = []){
			foreach($data as $k => $v){
				if(is_array($v)){
					$this->data[$k] = new self($v);
					$this->innerArrays++;
				} else {
					$this->data[$k] = $v;
				}
			}
			$this->updateBoudaryElements(2);
		}

		/**
		 * Возвращает JSON-представление внутреннего массива
		 * @return string
		 */
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
		 * Присваивает значение по заданному ключу. Если передан массив, то увеличивает внутренний счётчик количества вложенных массивов
		 * @param mixed $offset По какому смещению присваивать значение
		 * @param mixed $value Присваемое значение
		 * @return void
		 */
		public function offsetSet($offset, $value):void{
			if(is_array($value)){
				$this->innerArrays++;
				$value = new self($value);
			}
			if($offset === null)
				$this->data[] = $value;
			else
				$this->data[$offset] = $value;
		}

		/**
		 * Удаляет значение по указанному ключу и уменьшает внутренний счётчик количества вложенных массивов, если удаляемое значение - массив
		 * @param mixed $offset Ключ
		 * @return void
		 */
		public function offsetUnset($offset):void{
			if($this->data[$offset] instanceof self)
				$this->innerArrays--;
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

		// Вместо цикла по всем элементам можно просто хранить ключи внутренних массивов в другом списке
		public function changeKeyCase(int $case = CASE_UPPER, bool $recursive = true):self{
			$this->data = array_change_key_case($this->data, $case);
			if($this->innerArrays && $recursive)
				foreach($this->data as $k => &$v)
					if($v instanceof self)
						$v = $v->changeKeyCase($case, true);
			return $this;
		}

		protected function updateBoudaryElements($currentKey = null):void{
			$this->firstKey = key($this->data);
			$this->firstValue = $this->data[$this->firstKey];
			end($this->data);
			$this->lastKey = key($this->data);
			$this->lastValue = $this->data[$this->lastKey];
			reset($this->data);
			if($currentKey !== null){
				$keyMatches = false;
				$hasNext = true;
				while(!$keyMatches && $hasNext){
					if(key($this->data) === $currentKey){
						$keyMatches = true;
					} else {
						$hasNext = next($this->data);
					}
				}
			}
		}
	}