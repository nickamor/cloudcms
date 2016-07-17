<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 16/07/2016
 * Time: 6:34 PM
 */

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception\DynamoDbException;

/**
 * wrapper for database functions, get/set state of application
 *
 * @author nick
 *
 */
class Model
{
    /**
     * name of the database table for blog posts
     */
    private static $datatable = 'cloudcms-content';

    /**
     * retrieve a database client handle
     *
     * @return \Aws\DynamoDb\DynamoDbClient
     */
    private static function client()
    {
        $factoryOptions = [
            'version' => '2012-08-10',
            'region' => 'ap-southeast-2'
        ];

        // use profile configuration file if environment isn't set
        // useful for local testing
        if (!isset ($_ENV ['AWS_ACCESS_KEY_ID'])) {
            $factoryOptions ['profile'] = 'cloudcms';
        }

        return new DynamoDbClient($factoryOptions);
    }

    /**
     * create a new blog post item in the database
     *
     * @param $blogpost array
     *            the blog post to add
     * @return int ID of the newly added blog post, or 0 on error
     */
    public static function newBlogPost($blogpost)
    {
        $client = Model::client();
        $marshaler = new Marshaler ();

        // get a unique id
        do {
            $newID = time() * rand(100, 200);

            $existingBlogpost = Model::getBlogPost($newID);
        } while ($existingBlogpost != null);

//            $response = $client->getItem([
//                'TableName' => Model::$datatable,
//                'Key' => [
//                    'id' => [
//                        'N' => $newID
//                    ]
//                ]
//            ]);
//        } while (isset ($response ['Item']));

        // set required attributes
        $blogpost ['time'] = time();
        $blogpost ['id'] = $newID;

        // add new blog post to database
        try {
            $client->putItem(array(
                'TableName' => Model::$datatable,
                'Item' => $marshaler->marshalItem($blogpost)
            ));
        } catch (DynamoDbException $e) {
            return 0;
        }

        return $blogpost ['id'];
    }

    /**
     * retrieve blog post object from database
     *
     * @param int $id
     *            ID of blog post
     * @return array the blog post, or null if non-existent
     */
    public static function getBlogPost($id)
    {
        $client = Model::client();
        $marshaler = new Marshaler ();

        try {
            $request = [
                'TableName' => Model::$datatable,
                'Key' => [
                    'id' => [
                        'N' => $id
                    ]
                ]
            ];

            $response = $client->getItem($request);

            // format database output
            return $marshaler->unmarshalItem($response ['Item']);
        } catch (DynamoDbException $e) {
            return null;
        }
    }

    /**
     * update a given blog post
     *
     * @param array $blogpost the blog content
     * @return bool result
     */
    public static function updateBlogPost($blogpost)
    {
        $client = Model::client();
        $marshaler = new Marshaler ();

        try {
            $client->updateItem([
                'TableName' => Model::$datatable,
                'Key' => [
                    'id' => [
                        'N' => $blogpost ['id']
                    ]
                ],
                'UpdateExpression' => 'SET title = :title, author = :author, content = :content',
                'ExpressionAttributeValues' => [
                    ':title' => [
                        'S' => $blogpost ['title']
                    ],
                    ':author' => [
                        'S' => $blogpost ['author']
                    ],
                    ':content' => [
                        'S' => $blogpost ['content']
                    ]
                ]
            ]);
        } catch (DynamoDbException $e) {
            return false;
        }

        return true;
    }

    /**
     * delete a given blog post
     *
     * @param int $id
     *            ID of blog post to delete
     * @return bool result
     */
    public static function deleteBlog($id)
    {
        $client = Model::client();

        $request = [
            'TableName' => Model::$datatable,
            'Key' => [
                'id' => [
                    'N' => $id
                ]
            ]
        ];

        $response = $client->deleteItem($request);

        // TODO: test response
        if ($response) {
            return true;
        }

        return true;
    }

    /**
     * getAllBlogs - get all blog posts
     *
     * @return array an array of blog post objects, or null if there are none or an error occurred
     */
    public static function getAllBlogs()
    {
        $client = Model::client();
        $marshaler = new Marshaler ();

        $blogs = [];

        // The Scan API is paginated. Issue the Scan request multiple times.
        do {
            $request = [
                'TableName' => Model::$datatable,
                'Limit' => 10
            ];

            // Add the ExclusiveStartKey if we got one back in the previous response
            if (isset ($response) && isset ($response ['LastEvaluatedKey'])) {
                $request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
            }

            try {
                $response = $client->scan($request);
            } catch (DynamoDbException $e) {
                return null;
            }
            foreach ($response ['Items'] as $blog) {
                array_push($blogs, $marshaler->unmarshalItem($blog));
            }
        } while (isset ($response ['LastEvaluatedKey'])); // loop while there are more results

        return $blogs;
    }

    /**
     * getAllBlogsPaginated - get a page of blog posts
     *
     * @param int $page
     *            page to return
     * @return array of blog posts from db
     */
    public static function getBlogsPage($page = 0)
    {
        $client = Model::client();

        $currentPage = 0;

        // scan through to the requested page
        do {
            // set scan options for paging
            $request = array(
                'TableName' => Model::$datatable,
                'Count' => true,
                'Limit' => 10
            );

            // Add the ExclusiveStartKey if we got one back in the previous response
            if (isset ($response) && isset ($response ['LastEvaluatedKey'])) {
                $request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
                $currentPage++;
            }

            try {
                $response = $client->scan($request);
            } catch (DynamoDbException $e) {
                return null;
            }
        } while (isset ($response ['LastEvaluatedKey']) && $currentPage < $page);

        $marshaler = new Marshaler ();

        // get blog posts from response
        $blogs = [];
        foreach ($response ['Items'] as $item) {
            array_push($blogs, $marshaler->unmarshalItem($item));
        }

        // get pagination button values
        $pages = [];
        if (isset ($response) && $page > 0) {
            $pages ['previous'] = $page - 1;
        }

        if (isset ($response) && isset ($response ['LastEvaluatedKey'])) {
            // see if the next page isn't blank
            $request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
            $response = $client->scan($request);
            if (isset ($response ['Count']) && $response ['Count'] > 0) {
                $pages ['next'] = $page + 1;
            }
        }

        return [
            'blogs' => $blogs,
            'pages' => $pages
        ];
    }

    /**
     * find blogs with given text
     *
     * @param string $query
     *            text to search for
     * @return array an array of the blogs that match the search conditions
     */
    public static function getBlogsContaining($query)
    {
        $client = Model::client();
        $marshaler = new Marshaler ();
        $blogs = [];

        // The Scan API is paginated. Issue the Scan request multiple times.
        do {
            $request = [
                'TableName' => Model::$datatable,
                'ExpressionAttributeValues' => [
                    ':query' => [
                        'S' => $query
                    ]
                ],
                'FilterExpression' => 'contains (content, :query)',
                'Limit' => 10
            ];

            // Add the ExclusiveStartKey if we got one back in the previous response
            if (isset ($response) && isset ($response ['LastEvaluatedKey'])) {
                $request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
            }

            try {
                $response = $client->scan($request);
            } catch (DynamoDbException $e) {
                return null;
            }

            foreach ($response ['Items'] as $blog) {
                array_push($blogs, $marshaler->unmarshalItem($blog));
            }
        } while (isset ($response ['LastEvaluatedKey'])); // loop while there are more results

        return $blogs;
    }

    /**
     * delete all blog posts
     * @return true
     */
    public static function deleteAllBlogPosts()
    {
        $client = Model::client();

        $scan = $client->getIterator('Scan', [
            'TableName' => Model::$datatable
        ]);

        foreach ($scan as $item) {
            Model::deleteBlog($item ['id'] ['N']);
        }

        return true;
    }

    /**
     * add a new comment to a blog post
     *
     * @param int $id
     *            ID of blog post to add comment to
     * @param array $comment
     *            comment to add
     * @param int $time
     *            timestamp of comment, the default is the current time
     * @return bool result
     */
    public static function newBlogComment($id, $comment, $time = 0)
    {
        $client = Model::client();
        $marshaler = new Marshaler ();

        // set comment time
        if ($time == 0) {
            $time = time();
        }
        $comment ['time'] = $time;

        // update the blog post with the new comment
        $response = $client->updateItem([
            'TableName' => Model::$datatable,
            'Key' => [
                'id' => [
                    'N' => $id
                ]
            ],
            'UpdateExpression' => 'SET comments = list_append(if_not_exists(comments, :newlist), :newcomment)',
            'ExpressionAttributeValues' => [
                ':newcomment' => [
                    'L' => [
                        '0' => [
                            'M' => $marshaler->marshalItem($comment)
                        ]
                    ]
                ],
                ':newlist' => [
                    'L' => []
                ]
            ]
        ]);

        // TODO: test response
        if ($response) {
            return true;
        }

        return true;
    }

    /**
     * bring up database
     */
    public static function migrationUp()
    {
        $client = Model::client();

        $result = $client->createTable([
            'TableName' => Model::$datatable,
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => 'N'
                ]
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH'
                ]
            ],
            /* This is the read/write allowance requested for the table.
             * These values are appropriate for light usage of the app,
             * and should be adjusted to meet actual usage.
             * Currently, this must be done manually via the AWS DynamoDB
             * control panel.
             */
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 10,
                'WriteCapacityUnits' => 5
            ]
        ]);

        if ($result) {
            // TODO: test result

            $client->waitUntil('TableExists', [
                'TableName' => Model::$datatable
            ]);

            return true;
        }

        return true;
    }

    /**
     * bring down database table
     *
     * @return string true if successful, otherwise a string of the operation result
     * @throws Exception if the table does not exist
     */
    public static function migrationDown()
    {
        $client = Model::client();

        try {
            $result = $client->deleteTable([
                'TableName' => Model::$datatable
            ]);
        } catch (DynamoDbException $e) {
            throw new Exception('No such table exists.', $e);
        } catch (Exception $e) {
            throw new Exception('Unexpected error.', $e);
        }

        $client->waitUntil('TableNotExists', [
            'TableName' => Model::$datatable
        ]);

        return true;
    }

    /**
     * simple check to see if the database table is ready to be operated on
     *
     * @return bool whether the table does indeed exist
     */
    public static function tableExists()
    {
        $client = Model::client();

        try {
            $client->describeTable([
                'TableName' => Model::$datatable
            ]);
            return true;
        } catch (DynamoDbException $e) {
            return false;
        }
    }
}
