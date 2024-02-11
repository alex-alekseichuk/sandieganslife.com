<?
include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/lang.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/record.php");
include_once("../include/image.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("mags");

$db = new CDB();
$db->connect();


$page = new CLoggedPage("", "../html/admin/mags.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$mags = new CHtmlGrid($db, "mags", null);
$mags->m_sqlcount = "select count(*) as cnt from mags";
$mags->m_sql = "select m.magId,m.title,count(p.pageId) as pages from mags as m LEFT JOIN pages AS p ON m.magId=p.magId GROUP BY m.magId,m.title";
$mags->m_fields["magId"] = Array ("magId", null, "");
$mags->m_fields["title"] = Array ("title", null, "");
$mags->m_fields["pages"] = Array ("pages", null, "");
$page->add($mags);


$page->init();
$page->action();
$page->parse(null);



?>