<?php
	namespace Application;

	use \System\Singleton;

	class HTMLHead extends Singleton{
		public function setTitle(string $title):void{}
		public function setDescription(string $description):void{}
		public function setKeywords(string $keywords):void{}
		public function addLink(string $href, string $rel, string $type):void{}
		public function addMeta(string $name, string $content):void{}
		public function addScript(string $src, array $atts):void{}
		public function addScriptContent(string $content, array $atts):void{}
		public function addStylesheet(string $src, array $atts):void{}
		public function addStylesheetContent(string $content, array $atts):void{}
		public function addCustomElement(string $name, string $atts, string $content = ''):void{}
	}