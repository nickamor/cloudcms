
<?php
$summaryLength = 400;
$summaryDateFormat = 'l j, Y';
?>

<div class="blog-header">
	<h1 class="blog-title">Blog Title</h1>
	<p class="lead blog-description">Blog subtitle and description</p>
</div>

<?php if (count($blogposts)):?>
<?php foreach ($blogposts as $blogpost): ?>
<div class="blog-post">

	<h2 class="blog-post-title">
		<a href="/blog/<?php echo $blogpost['id']?>">
		<?php echo $blogpost['title']; ?>
		</a>
	</h2>

	<p class="blog-post-meta">
		<?php echo date($summaryDateFormat, $blogpost['time']); if (isset($blogpost['author'])) echo 'by '.$blogpost['author']; ?>
		</p>
	
		<?php if (strlen($blogpost['content']) >= $summaryLength): ?>
		<p><?php echo str_split($blogpost['content'], $summaryLength)[0]; ?>...</p>
		<?php else :?>
		<p><?php echo $blogpost['content']?></p>
		<?php endif;?>
		
		<p>
		<a href="/blog/<?php echo $blogpost['id']?>">Read More</a>
		<?php if (isset($blogpost['comments'])):?>
			 - <a href="/blog/<?php echo $blogpost['id']?>#comments"><?php echo count($blogpost['comments']); ?> comments</a>
		<?php endif; ?>
		</p>
</div>
<?php endforeach; ?>
<?php else:?>
<div class="blog-post">
	<h2 class="blog-post-title">No blog posts to display</h2>
	<p>
		Add some from <a href="/admin">the admin dashboard</a>!
	</p>
</div>
<?php endif;?>

<nav>
	<ul class="pager">
		<li><a href="#">Previous</a></li>
		<li><a href="#">Next</a></li>
	</ul>
</nav>
