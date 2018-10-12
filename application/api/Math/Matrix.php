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
		 * @throws Exception Если есть строки/столбцы разной длины
		 */
		public function __construct(array $matrix, bool $validate = true){
			if($validate){
				$this->validateDimensions($matrix);
			}
			$this->matrix = $matrix;
			$this->updateDimensionCount();
		}

		public function __clone(){
			return new self($this->matrix, false);
		}

		public function __toString(){
			$result = '<table style="text-align: center; border-left: 1px solid; border-right: 1px solid; table-layout: fixed"><tbody>';
			foreach($this->matrix as $row){
				$result .= '<tr>';
				foreach($row as $elt){
					$result .= "<td>{$elt}</td>";
				}
				$result .= '</tr>';
			}
			$result .= '</tbody></table>';
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
		 * Выполняет сложение двух матриц. Возвращает новую матрицу
		 * @param Matrix $mx Слагаемая матрица
		 * @return Matrix
		 * @throws Exception Если матрицы разных размеров
		 */
		public function add(Matrix $mx):Matrix{
			if(!$this->hasIdenticalDimensions($mx)){
				throw new Exception('Matrixes is not compatible');
			}
			$result = [];
			foreach($this->every() as $pos => $v){
				$result[$pos['ROW'] - 1][] = $this->getElement($pos['ROW'], $pos['COL']) + $mx->getElement($pos['ROW'], $pos['COL']);
			}
			return new self($result, false);
		}

		/**
		 * Умножает две матрицы. Возвращает новую матрицу
		 * @param Matrix $mx Вторая матрица для умножения
		 * @return Matrix
		 * @throws Exception Если количество колонок текущей матрицы не совпадает с количеством строк матрицы, переданной в качестве аргумента
		 */
		public function multiply(Matrix $mx):Matrix{
			if($this->cols !== $mx->rows){
				throw new Exception("Matrix should have {$this->cols} rows");
			}
			$result = [];
			for($i = 0; $i < $this->rows; $i++){
				$row = $this->getRow($i + 1);

				for($j = 0; $j < $mx->cols; $j++){
					$col = $mx->getCol($j + 1);
					$sum = 0;
					
					for($k = 0; $k < $this->cols; $k++){
						$sum += $row[$k] * $col[$k];
					}
					$result[$i][$j] = $sum;
				}
			}
			return new self($result, false);
		}

		public function multiplyByNumber($number):Matrix{
			$this->checkValueType($number);
			$mx = clone $this;
			foreach($mx->every() as &$elt){
				$elt *= $number;
			}
			return $mx;
		}

		/**
		 * Транспонирует текущую матрицу. Метод меняет текущий объект
		 * @return Matrix Транспонированную матрицу
		 */
		public function transpose():Matrix{
			$transposed = [];
			foreach($this->every() as $pos => $element){
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
			$this->checkOffset(1, $col);
			$result = [];
			for($i = 0; $i < $this->rows; $i++){
				array_push($result, $this->matrix[$i][$col - 1]);
			}
			return $result;
		}

		/**
		 * Удаляет указанную строку
		 * @param int $row Номер строки
		 * @return Matrix
		 * @uses self::checkOffset()
		 */
		public function removeRow(int $row):Matrix{
			$this->checkOffset($row);
			$result = [];
			foreach($this->matrix as $i => $r){
				if($i + 1 === $row){
					continue;
				}
				$result[] = $r;
			}
			$this->matrix = $result;
			$this->updateDimensionCount();
			return $this;
		}

		/**
		 * Удаляет указанный столбец
		 * @param int $col Номер колонки
		 * @return Matrix
		 * @uses self::checkOffset()
		 */
		public function removeCol(int $col):Matrix{
			$this->checkOffset(1, $col);
			$result = [];
			foreach($this->every() as $pos => $value){
				if($pos['COL'] === $col){
					continue;
				}
				$result[$pos['ROW'] - 1][] = $value;
			}
			$this->matrix = $result;
			$this->updateDimensionCount();
			return $this;
		}

		/**
		 * Добавляет строку снизу
		 * @param int[] $row Новая строка
		 * @return Matrix $this
		 * @throws Exception Если добавляемая строка имеет большее или меньшее количество столбцов, чем сама матрица
		 */
		function addRow(array $row):Matrix{
			$size = sizeof($row);
			if($size !== $this->cols){
				throw new Exception("Passed row has {$size} columns. It should be {$this->cols}");
			}
			$this->matrix[] = $row;
			$this->updateDimensionCount();
			return $this;
		}

		/**
		 * Добавляет столбец справа
		 * @param int[] $row Новый столбец
		 * @return Matrix $this
		 * @throws Exception Если добавляемый столбец имеет большее или меньшее количество строк, чем сама матрица
		 */
		function addCol(array $col):Matrix{
			$size = sizeof($col);
			if($size !== $this->rows){
				throw new Exception("Passed column has {$size} rows. It should be {$this->rows}");
			}
			foreach($this->matrix as $i => &$row){
				$row[] = $col[$i];
			}
			$this->updateDimensionCount();
			return $this;
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
		 * Проверяет, не выходит ли индекс элемента за пределы размера матрицы
		 * @param int $row Номер строки
		 * @param int $col Номер столбца
		 * @throws Exception Если введен некорректный адрес элемента
		 */
		protected function checkOffset(int $row, int $col = 1):void{
			if($row < 1 || $this->rows < $row || $col < 1 || $this->cols < $col){
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
		 * @param array $matrix Проверяеммый массив
		 * @return void
		 * @throws Exception Если есть строки с разными длинами
		 * @uses self::checkValueType()
		 */
		protected function validateDimensions(array $matrix):void{
			$cols = sizeof($matrix[0]);
			foreach($matrix as $index => $row){
				$curRowLength = sizeof($row);
				if($curRowLength !== $cols){
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