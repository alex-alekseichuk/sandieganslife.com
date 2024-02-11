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


class CLogin extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CLogin($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function init()
	{
		parent::init();
	}


	function action()
	{
		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");
		if ($cmd == "login")
		{
			$password = get_param("passwd", "");
			if ($password == ADMIN_PASSWD)
			{
				set_session("_admin", "admin");
				header("Location: users.php\n");
				exit;			
			} else {
				$this->sMessage = "Incorrect password";
			}

		}
		if ($cmd == "logout")
		{
			set_session("_admin", "");
			$this->sMessage = "Logged out";
		}
	}


	function parseBlock(&$html)
	{
		$html->setvar("sMessage", $this->sMessage);
		parent::parseBlock($html);
	}




}



$page = new CLogin($db, "", "../html/admin/index.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$page->init();
$page->action();
$page->parse(null);


?>