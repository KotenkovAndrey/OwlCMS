<?php
class OwlGenerate extends OwlController{
	private $user;
	private $types;
	private $options;
	private $category_options;

	public function __construct(){
		$this->site_config = OwlConfig::getInstance();
		$this->user = OwlUser::getInstance();
		if($this->user->access_level < 500){
			$this->method = 'loginForm';
		}

		$this->types = array(
			'int'			=>	array('struct_type' => 'int',	'sql_struct' => 'int(11) NOT NULL'),			 		
			'varchar_8'		=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(8) NOT NULL'),			 		
			'varchar_32'	=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(32) NOT NULL'),			 		
			'varchar_64'	=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(64) NOT NULL'),
			'varchar_128'	=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(128) NOT NULL'),
			'varchar_255'	=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(255) NOT NULL'),
			'varchar_512'	=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(512) NOT NULL'),
			'image'			=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(255) NOT NULL'),
			'html'			=>	array('struct_type' => 'html',	'sql_struct' => 'text NOT NULL'),
			'text'			=>	array('struct_type' => 'str',	'sql_struct' => 'text NOT NULL'),
			'json'			=>	array('struct_type' => 'str',	'sql_struct' => 'text NOT NULL'),
			'email'			=>	array('struct_type' => 'str',	'sql_struct' => 'varchar(255) NOT NULL'),
			'date'			=>	array('struct_type' => 'str',	'sql_struct' => 'date NOT NULL'),
			'datetime'		=>	array('struct_type' => 'str',	'sql_struct' => 'datetime NOT NULL')
		);

		$this->options=array(
			'image'					=>	array('checked' => false,	'type' => 'varchar_255',	'form_type' => 'image',			'default' => false),
			'parent'				=>	array('checked' => false,	'type' => 'int',			'form_type' => 'select',		'default' => 0,	'varname' => 'parent_id'),
			'user'					=>	array('checked' => false,	'type' => 'int',			'form_type' => 'hidden',		'default' => 0,	'varname' => 'user_id'),
			'access_level'			=>	array('checked' => false,	'type' => 'int',			'form_type' => 'hidden',		'default' => 0),
			'lang'					=>	array('checked' => false,	'type' => 'varchar_8',		'form_type' => 'lang_select',	'default' => false),
			'content'				=>	array('checked' => false,	'type' => 'html',			'form_type' => 'htmleditor',	'default' => false),
			'date_create'			=>	array('checked' => false,	'type' => 'datetime',		'form_type' => 'hidden',		'default' => '0000-00-00 00:00:00'),
			'date_edit'				=>	array('checked' => false,	'type' => 'datetime',		'form_type' => 'hidden',		'default' => '0000-00-00 00:00:00'),
			'date_published'		=>	array('checked' => false,	'type' => 'datetime',		'form_type' => 'datetime',		'default' => '0000-00-00 00:00:00'),
			'date_end_published'	=>	array('checked' => false,	'type' => 'datetime',		'form_type' => 'datetime',		'default' => '0000-00-00 00:00:00'),
		);

		$this->category_options=array(
			'category_image'			  => array('checked' => false, 'type' => 'varchar_255', 'form_type' => 'image',	   'default' => false,	'varname' => 'image'),
			'category_parent'			  => array('checked' => false, 'type' => 'int',			'form_type' => 'select',   'default' => 0,		'varname' => 'parent_id'),
			'category_user'				  => array('checked' => false, 'type' => 'int',			'form_type' => 'hidden',   'default' => 0,		'varname' => 'user_id'),
			'access_level'				  => $this->options['access_level'],
			'lang'						  => $this->options['lang'],
			'category_date_create'		  => array('checked' => false, 'type' => 'datetime',	'form_type' => 'hidden',   'default' => '0000-00-00 00:00:00',	'varname' =>' date_create'),
			'category_date_edit'		  => array('checked' => false, 'type' => 'datetime',	'form_type' => 'datetime', 'default' => '0000-00-00 00:00:00',	'varname' => 'date_edit'),
			'category_date_published'	  => array('checked' => false, 'type' => 'datetime',	'form_type' => 'datetime', 'default' => '0000-00-00 00:00:00',	'varname' =>' date_published'),
			'category_date_end_published' => array('checked' => false, 'type' => 'datetime',	'form_type' => 'datetime', 'default' => '0000-00-00 00:00:00',	'varname' =>' date_end_published'),
		);
	}

	public function checkExistComponent($name){
		$result = owlJsonInit();
		$lower = strtolower($name);
		if(file_exists(PATH . "models/" . $lower . ".php")){
			return owlJsonError('Model ' . $name . ' is exist');
		}
		if(file_exists(PATH . "controllers/" . $lower . ".php")){
			return owlJsonError('Controller ' . $name . ' is exist');
		}
		$db = OwlDb::getInstance()->mysqli;
		$sql = "SHOW TABLES LIKE '$lower'";
		$r = $db->query($sql);
		if($r->num_rows){
			return owlJsonError('Database table ' . $name . ' is exist');
		}
		$result['answer'] = 'ok';
		return json_encode($result);
	}

	public function generate(){
		$html = '<h1>OwlCodeGenerator</h1>';
		$submit = owlrequest('submit');

		if($submit != ''){
			$controller_name = owlrequest('controller_name');
			$only_test = owlrequest('only_test', 'int', 0);
			$category = owlrequest('category', 'int', 0);
			//get checking for standart features
			foreach ($this->options as $key => $value) {
				$this->options[$key]['checked'] = owlrequest($key, 'int', 0);
			}

			$title = owlrequest('title', 'array');
			$type = owlrequest('type', 'array');
			$default = owlrequest('default', 'array');
			$db_struct = '';
			$form_struct = '';
			$pi = '';
			$sql_struct = '';

			if(is_array($title)){
				$i = -1;
				foreach ($title as $t) {
					$i++;
					if($t == '')continue;
					$form_type = 'string';
					if($type[$i] == 'html'){$form_type = 'htmleditor';}
					$this->options[$t] = array('checked' => true, 'type' => $type[$i], 'form_type' => $form_type, 'default' => $default[$i]);
				}
			}
			foreach($this->options as $k => $item){
				if(!$item['checked']){
					continue;
				}
				$name = $k;
				if(isset($item['varname']) and $item['varname'] != ''){
					$name = $item['varname'];
				}
			 	$struct_type = $this->types[$item['type']]['struct_type'];
			 	$sql_struct .= "\t" . $name . ' ' . $this->types[$item['type']]['sql_struct'];
				if($item['default'] !== false){
					if($struct_type == 'int'){
						$pi .= PHP_EOL . "\t" . 'public $' . $name . '=' . intval($item['default']) . ';';
						$sql_struct .= ' DEFAULT ' . intval($item['default']);
					}
					else{
						$pi .= PHP_EOL . "\t" . 'public $' . $name . '="' . $item['default'] . '";';
						$sql_struct .= " DEFAULT '" . $item['default'] . "'";
					}
				}
				else{
					$pi .= PHP_EOL . "\t" . 'public $' . $name . ';';
					if($item['type'] == 'date')$sql_struct .= " DEFAULT '0000-00-00'";
					if($item['type'] == 'datetime')$sql_struct .= " DEFAULT '0000-00-00 00:00:00'";
				}
				$sql_struct .= ',' . PHP_EOL; 
			 	$db_struct .= PHP_EOL . "\t\t" . '"' . $name . '" => "' . $struct_type . '",';				 
				$form_struct .= PHP_EOL . "\t\t" . '"' . $name . '" => array("type" => "' . $item['form_type'] . '"),';
			}
			
			$component_str = 'require_once PATH."models/' . strtolower($controller_name) . '.php";' . PHP_EOL . PHP_EOL;
			$component_str .= 'class ' . $controller_name . ' extends OwlController{' . PHP_EOL . PHP_EOL;	
				$component_str .= "\t" . 'public function __construct(){' . PHP_EOL;
					if($category){
						$component_str .= "\t\t" . '$this->category_list=\'' . $controller_name . 'CategoryList\';' . PHP_EOL;
						$component_str .= "\t\t" . '$this->category_model=\'' . $controller_name . 'CategoryItem\';' . PHP_EOL;			
					}
					$component_str .= "\t\t" . '$this->list=\'' . $controller_name . 'List\';' . PHP_EOL;
					$component_str .= "\t\t" . '$this->model=\'' . $controller_name . 'Item\';' . PHP_EOL;
				$component_str .= "\t" . '}' . PHP_EOL . PHP_EOL;
			$component_str .= '}' . PHP_EOL;
			
			if($only_test){
				$html .= tag('div', 'Debug mode. Only text, not create any files and not execute any requests.', 'alert alert-info');	
			}
			//Show component item to copy and paste into app.php
			$html .= '<h2>$COMPONENTS item to copy and paste into app.php</h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= '$COMPONENTS["' . strtolower($controller_name) . '"] = array(' . PHP_EOL;
			$html .= "\t" . '"controller" => "' . $controller_name . '"' . PHP_EOL;
			$html .= "\t" . ');' . PHP_EOL;
			$html .= '</pre>' . PHP_EOL;

			//Show listing file for debug
			$html .= '<h2>/controllers/' . strtolower($controller_name) . '.php</h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= $component_str;
			$html .= '</pre>' . PHP_EOL;

			//write controller to file
			if(!$only_test){
				if(file_put_contents(PATH . '/controllers/' . strtolower($controller_name) . '.php', '<?php' . PHP_EOL . $component_str . '?>') === false){
					$html .= '<div class="alert alert-danger">Error write controller file!</div>';
					return $html;
				}
				else $html .= '<div class="alert alert-success">Create controller: OK</div>';
			}

			$model_str = 'class ' . $controller_name . 'Item extends OWlModel{';
				$model_str .= "\t" . $pi . PHP_EOL;
				$model_str .= "\t" . 'public $form_struct = array(' . $form_struct . PHP_EOL . "\t\t" . ');' . PHP_EOL . PHP_EOL;
				$model_str .= "\t" . 'protected $db_table = \'' . $controller_name . '\';' . PHP_EOL;
				$model_str .= "\t" . 'protected $db_struct = array(' . $db_struct . PHP_EOL . "\t\t" . ');' . PHP_EOL;
			$model_str .= '}' . PHP_EOL . PHP_EOL;

			$model_str .= 'class ' . $controller_name  .'List extends OWlList{' . PHP_EOL;
				$model_str .= "\t" . 'protected $db_table = \'' . $controller_name . '\';' . PHP_EOL;
				$model_str .= "\t" . 'protected $db_struct = array(' . $db_struct . PHP_EOL . "\t\t" . ');' . PHP_EOL;
			$model_str .= '}' . PHP_EOL . PHP_EOL;

			if($category){
				$fs = '';
				$pi = '';
				$sql_category_struct = ''; 
				$cdbs = "\t" . 'protected $db_struct = array(' . PHP_EOL;
				$cdbs .= "\t\t" . '"description" => "html",';
				$pi .= PHP_EOL . "\t" . 'public $description;';

				//get checking for standart catogory features
				foreach ($this->category_options as $k => $item) {
					$this->category_options[$k]['checked'] = owlrequest($k, 'int', 0);
					if(!$this->category_options[$k]['checked']){
						continue;
					}
					$name = $k;
					if(isset($item['varname']) and $item['varname'] != ''){
						$name = $item['varname'];
					}
				 	$struct_type = $this->types[$item['type']]['struct_type'];
				 	$sql_category_struct .= "\t" . $name . ' ' . $this->types[$item['type']]['sql_struct'];

					if($item['default'] !== false){
						if($struct_type == 'int'){
							$pi .= PHP_EOL . "\t" . 'public $' . $name . ' = ' . intval($item['default']) . ';';
							$sql_category_struct .= ' DEFAULT ' . intval($item['default']);
						}
						else{
							$pi .= PHP_EOL . "\t" . 'public $' . $name . ' = "' . $item['default'] . '";';
							$sql_category_struct .= " DEFAULT '" . $item['default'] . "'";
						}
					}
					else{
						$pi .= PHP_EOL . "\t" . 'public $' . $name . ';';
						if($item['type'] == 'date')$sql_category_struct .= " DEFAULT '0000-00-00'";
						if($item['type'] == 'datetime')$sql_category_struct .= " DEFAULT '0000-00-00 00:00:00'";
					}
					$sql_category_struct .= ',' . PHP_EOL; 
				 	$cdbs .= PHP_EOL  ."\t\t" . '"' . $name . '" => "' . $struct_type . '",';				 
					$fs .= PHP_EOL . "\t\t" . '"' . $name . '" => array("type" => "' . $item['form_type'] . '"),';
				}
				$cdbs .= PHP_EOL . "\t\t" . ');' . PHP_EOL;

				$model_str .= 'class ' . $controller_name . 'CategoryItem extends OWlModel{';
				$model_str .= $pi.PHP_EOL;
				
				$model_str .= "\t" . 'public $form_struct = array(' . PHP_EOL;
					$model_str .= "\t\t" . '"description" => array("type" => "htmleditor"),';
					$model_str .= $fs;
					$model_str .= PHP_EOL . "\t\t" . ');' . PHP_EOL . PHP_EOL;
				$model_str .= "\t" . 'protected $db_table = \'' . $controller_name . '_category\';' . PHP_EOL;
				$model_str .= $cdbs . PHP_EOL;
				$model_str .= '}' . PHP_EOL . PHP_EOL;

				$model_str .= 'class ' . $controller_name . 'CategoryList extends OWlList{' . PHP_EOL;
					$model_str .= "\t" . 'protected $db_table = \'' . $controller_name . '_category\';' . PHP_EOL;
					$model_str .= $cdbs . PHP_EOL;
				$model_str .= '}' . PHP_EOL . PHP_EOL;
			}

			//Show listing file for debug
			$html .= '<h2>/models/' . strtolower($controller_name) . '.php</h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= $model_str;
			$html .= '</pre>';

			//write model to file
			if(!$only_test){
				if(file_put_contents(PATH . '/models/' . strtolower($controller_name) . '.php', '<?php' . PHP_EOL . $model_str . '?>') === false){
					return $html . '<div class="alert alert-danger">Error write model file!</div>';
				}
				else $html .= '<div class="alert alert-success">Create model: OK</div>';
			}

			$sql = 'CREATE TABLE IF NOT EXISTS ' . $controller_name . ' (' . PHP_EOL;
			$sql .= "\t" . 'id int(11) NOT NULL,' . PHP_EOL;
			$sql .= "\t" . 'list_id int(11) NOT NULL,' . PHP_EOL;
			$sql .= "\t" . 'name varchar(64) NOT NULL,' . PHP_EOL;
			$sql .= $sql_struct;
			$sql .= "\t" . 'published int(11) NOT NULL DEFAULT 1,' . PHP_EOL;
			$sql .= "\t" . 'ordering int(11) NOT NULL' . PHP_EOL;
			$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;' . PHP_EOL;

			$db=OwlDb::getInstance()->mysqli;
			
			//show sql request for debug
			$html .= '<h2>SQL Request<h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= $sql;
			$html .= '</pre>';
			if(!$only_test){
				if ($db->query($sql) !== TRUE) {
					return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
				}
				else $html .= '<div class="alert alert-success">Sql request: OK</div>';
			}

			$sql = 'ALTER TABLE ' . $controller_name . ' ADD PRIMARY KEY (`id`);' . PHP_EOL . PHP_EOL;
			//show sql request for debug
			$html .= '<h2>SQL Request<h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= $sql;
			$html .= '</pre>';
			if(!$only_test){
				if ($db->query($sql) !== TRUE) {
					return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
				}
				else $html .= '<div class="alert alert-success">Sql request: OK</div>';
			}			

			$sql = 'ALTER TABLE ' . $controller_name . ' MODIFY id int(11) NOT NULL AUTO_INCREMENT;' . PHP_EOL . PHP_EOL;
			//show sql request for debug
			$html .= '<h2>SQL Request<h2>';
			$html .= '<pre>' . PHP_EOL;
			$html .= $sql;
			$html .= '</pre>';
			if(!$only_test){
				if ($db->query($sql) !== TRUE) {
					return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
				}
				else $html.='<div class="alert alert-success">Sql request: OK</div>';
			}

			if($category){
				$sql = 'CREATE TABLE IF NOT EXISTS ' . $controller_name . '_category (' . PHP_EOL;
				$sql .= "\t" . 'id int(11) NOT NULL,' . PHP_EOL;
				$sql .= "\t" . 'list_id int(11) NOT NULL,' . PHP_EOL;
				$sql .= "\t" . 'name varchar(64) NOT NULL,' . PHP_EOL;
				$sql .= "\t" . 'description text NOT NULL,' . PHP_EOL;
				$sql .= $sql_category_struct;
				$sql .= "\t" . 'published int(11) NOT NULL DEFAULT 1,' . PHP_EOL;
				$sql .= "\t" . 'ordering int(11) NOT NULL' . PHP_EOL;
				$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;' . PHP_EOL;

				//show sql request for debug
				$html .= '<h2>SQL Request<h2>';
				$html .= '<pre>' . PHP_EOL;
				$html .= $sql;
				$html .= '</pre>';
				//exec sql request
				if(!$only_test){
					if ($db->query($sql) !== TRUE) {
						return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
					}
					else $html .= '<div class="alert alert-success">Sql request: OK</div>';
				}

				$sql = 'ALTER TABLE ' . $controller_name . '_category ADD PRIMARY KEY (`id`);' . PHP_EOL;
				//show sql request for debug
				$html .= '<h2>SQL Request<h2>';
				$html .= '<pre>' . PHP_EOL;
				$html .= $sql;
				$html .= '</pre>';
				if(!$only_test){
					if ($db->query($sql) !== TRUE) {
						return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
					}
					else $html .= '<div class="alert alert-success">Sql request: OK</div>';
				}

				$sql = 'ALTER TABLE ' . $controller_name . '_category MODIFY id int(11) NOT NULL AUTO_INCREMENT;' . PHP_EOL;
				//show sql request for debug
				$html .= '<h2>SQL Request<h2>';
				$html .= '<pre>' . PHP_EOL;
				$html .= $sql;
				$html .= '</pre>';
				if(!$only_test){
					if ($db->query($sql) !== TRUE) {
						return $html . '<div class="alert alert-danger">Error sql request: ' . $db->error . '</div>';
					}
					else $html .= '<div class="alert alert-success">Sql request: OK</div>';
				}
			}
			return $html;			
		}

		$html .= '<form method="post" action="" id="OwlCodeGeneratorForm">';
		$html .= '<h3>Название компонента</h3>';
		$html .= '<div class="form-group">';
		$html .= '<div id="checkResult" class="alert" style="display:none;"></div>';
		$html .= '<input class="form-control" type="text" name="controller_name" required id="componentName" onkeyup="generator.checkComponentName()">';
		$html .= '<div class="checkbox"><label><input type="checkbox" name="only_test" value="1"> Только тест без создания компонента</label></div>';
		$html .= '</div>';
		$html .= '<h3>Базавая структара данных</h3>';

		$html .= '<pre>
id          -  int
name        -  varchar_64
published   -  int
ordering    -  int
list_id     -  int</pre>';

		foreach ($this->options as $key => $value) {
			$html .= '<div class="checkbox"><label><input type="checkbox" name="' . $key . '" value="1"> ' . lang("generate form " . $key) . '</label></div>';
		}

		$html .= '<hr>';
		$html .= '<div class="checkbox"><label><input type="checkbox" name="category" value="1"> Категории (модель и список для категории)</label></div>';
		foreach ($this->category_options as $key => $value) {
			if($key == 'access_level' or $key == 'lang'){
				continue;
			}
			$html .= '<div class="checkbox"><label><input type="checkbox" name="' . $key . '" value="1"> ' . lang("generate form " . $key) . '</label></div>';
		}
		$html .= '<hr>';
		$html .= '<div id="additional-inputs"></div>';
		$html .= '<button class="btn btn-default" onclick="generator.addDopInputs();return false;">Добавить поля</button><br><br>';
		$html .= '<hr>';
		$html .= '<input type="hidden" value="ok" name="submit"><button type="submit" class="btn btn-lg btn-success">Создать компонент</button>';
		$html .= '</form>';

		//for ajax request
		$html .= $this->getAdminHiddenInputs();

		$html2 = '<div class="form-group">';
		$html2 .= '<input class="form-control" type="text" name="title[]" placeholder="Название поля">';
		$html2 .= '<select class="form-control" name="type[]">';
			$html2 .= '<option value="">Тип поля в базе</option>';
			foreach ($this->types as $key => $value) {
				$html2 .= '<option value="' . $key . '">' . $key . '</option>';
			}
		$html2 .= '</select>';
		$html2 .= '<input class="form-control" type="text" name="default[]" placeholder="Значение по-умолчанию">';
		$html2 .= '</div>';

		$html .= '<script>
		var generator={
			np : 1,
			addDopInputs : function (){
			$(\'#additional-inputs\').append(\'<h4>Дополнительное поле №\'+this.np+\'</h4>' . $html2 . '\');
			this.np++;
			},
			checkComponentName : function(){
				var val=$("#componentName").val();
				if(val.length<1){
					$("#checkResult").html("error: Title is empty!");
					$("#checkResult").removeClass("alert-success");
					$("#checkResult").addClass("alert-danger");
					$("#checkResult").show();
					return false;
				}
				adminJson(\'checkExistComponent\',\'id=\'+val,function (){
					if(json_result.error){
						$("#checkResult").html("error: "+json_result.reason);
						$("#checkResult").removeClass("alert-success");
						$("#checkResult").addClass("alert-danger");
						$("#checkResult").show();
						return false;
					}
					$("#checkResult").html("You can use this title.");
					$("#checkResult").removeClass("alert-danger");
					$("#checkResult").addClass("alert-success");
					$("#checkResult").show();

				});
				
			}
		};
		</script>';
		return $html;
	}

}
?>