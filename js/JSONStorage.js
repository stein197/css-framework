function JSONStorage(name, type){
	this.name = name;
	type = type || JSONStorage.OBJECT;
	if(window[this.name] === undefined){
		switch(type){
			case JSONStorage.LIST:
				window[this.name] = [];
				break;
			case JSONStorage.OBJECT:
				window[this.name] = {};
				break;
			default:
				throw new Exception("Unknown datatype");
		}
	}
}

function JSONLocalStorage(key){
	if(!key || typeof key !== "string") throw new TypeError("First argument type must be a key string");
	var item = localStorage.getItem(key);
	this.name = key;
	Object.defineProperty(this, "name", {
		configurable: false,
		writable: false
	});
	// Если нет такого ключа в localStorage
	if(item === null) localStorage.setItem(key, JSON.stringify({}));
}
Class.extend(JSONLocalStorage, JSONStorage, {
	name: "",
	get: function(){
		return JSON.parse(localStorage.getItem(this.name));
	},
	set: function(obj){
		localStorage.setItem(this.name, JSON.stringify(obj));
	},
	remove: function(){
		localStorage.removeItem(this.name);
	},
	equals: function(c){
		return Class.prototype.equals.call(this, c) && localStorage.getItem(this.name) === localStorage.getItem(c.name);
	}
});

Class.extend(JSONStorage, {
	name: "",
	get: function(){
		return window[this.name];
	},
	set: function(data){
		window[this.name] = data;
	},
	remove: function(){
		return delete window[this.name];
	},
}, {
	LIST: 0,
	OBJECT: 1
});