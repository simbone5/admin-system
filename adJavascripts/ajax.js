function ajax(url, func){
	var httpRequest = new XMLHttpRequest();
	if(httpRequest != null ){
		httpRequest.onreadystatechange = function(){
			if((httpRequest.readyState == 4) && (httpRequest.status == 200)){
				func(httpRequest);
			}
		};
		httpRequest.open("GET", encodeURI(url), true);
		httpRequest.send(null); 

	}
}