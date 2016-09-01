<?php
class OwlDb{
    public $mysqli;
    protected static $_instance;
    private $site_config;
 
    private function __construct(){
        $this->site_config = OwlConfig::getInstance();    
        $this->mysqli = new mysqli($this->site_config->db_host, $this->site_config->db_user, $this->site_config->db_pass, $this->site_config->db_base);
        if ($this->mysqli->connect_error) {
            die('Ошибка подключения (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
        }

        if (mysqli_connect_error()) {
            die('Ошибка подключения (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
        $this->mysqli->set_charset("utf8");
    }

    private function __clone(){
    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getOneRow($sql){
        if ($result = self::$_instance->mysqli->query($sql)) {
            if($result->num_rows){
                return $result->fetch_object();    
            }
            else {
                if(self::$_instance->site_config->debug) echo 'Не удалось получить строку из Зпроса: ' . $sql;
                return false;
            }   
            $result->close();
        }
        else {
            if(self::$_instance->site_config->debug) echo 'Оишбка выполнения запроса ' . $sql;
            return false;
        }
    }

}
?>