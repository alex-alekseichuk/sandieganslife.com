<?

include_once("include/core.php");
include_once("include/db.php");


$db = new CDB();
$db->connect();


$magId = get_param("magId", 0);
if (0 == $magId)
{
	echo "the magazine is not specified";
	exit;
}
$sTitle = $db->DLookUp("SELECT title FROM mags WHERE magId=" . to_sql($magId, "Number"));
if (0 === $sTitle)
{
	echo "no such magazine";
	exit;
}

$domain = getenv("HTTP_HOST") . ROOT_PATH;

/* <param name="FlashVars" value="magId=<?=$magId?>&WEB_ROOT=http://<?=$domain?>/" /> */

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r" />
<title><?=$sTitle?></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" scroll="no" onLoad="OnLoad()">
<!--url's used in the movie-->
<!--text used in the movie-->
<!-- saved from url=(0013)about:internet -->
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100%" height="100%" id="viewer2" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="img/viewer2.swf" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" />
<param name="FlashVars" value="magId=<?=$magId?>&WEB_ROOT=http://<?=$domain?>/" />
<embed src="img/viewer2.swf" loop="false" quality="high" bgcolor="#ffffff" width="100%" height="100%" name="viewer2" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>

<script language="JavaScript">
<!--

function getFlashMovieObject(movieName)
{
  if (window.document[movieName]) 
  {
      return window.document[movieName];
  }
  if (navigator.appName.indexOf("Microsoft Internet")==-1)
  {
    if (document.embeds && document.embeds[movieName])
      return document.embeds[movieName]; 
  }
  else // if (navigator.appName.indexOf("Microsoft Internet")!=-1)
  {
    return document.getElementById(movieName);
  }
}

var g_nTimeout = 1000;
var g_timer = 0;


function OnLoad()
{
	g_timer = setTimeout("setMag();", g_nTimeout);
}
function setMag()
{
	var m = getFlashMovieObject("viewer2");
	m.SetVariable("/:magId", "<?=$magId?>");
	m.SetVariable("/:WEB_ROOT", "http://<?=$domain?>/");
}
//-->
</script>
</body>
</html>