if(!window["Class"])
	throw new Error("Class 'Class' does not declared");
function Interface(){
	throw new Error("Can't create instance of interface");
}
Class.extend(Interface);