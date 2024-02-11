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


$magId = get_param("magId", 0);
if (0 == $magId)
	ret_error();
$sTitle = $db->DLookUp("SELECT title FROM mags WHERE magId=" . to_sql($magId, "Number"));
if (0 === $sTitle)
	ret_error();

$s = "<vmag status=\"ok\" title=\"" . $sTitle . "\">";

$db->query("SELECT pageId,width,height FROM pages WHERE magId=" . to_sql($magId, "Number") . " ORDER BY nOrder");
while ($row = $db->fetch_row())
{
	$s .= "<page" .
		" pageId=\"" . $row["pageId"] . "\"" .
		" width=\"" . $row["width"] . "\"" .
		" height=\"" . $row["height"] . "\"" .
		" />";
}

$s .= "</vmag>";

print $s;

function ret_error()
{
	print "<vmag status=\"error\" />";
	exit;
}




?>