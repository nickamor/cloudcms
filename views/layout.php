<!DOCTYPE html>
<html>
<head>
<title><?php if (isset($pagetitle)) echo " $pagetitle - "; ?>cloud-dbapp</title>
<link rel="stylesheet" href="/static/css/bootstrap.min.css">
<link rel="stylesheet" href="/static/css/dbapp-theme.css">
</head>
<body>
	<div class="container">
		<div class="masthead">
			<h3 class="muted">
				<a href="/">cloud-dbapp</a>
			</h3>
		<?php if (isset($pagetitle)):?>
		<h2><?php echo $pagetitle; ?></h2>
		<?php endif;?>
	</div>
        <?php echo $body_content; ?>
    </div>

	<footer>
		<a href="#">Back to top</a>
	</footer>
</body>
</html>