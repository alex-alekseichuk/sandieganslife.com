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



$page = new CLoggedPage("", "../html/admin/users.html");
$page->add(new CHtmlBlock("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));


$users = new CHtmlGrid($db, "users", null);
$users->m_sqlcount = "SELECT count(*) as cnt FROM users";
$users->m_sql = "SELECT userId,passwd,email,fName,lName,cell,birth,if(gender='M','Male','Female') as gender, city, s.title as state, zip, address" .
	" FROM users AS u LEFT JOIN states AS s ON u.iState=s.stateId";
$users->m_fields["userId"] = Array ("userId", null);
$users->m_fields["email"] = Array ("email", null);
$users->m_fields["passwd"] = Array ("passwd", null);
$users->m_fields["fName"] = Array ("fName", null);
$users->m_fields["lName"] = Array ("lName", null);
$users->m_fields["cell"] = Array ("cell", null);
$users->m_fields["birth"] = Array ("birth", null);
$users->m_fields["gender"] = Array ("gender", null);
$users->m_fields["city"] = Array ("city", null);
$users->m_fields["state"] = Array ("state", null);
$users->m_fields["zip"] = Array ("zip", null);
$users->m_fields["address"] = Array ("address", null);
$users->m_sort = "userId";
$users->m_dir = "desc";
$page->add($users);


$page->init();
$page->action();
$page->parse(null);

?>
