<?php
	namespace System\Database;

	/**
	 * Набор методов для автоматического построения стандартных SQL-запросов.
	 * В частности, при <code>INSERT</code>-запросах. Все методы возвращают постотроенную SQL-строку запроса
	 */
	abstract class DBQuery{

		/**
		 * Возвращает SELECT-запрос
		 * @param string $table Имя таблицы
		 * @param string[] $columns Имена столбцов
		 * @param string[] $where WHERE-условие, объединяемое по условию <code>AND</code>
		 * @param string[] $orderby Массив ORDER BY-условий
		 * @param string[] $groupby Массив GROUP BY-условий
		 * @param array $join Имена перекрестных таблиц, где ключ - имя, в значение - псевдоним
		 * @param string[] $on Массив ON-условий объединяемые по условию <code>AND</code>, при условии если передан параметр <code>$join</code>
		 * @return string
		 */
		public static function getSelect(string $table, array $columns, array $where = [], array $orderby = [], array $groupby = [], array $join = [], array $on = []):string{
			$cols = join(", \n\t", $columns);
			$q = "SELECT\n\t{$cols}\nFROM\n\t{$table}";
			if(!empty($join)){
				$joinQ = '';
				foreach($join as $name => $alias){
					$joinQ .= "\n\t\tJOIN\n\t{$name} AS {$alias}";
				}
				$q .= $joinQ;
				if(!empty($on)){
					$q .= "\nON\n\t";
					$q .= join("\n\t\tAND\n\t", $on);
				}
			}
			if(!empty($where)){
				$q .= "\nWHERE\n\t";
				$q .= join("\n\t\tAND\n\t", $where);
			}
			if(!empty($groupby)){
				$q .= "\nGROUP BY\n\t";
				$q .= join(",\n\t", $groupby);
			}
			if(!empty($orderby)){
				$q .= "\nORDER BY\n\t";
				$q .= join(",\n\t", $orderby);
			}
			return $q;
		}

		/**
		 * Возвращает INSERT-запрос к базе
		 * @param string $table Имя таблицы
		 * @param array $values Массив ключей и значений, где ключи - имена столбцов, а значения - значения для INSERT
		 * @return string Готовый INSERT-запрос к базе
		 */
		public static function getInsert(string $table, array $values):string{
			$k = [];
			// Если второй параметр является многомерным массивом, то выполнить вставку с использованием VALUES
			if(is_array($values[key($values)])){
				return self::getMultipleInsert($table, $values);
			}
			$v = [];
			foreach($values as $col => $value){
				$k[] = $col;
				$v[] = self::getSQLEquivalent($value);
			}
			$k = join(', ', $k);
			$v = join(', ', $v);
			return "INSERT INTO\n\t{$table}\n({$k})\n\tVALUE\n({$v})";
		}

		public static function getSelectInsert(string $table, array $columns, array $where = [], array $orderby = [], array $groupby = [], array $join = [], array $on = []):string{
			$cols = join(', ', $columns);
			$select = self::getSelect($table, $columns, $where, $orderby, $groupby, $join, $on);
			$q = "INSERT INTO\n\t{$table}\n({$cols})\n{$select}";
			return $q;
		}

		public static function getUpdate(string $table, array $columns, array $where = []):string{
			$cols = '';
			foreach($columns as $col => &$val){
				$val = self::getSQLEquivalent($val);
				$cols .= "\n\t{$col} = {$val},";
			}
			$cols = rtrim($cols, ',');
			$q = "UPDATE\n\t{$table}\nSET{$cols}";
			return $q;
		}

		public static function getDelete(string $table, string $where){
			return "DELETE FROM {$table} WHERE {$where}";
		}

		public static function getCreateTable(string $name, array $columns, array $properties = []){
			$fields = join(",\n\t", $columns);
			$props = join(' ', $properties);
			return "CREATE TABLE {$name}(\n\t{$fields}\n) {$props}";
		}

		/**
		 * Получает эквивалент типа для MySQL
		 * @param mixed $value Значение, используемое для превращение в MySQL-эквивалент. То есть, php-строка превратится в MySQL-строку, число - в значение без кавычек и т.д.
		 * @return string Значение, готовое к использованию в SQL-запросов. Является строкой только для php, но не для SQL
		 */
		private static function getSQLEquivalent($value):string{
			switch(gettype($value)){
				case 'boolean':
					return $value ? 'TRUE' : 'FALSE';
				case 'integer':
				case 'double':
				case 'float':
					return "{$value}";
				case 'string':
					return strtoupper($value) === 'DEFAULT' ? 'DEFAULT' : "'{$value}'";
				case 'NULL':
					return 'NULL';
			}
		}

		/**
		 * Возвращает многострочный INSERT-запрос
		 * @param string $table Имя таблицы
		 * @param array $values Многомерный массив со строками
		 * @return string
		 */
		private static function getMultipleInsert(string $table, array $values):string{
			$k = [];
			$v = array_map(null, ...array_values($values));
			foreach($values as $col => $vals){
				$k[] = $col;
			}
			foreach($v as &$row){
				foreach($row as &$val){
					$val = self::getSQLEquivalent($val);
				}
				$row = '('.join(', ', $row).')';
			}
			$k = join(', ', $k);
			$v = join(", \n", $v);
			return "INSERT INTO\n\t{$table}\n({$k})\n\tVALUES\n{$v}";
		}
	}