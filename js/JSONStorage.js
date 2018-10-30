if(!window["Class"])
	throw new Error("Class 'Class' does not declared");
function JSONStorage(){}
Class.extend(JSONStorage, Interface, {
		name: "",
		get: function(){},
		set: function(){},

	},{
		TYPE_LIST: 0b0,
		TYPE_OBJECT: 0b1
	}
);

function JSONSessionStorage(){}
Interface.implement(JSONSessionStorage, JSONStorage);