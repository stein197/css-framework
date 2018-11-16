<?php
	namespace Application;

	use \System\Singleton;
	use \System\PropertyAccess;

	/**
	 * Представляет собой полноценный класс-синглтон, который может играть роль сущности "сайт"
	 * @property-read string $title
	 */
	class Application extends Singleton{

		use PropertyAccess;

		protected const NAMES = [
			'TITLE' => 'pageTitle',
			'DESCRIPTION' => 'pageDescription',
			'KEYWORDS' => 'pageKeywords',
			'H1' => 'pageH1',
		];

		protected $title;

		protected function __construct(){
			Buffer::start();
		}

		public function setTitle(string $title):void{
			$this->title = $title;
			Buffer::setProperty(self::NAMES['TITLE'], $title);
		}

		public function showTitle():void{
			Buffer::showProperty(self::NAMES['TITLE']);
		}

		public function addHeadScript(string $url):void{}
		public function addHeadStylesheet(string $url):void{}
		public function addHeadLink(string $href, array $atts):void{}
		public function addHeadMeta(array $atts):void{}

		protected function __destruct(){
			Buffer::end();
		}
	}