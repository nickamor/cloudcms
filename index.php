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
				'content' => $request->data->content 
		);
		
		// create blog post
		$newBlogPostID = DbHelper::newBlogPost ( $blogpost );
		if ($newBlogPostID > 0) {
			// go to blog post
			Flight::redirect ( '/blog/' . $newBlogPostID );
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
					'title' => $faker->text ( 25 ),
					'content' => $faker->text ( 800 ) 
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
		$comment = array (
				'author' => $request->data->author,
				'content' => $request->data->content 
		);
		
		DbHelper::newBlogPostComment ( $id, $comment );
		
		Flight::redirect ( $request->referrer . '#bottom' );
	}
}
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
		$marshaler = new Marshaler ();
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
		
		// TODO - use marshaler
		$comment_item = array (
				'author' => array (
						'S' => $comment ['author'] 
				),
				'content' => array (
						'S' => $comment ['content'] 
				),
				'time' => array (
						'N' => time () 
				) 
		);
		
		// update the blog post
		$response = $client->updateItem ( array (
				'TableName' => DbHelper::$dbapp_blogposts,
				'Key' => array (
						'id' => [ 
								'N' => $id 
						] 
				),
				'UpdateExpression' => 'SET comments = list_append(if_not_exists(comments, :newcomment), :newcomment)',
				'ExpressionAttributeValues' => array (
						':newcomment' => array (
								'L' => array (
										'0' => array (
												'M' => $comment_item 
										) 
								) 
						) 
				) 
		) );
	}
	
	/**
	 * bring up database
	 */
	public static function migrationUp() {
		$client = DbHelper::client ();
		
		$result = $client->createTable ( array (
				'TableName' => DbHelper::$dbapp_blogposts,
				'AttributeDefinitions' => array (
						array (
								'AttributeName' => 'id',
								'AttributeType' => 'N' 
						) 
				),
				'KeySchema' => array (
						array (
								'AttributeName' => 'id',
								'KeyType' => 'HASH' 
						) 
				),
				'ProvisionedThroughput' => array (
						'ReadCapacityUnits' => 10,
						'WriteCapacityUnits' => 20 
				) 
		) );
		
		$client->waitUntilTableExists ( array (
				'TableName' => DbHelper::$dbapp_blogposts 
		) );
		
		return true;
	}
	
	/**
	 * tear down database table
	 */
	public static function migrationDown() {
		$client = DbHelper::client ();
		
		try {
			$result = $client->deleteTable ( array (
					'TableName' => DbHelper::$dbapp_blogposts 
			) );
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
		$scan = $client->getIterator ( 'Scan', array (
				'TableName' => DbHelper::$dbapp_blogposts 
		) );
		foreach ( $scan as $item ) {
			$client->deleteItem ( array (
					'TableName' => DbHelper::$dbapp_blogposts,
					'Key' => array (
							'id' => array (
									'N' => $item ['id'] ['N'] 
							) 
					) 
			) );
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
		Flight::render ( 'allblogposts', array (
				'blogposts' => $blogposts 
		), 'body_content' );
		
		Flight::render ( 'layout' );
	}
	
	/**
	 * view blog post by id
	 */
	public static function blogPost($id) {
		// get blog post by id
		$blogpost = DbHelper::getBlogPost ( $id );
		
		// render page content
		Flight::render ( 'blogpost', array (
				'blogpost' => $blogpost 
		), 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', array (
				'pagetitle' => $blogpost ['title'] 
		) );
	}
	
	/**
	 * show admin dashboard
	 */
	public static function adminIndex() {
		// show admin dashboard
		Flight::render ( 'admin/index', array (), 'body_content' );
		Flight::render ( 'layout', array (
				'pagetitle' => 'Admin Dashboard' 
		) );
	}
	
	/**
	 * new blog post entry form
	 */
	public static function newBlogPostForm() {
		// show new blog post form
		Flight::render ( 'admin/newblogpost', array (), 'body_content' );
		Flight::render ( 'layout', array (
				'pagetitle' => 'New Blog Post' 
		) );
	}
}

/**
 * register routes
 */
Flight::route ( '/admin', array (
		'View',
		'adminIndex' 
) );

Flight::route ( 'GET /admin/newblogpost', array (
		'View',
		'newBlogPostForm' 
) );
Flight::route ( 'POST /admin/newblogpost', array (
		'Controller',
		'postNewBlogPost' 
) );

Flight::route ( '/admin/createtable', array (
		'Controller',
		'createTable' 
) );

Flight::route ( '/admin/deletetable', array (
		'Controller',
		'deleteTable' 
) );

Flight::route ( '/admin/fakedata(/@num:[0-9]+)', array (
		'Controller',
		'fakeBlogPosts' 
) );

Flight::route ( '/admin/deleteall', array (
		'Controller',
		'deleteAllBlogPosts' 
) );

Flight::route ( '/', array (
		'View',
		'allBlogPosts' 
) );

Flight::route ( '/@page:[0-9]+', array (
		'View',
		'allBlogPosts' 
) );

Flight::route ( 'GET /blog/@id:[0-9]+', array (
		'View',
		'blogPost' 
) );

Flight::route ( 'POST /blog/@id:[0-9]+', array (
		'Controller',
		'postNewComment' 
) );

Flight::route ( '/blog/@id:[0-9]+/fakecomments(/@num:[0-9]+)', array (
		'Controller',
		'fakeComments' 
) );

Flight::start ();
?>
