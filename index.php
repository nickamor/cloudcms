<?php
require 'aws/aws-autoloader.php';
use Aws\DynamoDb\DynamoDbClient;

require 'flight/Flight.php';

static $appTableName = 'dbapp-table';

class DbHelper
{
	public function getClient()
	{
		return DynamoDbClient::factory(array(
                            'region' => 'ap-southeast-2'
                    ));
	}
}

class Topic
{

    /**
     * createTopic
     */
    public static function createTopic ($newTopic)
    {
        $createTopicSuccess = null;
        
        if ($createTopicSuccess) {
            Flight::redirect('/' . $topic, 201);
        } else {
            // could not create topic
        }
    }

    /**
     * readTopic - show all threads within a topic
     */
    public static function readTopic ($topicID)
    {
        $topicExists = null;
        
        if ($topicExists) {
            // return topic
        } else {
            return null;
        }
    }

    /**
     * updateTopic
     */
    public static function updateTopic ($topicID, $newTopic)
    {
        $topicExists = null;
        
        if ($topicExists) {
            // update topic
        } else {
            // create topic
        }
    }

    /**
     * deleteTopic
     */
    public static function deleteTopic ($topicID)
    {
        $topicExists = null;
        
        if ($topicExists) {
            // update topic
        } else {
            // no such topic exists
        }
    }
}

class Thread
{

    /**
     * createThread - start a new thread
     */
    public static function createThread ($topicID, $newThread)
    {
        $thread = array(
                'id' => array(
                        'N' => $newID
                ),
                'type' => array(
                        'S' => 'thread'
                ),
                'title' => array(
                        'S' => $threadTitle
                ),
                'text' => array(
                        'S' => $treadText
                )
        );
        $thread['creationDate'] = array(
                'N' => getUnixTime()
        );
        
        $createThreadSuccess = null;
        
        if ($createThreadSuccess) {
            // redirect to newly created thread
            Flight::redirect('/' . $threadID, 201);
        } else {
            // could not create thread
        }
    }

    /**
     * readThread - show all comments within a topic
     *
     * @param int $thread
     *            Thread ID
     */
    public static function readThread ($threadID)
    {
        $threadExists = null;
        
        if ($threadExists) {
            // read thread
        } else {
            // no such thread exists
        }
    }

    /**
     * updateThread
     */
    public static function updateThread ($threadID, $newThread)
    {
        $threadExists = null;
        
        if ($threadExists) {
            // update thread
        } else {
            // create thread
        }
    }

    /**
     * deleteThread - delete a thread
     *
     * @param int $thread
     *            Thread ID of thread to be deleted
     */
    public static function deleteThread ($threadID)
    {
        $threadExists = null;
        
        if ($threadExists) {
            // create thread
        } else {
            // no such thread exists
        }
    }
}

class Comment
{

    /**
     * createComment
     */
    public static function createComment ($threadID, $newComment)
    {
        // create comment
    }

    /**
     * readComment
     */
    public static function readComment ($commentID)
    {
        $commentExists = null;
        
        if ($commentExists) {
            // create thread
        } else {
            // no such comment exists
        }
    }

    /**
     * updateComment
     */
    public static function updateComment ($commentID, $newComment)
    {
        $commentExists = null;
        
        if ($commentExists) {
            // create thread
        } else {
            // create comment
        }
    }

    /**
     * deleteComment
     */
    public static function deleteComment ($commentID)
    {
        $commentExists = null;
        
        if ($commentExists) {
            // create thread
        } else {
            // no such comment exists
        }
    }
}

/**
 * show all topics
 */
Flight::route('GET /', 
        function  ()
        {
        	echo "hello world";
            // get topics
            $client = Flight::db()->getClient();
        	
            $topics = $client->getIterator('Scan', 
                    array(
                            'TableName' => $appTableName
                    ));
            
            // render
            Flight::render('header', 
                    array(
                            'heading' => 'All Topics'
                    ), 'header_content');
            Flight::render('topics_body', 
                    array(
                            'topics' => $topics
                    ), 'body_content');
            
            Flight::render('layout', 
                    array(
                            'title' => 'dbapp'
                    ));
        });

/**
 * create a new topic
 */
Flight::route('POST /', 
        function  ()
        {
            $createTopicSuccess = null;
            
            if ($createTopicSuccess) {
                // redirect to new topic
            }
        });

/**
 * get topic
 */
Flight::route('GET /@topic', 
        function  ()
        {
            $topicExists = null;
            
            if ($topicExists) {
                
                Flight::render('topic_header', 
                        array(
                                'topic' => $topic
                        ), 'header_content');
                Flight::render('threads_body', 
                        array(
                                'topic' => $topic
                        ), 'body_content');
                
                Flight::render('layout', 
                        array(
                                'title' => 'dbapp'
                        ));
            }
        });

/**
 * post new comment
 */
Flight::route('POST /@topic', function  ()
{
    // post new comment
});

Flight::route('PUSH /@topic', function  ()
{
    // update topic
});

Flight::route('DELETE /@topic', function  ()
{
    // delete topic
});

/**
 * get thread
 */
Flight::route('GET /@topic/@thread', 
        function  ()
        {
            $threadExists = null;
            
            if ($threadExists) {
                // thread exists
            } else { 
            	Flight::render('header',
            	        array(
            	                'header' => 'Error'
            	        ), 'header_content');
            	Flight::render('body',
            	        array(
            	                'topic' => 'No such thread exists'
            	        ), 'body_content');
            	
            	Flight::render('layout',
            	        array(
            	                'title' => 'dbapp - No such thread exists'
            	        ));
            }
        });

Flight::route('POST /@topic/@thread', 
        function  ()
        {
            // post comment
        });

Flight::route('PUT /@topic/@thread', 
        function  ()
        {
            // update thread
        });

Flight::route('DELETE /@topic/@thread', 
        function  ()
        {
            // delete thread
        });

Flight::route('PUT /@topic/@thread/@comment', 
        function  ()
        {
            // update comment
        });

Flight::route('DELETE /@topic/@thread/@comment', 
        function  ()
        {
            // delete comment
        });

/**
 * set up DynamoDB client
 */
//Flight::register('db', 'DbHelper');

Flight::start();
?>
