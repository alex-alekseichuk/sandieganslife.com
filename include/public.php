<?


//	blocks params
$g_params = Array(
	"albums" => Array("albumsOffset", "albumsSort", "albumsDir"),
	"album" => Array("albumId")
);

//	blocks dependencies
$g_depends = Array(
	"album" => Array("albums")
);




// only logged in
class CLoggedPage extends CHtmlBlock
{
	var $m_db;

	function CLoggedPage($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function init()
	{
		if (get_session("_userId") == "")
		{
			header("Location: login.php?mes=login&" . get_params() . "\n");
			exit;
		}

		parent::init();
	}

}



class CCommonHeader extends CHtmlBlock
{
	var $m_db;
	function CCommonHeader($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function parseBlock(&$html)
	{
		global $g_options;

		if (get_session("_userId") != "")
		{
			$userId = get_session("_userId");
			$sWhere = "userId=" . to_sql($userId, "Number");

			$html->setvar("userId", $userId);
			$html->setvar("email", get_session("_email"));

			$html->parse("mainMenu");
		} else {
			$html->parse("loginMenu");
		}


		parent::parseBlock($html);
	}

}



?>