<?php
class OwlHtml extends OwlController{
	
function __construct($template = ''){
		if($template != ''){
			$this->template = $template;
		}
		$this->model = '';
		$this->list = '';
	}
}
?>