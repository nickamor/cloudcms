<?php
$postDateFormat = 'l j, Y';
?>

<div class="blog-post">
	<h2><?php echo $blog['title']; ?></h2>

	<p class="blog-post-meta"><?php echo date($postDateFormat, $blog['time']); ?> by <?php if (isset($blog['author'])) echo $blog['author']; else echo 'Anonymous'; ?></p>
		
	<?php foreach (explode("\n\n", $blog['content']) as $paragraph): ?>
	<p><?php echo $paragraph; ?></p>
 	<?php endforeach;?>
</div>

<div class="comments">
	<h3 id="comments">Comments</h3>
	
	<?php if (isset($blog['comments'])):?>
	<?php foreach ($blog['comments'] as $comment):?>
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
	
	<form id="new-comment" action="" method="POST" class="form-horizontal">
		<input name="id" hidden="true" type="text"
			value="<?php echo $blog['id']; ?>">
		<div class="form-group">
			<label for="author" class="col-sm-2 control-label">Name</label>
			<div class="col-sm-3">
				<input id="author" name="author" type="text" placeholder="Name"
					required="required" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="comment" class="col-sm-2 control-label">Comment</label>
			<div class="col-sm-6">
				<textarea id="content" name="content" placeholder="Comment"
					required="required" class="form-control"></textarea>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-6">
				<input type="submit" value="New Comment" class="btn btn-default">
			</div>
		</div>
	</form>
</div>