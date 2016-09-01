<?php
class OwlUser extends OwlModel{
    public $access_level;
    public $login;
    public $email;
    public $sex;
    public $bdate;
    public $rdate;
    public $lastlogin;

    protected static $_instance;
    protected $db_table = 'users';
    protected $db_struct = array(
        'access_level' => 'int',
        'name' => 'str',
        'login' => 'str',
        'email' => 'str',
        'sex' => 'str',
        'bdate' => 'str',
        'rdate' => 'str',
        'lastlogin' => 'str',
        'password' => 'str',
        'token' => 'str',
        );

    protected $password;
    protected $token;
 
    public function __construct($id = 0){
        $this->db_struct = array_merge($this->db_struct_base, $this->db_struct);
        $this->db = OwlDb::getInstance()->mysqli;
        $this->id = 0;
        $this->access_level = 0;
        $this->name = 'guest'; 
        if($id > 0)$this->getItem($id);
        else $this->auth();
    }

    public static function getInstance($id = 0) {
        if (null === self::$_instance) {
            self::$_instance = new self($id);
        }
        return self::$_instance;
    }

    private function setPassword($p){
        $this->password = md5($p);
    }

    private function setToken(){
        $this->token = md5($this->password . $this->login . $this->id);
        setcookie('user_id', $this->id, time() + 8640000, '/');
        setcookie('user_token', $this->token, time() + 8640000, '/');
        $this->updateItem();
    }

    private function registration(){
        $login = owlrequest('login');
        $email = owlrequest('email', 'str', '');
        $password = md5(owlrequest('password'));
        $this->where = "login='$login' or email='$email'";
        $this->getItem();
        if($this->id > 0){
            return false;
        }
        $this->login = $login;
        $this->name = $login;
        $this->email = $email;
        $this->password = $password;
        $this->addItem();
        $this->where = "login='$login' and password='$password'";
        $this->getItem();
        $this->setToken();
    }

    public function quit(){
        setcookie('user_id', '', time() - 8640000, '/');
        setcookie('user_token', '', time() - 8640000, '/');
        unset($_SESSION['OwlUserId']);
    }

    private function auth(){
        $form = owlrequest('formname');
        if($form == 'login'){
            $login = owlrequest('login');
            $password = md5(owlrequest('password'));
            $this->where = "login='$login' and password='$password'";
            $this->getItem();
            if($this->id > 0)$this->setToken();
        }
        else if(isset($_SESSION['OwlUserId']) and $_SESSION['OwlUserId'] > 0){
            $this->id = $_SESSION['OwlUserId'];
            if(!$this->getItem()){
                $this->id = 0;
                unset($_SESSION['OwlUserId']);
            }
        }
        else if(isset($_COOKIE['user_id']) and isset($_COOKIE['user_token'])){
            $this->id = $_COOKIE['user_id'] * 1;
            $this->where = "and token='" . $_COOKIE['user_token'] . "'";
            if(!$this->getItem()){
                $this->id = 0;
            }
            $_SESSION['OwlUserId'] = $this->id;            
        }
        else if($form == 'registration'){
            $this->registration();
        }
    }

    public function test(){
        $html = '<hr>';
        $html .= tag('h2', 'Testing ' . get_class($this));
        $c = array('id' => 'int', 'access_level' => 'int', 'name' => 'string', 'login' => 'string', 'sex' => 'string', 'bdate' => 'string', 'rdate' => 'string', 'lastlogin' => 'string', 'published' => 'int', 'ordering' => 'int', 'list_id' => 'int');
        foreach ($c as $k=>$v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v,varTest($this->$k, $v));
        }
        //check struct of database table
        if ($result = $this->db->query("DESCRIBE {$this->db_table}")) {
            if($result->num_rows){
                $cols = array();
                while($item = $result->fetch_object()){
                    $cols[] = $item->Field;
                    $r = array("fail" => false);
                    if(!isset($this->db_struct[$item->Field])){
                        $r['fail'] = true;
                        $r['reason'] = 'Not set ' . $item->Field . ' in $db_struct';
                    }
                    $html .= printTest(get_class($this) . ':: field ' . $item->Field . ' of database table ' . $this->db_table, $r);
                }       
                $r = array("fail" => false);
                if(count($this->db_struct) != count($cols)){
                    $r['fail'] = true;
                    $r['reason'] = 'Not equal count of fields';
                }
                $html .= printTest(get_class($this) . ':: count of fields in table and $db_struct', $r);            
            }
            else {
                $html .= '<div class="alert alert-danger">Не удалось получить список полей из таблицы ' . $this->db_table . '</div>';
            }   
            $result->close();
        }
        else {
            $html .= '<div class="alert alert-danger">Не удалось получить список полей из таблицы ' . $this->db_table . '</div>';
        }   
        return $html;
    }
}
?>