
<?php if (count($blogposts)):?>
<?php foreach ($blogposts as $blogpost): ?>
<div class="container blog-entry">
	<h3>
		<a href="/blog/<?php echo $blogpost['id']?>"><?php echo $blogpost['title']; ?></a>
	</h3>

	<p>Created <?php echo date('r', $blogpost['time']); if (isset($blogpost['author'])) echo 'by '.$blogpost['author']; ?></p>
	
	<?php if (strlen($blogpost['content']) >= 140): ?>
	<p><?php echo str_split($blogpost['content'], 140)[0]; ?>...</p>
	<?php else :?>
	<p><?php echo $blogpost['content']?></p>
	<?php endif;?>

	<?php if (isset($blogpost['comments'])):?>
	<p><?php echo count($blogpost['comments']); ?> comments.</p>
	<?php endif;?>
	
	<p>
		<a href="/blog/<?php echo $blogpost['id']; ?>">Read more</a>
	</p>
</div>
<?php endforeach; ?>
<?php else:?>
<div>
	<h3>No blog posts to display</h3>
	<p>
		Add some from <a href="/admin">the admin dashboard</a>!
	</p>
</div>
<?php endif;?>
