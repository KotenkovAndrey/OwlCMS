<?php
class OwlController {
	public $debug = '';
	protected $site_config;

	protected $method;
	protected $model;
	protected $list;
	protected $template;
	protected $router;
	protected $image_settigns = array(
		'folder' 	=> '',
		'maxwidth' 	=> 0,
		'maxheight' => 0,
		'minwidth' 	=> 0,
		'minheight' => 0
		);
	protected $category_model;
	protected $category_list;
	protected $admin_action='';
	protected $user_guest_cache=false;
	protected $controller_cache=false;
	protected $clear_all_cache=false;
	protected $clear_controller_cache=true;

	function __construct()	{
	}

	public function render(){
		$html = '';
		if(method_exists($this, $this->method)){
			$handler = $this->method;
			$html .= $this->$handler();
		}
		else if($this->template != '')include PATH . 'template/' . $this->template . '.php';
		return $html;
	}

	public function setTemplate($t){
		$this->template = $t;
	}

	public function setMethod($m){
		$this->method = $m;
	}

	public function clearCache(){
		if($this->clear_all_cache){
			$cache = OwlCache::getInstance();
			$cache->clearCache();
			return true;
		}
	}

	public function ajax($id,$act,$method,$model = '',$list = '',$ctype = '',$secret_key = '',$admin_action = ''){
		$this->site_config = OwlConfig::getInstance();
		if($ctype == 'html' or $ctype == 'html' ){
			$html = $this->$method();
			return $html;
		}

		//start block
		if($ctype == 'admin_json'){
			if($secret_key != $this->site_config->secret_admin) return false;
			$html = $this->$method($id, $act);
			return $html;
		}

		$html = '<div id="' . get_class($this) . '">';
		if($ctype == 'admin'){
			if($secret_key != $this->site_config->secret_admin) return false;
			if($admin_action == 'category'){
				$this->setCategory();
			}
			$html .= $this->$method($id, $act);
		}		

		if($ctype == 'admin_list'){
			if($secret_key != $this->site_config->secret_admin) return false;
			$l = new $this->list();
			$html .= $l->$method($id,$act);
		}
		
		if($ctype == 'admin_model'){
			if($secret_key != $this->site_config->secret_admin) return false;
			$l = new $this->model();
			$html .= $l->$method($id,$act);
		}
		//end block
		$html .= '</div>';
		return $html;
	}

	protected function getAdminHiddenInputs(){
		$html = '<input type="hidden" name="sk" id="sk" value="' . $this->site_config->secret_admin . '">';
		$html .= '<input type="hidden" name="controller" id="controller" value="' . get_class($this) . '">';
		$html .= '<input type="hidden" name="model" id="model" value="' . $this->model . '">';
		$html .= '<input type="hidden" name="list" id="list" value="' . $this->list . '">';
		$html .= '<input type="hidden" name="admin_action" id="admin_action" value="' . $this->admin_action . '">';
		return $html;
	}

	public function adminRender(){
		$this->site_config = OwlConfig::getInstance();
		$html = '';
		if($this->list != ''){
			$html = '<div id="' . get_class($this) . '">';
			unset($_SESSION['OwlAdminCategoryId']);
			$html .= $this->getAdminTable();
			$html .= '</div>';
		}
		$html .= $this->getAdminHiddenInputs();
		$html .= '<div id="upload_image_file" style="display:none;">';
		$html .= '<form id="IUF" method="post" enctype="multipart/form-data">';
		$html .= '<input name="file_is_admin" type="hidden" value="1">';
		$html .= '<input name="file_ctype" type="hidden" value="image">';
		$html .= '<input name="file_sk" type="hidden" value="' . $this->site_config->secret_admin . '">';
		$html .= '<span class="btn btn-success fileinput-button">';
        $html .= '<i class="glyphicon glyphicon-plus"></i>';
        $html .= '<span>Select files...</span>';
        $html .= '<input id="fileupload" type="file" name="files">';
        $html .= '<input id="image_upload_folder" type="hidden" name="folder" value="' . $this->image_settigns['folder'] . '">';
        $html .= '<input id="image_upload_maxwidth" type="hidden" name="maxwidth" value="' . $this->image_settigns['maxwidth'] . '">';
        $html .= '<input id="image_upload_maxheight" type="hidden" name="maxheight" value="' . $this->image_settigns['maxheight'] . '">';
        $html .= '<input id="image_upload_minwidth" type="hidden" name="minwidth" value="' . $this->image_settigns['minwidth'] . '">';
        $html .= '<input id="image_upload_minheight" type="hidden" name="minheight" value="' . $this->image_settigns['minheight'] . '">';
	    $html .= '</span>';
	    $html .= '</form></div>';
		return $html;
	}

	public function getAdminTable($list_id = 0){
		$this->router = OwlRouter::getInstance();
		$route_array = $this->router->route_array;
		if($list_id == -1){
			unset($_SESSION['OwlAdminCategoryId']);
			$list_id = 0;
		}
		else if($list_id > 0 and $this->admin_action != 'category'){
			$_SESSION['OwlAdminCategoryId'] = $list_id;
		}
		else if($this->admin_action != 'category' and isset($_SESSION['OwlAdminCategoryId']) and $_SESSION['OwlAdminCategoryId'] > 0){
			$list_id = $_SESSION['OwlAdminCategoryId'];
			}
		else {
			unset($_SESSION['OwlAdminCategoryId']);
		}
		$list = new $this->list($list_id, true);
		$published = array(0 => 'fa-eye-slash', 1 => 'fa-eye');
		$published_action = array(0 => 'show', 1 => 'hide');
		$html = '';
		if($this->category_list != ''){
			$html .= '<ul class="nav nav-tabs">';
			if(isset($route_array[3]) and $route_array[3] == 'category'){
				$html .= '<li role="presentation"><a href="/' . $route_array[0] . '/' . $route_array[1] . '/' . $route_array[2] . '">Item list</a></li>';
				$html .= '<li role="presentation" class="active"><a href="#">Category manager</a></li>';
			}
			else{
				$html .= '<li role="presentation" class="active"><a href="#">Item list</a></li>';
				$html .= '<li role="presentation"><a href="/' . $route_array[0] . '/' . $route_array[1] . '/' . $route_array[2] . '/category">Category manager</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '<div class="btn-group" style="width:100%;">';
		$html .= '<button type="button" class="btn btn-success" onclick="getEditFormFromList(0)"><i class="fa fa-plus-circle"></i> Добавить элемент</button>';
		if($this->category_list != '' and $this->admin_action != 'category'){
			$cats = new $this->category_list();
			$html .= '<div  class="form-inline pull-right"><div class="form-group"><label for="label-list_id">Категория</label> ';
			$html .= '<select id="label-list_id" class="form-control" name="list_id" onchange="getAdminTable($(this).val());">';
			$html .= '<option value="-1"'; 
				if($list_id == 0)
					$html .= ' selected';
				$html .= '>Все категории</option>';
			foreach ($cats->items as $item) {
				$html .= '<option value="' . $item['id'] . '"'; 
				if($item['id'] == $list_id)
					$html .= ' selected';
				$html .= '>' . $item['name'] . '</option>';
			}
			$html .= '</select></div></div>';
		}
		$html .= '</div>';
		$html .= '<table class="table table-hover" id="' . get_class($this) . '_table"><thead><tr><th>ID</th><th>Name</th>';
		foreach ($list->admin_extra_cols as $k => $v) {
			$v = str_replace(' - flag', '', $v);
			$html .= '<th>' . $v . '</th>';
		}
		$html .= '<th class="text-center">Published</th><th class="text-center">Ordering <i class="fa fa-history" onclick="mirrorOrderFromList(' . $list->list_id . ')"></i></th><th>Controls</th></tr></thead><tbody>';
		$count = count($list->items);
		foreach ($list->items as $item) {
			$html .= '<tr><td>' . $item['id'] . '</td><td><span style="cursor:pointer;" onclick="getEditFormFromList(' . $item['id'] . ')">' . $item['name'] . '</span></td>';
			foreach ($list->admin_extra_cols as $k => $v) {
				if(strstr($v, ' - flag')){
					if($item[$k]) $html .= '<td><i class="fa fa-flag"></i></td>';
					else $html .= '<td></td>';
				}
				else $html .= '<td>' . $item[$k] . '</td>';
			}
			$html .= '<td class="text-center"><i class="fa ' . $published[$item['published']] . '" onclick="publishedItemFromList(' . $item['id'] . ',\'' . $published_action[$item['published']] . '\')"></i></td><td class="text-center">';
			if($item['ordering'] == 1)$html .= '<i class="fa fa-arrow-down" onclick="orderItemFromList(' . $item['id'] . ',\'down\')"></i>';
			else if($item['ordering'] == $count)$html .= '<i class="fa fa-arrow-up" onclick="orderItemFromList(' . $item['id'] . ',\'up\')"></i>';
			else $html .= '<i class="fa fa-arrow-down" onclick="orderItemFromList(' . $item['id'] . ',\'down\')"></i> <i class="fa fa-arrow-up"  onclick="orderItemFromList(' . $item['id'] . ',\'up\')"></i>';
			$html .= '</td><td><i class="fa fa-pencil" onclick="getEditFormFromList(' . $item['id'] . ')"></i> <i class="fa fa-clone" onclick="copyItemFromList(' . $item['id'] . ');"></i> <i class="fa fa-trash" onclick="deleteItemFromList(' . $item['id'] . ');"></i></td></tr>';
		}
		$html .= '</tbody></table>';
		return $html;	
	}

	public function getEditFormFromList($id = 0){
		global $COMPONENTS;
		$model = new $this->model($id);
		$html = '';
		if(!$id) $html .= '<h2>New item</h2>';
		else $html .= '<h2>Edit item id ' . $id . '</h2>';
		$html .= '<form id="' . get_class($this) . '_form">';
		$html .= '<div class="btn-group">';
		$html .= '<button type="button" class="btn btn-success" onclick="submitEditFormFromList()"><i class="fa fa-check"></i> Save</button> ';
		$html .= '<button type="button" class="btn btn-danger" onclick="getAdminTable()"><i class="fa fa-ban"></i> Cancel</button>';
		$html .= '</div>';

		foreach ($model->form_struct as $key => $value) {
			if($value['type'] == 'hidden'){
				if($key == 'list_id' and $this->category_list != '' and $this->admin_action != 'category'){
					$cats = new $this->category_list();
					$html .= '<div class="form-group"><label for="label-' .  $key . '">Категория</label> ';
					$html .= '<select id="label-' . $key . '" class="form-control" name="' . $key . '">';
					foreach ($cats->items as $item) {
						$html .= '<option value="' . $item['id'] . '"'; 
						if($item['id'] == $model->$key)
							$html .= ' selected';
						$html .= '>' . $item['name'] . '</option>';
					}
					$html .= '</select></div>';
				}
				else $html .= '<input type="hidden" name="' . $key . '" value="' . $model->$key . '">';
				continue;
			}
			if($value['type'] == 'htmleditor') $html .= '<div class="form-group"><label for="html-editor">' . $key . '</label> ';
			else $html .= '<div class="form-group"><label for="label-' . $key . '">' . $key . '</label> ';
			switch ($value['type']) {
				case 'string':
					$html .= '<input id="label-' . $key . '" class="form-control" type="text" name="' . $key . '" value="' . $model->$key . '">';
					break;
				case 'string disabled':
					$html .= '<input id="label-' . $key . '" class="form-control" type="text" name="' . $key . '" value="' . $model->$key . '" disabled>';
					break;	
				case 'email':
					$html .= '<input id="label-' . $key . '" class="form-control" type="email" name="' . $key . '" value="' . $model->$key . '">';
					break;
				case 'password':
					$html .= '<input id="label-' . $key . '" class="form-control" type="password" name="' . $key . '" value="' . $model->$key . '">';
					$html .= '<input id="new-' . $key . '" class="form-control" type="hidden" name="new_' . $key . '" value="">';
					$html .= '<div id="password-' . $key . '"></div>';
					$html .= '<div class="btn btn-link password-generator" onclick="passwordGenerator.generate(\'' . $key . '\');">Generate new password</div>';
					if($id < 1){
						$html .= '<script>$(function () {passwordGenerator.generate(\'' . $key . '\');});</script>';
					}
					break;	
				case 'image':
					$html .= '<input id="label-' . $key . '" class="form-control" type="hidden" name="' . $key . '" value="' . $model->$key . '">';
					$html .= '<div id="imageUpload-' . $key . '">';
					$img = new OwlImage($this->image_settigns['folder'], $model->$key);
					$img->style = 'max-width:300px;max-height:300px;';
					if($model->$key != ''){
						$html .= tag('div', '<span class="btn btn-danger" onclick="deleteUploadImage(\'' . $key . '\')"><i class="fa fa-trash"></i> Delete image</span>');
						$html .= $img->getTag();
					}
					else {
						$html .= '<span class="btn btn-success" onclick="getImageUploadForm(\'' . $key . '\')"><i class="fa fa-plus-circle"></i> Add image</span>';
					}
					$html .= '</div>';
					break;	
				case 'json':
					$html .= '<textarea id="label-' . $key . '" class="form-control" type="text" name="' . $key . '">' . $model->$key . '</textarea>';
					break;
				case 'component_list':
					$html .= '<select id="label-' . $key . '" class="form-control" name="' . $key . '">';				
					$html .= '<option value="">' . lang('TEXT_CHOICE_COMPONENT') . '</option>';
						foreach ($COMPONENTS as $k => $v) {
							if($k == '404 page'){
								continue;
							}
							$html .= '<option value="' . $k . '"'; 
							if($k == $model->$key)
								$html .= ' selected';
							$html .= '>' . lang('COMPONENT ' . $k) . '</option>';
						}
					$html .= '</select>';
					break;
				case 'textarea':
					$html .= '<textarea id="label-' . $key . '" class="form-control" type="text" name="' . $key . '">' . $model->$key . '</textarea>';
					break;
				case 'htmleditor':
					$html .= '<textarea id="htmleditor" class="form-control" type="text" name="' . $key . '">' . $model->$key . '</textarea>';
					$html .= "<script> CKEDITOR.replace( 'htmleditor' );</script>";
					break;
				case 'select':
					$html .= '<select id="label-' . $key . '" class="form-control" name="' . $key . '">';
					foreach ($value['values'] as $k => $v) {
						$html .= '<option value="' . $v . '"'; 
						if($v == $model->$key)
							$html .= ' selected';
						$html .= '>' . $k . '</option>';
					}
					$html .= '</select>';
					break;	
				case 'lang_select':
					$this->site_config = OwlConfig::getInstance();
					$html .= '<select id="label-' . $key . '" class="form-control" name="' . $key . '">';
					$html .= '<option value="">' . lang('lang all') . '</option>';
					foreach ($this->site_config->acepted_langs as $l) {
						$html .= '<option value="' . $l . '"'; 
						if($l == $model->$key)
							$html .= ' selected';
						$html .= '>' . lang('lang ' . $l) . '</option>';
					}
					$html .= '</select>';
					break;
			}
			$html .= '</div>';
		}
		$html .= '</form>';
		$html .= '<div id="imageUploadForm"></div>';
		return $html;	
	}

	public function submitEditFormFromList(){
		$model = new $this->model();
		if($model->submitEditFormFromList()){
			$this->clearCache();
			return $this->getAdminTable();
		}
		return '<div class="alert alert-danger" role="alert">Data is not submited!</div>';
	}

	public function copyItemFromList($id){
		if($id < 1) return '<div class="alert alert-danger" role="alert">Not set ID!</div>';
		$model = new $this->model($id);
		$model->id = 0;
		$model->name .= '_copy';
		$model->subCopyFunction();
		$model->addItem();
		$this->clearCache();		
		return $this->getAdminTable();	
	}

	public function deleteItemFromList($id){
		if($id < 1) return '<div class="alert alert-danger" role="alert">Not set ID!</div>';
		$model = new $this->model($id);
		$model->subDeleteFunction();
		$model->deleteItem();
		$this->clearCache();
		return $this->getAdminTable();	
	}

	public function publishedItemFromList($id, $act = 'hide'){
		if($id < 1) return '<div class="alert alert-danger" role="alert">Not set ID!</div>';
		$model = new $this->model($id);
		$p = array('hide' => 0,'show' => 1);
		$model->published = $p[$act];
		$model->updateItem();
		$this->clearCache();
		return $this->getAdminTable();
	}

	public function orderItemFromList($id,$act){
		$list = new $this->list();
		switch ($act) {
			case 'up': $list->upItemOrdering($id); break;
			case 'down': $list->downItemOrdering($id); break;
		}
		$this->clearCache();
		return $this->getAdminTable();	
	}

		public function mirrorOrderFromList($list_id = 1){
		$list = new $this->list($list_id);
		$list->mirrorItemOrdering();
		$this->clearCache();
		return $this->getAdminTable($list_id);		
	}

	public function setCategory(){
		$this->list = $this->category_list;
		$this->model = $this->category_model;
		$this->admin_action = 'category';
	}


	public function subTest(){
        $html = '';
        if($this->model != ''){
        	$model = new $this->model();
        	if(method_exists($model, 'test')){
        		$html .= $model->test();
        	}
        	else {
        		$html .= '<div class="alert alert-danger">SubTest controller <h4>Model not have method test()!</h4></div>';
        	}
        }
        if($this->list != ''){
        	$list = new $this->list();
        	if(method_exists($list, 'test')){
        		$html .= $list->test();
        	}
        	else {
        		$html .= '<div class="alert alert-danger">SubTest controller <h4>List not have method test()!</h4></div>';
        	}
        }
        return $html;
    }

    public function test(){
        return $this->subTest();
    }

}
?>