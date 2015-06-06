<?php
$resultsDateFormat = 'l j, Y';
?>

<div>
	<h2><?php echo count($blogs); ?> Blogs containing '<?php echo $query; ?>'</h2>
	<hr>
</div>
<?php

if (isset ( $blogs ) && count ( $blogs )) :
	
	$keys = array_keys ( $blogs );
	$lastkey = end ( $keys );
	
	foreach ( $blogs as $key => $blog ) :
		?>
<div>
	<h3>
		<a href="/blogs/<?php echo $blog['id']; ?>"><?php echo $blog['title']; ?></a>
	</h3>
	<p class="blog-post-meta">
	<?php echo date($resultsDateFormat, $blog['time']); if (isset($blog['author'])) echo ' by '.$blog['author']; ?>
	</p>
	
	<?php if ($key != $lastkey):?>
	<hr>
	<?php endif;?>
</div>
<?php endforeach; ?>
<?php else:?>
<div>
	<p>No search results.</p>
</div>
<?php endif;?>