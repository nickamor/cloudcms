<!DOCTYPE html>
<html>
<head>
<title><?php if (isset($pagetitle)) echo " $pagetitle - "; ?>cloud-dbapp</title>
<link rel="stylesheet" href="/static/css/bootstrap.min.css">
<link rel="stylesheet" href="/static/css/dbapp-theme.css">
</head>
<body>
	<div class="container">
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="/">cloud-dbapp</a>
				</div>
				<form action="/search" method="get" class="navbar-form navbar-right">
					<input class="form-control" type="search" placeholder="Search...">
				</form>
			</div>
		</nav>
		<div class="masthead">
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