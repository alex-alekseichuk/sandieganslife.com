<?

// php version check
// session start
// several required HTTP headers

$PHPVersion = explode(".",  phpversion());
if ($PHPVersion[0] < 5) {
    echo "Sorry. This program requires PHP 5 or above to run.<br>You may upgrade your php at <a href='http://www.php.net/downloads.php'>http://www.php.net/downloads.php</a>";
    exit;
}

session_start();
header('Pragma: ');
header('Cache-control: ');
header('Expires: ');
header('Content-type: text/html;charset=utf-8');


define("ADMIN_PASSWD", "amigo21");
define("ADMIN_USER_ID", 777);		// it's just some dummy ID

//define("AMIGO_INCLUDE", "c:/projects/amigo/include/");
//define("AMIGO_INCLUDE", "/home/rockhost/amigo/include/");
define("AMIGO_INCLUDE", "/home/users/amigo/include/");

include_once(AMIGO_INCLUDE . "web.php");

?>