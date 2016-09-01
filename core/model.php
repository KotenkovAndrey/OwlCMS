<?php
class OwlModel {
	public $id = 0;
	public $name = '';
	public $published = 1;
	public $ordering = 1;
	public $list_id = 1;
	public $debug = '';
	public $where = '';

	public $form_struct = array();
	public $form_struct_base = array(
		'id' 		=> array('type' => 'hidden'),
		'list_id' 	=> array('type' => 'hidden'),
		'name' 		=> array('type' => 'string', 'required' => true),
		'published' => array('type' => 'select', 'required' => true, 'values' => array('published' => 1, 'not published' => 0)),
		'ordering' 	=> array('type' => 'hidden'));

	protected $site_config;
	protected $db;
	protected $db_table = '';
	protected $db_struct = array();
	protected $db_struct_base = array('id' => 'int', 'name' => 'str', 'published' => 'int', 'ordering' => 'int', 'list_id' => 'int');

	public function __construct($id = 0){
		$this->site_config 	= OwlConfig::getInstance();
		$this->db_struct 	= array_merge($this->db_struct_base,$this->db_struct);
		$this->form_struct 	= array_merge($this->form_struct_base,$this->form_struct);
		$this->db 			= OwlDb::getInstance()->mysqli;
		$this->id 			= $id;
		if($id){
			$this->getItem();
		}
	}
	
	public function getItem(){
		$return = true;
		$sql = "SELECT * FROM {$this->db_table}";
		if($this->id > 0) $sql .= " where id = " . $this->id;
		if($this->where != '' and $this->id > 0) $sql .= " " . $this->where;
		else if($this->where!='' and $this->id<1) $sql.= " where " . $this->where;
		$sql .= " limit 1";
		$item = OwlDb::getOneRow($sql);
		if(!is_object($item)){
			return false;
		}
		foreach ($this->db_struct as $key => $value) {
			if(!property_exists(get_class($this),$key)) print ' ***' . $key . '*** ';
			if($value == 'int') $this->$key=intval($item->$key);
			else $this->$key = $item->$key;

		}
		$this->where = '';
		return $return;
	}

	public function getLastOrdering(){
		$sql = "SELECT ordering FROM {$this->db_table} order by ordering desc limit 1";
		$item = OwlDb::getOneRow($sql);
		$this->ordering = $item->ordering + 1;
	}

	public function normalizeItemOrdering(){
		$sql = "SELECT id FROM {$this->db_table} order by ordering asc";
		if ($result = $this->db->query($sql)) {
			if($result->num_rows){
				$i = 1;
				while($item = $result->fetch_object()){
					$this->db->query("update {$this->db_table} set ordering = $i where id = {$item->id} limit 1");
					$i++;	
				}		
				
			}
			else {
				if($this->site_config->debug) echo $this->db->error;
			}	
			$result->close();
		}
		else {
			if($this->site_config->debug) echo 'Оишбка выполнения запроса ' . $sql;
		}
	}

	public function addItem(){
		if($this->name == '')return false;
		if($this->list_id < 1)
			$this->list_id = 1;
		$this->getLastOrdering();
		$set = "";		
		foreach ($this->db_struct as $key => $value) {
			if($key == 'id') continue;
			if($key == 'name') $set = "name = '{$this->name}'";
			else if($key == 'date_create') $set .= ", $key = NOW()";
			else if($key == 'date_edit') $set .= ", $key = NOW()";
			else if($value == 'int') $set .= ", $key = {$this->$key}";
			else $set .= ", $key = '{$this->$key}'";
		}
		if ($this->db->query("insert into {$this->db_table} set $set") === TRUE) {
    		if($this->site_config->debug) echo "Record insert successfully for id" . $this->id;
    		return true;
		} 
		else {
    		if($this->site_config->debug) echo "Error updating record: " . $this->db->error;
    		return false;
		}
	}

	public function updateItem(){
		if(!$this->id) return false;
		$set = "";		
		foreach ($this->db_struct as $key => $value) {
			if($key == 'id') continue;
			if($key == 'name') $set = "name = '{$this->name}'";
			else if($key == 'date_edit') $set .= ", $key = NOW()";
			else if($value == 'int') $set .= ", $key = {$this->$key}";
			else $set .= ", $key = '{$this->$key}'";
		}
		if ($this->db->query("update {$this->db_table} set $set where id = {$this->id} limit 1") === TRUE) {
    		return true;
		} 
		else {
    		return false;
		}
	}

	public function deleteItem(){
		if(!$this->id) return false;
		if ($this->db->query("delete from {$this->db_table} where id = {$this->id} limit 1") === TRUE) {
    		if($this->site_config->debug) echo "Record delete successfully for id" . $this->id;
    		$this->normalizeItemOrdering();
    		return true;
		} 
		else {
    		if($this->site_config->debug) echo "Error delete record: " . $this->db->error;
    		return false;
		}
	}

	public function setItemFromPost(){
		foreach ($this->db_struct as $key => $value) {
			$this->$key = owlrequest($key, $value);
		}
	}

	public function submitEditFormFromList(){
		$this->setItemFromPost();
		if($this->id > 0)$this->updateItem();
		else {
			$this->addItem();
		}
		return true;
	}


	public function subAddFunction(){
		//function for children
		return true;
	}

	public function subCopyFunction(){
		//function for children
		return true;
	}

	public function subDeleteFunction(){
		//function for children
		return true;
	}

	public function subTest(){
        $html = '';
        $c = array('id' => 'int', 'name' => 'string', 'published' => 'int', 'ordering' => 'int', 'list_id' => 'int', 'form_struct' => 'array', 'form_struct_base' => 'array', 'db_struct' => 'array', 'db_struct_base' => 'array', 'db_table' => 'string');
        foreach ($c as $k => $v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v, varTest($this->$k, $v));
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

    public function test(){
        return $this->subTest();
    }

}

class Owllist {
	public $list_id;
	public $items = array();
	public $where;
	public $order;
	public $limit;
	public $show_all = false;
	public $debug = '';
	public $admin_extra_cols = array();


	protected $db;
	protected $db_table = '';
	protected $db_struct = array();
	protected $db_struct_base = array('id' => 'int', 'name' => 'str', 'published' => 'int', 'ordering' => 'int');
	protected $site_config;

	public function __construct($list_id = 1, $sa = false, $where = '')	{
		$this->site_config 	= OwlConfig::getInstance();
		$this->db 			= OwlDb::getInstance()->mysqli;
		$this->db_struct 	= array_merge($this->db_struct_base,$this->db_struct);
		$this->list_id 		= $list_id;
		$this->show_all 	= $sa;
		$this->order 		= 'ordering asc';
		$this->where 		= $where;
		$this->getList();
	}
	
	public function getList(){
		$sql = "SELECT * FROM {$this->db_table}";
		if($this->list_id > 0 and $this->where != '')$this->where .= " and list_id = " . $this->list_id;
		if($this->list_id > 0 and $this->where == '')$this->where .= " list_id = " . $this->list_id;
		if(!$this->show_all and $this->where != '')$this->where .= " and published = 1 ";
		if(!$this->show_all and $this->where == '')$this->where .= " published = 1 ";		
		if($this->where != '')$sql .= " where " . $this->where;
		$sql .= " order by " . $this->order;
		if($this->limit != '')$sql .= " limit " . $this->limit;
		if ($result = $this->db->query($sql)) {
			if($result->num_rows){
				$this->items = array();
				$i = 0;
				while($item = $result->fetch_object()){
					foreach ($this->db_struct as $key => $value) {
						$this->items[$i][$key] = $item->$key;
					}
					$i++;	
				}		
				
			}
			else {
				if($this->site_config->debug) echo 'Не удалось получить список с ID ' . $id . ' из таблицы ' . $this->db_table;
			}	
			$result->close();
		}
		else {
			if($this->site_config->debug) echo 'Оишбка выполнения запроса ' . $sql;
		}
	}

	public function getLastOrdering(){
		$return = 0;
		$sql = "SELECT ordering FROM {$this->db_table} where list_id = {$this->list_id} order by ordering desc limit 1";
		$item  = OwlDb::getOneRow($sql);
		return $item->ordering;
	}

	public function upItemOrdering($id){
		$current_ordering = 0;
		$new_ordering = 0;
		$id2 = 0;
		
		//получение current ordering
		$sql = "SELECT ordering, list_id FROM {$this->db_table} where id = $id limit 1";
		$item = OwlDb::getOneRow($sql);
		$current_ordering = $item->ordering;
		$this->list_id = $item->list_id;

		//получение нового ordering
		$sql = "SELECT id, ordering FROM {$this->db_table} where ordering < $current_ordering and list_id = {$this->list_id} order by ordering desc limit 1";
		$item = OwlDb::getOneRow($sql);
		$new_ordering = $item->ordering;
		$id2 = $item->id;

		//обновляем текущую запись
		if ($this->db->query("update {$this->db_table} set ordering = $new_ordering where id = $id limit 1") !== TRUE) {
 	  		if($this->site_config->debug) echo "Error updating record: " . $this->db->error;
    		return false;
		}
		//обновляем вторую запись
		if ($this->db->query("update {$this->db_table} set ordering = $current_ordering where id = $id2 limit 1") !== TRUE) {
    		if($this->site_config->debug)echo "Error updating record: " . $this->db->error;
    		return false;
		}
	}

	public function downItemOrdering($id){
		$current_ordering = 0;
		$new_ordering = 0;
		$id2 = 0;
		
		//get current ordering
		$sql = "SELECT ordering, list_id FROM {$this->db_table} where id = $id limit 1";
		$item = OwlDb::getOneRow($sql);
		$current_ordering = $item->ordering;
		$this->list_id = $item->list_id;

		//get new ordering
		$sql = "SELECT id,ordering FROM {$this->db_table} where ordering > $current_ordering and list_id = {$this->list_id} order by ordering asc limit 1";
		$item = OwlDb::getOneRow($sql);
		$new_ordering = $item->ordering;
		$id2 = $item->id;
		//update row with $id
		if ($this->db->query("update {$this->db_table} set ordering = $new_ordering where id = $id limit 1") !== TRUE) {
    		if($this->site_config->debug) echo "Error updating record: " . $this->db->error;
    		return false;
		}
		//update row with $id2
		if ($this->db->query("update {$this->db_table} set ordering = $current_ordering where id = $id2 limit 1") !== TRUE) {
    		if($this->site_config->debug)echo "Error updating record: " . $this->db->error;
    		return false;
		}
	}

	public function normalizeItemOrdering(){
		$sql = "SELECT id FROM {$this->db_table} where list_id = {$this->list_id} order by ordering asc";
		if ($result = $this->db->query($sql)) {
			if($result->num_rows){
				$i = 1;
				while($item = $result->fetch_object()){
					$this->db->query("update {$this->db_table} set ordering = $i where id = {$item->id} limit 1");
					$i++;	
				}		
			}
			else {
				if($this->site_config->debug) echo $this->db->error;
			}	
			$result->close();
		}
		else {
			if($this->site_config->debug) echo 'Оишбка выполнения запроса ' . $sql;
		}
	}

	public function mirrorItemOrdering(){
		$sql="SELECT id FROM {$this->db_table} where list_id = {$this->list_id} order by ordering desc";
		if ($result = $this->db->query($sql)) {
			if($result->num_rows){
				$i = 1;
				while($item = $result->fetch_object()){
					$this->db->query("update {$this->db_table} set ordering = $i where id = {$item->id} limit 1");
					$i++;	
				}						
			}
			else {
				if($this->site_config->debug) echo $this->db->error;
			}	
			$result->close();
		}
		else {
			if($this->site_config->debug) echo 'Оишбка выполнения запроса ' . $sql;
		}
	}
	
	public function subTest(){
        $html = '';
        $c = array('items' => 'array', 'admin_extra_cols' => 'array', 'show_all' => 'bool', 'list_id' => 'int', 'db_struct' => 'array', 'db_struct_base' => 'array', 'db_table'=> 'string');
        foreach ($c as $k => $v) {
            $html .= printTest(get_class($this) . '::' . $k . ' is ' . $v, varTest($this->$k, $v));
        }
        return $html;
    }

    public function test(){
        return $this->subTest();
    }

}

?>