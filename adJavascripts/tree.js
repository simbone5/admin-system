
var backup = null;

function move(dir, id, incChildren){	
	//////////////////////////////
	// Controlling function. Calls appropriate direction function and handles cleanup
	
	setBackup();
	dir(id, incChildren);
	cleanUp();
	
	alertEl = document.getElementById('alert')
	if(alertEl)
		alertEl.innerHTML = "";
	
}

function up(id){
	var el = document.getElementById(id);
	if(!el)
		return;
	
	//////////////////////////////
	// basic - prevSibling is in same ul
	var prevSib = getPreviousSibling(el);
	if(prevSib){
		el.parentNode.insertBefore(el, prevSib);
		return;
	}
	
	//////////////////////////////
	// advanced - no prevSibling in same ul, so make it a child of its parent's previousSibling
	var newParentParent = getPreviousSibling(el.parentNode.parentNode);
	if(newParentParent){
		var newParent = createUl(newParentParent);
		newParent.appendChild(el);
	}
}

function down(id){
	var el = document.getElementById(id);
	if(!el)
		return;
	
	//////////////////////////////
	// basic - nextSibling is in same ul
	var nextSib = getNextSibling(el);
	var newNextSib = getNextSibling(nextSib);
	if(nextSib && newNextSib){ //insert el before sibling
		el.parentNode.insertBefore(el, newNextSib);
		return;
	}
	else if(nextSib && !newNextSib){// no sibling to insertBefore, so we move to bottom
		el.parentNode.appendChild(el);
		return;
	}
	
	//////////////////////////////
	// advanced - no nextSibling in same ul, so make it a child of its parent's nextSibling
	var newParentParent = getNextSibling(el.parentNode.parentNode);
	if(newParentParent){
		var newParent = createUl(newParentParent);
		
		//////////////////////////////
		// el needs to go at the top of newParent's children
		var newSiblings = newParent.getElementsByTagName("li");
		if(newSiblings.length>0){
			newParent.insertBefore(el, newSiblings[0]);
		}
		else{
			newParent.appendChild(el);
		}
	}
}

function right(id, incChildren){
	var el = document.getElementById(id);
	if(!el)
		return;
	
	
	//////////////////////////////
	// get the new parent parent (li) for el. Return false if there is no new parent i.e. el is at top
	var newParentParent = getPreviousSibling(el);
	if(newParentParent==null)
		return;
	
	var newChildren = Array();
	newChildren.push(el);
	
	//////////////////////////////
	// move el's children to become siblings of el
	if(!incChildren){
		var elChildren = el.getElementsByTagName("li");
		if(elChildren.length>0){
			for(i in elChildren){
				if(elChildren[i].tagName && elChildren[i].tagName.toLowerCase()=="li" && elChildren[i].parentNode.parentNode==el)
					newChildren.push(elChildren[i]);
			}
		}
	}
	
	//////////////////////////////
	// newChildren should include the selected el, plus any of el's children.
	// They now become children of newParent.
	var newParent = createUl(newParentParent);
	for(i in newChildren){
		newParent.appendChild(newChildren[i]);
	}
}
function left(id, incChildren){	
	var el = document.getElementById(id);
	if(!el)
		return;
		
	//////////////////////////////
	// get and check el's parent. Can't move left if already fully left.
	var newParent = el.parentNode.parentNode.parentNode;
	if(!newParent.tagName || newParent.tagName.toLowerCase()!="ul")
		return;
	
	//////////////////////////////
	// get the siblings of el, and turn them into children of el
	if(!incChildren){
		var siblings = getNextSiblings(el);
		if(siblings.length>0){
			var newSublist = document.createElement("ul");
			for(sibling in siblings){
				newSublist.appendChild(siblings[sibling]);
			}
			el.appendChild(newSublist);
		}
	}
		
	//////////////////////////////
	// move el to it's parent (i.e. move it to the left)
	if(getNextSibling(el)){
		newSibling = getNextSibling(el.parentNode.parentNode);
	}
	else{
		newSibling = el.parentNode.parentNode.nextSibling;
	}
		newParent.insertBefore(el, newSibling);
}

function getPreviousSibling(el){
	while(el && el.previousSibling){
		el = el.previousSibling;
		if(el.tagName && el.tagName.toLowerCase()=="li")
			return el;
	}
	return null;
}

function getNextSiblings(el){
	var siblings = new Array();
	
	while(el && el.nextSibling){
		el = el.nextSibling;
		if(el.tagName && el.tagName.toLowerCase()=="li")
			siblings.push(el);
	}
	return siblings;
}

function getNextSibling(el){
	while(el && el.nextSibling){
		el = el.nextSibling;
		if(el.tagName && el.tagName.toLowerCase()=="li")
			return el;
	}
	return null;
}

function createUl(newParentParent){
	//////////////////////////////
	// ensure that the newParentParent (which is an li) has a 
	// ul inside it. The ul is the newParent
	if(newParentParent.getElementsByTagName("ul").length>0){
		var newParent = newParentParent.getElementsByTagName("ul")[0];
	}
	else{
		var newParent = document.createElement("ul");
		newParentParent.appendChild(newParent);
	}
	return newParent;
}

function cleanUp(){
	var root = document.getElementById("treeRoot");
	if(!root)
		return;
		
	var uls = root.getElementsByTagName("ul");
	for(i in uls){
		var ul = uls[i];
		if(ul && ul.tagName && ul.getElementsByTagName("li").length==0){
			ul.parentNode.removeChild(ul);
		}
	}
	if(maxLevels>0)
		removeButtons(root, 1);
}	
	
function removeButtons(ul, level){
	///////////////////////////////////
	// Recursive function
	
	var li = ul.firstChild;
	do{
		if(li.nodeType==1){
			var liID = li.getAttribute("id");
			var toDisable = new Array();
			//if(level>=maxLevels)
				toDisable.push(liID+"_add");
			
			if(level>=maxLevels || li.getElementsByTagName("ul").length+level>=maxLevels)
				toDisable.push(liID+"_right");
				
			if(level==1)
				toDisable.push(liID+"_left");
				
			disableButtons(li, toDisable);
			
			var childUls = li.getElementsByTagName("ul");
			if(childUls.length>=1){
				removeButtons(childUls[0], level+1);
			}
		}
	}while(li = li.nextSibling)

}

function disableButtons(li, toDisable){
	///////////////////////////////////
	// toDisable holds the appending string of the li ID for the anchors to be disabled
	
	
	var anchors = li.getElementsByTagName("a");
	for(var i=0;i<anchors.length;i++){
		var anchorID = anchors[i].getAttribute("id")
		if(in_array(anchorID, toDisable))
			anchors[i].style.visibility = 'hidden';
		else
			anchors[i].style.visibility = 'visible';
	}
}

function in_array(needle, haystack, argStrict) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: true
 
    var found = false, key, strict = !!argStrict;
 
    for (key in haystack) {
        if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
            found = true;
            break;
        }
    }
 
    return found;
}

function setBackup(){
	root = document.getElementById("treeRoot");
	backup = root.innerHTML;
	
	var undoBt = document.getElementById("undoButton");
	undoBt.disabled = false;
	undoBt.setAttribute("class", "button");
	undoBt.setAttribute("className", "button");
}

function restoreBackup(){
	root = document.getElementById("treeRoot");
	root.innerHTML = backup;
	
	var undoBt = document.getElementById("undoButton");
	undoBt.disabled = true;
	undoBt.setAttribute("class", "button disabled");
	undoBt.setAttribute("className", "button disabled");
}