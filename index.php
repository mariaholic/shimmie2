<?php
// set up and purify the environment
define("DEBUG", false);
define("VERSION", '2.1.1-svn');

if(DEBUG) {
	error_reporting(E_ALL);
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_BAIL, 1);
}

if(version_compare(PHP_VERSION, "5.0.0") == -1) {
	print <<<EOD
Currently Shimmie 2 doesn't support versions of PHP lower than 5.0.0. Please
either upgrade your PHP, or tell Shish that PHP 4 support is a big deal for
you...
EOD;
	exit;
}

function stripslashes_r($arr) {
	return is_array($arr) ? array_map('stripslashes_r', $arr) : stripslashes($arr);
}
if(get_magic_quotes_gpc()) {
	$_GET = stripslashes_r($_GET);
	$_POST = stripslashes_r($_POST);
	$_COOKIE = stripslashes_r($_COOKIE);
}


// load base files
$files = array_merge(glob("core/*.php"), glob("core/*/*.php"), glob("ext/*/main.php"));
foreach($files as $filename) {
	require_once $filename;
}


// connect to database
$database = new Database();
$database->db->fnExecute = '_count_execs';
$config = new Config($database);


// load the theme parts
$_theme = $config->get_string("theme", "default");
if(!file_exists("themes/$_theme")) $_theme = "default";
require_once "themes/$_theme/page.class.php";
require_once "themes/$_theme/layout.class.php";
require_once "themes/$_theme/themelet.class.php";

$themelets = glob("ext/*/theme.php");
$custom_themelets = glob("themes/$_theme/*.theme.php");
if($custom_themelets) $themelets = array_merge($themelets, $custom_themelets);
foreach($themelets as $filename) {
	require_once $filename;
}


// start the page generation waterfall
$page = new Page();
$user = _get_user();
send_event(new InitExtEvent());
send_event(_get_page_request($page));
$page->display();
?>
