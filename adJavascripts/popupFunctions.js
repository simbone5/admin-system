/////////////////////////////////
// popupCounter tracks how many popups are open.
// This is so that when popups are closed and opened a div#popupIFrame is only
// hidden when last popup is closed
var popupCounter = 0;

function showPopup(popupId){	

	var bodyHeight = document.getElementsByTagName("body")[0].clientHeight+10;
	
	var windowHeight = 	window.innerHeight ? window.innerHeight : document.body.clientHeight;
	
	if(bodyHeight > windowHeight)
		iFrameHeight = bodyHeight;
	else
		iFrameHeight = windowHeight;
	
	popupCounter++;
	document.getElementById('popupIFrame'+popupCounter).style.display = "block";
	document.getElementById('popupIFrame'+popupCounter).style.zIndex = (100+(popupCounter*2));
	document.getElementById(popupId).style.display = "block";
	document.getElementById(popupId).style.zIndex = (101+(popupCounter*2));
	scroll(0,0);
}

function hidePopup(popupId){
	document.getElementById('popupIFrame'+popupCounter).style.display = "none";
	document.getElementById(popupId).style.display = "none";
	popupCounter--;
	
}