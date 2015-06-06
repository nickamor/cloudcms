
<?php
$summaryLength = 400;
$summaryDateFormat = 'l j, Y';
?>

<div class="masthead blog-header">
	<h1 class="blog-title">Blog Title</h1>
	<p class="lead blog-description">Blog subtitle and description</p>
</div>

<?php if (isset($blogs) && !is_null($blogs) && count($blogs)):?>
<?php

	$keys = array_keys ( $blogs );
	$lastkey = end ( $keys );
	?>
<?php foreach ($blogs as $key => $blog): ?>
<div class="blog-post">
	<h2 class="blog-post-title">
		<a href="/blogs/<?php echo $blog['id']?>">
		<?php echo $blog['title']; ?>
		</a>
	</h2>

	<p class="blog-post-meta">
	<?php echo date($summaryDateFormat, $blog['time']); if (isset($blog['author'])) echo ' by '.$blog['author']; ?>
	</p>
	
	<?php if (strlen($blog['content']) > $summaryLength): ?>
	<p><?php echo str_split($blog['content'], $summaryLength)[0]; ?>...</p>
	<?php else :?>
	<p><?php echo $blog['content']?></p>
	<?php endif;?>
		
	<p>
		<a href="/blogs/<?php echo $blog['id']?>">Read More</a>
	<?php if (isset($blog['comments'])):?>
		 - <a href="/blogs/<?php echo $blog['id']?>#comments"><?php echo count($blog['comments']); ?> comments</a>
	<?php endif; ?>
	</p>
	
	<?php if ($key != $lastkey):?>
	<hr>
	<?php endif;?>
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

<?php if (isset($pages) && count($pages) > 0):?>
<nav>
	<ul class="pager">
		<?php if (isset($pages['previous'])):?>
		<li class="previous"><a
			href="./<?php if ($pages['previous'] != 0) echo $pages['previous']; ?>"><span
				aria-hidden="true">&larr;</span> Previous</a></li>
		<?php endif;?>
		
		<?php if (isset($pages['next'])):?>
		<li class="next"><a href="./<?php echo $pages['next']?>">Next <span
				aria-hidden="true">&rarr;</span></a></li>
		<?php endif; ?>
	</ul>
</nav>
<?php endif;?>
