<?php
class OwlCache{

    public $clear_cache = false;
    public $disable_cache_this_page = false;
    
    protected static $_instance;
    protected $route;

    private $site_config;
 
    private function __construct($route = '') {
        if($route != '') {
            $this->route = $route;
        }
        $this->site_config = OwlConfig::getInstance();
    }

    private function __clone() {
    }

    public static function getInstance($route = '') {
        if (null === self::$_instance) {
            self::$_instance = new self($route);
        }
        return self::$_instance;
    }

    public function getCache() {
        if($this->clear_cache) {
            $this->clearCache();
            return false;
        }
        if(file_exists(PATH . '/cache/' . LANG . '_cache_' . $this->route . '.html') and $this->site_config->cache) {
            return file_get_contents(PATH . 'cache/' . LANG . '_cache_' . $this->route . '.html');
        }
        return false;
    }

    public function setCache($html) {
        if(!$this->site_config->cache or $this->disable_cache_this_page) {
            return false;
        }
        return file_put_contents(PATH.'cache/'.LANG.'_cache_'.$this->route.'.html',$html);
    }

    public function clearCache() {
        $files = glob(PATH . 'cache/*');
        $c = count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {      
                if (file_exists($file)) {
                    unlink($file);
                }   
            }
        }
    }

    public function clearControllerCache($str) {
        $files = glob(PATH . 'cache/*' . $str . '*');
        $c = count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {      
                if (file_exists($file)) {
                    unlink($file);
                }   
            }
        }
    }
    public function test() {
        $html = '<hr>';
        $html .= tag('h2', 'Testing ' . get_class($this));
        $c = array('clear_cache' => 'bool', 'disable_cache_this_page' => 'bool');
        foreach ($c as $k => $v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v, varTest($this->$k, $v, true));
        }
        return $html;
    }
}
?>