<?php
	namespace Application;

	class Application{

		public static function getInstance():Application{
			global $_APPLICATION;
			if(isset($_APPLICATION)){
				return $_APPLICATION;
			} else {
				$GLOBALS['_APPICATION'] = new self();
			}
		}
	}