<ul>
<?php
if (count($comments) == 0) {
    echo 'No comments to display';
} else {
    foreach ($comments as $comment) {
        Flight::render('comment', 
                array(
                        'comment' => $comment
                ));
    }
}

// create new comment
?>
</ul>