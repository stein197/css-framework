<?php
	namespace System;

	use \Exception;

	/**
	 * Обёртка для файловых путей локальной машины. Пути могут быть представлены как в полной форме (путь относительно корня ФС),
	 * абсолютной (относительно DOCUMENT_ROOT), если идет работа с файлом внутри этой директории,
	 * и относительной (относительно файла в котором вызывает конструктор)
	 * Внутри все пути обязательно приводятся к полному виду и абсолютному (если есть возможность)
	 * Констуктору обязательно должен быть передан параметр отвечающий за то,
	 * какой путь из себя представляет параметр. По умолчанию все пути - абсолютные
	 * Относительные пути выстраиваются относительно директории скрипта, в которой вызывается конструктор
	 * Т.е. - если есть скрипт <code>/var/www/public_html/script/index.php</code> и в нем вызывается <code>new Path('inner/access.log', Path::PATH_RELATIVE)</code>,
	 * то путь будет преобразован в <code>/var/www/public_html/script/inner/access.log</code>
	 * По умолчанию разделителями путей являются прямые слэши на всех системах
	 */
	class Path{

		/** @var string FOLDER_CURRENT Представляет собой текущую директорию */
		public const FOLDER_CURRENT = '.';
		/** @var string FOLDER_PARENT Представляет собой директорию родителя */
		public const FOLDER_PARENT = '..';
		/** @var int PATH_FULL Флаг того что путь полный */
		public const PATH_FULL = 0b001;
		/** @var int PATH_ABSOLUTE Флаг того что путь абсолютный. Устанавливается по умолчанию */
		public const PATH_ABSOLUTE = 0b010;
		/** @var int PATH_RELATIVE Флаг того что путь относительный */
		public const PATH_RELATIVE = 0b100;
		/** @var string $DS Разделитель директорий */
		public static $DS = '/';

		/** @var string Полный путь до файла */
		protected $full;
		/** @var string|null $absolute Абсолютный путь до файла. <code>null</code>, если файл не находится внутри <code>DOCUMENT_ROOT</code> */
		protected $absolute = null;
		/** @var bool $normalized Флаг нормализации. Стоит в <code>true</code>, если путь был нормализован */
		protected $normalized = false;
		/** @var string $url нормализованный URL с прямыми слэшами для вставки в HTML-код */
		protected $url = null;

		/**
		 * Создаёт обёртку над путями ФС
		 * @param string $path Передаваемый путь к директории/файлу
		 * @param int $type Тип передаваемого пути. По умолчанию абсолютный
		 * @param bool $normalize Нормализовывать ли путь (убирать лишние слэши, переходы типа <code>/../</code>)
		 */
		public function __construct(string $path, int $type = self::PATH_ABSOLUTE, bool $normalize = false){
			$root = rtrim(preg_replace('#[\\\/]#', self::$DS, $_SERVER['DOCUMENT_ROOT']), self::$DS);
			switch($type){
				case self::PATH_FULL:
					break;
				case self::PATH_ABSOLUTE:
					$path = $root.$path;
					break;
				case self::PATH_RELATIVE:
					$path = dirname(debug_backtrace()[0]['file']).self::$DS.$path;
					break;
			}
			$this->full = preg_replace('#[\\\/]+#', self::$DS, $path);
			if(strpos($this->full, $root) === 0)
				$this->absolute = str_replace($root, '', $this->full);
			if($normalize)
				$this->normalize();
		}

		/**
		 * Возвращает полный путь
		 * @return string
		 */
		public function __toString():string{
			return $this->full;
		}

		/**
		 * Возвращает полный или абсолютный путь
		 * @return string|null Полный или абсолютный путь. <code>null</code> в случае, если абсолютного пути нет
		 */
		public function get(bool $full = false):?string{
			return $full ? $this->full : $this->absolute;
		}

		/**
		 * Возвращает абсолютный путь. Отличие от <code>self::get()</code> в том, что все слэши прямые, а сам путь нормализуется
		 * Результаты работы сохраняются в переменную <code>$url</code>
		 * Используется для вставки пути в HTML код
		 * @param bool $useHost Возвращать ли имя хоста вместе с путём
		 * @return string URL
		 * @throws \Exception Если для пути нельзя найти URL относительно DOCUMENT_ROOT
		 * @cacheable
		 */
		public function getURL(bool $useHost = false):string{
			if($this->url)
				return $this->url;
			if($this->absolute === null)
				throw new Exception("There is no absolute path for '{$this->full}'. File is outside of document root");
			$this->normalize();
			$this->url = str_replace('\\', '/', $this->absolute);
			if($useHost)
				$this->url = (@$_SERVER['HTTPS'] || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$this->url;
			return $this->url;
		}
		
		/**
		 * Нормализует путь. Убирает двойные и лишние слэши, переходы типа <code>/../</code> и <code>/./</code>
		 * @return void
		 * @throws \Exception Если количество родительских переходов (/../) превышает допустимый предел
		 * @cacheable
		 */
		public function normalize():void{
			if($this->normalized)
				return;
			$parts = explode(self::$DS, $this->full);
			$result = [];
			foreach($parts as $part)
				switch($part){
					case self::FOLDER_CURRENT:
						continue;
						break;
					case self::FOLDER_PARENT:
						array_pop($result);
						if(!sizeof($result))
							throw new Exception("Can't normalize '{$this->full}' path. Too deep parent chain");
						break;
					default:
						$result[] = $part;
						break;
				}
			$root = rtrim(preg_replace('#[\\\/]#', self::$DS, $_SERVER['DOCUMENT_ROOT']), self::$DS);
			$this->full = join(self::$DS, $result);
			if(strpos($this->full, $root) === 0)
				$this->absolute = str_replace($root, '', $this->full);
			else
				$this->absolute = null;
			$this->normalized = true;
		}
	}