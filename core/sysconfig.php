<?php
class OwlConfig{
	public $offline = false;
	public $offline_message = 'Сайт закрыт на техническое обслуживание.<br />Пожалуйста, зайдите позже.';
	public $cache = false;
	public $sitename = 'Name of your web site';
	public $debug = false;
	public $debug_log = false;
	public $frontend_upload = false;
	public $db_host = '';
	public $db_user = '';
	public $db_pass = '';
	public $db_base = '';
	public $secret_admin = '';
	public $description = '';
	public $keywords = '';
	public $title_type = 's+p';
	public $title_separator = ' - ';
	public $title = '';
	public $lang = 'ru';
	public $acepted_langs = array('ru','en');

	protected static $_instance;

	private function __construct(){
		$route = owlrequest('route','str','');
		$route_array = explode('/', $route);
		foreach ($this->acepted_langs as $l) {
			if($route_array[0] == $l) {
			    $this->lang = $l;
			}
		}
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
        $c = array('offline' => 'bool', 'offline_message' => 'string', 'cache' => 'bool', 'sitename' => 'string', 'debug' => 'bool', 'debug_log' => 'bool', 'frontend_upload' => 'bool', 'db_host' => 'string',	'db_user' => 'string', 'db_pass' => 'string', 'db_base' => 'string', 'secret_admin' => 'string', 'description' => 'string', 'keywords' => 'string', 'title_type' => 'string', 'title_separator' => 'string', 'title' => 'string', 'lang' => 'string', 'acepted_langs' => 'array');
        foreach ($c as $k=>$v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v, varTest($this->$k, $v, true));
        }
        return $html;
    }

}
?>