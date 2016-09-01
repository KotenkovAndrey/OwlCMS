<?php
class OwlImage{
    public $filename;
    public $file_ext;
    public $file_size;
    public $width;
    public $height;
    public $maxwidth;
    public $maxheight;
    public $minwidth;
    public $minheight;
    public $folder;
    public $url;
    public $alt;
    public $id;
    public $class;
    public $style;

    function __construct($folder = '',$filename = '',$alt = ''){
    	$this->url = '/img/no_image.svg';
    	if($folder != ''){
    		$this->folder = $folder . '/';
    	}
    	if($filename != ''){ 
    		$this->filename = $filename;
    		$this->getUrlImage();
    	}
    	if($alt == '') $this->alt = $this->filename;
    	else $this->alt = $alt;
    	$this->alt = str_replace('"', '', $this->alt);
    	$this->alt = str_replace("'", "", $this->alt);
	}

    public function getUrlImage(){
    	if($this->filename == ''){
    		$this->url = '/img/no_image.svg';
    		return false;
    	}
    	$this->url = '/img/' . $this->folder . $this->filename;
    	if(!file_exists(PATH . '/html' . $this->url)){
    		$this->url = '/img/no_image.svg';
    		return false;	
    	}
    	return true;
    }

    public function getTag(){
    	$id = '';
    	$class = '';
    	$style = '';
    	$this->getUrlImage();

    	if($this->width > 0) $this->style .= 'width:' . $this->width . 'px;';
    	if($this->height > 0) $this->style .= 'height:' . $this->height . 'px;';
    	if($this->id != '') $id = ' id="' . $this->id . '"';
    	if($this->class != '') $class = ' class="' . $this->class . '"';
    	if($this->style != '') $style = ' style="' . $this->style . '"';
    	return '<img src="' . $this->url . '" alt="' . $this->alt . '"' . $id . $class . $style . '>';
    }

    public function ajaxUpload(){
    	$res = array(
    		"error" 		 => false,
    		"input_filename" => $_FILES['files']['name'], 	
    		"filetype" 		 => $_FILES['files']['type'],
    		"folder"		 => owlrequest('folder'),
			"maxwidth"		 => owlrequest('maxwidth','int',0),
			"maxheight"		 => owlrequest('maxheight','int',0),
			"minwidth"		 => owlrequest('minwidth','int',0),
			"minheight"		 => owlrequest('minheight','int',0),
		);

		if($_FILES['files']['error'] > 0){
			return owlJsonError('Error with file upload ' . $_FILES['files']['name'], $res);
		}

		$img_info = getimagesize($_FILES['files']['tmp_name']);
		if(($res["minwidth"] > 0 and $res["minwidth"] > $img_info[0]) or $img_info[0] < 1){
			return owlJsonError('Width uploaded image is too small. Need minimal ' . $res["minwidth"] . 'px.', $res);
		}

		if(($res["minheight"] > 0 and $res["minheight"] > $img_info[1]) or $img_info[1] < 1){
			return owlJsonError('Height uploaded image is too small. Need minimal ' . $res["minheight"] . 'px.', $res);
		}

		//check exist foldor end create if ints need
		$path = PATH . 'html/img/';
		if($res["folder" ]!= ''){
			$path .= $res["folder"] . '/';
			if (!file_exists($path)) {
    			if(!mkdir($path)){
					return owlJsonError('Can not create folder' . $res['folder'], $res);
    			}
			}
		}
		$ext = '';
		$filename = '';
		$input_img = '';
		$funcCreateImage = '';

		switch ($_FILES['files']['type']){
			case 'image/jpeg':
				$ext = '.jpg';
				$input_img = @imagecreatefromjpeg($_FILES['files']['tmp_name']);
				$funcCreateImage = 'imagejpeg';
				break;
			case 'image/png':
				$ext = '.png';
				$input_img = @imagecreatefrompng($_FILES['files']['tmp_name']);
				$funcCreateImage = 'imagepng';
				break;
		//TODO Add supprot for gif,webp and other formats. 
			default:
				return owlJsonError('Not supported format' . $_FILES['files']['type'], $res);
		}

		if(!$input_img){
			return owlJsonError('Can not create image resours from file', $res);
		}

		// generate unique filename
		do{
			$filename = uniqid(rand()) . $ext;
		}while(file_exists($path.$filename));
    	
    	$res['filename'] = $filename;

		$ratioX = 1;
		$ratioY = 1;
		if($res["maxwidth"] > 0) $ratioX = $img_info[0] / $res["maxwidth"];
		if($res["maxheight"] > 0) $ratioY = $img_info[1] / $res["maxheight"];
		$ratio = $ratioX > $ratioY ? $ratioX : $ratioY;
		$new_width = intval($img_info[0] / $ratio);
		$new_height = intval($img_info[1] / $ratio);

		$new_image = @imagecreatetruecolor($new_width, $new_height);
		if($_FILES['files']['type'] == 'image/png'){
			$background = imagecolorallocate($new_image, 0, 0, 0);
       		// removing the black from the placeholder
  	      	imagecolortransparent($new_image, $background);
			imageAlphaBlending($new_image, false);
			imageSaveAlpha($new_image, true);
		}

		if(!imagecopyresampled($new_image, $input_img, 0, 0, 0, 0, $new_width, $new_height, $img_info[0], $img_info[1])){
			return owlJsonError('Can not resize image', $res);
		}
		if(!$funcCreateImage($new_image,$path.$filename)){
			return owlJsonError('Can not write image to file', $res);
		}

		imagedestroy($input_img);
		imagedestroy($new_image);
    	return json_encode($res);
    }

    public function deleteUploadImage(){
    	$res = owlJsonInit();
    	$folder = owlrequest('folder');
		$filename = owlrequest('fname');

		if($filename == ''){
			return owlJsonError('Filename is not set.');	
		}
		$path = PATH . 'html/img/';
		if($folder != ''){
			$path .= $folder . '/';
			if (!file_exists($path)) {
				return owlJsonError('Folder ' . $folder . ' is not exist');			
			}
		}
		$path .= $filename;
		if (!file_exists($path)) {
			return owlJsonError('File ' . $filename . ' is not exist in folder /img/' . $folder);			
		}
		if(!unlink($path)){
			return owlJsonError('Can not delete file ' . $filename . ' in folder /img/' . $folder);	
		}
		return json_encode($res);
    }

}
?>