function openWindow( url, target, width, height, flags )
{
  var w = screen.width;
  var h = screen.height;

  var x = ( w / 2 ) - ( width / 2 );
  var y = ( h / 2 ) - ( height  / 2 );

  window.open( url, target, 'width=' + width + ',height=' + height + ',' + flags );
}

function tabToggle( tabname )
{
  if (curtab) curtab.style.display = 'none';
  curtab = document.getElementById( tabname );
  curtab.style.display = 'block';
}

function limitText(limitField, limitCount, limitNum)
{
	if (limitField.value.length > limitNum)
    {
		limitField.value = limitField.value.substring(0, limitNum);
		limitCount.innerHTML = "0";
	}
    else
    {
		limitCount.innerHTML = limitNum - limitField.value.length;
	}
}
<!--
function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}

var http = createRequestObject();

function sndReq(action) {
    http.open('get', action);
    http.onreadystatechange = handleResponse;
    http.send(null);
}

function handleResponse() {
    if(http.readyState == 4){
        var response = http.responseText;
        var update = new Array();

        if(response.indexOf('|' != -1)) {
            update = response.split('|');
            document.getElementById(update[0]).innerHTML = update[1];
        }
    }
}
//-->
<!--

function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
if(dd.style.display == "none") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}
//-->