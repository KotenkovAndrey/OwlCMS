<?php
$html.='<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar"><ul class="nav menu">';
foreach ($this->list->items as $item) {
	if($item['parent_id']>0)continue;
	if($item['alias']=='main')$item['alias']='';
	$html.='<li><a href="/admin/'.$this->site_config->secret_admin.'/'.$item['alias'].'">';
	if($item['icon']!='')$html.='<i class="fa fa-'.$item['icon'].'"></i> ';
	$html.=$item['name'].'</a></li>';	
	}					
$html.='</ul></div><!--/.sidebar-->';	
?>