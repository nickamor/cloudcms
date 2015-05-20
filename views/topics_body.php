<ul>
<?php
if (count($topics) == 0) {
    echo 'No topics to display';
} else {
    foreach ($topics as $topic) {
        Flight::render('topic', 
                array(
                        'topic' => $topic
                ));
    }
}

// create new topic

?>
</ul>