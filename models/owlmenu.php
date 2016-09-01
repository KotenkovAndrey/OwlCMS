<?php

class OwlMenuItem extends OWlModel{
	public $parent_id;
	public $alias;
	public $title;
	public $description;
	public $keywords;
	public $component;
	public $access_level;
	public $lang;

	protected $db_table = 'menu';
	protected $db_struct = array(
		'parent_id' 	=> 'int',
		'alias' 		=> 'str',
		"lang" 			=> "str",
		'title' 		=> 'str',
		'description' 	=> 'str',
		'keywords' 		=> 'str',
		'component' 	=> 'str',
		'access_level' 	=> 'int'
		);

	public $form_struct = array(
		'parent_id' 	=> array('type' => 'hidden'),
		'alias' 		=> array('type' => 'string'),
		"lang" 			=> array("type" => "lang_select"),
		'title' 		=> array('type' => 'string'),
		'description' 	=> array('type' => 'textarea'),
		'keywords' 		=> array('type' => 'textarea'),
		'component' 	=> array('type' => 'component_list'),
		'access_level' 	=> array('type' => 'hidden')
		);

	public function subAddFunction(){
		//function for children
		if($this->alias == '')$this->alias = str_replace(' ', '_', $this->name);
		return true;
	}
	
	public function subCopyFunction(){
		$this->alias .= '_copy';
		return true;
	}
}

class OwlMenuList extends OWlList{
	protected $db_table = 'menu';
	protected $db_struct = array(
		'list_id' 		=> 'int',
		'parent_id' 	=> 'int',
		'name' 			=> 'str',
		"lang" 			=> "str",
		'alias' 		=> 'str',
		'title' 		=> 'str',
		'description' 	=> 'str',
		'keywords' 		=> 'str',
		'component' 	=> 'str',
		'access_level' 	=> 'int'
		);
	public $admin_extra_cols = array('lang' => 'lang');
	
}

class OwlMenuCategoryItem extends OWlModel{
	public $description;
	public $access_level=0;
	public $lang;
	public $form_struct = array(
		"description" 	=> array("type" => "htmleditor"),
		"access_level" 	=> array("type" => "hidden"),
		"lang" 			=> array("type" => "lang_select"),
		);

	protected $db_table = 'menu_category';
	protected $db_struct = array(
		"description" 	=> "html",
		"access_level" 	=> "int",
		"lang" 			=> "str",
		);

}

class OwlMenuCategoryList extends OWlList{
	protected $db_table = 'menu_category';
	protected $db_struct = array(
		"description" 	=> "html",
		"access_level" 	=> "int",
		"lang" 			=> "str",
		);

}


?>