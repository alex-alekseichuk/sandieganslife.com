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

$page = new CHtmlBlock("", "html/register.html");
$page->add(new CCommonHeader($db, "iHeader", "html/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/footer.html"));

$fReg = new CHtmlRecord($db, "user", null, "users", "FROM users WHERE userId=", "reg_done.php?");
$fReg->m_fields["email"] = Array ("title" => "Email", "value" => "", "min" => 5, "max" => 128);
$fReg->m_fields["passwd"] = Array ("title" => "Password", "value" => "", "min" => 5, "max" => 64);
$fReg->m_fields["fName"] = Array ("title" => "First Name", "value" => "", "min" => 2, "max" => 64);
$fReg->m_fields["lName"] = Array ("title" => "Last Name", "value" => "", "min" => 2, "max" => 64);
$fReg->m_fields["cell"] = Array ("title" => "Cell Phone", "value" => "", "min" => 2, "max" => 64);
$fReg->m_fields["birth"] = Array ("title" => "Birthday", "type"=>"date3", "value" => "");
$fReg->m_fields["gender"] = Array ("title" => "Gender", "type"=>"slovRadio", "value" => "", "options" => Array('M', 'F'));
$fReg->m_fields["address"] = Array ("title" => "Address", "value" => "", "min" => 2, "max" => 128);
$fReg->m_fields["city"] = Array ("title" => "City", "value" => "", "min" => 2, "max" => 64);
$fReg->m_fields["iState"] = Array ("title" => "State", "type"=>"iselect", "value" => "", "sql"=>"SELECT stateId, title FROM states ORDER BY stateId", "sqlcheck"=>"SELECT count(*) FROM states WHERE stateId=");
$fReg->m_fields["zip"] = Array ("title" => "ZIP", "value" => "", "min" => 2, "max" => 10);

$page->add($fReg);


$page->init();
$page->action();
$page->parse(null);


?>