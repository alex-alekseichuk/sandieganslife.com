<?

//
// special script
// it outputs the content of the mag page as JPEG
//

include_once("include/core.php");
include_once("include/db.php");


$pageId = get_param("pageId", 0);
if ($pageId == 0)
	exit;

$db = new CDB();
$db->connect();

if ($pageId == -1)
	$sFile = "0.jpg";
else
	$sFile = $db->DLookUp("SELECT concat(magId,'/',sFile) AS sFile FROM pages WHERE pageId=" . to_sql($pageId, "Number"));

//echo $sFile;
//echo PAGES_DIR . "/" . $sFile;

if ($sFile !== 0)
{
	if (preg_match("/\.swf$/i", $sFile))
		header('Content-type: application/octet-stream');
	else
		header('Content-type: image/jpeg');

	$f = fopen(PAGES_DIR . "/" . $sFile, "rb");
	while ($buf = fread($f, 4096))
	{
		print $buf;
	}
	fclose($f);
}


?>