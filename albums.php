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

get_block_params("albums");

$db = new CDB();
$db->connect();

$page = new CHtmlBlock("", "html/albums.html");
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));


$albums = new CHtmlGrid($db, "albums", null);
$albums->m_sqlcount = "SELECT count(*) as cnt FROM albums";
$albums->m_sql = "SELECT a.albumId,a.title,count(p.picId) as pics,p.sFile,a.orderId" .
	" FROM albums AS a LEFT JOIN pics AS p ON a.albumId=p.albumId GROUP BY a.albumId,a.title,a.orderId";
$albums->m_fields["albumId"] = Array ("albumId", null);
$albums->m_fields["orderId"] = Array ("orderId", null);
$albums->m_fields["title"] = Array ("title", null);
$albums->m_fields["pics"] = Array ("pics", null);
$albums->m_fields["sFile"] = Array ("sFile", null);
$albums->m_nPerPage = 30;
$albums->m_nCells = 5;
$albums->m_sort = "orderId";
$albums->m_dir = "desc";
$page->add($albums);

//echo "<hr>" . $albums->m_sql . "<hr>";

$page->init();
$page->action();
$page->parse(null);


?>