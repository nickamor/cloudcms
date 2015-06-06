<?php
if (! is_null ( $blog )) {
	$editing = true;
} else {
	$blog = [ 
			'id' => '',
			'time' => '',
			'title' => '',
			'author' => '',
			'content' => '' 
	];
	$editing = false;
}

$postDateFormat = 'r';
?>

<div class="masthead">
<?php if ($editing == true):?>
	<h2>Update Blog Post</h2>
	<ul class="nav nav-pills">
		<li role="presentation"><a href="<?php echo $blog['id']; ?>/delete">Delete</a></li>
		<li role="presentation"><a href="<?php echo $blog['id']; ?>/fake">Create
				Fake Comments</a></li>
	</ul>
<?php else:?>
	<h2>New Blog Post</h2>
<?php endif;?>
</div>

<?php if ( isset ( $result ) ) : ?>
<div <?php if ( isset($result['success'])) :?>
	class="alert alert-success" <?php else:?> class="alert alert-danger"
	<?php endif;?> role="alert">
	<p><?php echo $result['message']; ?></p>
</div>
<?php endif; ?>

<div class="blog-post-editor">
	<?php if ($editing): ?>
	<p class="blog-post-meta">Created <?php echo date($postDateFormat, $blog['time']); ?></p>
	<?php endif; ?>

	<form name="blog-post-editor" action="" method="post"
		class="form-horizontal">
		<input name="id" id="id" type="text"
			value="<?php echo $blog['id']; ?>" hidden="true">
		<div class="form-group">
			<label for="title" class="control-label col-sm-2">Title</label>
			<div class="col-sm-3">
				<input name="title" type="text" placeholder="Title"
					value="<?php if (isset($blog['title'])) echo $blog['title']; ?>"
					required="required" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="author" class="control-label col-sm-2">Author</label>
			<div class="col-sm-3">
				<input name="author" type="text" placeholder="Author"
					value="<?php if (isset($blog['author'])) echo $blog['author']; ?>"
					required="required" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="control-label col-sm-2">Content</label>
			<div class="col-sm-6">
				<textarea name="content" rows="4" cols="80" placeholder="Content"
					required="required" class="form-control"><?php if (isset($blog['content'])) echo $blog['content']?></textarea>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-6">
				<input name="submit" type="submit" <?php if ($editing == true):?>
					value="Update Blog Post" <?php else:?> value="New Blog Post"
					<?php endif;?> class="btn btn-default">
			</div>
		</div>
	</form>
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
	<p>No comments to display.</p>
	<?php endif;?>
</div>