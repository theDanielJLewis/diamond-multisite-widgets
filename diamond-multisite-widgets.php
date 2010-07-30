<?php
/*
 Plugin Name: Diamond MultiSite Widgets
 Plugin URI: http://www.amegrant.hu
 Description: Multisite recetn posts widget, Multisite recent comments widget. Content from the whole network.
 Author: Daniel Bozo
 Version: 1.0
 Author URI: http://www.amegrant.hu
 */
 
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


 
 	class DiamondRP {

	function DiamondRP() {
		add_action('widgets_init', array($this, 'init_diamondRP'));
	}
		 
		function init_diamondRP() {
		if ( !function_exists('register_sidebar_widget') ||
		!function_exists('register_widget_control') )
		return;
		 
		register_sidebar_widget(array(__('Diamond Recent Posts', 'rp-name'),'widgets'),array($this, 'widget_endView'));
		register_widget_control(array(__('Diamond Recent Posts', 'rp_name'), 'widgets'), array($this, 'widget_controlView'));
		 
	}


	function widget_endView($args)
	{
		global $switched;
		global $table_prefix;
		global $wpdb;
		
		$wgt_title=get_option('wgt_title');
		$wgt_count=get_option('wgt_count');		
		$wgt_miss= split(';', get_option('wgt_miss'));		
		$wgt_format= get_option('wgt_format');		
		
		extract($args);
		echo $before_widget.$before_title.$wgt_title.
		$after_title;
	
		$sqlstr = '';
		$blog_list = get_blog_list( 0, 'all' );
		if (!in_array(1, $wgt_miss)) {
			$sqlstr = "SELECT 1 as blog_id, id, post_date_gmt from ".$table_prefix ."posts where post_status = 'publish' and post_type = 'post' ";
		}
		$uni = '';
		
		foreach ($blog_list AS $blog) {
			if (!in_array($blog['blog_id'], $wgt_miss) && $blog['blog_id'] != 1) {
				if ($sqlstr != '')
					$uni = ' union ';;	
				$sqlstr .= $uni . " SELECT ".$blog['blog_id']." as blog_id, id, post_date_gmt from ".$table_prefix .$blog['blog_id']."_posts  where post_status = 'publish' and post_type = 'post' ";				
			}
		}
		
		$limit = '';
		if ((int)$wgt_count > 0)
			$limit = ' LIMIT 0, '. (int)$wgt_count;
		$sqlstr .= " ORDER BY post_date_gmt desc " . $limit;		
		
		
		// echo $sqlstr; 
		$post_list = $wpdb->get_results($sqlstr, ARRAY_A);
		
		echo '<ul>';
		foreach ($post_list AS $post) {
			echo '<li>';
			
			$txt = ($wgt_format == '') ? '<b>{title}<b> - <i>{date}<i>' : $wgt_format;
			

			$p = get_blog_post($post["blog_id"], $post["id"]);
			$txt = str_replace('{title}', '<a href="' .get_blog_permalink($post["blog_id"], $post["id"]).'">'.$p->post_title.'</a>' , $txt);
			$txt = str_replace('{date}', $p->post_date, $txt);
			$txt = str_replace('{author}', get_userdata($p->post_author)->nickname, $txt);
			$txt = str_replace('{blog}', get_blog_option($post["blog_id"], 'blogname') , $txt);		
			
			echo $txt;
			echo '</li>';
		}
		echo '</ul>';
		
		echo $wpdb->print_error(); 
		//print_r($post_list);		
		
		echo $after_widget;
	}
	 
	function widget_controlView()
	{
		// Title
		if ($_POST['wgt_title']) {
			$option=$_POST['wgt_title'];
			update_option('wgt_title',$option);		
		}
		$wgt_title=get_option('wgt_title');
		
		echo '<input type="hidden" name="wgt_post_hidden" value="success" />';
		
		echo '<label for="wgt_title">' . __('Widget Title', 'widtitle') . ':<br /><input id="wgt_title" name="wgt_title" type="text" value="'.$wgt_title.'" /></label>';
		
		// Count
		if ($_POST['wgt_count']) {
			$option=$_POST['wgt_count'];
			update_option('wgt_count',$option);
		}
		$wgt_count=get_option('wgt_count');
		echo '<br /><label for="wgt_number">' .__('Posts count', 'postcount') . ':<br /><input id="wgt_count" name="wgt_count" type="text" value="'.$wgt_count.'" /></label>';		
		
		// miss blogs
		if ($_POST['wgt_post_hidden']) {		
			$option=$_POST['wgt_miss'];
			$tmp = '';
			$sep = '';
			if (isset($option) && $option != '')
			foreach ($option AS $op) {			
				$tmp .= $sep .$op;
				$sep = ';';
			}
			update_option('wgt_miss',$tmp);		
		}
		
		$wgt_miss=get_option('wgt_miss');
		$miss = split(';',$wgt_miss);
		echo '<br /><label for="wgt_miss">' . __('Exclude blogs: (The first 50 blogs)','missstr');
		$blog_list = get_blog_list( 0, 50 ); 
		echo '<br />';
		foreach ($blog_list AS $blog) {
			echo '<input id="wgt_miss_'.$blog['blog_id'].'" name="wgt_miss[]" type="checkbox" value="'.$blog['blog_id'].'" ';
			if (in_array($blog['blog_id'], $miss)) echo ' checked="checked" ';
			echo ' />';
			echo get_blog_option( $blog['blog_id'], 'blogname' );
			echo '<br />';
		}
		echo '</label>';		
		
		
		// Format
		if ($_POST['wgt_format']) {
			$option=$_POST['wgt_format'];
			update_option('wgt_format',$option);
		}
		$wgt_format=get_option('wgt_format');
		echo '<label for="wgt_number">' . __('Format string', 'formatstr') .':<br /><input id="wgt_format" name="wgt_format" type="text" value="'.$wgt_format.'" /></label><br />';		
		echo '{title} - '. __('The post\'s title', 'posttitle').'<br />';
		echo '{date} - ' . __('The post\'s date', 'postdate') .'<br />';
		echo '{author} - ' . __('The post\'s author', 'postauthor') .'<br />';
		echo '{blog} - '. __('The post\'s blog name', 'postblog') .'<br />';
		echo '<br />';
		_e('if you like this widget then', 'ifyoulike');
		echo ': <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40amegrant%2ehu&lc=HU&item_name=Diamond%20Multisite%20WordPress%20Widget&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">';
		_e('Buy me a beer!', 'buy me beer');
		echo '</a><br />';
	}
	
	}
	$newWidget = new DiamondRP ();
 
 
 
 
 	class DiamondRC {

	function DiamondRC() {
	add_action('widgets_init', array($this, 'init_diamondRC'));
	}
		 
		function init_diamondRC() {
		if ( !function_exists('register_sidebar_widget') ||
		!function_exists('register_widget_control') )
		return;
		 
		register_sidebar_widget(array(__('Diamond Recent Comments', 'rc-name'),'widgets'),array($this, 'widget_endView'));
		register_widget_control(array(__('Diamond Recent Comments', 'rc-name'), 'widgets'), array($this, 'widget_controlView'));
		 
	}


	function widget_endView($args)
	{
		global $switched;
		global $table_prefix;
		global $wpdb;
		
		$wgt_title=get_option('c_wgt_title');
		$wgt_count=get_option('c_wgt_count');		
		$wgt_miss= split(';', get_option('c_wgt_miss'));		
		$wgt_format= get_option('c_wgt_format');		
		
		extract($args);
		echo $before_widget.$before_title.$wgt_title.
		$after_title;
	
		$sqlstr = '';
		$blog_list = get_blog_list( 0, 'all' );
		if (!in_array(1, $wgt_miss)) {
			$sqlstr = "SELECT 1 as blog_id, comment_date, comment_id, comment_post_id, comment_content, comment_date_gmt from ".$table_prefix ."comments where comment_approved = 1 ";
		}
		$uni = '';
		
		foreach ($blog_list AS $blog) {
			if (!in_array($blog['blog_id'], $wgt_miss) && $blog['blog_id'] != 1) {
				if ($sqlstr != '')
					$uni = ' union ';;	
				$sqlstr .= $uni . " SELECT ".$blog['blog_id']." as blog_id, comment_date, comment_id, comment_post_id, comment_content, comment_date_gmt  from ".$table_prefix .$blog['blog_id']."_comments where comment_approved = 1 ";				
			}
		}
		
		$limit = '';
		if ((int)$wgt_count > 0)
			$limit = ' LIMIT 0, '. (int)$wgt_count;
		$sqlstr .= " ORDER BY comment_date_gmt desc " . $limit;		
				
		// echo $sqlstr; 
		 
		$comm_list = $wpdb->get_results($sqlstr, ARRAY_A);	
		
		
		
		echo '<ul>';
		foreach ($comm_list AS $comm) {
			echo '<li>';
			
			$txt = ($wgt_format == '') ? '<b>{title}<b> - <i>{date}<i>' : $wgt_format;			

			$p = get_blog_post($comm["blog_id"], $post["comment_post_id"]);
			$c = $comm['comment_content'];
			if (strlen($c) > 50) 
				$c = strip_tags(substr($c, 0, 51)) . '...';
			$txt = str_replace('{title}', '<a href="' .get_blog_permalink($comm["blog_id"], $comm["comment_post_id"]).'">'.$c.'</a>' , $txt);
			$txt = str_replace('{date}', $comm['comment_date'], $txt);			
			
			echo $txt;
			echo '</li>';
		}
		echo '</ul>';
		
		echo $wpdb->print_error(); 
		//print_r($post_list);		
		
		echo $after_widget;
	}
	 
	function widget_controlView()
	{
		// Title
		if ($_POST['wgt_title']) {
			$option=$_POST['wgt_title'];
			update_option('c_wgt_title',$option);		
		}
		$wgt_title=get_option('c_wgt_title');
		
		echo '<input type="hidden" name="wgt_post_hidden" value="success" />';
		
		echo '<label for="wgt_title">' . __('Widget Title', 'widtitle') . ':<br /><input id="wgt_title" name="wgt_title" type="text" value="'.$wgt_title.'" /></label>';
		
		// Count
		if ($_POST['wgt_count']) {
			$option=$_POST['wgt_count'];
			update_option('c_wgt_count',$option);
		}
		$wgt_count=get_option('c_wgt_count');
		echo '<br /><label for="wgt_number">'.__('Comments count', 'commcount').'<br /><input id="wgt_count" name="wgt_count" type="text" value="'.$wgt_count.'" /></label>';		
		
		// miss blogs
		if ($_POST['wgt_post_hidden']) {		
			$option=$_POST['wgt_miss'];
			$tmp = '';
			$sep = '';
			if (isset($option) && $option != '')
			foreach ($option AS $op) {			
				$tmp .= $sep .$op;
				$sep = ';';
			}
			update_option('c_wgt_miss',$tmp);		
		}
		
		$wgt_miss=get_option('c_wgt_miss');
		$miss = split(';',$wgt_miss);
		echo '<br /><label for="wgt_miss">' . __('Exclude blogs: (The first 50 blogs)','missstr');
		$blog_list = get_blog_list( 0, 50 ); 
		echo '<br />';
		foreach ($blog_list AS $blog) {
			echo '<input id="wgt_miss_'.$blog['blog_id'].'" name="wgt_miss[]" type="checkbox" value="'.$blog['blog_id'].'" ';
			if (in_array($blog['blog_id'], $miss)) echo ' checked="checked" ';
			echo ' />';
			echo get_blog_option( $blog['blog_id'], 'blogname' );
			echo '<br />';
		}
		echo '</label>';		
		
		
		// Format
		if ($_POST['wgt_format']) {
			$option=$_POST['wgt_format'];
			update_option('c_wgt_format',$option);
		}
		$wgt_format=get_option('c_wgt_format');
		echo '<label for="wgt_number">' . __('Format string', 'formatstr') .':<br /><input id="wgt_format" name="wgt_format" type="text" value="'.$wgt_format.'" /></label><br />';		
		echo '{title} - '. __('The comment\'s content', 'comcont') . '</p><br />';
		echo '{date} - '.__('The post\'s date', 'commdate'). '</p><br />';
		echo '<br />';
		_e('if you like this widget then', 'ifyoulike');
		echo ': <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40amegrant%2ehu&lc=HU&item_name=Diamond%20Multisite%20WordPress%20Widget&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">';
		_e('Buy me a beer!', 'buy me beer');
		echo '</a><br />';
		
	}
	
	}
	$newWidget2 = new DiamondRC ();
	?>