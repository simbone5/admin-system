
var htmlInputs = ["input", "textarea", "select"];

function loadFieldValues(tinyMCE){
	var frame = window.frames[0].document;
	for(i=0;i<fieIds.length;i++){
		////////////////////////////////////
		//cycle through the inputs (childNodes) of each 'field_'+fieIds[i]
		var inputs = frame.getElementById('field_'+fieIds[i]).childNodes;
		for(j=0;j<inputs.length;j++){
			if(inputs[j].tagName && in_array(inputs[j].tagName.toLowerCase(), htmlInputs)){
				var input = inputs[j];
				if(input.getAttribute("class")=="tinymce" || input.getAttribute("className")=="tinymce"){
					//////////////////////////////////////////
					// innerHTML returns invalid XHTML, so we'll use proper way using tinyMCE
					/*var content ="";
					var tinyMCEFrame = window.frames[0].document.getElementById("fieContent_"+fieIds[i]+"_ifr")
					
					if(tinyMCEFrame.contentWindow.document)
						var docRoot = tinyMCEFrame.contentWindow.document;
					else
						var docRoot = tinyMCEFrame.contentDocument;
					
					if(!docRoot.getElementsByTagName("body")[0].innerHTML){
						alert("loadFieldValues - unsupported browser");
					}
					
					
					content = docRoot.getElementsByTagName("body")[0].innerHTML
					*/
					content = window.frames[0].tinyMCE.get("fieContent_"+fieIds[i]).getContent();
				}
				else if(input.tagName.toLowerCase()=="select")
					content = input.selectedIndex;
				else
					content = input.value;
				
				//alert(content);	
				/////////////////////////////////////////////////////
				// Copy content into hidden form
				if(input.tagName.toLowerCase()=="select")
					document.getElementById(input.id).selectedIndex = content;
				else
					document.getElementById(input.id).value = content;
				//alert(input.tagName.toLowerCase()+"\n content: "+content+"\nid: "+input.id+"\n value:"+input.value);
			}
		}
	}
	return true;
}

function alterFrameNavigation(domain){
	var frame = window.frames[0].document;
	var as = frame.getElementsByTagName("a");//variable called as because it is plural of a
	for(i=0;i<as.length;i++){
		/*///////////////////////////////////////////////
		//	We need to format parts of the href. Remove the domain, 
		//	make sure it's a link to a yspage and not an external site
		*/
		var href = as[i].getAttribute("href");
		if(href){//If there is a href
			href = href.replace("http://"+domain,"");
			if(href.toLowerCase().indexOf("javascript")>=0){
				//ado nothign
			}
			else if(href.toLowerCase().indexOf("http")>=0 || href.toLowerCase().indexOf("mailto")>=0)
				href = "javascript:;"
			else{
				/* Careful! The href is set so that when clicked it shows popup, and sets the popup button to save, then redirect user */
				href = "javascript:window.parent.selectLink('"+href+"')";
			}
			as[i].setAttribute("href", href);
		}
	}
	
	forms = frame.getElementsByTagName("form");
	for(i=0;i<forms.length;i++){
		forms[i].setAttribute("action", "javascript:;");
	}
}

function selectBrokenLink(pageTitle){
	document.getElementById('brokenMenuTitle').innerHTML = pageTitle;
	showPopup('brokenMenuItemWarning');
}


function selectLink(href){
	document.getElementById("editPageHref").value = href;
	showPopup("confirmSave");
}


/*//////////////////////////
//	Insert image functions
*/
var imageSrc;
var imageId;
var imageAlt;
var imageFieldId;

function printGallery(response){
	document.getElementById('galleryListWrapper').innerHTML = response.responseText;
}

function selectEditImage(fieldId, url){
	imageFieldId = fieldId;
	
	showPopup('selectEditImage');
	ajax(url, printGallery);
}

function promptForImageAlt(imgSrc, imgId, defaultAlt){
	imageSrc = imgSrc;
	imageId = imgId;
	imageAlt = defaultAlt;
	
	document.getElementById('imageAlt').value = defaultAlt;
	showPopup('promptForImageAlt');
}

function insertSelectedEditImage(){
	hidePopup('promptForImageAlt');
	hidePopup('selectEditImage');
	
	imageAlt = document.getElementById("imageAlt").value;
	
	window.frames[0].document.getElementById(imageFieldId).value = imageId+"|"+imageAlt;
	window.frames[0].document.getElementById(imageFieldId+"_image").src = imageSrc;
	window.frames[0].document.getElementById(imageFieldId+"_image").setAttribute("alt", imageAlt);

	document.getElementById('galleryListWrapper').innerHTML = '<img src="adImages/loader.gif" alt="loading" class="loading"/>';	
}