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

$unsubscode = get_param("unsubscode", "");
if ($unsubscode == "")
{
	exit;
}

$userId = $db->DLookUp("SELECT userId FROM users WHERE unsubscode=" . to_sql($unsubscode, ""));
if ($userId == 0)
{
	exit;
}

$db->execute("update users set bSubs='N' WHERE userId=" . to_sql($userId, "Number"));

?>

Done.