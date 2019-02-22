<?php
	namespace System;

	interface Comparable{
		public function equals(Comparable $obj):bool;
	}