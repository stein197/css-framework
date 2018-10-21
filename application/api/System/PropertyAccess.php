<?php
	namespace System;

	use \PropertyAccessException;
	use \InvalidArgumentException;
	use \ReflectionClass;
	use \ReflectionProperty;

	checkExtension('Reflection');

	/**
	 * Трейт, регулирующий доступ к свойствам класса. Работает на основе doc-комментариев к используещему этот трейт классу.
	 * Имеет смысл использовать, если есть doc-аннотации <code>@property-read</code> и <code>@property-write</code>, которые соответственно будут регулировать чтение/запись свойств, но не поведение
	 * По сути, просто имитирует геттеры и сеттеры
	 * Геттеры и сеттеры реагируют только на свойства класса (то есть, если свойство заранее прописано в классе)
	 * Также, геттеры и сеттеры реагируют только если к соответствующим свойствам есть аннотации в doc-блоке класса и если свойства имеют модификатор доступа <code>private/protected</code>
	 * Удобно, если например нужно имитировать неизменяемые публичные свойства
	 * @todo Поиск аннотации у родительских классов
	 * @version 1.0
	 */
	trait PropertyAccess{

		/**
		 * Чтение <code>private/protected</code> свойства, к которому есть <code>@property-read</code> аннотация в doc-блоке класса
		 * Такое свойство нельзя изменить извне
		 * @param string $name Имя получаемого свойства
		 * @return mixed
		 * @throws PropertyAccessException В случае, если свойство имеет приватный доступ и к нему нет аннотации <code>@property-read</code>
		 */
		public function __get(string $name){
			// Есть ли свойство в определении класса
			if(property_exists(static::class, $name)){
				$c = new ReflectionClass(static::class);
				$p = $c->getProperty($name);
				$doc = new DocComment($c->getDocComment());

				if($doc->getAnnotation('property-read')){
					// Проверить на существование аннотации @property-read с таким именем
					foreach($doc->getAnnotation('property-read') as $annotation){
						if($annotation['NAME'] === '$'.$name){
							return $this->{$name};
						}
					}
				}
				throw new PropertyAccessException($p->getName(), $p->isPrivate() ? 'private' : 'protected', static::class, PropertyAccessException::M_WRITE);
			// Если нет, просто возвращаем произвольное свойство
			} else {
				return $this->{$name};
			}
		}

		/**
		 * Запись в свойство с модификатором <code>private/protected</code>, к которому есть <code>@property-write</code> аннотация в doc-блоке класса
		 * Если в качестве записываемого типа выступает класс, который находится в пространстве имен вместе с классом, указанным в аннотации, то в комментарии допускается опускать полное имя класса (т.е. пространство имен)
		 * Иначе, в аннотации следует указать полное имя класса
		 * Такое свойство нельзя прочитать извне
		 * @param string $name Имя записываемого свойства
		 * @param mixed $value Записываемое свойство. Тип должен совпадать с типом, указанным в аннотации
		 * @return mixed
		 * @throws InvalidArgumentException Если тип записываемого значения не совпадает с типом, описанным в аннотации
		 * @throws PropertyAccessException Если есть попытка записи в недоступное свойство, для которого нет <code>@property-write</code> аннотации
		 */
		public function __set(string $name, $value){
			if(property_exists(static::class, $name)){
				$c = new ReflectionClass(static::class);
				$ns = $c->getNamespaceName();
				$ns = $ns ? '\\'.$ns.'\\' : '\\';
				$p = $c->getProperty($name);
				$doc = new DocComment($c->getDocComment());

				if($doc->getAnnotation('property-write')){

					// Цикл по всем аннотациям @property-write
					foreach($doc->getAnnotation('property-write') as $annotation){
						if($annotation['NAME'] === '$'.$name){
							$types = explode('|', $annotation['TYPE']);
							$fullTypeNames = [];

							// Цикл по всем типам, указанным в @property-write
							foreach($types as &$type){
								if(!in_array($type, ['bool', 'boolean', 'int', 'integer', 'double', 'float', 'string', 'resource', 'null', 'array', 'mixed']) && !preg_match('/^.+\[\]$/', $type)){
									if($type{0} !== '\\'){
										$type = $ns.$type;
									}
								}
								$fullTypeNames[] = $type;
								if(typeEqualsTo($value, $type)){
									return $this->{$name} = $value;
								}
							}
							throw new InvalidArgumentException(sformat('Cannot set \%1::$%2 property. Expected type(-s) %3, %4 supplied', static::class, $p->getName(), join('|', $fullTypeNames), typeof($value)));
						}
					}
				}
				throw new PropertyAccessException($p->getName(), $p->isPrivate() ? 'private' : 'protected', static::class, PropertyAccessException::M_READ);
			} else {
				$this->{$name} = $value;
			}
		}
	}