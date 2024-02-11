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


get_block_params("mag");



$db = new CDB();
$db->connect();

$magId = get_param("magId");
if ($magId == "")
	$magId = 0;

$cmd = get_param("cmd");
$pageId = get_param("pageId");
if ($cmd == "page_up" && $pageId > 0)
{
	$nOrder = $db->DLookUp("SELECT nOrder FROM pages WHERE pageId=" . to_sql($pageId, "Number"));
	$pageId2 = $db->DLookUp("SELECT pageId FROM pages WHERE magId=" . to_sql($magId, "Number") . " AND nOrder<" . to_sql($nOrder, "Number") . " ORDER BY nOrder DESC LIMIT 1");
	if ($pageId2 > 0)
	{
		$nOrder2 = $db->DLookUp("SELECT nOrder FROM pages WHERE pageId=" . to_sql($pageId2, "Number"));
		$db->execute("UPDATE pages SET nOrder=" . $nOrder2 . " WHERE pageId=" . to_sql($pageId, "Number"));
		$db->execute("UPDATE pages SET nOrder=" . $nOrder . " WHERE pageId=" . to_sql($pageId2, "Number"));
	}
}
if ($cmd == "page_down" && $pageId > 0)
{
	$nOrder = $db->DLookUp("SELECT nOrder FROM pages WHERE pageId=" . to_sql($pageId, "Number"));
	$pageId2 = $db->DLookUp("SELECT pageId FROM pages WHERE magId=" . to_sql($magId, "Number") . " AND nOrder>" . to_sql($nOrder, "Number") . " ORDER BY nOrder ASC LIMIT 1");
	if ($pageId2 > 0)
	{
		$nOrder2 = $db->DLookUp("SELECT nOrder FROM pages WHERE pageId=" . to_sql($pageId2, "Number"));
		$db->execute("UPDATE pages SET nOrder=" . $nOrder2 . " WHERE pageId=" . to_sql($pageId, "Number"));
		$db->execute("UPDATE pages SET nOrder=" . $nOrder . " WHERE pageId=" . to_sql($pageId2, "Number"));
	}
}
if ($cmd == "page_delete" && $pageId > 0)
{
	$sFile = $db->DLookUp("SELECT concat(magId,'/',sFile) AS sFile FROM pages WHERE pageId=" . to_sql($pageId, "Number"));
	unlink(PAGES_DIR . "/" . $sFile);
	$db->execute("DELETE FROM pages WHERE pageId=" . to_sql($pageId, "Number"));
}



class CImageForm extends CHtmlRecord
{


	function customValidate($cmd)
	{

		if ($cmd == $this->m_name . "_insert")
		{
			$this->m_fields["nOrder"]["value"] = $this->m_db->DLookUp("SELECT if(max(pageId) is null, 1, max(pageId)+1) FROM pages LIMIT 1");
		}
	}

}

class CMagRecord extends CHtmlRecord
{

	function customAction($cmd)
	{
		global $magId;

		if ($cmd == $this->m_name . "_delete")
		{
			$this->m_db->query("SELECT sFile FROM pages WHERE magId=" . to_sql($magId, "Number"));
			while ($row = $this->m_db->fetch_row())
			{
				unlink(PAGES_DIR . "/" . $magId . "/" . $row["sFile"]);
			}
			$this->m_db->free_result();

			recursive_remove_directory(PAGES_DIR . "/" . $magId);
			$this->m_db->execute("DELETE FROM pages WHERE magId=" . to_sql($magId, "Number"));
		}
		if ($cmd == $this->m_name . "_insert")
		{
			mkdir(PAGES_DIR . "/" . $this->m_id);
		}
		return "";
	}


}



$page = new CLoggedPage("", "../html/admin/mag.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$mag = new CMagRecord($db, "mag", null, "mags", "FROM mags WHERE magId=", "mags.php?");
$mag->m_fields["title"] = Array ("title" => "Title", "value" => "", "min" => 1, "max" => 255);
$page->add($mag);


if ($magId > 0)
{

//	$fimage = new CImageForm($db, "image", null);
	$fimage = new CImageForm($db, "image", null, "pages", "FROM pages WHERE pageId=", "mags.php?");
	$fimage->m_fields["width"] = Array ("type"=>"int", "title" => "Width", "value" => 100, "min" => 100, "max" => 10000);
	$fimage->m_fields["height"] = Array ("type"=>"int", "title" => "Height", "value" => 100, "min" => 100, "max" => 10000);
	$fimage->m_fields["sFile"] = Array ("type"=>"file", "title" => "Image", "value" => "", "dir" => PAGES_DIR . "/" . $magId, "exts" => "swf", "max" => 10*1024*1024*1024);
	$fimage->m_fields["magId"] = Array ("type"=>"int", "title" => "magId", "value" => $magId, "nohttp"=>1);
	$fimage->m_fields["nOrder"] = Array ("type"=>"int", "title" => "nOrder", "value" => 0, "nohttp"=>1);
	$page->add($fimage);

	$pages = new CHtmlGrid($db, "pages", null);
	$pages->m_sqlcount = "select count(*) as cnt from pages where magId=" . to_sql($magId, "Number");
	$pages->m_sql = "SELECT pageId, nOrder, concat(width, 'x', height) as size, concat('" . PAGES_WEBDIR . "/',magId,'/',sFile) as pageFile FROM pages WHERE magId=" . to_sql($magId, "Number") . " ORDER BY nOrder,pageId";
	$pages->m_fields["pageId"] = Array ("pageId", null, "");
	$pages->m_fields["pageFile"] = Array ("pageFile", null, "");
	$pages->m_fields["size"] = Array ("size", null, "");
	$page->add($pages);
}


$page->init();
$page->action();
$page->parse(null);



?>