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


$db = new CDB();
$db->connect();


$page = new CLoggedPage("", "../html/admin/emails.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));


$emails = new CHtmlGrid($db, "emails", null);
$emails->m_sqlcount = "SELECT count(*) as cnt FROM emails";
$emails->m_sql = "SELECT emailId,subject,sentDate,nSent,nOpens,if(nSent>0,(nOpens * 100)/nSent,0) AS nOpensP " .
	" FROM emails";
$emails->m_fields["emailId"] = Array ("emailId", null);
$emails->m_fields["subject"] = Array ("subject", null);
$emails->m_fields["sentDate"] = Array ("sentDate", null);
$emails->m_fields["nSent"] = Array ("nSent", null);
$emails->m_fields["nOpens"] = Array ("nOpens", null);
$emails->m_fields["nOpensP"] = Array ("nOpensP", null);
$emails->m_sort = "sentDate";
$emails->m_dir = "asc";
$page->add($emails);


$page->init();
$page->action();
$page->parse(null);

?>
