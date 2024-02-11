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



$db->execute("create table mags (magId integer unsigned primary key not null auto_increment,title varchar(255) not null)");
$db->execute("create table pages (pageId integer unsigned primary key not null auto_increment,magId integer unsigned not null,nOrder int not null,sFile varchar(255) not null,width int not null,height int not null,INDEX pages_nOrder_idx (nOrder))");



?>