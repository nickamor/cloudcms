<?php
$postDateFormat = 'l j, Y';
?>

<div class="blog-post">
	<div class="container">
		<p class="blog-post-meta"><?php echo date($postDateFormat, $blogpost['time']); ?> by <?php if (isset($blogpost['author'])) echo $blogpost['author']; else echo 'Anonymous'; ?></p>
		<?php echo $blogpost['content']; ?>
	</div>
</div>

<div class="container comments">
	<h3 id="comments">Comments</h3>
	
	<?php if (isset($blogpost['comments'])):?>
		<?php foreach ($blogpost['comments'] as $comment):?>
	<p class="comment-meta">
		<?php if (isset($comment['author'])) echo $comment['author']; else echo 'Anonymous';?> wrote on <?php echo date($postDateFormat, $comment['time']); ?>
 	</p>
 	
	<p><?php echo $comment['content']; ?></p>
	<hr>
		<?php endforeach;?>
	
	<?php else:?>
	<p>Be the first to comment!</p>
	<hr>
	<?php endif;?>
	
	<form id="new-comment" action="" method="POST">
		<input name="id" hidden="true" type="text"
			value="<?php echo $blogpost['id']; ?>">
		<div>
			<input name="author" type="text" placeholder="Name">
		</div>
		<div>
			<textarea name="content" placeholder="Comment" required="required"></textarea>
		</div>
		<input type="submit" value="New Comment">
	</form>
</div>