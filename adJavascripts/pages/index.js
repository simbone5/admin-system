function saveStructure(){
	///////////////////////////////////
	// Called when the page structure form is submitted
	// Calls getLis() to place the structure of the <ul> and <li>s into a <textarea>
	
	var ul = document.getElementById("treeRoot");
	var txtArea = document.getElementById("structure");
	
	txtArea.value = "<pages>\n";
	getLis(ul);
	txtArea.value += "</pages>";
	
	return true;
}


function getLis(ul){
	///////////////////////////////////
	// Recursive function
	// Converts a passed <ul> and its <li>s into 
	// <item pagID=''> tags and writes them to textarea
	var txtArea = document.getElementById("structure");
	var li = ul.firstChild;
	do{
		if(li.nodeType==1){
			var childUls = li.getElementsByTagName("ul");
			var pagId = li.getAttribute("id").replace(/pagID_/, "");
			//pagId = li.firstChild.innerHTML;
			if(childUls.length>=1){
				txtArea.value += '<item pagID="'+pagId+'">\n';
				getLis(childUls[0]);
				txtArea.value += '</item>\n';
			}
			else{
				txtArea.value += '<item pagID="'+pagId+'"/>\n';
			}
		}
	}while(li = li.nextSibling)
}