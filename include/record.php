<?php

//	Record block
//
//	rec_field_title($field, $name) returns the title of the field
//		$field is a ref. to field array
//	rec_fields_to_sql(&$fields) returns the list of pairs like name=value,name2=lvaue2 for sql-update
//	rec_sql_fields(&$fields) returns the list of fields names for sql=insert
//	rec_sql_values(&$fields) returns the list of fields values for sql=insert
//	rec_get_http($db, &$fields, $_id, $form, $_table) get values from HTTP
//	rec_get_db($db, &$fields, $sqlFromWhere) get values from db
//		$sqlFromWhere is a sub-sql like "FROM table WHERE ..."
//	rec_parse_checks($name, $db, &$html, $sql, $mask) parse set of items (checkboxes usually) by bit mask
//		$name is a html block name; there are tags {title} and {value} in the block
//		$sql select should have 2 fields 1-st is id and 2-nd is a title
//	rec_parse_checksOn($name, $db, &$html, $sql, $mask) the same as above but only checked items parsed
//
//
//
//
//	CHtmlRecord($db, $name, $html_path, $table, $sqlFromWhere, $return_page) constructor
//
//	types: text field by defaut; min max optional
//		plain
//		int			min max unique optional
//		float		min max unique optional
//		iselect		sql sqlcheck optional
//			parse the list of options into {$nameOptions}
//		sselect		sql sqlcheck optional
//			parse the list of options into {$nameOptions}
//		ilov		options optional
//			parse the list of options into {$nameOptions}
//		slov		options optional
//			parse the list of options into {$nameOptions}
//		check		value: 0 or 1; optional or should be 1
//			in db it should be 'Y' or 'N'
//			parse {$name} or {$name_no} as ' checked'
//		checks		sql(value,title)
//			value: bit mask of checked checkboxes; optional or should be checked at least one
//			parse all values
//		checksOn	sql(value,title)
//			value: bit mask of checked checkboxes; optional or should be checked at least one
//			parse only checked values by bit mask
//		date3		optional
//			value as 2006-03-11
//			3 http values: nameYear, nameMonth, nameDate
//			parse options for 3 select's: DayOptions, MonthOptions and YearOptions
//		idblookup	sql
//		lovlookup	options
//		file		dir file exts max nocheck optional
//		ilovRadio	options optional
//			parse the set of radio buttons by scheme name_value
//		slovRadio	options optional
//			parse the set of radio buttons by scheme name_value
//
//	more options:
//		nodb,noinsert,noupdate,nocheck,nohttp
//		dbSelect - field in sql select
//
//	example:
//
//	$compose = new CComposeForm($db, "compose", null, "messages", "FROM messages WHERE messageId=", "compose.php?");
//	$compose->m_fields["message"] = Array ("title"=>"Сообщение", "value"=>"", "min"=>0, "max"=>64);
//	$compose->m_fields["userId"] = Array ("type"=>"int", "value"=>$toId, "noupdate"=>1, "nocheck"=>1);
//	$compose->m_fields["fromId"] = Array ("type"=>"int", "value"=>$userId, "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
//	$compose->m_fields["sent"] = Array ("plain" => "now()", "type"=>"plain", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
//	$page->add($compose);
//
//
// TODO
// change (isset($i["title"]) ? $i["title"] : $name) to rec_field_title($i, $name)
//
//
//
//	2006-10-18




// global function useful for record forms


// returns the title of the field
function rec_field_title($field, $name)
{
	if (isset($field["title"]))
	{
		return $field["title"];
	} else {
		return $name;
	}
}



// returns the list of pairs like name=value
// separated by ','
// ready to insert into sql-update
function rec_fields_to_sql(&$fields)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["noupdate"]))
		{
			if ($ret != "") $ret .= ",";
			$t = "";

			if (isset($i["type"]) && (
				$i["type"] == "checks" || 
				$i["type"] == "checksOn" || 
				$i["type"] == "int" || 
				$i["type"] == "float" ||
				$i["type"] == "ilov" ||
				$i["type"] == "ilovRadio" ||
				$i["type"] == "iselect"
				))
				$t = "Number" ;
			if (isset($i["type"]) && $i["type"] == "plain") $t = "Plain";
			if (isset($i["type"]) && $i["type"] == "check") $t = "Check";

			if (isset($i["type"]) && $i["type"] == "plain")
				$v = $i["plain"];
			else
				$v = $i["value"];

			$ret .= $name . "=" . to_sql($v, $t);
		}
	}
	return $ret;
}


// returns the list of fields names
// separated by ','
// ready to insert into sql-insert
function rec_sql_fields(&$fields)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["noinsert"]))
		{
			if ($ret != "") $ret .= ",";
			$ret .= $name;
		}
	}
	return $ret;
}

// returns the list of fields values
// separated by ','
// ready to insert into sql-insert
function rec_sql_values(&$fields)
{
	$ret = "";
	foreach ($fields as $i)
	{
		if (! isset($i["noinsert"]))
		{
			if ($ret != "") $ret .= ",";
			$t = "";

			if (isset($i["type"]) && (
				$i["type"] == "int" || 
				$i["type"] == "float" ||
				$i["type"] == "ilov" ||
				$i["type"] == "ilovRadio" ||
				$i["type"] == "iselect"
				))
				$t = "Number" ;
			if (isset($i["type"]) && $i["type"] == "plain") $t = "Plain";
			if (isset($i["type"]) && $i["type"] == "check") $t = "Check";

			if (isset($i["type"]) && $i["type"] == "plain")
				$v = $i["plain"];
			else
				$v = $i["value"];

			$ret .= to_sql($v, $t);
		}
	}
	return $ret;
}


// get values of fields from http
// $_id id of the this record
// $form is a name of the record form
// $_table the sql table of the record
// returns error strings or ""
function rec_get_http($db, &$fields, $_id, $form, $_table)
{
	global $g_messages;
	global $HTTP_POST_FILES;
	$sRet = "";
	foreach ($fields as $name => $i)
	{
		if (isset($i["type"]) && $i["type"] == "file")
		{
			if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
			{
				// check file size
				if (isset($i["max"]) && $HTTP_POST_FILES[$name]["size"] > $i["max"])
				{

					$sRet = add_error($sRet, parseHash($g_messages["tobigsize"], 
						Array(
							"file" => rec_field_title($i, $name), 
							"bigsize" => to_anum($HTTP_POST_FILES[$name]["size"]), 
							"size" => to_anum($i["max"])
						)));
					
				} else {

					// check file ext.
					if (isset($i["exts"]) && ! (preg_match("/" . $i["exts"] . "$/i", $HTTP_POST_FILES[$name]["name"])))
					{
						$sRet = add_error($sRet, parseHash($g_messages["badext"], 
							Array(
								"file" => rec_field_title($i, $name), 
								"exts" => ereg_replace("\|", ", ", $i["exts"])
							)));
//					} else {

					}
				}


			} else {
				if ((! isset($i["nocheck"])) && (! isset($i["optional"])))
				{
					if ($i["value"] == "")
						$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
				}
			}
		}
		else if (! isset($i["nohttp"]))
		{
			$v = get_param($name, $i["value"]);

			if (! isset($i["nocheck"]))
			{
				if (isset($i["type"]) && ($i["type"] == "int" || $i["type"] == "float"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if ($i["type"] == "int") $v = (int)$v;
						if ($i["type"] == "float") $v = (double)$v;
						if (isset($i["min"]) && (0 + $v) < $i["min"])
						{
							$sRet = add_error($sRet, parseHash($g_messages["imin"], Array("name"=>rec_field_title($i, $name), "min"=>$i["min"])));
						}
						if (isset($i["max"]) && (0 + $v) > $i["max"])
						{
							$sRet = add_error($sRet, parseHash($g_messages["imax"], Array("name"=>rec_field_title($i, $name), "max"=>$i["max"])));
						}
						if (isset($i["unique"]))
						{
							if ($db->DLookUp("SELECT count(*) FROM $_table WHERE " . $form . "Id<>" . to_sql($_id, "Number") . " AND " . $name . "=" . to_sql($v, "Number")) > 0)
							{
								$sRet = add_error($sRet, parseHash($g_messages["unique"], Array("name"=>rec_field_title($i, $name))));
							}
						}


					}
				}
				else if (isset($i["type"]) && ($i["type"] == "iselect" || $i["type"] == "sselect"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if (isset($i["sqlcheck"]))
						{
							if ($db->DLookUp($i["sqlcheck"] . to_sql($v, ($i["type"] == "iselect" ? "Number" : ""))) == 0)
								$sRet = add_error($sRet, parseHash($g_messages["incorrect"], Array("name" => rec_field_title($i, $name))));
						}
					}
				}
				else if (isset($i["type"]) && ($i["type"] == "ilov" || $i["type"] == "slov"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if (isset($i["options"]))
						{
							if (! isset($i["options"][$v]))
								$sRet = add_error($sRet, parseHash($g_messages["incorrect"], Array("name" => rec_field_title($i, $name))));
						}
					}
				}
				else if (isset($i["type"]) && ($i["type"] == "ilovRadio" || $i["type"] == "slovRadio"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if (isset($i["options"]))
						{
							if (! in_array($v, $i["options"]))
								$sRet = add_error($sRet, parseHash($g_messages["incorrect"], Array("name" => rec_field_title($i, $name))));
						}
					}
				}
				else if (isset($i["type"]) && $i["type"] == "check")
				{
					$v = get_param($name, "0");
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if ($i["value"] != 0 && $i["value"] != 1)
							$sRet = add_error($sRet, parseHash($g_messages["incorrect"], Array("name" => rec_field_title($i, $name))));
					}
				}
				else if (isset($i["type"]) && ($i["type"] == "checks" || $i["type"] == "checksOn"))
				{
					$v = get_checks_param($name);
					if ($v == 0)
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					}
				}
				else if (isset($i["type"]) && $i["type"] == "date3")
				{
					$vY = get_param($name . "Year", "");
					$vM = get_param($name . "Month", "");
					$vD = get_param($name . "Day", "");
					if ($vD == "" || $vM == "" || $vY == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if ($vY < 1900 || $vY > 2010 || $vM < 1 || $vM > 12 || $vD < 1 || $vD > 31)
							$sRet = add_error($sRet, parseHash($g_messages["incorrect"], Array("name" => rec_field_title($i, $name))));
						else
							$v = $vY . "-" . $vM . "-" . $vD;
					}
				} else { // pure text field
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet = add_error($sRet, parseHash($g_messages["required"], Array("name" => rec_field_title($i, $name))));
					} else {
						if (isset($i["min"]) && strlen($v) < $i["min"])
						{
							$sRet = add_error($sRet, parseHash($g_messages["smin"], Array("name"=>rec_field_title($i, $name), "min"=>$i["min"])));
						}
						if (isset($i["max"]) && strlen($v) > $i["max"])
						{
							$sRet = add_error($sRet, parseHash($g_messages["smax"], Array("name"=>rec_field_title($i, $name), "max"=>$i["max"])));
						}
					}
				}
			}

			$fields[$name]["value"] = $v;
		}
	}
	return $sRet;
}


// get values from db
// $sqlFromWhere is a sub-sql like "FROM table WHERE ..."
function rec_get_db($db, &$fields, $sqlFromWhere)
{
	$fs = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["nodb"]))
		{
			if ($fs != "") $fs .= "," ;
			if (isset($i["dbSelect"]))
				$fs .= $i["dbSelect"] . " as " . $name;
			else	
				$fs .= $name;
		}
	}

	$sql = "SELECT " . $fs . " " . $sqlFromWhere;

//debug ($sql);

	$ret = 0;		
	$db->query($sql);
	if ($row = $db->fetch_row())
	{
		foreach ($fields as $name => $i)
		{
			if ((! isset($i["nodb"])) && isset($row[$name]))
			{
				if (isset($i["type"]) && $i["type"] == "check")
				{
					$fields[$name]["value"] = ($row[$name] == 'Y' ? 1 : ($row[$name] == 'N' ? 0 : ""));
				} else {
					$fields[$name]["value"] = $row[$name];
				}
			}
		}
		$ret = 1;
	}
	$db->free_result();
	return $ret;
}

// parse set of items (checkboxes usually) by bit mask
// $name is a html block name; there are tags {title} and {value} in the block
// $sql select should have 2 fields 1-st is id and 2-nd is a title
function rec_parse_checks($name, $db, &$html, $sql, $mask)
{
	if ($db->query($sql))
	{
		while ($row = $db->fetch_row())
		{
			$html->setvar("value", $row[0]);
			$html->setvar("title", $row[1]);
			if ($mask & (1 << ($row[0] - 1)))
				$html->setvar("checked", " checked");
			else
				$html->setvar("checked", "");
			$html->parse($name, true);
		}
		$db->free_result();
	}
}

// parse set of checked items (checkboxes usually) by bit mask
// $name is a html block name; there are tags {title} and {value} in the block
// $sql select should have 2 fields 1-st is id and 2-nd is a title
function rec_parse_checksOn($name, $db, &$html, $sql, $mask)
{
	if ($db->query($sql))
	{
		while ($row = $db->fetch_row())
		{
			$html->setvar("value", $row[0]);
			$html->setvar("title", $row[1]);
			if ($mask & (1 << ($row[0] - 1)))
				$html->parse($name, true);
		}
		$db->free_result();
	}
}

// parse all fields
function rec_parse_values($db, &$html, &$fields)
{
	global $g_months;

	foreach ($fields as $name => $i)
	{
		if (isset($i["value"]))
		{
			if (isset($i["type"]) && $i["type"] == "file")
			{
				$html->setvar($name, $i["value"]);
			}
			else if (isset($i["type"]) && $i["type"] == "check")
			{
				$html->setvar($name, $i["value"] == 1 ? " checked" : "");
				$html->setvar($name . "_no", $i["value"] == 0 ? " checked" : "");
			}
			else if (isset($i["type"]) && $i["type"] == "checks")
			{
				rec_parse_checks($name, $db, $html, $i["sql"], $i["value"]);
			}
			else if (isset($i["type"]) && $i["type"] == "checksOn")
			{
				rec_parse_checksOn($name, $db, $html, $i["sql"], $i["value"]);
			}
			else if (isset($i["type"]) && ($i["type"] == "ilov" || $i["type"] == "slov") && isset($i["options"]))
			{
				$html->setvar($name . "Options", HSelectOptions($i["options"], $i["value"])	);
			}
			else if (isset($i["type"]) && ($i["type"] == "ilovRadio" || $i["type"] == "slovRadio") && isset($i["options"]))
			{
				foreach ($i["options"] as $val)
				{
					if ($i["value"] == $val)
						$html->setvar($name . "_" . $val, "checked");
				}
			}
			else if (isset($i["type"]) && ($i["type"] == "iselect" || $i["type"] == "sselect") && isset($i["sql"]))
			{
				$html->setvar($name . "Options", $db->DSelectOptions($i["sql"], $i["value"]));
			}
			else if (isset($i["type"]) && $i["type"] == "idblookup")
			{
				$s = $db->DLookUp($i["sql"] . to_sql($i["value"], "Number"));
				if ($s === 0) $s = "";
				$html->setvar($name, $s);
			}
			else if (isset($i["type"]) && $i["type"] == "lovlookup")
			{
				if (isset($i["options"][$i["value"]]))
					$html->setvar($name, $i["options"][$i["value"]]);
			}
			else if (isset($i["type"]) && $i["type"] == "date3")
			{
				$d = "";
				$m = "";
				$y = "";
				if ($i["value"] != "")
				{
					$aa = explode("-", $i["value"]);
					$d = $aa[2];
					$m = $aa[1];
					$y = $aa[0];
				}
				$html->setvar($name . "DayOptions", NSelectOptions(1, 31, $d));
				$html->setvar($name . "MonthOptions", HSelectOptions($g_months, $m));
				$html->setvar($name . "YearOptions", NSelectOptions(1930, 2006, $y));
			} else {
				$html->setvar($name, $i["value"]);
			}
		}
	}
}

// return the set of Javascript checks
// $form is the name of the <form>
function rec_html_checks(&$fields, $form)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["nocheck"]))
		{
			if (isset($i["type"]) && (
				$i["type"] == "ilovRadio" ||
				$i["type"] == "slovRadio"
				) && (! isset($i["optional"])) )
			{
				$ret .= "sError += checkEmptyRadio(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . 
					");";
			}
			else if (isset($i["type"]) && (
				$i["type"] == "iselect" ||
				$i["type"] == "sselect" ||
				$i["type"] == "ilov" ||
				$i["type"] == "slov"
				) && (! isset($i["optional"])) )
			{
				$ret .= "sError += checkEmptyValue(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".options[document.forms[\"" . $form . "\"]." . $name . ".selectedIndex].value" .
					");";
			}
			else if (isset($i["type"]) && $i["type"] == "file")
			{
				if (! isset($i["optional"]))
				{
					$ret .= "sError += checkEmptyValue(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", document.forms[\"" . $form . "\"]." . $name . ".value" .
						");\n";
				}

				if (isset($i["exts"]))
				{
					$ss = "";
					$aE = explode("|", $i["exts"]);
					foreach ($aE as $e)
					{
						if ($ss != "") $ss .= " && ";
						$ss .= "sExt != \"." . $e . "\"";
					}
					if ($ss != "")
					$ret .= 
						"if (document.forms[\"" . $form . "\"]." . $name . ".value.length > 0)\n" .
						"{\n" .
						"	var sExt = document.forms[\"" . $form . "\"]." . $name . ".value;\n" .
						"	var i = sExt.lastIndexOf('.');\n" .
						"	if (i != -1)\n" .
						"	{\n" .
						"		sExt = sExt.substring(i).toLowerCase();\n" .
						"	}\n" .
						"	if (" . $ss . ")\n" .
						"	{\n" .
						"		sError += \"\\t" . rec_field_title($i, $name) . " has incorrect file type\\n\";\n" .
						"	}\n" .
						"}\n";
				}

			}
			else if (isset($i["type"]) && $i["type"] == "date3")
			{
				if (! isset($i["optional"]))
				{
					$ret .= "sError += checkEmptyValue3(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", " .
						"document.forms[\"" . $form . "\"]." . $name . "Day.options[document.forms[\"" . $form . "\"]." . $name . "Day.selectedIndex].value," .
						"document.forms[\"" . $form . "\"]." . $name . "Month.options[document.forms[\"" . $form . "\"]." . $name . "Month.selectedIndex].value," .
						"document.forms[\"" . $form . "\"]." . $name . "Year.options[document.forms[\"" . $form . "\"]." . $name . "Year.selectedIndex].value" .
						");";
				}
			}
			else if (isset($i["type"]) && $i["type"] == "check")
			{
				if (! isset($i["optional"]))
					$ret .= "sError += checkCheckField(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", document.forms[\"" . $form . "\"]." . $name . ",1" .
						");";
			}
			else if (isset($i["type"]) && ($i["type"] == "checks" || $i["type"] == "checksOn"))
			{
				if (! isset($i["optional"]))
				{
					// TODO ! ! !
				}
			}
			else if (isset($i["type"]) && $i["type"] == "int")
			{
				$ret .= "sError += checkIntField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			}
			else if (isset($i["type"]) && $i["type"] == "float")
			{
				$ret .= "sError += checkFloatField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			} else {
				$ret .= "sError += checkField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			}
		
			$ret .= "\n";
		}
	}
	return $ret;
}







class CHtmlRecord extends CHtmlBlock
{
	var $m_db = null;
	var $m_fields = Array();

	var $m_bInsert = 1;
	var $m_bUpdate = 1;
	var $m_bDelete = 1;

	/**
		HTTP parameter for ID
		by default ("") it's <fromName>Id
	*/
	var $m_IdParamName = "";

	var $m_id = 0;

	var $m_table;
	var $m_return_page = "";
	var $m_sqlFromWhere = "";

	var $sMessage = "";


	function CHtmlRecord($db, $name, $html_path, $table, $sqlFromWhere, $return_page)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
		$this->m_sqlFromWhere = $sqlFromWhere;
		$this->m_table = $table;
		$this->m_return_page = $return_page;
	}


	function customValidate(&$cmd)
	{
		return "";
	}

	function customAction(&$cmd)
	{
		return "";
	}


	function init()
	{
		global $g_messages;

		if ($this->m_IdParamName == "")
			$this->m_IdParamName = $this->m_name . "Id";

		$this->m_id = get_param($this->m_IdParamName, $this->m_id);
		if ($this->m_id == "")
			$this->m_id = 0;

		if ($this->m_id != 0)
		{
			if (! rec_get_db($this->m_db, $this->m_fields, $this->m_sqlFromWhere .  to_sql($this->m_id, "Number")))
			{
				$this->sMessage = $g_messages["no_such_record"];
			}
		}
		parent::init();
	}


	function action()
	{
		global $g_messages;
		global $HTTP_POST_FILES;

		$cmd = get_param("cmd", "");

		if ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update")
		{

			if ( (($this->m_id == 0) && (! $this->m_bInsert)) || (($this->m_id > 0) && (! $this->m_bUpdate)) )
			{
				header("Location: " . $this->m_return_page . get_params() . "\n");
				exit;			
			}

			$this->sMessage .= rec_get_http($this->m_db, $this->m_fields, $this->m_id, $this->m_name, $this->m_table);

			$this->sMessage .= $this->customValidate($cmd);

			if ($this->sMessage == "")
			{


				foreach ($this->m_fields as $name => $i)
				{
					if (
						isset($i["type"]) && $i["type"] == "file" &&
						isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"])
					)
					{
						// trying to add uploaded file
						if (isset($i["file"]))
							$sFile = $i["file"];
						else
							$sFile = "{rand}_{name}";
						$sFile = ereg_replace("{rand}", substr(md5(uniqid(rand())), 1, 10), $sFile);
						$sFile = ereg_replace("{name}", $HTTP_POST_FILES[$name]["name"], $sFile);
						if ($i["value"] != "")
							@unlink($i["dir"] . "/" . $i["value"]);
						if (! @move_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'],
							$i["dir"] . "/" . $sFile))
						{
							$this->sMessage .= add_error($sRet, parseHash($g_messages["cantsave"], Array("file" => rec_field_title($i, $name))));
							$this->m_fields[$name]["value"] = "";
						} else {
							$this->m_fields[$name]["value"] = $sFile;
						}

					}
				}


				if ($this->sMessage == "")
				{

					$sql = "";
					if ($this->m_id == 0)
					{
						$sResult = "added";
						$sql = "INSERT INTO " . $this->m_table . " (" . rec_sql_fields($this->m_fields) . ") values (" . rec_sql_values($this->m_fields) . ")";
					} else {
						$sResult = "updated";
						$sql = "UPDATE " . $this->m_table . " SET " . rec_fields_to_sql($this->m_fields) .
							" WHERE " . $this->m_name . "Id=" . to_sql($this->m_id, "Number");
					}

//echo "<hr>$sql<hr>";

					if ($this->m_db->execute($sql))
					{
						if ($this->m_id == 0)
						{
							$this->m_id = $this->m_db->get_insert_id();
						}
		
						$this->sMessage .= $this->customAction($cmd);
				
						if ($this->sMessage == "" && ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update"))
						{
							header("Location: " . $this->m_return_page . "res=" . $sResult . "&" . get_params() . "\n");
							exit;
						}
					}
				}
			}
		} else {
			$this->sMessage .= $this->customValidate($cmd);
		}

		$this->sMessage .= $this->customAction($cmd);

		if ($cmd == $this->m_name . "_delete")
		{
			if (! $this->m_bDelete)
			{
				header("Location: " . $this->m_return_page . get_params() . "\n");
				exit;			
			}
			if ($this->m_id != 0)
			{
				foreach ($this->m_fields as $name => $i)
				{
					if (isset($i["type"]) && $i["type"] == "file" && $i["value"])
					{
						@unlink($i["dir"] . "/" . $i["value"]);
					}
				}

				$this->m_db->execute("DELETE FROM " . $this->m_table . " WHERE " . $this->m_name . "Id=" . to_sql($this->m_id, "Number"));
				
				header("Location: " . $this->m_return_page . "res=deleted&" . get_params() . "\n");
				exit;			
			}			
		}


	}


	function parseBlock(&$html)
	{

		$html->setvar($this->m_name . "Id", $this->m_id);
		$html->setvar("cmd", $this->m_id == 0 ? "insert" : "update");

		if ($this->m_id == 0)
		{
			if ($html->blockexists($this->m_name . "_bHeadNew"))
				$html->parse($this->m_name . "_bHeadNew");
			//$html->setvar($this->m_name . "_bHeadExisted", "");

			if ($this->m_bInsert && $html->blockexists($this->m_name . "_bInsert"))
				$html->parse($this->m_name . "_bInsert");
		}
		else
		{
			//$html->setvar($this->m_name . "_bHeadNew", "");
			if ($html->blockexists($this->m_name . "_bHeadExisted"))
				$html->parse($this->m_name . "_bHeadExisted");

			if ($this->m_bDelete && $html->blockexists($this->m_name . "_bDelete"))
				$html->parse($this->m_name . "_bDelete");
			if ($this->m_bUpdate && $html->blockexists($this->m_name . "_bUpdate"))
				$html->parse($this->m_name . "_bUpdate");
		}

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}


		rec_parse_values($this->m_db, $html, $this->m_fields);

		$html->setvar("checks", rec_html_checks($this->m_fields, $this->m_name));


		parent::parseBlock($html);
	}


}

?>