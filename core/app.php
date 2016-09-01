<?php
session_start();
define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/../');
require_once "functions.php";
require_once "sysconfig.php";
$config = OwlConfig::getInstance();
if($config->offline) {
	die($config->offline_message);
}

define('LANG_PATH', PATH . 'language/' . $config->lang . '/');
define('LANG', $config->lang);
$LANG = array();
$COMPONENTS = array(
	"404 page" 			=> array("controller" => "OwlHtml", "template" => "404"),
	"login" 			=> array("controller" => "OwlStudioUsers", "method" => "loginForm"),
	"registration" 		=> array("controller" => "OwlStudioUsers", "method" => "registrationForm"),
	"lk" 				=> array("controller" => "OwlStudioUsers", "method" => "userPage"),
	"quit" 				=> array("controller" => "OwlStudioUsers", "method" => "quit"),
	"main page pills" 	=> array("controller" => "OwlstudioMainPagePills", "method" => "getModule"),
	"articles" 			=> array("controller" => "OwlstudioArticle"),
	"projects" 			=> array("controller" => "OwlstudioProjects"),
	"work" 				=> array("controller" => "OwlstudioWork"),
	"docs" 				=> array("controller" => "OwlHtml", "template" => "docs"),
	"contacts" 			=> array("controller" => "OwlstudioContacts"),
	"contact form" 		=> array("controller" => "OwlstudioContactForm")
);

require_once LANG_PATH . "system.php";
require_once "db.php";
require_once "cache.php";
require_once "image.php";
require_once "controller.php";
require_once "model.php";
require_once "user.php";
require_once "router.php";
require_once "page.php";
?>