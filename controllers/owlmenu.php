<?php
require_once PATH . "models/owlmenu.php";

class OwlMenu extends OwlController{

	private $user;

	function __construct($type = ''){
		if($type == ''){
			$this->method = 'mainMenu';
		}
		$this->list = 'OwlMenuList';
		$this->model = 'OwlMenuItem';
		$this->category_list = 'OwlMenuCategoryList';
		$this->category_model = 'OwlMenuCategoryItem';
		$this->clear_all_cache = true;
		$this->user = OwlUser::getInstance();
	}

	public function mainMenu(){
		if(LANG != 'en'){
			$this->list = new OwlMenuList(1, false, " lang!='en'");
		}
		else {
			$this->list = new OwlMenuList(1, false, " lang='en'");
		}
		$html = '';
		include PATH . "template/main_menu.php";
		return $html;
	}

}
?>