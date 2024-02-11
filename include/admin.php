<?

define("ADMIN_PASSWD", "tokio3");

$g_images = "../img/admin";



//	blocks params
$g_params = Array(
	"albums" => Array("albumsOffset", "albumsSort", "albumsDir"),
	"album" => Array("albumId"),
	"videos" => Array("videosOffset", "videosSort", "videosDir"),
	"video" => Array("videoId"),
	"mags" => Array("magsOffset", "magsSort", "magsDir"),
	"mag" => Array("magId")
);

//	blocks dependencies
$g_depends = Array(
	"album" => Array("albums"),
	"video" => Array("videos"),
	"mag" => Array("mags")
);




class CLoggedPage extends CHtmlBlock
{
	function init()
	{

		if (get_session("_admin") != "admin")
		{
			header("Location: index.php?mes=login&" . get_params() . "\n");
			exit;			
		}

		parent::init();
	}

}



?>