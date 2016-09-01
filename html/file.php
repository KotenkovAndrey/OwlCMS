<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../core/app.php";
$site_config= OwlConfig::getInstance();
$secret_key=owlrequest('file_sk');
$is_admin=owlrequest('file_is_admin','int',0);
$ctype=owlrequest('file_ctype','str','image');
if($is_admin){
	if($secret_key!=$site_config->secret_admin){
		die('{"error":true,"reason":"You are cheater!!!"}');
	}
	$user= OwlUser::getInstance();
	if($user->access_level<500){
		die('{"error":true,"reason":"You do not have access to upload file."}');
	}
}
else {
	if(!$site_config->frontend_upload){
		die('{"error":true,"reason":"Upload files from site is disabled."}');
	}
}

switch ($ctype) {
	case 'image':
		$c=new OwlImage();
		print $c->ajaxUpload();
		break;
	case 'del_image':
		$c=new OwlImage();
		print $c->deleteUploadImage();
		break;

	default:
		die('{"error":true,"reason":"Unknown upload type."}');
		break;
}
?>

