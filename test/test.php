<?php
	use \System\Log;
	use \System\Path;
	$p = (new Path('./../../../..', Path::PATH_RELATIVE, true))->get(true);
	Log::println($p);