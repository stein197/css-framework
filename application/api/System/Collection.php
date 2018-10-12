<?php
	namespace System;

	use \InvalidArgumentException;
	use \Iterator;
	use \ArrayAccess;
	use \Countable;

	/**
	 * Класс для работы с массивом объектов определенного типа.
	 * То есть, в такой массив нельзя будет сложить объекты иного типа, кроме как обэекты типа, указанного в конструкторе
	 * Работает как с классами, так и с примитивными типами
	 */
	class Collection implements Iterator, ArrayAccess, Countable{
		/** @var string $type Тип массива */
		protected $type;
		/** @var array $data Массив с объектами указанного типа */
		protected $data;
		/** @var int $cursor Текущее смещение для снутреннего массива объектов. Используется для <code>foreach</code> */
		private $cursor = 0;

		/**
		 * Создает новую коллекцию с объектами указанного типа
		 * @param string $type Имя класса, объекты которого будут содержаться в коллекции
		 */
		public function __construct(string $type, array $data = []){
			$this->type = $type;
			if(!empty($data)){
				foreach($data as $i => $value){
					$t = gettype($value);
					switch($t){
						case 'object':
							if(!($value instanceof $type)){
								throw new InvalidArgumentException("Invalid argument type at position {$i}");
							}
							break;
						case 'NULL':
							throw new InvalidArgumentException("NULL value is not allowed at position {$i}");
							break;
						default:
							if($t !== $type){
								throw new InvalidArgumentException("Invalid argument type at position {$i}");
							}
					}
					$this->data[] = $value;
				}
			}
		}

		// Реализация интерфейса Iterator

		/**
		 * Возвращает текущий элемент коллекции
		 */
		public function current(){
			return $this->data[$this->cursor];
		}

		/**
		 * Возвращает ключ текущего элемента коллекции
		 * @return int
		 */
		public function key():int{
			return $this->cursor;
		}

		/**
		 * Сдвигает позицию внутреннего указателя
		 * @return void
		 */
		public function next():void{
			$this->cursor++;
		}

		/**
		 * Сбрасывает значение внутреннего указателя
		 * @return void
		 */
		public function rewind():void{
			$this->cursor = 0;
		}

		/**
		 * Проверяет валидность текущего элемента коллекции, т.е. не выходит ли в процессе обхода указатель за пределы массива
		 * @return bool
		 */
		public function valid():bool{
			return isset($this->data[$this->cursor]);
		}

		// Реализация интерфейса ArrayAccess
		/**
		 * Проверяет, существует ли элемент с указанным ключом
		 * @return bool
		 */
		public function offsetExists($offset):bool{
			return isset($this->data[$offset]);
		}

		/**
		 * Возвращает элемент с указанным ключом
		 */
		public function offsetGet($offset){
			return $this->data[$offset];
		}

		/**
		 * Устанавливает новое или меняет старое значение в массиве
		 * @return void
		 */
		public function offsetSet($offset, $value):void{
			$type = gettype($value);
			switch($type){
				case 'object':
					if(!($value instanceof $this->type)){
						$c = get_class($value);
						throw new InvalidArgumentException("Passed value type must be compatible with {$this->type} type. {$c} type supplied");
					}
					break;
				case 'NULL':
					throw new InvalidArgumentException("NULL value is not allowed");
					break;
				default:
					if($type !== $this->type){
						throw new InvalidArgumentException("Passed value type must be compatible with {$this->type} type. {$type} type supplied");
					}
			}
			if($offset === null){
				$this->data[] = $value;
			} else {
				$this->data[$offset] = $value;
			}
		}

		/**
		 * Удаляет существующий элемет из коллекции, при этом размер массива остается прежним
		 * @return void
		 */
		public function offsetUnset($offset):void{
			$this->data[$offset] = null;
		}

		// Реализация интерфейса Countable

		/**
		 * Возвращает размер коллекции
		 * @return int
		 */
		public function count():int{
			return sizeof($this->data);
		}
	}