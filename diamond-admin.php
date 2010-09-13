<?php

class DiamondAdmin {

	function DiamondAdmin() {
		add_action('admin_menu', array($this, 'my_plugin_menu'));	
		add_action('init', array($this, 'load_translation_domain'));										
	}		
	
	function load_translation_domain() {		
		$plugin_dir = basename(dirname(dirname(__FILE__)));
		load_plugin_textdomain( 'diamond', false, $plugin_dir . '/languages' );
	}
	
	function set_style_head() {
		echo '<link rel="stylesheet" id="diamond-admin"  href="'. plugins_url("", __FILE__) . '/css/adminstyle.css' .'" type="text/css" media="all" />';	
	}
	
	function my_plugin_menu() {
		$plugin_page = add_options_page(__('Diamond Options', 'diamond'), __('Diamond MultiSite Widgets', 'diamond'), 'manage_options', 'diamond-multisite-widgets-options', array($this, 'show_adminPage'));
		add_action( 'admin_head-'. $plugin_page, array($this, 'set_style_head' ));
	}		

	function show_adminHeader() {	
		echo '<table class="header_table" width="100%" ><tr><td>';
		echo '<img class="logo" src="' . plugins_url("", __FILE__) .'/images/diamond_small.jpg" alt="Diamond MultiSite Widgets logo" title="Diamond MultiSite Widgets logo" />';		
		echo '</td><td>';		
		echo '<a href="http://www.amegrant.com" target="_blank">';
		echo '<img src="'. plugins_url("", __FILE__) . '/images/about_us.jpg' .'" />';
		echo '</td></tr></table>';
		
	}
	 
	function show_adminPage()
	{	
	
		wp_enqueue_style('diamond-admin', plugins_url("", __FILE__) . '/css/adminstyle.css');
		
		global $newWidget;
		global $newWidget2;
		global $feedObj;
		$this->show_adminHeader();
		
		if (is_super_admin()) {		
		
			// *** Broadcast Post ***
			if (isset($_POST['diamond_broadcast_submit'])) {
				if ($_POST['diamond_broadcast_cb']  && $_POST['diamond_broadcast_cb']  == "1") 
					update_option('diamond_allow_broadcast', 1);
				else
					update_option('diamond_allow_broadcast', 0);
			}
		
			echo '<div class="broadcast_posts" >';
			echo '<h3 class="subtitle">';
			_e('Broadcast Posts', 'diamond');
			echo '</h3>';
			echo '<form action="" name="broadcastpostform" method="post">';
			echo '<input type="checkbox" value="1" name="diamond_broadcast_cb"';
			if (get_option('diamond_allow_broadcast') != 0) 
				echo ' checked="checked" ';
			echo ' />';
			echo '<span class="checkboxtext">';
			_e('Enable Broadcast Post Widget', 'diamond');
			echo '</span';
			echo '<p class="broadcastp">';
			_e('This widget enables you to copy your posts to other blogs. Check the post edit window', 'diamond');
			echo '</p>';
			echo '<span class="update"';
			echo '<input type="submit" name="diamond_broadcast_submit" value="'. __('Update', 'diamond') .'">';
			echo '</span';
			echo '</form>';
			echo '</div>';		
		}
		
		echo '<div class="recent_posts">';
		echo '<h3  class="subtitle">';
		_e('Recent Posts', 'diamond');
		echo '</h3>';
		
		echo '<form action="" name="diamondpostform" method="post">';
		echo '<p>';
		$newWidget->widget_controlView(true);echo '</p>';
		echo '<span class="update"';
		echo '<input type="submit" name="diamond_post_submit" value="'. __('Update', 'diamond') .'">';
		echo '</span';
		echo '</form>';
		echo '</div>';
		
		echo '<div class="recent_comments">';
		echo '<h3 class="subtitle">';
		echo '<form action="" name="diamondcommentform" method="post">';
		_e('Recent Comments', 'diamond');
		echo '</h3>';
		$newWidget2->widget_controlView(true);
		echo '<span class="update"';
		echo '<input type="submit" name="diamond_post_submit" value="'. __('Update', 'diamond') .'">';
		echo '</span';
		echo '</form>';
		echo '</div>';	
		
		echo '<div>';
			echo '<div class="recent_feed">';
		echo '<h3 class="subtitle">';
		_e('Recent Posts Feed', 'diamond');
		echo '</h3>';
		echo '<form action="" name="diamondfeedform" method="post">';
		$feedObj->feed_adminPage();
		echo '<span class="update"';
		echo '<input type="submit" name="diamond_feed_submit" value="'. __('Update', 'diamond') .'">';
		echo '</span';
		echo '</form>';
		echo '</div>';
		echo '<div class="donate_image_container">';
		echo '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40amegrant%2ehu&lc=HU&item_name=Diamond%20Multisite%20WordPress%20Widget&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">';
		echo '<img src="'. plugins_url("", __FILE__) . '/images/buy_me_a_beer.jpg' .'" />';
		echo '</a>';
		
		echo '</div>';
		echo '</div>';
		
		
		
		
		
	}
}	
	$adminObj = new DiamondAdmin();

?>