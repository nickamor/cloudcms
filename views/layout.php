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
					<div class="col-lg-9">
						<div class="input-group">
							<input type="search" placeholder="Search..." id="search" name="q"
								class="form-control"> <span class="input-group-btn">
								<button class="btn btn-default" type="button">
									<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
								</button>
							</span>
						</div>
					</div>
				</form>

			</div>
		</nav>
        <?php echo $body_content; ?>
    </div>

	<footer>
		<a href="#">Back to top</a>
	</footer>
</body>
</html>