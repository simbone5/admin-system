var currentSelectedParentMenu = null;

function submitSelectedParentMenu(){
	hidePopup("newMenuItemSelectParent");
	document.getElementById("parentMenuId").value = currentSelectedParentMenu;
	showPopup("editMenuItem");
}

function changeMenuItemType(menuItemId, type){
	hideAllMenuItemValues(menuItemId);
	document.getElementById(type+"ValueLabel"+menuItemId).style.display = "block";
	document.getElementById(type+"Value"+menuItemId).style.display = "block";
}

function hideAllMenuItemValues(menuItemId){
	document.getElementById("pageValueLabel"+menuItemId).style.display = "none";
	document.getElementById("pageValue"+menuItemId).style.display = "none";
	document.getElementById("urlValueLabel"+menuItemId).style.display = "none";
	document.getElementById("urlValue"+menuItemId).style.display = "none";
}

function selectDeleteMenuItem(menuItemId){
	document.getElementById("deleteMenuItemId").value = menuItemId;
	showPopup("confirmDeletePopup");
}

function selectParentMenu(parentId){
		
	///////////////////////////////////
	// unhighlight previous selection
	if(currentSelectedParentMenu!=null){
		var previousAnchorId = "menuID_parent_"+currentSelectedParentMenu;
		var previousAnchorTag = document.getElementById(previousAnchorId);
		previousAnchorTag.setAttribute("class", "");
		previousAnchorTag.setAttribute("className", "");
	}
	
	///////////////////////////////////
	// highlight new selection
	var anchorId = "menuID_parent_"+parentId;
	var anchorTag = document.getElementById(anchorId);
	anchorTag.setAttribute("class", "selected");
	anchorTag.setAttribute("className", "selected");
	
	///////////////////////////////////
	// store new selection
	currentSelectedParentMenu=parentId;
}

function selectLinkToPage(menuItemId, pageId){
	
	var input = document.getElementById('hiddenPageValue'+menuItemId);
	
	
	///////////////////////////////////
	// unhighlight previous selection
	if(input.value>0){
		var previousAnchorId = "menuItem"+menuItemId+"_linkToPage"+input.value;
		var previousAnchorTag = document.getElementById(previousAnchorId);
		previousAnchorTag.setAttribute("class", "");
		previousAnchorTag.setAttribute("className", "");
	}
	
	///////////////////////////////////
	// highlight new selection
	var anchorId = "menuItem"+menuItemId+"_linkToPage"+pageId;
	var anchorTag = document.getElementById(anchorId);
	anchorTag.setAttribute("class", "selected");
	anchorTag.setAttribute("className", "selected");
	
	///////////////////////////////////
	// store new selection
	input.value=pageId;
	
}

function saveStructure(){
	///////////////////////////////////
	// Called when the menu structure form is submitted
	// Calls getLis() to place the structure of the <ul> and <li>s into a <textarea>
	
	var ul = document.getElementById("treeRoot");
	var txtArea = document.getElementById("structure");
	
	txtArea.value = "<menu>\n";
	getLis(ul);
	txtArea.value += "</menu>";

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
			var menuId = li.getAttribute("id").replace(/menuID_/, "");
			//pagId = li.firstChild.innerHTML;
			if(childUls.length>=1){
				txtArea.value += '<item menuID="'+menuId+'">\n';
				getLis(childUls[0]);
				txtArea.value += '</item>\n';
			}
			else{
				txtArea.value += '<item menuID="'+menuId+'"/>\n';
			}
		}
	}while(li = li.nextSibling)
}