<?php

$search = '';
$skey = '';
$search_arr = array();

if (count($_POST)>0) {
	$skey = (isset($_POST['skey']))?$_POST['skey']:'';
	if ($skey !== '') {
		$search = (string)$_POST['skey'];
		array_push($search_arr,$search);
	}
	if (isset($_POST['chk'])) {
		$chk_arr = $_POST['chk'];
		foreach ($chk_arr as $chk => $chv) {
			array_push($search_arr,$chv);
		}
	}
}

$search_count = count($search_arr);

$arr_items = array();
$arr_items2 = array();
$arr_ips = array();
$sel_arr = ['1' => 'select', '2' => 'union', '3' => 'script',
 			'4' => 'admin', '5' => '.php', '6' => '.log',
  			'7' => 'backend', '8' => 'sql'];

if(isset($_FILES["txt_file"])){

	$uploadOk = 1;

	$txt_file = $_FILES["txt_file"];
	$tmp_name = $txt_file["tmp_name"];

	$f_size = $txt_file["size"] / 1024;

	$check = getimagesize($tmp_name);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    // 讀取資料
    $file_path = $tmp_name;
	$myfile = fopen($file_path, "r");
	$arr_items = array();
	$i = 0;
	while(!feof($myfile)) {
	  	$data = fgets($myfile);
	  	$arr_items[$i] = "-".$data;
	  	$i++;
	}
	fclose($myfile);

	$i2 = 0;
	$arr_items_len = count($arr_items);

	for ($sc=0; $sc < $search_count; $sc++) {
		$search = $search_arr[$sc];

		for ($k=0; $k < $arr_items_len; $k++) {

		  	if($k > 0 ){
				$items = new \stdClass();

				$val = (string)$arr_items[$k];
				$len = strlen($val);
				$ip_addr = strpos($val," ");
				$ip = substr($val, 0, $ip_addr);
				$str2 = substr($val, $ip_addr+4, $len);
				$time_addr = strpos($str2,"] \"");
				$time = substr($str2, 0, $time_addr+1);
				$str3 = substr($str2, $time_addr+3, $len);
				$method_addr = strpos($str3," ");
				$method = substr($str3, 0, $method_addr+1);

				$items->ip = $ip;
				$items->time = $time;
				$items->method = $method;
				$items->url = substr($str3, $method_addr, $len);

				if (strpos($val,$search)) {
					if (!in_array($ip, $arr_ips)) {
						array_push($arr_ips, $ip);
						array_push($arr_items2, $items);
					}
				}
			  	$i2++;
		    }
		}
	}
}

// 資料長度
$len = count($arr_items2);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>LOG 檔案上傳</title>
 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<link rel="stylesheet" type="text/css" href="anly.css">
</head>
<body>
    <div class="warp">
    	<form name="f1" id="f1" method="post" enctype="multipart/form-data" action="anly_chk2.php">

    	<h3>LOG ANALYSIS</h3>
    	<div class="f_upload">
    		<input type="file" name="txt_file" id="txt_file">
    		<span class="f_name"></span>
    		<button type="button" class="btn btn-primary btn-lg" id="upfile">LOG 檔案上傳</button>
    	</div>
    	<div class="info">
    		<input type="text" class="form-control mb-2 mr-sm-2" name="skey" id="skey" value="<?=$skey?>" placeholder="輸入搜尋關鍵字...">
    	</div>
		<div class="info">
			<div class="checkbox">
				<?php
					foreach ($sel_arr as $ck => $cv) {
	    		?>
				  <input type="checkbox" name="chk[]" id="<?=$cv?>" value="<?=$cv?>"><?=$cv?>&nbsp; &nbsp; &nbsp; &nbsp; 
				<?php
					}
				?>
			</div>
		</div>
    	<div class="info">符合筆數：<span class="info_len"><?=$len?></span></div>
    	<div class="tb">
    		<div class="css_tr">
			    <div class="css_th tr">
			    	<div class="itema">IP</div>
			    	<div class="itema">DATE</div>
			    	<div class="itemc">MODE</div>
			    	<div class="itemb">URL</div>
			    </div>
				    <?for ($i=0; $i < $len ; $i++) {?>
				    <?
				    	$self = $arr_items2[$i];
				    	$ip = $self->ip;
				    	$time = $self->time;
				    	$method = $self->method;
				    	$url = $self->url;
				    ?>
				    <div class="css_th tr">
				    	<div class="itema"><?=$ip?></div>
				    	<div class="itema"><?=$time?></div>
				    	<div class="itemc"><?=$method?></div>
				    	<div class="itemb"><?=$url?></div>
			    	</div>
					<?}?>
			</div>
		</div>
		</form>
	</div>
</body>
</html>

<script>
window.onload = function () {
    var btn = document.getElementById("upfile");
    btn.disabled=false;
    btn.innerText="LOG 檔案上傳";
};

var chk_arr = JSON.parse('<?php echo json_encode($sel_arr);?>');
var search_arr = JSON.parse('<?php echo json_encode($search_arr);?>');

$.each(Object.values(search_arr), function(index, v) {
    //輸出為-1表示不存在
    var ia = $.inArray(v, Object.values(chk_arr));
    if (ia !== -1) {
    	document.getElementById(v).checked = true;
    }
});

// 檔案上傳 button
$('#upfile').click(function(e) {
	var txt_file = $('#txt_file');
	var btn = document.getElementById("upfile");
	var chk_count = $("input[type=checkbox]:checked").length;
	if (chk_count < 1) {
		alert("請核取搜尋關鍵字!");
		return false;
	}

	// 點選上傳檔案
	txt_file.click();

	txt_file.change("change", function(){
	    var file = this.files[0],
	        fileName = file.name,
	        fileSize = file.size / 1024;

	   		$('.f_name').eq(0).text(fileName);
		    document.getElementById("f1").submit();

	});

	btn.disabled=true;
    btn.innerText="載入中...";
});


</script>