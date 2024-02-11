<?

//
// spec. script
// it outputs flash variables
// there are pages info for spec. magazine
// input param. magId
//
//

include_once("include/core.php");
include_once("include/db.php");



$db = new CDB();
$db->connect();

/*
$magId = get_param("magId", 0);
if (0 == $magId)
	ret_error();
$sTitle = $db->DLookUp("SELECT title FROM mags WHERE magId=" . to_sql($magId, "Number"));
if (0 === $sTitle)
	ret_error();
*/

$sTitle = "";
$magId = 7;

$s = "<VMAG><PAGES>";

$db->query("SELECT pageId,width,height,sFile FROM pages WHERE magId=" . to_sql($magId, "Number") . " ORDER BY nOrder");
while ($row = $db->fetch_row())
{
	$s .= "<P>" .
		"pages/" . $magId . "/" . $row["sFile"] .
		"</P>";
}

$s .= "</PAGES></VMAG>";

header('Content-type: text/xml');
print $s;

function ret_error()
{
	print "<vmag status=\"error\" />";
	exit;
}



?>