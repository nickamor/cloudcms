<?php
$editing = false;
if (! isset ( $blogpost )) {
	$blogpost = [ 
			'id' => '',
			'title' => '',
			'author' => '',
			'content' => '' 
	];
	$editing = true;
}
?>
<div class="masthead">
	<h2></h2>
</div>

<div class="blog-post-editor">
	<form name="blog-post-editor" action="" method="post"
		class="form-horizontal">
		<input name="id" id="id" type="text"
			value="<?php echo $blogpost['id']; ?>" hidden="true">
		<div class="form-group">
			<label for="title" class="control-label col-sm-2">Title</label>
			<div class="col-sm-3">
				<input name="title" type="text" placeholder="Title"
					value="<?php echo $blogpost['title']; ?>" required="required"
					class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="author" class="control-label col-sm-2">Author</label>
			<div class="col-sm-3">
				<input name="author" type="text" placeholder="Author"
					value="<?php echo $blogpost['author']; ?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="control-label col-sm-2">Content</label>
			<div class="col-sm-6">
				<textarea name="content" rows="4" cols="80" placeholder="Content"
					required="required" class="form-control"><?php echo $blogpost['content']?></textarea>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-6">
				<input name="submit" type="submit" value="New Blog Post"
					class="btn btn-default">
			</div>
		</div>
	</form>
</div>