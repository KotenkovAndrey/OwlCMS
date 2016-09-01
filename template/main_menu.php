<?php
//$html.='<h1>Главная страница</h1>';
$html.='
<div class="main-menu">
	<div class="container">
		<div class="row">		
			<div class="col-md-12">
				<div class="dropdown">
					<ul class="nav nav-pills pull-left">';
				foreach ($this->list->items as $item) {
					if($item['parent_id']>0 or !$item['published']){
						continue;
					}

					if($item['alias']=='home'){
						$html.='<li><a class="brand" href="" title="'.$item['title'].'"><img src="/img/owl_logo_white.svg" alt="owlstudio.ru"></a></li>';
					}
					else {
						$html.='<li><a href="'.$item['alias'].'" title="'.$item['title'].'">'.$item['name'].'</a></li>';					
					}
				}
				$html.='</ul><ul class="nav nav-pills pull-right">';
				if(!$this->user->id){
					$html.='<li><a href="login">'.lang('login').'</a></li>';

				}
				else{
					$html.='<li><a href="lk">'.lang('user page').'</a></li>';
					$html.='<li><a href="quit">'.lang('log out').'</a></li>';
					
				}
					/*
				if(LANG=='en')$html.='<li><a href="/">Русская версия</a></li>';	
				else $html.='<li><a href="/en/">English version</a></li>';	*/					
				$html.='</ul>
				</div>
			</div>	
		</div>				
	</div>
</div>
		';	

?>