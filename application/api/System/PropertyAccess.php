<?php
	namespace System;

	use \PropertyAccessException;
	use \ReflectionClass;
	use \ReflectionProperty;

	checkExtension('Reflection');

	/**
	 * Трейт, регулирующий доступ к свойствам класса. Работает на основе doc-комментариев к используещему этот трейт классу.
	 * Имеет смысл использовать, если есть doc-аннотации <code>@property-read</code> и <code>@property-write</code>, которые соответственно будут регулировать чтение/запись свойств, но не поведение
	 * По сути, просто имитирует геттеры и сеттеры
	 * Геттеры и сеттеры реагируют только на свойства класса (то есть, если свойство заранее прописано в классе)
	 * Также, геттеры и сеттеры реагируют только если к соответствующим свойствам есть аннотации в doc-блоке класса и если свойства имеют модификатор доступа <code>private/protected</code>
	 * @todo Добавить методы для управления поведением при чтении/записи в private/protected свойства
	 */
	trait PropertyAccess{

		/**
		 * Чтение <code>private/protected</code> свойства, к которому есть <code>@property-read</code> аннотация в doc-блоке класса
		 * Такое свойство нельзя изменить
		 * @param string $name Имя получаемого свойства
		 * @throws PropertyAccessException В случае, если свойство имеет приватный доступ и к нему нет аннотации <code>@property-read</code>
		 */
		public function __get(string $name){
			// Есть ли свойство в определении класса
			if(property_exists(self::class, $name)){
				$c = new ReflectionClass(self::class);
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
				throw new PropertyAccessException($p->getName(), $p->isPrivate() ? 'private' : 'protected', self::class, PropertyAccessException::M_READ);
			// Если нет, просто устанавливаем произвольное свойство
			} else {
				return $this->{$name};
			}
		}

		/**
		 * Запись в свойство с модификатором <code>private/protected</code>, к которому есть <code>@property-write</code> аннотация в doc-блоке класса
		 * Такое свойство нельзя прочитать
		 * @param string $name Имя записываемого свойства
		 * @param mixed $value Записываемое свойство. Тип должен совпадать с типом, указанным в аннотации
		 */
		public function __set(string $name, $value){
			if(property_exists(self::class, $name)){
				$c = new ReflectionClass(self::class);
				$p = $c->getProperty($name);
				$doc = new DocComment($c->getDocComment());

				if($doc->getAnnotation('property-write')){
					foreach($doc->getAnnotation('property-write') as $annotation){
						if($annotation['NAME'] === '$'.$name){
							$types = explode('|', $annotation['TYPE']);
							foreach($types as $type){
								if(typeof($value, $type)){
									return $this->{$name} = $value;
								}
							}
						}
					}
				}
				throw new PropertyAccessException($p->getName(), $p->isPrivate() ? 'private' : 'protected', self::class, PropertyAccessException::M_WRITE);
			} else {
				$this->{$name} = $value;
			}
		}
	}