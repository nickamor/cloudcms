<?php
require 'vendor/flight/Flight.php';
require 'vendor/aws/aws-autoloader.php';
require 'vendor/faker/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception;
use Aws\DynamoDb\Aws\DynamoDb;

date_default_timezone_set ( 'Australia/Melbourne' );

/**
 * handles request input
 *
 * @author nick
 *        
 */
class Controller {
	
	/**
	 * create new blog post from request data
	 */
	public static function postNewBlogPost() {
		$request = Flight::request ();
		
		// build blogpost from request body
		$blogpost = array (
				'title' => $request->data->title,
				'author' => $request->data->author,
				'content' => $request->data->content 
		);
		
		// create blog post
		$newBlogPostID = DbHelper::newBlogPost ( $blogpost );
		if ($newBlogPostID > 0) {
			// go to blog post
			Flight::redirect ( '/admin/blog/' . $newBlogPostID );
		} else {
			// show error
			$status = 'Could not create new blog post.';
			
			Flight::render ( 'admin/message', array (
					'content' => $status 
			), 'body_content' );
			
			Flight::render ( 'layout', array (
					'pagetitle' => 'New Blog Post' 
			) );
		}
	}
	
	/**
	 * create blog table and show outcome
	 */
	public static function createTable() {
		if (DbHelper::migrationUp ()) {
			$status = 'Table created.';
		} else {
			$status = 'Unexpected error.';
		}
		
		// render page content
		Flight::render ( 'admin/message', array (
				'content' => $status 
		), 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', array (
				'pagetitle' => 'Create Table' 
		) );
	}
	
	/**
	 * delete blog table and show outcome
	 */
	public static function deleteTable() {
		if (DbHelper::migrationDown ()) {
			$status = 'Table deleted.';
		} else {
			$status = 'Unexpected error.';
		}
		
		// render page content
		Flight::render ( 'admin/message', array (
				'content' => $status 
		), 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', array (
				'pagetitle' => 'Delete Table' 
		) );
	}
	
	/**
	 * post some randomised blog posts
	 */
	public static function fakeBlogPosts($num) {
		$faker = Faker\Factory::create ( 'en_AU' );
		
		if (is_null ( $num )) {
			$num = 5;
		}
		
		$i = 0;
		for(; $i < $num; $i ++) {
			$blogpost = array (
					'title' => $faker->sentence,
					'content' => implode ( "\n\n", $faker->paragraphs ( $faker->numberBetween ( 1, 9 ) ) ) 
			);
			
			// insert blogpost, break on error
			$id = DbHelper::newBlogPost ( $blogpost );
			if ($id <= 0) {
				break;
			} else {
				// add some fake comments
				Controller::fakeComments ( $id, $faker->numberBetween ( 0, 15 ) );
			}
		}
		
		$status = "Created $i blog posts.";
		
		// render page content
		Flight::render ( 'admin/message', array (
				'content' => $status 
		), 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', array (
				'pagetitle' => 'Insert Faked Data' 
		) );
	}
	
	/**
	 * post some randomised comments
	 */
	public static function fakeComments($id, $num) {
		$faker = Faker\Factory::create ( 'en_AU' );
		
		// $num is an optional parameter
		if (is_null ( $num )) {
			$num = 5;
		}
		
		for($i = 0; $i < $num; $i ++) {
			$comment = [ 
					'content' => $faker->paragraph 
			];
			
			// chance of author name
			if ($faker->numberBetween ( 0, 2 )) {
				$comment ['author'] = $faker->firstName;
			}
			
			DbHelper::newBlogPostComment ( $id, $comment );
		}
		
		return "Added $i comments.";
	}
	
	// delete all blog posts and show outcome
	public static function deleteAllBlogPosts() {
		DbHelper::deleteAllBlogPosts ();
		
		$status = 'All blog posts deleted.';
		
		// render page content
		Flight::render ( 'admin/message', array (
				'content' => $status 
		), 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', array (
				'pagetitle' => 'Delete All Blog Posts' 
		) );
	}
	
	/**
	 * post new comment to blog post from request
	 */
	public static function postNewComment() {
		$request = Flight::request ();
		
		// TODO - cannot post a comment with no author
		
		// build comment from request
		$id = $request->data->id;
		$comment = [ 
				'content' => $request->data->content 
		];
		
		// add optional parameters
		if (isset ( $request->data->author )) {
			$comment ['author'] = $request->data->author;
		}
		
		DbHelper::newBlogPostComment ( $id, $comment );
		
		Flight::redirect ( $request->referrer . '#bottom' );
	}
}

/**
 * wrapper for database functions
 *
 * @author nick
 *        
 */
class DbHelper {
	/**
	 * name of the database table for blog posts
	 */
	private static $dbapp_blogposts = 'dbapp-blogposts';
	
	/**
	 * retrieve a database client handle
	 *
	 * @return \Aws\DynamoDb\DynamoDbClient
	 */
	private static function client() {
		$factoryOptions = array (
				'region' => 'ap-southeast-2' 
		);
		
		// use profile configuration file if environment isn't set
		// useful for local testing
		if (! isset ( $_ENV ['AWS_ACCESS_KEY_ID'] )) {
			$factoryOptions ['profile'] = 'dbapp-profile';
		}
		
		return DynamoDbClient::factory ( $factoryOptions );
	}
	
	/**
	 * create a new blog post item in the database
	 *
	 * @param array $blogpost
	 *        	the blog post to add
	 * @return ID of the newly added blog post, or 0 on error
	 */
	public static function newBlogPost($blogpost) {
		$client = DbHelper::client ();
		$marshaler = new Marshaler ();
		
		$blogpost ['id'] = rand ( 100000, 999999 );
		$blogpost ['time'] = time ();
		
		try {
			$client->putItem ( array (
					'TableName' => DbHelper::$dbapp_blogposts,
					'Item' => $marshaler->marshalItem ( $blogpost ) 
			) );
			
			return $blogpost ['id'];
		} catch ( Exception $e ) {
			return - 1;
		}
		
		return 0;
	}
	
	/**
	 * retrieve blog post object from database
	 *
	 * @param int $id
	 *        	ID of blog post
	 * @return the blog post, or false if non-existent
	 */
	public static function getBlogPost($id) {
		$client = DbHelper::client ();
		$marshaler = new Marshaler ();
		
		try {
			$response = $client->getItem ( array (
					'TableName' => DbHelper::$dbapp_blogposts,
					'Key' => array (
							'id' => array (
									'N' => $id 
							) 
					) 
			) );
			
			return $marshaler->unmarshalItem ( $response ['Item'] );
		} catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * getAllBlogPosts - get a page of blog posts
	 *
	 * @param int $page
	 *        	- page to return
	 * @return array of blogposts from db
	 */
	public static function getAllBlogPosts($page = 0) {
		$client = DbHelper::client ();
		$marshaler = new Marshaler ();
		
		// set scan options for paging
		$scanOptions = array (
				'TableName' => DbHelper::$dbapp_blogposts,
				'Limit' => 10,
				'Count' => true 
		);
		
		// scan through to the requested page
		$scanLastKey = null;
		for($i = 0; $i < $page; $i ++) {
			if (isset ( $scanLastKey )) {
				$scanOptions ['ExclusiveStartKey'] = $scanLastKey;
			}
			$scan = $client->scan ( $scanOptions );
			if ($scan ['Count'] == 10) {
				if (isset ( $scan ['LastEvaluatedKey'] )) {
					$scanLastKey = $scan ['LastEvaluatedKey'];
				}
			} else {
				break;
			}
		}
		
		// scan the right page
		$scanOptions ['Count'] = false;
		$scan = $client->getIterator ( 'Scan', $scanOptions );
		
		// return blog posts array
		$blogposts = array ();
		foreach ( $scan as $item ) {
			array_push ( $blogposts, $marshaler->unmarshalItem ( $item ) );
		}
		
		return $blogposts;
	}
	
	/**
	 * add a new comment to a blog post
	 */
	public static function newBlogPostComment($id, $comment, $time = 0) {
		$client = DbHelper::client ();
		$marshaler = new Marshaler ();
		
		// set comment time
		if ($time == 0) {
			$time = time ();
		}
		$comment ['time'] = $time;
		
		// update the blog post with the new comment
		$response = $client->updateItem ( [ 
				'TableName' => DbHelper::$dbapp_blogposts,
				'Key' => [ 
						'id' => [ 
								'N' => $id 
						] 
				],
				'UpdateExpression' => 'SET comments = list_append(if_not_exists(comments, :newcomment), :newcomment)',
				'ExpressionAttributeValues' => [ 
						':newcomment' => [ 
								'L' => [ 
										'0' => [ 
												'M' => $marshaler->marshalItem ( $comment ) 
										] 
								] 
						] 
				] 
		] );
	}
	
	/**
	 * bring up database
	 */
	public static function migrationUp() {
		$client = DbHelper::client ();
		
		$result = $client->createTable ( [ 
				'TableName' => DbHelper::$dbapp_blogposts,
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
				'ProvisionedThroughput' => [ 
						'ReadCapacityUnits' => 10,
						'WriteCapacityUnits' => 20 
				] 
		] );
		
		$client->waitUntilTableExists ( [ 
				'TableName' => DbHelper::$dbapp_blogposts 
		] );
		
		return true;
	}
	
	/**
	 * tear down database table
	 */
	public static function migrationDown() {
		$client = DbHelper::client ();
		
		try {
			$result = $client->deleteTable ( [ 
					'TableName' => DbHelper::$dbapp_blogposts 
			] );
		} catch ( Aws\DynamoDB\Exception\ResourceNotFoundException $e ) {
			return 'No such table exists.';
		} catch ( Exception $e ) {
			return 'Unexpected error.';
		}
		
		$client->waitUntilTableNotExists ( array (
				'TableName' => DbHelper::$dbapp_blogposts 
		) );
		
		return true;
	}
	
	/**
	 * delete all blog posts
	 */
	public static function deleteAllBlogPosts() {
		$client = DbHelper::client ();
		
		// iterate over all database items and delete them
		$scan = $client->getIterator ( 'Scan', [ 
				'TableName' => DbHelper::$dbapp_blogposts 
		] );
		foreach ( $scan as $item ) {
			$client->deleteItem ( [ 
					'TableName' => DbHelper::$dbapp_blogposts,
					'Key' => [ 
							'id' => [ 
									'N' => $item ['id'] ['N'] 
							] 
					] 
			] );
		}
	}
}

/**
 * View - produce web pages
 *
 * @author nick
 *        
 */
class View {
	
	/**
	 * show all topics
	 */
	public static function allBlogPosts($page = 0) {
		$blogposts = DbHelper::getAllBlogPosts ( $page );
		
		// render
		Flight::render ( 'blogs', [ 
				'blogposts' => $blogposts 
		], 'body_content' );
		
		Flight::render ( 'layout' );
	}
	
	/**
	 * view blog post by id
	 */
	public static function blogPost($id) {
		// get blog post by id
		$blogpost = DbHelper::getBlogPost ( $id );
		
		// render page content
		Flight::render ( 'blog', [ 
				'blogpost' => $blogpost 
		], 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', [ 
				'pagetitle' => $blogpost ['title'] 
		] );
	}
	
	/**
	 * show admin dashboard
	 */
	public static function adminIndex() {
		// show admin dashboard
		Flight::render ( 'admin/index', [ ], 'body_content' );
		Flight::render ( 'layout', [ 
				'pagetitle' => 'Admin Dashboard' 
		] );
	}
	
	/**
	 * new blog post entry form
	 */
	public static function newBlogPostForm() {
		// show new blog post form
		Flight::render ( 'admin/blog', [ ], 'body_content' );
		Flight::render ( 'layout', [ 
				'pagetitle' => 'New Blog Post' 
		] );
	}
	
	/**
	 * new blog post entry form
	 */
	public static function editBlogPostForm($id) {
		$blogpost = DbHelper::getBlogPost ( $id );
		
		if ($blogpost) {
			// show edit blog post form
			Flight::render ( 'admin/blog', [ 
					'blogpost' => $blogpost 
			], 'body_content' );
			Flight::render ( 'layout', [ 
					'pagetitle' => 'New Blog Post' 
			] );
		} else {
			// show error
			Flight::notFound ();
		}
	}
	
	/**
	 * show file not found error message
	 */
	public static function fileNotFound() {
		Flight::render ( 'error', [ 
				'message' => 'No document found at that URI.' 
		], 'body_content' );
		Flight::render ( 'layout', [ 
				'pagetitle' => 'File Not Found' 
		] );
	}
}

/**
 * register routes
 */
Flight::route ( '/admin', [ 
		'View',
		'adminIndex' 
] );

Flight::route ( '/admin/install', [ 
		'Controller',
		'createTable' 
] );

Flight::route ( '/admin/uninstall', [ 
		'Controller',
		'deleteTable' 
] );

Flight::route ( 'GET /admin/blog/new', [ 
		'View',
		'newBlogPostForm' 
] );

Flight::route ( 'POST /admin/blog/new', [ 
		'Controller',
		'postNewBlogPost' 
] );

Flight::route ( 'GET /admin/blog/@id', [ 
		'View',
		'editBlogPostForm' 
] );

Flight::route ( 'POST /admin/blog/@id', [ 
		'Controller',
		'updateNewBlogPost' 
] );

Flight::route ( '/admin/blog/new/fake(/@num:[0-9]+)', [ 
		'Controller',
		'fakeBlogPosts' 
] );

Flight::route ( '/admin/blog/deleteall', [ 
		'Controller',
		'deleteAllBlogPosts' 
] );

Flight::route ( '/(@page:[0-9]+)', [ 
		'View',
		'allBlogPosts' 
] );

Flight::route ( 'GET /blog/@id:[0-9]+', [ 
		'View',
		'blogPost' 
] );

Flight::route ( 'POST /blog/@id:[0-9]+', [ 
		'Controller',
		'postNewComment' 
] );

Flight::route ( '/admin/blog/@id:[0-9]+/fakecomments(/@num:[0-9]+)', [ 
		'Controller',
		'fakeComments' 
] );

Flight::route ( '/debug', function () {
	$client = DynamoDbClient::factory ( [ 
			'region' => 'ap-southeast-2',
			'profile' => 'dbapp-profile' 
	] );
	$marshaler = new Marshaler ();
	
	$responses = [ ];
	$key = null;
	$done = false;
	$scanOptions = [ 
			'TableName' => 'dbapp-blogposts',
			'Count' => true,
			'Limit' => 10 
	];
	
	while ( ! $done ) {
		if (isset ( $key )) {
			$scanOptions ['ExclusiveStartKey'] = $key;
		}
		
		$response = $client->scan ( $scanOptions );
		
		if (isset ( $response ['LastEvaluatedKey'] )) {
			$key = $response ['LastEvaluatedKey'];
		}
		
		array_push ( $responses, $response );
		
		if ($response ['Count'] < 10) {
			$done = true;
		}
	}
	
	// render view
	Flight::render ( 'admin/message', [ 
			'content' => '<pre>' . implode("", $responses) . '</pre>' 
	], 'body_content' );
	Flight::render ( 'layout' );
} );

// override default 404 message
Flight::map ( 'notFound', [ 
		'View',
		'fileNotFound' 
] );

Flight::start ();
?>
