<?php
	namespace System;

	use \ReflectionClass;
	use \Reflection;

	checkExtension('Reflection');

	/**
	 * Предоставляет средства для дампа объектов в виде JSON-структуры
	 * Если класс реализует этот трейт, то вызов <code>\System\Log::dump()</code> на этом объекте выдаст JSON-представление полей объекта,
	 * как публичных, так и сокрытых от внешнего доступа. В любом случае можно вызвать метод <code>self::dump()</code> напрямую,
	 * который вернёт обычный массив свойств объекта, который в дальнейшем можно преобразовать в JSON-строку
	 * Если объект содержит поле непримитивного типа (то есть, поле, тип которого принадлежит определенному классу),
	 * то в ключ <code>VALUE</code> для свойства пропишится рекурсивное значение этого поля, но только в том случае,
	 * если сам тип поля реализует трейт <code>ObjectDump</code>, иначе вернется просто стандартная строка дампа для поля-объекта
	 * @todo Попробовать реализовать через __debugInfo(), упростить метод
	 * @version 1.0
	 */
	trait ObjectDump{

		/**
		 * Единственный метод трейта
		 * Возвращает массив полей объекта, для каждого поля возвращаются его значение, тип и модификатор доступа,
		 * а ключами массивов являются имена полей
		 * @param bool $checkArrays Проделывать ли цикл по каждому свойству типа <code>array</code>, в случае если например в свойстве-массиве хранится объект и его надо вывести
		 * @return array[]
		 */
		public function dump(bool $checkArrays = true){
			$result = [];
			$r = new ReflectionClass(static::class);
			foreach($r->getProperties() as $p){
				if($p->isStatic())
					continue;
				$prop = [
					'MODIFIER' => join(' ', Reflection::getModifierNames($p->getModifiers())),
				];
				$v = $this->{$p->getName()};
				$type = gettype($v);
				$type = $type === 'object' ? get_class($v) : $type;
				$prop['TYPE'] = $type;
				if(is_object($v) && !is_array($v)){
					$vr = new ReflectionClass($type);
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
				} elseif(is_array($v) && $checkArrays){
					$arValue = [];
					foreach($v as $key => $value){
						if(is_object($value) && !is_array($value)){
							$vr = new ReflectionClass(get_class($value));
							$hasTrait = false;
							foreach($vr->getTraits() as $trait){
								if($trait->name === __TRAIT__){
									$hasTrait = true;
									break;
								}
							}
							if($hasTrait){
								$arValue[$key] = [
									'TYPE' => get_class($value),
									'VALUE' => $value->dump()
								];
							} else {
								$arValue[$key] = print_r($value, true);
							}
						} else {
							$arValue[$key] = $value;
						}
					}
					$prop['VALUE'] = $arValue;
				} else {
					$prop['VALUE'] = $v;
				}
				$result[$p->getName()] = $prop;
			}
			return $result;
		}
	}