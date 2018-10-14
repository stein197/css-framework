<?php
	require_once 'application/functions.php';
	use System\Log;
	use \Application\Buffer;
	use System\File;

	class g{
		function __construct(){

		}
		function __destruct(){
			var_dump(1);
		}
	}
	$f = new g;
	$f = null;
	echo 'st';