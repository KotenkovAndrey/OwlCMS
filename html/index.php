<?php
//$mtime = microtime(true);
require_once "../core/app.php";
$router=OwlRouter::getInstance();
$user=OwlUser::getInstance();
$cache=OwlCache::getInstance($router->route_for_cache);
$html=$cache->getCache();
if($html){
	echo $html;
	//$mtime2 = microtime(true);
	//$speed = $mtime2 - $mtime;
	//print '<!-- Время выпонения скрипта: '.$speed.' -->';
	exit(0);
}
//print_r($user);
$page=new OwlPage();

$page->addCss('/css/bootstrap.min.css');
$page->addCss('/css/font-awesome.min.css');
$page->addJs('/js/jquery-1.11.3.min.js');
$page->addJs('/bootstrap/js/bootstrap.min.js');

if($router->isAdminPanel()) {
	$page->addCss('/css/admin.css');
	$page->addCss('/css/jquery.fileupload.css');
	$page->addJs('/js/vendor/jquery.ui.widget.js');
	$page->addJs('/js/jquery.iframe-transport.js');
	$page->addJs('/js/jquery.fileupload.js');
	$page->addJs('/ckeditor/ckeditor.js');
	$page->addJs('/js/admin.js');
}
else {
	$page->addCss('/css/style.css');
	$page->addJs('/js/main.js');

}


$page->render();
//$mtime2 = microtime(true);
//$speed = $mtime2 - $mtime;
//print '<!-- Время выпонения скрипта: '.$speed.' -->';
?>

