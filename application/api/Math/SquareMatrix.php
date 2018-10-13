<?php
	namespace Math;
	use \Exception;

	class SquareMatrix extends Matrix{
		
		/** @var int $dt Детерминант */
		protected $dt = null;

		public function __clone(){
			return new self($this->matrix, false);
		}

		/**
		 * Возвращает детерминант матрицы. Сохраняет значение в свойстве <code>$dt</code>
		 * @param bool $reset Сбросить значение свойства <code>$dt</code>. Если определитель еще не вычислен, то передача параметра не влияет на ход операции
		 * @return int Определитель матрицы
		 */
		public function getDt($reset = false):int{
			if($this->dt !== null && !$reset){
				return $this->dt;
			}
			switch($this->cols){
				case 1:
					return $this->dt = $this->getElement(1, 1);
				case 2:
					return $this->dt = $this->getQuadraticDt();
				case 3:
					return $this->dt = $this->getCubicDt();
				default:
					return $this->dt = $this->getCommonDt();
			}
		}
		
		/**
		 * Находит обратную матрицу. Возвращает новую матрицу
		 * @return Matrix Обратную матрицу
		 * @todo Операция клонирования та же, что и создание нового объекта, однако поведение разное
		 */
		public function getReverseMatrix():Matrix{
			$minor = [];
			foreach($this->every() as $pos => $elt){
				// $mx = clone $this;
				$mx = new self($this->matrix, false);
				$mx->removeRow($pos['ROW'])->removeCol($pos['COL']);
				if(($pos['ROW'] + $pos['COL']) % 2){
					$minor[$pos['ROW'] - 1][] = -$mx->getDt();
				} else {
					$minor[$pos['ROW'] - 1][] = $mx->getDt();
				}
			}
			$mx = new self($minor, false);
			return $mx->transpose()->multiplyByNumber(1 / $this->getDt());
		}

			
		/**
		 * Проверяет, единичная ли матрица
		 * @return bool
		 */
		public function isUnit():bool{
			foreach($this->every() as $pos => $elt){
				if($pos['ROW'] === $pos['COL'] && $elt !== 1 || $pos['ROW'] !== $pos['COL'] && $elt !== 0){
					return false;
				}
			}
			return true;
		}

		/**
		 * Возводит матрицу в степень. Возвращает новую матрицу
		 * @param int $power Степень, в которую нужно возвести матрицу
		 * @return Matrix Возвращает матрицу в степени
		 */
		public function pow(int $power):Matrix{
			$result = new self($this->matrix, false);
			for($i = 1; $i < $power; $i++){
				$result = $this->multiply($result);
			}
			return $result;
		}
		
		/**
		 * Метод нахождения детерминанта для матрицы 2х2
		 * @return int
		 */
		protected function getQuadraticDt():int{
			return $this->getElement(1, 1) * $this->getElement(2, 2) - $this->getElement(1, 2) * $this->getElement(2, 1);
		}

		/**
		 * Метод нахождения детерминанта для матрицы 3х3
		 * @return int
		 */
		protected function getCubicDt():int{
			$primaryD = $this->getElement(1, 1) * $this->getElement(2, 2) * $this->getElement(3, 3);
			$primaryT1 = $this->getElement(1, 3) * $this->getElement(3, 2) * $this->getElement(2, 1);
			$primaryT2 = $this->getElement(3, 1) * $this->getElement(1, 2) * $this->getElement(2, 3);

			$secondaryD = $this->getElement(1, 3) * $this->getElement(2, 2) * $this->getElement(3, 1);
			$secondaryT1 = $this->getElement(1, 1) * $this->getElement(2, 3) * $this->getElement(3, 2);
			$secondaryT2 = $this->getElement(3, 3) * $this->getElement(2, 1) * $this->getElement(1, 2);

			return ($primaryD + $primaryT1 + $primaryT2) - ($secondaryD + $secondaryT1 + $secondaryT2);
		}

		/**
		 * Метод нахождения детерминанта для матрицы, больше чем 3x3. Рекурсивно вызывает метод <code>getDt()</code>;
		 * @return int
		 */
		protected function getCommonDt():int{
			$sum = 0;
			for($i = 1; $i <= $this->cols; $i++){
				$mx = clone $this;
				$mx->removeRow(1)->removeCol($i);
				$dt = $this->getElement(1, $i) * $mx->getDt();
				if($i % 2){
					$sum += $dt;
				} else {
					$sum -= $dt;
				}
			}
			return $sum;
		}

		/**
		 * Проверяет элементы и строки матрицы на соответствие
		 * @return void
		 * @throws Exception Если есть строки с разными длинами, или если переданная матрица - не квадратная
		 * @uses self::checkValueType()
		 */
		protected function validateDimensions(array $matrix):void{
			if(sizeof($matrix) !== sizeof($matrix[0])){
				throw new Exception('Matrix is not square');
			}
			parent::validateDimensions($matrix);
		}
	}