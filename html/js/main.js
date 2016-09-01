var controller='';
var method='';
var ctype='html';
var html_id='';
var ajax_str='ctype='+ctype+'&controller='+controller+'&method='+method;
var json_result=false;

function setAjaxString(str){
	ajax_str='ctype='+ctype+'&controller='+controller+'&method='+method+'&'+str;
}

function owlAjax(c,m,str){
	controller=c;
	method=m;
	setAjaxString(str);
	$.post('/ajax.php', ajax_str, function(data){
		$('#'+html_id).replaceWith(data)
    });
}

function owlJson(c,m,str,callback){
	controller=c;
	method=m;
	ctype='json';
	setAjaxString(str);
	$.getJSON('/ajax.php?'+ajax_str, function(data){
		json_result=data;
		if (typeof callback != 'undefined') {
  			callback();
		}
    });
}

contactForm={
	name 		: 	'',
	email 		: 	'',
	question 	: 	'',
	getForm		: 	function(){
		html_id='contactForm';
		owlAjax('OwlstudioContactForm','getForm','');
	}, 
	submit		: 	function(){
		html_id='contactForm';
		this.name=$("#"+html_id+" input[name='name']").val();
		this.email=$("#"+html_id+" input[name='email']").val();
		this.question=$("#"+html_id+" textarea").val();
		//console.log(this);
		owlAjax('OwlstudioContactForm','submitForm','name='+this.name+'&email='+this.email+'&question='+this.question);
	},

};