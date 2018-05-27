<?php include ('core/koneksi.php') ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Document</title>
<style>
	*{margin:0px auto;}
	#wrapper{padding:1em 0;}
	#hasil{padding:.5em 0; text-align:center;}
	input#analyze{width:100%; font-size:30px; padding:.5em; border-radius:4px; border:1px solid #ccc;}
	
	img, button{transition:.25s ease; -moz-transition:.25s ease; -webkit-transition:.25s ease; -o-transition:.25s ease;}
	.hide{opacity:0;}

</style>
<link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body>

<div id="wrapper" class="container">
	<span class="btn btn-primary" id="refresh">
		Refresh
	</span>
	<input type="text" id="analyze" placeholder="Masukkan kalimat komentar disini">
	
	<div id="hasil">
		<img src="assets/pie.gif" class="hide">
		<div id="out"></div>
		<input type="hidden" name="unique_id" value="">
	</div>
</div>

<script src="assets/jquery-1.12.3.min.js"></script>
<script src="assets/less-1.3.3.min.js"></script>
<script>
	$(function(){
		$("#analyze").on("keypress",function(e){
			if(e.which == 13){
				$("img").removeClass("hide");
				$.ajax({
					url : "api.php",
					method : "GET",
					data : {q : $("#analyze").val()},
					dataType : "json"
				}).done(function(data){
					$("img").addClass("hide");
					if(data['error'] == 0){
						if(data['sentiment'] == 1){
							vclass = 'alert-success';
						}
						else{
							vclass = 'alert-warning';
						}
						$("#out").html("<div class='alert "+vclass+"'>"+data['message']+"</div>");
						$("input[name=unique_id]").val(data['unique_id']);
					}
					else{
						$("#out").html("<div class='alert alert-info'>"+data['message']+"</div>");
					}
				});
			}
		});

		$("#refresh").click(function(){
			location.reload();
		});
	});
</script>
</body>
</html>