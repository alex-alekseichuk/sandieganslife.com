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

get_block_params("video");




$db = new CDB();
$db->connect();

$videoId = get_param("videoId", 0);




class CVideoRecord extends CHtmlRecord
{

	function customValidate($cmd)
	{
		global $HTTP_POST_FILES;

		if ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update")
		{
			if (
				$this->m_fields["link"]["value"] == "" && 
				! (
					(isset($HTTP_POST_FILES["sFile"]) && is_uploaded_file($HTTP_POST_FILES["sFile"]["tmp_name"]))
					||
					$this->m_fields["sFile"]["value"] != ""
				)
			)
				return "You should upload a movie or provide the link on youtube.com";
		}

		return "";
	}


	function parseBlock(&$html)
	{
		if ($this->m_id != 0)
		{
			$html->setvar("sFile", $this->m_fields["sFile"]["value"]);
			$html->setvar("sPic", $this->m_fields["sPic"]["value"]);
			$html->setvar("link", $this->m_fields["link"]["value"]);
			if ($this->m_fields["link"]["value"] == "")
			{
				$html->setvar("width", $this->m_fields["width"]["value"] + 20);
				$html->setvar("height", $this->m_fields["height"]["value"] + 20);
				$html->parse("our");
			} else {
				$html->setvar("width", $this->m_fields["width"]["value"]);
				$html->setvar("height", $this->m_fields["height"]["value"]);
				$html->parse("youtube");
			}
			
			$html->parse("viewer");
		}

		parent::parseBlock($html);
	}



}




$page = new CLoggedPage("", "../html/admin/video.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$video = new CVideoRecord($db, "video", null, "videos", "FROM videos WHERE videoId=", "videos.php?");
$video->m_fields["title"] = Array ("title" => "Title", "value" => "", "min" => 1, "max" => 255);
$video->m_fields["link"] = Array ("title" => "Link", "value" => "", "min" => 1, "max" => 255, "optional" => 1);
$video->m_fields["width"] = Array ("type"=>"int", "title" => "Width", "value" => 100, "min" => 100, "max" => 500);
$video->m_fields["height"] = Array ("type"=>"int", "title" => "Height", "value" => 100, "min" => 100, "max" => 500);
$video->m_fields["sFile"] = Array ("type"=>"file", "title" => "Video", "value" => "", "dir" => VIDEO_DIR, "exts" => "mov", "max" => 10*1024*1024*1024, "optional"=>1);
$video->m_fields["sPic"] = Array ("type"=>"file", "title" => "Thumb.", "value" => "", "dir" => VIDEO_DIR."/thumbs", "exts" => "jpg|jpeg", "max" => 1024*1024*1024);

if ($videoId != 0)
{
	$video->m_fields["sPic"]["optional"] = 1;
}

$page->add($video);



$page->init();
$page->action();
$page->parse(null);



?>