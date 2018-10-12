<?php
	namespace System\Database;

	use \Exception;

	checkExtension('pgsql');
	
	/**
	 * Класс для работы с базой PostgreSQL
	 */
	class PostgreSQL{
		/** @var string $host Имя хоста */
		private $host = '';
		/** @var int $post Номер порта для подключения */
		private $port = 5432;
		/** @var string $dbname Имя базы данных */
		private $dbname = '';
		/** @var string $connection Имя пользователя */
		private $user = 'root';
		/** @var string $connection Пароль */
		private $password = '';
		/** @var string $options Дополнительные опции для соединения */
		private $options = '';
		/** @var resource $connection Дескриптор соединения с базой данных */
		private $connection;

		/**
		 * Создает экземпляр класса, но не устанавливает соединение
		 * @param string $host Имя хоста, на котором находится база
		 * @param string $user Имя пользователя
		 * @param string $password Пароль
		 * @param string $dbname Имя базы данных
		 * @param int $port Номер порта
		 */
		public function __construct(string $host, string $user = 'root', string $password = '', string $dbname = '', int $port = 5432, string $options = ''){
			$this->host = $host;
			$this->user = $user;
			$this->password = $password;
			$this->dbname = $dbname;
			$this->port = $port;
		}

		public function __get(string $name){
			return $this->{$name};
		}

		public function __set(string $name, $value):void{
			if($name === 'port'){
				if(gettype($value) !== 'integer'){
					throw new \InvalidArgumentException('Port property should be an integer type');
				} else {
					$this->{$name} = $value;
				}
			} else {
				$this->{$name} = $value;
			}
		}

		/**
		 * Устанавливает соединение с базой
		 * @return void
		 * @throws Exception Если не удалось подключиться к серверу
		 */
		public function connect():void{
			if(!$this->connection){
				if($this->connection = pg_connect($this->createConnectionString())){
					throw new Exception('Cannot connect to PostgreSQL server');
				}
			}
		}
		public function query(){}
		protected function createConnectionString():string{
			$q = ["port={$this->port}"];
			if($this->host){
				$q[] = "host={$this->host}";
			}
			if($this->user){
				$q[] = "user={$this->user}";
			}
			if($this->password){
				$q[] = "password={$this->password}";
			}
			if($this->dbname){
				$q[] = "dbname={$this->dbname}";
			}
			if($this->options){
				$q[] = "options='{$this->options}'";
			}
			return join(' ', $q);
		}
	}