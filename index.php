<?php
require 'vendor/flight/Flight.php';
require 'vendor/aws/aws-autoloader.php';
require 'vendor/faker/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception;

date_default_timezone_set ( 'Etc/Universal' );

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
		if (is_null ( $num )) {
			$num = 25;
		}
		
		$faker = Faker\Factory::create ( 'en_AU' );
		
		$i = 0;
		for(; $i < $num; $i ++) {
			$blogpost = array (
					'title' => $faker->text ( 40 ),
					'content' => $faker->text ( 1600 ) 
			);
			
			// insert blogpost, break on error
			if (DbHelper::newBlogPost ( $blogpost ) <= 0) {
				break;
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
		if (is_null ( $num )) {
			$num = 25;
		}
		
		for($i = 0; $i < $num; $i ++) {
			DbHelper::newBlogPostComment ( $id, [ 
					'author' => 'Anon',
					'content' => 'Hello World' 
			] );
		}
		
		echo "Added $i comments.";
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
		
		// build comment from request
		$id = $request->data->id;
		$comment = [ 
				'author' => $request->data->author,
				'content' => $request->data->content 
		];
		
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
	 * getAllBlogPosts - get all blog posts
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
	public static function newBlogPostComment($id, $comment) {
		$client = DbHelper::client ();
		$marshaler = new Marshaler ();
		
		// update time on comment
		$comment ['time'] = time ();
		
		// update the blog post
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
 * View - renders web pages from requests
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

Flight::start ();
?>
