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


$db = new CDB();
$db->connect();

//$db->execute("alter table users add bSubs char(1) not null default 'Y'");

//$db->execute("create table albums(albumId integer unsigned primary key not null auto_increment,title varchar(255) not null)");
//$db->execute("create table pics(picId integer unsigned primary key not null auto_increment,albumId integer unsigned not null,title varchar(255) not null default '',sFile varchar(255) not null,INDEX pics_albumId_idx (albumId))");

/*
$db->query("select orderId,albumId FROM albums ORDER BY orderId");
$n=0;
$a = Array();
while($row = $db->fetch_row())
{
	
	$a[$n] = Array(
		"orderId" => $row["orderId"],
		"albumId" => $row["albumId"]
	);
	$n++;
}
$db->free_result();

for ($i=0; $i<$n; $i++)
{
	$db->execute("UPDATE albums SET orderId=" . to_sql($a[$i]["orderId"], "Number") . " WHERE albumId=" . to_sql($a[$n - $i -1]["albumId"], "Number"));
}



$db->execute("alter table videos add orderId int unsigned not null default 0");
$db->execute("CREATE INDEX videos_orderId_idx ON videos(orderId)");



$db->query("select orderId,videoId FROM videos ORDER BY orderId");
$n=0;
$a = Array();
while($row = $db->fetch_row())
{
	
	$a[$n] = Array(
		"orderId" => $row["orderId"],
		"videoId" => $row["videoId"]
	);
	$n++;
}
$db->free_result();

for ($i=0; $i<$n; $i++)
{
	$db->execute("UPDATE videos SET orderId=" . to_sql($a[$i]["orderId"], "Number") . " WHERE videoId=" . to_sql($a[$n-$i-1]["videoId"], "Number"));
}
*/


//$db->execute("UPDATE videos SET orderId=44-videoId+1");
/*
$db->query("select orderId,videoId FROM videos ORDER BY orderId");
while($row = $db->fetch_row())
{
	echo $row["orderId"] . " - " . $row["videoId"] . "<br>";
}
$db->free_result();
*/

$db->query("select orderId,albumId FROM albums ORDER BY orderId");
while($row = $db->fetch_row())
{
	echo $row["orderId"] . " - " . $row["albumId"] . "<br>";
}
$db->free_result();

?>