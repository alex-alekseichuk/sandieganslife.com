<?php

//
//	2 main classes of the Framework
//
//
//	example of regular framework page:
//	
//	include_once("include/core.php");
//	include_once("include/db.php");
//	include_once("include/lang.php");
//	include_once("include/html.php");
//	include_once("include/params.php");
//	include_once("include/block.php");
//	include_once("include/grid.php");
//	include_once("include/record.php");
//	include_once("include/common.php");
//	
//	$db = new CDB();
//	$db->connect();
//	
//	$page = new CHtmlBlockPage("", "html/1.html");
//	$page->add(new CHtmlBlock("iHeader", "html/header.html"));
//	$page->add(new CHtmlBlock("iFooter", "html/footer.html"));
//	
//	$page->init();
//	$page->action();
//	$page->parse(null);
//	



//
//	parent for any block
//		used to create hierarchy of the blocks
//		The page is a block; usually it's root block of hierarcy
//		Block may have several sub-blocks
//
//	CBlock($name)	constructor
//		$name maybe ""
//	add($block)		add $block as a child to this block 
//	init()			init the block recursively
//	action()		do the action recursively
//		

class CBlock
{
	var $m_blocks = Array();	// child blocks
	var $m_name = "";			// name of the block
	var $m_parent = null;		// ref. to parent block
	var $m_root = null;			// ref. to root block (page)

	function CBlock($name)
	{
		$this->m_name = $name;
		$this->m_root = $this;
	}

	function init()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			$this->m_blocks[$n]->init();
		}
	}

	function action()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			$this->m_blocks[$n]->action();
		}
	}

	function add(&$b)
	{
		if ($b->m_name == "")
			return;
		if (isset($this->m_blocks[$b->m_name]))
			return;
		$this->m_blocks[$b->m_name] = $b; // php4.3: &$b    php5: $b   
		$b->m_parent = $this;
		$b->m_root = $this->m_root;
	}

}


//	parent for any HTML block
//
//	$name of CHtmlBlock should be "" or have appropriate block in HTML template
//
//	$g_images - global var.; relative path to folder with images
//		'img' by default
//
//	CHtmlBlock($name, $html_path) constructor
//		loads HTML template and set 2 tags
//			{images} - url path to images, js and css
//			{params} - saved parameters for current page
//		$name maybe ""
//		$html_path path to HTML template or null if it's just a part of the page
//
//	parseBlock(&$html)
//		parse only this block
//

class CHtmlBlock extends CBlock
{
	// ref. to CHtml for this block if it's a page
	// or null if it's just some part of the page
	var $m_html = null;

	function CHtmlBlock($name, $html_path)
	{
		global $g_images;
		if (! isset($g_images))
			$g_images = "img";

		$this->CBlock($name);
		if ($html_path != null)
		{
			$this->m_html = new CHtml();
			$this->m_html->LoadTemplate($html_path , "main");
			$this->m_html->setvar("img" , $g_images);
			$this->m_html->setvar("params", get_params());
		}
	}

	function init()
	{
		parent::init();
	}

	function action()
	{
		parent::action();
	}


	// parse only this block
	// don't call this method
	function parseBlock(&$html)
	{
		if ($this->m_html != null)
		{
			$html->parse("main");
		}
		else
		{
			$html->parse($this->m_name);
		}
	}


	// parse the block in blocks tree
	// call this method
	function parse($html)
	{
		if ($this->m_html != null)
		{
			$html_ = &$this->m_html;
		} else {
			if ($html == null)
				return;
			$html_ = &$html;
		}
		foreach ($this->m_blocks as $name => $b)
		{
			$this->m_blocks[$name]->parse($html_); // php4.3: &$html_    php5: $html_
		}
		if ($this->m_html != null)
		{
			if ($this->m_parent == null)
			{
				$this->parseBlock($this->m_html);
				echo $this->m_html->getvar("main");
			} else {
				$this->parseBlock($this->m_html);
				$html->setvar($this->m_name, $this->m_html->getvar("main"));
			}
		} else {
			$this->parseBlock($html);
		}
	}

}


?>