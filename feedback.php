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

$email = get_param("email", "");
$emailId = get_param("emailId", "0");
$bUser = get_param("user", 0);
if ($email != "" && $emailId != 0)
{
	if (0 == $db->DLookUp("SELECT count(*) FROM opens WHERE email=" . to_sql($email, "") . " AND emailId=" . to_sql($emailId, "Number")))
	{
		$db->execute("INSERT INTO opens (email, emailId) VALUES (" . to_sql($email, "") . "," . to_sql($emailId, "Number") . ")");
		$db->execute("UPDATE emails SET nOpens=nOpens+1 WHERE emailId=" . to_sql($emailId, "Number"));
	}
}


?>