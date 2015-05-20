<p><?php
foreach ($comments as $comment) {
    Flight::render('comment', array(
            'comment' => $comment
    ));
}

// create new comment

?></p>