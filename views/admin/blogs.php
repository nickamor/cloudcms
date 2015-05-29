
<ul class="nav nav-pills">
  <li role="presentation"><a href="/admin/blogs/new">Create</a></li>
  <li role="presentation"><a href="/admin/blogs/newfake">Create Fake</a></li>
  <li role="presentation"><a href="/admin/blogs/deleteall">Delete All</a></li>
</ul>

<hr>

<table class="table table-striped">
	<tr>
		<th>id</th>
		<th>title</th>
		<th>author</th>
		<th>time</th>
		<!-- management options -->
		<th></th>
		<th></th>
	</tr>
<?php foreach ($blogs as $blog): ?>
	<tr>
		<td><a href="/blogs/<?php echo $blog['id']; ?>"><?php echo $blog['id']; ?></a></td>
		<td><?php if (isset($blog['title'])) echo $blog['title']; ?></td>
		<td><?php if (isset($blog['author'])) echo $blog['author']; ?></td>
		<td><?php if (isset($blog['time'])) echo date("r", $blog['time']); ?></td>
		<td><a href="/admin/blogs/<?php echo $blog['id']; ?>">Update</a></td>
		<td><a href="/admin/blogs/<?php echo $blog['id'] . '/delete'; ?>">Delete</a></td>
	</tr>
<?php endforeach;?>
</table>
