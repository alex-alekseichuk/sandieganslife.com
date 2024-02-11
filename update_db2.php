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



//$db->execute("create table cats (	catId integer unsigned not null primary key auto_increment,	title varchar(100) not null )");
//$db->execute("alter table addrs add catId integer unsigned not null");
//$db->execute("create index idx_addrs_catId ON addrs (catId)");
//$db->execute("update addrs set catId=1");

$db->execute("insert into cats (title) values ('Main')");



?>