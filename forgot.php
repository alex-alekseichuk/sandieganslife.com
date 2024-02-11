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



class CLoginForm extends CHtmlBlock
{
	var $m_db;
	var $sMessage = "";
	var $email = "";
	function CLoginForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		global $g_messages;

		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");
		if ($cmd == "login")
		{
			$this->sMessage = $g_messages["no_email"];

			$email = get_param("email", "");
			$passwd = $this->m_db->DLookUp("SELECT passwd FROM users WHERE email=" . to_sql($email, ""));
			if ($passwd)
			{
				$message = "There is your account on www.SanDiegansLife.com site<br />" .
					"Email: " . $email .
					"Password: " . $passwd;
				send_email($email, FROM_EMAIL, "www.SanDiegansLife.com password", $message);

				header("Location: mes.php?mes=passwd_sent\n");
				exit;
			}

		}

	}


	function parseBlock(&$html)
	{
		$html->setvar("email", get_param("email", ""));

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parse($this->m_name . "_bMessage");
		}
		parent::parseBlock($html);
	}


}

$page = new CHtmlBlock("", "html/forgot.html");
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));

$page->add(new CLoginForm($db, "fLogin", null));



$page->init();
$page->action();
$page->parse(null);


?>