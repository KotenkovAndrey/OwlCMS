<?php
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { die(); }
header('Content-Type: text/html; charset=utf-8');
require_once "../core/app.php";
$id=owlrequest('id');
$act=owlrequest('act');
$secret_key=owlrequest('sk');
$controller=owlrequest('controller');
$model=owlrequest('model');
$list=owlrequest('list');
$method=owlrequest('method');
$admin_action=owlrequest('admin_action');
$ctype=owlrequest('ctype','str','controller');
/*
print '<br>id '.$id;
print '<br>act '.$act;
print '<br>secret_key '.$secret_key;
print '<br>controller '.$controller;
print '<br>model '.$model;
print '<br>list '.$list;
print '<br>method '.$method;
print '<br>ctype '.$ctype;
print '<br>';
*/
require_once "../controllers/".strtolower($controller).".php";
$c=new $controller();
print $c->ajax($id,$act,$method,$model,$list,$ctype,$secret_key,$admin_action);
//print_r($_SERVER);
?>

