<?php
class OwlRouter{

    public $route;
    public $route_array;
    public $route_for_cache;
    public $title;
    public $description;
    public $keywords;
    public $params;

    private $admin;

    protected static $_instance;
    protected $menu_item;
 
    private function __construct(){
        if($_SERVER['PHP_SELF'] == '/ajax.php'){
            $this->route = str_replace($_SERVER['HTTP_ORIGIN'] . '/', '', $_SERVER['HTTP_REFERER']);
            }
        else {
            $this->route = owlrequest('route', 'str', 'home');
        }
        $pos = strpos($this->route, 'ru/');
        $pos2 = strpos($this->route, 'en/');
        if(($pos !== false and $pos == 0) or ($pos2 !== false and $pos2 == 0)){
            $this->route = substr($this->route, 3);
            if($this->route == ''){
                $this->route = 'home';
            }
        }
        $this->route_for_cache = str_replace('/', '_SUB_', $this->route);
        $this->route_array = explode('/', $this->route);
        if($this->route_array[0] == 'admin'){
            $sc = OwlConfig::getInstance();
            if($this->route_array[1] == $sc->secret_admin){
                $this->admin = true;
                require_once "../models/owladminmenu.php";
                $this->menu_item = new OwlAdminMenuItem();
                $alias = $this->route_array[2] ? $this->route_array[2] : 'main';
                $this->menu_item->where = " alias='" . $alias . "'";
                $this->menu_item->getItem();   
                $this->params = array();
                $this->params['controller'] = owlval($this->menu_item->controller);
                $this->params['method'] = owlval($this->menu_item->method);
                $this->params['title'] = owlval($this->menu_item->title);
                if(isset($this->route_array[3]))$this->params['action'] = owlval($this->route_array[3]);
            }
            else {
                $this->admin = false; 
            }
        }
        else{
            require_once "../models/owlmenu.php";
            $this->menu_item = new OwlMenuItem();
            $this->menu_item->where = " alias='" . $this->route_array[0] . "'";
            $this->menu_item->getItem();
            $this->component = $this->menu_item->component;
            $this->title = $this->menu_item->title;
            $this->description = $this->menu_item->description;
            $this->keywords = $this->menu_item->keywords;
            $this->admin = false;
        }
    }

    public function isAdminPanel(){
        return $this->admin;
    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function test(){
        $html = '<hr>';
        $html .= tag('h2', 'Testing ' . get_class($this));
        $c = array('route' => 'string', 'route_array' => 'array', 'title' => 'string', 'description' => 'string', 'keywords' => 'string', 'component' => 'string');
        foreach ($c as $k=>$v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v, varTest($this->$k, $v, true));
        }
        return $html;
    }

}
?>