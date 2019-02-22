<?php
	namespace System;

	use \Exception;

	abstract class ArraySort{

		public const SORT_BUBBLE = 0;
		public const SORT_COCTAIL = 1;
		public const SORT_INSERT = 2;
		public const SORT_MERGE = 3;
		public const SORT_QUICK = 4;
		public const SORT_HEAP = 5;

		public static function sort(array &$ar, callable $f = null, bool $reverse = false, int $algorithm = self::SORT_BUBBLE){
			$f = self::getFunc($f);
			switch($algorithm){
				case self::SORT_BUBBLE:
					self::bubbleSort($ar, $f, $reverse);
					break;
				case self::SORT_COCTAIL:
					self::coctailSort($ar, $f, $reverse);
					break;
				case self::SORT_INSERT:
					self::insertSort($ar, $f, $reverse);
					break;
				case self::SORT_MERGE:
					self::mergeSort($ar, $f, $reverse);
					break;
				case self::SORT_QUICK:
					self::quickSort($ar, $f, $reverse);
					break;
				case self::SORT_HEAP:
					self::heapSort($ar, $f, $reverse);
					break;
				default:
					throw new Exception('There is no sort algorithm with given name');
			}
		}
		/* 
			return [
				'operations' => n,
				'swaps' => n
			]
		 */
		private static function bubbleSort(array &$ar, callable $f, bool $reverse):void{
			$l = sizeof($ar);
			for($j = 0; $j < $l - 1; $j++)
				for($i = 0; $i < $l - 1 - $j; $i++){
					$a = $ar[$i];
					$b = $ar[$i + 1];
					$comp = $f($a, $b);
					if($comp > 0)
						self::swap($ar, $i, $i + 1);
				}
		}
		public static function selectionSort(array &$ar, bool $reverse = false){
			$l = sizeof($ar);
			for($j = 0; $j < $l; $j++){
				$a = $ar[$j];
				for($i = 1; $i < $l - $j; $i++){
					// $b = 
				}
			}
		}
		public static function coctailSort(array &$ar, bool $reverse = false){}
		public static function insertSort(array &$ar, bool $reverse = false){}
		public static function mergeSort(array &$ar, bool $reverse = false){}
		public static function quickSort(array &$ar, bool $reverse = false){}
		public static function heapSort(array &$ar, bool $reverse = false){}

		public static function swap(array &$ar, int $a, int $b):void{
			$t = $ar[$a];
			$ar[$a] = $ar[$b];
			$ar[$b] = $t;
		}
		public static function getFunc(callable $f = null):callable{
			return $f ?? function($a, $b){
				if($a > $b)
					return 1;
				elseif($a < $b)
					return -1;
				else
					return 0;
			};
		}
	}