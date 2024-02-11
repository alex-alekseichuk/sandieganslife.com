<?php

// php version check
// session start
// several required HTTP headers

$PHPVersion = explode(".",  phpversion());
if (($PHPVersion[0] < 4) || ($PHPVersion[0] == 4  && $PHPVersion[1] < 1)) {
    echo "Sorry. This program requires PHP 4.1 and above to run.<br>You may upgrade your php at <a href='http://www.php.net/downloads.php'>http://www.php.net/downloads.php</a>";
    exit;
}

session_start();
header('Pragma: ');
header('Cache-control: ');
header('Expires: ');
header('Content-type: text/html;charset=utf-8');



include_once("conf.php");


// common methods

function to_html($Value)
{
	return nl2br(htmlspecialchars($Value));
}
function to_url($Value)
{
	return urlencode($Value);
}
function get_session($parameter_name)
{
    return isset($_SESSION[$parameter_name]) ? $_SESSION[$parameter_name] : "";
}
function set_session($param_name, $param_value)
{
    $_SESSION[$param_name] = $param_value;
}
function get_cookie($parameter_name)
{
    return isset($_COOKIE[$parameter_name]) ? $_COOKIE[$parameter_name] : "";
}
function set_cookie($parameter_name, $param_value, $expired = -1)
{
  if ($expired == -1)
    $expired = time() + 3600 * 24 * 366;
  elseif ($expired && $expired < time())
    $expired = time() + $expired;
  setcookie ($parameter_name, $param_value, $expired);  
}

// used in get_param()
function strip($value)
{
  if(get_magic_quotes_gpc() != 0)
  {
    if(is_array($value))  
      foreach($value as $key=>$val)
        $value[$key] = stripslashes($val);
    else
      $value = stripslashes($value);
  }
  return $value;
}

// get param from HTTP
function get_param($parameter_name, $default_value = "")
{
    $parameter_value = "";
    if(isset($_POST[$parameter_name]))
        $parameter_value = strip($_POST[$parameter_name]);
    else if(isset($_GET[$parameter_name]))
        $parameter_value = strip($_GET[$parameter_name]);
    else
        $parameter_value = $default_value;
    return $parameter_value;
}

// returns the array of HTTP values of the same $parameter_name paramter
function get_param_array($parameter_name)
{
	$arr = Array();
	
    if(isset($_POST[$parameter_name]))
	{
		if (is_array($_POST[$parameter_name]))
		{
			$arr = $_POST[$parameter_name];
		}
		else
		{
			$arr = Array($_POST[$parameter_name]);
		}
	}
    else if(isset($_GET[$parameter_name]))
	{
		if (is_array($_GET[$parameter_name]))
		{
			$arr = $_GET[$parameter_name];
		}
		else
		{
			$arr = Array($_GET[$parameter_name]);
		}
	}
    return $arr;
}


// get from HTTP bit mask for by several values of $name parameter
function get_checks_param($name)
{
	$arr = get_param_array($name);
	$v = 0;
	foreach ($arr as $param)
	{
		$v |= (1 << ($param - 1));
	}
	return $v;
}


// just parse some string template like "...{param}..." with specified hash like param=>value
function parseHash($str, $hash)
{
	$s = $str;
	foreach ($hash as $name => $value)
	{
		$s = str_replace("{" . $name . "}", $value, $s);
	}
	return $s;
}

// accumulate errors/warnings/messages list for further html output
function add_error($s, $sError)
{
	if ($s != "")
		$s .= "<BR>";
	return $s . $sError;
}


// send email by regualr php method
// $to - to address
// $from - from address
// $subject
// $message
// $type - content-type of the body (text/html or text/plain); text/plain used by default
// $bHiPriority - true - add hi-priority flag
function send_email($to, $from, $subject, $message, $type = "text/html", $bHiPriority = false)
{
	$headers = "";
	$headers .= "From: " . $from . "\n";
	if ($type == "")
		$type = "text/plain";
	$headers .= "Content-type: " . $type . "\n";
	if ($bHiPriority)
		$headers .= "X-Priority: 2 (High)\n";

	$aTo = explode(";", $to);

	foreach ($aTo as $e)
		@mail($e, $subject, $message, $headers);
}

// just to debug some value
function debug($s)
{
	echo "<hr>" . $s . "<hr>\n";
}

function redirect($url)
{
	header("Location: " . $url . "\n");
	exit;			
}

function to_anum($n)
{
	$s = (string)$n;
	$s2 = "";
	while (strlen($s) > 0)
	{
		if ($s2 != "")
			$s2 = "," . $s2;
		if (strlen($s) > 3)
		{
			$s2 = substr($s, strlen($s)-3) . $s2;
			$s = substr($s, 0, strlen($s)-3);
		} else  {
			$s2 = $s . $s2;
			$s = "";
		}
	}
	return $s2;
}


function recursive_remove_directory($directory, $empty=FALSE)
{
     // if the path has a slash at the end we remove it here
     if(substr($directory,-1) == '/')
     {
         $directory = substr($directory,0,-1);
     }
  
     // if the path is not valid or is not a directory ...
     if(!file_exists($directory) || !is_dir($directory))
     {
         // ... we return false and exit the function
         return FALSE;
  
     // ... if the path is not readable
     }elseif(!is_readable($directory))
     {
         // ... we return false and exit the function
         return FALSE;
  
     // ... else if the path is readable
     }else{
  
         // we open the directory
         $handle = opendir($directory);
  
         // and scan through the items inside
         while (FALSE !== ($item = readdir($handle)))
         {
             // if the filepointer is not the current directory
             // or the parent directory
             if($item != '.' && $item != '..')
             {
                 // we build the new path to delete
                 $path = $directory.'/'.$item;
  
                 // if the new path is a directory
                 if(is_dir($path)) 
                 {
                     // we call this function with the new path
                     recursive_remove_directory($path);
  
                 // if the new path is a file
                 }else{
                     // we remove the file
                     unlink($path);
                 }
             }
         }
         // close the directory
         closedir($handle);
  
         // if the option to empty is not set to true
         if($empty == FALSE)
         {
             // try to delete the now empty directory
             if(!rmdir($directory))
             {
                 // return false if not possible
                 return FALSE;
             }
         }
         // return success
         return TRUE;
     }
}


?>