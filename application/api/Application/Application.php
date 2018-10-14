<?php
	namespace Application;

	use \System\Singleton;
	use \System\PropertyAccess;

	/**
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
			$GLOBALS['_APPLICATION'] = $this;
			Buffer::start();
		}

		public function setTitle(string $title):void{
			$this->title = $title;
			Buffer::setProperty(self::NAMES['TITLE'], $title);
		}

		public function showTitle():void{
			Buffer::showProperty(self::NAMES['TITLE']);
		}

		protected function __destruct(){
			Buffer::end();
		}
	}