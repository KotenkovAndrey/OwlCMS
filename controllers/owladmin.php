<?php
require_once PATH . "models/owladminmenu.php";

class OwlAdmin extends OwlController{
	private $user;

	function __construct()	{
		$this->site_config = OwlConfig::getInstance();
		$this->user = OwlUser::getInstance();
		if($this->user->access_level < 500){
			$this->method = 'loginForm';
		}
	}

	public function menu(){
		if($this->user->access_level < 500){
			return false;
		}
		$this->list = new OwlAdminMenuList(1);
		$html = '';
		include PATH . "template/admin_menu.php";
		return $html;
	}

	public function help(){
		$html = '<h1>HELP</h1>';
		return $html;
	}

	public function langEditor(){
		$html = '';
		$filename = 'system';
		$this->router = OwlRouter::getInstance();
		$route_array = $this->router->route_array;
		if(isset($route_array[3])){
			$filename = trim($route_array[3]);
			if(!file_exists(PATH . 'controllers/' . $filename . '.php')){
				$html .= '<div class="alert alert-danger">Wrong component name:' . $filename . ' !</div>';
				$filename = 'system';
			}
		}
		$submit = owlrequest('submit');
		if($submit != ''){
			$phrase_key = owlrequest('phrase_key', 'array');
			foreach ($this->site_config->acepted_langs as $lang) {
				$var = 'phrase_' . $lang;
				$translate = owlrequest($var, 'array');
				$str = 'global $_LANG;' . PHP_EOL . PHP_EOL;
				$i = 0;
				foreach ($phrase_key as $p) {
					$phrase = trim($p);
					if($phrase == ''){
						$i++;
						continue;
					}
					$str .= '$_LANG[\'' . $phrase . '\'] = \'' . $translate[$i] . '\';' . PHP_EOL;
					$i++;
				}
				
				$html .= tag('h2', 'Langeage is ' . $lang);
				$html .= '<pre>';
				$html .= $str;
				$html .= '</pre>';

				if(file_put_contents(PATH. 'language/' . $lang . '/' . $filename . '.php', '<?php' . PHP_EOL . $str . '?>') === false){
					$html .= '<div class="alert alert-danger">Error write language file ' . PATH . 'language/' . $lang . '/' . $filename . '.php !</div>';
				}
				else $html .= '<div class="alert alert-success">Create language file: OK</div>';
			}

			return $html;
		}
		
		$files = array_slice(scandir(PATH . 'language/en/'), 2);

		$html .= '<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">' . lang('TEXT_CHOICE_COMPONENT') . ' <span class="caret"></span></button><ul class="dropdown-menu" aria-labelledby="dropdownMenu1">';
		$base_url = '/' . $route_array[0] . '/' . $route_array[1] . '/' . $route_array[2];
		foreach ($files as $f) {
			$end_url = '';
			$f=str_replace('.php', '', $f);
			if($f != 'system'){
				$end_url = '/' . $f;
			}
			$html .= '<li><a href="' . $base_url.$end_url . '">' . $f . '</a></li>';
		}
  		$html .= '</ul></div><br>';

		$dictionary = array();
		$th = '';
		$tdControl = '<td><i class="fa fa-arrow-down" onclick="langEditor.downRow(this);"></i> <i class="fa fa-arrow-up" onclick="langEditor.upRow(this);"></i> <i class="fa fa-clone" onclick="langEditor.copyRow(this);"></i> <i class="fa fa-trash" onclick="langEditor.deleteRow(this);" style="cursor:pointer;"></i></td>';
		$emptyRow = '<tr><td><input class="form-control" type="text" name="phrase_key[]" value=""></td>';

		foreach ($this->site_config->acepted_langs as $lang) {
			$content = file_get_contents(PATH . 'language/' . $lang . '/' . $filename . '.php');
			$content = str_replace('<?php', '', $content);
			$content = str_replace('global $_LANG;', '', $content);
			$content = str_replace('?>', '', $content);
			$content = preg_replace('/#.*/','',$content);
			$content = preg_replace('#//.*#','',$content);
			$content = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$content);
			$content = str_replace(PHP_EOL . PHP_EOL, '', $content);
			$content = str_replace(';$_LANG', ';' . PHP_EOL . '$_LANG', $content);
			$content = str_replace('$_LANG[\'', '', $content);
			$content = str_replace('\']=\'', '=', $content);
			$content = str_replace('\';', '', $content);
			$strings = explode(PHP_EOL, $content);
			foreach ($strings as $item) {
				$phrase = explode("=", $item);
				if(!isset($phrase[1])){
					$phrase[1] = '';
				}
				$key = trim($phrase[0]);
				$str = trim($phrase[1]);
				if($key == ''){
					continue;
				}
				$dictionary[$key][$lang] = $str;
			}			
			$th .= '<th class="text-center">' . $lang . '</th>';
			$emptyRow .= '<td><input class="form-control" type="text" name="phrase_' . $lang . '[]" value=""></td>';
		}
		$emptyRow .= $tdControl . '</tr>';

		//$html.=tag('pre',print_r($dictionary,true));
		$html .= '<form action="" method="post">';
		$html .= '<table id="langEditorTable" class="table table-bordered"><thead><tr><th class="text-center">Key</th>' . $th . '<th></th></tr></thead><tbody>';
		foreach ($dictionary as $k => $v) {
			$html .= '<tr><td><input class="form-control" type="text" name="phrase_key[]" value="' . $k . '"></td>';
			foreach ($this->site_config->acepted_langs as $lang) {
				if(!isset($v[$lang])){
					$v[$lang] = $k;
				}
				$html .= '<td><input class="form-control" type="text" name="phrase_' . $lang . '[]" value="' . $v[$lang] . '"></td>';
			}
			$html .= $tdControl . '</tr>';
		}
		$html .= '</tbody></table>';
		$html .= '<input type="hidden" value="ok" name="submit"><button type="submit" class="btn btn-success">Сохранить изменения</button>';
		$html .= '<button class="btn btn-default pull-right" onclick="langEditor.addRow();return false;">Добавить поля</button><br><br>';
		$html .= '</form>';

		$html .= '<script>
		var langEditor={
			addRow : function(){
				$("#langEditorTable").append(\'' . $emptyRow . '\');
			},
			deleteRow : function(th){
				if(confirm(\'Вы подтверждаете удаление строки?\')){
					$(th).parent().parent().remove();
				}
			},
			copyRow : function(th){
				var a=$(th).parent().parent().clone();
				$(th).parent().parent().after(a);
			},
			upRow : function(th){
				var a=$(th).parent().parent();
				a.insertBefore(a.prev());
			},
			downRow : function(th){
				var a=$(th).parent().parent();
				a.insertAfter(a.next());
			}
		};
		</script>';
		$html .= $this->getAdminHiddenInputs();
		return $html;
	}

	public function loginForm(){
		$html = '<div class="col-sm-6 col-lg-8"><h1 class="text-center">' . lang('Login to control panel') . '</h1>
<form role="form" method="POST" actions="">
<input type="hidden" name="formname" value="login">
  <div class="form-group">
    <label for="UserName">' . lang('username') . '</label>
    <input type="text" class="form-control" id="UserName" name="login">
  </div>
  <div class="form-group">
    <label for="Password">' . lang('password') . '</label>
    <input type="password" class="form-control" id="Password" name="password">
  </div>
  <div class="text-center"><button type="submit" class="btn btn-success">' . lang('send') . '</button></div>
</form>
		</div>';

		return $html;
	}

	public function mainPage(){
		$html = '';
		return $html;
	}

	public function setMethod($method){
		$this->method = $method;
	}

}
?>