<?php
if (! isset ( $blogpost )) {
	$blogpost = [ 
			'title' => '',
			'author' => '',
			'content' => '' 
	];
}
?>

<form name="blog-post-editor" action="" method="post">
	<div>
		<input name="title" type="text" placeholder="Title"
			value="<?php echo $blogpost['title']; ?>" required="required">
	</div>
	<div>
		<input name="author" type="text" placeholder="Author"
			value="<?php echo $blogpost['author']; ?>">
	</div>
	<div>
		<textarea name="content" rows="4" cols="80" placeholder="Content"
			required="required"><?php echo $blogpost['content']?></textarea>
	</div>
	<input name="submit" type="submit" value="Create Blog Post">
</form>