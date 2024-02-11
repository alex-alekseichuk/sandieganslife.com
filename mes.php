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



$db = new CDB();
$db->connect();


class CMesPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g_messages;

		$mes = get_param("mes", "");
		if ($mes == "passwd_sent")
		{
			$html->setvar("mes", $g_messages["passwd_sent"]);
		}

		parent::parseBlock($html);
	}
}



$page = new CMesPage("", "html/mes.html");
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));


$page->init();
$page->action();
$page->parse(null);


?>