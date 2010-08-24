<?php
/*
 Plugin Name: Diamond MultiSite Widgets
 Plugin URI: http://wordpress.org/extend/plugins/diamond-multisite-widgets/
 Description: Multisite recent posts widget, Multisite recent comments widget. Content from the whole network. An administration widget on the post-writing window. You can copy your post to the network's sub blogs.
 Author: Daniel Bozo
 Version: 1.3
 Author URI: http://www.amegrant.hu
 */
 
/*  Copyright 2010  Daniel Bozo  (email : daniel.bozo@amegrant.hu)

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
		global $wpdb;
		$table_prefix = $wpdb->base_prefix;
		
		$wgt_title=get_option('wgt_title');
		$wgt_count=get_option('wgt_count');		
		$wgt_miss= split(';', get_option('wgt_miss'));		
		$wgt_format = get_option('wgt_format');		
		$wgt_avsize = get_option('wgt_avsize');		
		$wgt_mtext = get_option('wgt_mtext');		
		$wgt_defav = get_option('wgt_defav');		
		$wgt_dt = get_option('wgt_dt');		
		
		if (!isset($wgt_dt) || trim($wgt_dt) =='') 
			$wgt_dt = 'M. d. Y.';
		
		if (!isset($wgt_avsize) || $wgt_avsize == '')
			$wgt_avsize = 96;
		
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
		
		
		 //echo $sqlstr; 
		$post_list = $wpdb->get_results($sqlstr, ARRAY_A);
		//echo $wpdb->print_error(); 
		//print_r($post_list);
		echo '<ul>';
		foreach ($post_list AS $post) {
			echo '<li>';
			
			$txt = ($wgt_format == '') ? '<b>{title}<b> - <i>{date}<i>' : $wgt_format;
			
			$p = get_blog_post($post["blog_id"], $post["id"]);			
			
			$av = get_avatar(get_userdata($p->post_author)->user_email, $wgt_avsize, $defav);
			
			$ex = $p->post_excerpt;
			if (!isset($ex) || trim($ex) == '')
				$ex = substr(strip_tags($p->post_content), 0, 65) . '...';
			
			$txt = str_replace('{title}', '<a href="' .get_blog_permalink($post["blog_id"], $post["id"]).'">'.$p->post_title.'</a>' , $txt);
			$txt = str_replace('{more}', '<a href="' .get_blog_permalink($post["blog_id"], $post["id"]).'">'.$wgt_mtext.'</a>' , $txt);
			$txt = str_replace('{title_txt}', $p->post_title , $txt);
			$txt = str_replace('{date}', date_i18n($wgt_dt, strtotime($p->post_date)), $txt);
			$txt = str_replace('{excerpt}', $ex , $txt);
			$txt = str_replace('{author}', get_userdata($p->post_author)->nickname, $txt);
			$txt = str_replace('{avatar}', $av , $txt);
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
			if (!isset($option) || $option == '')
				$option = '<b>{title}<b> - <i>{date}<i>';
			update_option('wgt_format',$option);
		}
		$wgt_format=get_option('wgt_format');
		echo '<label for="wgt_number">' . __('Format string', 'formatstr') .':<br /><input id="wgt_format" name="wgt_format" type="text" value="'.$wgt_format.'" /></label><br />';		
		echo '{title} - '. __('The post\'s title', 'posttitle').'<br />';
		echo '{title_txt} - '. __('The post\'s title', 'posttitle').' '.__('(without link)', 'posttitletxt').'<br />';
		echo '{excerpt} - '. __('The post\'s excerpt', 'postexcerpt').'<br />';		
		echo '{date} - ' . __('The post\'s date', 'postdate') .'<br />';
		echo '{author} - ' . __('The post\'s author', 'postauthor') .'<br />';
		echo '{avatar} - ' . __('Author\'s avatar', 'postauthoravatar') .'<br />';
		echo '{blog} - '. __('The post\'s blog name', 'postblog') .'<br />';
		echo '{more} - '. __('The "Read More" link', 'rmlink') .'<br />';
		echo '<br />';
		
		
		if ($_POST['wgt_avsize']) {
			$option=$_POST['wgt_avsize'];
			if (!isset($option) || $option == '')
				$option = 96;
			update_option('wgt_avsize',$option);		
		}
		$wgt_avsize=get_option('wgt_avsize');	
		
		echo '<label for="wgt_avsize">' . __('Avatar Size (px)', 'avsize') .
		':<br /><input id="wgt_avsize" name="wgt_avsize" type="text" value="'.
		$wgt_avsize.'" /></label>';
		echo '<br />';
		
		
		if ($_POST['wgt_defav']) {
			$option=$_POST['wgt_defav'];			
			update_option('wgt_defav',$option);		
		}
		$wgt_defav=get_option('wgt_defav');	
		
		echo '<label for="wgt_defav">' . __('Default Avatar URL', 'defav') .
		':<br /><input id="wgt_defav" name="wgt_defav" type="text" value="'.
		$wgt_defav.'" /></label>';
		echo '<br />';
		
		
		
		if ($_POST['wgt_mtext']) {
			$option=$_POST['wgt_mtext'];
			if (!isset($option) || $option == '')
				$option = 'Read More';
			update_option('wgt_mtext',$option);		
		}
		$wgt_mtext=get_option('wgt_mtext');	
		
		echo '<label for="wgt_mtext">' . __('"Read More" link text', 'rmtext') . 
		':<br /><input id="wgt_mtext" name="wgt_mtext" type="text" value="'.
		$wgt_mtext.'" /></label>';
		echo '<br />';		
		
		if ($_POST['wgt_dt']) {
			$option=$_POST['wgt_dt'];			
			update_option('wgt_dt',$option);		
		}
		$wgt_dt=get_option('wgt_dt');	
		if (!isset($wgt_dt) || trim($wgt_dt) =='') {
			$wgt_dt = 'M. d. Y.';
			update_option('wgt_dt',$wgt_dt);				
		}
		
		echo '<label for="wgt_dt">' . __('DateTime format (<a href="http://php.net/manual/en/function.date.php" target="_blank">manual</a>)', 'dttext') . 
		':<br /><input id="wgt_dt" name="wgt_dt" type="text" value="'.
		$wgt_dt.'" /></label>';
		echo '<br />';	
		
		
		
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
		global $wpdb;
		$table_prefix = $wpdb->base_prefix;
		
		$wgt_title=get_option('c_wgt_title');
		$wgt_count=get_option('c_wgt_count');		
		$wgt_miss= split(';', get_option('c_wgt_miss'));		
		$wgt_format= get_option('c_wgt_format');		
		$wgt_avsize = get_option('wgtc_avsize');		
		$wgt_mtext = get_option('wgtc_mtext');		
		$wgt_defav = get_option('wgtc_defav');		
		$wgt_dt = get_option('wgtc_dt');		
		
		if (!isset($wgt_dt) || trim($wgt_dt) =='') 
			$wgt_dt = 'M. d. Y.';
		
		if (!isset($wgt_avsize) || $wgt_avsize == '')
			$wgt_avsize = 96;
		
		extract($args);
		echo $before_widget.$before_title.$wgt_title.
		$after_title;
	
		$sqlstr = '';
		$blog_list = get_blog_list( 0, 'all' );
		if (!in_array(1, $wgt_miss)) {
			$sqlstr = "SELECT 1 as blog_id, comment_date, comment_id, comment_post_id, comment_content, comment_date_gmt, comment_author, comment_author_email from ".$table_prefix ."comments where comment_approved = 1 ";
		}
		$uni = '';
		
		foreach ($blog_list AS $blog) {
			if (!in_array($blog['blog_id'], $wgt_miss) && $blog['blog_id'] != 1) {
				if ($sqlstr != '')
					$uni = ' union ';;	
				$sqlstr .= $uni . " SELECT ".$blog['blog_id']." as blog_id, comment_date, comment_id, comment_post_id, comment_content, comment_date_gmt, comment_author, comment_author_email   from ".$table_prefix .$blog['blog_id']."_comments where comment_approved = 1 ";				
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
			
			$av = get_avatar($comm['comment_author_email'], $wgt_avsize, $defav);
			
			if (strlen($c) > 50) 
				$c = substr(strip_tags($c), 0, 51) . '...';
			$txt = str_replace('{title}', '<a href="' .get_blog_permalink($comm["blog_id"], $comm["comment_post_id"]).'">'.$c.'</a>' , $txt);
			$txt = str_replace('{title_txt}', $c, $txt);
			$txt = str_replace('{author}', $comm['comment_author'], $txt);
			$txt = str_replace('{avatar}', $av, $txt);
			$txt = str_replace('{date}', date_i18n($wgt_dt, strtotime($comm['comment_date'])), $txt);			
			
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
		echo '{title_txt} - '. __('The comment\'s title', 'posttitle').' '.__('(without link)', 'posttitletxt').'<br />';
		echo '{date} - '.__('The post\'s date', 'commdate'). '</p><br />';
		echo '{author} - ' . __('The comment\'s author', 'commauthor') .'<br />';
		echo '{avatar} - ' . __('Author\'s avatar', 'postauthoravatar') .'<br />';
		echo '<br />';	
		
		
		if ($_POST['wgtc_avsize']) {
			$option=$_POST['wgtc_avsize'];
			if (!isset($option) || $option == '')
				$option = 96;
			update_option('wgtc_avsize',$option);		
		}
		$wgtc_avsize=get_option('wgtc_avsize');	
		
		echo '<label for="wgtc_avsize">' . __('Avatar Size (px)', 'avsize') . ':<br /><input id="wgtc_avsize" name="wgtc_avsize" type="text" value="'.$wgtc_avsize.'" /></label>';
		echo '<br />';
		
		
		if ($_POST['wgtc_defav']) {
			$option=$_POST['wgtc_defav'];			
			update_option('wgtc_defav',$option);		
		}
		$wgtc_defav=get_option('wgtc_defav');	
		
		echo '<label for="wgtc_defav">' . __('Default Avatar URL', 'defav') . ':<br /><input id="wgtc_defav" name="wgtc_defav" type="text" value="'.$wgtc_defav.'" /></label>';
		echo '<br />';
		
		
		
		if ($_POST['wgtc_mtext']) {
			$option=$_POST['wgtc_mtext'];
			if (!isset($option) || $option == '')
				$option = 'Read More';
			update_option('wgtc_mtext',$option);		
		}
		$wgtc_mtext=get_option('wgtc_mtext');	
		
		echo '<label for="wgtc_mtext">' . __('"Read More" link text', 'rmtext') . ':<br /><input id="wgtc_mtext" name="wgtc_mtext" type="text" value="'.$wgtc_mtext.'" /></label>';
		echo '<br />';	

		if ($_POST['wgtc_dt']) {
			$option=$_POST['wgtc_dt'];			
			update_option('wgtc_dt',$option);		
		}
		$wgtc_dt=get_option('wgtc_dt');	
		if (!isset($wgtc_dt) || trim($wgtc_dt) =='') {
			$wgtc_dt = 'M. d. Y.';
			update_option('wgtc_dt',$wgtc_dt);				
		}
		
		echo '<label for="wgtc_dt">' . __('DateTime format (<a href="http://php.net/manual/en/function.date.php" target="_blank">manual</a>)', 'dttext') . 
		':<br /><input id="wgtc_dt" name="wgtc_dt" type="text" value="'.
		$wgtc_dt.'" /></label>';
		echo '<br />';			
		
		echo '<br />';		
		
		_e('if you like this widget then', 'ifyoulike');
		echo ': <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40amegrant%2ehu&lc=HU&item_name=Diamond%20Multisite%20WordPress%20Widget&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">';
		_e('Buy me a beer!', 'buy me beer');
		echo '</a><br />';
		
	}
	
	}
	$newWidget2 = new DiamondRC ();
	
	
	
	// Broadcast Post
	class DiamondBCP {

		function DiamondBCP() {
			add_action('post_submitbox_start', array($this, 'widget_endView'));
			add_action('save_post', array($this, 'diamond_save_post'));
		}
	
	
		function diamond_save_post($post_ID) {
		
			
		
			global $switched;
			if ($switched	) {
				return;
			}		
			$datef = __( 'M j, Y @ G:i' );
		
			$post = get_post($post_ID, ARRAY_A);
			
			//print_r($post['post_type']);
			
			if ($post['post_type'] == 'revision')
				return;
				
			unset($post['ID']);			
			unset($post['post_parent']);
			$post['post_status'] = 'publish';
			$post['post_category'] = '';		 			
			unset($post['post_date'] );
			unset($post['post_date_gmt']) ;
			unset($post['post_name']);
			unset($post['guid']);
			unset($post['comment_count']);
			$post['post_type'] = 'post';
			
			
			//print_r($post);
			
			$blogarr = $_POST["diamond_blogs"];					
			
			//print_r($blogarr);
			
			$newshare = '';
			$sep = '';
			
			if ($blogarr){
				foreach ($blogarr as $b) {
					if ($b != 0 ) {							
						switch_to_blog($b);					
						wp_insert_post( $post, $wp_error );
						restore_current_blog();
						if ($wp_error) {
							print_r($wp_error);
						}
						else {
							$newshare .= $sep . $b;
							$sep = ';';
						}
					}
				}				
				$shared = get_post_custom_values('diamond_broadcast_blogs', $post_ID);
				if ($shared) {					
					$shared = $shared[0] . $sep . $newshare;
					update_post_meta($post_ID, 'diamond_broadcast_blogs', $shared);
				} else {
					add_post_meta($post_ID, 'diamond_broadcast_blogs', $newshare);	
				}
			}			
		}
		
		function widget_endView($args)
		{
			if (!is_super_admin())
				return;
			global $wpdb;				
			
			echo '<fieldset><legend>';
			echo __('Broadcast this post', 'diamond');
			echo '</legend>';			
			echo '<label>';
			echo __('Select blogs where you want to copy this post', 'diamond');
			echo '<select name="diamond_blogs[]" id="diamond_blogs" style="height:120px; width: 100%"  multiple="multiple">';
			echo '<option value="0">--- No broadcast ---</option>';
			$blog_list = get_blog_list( ); 			
			$shared = get_post_custom_values('diamond_broadcast_blogs', ($_GET['post']) ? $_GET['post'] : 0);
			
			$sharr = split(";", $shared[0]);
			
			foreach ($blog_list AS $blog) {
				if ($blog['blog_id'] != $wpdb->blogid)
					echo '<option value="'.  $blog['blog_id'].'">'. get_blog_option( $blog['blog_id'], 'blogname' );
					if ($sharr && in_array($blog['blog_id'], $sharr))
						echo __(' (copied)', 'diamond');
					echo '</option>'	;		
			}
			echo '</select>';
			echo '</label>';
			//print_r($shared[0]);
			//print_r($sharr)	;
			//print_r($shared);
			echo '</fieldset>';
		}
	 
		
	
	}
	$newWidget3 = new DiamondBCP ();	
	?>