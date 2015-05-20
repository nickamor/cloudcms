<p><?php
foreach ($threads as $thread) {
    Flight::render('thread', array(
            'thread' => $thread
    ));
    
    // create thread
}
?></p>