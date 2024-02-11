<?
include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/lang.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/record.php");
include_once("../include/common.php");
include_once("../include/admin.php");


get_block_params("albums");


$db = new CDB();
$db->connect();

$albumId = get_param("albumId");
if ($albumId == "")
	$albumId = 0;

$cmd = get_param("cmd");
if ($cmd == "album_down" && $albumId > 0)
{
	$orderId = $db->DLookUp("SELECT orderId FROM albums WHERE albumId=" . to_sql($albumId, "Number"));
	$albumId2 = $db->DLookUp("SELECT albumId FROM albums WHERE orderId<" . to_sql($orderId, "Number") . " ORDER BY orderId DESC LIMIT 1");
	if ($albumId2 > 0)
	{
		$orderId2 = $db->DLookUp("SELECT orderId FROM albums WHERE albumId=" . to_sql($albumId2, "Number"));
		$db->execute("UPDATE albums SET orderId=" . $orderId2 . " WHERE albumId=" . to_sql($albumId, "Number"));
		$db->execute("UPDATE albums SET orderId=" . $orderId . " WHERE albumId=" . to_sql($albumId2, "Number"));
	}
}
if ($cmd == "album_up" && $albumId > 0)
{
	$orderId = $db->DLookUp("SELECT orderId FROM albums WHERE albumId=" . to_sql($albumId, "Number"));
	$albumId2 = $db->DLookUp("SELECT albumId FROM albums WHERE orderId>" . to_sql($orderId, "Number") . " ORDER BY orderId ASC LIMIT 1");
	if ($albumId2 > 0)
	{
		$orderId2 = $db->DLookUp("SELECT orderId FROM albums WHERE albumId=" . to_sql($albumId2, "Number"));
		$db->execute("UPDATE albums SET orderId=" . $orderId2 . " WHERE albumId=" . to_sql($albumId, "Number"));
		$db->execute("UPDATE albums SET orderId=" . $orderId . " WHERE albumId=" . to_sql($albumId2, "Number"));
	}
}


$page = new CLoggedPage("", "../html/admin/albums.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));


$albums = new CHtmlGrid($db, "albums", null);
$albums->m_sqlcount = "SELECT count(*) as cnt FROM albums";
$albums->m_sql = "SELECT a.albumId,a.title,count(p.picId) as pics, a.orderId" .
	" FROM albums AS a LEFT JOIN pics AS p ON a.albumId=p.albumId GROUP BY a.albumId,a.title";
$albums->m_fields["albumId"] = Array ("albumId", null);
$albums->m_fields["orderId"] = Array ("orderId", null);
$albums->m_fields["title"] = Array ("title", null);
$albums->m_fields["pics"] = Array ("pics", null);
$albums->m_sort = "orderId";
$albums->m_dir = "desc";
$page->add($albums);

//echo "<hr>" . $albums->m_sql . "<hr>";

$page->init();
$page->action();
$page->parse(null);


?>