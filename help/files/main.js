
// universal JS centered window-opening function 
function windowOpen(url, winName, width, height, peripherals)
{
	var screenW = screen.width;
	var screenH	= screen.height;

	// adjust for window sizes that are larger than the user's screen:	
	if (width > (screenW - 50)) 	width = screenW - 50;
	if (height > (screenH - 50))	height = screenH - 50;

	// determine x,y for centered window
	var winLeft	= ((screenW - width) / 2) - 16;
	var winTop	= (screenH - height) / 2;

	// open it (peripherals may include resizable, scrollbars, menubar, toolbar, etc)
	var win = window.open(url, winName, "width=" + width + ",height=" + height + ",left=" + winLeft + ",top=" + winTop + (peripherals ? "," + peripherals : ""));
	win.focus();
	
	return false;
}

function mainAlert(){
	var items = mainAlert.arguments.length;
	
	alertName = mainAlert.arguments[0];
	
	alertStr = adminAlerts[alertName];
	for (i = 0; i < items;i++){
		findStr = '';
		repStr = '';
		if(i>0 && i%2==0){
			findStr = mainAlert.arguments[i-1];
			repStr = mainAlert.arguments[i];

			var newRegExp = new RegExp(findStr,"g");
			alertStr = alertStr.replace(newRegExp,repStr);
		}
	}
	
	alert(alertStr);
}

function mainChangeSlideshow(){
	//document.mainMenu.ss.value = document.mainMenu.changeShow.options[document.mainMenu.changeShow.selectedIndex].value;
	document.mainMenu.ss.value = document.getElementById('changeShow').options[document.getElementById('changeShow').selectedIndex].value;

	var perm = parseInt(document.mainMenu.perm.value);
	var acctype = parseInt(document.mainMenu.acctype.value);
	
	if(acctype==3&&perm==1){
		cururl = document.location.toString();

		if(cururl.indexOf('slideshowsTM.php') <= 0){		
			document.mainMenu.action = 'slideshowsTM.php';
			document.mainMenu.submit();
		}else{
			editScene(document.mainMenu.ss.value);
		}
	}else{
		document.mainMenu.action = 'slideshowTM.php';
		document.mainMenu.submit();
	}
}

function switchLanguage(langId){
	document.langForm.languageId.value=langId;
	document.langForm.submit();
}

function setCookie (name,value,expires,path,domain,secure) {
	document.cookie = name + "=" + escape (value) +
					((expires) ? "; expires=" + expires.toGMTString() : "") +
					((path) ? "; path=" + path : "") +
					((domain) ? "; domain=" + domain : "") +
					((secure) ? "; secure" : "");
}

function getCookieVal (offset){
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
		endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

function getCookie (name){
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen){
		var j = i + alen;
		
		if (document.cookie.substring(i, j) == arg)
			return getCookieVal (j);
			
		i = document.cookie.indexOf(" ", i) + 1;
		
		if (i == 0) break; 
	}
	return null;
}

yVal   = "esAHc9ANyahS30fiLAc00";
setCookie("y", yVal, null, '/', '.oddcast.com');

yVal = getCookie("y");

if ( yVal == null ){
	alert("Your browser settings prevent the use of cookies. Cookies are required in order to proceed. Please enable cookies in your browser's 'Options' and try again.");
}




// this function adds the onmouseover and onmouseout events to the navigation
// it also handles replication of the action/pagination bar below the content list

function activateNav() 
{
	if (document.getElementById('tblNav'))
	{
		var cells = document.getElementById('tblNav').getElementsByTagName('td');
		
		for (c=0; c < cells.length; c++)
		{
			if (cells[c].getElementsByTagName('a')[0])
			{
				cells[c].onmouseover = new Function("this.getElementsByTagName('a')[0].style.color='YELLOW';");
				cells[c].onmouseout  = new Function("this.getElementsByTagName('a')[0].style.color='#FFFFFF';");
			}
		}
	}
	
	if (document.getElementById('tblNav2'))
	{
		var cells = document.getElementById('tblNav2').getElementsByTagName('td');
		
		for (c=0; c < cells.length; c++)
		{
			if (cells[c].getElementsByTagName('a')[0])
			{
				cells[c].onmouseover = new Function("this.getElementsByTagName('a')[0].style.color='YELLOW'; document.getElementById('tblNav2').style.visibility='visible';");
				cells[c].onmouseout  = new Function("this.getElementsByTagName('a')[0].style.color='#FFFFFF'; document.getElementById('tblNav2').style.visibility='hidden';");
			}
		}
	}
	

	if (document.getElementById('tblNav2'))
		document.getElementById('tblNav2').style.visibility = 'hidden';
	
	duplicateActionsNav();
}


function duplicateActionsNav() 
{
	if (document.getElementById('spanActionsNavigation') && document.getElementById('spanActionsNavigationRepeat'))
		document.getElementById('spanActionsNavigationRepeat').innerHTML = document.getElementById('spanActionsNavigation').innerHTML
}




// String Truncation Code

function truncate(text,chars) {

	if (text.length <= chars) {
		
		return text; 
	
	} else {
	
		var newText = text.substring(0,chars);
		
		var breakSpot = /[ \.\?\!\;\,\:]/;
		
		// make sure there is at least one breakspot, if not return as is plus elipses
		if (newText.match(breakSpot) == null) {
			newText += "&#0133;";
			return newText;
		}

		// find the first break character from the end
		while (newText.charAt(newText.length-1).match(breakSpot) == null) {
			newText = newText.substring(0,newText.length-1);
		}
		
		// find the first real character from the new end
		while (newText.charAt(newText.length-1).match(breakSpot) != null) {
			newText = newText.substring(0,newText.length-1);
		}

		newText += "&#0133;";
		return newText;
	}
}

