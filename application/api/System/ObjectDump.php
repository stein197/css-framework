<?php
	namespace System;

	use \ReflectionClass;
	use \Reflection;

	checkExtension('Reflection');

	trait ObjectDump{
		public function dump(){
			$result = [];
			$r = new ReflectionClass(static::class);
			foreach($r->getProperties() as $p){
				if($p->isStatic())
					continue;
				$prop = [
					'MODIFIER' => join(' ', Reflection::getModifierNames($p->getModifiers())),
				];
				$v = $this->{$p->getName()};
				if(is_object($v) && !is_array($v)){
					$vr = new ReflectionClass(get_class($v));
					$hasTrait = false;
					foreach($vr->getTraits() as $trait){
						if($trait->name === __TRAIT__){
							$hasTrait = true;
							break;
						}
					}
					if($hasTrait){
						$prop['VALUE'] = $v->dump();
					} else {
						$prop['VALUE'] = print_r($v, true);
					}
				} else {
					$prop['VALUE'] = $v;
				}
				$result[$p->getName()] = $prop;
			}
			return $result;
		}
	}