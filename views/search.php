<?php
if (! isset ( $query )) {
	$query = '';
}
?>

<h2>Search</h2>

<form name="search" action="" method="get">
	<input type="search" value="<?php echo $query; ?>"> <input
		type="submit" value="Search">
</form>
<hr>

<?php if (isset($results)):?>
<?php if (count($results)):?>
<?php foreach ($results as $result):?>
<div>
	<h2>Title</h2>
	<p>Content</p>
</div>
<?php endforeach; ?>
<?php else:?>
<div>
	<p>No search results.</p>
</div>
<?php endif;?>
<?php endif;?>