<?php
	namespace Math;
	
	use \Exception;
	import('Math.Matrix');

	class SquareMatrix extends Matrix{
		
		/** @var int $dt Детерминант */
		protected $dt;

		public function __clone(){
			return new self($this->matrix, false);
		}
		public function getDt():int{}
		public function getReverseMatrix():Matrix{}

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
	}