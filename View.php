<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 16/07/2016
 * Time: 6:26 PM
 */

/**
 * View - produce web pages
 *
 * @author nick
 *
 */
class View
{

    /**
     * show admin dashboard
     *
     * @param bool $tableExists
     *            which form to display, depending on whether the database table has been set up
     */
    public static function renderAdminIndex($tableExists)
    {
        // show admin dashboard
        Flight::render('admin/index', [
            'tableExists' => $tableExists
        ], 'body_content');
        Flight::render('layout', [
            'pagetitle' => 'Admin Dashboard'
        ]);
    }

    /**
     * the admin blog list interface
     *
     * @param array $blogs
     *            collection of blogs to display
     *
     */
    public static function renderAdminBlogs($blogs)
    {
        Flight::render('admin/blogs', [
            'blogs' => $blogs
        ], 'body_content');
        Flight::render('layout');
    }

    /**
     * the blog editor form
     *
     * @param $blog array
     *            blog post to edit, default is empty form
     * @param $result array
     *            optional status message of previous action
     */
    public static function renderAdminBlog($blog = null, $result = null)
    {
        if (isset ($result) && !is_null($result)) {
            Flight::render('admin/blog', [
                'blog' => $blog,
                'result' => $result
            ], 'body_content');
        } else {
            Flight::render('admin/blog', [
                'blog' => $blog
            ], 'body_content');
        }
        Flight::render('layout');
    }

    /**
     * show an error page
     *
     * @param string $status
     *            message to display
     */
    public static function renderAdminError($status)
    {
        Flight::render('admin/message', array(
            'content' => $status
        ), 'body_content');

        Flight::render('layout', array(
            'pagetitle' => 'New Blog Post'
        ));
    }

    /**
     * the blog index
     *
     * @param array $blogs
     *            collection of blogs to display
     */
    public static function renderBlogs($blogs, $pages)
    {
        Flight::render('blogs', [
            'blogs' => $blogs,
            'pages' => $pages
        ], 'body_content');

        Flight::render('layout');
    }

    /**
     * view blog post by id
     *
     * @param array $blog
     *            blog post to display
     */
    public static function renderBlog($blog)
    {
        // render page content
        Flight::render('blog', [
            'blog' => $blog
        ], 'body_content');

        // render page layout
        Flight::render('layout', [
            'pagetitle' => $blog ['title']
        ]);
    }

    /**
     * view search results
     *
     * @param string $query
     *            search query to display
     * @param array $blogs
     *            blog posts to display
     */
    public static function renderSearch($query, $blogs)
    {
        Flight::render('search.php', [
            'query' => $query,
            'blogs' => $blogs
        ], 'body_content');
        Flight::render('layout');
    }

    /**
     * show file not found error message
     */
    public static function renderFileNotFound()
    {
        Flight::render('error', [
            'message' => 'No document found at that URI.'
        ], 'body_content');
        Flight::render('layout', [
            'pagetitle' => 'File Not Found'
        ]);
    }
}