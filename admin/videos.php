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


get_block_params("videos");


$db = new CDB();
$db->connect();


$videoId = get_param("videoId");
if ($videoId == "")
	$videoId = 0;

$cmd = get_param("cmd");
if ($cmd == "sync")
{
	$h = opendir(VIDEO_DIR . "/upload");
	while ($file = readdir($h))
		if ($file != "." && $file != "..")
		{
			$title = ereg_replace("\.mov$", "", $file);

			$sFile = substr(md5(uniqid(rand())), 1, 10) . "_" . $file;
			$sPic = substr(md5(uniqid(rand())), 1, 10) . "_default.jpg";


			rename(VIDEO_DIR . "/upload/" . $file, VIDEO_DIR . "/" . $sFile);
			copy(VIDEO_DIR . "/thumbs/default.jpg", VIDEO_DIR . "/thumbs/" . $sPic);

			$db->execute(
				"INSERT INTO videos (sFile, sPic, width, height, title) VALUES (" .
				to_sql($sFile, "") . "," .
				to_sql($sPic, "") . "," .
				to_sql(300, "Number") . "," .
				to_sql(300, "Number") . "," .
				to_sql($title, "") . ")"
			);

		}
	closedir($h);
	recursive_remove_directory(VIDEO_DIR . "/upload", true);
	redirect("videos.php?" . get_params());
}

if ($cmd == "video_down" && $videoId > 0)
{
	$orderId = $db->DLookUp("SELECT orderId FROM videos WHERE videoId=" . to_sql($videoId, "Number"));
	$videoId2 = $db->DLookUp("SELECT videoId FROM videos WHERE orderId<" . to_sql($orderId, "Number") . " ORDER BY orderId DESC LIMIT 1");
	if ($videoId2 > 0)
	{
		$orderId2 = $db->DLookUp("SELECT orderId FROM videos WHERE videoId=" . to_sql($videoId2, "Number"));
		$db->execute("UPDATE videos SET orderId=" . $orderId2 . " WHERE videoId=" . to_sql($videoId, "Number"));
		$db->execute("UPDATE videos SET orderId=" . $orderId . " WHERE videoId=" . to_sql($videoId2, "Number"));
	}
}
if ($cmd == "video_up" && $videoId > 0)
{
	$orderId = $db->DLookUp("SELECT orderId FROM videos WHERE videoId=" . to_sql($videoId, "Number"));
	$videoId2 = $db->DLookUp("SELECT videoId FROM videos WHERE orderId>" . to_sql($orderId, "Number") . " ORDER BY orderId ASC LIMIT 1");
	if ($videoId2 > 0)
	{
		$orderId2 = $db->DLookUp("SELECT orderId FROM videos WHERE videoId=" . to_sql($videoId2, "Number"));
		$db->execute("UPDATE videos SET orderId=" . $orderId2 . " WHERE videoId=" . to_sql($videoId, "Number"));
		$db->execute("UPDATE videos SET orderId=" . $orderId . " WHERE videoId=" . to_sql($videoId2, "Number"));
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

$page = new CLoggedPage("", "../html/admin/videos.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));


$videos = new CVideoGrid($db, "videos", null);
$videos->m_sqlcount = "select count(*) as cnt from videos";
$videos->m_sql = "SELECT videoId, title, sPic, width, height, link FROM videos";
$videos->m_fields["videoId"] = Array ("videoId", null, "");
$videos->m_fields["orderId"] = Array ("orderId", null);
$videos->m_fields["sPic"] = Array ("sPic", null, "");
$videos->m_fields["title"] = Array ("title", null, "");
$videos->m_fields["width"] = Array ("width", null, "");
$videos->m_fields["height"] = Array ("height", null, "");
$videos->m_fields["link"] = Array ("link", null, "");
$videos->m_itemBlocks["bLink"] = 0;
$videos->m_nPerPage = 30;
$videos->m_nCells = 3;
$videos->m_sort = "orderId";
$videos->m_dir = "desc";
$page->add($videos);


//echo "<hr>" . $videos->m_sql . "<hr>";

$page->init();
$page->action();
$page->parse(null);


?>