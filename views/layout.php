<!DOCTYPE html>
<html>
<head>
<title><?php if (isset($pagetitle)) echo " $pagetitle - "; ?>cloud-dbapp</title>
<link rel="stylesheet" href="/static/css/bootstrap.min.css">
<link rel="stylesheet" href="/static/css/bootstrap-theme.min.css">
<style type="text/css">
.navbar .nav li {
	display: table-cell;
	width: 1%;
	float: none;
}
</style>
</head>
<body>
	<div class="masthead">
		<h3 class="muted">
			<a href="/">cloud-dbapp</a>
		</h3>
		<!-- 
		<div class="navbar">
			<ul class="nav">
				<li class="active"><a href="/">Blog</a></li>
				<li><a href="/about">About</a></li>
			</ul>
		</div>
		 -->
		<?php if (isset($pagetitle)):?>
		<h1><?php echo $pagetitle; ?></h1>
		<?php endif;?>
	</div>

	<div class="container">
        <?php echo $body_content; ?>
    </div>
</body>
</html>