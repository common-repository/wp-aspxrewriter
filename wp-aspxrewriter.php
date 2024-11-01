<?php
/*
Plugin Name: ASPX WebRequest Rewriter
Plugin URI: http://www.the-mice.co.uk/switch/
Description: Configures a wordpress site for ASP.NET Url Rewriting
Author: Martin Hinks
Version: 1.0
Author URI: http://www.the-mice.co.uk/switch/

Copyright (c) 2008 Martin Hinks

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

add_action ('admin_menu', 'wp_aspx_rewriter_menu');
add_filter('feed_link', 'wp_aspx_rewriter_feed_filter', 10, 2);
add_filter('trackback_url', 'wp_aspx_rewriter_trackback_url_filter', 10, 1);
add_filter('category_link', 'wp_aspx_rewriter_simple_filter', 10, 1);
add_filter('tag_link', 'wp_aspx_rewriter_simple_filter', 10, 1);
add_filter('month_link', 'wp_aspx_rewriter_simple_filter', 10, 1);
add_filter('post_comments_feed_link', 'wp_aspx_rewriter_comment_link_filter', 10, 1);
add_filter('page_link', 'wp_aspx_rewriter_page_filter', 10, 1);
add_filter('posts_nav_link', 'wp_aspx_rewriter_simple_filter', 10, 1);
add_filter('get_pagenum_link', 'wp_aspx_rewriter_page_link_filter', 10, 1);


function wp_aspx_rewriter_page_link_filter($output) {
	
	if($output == get_option('home') . '/') {
		return $output;
	}

	$output=str_replace('?','/',$output);
	$output=str_replace('=','/',$output);

	$output = str_replace(get_option('home') . '//', get_option('home') . '/page/1/', $output);

	return $output . '/page.aspx';
}

function wp_aspx_rewriter_menu() {
    if (function_exists('add_management_page')) {
		add_management_page('ASPX Rewriter', 'ASPX Rewriter', 8, __FILE__, 'wp_aspx_rewriter_manage');
	}
}

function wp_aspx_rewriter_simple_filter($output) {
	return $output . '.aspx';
}


function wp_aspx_rewriter_page_filter($output) {
	$postid = url_to_postid($output);
	return $output . '/page-' . $postid . '.aspx';
}

function wp_aspx_rewriter_comment_link_filter($output) {
	return str_replace('.aspx/feed', '/feed.aspx', $output);
}

function wp_aspx_rewriter_trackback_url_filter($output) {
	return str_replace('.aspx/trackback', '/trackback.aspx', $output);
}

function wp_aspx_rewriter_feed_filter($output, $feed) {

	if (!$feed && false != strpos($output, '/comments/')) {
		$feed = 'comments_rss2';
	} elseif (!$feed) {
		$feed = 'rss2';
	}

	return $output . '-' . $feed . '.aspx';
}

function wp_aspx_rewriter_write_setting($url, $regex) {
	return '&lt;add value="' . $url . '" key="' . $regex . '"/&gt;<br/>';
}

function wp_aspx_rewriter_manage() {

global $wp_rewrite;

$rewritecode = array(
        	'%year%',
        	'%monthnum%',
        	'%day%',
	'%hour%',
	'%minute%',
	'%second%',
        	'%postname%',
       	 '%post_id%',
	'%category%',
	'%author%'
    );
	    
$rewritereplace = array(
                	'(?:[0-9]{4})',
                	'(?:[0-9]{2})',
                	'(?:[0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[a-zA-Z0-9\-]+)',
                	'([0-9]{1,9})',
                	'(?:[a-zA-Z0-9\-]+)',
                	'(?:[a-zA-Z0-9\-]+)'
            );

$rewritereplacedates = array(
                	'([0-9]{4})',
                	'([0-9]{2})',
                	'([0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[0-9]{1,2})',
                	'(?:[a-zA-Z0-9\-]+)',
                	'(?:[0-9]{1,9})',
                	'(?:[a-zA-Z0-9\-]+)',
                	'(?:[a-zA-Z0-9\-]+)'
            );

$home = get_settings('home');


$permalink_regex = $home . str_replace($rewritecode, $rewritereplace, get_settings('permalink_structure'));

$month_regex = $home . str_replace($rewritecode, $rewritereplacedates, user_trailingslashit($wp_rewrite->get_month_permastruct(), 'month'));

$day_regex = $home . str_replace($rewritecode, $rewritereplacedates, user_trailingslashit($wp_rewrite->get_day_permastruct(), 'week'));


$tag_base = get_option('tag_base');
$cat_base = get_option('category_base');
?>
<div class=wrap>
  <h2>ASPX Rewriter</h2>
  <fieldset class="options">
  <p>Here is the setup that you need to place in web.config for use with this blog and ASPX Rewriter:</p>

<?php

//posts
echo wp_aspx_rewriter_write_setting($home . '/' . '?p=$1', $permalink_regex . '$');
//tags
echo wp_aspx_rewriter_write_setting($home . '/' . '?tag=$1', $home . get_option('tag_base') . '/([^\.]+)\.aspx$');
//categories
echo wp_aspx_rewriter_write_setting($home . '/' . '?category_name=$1', $home . get_option('category_base') . '/([^\.]+)\.aspx$');
//trackback
echo wp_aspx_rewriter_write_setting($home . '/' . 'wp-trackback.php?p=$1', wp_aspx_rewriter_trackback_url_filter($permalink_regex . '/trackback') . '$');
//feeds
echo wp_aspx_rewriter_write_setting($home . '/' . '?feed=$1', $home . '/feed\-([^.]+)\.aspx$');
echo wp_aspx_rewriter_write_setting($home . '/' . '?feed=$1', $home . '/feed/[^\-]+\-([^.]+)\.aspx$');
echo wp_aspx_rewriter_write_setting($home . '/' . '?wp-commentsrss2.php', $home . '/comments/feed\-([^.]+)\.aspx$');
//individual comment feed
echo wp_aspx_rewriter_write_setting($home . '/' . '?feed=rss2&amp;amp;p=$1', wp_aspx_rewriter_comment_link_filter($permalink_regex . '/feed') . '$');
//monthly posts
echo wp_aspx_rewriter_write_setting($home . '/' . '?m=$1$2', $month_regex . '\.aspx$');
//daily posts
echo wp_aspx_rewriter_write_setting($home . '/' . '?m=$1$2$3', $day_regex . '\.aspx$');
//pages
echo wp_aspx_rewriter_write_setting($home . '/' . '?page_id=$1', '[^/]+/page-([0-9]+)\.aspx$');
//pagination
echo wp_aspx_rewriter_write_setting($home . '/' . '?paged=$1', $home . '/page/([0-9]{1,9})/page\.aspx$');
echo wp_aspx_rewriter_write_setting($home . '/' . '?paged=$1&amp;amp;$2=$3', $home . '/page/([0-9]{1,9})/(cat|tag)/([a-zA-Z0-9\-]+)/page\.aspx$');
echo wp_aspx_rewriter_write_setting($home . '/' . '?paged=$1&amp;amp;m=$2', $home . '/page/([0-9]{1,9})/m/([0-9\-]{6,8})/page\.aspx$');

?>

<?php

}
?>