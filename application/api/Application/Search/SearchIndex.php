<?php
	namespace Application\Search;
	use \Exception;

	class SearchIndex{

		/** @var string $t Имя MySQL таблицы для хранения индекса */
		public $t = 'Custom_Search_Table';
		/** @var string $fieldT Имя таблицы для хранения полей индекса */
		public $fieldT = 'Custom_Search_Field';

		/** @var bool $tExists Флаг существования таблицы с индексом */
		protected $tExists = false;
		/** @var bool $fieldTExists Флаг существования таблицы с полями */
		protected $fieldTExists = false;
		/** @var string $host Используемый для индексации домен */
		protected $host;
		/** @var string $scheme Используемый протокол */
		protected $scheme;
		/** @var string[] $index Массив проиндексированных страниц */
		protected $index = [];
		/** @var array $areas Массив индексируемых полей, отдельно от основного контента */
		protected $areas = [];
		/** @var string[] $areas Массив запрещенных к индексации областей */
		protected $forbidden = [];
		/** @var MySQLi $DB База данных */
		protected $DB;

		/**
		 * @param string $domain Домен сайта, который нужно проиндексировать. По умолчанию <code>$_SERVER['HTTP_HOST']</code>. Имя протокола можно опустить
		 * @param MySQLi $db Объект соединения с базой данных.
		 */
		public function __construct(string $domain = '', MySQLi $db = null){
			if($domain){
				$info = parse_url($domain);
				$this->host = @$info['host'] ?: $info['path'];
				$this->scheme = @$info['scheme'] ?: 'http';
			} else {
				$this->host = $_SERVER['HTTP_HOST'];
			}
			if($db){
				$this->DB = $db;
			}
		}

		/**
		 * Возвращает полный URL страницы из относительного пути к ней
		 * @param string $path Путь до страницы
		 * @return string
		 */
		public function getURL(string $path = '/'):string{
			return $this->scheme.'://'.$this->host.$path;
		}

		/**
		 * Устанавливает объект соединения с базой данных
		 * @param MySQLi $db БД
		 * @return void
		 */
		public function setDB(MySQLi $db):void{
			$this->DB = $db;
		}

		/**
		 * Индексирует содержимое сайта. Рекурсивно вызывает себя при индексации
		 * @param string $path Путь. По умолчанию главная страница
		 * @param int $depth Максимальное количество индексируемых докуметов. При <code>-1</code> сайт индексируется полностью
		 * @return void
		 */
		public function index($path = '/', $depth = -1){
			$hasDepth = $depth > -1;
			$info = parse_url($path);
			if($this->isIndexed($path) || ($hasDepth && !$depth)){
				return;
			}
			$this->index[] = $path;
			$this->indexContent($path);
			foreach($this->getLinks($path) as &$link){
				$link = (string) $link->attributes()->href;
				if(!$this->isLink(parse_url($link)) || $this->isExternal(parse_url($link))){
					continue;
				}
				$link = $this->restoreLink($path, $link);
				$this->index($link, --$depth);
			}
		}

		/**
		 * Индексирует контент каждой страницы
		 * @param string $path Путь
		 * @return void
		 */
		public function indexContent($path){
			$page = $this->pageAsXML($path);
			$qFields = ['Domain', 'Path'];
			$qValues = [$this->host, $path];
			foreach($this->areas as $area){
				$qFields[] = $area['Name'];
				$content = '';
				foreach($page->xpath($area['Rule']) as $xpath){
					$content .= strip_tags((string) $xpath);
				}
				$qValues[] = $content;
			}
			$dom = new DOMDocument();
			$dom->loadXML($page->asXML());
			$xpath = new DOMXPath($dom);
			$head = $xpath->query('//head')[0];
			$head->parentNode->removeChild($head);
			foreach($this->forbidden as $area){
				$nodes = $xpath->query($area['Rule']);
				foreach($nodes as $node){
					$node->parentNode->removeChild($node);
				}
			}
			$qFields[] = 'Content';
			$qValues[] = preg_replace('/[\x{0000}-\x{001F}]+/', ' ', strip_tags($dom->saveXML()));
			$qFields = join(', ', $qFields);
			$qValues = join('\', \'', $qValues);
			$q = "INSERT INTO {$this->tablePrefix}Search_Index ({$qFields}) VALUES ('$qValues')";
			$this->DB->query($q);
		}

		/**
		 * Восстанавливает относительные ссылки
		 * @param string $parentLink Родительская страница, по отношению в $path
		 * @param string $paеh Восстанавливаемая ссылка
		 * @return string
		 */
		public function restoreLink(string $parentLink, string $path):string{
			if($path{0} === '/'){
				return $path;
			} else {
				return rtrim($parentLink, '/').$path;
			}
		}
		/**
		 * Проверяет, является ли ссылка ссылкой на страницу, т.е. это не якорь и проч.
		 * @param array $info Массив частей URL
		 * @return bool
		 */
		public function isLink($info):bool{
			if(isset($info['scheme'])){
				return isset($info['path']) && preg_match('/https?/', $info['path']) && !in_array(pathinfo($info['path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'pdf']);
			} else {
				return isset($info['path']) && !in_array(pathinfo($info['path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'pdf']);
			}
		}

		/**
		 * Проверяет, является ли ссылка внешней
		 * @param array $info Массив частей URL
		 * @return bool
		 */
		public function isExternal($info){
			if(isset($info['host'])){
				if(isset($info['scheme'])){
					return $info['host'] === $this->host && preg_match('/https?/', $info['path']);
				} else {
					return $info['host'] === $this->host;
				}
			} else {
				return false;
			}
		}

		/**
		 * Возвращает указанную страницу в виде XML-объекта
		 * @param string $url Путь
		 * @return SimpleXMLElement Страницу в XML формате
		 */
		public function pageAsXML($path){
			$dom = new DOMDocument();
			$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'.file_get_contents($this->getURL($path)));
			return new SimpleXMLElement($dom->saveXML());
		}

		/**
		 * Проверяет, является ли страница уже проиндексированной
		 * @param string $path Путь
		 * @return bool
		 */
		public function isIndexed($path){
			return array_search($path, $this->index) !== false;
		}

		/**
		 * Возвращает массив ссылок указанной страницы
		 * @param string $path Путь
		 * @return SimpleXMLElement[] Массив ссылок
		 */
		public function getLinks($path){
			return $this->pageAsXML($path)->xpath('//a[@href]');
		}

		/**
		 * Создает таблицу для полей
		 * @return void
		 * @throws Exception Если уже существует таблица с таким именем
		 */
		public function createFieldTable():void{
			$q = <<<SQL
				CREATE TABLE {$this->fieldT}(
					ID TINYNT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					Domain VARCHAR(128) NOT NULL DEFAULT {$this->host},
					Name VARCHAR(32) NOT NULL,
					Rule VARCHAR(128) NOT NULL,
					Weight TINYINT NOT NULL DEFAULT 1,
					Forbidden BOOLEAN NOT NULL DEFAULT FALSE,
					UNIQUE KEY Name (Domain, Name)
				)
SQL;
			$this->DB->query($q);
			if($this->DB->errno){
				throw new Exception($this->DB->error);
			}
			$this->fieldTExists = true;
		}

		/**
		 * Создает таблицу для хранения индекса
		 * @return void
		 * @throws Exception Если уже существует таблица с таким именем
		 */
		public function createIndexTable():void{
			$q = <<<SQL
				CREATE TABLE {$this->t}(
					ID INT UNSIGNED NOT NULL DEFAULT AUTO_INCREMENT PRIMARY KEY,
					Domain VARCHAR(128) NOT NULL DEFAULT {$this->host},
					Path VARCHAR(256) NOT NULL,
					Content TEXT NULL,
					UNIQUE KEY URL (Domain, Path)
				)
SQL;
			$this->DB->query($q);
			if($this->DB->errno){
				throw new Exception($this->DB->error);
			}
			$this->tExists = true;
		}

		/**
		 * Возвращает все правила индексации
		 * @param int $areas Какие возвращать правила. -1 запрещенные, 0 - все и 1 - только разрешенные
		 * @return array Массив правил
		 * @uses self::checkDB()
		 */
		public function getRules(int $areas = 0):array{
			$this->checkDB();
			$result = [];
			switch($areas){
				case -1:
					$result = $this->DB->query("SELECT * FROM {$this->fieldT} WHERE Domain = '{$this->host}' AND Forbidden")->fetch_all(MYSQLI_ASSOC);
					break;
				case 0:
					$result = $this->DB->query("SELECT * FROM {$this->fieldT} WHERE Domain = '{$this->host}'")->fetch_all(MYSQLI_ASSOC);
					break;
				case 1:
					$result = $this->DB->query("SELECT * FROM {$this->fieldT} WHERE Domain = '{$this->host}' AND !Forbidden")->fetch_all(MYSQLI_ASSOC);
					break;
			}
			if($this->DB->errno){
				throw new Exception($this->DB->error);
			}
			return $result;
		}

		/**
		 * Добавляет правило для индексации
		 * @param string $name Название правила
		 * @param string $xpath XPath-запрос для выборки определенных областей на странице
		 * @param int $weight Вес поля
		 * @param int $forbidden Запрещена ли для индексации область
		 * @return void
		 * @throws Exception Если уже существует правило с таким именем
		 * @uses self::checkDB()
		 * @todo Добавить соответствующую колонку в таблицу индекса
		 */
		public function addRule(string $name, string $xpath, $weight = 1, $forbidden = 0):void{
			$this->checkDB();
			$q = "INSERT INTO {$this->fieldT} (Domain, Name, Rule, Weight, Forbidden) VALUE ('{$this->host}', '{$name}', '{$xpath}', {$weight}, {$forbidden})";
			$this->DB->query($q);
			if($this->DB->errno){
				throw new Exception($this->DB->error);
			}
		}

		/**
		 * Удаляет правило по его ID
		 * @param int $id Идентификатор правила
		 * @return void
		 * @uses self::checkDB()
		 */
		public function removeRuleByID(int $id):void{
			$this->checkDB();
			$q = "DELETE FROM {$this->fieldT} WHERE ID = {$id}";
			$this->DB->query($q);
		}
		
		/**
		 * Удаляет правило по его имени
		 * @param string $name Имя правила
		 * @return void
		 * @uses self::checkDB()
		 */
		public function removeRuleByName(string $name):void{
			$this->checkDB();
			$q = "DELETE FROM {$this->fieldT} WHERE Domain = '{$this->host}' AND Name = '{$name}'";
			$this->DB->query($q);
		}

		/**
		 * Проверяет на наличие соединения с базой данных или ошибок с соединением
		 * @return void
		 * @throws Exception Если есть проблемы с соединением
		 */
		private function checkDB():void{
			if(!$this->DB){
				throw new Exception('Database is not specified');
			}
			if($this->DB->connect_errno){
				throw new Exception($this->DB->connect_error);
			}
		}
	}