<?
include_once("include/core.php");
include_once("include/db.php");
include_once("include/lang.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/record.php");
include_once("include/common.php");
include_once("include/public.php");

get_block_params("album");

$db = new CDB();
$db->connect();


$albumId = get_param("albumId");
if ($albumId == "")
	$albumId = 0;


$page = new CHtmlBlock("", "html/album.html");
$page->m_html->setvar("albumId", $albumId);
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));


	$pics = new CHtmlGrid($db, "pics", null);
	$pics->m_sqlcount = "select count(*) as cnt from pics where albumId=" . to_sql($albumId, "Number");
	$pics->m_sql = "SELECT picId, title, sFile FROM pics WHERE albumId=" . to_sql($albumId, "Number");
	$pics->m_fields["picId"] = Array ("picId", null, "");
	$pics->m_fields["sFile"] = Array ("sFile", null, "");
	$pics->m_fields["title"] = Array ("title", null, "");
	if (get_session("_userId") == "")
	{
		$pics->m_itemBlocks["bPic"] = 0;
		$pics->m_itemBlocks["bLogin"] = 1;
	} else {
		$pics->m_itemBlocks["bPic"] = 1;
		$pics->m_itemBlocks["bLogin"] = 0;
	}
	$pics->m_nPerPage = 25;
	$pics->m_nCells = 5;
	$page->add($pics);

$page->init();
$page->action();
$page->parse(null);



?>