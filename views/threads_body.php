<ul>
<?php
if (count($threads) == 0) {
    echo 'No threads to display';
} else {
    foreach ($threads as $thread) {
        Flight::render('thread', 
                array(
                        'thread' => $thread
                ));
    }
}

// create new thread
?>
</ul>