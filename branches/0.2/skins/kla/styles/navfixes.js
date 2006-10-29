/*
	In principe werkt het menu in Opera Ok zonder dit script, maar dit script heeft 
	een iets andere CSS die ervoor zorgt dat het menu niet teveel naar rechts staat.
*/
window.onload = function(){
if ((navigator.userAgent.match ("MSIE")) || (navigator.userAgent.match ("Opera"))) {
	var all = getAllMenuItems ();
	
	for (i=0;i < all.length; i++) {
		var node = all[i];
		node.onmouseover=function() {
			this.className+=" over";
		}
		node.onmouseout=function() {
			this.className=this.className.replace(" over", "");
		}
	}
}

/*
	ROT IE
*/
if (navigator.userAgent.match ("MSIE")) {
	/*var navRoot = document.getElementById("nav");
	var l = navRoot.childNodes.length;
	for (i=0;i < l; i++) {
		var node = navRoot.childNodes[i];
		if (node.nodeName == "LI") {
			for (j=0; j< node.childNodes.length; j++) {
				var child = node.childNodes[j];
				if (child.nodeName == "UL") {
					child.className +=" ie";
				}
			}	
		}
	}*/
	
	var all = getAllMenuItems ();

	for (i=0;i < all.length; i++) {
		var node = all[i];
		for (j=0; j< node.childNodes.length; j++) {
			var child = node.childNodes[j];
			if (child.nodeName == "OL") {
				child.className +=" ie";
			}
			
		}
	}
}
}


function getAllMenuItems () {
	var allNodes = new Array ();
	if (document.all&&document.getElementById) {
		var navRoot = document.getElementById("nav");
		var ols = document.getElementsByTagName ("ol");
		var olRoot = ols[0];
		var l = olRoot.childNodes.length;
		for (i=0;i < l; i++) {
			var node = olRoot.childNodes[i];
			if (node.nodeName=="LI") {
				allNodes.push (node);
			}
		}
	}
	return allNodes;
}