<?php
require 'vendor/flight/Flight.php';
require 'vendor/aws/aws-autoloader.php';
require 'vendor/faker/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception;
use Aws\DynamoDb\Exception\ResourceNotFoundException;
use Aws\DynamoDb\Aws\DynamoDb;

date_default_timezone_set ( 'Australia/Melbourne' );

/**
 * wrapper for database functions, get/set state of application
 *
 * @author nick
 *        
 */
class Model {
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
	 * @param $blogpost array
	 *        	the blog post to add
	 * @return ID of the newly added blog post, or 0 on error
	 */
	public static function newBlogPost($blogpost) {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		
		// get a unique id
		do {
			$newID = rand ( 100000, 999999 );
			
			$response = $client->getItem ( [ 
					'TableName' => Model::$dbapp_blogposts,
					'Key' => [ 
							'id' => [ 
									'N' => $newID 
							] 
					] 
			] );
		} while ( isset ( $response ['Item'] ) );
		
		// set required attributes
		$blogpost ['time'] = time ();
		$blogpost ['id'] = $newID;
		
		try {
			$client->putItem ( array (
					'TableName' => Model::$dbapp_blogposts,
					'Item' => $marshaler->marshalItem ( $blogpost ) 
			) );
		} catch ( Exception $e ) {
			return 0;
		}
		
		return $blogpost ['id'];
	}
	
	/**
	 * retrieve blog post object from database
	 *
	 * @param int $id
	 *        	ID of blog post
	 * @return the blog post, or false if non-existent
	 */
	public static function getBlogPost($id) {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		
		try {
			$request = [ 
					'TableName' => Model::$dbapp_blogposts,
					'Key' => [ 
							'id' => [ 
									'N' => $id 
							] 
					] 
			];
			
			$response = $client->getItem ( $request );
			
			return $marshaler->unmarshalItem ( $response ['Item'] );
		} catch ( Exception $e ) {
			return null;
		}
	}
	
	/**
	 * update a given blog post
	 */
	public static function updateBlogPost($blogpost) {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		
		try {
			$client->updateItem ( [ 
					'TableName' => Model::$dbapp_blogposts,
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
			] );
		} catch ( Exception $e ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * delete a given blog post
	 */
	public static function deleteBlog($id) {
		$client = Model::client ();
		
		$request = [ 
				'TableName' => Model::$dbapp_blogposts,
				'Key' => [ 
						'id' => [ 
								'N' => $id 
						] 
				] 
		];
		
		$response = $client->deleteItem ( $request );
		
		return true;
	}
	
	/**
	 * getAllBlogs - get all blog posts
	 */
	public static function getAllBlogs() {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		
		$blogs = [ ];
		
		// The Scan API is paginated. Issue the Scan request multiple times.
		do {
			$request = [ 
					'TableName' => Model::$dbapp_blogposts,
					'Limit' => 10 
			];
			
			// Add the ExclusiveStartKey if we got one back in the previous response
			if (isset ( $response ) && isset ( $response ['LastEvaluatedKey'] )) {
				$request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
			}
			
			try {
				$response = $client->scan ( $request );
			} catch ( ResourceNotFoundException $e ) {
				return null;
			}
			foreach ( $response ['Items'] as $blog ) {
				array_push ( $blogs, $marshaler->unmarshalItem ( $blog ) );
			}
		} while ( isset ( $response ['LastEvaluatedKey'] ) ); // loop while there are more results
		
		return $blogs;
	}
	
	/**
	 * getAllBlogsPaginated - get a page of blog posts
	 *
	 * @param int $page
	 *        	page to return
	 * @return array of blogposts from db
	 */
	public static function getBlogsPage($page = 0) {
		$client = Model::client ();
		
		// scan through to the requested page
		do {
			// set scan options for paging
			$request = array (
					'TableName' => Model::$dbapp_blogposts,
					'Count' => true,
					'Limit' => 10 
			);
			
			// Add the ExclusiveStartKey if we got one back in the previous response
			if (isset ( $response ) && isset ( $response ['LastEvaluatedKey'] )) {
				$request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
				$currentPage ++;
			} else {
				$currentPage = 0;
			}
			
			try {
				$response = $client->scan ( $request );
			} catch ( ResourceNotFoundException $e ) {
				return null;
			}
		} while ( isset ( $response ['LastEvaluatedKey'] ) && $currentPage < $page );
		
		$marshaler = new Marshaler ();
		
		// get blog posts from response
		$blogs = [ ];
		foreach ( $response ['Items'] as $item ) {
			array_push ( $blogs, $marshaler->unmarshalItem ( $item ) );
		}
		
		// get pagination button values
		$pages = [ ];
		if (isset ( $response ) && $page > 0) {
			$pages ['previous'] = $page - 1;
		}
		
		if (isset ( $response ) && isset ( $response ['LastEvaluatedKey'] )) {
			// see if the next page isn't blank
			$request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
			$response = $client->scan ( $request );
			if (isset ( $response ['Count'] ) && $response ['Count'] > 0) {
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
	 *        	text to search for
	 * @return an array of the blogs that match the search conditions
	 */
	public static function getBlogsContaining($query) {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		$blogs = [ ];
		
		// The Scan API is paginated. Issue the Scan request multiple times.
		do {
			$request = [ 
					'TableName' => Model::$dbapp_blogposts,
					'ExpressionAttributeValues' => [ 
							':query' => [ 
									'S' => $query 
							] 
					],
					'FilterExpression' => 'contains (content, :query)',
					'Limit' => 10 
			];
			
			// Add the ExclusiveStartKey if we got one back in the previous response
			if (isset ( $response ) && isset ( $response ['LastEvaluatedKey'] )) {
				$request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
			}
			
			try {
				$response = $client->scan ( $request );
			} catch ( ResourceNotFoundException $e ) {
				return null;
			}
			
			foreach ( $response ['Items'] as $blog ) {
				array_push ( $blogs, $marshaler->unmarshalItem ( $blog ) );
			}
		} while ( isset ( $response ['LastEvaluatedKey'] ) ); // loop while there are more results
		
		return $blogs;
	}
	
	/**
	 * delete all blog posts
	 */
	public static function deleteAllBlogPosts() {
		$client = Model::client ();
		
		// iterate over all database items and delete them
		$scan = $client->getIterator ( 'Scan', [ 
				'TableName' => Model::$dbapp_blogposts 
		] );
		
		foreach ( $scan as $item ) {
			// TODO - marshal
			$client->deleteItem ( [ 
					'TableName' => Model::$dbapp_blogposts,
					'Key' => [ 
							'id' => [ 
									'N' => $item ['id'] ['N'] 
							] 
					] 
			] );
		}
	}
	
	/**
	 * add a new comment to a blog post
	 *
	 * @param int $id
	 *        	ID of blog post to add comment to
	 * @param array $comment
	 *        	comment to add
	 * @param int $time
	 *        	timestamp of comment, the default is the current time
	 */
	public static function newBlogComment($id, $comment, $time = 0) {
		$client = Model::client ();
		$marshaler = new Marshaler ();
		
		// set comment time
		if ($time == 0) {
			$time = time ();
		}
		$comment ['time'] = $time;
		
		// update the blog post with the new comment
		$response = $client->updateItem ( [ 
				'TableName' => Model::$dbapp_blogposts,
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
												'M' => $marshaler->marshalItem ( $comment ) 
										] 
								] 
						],
						':newlist' => [ 
								'L' => [ ] 
						] 
				] 
		] );
	}
	
	/**
	 * bring up database
	 */
	public static function migrationUp() {
		$client = Model::client ();
		
		$result = $client->createTable ( [ 
				'TableName' => Model::$dbapp_blogposts,
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
		] );
		
		$client->waitUntilTableExists ( [ 
				'TableName' => Model::$dbapp_blogposts 
		] );
		
		return true;
	}
	
	/**
	 * bring down database table
	 */
	public static function migrationDown() {
		$client = Model::client ();
		
		try {
			$result = $client->deleteTable ( [ 
					'TableName' => Model::$dbapp_blogposts 
			] );
		} catch ( Aws\DynamoDB\Exception\ResourceNotFoundException $e ) {
			return 'No such table exists.';
		} catch ( Exception $e ) {
			return 'Unexpected error.';
		}
		
		$client->waitUntilTableNotExists ( array (
				'TableName' => Model::$dbapp_blogposts 
		) );
		
		return true;
	}
	
	/**
	 * simple check to see if the database table is ready to be operated on
	 *
	 * @return whether the table does indeed exist
	 */
	public static function tableExists() {
		$client = Model::client ();
		
		try {
			$client->describeTable ( [ 
					'TableName' => Model::$dbapp_blogposts 
			] );
			return true;
		} catch ( ResourceNotFoundException $e ) {
			return false;
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
	 * show admin dashboard
	 */
	public static function renderAdminIndex($tableExists) {
		// show admin dashboard
		Flight::render ( 'admin/index', [ 
				'tableExists' => $tableExists 
		], 'body_content' );
		Flight::render ( 'layout', [ 
				'pagetitle' => 'Admin Dashboard' 
		] );
	}
	
	/**
	 * the admin blog list interface
	 *
	 * @param array $blogs
	 *        	collection of blogs to display
	 *        	
	 */
	public static function renderAdminBlogs($blogs) {
		Flight::render ( 'admin/blogs', [ 
				'blogs' => $blogs 
		], 'body_content' );
		Flight::render ( 'layout' );
	}
	
	/**
	 * the blog editor form
	 *
	 * @param $blog array
	 *        	blog post to edit, default is empty form
	 * @param $result array
	 *        	optional status message of previous action
	 */
	public static function renderAdminBlog($blog = null, $result = null) {
		if (isset ( $result ) && ! is_null ( $result )) {
			Flight::render ( 'admin/blog', [ 
					'blog' => $blog,
					'result' => $result 
			], 'body_content' );
		} else {
			Flight::render ( 'admin/blog', [ 
					'blog' => $blog 
			], 'body_content' );
		}
		Flight::render ( 'layout' );
	}
	
	/**
	 * show an error page
	 *
	 * @param string $status
	 *        	message to display
	 */
	public static function renderAdminError($status) {
		Flight::render ( 'admin/message', array (
				'content' => $status 
		), 'body_content' );
		
		Flight::render ( 'layout', array (
				'pagetitle' => 'New Blog Post' 
		) );
	}
	
	/**
	 * the blog index
	 *
	 * @param array $blogs
	 *        	collection of blogs to display
	 */
	public static function renderBlogs($blogs, $pages) {
		Flight::render ( 'blogs', [ 
				'blogs' => $blogs,
				'pages' => $pages 
		], 'body_content' );
		
		Flight::render ( 'layout' );
	}
	
	/**
	 * view blog post by id
	 *
	 * @param array $blog
	 *        	blog post to display
	 */
	public static function renderBlog($blog) {
		// render page content
		Flight::render ( 'blog', [ 
				'blog' => $blog 
		], 'body_content' );
		
		// render page layout
		Flight::render ( 'layout', [ 
				'pagetitle' => $blog ['title'] 
		] );
	}
	
	/**
	 * view search results
	 *
	 * @param string $query
	 *        	search query to display
	 * @param array $blogs        	
	 */
	public static function renderSearch($query, $blogs) {
		Flight::render ( 'search.php', [ 
				'query' => $query,
				'blogs' => $blogs 
		], 'body_content' );
		Flight::render ( 'layout' );
	}
	
	/**
	 * show file not found error message
	 */
	public static function renderFileNotFound() {
		Flight::render ( 'error', [ 
				'message' => 'No document found at that URI.' 
		], 'body_content' );
		Flight::render ( 'layout', [ 
				'pagetitle' => 'File Not Found' 
		] );
	}
}

/**
 * handles requests and request input
 *
 * @author nick
 *        
 */
class Controller {
	public static function adminIndex() {
		View::renderAdminIndex ( Model::tableExists () );
	}
	
	/**
	 * create blog table and show outcome
	 */
	public static function adminCreateTable() {
		if (Model::migrationUp ()) {
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
	public static function adminDeleteTable() {
		if (Model::migrationDown ()) {
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
	 * administer all blogs
	 */
	public static function adminBlogs() {
		$blogs = Model::getAllBlogs ();
		
		View::renderAdminBlogs ( $blogs );
	}
	
	/**
	 */
	public static function adminNewBlog() {
		$request = Flight::request ();
		
		if ($request->method == 'POST') {
			// build blogpost from request body
			$blogpost = array (
					'title' => $request->data->title,
					'author' => $request->data->author,
					'content' => $request->data->content 
			);
			
			// attempt to create blog post
			$newBlogPostID = Model::newBlogPost ( $blogpost );
			
			if ($newBlogPostID > 0) {
				// go to blog post
				Flight::redirect ( '/admin/blogs/' . $newBlogPostID );
			} else {
				View::adminError ( 'Could not create new blog post.' );
			}
		} else {
			View::renderAdminBlog ();
		}
	}
	
	/**
	 * edit a blog post
	 *
	 * @param int $id
	 *        	ID of blog post to edit
	 */
	public static function adminUpdateBlog($id) {
		$request = Flight::request ();
		
		if ($request->method == 'POST') {
			// build blogpost from request body
			$blogpost = [ 
					'id' => $request->data->id,
					'title' => $request->data->title,
					'author' => $request->data->author,
					'content' => $request->data->content 
			];
			
			Model::updateBlogPost ( $blogpost );
			
			View::renderAdminBlog ( Model::getBlogPost ( $id ), [ 
					'success' => true,
					'message' => 'Successfully updated' 
			] );
		} else {
			$blog = Model::getBlogPost ( $id );
			
			if (! is_null ( $blog )) {
				View::renderAdminBlog ( $blog );
			} else {
				Flight::notFound ();
			}
		}
	}
	
	/**
	 * delete a blog post
	 *
	 * @param int $id
	 *        	ID of blog post to delete
	 */
	public static function adminDeleteBlog($id) {
		Model::deleteBlog ( $id );
		
		Flight::redirect ( '/admin/blogs' );
	}
	
	/**
	 * post some randomised comments
	 */
	public static function adminBlogFakeComments($id, $num) {
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
			
			Model::newBlogComment ( $id, $comment );
		}
		
		Flight::redirect ( "/admin/blogs/$id" );
	}
	
	/**
	 * post some randomised blog posts
	 *
	 * @param int $num
	 *        	number of posts to create, defaults to 7
	 * @param bool $comments
	 *        	fake comments with each blog post, defaults to false
	 */
	public static function adminFakeBlogs($num, $comments = false) {
		$faker = Faker\Factory::create ( 'en_AU' );
		
		// initialise defaults
		if (is_null ( $num )) {
			$num = 7;
		}
		
		$i = 0;
		for(; $i < $num; $i ++) {
			$blogpost = array (
					'title' => $faker->sentence,
					'author' => $faker->firstName,
					'content' => implode ( "\n\n", $faker->paragraphs ( $faker->numberBetween ( 1, 9 ) ) ) 
			);
			
			// insert blogpost, break on error
			$id = Model::newBlogPost ( $blogpost );
			if ($id <= 0) {
				break;
			} else {
				// add some fake comments
				if ($comments) {
					Controller::adminBlogFakeComments ( $id, $faker->numberBetween ( 0, 15 ) );
				}
			}
		}
		
		$status = "Created $i blog posts.";
		
		// render page content
		Flight::redirect ( '/admin/blogs' );
	}
	
	/**
	 * delete all blog posts and show result
	 */
	public static function adminDeleteAllBlogs() {
		Model::deleteAllBlogPosts ();
		
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
	 * show front page of blogs, or subsequent pages
	 */
	public static function blogs($page) {
		if (! Model::tableExists ()) {
			// handle first run
			Flight::redirect ( '/admin' );
		}
		
		if (! is_null ( $page )) {
			$blogsAndPages = Model::getBlogsPage ( $page );
		} else {
			$blogsAndPages = Model::getBlogsPage ();
		}
		
		if (! is_null ( $blogsAndPages ['blogs'] )) {
			View::renderBlogs ( $blogsAndPages ['blogs'], $blogsAndPages ['pages'] );
		} else {
			Flight::notFound ();
		}
	}
	
	/**
	 * show blog post, or post a comment to a blog
	 *
	 * @param $id int
	 *        	blog post to show
	 */
	public static function blog($id) {
		if (! is_null ( $id )) {
			$blog = Model::getBlogPost ( $id );
			
			if (! is_null ( $blog )) {
				$request = Flight::request ();
				
				// handle new comment
				if ($request->method == 'POST') {
					// build comment from request
					$id = $request->data->id;
					$comment = [ 
							'content' => $request->data->content 
					];
					
					if (isset ( $request->data->author )) {
						$comment ['author'] = $request->data->author;
					}
					
					Model::newBlogComment ( $id, $comment );
					
					Flight::redirect ( '/blogs/' . $id . '#bottom' );
				} else {
					View::renderBlog ( $blog );
				}
			} else {
				Flight::notFound ();
			}
		} else {
			Flight::notFound ();
		}
	}
	public static function search() {
		$query = Flight::request ()->query ['q'];
		
		$blogs = Model::getBlogsContaining ( $query );
		
		View::renderSearch ( $query, $blogs );
	}
	public static function install() {
		Model::migrationUp ();
		Flight::redirect ( '/admin' );
	}
	public static function uninstall() {
		Model::migrationDown ();
		Flight::redirect ( '/admin' );
	}
	
	/**
	 * register all request handlers
	 */
	public static function register() {
		// admin interface pages
		Flight::route ( '/admin', [ 
				'Controller',
				'adminIndex' 
		] );
		
		Flight::route ( '/admin/blogs', [ 
				'Controller',
				'adminBlogs' 
		] );
		
		// admin high level functions
		Flight::route ( '/admin/install', [ 
				'Controller',
				'install' 
		] );
		
		Flight::route ( '/admin/uninstall', [ 
				'Controller',
				'uninstall' 
		] );
		
		// admin blog post pages - special pages first
		Flight::route ( '/admin/blogs/deleteall', [ 
				'Controller',
				'adminDeleteAllBlogs' 
		] );
		
		Flight::route ( '/admin/blogs/newfake(/@num:[0-9]+)', [ 
				'Controller',
				'adminFakeBlogs' 
		] );
		
		Flight::route ( 'GET|POST /admin/blogs/new', [ 
				'Controller',
				'adminNewBlog' 
		] );
		
		Flight::route ( 'GET|POST /admin/blogs/@id', [ 
				'Controller',
				'adminUpdateBlog' 
		] );
		
		// TODO - move modification functions to POST methods?
		Flight::route ( '/admin/blogs/@id/delete', [ 
				'Controller',
				'adminDeleteBlog' 
		] );
		
		Flight::route ( '/admin/blogs/@id:[0-9]+/fake(/@num:[0-9]+)', [ 
				'Controller',
				'adminBlogFakeComments' 
		] );
		
		// blog post index view
		Flight::route ( '/(@page:[0-9]+)', [ 
				'Controller',
				'blogs' 
		] );
		
		// individual blog post view
		Flight::route ( 'GET|POST /blogs/@id:[0-9]+', [ 
				'Controller',
				'blog' 
		] );
		
		Flight::route ( '/search', [ 
				'Controller',
				'search' 
		] );
	}
}

Controller::register ();

/**
 * TODO: remove before submittal *
 */
Flight::route ( '/debug', function () {
	$client = DynamoDbClient::factory ( [ 
			'region' => 'ap-southeast-2',
			'profile' => 'dbapp-profile' 
	] );
	
	$marshaler = new Marshaler ();
	
	$responses = [ ];
	$key = null;
	$done = false;
	
	do {
		$request = [ 
				'TableName' => 'dbapp-blogposts',
				'Count' => true,
				'Limit' => 10 
		];
		
		if (isset ( $response ) && isset ( $response ['LastEvaluatedKey'] )) {
			$request ['ExclusiveStartKey'] = $response ['LastEvaluatedKey'];
		}
		
		$response = $client->scan ( $request );
		
		array_push ( $responses, $response );
	} while ( isset ( $response ['LastEvaluatedKey'] ) );
	
	Flight::render ( 'admin/message', [ 
			'content' => '<pre>' . implode ( "", $responses ) . '</pre>' 
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
