<?php
require 'flight/Flight.php';

require 'aws/aws-autoloader.php';
use Aws\DynamoDb\DynamoDbClient;
use Aws\CloudFront\Exception\Exception;

class DbHelper
{

    public static $appTableName = 'dbapp-table';

    public static function client()
    {
        $factoryOptions = array(
            'region' => 'ap-southeast-2'
        );
        
        if (! isset($_ENV['AWS_ACCESS_KEY_ID'])) {
            $factoryOptions['profile'] = 'dbapp-profile';
        }
        
        return DynamoDbClient::factory($factoryOptions);
    }
}

class Topic
{

    public static function all()
    {
        // get all
    }

    public static function where($condition)
    {
        // get some
    }

    /**
     * createTopic
     */
    public static function createTopic($newTopic)
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
    public static function readTopic($topicID)
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
    public static function updateTopic($topicID, $newTopic)
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
    public static function deleteTopic($topicID)
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
    public static function createThread($topicID, $newThread)
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
    public static function readThread($threadID)
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
    public static function updateThread($threadID, $newThread)
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
    public static function deleteThread($threadID)
    {
        $threadExists = null;
        
        if ($threadExists) {
            // create thread
        } else {
            // no such thread exists
        }
    }
}

/**
 * Comment - model for comments
 *
 * @author nick
 *        
 */
class Comment
{

    /**
     * createComment
     */
    public static function createComment($threadID, $newComment)
    {
        // create comment
    }

    /**
     * readComment
     */
    public static function readComment($commentID)
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
    public static function updateComment($commentID, $newComment)
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
    public static function deleteComment($commentID)
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
 * methods for setting up and tearing down the database
 *
 * @author nick
 *        
 */
class Migration
{

    /**
     * bring up database
     */
    public static function up()
    {
        echo "creating table...\n";
        
        $client = DbHelper::client();
        
        try {
            $result = $client->createTable(array(
                'TableName' => DbHelper::$appTableName,
                'AttributeDefinitions' => array(
                    array(
                        'AttributeName' => 'id',
                        'AttributeType' => 'S'
                    ),
                    array(
                        'AttributeName' => 'created',
                        'AttributeType' => 'N'
                    )
                ),
                'KeySchema' => array(
                    array(
                        'AttributeName' => 'id',
                        'KeyType' => 'HASH'
                    ),
                    array(
                        'AttributeName' => 'created',
                        'KeyType' => 'RANGE'
                    )
                ),
                'ProvisionedThroughput' => array(
                    'ReadCapacityUnits' => 10,
                    'WriteCapacityUnits' => 20
                )
            ));
            
            $client->waitUntilTableExists(array(
                'TableName' => DbHelper::$appTableName
            ));
        } catch (Exception $e) {
            echo "Unexpected error.\n";
            print_r($e);
            return;
        }
        
        echo 'Table created.';
    }

    /**
     * tear down database table
     */
    public static function down()
    {
        echo "Deleting table...\n"; // double string
        
        $client = DbHelper::client();
        
        try {
            $result = $client->deleteTable(array(
                'TableName' => DbHelper::$appTableName
            ));
        } catch (Aws\DynamoDB\Exception\ResourceNotFoundException $e) {
            echo 'No such table exists.\n';
            return;
        } catch (Exception $e) {
            print_r($e);
        }
        
        $client->waitUntilTableNotExists(array(
            'TableName' => DbHelper::$appTableName
        ));
        
        echo 'Table deleted.';
    }
}

class Admin
{

    public static function listTables()
    {
        $client = DbHelper::client();
        
        $tablesIter = $client->getIterator('ListTables');
        
        echo "<ul>";
        foreach ($tablesIter as $tableName) {
            printf("<li>%s</li>", $tableName);
        }
        echo "</ul>";
    }

    public static function hello()
    {
        echo 'Hello World!';
    }
    
    public static function showTable()
    {
        $client = DbHelper::client();

        $itemsIter = $client->getIterator ( 'Scan', array (
            'TableName' => DbHelper::$appTableName
        ) );
        
        echo "<ul>";
        foreach ($itemsIter as $item)
        {
        	printf("<li>%s - %d</li>", $item['id'], $item['created']);
        }
        echo "</ul>";
    }
}

Flight::route('/admin/createtable', array(
    'Migration',
    'up'
));

Flight::route('/admin/deletetable', array(
    'Migration',
    'down'
));

Flight::route('/admin/listtables', array(
    'Admin',
    'listTables'
));

Flight::route('/admin/showtable', array(
    'Admin',
    'showTable'
));

Flight::route('/admin/hello', array(
    'Admin',
    'hello'
));

class View
{

    /**
     * show all topics
     */
    public static function allTopics()
    {
        // get topics
        $client = Flight::db()->client();
        
        $topics = $client->getIterator('Scan', array(
            'TableName' => DbHelper::$appTableName
        ));
        
        // render
        Flight::render('header', array(
            'heading' => 'All Topics'
        ), 'header_content');
        Flight::render('topics_body', array(
            'topics' => $topics
        ), 'body_content');
        
        Flight::render('layout', array(
            'title' => 'dbapp'
        ));
    }
}

Flight::route('GET /', array(
    'View',
    'allTopics'
));

/**
 * create a new topic
 */
Flight::route('POST /', function () {
    $createTopicSuccess = null;
    
    if ($createTopicSuccess) {
        // redirect to new topic
    }
});

/**
 * get topic
 */
Flight::route('GET /@topic', function () {
    $topicExists = null;
    
    if ($topicExists) {
        
        Flight::render('topic_header', array(
            'topic' => $topic
        ), 'header_content');
        Flight::render('threads_body', array(
            'topic' => $topic
        ), 'body_content');
        
        Flight::render('layout', array(
            'title' => 'dbapp'
        ));
    }
});

/**
 * post new comment
 */
Flight::route('POST /@topic', function () {
    // post new comment
});

Flight::route('PUSH /@topic', function () {
    // update topic
});

Flight::route('DELETE /@topic', function () {
    // delete topic
});

/**
 * return view
 */
function view($header, $body)
{
    // return render
}

/**
 * get thread
 */
Flight::route('GET /@topic/@thread', function () {
    $threadExists = null;
    
    if ($threadExists) {
        // thread exists
    } else {
        Flight::render('header', array(
            'header' => 'Error'
        ), 'header_content');
        Flight::render('body', array(
            'topic' => 'No such thread exists'
        ), 'body_content');
        
        Flight::render('layout', array(
            'title' => 'dbapp - No such thread exists'
        ));
    }
});

Flight::route('POST /@topic/@thread', function () {
    // post comment
});

Flight::route('PUT /@topic/@thread', function () {
    // update thread
});

Flight::route('DELETE /@topic/@thread', function () {
    // delete thread
});

Flight::route('PUT /@topic/@thread/@comment', function () {
    // update comment
});

Flight::route('DELETE /@topic/@thread/@comment', function () {
    // delete comment
});

Flight::register('db', 'DbHelper');

Flight::start();
?>
