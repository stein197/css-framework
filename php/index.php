<?php
	require_once 'functions.php';
	echo import('System.Path');
	use System\Path;
	echo Path::getAbsolute($_SERVER['DOCUMENT_ROOT'].'/css/st.css');
	echo PHP_EOL;
	echo Path::getAbsolute('/css/st.css');
	echo PHP_EOL;
	echo Path::getAbsolute('css/st.css');