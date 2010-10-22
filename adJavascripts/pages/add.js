var currentSelected = null;

function setParentPageId(id){
	
	//remove dot from previous selection
	if(currentSelected!=null){
		document.getElementById("pagID_"+currentSelected).setAttribute("class", "");
		document.getElementById("pagID_"+currentSelected).setAttribute("className", "");
	}
	
	//update input value
	document.getElementById("parentPagId").value = id;
	currentSelected = id;
	
	//set class for newly selected
	document.getElementById("pagID_"+currentSelected).setAttribute("class", "selected");
	document.getElementById("pagID_"+currentSelected).setAttribute("className", "selected");
	
	
}