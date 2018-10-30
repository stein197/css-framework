if(!window["Class"])
	throw new Error("Class 'Class' does not declared");
/**
 * Предоставляет доступ к каждому классу API, расположенному в директории /application/api
 * @class BackendAPI
 * @param {string} classname Полное имя класса (вместе с пространством имён). Пространства имён разделяются "."
 * @param {array} args Массив данных, передаваемых конструктору класса
 */
function BackendAPI(classname, args = []){
	this.classPath = classname.split(".");
	this.args = args;
}
Class.extend(BackendAPI, {
		className: "",
		args: [],
		async: false,
		construct: function(args){},
		desctruct: function(){},
		call: function(name, args){},
		callStatic: function(name, args){},
		getProperty: function(name){},
		getStaticProperty: function(name){},
		send: function(){}
	}, {
		SCRIPT: '/application/backendapi.php',
		METHOD: 'GET',

		/**
		 * Вызывает произвольную функцию с именем <code>name</code>
		 * Имя функции должно быть полным, т.е. если эта функция не глобальна, то должно быть предоставлено вместе с пространством имён
		 * Пространства имён отделяются друг от друга точкой (".")
		 * @param {string} name Полное имя функции
		 * @param {array} [args=[]] Массив аргументов, передаваемых в функцию
		 * @return {void}
		 */
		callFunction: function(name, args, f){
			var x = new XMLHttpRequest();
			args = args || [];
			x.open(this.METHOD, this.SCRIPT, true);
			x.onreadystatechange = function(){
				if(x.readyState === XMLHttpRequest.DONE){
					if(x.status === 200){
						f(x.response);
					}
				}
			}
			x.send();
		},

		/**
		 * Возвращает значение PHP-константы
		 * Имя константы должно быть полным, т.е. содержать в себе пространство имён, если таковое имеется
		 * @param {string} name Полное название константы
		 * @return {*} Значение константы
		 */
		getConst: function(name){}
	}
);