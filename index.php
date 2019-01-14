<?php
	require_once 'application/functions.php';
	use \System\File;
	use \System\Directory;
	$f = new File('/test/test.txt');
	// echo $f->move(new Directory('/'));
	$ar = [
		function(){
			return 1;
		}
	];
	echo $ar[0]();