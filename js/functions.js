function getRequestQuery(data, keyname = ""){
	var result = [];
	for(var key in data){
		if(Array.isArray(data[key])){
			result.push(getRequestQuery(data[key], key));
		} else {
			if(keyname){
				result.push(keyname + "=" + data[key]);
			} else {
				result.push(key + "=" + data[key]);
			}
		}
	}
	return result.join("&");
}

var BraceParser = {

	BRACE_ROUND: 0,
	BRACE_SQUARE: 1,
	BRACE_CURVE: 2,
	BRACE_CORNER: 3,

	getLevel: function(string, level, brace){
		var length = string.length;
		var result = [];
		var braces = this.getBraces(brace);
		var depth = 0;
		var braceIndex = 0;
		var currentString = "";
		for(var i = 0; i < length; i++){
			var char = string.charAt(i);
			if(char === braces.open){
				depth++;
				if(depth > level)
					currentString += char;
			} else if(char === braces.close) {
				if(depth > level)
					currentString += char;
				depth--;
				if(level - 1 === depth){
					result[braceIndex] = currentString;
					braceIndex++;
					currentString = "";
				}
			} else {
				if(depth >= level)
					currentString += char;
			}
		}
		return result;
	},
	getMaxDepth: function(string, brace){

	},
	getBraces: function(type){
		var braces = {
			open: "",
			close: ""
		};
		switch(type){
			case this.BRACE_ROUND:
				braces.open = "(";
				braces.close = ")";
				break;
			case this.BRACE_SQUARE:
				braces.open = "[";
				braces.close = "]";
				break;
			case this.BRACE_CURVE:
				braces.open = "{";
				braces.close = "}";
				break;
			case this.BRACE_CORNER:
				braces.open = "<";
				braces.close = ">";
				break;
		}
		return braces;
	}
}
var str = "string (with (deep)) (braces) inside (string.(another (one)))";