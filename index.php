<?php
	require_once 'application/functions.php';
	use System\Log;
	use \Application\Buffer;
	use System\File;
	Buffer::start();

	Buffer::showSection('sec');
	Log::println('after section sec');
	Buffer::showSection('sec2');
	Log::println('after section sec2');

	Buffer::startSection('sec');
	Log::println('1');
	Log::println('2');
	Log::println('3');

	Buffer::startSection('sec2');
	Log::println('4');
	Buffer::startSection('sec3');
	Log::println('5');
	Buffer::endSection('sec3');
	Log::println('6');
	Buffer::endSection('sec2');

	Log::println('7');
	Log::println('8');
	Log::println('9');
	Buffer::endSection('sec');
	
	Buffer::end();

	// Log::dump(Buffer::$stack, Buffer::$sections);