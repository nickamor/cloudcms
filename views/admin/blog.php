<?php
if (! is_null ( $blog )) {
	$editing = true;
} else {
	$blog = [ 
			'id' => '',
			'title' => '',
			'author' => '',
			'content' => '' 
	];
	$editing = true;
}
?>
<div class="masthead">
	<h2>
<?php if ($editing == true):?>
		Update Blog Post
<?php else:?>
		New Blog Post
<?php endif;?>
	</h2>
</div>

<?php if ( isset ( $result ) ) : ?>
<div <?php if ( isset($result['success'])) :?>
	class="alert alert-success" <?php else:?> class="alert alert-danger"
	<?php endif;?> role="alert">
	<p><?php echo $message; ?></p>
</div>
<?php endif; ?>

<div class="blog-post-editor">
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
					class="form-control">
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