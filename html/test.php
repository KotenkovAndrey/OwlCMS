<?php
$mtime = microtime(true);
$project_name=$_SERVER['HTTP_HOST'];

require_once "../core/app.php";
require_once "../controllers/owlmenu.php";
$router=OwlRouter::getInstance();
$cache=OwlCache::getInstance($router->route);
$user=OwlUser::getInstance();
$page=new OwlPage();
$menu= new OwlMenu();


echo '<html><head><title>OwlTest - '.$project_name.'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><meta name="keywords" content="OwlTestPHP"><meta name="description" content="Project created by OwlStudio"><link rel="stylesheet" href="/css/bootstrap.min.css"><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"></head><body><header><h1>Test of project '.$project_name.'</h1></header>';

// Tests of dlibal functions
echo '<hr>';
echo tag('h2','Testing functions');
echo printTest('owlstrClear() #1',varTest(owlstrClear(' <span>asd</span> ',false),'string',true,'<span>asd</span>'));
echo printTest('owlstrClear() #2',varTest(owlstrClear('<span>asd</span>'),'string',true,'asd'));

echo printTest('tag() #1',varTest(tag(),'bool',true,false,true));
echo printTest('tag() #2',varTest(tag("div"),'string',true,'<div></div>'));
echo printTest('tag() #3',varTest(tag("div","test"),'string',true,'<div>test</div>'));
echo printTest('tag() #4',varTest(tag("div","test","t1"),'string',true,'<div class="t1">test</div>'));
echo printTest('tag() #5',varTest(tag("div","test",false,"id_123"),'string',true,'<div id="id_123">test</div>'));
echo printTest('tag() #6',varTest(tag("div","test",0,0,"border:1px red solid;"),'string',true,'<div style="border:1px red solid;">test</div>'));

echo $config->test();
echo $router->test();
echo $cache->test();
echo $user->test();
echo $menu->test();
echo $page->test();


echo tag('h2','Testing components');
foreach ($COMPONENTS as $key => $value) {
	echo printTest('Component '.$key,componentTest($key));
    $fname=PATH."controllers/".strtolower($value['controller']).".php";
    require_once $fname;
    $c = new $value['controller']();
	echo $c->test();    
}

echo tag('h2','Session');
echo tag('pre',print_r($_SESSION,true));

$mtime2 = microtime(true);
$speed = $mtime2 - $mtime;
echo '<hr>';
echo tag('h4','Time execute of tests: '.$speed.' seconds');
echo '</body><script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script><script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script></html>
';
?>
<script type="text/javascript">
var test={
	total : 0,
	total_ok : 0,
	total_fail : 0,
	countTotal : function (){
		$('.label-success').each(function (){
			test.total_ok++;
			});
		$('body').append('<h4>Passed tests: '+this.total_ok+'</h4>');

		$('.alert-danger').each(function (){
			test.total_fail++;
			});
		$('body').append('<h4>Fail tests: '+this.total_fail+'</h4>');

		this.total=this.total_ok+this.total_fail;
		$('body').append('<h4>Total tests: '+this.total+'</h4>');
	}
};

$(function() {
	test.countTotal();
});
</script>