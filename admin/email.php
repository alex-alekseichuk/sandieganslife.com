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


class CEmail extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CEmail($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function init()
	{
		if (get_session("_admin") != "admin")
		{
			header("Location: index.php\n");
			exit;			
		}

		parent::init();
	}


	function action()
	{
		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");
		if ($cmd == "send")
		{
			$toUsers = get_param("toUsers", "");
			$toEmails = get_param("toEmails", "");
			$toTest = get_param("toTest", "");
			$emailTest = get_param("emailTest", "");
			$subject = get_param("subject", "");
			$from = get_param("from", "");
			$body = get_param("body", "");
			$catId = get_param("catId", 0);

			//$body = preg_replace("/\r\n/", "<br>", $body);
			//$body = preg_replace("/\n\r/", "<br>", $body);
			//$body = preg_replace("/\r/", "<br>", $body);
			//$body = preg_replace("/\n/", "<br>", $body);

			//if ((! $toUsers) && (! $toEmails))
			//	return;

			$nUsersSent = 0;
			$nEmailsSent = 0;
			$nTestSent = 0;

			if ($toUsers || $toEmails)
			{

				$this->m_db->execute(
					"INSERT INTO emails (subject,sentDate,nSent,nOpens) VALUES (" .
						to_sql($subject, "") . "," .
						"now()," .
						"0," .
						"0" .
					")"
				);
				$emailId = $this->m_db->get_insert_id();


				if ($toUsers)
				{
					$this->m_db->query("SELECT email FROM users WHERE bSubs='Y'");
					while($row = $this->m_db->fetch_row())
					{
						$email = $row["email"];
						$body2 = $body . "<img src=\"" . URL_EMAIL_BACK . "?user=1&emailId=" . $emailId . "&email=" . to_url($email) . "\" border=\"0\" width=\"1\" height=\"1\" />";
						send_email($row["email"], $from, $subject, $body2);
						$nUsersSent++;
					}
					$this->m_db->free_result();
				}

				if ($toEmails)
				{
					if ($catId == 0)
						$this->m_db->query("SELECT email FROM addrs");
					else
						$this->m_db->query("SELECT email FROM addrs WHERE catId=" . to_sql($catId, "Number"));
					while($row = $this->m_db->fetch_row())
					{
						$email = $row["email"];
						$body2 = $body . "<img src=\"" . URL_EMAIL_BACK . "?emailId=" . $emailId . "&email=" . to_url($email) . "\" border=\"0\" width=\"1\" height=\"1\" />";
						send_email($email, $from, $subject, $body2);
						$nEmailsSent++;
					}
					$this->m_db->free_result();
			

				}

				$this->m_db->execute("UPDATE emails SET nSent=" . to_sql($nUsersSent + $nEmailsSent, "Number") . " WHERE emailId=" . to_sql($emailId, "Number"));
			}

			if ($toTest && $emailTest != "")
			{
				if (eregi("^([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-._]?[0-9a-z])*\.[a-z]{2,4})\r?\n?$", $emailTest, $arr))
				{
					$email = $arr[1];
					$body2 = $body; // . "<img src=\"" . URL_EMAIL_BACK . "?emailId=" . $emailId . "&email=" . to_url($email) . "\" border=\"0\" width=\"1\" height=\"1\" />";
					send_email($email, $from, $subject, $body2);
					$nTestSent++;
				}
			}

			$this->sMessage = "The message was sent to " . $nUsersSent . " users, to " . $nEmailsSent . " emails and to " . $nTestSent . " test email from the list";
		}
	}


	function parseBlock(&$html)
	{
		$html->setvar("sMessage", $this->sMessage);
		$html->setvar("from", FROM_NAME . " <" . FROM_EMAIL . ">");
		$html->setvar("nUsers", $this->m_db->DLookUp("SELECT count(*) FROM users WHERE bSubs='Y'"));
		$html->setvar("nEmails", $this->m_db->DLookUp("SELECT count(*) FROM addrs"));

		$this->m_db->query("select count(a.email) as cnt, c.catId, c.title from addrs as a join cats as c on c.catId=a.catId group by c.catId order by c.title");
		$s = "";
		while ($row = $this->m_db->fetch_row())
		{
			$s .= "<option value=\"" . $row["catId"] . "\">" . $row["title"] . " (" . $row["cnt"] . ")</option>";
		}
		$this->m_db->free_result();
		$html->setvar("catIdOptions", $s);

		
		parent::parseBlock($html);
	}

}



$page = new CEmail($db, "", "../html/admin/email.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$page->init();
$page->action();
$page->parse(null);


?>