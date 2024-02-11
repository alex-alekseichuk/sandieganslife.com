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

get_block_params("album");




$db = new CDB();
$db->connect();

$albumId = get_param("albumId");
if ($albumId == "")
	$albumId = 0;





$g_im = new Image();

function uploadPic($pathPic, $title)
{
	global $albumId;
	global $db;
	global $g_im;

	$sFile = $albumId . "_" . date("YmdHis") . "_" . rand(0,1000) . ".jpg";

	if ($g_im->loadImage($pathPic))
	{
		$w = $g_im->getWidth();
		$h = $g_im->getHeight();
			
//		$g_im->resizeCropped(PIC_REAL_X, PIC_REAL_Y, "", 0);
		$g_im->saveImage(PICS_DIR . "/" . $albumId . "/" . $sFile, IMAGE_QUALITY);

		$g_im->resizeCropped(PIC_SMALL_X, PIC_SMALL_Y, "", 0);
		$g_im->saveImage(PICS_DIR . "/" . $albumId . "/small/" . $sFile, IMAGE_QUALITY);
	}

	$db->execute("INSERT INTO pics (albumId, title, sFile) VALUES (" .
		to_sql($albumId, "Number") . "," .
		to_sql($title, "") . "," .
		to_sql($sFile, "") .
	")");
	return $db->get_insert_id();
}





$cmd = get_param("cmd");
$picId = get_param("picId");
if ($cmd == "pic_delete" && $picId > 0)
{
	$sFile = $db->DLookUp("SELECT sFile FROM pics WHERE picId=" . to_sql($picId, "Number"));
	unlink(PICS_DIR . "/" . $albumId . "/" . $sFile);
	unlink(PICS_DIR . "/" . $albumId . "/small/" . $sFile);
	$db->execute("DELETE FROM pics WHERE picId=" . to_sql($picId, "Number"));
	redirect("album.php?" . get_params());
}
if ($cmd == "pic_rename" && $picId > 0)
{
	$title = get_param("title");
	$db->execute("UPDATE pics SET title=" . to_sql($title, "") . " WHERE picId=" . to_sql($picId, "Number"));
	redirect("album.php?" . get_params());
}
if ($cmd == "sync" && $albumId > 0)
{
	$h = opendir(PICS_DIR . "/" . $albumId . "/upload");
	while ($file = readdir($h))
		if ($file != "." && $file != "..")
		{
			$title = ereg_replace("\.((jpg)|(jpeg))$", "", $file);
			$picId = uploadPic(PICS_DIR . "/" . $albumId . "/upload/" . $file, $title);
		}
	closedir($h);
//	recursive_remove_directory(PICS_DIR . "/" . $albumId . "/upload", true);
	redirect("album.php?" . get_params());
}



class CPicForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CPicForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function validate()
	{
		global $HTTP_POST_FILES;
		$name = "image";
		$ret = "";
		$exts = Array("jpg", "jpeg");
		if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
		{
			if ($HTTP_POST_FILES[$name]["size"] > 10485760)
				return "Too large size of uploaded file";
		
			$sP = "";
			foreach ($exts as $ext)
			{
				if ($sP != "") $sP .= "|";
				$sP .= "(\." . $ext . ")";
			}
			$sP = "/(" . $sP . ")$/i";

			if (preg_match($sP, $HTTP_POST_FILES[$name]['name']) != 1)
			{
				return "Incorrect file type";
			}
		} else {
			return "No image";
		}

		return "";
	}
	
	function upload()
	{
		global $HTTP_POST_FILES;
		global $albumId;
		$name = "image";
		$cant = 0;
		if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
		{

			$title = get_param("title");
			$picId = uploadPic($HTTP_POST_FILES[$name]['tmp_name'], $title);

			return true;
		}
	}

	function action()
	{
		$cmd = get_param("cmd", "");

		if ($cmd == "pic_insert")
		{
			$this->sMessage = $this->validate();
			if ($this->sMessage != "")
				return;

			if (! $this->upload())
				$this->sMessage = "Can't process uploaded picture";
			else
				redirect("album.php?" . get_params());
		}
	}

}

class CAlbumRecord extends CHtmlRecord
{

	function customValidate($cmd)
	{
		global $toId;
		global $userId;

	}

	function customAction($cmd)
	{
		global $albumId;

		if ($cmd == $this->m_name . "_delete")
		{
			recursive_remove_directory(PICS_DIR . "/" . $albumId);
			$this->m_db->execute("DELETE FROM pics WHERE albumId=" . to_sql($albumId, "Number"));
		}

		if ($cmd == $this->m_name . "_insert")
		{
			$albumId = $this->m_id;
			mkdir(PICS_DIR . "/" . $albumId);
			mkdir(PICS_DIR . "/" . $albumId . "/small");
			mkdir(PICS_DIR . "/" . $albumId . "/upload");
			$this->m_db->execute("UPDATE albums SET orderId=" . to_sql($albumId, "Number") . " WHERE albumId=" . to_sql($albumId, "Number"));
		}

		return "";
	}


}




$page = new CLoggedPage("", "../html/admin/album.html");
$page->m_html->setvar("albumId", $albumId);
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$album = new CAlbumRecord($db, "album", null, "albums", "FROM albums WHERE albumId=", "albums.php?");
$album->m_fields["title"] = Array ("title" => "Title", "value" => "", "min" => 1, "max" => 255);
$page->add($album);


if ($albumId > 0)
{
	$fpic = new CPicForm($db, "pic", null);
	$page->add($fpic);

	$pics = new CHtmlGrid($db, "pics", null);
	$pics->m_sqlcount = "select count(*) as cnt from pics where albumId=" . to_sql($albumId, "Number");
	$pics->m_sql = "SELECT picId, title, sFile FROM pics WHERE albumId=" . to_sql($albumId, "Number");
	$pics->m_fields["picId"] = Array ("picId", null, "");
	$pics->m_fields["sFile"] = Array ("sFile", null, "");
	$pics->m_fields["title"] = Array ("title", null, "");
	$pics->m_nPerPage = 20;
	$pics->m_nCells = 4;
	$page->add($pics);
}

$page->init();
$page->action();
$page->parse(null);



?>