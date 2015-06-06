<?php if (!$tableExists): ?>

<ul class="nav nav-pills">
	<li role="presentation"><a href="/admin/install">Create Database Table</a></li>
</ul>

<?php else: ?>
<ul class="nav nav-pills">
	<li role="presentation"><a href="/admin/blogs">Manage Blog Posts</a></li>
</ul>
<ul class="nav nav-pills">
	<li role="presentation"><a href="/admin/uninstall">Delete Database Table</a></li>
</ul>

<?php endif; ?>