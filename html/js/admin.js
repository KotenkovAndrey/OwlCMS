var sk=$('#sk').val();
var controller=$('#controller').val();
var model=$('#model').val();
var list=$('#list').val();
var admin_action=$('#admin_action').val();
var method='';
var ctype='controller';
var ajax_str='sk='+sk+'&ctype='+ctype+'&controller='+controller+'&model='+model+'&list='+list+'&method='+method;
var json_result=false;

var image_id='';

function setAjaxString(str){
	ajax_str='sk='+sk+'&ctype='+ctype+'&controller='+controller+'&model='+model+'&list='+list+'&method='+method+'&'+str;
	if(admin_action.length>1)
		ajax_str+='&admin_action='+admin_action;	
}

function adminAjax(m,str){
	method=m;
	ctype='admin';
	setAjaxString(str);
	$.post('/ajax.php', ajax_str, function(data){
		$('#'+controller).replaceWith(data)
    });
}

function adminJson(m,str,callback){
	method=m;
	ctype='admin_json';
	setAjaxString(str);
	$.getJSON('/ajax.php?'+ajax_str, function(data){
		json_result=data;
		if (typeof callback != 'undefined') {
  			callback();
		}
    });
}

function publishedItemFromList(id,act){
	adminAjax('publishedItemFromList','id='+id+'&act='+act);
}

function orderItemFromList(id,act){
	adminAjax('orderItemFromList','id='+id+'&act='+act);
}

function mirrorOrderFromList(id){
	adminAjax('mirrorOrderFromList','id='+id);
}

function getEditFormFromList(id){
	var str='id='+id;
	adminAjax('getEditFormFromList',str);
}

function submitEditFormFromList(){
	if($('#htmleditor').length){
		$('#htmleditor').html(CKEDITOR.instances.htmleditor.getData());
		}
	var str=$('#'+controller+'_form').serialize();
	adminAjax('submitEditFormFromList',str);
}

function copyItemFromList(id){
	adminAjax('copyItemFromList','id='+id);
}

function deleteItemFromList(id){
	if(confirm('Вы подтверждаете удаление элемента ID '+id+'?')){
		adminAjax('deleteItemFromList','id='+id);
	}
}

function getAdminTable(list_id){
	var str='';
	if(parseInt(list_id)>0 || parseInt(list_id)==-1){
		str='id='+list_id;
	}
	adminAjax('getAdminTable',str);
}

function closeImageUploadForm(){
	$('#imageUploadForm').html('');
	$('#'+controller+'_form').hide();
}


function getImageUploadForm(id){
	$('#'+controller+'_form').hide();
	$('#upload_image_file').show();
	image_id=id;

}

function hideImageUploadForm(){
	$('#'+controller+'_form').show();
	$('#upload_image_file').hide();
	image_id='';
}


function deleteUploadImage(id){
	var fname=$('#label-'+id).val();
	if(fname.length<1) return false;
	var folder=$('#image_upload_folder').val();

	$.getJSON('/file.php?file_sk='+sk+'&file_is_admin=1&file_ctype=del_image&fname='+fname+'&folder='+folder, function(data){
		if(!data.error){
			$('#imageUpload-'+id).html('<span class="btn btn-success" onclick="getImageUploadForm(\''+id+'\')"><i class="fa fa-plus-circle"></i> Add image</span>');
		}
    });
}

passwordGenerator={
	specials 	: '!@#$%^&*()_+{}:<>?[];,.~',
	lowercase 	: 'abcdefghijklmnopqrstuvwxyz',
	uppercase 	: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	numbers 	: '0123456789',
	password 	: '',
	pick		: function(str,n){
		var chars = '';
		for (var i = 0; i < n; i++) {
        	chars += str.charAt(Math.floor(Math.random() * str.length));
    	}
    	return chars;
	},
	shuffle		: function(str){
		var array = str.split('');
	    var tmp, current, top = array.length;

	    if (top) while (--top) {
	        current = Math.floor(Math.random() * (top + 1));
	        tmp = array[current];
	        array[current] = array[top];
	        array[top] = tmp;
	    }

	    return array.join('');		
	},
	generate	: function(key){
		this.password= this.shuffle(this.pick(this.specials,1) + this.pick(this.lowercase,5) + this.pick(this.uppercase,3) + this.pick(this.numbers, 3));
		$("#password-"+key).html(this.password);
		$("#new-"+key).val(this.password);

		
	},

};

// https://github.com/blueimp/jQuery-File-Upload/wiki/Options
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        url: '/file.php',
        add: function (e, data) {
            data.context = $('<p/>').text('Uploading...').appendTo($('#upload_image_file'));
            data.submit();   
        },
        done: function (e, data) {
            data.context.text('Upload finished.');
            var answer=data.jqXHR.responseJSON;
            //console.log(answer);
            $('#label-'+image_id).val(answer.filename);
            var folder=$('#image_upload_folder').val();
            if(folder.length>1){
            	folder=folder+'/';
            }
            $('#imageUpload-'+image_id).html('<div><span class="btn btn-danger" onclick="deleteUploadImage(\''+image_id+'\')"><i class="fa fa-trash"></i> Delete</span></div><img src="/img/'+folder+answer.filename+'">');
            
            hideImageUploadForm();
        }
    });
});
