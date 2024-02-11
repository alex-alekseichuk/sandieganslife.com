<?php

//
//	Grid HTML block
//
//	CHtmlGrid($db, $name, $html_path) constructor
//	onItem() called for each item after getting data but before parsing
//
//	regular example:
//
//	$list = new CListGrid($db, "searchlist", "html/searchlist.html");
//	$list->m_sqlcount = "select count(*) as cnt from videos where userId=" . to_sql($userId, "Number");
//	$list->m_sql = "select login from videos where userId=" . to_sql($userId, "Number");
//
//	$list->m_fields["login"] = Array ("login", null);
//	$list->m_fields["img"] = Array ("img", "no.jpg");
//		"name", (null | "value") - if value != null then it's a value from this array
//		if value==null then it's from sql-result
//	$list->m_sort = "registered";
//	$list->m_dir = "desc";
//	$page->add($list);
//
//	more parameters:
//
//	$list->m_nPerPage			= 20;					// number items per page
//	$list->m_itemBlocks["vip"]	= 0;
//		if there is a block gridName_itemBlockName then
//			if it's true or 1 then parse it else set this internal item block to ""
//	$list->m_pageMode			= GRIDMODE_LASTPAGE;	// last page filled by items from prev. page
//	$list->m_lastPageByDefault	= GRIDMODE_LASTPAGE;	// by default grid views last page
//
//	$list->m_nCells	= 3;	// split whole grid into 3 cells (3 items per special row)
//							// here also used _begin, _end and _newline html blocks
//
//
//	grid blocks and tags:
//		{info} to show info about current page
//		gridName_pager
//			gridName_first
//				{url}, {page}
//			gridName_prev
//				{url}, {page}
//			gridName_page
//				{url}, {page}
//				gridName_link
//					{url}, {page}
//				gridName_curpage
//					{url}, {page}
//			gridName_next
//				{url}, {page}
//			gridName_last
//				{url}, {page}
//		gridName_sort_*fieldName* - one arrow sorter
//			gridName_asc_*fieldName*
//			gridName_desc_*fieldName*
//		gridName_sort2_*fieldName* - 2 arrows sorter
//			{urlAsc}
//			{urlDesc}
//			{asc} - "" or 2
//			{desc} - "" or 2
//		gridName_item
//			{*field*}
//			{n0} number of item from 0
//			{n1} number of item from 1
//			* internal items blocks *
//			gridName_middle		parsed for the item in the middle
//			gridName_even		parsed for items: 0,2,4,6,...
//			gridName_odd		parsed for items: 1,3,5,...
//			gridName_separator	parsed for all items except last one
//		gridName_noitems
//		{pager2} copy parsed pager block
//
//
//
//	2006-03-10
//

define("GRIDMODE_REGULAR", 0);
define("GRIDMODE_LASTPAGE", 1);

class CHtmlGrid extends CHtmlBlock
{
	var $m_nPerPage = 30; // number items per page

	var $m_sql = null;			// sql to get items for this page
	var $m_sqlcount = null;		// count sql to get whole number of items
	var $m_db = null;			// ref. to CDB
	var $m_sort = "";			// default sort field
	var $m_dir = "";			// default sort dir. 'asc' or 'desc'

	// GRIDMODE_REGULAR		page by page strongly,
	// GRIDMODE_LASTPAGE	last page should be filled
	var $m_pageMode = GRIDMODE_REGULAR;

	// GRIDMODE_REGULAR		view first page by default as regular grid,
	// GRIDMODE_LASTPAGE	view last page by default
	var $m_lastPageByDefault = GRIDMODE_REGULAR;

	var $m_nCells = 0;

	// the map of columns (fields)
	var $m_fields = Array();
	// "name", (null | "value") - if value != null then it's a value from this array
	// if value==null then it's from sql-result

	// blocks that we should parse/hide in each item
	var $m_itemBlocks = Array();

	
	// warning or message we can parse as {sMessage} in the grid or in bMessage block
	var $sMessage = "";

	var $resultset = null;

	// constructor
	function CHtmlGrid($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function init()
	{
		global $g_param_values;

		parent::init();

		$nPerPage = get_param($this->m_name . "Page", $this->m_nPerPage);
		if ($nPerPage > 0)
			$this->m_nPerPage = $nPerPage;
	
		// get sort and dir params
		$sortParam = $this->m_name . "Sort"; // http/cookie param of sorting
		//$sort = get_cookie($sortParam);	// get sorting from cookie
		//if (! $sort)
			$sort = $this->m_sort;
		$sort = get_param($sortParam, $sort); // get sort from http
		if ($sort != $this->m_sort && $sort != "")	// if it is but not default sorting
			$this->m_dir = "";		// then don't use default dir
		$dirParam = $this->m_name . "Dir"; // http/cookie param of dir
		//$dir = get_cookie($dirParam);	// get dir from cookie
		//if (! $dir)
			$dir = $this->m_dir;
		$dir = get_param($dirParam, $dir); // get dir from http
		if ($dir == "")
			$dir = $this->m_dir;
		if (! isset($this->m_fields[$sort]))	// if there is not such field to sort
			$sort = $this->m_sort;				// then sort by default
		else if ($this->m_fields[$sort][1] != null)	// or if this is not DB field
			$sort = $this->m_sort;					// then sort by default as well
		if ($sort != "" && $dir != "asc" && $dir != "desc") $dir = "asc"; // dir should be 'asc' or 'desc'
		//set_cookie($sortParam, $sort);
		//set_cookie($dirParam, $dir);
		$g_param_values[$sortParam] = $sort;
		$g_param_values[$dirParam] = $dir;

		$this->m_sort = $sort;
		$this->m_dir = $dir;
	}


	// all stuff is here
	function parseBlock(&$html)
	{
		$sortParam = $this->m_name . "Sort"; // http/cookie param of sorting
		$sort = $this->m_sort;
		$dir = $this->m_dir;

		// get the total number of items
		$n_n = $this->m_db->DLookUp($this->m_sqlcount);
		if ($n_n > 0) // if there is some item(s)
		{
			if ($n_n == "") $n_n = 0; // ?

			// number of pages
			$n_p = (int)(($n_n % $this->m_nPerPage > 0 ? 1 : 0) + ($n_n / $this->m_nPerPage));

			$nOffset = get_param($this->m_name . "Offset", ""); // get offset from http
			if ($this->m_lastPageByDefault == GRIDMODE_LASTPAGE)	// if we need last page by default
				if ($nOffset === "")					// and if there is not offset yet
					$nOffset = $n_p - 1;			// then switch it to last page
			if ($nOffset < 0) $nOffset = 0;				// 0-first page
			if ($nOffset >= $n_p) $nOffset = $n_p - 1;	// last page


			// first item we are going to view
			if ($this->m_pageMode == GRIDMODE_LASTPAGE)
			{
				$nFirst = $nOffset * $this->m_nPerPage;
				if ($nFirst > $n_n - $this->m_nPerPage)
					$nFirst = $n_n - $this->m_nPerPage;
				if ($nFirst < 0)
					$nFirst = 1;
				else
					$nFirst ++;
			} else {
				$nFirst = ($nOffset  * $this->m_nPerPage + 1);
			}
			// last item we are going to view
			if ($this->m_pageMode == GRIDMODE_LASTPAGE)
			{
				$nLast = $nFirst + $this->m_nPerPage;
				if ($nLast > $n_n)
					$nLast = $n_n;
			} else {
				$nLast = ($nOffset + 1) * $this->m_nPerPage;
				if ($nLast > $n_n) $nLast = $n_n;
			}

			$html->setvar("info", to_anum($nFirst) . " - " . to_anum($nLast) . " of " . to_anum($n_n));

			$sOffset = $this->m_name . "Offset";
			if ($nOffset >= 10)
			{
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, "0"));
				$html->parse($this->m_name . "_first");
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $nOffset - 10));
				$html->parse($this->m_name . "_prev");
			} else {
				$html->setblockvar($this->m_name . "_first", "");
				$html->setblockvar($this->m_name . "_prev", "");
			}

			if ($html->blockexists($this->m_name . "_page"))
			{
				$n = 10*(int)($nOffset / 10) + 10;
				if ($n > $n_p) $n = $n_p;
				for ($i = 10*(int)($nOffset / 10); $i < $n; $i++)
				{
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $i));
					$html->setvar("page", $i+1);
					if ($i != $nOffset)
					{
						$html->parse($this->m_name . "_link", false);
						$html->setblockvar($this->m_name . "_curpage", "");
					} else {
						$html->setblockvar($this->m_name . "_link", "");
						$html->parse($this->m_name . "_curpage", false);
					}
					$html->parse($this->m_name . "_page", true);
				}
			}

			if ((int)($nOffset / 10) < (int)($n_p / 10))
			{
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, ($nOffset + 10 > $n_p - 1) ? $n_p - 1 : $nOffset + 10 ));
				$html->parse($this->m_name . "_next");
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $n_p - 1));
				$html->parse($this->m_name . "_last");
			} else {
				$html->setblockvar($this->m_name . "_next", "");
				$html->setblockvar($this->m_name . "_last", "");
			}
			if ($html->blockexists($this->m_name . "_pager"))
				$html->parse($this->m_name . "_pager");
			$html->setvar("pager2", $html->getvar($this->m_name . "_pager"));
			$html->setblockvar($this->m_name . "_noitems", "");


			$sql = $this->m_sql;

			if ($sort != "")
				$sql .= " ORDER BY " . $this->m_fields[$sort][0] . " " . $dir;
			$sql .= " LIMIT ";
			if ($nOffset > 0)
			{
				if ($this->m_pageMode == GRIDMODE_LASTPAGE) // last page should be filled
				{
					$_i = $nOffset * $this->m_nPerPage;
					if ($_i > $n_n - $this->m_nPerPage)
						$_i = $n_n - $this->m_nPerPage;
					if ($_i > 0)
						$sql .= $_i . ",";
				} else {
					$sql .= ($nOffset * $this->m_nPerPage) . ",";
				}
			}
			$sql .= $this->m_nPerPage;

//echo "$sql";

			$this->resultset = $this->m_db->queryAll($sql);
			if ($this->resultset)
			{
				$this->onQuery();

				$counter = 0;
				$n = $nLast - $nFirst + 1;

				if ($this->m_nCells > 0)
				{
					$html->parsesafe($this->m_name . "_begin", false);
					if ($n > $this->m_nCells && $n % $this->m_nCells > 0)
					{
						$html->setvar("lostCells", $this->m_nCells - ($n % $this->m_nCells));
						$html->parsesafe($this->m_name . "_close", false);
					}
					$html->parsesafe($this->m_name . "_end", false);
				}

				foreach ($this->resultset as $row)
				{
					foreach ($this->m_fields as $fn => $field)
					{
						if ($field[1] == null)
						{
							if (isset($row[$field[0]]))
								$this->m_fields[$fn][2] = $row[$field[0]];
						} else {
							$this->m_fields[$fn][2] = $field[1];
						}
					}
					$this->onItem();
					foreach ($this->m_fields as $fn => $field)
					{
						if (isset($field[2]))
						{
							$html->setvar($fn, $field[2]);
							$this->m_fields[$fn][1] = null;
							$this->m_fields[$fn][2] = "";
						}
					}
					$html->setvar("n0", $counter);
					$html->setvar("n1", $counter + 1);
					foreach ($this->m_itemBlocks as $itemBlock => $b)
					{
						if ($html->blockexists($this->m_name . "_" . $itemBlock))
						{
							if ($b)
								$html->parse($this->m_name . "_" . $itemBlock, false);
							else
								$html->setblockvar($this->m_name . "_" . $itemBlock, "");
						}
					}
					if ($html->blockexists($this->m_name . "_middle") && $n > 1)
					{
						if ($counter == ceil($n / 2) - 1)
							$html->parse($this->m_name . "_middle", false);
						else
							$html->setblockvar($this->m_name . "_middle", "");
					}
					if ($html->blockexists($this->m_name . "_odd"))
					{
						if ($counter % 2 == 1)
							$html->parse($this->m_name . "_odd", false);
						else
							$html->setblockvar($this->m_name . "_odd", "");
					}
					if ($html->blockexists($this->m_name . "_even"))
					{
						if ($counter % 2 == 0)
							$html->parse($this->m_name . "_even", false);
						else
							$html->setblockvar($this->m_name . "_even", "");
					}

					if ($this->m_nCells > 0)
					{
						if ($html->blockexists($this->m_name . "_newline"))
						{
							if (
								($counter + 1) % $this->m_nCells == 0
								&& $counter < $n - 1
							)
								$html->parse($this->m_name . "_newline", false);
							else
								$html->setblockvar($this->m_name . "_newline", "");
						}
					}

					if ($html->blockexists($this->m_name . "_separator"))
					{
						if ($counter < $n - 1)
							$html->parse($this->m_name . "_separator", false);
						else
							$html->setblockvar($this->m_name . "_separator", "");
					}
					$html->parse($this->m_name . "_item", true);
					$counter ++;

				}

			} else {
				$n_n = 0;
			}

		}
		if ($n_n == 0)
		{
			$html->setvar("info", "");
			$html->setvar($this->m_name . "_pager", "");
			$html->setvar($this->m_name . "_pager2", "");
			$html->setvar("pager2", "");

			if ($html->blockexists($this->m_name . "_noitems"))
				$html->parse($this->m_name . "_noitems");
		}

		foreach ($this->m_fields as $fname => $field)
		{
			$b = $this->m_name . "_sort_" . $fname;
			if ($html->blockexists($b))
			{
				if ($sort == $fname && $dir == "asc")
				{
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $fname, $this->m_name . "Dir", "desc"));
				}
				else
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $fname, $this->m_name . "Dir", "asc"));
				if ($sort == $fname && $dir == "desc")
					$html->parse($this->m_name . "_desc_" . $fname);
				else				
					$html->setvar($this->m_name . "_desc_" . $fname, "");
				if ($sort == $fname && $dir == "asc")
					$html->parse($this->m_name . "_asc_" . $fname);
				else				
					$html->setvar($this->m_name . "_asc_" . $fname, "");
				$html->parse($b);
			}

			$b = $this->m_name . "_sort2_" . $fname;
			if ($html->blockexists($b))
			{
				$html->setvar("urlAsc", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $fname, $this->m_name . "Dir", "asc"));
				$html->setvar("urlDesc", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $fname, $this->m_name . "Dir", "desc"));
				if ($sort == $fname && $dir == "desc")
					$html->setvar("desc", "2");
				else				
					$html->setvar("desc", "");
				if ($sort == $fname && $dir == "asc")
					$html->setvar("asc", "2");
				else				
					$html->setvar("asc", "");
				$html->parse($b);
			}
		}


		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
			{
				$html->parse($this->m_name . "_bMessage");
			}
		}

		unset($this->resultset);

		parent::parseBlock($html);
	}


	/**
		called one time for each record before parsing
	*/
	function onItem()
	{
	}	

	/**
		called one time for whole resultset after query before parsing
	*/
	function onQuery()
	{
	}	

	

}

?>