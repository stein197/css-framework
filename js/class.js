/**
 * суперкласс для всех классов
 * @class
 */
function Class(){}
Class.prototype = {
	constructor: Class,
	get classname(){
		return this.constructor.name;
	},
	equals: function(obj){
		return this === obj;
	},
	clone: function(){
		return new this.constructor();
	},
	getPrototypes: function(){
		var proto = Object.getPrototypeOf(this);
		var chain = [proto];
		while(true){
			proto = Object.getPrototypeOf(proto);
			if(proto instanceof Class) chain.push(proto);
			else break;
		}
		return chain;
	}
}
/**
 * Наследование классов
 * @param {function} classname Наследующий класс
 * @param {(function|object)} proto Наследуемый класс или собственные методы, если класс наследует только Class
 * @param {object} [body] Собственные методы или (если не указан наследуемый класс) статические свойства
 * @param {object} [stat] Статические методы, если указан наследуемый класс
 * @return {void}
 */
Class.extend = function(classname, proto, body, stat){
	var parent;
	var methods;
	var statMethods;
	if(typeof proto === "function"){
		parent = proto;
		methods = body;
		statMethods = stat;
	} else {
		parent = Class;
		methods = proto;
		statMethods = body;
	}
	classname.prototype = Object.create(parent.prototype);
	classname.prototype.constructor = classname;
	for(var m in methods)
		classname.prototype[m] = methods[m];
	for(var s in statMethods)
		classname[s] = statMethods[s];
}