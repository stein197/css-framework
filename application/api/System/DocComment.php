<?php
	namespace System;

	/**
	 * Простой класс для работы с PHP-документацией. Парсит входящую строку doc-комментария в удобоиспользуемую структуру
	 * Предназначен прежде всего для документирования и рефлексии
	 * Описание может быть многострочным и обязательно в начале блока. Возможны пустые строки до, внутри и после блока описания
	 * Также возможно наличие/отсутствие точек и пустых пробельных символом в конце каждой строки блока описания и пробельных символов вокруг блока комментария
	 * Описание к аннотацием только однострочное, новые строки не допускаются
	 * @property-read string $description
	 */
	class DocComment{

		use PropertyAccess;

		/** @var array PATTERNS Шаблоны синтаксиса аннотаций. Каждое отдельное слово в строке является ключом ассоциативного массива в массиве аннотаций. Флаг <code>multiple</code> показывает, что можно использовать эту аннотацию несколько раз в одном блоке */
		public const PATTERNS = [
			'author' => ['pattern' => 'NAME EMAIL',				'multiple' => false],
			'deprecated' => ['pattern' => 'VERSION',			'multiple' => false],
			'example' => ['pattern' => 'LOCATION START COUNT',	'multiple' => false],
			'global' => ['pattern' => 'TYPE NAME',				'multiple' => false],
			'license' => ['pattern' => 'URL NAME',				'multiple' => false],
			'property' => ['pattern' => 'TYPE NAME',			'multiple' => true],
			'property-read' =>['pattern' => 'TYPE NAME',		'multiple' => true],
			'property-write' =>['pattern' => 'TYPE NAME',		'multiple' => true],
			'var' => ['pattern' => 'TYPE NAME',					'multiple' => false],
			'param' => ['pattern' => 'TYPE NAME',				'multiple' => true],
			'throws' => ['pattern' => 'TYPE',					'multiple' => false],
			'return' => ['pattern' => 'TYPE',					'multiple' => false],
			'link' => ['pattern' => 'URL',						'multiple' => false],
			'see' => ['pattern' => 'URL',						'multiple' => false],
			'uses' => ['pattern' => 'FQSEN',					'multiple' => true],
			'since' => ['pattern' => 'VERSION',					'multiple' => false],
			'source' => ['pattern' => 'START COUNT',			'multiple' => false]
		];

		/** @var string $doc Первоначальная строка комментария (без обрамляющих слешэй) */
		private $doc = '';
		/** @var string[] $lines Строки блока комментария без звёздочек */
		private $lines;
		/** @var array[] $annotations Массив параметров извлеченных из комментария */
		private $annotations = [];
		/** @var string $description Описание к функции/классу/переменной/методу */
		private $description = '';

		/**
		 * Создает структурированное предствление doc-комментария и парсит содержимое
		 * @param string $doc Блок doc-комментария
		 */
		public function __construct(string $doc){
			$trimmed = preg_replace('/(?:^\s*\/\*+\s*|\s*\*+\/\s*)/', '', trim($doc));
			$this->doc = $trimmed;
			$this->lines = new Collection(\string::class, preg_split('/\s*\*\s*/', $this->doc, -1, PREG_SPLIT_NO_EMPTY));
			$this->parse();
		}

		public function __toString():string{
			return $this->doc;
		}

		/**
		 * Возвращает свойства аннотации по имени. Не используются вычисления
		 * @param string $name Название аннотации
		 * @return array|null Массив свойств аннотации или <code>null</code>, если такой аннотации не найдено
		 */
		public function getAnnotation(string $name):?array{
			return @$this->annotations[$name] ?: null;
		}

		/**
		 * Возвращает все аннотации
		 * @return array|null Массив всех аннотаций, или <code>null</code>, если блок комментария не содержит аннотаций
		 */
		public function getAnnotations():?array{
			return empty($this->annotations) ? null : $this->annotations;
		}

		/**
		 * Возвращает блок описания
		 * @return string
		 */
		public function getDescription():string{
			return $this->description;
		}

		/**
		 * Парсит и обрабатывает блок комментария. Основная логика класса
		 * @return void
		 */
		protected function parse():void{
			$descLines = [];
			foreach($this->lines as $line){

				if(self::isAnnotation($line)){
					$fragments = explode(' ', $line);
					$pName = substr(array_shift($fragments), 1);
					$pattern = self::getPattern($pName);
					$annotation = &$this->getAnnotationElement($pName, $pattern['multiple']);
					$struct = explode(' ', $pattern['pattern']);
					foreach($struct as $name){
						$annotation[$name] = array_shift($fragments);
					}
					$annotation['DESCRIPTION'] = join(' ', $fragments);
				} else {
					$descLines[] = preg_replace('/\.\s*$/', '', $line);
				}
			}
			$this->description = join('. ', $descLines);
		}

		/**
		 * Возвращает элемент аннотации по ссылке. Используется методом <code>self::parse()</code>
		 * @param string $name Имя возвращаемого элемента аннотации
		 * @param bool $isMultiple Можно ли использовать эту аннотацию несколько раз в блоке
		 * @return array Элемент аннотации по ссылке
		 */
		protected function &getAnnotationElement(string $name, bool $isMultiple):array{
			if(!isset($this->annotations[$name])){
				$this->annotations[$name] = [];
			}
			if($isMultiple){
				$l = sizeof($this->annotations[$name]);
				$this->annotations[$name][] = [];
				return $this->annotations[$name][$l];
			} else {
				return $this->annotations[$name];
			}
		}

		/**
		 * Возвращает шаблон аннотации для аннотации по имени
		 * @param string $name Имя шаблона
		 * @return array Существующий шаблон из <code>self::PATTERNS</code>, или по умолчанию, если такого нет
		 */
		public static function getPattern(string $name):array{
			if(isset(self::PATTERNS[$name])){
				return self::PATTERNS[$name];
			} else {
				return [
					'pattern' => '',
					'multiple' => false
				];
			}
		}

		/**
		 * Проверяет, является ли строка аннотацией
		 * @param string $line Строка для проверки
		 * @return bool
		 */
		public static function isAnnotation(string $line):bool{
			return $line{0} === '@';
		}
	}