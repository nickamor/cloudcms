<ul>
<?php
foreach ($topics as $topic) {
    printf('<li><a href="%s">%s</a></li>', $topic['id'], $topic['name']);
}

// create new topic

?>
</ul>