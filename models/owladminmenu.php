<?php

class OwlAdminMenuItem extends OWlModel{
	public $id;
	public $parent_id;
	public $name;
	public $alias;
	public $title;
	public $controller;
	public $method;
	public $access_level;
	public $icon;

	protected $db_table = 'admin_menu';
	protected $db_struct = array(
		'parent_id' 	=> 'int',
		'alias' 		=> 'str',
		'title' 		=> 'str',
		'controller' 	=> 'str',
		'method' 		=> 'str',
		'access_level' 	=> 'int',
		'icon' 			=> 'str'
		);
}

class OwlAdminMenuList extends OWlList{
	protected $db_table = 'admin_menu';
	protected $db_struct = array(
		'parent_id' 	=> 'int',
		'alias' 		=> 'str',
		'title' 		=> 'str',
		'controller' 	=> 'str',
		'method' 		=> 'str',
		'access_level' 	=> 'int',
		'icon' 			=> 'str'
		);
}
?>