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

//

class CEmail extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	var $m_catId = 0;
	var $m_title = "";
	var $m_addrs = "";

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

		$this->m_catId = get_param("catId", 0);
		if ($this->m_catId == 0)
			$this->m_catId = $this->m_db->DLookUp("SELECT min(catId) FROM cats");
		$this->m_title = $this->m_db->DLookUp("SELECT title FROM cats WHERE catId=" . to_sql($this->m_catId, "Number"));
	}


	function updateCat()
	{
			$emails = get_param("emails", "");
			$emails = str_replace(",", "\n", $emails);
			$emails = str_replace(" ", "\n", $emails);
			$emails = str_replace(";", "\n", $emails);
			$emails = str_replace("\t", "\n", $emails);
			$emails = str_replace("\r", "\n", $emails);
			$emails = str_replace("<", "", $emails);
			$emails = str_replace(">", "", $emails);
			$emails = explode("\n", $emails);
			foreach ($emails as $email)
			{
				if (eregi("^([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-._]?[0-9a-z])*\.[a-z]{2,4})\r?\n?$", $email, $arr))
				{
					$email = $arr[1];
					$email = strtolower($email);

// additional check by DNS
//$email_arr = explode("@" , $email); 
//$host = $email_arr[1]; 
//if (getmxrr($host, $mxhostsarr)) 
//{ 

					if (0 == $this->m_db->DLookUp("SELECT count(*) FROM users WHERE email=" . to_sql($email, "")))
						$this->m_db->execute("INSERT INTO addrs (email, catId) VALUES (" . to_sql($email, "") . "," . to_sql($this->m_catId, "Number") . ")");

//} 

				}
			}
	}

	function action()
	{
		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");

		if ($cmd == "removeCat" && $this->m_catId != 0)
		{
			$this->m_db->execute("DELETE FROM addrs WHERE catId=" . to_sql($this->m_catId, "Number"));
			$this->m_db->execute("DELETE FROM cats WHERE catId=" . to_sql($this->m_catId, "Number"));
			$this->sMessage = "Category " . $this->m_title . " was removed";
		}

		if ($cmd == "update" && $this->m_catId != 0)
		{
			$this->m_db->execute("DELETE FROM addrs WHERE catId=" . to_sql($this->m_catId, "Number"));
			$this->updateCat();
			$this->sMessage = "Category " . $this->m_title . " was updated";
		}

		if ($cmd == "createCat")
		{
			$this->m_title = get_param("title", "");
			if ($this->m_title == "")
			{
				$this->sMessage = "Provide some title for new category";
				$this->m_addrs = get_param("emails", "");
				return;
			}
			$this->m_db->execute("INSERT INTO cats (title) VALUES (" . to_sql($this->m_title, "") . ")");
			$this->m_catId = $this->m_db->get_insert_id();
			$this->updateCat();
			$this->sMessage = "Category " . $this->m_title . " was created";
		}




	}


	function parseBlock(&$html)
	{
		$html->setvar("sMessage", $this->sMessage);
		$html->setvar("nAddr", $this->m_db->DLookUp("SELECT count(*) FROM addrs"));

		$this->m_db->query("SELECT email FROM addrs WHERE catId=" . to_sql($this->m_catId, "Number"));
		$emails = "";
		while($row = $this->m_db->fetch_row())
		{
			$emails .= $row["email"] . "\n";
		}
		$this->m_db->free_result();
		$html->setvar("addrs", $emails);

		$this->m_db->query("select count(a.email) as cnt, c.catId, c.title from cats as c LEFT JOIN addrs as a ON c.catId=a.catId group by c.catId order by c.title");
		$s = "";
		while ($row = $this->m_db->fetch_row())
		{
			$s .= "<option value=\"" . $row["catId"] . "\"" . ($this->m_catId == $row["catId"] ? " SELECTED" : "") . ">" . $row["title"] . " (" . $row["cnt"] . ")</option>";
		}
		$this->m_db->free_result();
		$html->setvar("catIdOptions", $s);

		if ($this->m_addrs != "")
			$html->setvar("addrs", $this->m_addrs);
		
		parent::parseBlock($html);
	}

}



$page = new CEmail($db, "", "../html/admin/email_import.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$page->init();
$page->action();
$page->parse(null);


?>