<nav><a href="/admin">admin</a> > blogs
</nav>

<table>
	<tr>
		<th>id</th>
		<th>title</th>
		<th>author</th>
		<th>time</th>
	</tr>
<?php foreach ($blogposts as $blogpost): ?>
	<tr>
		<td><a href="/blog/<?php $blogpost['id']; ?>"><?php echo $blogpost['id']; ?></a></td>
		<td><?php echo $blogpost['title']; ?></td>
		<td><?php echo $blogpost['author']; ?></td>
		<td><?php echo $blogpost['time']; ?></td>
		<td><a href="/admin/blog/<?php echo $blogpost['id']; ?>">Update</a></td>
		<td><a href="/admin/blog/<?php echo $blogpost['id']/delete; ?>">Delete</a></td>
	</tr>
<?php endforeach;?>
	<tr>
		<td><a href="/admin/blog/new">Create</a></td>
	</tr>
</table>
