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
