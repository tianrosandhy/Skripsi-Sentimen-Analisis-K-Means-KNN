<?php
if(!isset($menu))
	$menu = 0;
$start = microtime(true);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sistem Analisis Sentimen</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div id="wrapper">
	<h1>Sistem Analisis Sentimen</h1>
	<nav class="navbar navbar-inverse">
		<ul class="nav navbar-nav">
			<li class="<?php is_same(1, $menu, "active")?>"><a href="index.php">Single Data Analysis</a></li>
			<li class="<?php is_same(2, $menu, "active")?>"><a href="multiple.php">Multiple Data Analysis</a></li>
			<li class="<?php is_same(3, $menu, "active")?>"><a href="rekap.php">Rekap Analisis</a></li>
		</ul>
	</nav>