<?php
class OwlPage {
	public $sitename;
	public $description;
	public $keywords;
	public $pagetitle;
	public $title;

	public $debug = '';

	private $css = array();
	private $js = array();
	private $title_type;
	private $title_separator;
	private $body = '';
	private $cache;
	private $site_config;
	private $router;

	function __construct()	{
		$this->site_config 		= OwlConfig::getInstance();
		$this->sitename 		= $this->site_config->sitename;
		$this->description 		= $this->site_config->description;
		$this->keywords 		= $this->site_config->keywords;
		$this->title_type 		= $this->site_config->title_type;
		$this->title_separator  = $this->site_config->title_separator;
		$this->pagetitle 		= $this->site_config->title;
		$this->cache 			= OwlCache::getInstance();
		$this->router 			= OwlRouter::getInstance();
		$this->setTitle();
	}

	public function setTitle($t = ''){
		if($t != '')$this->pagetitle = $t;
		switch($this->title_type){
        	case 's': $this->title = $this->sitename; break;
        	case 'p': $this->title = $this->pagetitle; break;
        	case 'p+s': $this->title = $this->pagetitle . $this->title_separator . $this->sitename; break;
        	case 's+p':
        	default : $this->title = $this->sitename . $this->title_separator . $this->pagetitle;
    	}
	}

	public function addJs($url){
		$this->js[] = $url;
	}
	
	public function addCss($url){
		$this->css[] = $url;
	}

	public function addBody($html,$tag = '',$class = '',$id = '',$style = ''){
		if($tag != '') {
			$this->body .= '<' . $tag;
			if($class)$this->body .= ' class="' . $class . '"';
			if($id)$this->body .= ' id="' . $id . '"';
			if($style)$this->body .= ' style="' . $style . '"';
			$this->body .= '>' . $html . '</' . $tag . '>';
		}
		else $this->body .= $html;
	}	

	public function getHead(){
		$html = '<head>';
		$html .= '<title>' . $this->title . '</title>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		$html .= '<meta name="keywords" content="' . $this->keywords . '">';
		$bu = '';
		if(LANG != 'ru')$bu = 'en/';
		$html .= '<base href="http://' . $_SERVER['SERVER_NAME'] . '/' . $bu . '">';
		$html .= '<meta name="description" content="' . $this->description . '">';
		foreach($this->css as $url)$html .= '<link rel="stylesheet" href="' . $url . '">';
		$html .= '<link rel="icon" type="image/x-icon" href="/favicon.ico">';
    	$html .= '</head>';
    	return $html;
	}

	public function getJs(){
		$html = '';
		foreach($this->js as $url)$html .= '<script type="text/javascript" src="' . $url . '"></script>';
		return $html;
	}

	public function render(){
		global $COMPONENTS;
		if($this->router->isAdminPanel()){
			return $this->renderAdmin();			
		}
		require_once "../controllers/owlmenu.php";
		$menu = new OwlMenu();
		$mainmenu = $menu->render();
		if($this->router->keywords != '')
			$this->keywords = $this->router->keywords;
		if($this->router->description != '')
			$this->description = $this->router->description;
		if($this->router->title != '')
			$this->setTitle($this->router->title);
		$chtml = '';
		if(!isset($this->router->component) or !isset($COMPONENTS[$this->router->component])){
			$chtml = getComponent('404 page');
		}
		else{
			$chtml = getComponent($this->router->component);
		}
		$layout = file_get_contents(PATH . "template/layout.php");
		$layout = str_replace('{$component}', $chtml, $layout);
		$layout = str_replace('{$mainmenu}', $mainmenu, $layout);
		$layout = str_replace('{$TEXT_WE_SOCIAL}', lang('TEXT_WE_SOCIAL'), $layout);
		if(LANG == 'ru')$select_lang = '<a href="/en/">English version</a>';	
		else $select_lang = '<a href="/">Русская версия</a>';
		$layout = str_replace('{$SELECT_LANG}', $select_lang, $layout);

		$this->addBody($layout);
		$html = '<!DOCTYPE html> <html lang=' . LANG . '>' . $this->getHead() . '<body>' . $this->body . '</body>' . $this->getJs() . '</html>';
		//кэширование страницы если не запрещено, добавить проверку
		$this->cache->setCache($html);
		if($this->site_config->debug){
			echo lang('Debug mode') . '<br>';
			if(!$this->site_config->cache) echo lang('Cache is disabled');
		}
		echo $html;
	}

	private function renderAdmin(){
		require_once "../controllers/owladmin.php";
		$admin = new OwlAdmin();
		//$admin_route=array_slice($this->router->route_array,3);
		if($this->router->params['controller'] and $this->router->params['controller'] != 'OwlAdmin'){
			require_once "../controllers/" . strtolower($this->router->params['controller']) . ".php";
			$c = new $this->router->params['controller']();
			if(isset($this->router->params['action'])){
				if($this->router->params['action'] == 'category'){
					$c->setCategory();
				}
			}
			$m = $this->router->params['method'];	
			if($m != '')
				$chtml = $c->$m();
			else 	
				$chtml = $c->adminRender();
		}
		else{
			if($this->router->params['method'] != '')
				$admin->setMethod($this->router->params['method']);
			$chtml = $admin->render();	
		}
		
		$menu = $admin->menu();
		$layout = file_get_contents(PATH . "template/admin.php");
		$layout = str_replace('{$component}', $chtml, $layout);
		$layout = str_replace('{$menu}', $menu, $layout);
		$layout = str_replace('{$pagetitle}', owlval($this->router->params['title'], 'Панель управления сайтом'), $layout);
		$layout = str_replace('{$secret}', $this->site_config->secret_admin, $layout);
		$this->addBody($layout);
		$html = '<html>' . $this->getHead() . '<body>' . $this->body . '</body>' . $this->getJs() . '</html>';
		echo $html;
	}

	public function test(){
		global $COMPONENTS;
		$html = '';
		if(!isset($this->router->component) or !isset($COMPONENTS[$this->router->component])){
			$html .= '<div class="alert alert-danger">OwlPage <h4>Not set component!</h4></div>'; 
		}
		else{
			require_once  PATH . "controllers/" . strtolower($COMPONENTS[$this->router->component]['controller']) . ".php";
			$c = new $COMPONENTS[$this->router->component]['controller']();
			$html .= $c->test();
		}
		return $html;
	}
}
?>