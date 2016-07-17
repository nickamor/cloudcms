<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 16/07/2016
 * Time: 6:26 PM
 */

/**
 * handles requests and request input
 *
 * @author nick
 *
 */
class Controller
{
    /**
     * show functions for administering the application
     */
    public static function adminIndex()
    {
        View::renderAdminIndex(Model::tableExists());
    }

    /**
     * create blog table and show outcome
     */
    public static function adminCreateTable()
    {
        if (Model::migrationUp()) {
            $status = 'Table created.';
        } else {
            $status = 'Unexpected error.';
        }

        // render page content
        Flight::render('admin/message', array(
            'content' => $status
        ), 'body_content');

        // render page layout
        Flight::render('layout', array(
            'pagetitle' => 'Create Table'
        ));
    }

    /**
     * delete blog table and show outcome
     */
    public static function adminDeleteTable()
    {
        if (Model::migrationDown()) {
            $status = 'Table deleted.';
        } else {
            $status = 'Unexpected error.';
        }

        // render page content
        Flight::render('admin/message', array(
            'content' => $status
        ), 'body_content');

        // render page layout
        Flight::render('layout', array(
            'pagetitle' => 'Delete Table'
        ));
    }

    /**
     * administer all blogs
     */
    public static function adminBlogs()
    {
        $blogs = Model::getAllBlogs();

        View::renderAdminBlogs($blogs);
    }

    /**
     * create a new blog post
     */
    public static function adminNewBlog()
    {
        $request = Flight::request();

        if ($request->method == 'POST') {
            // build blogpost from request body
            $blogpost = array(
                'title' => $request->data->title,
                'author' => $request->data->author,
                'content' => $request->data->content
            );

            // attempt to create blog post
            $newBlogPostID = Model::newBlogPost($blogpost);

            if ($newBlogPostID > 0) {
                // go to blog post
                Flight::redirect('/admin/blogs/' . $newBlogPostID);
            } else {
                View::renderAdminError('Could not create new blog post.');
            }
        } else {
            View::renderAdminBlog();
        }
    }

    /**
     * edit a blog post
     *
     * @param int $id
     *            ID of blog post to edit
     */
    public static function adminUpdateBlog($id)
    {
        $request = Flight::request();

        if ($request->method == 'POST') {
            // build blogpost from request body
            $blogpost = [
                'id' => $request->data->id,
                'title' => $request->data->title,
                'author' => $request->data->author,
                'content' => $request->data->content
            ];

            Model::updateBlogPost($blogpost);

            View::renderAdminBlog(Model::getBlogPost($id), [
                'success' => true,
                'message' => 'Successfully updated'
            ]);
        } else {
            $blog = Model::getBlogPost($id);

            if (!is_null($blog)) {
                View::renderAdminBlog($blog);
            } else {
                Flight::notFound();
            }
        }
    }

    /**
     * delete a blog post
     *
     * @param int $id
     *            ID of blog post to delete
     */
    public static function adminDeleteBlog($id)
    {
        Model::deleteBlog($id);

        Flight::redirect('/admin/blogs');
    }

    /**
     * post some randomised comments
     *
     * @param int $id
     *            ID of blog post to comment on
     * @param int $num
     *            number of comments to create
     */
    public static function adminBlogFakeComments($id, $num)
    {
        $faker = Faker\Factory::create('en_AU');

        // $num is an optional parameter
        if (is_null($num)) {
            $num = 5;
        }

        for ($i = 0; $i < $num; $i++) {
            $comment = [
                'content' => $faker->paragraph
            ];

            // chance of author name
            if ($faker->numberBetween(0, 2)) {
                $comment ['author'] = $faker->firstName;
            }

            Model::newBlogComment($id, $comment);
        }

        Flight::redirect("/admin/blogs/$id");
    }

    /**
     * post some randomised blog posts
     *
     * @param int $num
     *            number of posts to create, defaults to 7
     * @param bool $comments
     *            fake comments with each blog post, defaults to false
     */
    public static function adminFakeBlogs($num, $comments = false)
    {
        $faker = Faker\Factory::create('en_AU');

        // initialise defaults
        if (is_null($num)) {
            $num = 7;
        }

        $i = 0;
        for (; $i < $num; $i++) {
            $blogpost = array(
                'title' => $faker->sentence,
                'author' => $faker->firstName,
                'content' => implode("\n\n", $faker->paragraphs($faker->numberBetween(1, 9)))
            );

            // insert blogpost, break on error
            $id = Model::newBlogPost($blogpost);
            if ($id <= 0) {
                break;
            } else {
                // add some fake comments
                if ($comments) {
                    Controller::adminBlogFakeComments($id, $faker->numberBetween(0, 15));
                }
            }
        }

        $status = "Created $i blog posts.";

        // render page content
        Flight::redirect('/admin/blogs');
    }

    /**
     * delete all blog posts and show result
     */
    public static function adminDeleteAllBlogs()
    {
        Model::deleteAllBlogPosts();

        $status = 'All blog posts deleted.';

        // render page content
        Flight::render('admin/message', array(
            'content' => $status
        ), 'body_content');

        // render page layout
        Flight::render('layout', array(
            'pagetitle' => 'Delete All Blog Posts'
        ));
    }

    /**
     * show front page of blogs, or subsequent pages
     */
    public static function blogs($page)
    {
        if (!Model::tableExists()) {
            // handle first run
            Flight::redirect('/admin');
        }

        if (!is_null($page)) {
            $blogsAndPages = Model::getBlogsPage($page);
        } else {
            $blogsAndPages = Model::getBlogsPage();
        }

        if (!is_null($blogsAndPages ['blogs'])) {
            View::renderBlogs($blogsAndPages ['blogs'], $blogsAndPages ['pages']);
        } else {
            Flight::notFound();
        }
    }

    /**
     * show blog post, or post a comment to a blog
     *
     * @param $id int
     *            blog post to show
     */
    public static function blog($id)
    {
        if (!is_null($id)) {
            $blog = Model::getBlogPost($id);

            if (!is_null($blog)) {
                $request = Flight::request();

                // handle new comment
                if ($request->method == 'POST') {
                    // build comment from request
                    $id = $request->data->id;
                    $comment = [
                        'content' => $request->data->content
                    ];

                    if (isset ($request->data->author)) {
                        $comment ['author'] = $request->data->author;
                    }

                    Model::newBlogComment($id, $comment);

                    Flight::redirect('/blogs/' . $id . '#bottom');
                } else {
                    View::renderBlog($blog);
                }
            } else {
                Flight::notFound();
            }
        } else {
            Flight::notFound();
        }
    }

    /**
     * search all blog posts for a given query and show the result
     */
    public static function search()
    {
        $query = Flight::request()->query ['q'];

        $blogs = Model::getBlogsContaining($query);

        View::renderSearch($query, $blogs);
    }

    /**
     * initialise the application database
     */
    public static function install()
    {
        Model::migrationUp();
        Flight::redirect('/admin');
    }

    /**
     * delete the database table in preparation of uninstalling the application
     */
    public static function uninstall()
    {
        Model::migrationDown();
        Flight::redirect('/admin');
    }

    /**
     * register all request handlers
     */
    public static function register()
    {
        // admin interface pages
        Flight::route('/admin', [
            'Controller',
            'adminIndex'
        ]);

        Flight::route('/admin/blogs', [
            'Controller',
            'adminBlogs'
        ]);

        // admin high level functions
        Flight::route('/admin/install', [
            'Controller',
            'install'
        ]);

        Flight::route('/admin/uninstall', [
            'Controller',
            'uninstall'
        ]);

        // admin blog post pages - special pages first
        Flight::route('/admin/blogs/deleteall', [
            'Controller',
            'adminDeleteAllBlogs'
        ]);

        Flight::route('/admin/blogs/newfake(/@num:[0-9]+)', [
            'Controller',
            'adminFakeBlogs'
        ]);

        Flight::route('GET|POST /admin/blogs/new', [
            'Controller',
            'adminNewBlog'
        ]);

        Flight::route('GET|POST /admin/blogs/@id', [
            'Controller',
            'adminUpdateBlog'
        ]);

        Flight::route('/admin/blogs/@id/delete', [
            'Controller',
            'adminDeleteBlog'
        ]);

        Flight::route('/admin/blogs/@id:[0-9]+/fake(/@num:[0-9]+)', [
            'Controller',
            'adminBlogFakeComments'
        ]);

        // blog post index view
        Flight::route('/(@page:[0-9]+)', [
            'Controller',
            'blogs'
        ]);

        // individual blog post view
        Flight::route('GET|POST /blogs/@id:[0-9]+', [
            'Controller',
            'blog'
        ]);

        Flight::route('/search', [
            'Controller',
            'search'
        ]);
    }
}