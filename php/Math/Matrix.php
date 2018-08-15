<?php
	namespace Math;
	use \Exception;
	use \Generator;
	/**
	 * 
	 */
	class Matrix{
		/** @var int $rows Количество строк */
		protected $rows = 0;
		/** @var int $cols Количество столбцов */
		protected $cols = 0;
		/** @var int[] $matrix Матрица */
		protected $matrix = [];
		/** @var int $rang Ранг матрицы */
		protected $rang = 0;
		
		/**
		 * Создает матрицу и заполняет ее указанными элементами
		 * @param array $matrix Массив массивов, где внутренние массивы - строки
		 * @param bool $validate Пройти проверку на соответствие
		 */
		public function __construct(array $matrix, bool $validate = true){
			if($validate){
				$this->validateDimensions($matrix);
			}
			$this->matrix = $matrix;
		}

		public function __clone(){
			return new self($this->matrix, false);
		}

		public function __toString(){
			$char_length = strlen($this->getMax());
			$result = '';

			foreach($this->matrix as $row){
				$result .= '| ';

				foreach($row as $element){
					$elt = $element;
					$diff = $char_length - strlen($elt);
					if($diff){
						$elt .= str_repeat(' ', $diff);
					}
					$result .= $elt.' ';
				}
				$result .= "|\n";
			}
			return $result;
		}

		/**
		 * Сравнивает две матрицы. Возвращает true, если обе имеют одинаковую размерность
		 * @param Matrix $mx Матрица для сравнения
		 * @return bool
		 */
		public function hasIdenticalDimensions(Matrix $mx):bool{
			return
				sizeof($this->matrix) === sizeof($mx->matrix)
					&&
				sizeof($this->matrix[0]) === sizeof($mx->matrix[0]);
		}

		/**
		 * Возвращает массив элементов матрицы
		 * @return array
		 */
		public function getMatrix():array{
			return $this->matrix;
		}

		/**
		 * Выполняет сложение двух матриц. Меняет текущую матрицу
		 * @param Matrix $mx Слагаемая матрица
		 * @return Matrix
		 * @throws Exception Если матрицы разных размеров
		 */
		public function addition(Matrix $mx):Matrix{
			if(!$this->hasIdenticalDimensions($mx)){
				throw new Exception('Matrixes is not compatible');
			}
			foreach($this->every() as $pos => &$v){
				$v += $mx->getElement($pos['ROW'], $pos['COL']);
			}
			return $this;
		}

		public function multiplication(Matrix $mx):Matrix{}

		/**
		 * Транспонирует текущую матрицу. Метод меняет текущий объект
		 * @return Matrix Транспонированную матрицу
		 */
		public function transpose():Matrix{
			$transposed = [];
			foreach($this->every() as $pos => $element){
				if(!isset($transposed[$pos['COL']])){
					$transposed[$pos['COL']] = [];
				}
				$transposed[$pos['COL'] - 1][] = $element;
			}
			$this->matrix = $transposed;
			$this->updateDimensionCount();
			return $this;
		}

		/**
		 * Возвращает элемент, лежащий по указанному адресу. Использует метод checkOffset, который может выбросить исключение
		 * @param int $row Номер строки
		 * @param int $col Номер столбца
		 * @return int|double
		 * @uses self::checkOffset()
		 */
		public function getElement(int $row, int $col){
			$this->checkOffset($row, $col);
			return $this->matrix[$row - 1][$col - 1];
		}
		
		/**
		 * Возвращает строку матрицы
		 * @param int $row Номер матрицы
		 * @return array
		 * @uses self::checkOffset()
		 */
		public function getRow(int $row):array{
			$this->checkOffset($row);
			return $this->matrix[$row - 1];
		}

		/**
		 * Возвращает столбец матрицы
		 * @param int $col Номер столбца
		 * @return array
		 * @uses self::checkOffset()
		 */
		public function getCol(int $col):array{
			$this->checkOffset($col);
			$result = [];
			for($i = 0; $i < $this->rows; $i++){
				array_push($result, $this->matrix[$i][$col - 1]);
			}
			return $result;
		}

		/**
		 * Устанавливает значение указанного элемента
		 * @param int $row Номер строки
		 * @param int $col Номер столбца
		 * @param int|double $value Новое значение
		 * @return Matrix
		 * @uses self::checkOffset()
		 * @uses self::checkValueType()
		 */
		public function setElement(int $row, int $col, $value):Matrix{
			$this->checkOffset($row, $col);
			$this->checkValueType($value);
			$this->matrix[$row - 1][$col - 1] = $value;
			return $this;
		}

		/**
		 * Меняет местами строки матрицы
		 * @param int $from Меняемая строка
		 * @param int $to Меняемая строка
		 * @return Matrix
		 * @uses self::getRow()
		 */
		public function swapRows(int $from, int $to):Matrix{
			$row = $this->getRow($from);
			$this->matrix[$from - 1] = $this->getRow($to);
			$this->matrix[$to - 1] = $row;
			return $this;
		}

		/**
		 * Меняет местами столбцы матрицы
		 * @param int $from Меняемый столбец
		 * @param int $to Меняемый столбец
		 * @return Matrix
		 * @uses self::getCol()
		 */
		public function swapCols(int $from, int $to):Matrix{
			$col1 = $this->getCol($from);
			$col2 = $this->getCol($to);
			foreach($this->matrix as $index => &$row){
				$row[$from - 1] = $col2[$index];
				$row[$to - 1] = $col1[$index];
			}
			return $this;
		}

		/**
		 * Функция для обхода элементов матрицы в цикле foreach. В качестве ключа возвращается массив с номерами строки и столбца. Нкмерация начинается с 1
		 * @return Generator Объект генератора
		 */
		public function &every():Generator{
			foreach($this->matrix as $rownum => &$row){
				foreach($row as $colnum => &$col){
					yield ['ROW' => $rownum + 1, 'COL' => $colnum + 1] => $col;
				}
			}
		}

		/**
		 * Возвращает наибольшее значение в матрице
		 * @return int|double
		 */
		public function getMax(){
			$max = -INF;
			foreach($this->every() as $num){
				$max = max($num, $max);
			}
			return $max;
		}

		/**
		 * Проверяет, не выходит ли индекс элемента за пределы размера матрицы
		 * @param int $row Номер строки
		 * @param int $col Номер столбца
		 * @throws Exception Если введен некорректный адрес элемента
		 */
		protected function checkOffset(int $row, int $col = 1):void{
			if($row < 0 || $this->rows < $row || $col < 0 || $this->cols < $col){
				throw new Exception("Element at position {$row}:{$col} does not exist");
			}
		}

		/**
		 * Проверяет тип вводимых значений
		 * @param int|double $value Проверяемое значение
		 * @throws Exception Если тип не число
		 */
		protected function checkValueType($value){
			$type = gettype($value);
			if($type !== 'integer' && $type !== 'double'){
				throw new Exception('Value type must be a number');
			}
		}

		/**
		 * Проверяет элементы и строки матрицы на соответствие
		 * @return void
		 * @throws Exception Если есть строки с разными длинами
		 * @uses self::checkValueType()
		 */
		protected function validateDimensions(array $matrix):void{
			$this->rows = sizeof($matrix);
			$this->cols = sizeof($matrix[0]);
			
			foreach($matrix as $index => $row){
				$curRowLength = sizeof($row);
				if($curRowLength !== $this->cols){
					throw new Exception(sformat('Incompatible row length at position %1', $index + 1));
				}

				foreach($row as $idx => $element){
					$this->checkValueType($element);
				}
			}
		}

		/**
		 * Обновляет параметры размеров матрицы
		 * @return void
		 */
		protected function updateDimensionCount():void{
			$this->rows = sizeof($this->matrix);
			$this->cols = sizeof($this->matrix[0]);
		}
	}