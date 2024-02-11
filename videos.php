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


get_block_params("videos");


$db = new CDB();
$db->connect();


$videoId = get_param("videoId", 0);
if ($videoId == 0)
	$videoId = $db->DLookUp("SELECT videoId from videos ORDER BY videoId LIMIT 1");

$oVideo = false;
if ($videoId != 0)
{
	$a = $db->queryAll("SELECT title,link,width,height,sFile from videos WHERE videoId=" . to_sql($videoId, "Number"));
	if (isset($a[0]))
		$oVideo = $a[0];
}

class CVideoPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $oVideo;
		global $videoId;

		if ($videoId > 0 && get_session("_userId") != "")
		{
			$html->setvar("title", $oVideo["title"]);
			$html->setvar("sFile", $oVideo["sFile"]);
			$html->setvar("link", $oVideo["link"]);
			if ($oVideo["link"] == "")
			{
				$html->setvar("width", $oVideo["width"] + 20);
				$html->setvar("height", $oVideo["height"] + 20);
				$html->parse("our");
			} else {
				$html->setvar("width", $oVideo["width"]);
				$html->setvar("height", $oVideo["height"]);
				$html->parse("youtube");
			}
			$html->parse("bVideo");
		} else {
			$html->setvar("title", "");
		}

		parent::parseBlock($html);
	}
}


class CVideoGrid extends CHtmlGrid
{
	function onItem()
	{
		if ($this->m_fields["link"][2] == "")
		{
			$this->m_itemBlocks["bLink"] = 0;
		} else {
			$this->m_itemBlocks["bLink"] = 1;
		}

		
	}	
}

$page = new CVideoPage("", "html/videos.html");
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));


$videos = new CVideoGrid($db, "videos", null);
$videos->m_sqlcount = "select count(*) as cnt from videos";
$videos->m_sql = "SELECT videoId, title, sPic, link FROM videos";
$videos->m_fields["videoId"] = Array ("videoId", null, "");
$videos->m_fields["orderId"] = Array ("orderId", null);
$videos->m_fields["sPic"] = Array ("sPic", null, "");
$videos->m_fields["title"] = Array ("title", null, "");
$videos->m_fields["link"] = Array ("link", null, "");
$videos->m_itemBlocks["bLink"] = 0;
if (get_session("_userId") == "")
{
	$videos->m_itemBlocks["bVideo"] = 0;
	$videos->m_itemBlocks["bLogin"] = 1;
} else {
	$videos->m_itemBlocks["bVideo"] = 1;
	$videos->m_itemBlocks["bLogin"] = 0;
}
$videos->m_nPerPage = 10;
$videos->m_nCells = 1;
$videos->m_sort = "orderId";
$videos->m_dir = "desc";
$page->add($videos);

//echo "<hr>" . $videos->m_sql . "<hr>";

$page->init();
$page->action();
$page->parse(null);


?>