<div class="blog-post">
	<div class="container">
		<p class="blog-time"><?php echo date("r", $blogpost['time']); ?></p>
		<p class="blog-content"><?php echo $blogpost['content']; ?></p>
	</div>
</div>

<div class="container comments">
	<h3>Comments</h3>
	
	<?php if (isset($blogpost['comments'])):?>
	<?php foreach ($blogpost['comments'] as $comment):?>
	<?php if (isset($blogpost['author'])):?>
	<p><?php echo $comment['author']?> wrote on <?php date('r', $comment['time']); ?></p>
	<?php else:?>
	<p>Anonymous wrote on <?php date('r', $comment['time']); ?></p>
	<?php endif;?>
	<p><?php echo $comment['content']; ?></p>
	<?php endforeach;?>
	
	<?php else:?>
	<p>Be the first to comment!</p>
	<?php endif;?>
	
	<form id="new-comment" action="" method="POST">
		<input name="id" hidden="true" type="text" value="<?php echo $blogpost['id']; ?>">
		<div>
			<label for="author">Name</label><input name="author" type="text">
		</div>
		<div>
			<label for="content">Comment</label>
			<textarea name="content"></textarea>
		</div>
		<input type="submit" value="New Comment">
	</form>
</div>