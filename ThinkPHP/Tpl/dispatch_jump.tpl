<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<link href="__PUBLIC__/Admin/assets/css/bootstrap.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="__PUBLIC__/Admin/assets/css/ace.min.css" />
	<link rel="stylesheet" href="__PUBLIC__/Admin/assets/css/font-awesome.min.css" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>跳转提示</title>
	<style type="text/css">
		*{ padding: 0; margin: 0; }
		body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
		.system-messageq{ padding:0 0 48px;margin:150px auto;width:400px;}
		.system-messageq h3{ font-size: 50px; font-weight: normal; line-height: 120px; margin-bottom: 12px;border:1px solid #ccc}
		.system-messageq .jump{ padding-top: 10px;font-size:12px;}
		.system-messageq .jump a{ color: #333;}
		.system-messageq .success,.system-message .error{ line-height: 1.8em; font-size: 23px ;text-align: center;}
		.system-messageq .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
	</style>
</head>
<body class="login-layout" style="background-color: #fff;">
<div class="system-messageq" style="background: #fff">
	<!--p style="height:35px;background:<?php if($message !== '' ){ echo 'url(__PUBLIC__/img/msg_top_bg_new.png)'; }else{ echo 'url(__PUBLIC__/img/msg_top_bg.png)'; } ?> #ccc;padding-left:10px;line-height:35px;color:white">提醒</p-->

	<div style="padding:24px;border: 2px solid #1ab394;">
		<present name="message">
			<div class="alert alert-block alert-success" style="border-radius:5px;" >
				<div style="margin:0 0 15px 0 ;padding:0 0 10px 0;font-size:16px;border-bottom: 1px solid #c9e2b3">成功提示</div>

				<div>
					<span style="font-size:20px;margin:0 10px 0 0;" class="  icon-ok"></span><?php echo($message); ?></div>
				<div class="jump" style="float:right;padding-right:5px;">
					页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b>
				</div>
				<div style="clear: both"></div>
			</div>
			<div class="success"></div>
			<else/>
			<div class="alert alert-danger " style="border-radius:5px;" >
				<div style="margin:0 0 15px 0 ;padding:0 0 10px 0;font-size:16px;border-bottom: 1px solid#1ab394">错误提示</div>

				<div>
					<span style="font-size:20px;margin:0 10px 0 0;" class=" icon-remove"></span><?php echo($error); ?></div>
				<div class="jump" style="float:right;padding-right:5px;">
					页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b>
				</div>
				<div style="clear: both"></div>
			</div>

		</present>

	</div>
	<p class="detail"></p>

</div>
<script type="text/javascript">
	(function(){
		var wait = document.getElementById('wait'),href = document.getElementById('href').href;
		var interval = setInterval(function(){
			var time = --wait.innerHTML;
			if(time == 0) {
				location.href = href;
				clearInterval(interval);
			};
		}, 1000);
	})();
</script>
</body>
</html>